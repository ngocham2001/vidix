<?php
session_start();
include_once 'donVon_kiemtraVon.php';
include_once '../define.php';
include_once PATH_MAIN_FUNCTION.'/conn-login-logout.php';
$conn = connection_to_database();
$ngayhientai = date('Y-m-d'); //Lấy ngày hiện tại đưa về các bảng
$soHD = urldecode(base64_decode($_GET['var'])); //Số hợp đồng truyền từ biến href
$ManvNhap = $_SESSION['user_info']['logon_id']; //Lấy mã nhân viên đưa vào các bảng
$tongAchuyen =TongTienA($soHD,$conn); //Lấy tổng tiền A còn dư (từ file DOnVon_KiemtraVon.php


//Lấy TT từ hợp đồng để đưa vào bảng tbl_TTchung_AG và bảng Tbl_noptiensangAG
$sqlselect =  "SELECT LoaiHD, SoDVTC, SonamHD, TudongTangAG FROM `tbl_hopdong_ttchung` WHERE soHD = '".$soHD."'";
$resultselect = mysqli_query($conn,$sqlselect) or die(mysqli_error($conn));
while($setselect = mysqli_fetch_array($resultselect,MYSQLI_ASSOC)){
	$LoaiHD = $setselect['LoaiHD'];
	$SoDVTC = $setselect['SoDVTC'];
	$SonamHD = $setselect['SonamHD'];
	$TudongTangAG = $setselect['TudongTangAG'];
}
$trigiaHD = $SoDVTC*1260000*$SonamHD;	
if($tongAchuyen < $trigiaHD){
	header('location:../TT_Hopdong_ThongbaoA_AG.php?var='.urlencode(base64_encode($soHD)).'&fmess=1');
	exit(); 
}
//Nếu hợp đồng A chuyển thành AG cần xác nhận sửa hợp đồng thành AG:
if($LoaiHD == 'A'){
	//Chèn vào bảng Tbl_thaydoi_tt_HD
	$sqlinsert = "INSERT INTO `tbl_thaydoi_tt_hd`(`ID`, `SoHD`,`TenCot`, `GiaTriCu`, `GiaTriMoi`, `NgayDoiTT`) VALUES(NULL,'".$soHD."', 'LoaiHD','A','AG','".$ngayhientai."')";
	mysqli_query($conn,$sqlinsert);
	
	//Update thay đổi lên AG trong bảng tbl_hopdong_ttchung:
	$sqlupdate = "UPDATE `tbl_hopdong_ttchung` SET `LoaiHD`='AG' WHERE soHD ='".$soHD."'" ;
	mysqli_query($conn,$sqlupdate);
}

//Lấy TT từ bảng TT_chungA để đưa sang hai bảng tbl_TTchung_AG và bảng Tbl_noptiensangAG
$ngayChuyenAG = NULL; $id_Tbl_TTchungA_Arr = array(); $id_TbNoptienMax = 0; $ttTheodoi = '';
$sqlselect = "SELECT ID, SotienChuyen, ngaytinhLaiCuoi, ThoigianTinhLaiA,id_TblNoptien FROM `tbl_ttchung_a` WHERE soHD = '".$soHD."' and TrangThaiVonGop !='Đã chuyển AG' and TrangThaiTinhLai !='Closed' and `NgàyChuyenAG` IS NULL";
$resultselect = mysqli_query($conn,$sqlselect) or die(mysqli_error($conn));
while($setselect = mysqli_fetch_array($resultselect,MYSQLI_ASSOC)){
	$tmp_maxngaytinhLai = max($setselect['ngaytinhLaiCuoi'],$setselect['ThoigianTinhLaiA']);
	if(empty($ngayChuyenAG) || $ngayChuyenAG < $tmp_maxngaytinhLai) $ngayChuyenAG = $tmp_maxngaytinhLai;
	$id_Tbl_TTchungA_Arr[$setselect['ID']] =  $setselect['SotienChuyen'];
	if($id_TbNoptienMax < $setselect['id_TblNoptien']) $id_TbNoptienMax = $setselect['id_TblNoptien'];
	$ttTheodoi.='ID bảng A: '.$setselect['ID'].' số tiền: '.$setselect['SotienChuyen'].', ';
}


