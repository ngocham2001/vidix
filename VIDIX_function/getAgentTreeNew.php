<?php
/**
 * getAgentTreeNew.php
 * Trả về JSON cho popup cây hợp đồng (TT_Hopdong_TCB):
 *   - sponsor_hd : số HĐ/Iv tùy chọn B của người tuyển dụng trực tiếp
 *   - self       : thông tin bản thân (HĐ tùy chọn B, điểm, số HĐ phụ trách, rank)
 *   - upgrade    : điều kiện & tiến độ tăng cấp
 *   - children_by_rank : cấp dưới trực tiếp (depth=1) nhóm theo cấp bậc,
 *                        mỗi người kèm thiếu bao nhiêu HĐ/điểm để lên cấp trên
 */
session_start();
include_once '../define.php';
include_once PATH_MAIN_FUNCTION . '/conn-login-logout.php';
$conn = connection_to_database();

header('Content-Type: application/json; charset=utf-8');

// -------------------------------------------------------
// Nhận agent_id (nhân viên được tra cứu)
// -------------------------------------------------------
$agent_id = isset($_POST['agent_id']) ? (int)$_POST['agent_id'] : 0;
if ($agent_id <= 0) {
    echo json_encode(['error' => 'Thiếu agent_id']);
    exit;
}

// ===================================================================
// HELPER: Lấy số HĐ tùy chọn B (SoHD hoặc Iv) của 1 agent
// ===================================================================
function getHdB($conn, $agentId) {
    if (!$agentId) return null;
    $r = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COALESCE(hd.SoHD, hd.Iv) AS so_hd_b,
                hd.TrangThaiHDcho, hd.TrangThaiHD,
                hd.NgayNopTien1, hd.SoDVTC, hd.SonamHD,
                hd.LoaiHD,
                DATEDIFF(NOW(), hd.NgayNopTien1) AS so_ngay
         FROM   tbl_hopdong_ttchung hd
         WHERE  hd.agent_id_banhang = $agentId
           AND  hd.HDTuychonB = 1
         LIMIT 1"
    ));
    return $r;
}

// ===================================================================
// HELPER: Điểm thưởng tích lũy của 1 agent
// ===================================================================
function getTotalPoints($conn, $agentId) {
    $r = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COALESCE(SUM(pt.points), 0) AS total_points
         FROM   point_transaction pt
         WHERE  pt.agent_id = $agentId"
    ));
    return (float)($r['total_points'] ?? 0);
}

// ===================================================================
// HELPER: Tổng số HĐ đang hoạt động mà agent chịu trách nhiệm
// (bao gồm cả HĐ của toàn bộ cấp dưới trong mạng lưới)
// ===================================================================
function getTotalHdManaged($conn, $agentId) {
    // HĐ trực tiếp của chính họ
    $r1 = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) AS cnt
         FROM   tbl_hopdong_ttchung hd
         WHERE  hd.agent_id_banhang = $agentId
           AND  hd.TrangThaiHD = 'Dang_hoat_dong'"
    ));
    // HĐ của toàn bộ cấp dưới
    $r2 = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) AS cnt
         FROM   tbl_hopdong_ttchung hd
         JOIN   agent_hierarchy ah ON ah.descendant_id = hd.agent_id_banhang
         WHERE  ah.ancestor_id  = $agentId
           AND  ah.descendant_id != $agentId
           AND  hd.TrangThaiHD = 'Dang_hoat_dong'"
    ));
    return (int)$r1['cnt'] + (int)$r2['cnt'];
}

// ===================================================================
// HELPER: HĐ trực tiếp của agent (chính họ tự bán, không qua cấp dưới)
// ===================================================================
function getDirectHd($conn, $agentId) {
    $r = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) AS cnt
         FROM   tbl_hopdong_ttchung hd
         WHERE  hd.agent_id_banhang = $agentId
           AND  hd.TrangThaiHD = 'Dang_hoat_dong'"
    ));
    return (int)$r['cnt'];
}

