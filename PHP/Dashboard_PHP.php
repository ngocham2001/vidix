<?php
session_start();
include_once 'define.php';
include_once PATH_MAIN_FUNCTION . '/conn-login-logout.php';
$conn = connection_to_database();

// ============================================================
// PHẦN 1: SỐ LIỆU NHANH
// ============================================================

// 1.1 Tổng hợp đồng đang hoạt động
$r = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS cnt FROM tbl_hopdong_ttchung WHERE TrangThaiHD = 'Dang_hoat_dong'"
));
$tongHD = $r['cnt'];

// 1.2 Hợp đồng mới trong tháng này
$r = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS cnt FROM tbl_hopdong_ttchung
     WHERE MONTH(NgayNopTien1) = MONTH(CURDATE())
       AND YEAR(NgayNopTien1)  = YEAR(CURDATE())
       AND TrangThaiHD != 'Da_huy_trong_21_ngay'"
));
$hdMoiThang = $r['cnt'];

// 1.3 Hoa hồng chờ duyệt
$r = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS cnt, COALESCE(SUM(commission_amount),0) AS total
     FROM commission_transaction WHERE status = 'pending'"
));
$hoaHongCnt   = $r['cnt'];
$hoaHongTotal = $r['total'];

// 1.4 Nhân viên đang hoạt động
$r = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS cnt FROM agent WHERE status = 'active'"
));
$tongAgent = $r['cnt'];

// 1.5 Nhân viên mới tháng này
$r = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS cnt FROM agent
     WHERE MONTH(join_date) = MONTH(CURDATE())
       AND YEAR(join_date)  = YEAR(CURDATE())"
));
$agentMoiThang = $r['cnt'];

// ============================================================
// PHẦN 2: THÔNG BÁO HÀNH ĐỘNG
// ============================================================
// 2.1 Hợp đồng chờ đủ 21 ngày:
// Điều kiện: Hợp đồng đã nộp tiền >=21 ngày và đang ở trạng thái HĐ chờ:
$sqlHDCho = "
	SELECT count(*) as soHDCho
    FROM tbl_hopdong_ttchung 
    WHERE TrangThaiHDCho = 1
      AND DATEDIFF(NOW(), NgayNopTien1) >=21
";
$resultHDCho = mysqli_query($conn, $sqlHDCho) or die(mysqli_error($conn));
while ($row = mysqli_fetch_assoc($resultHDCho)) {
    $soHDCho= $row['soHDCho'];
}
// 2.2 Hợp đồng A đủ điều kiện chuyển sang AG
// Điều kiện: Tổng tiền trong tbl_ttchung_a (TrangthaiTinhLai='Continue')
//            >= trị giá hợp đồng (SoDVTC * SonamHD * 1260000)
$sqlChuyenAG = "
    SELECT
        hd.SoHD,
        hd.Khachhang_ID,
        hd.SoDVTC,
        hd.SonamHD,
        hd.SoDVTC * hd.SonamHD * 1260000 AS trigia_hd,
        SUM(a.SotienChuyen)              AS tong_tien_a,
        hd.maNV_banhang,
        hd.agent_id_banhang
    FROM tbl_hopdong_ttchung hd
    JOIN tbl_ttchung_a a ON a.SoHD = hd.SoHD
    WHERE hd.TrangThaiHD = 'Dang_hoat_dong'
      AND hd.LoaiHD      = 'A'
      AND a.TrangthaiTinhLai = 'Continue'
      AND a.TrangthaiVonGop != 'Đã chuyển AG'
      AND a.NgàyChuyenAG IS NULL
    GROUP BY hd.SoHD
    HAVING tong_tien_a >= trigia_hd
    ORDER BY tong_tien_a DESC
";
$resultChuyenAG = mysqli_query($conn, $sqlChuyenAG) or die(mysqli_error($conn));
$dsChuyenAG     = [];
while ($row = mysqli_fetch_assoc($resultChuyenAG)) {
    $dsChuyenAG[] = $row;
}
$soHDChuyenAG = count($dsChuyenAG);

