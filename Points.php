<?php
session_start();
include_once 'define.php';
include_once PATH_MAIN_FUNCTION . '/conn-login-logout.php';
$conn = connection_to_database();

// -------------------------------------------------------
// XỬ LÝ NHẬN THƯỞNG THĂNG CẤP
// -------------------------------------------------------
if (isset($_POST['cashout_bonus'])) {
    $bonus_id   = (int)$_POST['bonus_id'];
    $immediate  = $_POST['cashout_type'] === 'immediate';
    $multiplier = $immediate ? 1.0 : (float)$_POST['multiplier'];

    $bR   = mysqli_query($conn,
        "SELECT * FROM promotion_bonus WHERE bonus_id = $bonus_id AND status = 'pending'"
    );
    $bonus = mysqli_fetch_assoc($bR);

    if ($bonus) {
        $amount  = floor($bonus['bonus_points'] * $bonus['point_value'] * $multiplier);
        $status  = $immediate ? 'immediate' : 'deferred';
        mysqli_query($conn,
            "UPDATE promotion_bonus
             SET    status = '$status', multiplier = $multiplier,
                    cash_out_date = CURDATE(), amount_paid = $amount
             WHERE  bonus_id = $bonus_id"
        );
    }
    header('location:' . $_SERVER['PHP_SELF'] . '?fmess=cashout');
    exit;
}

// -------------------------------------------------------
// DỮ LIỆU HIỂN THỊ: điểm theo nhân viên
// -------------------------------------------------------
$filter_agent = isset($_POST['filter_agent']) ? (int)$_POST['filter_agent'] : 0;

$sqlPoints = "SELECT
    a.agent_id, a.full_name, rc.rank_code,
    COALESCE(SUM(CASE WHEN pt.is_expired=0 AND pt.expiry_date > CURDATE() THEN pt.points ELSE 0 END), 0) AS active_points,
    COALESCE(SUM(pt.points), 0) AS total_points,
    ruc.min_points_required AS points_needed,
    ruc2.rank_code AS next_rank_code,
    (SELECT COUNT(*) FROM agent_hierarchy ah WHERE ah.ancestor_id = a.agent_id AND ah.depth = 1) AS direct_agents,
    ruc.min_direct_agents AS direct_needed
FROM   agent a
JOIN   rank_config rc  ON rc.rank_id  = a.current_rank_id
LEFT JOIN point_transaction pt ON pt.agent_id = a.agent_id
LEFT JOIN (
    SELECT ruc_inner.from_rank_id, ruc_inner.min_points_required, ruc_inner.to_rank_id, ruc_inner.min_direct_agents
    FROM   rank_upgrade_condition ruc_inner
    WHERE  ruc_inner.effective_date = (
        SELECT MAX(effective_date) FROM rank_upgrade_condition
        WHERE  from_rank_id = ruc_inner.from_rank_id AND effective_date <= CURDATE()
    )
) ruc ON ruc.from_rank_id = a.current_rank_id
LEFT JOIN rank_config ruc2 ON ruc2.rank_id = ruc.to_rank_id
WHERE  a.status = 'active'
" . ($filter_agent ? " AND a.agent_id = $filter_agent" : "") . "
GROUP BY a.agent_id
ORDER BY active_points DESC";

$pointsResult = mysqli_query($conn, $sqlPoints) or die(mysqli_error($conn));
$agentPoints  = [];
while ($r = mysqli_fetch_assoc($pointsResult)) { $agentPoints[] = $r; }

// -------------------------------------------------------
// PHIẾU THƯỞNG THĂNG CẤP chờ xử lý
// -------------------------------------------------------
$bonusResult = mysqli_query($conn,
    "SELECT pb.*, a.full_name,
            fr.rank_code AS from_code, tr.rank_code AS to_code
     FROM   promotion_bonus pb
     JOIN   agent       a  ON a.agent_id   = pb.agent_id
     JOIN   rank_config fr ON fr.rank_id   = pb.from_rank_id
     JOIN   rank_config tr ON tr.rank_id   = pb.to_rank_id
     WHERE  pb.status = 'pending'
     ORDER  BY pb.promotion_date DESC"
);
$pendingBonuses = [];
while ($r = mysqli_fetch_assoc($bonusResult)) { $pendingBonuses[] = $r; }

// Dropdown nhân viên
$agentOpts = '<option value="">-- Tất cả nhân viên --</option>';
$ar = mysqli_query($conn,
    "SELECT a.agent_id, a.full_name, rc.rank_code
     FROM   agent a JOIN rank_config rc ON rc.rank_id = a.current_rank_id
     WHERE  a.status = 'active' ORDER BY rc.rank_id, a.full_name"
);
while ($r = mysqli_fetch_assoc($ar)) {
    $sel = $filter_agent == $r['agent_id'] ? 'selected' : '';
    $agentOpts .= "<option value='{$r['agent_id']}' $sel>[{$r['rank_code']}] {$r['full_name']}</option>";
}
?>
<!DOCTYPE html>
<html>
<head><?php include_once 'html/headertitle.php'; ?></head>
<body>
<div class="container">
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header"><?php include_once 'html/topmenu-left.php'; ?></div>
            <div id="navbar" class="navbar-collapse collapse"><?php include_once 'html/topmenu-right.php'; ?></div>
        </div>
    </nav>
</div>

