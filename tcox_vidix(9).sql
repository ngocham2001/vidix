-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2026 at 11:09 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tcox.vidix`
--

-- --------------------------------------------------------

--
-- Table structure for table `agent`
--

CREATE TABLE `agent` (
  `agent_id` int(10) UNSIGNED NOT NULL,
  `Khachhang_ID` int(10) UNSIGNED DEFAULT NULL,
  `agent_code` varchar(30) NOT NULL COMMENT 'Mã NV hiển thị',
  `full_name` varchar(150) NOT NULL,
  `id_number` varchar(20) NOT NULL COMMENT 'CCCD/CMND',
  `NgayCapCCCD` date DEFAULT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `Ngaysinh` date DEFAULT NULL,
  `DiaChiThuongTru` varchar(250) DEFAULT NULL,
  `bank_account` varchar(30) DEFAULT NULL COMMENT 'Tài khoản ngân hàng nhận hoa hồng',
  `bank_name` varchar(100) DEFAULT NULL COMMENT 'Tên Ngân hàng',
  `current_rank_id` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Mã cấp bậc. Ban đầu là C1',
  `sponsor_agent_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Người tuyển dụng trực tiếp (NULL = gốc)',
  `join_date` date NOT NULL COMMENT 'Ngày bắt đầu tham gia',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Nhân viên bán hàng';

--
-- Dumping data for table `agent`
--

