<?php
session_start();
include_once 'define.php';
include_once PATH_MAIN_FUNCTION . '/conn-login-logout.php';
$conn = connection_to_database();

// -------------------------------------------------------
// XỬ LÝ DUYỆT / CHI HOA HỒNG
// -------------------------------------------------------
if (isset($_POST['approve_commission'])) {
    $txn_id = (int)$_POST['txn_id'];
    mysqli_query($conn,
        "UPDATE commission_transaction SET status = 'approved'
         WHERE  txn_id = $txn_id AND status = 'pending'"
    );
    header('location:' . $_SERVER['PHP_SELF'] . '?fmess=approved');
    exit;
}

if (isset($_POST['pay_commission'])) {
    $txn_id = (int)$_POST['txn_id'];
    mysqli_query($conn,
        "UPDATE commission_transaction
         SET    status = 'paid', paid_at = NOW()
         WHERE  txn_id = $txn_id AND status = 'approved'"
    );
    header('location:' . $_SERVER['PHP_SELF'] . '?fmess=paid');
    exit;
}

// Duyệt hàng loạt
if (isset($_POST['approve_batch']) && !empty($_POST['txn_ids'])) {
    $ids = implode(',', array_map('intval', $_POST['txn_ids']));
    mysqli_query($conn,
        "UPDATE commission_transaction SET status = 'approved'
         WHERE  txn_id IN ($ids) AND status = 'pending'"
    );
    header('location:' . $_SERVER['PHP_SELF'] . '?fmess=approved_batch');
    exit;
}

// -------------------------------------------------------
// LỌC & PHÂN TRANG
// -------------------------------------------------------
$where = "WHERE 1";

if (isset($_POST['search']) && !empty($_POST['textcond'])) {
    $tc = mysqli_real_escape_string($conn, $_POST['textcond']);
    $where .= " AND (a.full_name LIKE '%$tc%' OR rc.rank_code LIKE '%$tc%')";
}
if (isset($_POST['filter_status']) && $_POST['filter_status'] !== '') {
    $fs = mysqli_real_escape_string($conn, $_POST['filter_status']);
    $where .= " AND ct.status = '$fs'";
}
if (isset($_POST['filter_type']) && $_POST['filter_type'] !== '') {
    $ft = mysqli_real_escape_string($conn, $_POST['filter_type']);
    $where .= " AND ct.commission_type = '$ft'";
}
if (isset($_POST['filter_agent']) && $_POST['filter_agent'] !== '') {
    $fa = (int)$_POST['filter_agent'];
    $where .= " AND ct.beneficiary_agent_id = $fa";
}

// Tổng hợp số liệu
$summaryR = mysqli_query($conn,
    "SELECT
        SUM(CASE WHEN ct.status='pending'  THEN ct.commission_amount ELSE 0 END) AS total_pending,
        SUM(CASE WHEN ct.status='approved' THEN ct.commission_amount ELSE 0 END) AS total_approved,
        SUM(CASE WHEN ct.status='paid'     THEN ct.commission_amount ELSE 0 END) AS total_paid,
        COUNT(*) AS total_txn
     FROM commission_transaction ct"
);
$summary = mysqli_fetch_assoc($summaryR);

// Danh sách giao dịch
$sql = "SELECT ct.*,
               a.full_name AS beneficiary_name,
               rc.rank_code,
               pr.payment_date,
               c.customer_name,
               c.contract_id,
               ca.full_name AS seller_name
        FROM   commission_transaction ct
        JOIN   agent a          ON a.agent_id    = ct.beneficiary_agent_id
        JOIN   rank_config rc   ON rc.rank_id    = ct.rank_at_time
        JOIN   payment_record pr ON pr.payment_id = ct.payment_id
        JOIN   contract c       ON c.contract_id  = pr.contract_id
        JOIN   agent ca         ON ca.agent_id    = c.agent_id
        $where
        ORDER  BY ct.calculated_at DESC
        LIMIT  200";
$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
$rows   = [];
while ($r = mysqli_fetch_assoc($result)) { $rows[] = $r; }

