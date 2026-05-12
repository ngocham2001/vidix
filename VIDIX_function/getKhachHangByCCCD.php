<?php
/**
 * getKhachHangByCCCD.php
 * AJAX: Tra cứu KH theo CCCD
 * Trả về JSON:
 * {
 *   found: true/false,
 *   khach_hang: { MaKH, HoTen, ... TinhTrangSucKhoe_text, TinhTrangSucKhoe_bhyt },
 *   nlh: { HoTenNLH, ... } | null,
 *   agent: { agent_id, agent_code, full_name, rank_code } | null
 * }
 * Đặt tại: VIDIX_function/getKhachHangByCCCD.php
 */

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['found' => false, 'message' => 'Method not allowed']);
    exit;
}
if (empty($_SESSION['user_info'])) {
    http_response_code(401);
    echo json_encode(['found' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

include_once __DIR__ . '/../define.php';
include_once PATH_MAIN_FUNCTION . '/conn-login-logout.php';
$conn = connection_To_Database();

header('Content-Type: application/json; charset=utf-8');

$cccd = trim($_POST['cccd'] ?? '');

if (empty($cccd)) {
    echo json_encode(['found' => false, 'message' => 'Số CCCD không được để trống']);
    exit;
}
if (!preg_match('/^[a-zA-Z0-9]{6,20}$/', $cccd)) {
    echo json_encode(['found' => false, 'message' => 'Số CCCD không hợp lệ']);
    exit;
}

$cccdSafe = mysqli_real_escape_string($conn, $cccd);

// ════════════════════════════════════════════════════════
// BƯỚC 1: Tra cứu khách hàng
// ════════════════════════════════════════════════════════
$sqlKH = "
    SELECT
        id, MaKH, HoTen, CCCD, SoDT,
        DATE_FORMAT(NgaycapCCCD, '%Y-%m-%d') AS NgaycapCCCD,
        DATE_FORMAT(NgaySinh,    '%Y-%m-%d') AS NgaySinh,
        GioiTinh, NoiOHientai, HKThuongtru, Email,
        DanToc, QuocTich, TinhTrangHonnhan,
        TinhTrangSucKhoe, TrinhDoHocVan, GhiChu
    FROM tbl_khachhang
    WHERE CCCD = '$cccdSafe'
    LIMIT 1
";
$resKH = mysqli_query($conn, $sqlKH);

if (!$resKH) {
    http_response_code(500);
    echo json_encode(['found' => false, 'message' => 'Lỗi truy vấn DB']);
    exit;
}
if (mysqli_num_rows($resKH) === 0) {
    echo json_encode(['found' => false]);
    exit;
}

$kh = mysqli_fetch_assoc($resKH);

// Tách TinhTrangSucKhoe → 2 phần cho 2 field riêng
$skParts = preg_split('/\s*[–\-]\s*/u', $kh['TinhTrangSucKhoe'] ?? '', 2);
$kh['TinhTrangSucKhoe_text'] = trim($skParts[0] ?? '');
$kh['TinhTrangSucKhoe_bhyt'] = trim($skParts[1] ?? '');

// ════════════════════════════════════════════════════════
// BƯỚC 2: Lấy thông tin NLH từ hợp đồng gần nhất
// ════════════════════════════════════════════════════════
$idKhSafe = (int)$kh['id'];
$sqlNLH = "
    SELECT
        HoTenNLH, MQH_chuHD,
        DATE_FORMAT(NgaysinhNLH,    '%Y-%m-%d') AS NgaysinhNLH,
        GioitinhNLH, EmailNLH,
        NoiohiennayNLH, DCThuongtruNLH, SoDTNLH,
        DantocNLH, QuoctichNLH, SoCCCDNLH,
        DATE_FORMAT(NgaycapCCCDNLH, '%Y-%m-%d') AS NgaycapCCCDNLH,
        Sotaikhoan, TenNganHang, HotenChuTK,
        SoHD,
        DATE_FORMAT(NgayNopTien1, '%Y-%m-%d') AS NgayNopTien1
    FROM tbl_hopdong_ttchung
    WHERE Khachhang_ID = $idKhSafe
    ORDER BY NgayNopTien1 DESC
    LIMIT 1
";
$resNLH = mysqli_query($conn, $sqlNLH);
$nlh    = null;
if ($resNLH && mysqli_num_rows($resNLH) > 0) {
    $row = mysqli_fetch_assoc($resNLH);
    // Chuẩn hoá ngày rỗng
    foreach (['NgaysinhNLH', 'NgaycapCCCDNLH'] as $col) {
        if (($row[$col] ?? '') === '0000-00-00') $row[$col] = '';
    }
    if (!empty(trim($row['HoTenNLH'] ?? ''))) {
        $nlh = $row;
    }
}

// ════════════════════════════════════════════════════════
// BƯỚC 3: Kiểm tra KH có đồng thời là CTV không
// ════════════════════════════════════════════════════════
$sqlAgent = "
    SELECT a.agent_id, a.agent_code, a.full_name, rc.rank_code, rc.rank_name
    FROM agent a
    JOIN rank_config rc ON rc.rank_id = a.current_rank_id
    WHERE a.id_number = '$cccdSafe'
      AND a.status = 'active'
    LIMIT 1
";
$resAgent = mysqli_query($conn, $sqlAgent);
$agentInfo = null;
if ($resAgent && mysqli_num_rows($resAgent) > 0) {
    $agentInfo = mysqli_fetch_assoc($resAgent);
}

// ════════════════════════════════════════════════════════
// Encode an toàn và trả về JSON
// ════════════════════════════════════════════════════════
$sanitize = function(array $arr): array {
    return array_map(
        fn($v) => htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'),
        $arr
    );
};

echo json_encode([
    'found'      => true,
    'khach_hang' => $sanitize($kh),
    'nlh'        => $nlh   ? $sanitize($nlh)   : null,
    'agent'      => $agentInfo ? $sanitize($agentInfo) : null,
], JSON_UNESCAPED_UNICODE);

mysqli_close($conn);
exit;