<?php
// ============================================================
// cron/process_commissions.php
// Chạy hàng ngày lúc 8:00 sáng
// Lệnh crontab: 0 8 * * * php /var/www/html/mlm/cron/process_commissions.php
// ============================================================
require_once __DIR__ . '/../define.php';
require_once __DIR__ . '/../' . PATH_MAIN_FUNCTION . '/conn-login-logout.php';

$conn = connection_to_database();
$log  = "[" . date('Y-m-d H:i:s') . "] === Bắt đầu xử lý hoa hồng ===\n";

// -------------------------------------------------------
// BƯỚC 1: Lấy tất cả payment đã qua 22 ngày, chưa xử lý
// -------------------------------------------------------
$sql = "SELECT pr.*, c.agent_id, a.current_rank_id
        FROM   payment_record pr
        JOIN   contract c ON c.contract_id = pr.contract_id
        JOIN   agent    a ON a.agent_id     = c.agent_id
        WHERE  pr.commission_processed   = 0
          AND  pr.commission_unlock_date <= CURDATE()
          AND  c.status != 'cancelled'";

$result   = mysqli_query($conn, $sql);
$payments = [];
while ($r = mysqli_fetch_assoc($result)) { $payments[] = $r; }

$log .= "Tìm thấy " . count($payments) . " lần thanh toán cần xử lý.\n";

$countOk  = 0;
$countErr = 0;

foreach ($payments as $payment) {
    $pid          = (int)$payment['payment_id'];
    $agentId      = (int)$payment['agent_id'];
    $rankId       = (int)$payment['current_rank_id'];
    $baseAmount   = (float)$payment['amount_paid'];

    mysqli_begin_transaction($conn);
    try {
        // Lấy tỷ lệ hoa hồng của người bán
        $rR       = mysqli_query($conn, "SELECT commission_rate FROM rank_config WHERE rank_id = $rankId");
        $rRow     = mysqli_fetch_assoc($rR);
        $sellerRate = (float)$rRow['commission_rate'];

        // HOA HỒNG TRỰC TIẾP cho người bán
        $directAmt = floor($baseAmount * $sellerRate);
        mysqli_query($conn,
            "INSERT INTO commission_transaction
                 (payment_id, beneficiary_agent_id, commission_type, rank_at_time,
                  base_amount, rate_applied, commission_amount)
             VALUES ($pid, $agentId, 'direct', $rankId, $baseAmount, $sellerRate, $directAmt)"
        );

        // HOA HỒNG OVERRIDE: lấy tất cả cấp trên
        $ancestors = [];
        $aR = mysqli_query($conn,
            "SELECT h.ancestor_id, a.current_rank_id, rc.commission_rate
             FROM   agent_hierarchy h
             JOIN   agent a        ON a.agent_id  = h.ancestor_id
             JOIN   rank_config rc ON rc.rank_id  = a.current_rank_id
             WHERE  h.descendant_id = $agentId AND h.depth > 0
             ORDER  BY h.depth ASC"
        );
        while ($ar = mysqli_fetch_assoc($aR)) { $ancestors[] = $ar; }

        $prevRate = $sellerRate;
        foreach ($ancestors as $anc) {
            $ancRankId = (int)$anc['current_rank_id'];
            $ancRate   = (float)$anc['commission_rate'];
            $ancId     = (int)$anc['ancestor_id'];

            if ($ancRankId <= $rankId) { continue; } // chỉ chia cho cấp cao hơn

            $overrideRate = $ancRate - $prevRate;
            if ($overrideRate <= 0) { continue; }

            $overrideAmt = floor($baseAmount * $overrideRate);
            mysqli_query($conn,
                "INSERT INTO commission_transaction
                     (payment_id, beneficiary_agent_id, commission_type, rank_at_time,
                      base_amount, rate_applied, commission_amount)
                 VALUES ($pid, $ancId, 'override', $ancRankId, $baseAmount, $overrideRate, $overrideAmt)"
            );
            $prevRate = $ancRate;
        }

        // CỘNG ĐIỂM cho người bán (tỷ lệ quy đổi: 1,000đ = 1 điểm — cập nhật theo quy chế)
        $points   = round($baseAmount / 1000, 4);
        $today    = date('Y-m-d');

        // Điểm cho chính người bán
        mysqli_query($conn,
            "INSERT INTO point_transaction
                 (agent_id, source_agent_id, payment_id, points, point_type, transaction_date, expiry_date)
             VALUES ($agentId, $agentId, $pid, $points, 'sales', '$today',
                     DATE_ADD('$today', INTERVAL 30 MONTH))"
        );

        // Chia điểm lên cấp trên (chỉ những người cấp CAO HƠN người bán)
        foreach ($ancestors as $anc) {
            $ancRankId = (int)$anc['current_rank_id'];
            $ancId     = (int)$anc['ancestor_id'];
            if ($ancRankId <= $rankId) { continue; }

            mysqli_query($conn,
                "INSERT INTO point_transaction
                     (agent_id, source_agent_id, payment_id, points, point_type, transaction_date, expiry_date)
                 VALUES ($ancId, $agentId, $pid, $points, 'team', '$today',
                         DATE_ADD('$today', INTERVAL 30 MONTH))"
            );
        }

        // Đánh dấu payment đã xử lý
        mysqli_query($conn,
            "UPDATE payment_record SET commission_processed = 1 WHERE payment_id = $pid"
        );

        mysqli_commit($conn);
        $countOk++;

        // KIỂM TRA THĂNG CẤP sau khi cộng điểm
        checkAndPromote($conn, $agentId);
        // Kiểm tra cho các cấp trên cũng được cộng điểm
        foreach ($ancestors as $anc) {
            if ((int)$anc['current_rank_id'] > $rankId) {
                checkAndPromote($conn, (int)$anc['ancestor_id']);
            }
        }

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $countErr++;
        $log .= "  LỖI payment #$pid: " . $e->getMessage() . "\n";
    }
}

