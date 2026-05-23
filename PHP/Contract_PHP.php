<?php
session_start();
include_once 'define.php';
include_once PATH_MAIN_FUNCTION . '/conn-login-logout.php';
include_once PATH_MAIN_FUNCTION . '/pagination.php';
$conn = connection_to_database();

define('CONTRACT_PER_PAGE', 20);

// -------------------------------------------------------
// XỬ LÝ THÊM HỢP ĐỒNG MỚI
// -------------------------------------------------------
if (isset($_POST['submit_new'])) {
    $agent_id        = (int)$_POST['agent_id'];
    $customer_name   = mysqli_real_escape_string($conn, trim($_POST['customer_name']));
    $customer_cccd   = mysqli_real_escape_string($conn, trim($_POST['customer_id_number']));
    $customer_phone  = mysqli_real_escape_string($conn, trim($_POST['customer_phone']));
    $product_code    = mysqli_real_escape_string($conn, trim($_POST['product_code']));
    $annual_value    = (float)$_POST['annual_value'];
    $payment_type    = mysqli_real_escape_string($conn, $_POST['payment_type']);
    $start_date      = mysqli_real_escape_string($conn, trim($_POST['start_date']));

    // Tự động tính ngày hết hạn hủy = start_date + 21 ngày (MySQL tính)
    $sql = "INSERT INTO contract
                (agent_id, customer_name, customer_id_number, customer_phone,
                 product_code, annual_value, payment_type, start_date,
                 cancellation_deadline, status)
            VALUES
                ($agent_id,'$customer_name','$customer_cccd','$customer_phone',
                 '$product_code',$annual_value,'$payment_type','$start_date',
                 DATE_ADD('$start_date', INTERVAL 21 DAY), 'pending')";

    mysqli_query($conn, $sql) or die(mysqli_error($conn));
    header('location:' . $_SERVER['PHP_SELF'] . '?fmess=1');
    exit;
}

// -------------------------------------------------------
// XỬ LÝ CẬP NHẬT HỢP ĐỒNG
// -------------------------------------------------------
if (isset($_POST['submit_edit'])) {
    $contract_id   = (int)$_POST['edit_contract_id'];
    $customer_name = mysqli_real_escape_string($conn, trim($_POST['edit_customer_name']));
    $customer_phone= mysqli_real_escape_string($conn, trim($_POST['edit_customer_phone']));
    $annual_value  = (float)$_POST['edit_annual_value'];
    $payment_type  = mysqli_real_escape_string($conn, $_POST['edit_payment_type']);
    $status        = mysqli_real_escape_string($conn, $_POST['edit_status']);

    mysqli_query($conn,
        "UPDATE contract SET
            customer_name  = '$customer_name',
            customer_phone = '$customer_phone',
            annual_value   = $annual_value,
            payment_type   = '$payment_type',
            status         = '$status'
         WHERE contract_id = $contract_id"
    ) or die(mysqli_error($conn));
    header('location:' . $_SERVER['PHP_SELF'] . '?fmess=2');
    exit;
}

// -------------------------------------------------------
// XỬ LÝ HỦY HỢP ĐỒNG (không xóa vật lý, chỉ đổi status)
// -------------------------------------------------------
if (isset($_POST['cancel-submit'])) {
    $contract_id = (int)$_POST['id_cancel'];
    mysqli_query($conn,
        "UPDATE contract SET status = 'cancelled'
         WHERE  contract_id = $contract_id"
    );
    header('location:' . $_SERVER['PHP_SELF'] . '?fmess=cancelled');
    exit;
}

// -------------------------------------------------------
// DỮ LIỆU PHỤ: dropdown chọn nhân viên bán
// -------------------------------------------------------
$agentOptions = '<option value="">-- Chọn nhân viên --</option>';
$agentResult  = mysqli_query($conn,
    "SELECT a.agent_id, a.full_name, rc.rank_code
     FROM   agent a
     JOIN   rank_config rc ON rc.rank_id = a.current_rank_id
     WHERE  a.status = 'active'
     ORDER  BY rc.rank_id ASC, a.full_name ASC"
);
while ($r = mysqli_fetch_assoc($agentResult)) {
    $agentOptions .= "<option value='{$r['agent_id']}'>[{$r['rank_code']}] {$r['full_name']}</option>";
}
$xhtmlSelectAgent     = "<select name='agent_id' id='new_agent_id' class='input-sm' style='width:260px;'>$agentOptions</select>";
$xhtmlSelectAgentEdit = "<select name='edit_agent_id' id='edit_agent_id' class='input-sm' style='width:260px;' disabled>$agentOptions</select>";

// -------------------------------------------------------
// XÂY DỰNG ĐIỀU KIỆN WHERE (dùng chung cho COUNT và SELECT)
// -------------------------------------------------------
$where = "WHERE 1";
if (isset($_POST['search']) && !empty($_POST['textcond'])) {
    $tc    = mysqli_real_escape_string($conn, $_POST['textcond']);
    $where .= " AND (c.customer_name LIKE '%$tc%'
                 OR  c.customer_id_number LIKE '%$tc%'
                 OR  c.contract_id LIKE '%$tc%'
                 OR  a.full_name LIKE '%$tc%')";
}
if (isset($_POST['filter_status']) && $_POST['filter_status'] !== '') {
    $fs    = mysqli_real_escape_string($conn, $_POST['filter_status']);
    $where .= " AND c.status = '$fs'";
}
if (isset($_POST['filter_payment']) && $_POST['filter_payment'] !== '') {
    $fp    = mysqli_real_escape_string($conn, $_POST['filter_payment']);
    $where .= " AND c.payment_type = '$fp'";
}

