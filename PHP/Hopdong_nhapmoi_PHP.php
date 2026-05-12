<?php
/**
 * Hopdong_nhapmoi_PHP.php
 * Xử lý 2 chức năng:
 *   1. Cung cấp dữ liệu phụ cho form ($xhtmlSelectAgent)
 *   2. Xử lý submit AJAX → trả về JSON
 *
 * Luồng lưu dữ liệu:
 *   B1: Xử lý khách hàng (tbl_khachhang)
 *   B2: Tạo agent nếu Tùy chọn B (agent + agent_hierarchy)
 *   B3: INSERT tbl_hopdong_ttchung (hồ sơ chờ)
 *   B4: INSERT tbl_thuake (người thụ hưởng)
 *   B5: Ghi log tbl_theodoi
 */

session_start();
include_once __DIR__ . '/../define.php';
include_once PATH_MAIN_FUNCTION . '/conn-login-logout.php';
$conn = connection_To_Database();

// ================================================================
// HÀM TIỆN ÍCH
// ================================================================

/** Sinh mã KH tự tăng dạng KH00001 */
function genMaKH($conn): string {
    $r = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT MAX(CAST(SUBSTRING(MaKH, 3) AS UNSIGNED)) AS max_num
         FROM tbl_khachhang WHERE MaKH REGEXP '^KH[0-9]+$'"
    ));
    $next = ($r['max_num'] ?? 0) + 1;
    return 'KH' . str_pad($next, 5, '0', STR_PAD_LEFT);
}

/** Escape và trim chuỗi đầu vào */
function esc($conn, $val): string {
    return mysqli_real_escape_string($conn, trim($val ?? ''));
}

/** Trả về NULL hoặc giá trị có dấu nháy cho SQL */
function sqlVal($val): string {
    $v = trim($val ?? '');
    return ($v === '' || $v === '0000-00-00') ? 'NULL' : "'" . $v . "'";
}

/** Trả về JSON và thoát */
function jsonOut(array $data): void {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ================================================================
// DỮ LIỆU PHỤ CHO FORM: Dropdown nhân viên tư vấn
// ================================================================
$agentOpts = '<option value="">-- Chọn nhân viên tư vấn --</option>';
$agentRes  = mysqli_query($conn,
    "SELECT a.agent_id, a.agent_code, a.full_name, rc.rank_code
     FROM   agent a
     JOIN   rank_config rc ON rc.rank_id = a.current_rank_id
     WHERE  a.status = 'active'
     ORDER  BY rc.rank_id DESC, a.full_name ASC"
);
if ($agentRes) {
    while ($r = mysqli_fetch_assoc($agentRes)) {
        $agentOpts .= "<option value='{$r['agent_id']}'>"
                    . "[{$r['rank_code']}] {$r['full_name']} ({$r['agent_code']})"
                    . "</option>";
    }
}
$xhtmlSelectAgent = "<select id='agent_id_banhang' name='agent_id_banhang'
                             class='form-input'>$agentOpts</select>";

// ================================================================
// CHỈ XỬ LÝ KHI CÓ SUBMIT
// ================================================================
if (!isset($_POST['submit_ho_so'])) {
    return; // File được include vào form.php, không submit → dừng ở đây
}

// Từ đây chỉ chạy khi AJAX POST
header('Content-Type: application/json; charset=utf-8');

// Bắt PHP fatal error → trả về JSON
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR])) {
        if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi PHP: ' . $err['message'] . ' (dòng ' . $err['line'] . ')',
        ], JSON_UNESCAPED_UNICODE);
    }
});

mysqli_begin_transaction($conn);