// ===================================================================
// HELPER: Điều kiện tăng cấp tiếp theo (từ rank_upgrade_condition)
// ===================================================================
function getUpgradeCondition($conn, $rankId) {
    // rank_upgrade_condition: điều kiện để từ rank hiện tại lên rank trên
    $r = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT ruc.*, rc_next.rank_code AS next_rank_code, rc_next.rank_name AS next_rank_name
         FROM   rank_upgrade_condition ruc
         JOIN   rank_config rc_next ON rc_next.rank_id = ruc.to_rank_id
         WHERE  ruc.from_rank_id = $rankId
         LIMIT 1"
    ));
    return $r;
}

// ===================================================================
// 1. Thông tin bản thân
// ===================================================================
$sqlSelf = "
    SELECT a.agent_id, a.agent_code, a.full_name, a.phone, a.email,
           a.join_date, a.status, a.sponsor_agent_id,
           rc.rank_id, rc.rank_code, rc.rank_name,
           sp.agent_id   AS sponsor_id,
           sp.full_name  AS sponsor_name,
           sp.agent_code AS sponsor_code,
           sp_rc.rank_code AS sponsor_rank_code
    FROM   agent a
    JOIN   rank_config rc ON rc.rank_id = a.current_rank_id
    LEFT JOIN agent sp        ON sp.agent_id  = a.sponsor_agent_id
    LEFT JOIN rank_config sp_rc ON sp_rc.rank_id = sp.current_rank_id
    WHERE  a.agent_id = $agent_id
";
$selfRow = mysqli_fetch_assoc(mysqli_query($conn, $sqlSelf));
if (!$selfRow) {
    echo json_encode(['error' => 'Không tìm thấy nhân viên']);
    exit;
}

// ===================================================================
// 2. HĐ tùy chọn B của bản thân
// ===================================================================
$selfHdB = getHdB($conn, $agent_id);

// ===================================================================
// 3. HĐ tùy chọn B của người tuyển dụng (sponsor)
// ===================================================================
$sponsorHdB = null;
if ($selfRow['sponsor_agent_id']) {
    $sponsorHdB = getHdB($conn, $selfRow['sponsor_agent_id']);
}

// ===================================================================
// 4. Thống kê bản thân
// ===================================================================
$selfPoints      = getTotalPoints($conn, $agent_id);
$selfTotalHd     = getTotalHdManaged($conn, $agent_id);
$selfDirectHd    = getDirectHd($conn, $agent_id);

// ===================================================================
// 5. Điều kiện tăng cấp + tiến độ
// ===================================================================
$upgradeCond = getUpgradeCondition($conn, $selfRow['rank_id']);
$upgradeInfo = null;
if ($upgradeCond) {
    // Số HĐ cần / điểm cần
    $needHd     = (int)($upgradeCond['min_contracts'] ?? 0);
    $needPoints = (float)($upgradeCond['min_points']  ?? 0);
    // Số thành viên cấp dưới trực tiếp cần
    $needDirect = (int)($upgradeCond['min_direct_agents'] ?? 0);

    // Đếm số cấp dưới trực tiếp của bản thân
    $rDirect = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) AS cnt FROM agent_hierarchy ah
         WHERE  ah.ancestor_id   = $agent_id
           AND  ah.depth         = 1
           AND  ah.descendant_id != $agent_id"
    ));
    $hasDirect = (int)$rDirect['cnt'];

    $upgradeInfo = [
        'to_rank_code'  => $upgradeCond['next_rank_code'],
        'to_rank_name'  => $upgradeCond['next_rank_name'],
        'need_hd'       => $needHd,
        'need_points'   => $needPoints,
        'need_direct'   => $needDirect,
        'have_hd'       => $selfTotalHd,
        'have_points'   => $selfPoints,
        'have_direct'   => $hasDirect,
        'lack_hd'       => max(0, $needHd     - $selfTotalHd),
        'lack_points'   => max(0, $needPoints  - $selfPoints),
        'lack_direct'   => max(0, $needDirect  - $hasDirect),
    ];
}

