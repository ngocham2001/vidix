<?php
session_start();
include_once 'donVon_kiemtraVon.php';
include_once '../define.php';
include_once PATH_MAIN_FUNCTION.'/conn-login-logout.php';
$conn = connection_to_database();
$ngayhientai = date('Y-m-d');
if ($_SERVER['REQUEST_METHOD'] == "POST"){
	//Lấy số lần nộp tiền:
	$soHD 	= mysqli_real_escape_String($conn,$_POST['N_tinNopTien_soHD']);
	$LoaiHD 	= mysqli_real_escape_String($conn,$_POST['N_Noptien_LoaiHD']);
	$SoDVTC 	= mysqli_real_escape_String($conn,$_POST['N_Noptien_DVTC']);
	$sonamHD 	= mysqli_real_escape_String($conn,$_POST['N_Noptien_sonamHD']);
	$soLanNop 	= mysqli_real_escape_String($conn,$_POST['N_Noptien_soLanNop']);
	$soTien_DVTC = $sonamHD*1260000*$SoDVTC;
	$sotien = mysqli_real_escape_String($conn,$_POST['N_Noptien_sotien']);
	$currentdate=date("Y-m-d");
	$ngaynop = mysqli_real_escape_String($conn,$_POST['N_Noptien_ngay']);
	$ngayTraLai1 = date('Y-m-d', strtotime("+90 days", strtotime($ngaynop)));
	$N_Ghichu 	= mysqli_real_escape_String($conn,$_POST['N_Ghichu_Noptien']);		
	$ManvNhap = $_SESSION['user_info']['logon_id'];
	$TenNVnhap = $_SESSION['user_info']['fullname'];
	$sqlselectTudong = "select TudongTangAG from tbl_hopdong_ttchung  where soHD = '".$soHD."'";
	$resultselectTudong = mysqli_query($conn,$sqlselectTudong) or die(mysqli_error($conn));
	$setselectTudong = mysqli_fetch_array($resultselectTudong,MYSQLI_ASSOC);
	$tangTuDongAG = $setselectTudong['TudongTangAG'];
	
	//Nếu nộp tiền lần đầu và $số tiền>= số tiền đăng ký tín chỉ và loại HĐ là AG thì:1- Nhập vào tbl_noptien, 
	//2-Tách phần đủ vào tbl_TTchung_AG; 3- Nếu dư tách sang tbl_TTchung_A
	 if($LoaiHD =="AG"){
		if($soLanNop == 0){ //Nếu nộp lần đầu - Lưu ý lần đầu bắt buộc phải nộp đủ số tiền đăng ký tín chỉ với loại HĐ là AG
			if($sotien >= $soTien_DVTC){  //Nếu số tiền nộp >= số tiền đăng ký tín chỉ
				$trangthaiDongTien = $sotien==$soTien_DVTC?'Đã chuyển toàn bộ tới AG':'Chuyển A & AG';
				$tiendu = $sotien - $soTien_DVTC;
				$N_Ghichu = !empty($N_Ghichu)?$N_Ghichu:'';
				//Chèn vào tbl_noptien:
				$id_in_tblNoptien = ChenDuLienTbl_noptien($conn, $soHD, $ngaynop, $sotien,$ngayTraLai1,$N_Ghichu,$trangthaiDongTien,$TenNVnhap, $ManvNhap,$currentdate);
				
				//Chèn vào tbl_ttchung_ag 
				//echo $ngaynop.' / '.$ngayTraLai1.' / '.$SoDVTC.' / '.$ManvNhap;
				$id_in_tblTTchungAG = ChenDuLienTblAG($conn, $soHD, $ngaynop, $ngayTraLai1,$SoDVTC, $ManvNhap,$currentdate);
				
				//Chèn vào tbl_noptienSangAG 	
				$NguonChuyen = 'tbl_noptien';				
				ChenDuLieuTbl_noptiensangAG($conn,$id_in_tblNoptien,$id_in_tblTTchungAG,$soHD,$soTien_DVTC,$ManvNhap,$currentdate,$NguonChuyen);
							
				//Nếu còn thừa thì chuyển A:
				if($tiendu>0){
					$trangthai_Vongop = 'Dư sau kết chuyển';
					$trangthai_TinhLai = 'Continue';
					$ngayChuyenAG=NULL;
					$ngaytinhLaiCuoi=NULL;
					
					ChenDuLienTblA($conn, $soHD, $tiendu, $ngayTraLai1,$trangthai_Vongop,$ngaytinhLaiCuoi,$trangthai_TinhLai,$id_in_tblNoptien,$ngayChuyenAG, $ManvNhap,$currentdate);
					
					$sqlinsert = "INSERT INTO `tbl_theodoi`(`ID`, `IDlogon`, `Action`, `AtTime`) VALUES (NULL,'".$_SESSION['user_info']['logon_id']."','Nhập bảng nộp tiền, KH: ".$id_in_tblNoptien.", ngày ".$ngaynop.", số tiền ".$sotien."','".$currentdate."')";
					mysqli_query($conn,$sqlinsert) or die ("Không thể kết nối dữ liệu! ".mysqli_error($conn));
					//Chuyển về trang chuyển TT A và AG để xác minh chuyển:
					header('location:../TT_Hopdong_ThongbaoA_AG.php?var='.urlencode(base64_encode($soHD)));
					exit();
				
				}
				$sqlinsert = "INSERT INTO `tbl_theodoi`(`ID`, `IDlogon`, `Action`, `AtTime`) VALUES (NULL,'".$_SESSION['user_info']['logon_id']."','Nhập bảng nộp tiền, KH: ".$id_in_tblNoptien.", ngày ".$ngaynop.", số tiền ".$sotien."','".$currentdate."')";
				mysqli_query($conn,$sqlinsert) or die ("Không thể kết nối dữ liệu! ".mysqli_error($conn));
				header('location:../TT_Hopdong_Chitiet.php?var='.urlencode(base64_encode($soHD)).'&var2='.urlencode(base64_encode($mess2)));
				exit();
			
				
			}//end of else //Nếu số tiền nộp >= số tiền đăng ký tín chỉ	
			$fmess=2;
			header('location:../TT_Hopdong_Chitiet.php?var='.urlencode(base64_encode($soHD)).'&fmess='.$fmess);
			exit();
		}//end of if(!mysqli_num_rows($resultselect))
			
		else{ //Nếu hợp đồng AG chuyển từ lần 2: thì trước tiên lưu vào nộp tiền rồi chuyển tới A: Sau khi chuyển A thì phải kiểm tra xem trạng thái tự chuyển AG. Nếu có tự chuyển AG: và tổng tiền còn của A >= số tín chỉ đăng ký thì chuyển AG. Nếu ko tự chuyển thì chỉ lưu vào A sau đó chờ xét duyệt (nhớ chuyển định dạng màu của bảng TT A sang màu vàng để biết đã đủ AG chờ xác nhận từ chủ HĐ để chuyển A.
		 
			$trangthaiDongTien = 'Đã chuyển tới A';
			
			//1. Chèn vào bảng tbl_noptien:
			$N_Ghichu = !empty($N_Ghichu)?$N_Ghichu:'';
			$id_in_tblNoptien = ChenDuLienTbl_noptien($conn, $soHD, $ngaynop, $sotien,$ngayTraLai1,$N_Ghichu,$trangthaiDongTien,$TenNVnhap, $ManvNhap,$currentdate);
			
			//2. Chèn vào bảng tbl_ttchung_a:
			$trangthai_Vongop = 'Nộp tiền lần 1';
			$trangthai_TinhLai = 'Continue';
			$ngayChuyenAG=NULL;
			$ngaytinhLaiCuoi=NULL;
			
			ChenDuLienTblA($conn, $soHD, $sotien, $ngayTraLai1,$trangthai_Vongop,$ngaytinhLaiCuoi,$trangthai_TinhLai,$id_in_tblNoptien,$ngayChuyenAG, $ManvNhap,$currentdate);
			
			$sqlinsert = "INSERT INTO `tbl_theodoi`(`ID`, `IDlogon`, `Action`, `AtTime`) VALUES (NULL,'".$_SESSION['user_info']['logon_id']."','Nhập bảng nộp tiền, KH: ".$id_in_tblNoptien.", ngày ".$ngaynop.", số tiền ".$sotien."','".$currentdate."')";
			mysqli_query($conn,$sqlinsert) or die ("Không thể kết nối dữ liệu! ".mysqli_error($conn));
			//Chuyển về trang chuyển TT A và AG để xác minh chuyển:
			header('location:../TT_Hopdong_ThongbaoA_AG.php?var='.urlencode(base64_encode($soHD)));
			exit();		
		}
	} //end of if($LoaiHD =="AG")
	else { //Nếu loại hợp đồng ko phải AG (tức là A)
		$trangthai_Vongop = 'Nộp tiền lần 1';
		$trangthai_TinhLai = 'Continue';
		$ngayChuyenAG=NULL;
		$ngaytinhLaiCuoi=NULL;
		$trangthaiDongTien = 'Đã chuyển tới A';
		
		$id_in_tblNoptien = ChenDuLienTbl_noptien($conn, $soHD, $ngaynop, $sotien,$ngayTraLai1,$N_Ghichu,$trangthaiDongTien,$TenNVnhap, $ManvNhap,$currentdate);
		
		ChenDuLienTblA($conn, $soHD, $sotien, $ngayTraLai1,$trangthai_Vongop,$ngaytinhLaiCuoi,$trangthai_TinhLai,$id_in_tblNoptien,$ngayChuyenAG, $ManvNhap,$currentdate);
		
		$sqlinsert = "INSERT INTO `tbl_theodoi`(`ID`, `IDlogon`, `Action`, `AtTime`) VALUES (NULL,'".$_SESSION['user_info']['logon_id']."','Nhập bảng nộp tiền va tbl_TTchung_A, KH: ".$MaKhach.", ngày ".$ngaynop.", số tiền ".$sotien."','".$currentdate."')";
		mysqli_query($conn,$sqlinsert) or die ("Không thể kết nối dữ liệu! ".mysqli_error($conn));
	
		header('location:../TT_Hopdong_ThongbaoA_AG.php?var='.urlencode(base64_encode($soHD)));
		exit();
	} 	 	
	
	
	$sqlinsert = "INSERT INTO `tbl_noptien`(`ID`, `SoHD`, `ThoigianNop`, `SoTienNop`, `NgayTraLai1`, `GhiChu`, `maNV_nhap`, `fullname_NVnhap`) VALUES (NULL,'".$soHD."','".$ngaynop."',".$sotien.",'".$ngayTraLai1."',";
	$sqlinsert.= !empty($N_Ghichu)?"'".$N_Ghichu."',":"'',";
	$sqlinsert.= "'".$ManvNhap."','".$TenNVnhap."')";
	mysqli_query($conn,$sqlinsert) or die ("Không thể kết nối dữ liệu! ".mysqli_error($conn));
	//chèn vào bảng theo dõi:
	$sqlinsert = "INSERT INTO `tbl_theodoi`(`ID`, `IDlogon`, `Action`, `AtTime`) VALUES (NULL,'".$_SESSION['user_info']['logon_id']."','Nhập bảng nộp tiền, KH: ".$MaKhach.", ngày ".$ngaynop.", số tiền ".$sotien."','".$currentdate."')";
	mysqli_query($conn,$sqlinsert) or die ("Không thể kết nối dữ liệu! ".mysqli_error($conn));
	$fmess=2;
	header('location:../TT_Hopdong_Chitiet.php?var='.urlencode(base64_encode($soHD)).'&fmess='.$fmess);
	exit();
	
	}
	
	
?>