// 2.3 Hợp đồng A/AG đủ điều kiện tăng hạn mức TCOx
// Điều kiện A: Tổng tiền A - (SoTCOx đã tăng * trigia) >= trigia
// Điều kiện AG: Có bản ghi tbl_ttchung_ag chưa có NgayChuyenTCOx
$sqlTangTCOx = "
    SELECT
        hd.SoHD,
        hd.Khachhang_ID,
        hd.LoaiHD,
        hd.SoDVTC,
        hd.SonamHD,
        hd.SoDVTC * hd.SonamHD * 1260000 AS trigia_hd,
        hd.maNV_banhang,
        hd.agent_id_banhang,
        -- Cho HĐ AG: đếm số bản ghi chưa chuyển TCOx
        (SELECT COUNT(*) FROM tbl_ttchung_ag ag
         WHERE ag.SoHD = hd.SoHD
           AND ag.NgayChuyenTCOx IS NULL
           AND ag.MaNVChuyenTCOx IS NULL) AS ag_chua_tang,
        -- Cho HĐ A: tính số tiền dư đủ để tăng TCOx
        (SELECT COALESCE(SUM(a2.SotienChuyen),0)
         FROM tbl_ttchung_a a2
         WHERE a2.SoHD = hd.SoHD
           AND a2.TrangthaiTinhLai = 'Continue') AS tong_tien_a,
        (SELECT COALESCE(SUM(t.SoTCOx),0)
         FROM tbl_ttchunga_tcox t
         WHERE t.SoHD = hd.SoHD) AS tong_tcox_da_tang
    FROM tbl_hopdong_ttchung hd
    WHERE hd.TrangThaiHD = 'Dang_hoat_dong'
    HAVING
        (LoaiHD = 'AG' AND ag_chua_tang > 0)
        OR
        (LoaiHD = 'A'
         AND tong_tien_a - (tong_tcox_da_tang * trigia_hd) >= trigia_hd)
    ORDER BY hd.SoHD
";
$resultTangTCOx = mysqli_query($conn, $sqlTangTCOx) or die(mysqli_error($conn));
$dsTangTCOx     = [];
while ($row = mysqli_fetch_assoc($resultTangTCOx)) {
    $dsTangTCOx[] = $row;
}
$soHDTangTCOx = count($dsTangTCOx);

// ============================================================
// PHẦN 3: LÃI SẮP ĐẾN HẠN
// ============================================================

// 3.1 HĐ loại A sắp đến hạn tính lãi năm (trong 30 ngày tới)
// Lãi năm được tính sau 90 ngày kể từ ngày nộp tiền,
// sau đó mỗi năm 1 lần tính từ ThoiGianTinhLaiA
$sqlLaiA = "
    SELECT
        a.ID,
        a.SoHD,
        a.SotienChuyen,
        a.ThoiGianTinhLaiA,
        a.NgayTinhLaiCuoi,
        kh.MaKH,
        kh.HoTen,
        -- Ngày tính lãi tiếp theo
        CASE
            WHEN a.NgayTinhLaiCuoi IS NULL
                THEN DATE_ADD(a.ThoiGianTinhLaiA, INTERVAL 1 YEAR)
            ELSE DATE_ADD(a.NgayTinhLaiCuoi, INTERVAL 1 YEAR)
        END AS ngay_lai_tiep_theo,
        -- Lãi dự kiến = 5%/năm * vốn
        ROUND(a.SotienChuyen * 0.05, 0) AS lai_du_kien
    FROM tbl_ttchung_a a
    JOIN tbl_hopdong_ttchung hd ON hd.SoHD = a.SoHD
    JOIN tbl_khachhang kh       ON kh.ID = hd.Khachhang_ID
    WHERE a.TrangthaiTinhLai = 'Continue'
      AND CASE
            WHEN a.NgayTinhLaiCuoi IS NULL
                THEN DATE_ADD(a.ThoiGianTinhLaiA, INTERVAL 1 YEAR)
            ELSE DATE_ADD(a.NgayTinhLaiCuoi, INTERVAL 1 YEAR)
          END BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY ngay_lai_tiep_theo ASC
    LIMIT 10
";
$resultLaiA = mysqli_query($conn, $sqlLaiA) or die(mysqli_error($conn));
$dsLaiA     = [];
while ($row = mysqli_fetch_assoc($resultLaiA)) {
    $dsLaiA[] = $row;
}
$soLaiA = count($dsLaiA);

