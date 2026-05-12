<?php 
    session_start() ;
    include_once 'define.php';
    include_once PATH_MAIN_FUNCTION.'/conn-login-logout.php';
   	$conn = connection_to_database();
	$soHD = urldecode(base64_decode($_GET['var']));
	$ngayhientai = date('y-m-d');
	//Lay TT NVBH:
	
	$sqlselect =  "SELECT maNV_banhang,fullname_NVbanhang, LoaiHD, SoDVTC, SonamHD, TudongTangAG FROM `tbl_hopdong_ttchung` WHERE soHD = '".$soHD."'";
	$resultselect = mysqli_query($conn,$sqlselect) or die(mysqli_error($conn));
	$TTNVBH ='';
	while($setselect = mysqli_fetch_array($resultselect,MYSQLI_ASSOC)){
		
		$maNV_banhang = $setselect['maNV_banhang'];
		$fullname_NVbanhang = $setselect['fullname_NVbanhang'];
		if(empty($maNV_banhang) && empty($fullname_NVbanhang)) {$TTNVBH = '<span class = "error strong-text" >Thiếu thông tin nhân viên tư vấn</span>';}
		else{
			$TTNVBH = 'NVTV: '.$maNV_banhang.'-'.$fullname_NVbanhang;
		}
		$LoaiHD = $setselect['LoaiHD'];
		$SoDVTC = $setselect['SoDVTC'];
		$SonamHD = $setselect['SonamHD'];
		$TudongTangAG = $setselect['TudongTangAG'];
	}	
	$trigiaHD = $SoDVTC*12*$SonamHD*105000;
	if ($SoDVTC >=12 && $SoDVTC%12 ==0) {$TinChi = $SoDVTC/12; $TinChi = $TinChi.' TCOx';} else {$TinChi = $SoDVTC.' ĐVTC';}
	
	//Tính tổng số tiền đã nộp:
	$tongnop = 0;
	$sqlselect="SELECT sum(`SoTienNop`) as TongTienNop FROM `tbl_noptien` WHERE SoHD = '".$soHD."'";
	$resultselect = mysqli_query($conn,$sqlselect) or die(mysqli_error($conn));
	$setselect = mysqli_fetch_array($resultselect,MYSQLI_ASSOC);
	$tongnop = $setselect['TongTienNop'];
	$xhtmlTTA ='';
	$xhtmlTTAG ='';
	$tongA = 0;
	$sqlselect = "SELECT `ID`, `SoHD`, `SotienChuyen`, `ThoiGianTinhLaiA`, `TrangthaiVonGop`, `TrangthaiTinhLai`, `id_TblNoptien`, `NgàyChuyenAG`, `NgayTinhLaiCuoi`, `MaNV` FROM `tbl_ttchung_a` WHERE soHD = '".$soHD."' order by ThoiGianTinhLaiA desc";
	$resultselect = mysqli_query($conn,$sqlselect) or die(mysqli_error($conn));
	if(mysqli_num_rows($resultselect)==0)
		{$xhtmlTTA.='<p><strong><span class = "text-danger">Không có thông tin tùy chọn A của hợp đồng</span</strong></p>';}
	else{
		$xhtmlTTA.= '<p><strong>Thông tin tùy chọn A: </strong></p>
						<table class = "table table-bordered">
							<thead> 
								<tr>
									<th scope="col" >ID nộp tiền</th>
									<th scope="col" > Số tiền t/c A</th>
									<th scope="col" > Thời gian<br/>chuyển A</th>
									<th scope="col" >Trạng thái<br/>Vốn</th>
									<th scope="col" >Trạng thái<br/>Tính Lãi</th>
									<th scope="col" >Ngày tính lãi cuối</th>
								</tr>
							</thead>
							<tbody>';
		while($setselect = mysqli_fetch_array($resultselect,MYSQLI_ASSOC)){
			$tongA += $setselect['TrangthaiTinhLai']<>"Closed" ? $setselect['SotienChuyen'] :0;
			$xhtmlTTA.= '<tr ';
			if($setselect['ThoiGianTinhLaiA'] == "Closed" || $setselect['TrangthaiVonGop'] == "Đã chuyển AG"){
				$xhtmlTTA.= 'class = "text-muted"';
			}
			$xhtmlTTA.= ' >
							<td scope = "col">'.$setselect['id_TblNoptien'].'</td>
							<td scope = "col" align="right">'.number_format($setselect['SotienChuyen']).'</td>
							<td scope = "col">'.$setselect['ThoiGianTinhLaiA'].'</td>
							<td scope = "col">'.$setselect['TrangthaiVonGop'].'</td>
							<td scope = "col">'.$setselect['TrangthaiTinhLai'].'</td>
							<td scope = "col">'.$setselect['NgayTinhLaiCuoi'].'</td></tr>';
		}
		$xhtmlTTA.='</tbody></table>';
		$xhtmlChuyenAG  = '';
		if ($tongA >= $trigiaHD) {
			$xhtmlChuyenAG = '<p class = "error" style="font-style:italic;"> <strong>Hợp đồng đã đủ điều kiện chuyển tùy chọn từ A lên AG. Bấm nút xác nhận để hệ thống chuyển đổi số tiền từ A lên AG </strong></p>
			';
			
		}
	}
	if(isset($_POST['modal_submit'])) {
		$soHD=$_POST['idsoHD'];
		header('location:VIDIX_function/Hopdong_chuyenAlenAG.php?var='.urlencode(base64_encode($soHD)));
	}

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