// Dropdown nhân viên
$agentOpts = '<option value="">-- Tất cả nhân viên --</option>';
$ar = mysqli_query($conn,
    "SELECT a.agent_id, a.full_name, rc.rank_code
     FROM agent a JOIN rank_config rc ON rc.rank_id = a.current_rank_id
     ORDER BY rc.rank_id, a.full_name"
);
while ($r = mysqli_fetch_assoc($ar)) {
    $sel = (isset($_POST['filter_agent']) && $_POST['filter_agent'] == $r['agent_id']) ? 'selected' : '';
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
    <h2>QUẢN LÝ HOA HỒNG</h2>

    <!-- THỐNG KÊ NHANH -->
    <div class="row" style="margin-bottom:15px;">
        <div class="col-sm-3">
            <div class="panel panel-default text-center">
                <div class="panel-body">
                    <h4 class="text-muted" style="margin:0;">Chờ duyệt</h4>
                    <h3 class="text-warning" style="margin:5px 0;"><?= number_format($summary['total_pending'], 0, ',', '.') ?>đ</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="panel panel-default text-center">
                <div class="panel-body">
                    <h4 class="text-muted" style="margin:0;">Đã duyệt</h4>
                    <h3 class="text-primary" style="margin:5px 0;"><?= number_format($summary['total_approved'], 0, ',', '.') ?>đ</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="panel panel-default text-center">
                <div class="panel-body">
                    <h4 class="text-muted" style="margin:0;">Đã chi</h4>
                    <h3 class="text-success" style="margin:5px 0;"><?= number_format($summary['total_paid'], 0, ',', '.') ?>đ</h3>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="panel panel-default text-center">
                <div class="panel-body">
                    <h4 class="text-muted" style="margin:0;">Tổng giao dịch</h4>
                    <h3 style="margin:5px 0;"><?= number_format($summary['total_txn']) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- MESSAGES -->
    <div class="alert alert-success alert-dismissable alert-nonedisplay" id="success-alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <b><span id="text-success-message"></span></b>
    </div>
    <div class="alert alert-warning alert-dismissable alert-nonedisplay" id="warning-alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <b><span id="text-warning-message"></span></b>
    </div>

    <!-- BỘ LỌC -->
    <form action="" method="POST" id="filter-form">
        <div class="row" style="margin-bottom:10px;">
            <div class="col-xs-12">
                <select name="filter_status" class="input-sm" style="width:140px;"
                        onchange="this.form.submit();">
                    <option value="">-- Tất cả TT --</option>
                    <option value="pending"  <?= isset($_POST['filter_status'])&&$_POST['filter_status']==='pending'  ?'selected':'' ?>>Chờ duyệt</option>
                    <option value="approved" <?= isset($_POST['filter_status'])&&$_POST['filter_status']==='approved' ?'selected':'' ?>>Đã duyệt</option>
                    <option value="paid"     <?= isset($_POST['filter_status'])&&$_POST['filter_status']==='paid'     ?'selected':'' ?>>Đã chi</option>
                </select>
                &nbsp;
                <select name="filter_type" class="input-sm" style="width:140px;"
                        onchange="this.form.submit();">
                    <option value="">-- Loại HH --</option>
                    <option value="direct"   <?= isset($_POST['filter_type'])&&$_POST['filter_type']==='direct'   ?'selected':'' ?>>Trực tiếp</option>
                    <option value="override" <?= isset($_POST['filter_type'])&&$_POST['filter_type']==='override' ?'selected':'' ?>>Override</option>
                </select>
                &nbsp;
                <select name="filter_agent" class="input-sm" style="width:220px;"
                        onchange="this.form.submit();">
                    <?= $agentOpts ?>
                </select>
                <span class="pull-right">
                    <input type="text" name="textcond"
                           value="<?= isset($_POST['textcond']) ? htmlspecialchars($_POST['textcond']) : '' ?>"
                           placeholder="Tìm tên NV, cấp..." class="input-sm" style="width:190px;"/>
                    <button class="btn btn-default btn-sm" type="submit" name="search">
                        <span class="glyphicon glyphicon-search"></span>
                    </button>
                </span>
            </div>
        </div>

        <!-- BẢNG HOA HỒNG -->
        <table class="table table-hover table-bordered table-condensed">
            <thead>
                <tr class="active">
                    <th width="30px"><input type="checkbox" id="check-all"/></th>
                    <th width="150px">Ngày tính HH</th>
                    <th width="170px">Người nhận</th>
                    <th width="140px">Khách hàng / HĐ</th>
                    <th width="80px">Loại HH</th>
                    <th width="70px">Tỷ lệ</th>
                    <th width="130px">Số tiền HH</th>
                    <th width="100px">Trạng thái</th>
                    <th width="120px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="9" class="text-center text-muted">Không có dữ liệu</td></tr>
            <?php else: foreach ($rows as $r):
                $typeBadge = $r['commission_type'] === 'direct'
                    ? '<span class="label label-primary">Trực tiếp</span>'
                    : '<span class="label label-info">Override</span>';
                $stBadge = match($r['status']) {
                    'paid'     => '<span class="label label-success">Đã chi</span>',
                    'approved' => '<span class="label label-warning">Đã duyệt</span>',
                    default    => '<span class="label label-default">Chờ duyệt</span>',
                };
            ?>
            <tr>
                <td>
                    <?php if ($r['status'] === 'pending'): ?>
                    <input type="checkbox" name="txn_ids[]" value="<?= $r['txn_id'] ?>"/>
                    <?php endif; ?>
                </td>
                <td><small><?= $r['calculated_at'] ?></small></td>
                <td>[<?= $r['rank_code'] ?>] <?= htmlspecialchars($r['beneficiary_name']) ?></td>
                <td>
                    <a href="Contract_detail.php?id=<?= $r['contract_id'] ?>">
                        #<?= $r['contract_id'] ?></a><br/>
                    <small class="text-muted"><?= htmlspecialchars($r['customer_name']) ?></small>
                </td>
                <td class="text-center"><?= $typeBadge ?></td>
                <td class="text-right"><?= number_format($r['rate_applied'] * 100, 1) ?>%</td>
                <td class="text-right"><strong><?= number_format($r['commission_amount'], 0, ',', '.') ?>đ</strong></td>
                <td class="text-center"><?= $stBadge ?></td>
                <td>
                    <?php if ($r['status'] === 'pending'): ?>
                    <form action="" method="POST" style="display:inline;">
                        <input type="hidden" name="txn_id" value="<?= $r['txn_id'] ?>"/>
                        <button type="submit" name="approve_commission" class="btn btn-xs btn-warning">Duyệt</button>
                    </form>
                    <?php elseif ($r['status'] === 'approved'): ?>
                    <form action="" method="POST" style="display:inline;">
                        <input type="hidden" name="txn_id" value="<?= $r['txn_id'] ?>"/>
                        <button type="submit" name="pay_commission" class="btn btn-xs btn-success">Chi tiền</button>
                    </form>
                    <?php else: ?>
                    <small class="text-muted"><?= $r['paid_at'] ?></small>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>

        <!-- DUYỆT HÀNG LOẠT -->
        <div>
            <button type="submit" name="approve_batch" class="btn btn-warning btn-sm"
                    onclick="return confirm('Duyệt tất cả giao dịch đã chọn?');">
                <span class="glyphicon glyphicon-ok"></span> Duyệt tất cả đã chọn
            </button>
        </div>
    </form>
</div>

<?php include_once 'html/emb_js.php'; ?>
<script>
$(document).ready(function () {
    $.urlParam = function (name) {
        var r = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
        return r && r.length > 1 ? decodeURIComponent(r[1]) : null;
    };
    $(window).load(function () {
        var m = $.urlParam('fmess');
        if (m === 'approved')       showAlert('warning', 'Đã duyệt giao dịch hoa hồng.');
        if (m === 'paid')           showAlert('success', 'Đã chi hoa hồng thành công!');
        if (m === 'approved_batch') showAlert('warning', 'Đã duyệt hàng loạt thành công.');
    });
    $('#check-all').change(function () {
        $('input[name="txn_ids[]"]').prop('checked', this.checked);
    });
});
function showAlert(type, msg) {
    $('#text-' + type + '-message').text(msg);
    $('#' + type + '-alert').fadeTo('slow', 1).fadeOut(6000, function(){ $(this).alert('close'); });
}
</script>
</body>
</html>
