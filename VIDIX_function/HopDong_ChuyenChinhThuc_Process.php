<?php
// ============================================================
// VIDIX_function/HopDong_ChuyenChinhThuc_Process.php
// Xử lý chuyển hợp đồng chờ sang hợp đồng chính thức
// Toàn bộ chạy trong 1 SQL TRANSACTION — lỗi bất kỳ bước nào → ROLLBACK
// Thứ tự: B0(kiểm tra) → B1+B2 → B3+B4(AG)/B8(A) → B5(điểm)
//         → B6(HH theo cấp CŨ) → B7(payout) → B6b(thăng cấp) → COMMIT
// ============================================================

session_start();
include_once '../define.php';
include_once PATH_MAIN_FUNCTION . '/conn-login-logout.php';
include_once 'DonVon_KiemTraVon.php';

$conn = connection_to_database();

// Kiểm tra đăng nhập
if (empty($_SESSION['user_info']['logon_id'])) {
    echo json_encode(['success' => false, 'message' => 'Phiên đăng nhập đã hết hạn.']);
    exit;
}

$maNV_thuchien  = $_SESSION['user_info']['logon_id'];
$tenNV_thuchien = $_SESSION['user_info']['fullname'];
$ngayHienTai    = date('Y-m-d');

// Nhận danh sách ID hợp đồng từ POST (xử lý tuần tự từng cái)
if (!isset($_POST['hopdong_ids']) || empty($_POST['hopdong_ids'])) {
    echo json_encode(['success' => false, 'message' => 'Không có hợp đồng nào được chọn.']);
    exit;
}

$rawIds       = $_POST['hopdong_ids'];
$hopdongIds   = array_filter(array_map('intval', explode(',', $rawIds)));

if (empty($hopdongIds)) {
    echo json_encode(['success' => false, 'message' => 'ID hợp đồng không hợp lệ.']);
    exit;
}

$ketQua = [];

foreach ($hopdongIds as $hdId) {
    $ketQua[] = xuLyMotHopDong($conn, $hdId, $maNV_thuchien, $tenNV_thuchien, $ngayHienTai);
}

echo json_encode(['success' => true, 'results' => $ketQua]);
exit;