<div class="container">
    <h2>ĐIỂM TÍCH LŨY & THĂNG CẤP</h2>

    <!-- MESSAGES -->
    <div class="alert alert-success alert-dismissable alert-nonedisplay" id="success-alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <b><span id="text-success-message"></span></b>
    </div>

    <!-- PHIẾU THƯỞNG CHỜ XỬ LÝ -->
    <?php if (!empty($pendingBonuses)): ?>
    <div class="panel panel-warning">
        <div class="panel-heading">
            <h3 class="panel-title">
                <span class="glyphicon glyphicon-gift"></span>
                Phiếu thưởng thăng cấp chờ xử lý
                <span class="badge"><?= count($pendingBonuses) ?></span>
            </h3>
        </div>
        <div class="panel-body" style="padding:0;">
            <table class="table table-condensed table-hover" style="margin-bottom:0;">
                <thead>
                    <tr class="active">
                        <th>Nhân viên</th>
                        <th>Thăng cấp</th>
                        <th>Ngày thăng</th>
                        <th class="text-right">Điểm thưởng</th>
                        <th class="text-right">Nhận ngay</th>
                        <th width="200px">Xử lý</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pendingBonuses as $b): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($b['full_name']) ?></strong></td>
                    <td>
                        <span class="label label-default"><?= $b['from_code'] ?></span>
                        <span class="glyphicon glyphicon-arrow-right"></span>
                        <span class="label label-success"><?= $b['to_code'] ?></span>
                    </td>
                    <td><?= $b['promotion_date'] ?></td>
                    <td class="text-right"><?= $b['bonus_points'] ?> điểm</td>
                    <td class="text-right">
                        <strong><?= number_format($b['bonus_points'] * $b['point_value'], 0, ',', '.') ?>đ</strong>
                    </td>
                    <td>
                        <form action="" method="POST" class="form-inline">
                            <input type="hidden" name="bonus_id" value="<?= $b['bonus_id'] ?>"/>
                            <select name="cashout_type" class="input-sm" style="width:110px;">
                                <option value="immediate">Nhận ngay</option>
                                <option value="deferred">Để lại quỹ</option>
                            </select>
                            <input type="number" name="multiplier" value="1" min="1" max="4" step="0.1"
                                   class="input-sm" style="width:50px;" title="Hệ số nhân (nếu để quỹ)"/>
                            <button type="submit" name="cashout_bonus" class="btn btn-xs btn-success">
                                Xác nhận
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- BẢNG TIẾN ĐỘ THĂNG CẤP -->
    <form action="" method="POST" id="filter-form">
        <div style="margin-bottom:10px;">
            <select name="filter_agent" class="input-sm" style="width:240px;"
                    onchange="this.form.submit();">
                <?= $agentOpts ?>
            </select>
        </div>
    </form>

    <table class="table table-hover table-bordered">
        <thead>
            <tr class="active">
                <th width="40px">#</th>
                <th width="180px">Nhân viên</th>
                <th width="70px">Cấp</th>
                <th width="120px">Điểm hiệu lực</th>
                <th width="200px">Tiến độ thăng cấp</th>
                <th width="80px">NV trực tiếp</th>
                <th width="100px">Cấp tiếp theo</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($agentPoints)): ?>
            <tr><td colspan="7" class="text-center text-muted">Không có dữ liệu</td></tr>
        <?php else: foreach ($agentPoints as $i => $ag):
            $pctPoints = 0;
            $progressBar = '<span class="text-muted"><small>Đã ở cấp cao nhất</small></span>';
            if ($ag['points_needed'] > 0) {
                $pctPoints = min(100, round($ag['active_points'] / $ag['points_needed'] * 100));
                $progressBar = '
                    <div class="progress" style="margin-bottom:2px;height:14px;">
                        <div class="progress-bar progress-bar-info" style="width:' . $pctPoints . '%;line-height:14px;font-size:11px;">' . $pctPoints . '%</div>
                    </div>
                    <small class="text-muted">' . number_format($ag['active_points'], 2) . ' / ' . number_format($ag['points_needed'], 0) . ' điểm</small>';
            }
            $directBar = $ag['direct_needed'] > 0
                ? '<small>' . $ag['direct_agents'] . ' / ' . $ag['direct_needed'] . ' NV</small>'
                : '<small>' . $ag['direct_agents'] . ' NV</small>';
        ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><strong><?= htmlspecialchars($ag['full_name']) ?></strong></td>
            <td class="text-center"><span class="label label-primary"><?= $ag['rank_code'] ?></span></td>
            <td class="text-right"><strong><?= number_format($ag['active_points'], 2) ?></strong></td>
            <td><?= $progressBar ?></td>
            <td class="text-center"><?= $directBar ?></td>
            <td class="text-center">
                <?= $ag['next_rank_code']
                    ? '<span class="label label-success">' . $ag['next_rank_code'] . '</span>'
                    : '<span class="text-muted">—</span>' ?>
            </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php include_once 'html/emb_js.php'; ?>
<script>
$(window).load(function () {
    var r = new RegExp('[\\?&]fmess=([^&#]*)').exec(window.location.href);
    if (r && r[1] === 'cashout') {
        $('#text-success-message').text('Đã xử lý phiếu thưởng thăng cấp thành công!');
        $('#success-alert').fadeTo('slow', 1).fadeOut(6000, function(){ $(this).alert('close'); });
    }
});
</script>
</body>
</html>