// ===================================================================
// 6. Cấp dưới trực tiếp (depth = 1), nhóm theo rank
// ===================================================================
$sqlChildren = "
    SELECT
        a.agent_id,
        a.agent_code,
        a.full_name,
        a.phone,
        a.join_date,
        a.status,
        rc.rank_id,
        rc.rank_code,
        rc.rank_name,
        -- Số HĐ/Iv tùy chọn B
        COALESCE(hdb.SoHD, hdb.Iv)              AS so_hd_b,
        hdb.TrangThaiHDcho                       AS hd_cho,
        hdb.TrangThaiHD                          AS hd_tt,
        -- Tổng HĐ phụ trách (toàn cây con)
        (SELECT COUNT(*)
         FROM   tbl_hopdong_ttchung hd2
         JOIN   agent_hierarchy ah2 ON ah2.descendant_id = hd2.agent_id_banhang
         WHERE  ah2.ancestor_id = a.agent_id
           AND  hd2.TrangThaiHD = 'Dang_hoat_dong') AS tong_hd_phu_trach,
        -- HĐ trực tiếp (chính người đó)
        (SELECT COUNT(*)
         FROM   tbl_hopdong_ttchung hd3
         WHERE  hd3.agent_id_banhang = a.agent_id
           AND  hd3.TrangThaiHD = 'Dang_hoat_dong') AS hd_truc_tiep,
        -- Số cấp dưới trực tiếp của họ
        (SELECT COUNT(*)
         FROM   agent_hierarchy ah4
         WHERE  ah4.ancestor_id  = a.agent_id
           AND  ah4.depth        = 1
           AND  ah4.descendant_id != a.agent_id) AS so_cap_duoi_tt,
        -- Điểm thưởng tích lũy
        COALESCE((SELECT SUM(pt.points) FROM point_transaction pt
                  WHERE pt.agent_id = a.agent_id), 0) AS tong_diem
    FROM   agent_hierarchy ah
    JOIN   agent a        ON a.agent_id  = ah.descendant_id
    JOIN   rank_config rc ON rc.rank_id  = a.current_rank_id
    LEFT JOIN tbl_hopdong_ttchung hdb
           ON  hdb.agent_id_banhang = a.agent_id
           AND hdb.HDTuychonB = 1
    WHERE  ah.ancestor_id   = $agent_id
      AND  ah.descendant_id != $agent_id
      AND  ah.depth          = 1
    ORDER BY rc.rank_id DESC, a.join_date ASC
";
$childRes  = mysqli_query($conn, $sqlChildren);
$childRows = [];
while ($cr = mysqli_fetch_assoc($childRes)) {
    $childRows[] = $cr;
}

// Với mỗi child, tính điều kiện lên cấp trên và mức thiếu
$childrenByRank = [];
foreach ($childRows as $c) {
    $cRankId  = (int)$c['rank_id'];
    $cRankCode = $c['rank_code'];

    // Điều kiện tăng cấp của child
    $cUpgrade = getUpgradeCondition($conn, $cRankId);
    $c['upgrade'] = null;
    if ($cUpgrade) {
        $needHd     = (int)($cUpgrade['min_contracts'] ?? 0);
        $needPoints = (float)($cUpgrade['min_points']  ?? 0);
        $haveHd     = (int)$c['tong_hd_phu_trach'];
        $havePoints = (float)$c['tong_diem'];
        $c['upgrade'] = [
            'to_rank_code' => $cUpgrade['next_rank_code'],
            'need_hd'      => $needHd,
            'need_points'  => $needPoints,
            'have_hd'      => $haveHd,
            'have_points'  => $havePoints,
            'lack_hd'      => max(0, $needHd     - $haveHd),
            'lack_points'  => max(0, $needPoints  - $havePoints),
        ];
    }

    if (!isset($childrenByRank[$cRankCode])) {
        $childrenByRank[$cRankCode] = [
            'rank_code' => $cRankCode,
            'rank_name' => $c['rank_name'],
            'rank_id'   => $cRankId,
            'members'   => [],
        ];
    }
    $childrenByRank[$cRankCode]['members'][] = $c;
}

// Sắp xếp nhóm theo rank_id giảm dần (cấp cao nhất trước)
usort($childrenByRank, function($a, $b) {
    return $b['rank_id'] - $a['rank_id'];
});

// ===================================================================
// OUTPUT
// ===================================================================
echo json_encode([
    'self'             => $selfRow,
    'self_hd_b'        => $selfHdB,
    'self_points'      => $selfPoints,
    'self_total_hd'    => $selfTotalHd,
    'self_direct_hd'   => $selfDirectHd,
    'upgrade'          => $upgradeInfo,
    'sponsor_hd_b'     => $sponsorHdB,
    'children_by_rank' => array_values($childrenByRank),
], JSON_UNESCAPED_UNICODE);