// 3.2 HĐ loại AG: lãi hàng tháng cần xử lý (đến hạn trong tháng này)
$sqlLaiAG = "
    SELECT
        ag.ID,
        ag.SoHD,
        ag.SoDVTC,
        ag.NgaychuyenAG,
        kh.MaKH,
        hd.SonamHD,
        kh.HoTen,
        kh.SoDT,
        -- Lãi tháng = SoDVTC * SonamHD * 1260000 * 5% / 12
        ROUND(ag.SoDVTC * hd.SonamHD * 1260000 * 0.05 / 12, 0) AS lai_thang,
        -- Ngày trả lãi tháng này
        DATE_FORMAT(
            DATE_ADD(ag.NgaychuyenAG,
                INTERVAL TIMESTAMPDIFF(MONTH, ag.NgaychuyenAG, CURDATE()) MONTH
            ), '%Y-%m-%d'
        ) AS ngay_tra_lai_thang_nay
    FROM tbl_ttchung_ag ag
    JOIN tbl_hopdong_ttchung hd ON hd.SoHD = ag.SoHD
    JOIN tbl_khachhang kh       ON kh.ID = hd.Khachhang_ID
    WHERE hd.TrangThaiHD = 'Dang_hoat_dong'
      AND ag.NgaychuyenAG IS NOT NULL
      AND DAY(ag.NgaychuyenAG) BETWEEN DAY(CURDATE()) - 3 AND DAY(CURDATE()) + 7
    ORDER BY DAY(ag.NgaychuyenAG)
    LIMIT 10
";
$resultLaiAG = mysqli_query($conn, $sqlLaiAG) or die(mysqli_error($conn));
$dsLaiAG     = [];
while ($row = mysqli_fetch_assoc($resultLaiAG)) {
    $dsLaiAG[] = $row;
}
$soLaiAG = count($dsLaiAG);

// Tổng lãi AG phải trả tháng này
$r = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(ag.SoDVTC * hd.SonamHD * 1260000 * 0.05 / 12), 0) AS tong_lai
     FROM tbl_ttchung_ag ag
     JOIN tbl_hopdong_ttchung hd ON hd.SoHD = ag.SoHD
     WHERE hd.TrangThaiHD = 'Dang_hoat_dong'
       AND ag.NgaychuyenAG IS NOT NULL"
));
$tongLaiAGThang = $r['tong_lai'];

// ============================================================
// PHẦN 4: TOP NHÂN VIÊN & PHÂN BỔ CẤP BẬC
// ============================================================

// 4.1 Phân bổ nhân viên theo cấp
$rankStats = [];
$rR = mysqli_query($conn,
    "SELECT rc.rank_id, rc.rank_code, rc.rank_name,
            COUNT(a.agent_id) AS cnt
     FROM   rank_config rc
     LEFT JOIN agent a ON a.current_rank_id = rc.rank_id
                      AND a.status = 'active'
     GROUP  BY rc.rank_id
     ORDER  BY rc.rank_id ASC"
);
while ($r = mysqli_fetch_assoc($rR)) {
    $rankStats[] = $r;
}

// 4.2 Top 5 nhân viên theo số hợp đồng
$topAgents = [];
$tR = mysqli_query($conn,
    "SELECT
        a.agent_id,
        a.agent_code,
        a.full_name,
        rc.rank_code,
        COUNT(DISTINCT hd.SoHD)          AS so_hd,
        COALESCE(SUM(nt.SoTienNop), 0)   AS tong_nop
     FROM agent a
     JOIN rank_config rc ON rc.rank_id = a.current_rank_id
     LEFT JOIN tbl_hopdong_ttchung hd
          ON hd.agent_id_banhang = a.agent_id
         AND hd.TrangThaiHD = 'Dang_hoat_dong'
     LEFT JOIN tbl_noptien nt
          ON nt.SoHD = hd.SoHD
         AND MONTH(nt.ThoigianNop) = MONTH(CURDATE())
         AND YEAR(nt.ThoigianNop)  = YEAR(CURDATE())
     WHERE a.status = 'active'
     GROUP BY a.agent_id
     ORDER BY so_hd DESC, tong_nop DESC
     LIMIT 5"
);
while ($r = mysqli_fetch_assoc($tR)) {
    $topAgents[] = $r;
}
