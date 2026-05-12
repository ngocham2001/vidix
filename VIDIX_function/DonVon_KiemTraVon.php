<?php
//Hàm để kiểm tra số tiền vốn góp từ A có đủ để chuyển AG không (với tổng tiền của cột TrangthaiVonGop <>'Đã chuyển AG'>=số tiền tín chỉ AG)
function TongTienA($soHD_phu,$conn_phu){
	$sqlselectF = "SELECT sum(sotienchuyen) as tongtienTuA FROM `tbl_ttchung_a` WHERE soHD = '".$soHD_phu."' and TrangthaiVonGop !='Đã chuyển AG'";
	$resultselectF = mysqli_query($conn_phu,$sqlselectF) or die(mysqli_error($conn_phu));
	$setselectF = mysqli_fetch_array($resultselectF,MYSQLI_ASSOC);
	$tongVon = $setselectF['tongtienTuA'];
	return $tongVon;
} 
function ChenDuLienTblAG($conn_phu, $soHD_phu, $NgayxacminhAG_phu, $ngayChuyenAG_phu,$soDVTC_phu, $username_phu,$currentdate_phu){
	$sqlinsert = "INSERT INTO `tbl_ttchung_ag`(`ID`, `SoHD`, `NgayxacminhAG`, `NgaychuyenAG`, `SoDVTC`, `id_logon`, `NgayChuyenTCOx`, `MaNVChuyenTCOx`) VALUES (NULL,'".$soHD_phu."','".$NgayxacminhAG_phu."','".$ngayChuyenAG_phu."',".$soDVTC_phu.",'".$username_phu."','".$currentdate_phu."','".$username_phu."')";
	if (mysqli_query($conn_phu,$sqlinsert)) return mysqli_insert_id($conn_phu);
	else mysqli_error($conn_phu);
	$sqlinsert = "INSERT INTO `tbl_theodoi`(`ID`, `IDlogon`, `Action`, `AtTime`) VALUES (NULL,'".$_SESSION['user_info']['logon_id']."','Nhập bảng A, ID: ".$id_in_tblAG.", số DVTC: ".$soDVTC_phu."','".$currentdate_phu."')";
	mysqli_query($conn_phu,$sqlinsert) or die ("Không thể kết nối dữ liệu! ".mysqli_error($conn_phu));
}

function ChenDuLienTblA($conn_phu, $soHD_phu, $soTienChuyen_phu, $ngayTinhLaiA1_phu,$TrangThai_VonGop_phu,$ngaytinhLaiCuoi_phu,$TrangThai_TinhLai_phu,$id_TblNoptien_phu,$ngayChuyenAG_phu, $username_phu,$currentdate_phu){
	$sqlinsert = "INSERT INTO `tbl_ttchung_a`(`ID`, `SoHD`, `SotienChuyen`, `ThoiGianTinhLaiA`, `TrangthaiVonGop`, `TrangthaiTinhLai`, `id_TblNoptien`, `NgàyChuyenAG`, `NgayTinhLaiCuoi`, `MaNV`) VALUES (NULL,'".$soHD_phu."',".$soTienChuyen_phu.",'".$ngayTinhLaiA1_phu."','".$TrangThai_VonGop_phu."','".$TrangThai_TinhLai_phu."',".$id_TblNoptien_phu.",";
	
	$sqlinsert .= empty($ngayChuyenAG_phu)?"NULL,":"'".$ngayChuyenAG_phu."',";
	$sqlinsert .= empty($ngaytinhLaiCuoi_phu)?"NULL,":"'".$ngaytinhLaiCuoi_phu."',";
	$sqlinsert .= "'".$username_phu."')";
	mysqli_query($conn_phu,$sqlinsert) or die ("Không thể kết nối! ". mysqli_error($conn_phu));
	$id_in_tblA = mysqli_insert_id($conn_phu);
	$sqlinsert = "INSERT INTO `tbl_theodoi`(`ID`, `IDlogon`, `Action`, `AtTime`) VALUES (NULL,'".$_SESSION['user_info']['logon_id']."','Nhập bảng A, ID: ".$id_in_tblA.", số tiền ".$soTienChuyen_phu."','".$currentdate_phu."')";
	mysqli_query($conn_phu,$sqlinsert) or die ("Không thể kết nối dữ liệu! ".mysqli_error($conn_phu));
}

function ChenDuLienTbl_noptien($conn_phu, $soHD_phu, $ThoigianNop_phu, $soTiennop_phu,$ngaytralai1_phu,$GhiChu_phu,$TrangThai_DongTien_phu,$tenNVNhap_phu, $username_phu,$currentdate_phu){
	$sqlinsert = "INSERT INTO `tbl_noptien`(`ID`, `SoHD`, `ThoigianNop`, `SoTienNop`, `NgayTraLai1`, `GhiChu`, `TrangThaiDongTien`, `maNV_nhap`, `fullname_NVnhap`) VALUES (NULL,'".$soHD_phu."','".$ThoigianNop_phu."',".$soTiennop_phu.",'".$ngaytralai1_phu."','".$GhiChu_phu."','".$TrangThai_DongTien_phu."','".$username_phu."','".$tenNVNhap_phu."')";
	//echo $sqlinsert;
	if(mysqli_query($conn_phu,$sqlinsert)) return mysqli_insert_id($conn_phu); 
	else mysqli_error($conn_phu);
	$sqlinsert = "INSERT INTO `tbl_theodoi`(`ID`, `IDlogon`, `Action`, `AtTime`) VALUES (NULL,'".$_SESSION['user_info']['logon_id']."','Nhập bảng nộp tiền, ID: ".$id_in_tblNoptien.", ngày ".$ThoigianNop_phu.", số tiền ".$soTiennop_phu."','".$currentdate_phu."')";
	mysqli_query($conn_phu,$sqlinsert) or die ("Không thể kết nối dữ liệu! ".mysqli_error($conn_phu));
}

function ChenDuLieuTbl_noptiensangAG($conn_phu,$id_tblNoptien_phu,$id_tblThongtinAG_phu,$soHD_phu,$soTienChuyen_phu,$username_phu,$currentdate_phu,$NguonChuyen_phu){
	$sqlinsert = "INSERT INTO `tbl_noptiensangag`(`ID`, `id_tblNoptien`, `id_tblThongtinAG`, `SoHD`, `SotienChuyen`, `Ghichu`,`NguonChuyen`) VALUES (NULL,".$id_tblNoptien_phu.",".$id_tblThongtinAG_phu.",'".$soHD_phu."',".$soTienChuyen_phu.",'".$username_phu."','".$NguonChuyen_phu."')";

	mysqli_query($conn_phu,$sqlinsert) or die ("Không thể kết nối! ". mysqli_error($conn_phu));
	$id_in_tblNoptienSangAG = mysqli_insert_id($conn_phu);
	
	$sqlinsert = "INSERT INTO `tbl_theodoi`(`ID`, `IDlogon`, `Action`, `AtTime`) VALUES (NULL,'".$_SESSION['user_info']['logon_id']."','Nhập bảng noptiensangag, ID: ".$id_in_tblNoptienSangAG.", số tiền ".$soTienChuyen_phu."','".$currentdate_phu."')";
	mysqli_query($conn_phu,$sqlinsert) or die ("Không thể kết nối dữ liệu! ".mysqli_error($conn_phu));
}

?>