try {
    // ────────────────────────────────────────────────────────────
    // THÔNG TIN NGƯỜI NHẬP
    // ────────────────────────────────────────────────────────────
    $maNVNhap  = $_SESSION['user_info']['logon_id'] ?? '';
    $tenNVNhap = $_SESSION['user_info']['fullname']  ?? '';

    // ════════════════════════════════════════════════════════════
    // NHÓM 1: THÔNG TIN KHÁCH HÀNG (CARD 1)
    // ════════════════════════════════════════════════════════════
    $cccd             = esc($conn, $_POST['cccd']                   ?? '');
    $maKhach          = esc($conn, $_POST['ma_khach']               ?? '');
    $hoTen            = esc($conn, $_POST['ho_ten']                 ?? '');
    $soDT             = esc($conn, $_POST['so_dt']                  ?? '');
    $ngaySinh         = esc($conn, $_POST['ngay_sinh']              ?? '');
    $ngayCapCCCD      = esc($conn, $_POST['ngay_cap_cccd']          ?? '');
    $gioiTinh         = esc($conn, $_POST['gioi_tinh']              ?? '');
    $email            = esc($conn, $_POST['email']                  ?? '');
    $danToc           = esc($conn, $_POST['dan_toc']                ?? '');
    $quocTich         = esc($conn, $_POST['quoc_tich']              ?? '');
    $tinhTrangHonNhan = esc($conn, $_POST['tinh_trang_hon_nhan']    ?? '');
    $trinhDoHocVan    = esc($conn, $_POST['trinh_do_hoc_van']       ?? '');
    $noiOHienTai      = esc($conn, $_POST['noi_o_hien_tai']         ?? '');
    $hkThuongTru      = esc($conn, $_POST['hk_thuong_tru']          ?? '');
    $ghiChuKH         = esc($conn, $_POST['ghi_chu_kh']             ?? '');

    // Ghép tình trạng sức khỏe (JS đã ghép, PHP fallback)
    $skCombined = trim($_POST['tinh_trang_suc_khoe'] ?? '');
    if (empty($skCombined)) {
        $skText     = trim($_POST['tinh_trang_suc_khoe_text'] ?? '');
        $skBHYT     = trim($_POST['tinh_trang_suc_khoe_bhyt'] ?? '');
        $skCombined = $skText . ($skBHYT ? ' – ' . $skBHYT : '');
    }
    $tinhTrangSucKhoe = esc($conn, $skCombined);

    // ════════════════════════════════════════════════════════════
    // NHÓM 2: MÃ HỒ SƠ (CARD 2)
    // ════════════════════════════════════════════════════════════
    $soHSS          = esc($conn, $_POST['so_hss']            ?? '');
    $soKB           = esc($conn, $_POST['so_kb']             ?? '');
    $soIv           = esc($conn, $_POST['so_iv']             ?? '');
    $maVP           = esc($conn, $_POST['ma_vp']             ?? '');
    $hanHuySql      = esc($conn, $_POST['han_huy_sql']       ?? ''); // YYYY-MM-DD
    $ngayKhoiTaoSql = esc($conn, $_POST['ngay_khoi_tao_sql'] ?? ''); // YYYY-MM-DD

    // Fallback tính ngày từ KB nếu JS chưa tính
    if (empty($hanHuySql) || empty($ngayKhoiTaoSql)) {
        $kb = trim($_POST['so_kb'] ?? '');
        if (strlen($kb) >= 8) {
            $dd   = substr($kb, 0, 2);
            $mm   = substr($kb, 2, 2);
            $yyyy = substr($kb, 4, 4);
            $ngayKhoiTaoSql = $yyyy . '-' . $mm . '-' . $dd;
            $dt = DateTime::createFromFormat('Y-m-d', $ngayKhoiTaoSql);
            if ($dt) {
                $ngayKhoiTaoSql = $dt->format('Y-m-d');
                $dt->modify('+21 days');
                $hanHuySql = $dt->format('Y-m-d');
            }
        }
        if (empty($hanHuySql)) {
            throw new Exception('Số KB không hợp lệ — không tính được ngày chính thức.');
        }
    }

    $namNhanHS = (int)substr($ngayKhoiTaoSql, 0, 4);

    // ════════════════════════════════════════════════════════════
    // NHÓM 3: TÙY CHỌN HỢP ĐỒNG (CARD 3)
    // ════════════════════════════════════════════════════════════
    $tuyChonThamGia = esc($conn, $_POST['tuy_chon_thamgia'] ?? '');
    $laCtv          = ($tuyChonThamGia === 'B');
    $loaiHD         = esc($conn, $_POST['loai_hd']          ?? '');
    $soDVTC         = (int)($_POST['so_dvtc']                ?? 0);
    $soNamHD        = (int)($_POST['so_nam_hd']              ?? 0);
	//Xử lý số tiền nộp từ textbox so_tien_nop
    $soTienNopRaw = trim($_POST['so_tien_nop'] ?? '');
	// Xóa dấu chấm phân cách nếu NV nhập dạng "1.260.000"
	$soTienNopRaw = str_replace(['.', ',', ' '], '', $soTienNopRaw);
	$soTienNop    = (float)$soTienNopRaw;

	// Nếu rỗng hoặc 0 thì tính lại (fallback)
	if ($soTienNop <= 0) {
		$soTienNop = $soDVTC * $soNamHD * 105000 * 12;
	}
	
	//Xử lý kiểm tra nếu khách hàng - ctv đã có hợp đồng tùy chọn B:
	// ── KIỂM TRA: Mỗi KH chỉ được có 1 hợp đồng Tùy chọn B ──────────
	if ($laCtv && !empty($cccd)) {
		$stmtChkB = $conn->prepare("
			SELECT COUNT(*) AS total
			FROM tbl_hopdong_ttchung hd
			JOIN tbl_khachhang kh ON kh.id = hd.Khachhang_ID
			WHERE kh.CCCD        = ?
			  AND hd.HDTuyChonB  = '1'
			  AND hd.TrangThaiHD != 'Huy'
		");
		$stmtChkB->bind_param('s', $cccd);
		$stmtChkB->execute();
		$rowChkB = $stmtChkB->get_result()->fetch_assoc();
		$stmtChkB->close();

		if ((int)$rowChkB['total'] > 0) {
			// Đã có hợp đồng B → tự động hạ xuống A và cảnh báo trong log
			$laCtv          = false;
			$tuyChonThamGia = 'A';
			// Gắn cờ để frontend biết mà hiển thị thông báo
			$warnOptionB    = true;
		}
	}
	$warnOptionB = $warnOptionB ?? false;
	// ─────────────────────────────────────────────────────────────────




	// Validate: không được vượt quá trị giá HĐ
	$trigiaHD = $soDVTC * $soNamHD * 105000 * 12;
	if ($soTienNop > $trigiaHD) {
		throw new Exception('Số tiền nộp (' . number_format($soTienNop) 
			. ') vượt quá trị giá hợp đồng (' . number_format($trigiaHD) . ')');
	}
    $tuDongTangAG   = isset($_POST['tu_dong_tang_ag']) ? 1 : 0;

    if ($soDVTC <= 0 || $soNamHD <= 0) {
        throw new Exception('Số ĐVTC và số năm hợp đồng không hợp lệ.');
    }

    // ════════════════════════════════════════════════════════════
    // NHÓM 4: NGƯỜI LIÊN HỆ & NGÂN HÀNG (CARD 4)
    // ════════════════════════════════════════════════════════════
    $soTaiKhoan    = esc($conn, $_POST['so_tai_khoan']  ?? '');
    $tenNganHang   = esc($conn, $_POST['ten_ngan_hang'] ?? '');
    $hoTenChuTK    = esc($conn, $_POST['ho_ten_chu_tk'] ?? '');
    $hoTenNLH      = esc($conn, $_POST['ho_ten_nlh']    ?? '');
    $mqhChuHD      = esc($conn, $_POST['mqh_chu_hd']    ?? '');
    $ngaySinhNLH   = esc($conn, $_POST['ngay_sinh_nlh'] ?? '');
    $gioiTinhNLH   = esc($conn, $_POST['gioi_tinh_nlh'] ?? '');
    $soCCCDNLH     = esc($conn, $_POST['so_cccd_nlh']   ?? '');
    $noiONLH       = esc($conn, $_POST['noi_o_nlh']     ?? '');
    $hkNLH         = esc($conn, $_POST['hk_nlh']        ?? '');
    $ghiChuHD      = esc($conn, $_POST['ghi_chu_hd']    ?? '');

    // NV tư vấn: nếu B và KH là CTV → dùng agent_id_banhang được JS truyền
    $agentId = $laCtv ? 0 : (int)($_POST['agent_id_banhang'] ?? 0);

    // ════════════════════════════════════════════════════════════
    // NHÓM 5: NGƯỜI THỤ HƯỞNG (CARD 5)
    // ════════════════════════════════════════════════════════════
    $nlhLaThuHuong    = isset($_POST['nlh_la_thu_huong']) ? 1 : 0;
    $doanhNghiepTK    = isset($_POST['DoanhNghiepThuaKe']) ? 1 : 0;
    $tenDN            = esc($conn, $_POST['TenDN']                     ?? '');
    $mst              = esc($conn, $_POST['MST']                       ?? '');
    $thHoTen          = esc($conn, $_POST['thu_huong_HoTen']           ?? '');
    $thMQH            = esc($conn, $_POST['thu_huong_MQH_chuHD']       ?? '');
    $thNgaySinh       = esc($conn, $_POST['thu_huong_Ngaysinh']        ?? '');
    $thGioiTinh       = esc($conn, $_POST['thu_huong_Gioitinh']        ?? '');
    $thCCCD           = esc($conn, $_POST['thu_huong_SoCCCD']          ?? '');
    $thSoDT           = esc($conn, $_POST['thu_huong_SoDT']            ?? '');
    $thEmail          = esc($conn, $_POST['thu_huong_Email']           ?? '');
    $thDanToc         = esc($conn, $_POST['thu_huong_Dantoc']          ?? '');
    $thDiaChiHT       = esc($conn, $_POST['thu_huong_DiaChiHientai']   ?? '');
    $thDCThuongTru    = esc($conn, $_POST['thu_huong_DCThuongtru']     ?? '');
    $thSTK            = esc($conn, $_POST['thu_huong_Sotaikhoan']      ?? '');
    $thNganHang       = esc($conn, $_POST['thu_huong_TenNganHang']     ?? '');
    $thChuTK          = esc($conn, $_POST['thu_huong_HotenChuTK']      ?? '');
    $thPhanTram       = (float)($_POST['thu_huong_PhantramThuhuong']   ?? 100);
    $thGhiChu         = esc($conn, $_POST['thu_huong_GhiChu']          ?? '');

    // ════════════════════════════════════════════════════════════
    // BƯỚC 0: Kiểm tra trùng HSS và KB
    // ════════════════════════════════════════════════════════════
    if (!empty($soHSS)) {
        $chk = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT ID FROM tbl_hopdong_ttchung WHERE HSs = '$soHSS' LIMIT 1"
        ));
        if ($chk) throw new Exception('hss_exists');
    }

    $chkKB = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT ID FROM tbl_hopdong_ttchung WHERE KB = '$soKB' LIMIT 1"
    ));
    if ($chkKB) throw new Exception('kb_exists');

    // ════════════════════════════════════════════════════════════
    // BƯỚC 1: XỬ LÝ KHÁCH HÀNG → tbl_khachhang
    // ════════════════════════════════════════════════════════════
    $khachHangCu = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT id, MaKH FROM tbl_khachhang WHERE CCCD = '$cccd' LIMIT 1"
    ));

    if ($khachHangCu) {
        // KH đã tồn tại → dùng lại
        $khachHangId = (int)$khachHangCu['id'];
        $maKhach     = $khachHangCu['MaKH'];
    } else {
        // KH mới → kiểm tra trùng mã rồi INSERT
        $chkMaKH = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT id FROM tbl_khachhang WHERE MaKH = '$maKhach' LIMIT 1"
        ));
        if ($chkMaKH) {
            $maKhach = esc($conn, genMaKH($conn));
        }

        $sqlKH = "
            INSERT INTO tbl_khachhang (
                MaKH, HoTen, CCCD, SoDT,
                NgaycapCCCD, NgaySinh, GioiTinh,
                NoiOHientai, HKThuongtru, Email,
                DanToc, QuocTich, TinhTrangHonnhan,
                TinhTrangSucKhoe, TrinhDoHocVan, GhiChu
            ) VALUES (
                '$maKhach', '$hoTen', '$cccd', '$soDT',
                " . sqlVal($ngayCapCCCD) . ",
                " . sqlVal($ngaySinh) . ",
                '$gioiTinh',
                '$noiOHienTai', '$hkThuongTru', '$email',
                '$danToc', '$quocTich', '$tinhTrangHonNhan',
                '$tinhTrangSucKhoe', '$trinhDoHocVan', '$ghiChuKH'
            )
        ";
        mysqli_query($conn, $sqlKH)
            or throw new Exception('Lỗi INSERT khách hàng: ' . mysqli_error($conn));
        $khachHangId = mysqli_insert_id($conn);
    }

    // ════════════════════════════════════════════════════════════
    // BƯỚC 2: XỬ LÝ AGENT (Tùy chọn B → tạo NV mới)
    // ════════════════════════════════════════════════════════════
    $agentCode = '';
    $agentName = '';

    if ($laCtv) {
        // Kiểm tra đã có agent với CCCD này chưa
        $existAgent = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT agent_id, agent_code, full_name FROM agent
             WHERE id_number = '$cccd' LIMIT 1"
        ));

        if ($existAgent) {
            // Dùng agent đã có
            $agentId   = (int)$existAgent['agent_id'];
            $agentCode = esc($conn, $existAgent['agent_code']);
            $agentName = esc($conn, $existAgent['full_name']);
        } else {
            // Sinh agent_code mới: C1-XXXXX
            $rAC = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT MAX(CAST(SUBSTRING(agent_code, 4) AS UNSIGNED)) AS max_num
                 FROM agent WHERE agent_code REGEXP '^NV[0-9]+$'"
            ));
            $nextAC    = ($rAC['max_num'] ?? 0) + 1;
            $agentCode = 'NV' . str_pad($nextAC, 5, '0', STR_PAD_LEFT);

            // Lấy rank_id của C1
            $rankC1 = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT rank_id FROM rank_config WHERE rank_code = 'C1' LIMIT 1"
            ));
            $rankId = $rankC1 ? (int)$rankC1['rank_id'] : 1;

            $sqlAgent = "
                INSERT INTO agent (
                    agent_code, full_name, id_number,
                    NgayCapCCCD, phone, email,
                    Ngaysinh, DiaChiThuongTru,
                    bank_account, bank_name,
                    current_rank_id, status,
                    Khachhang_ID, join_date
                ) VALUES (
                    '$agentCode', '$hoTen', '$cccd',
                    " . sqlVal($ngayCapCCCD) . ",
                    '$soDT', '$email',
                    " . sqlVal($ngaySinh) . ",
                    '$hkThuongTru',
                    '$soTaiKhoan', '$tenNganHang',
                    $rankId, 'active',
                    $khachHangId, CURDATE()
                )
            ";
            mysqli_query($conn, $sqlAgent)
                or throw new Exception('Lỗi INSERT agent: ' . mysqli_error($conn));
            $agentId   = mysqli_insert_id($conn);
            $agentName = esc($conn, $hoTen);

            // ── BƯỚC 2B: Ghi agent_hierarchy (closure table) ──
            // Bản ghi tự tham chiếu (depth=0)
            mysqli_query($conn, "
                INSERT INTO agent_hierarchy
                    (ancestor_id, descendant_id, depth, senior_rank_at_insert, is_active)
                VALUES ($agentId, $agentId, 0, $rankId, 1)
            ") or throw new Exception('Lỗi INSERT agent_hierarchy self: ' . mysqli_error($conn));

            // Bản ghi cho toàn bộ tổ tiên của sponsor (nếu có sponsor)
            $sponsorId = (int)($_POST['sponsor_agent_id'] ?? 0);
			if($sponsorId ===0){
				$sponsorId = (int)($_POST['agent_id_banhang'] ?? 0);
			}
            if ($sponsorId > 0) {
                mysqli_query($conn, "
                    INSERT INTO agent_hierarchy
                        (ancestor_id, descendant_id, depth, senior_rank_at_insert, is_active)
                    SELECT
                        ah.ancestor_id,
                        $agentId,
                        ah.depth + 1,
                        a.current_rank_id,
                        1
                    FROM agent_hierarchy ah
                    JOIN agent a ON a.agent_id = ah.ancestor_id
                    WHERE ah.descendant_id = $sponsorId
                ") or throw new Exception('Lỗi INSERT agent_hierarchy ancestry: ' . mysqli_error($conn));

                // Cập nhật sponsor_agent_id
                mysqli_query($conn,
                    "UPDATE agent SET sponsor_agent_id = $sponsorId WHERE agent_id = $agentId"
                );
            }
			else{
				// sponsor_agent_id = 0 -> NV gốc, không có cấp trên
				// agent_hierarchy chỉ có 1 bản ghi depth=0 (đã INSERT ở trên)
				// sponsor_agent_id giữ NULL trong bảng agent
				mysqli_query($conn,"UPDATE agent SET sponsor_agent_id = NULL WHERE agent_id = $agentId");
			}			
        }
    } else {
        // Tùy chọn A hoặc Khác → lấy thông tin NV tư vấn đã chọn
        if ($agentId > 0) {
            $agentRow = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT agent_code, full_name FROM agent WHERE agent_id = $agentId LIMIT 1"
            ));
            if (!$agentRow) throw new Exception('Không tìm thấy nhân viên tư vấn ID=' . $agentId);
            $agentCode = esc($conn, $agentRow['agent_code']);
            $agentName = esc($conn, $agentRow['full_name']);
        }
    }

    // ════════════════════════════════════════════════════════════
    // BƯỚC 3: INSERT tbl_hopdong_ttchung
    // Trạng thái: Dang_hoat_dong + TrangThaiHDCho=1 (chưa đủ 21 ngày)
    // SoHD để NULL — sẽ được điền sau khi xác nhận chính thức
    // ════════════════════════════════════════════════════════════
    $sqlHD = "
        INSERT INTO tbl_hopdong_ttchung (
            SoHD,
            Khachhang_ID, LoaiHD,
            NgayNopTien1, NgayPHHD,
            SoDVTC, SonamHD, TudongTangAG,
            TrangThaiHD, TrangThaiHDCho,
            Sotaikhoan, TenNganHang, HotenChuTK,
            HoTenNLH, MQH_chuHD, NgaysinhNLH,
            GioitinhNLH, EmailNLH,
            NoiohiennayNLH, DCThuongtruNLH, SoDTNLH,
            DantocNLH, QuoctichNLH, SoCCCDNLH,
            GhiChu,
            maNV_nhap, fullname_NVnhap,
            agent_id_banhang, maNV_banhang,
            Iv, HSs, KB,
            NgayHuyHD,HDTuyChonB
        ) VALUES (
            NULL,
            $khachHangId, '$loaiHD',
            " . sqlVal($ngayKhoiTaoSql) . ",
            " . sqlVal($hanHuySql) . ",
            $soDVTC, $soNamHD, $tuDongTangAG,
            'Dang_hoat_dong', 1,
            '$soTaiKhoan', '$tenNganHang', '$hoTenChuTK',
            '$hoTenNLH', '$mqhChuHD',
            " . sqlVal($ngaySinhNLH) . ",
            '$gioiTinhNLH', '',
            '$noiONLH', '$hkNLH', '',
            '', '', '$soCCCDNLH',
            '$ghiChuHD',
            '$maNVNhap', '$tenNVNhap',
            " . ($agentId > 0 ? $agentId : 'NULL') . ", '$agentCode',
            '$soIv', '$soHSS', '$soKB',
            NULL,'$laCtv'
        )
    ";
    mysqli_query($conn, $sqlHD)
        or throw new Exception('Lỗi INSERT hợp đồng: ' . mysqli_error($conn));
    $idHopDong = mysqli_insert_id($conn);

    // ════════════════════════════════════════════════════════════
    // BƯỚC 4: INSERT tbl_thuake (người thụ hưởng)
    // Nếu NLH = người thụ hưởng → copy dữ liệu từ NLH
    // ════════════════════════════════════════════════════════════
    if ($nlhLaThuHuong) {
        // Copy từ NLH
        $thHoTen       = $hoTenNLH;
        $thMQH         = $mqhChuHD;
        $thNgaySinh    = $ngaySinhNLH;
        $thGioiTinh    = $gioiTinhNLH;
        $thCCCD        = $soCCCDNLH;
        $thSoDT        = '';
        $thEmail       = '';
        $thDanToc      = '';
        $thDiaChiHT    = $noiONLH;
        $thDCThuongTru = $hkNLH;
        $thSTK         = $soTaiKhoan;
        $thNganHang    = $tenNganHang;
        $thChuTK       = $hoTenChuTK;
        $thPhanTram    = 100;
        $thGhiChu      = '';
    }

    // Chỉ INSERT khi có ít nhất họ tên người thụ hưởng
    if (!empty($thHoTen)) {
        $sqlTK = "
            INSERT INTO tbl_thuake (
                SoHD,
                Sotaikhoan, TenNganHang, HotenChuTK,
                HoTen, MQH_chuHD,
                Ngaysinh, Gioitinh,
                Email, DiaChiHientai, DCThuongtru,
                SoDT, Dantoc, Quoctich,
                SoCCCD,
                GhiChu,
                maNV_nhap, fullname_NVnhap,
                TenDN, MST,
                DoanhNghiepThuaKe,
                PhantramThuhuong
            ) VALUES (
                NULL,
                '$thSTK', '$thNganHang', '$thChuTK',
                '$thHoTen', '$thMQH',
                " . sqlVal($thNgaySinh) . ",
                '$thGioiTinh',
                '$thEmail', '$thDiaChiHT', '$thDCThuongTru',
                '$thSoDT', '$thDanToc', 'Việt Nam',
                '$thCCCD',
                '$thGhiChu',
                '$maNVNhap', '$tenNVNhap',
                " . sqlVal($tenDN) . ",
                " . sqlVal($mst) . ",
                $doanhNghiepTK,
                $thPhanTram
            )
        ";
        mysqli_query($conn, $sqlTK)
            or throw new Exception('Lỗi INSERT người thụ hưởng: ' . mysqli_error($conn));
        $idThuake = mysqli_insert_id($conn);

        // Cập nhật SoHD vào tbl_thuake sau khi có ID hợp đồng
        // (SoHD chưa có, dùng ID hợp đồng tạm)
        mysqli_query($conn, "
            UPDATE tbl_thuake SET SoHD = NULL
            WHERE ID = $idThuake
        ");
    }

    // ════════════════════════════════════════════════════════════
    // BƯỚC 5: GHI LOG → tbl_theodoi
    // ════════════════════════════════════════════════════════════
    $logNote = sprintf(
        'Tạo HĐ chờ ID=%d - KB=%s - HSS=%s - KH=%s (%s) - NV_tv=%s - TùyChọn=%s',
        $idHopDong, $soKB, $soHSS, $maKhach, $hoTen,
        ($agentCode ?: 'N/A'), $tuyChonThamGia
    );
    $logSafe = esc($conn, $logNote);
    mysqli_query($conn, "
        INSERT INTO tbl_theodoi (IDlogon, Action, AtTime)
        VALUES ('$maNVNhap', '$logSafe', NOW())
    ");

    // ════════════════════════════════════════════════════════════
    // COMMIT
    // ════════════════════════════════════════════════════════════
    mysqli_commit($conn);

    jsonOut([
        'success'      => true,
        'id'           => $idHopDong,
        'ma_khach'     => $maKhach,
        'agent_code'   => $agentCode,
        'message'      => 'Lưu hồ sơ chờ thành công!',
		'warn_option_b' => $warnOptionB
        ? 'Khách hàng đã có hợp đồng Tùy chọn B trước đó. Hợp đồng này đã được tự động chuyển sang Tùy chọn A.'
        : null,
    ]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    $errMsg = $e->getMessage();

    $knownErrs = [
        'hss_exists' => 'Số HSS đã tồn tại trong hệ thống',
        'kb_exists'  => 'Số KB đã tồn tại trong hệ thống',
    ];

    jsonOut([
        'success' => false,
        'message' => $knownErrs[$errMsg] ?? $errMsg,
    ]);
}