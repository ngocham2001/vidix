<?php 
    session_start() ;
    include_once 'define.php';
    include_once PATH_MAIN_FUNCTION.'/conn-login-logout.php';
   	$conn = connection_to_database();
	$soHD = urldecode(base64_decode($_GET['var']));
	$ngayhientai = date('Y-m-d');
	$tomtatUngTien='';	$xhtmlButtonUngTien='';	$xhtmlContractFile = ''; $xhtmlNoptien='';
	$xhtmlTTA=''; $xhtmlTTAG='';$xhtmlUngTien='';$tongtienA = 0;$hesoChuyenTCOx=0;
	
	//Lay TT Hop Dong:
	$sqlselect = "select count(*) as solanNop from tbl_noptien where soHD = '".$soHD."'";
	$resultselect = mysqli_query($conn,$sqlselect) or die(mysqli_error($conn));
	$setselect = mysqli_fetch_array($resultselect,MYSQLI_ASSOC);
	$soLanNop = $setselect['solanNop'];
	
	$sqlselect =  "SELECT `soHD`, `MaKhach`, `LoaiHD`, `NgayNopTien1`, `NgayPHHD`, `SoDVTC`,`SonamHD`, `TrangThaiHD`, `Sotaikhoan`, `TenNganHang`, `HotenChuTK`,`GhiChu` FROM `tbl_hopdong_ttchung` WHERE soHD = '".$soHD."'";
	$resultselect = mysqli_query($conn,$sqlselect) or die(mysqli_error($conn));
	
	while($setselect = mysqli_fetch_array($resultselect,MYSQLI_ASSOC)){
		$MaKhach = $setselect['MaKhach'];
		$LoaiHD = $setselect['LoaiHD'];
		$NgayNopTien1 = $setselect['NgayNopTien1'];
		$NgayPHHD = date('Y-m-d', strtotime($setselect['NgayNopTien1'] . ' + 21 days'));
		$SoDVTC = $setselect['SoDVTC'];
		$SonamHD = $setselect['SonamHD'];
		$TrangThaiHD = $setselect['TrangThaiHD'];
		$Sotaikhoan = $setselect['Sotaikhoan'];
		$TenNganHang = $setselect['TenNganHang'];
		$HotenChuTK = $setselect['HotenChuTK'];
		$GhiChu = $setselect['GhiChu'];
	}
	$trigiaHD = $SoDVTC*1260000*$SonamHD;
	if ($TrangThaiHD == 'Dang_hoat_dong') {$Trangthaiclass = '<strong><span class = "text-success">'.$TrangThaiHD.'</span></strong>';}
	if ($TrangThaiHD == 'Tam_dung') {$Trangthaiclass = '<span class = "text-warning">'.$TrangThaiHD.'</span>';}
	if ($TrangThaiHD == 'Da_ket_thuc') {$Trangthaiclass = '<span class = "text-muted">'.$TrangThaiHD.'</span>';}
	if (str_contains($TrangThaiHD,'Da_huy_')) {$Trangthaiclass = '<span class = "text-danger">'.$TrangThaiHD.'</span>';}
	if ($SoDVTC >=12 && $SoDVTC%12 ==0) {$soTinChiChung = $SoDVTC/12; $soTinChiChung .= ' TCOx';} else {$soTinChiChung = $SoDVTC.' ĐVTC';}
 //Lay TT Khách hàng:
	$sqlselect =  "SELECT `MaKH`, `HoTen`, `CCCD`, `SoDT`, `NgaycapCCCD`, `NgaySinh`, `GioiTinh`, `NoiOHientai`, `HKThuongtru`, `Email`, `DanToc`, `QuocTich`, `TinhTrangHonnhan`, `TinhTrangSucKhoe`, `TrinhDoHocVan`, `GhiChu` FROM `tbl_khachhang` WHERE MaKH = '".$MaKhach."'";
	$resultselect = mysqli_query($conn,$sqlselect) or die(mysqli_error($conn));
	
	while($setselect = mysqli_fetch_array($resultselect,MYSQLI_ASSOC)){
		$HoTen = $setselect['HoTen'];
		$CCCD = $setselect['CCCD'];
		$SoDT = $setselect['SoDT'];
		$NgaycapCCCD = $setselect['NgaycapCCCD'];
		$NgaySinh = $setselect['NgaySinh'];
		$GioiTinh = $setselect['GioiTinh'];
		$HKThuongtru = $setselect['HKThuongtru'];
		$NoiOHientai = $setselect['NoiOHientai'];
		$Email = $setselect['Email'];
		$DanToc = $setselect['DanToc'];
		$QuocTich = $setselect['QuocTich'];
		$TinhTrangHonnhan = $setselect['TinhTrangHonnhan'];
		$TinhTrangSucKhoe = $setselect['TinhTrangSucKhoe'];
		$TrinhDoHocVan = $setselect['TrinhDoHocVan'];
		$GhiChu = $setselect['GhiChu'];
	}
	
	//Contract file Information:
	$sqlselectContract="SELECT `ID`, `SoHD`, `LoaiFile`, `linkFile` FROM `tbl_hopdong_file` WHERE SoHD = '".$soHD."'";
	$resultselectContract = mysqli_query($conn,$sqlselectContract) or die(mysqli_error($conn));
	$filecount = 0;
	if(mysqli_num_rows($resultselectContract)>0){
		while($setselect = mysqli_fetch_array($resultselectContract,MYSQLI_ASSOC)){
			$filecount++;
			$urlfile = $setselect['linkFile'];
			$idfile = $setselect['ID'];
			$loaiFile = $setselect['LoaiFile'];
			$xhtmlContractFile.='<a href="upload/HopDong/'.$urlfile.'">'.$urlfile.'</a> &nbsp;
									<a href="#" onclick="DelProcess('.$idfile.',\'delfile\')" ><img src="'.URL_APPLICATION.'/img/file_delete.png"/> </a>
									<br/>';
		}
	}
	//Tính tổng số tiền đã nộp:
	
	
	$sqlselect="SELECT `ID`, `ThoigianNop`, `SoTienNop`, `NgayTraLai1`, `GhiChu`, `maNV_nhap`, `maNV_duyet`, `fullname_NVnhap`, `fullname_NVduyet`,TrangThaiDongTien FROM `tbl_noptien` WHERE SoHD = '".$soHD."' order by ThoigianNop desc";
	$resultselect = mysqli_query($conn,$sqlselect) or die(mysqli_error($conn));
	$tongnop = 0;
	if(mysqli_num_rows($resultselect)==0){
		$xhtmlNoptien.='<p class = "strong-text text-danger">Không có thông tin nộp tiền cho hợp đồng này</p>';
	}
	else{
		$xhtmlNoptien.= '<p  class = "strong-text">Thông tin nộp tiền: </p>
					<table class = "table table-bordered">
						<thead> 
							<tr>
								<th scope="col" >ID nộp tiền</th>
								<th scope="col" > Thời gian nộp</th>
								<th scope="col" > Số tiền nộp</th>
								<th scope="col" > Ngày trả lãi lần 1</th>
								<th scope="col" >Trạng thái dòng tiền</th>
								<th scope="col" > Thông tin NV</th>
								<th scope="col" > Ghi chú</th>
							</tr>
						</thead>
						<tbody>';
		while($setselect = mysqli_fetch_array($resultselect,MYSQLI_ASSOC)){
			
			$tongnop = $tongnop + $setselect['SoTienNop'];
			$xhtmlNoptien.= '<tr>
							<td scope = "col">'.$setselect['ID'].'</td>
							<td scope = "col">'.date('Y-m-d',strtotime($setselect['ThoigianNop'])).'</td>
							<td scope = "col" align="right">'.number_format($setselect['SoTienNop'],0).'</td>
							<td scope = "col">'.$setselect['NgayTraLai1'].'</td>
							<td scope = "col">'.$setselect['TrangThaiDongTien'].'</td>
							<td scope = "col">NV nhập: '.$setselect['maNV_nhap'].' '.$setselect['fullname_NVnhap'];
			if(!empty($setselect['maNV_duyet'])){
				$xhtmlNoptien.= 'NV duyệt: '.$setselect['maNV_duyet'].' '.$setselect['fullname_NVduyet'];
			}				
			$xhtmlNoptien.='</td>
							<td scope = "col">'.$setselect['GhiChu'].'</td></tr>';

		}
		$xhtmlNoptien.='</tbody></table>';
	}
	
	$sqlselect = "SELECT `ID`, `SoHD`, `SotienChuyen`, `ThoiGianTinhLaiA`, `TrangthaiVonGop`, `TrangthaiTinhLai`, `id_TblNoptien`, `NgàyChuyenAG`, `NgayTinhLaiCuoi`, `MaNV` FROM `tbl_ttchung_a` WHERE soHD = '".$soHD."' order by ThoiGianTinhLaiA desc";
	$resultselect = mysqli_query($conn,$sqlselect) or die(mysqli_error($conn));
	if(mysqli_num_rows($resultselect)==0)
		{$xhtmlTTA.='<p class = "strong-text text-danger">Không có thông tin tùy chọn A của hợp đồng</p>';}
	else{
		$xhtmlTTA.= '<p class = "strong-text">Thông tin tùy chọn A:</p>
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
			$tongtienA += $setselect['SotienChuyen'];
			$xhtmlTTA.= '<tr';
			if($setselect['TrangthaiTinhLai'] == 'Closed' || $setselect['TrangthaiVonGop'] == 'Đã chuyển AG'){
				$xhtmlTTA.= ' class = "text-muted"';
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
	}
	
	
	if($LoaiHD == 'AG'){
		$noptienAG = array();
		$soduDVTC = 0;
		$sqlselect = "SELECT `ID`, `NgayxacminhAG`, `NgaychuyenAG`, `SoDVTC`, `id_logon`, NgayChuyenTCOx, MaNVChuyenTCOx FROM `tbl_ttchung_ag` WHERE soHD = '".$soHD."'";
		$resultselect = mysqli_query($conn,$sqlselect) or die(mysqli_error($conn));
		$no=0;
		if(mysqli_num_rows($resultselect)==0){
			$xhtmlTTAG.='<p class= "strong-text text-danger">Không có thông tin tùy chọn AG của hợp đồng</p>';
		}
		else{
			$xhtmlTTAG.= '<p class= "strong-text">Thông tin tùy chọn AG</p>
							<table class = "table table-bordered">
								<thead> 
									<tr>
										<th scope="col" >STT</th>
										<th scope="col" >Số tiền AG</th>
										<th scope="col" >Ngày xác minh AG</th>
										<th scope="col" >Ngày chuyển AG</th>
									</tr>
								</thead>
								<tbody>';
			while($setselect = mysqli_fetch_array($resultselect,MYSQLI_ASSOC)){
				$no++; 
				$soDVTC_AG = $setselect['SoDVTC'];
				$NgayChuyenTCOx = $setselect['NgayChuyenTCOx'];
				$MaNVChuyenTCOx = $setselect['MaNVChuyenTCOx'];
				if(empty($NgayChuyenTCOx) && empty($MaNVChuyenTCOx)){$soduDVTC++;}
				$sotienAG = $soDVTC_AG * 1260000 * $SonamHD;
				if ($soDVTC_AG >=12 && $soDVTC_AG%12 ==0) {
					$TinChi = $soDVTC_AG/12; $TinChi = $TinChi.' TCOx';
					if(!empty($NgayChuyenTCOx) && !empty($MaNVChuyenTCOx)) $TinChi .= ' - Đã chuyển TCOx';
				} else {$TinChi = $soDVTC_AG.' ĐVTC';}
				$xhtmlTTAG.= '<tr>
						<td scope = "col">'.$no.'</td>
						<td scope = "col" align = "right">'.number_format($sotienAG,0).' (~'.$TinChi.')</td>
						<td scope = "col">'.$setselect['NgayxacminhAG'].'</td>
						<td scope = "col">'.$setselect['NgaychuyenAG'].'</td></tr>';
			}		
		$xhtmlTTA.='</tbody></table>';
		
		}
	
	
		$tongung = 0; $no =0;
		$sqlselect = "SELECT `ID`, `ThoigianUng`, `SoTienUng`, `NgayTraLai1`, `GhiChu` FROM `tbl_ungtien` WHERE soHD = '".$soHD."' order by ThoigianUng desc";
		$resultselect = mysqli_query($conn,$sqlselect) or die(mysqli_error($conn));
		if(mysqli_num_rows($resultselect)==0){$xhtmlUngTien ='<p><span class = "strong-text text-danger">Không có thông tin ứng tiền cho hợp đồng này</span</p>';}
		else{
			$xhtmlUngTien = '<p class = "strong-text">Thông tin ứng tiền: </p>
						<table class = "table table-bordered">
							<thead> 
								<tr>
									<th scope="col" >#</th>
									<th scope="col" > Thời gian ứng</th>
									<th scope="col" > Số tiền ứng</th>
									<th scope="col" > Ngày trả lãi lần 1</th>
									<th scope="col" > Ghi chú</th>
								</tr>
							</thead>
							<tbody>';
			while($setselect = mysqli_fetch_array($resultselect,MYSQLI_ASSOC)){
				$no++;
				$tongung = $tongung + $setselect['SoTienUng'];
				$xhtmlUngTien.= '<tr>
								<td scope = "col">'.$no.'</td>
								<td scope = "col">'.$setselect['ThoigianUng'].'</td>
								<td scope = "col">'.$setselect['SoTienUng'].'</td>
								<td scope = "col">'.$setselect['NgayTraLai1'].'</td>
								<td scope = "col">'.$setselect['GhiChu'].'</td></tr>';
			}
			$xhtmlUngTien.='</tbody></table>';
			
		}
		$thoigianduocphepUng = date('Y-m-d', strtotime($NgayNopTien1.' + 3 years'));
		if(strtotime($ngayhientai)<=strtotime($thoigianduocphepUng)) {
			$tomtatUngTien.='<span class = "text-danger">Chưa đủ điều kiện ứng tiền</span> &nbsp; - &nbsp; Số tiền đã ứng: <span class = "text-danger">'.number_format($tongung,0).' đ</span>';
			
			}
		else
		{
			$tomtatUngTien.='<span class = "text-success">Đủ điều kiện ứng tiền từ '.$thoigianduocphepUng.'</span> &nbsp; - &nbsp; Số tiền đã ứng: <span class = "text-danger">'.number_format($tongung,0).' đ</span>';
			$xhtmlButtonUngTien= '&emsp; <a href="#" onclick="ThemTTUngTien(\''.$soHD.'\')" class="btn btn-primary strong-text">Nhập TT ứng tiền</a>';
		}
	}	
	else{ //Loại HĐ == A
		//Lấy tổng tiền đã chuyển vào TCOx:
		$sqlselect = "SELECT sum(`SoTCOx`) as TongTCOx_A FROM `tbl_ttchunga_tcox` WHERE soHD = '".$soHD."'";
		$resultselect = mysqli_query($conn,$sqlselect) or die(mysqli_error($conn));
		$setselect = mysqli_fetch_array($resultselect,MYSQLI_ASSOC);
		$TongTCOx_A = $setselect['TongTCOx_A'];
		$TongTrigiaTCOx_A = $TongTCOx_A*$SoDVTC*1260000*$SonamHD;
		$TienChuaChuyenTCOx = $tongtienA - $TongTrigiaTCOx_A;
		
		if($TienChuaChuyenTCOx>0){
			$hesoChuyenTCOx = floor($TienChuaChuyenTCOx/$trigiaHD);//Đây là hệ số tiền còn lại của A đưa vào TCOx
		}
	}
	if(isset($_POST['Del_item'])) {
		$soHD=$_POST['soHD_tangTCOx'];
		header('location:VIDIX_function/Hopdong_tangTCOX.php?var='.urlencode(base64_encode($soHD)));
	}
?>