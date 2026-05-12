<?php
session_start();
include_once 'define.php';
include_once PATH_MAIN_FUNCTION . '/conn-login-logout.php';
include_once 'PHP/Payment_PHP.php'; // xử lý POST thanh toán
$conn = connection_to_database();

$contract_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$contract_id) { header('location:Contract.php'); exit; }

// Lấy thông tin hợp đồng
$cResult = mysqli_query($conn,
    "SELECT c.*, a.full_name AS agent_name, rc.rank_code, rc.commission_rate
     FROM   contract c
     JOIN   agent a        ON a.agent_id  = c.agent_id
     JOIN   rank_config rc ON rc.rank_id  = a.current_rank_id
     WHERE  c.contract_id = $contract_id"
);
$contract = mysqli_fetch_assoc($cResult);
if (!$contract) { header('location:Contract.php'); exit; }

// Lấy lịch sử thanh toán
$pResult = mysqli_query($conn,
    "SELECT pr.*,
            CASE WHEN commission_unlock_date <= CURDATE() AND commission_processed = 0
                 THEN 'unlocked'
                 WHEN commission_processed = 1 THEN 'processed'
                 ELSE 'locked'
            END AS commission_status
     FROM   payment_record pr
     WHERE  pr.contract_id = $contract_id
     ORDER  BY pr.payment_date ASC"
);
$payments = [];
while ($r = mysqli_fetch_assoc($pResult)) { $payments[] = $r; }

// Tổng đã nộp
$totalPaid = array_sum(array_column($payments, 'amount_paid'));
$annualValue = (float)$contract['annual_value'];
$pct = $annualValue > 0 ? min(100, round($totalPaid / $annualValue * 100)) : 0;

// Lấy hoa hồng liên quan đến hợp đồng này
$commResult = mysqli_query($conn,
    "SELECT ct.*, a.full_name AS beneficiary_name, rc.rank_code, pr.payment_date
     FROM   commission_transaction ct
     JOIN   agent a        ON a.agent_id  = ct.beneficiary_agent_id
     JOIN   rank_config rc ON rc.rank_id  = ct.rank_at_time
     JOIN   payment_record pr ON pr.payment_id = ct.payment_id
     WHERE  pr.contract_id = $contract_id
     ORDER  BY ct.calculated_at DESC"
);
$commissions = [];
while ($r = mysqli_fetch_assoc($commResult)) { $commissions[] = $r; }

