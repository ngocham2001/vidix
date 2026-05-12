<?php
session_start();
include_once '../define.php';
include_once PATH_MAIN_FUNCTION.'/conn-login-logout.php';
$conn = connection_to_database();
if ($_SERVER['REQUEST_METHOD'] == "POST"){
	$ManvNhap = $_SESSION['user_info']['logon_id'];
	$TenNVnhap = $_SESSION['user_info']['fullname'];
	$mk 	= mysqli_real_escape_String($conn,$_POST['N_MaKhach']);
	$tk 	= mysqli_real_escape_String($conn,$_POST['N_TenKhach']);
	$N_NgaySinh 	= mysqli_real_escape_String($conn,$_POST['N_NgaySinh']);
	$N_DT 		= mysqli_real_escape_String($conn,$_POST['N_DT']); //Điện thoại
	$N_Email 	= mysqli_real_escape_String($conn,$_POST['N_Email']);
	$N_CCCD 	= mysqli_real_escape_String($conn,$_POST['N_CCCD']);
	$email 		= mysqli_real_escape_String($conn,$_POST['N_Email']);		
	$N_NgayCap 		= mysqli_real_escape_String($conn,$_POST['N_NgayCap']);		
	$N_Diachi 		= mysqli_real_escape_String($conn,$_POST['N_Diachi']);		
	$N_HoKhau 		= mysqli_real_escape_String($conn,$_POST['N_HoKhau']);		
	$N_DanToc 		= mysqli_real_escape_String($conn,$_POST['N_DanToc']);		
			
	$N_QuocTich 	= mysqli_real_escape_String($conn,$_POST['N_QuocTich']);		
	$GioiTinh 		= mysqli_real_escape_String($conn,$_POST['select_GioiTinh']);		
	$N_Suckhoe 		= mysqli_real_escape_String($conn,$_POST['N_Suckhoe']).'-'.mysqli_real_escape_String($conn,$_POST['select_Suckhoe']);		
	$HonNhan 		= mysqli_real_escape_String($conn,$_POST['select_HonNhan']);		
	$TrinhDo 		= mysqli_real_escape_String($conn,$_POST['select_TrinhDo']);		
	$note 		= mysqli_real_escape_String($conn,$_POST['N_Ghichu']);		
	$sqlinsert = "INSERT INTO `tbl_khachhang`(`MaKH`, `HoTen`, `CCCD`, `SoDT`, `NgaycapCCCD`, `NgaySinh`, `GioiTinh`, `NoiOHientai`, `HKThuongtru`, `Email`, `DanToc`, `QuocTich`, `TinhTrangHonnhan`, `TinhTrangSucKhoe`, `TrinhDoHocVan`, `GhiChu`) VALUES ('".$mk."','".$tk."','".$N_CCCD."',";
	$sqlinsert.= !empty($N_DT)?"'".$N_DT."',":"'',";
	$sqlinsert.= !empty($N_NgayCap)?"'".$N_NgayCap."',":"'',";
	$sqlinsert.= !empty($N_NgaySinh)?"'".$N_NgaySinh."',":"'',";
	$sqlinsert.= !empty($GioiTinh)?"'".$GioiTinh."',":"'',";
	$sqlinsert.= !empty($N_Diachi)?"'".$N_Diachi."',":"'',";
	$sqlinsert.= !empty($N_HoKhau)?"'".$N_HoKhau."',":"'',";
	$sqlinsert.= !empty($email)?"'".$email."',":"'',";
	$sqlinsert.= !empty($N_DanToc)?"'".$N_DanToc."',":"'',";
	$sqlinsert.= !empty($N_QuocTich)?"'".$N_QuocTich."',":"'',";
	$sqlinsert.= !empty($HonNhan)?"'".$HonNhan."',":"'',";
	$sqlinsert.= !empty($N_Suckhoe)?"'".$N_Suckhoe."',":"'',";
	$sqlinsert.= !empty($TrinhDo)?"'".$TrinhDo."',":"'',";
	$sqlinsert.= !empty($note)?"'".$note."')":"'')";
	
	mysqli_query($conn,$sqlinsert) or die ("Không thể kết nối! ". mysqli_error($conn));
	//echo $sqlinsert;
	$N_SoHD 	= mysqli_real_escape_String($conn,$_POST['N_SoHD']);
	$LoaiHD 	= mysqli_real_escape_String($conn,$_POST['select_LoaiHD']);
	$N_Ngaynop1 		= mysqli_real_escape_String($conn,$_POST['N_Ngaynop1']); 
	$N_NgayPHHD 	= mysqli_real_escape_String($conn,$_POST['N_NgayPHHD']);
	$N_DVTC 	= mysqli_real_escape_String($conn,$_POST['N_DVTC']);
	$dvtc	= $N_DVTC=="" ? mysqli_real_escape_String($conn,$_POST['N_SoTC'])*12:$N_DVTC;
	$N_TudongTangTC 	= mysqli_real_escape_String($conn,$_POST['N_TudongTangTC']);
	$N_soNamHD 	= mysqli_real_escape_String($conn,$_POST['select_soNamHD']);
	$trangthaiHD 		= "Đang hoạt động";		
	$N_SoTK 		= mysqli_real_escape_String($conn,$_POST['N_SoTK']);		
	$N_TenNH 		= mysqli_real_escape_String($conn,$_POST['N_TenNH']);		
	$TTNV_banhang 		= mysqli_real_escape_String($conn,$_POST['select_MaNV']);		
	$MaNV_banhang 		= substr($TTNV_banhang,0,strpos($TTNV_banhang,"-"));		
	$TenNV_banhang 		= substr($TTNV_banhang,-(strlen($TTNV_banhang)-strpos($TTNV_banhang,"-")-1));		
	$N_HotenCTK 		= mysqli_real_escape_String($conn,$_POST['N_HotenCTK']);		
	$N_TenNLH 		= mysqli_real_escape_String($conn,$_POST['N_TenNLH']);		
	$N_NgaySinhNLH 	= mysqli_real_escape_String($conn,$_POST['N_NgaySinhNLH']);		
	$N_DTNLH 		= mysqli_real_escape_String($conn,$_POST['N_DTNLH']);		//Điện thoại
	$N_EmailNLH 		= mysqli_real_escape_String($conn,$_POST['N_EmailNLH']);		
	$N_DanTocNLH 		= mysqli_real_escape_String($conn,$_POST['N_DanTocNLH']);		
	$N_QuocTichNLH 		= mysqli_real_escape_String($conn,$_POST['N_QuocTichNLH']);		
	$N_DiachiNLH 		= mysqli_real_escape_String($conn,$_POST['N_DiachiNLH']);		
	$N_HoKhauNLH 		= mysqli_real_escape_String($conn,$_POST['N_HoKhauNLH']);		
	$N_CCCDNLH 		= mysqli_real_escape_String($conn,$_POST['N_CCCDNLH']);		
	$N_NgayCapNLH 		= mysqli_real_escape_String($conn,$_POST['N_NgayCapNLH']);		
	$GioiTinhNLH 		= mysqli_real_escape_String($conn,$_POST['select_GioiTinhNLH']);		
	$N_MQH 		= mysqli_real_escape_String($conn,$_POST['select_MQH']);				
	$note 		= mysqli_real_escape_String($conn,$_POST['N_Ghichu_HD']);	
	$sqlinsert_HD = "INSERT INTO `tbl_hopdong_ttchung`(`SoHD`, `MaKhach`, `LoaiHD`, `NgayNopTien1`, `NgayPHHD`, `SoDVTC`, `SonamHD`,`TrangThaiHD`, `Sotaikhoan`, `TenNganHang`, `HotenChuTK`, `HoTenNLH`, `MQH_chuHD`, `NgaysinhNLH`, `GioitinhNLH`, `EmailNLH`, `NoiohiennayNLH`, `DCThuongtruNLH`, `SoDTNLH`, `DantocNLH`, `QuoctichNLH`, `SoCCCDNLH`, `NgaycapCCCDNLH`, `GhiChu`,`maNV_nhap`, `fullname_NVnhap`,`TudongTangAG`, `maNV_banhang`, `fullname_NVbanhang`) VALUES ('".$N_SoHD."','".$mk."','".$LoaiHD."','".$N_Ngaynop1."',";
		
	
	header("location:../TT_Hopdong_Chitiet.php?var=".urlencode(base64_encode($soHD)));
	$sqlinsert_HD.= !empty($N_NgayPHHD)?"'".$N_NgayPHHD."',":"'',";
	$sqlinsert_HD.= !empty($dvtc)?$dvtc.",":"0,";
	$sqlinsert_HD.= $N_soNamHD.",";
	$sqlinsert_HD.= "'".$trangthaiHD."',";	
	$sqlinsert_HD.= !empty($N_SoTK)?"'".$N_SoTK."',":"'',";
	$sqlinsert_HD.= !empty($N_TenNH)?"'".$N_TenNH."',":"'',";
	$sqlinsert_HD.= !empty($N_HotenCTK)?"'".$N_HotenCTK."',":"'',";
	$sqlinsert_HD.= !empty($N_TenNLH)?"'".$N_TenNLH."',":"'',";
	$sqlinsert_HD.= !empty($N_MQH)?"'".$N_MQH."',":"'',";
	$sqlinsert_HD.= !empty($N_NgaySinhNLH)?"'".$N_NgaySinhNLH."',":"'',";
	$sqlinsert_HD.= !empty($GioiTinhNLH)?"'".$GioiTinhNLH."',":"'',";
	$sqlinsert_HD.= !empty($N_EmailNLH)?"'".$N_EmailNLH."',":"'',";
	$sqlinsert_HD.= !empty($N_DiachiNLH)?"'".$N_DiachiNLH."',":"'',";
	$sqlinsert_HD.= !empty($N_HoKhauNLH)?"'".$N_HoKhauNLH."',":"'',";
	$sqlinsert_HD.= !empty($N_DTNLH)?"'".$N_DTNLH."',":"'',";
	$sqlinsert_HD.= !empty($N_DanTocNLH)?"'".$N_DanTocNLH."',":"'',";
	$sqlinsert_HD.= !empty($N_QuocTichNLH)?"'".$N_QuocTichNLH."',":"'',";
	$sqlinsert_HD.= !empty($N_CCCDNLH)?"'".$N_CCCDNLH."',":"'',";
	$sqlinsert_HD.= !empty($N_NgayCapNLH)?"'".$N_NgayCapNLH."',":"'',";
	$sqlinsert_HD.= !empty($note)?"'".$note."'":"'',";
	$sqlinsert_HD.= "'".$ManvNhap."','".$TenNVnhap."',";
	$sqlinsert_HD.= !empty($N_TudongTangTC)?"'".$N_TudongTangTC."',":"0,";
	$sqlinsert_HD.= "'".$MaNV_banhang."','".$TenNV_banhang."')";
	mysqli_query($conn,$sqlinsert_HD) or die ("Không thể kết nối! ". mysqli_error($conn));
	
	$sqlinsert = "INSERT INTO `tbl_theodoi`(`ID`, `IDlogon`, `Action`, `AtTime`) VALUES (NULL,'".$_SESSION['user_info']['logon_id']."','Tạo hợp đồng: ".$N_SoHD." khách hàng: ".$mk."','".$currentDateAndTime."') ";
	mysqli_query($conn,$sqlinsert) or die("Could not connect. " .mysqli_error($conn));	
	$fmess=1;
	header('location:../TT_Hopdong_chung.php?fmess='.$fmess); 
	}
	
?>