//Tính toán:
$hesoChuyenAGtuA = floor($tongAchuyen/$trigiaHD);//Đây là hệ số có thể chuyển từ A lên AG
$soDVTCchuyenAG = $hesoChuyenAGtuA * $SoDVTC; //Từ hệ số quy đổi ra số đơn vị tín chỉ để ghi vào bảng TT chung AG
$soTienAG2 = $soDVTCchuyenAG*1260000*$SonamHD;	//Đổi ra số tiền tương ứng với số tín chỉ chuyển AG
$soTienDu2 = $tongAchuyen - $soTienAG2; //Số tiền còn lại của A sau khi đổi lên AG
$NguonChuyen = 'tbl_ttchungA'; //Để ghi vào bảng tbl_nộp tiền sang ag

//Ghi dữ liệu vào Tbl_TTchung_AG:
$sqlinsert = "INSERT INTO `tbl_ttchung_ag`(`ID`, `SoHD`, `NgayxacminhAG`, `NgaychuyenAG`, `SoDVTC`, `id_logon`) VALUES (NULL,'".$soHD."','".$ngayhientai."','".$ngayChuyenAG."',".$soDVTCchuyenAG.",'".$ManvNhap."')";
if(mysqli_query($conn,$sqlinsert)) $id_in_tblTTchungAG2 = mysqli_insert_id($conn);
else mysqli_error($conn);

//Ghi dữ liệu vào Tbl_noptiensangAG: //Sử dụng vòng lặp chạy qua tất cả các id_Tbl_TTchungA_Arr

foreach($id_Tbl_TTchungA_Arr as $id_Key=>$soTienVal){	
	$sqlinsert = "INSERT INTO `tbl_noptiensangag`(`ID`, `id_tblNoptien`, `id_tblThongtinAG`, `SoHD`, `SotienChuyen`, `Ghichu`,`NguonChuyen`) VALUES (NULL,".$id_Key.",".$id_in_tblTTchungAG2.",'".$soHD."',".$soTienVal.",'".$ManvNhap."','".$NguonChuyen."')";
	mysqli_query($conn,$sqlinsert) or die(mysqli_error($conn));
} 
//Cập nhật dữ liệu bảng tbl_ttchung_a:
 $sqlupdate = "UPDATE `tbl_ttchung_a` SET `ngaytinhLaiCuoi`=`ThoigianTinhLaiA` WHERE soHD = '".$soHD."' and TrangthaiVonGop <>'Đã chuyển AG' and TrangthaiTinhLai = 'Continue' and `ngaytinhLaiCuoi` is NULL";
mysqli_query($conn,$sqlupdate) or die(mysqli_error($conn));

$sqlupdate = "UPDATE `tbl_ttchung_a` SET `TrangthaiVonGop`='Đã chuyển AG',`TrangthaiTinhLai`='Closed',`NgàyChuyenAG`='".$ngayChuyenAG."' WHERE soHD = '".$soHD."' and TrangthaiVonGop <>'Đã chuyển AG' and TrangthaiTinhLai = 'Continue' ";
mysqli_query($conn,$sqlupdate) or die(mysqli_error($conn)); 

//Chèn số dư còn lại trả về tbl_TTchung_A:
if($soTienDu2>0) {
	$TrangThaiVonGop2='Dư sau kết chuyển';
	$TrangThai_TinhLai='Continue';
	$sqlinsert = "INSERT INTO `tbl_ttchung_a`(`ID`, `SoHD`, `SotienChuyen`, `ThoiGianTinhLaiA`, `TrangthaiVonGop`, `TrangthaiTinhLai`, `id_TblNoptien`,`MaNV`) VALUES (NULL,'".$soHD."',".$soTienDu2.",'".$ngayChuyenAG."','".$TrangThaiVonGop2."','".$TrangThai_TinhLai."',".$id_TbNoptienMax.",'".$ManvNhap."')";
	mysqli_query($conn,$sqlinsert) or die ("Không thể kết nối! ". mysqli_error($conn));
} 



//Chèn vào bảng theo dõi:
$sqlinsert = "INSERT INTO `tbl_theodoi`(`ID`, `IDlogon`, `Action`, `AtTime`) VALUES (NULL,'".$_SESSION['user_info']['logon_id']."','Chuyển tiền từ A lên AG,".$ttTheodoi.", giá trị dư kết chuyển A: ".$soTienDu2."','".$ngayhientai."')";
mysqli_query($conn,$sqlinsert) or die ("Không thể kết nối dữ liệu! ".mysqli_error($conn));
if(!isset($_GET['var2'])){
	header('location:../TT_Hopdong_ThongbaoA_AG.php?var='.urlencode(base64_encode($soHD)).'&$fmess=2');}
else {
	header('location:../TT_Hopdong_ĐuKChuyenAG.php?fmess=2');
}
 exit(); 

?>