// -------------------------------------------------------
// PHÂN TRANG: đếm tổng bản ghi
// -------------------------------------------------------
$countSqlContract = "
    SELECT COUNT(DISTINCT c.contract_id) AS total
    FROM   contract c
    JOIN   agent a        ON a.agent_id = c.agent_id
    JOIN   rank_config rc ON rc.rank_id = a.current_rank_id
    $where";
$totalRowsContract = (int)mysqli_fetch_assoc(mysqli_query($conn, $countSqlContract))['total'];

$requestedPage = isset($_POST['page']) ? (int)$_POST['page'] : 1;
[$currentPage, $totalPages, $offset] = getPaginationParams($totalRowsContract, $requestedPage, CONTRACT_PER_PAGE);

// -------------------------------------------------------
// QUERY CHÍNH có LIMIT/OFFSET
// -------------------------------------------------------
$sql = "SELECT c.*,
               a.full_name AS agent_name,
               rc.rank_code,
               COALESCE(SUM(pr.amount_paid),0) AS paid_total,
               COUNT(pr.payment_id) AS payment_count
        FROM   contract c
        JOIN   agent a        ON a.agent_id  = c.agent_id
        JOIN   rank_config rc ON rc.rank_id  = a.current_rank_id
        LEFT JOIN payment_record pr ON pr.contract_id = c.contract_id AND pr.is_first_year = 1
        $where
        GROUP  BY c.contract_id
        ORDER  BY c.start_date DESC
        LIMIT  $offset, " . CONTRACT_PER_PAGE;

$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

$statusMap = [
    'pending'   => ['label' => 'Chờ kích hoạt', 'class' => 'label-default'],
    'active'    => ['label' => 'Đang hoạt động','class' => 'label-success'],
    'cancelled' => ['label' => 'Đã hủy',        'class' => 'label-danger'],
    'expired'   => ['label' => 'Hết hiệu lực',  'class' => 'label-warning'],
];

$xhtmlItem = '
<table class="table table-hover table-bordered" id="contract-table">
    <thead>
        <tr class="active">
            <th width="40px">#</th>
            <th width="100px">Mã HĐ</th>
            <th width="170px">Khách hàng</th>
            <th width="110px">CCCD</th>
            <th width="90px">Loại HĐ</th>
            <th width="120px">Trị giá năm đầu</th>
            <th width="120px">Đã nộp</th>
            <th width="95px">Ngày hiệu lực</th>
            <th width="130px">Nhân viên bán</th>
            <th width="100px">Trạng thái</th>
            <th width="100px">Thao tác</th>
        </tr>
    </thead>
    <tbody>';

$no = 0;
if (mysqli_num_rows($result)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $no++;
        $s      = $statusMap[$row['status']] ?? ['label' => $row['status'], 'class' => 'label-default'];
        $pct    = $row['annual_value'] > 0
                    ? min(100, round($row['paid_total'] / $row['annual_value'] * 100))
                    : 0;
        $ptLabel= $row['payment_type'] === 'annual'
                    ? '<span class="label label-info">Năm</span>'
                    : '<span class="label label-primary">Tháng</span>';
        $closedBadge = $row['first_year_commission_closed']
                    ? ' <span class="label label-success" title="Đã đóng đủ năm đầu">✓</span>' : '';

        $xhtmlItem .= '
        <tr>
            <td>' . $no . '</td>
            <td><a href="Contract_detail.php?id=' . $row['contract_id'] . '">
                <strong>#' . $row['contract_id'] . '</strong></a></td>
            <td><strong>' . htmlspecialchars($row['customer_name']) . '</strong><br/>
                <small class="text-muted">' . htmlspecialchars($row['customer_phone'] ?? '') . '</small></td>
            <td>' . htmlspecialchars($row['customer_id_number']) . '</td>
            <td class="text-center">' . $ptLabel . '</td>
            <td class="text-right">' . number_format($row['annual_value'], 0, ',', '.') . 'đ</td>
            <td>
                <div class="progress" style="margin-bottom:2px;height:12px;">
                    <div class="progress-bar progress-bar-success" style="width:' . $pct . '%"></div>
                </div>
                <small>' . number_format($row['paid_total'], 0, ',', '.') . 'đ (' . $pct . '%)' . $closedBadge . '</small>
            </td>
            <td>' . $row['start_date'] . '</td>
            <td>[' . $row['rank_code'] . '] ' . htmlspecialchars($row['agent_name']) . '</td>
            <td class="text-center"><span class="label ' . $s['class'] . '">' . $s['label'] . '</span></td>
            <td>
                <a href="Contract_detail.php?id=' . $row['contract_id'] . '"
                   class="btn btn-xs btn-default">Chi tiết</a>
                <a href="#" class="btn btn-xs btn-warning"
                   onclick="editContract(
                       ' . $row['contract_id'] . ',
                       \'' . addslashes($row['customer_name'])   . '\',
                       \'' . addslashes($row['customer_phone'] ?? '') . '\',
                       ' . $row['annual_value']  . ',
                       \'' . $row['payment_type'] . '\',
                       \'' . $row['status']       . '\',
                       ' . $row['agent_id']       . '
                   );">Sửa</a>
                ' . ($row['status'] !== 'cancelled' ? '
                <a href="#" class="btn btn-xs btn-danger"
                   onclick="cancelContract(' . $row['contract_id'] . ',\'' . addslashes($row['customer_name']) . '\');">Hủy</a>' : '') . '
            </td>
        </tr>';
    }
} else {
    $xhtmlItem .= '<tr><td colspan="11" class="text-center text-muted">Chưa có dữ liệu</td></tr>';
}
$xhtmlItem .= '</tbody></table>';
$xhtmlItem .= renderPagination($currentPage, $totalPages, $totalRowsContract, CONTRACT_PER_PAGE);
?>
