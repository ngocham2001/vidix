<?php
session_start();
include_once 'donVon_kiemtraVon.php';
include_once '../define.php';
include_once PATH_MAIN_FUNCTION.'/conn-login-logout.php';
$conn = connection_to_database();
if ($_SERVER['REQUEST_METHOD'] == "POST"){
	$ngayhientai = date('Y-m-d'); //Lấy ngày hiện tại đưa về các bảng
	$soHD 	= $_POST['soHD'];
	$hesoChuyenTCOx 	=  mysqli_real_escape_String($conn,$_POST['hesoChuyenTCOx']);
	$ManvNhap = $_SESSION['user_info']['logon_id']; //Lấy mã nhân viên đưa vào các bảng
	$SoDVTC = 0;
	$soduDVTC = 0;$SoDVTCtangTCOx = 0;

	//Lấy TT từ hợp đồng để đưa vào bảng tbl_TTchung_AG và bảng Tbl_noptiensangAG
	$sqlselect =  "SELECT SoDVTC, LoaiHD FROM `tbl_hopdong_ttchung` WHERE soHD = '".$soHD."' and `TrangThaiHD` ='Đang hoạt động'";
	$resultselect = mysqli_query($conn,$sqlselect) or die(mysqli_error($conn));
	while($setselect = mysqli_fetch_array($resultselect,MYSQLI_ASSOC)){
		$SoDVTC = $setselect['SoDVTC'];
		$LoaiHD = $setselect['LoaiHD'];
	}
	if($LoaiHD =='AG'){

		$sqlselect =  "SELECT `NgayChuyenTCOx`,`MaNVChuyenTCOx`,SoDVTC FROM `tbl_ttchung_ag` WHERE soHD = '".$soHD."'";
		$resultselect = mysqli_query($conn,$sqlselect) or die(mysqli_error($conn));
		while($setselect = mysqli_fetch_array($resultselect,MYSQLI_ASSOC)){
			if(empty($setselect['NgayChuyenTCOx']) && empty($setselect['MaNVChuyenTCOx'])){
				$soduDVTC ++; $SoDVTCtangTCOx += $setselect['SoDVTC'];
				}
		}

		if($soduDVTC ==0){
			header('location:../TT_Hopdong_Chitiet.php?var='.urlencode(base64_encode($soHD)).'&fmess=4');
			exit(); 
		}
		
		else {
			//Nếu có 1 bản ghi trong tbl_ttchung_ag rỗng phần MaNVchuyenTCOx và NgayChuyenTCOx thì thực hiện việc nâng ĐVTC lên
			$sqlupdate = "UPDATE `tbl_ttchung_ag` SET `NgayChuyenTCOx`='".$ngayhientai."',`MaNVChuyenTCOx`='".$ManvNhap."' WHERE `SoHD`='".$soHD."' and `NgayChuyenTCOx` is NULL and `MaNVChuyenTCOx` is NULL";
			mysqli_query($conn,$sqlupdate) or die(mysqli_error($conn));
			$sqlupdate = "UPDATE `tbl_hopdong_ttchung` SET  `SoDVTC`=`SoDVTC`+".$SoDVTCtangTCOx." WHERE soHD = '".$soHD."'";
			echo $sqlupdate;
			mysqli_query($conn,$sqlupdate) or die(mysqli_error($conn));
		}
	}
	else{ //Nếu Hợp đồng A:
		$soTCOx = $hesoChuyenTCOx*$SoDVTC;
		$sqlinsert = "INSERT INTO `tbl_ttchunga_tcox`(`ID`, `SoHD`, `SoTCOx`, `NgayChuyenTCOx`, `MaNVChuyenTCOx`) VALUES (NULL,'".$soHD."','".$soTCOx."','".$ngayhientai."','".$ManvNhap."')";
		mysqli_query($conn,$sqlinsert) or die(mysqli_error($conn));
	}
}

 // header('location:../TT_Hopdong_ThongbaoA_AG.php?var='.urlencode(base64_encode($soHD)).'&$fmess=2');
 // exit(); 

?>