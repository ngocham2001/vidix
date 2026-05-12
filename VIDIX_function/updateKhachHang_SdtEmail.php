<?php
/**
 * updateKhachHang_SdtEmail.php
 * AJAX: Cập nhật SĐT hoặc Email của khách hàng theo MaKH
 * Chỉ cho phép sửa 2 trường: SoDT và Email
 * Ghi log vào tbl_theodoi
 * Đặt tại: VIDIX_function/updateKhachHang_SdtEmail.php
 */

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (empty($_SESSION['user_info'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

include_once '../define.php';
include_once PATH_MAIN_FUNCTION . '/conn-login-logout.php';
$conn = connection_To_Database();

header('Content-Type: application/json; charset=utf-8');

$maKhach = trim($_POST['ma_khach'] ?? '');
$field   = trim($_POST['field']    ?? '');
$value   = trim($_POST['value']    ?? '');
$maNV    = $_SESSION['user_info']['logon_id'] ?? '';

// ── Validate đầu vào ──────────────────────────────────────
if (empty($maKhach)) {
    echo json_encode(['success' => false, 'message' => 'Thiếu mã khách hàng']);
    exit;
}

// Whitelist: chỉ cho phép sửa đúng 2 trường này
$allowedFields = [
    'so_dt' => 'SoDT',
    'email' => 'Email',
];

if (!isset($allowedFields[$field])) {
    echo json_encode(['success' => false, 'message' => 'Trường không được phép cập nhật']);
    exit;
}

$dbColumn = $allowedFields[$field];

// Validate giá trị
if ($field === 'so_dt') {
    if (empty($value)) {
        echo json_encode(['success' => false, 'message' => 'Số điện thoại không được để trống']);
        exit;
    }
    if (!preg_match('/^[0-9+\-\s]{8,15}$/', $value)) {
        echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ']);
        exit;
    }
}

if ($field === 'email' && !empty($value)) {
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Địa chỉ email không hợp lệ']);
        exit;
    }
}

// ── Escape ────────────────────────────────────────────────
$maKhSafe = mysqli_real_escape_string($conn, $maKhach);
$valSafe  = mysqli_real_escape_string($conn, $value);
$maNVSafe = mysqli_real_escape_string($conn, $maNV);

// ── Lấy giá trị cũ để ghi log ────────────────────────────
$oldRow = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT `$dbColumn` FROM tbl_khachhang WHERE MaKH = '$maKhSafe' LIMIT 1"
));

if (!$oldRow) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy khách hàng']);
    exit;
}

$oldValue = $oldRow[$dbColumn];

// ── UPDATE ────────────────────────────────────────────────
$sqlUpdate = "
    UPDATE tbl_khachhang
    SET `$dbColumn` = '$valSafe'
    WHERE MaKH = '$maKhSafe'
";

if (!mysqli_query($conn, $sqlUpdate)) {
    echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật: ' . mysqli_error($conn)]);
    exit;
}

// ── Ghi log tbl_theodoi ───────────────────────────────────
$logAction = "Cập nhật $dbColumn KH=$maKhach: '$oldValue' → '$value'";
$logSafe   = mysqli_real_escape_string($conn, $logAction);
mysqli_query($conn, "
    INSERT INTO tbl_theodoi (IDlogon, Action, AtTime)
    VALUES ('$maNVSafe', '$logSafe', NOW())
");

echo json_encode([
    'success'   => true,
    'message'   => 'Cập nhật thành công',
    'field'     => $field,
    'old_value' => $oldValue,
    'new_value' => $value,
]);

mysqli_close($conn);
exit;