$log .= "Hoàn tất: $countOk thành công, $countErr lỗi.\n";
echo $log;
file_put_contents(__DIR__ . '/cron_commission.log', $log, FILE_APPEND);


// -------------------------------------------------------
// HÀM KIỂM TRA VÀ THỰC HIỆN THĂNG CẤP
// -------------------------------------------------------
function checkAndPromote($conn, int $agentId): void
{
    // Lấy cấp hiện tại
    $aR  = mysqli_query($conn, "SELECT current_rank_id FROM agent WHERE agent_id = $agentId");
    $ag  = mysqli_fetch_assoc($aR);
    if (!$ag) { return; }
    $currentRankId = (int)$ag['current_rank_id'];

    // Lấy điều kiện thăng cấp hiện hành
    $cR = mysqli_query($conn,
        "SELECT * FROM rank_upgrade_condition
         WHERE  from_rank_id  = $currentRankId
           AND  effective_date <= CURDATE()
         ORDER  BY effective_date DESC
         LIMIT  1"
    );
    if (mysqli_num_rows($cR) === 0) { return; }
    $cond = mysqli_fetch_assoc($cR);

    // Tổng điểm còn hiệu lực
    $pR  = mysqli_query($conn,
        "SELECT COALESCE(SUM(points), 0) AS total
         FROM   point_transaction
         WHERE  agent_id = $agentId AND is_expired = 0 AND expiry_date > CURDATE()"
    );
    $pRow  = mysqli_fetch_assoc($pR);
    $total = (float)$pRow['total'];

    // Số nhân viên trực tiếp
    $dR    = mysqli_query($conn,
        "SELECT COUNT(*) AS cnt FROM agent_hierarchy
         WHERE  ancestor_id = $agentId AND depth = 1"
    );
    $dRow  = mysqli_fetch_assoc($dR);
    $direct= (int)$dRow['cnt'];

    if ($total  >= (float)$cond['min_points_required'] &&
        $direct >= (int)$cond['min_direct_agents']) {

        $toRank = (int)$cond['to_rank_id'];

        // Cập nhật cấp bậc
        mysqli_query($conn,
            "UPDATE agent SET current_rank_id = $toRank WHERE agent_id = $agentId"
        );

        // Tạo phiếu thưởng
        mysqli_query($conn,
            "INSERT INTO promotion_bonus
                 (agent_id, from_rank_id, to_rank_id, bonus_points, promotion_date)
             VALUES ($agentId, $currentRankId, $toRank, 1.00, CURDATE())"
        );

        echo "[" . date('H:i:s') . "] Agent #$agentId thăng từ rank $currentRankId lên $toRank\n";
    }
}