// ============================================================
// HÀM CHÍNH: Xử lý 1 hợp đồng trong 1 transaction riêng biệt
// ============================================================
function xuLyMotHopDong($conn, $hdId, $maNV_thuchien, $tenNV_thuchien, $ngayHienTai) {

    // ----------------------------------------------------------
    // Lấy thông tin hợp đồng
    // ----------------------------------------------------------
    $stmt = mysqli_prepare($conn,
        "SELECT hd.ID, hd.SoHD, hd.LoaiHD, hd.SoDVTC, hd.SonamHD,
                hd.NgayNopTien1, hd.TrangThaiHDCho, hd.agent_id_banhang,
                hd.maNV_banhang, hd.HDTuyChonB,
                np.ID AS noptien_id, np.SoTienNop, np.NgayTraLai1,
                np.TrangThaiDongTien,
                kh.Sotaikhoan AS kh_sotk, kh.TenNganHang AS kh_nganhang
         FROM   tbl_hopdong_ttchung hd
         JOIN   tbl_noptien np ON np.SoHD = hd.SoHD
         LEFT JOIN tbl_khachhang kh ON kh.ID = hd.Khachhang_ID
         WHERE  hd.ID = ?
         ORDER  BY np.ID ASC
         LIMIT  1"
    );
    mysqli_stmt_bind_param($stmt, 'i', $hdId);
    mysqli_stmt_execute($stmt);
    $hd = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$hd) {
        return ['id' => $hdId, 'soHD' => '?', 'success' => false,
                'message' => 'Không tìm thấy hợp đồng.'];
    }

    $soHD        = $hd['SoHD'];
    $loaiHD      = $hd['LoaiHD'];     // 'A' hoặc 'AG'
    $soDVTC      = (int)$hd['SoDVTC'];
    $soNamHD     = (int)$hd['SonamHD'];
    $ngayNop     = $hd['NgayNopTien1'];
    $soTienNop   = (float)$hd['SoTienNop'];
    $noptienId   = (int)$hd['noptien_id'];
    $agentId     = (int)$hd['agent_id_banhang'];
    $maNVBanHang = $hd['maNV_banhang'];
    $trigiaHD    = $soDVTC * 1260000 * $soNamHD;

    // ----------------------------------------------------------
    // BƯỚC 0: Kiểm tra điều kiện số tiền nộp
    // ----------------------------------------------------------
    if ($loaiHD === 'AG') {
        if ($soTienNop < $trigiaHD) {
            return ['id' => $hdId, 'soHD' => $soHD, 'success' => false,
                    'message' => "HĐ AG: tiền nộp ($soTienNop) chưa đủ trị giá HĐ ($trigiaHD). Không thể chuyển chính thức."];
        }
    } else { // Loại A
        if ($soTienNop <= 0) {
            return ['id' => $hdId, 'soHD' => $soHD, 'success' => false,
                    'message' => 'HĐ A: chưa có tiền nộp. Không thể chuyển chính thức.'];
        }
    }

    // Tính các giá trị dùng cho hoa hồng và điểm
    // AG: base = trigiaHD | A: base = min(tiềnnộp, trigiaHD)
    $baseHoaHong = ($loaiHD === 'AG') ? $trigiaHD : min($soTienNop, $trigiaHD);
    $tienDu      = ($loaiHD === 'AG') ? ($soTienNop - $trigiaHD) : 0;

    // Ngày phát hành HĐ = ngày nộp tiền + 21 ngày
    $ngayPHHD = date('Y-m-d', strtotime($ngayNop . ' + 21 days'));
    // NgayTraLai1 = ngày nộp + 90 ngày
    $ngayTraLai1 = date('Y-m-d', strtotime($ngayNop . ' + 90 days'));

    // ----------------------------------------------------------
    // Bắt đầu TRANSACTION
    // ----------------------------------------------------------
    mysqli_begin_transaction($conn);

    try {

        // ======================================================
        // BƯỚC 1: Cập nhật tbl_noptien
        // ======================================================
        $trangThaiDongTien = ($loaiHD === 'AG') ? 'Đã chuyển toàn bộ tới AG' : 'Đã chuyển tới A';

        // Cập nhật TrangThaiDongTien + NgayTraLai1 (nếu đang NULL/rỗng)
        $stmt1 = mysqli_prepare($conn,
            "UPDATE tbl_noptien
             SET    TrangThaiDongTien = ?,
                    NgayTraLai1 = CASE
                        WHEN NgayTraLai1 IS NULL OR NgayTraLai1 = '' THEN ?
                        ELSE NgayTraLai1
                    END
             WHERE  ID = ?"
        );
        mysqli_stmt_bind_param($stmt1, 'ssi', $trangThaiDongTien, $ngayTraLai1, $noptienId);
        mysqli_stmt_execute($stmt1);

        // ======================================================
        // BƯỚC 2: Cập nhật tbl_hopdong_ttchung
        // ======================================================
        $stmt2 = mysqli_prepare($conn,
            "UPDATE tbl_hopdong_ttchung
             SET    TrangThaiHDCho = 0,
                    NgayPHHD = ?
             WHERE  ID = ?"
        );
        mysqli_stmt_bind_param($stmt2, 'si', $ngayPHHD, $hdId);
        mysqli_stmt_execute($stmt2);

        // ======================================================
        // BƯỚC 3 + 4 (AG) hoặc BƯỚC 8 (A)
        // ======================================================
        $agId = null; // ID bản ghi tbl_ttchung_ag nếu là AG

        if ($loaiHD === 'AG') {

            // BƯỚC 3: Chèn vào tbl_ttchung_ag
            $stmt3 = mysqli_prepare($conn,
                "INSERT INTO tbl_ttchung_ag
                     (SoHD, NgayxacminhAG, NgaychuyenAG, SoDVTC, id_logon,
                      NgayChuyenTCOx, MaNVChuyenTCOx)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            // NgayxacminhAG = NgaychuyenAG = NgayChuyenTCOx = ngày nộp tiền
            // MaNVChuyenTCOx = mã NV bán hàng, id_logon = NV đang thao tác
            mysqli_stmt_bind_param($stmt3, 'sssisis',
                $soHD, $ngayNop, $ngayNop, $soDVTC,
                $maNV_thuchien, $ngayNop, $maNVBanHang
            );
            mysqli_stmt_execute($stmt3);
            $agId = mysqli_insert_id($conn);

            // BƯỚC 4: Chèn vào tbl_noptiensangag
            // Số tiền chuyển sang AG = trigiaHD (không tính phần dư)
            $stmt4 = mysqli_prepare($conn,
                "INSERT INTO tbl_noptiensangag
                     (id_tblNoptien, id_tblThongtinAG, SoHD, SotienChuyen,
                      Ghichu, NguonChuyen)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $ghiChu4     = $maNV_thuchien;
            $nguonChuyen = 'tbl_noptien';
            mysqli_stmt_bind_param($stmt4, 'iiisss',
                $noptienId, $agId, $soHD, $trigiaHD, $ghiChu4, $nguonChuyen
            );
            mysqli_stmt_execute($stmt4);

            // Nếu AG nộp dư → phần dư chuyển sang tbl_ttchung_a
            if ($tienDu > 0) {
                $ngayTinhLaiA = date('Y-m-d', strtotime($ngayNop . ' + 90 days'));
                $stmt4b = mysqli_prepare($conn,
                    "INSERT INTO tbl_ttchung_a
                         (SoHD, SotienChuyen, ThoiGianTinhLaiA, TrangthaiVonGop,
                          TrangthaiTinhLai, id_TblNoptien, NgayChuyenAG,
                          NgayTinhLaiCuoi, MaNV)
                     VALUES (?, ?, ?, 'Dư sau kết chuyển', 'Continue', ?,
                             NULL, NULL, ?)"
                );
                mysqli_stmt_bind_param($stmt4b, 'sdsss',
                    $soHD, $tienDu, $ngayTinhLaiA, $noptienId, $maNV_thuchien
                );
                mysqli_stmt_execute($stmt4b);
            }

        } else {
            // BƯỚC 8: Loại A → chèn vào tbl_ttchung_a
            $ngayTinhLaiA = date('Y-m-d', strtotime($ngayNop . ' + 90 days'));
            $stmt8 = mysqli_prepare($conn,
                "INSERT INTO tbl_ttchung_a
                     (SoHD, SotienChuyen, ThoiGianTinhLaiA, TrangthaiVonGop,
                      TrangthaiTinhLai, id_TblNoptien, NgayChuyenAG,
                      NgayTinhLaiCuoi, MaNV)
                 VALUES (?, ?, ?, 'Nộp tiền lần 1', 'Continue', ?,
                         NULL, NULL, ?)"
            );
            mysqli_stmt_bind_param($stmt8, 'sdsss',
                $soHD, $soTienNop, $ngayTinhLaiA, $noptienId, $maNV_thuchien
            );
            mysqli_stmt_execute($stmt8);
        }

        // ======================================================
        // BƯỚC 5: Cộng điểm vào point_transaction
        // ======================================================
        // Công thức điểm: (base * 15 * 10%) / 1560000
        $diemHopDong = ($baseHoaHong * 15 * 0.10) / 1560000;
        $expiryDate  = date('Y-m-d', strtotime($ngayHienTai . ' + 30 months'));

        // Lấy toàn bộ người được cộng điểm:
        // người bán + ancestor có is_active = 1 trong agent_hierarchy
        $resAncestors = mysqli_query($conn,
            "SELECT ah.ancestor_id, ah.depth,
                    a.current_rank_id AS ancestor_rank,
                    sel.current_rank_id AS seller_rank
             FROM   agent_hierarchy ah
             JOIN   agent a   ON a.agent_id = ah.ancestor_id
             JOIN   agent sel ON sel.agent_id = $agentId
             WHERE  ah.descendant_id = $agentId
               AND  ah.is_active = 1
             ORDER  BY ah.depth ASC"
        );

        $sellerRank = null;
        while ($anc = mysqli_fetch_assoc($resAncestors)) {
            $ancId   = (int)$anc['ancestor_id'];
            $depth   = (int)$anc['depth'];
            $ancRank = (int)$anc['ancestor_rank'];

            if ($depth === 0) {
                // Chính người bán — điểm sales
                $sellerRank  = $ancRank;
                $pointType   = 'sales';
                $sourceAgent = $agentId;
            } else {
                // Cấp trên — điểm team
                // Nguyên tắc: đồng cấp (is_active=0 đã lọc) được điểm
                // is_active=1 đảm bảo ancestor này đang cao cấp hơn người bán
                $pointType   = 'team';
                $sourceAgent = $agentId;
            }

            $stmtPt = mysqli_prepare($conn,
                "INSERT INTO point_transaction
                     (agent_id, source_agent_id, soHD, noptien_id,
                      points, point_type, transaction_date, expiry_date,
                      is_expired, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())"
            );
            mysqli_stmt_bind_param($stmtPt, 'iisidsss',
                $ancId, $sourceAgent, $soHD, $noptienId,
                $diemHopDong, $pointType,
                $ngayHienTai, $expiryDate
            );
            mysqli_stmt_execute($stmtPt);
        }

        // ======================================================
        // BƯỚC 6: Tính hoa hồng commission_transaction
        // Dùng cấp bậc HIỆN TẠI (trước thăng cấp)
        // ======================================================

        // Lấy cấp của người bán tại thời điểm này
        $sellerRow  = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT current_rank_id FROM agent WHERE agent_id = $agentId"
        ));
        $sellerRankId = (int)$sellerRow['current_rank_id'];

        // Lấy tỷ lệ HH trực tiếp của người bán
        $rateRow = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT rate FROM commission_direct_rate
             WHERE  rank_id = $sellerRankId
               AND  effective_from <= '$ngayHienTai'
               AND  (effective_to IS NULL OR effective_to >= '$ngayHienTai')
             ORDER  BY effective_from DESC LIMIT 1"
        ));
        $directRate = $rateRow ? (float)$rateRow['rate'] : 0;
        $directAmt  = floor($baseHoaHong * $directRate);

        // Ghi hoa hồng trực tiếp cho người bán
        if ($directAmt > 0) {
            $stmtD = mysqli_prepare($conn,
                "INSERT INTO commission_transaction
                     (noptien_id, beneficiary_agent_id, commission_type,
                      contract_rank_locked, base_amount, rate_applied,
                      commission_amount, status, soHD)
                 VALUES (?, ?, 'direct', ?, ?, ?, ?, 'pending', ?)"
            );
            mysqli_stmt_bind_param($stmtD, 'iiiddss',
                $noptienId, $agentId, $sellerRankId,
                $baseHoaHong, $directRate, $directAmt, $soHD
            );
            mysqli_stmt_execute($stmtD);
        }

        // Lấy toàn bộ ancestor để tính override
        // Chỉ lấy ancestor có is_active = 1 (cấp cao hơn người bán)
        // Đồng cấp (is_active=0) không được hoa hồng — đã bị lọc sẵn
        $resOver = mysqli_query($conn,
            "SELECT ah.ancestor_id, ah.depth,
                    a.current_rank_id AS anc_rank_id
             FROM   agent_hierarchy ah
             JOIN   agent a ON a.agent_id = ah.ancestor_id
             WHERE  ah.descendant_id = $agentId
               AND  ah.ancestor_id  != $agentId
               AND  ah.is_active     = 1
             ORDER  BY ah.depth ASC"
        );

        // Xây mảng ancestor theo thứ tự từ gần đến xa
        $ancestors = [];
        while ($r = mysqli_fetch_assoc($resOver)) {
            $ancestors[] = $r;
        }

        // Tính override theo nguyên tắc chênh lệch tỷ lệ
        // commission_override_rate lưu sẵn rate = tỷ lệ senior - tỷ lệ junior cao nhất dưới nhánh
        // Tìm junior rank cao nhất trong nhánh dưới mỗi ancestor
        $prevMaxRankInBranch = $sellerRankId; // bắt đầu từ người bán

        foreach ($ancestors as $anc) {
            $ancId      = (int)$anc['ancestor_id'];
            $ancRankId  = (int)$anc['anc_rank_id'];

            // Chỉ chia HH nếu ancestor cao cấp hơn người bán
            if ($ancRankId <= $sellerRankId) {
                // Đây là trường hợp đồng cấp tuyển dụng — bỏ qua HH
                continue;
            }

            // Lấy tỷ lệ override từ bảng commission_override_rate
            $overrideRow = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT rate FROM commission_override_rate
                 WHERE  senior_rank_id = $ancRankId
                   AND  junior_rank_id = $prevMaxRankInBranch
                   AND  effective_from <= '$ngayHienTai'
                   AND  (effective_to IS NULL OR effective_to >= '$ngayHienTai')
                 ORDER  BY effective_from DESC LIMIT 1"
            ));

            if (!$overrideRow) continue;

            $overrideRate = (float)$overrideRow['rate'];
            $overrideAmt  = floor($baseHoaHong * $overrideRate);

            if ($overrideAmt <= 0) continue;

            $stmtO = mysqli_prepare($conn,
                "INSERT INTO commission_transaction
                     (noptien_id, beneficiary_agent_id, commission_type,
                      contract_rank_locked, base_amount, rate_applied,
                      commission_amount, status, soHD)
                 VALUES (?, ?, 'override', ?, ?, ?, ?, 'pending', ?)"
            );
            mysqli_stmt_bind_param($stmtO, 'iiiddss',
                $noptienId, $ancId, $ancRankId,
                $baseHoaHong, $overrideRate, $overrideAmt, $soHD
            );
            mysqli_stmt_execute($stmtO);

            // Cập nhật junior rank cao nhất cho vòng tiếp theo
            $prevMaxRankInBranch = max($prevMaxRankInBranch, $ancRankId);
        }

        // ======================================================
        // BƯỚC 7: Tạo commission_payout cho từng agent nhận HH
        // ======================================================
        // Lấy tổng HH của từng agent từ các bản ghi vừa tạo (theo noptien_id + soHD)
        $resPayout = mysqli_query($conn,
            "SELECT beneficiary_agent_id,
                    SUM(commission_amount) AS total_hh,
                    a.bank_account, a.bank_name
             FROM   commission_transaction ct
             JOIN   agent a ON a.agent_id = ct.beneficiary_agent_id
             WHERE  ct.noptien_id = $noptienId
               AND  ct.soHD = '$soHD'
               AND  ct.status = 'pending'
             GROUP  BY beneficiary_agent_id"
        );

        $payoutIdMap = []; // agent_id => payout_id
        while ($pay = mysqli_fetch_assoc($resPayout)) {
            $benId      = (int)$pay['beneficiary_agent_id'];
            $totalHH    = (float)$pay['total_hh'];
            $bankAcc    = $pay['bank_account'];
            $bankName   = $pay['bank_name'];

            $stmtPay = mysqli_prepare($conn,
                "INSERT INTO commission_payout
                     (id_tbl_noptien, SoHD, agent_id, updated_at,
                      total_amount, bank_account, bank_name, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')"
            );
            mysqli_stmt_bind_param($stmtPay, 'isisdss',
                $noptienId, $soHD, $benId, $ngayHienTai,
                $totalHH, $bankAcc, $bankName
            );
            mysqli_stmt_execute($stmtPay);
            $payoutIdMap[$benId] = mysqli_insert_id($conn);
        }

        // Gán payout_id ngược lại vào commission_transaction
        foreach ($payoutIdMap as $benId => $payoutId) {
            mysqli_query($conn,
                "UPDATE commission_transaction
                 SET    payout_id = $payoutId
                 WHERE  noptien_id = $noptienId
                   AND  soHD = '$soHD'
                   AND  beneficiary_agent_id = $benId"
            );
        }

        // ======================================================
        // BƯỚC 6b: Kiểm tra và xử lý thăng cấp (SAU KHI tính HH)
        // Chạy bottom-up: người bán trước, ancestor sau
        // ======================================================

        // Lấy danh sách cần rà soát: người bán + tất cả ancestor is_active=1
        // Sắp xếp theo depth DESC (người bán depth=0 trước, ancestor xa nhất cuối)
        $resRank = mysqli_query($conn,
            "SELECT ah.ancestor_id AS agent_id, ah.depth
             FROM   agent_hierarchy ah
             WHERE  ah.descendant_id = $agentId
               AND  ah.is_active = 1
             ORDER  BY ah.depth ASC"
        );

        $danhSachRaSoat = [];
        while ($r = mysqli_fetch_assoc($resRank)) {
            $danhSachRaSoat[] = (int)$r['agent_id'];
        }

        foreach ($danhSachRaSoat as $checkAgentId) {
            kiemTraVaThangCap($conn, $checkAgentId, $ngayHienTai);
        }

        // ======================================================
        // Ghi log tbl_theodoi
        // ======================================================
        $logAction = "Chuyển HĐ chính thức: $soHD | Loại: $loaiHD | Base HH: " .
                     number_format($baseHoaHong) . " | NV bán: $maNVBanHang";
        $stmtLog = mysqli_prepare($conn,
            "INSERT INTO tbl_theodoi (IDlogon, Action, AtTime)
             VALUES (?, ?, NOW())"
        );
        mysqli_stmt_bind_param($stmtLog, 'ss', $maNV_thuchien, $logAction);
        mysqli_stmt_execute($stmtLog);

        // ======================================================
        // COMMIT — tất cả bước thành công
        // ======================================================
        mysqli_commit($conn);

        return [
            'id'      => $hdId,
            'soHD'    => $soHD,
            'success' => true,
            'message' => "HĐ $soHD đã chuyển chính thức thành công.",
            'details' => [
                'loaiHD'      => $loaiHD,
                'trigiaHD'    => $trigiaHD,
                'baseHoaHong' => $baseHoaHong,
                'tienDu'      => $tienDu,
                'ngayPHHD'    => $ngayPHHD,
            ]
        ];

    } catch (Exception $e) {
        mysqli_rollback($conn);
        return [
            'id'      => $hdId,
            'soHD'    => $soHD,
            'success' => false,
            'message' => 'Lỗi hệ thống: ' . $e->getMessage()
        ];
    }
}