$statusMap = [
    'pending'   => ['label' => 'Chờ kích hoạt', 'class' => 'label-default'],
    'active'    => ['label' => 'Đang hoạt động','class' => 'label-success'],
    'cancelled' => ['label' => 'Đã hủy',        'class' => 'label-danger'],
    'expired'   => ['label' => 'Hết hiệu lực',  'class' => 'label-warning'],
];
$s = $statusMap[$contract['status']] ?? ['label' => $contract['status'], 'class' => 'label-default'];
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
    <ol class="breadcrumb">
        <li><a href="Contract.php">Hợp đồng</a></li>
        <li class="active">Chi tiết #<?= $contract_id ?></li>
    </ol>

    <!-- MESSAGES -->
    <div class="alert alert-success alert-dismissable alert-nonedisplay" id="success-alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <b><span id="text-success-message"></span></b>
    </div>
    <div class="alert alert-danger alert-dismissable alert-nonedisplay" id="danger-alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <b><span id="text-danger-message"></span></b>
    </div>

    <div class="row">
        <!-- CỘT TRÁI: Thông tin hợp đồng -->
        <div class="col-md-5">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <strong>Hợp đồng #<?= $contract_id ?></strong>
                        <span class="label <?= $s['class'] ?> pull-right"><?= $s['label'] ?></span>
                    </h3>
                </div>
                <div class="panel-body">
                    <table class="table table-condensed" style="margin-bottom:0;">
                        <tr><td width="140px"><strong>Khách hàng</strong></td>
                            <td><?= htmlspecialchars($contract['customer_name']) ?></td></tr>
                        <tr><td><strong>CCCD/CMND</strong></td>
                            <td><?= htmlspecialchars($contract['customer_id_number']) ?></td></tr>
                        <tr><td><strong>Điện thoại</strong></td>
                            <td><?= htmlspecialchars($contract['customer_phone'] ?? '—') ?></td></tr>
                        <tr><td><strong>Mã sản phẩm</strong></td>
                            <td><?= htmlspecialchars($contract['product_code']) ?></td></tr>
                        <tr><td><strong>Trị giá năm đầu</strong></td>
                            <td><strong class="text-primary"><?= number_format($annualValue, 0, ',', '.') ?>đ</strong></td></tr>
                        <tr><td><strong>Hình thức nộp</strong></td>
                            <td><?= $contract['payment_type'] === 'annual' ? 'Theo năm' : 'Theo tháng' ?></td></tr>
                        <tr><td><strong>Ngày hiệu lực</strong></td>
                            <td><?= $contract['start_date'] ?></td></tr>
                        <tr><td><strong>Hạn hủy HĐ</strong></td>
                            <td><?= $contract['cancellation_deadline'] ?></td></tr>
                        <tr><td><strong>Nhân viên bán</strong></td>
                            <td>[<?= $contract['rank_code'] ?>] <?= htmlspecialchars($contract['agent_name']) ?></td></tr>
                        <tr><td><strong>Hoa hồng NV</strong></td>
                            <td><?= number_format($contract['commission_rate'] * 100, 1) ?>%</td></tr>
                    </table>
                </div>
            </div>

            <!-- Tiến độ đóng tiền năm đầu -->
            <div class="panel panel-default">
                <div class="panel-heading"><h3 class="panel-title">Tiến độ đóng tiền năm đầu</h3></div>
                <div class="panel-body">
                    <div class="progress" style="height:20px;">
                        <div class="progress-bar progress-bar-success progress-bar-striped"
                             style="width:<?= $pct ?>%; line-height:20px; font-size:13px;">
                            <?= $pct ?>%
                        </div>
                    </div>
                    <table class="table table-condensed" style="margin-bottom:0;">
                        <tr><td>Đã nộp</td>
                            <td class="text-right"><strong><?= number_format($totalPaid, 0, ',', '.') ?>đ</strong></td></tr>
                        <tr><td>Còn thiếu</td>
                            <td class="text-right text-danger">
                                <strong><?= number_format(max(0, $annualValue - $totalPaid), 0, ',', '.') ?>đ</strong>
                            </td></tr>
                        <tr><td>Trạng thái hoa hồng năm đầu</td>
                            <td class="text-right">
                                <?= $contract['first_year_commission_closed']
                                    ? '<span class="label label-success">Đã hoàn tất</span>'
                                    : '<span class="label label-warning">Đang theo dõi</span>' ?>
                            </td></tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: Form nhập tiền + lịch sử -->
        <div class="col-md-7">
            <!-- FORM NHẬP TIỀN -->
            <?php if ($contract['status'] !== 'cancelled' && !$contract['first_year_commission_closed']): ?>
            <div class="panel panel-primary">
                <div class="panel-heading"><h3 class="panel-title">Ghi nhận lần nộp tiền mới</h3></div>
                <div class="panel-body">
                    <form action="" method="POST" id="payment-form">
                        <input type="hidden" name="contract_id" value="<?= $contract_id ?>"/>
                        <div class="form-inline">
                            <div class="form-group">
                                <label>Số tiền nộp (đ) &nbsp;</label>
                                <input type="number" name="amount_paid" id="amount_paid"
                                       class="form-control input-sm" style="width:160px;"
                                       min="1" placeholder="Số tiền..." required/>
                            </div>
                            &nbsp;&nbsp;
                            <div class="form-group">
                                <label>Ngày nộp &nbsp;</label>
                                <input type="text" name="payment_date" id="payment_date"
                                       class="form-control input-sm" style="width:120px;"
                                       placeholder="yyyy-mm-dd" required/>
                            </div>
                            &nbsp;&nbsp;
                            <button type="submit" name="submit_payment" class="btn btn-primary btn-sm">
                                <span class="glyphicon glyphicon-plus"></span> Ghi nhận
                            </button>
                        </div>
                        <small class="text-muted">
                            Hoa hồng sẽ được tính sau 22 ngày kể từ ngày nộp tiền (khách có 21 ngày hủy HĐ).
                        </small>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- LỊCH SỬ THANH TOÁN -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Lịch sử thanh toán
                        <span class="badge"><?= count($payments) ?></span>
                    </h3>
                </div>
                <div class="panel-body" style="padding:0;">
                    <?php if (empty($payments)): ?>
                        <p class="text-center text-muted" style="padding:20px;">Chưa có lần nộp tiền nào.</p>
                    <?php else: ?>
                    <table class="table table-condensed table-hover" style="margin-bottom:0;">
                        <thead>
                            <tr class="active">
                                <th>#</th>
                                <th>Ngày nộp</th>
                                <th class="text-right">Số tiền</th>
                                <th class="text-right">Lũy kế năm đầu</th>
                                <th>Mở khóa HH</th>
                                <th>Trạng thái HH</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($payments as $i => $p):
                            $commBadge = match($p['commission_status']) {
                                'processed' => '<span class="label label-success">Đã tính</span>',
                                'unlocked'  => '<span class="label label-warning">Chờ xử lý</span>',
                                default     => '<span class="label label-default">Đang khóa</span>',
                            };
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= $p['payment_date'] ?></td>
                            <td class="text-right"><strong><?= number_format($p['amount_paid'], 0, ',', '.') ?>đ</strong></td>
                            <td class="text-right"><?= number_format($p['cumulative_first_year'], 0, ',', '.') ?>đ</td>
                            <td><small><?= $p['commission_unlock_date'] ?></small></td>
                            <td><?= $commBadge ?></td>
                            <td>
                                <?php if ($p['commission_processed'] == 0): ?>
                                <form action="" method="POST" style="margin:0;">
                                    <input type="hidden" name="payment_id"  value="<?= $p['payment_id'] ?>"/>
                                    <input type="hidden" name="contract_id" value="<?= $contract_id ?>"/>
                                    <button type="submit" name="delete_payment"
                                            class="btn btn-xs btn-danger"
                                            onclick="return confirm('Xóa lần nộp này?');">
                                        <span class="glyphicon glyphicon-trash"></span>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- HOA HỒNG LIÊN QUAN -->
            <?php if (!empty($commissions)): ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Hoa hồng phát sinh từ hợp đồng này
                        <span class="badge"><?= count($commissions) ?></span>
                    </h3>
                </div>
                <div class="panel-body" style="padding:0;">
                    <table class="table table-condensed table-hover" style="margin-bottom:0;">
                        <thead>
                            <tr class="active">
                                <th>Ngày tính</th>
                                <th>Người nhận</th>
                                <th>Loại</th>
                                <th class="text-right">Tỷ lệ</th>
                                <th class="text-right">Số tiền</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($commissions as $c):
                            $typeBadge = $c['commission_type'] === 'direct'
                                ? '<span class="label label-primary">Trực tiếp</span>'
                                : '<span class="label label-info">Override</span>';
                            $statusBadge = match($c['status']) {
                                'paid'     => '<span class="label label-success">Đã chi</span>',
                                'approved' => '<span class="label label-warning">Đã duyệt</span>',
                                default    => '<span class="label label-default">Chờ duyệt</span>',
                            };
                        ?>
                        <tr>
                            <td><small><?= $c['calculated_at'] ?></small></td>
                            <td>[<?= $c['rank_code'] ?>] <?= htmlspecialchars($c['beneficiary_name']) ?></td>
                            <td><?= $typeBadge ?></td>
                            <td class="text-right"><?= number_format($c['rate_applied'] * 100, 1) ?>%</td>
                            <td class="text-right"><strong><?= number_format($c['commission_amount'], 0, ',', '.') ?>đ</strong></td>
                            <td><?= $statusBadge ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div><!-- /col-md-7 -->
    </div><!-- /row -->
</div><!-- /container -->

<?php include_once 'html/emb_js.php'; ?>
<script src="js/vidix/Contract.js" type="text/javascript"></script>
</body>
</html>
