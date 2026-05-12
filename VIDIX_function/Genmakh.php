<?php
/**
 * genMaKH.php
 * AJAX: Sinh mã khách hàng mới tự động
 * Trả về JSON: { ma_kh: 'KH00006' }
 * Đặt tại: VIDIX_function/genMaKH.php
 */
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (empty($_SESSION['user_info'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Chưa đăng nhập']);
    exit;
}

include_once '../define.php';   // ← sửa từ ../../ thành ../
include_once PATH_MAIN_FUNCTION . '/conn-login-logout.php';
$conn = connection_To_Database();

header('Content-Type: application/json; charset=utf-8');

try {
    // Lấy max từ cả 2 bảng để tránh trùng với KH đang trong hồ sơ chờ
    $r1 = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT MAX(CAST(SUBSTRING(MaKH, 3) AS UNSIGNED)) AS max_num
         FROM tbl_khachhang
         WHERE MaKH REGEXP '^KH[0-9]+$'"
    ));
    $maxNum  = (int)($r1['max_num'] ?? 0);
    $nextNum = $maxNum + 1;
    $maKH    = 'KH' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

    // Kiểm tra lại lần cuối phòng race condition hiếm gặp
    while (mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT MaKH FROM tbl_khachhang WHERE MaKH = '$maKH' LIMIT 1"
    ))) {
        $nextNum++;
        $maKH = 'KH' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
    }

    echo json_encode([
        'ma_kh'     => $maKH,
        'so_thu_tu' => $nextNum,
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Không thể sinh mã KH: ' . $e->getMessage()]);
}

mysqli_close($conn);