INSERT INTO `agent` (`agent_id`, `Khachhang_ID`, `agent_code`, `full_name`, `id_number`, `NgayCapCCCD`, `phone`, `email`, `Ngaysinh`, `DiaChiThuongTru`, `bank_account`, `bank_name`, `current_rank_id`, `sponsor_agent_id`, `join_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 'NV0001', 'Trần Thiện Nhân', '1234567890', NULL, '0981234567', 'test111@gmail.com', NULL, NULL, '0981234567', 'Vietcombank', 1, NULL, '2026-04-17', 'active', '2026-04-17 14:59:37', '2026-05-01 13:46:51'),
(3, 2, 'NV0003', 'Hoàng Ngọc Anh', '1234567809', NULL, '09812345679', 'hna111test@gmail.com', NULL, NULL, '09812345679', 'BIDV', 1, 1, '2026-04-24', 'active', '2026-04-24 07:26:23', '2026-05-02 15:54:05'),
(4, 7, 'C1-00001', 'Ngọc Lan', '1212121211', NULL, '1234567890', '', NULL, NULL, NULL, NULL, 1, 1, '2026-05-02', 'active', '2026-05-02 16:25:38', '2026-05-04 15:47:15'),
(5, 8, 'C1-00002', 'Hoàng Ngọc Lan', '1213121212', '2024-10-15', '0212314516', 'ngoclanhoang@gmail.com', '2001-08-12', 'Hải Phòng', '1234567890', 'Vietcombank', 1, 3, '2026-05-03', 'active', '2026-05-03 17:01:56', '2026-05-04 15:47:27'),
(6, 9, 'C1-00003', 'Trần Phương Mai', '1212131212', '2020-11-20', '0987654321', 'maiphuongtran@gmail.com', '2001-10-23', 'Đà Nẵng', '', '', 1, 3, '2026-05-07', 'active', '2026-05-07 13:29:25', '2026-05-07 13:29:25');

-- --------------------------------------------------------

--
-- Table structure for table `agent_hierarchy`
--

CREATE TABLE `agent_hierarchy` (
  `hierarchy_id` bigint(20) UNSIGNED NOT NULL,
  `ancestor_id` int(10) UNSIGNED NOT NULL COMMENT 'Nhân viên cấp trên',
  `descendant_id` int(10) UNSIGNED NOT NULL COMMENT 'Nhân viên cấp dưới',
  `depth` smallint(5) UNSIGNED NOT NULL COMMENT '1=trực tiếp, 2=cháu...',
  `senior_rank_at_insert` tinyint(3) UNSIGNED NOT NULL COMMENT 'Cấp của ancestor tại thời điểm tạo quan hệ',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0: junior đã ngang hoặc vượt cấp senior'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Closure table quan hệ cấp bậc trong mạng lưới';

--
-- Dumping data for table `agent_hierarchy`
--

INSERT INTO `agent_hierarchy` (`hierarchy_id`, `ancestor_id`, `descendant_id`, `depth`, `senior_rank_at_insert`, `is_active`) VALUES
(4, 3, 3, 0, 1, 1),
(5, 1, 3, 1, 1, 0),
(6, 5, 5, 0, 1, 1),
(7, 3, 5, 1, 1, 1),
(8, 1, 5, 2, 1, 0),
(9, 1, 1, 0, 1, 0),
(10, 6, 6, 0, 1, 1),
(11, 3, 6, 1, 1, 1),
(12, 1, 6, 2, 1, 1),
(13, 1, 4, 1, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `commission_direct_rate`
--

CREATE TABLE `commission_direct_rate` (
  `rate_id` int(10) UNSIGNED NOT NULL,
  `rank_id` tinyint(3) UNSIGNED NOT NULL COMMENT 'Cấp của nhân viên bán hàng trực tiếp',
  `rate` decimal(5,4) NOT NULL COMMENT 'Tỷ lệ % hoa hồng khi bán hàng trực tiếp. VD: 0.0500 = 5%',
  `effective_from` date NOT NULL COMMENT 'Ngày bắt đầu áp dụng',
  `effective_to` date DEFAULT NULL COMMENT 'NULL: đang có hiệu lực',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Quy tắc tỷ lệ hoa hồng theo cấp';

--
-- Dumping data for table `commission_direct_rate`
--

INSERT INTO `commission_direct_rate` (`rate_id`, `rank_id`, `rate`, `effective_from`, `effective_to`, `created_at`) VALUES
(1, 1, '0.2100', '2025-01-01', NULL, '2026-04-22 11:18:34'),
(2, 2, '0.3000', '2025-01-01', NULL, '2026-04-22 11:18:34'),
(3, 3, '0.4000', '2025-01-01', NULL, '2026-04-22 11:20:36'),
(4, 4, '0.5089', '2025-01-01', NULL, '2026-04-22 11:20:36'),
(5, 5, '0.6039', '2025-01-01', NULL, '2026-04-22 11:20:36'),
(6, 6, '0.6589', '2025-01-01', NULL, '2026-04-22 11:20:36'),
(7, 7, '0.6989', '2025-01-01', NULL, '2026-04-22 11:20:36'),
(8, 8, '0.7189', '2025-01-01', NULL, '2026-04-22 11:20:36'),
(9, 1, '0.1900', '2024-01-01', NULL, '2026-04-22 11:18:34');

-- --------------------------------------------------------

--
-- Table structure for table `commission_override_rate`
--

CREATE TABLE `commission_override_rate` (
  `override_id` int(10) UNSIGNED NOT NULL,
  `senior_rank_id` tinyint(3) UNSIGNED NOT NULL COMMENT 'Cấp nhận hoa hồng override (cấp trên)',
  `junior_rank_id` tinyint(3) UNSIGNED NOT NULL COMMENT 'Cấp cao nhất trong nhánh bên dưới',
  `rate` decimal(6,4) NOT NULL COMMENT 'Tỷ lệ chênh lệch = rate[senior] - rate[junior]',
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL COMMENT 'NULL = đang hiệu lực',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `commission_override_rate`
--

INSERT INTO `commission_override_rate` (`override_id`, `senior_rank_id`, `junior_rank_id`, `rate`, `effective_from`, `effective_to`, `created_at`) VALUES
(1, 8, 7, '0.0200', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(2, 8, 6, '0.0600', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(3, 8, 5, '0.1150', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(4, 8, 4, '0.2100', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(5, 8, 3, '0.3189', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(6, 8, 2, '0.4189', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(7, 8, 1, '0.5089', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(8, 7, 6, '0.0400', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(9, 7, 5, '0.0950', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(10, 7, 4, '0.1900', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(11, 7, 3, '0.2989', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(12, 7, 2, '0.3989', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(13, 7, 1, '0.4889', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(14, 6, 5, '0.0550', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(15, 6, 4, '0.1500', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(16, 6, 3, '0.2589', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(17, 6, 2, '0.3539', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(18, 6, 1, '0.4489', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(19, 5, 4, '0.0950', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(20, 5, 3, '0.2039', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(21, 5, 2, '0.3039', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(22, 5, 1, '0.3939', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(23, 4, 3, '0.1089', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(24, 4, 2, '0.2089', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(25, 4, 1, '0.2989', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(26, 3, 2, '0.1000', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(27, 3, 1, '0.1900', '2024-01-01', NULL, '2026-04-22 12:21:36'),
(28, 2, 1, '0.0900', '2024-01-01', NULL, '2026-04-22 12:21:36');

-- --------------------------------------------------------

--
-- Table structure for table `commission_payout`
--

CREATE TABLE `commission_payout` (
  `payout_id` int(10) UNSIGNED NOT NULL,
  `id_tbl_noptien` int(10) UNSIGNED NOT NULL,
  `SoHD` varchar(30) NOT NULL,
  `agent_id` int(10) UNSIGNED NOT NULL,
  `updated_at` date NOT NULL COMMENT 'Ngày công ty cập nhật trạng thái chi trả',
  `total_amount` decimal(15,2) NOT NULL COMMENT 'Tổng tiền hoa hồng chi trả lần này (=SUM của commission_transaction liên quan)',
  `bank_account` varchar(30) DEFAULT NULL COMMENT 'Số tk nhận tiền tại thời điểm chi',
  `bank_name` varchar(100) DEFAULT NULL,
  `paid_date` date DEFAULT NULL COMMENT 'Ngày thanh toán hh. NULL khi chưa TT',
  `note` varchar(300) DEFAULT NULL COMMENT 'Lý do failed, cancelled..',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Ngày hệ thống tạo bản ghi',
  `status` enum('pending','approved','paid','failed','cancelled') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commission_transaction`
--

CREATE TABLE `commission_transaction` (
  `txn_id` bigint(20) UNSIGNED NOT NULL,
  `noptien_id` int(10) UNSIGNED NOT NULL,
  `beneficiary_agent_id` int(10) UNSIGNED NOT NULL COMMENT 'Nhân viên được nhận hoa hồng',
  `commission_type` enum('direct','override') NOT NULL COMMENT 'Loại hoa hồng',
  `contract_rank_locked` tinyint(3) UNSIGNED NOT NULL COMMENT 'Cấp của người nhận tại thời điểm ký hợp đồng (dùng để cố định tỷ lệ hoa hồng mà người bán nhận được tại thời điểm ký hợp đồng)',
  `base_amount` decimal(15,2) NOT NULL COMMENT 'Số tiền gốc để tính hoa hồng - số tiền chủ hợp đồng nộp (theo từng phần hoặc 1 lần)',
  `rate_applied` decimal(5,4) NOT NULL COMMENT '% hoa hồng được nhận',
  `commission_amount` decimal(15,2) NOT NULL COMMENT 'Số tiền hoa hồng thực nhận',
  `calculated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','paid') NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `payout_id` int(10) UNSIGNED DEFAULT NULL,
  `soHD` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Giao dịch hoa hồng thực tế';

-- --------------------------------------------------------

--
-- Table structure for table `promotion_bonus`
--

CREATE TABLE `promotion_bonus` (
  `bonus_id` int(10) UNSIGNED NOT NULL,
  `agent_id` int(10) UNSIGNED NOT NULL,
  `from_rank_id` tinyint(3) UNSIGNED NOT NULL,
  `to_rank_id` tinyint(3) UNSIGNED NOT NULL,
  `bonus_points` decimal(8,2) NOT NULL DEFAULT 1.00 COMMENT 'Điểm thưởng (thường = 1)',
  `promotion_date` date NOT NULL,
  `status` enum('pending','immediate','deferred') NOT NULL DEFAULT 'pending',
  `point_value` decimal(12,2) NOT NULL DEFAULT 3630000.00 COMMENT 'Giá trị mỗi điểm tại thời điểm nhận',
  `multiplier` decimal(4,2) NOT NULL DEFAULT 1.00 COMMENT 'Hệ số nhân nếu để lại quỹ (3-4 lần)',
  `cash_out_date` date DEFAULT NULL COMMENT 'Ngày thực nhận tiền thưởng',
  `amount_paid` decimal(15,2) DEFAULT NULL COMMENT 'Số tiền thực chi',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Thưởng thăng cấp';

-- --------------------------------------------------------

--
-- Table structure for table `rank_config`
--

CREATE TABLE `rank_config` (
  `rank_id` tinyint(3) UNSIGNED NOT NULL,
  `rank_code` varchar(5) NOT NULL COMMENT 'C1, C2 ... C8',
  `rank_name` varchar(100) NOT NULL COMMENT 'Nhân viên / Chuyên viên...',
  `is_specialist` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Từ C3: nhận hoa hồng chênh lệch từ nhánh',
  `monthly_salary_eligible` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Từ C4: được nhận lương hàng tháng',
  `description` text DEFAULT NULL COMMENT 'Mô tả quyền lợi',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Cấu hình cấp bậc C1-C8';

--
-- Dumping data for table `rank_config`
--

INSERT INTO `rank_config` (`rank_id`, `rank_code`, `rank_name`, `is_specialist`, `monthly_salary_eligible`, `description`, `created_at`) VALUES
(1, 'C1', 'Cộng tác viên', 0, 0, 'Hoa hồng 21% trị giá hợp đồng năm đầu', '2026-04-16 16:30:31'),
(2, 'C2', 'Chuyên viên thị trường', 1, 0, 'Hoa hồng 30% + chênh lệch 5% từ nhánh C1', '2026-04-16 16:30:31'),
(3, 'C3', 'Trưởng nhóm thị trường', 1, 0, 'Hoa hồng 40% + chênh lệch 5% từ nhánh C2, 10% từ C1', '2026-04-16 16:30:31'),
(4, 'C4', 'Phó giám đốc thị trường', 1, 1, 'Hoa hồng 50,89% + lương hàng tháng', '2026-04-16 16:30:31'),
(5, 'C5', 'Giám đốc thị trường', 1, 1, 'Hoa hồng 60,39%', '2026-04-16 16:30:31'),
(6, 'C6', 'Phó giám đốc KD VIDIX', 1, 1, 'Hoa hồng 65,89%', '2026-04-16 16:30:31'),
(7, 'C7', 'Giám đốc KD VIDIX', 1, 1, 'Hoa hồng 69,89%', '2026-04-16 16:30:31'),
(8, 'C8', 'Giám đốc KD Tổng hợp', 1, 1, 'Hoa hồng 71,89%', '2026-04-16 16:30:31');

-- --------------------------------------------------------

--
-- Table structure for table `rank_upgrade_condition`
--

CREATE TABLE `rank_upgrade_condition` (
  `condition_id` int(10) UNSIGNED NOT NULL,
  `from_rank_id` tinyint(3) UNSIGNED NOT NULL,
  `to_rank_id` tinyint(3) UNSIGNED NOT NULL,
  `min_points_total` decimal(12,2) NOT NULL COMMENT 'Điểm tối thiểu (bản thân + cấp dưới)',
  `min_direct_agents` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Số nhân viên trực tiếp tối thiểu',
  `effective_date` date NOT NULL COMMENT 'Ngày áp dụng quy chế',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Điều kiện thăng cấp theo từng giai đoạn';

--
-- Dumping data for table `rank_upgrade_condition`
--

INSERT INTO `rank_upgrade_condition` (`condition_id`, `from_rank_id`, `to_rank_id`, `min_points_total`, `min_direct_agents`, `effective_date`, `created_at`) VALUES
(1, 1, 2, '365.00', 3, '2024-01-01', '2026-04-17 10:47:06'),
(2, 2, 3, '1200.00', 15, '2024-01-01', '2026-04-17 10:47:06'),
(3, 3, 4, '4500.00', 63, '2024-01-01', '2026-04-17 10:47:06'),
(4, 4, 5, '35000.00', 479, '2024-01-01', '2026-04-17 10:47:06'),
(5, 5, 6, '69000.00', 943, '2024-01-01', '2026-04-17 10:47:06'),
(6, 6, 7, '89000.00', 1887, '2024-01-01', '2026-04-17 10:47:06'),
(7, 7, 8, '168000.00', 3778, '2024-01-01', '2026-04-17 10:47:06');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_hopdong_file`
--

CREATE TABLE `tbl_hopdong_file` (
  `ID` mediumint(8) UNSIGNED NOT NULL,
  `SoHD` varchar(30) NOT NULL,
  `LoaiFile` varchar(50) NOT NULL COMMENT 'Hợp đồng, Phụ lục, ...',
  `linkFile` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tbl_hopdong_file`
--

INSERT INTO `tbl_hopdong_file` (`ID`, `SoHD`, `LoaiFile`, `linkFile`) VALUES
(2, '000000001', 'Hop_Dong', 'Hop_Dong-000000001-2026-03-20-0Z.pdf'),
(3, '000000002', 'Hop_Dong', 'Hop_Dong-000000002-2026-03-26-a9.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_hopdong_ttchung`
--

CREATE TABLE `tbl_hopdong_ttchung` (
  `ID` int(10) UNSIGNED NOT NULL,
  `SoHD` varchar(30) DEFAULT NULL,
  `HDTuyChonB` tinyint(1) NOT NULL COMMENT '1: Là hợp đồng tùy chọn B, 0: Là hợp đồng tùy chọn khác',
  `TrangThaiHDCho` tinyint(1) NOT NULL COMMENT '1: Hợp đồng chưa đủ 21 ngày. 0: Hợp đồng đã đủ 21 ngày',
  `Iv` varchar(30) NOT NULL COMMENT 'Số Iv: Mã số văn phòng thứ (CN: 08) 2 số tuần 2 số năm 04 là hồ sơ đến, stt hồ sơ phát hành',
  `Khachhang_ID` int(10) UNSIGNED DEFAULT NULL,
  `agent_id_banhang` int(10) UNSIGNED DEFAULT NULL COMMENT 'Khóa ngoại là agent_id tại bảng agent',
  `LoaiHD` varchar(10) NOT NULL COMMENT 'A/AG',
  `NgayNopTien1` date DEFAULT NULL COMMENT 'Ngày nộp tiền l1/Ngày ký HĐ',
  `NgayPHHD` date DEFAULT NULL COMMENT 'Ngày Phát hành HĐ = Ngày nộp tiền l1 + 21 ngày',
  `SoDVTC` int(10) UNSIGNED NOT NULL COMMENT '1 đvị tín chỉ = 1/12 tín chỉ',
  `SonamHD` tinyint(3) UNSIGNED NOT NULL,
  `TudongTangAG` tinyint(1) NOT NULL COMMENT '0: Không tự động tăng; 1: Tự động tăng khi đủ số TC đã đăng ký ở cột SoDVTC',
  `TrangThaiHD` enum('Tam_dung','Da_huy_trong_21_ngay','Dang_hoat_dong','Da_huy_sau_21_ngay','Da_ket_thuc') NOT NULL COMMENT 'Đang HĐ. Tạm dừng. Đã hết hiệu lực. Đã hủy trong 21 ngày. Đã hủy sau 21 ngày',
  `Sotaikhoan` varchar(20) NOT NULL,
  `TenNganHang` varchar(40) NOT NULL,
  `HotenChuTK` varchar(100) NOT NULL,
  `HoTenNLH` varchar(100) NOT NULL COMMENT 'Họ tên người liên hệ',
  `MQH_chuHD` varchar(30) NOT NULL COMMENT 'Mối quan hệ với chủ hợp đồng',
  `NgaysinhNLH` date DEFAULT NULL COMMENT 'Ngày sinh người liên hệ',
  `GioitinhNLH` varchar(10) NOT NULL COMMENT 'Giới tính người liên hệ',
  `EmailNLH` varchar(100) NOT NULL,
  `NoiohiennayNLH` varchar(500) NOT NULL COMMENT 'Nơi ở hiện nay của người liên hệ',
  `DCThuongtruNLH` varchar(500) NOT NULL COMMENT 'Địa chỉ thường trú NLH',
  `SoDTNLH` varchar(15) NOT NULL COMMENT 'Số điện thoại NLH',
  `DantocNLH` varchar(20) NOT NULL,
  `QuoctichNLH` varchar(20) NOT NULL,
  `SoCCCDNLH` varchar(20) NOT NULL,
  `NgaycapCCCDNLH` date DEFAULT NULL,
  `GhiChu` varchar(200) NOT NULL,
  `maNV_nhap` varchar(30) NOT NULL,
  `fullname_NVnhap` varchar(150) NOT NULL,
  `maNV_banhang` varchar(30) NOT NULL COMMENT 'Tham chiếu tới agent_code của bảng agent',
  `HSs` varchar(30) NOT NULL COMMENT '2 số ngày 2 số tháng 4 số năm sinh khách hàng 04 / 2 số mã VP 3 số cuối của năm hợp đồng',
  `KB` varchar(30) NOT NULL COMMENT 'Ký hiệu hợp đồng. Định dạng nhập: 2 số ngày 2 số tháng 3 số cuối năm H 2 số giờ 2 số phút 2 số giây',
  `NgayHuyHD` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tbl_hopdong_ttchung`
--

INSERT INTO `tbl_hopdong_ttchung` (`ID`, `SoHD`, `HDTuyChonB`, `TrangThaiHDCho`, `Iv`, `Khachhang_ID`, `agent_id_banhang`, `LoaiHD`, `NgayNopTien1`, `NgayPHHD`, `SoDVTC`, `SonamHD`, `TudongTangAG`, `TrangThaiHD`, `Sotaikhoan`, `TenNganHang`, `HotenChuTK`, `HoTenNLH`, `MQH_chuHD`, `NgaysinhNLH`, `GioitinhNLH`, `EmailNLH`, `NoiohiennayNLH`, `DCThuongtruNLH`, `SoDTNLH`, `DantocNLH`, `QuoctichNLH`, `SoCCCDNLH`, `NgaycapCCCDNLH`, `GhiChu`, `maNV_nhap`, `fullname_NVnhap`, `maNV_banhang`, `HSs`, `KB`, `NgayHuyHD`) VALUES
(1, '000000001', 0, 0, '510202604/005', 2, 1, 'A', '2026-03-05', '2026-03-26', 1, 16, 0, 'Dang_hoat_dong', '0123456789123', 'Vietcombank', 'Nguyễn Anh', 'Phạm An', '', '2005-09-10', '', 'anpham2012@gmail.com', 'Hà Nội, Việt nam', 'Hà nội, Việt Nam', '0382122622', 'Kinh', 'VN', '01234567890', '2026-02-19', '', 'NV0002', 'Phạm Quang Nhiêu', 'NV0001', '', '', NULL),
(2, '000000002', 1, 0, '810202604/008', 3, 1, 'AG', '2026-03-08', '2026-03-29', 2, 26, 1, 'Dang_hoat_dong', '0981234567', 'MSB', 'Hoàng Thị Hai', '', '', NULL, '', '', '', '', '', '', '', '', NULL, '', 'NV0002', 'Phạm Quang Nhiêu', 'NV0001', '', '0803026H130000', NULL),
(3, '000201026/01B01', 1, 1, '710202604/007', 7, 4, 'AG', '2026-03-07', '2026-03-28', 12, 16, 0, 'Dang_hoat_dong', '0991234567', 'BIDV', 'Trần Văn C', '', '', NULL, '', '', '', '', '', '', '', '', NULL, '', 'NV0002', 'Phạm Quang Nhiêu', 'NV0001', '', '', NULL),
(4, '0001501026/03B02', 1, 0, '510202604/006', 2, 3, 'A', '2026-03-05', '2026-03-26', 1, 16, 0, 'Dang_hoat_dong', '0123456789123', 'Vietcombank', 'Nguyễn Anh', 'Phạm An', '', '2005-09-10', '', 'anpham2012@gmail.com', 'Hà Nội, Việt nam', 'Hà nội, Việt Nam', '0382122622', 'Kinh', 'VN', '01234567890', '2026-02-19', '', 'NV0002', 'Phạm Quang Nhiêu', 'NV0002', '', '', NULL),
(5, NULL, 0, 1, '217202604/009', 6, 3, 'A', '2026-04-20', '2026-05-11', 1, 16, 0, 'Dang_hoat_dong', '', '', '', '', '', NULL, '', '', '', '', '1234567890', '', '', '', NULL, '', 'NV0002', 'Phạm Quang Nhiêu', 'NV0002', '0212199804/00026', '2004026H150300', NULL),
(7, NULL, 1, 1, '618202604/010', 8, 5, 'A', '2026-04-24', '2026-05-15', 2, 16, 0, 'Dang_hoat_dong', '1234567890', 'Vietcombank', 'Hoàng Ngọc Lan', 'Hoàng Ngọc Anh', 'Anh chị em ruột', NULL, '', '', '', '', '', '', '', '', NULL, '', 'NV0002', 'Phạm Quang Nhiêu', 'C1-00002', '1208200104/00026', '24042026H120300', NULL),
(8, NULL, 1, 1, '219202604/011', 9, 6, 'AG', '2026-05-04', '2026-05-25', 2, 16, 1, 'Dang_hoat_dong', '', '', '', '', '', NULL, '', '', '', '', '', '', '', '', NULL, '', 'NV0002', 'Phạm Quang Nhiêu', 'C1-00003', '2310200104/00026/04/011', '04052026H130000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_khachhang`
--

CREATE TABLE `tbl_khachhang` (
  `ID` int(10) UNSIGNED NOT NULL,
  `MaKH` varchar(30) NOT NULL,
  `HoTen` varchar(120) NOT NULL,
  `CCCD` varchar(20) NOT NULL COMMENT 'Số CCCD hoặc số hộ chiếu',
  `SoDT` varchar(15) NOT NULL,
  `NgaycapCCCD` date NOT NULL,
  `NgaySinh` date NOT NULL,
  `GioiTinh` varchar(10) NOT NULL,
  `NoiOHientai` varchar(500) NOT NULL COMMENT 'Nơi ở hiện tại',
  `HKThuongtru` varchar(500) NOT NULL COMMENT 'Hộ khẩu thường trú',
  `Email` varchar(100) NOT NULL,
  `DanToc` varchar(20) NOT NULL,
  `QuocTich` varchar(20) NOT NULL,
  `TinhTrangHonnhan` varchar(30) NOT NULL COMMENT 'Đã kết hôn/ Độc thân',
  `TinhTrangSucKhoe` varchar(100) NOT NULL,
  `TrinhDoHocVan` varchar(30) NOT NULL,
  `GhiChu` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tbl_khachhang`
--

INSERT INTO `tbl_khachhang` (`ID`, `MaKH`, `HoTen`, `CCCD`, `SoDT`, `NgaycapCCCD`, `NgaySinh`, `GioiTinh`, `NoiOHientai`, `HKThuongtru`, `Email`, `DanToc`, `QuocTich`, `TinhTrangHonnhan`, `TinhTrangSucKhoe`, `TrinhDoHocVan`, `GhiChu`) VALUES
(1, 'KH000001', 'Nguyễn Anh', '11111111', '0981111111', '2015-09-05', '2000-03-15', 'Nam', 'Hải Phòng', 'Hải Phòng', 'anh.nguyen@gmail.com', 'Kinh', 'VN', 'Độc thân', 'Tốt-Có BHYT', 'Đại học', ''),
(2, 'KH00002', 'Hoàng Thị Hai', '1417237465', '0981234567', '2025-10-01', '1990-07-25', 'Nữ', 'Thôn Chùa xã Ngọc Bảo thành phố Hải Phòng', 'Thôn Chùa xã Ngọc Bảo thành phố Hải Phòng', 'hai.hoang@gmail.com', 'Kinh', 'Việt Nam', 'Độc thân', 'Tốt-Có BHYT', 'Đại học', ''),
(3, 'KH00003', 'Trần Văn C', '19950327781', '0991234567', '2025-12-30', '1995-03-27', 'Nam', 'Thôn A xã B thành phố C', 'thôn A xã B thành phố C', 'c.tran.van@gmail.com', 'Kinh', 'Việt Nam', 'Nuôi con đơn thân', 'Tốt-Có BHYT', 'Đại học', ''),
(4, 'KH00004', 'Pham', '1234567890', '09832132487', '2026-03-29', '2026-04-02', 'Nam', 'Thôn A xã B thành phố C', 'thôn A xã B thành phố C', 'pham@gmail.com', 'Kinh', 'Việt Nam', 'Độc thân', 'Tốt-Không có BHYT', 'Cao đẳng', ''),
(5, 'KH0002', 'Hoàng Thị Hai', '1417237465', '0981234567', '2025-10-02', '1995-07-20', 'Nữ', 'Thôn Chùa xã Ngọc Bảo thành phố Hải Phòng', 'Thôn Chùa xã Ngọc Bảo thành phố Hải Phòng', 'hai.hoang@gmail.com', 'Kinh', 'Việt Nam', 'Đã kết hôn', 'Tốt-Có BHYT', 'Đại học', ''),
(6, 'KH00005', 'Ngọc Anh', '12121212', '1234567890', '2025-12-10', '1998-12-02', '', '', '', '', '', '', '', '', '', ''),
(7, 'KH00006', 'Ngọc Lan', '1212121211', '1234567890', '2024-02-01', '2000-03-20', 'Nam', '', '', '', '', '', '', '', '', ''),
(8, 'KH00007', 'Hoàng Ngọc Lan', '1213121212', '0212314516', '2024-10-15', '2001-08-12', 'Nữ', 'Hải Phòng', 'Hải Phòng', 'ngoclanhoang@gmail.com', 'Kinh', 'Việt Nam', 'Độc thân', 'Tốt – Có BHYT', 'Cao đẳng', ''),
(9, 'KH00008', 'Trần Phương Mai', '1212131212', '0987654321', '2020-11-20', '2001-10-23', 'Nam', 'Hà Nội', 'Đà Nẵng', 'maiphuongtran@gmail.com', 'Kinh', 'Việt Nam', 'Độc thân', 'Tốt – Có BHYT', 'Đại học', '');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_loai_hopdong`
--

CREATE TABLE `tbl_loai_hopdong` (
  `ma_loai` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `ten_loai` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'A / AG / ...',
  `mo_ta` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `lai_suat` decimal(5,4) NOT NULL DEFAULT 0.0500 COMMENT 'Lãi suất cố định của loại HĐ này',
  `tinh_lai_theo` enum('month','year') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'year' COMMENT 'A tính lãi theo năm, AG theo tháng',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_loai_hopdong`
--

INSERT INTO `tbl_loai_hopdong` (`ma_loai`, `ten_loai`, `mo_ta`, `lai_suat`, `tinh_lai_theo`, `is_active`, `created_at`) VALUES
('A', 'Tùy chọn A', 'Tích lũy lãi hàng năm', '0.0500', 'year', 1, '2026-04-22 13:25:27'),
('AG', 'Tùy chọn AG', 'Nhận lãi hàng tháng', '0.0500', 'month', 1, '2026-04-22 13:25:27');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_login`
--

CREATE TABLE `tbl_login` (
  `maNV` varchar(30) NOT NULL,
  `uname` varchar(40) NOT NULL,
  `Fullname` varchar(150) NOT NULL,
  `pword` varchar(50) NOT NULL,
  `VPDD` varchar(7) NOT NULL COMMENT 'Số hiệu VPĐD',
  `status` enum('active','inactive','suspended') NOT NULL COMMENT 'Trạng thái của username: Đang hoạt động/ Không hoạt động/Tạm dừng',
  `NgaycapMNV` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tbl_login`
--

INSERT INTO `tbl_login` (`maNV`, `uname`, `Fullname`, `pword`, `VPDD`, `status`, `NgaycapMNV`) VALUES
('NV0001', 'ntc', 'Ngô Thị Châm', '*238DAE528512D9C65779D2636B148B855348E6E8', '1', 'active', NULL),
('NV0002', 'pqn', 'Phạm Quang Nhiêu', '*6BB4837EB74329105EE4568DDA7DC67ED2CA2AD9', '1', 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_noptien`
--

CREATE TABLE `tbl_noptien` (
  `ID` int(10) UNSIGNED NOT NULL,
  `SoHD` varchar(30) NOT NULL,
  `ThoigianNop` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `SoTienNop` double NOT NULL,
  `NgayTraLai1` date NOT NULL,
  `GhiChu` varchar(250) NOT NULL,
  `TrangThaiDongTien` varchar(100) NOT NULL COMMENT '1. Đã chuyển toàn bộ tới AG; 2. Chuyển A & AG, 3. Đã chuyển tới A',
  `maNV_nhap` varchar(30) NOT NULL,
  `maNV_duyet` varchar(30) NOT NULL,
  `fullname_NVnhap` varchar(150) NOT NULL,
  `fullname_NVduyet` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tbl_noptien`
--

INSERT INTO `tbl_noptien` (`ID`, `SoHD`, `ThoigianNop`, `SoTienNop`, `NgayTraLai1`, `GhiChu`, `TrangThaiDongTien`, `maNV_nhap`, `maNV_duyet`, `fullname_NVnhap`, `fullname_NVduyet`) VALUES
(2, '000000001', '2026-04-03 02:54:49', 10000000, '2026-06-01', '', 'Đã chuyển tới A', 'NV0002', '', 'Phạm Quang Nhiêu', ''),
(9, '000201026/01B01', '2026-03-15 17:00:00', 251920000, '2026-06-14', '', 'Chuyển A & AG', 'NV0002', '', 'Phạm Quang Nhiêu', ''),
(16, '000201026/01B01', '2026-03-27 17:00:00', 241000000, '2026-06-26', '', 'Đã chuyển tới A', 'NV0002', '', 'Phạm Quang Nhiêu', ''),
(18, '000000001', '2026-04-02 17:00:00', 11000000, '2026-07-02', '', 'Đã chuyển tới A', 'NV0002', '', 'Phạm Quang Nhiêu', ''),
(19, '000000002', '2026-04-02 17:00:00', 70000000, '2026-07-02', '', 'Đã chuyển tới A', 'NV0002', '', 'Phạm Quang Nhiêu', ''),
(20, '000000002', '2026-04-10 17:00:00', 61200000, '2026-07-10', '', 'Đã chuyển tới A', 'NV0002', '', 'Phạm Quang Nhiêu', ''),
(21, '000000002', '2026-04-10 17:00:00', 61200000, '2026-07-10', '', 'Đã chuyển tới A', 'NV0002', '', 'Phạm Quang Nhiêu', '');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_noptiensangag`
--

CREATE TABLE `tbl_noptiensangag` (
  `ID` int(10) UNSIGNED NOT NULL,
  `id_tblNoptien` int(10) UNSIGNED NOT NULL COMMENT 'Id của tbl nộp tiền hoặc id của tbl_ttchung_a tùy theo cột Nguonchuyen',
  `id_tblThongtinAG` int(10) UNSIGNED NOT NULL COMMENT 'id của tbl thông tin chung AG',
  `SoHD` varchar(30) NOT NULL,
  `SotienChuyen` double UNSIGNED NOT NULL,
  `Ghichu` varchar(200) NOT NULL COMMENT 'Là mã nhân viên nhập dữ liệu',
  `NguonChuyen` varchar(30) NOT NULL COMMENT 'Nguồn chuyển từ tbl_noptien hoặc từ tbl_ttchung_a.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tbl_noptiensangag`
--

INSERT INTO `tbl_noptiensangag` (`ID`, `id_tblNoptien`, `id_tblThongtinAG`, `SoHD`, `SotienChuyen`, `Ghichu`, `NguonChuyen`) VALUES
(16, 20, 23, '000000001', 10000000, 'NV0002', 'tbl_ttchungA'),
(17, 19, 24, '000000001', 11000000, 'NV0002', 'tbl_ttchungA'),
(18, 20, 24, '000000001', 10000000, 'NV0002', 'tbl_ttchungA'),
(19, 23, 25, '000000002', 70000000, 'NV0002', 'tbl_ttchungA'),
(20, 1, 26, '000000003', 10000000, 'NV0002', 'tbl_ttchungA'),
(21, 7, 26, '000000003', 241000000, 'NV0002', 'tbl_ttchungA'),
(22, 19, 27, '000000001', 11000000, 'NV0002', 'tbl_ttchungA'),
(23, 20, 27, '000000001', 10000000, 'NV0002', 'tbl_ttchungA'),
(24, 24, 28, '000000002', 4480000, 'NV0002', 'tbl_ttchungA'),
(25, 26, 28, '000000002', 61200000, 'NV0002', 'tbl_ttchungA'),
(26, 27, 28, '000000002', 61200000, 'NV0002', 'tbl_ttchungA');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_permission`
--

CREATE TABLE `tbl_permission` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `maNV` varchar(30) NOT NULL,
  `permission` varchar(30) NOT NULL,
  `note` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_thaydoi_tt_hd`
--

CREATE TABLE `tbl_thaydoi_tt_hd` (
  `ID` int(10) UNSIGNED NOT NULL,
  `SoHD` varchar(30) NOT NULL,
  `TenCot` varchar(50) NOT NULL,
  `GiaTriCu` varchar(250) NOT NULL,
  `GiaTriMoi` varchar(250) NOT NULL,
  `NgayDoiTT` date NOT NULL COMMENT 'TT trong bảng có hiệu lực đến ngày này',
  `MaNV` varchar(30) NOT NULL COMMENT 'Mã NV thay đổi nội dung TT'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_theodoi`
--

CREATE TABLE `tbl_theodoi` (
  `ID` int(10) UNSIGNED NOT NULL,
  `IDlogon` varchar(30) NOT NULL,
  `Action` varchar(300) NOT NULL,
  `AtTime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tbl_theodoi`
--

INSERT INTO `tbl_theodoi` (`ID`, `IDlogon`, `Action`, `AtTime`) VALUES
(24, 'NV0002', 'Nhập bảng A, ID: 4, số tiền 241000000', '2026-03-30 17:00:00'),
(25, 'NV0002', 'Nhập bảng nộp tiền, KH: 13, ngày 2026-03-27, số tiền 241000000', '2026-03-30 17:00:00'),
(26, 'NV0002', 'Nhập bảng A, ID: 5, số tiền 241000000', '2026-03-30 17:00:00'),
(27, 'NV0002', 'Nhập bảng nộp tiền, KH: 14, ngày 2026-03-25, số tiền 241000000', '2026-03-30 17:00:00'),
(28, 'NV0002', 'Nhập bảng A, ID: 6, số tiền 241000000', '2026-03-30 17:00:00'),
(29, 'NV0002', 'Nhập bảng nộp tiền, KH: 15, ngày 2026-03-28, số tiền 241000000', '2026-03-30 17:00:00'),
(30, 'NV0002', 'Nhập bảng A, ID: 7, số tiền 241000000', '2026-03-30 17:00:00'),
(31, 'NV0002', 'Nhập bảng nộp tiền, KH: 16, ngày 2026-03-28, số tiền 241000000', '2026-03-30 17:00:00'),
(32, 'NV0002', 'Nhập bảng noptiensangag, ID: 8, số tiền 241000000', '2026-03-30 17:00:00'),
(33, 'NV0002', 'Nhập bảng A, ID: 8, số tiền 0', '2026-03-30 17:00:00'),
(34, 'NV0002', 'Nhập bảng noptiensangag, ID: 9, số tiền 0', '2026-03-30 17:00:00'),
(35, 'NV0002', 'Nhập bảng A, ID: 9, số tiền 0', '2026-03-30 17:00:00'),
(36, 'NV0002', 'Nhập bảng noptiensangag, ID: 10, số tiền 0', '2026-03-30 17:00:00'),
(37, 'NV0002', 'Nhập bảng A, ID: 10, số tiền 0', '2026-03-30 17:00:00'),
(38, 'NV0002', 'Nhập bảng noptiensangag, ID: 11, số tiền 0', '2026-03-30 17:00:00'),
(39, 'NV0002', 'Nhập bảng A, ID: 11, số tiền 0', '2026-03-30 17:00:00'),
(40, 'NV0002', 'Nhập bảng noptiensangag, ID: 12, số tiền 10000000', '2026-03-30 17:00:00'),
(41, 'NV0002', 'Nhập bảng noptiensangag, ID: 13, số tiền 241000000', '2026-03-30 17:00:00'),
(42, 'NV0002', 'Nhập bảng A, ID: 12, số tiền 0', '2026-03-30 17:00:00'),
(43, 'NV0002', 'Nhập bảng A, ID: 13, số tiền 0', '2026-03-30 17:00:00'),
(44, 'NV0002', 'Nhập bảng A, ID: 14, số tiền 0', '2026-03-30 17:00:00'),
(45, 'NV0002', 'Nhập bảng A, ID: 15, số tiền 0', '2026-03-30 17:00:00'),
(46, 'NV0002', 'Nhập bảng A, ID: 16, số tiền 9080000', '2026-03-30 17:00:00'),
(47, 'NV0002', 'Nhập bảng nộp tiền, KH: , ngày 2026-04-02, số tiền 11000000', '2026-04-01 17:00:00'),
(48, 'NV0002', 'Nhập bảng A, ID: 19, số tiền 11000000', '2026-04-02 17:00:00'),
(49, 'NV0002', 'Nhập bảng nộp tiền va tbl_TTchung_A, KH: , ngày 2026-04-03, số tiền 11000000', '2026-04-02 17:00:00'),
(50, 'NV0002', 'Chuyển tiền từ A lên AG,ID bảng A: 19 số tiền: 11000000, ID bảng A: 20 số tiền: 10000000, , giá trị dư kết chuyển A: 840000', '2026-04-02 17:00:00'),
(51, 'NV0002', 'Nhập bảng A, ID: 23, số tiền 70000000', '2026-04-02 17:00:00'),
(52, 'NV0002', 'Nhập bảng nộp tiền va tbl_TTchung_A, KH: , ngày 2026-04-03, số tiền 70000000', '2026-04-02 17:00:00'),
(53, 'NV0002', 'Chuyển tiền từ A lên AG,ID bảng A: 23 số tiền: 70000000, , giá trị dư kết chuyển A: 4480000', '2026-04-02 17:00:00'),
(54, 'NV0002', 'Chuyển tiền từ A lên AG,ID bảng A: 1 số tiền: 10000000, ID bảng A: 7 số tiền: 241000000, , giá trị dư kết chuyển A: 9080000', '2026-04-05 17:00:00'),
(55, 'NV0002', 'Nhập bảng A, ID: 26, số tiền 61200000', '2026-04-10 17:00:00'),
(56, 'NV0002', 'Nhập bảng nộp tiền, KH: 20, ngày 2026-04-11, số tiền 61200000', '2026-04-10 17:00:00'),
(57, 'NV0002', 'Nhập bảng A, ID: 27, số tiền 61200000', '2026-04-10 17:00:00'),
(58, 'NV0002', 'Nhập bảng nộp tiền, KH: 21, ngày 2026-04-11, số tiền 61200000', '2026-04-10 17:00:00'),
(59, 'NV0002', 'Chuyển tiền từ A lên AG,ID bảng A: 19 số tiền: 11000000, ID bảng A: 20 số tiền: 10000000, , giá trị dư kết chuyển A: 840000', '2026-04-10 17:00:00'),
(60, 'NV0002', 'Chuyển tiền từ A lên AG,ID bảng A: 24 số tiền: 4480000, ID bảng A: 26 số tiền: 61200000, ID bảng A: 27 số tiền: 61200000, , giá trị dư kết chuyển A: 320000', '2026-04-10 17:00:00'),
(61, 'NV0002', 'Tạo hồ sơ chờ ID=5 - HSS=0212199804/00026 - KB=2004026H150300 - KH=KH00005 - NVtv=NV0002', '2026-05-02 08:49:09'),
(62, 'NV0002', 'Tạo HĐ chờ ID=7 - KB=24042026H120300 - HSS=1208200104/00026/04/010 - KH=KH00007 (Hoàng Ngọc Lan) - NV_tv=C1-00002 - TùyChọn=B', '2026-05-03 10:01:56'),
(63, 'NV0002', 'Tạo HĐ chờ ID=8 - KB=04052026H130000 - HSS=2310200104/00026/04/011 - KH=KH00008 (Trần Phương Mai) - NV_tv=C1-00003 - TùyChọn=B', '2026-05-07 06:29:25');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_thuake`
--

CREATE TABLE `tbl_thuake` (
  `ID` int(10) UNSIGNED NOT NULL,
  `SoHD` varchar(30) DEFAULT NULL,
  `Sotaikhoan` varchar(20) NOT NULL,
  `TenNganHang` varchar(40) NOT NULL,
  `HotenChuTK` varchar(100) NOT NULL,
  `HoTen` varchar(100) NOT NULL COMMENT 'Họ tên cá nhân hoặc người đại diện doanh nghiệp - nếu doanh nghiệp là người thừa ké',
  `MQH_chuHD` varchar(30) NOT NULL COMMENT 'Mối quan hệ với chủ hợp đồng',
  `Ngaysinh` date DEFAULT NULL COMMENT 'Ngày sinh người liên hệ',
  `Gioitinh` varchar(10) NOT NULL COMMENT 'Giới tính người liên hệ',
  `Email` varchar(100) NOT NULL,
  `DiaChiHientai` varchar(500) NOT NULL COMMENT 'Nơi ở hiện nay của người liên hệ',
  `DCThuongtru` varchar(500) NOT NULL COMMENT 'Địa chỉ thường trú NLH',
  `SoDT` varchar(15) NOT NULL COMMENT 'Số điện thoại NLH',
  `Dantoc` varchar(20) NOT NULL,
  `Quoctich` varchar(20) NOT NULL,
  `SoCCCD` varchar(20) NOT NULL,
  `NgaycapCCCD` date DEFAULT NULL,
  `GhiChu` varchar(200) NOT NULL,
  `maNV_nhap` varchar(30) NOT NULL,
  `fullname_NVnhap` varchar(150) NOT NULL,
  `TenDN` varchar(100) DEFAULT NULL COMMENT 'Tên Doanh nghiệp thừa kế, bỏ trống nếu là cá nhân',
  `MST` varchar(20) DEFAULT NULL COMMENT 'Mã số thuế',
  `DoanhNghiepThuaKe` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1: Doanh nghiệp thừa kế HĐ 0: Cá nhân thừa kề HĐ',
  `PhantramThuhuong` decimal(10,4) UNSIGNED NOT NULL COMMENT 'Phần trăm thụ hưởng'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tbl_thuake`
--

INSERT INTO `tbl_thuake` (`ID`, `SoHD`, `Sotaikhoan`, `TenNganHang`, `HotenChuTK`, `HoTen`, `MQH_chuHD`, `Ngaysinh`, `Gioitinh`, `Email`, `DiaChiHientai`, `DCThuongtru`, `SoDT`, `Dantoc`, `Quoctich`, `SoCCCD`, `NgaycapCCCD`, `GhiChu`, `maNV_nhap`, `fullname_NVnhap`, `TenDN`, `MST`, `DoanhNghiepThuaKe`, `PhantramThuhuong`) VALUES
(1, NULL, '1234567890', 'Vietcombank', 'Hoàng Ngọc Lan', 'Hoàng Ngọc Anh', 'Anh chị em ruột', NULL, '', '', '', '', '', '', 'Việt Nam', '', NULL, '', 'NV0002', 'Phạm Quang Nhiêu', NULL, NULL, 0, '100.0000');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_ttchunga_tcox`
--

CREATE TABLE `tbl_ttchunga_tcox` (
  `ID` int(10) UNSIGNED NOT NULL,
  `SoHD` varchar(30) NOT NULL,
  `SoTCOx` smallint(5) UNSIGNED NOT NULL COMMENT 'Là số DVTC mà KH đồng ý chuyển TCOx để nhận giá trị tri ân',
  `NgayChuyenTCOx` date NOT NULL,
  `MaNVChuyenTCOx` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_ttchung_a`
--

CREATE TABLE `tbl_ttchung_a` (
  `ID` int(10) UNSIGNED NOT NULL,
  `SoHD` varchar(30) NOT NULL,
  `SotienChuyen` double UNSIGNED NOT NULL,
  `ThoiGianTinhLaiA` date NOT NULL COMMENT 'Thời gian bắt đầu tính lãi A',
  `TrangthaiVonGop` varchar(50) NOT NULL COMMENT 'Dư sau kết chuyển\r\nĐã chuyển AG\r\nNộp tiền lần 1\r\nChuyển lãi từ A',
  `TrangthaiTinhLai` varchar(30) NOT NULL COMMENT 'Continue: Tiếp tục tính lãi A\r\nClosed: Đóng trạng thái tính lãi để chuyển AG',
  `id_TblNoptien` int(10) UNSIGNED NOT NULL,
  `NgàyChuyenAG` date DEFAULT NULL,
  `NgayTinhLaiCuoi` date DEFAULT NULL COMMENT 'Ngày tính lãi gần nhất',
  `MaNV` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tbl_ttchung_a`
--

INSERT INTO `tbl_ttchung_a` (`ID`, `SoHD`, `SotienChuyen`, `ThoiGianTinhLaiA`, `TrangthaiVonGop`, `TrangthaiTinhLai`, `id_TblNoptien`, `NgàyChuyenAG`, `NgayTinhLaiCuoi`, `MaNV`) VALUES
(19, '000000001', 11000000, '2026-07-02', 'Nộp tiền lần 1', 'Continue', 18, NULL, NULL, 'NV0002'),
(20, '000000001', 10000000, '2026-06-01', 'Nộp tiền lần 1', 'Continue', 2, NULL, NULL, 'NV0002'),
(23, '000000002', 70000000, '2026-07-02', 'Nộp tiền lần 1', 'Closed', 19, '2026-07-02', '2026-07-02', 'NV0002'),
(24, '000000002', 4480000, '2026-07-02', 'Đã chuyển AG', 'Closed', 19, '2026-07-10', '2026-07-02', 'NV0002'),
(26, '000000002', 61200000, '2026-07-10', 'Đã chuyển AG', 'Closed', 20, '2026-07-10', '2026-07-10', 'NV0002'),
(27, '000000002', 61200000, '2026-07-10', 'Đã chuyển AG', 'Closed', 21, '2026-07-10', '2026-07-10', 'NV0002'),
(29, '000000002', 320000, '2026-07-10', 'Dư sau kết chuyển', 'Continue', 21, NULL, NULL, 'NV0002');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_ttchung_ag`
--

CREATE TABLE `tbl_ttchung_ag` (
  `ID` int(10) UNSIGNED NOT NULL,
  `SoHD` varchar(30) NOT NULL,
  `NgayxacminhAG` date NOT NULL COMMENT 'Ngày nhân viên làm việc với chủ HĐ để xác nhận chuyển AG',
  `NgaychuyenAG` date NOT NULL COMMENT 'Ngày chính thức chuyển AG',
  `SoDVTC` smallint(5) UNSIGNED NOT NULL,
  `id_logon` varchar(30) NOT NULL COMMENT 'Xác minh người chuyển AG cho hợp đồng',
  `NgayChuyenTCOx` date DEFAULT NULL COMMENT 'Ngày xác nhận chuyển TCOx để nhận quyền lợi tri ân sau 3 năm kể từ ngày này.',
  `MaNVChuyenTCOx` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tbl_ttchung_ag`
--

INSERT INTO `tbl_ttchung_ag` (`ID`, `SoHD`, `NgayxacminhAG`, `NgaychuyenAG`, `SoDVTC`, `id_logon`, `NgayChuyenTCOx`, `MaNVChuyenTCOx`) VALUES
(25, '000000002', '2026-04-03', '2026-07-02', 2, '0', '2026-04-03', 'NV0002'),
(28, '000000002', '2026-04-11', '2026-07-10', 6, 'NV0002', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_ungtien`
--

CREATE TABLE `tbl_ungtien` (
  `ID` int(10) UNSIGNED NOT NULL,
  `SoHD` varchar(30) NOT NULL,
  `ThoigianUng` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `SoTienUng` double NOT NULL,
  `NgayTraLai1` date NOT NULL,
  `GhiChu` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agent`
--
ALTER TABLE `agent`
  ADD PRIMARY KEY (`agent_id`),
  ADD UNIQUE KEY `uq_id_number` (`id_number`),
  ADD UNIQUE KEY `uq_agent_code` (`agent_code`),
  ADD UNIQUE KEY `Khachhang_ID` (`Khachhang_ID`),
  ADD KEY `idx_sponsor` (`sponsor_agent_id`),
  ADD KEY `idx_rank` (`current_rank_id`);

--
-- Indexes for table `agent_hierarchy`
--
ALTER TABLE `agent_hierarchy`
  ADD PRIMARY KEY (`hierarchy_id`),
  ADD UNIQUE KEY `uq_anc_desc` (`ancestor_id`,`descendant_id`),
  ADD KEY `idx_descendant` (`descendant_id`),
  ADD KEY `fk_hierarchy_rank` (`senior_rank_at_insert`);

--
-- Indexes for table `commission_direct_rate`
--
ALTER TABLE `commission_direct_rate`
  ADD PRIMARY KEY (`rate_id`),
  ADD KEY `idx_rank_type` (`rank_id`,`effective_from`);

--
-- Indexes for table `commission_override_rate`
--
ALTER TABLE `commission_override_rate`
  ADD PRIMARY KEY (`override_id`),
  ADD UNIQUE KEY `uq_rank_pair` (`senior_rank_id`,`junior_rank_id`,`effective_from`),
  ADD KEY `junior_rank_id` (`junior_rank_id`);

--
-- Indexes for table `commission_payout`
--
ALTER TABLE `commission_payout`
  ADD PRIMARY KEY (`payout_id`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `fk_comm_noptien` (`id_tbl_noptien`),
  ADD KEY `fk_comm_hopdong` (`SoHD`);

--
-- Indexes for table `commission_transaction`
--
ALTER TABLE `commission_transaction`
  ADD PRIMARY KEY (`txn_id`),
  ADD KEY `idx_payment` (`noptien_id`),
  ADD KEY `idx_agent` (`beneficiary_agent_id`,`status`),
  ADD KEY `idx_status` (`status`,`calculated_at`),
  ADD KEY `payout_id` (`payout_id`),
  ADD KEY `idx_soHD` (`soHD`);

--
-- Indexes for table `promotion_bonus`
--
ALTER TABLE `promotion_bonus`
  ADD PRIMARY KEY (`bonus_id`),
  ADD KEY `idx_agent_status` (`agent_id`,`status`),
  ADD KEY `fk_pb_from_rank` (`from_rank_id`),
  ADD KEY `fk_pb_to_rank` (`to_rank_id`);

--
-- Indexes for table `rank_config`
--
ALTER TABLE `rank_config`
  ADD PRIMARY KEY (`rank_id`),
  ADD UNIQUE KEY `uq_rank_code` (`rank_code`);

--
-- Indexes for table `rank_upgrade_condition`
--
ALTER TABLE `rank_upgrade_condition`
  ADD PRIMARY KEY (`condition_id`),
  ADD KEY `idx_from_rank_effective` (`from_rank_id`,`effective_date`),
  ADD KEY `fk_ruc_to` (`to_rank_id`);

--
-- Indexes for table `tbl_hopdong_file`
--
ALTER TABLE `tbl_hopdong_file`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `fk_hopdong_file` (`SoHD`);

--
-- Indexes for table `tbl_hopdong_ttchung`
--
ALTER TABLE `tbl_hopdong_ttchung`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Iv` (`Iv`),
  ADD UNIQUE KEY `SoHD` (`SoHD`),
  ADD KEY `fk_hopdong_loai` (`LoaiHD`),
  ADD KEY `fk_hopdong_agent` (`agent_id_banhang`),
  ADD KEY `fk_hopdong_khach` (`Khachhang_ID`);

--
-- Indexes for table `tbl_khachhang`
--
ALTER TABLE `tbl_khachhang`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `MaKH` (`MaKH`);

--
-- Indexes for table `tbl_loai_hopdong`
--
ALTER TABLE `tbl_loai_hopdong`
  ADD PRIMARY KEY (`ma_loai`);

--
-- Indexes for table `tbl_login`
--
ALTER TABLE `tbl_login`
  ADD PRIMARY KEY (`maNV`);

--
-- Indexes for table `tbl_noptien`
--
ALTER TABLE `tbl_noptien`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `idx_soHD` (`SoHD`),
  ADD KEY `idx_thoigian` (`ThoigianNop`);

--
-- Indexes for table `tbl_noptiensangag`
--
ALTER TABLE `tbl_noptiensangag`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbl_permission`
--
ALTER TABLE `tbl_permission`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_permission_login` (`maNV`);

--
-- Indexes for table `tbl_thaydoi_tt_hd`
--
ALTER TABLE `tbl_thaydoi_tt_hd`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `fk_hopdong_thaydoi` (`SoHD`),
  ADD KEY `fk_thaydoi_login` (`MaNV`);

--
-- Indexes for table `tbl_theodoi`
--
ALTER TABLE `tbl_theodoi`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbl_thuake`
--
ALTER TABLE `tbl_thuake`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `SoHD` (`SoHD`);

--
-- Indexes for table `tbl_ttchunga_tcox`
--
ALTER TABLE `tbl_ttchunga_tcox`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `fk_ttchunga_hopdong` (`SoHD`),
  ADD KEY `fk_ttchungatcox_login` (`MaNVChuyenTCOx`);

--
-- Indexes for table `tbl_ttchung_a`
--
ALTER TABLE `tbl_ttchung_a`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `fk_a_hopdong` (`SoHD`),
  ADD KEY `fk_a_login` (`MaNV`);

--
-- Indexes for table `tbl_ttchung_ag`
--
ALTER TABLE `tbl_ttchung_ag`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `fk_ag_hopdong` (`SoHD`),
  ADD KEY `fk_ttag_login` (`MaNVChuyenTCOx`);

--
-- Indexes for table `tbl_ungtien`
--
ALTER TABLE `tbl_ungtien`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agent`
--
ALTER TABLE `agent`
  MODIFY `agent_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `agent_hierarchy`
--
ALTER TABLE `agent_hierarchy`
  MODIFY `hierarchy_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `commission_direct_rate`
--
ALTER TABLE `commission_direct_rate`
  MODIFY `rate_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `commission_override_rate`
--
ALTER TABLE `commission_override_rate`
  MODIFY `override_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `commission_payout`
--
ALTER TABLE `commission_payout`
  MODIFY `payout_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commission_transaction`
--
ALTER TABLE `commission_transaction`
  MODIFY `txn_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotion_bonus`
--
ALTER TABLE `promotion_bonus`
  MODIFY `bonus_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rank_config`
--
ALTER TABLE `rank_config`
  MODIFY `rank_id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rank_upgrade_condition`
--
ALTER TABLE `rank_upgrade_condition`
  MODIFY `condition_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_hopdong_file`
--
ALTER TABLE `tbl_hopdong_file`
  MODIFY `ID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_hopdong_ttchung`
--
ALTER TABLE `tbl_hopdong_ttchung`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_khachhang`
--
ALTER TABLE `tbl_khachhang`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tbl_noptien`
--
ALTER TABLE `tbl_noptien`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tbl_noptiensangag`
--
ALTER TABLE `tbl_noptiensangag`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `tbl_permission`
--
ALTER TABLE `tbl_permission`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_thaydoi_tt_hd`
--
ALTER TABLE `tbl_thaydoi_tt_hd`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_theodoi`
--
ALTER TABLE `tbl_theodoi`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `tbl_thuake`
--
ALTER TABLE `tbl_thuake`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_ttchunga_tcox`
--
ALTER TABLE `tbl_ttchunga_tcox`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_ttchung_a`
--
ALTER TABLE `tbl_ttchung_a`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `tbl_ttchung_ag`
--
ALTER TABLE `tbl_ttchung_ag`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `tbl_ungtien`
--
ALTER TABLE `tbl_ungtien`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agent`
--
ALTER TABLE `agent`
  ADD CONSTRAINT `fk_agent_khachhang` FOREIGN KEY (`Khachhang_ID`) REFERENCES `tbl_khachhang` (`ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_agent_rank` FOREIGN KEY (`current_rank_id`) REFERENCES `rank_config` (`rank_id`),
  ADD CONSTRAINT `fk_agent_sponsor` FOREIGN KEY (`sponsor_agent_id`) REFERENCES `agent` (`agent_id`);

--
-- Constraints for table `agent_hierarchy`
--
ALTER TABLE `agent_hierarchy`
  ADD CONSTRAINT `fk_hier_ancestor` FOREIGN KEY (`ancestor_id`) REFERENCES `agent` (`agent_id`),
  ADD CONSTRAINT `fk_hier_descendant` FOREIGN KEY (`descendant_id`) REFERENCES `agent` (`agent_id`),
  ADD CONSTRAINT `fk_hierarchy_rank` FOREIGN KEY (`senior_rank_at_insert`) REFERENCES `rank_config` (`rank_id`) ON UPDATE CASCADE;

--
-- Constraints for table `commission_direct_rate`
--
ALTER TABLE `commission_direct_rate`
  ADD CONSTRAINT `fk_crule_rank` FOREIGN KEY (`rank_id`) REFERENCES `rank_config` (`rank_id`);

--
-- Constraints for table `commission_override_rate`
--
ALTER TABLE `commission_override_rate`
  ADD CONSTRAINT `commission_override_rate_ibfk_1` FOREIGN KEY (`senior_rank_id`) REFERENCES `rank_config` (`rank_id`),
  ADD CONSTRAINT `commission_override_rate_ibfk_2` FOREIGN KEY (`junior_rank_id`) REFERENCES `rank_config` (`rank_id`);

--
-- Constraints for table `commission_payout`
--
ALTER TABLE `commission_payout`
  ADD CONSTRAINT `commission_payout_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `agent` (`agent_id`),
  ADD CONSTRAINT `fk_comm_hopdong` FOREIGN KEY (`SoHD`) REFERENCES `tbl_hopdong_ttchung` (`SoHD`),
  ADD CONSTRAINT `fk_comm_noptien` FOREIGN KEY (`id_tbl_noptien`) REFERENCES `tbl_noptien` (`ID`);

--
-- Constraints for table `commission_transaction`
--
ALTER TABLE `commission_transaction`
  ADD CONSTRAINT `commission_transaction_ibfk_1` FOREIGN KEY (`payout_id`) REFERENCES `commission_payout` (`payout_id`),
  ADD CONSTRAINT `fk_commission_hopdong` FOREIGN KEY (`soHD`) REFERENCES `tbl_hopdong_ttchung` (`SoHD`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ctxn_agent` FOREIGN KEY (`beneficiary_agent_id`) REFERENCES `agent` (`agent_id`),
  ADD CONSTRAINT `fk_ctxn_payment` FOREIGN KEY (`noptien_id`) REFERENCES `tbl_noptien` (`ID`);

--
-- Constraints for table `promotion_bonus`
--
ALTER TABLE `promotion_bonus`
  ADD CONSTRAINT `fk_pb_agent` FOREIGN KEY (`agent_id`) REFERENCES `agent` (`agent_id`),
  ADD CONSTRAINT `fk_pb_from_rank` FOREIGN KEY (`from_rank_id`) REFERENCES `rank_config` (`rank_id`),
  ADD CONSTRAINT `fk_pb_to_rank` FOREIGN KEY (`to_rank_id`) REFERENCES `rank_config` (`rank_id`);

--
-- Constraints for table `rank_upgrade_condition`
--
ALTER TABLE `rank_upgrade_condition`
  ADD CONSTRAINT `fk_ruc_from` FOREIGN KEY (`from_rank_id`) REFERENCES `rank_config` (`rank_id`),
  ADD CONSTRAINT `fk_ruc_to` FOREIGN KEY (`to_rank_id`) REFERENCES `rank_config` (`rank_id`);

--
-- Constraints for table `tbl_hopdong_file`
--
ALTER TABLE `tbl_hopdong_file`
  ADD CONSTRAINT `fk_hopdong_file` FOREIGN KEY (`SoHD`) REFERENCES `tbl_hopdong_ttchung` (`SoHD`);

--
-- Constraints for table `tbl_hopdong_ttchung`
--
ALTER TABLE `tbl_hopdong_ttchung`
  ADD CONSTRAINT `fk_hopdong_agent` FOREIGN KEY (`agent_id_banhang`) REFERENCES `agent` (`agent_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hopdong_khach` FOREIGN KEY (`Khachhang_ID`) REFERENCES `tbl_khachhang` (`ID`),
  ADD CONSTRAINT `fk_hopdong_loai` FOREIGN KEY (`LoaiHD`) REFERENCES `tbl_loai_hopdong` (`ma_loai`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_noptien`
--
ALTER TABLE `tbl_noptien`
  ADD CONSTRAINT `fk_noptien_hopdong` FOREIGN KEY (`SoHD`) REFERENCES `tbl_hopdong_ttchung` (`SoHD`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_permission`
--
ALTER TABLE `tbl_permission`
  ADD CONSTRAINT `fk_permission_login` FOREIGN KEY (`maNV`) REFERENCES `tbl_login` (`maNV`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_thaydoi_tt_hd`
--
ALTER TABLE `tbl_thaydoi_tt_hd`
  ADD CONSTRAINT `fk_hopdong_thaydoi` FOREIGN KEY (`SoHD`) REFERENCES `tbl_hopdong_ttchung` (`SoHD`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_thaydoi_login` FOREIGN KEY (`MaNV`) REFERENCES `tbl_login` (`maNV`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_thuake`
--
ALTER TABLE `tbl_thuake`
  ADD CONSTRAINT `FK_thuake_hopdong` FOREIGN KEY (`SoHD`) REFERENCES `tbl_hopdong_ttchung` (`SoHD`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_ttchunga_tcox`
--
ALTER TABLE `tbl_ttchunga_tcox`
  ADD CONSTRAINT `fk_ttchunga_hopdong` FOREIGN KEY (`SoHD`) REFERENCES `tbl_hopdong_ttchung` (`SoHD`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ttchungatcox_login` FOREIGN KEY (`MaNVChuyenTCOx`) REFERENCES `tbl_login` (`maNV`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_ttchung_a`
--
ALTER TABLE `tbl_ttchung_a`
  ADD CONSTRAINT `fk_a_hopdong` FOREIGN KEY (`SoHD`) REFERENCES `tbl_hopdong_ttchung` (`SoHD`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_a_login` FOREIGN KEY (`MaNV`) REFERENCES `tbl_login` (`maNV`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_ttchung_ag`
--
ALTER TABLE `tbl_ttchung_ag`
  ADD CONSTRAINT `fk_ag_hopdong` FOREIGN KEY (`SoHD`) REFERENCES `tbl_hopdong_ttchung` (`SoHD`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ttag_login` FOREIGN KEY (`MaNVChuyenTCOx`) REFERENCES `tbl_login` (`maNV`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