// ============================================================
// HÀM PHỤ: Kiểm tra và thực hiện thăng cấp cho 1 nhân viên
// Chỉ thăng từng cấp một. Nếu đủ điều kiện → thăng + kiểm tra vượt cấp
// ============================================================
function kiemTraVaThangCap($conn, $agentId, $ngayHienTai) {

    // Lấy thông tin hiện tại của nhân viên
    $agRow = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT current_rank_id, sponsor_agent_id FROM agent WHERE agent_id = $agentId"
    ));
    if (!$agRow) return;

    $currentRankId = (int)$agRow['current_rank_id'];
    $sponsorId     = $agRow['sponsor_agent_id'];

    // Lấy điều kiện thăng cấp cho cấp kế tiếp (current + 1)
    $condRow = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT ruc.to_rank_id, ruc.min_points_total, ruc.min_direct_agents
         FROM   rank_upgrade_condition ruc
         WHERE  ruc.from_rank_id = $currentRankId
           AND  ruc.effective_date <= '$ngayHienTai'
         ORDER  BY ruc.effective_date DESC LIMIT 1"
    ));
    if (!$condRow) return; // Đã cấp cao nhất hoặc chưa có điều kiện

    $toRankId       = (int)$condRow['to_rank_id'];
    $minPoints      = (float)$condRow['min_points_total'];
    $minDirectAgent = (int)$condRow['min_direct_agents'];

    // Tính tổng điểm chưa hết hạn (bản thân + nhánh cấp dưới)
    // point_transaction lưu điểm của tất cả NV trong nhánh gộp về agent_id
    $ptRow = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COALESCE(SUM(pt.points), 0) AS tong_diem
         FROM   point_transaction pt
         WHERE  pt.agent_id = $agentId
           AND  pt.is_expired = 0
           AND  pt.expiry_date >= '$ngayHienTai'"
    ));
    $tongDiem = (float)$ptRow['tong_diem'];

    // Đếm số NV trực tiếp đang active
    $nvRow = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) AS so_nv
         FROM   agent
         WHERE  sponsor_agent_id = $agentId
           AND  status = 'active'"
    ));
    $soNVTrucTiep = (int)$nvRow['so_nv'];

    // Kiểm tra đủ điều kiện không
    if ($tongDiem < $minPoints || $soNVTrucTiep < $minDirectAgent) {
        return; // Chưa đủ — bỏ qua
    }

    // ----------------------------------------------------------
    // ĐỦ ĐIỀU KIỆN → Thực hiện thăng cấp
    // ----------------------------------------------------------

    // Cập nhật cấp mới cho nhân viên
    mysqli_query($conn,
        "UPDATE agent SET current_rank_id = $toRankId WHERE agent_id = $agentId"
    );

    // Ghi nhận thưởng thăng cấp (promotion_bonus)
    mysqli_query($conn,
        "INSERT INTO promotion_bonus
             (agent_id, from_rank_id, to_rank_id, bonus_points,
              promotion_date, status, point_value, multiplier)
         VALUES ($agentId, $currentRankId, $toRankId, 1.00,
                 '$ngayHienTai', 'pending', 3630000.00, 1.00)"
    );

    // ----------------------------------------------------------
    // Cập nhật agent_hierarchy sau thăng cấp
    // Nguyên tắc: is_active dựa trên so sánh cấp
    // ----------------------------------------------------------

    // Tắt is_active với các ancestor có cấp <= cấp mới (đồng cấp hoặc thấp hơn)
    mysqli_query($conn,
        "UPDATE agent_hierarchy ah
         JOIN   agent a_anc ON a_anc.agent_id = ah.ancestor_id
         SET    ah.is_active = 0
         WHERE  ah.descendant_id = $agentId
           AND  ah.ancestor_id  != $agentId
           AND  a_anc.current_rank_id <= $toRankId"
    );

    // Bật is_active với các ancestor có cấp > cấp mới (thực sự cấp trên)
    mysqli_query($conn,
        "UPDATE agent_hierarchy ah
         JOIN   agent a_anc ON a_anc.agent_id = ah.ancestor_id
         SET    ah.is_active = 1
         WHERE  ah.descendant_id = $agentId
           AND  ah.ancestor_id  != $agentId
           AND  a_anc.current_rank_id > $toRankId"
    );

    // ----------------------------------------------------------
    // Kiểm tra vượt cấp so với sponsor trực tiếp
    // Nếu vượt cấp: các ancestor bị tắt is_active (đã xử lý trên)
    // Tách nhánh: agentId trở thành gốc độc lập (không cần thêm bản ghi)
    // ----------------------------------------------------------
    if (!empty($sponsorId)) {
        $sponsorRankRow = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT current_rank_id FROM agent WHERE agent_id = $sponsorId"
        ));
        $sponsorRank = $sponsorRankRow ? (int)$sponsorRankRow['current_rank_id'] : 0;

        // Nếu cấp mới >= cấp sponsor → vượt cấp, đã tách nhánh
        // (Các bản ghi is_active đã cập nhật đúng ở trên)
        // Không cần thêm bản ghi mới vào agent_hierarchy
    }
}
