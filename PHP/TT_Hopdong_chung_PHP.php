<?php
    session_start();
    include_once 'define.php';
    include_once PATH_MAIN_FUNCTION.'/conn-login-logout.php';
    include_once PATH_MAIN_FUNCTION.'/pagination.php';
    $conn = connection_to_database();

    define('TTCHUNG_PER_PAGE', 25);
	
    $xhtmlChonGioiTinh=  '<select name="select_GioiTinh" id="select_GioiTinh" class="input-sm" style="width:110px;">
                            <option value="">Giới tính</option>
                            <option value="Nam">Nam</option>
                            <option value="Nữ">Nữ</option></select>';
	 $xhtmlChonTTHonNhan=  '<select name="select_HonNhan" id="select_HonNhan" class="input-sm" style="width:165px;">
                            <option value="">Hôn nhân</option>
                            <option value="Độc thân">Độc thân</option>
                            <option value="Đã kết hôn">Đã kết hôn</option>
                            <option value="Nuôi con đơn thân">Nuôi con đơn thân</option>
                            <option value="Khác">Khác</option>
							</select>';
	$xhtmlChonTTSuckhoe=  '<select name="select_Suckhoe" id="select_Suckhoe" class="input-sm" style="width:110px;">
                            <option value="">TT Sức khỏe</option>
                            <option value="Có BHYT">Có BHYT</option>
                            <option value="Không có BHYT">Không có BHYT</option>
							</select>';
	$xhtmlChonTrinhDo=  '<select name="select_TrinhDo" id="select_TrinhDo" class="input-sm" style="width:110px;">
                            <option value="">Trình độ HV</option>
                            <option value="PTTH-Trung Cấp">PTTH / Trung cấp</option>
                            <option value="Cao đẳng">Cao đẳng</option>
                            <option value="Đại học">Đại học</option>
                            <option value="Trên Đại học">Trên Đại học</option>
							</select>';
	$xhtmlChonLoaiHD=  '<select name="select_LoaiHD" id="select_LoaiHD" class="input-sm" style="width:120px;">
                            <option value="">Loại HĐ</option>
                            <option value="A">A</option>
                            <option value="AG">AG</option>
							</select>';
	$xhtmlChonLoaiHD_OnlyCon=  '<select name="select_LoaiHD_OnlyCon" id="select_LoaiHD_OnlyCon" class="input-sm" style="width:120px;">
                            <option value="">Loại HĐ</option>
                            <option value="A">A</option>
                            <option value="AG">AG</option>
							</select>';
	$xhtmlChonMQH=  '<select name="select_MQH" id="select_MQH" class="input-sm" style="width:125px;">
                            <option value="">MQH với chủ HĐ</option>
                            <option value="Bố mẹ">Bố mẹ</option>
                            <option value="Vợ chồng">Vợ/Chồng</option>
                            <option value="Anh chị em ruột">Anh chị em ruột</option>
                            <option value="Khác">Khác</option>
							</select>';
	$xhtmlChonMQH_OnlyCon=  '<select name="select_MQH_OnlyCon" id="select_MQH_OnlyCon" class="input-sm" style="width:125px;">
                            <option value="">MQH với chủ HĐ</option>
                            <option value="Bố mẹ">Bố mẹ</option>
                            <option value="Vợ chồng">Vợ/Chồng</option>
                            <option value="Anh chị em ruột">Anh chị em ruột</option>
                            <option value="Khác">Khác</option>
							</select>';
	$xhtmlChonGioiTinhNLH=  '<select name="select_GioiTinhNLH" id="select_GioiTinhNLH" class="input-sm" style="width:80px;">
                            <option value="">Giới tính</option>
                            <option value="Nam">Nam</option>
                            <option value="Nữ">Nữ</option></select>';
	$xhtmlChonGioiTinhNLH_OnlyCon=  '<select name="select_GioiTinhNLH_OnlyCon" id="select_GioiTinhNLH_OnlyCon" class="input-sm" style="width:80px;">
                            <option value="">Giới tính</option>
                            <option value="Nam">Nam</option>
                            <option value="Nữ">Nữ</option></select>';
	$xhtmlChonsoNam=  '<select name="select_soNamHD" id="select_soNamHD" class="input-sm" style="width:140px;">
                            <option value="">Số năm HĐ</option>
                            <option value="16">16 năm</option>
                            <option value="26">26 năm</option>
                            <option value="36">36 năm</option>
                            <option value="46">46 năm</option></select>';
	$xhtmlChonsoNam_OnlyCon=  '<select name="select_soNamHD_OnlyCon" id="select_soNamHD_OnlyCon" class="input-sm" style="width:140px;">
                            <option value="">Số năm HĐ</option>
                            <option value="16">16 năm</option>
                            <option value="26">26 năm</option>
                            <option value="36">36 năm</option>
                            <option value="46">46 năm</option></select>';
	$xhtmlChonNV='';
    $xhtmlChonNV.=  '<select name="select_MaNV" id="select_MaNV" class="input-sm" style="width:250px;">
                            <option value="">Nhân viên bán hàng </option>';
    $query="SELECT  `MaNV`, fullname,concat(MaNV,'-',fullname)as MaTenNV FROM `tbl_login` where status = 'Đang hoạt động'";
    $result=mysqli_query($conn,$query) or die(mysqli_error($conn));
    while($row=mysqli_fetch_assoc($result)){
		$xhtmlChonNV.= "<option value='".$row['MaNV']."-".$row['fullname']."'>".$row['MaTenNV']."</option>";
    } 
    $xhtmlChonNV.='</select>';

    $xhtmlChonNV_OnlyCon =  '<select name="select_MaNV_OnlyCon" id="select_MaNV_OnlyCon" class="input-sm" style="width:250px;">
                            <option value="">Nhân viên bán hàng </option>';
    $query="SELECT  `MaNV`, fullname,concat(MaNV,'-',fullname)as MaTenNV FROM `tbl_login` where status = 'Đang hoạt động'";
    $result=mysqli_query($conn,$query) or die(mysqli_error($conn));
    while($row=mysqli_fetch_assoc($result)){
		$xhtmlChonNV.= "<option value='".$row['MaNV']."-".$row['fullname']."'>".$row['MaTenNV']."</option>";
    } 
    $xhtmlChonNV.='</select>';
	
	
	/*
	
	$sqlselect = "select mact, sum(sotien) as tongtien from tbl_chitietthu group by mact ";
	
	$result=mysqli_query($conn,$sqlselect) or die('Không thể liên kết tới bảng thu');
    $tongthu=array();
    while ($set=mysqli_fetch_array($result,MYSQLI_ASSOC)) 
	{
		$tongthu[$set['mact']] = $set['tongtien'];
	}
	
	$sqlselect = "select mact, sum(sotien) as tongtien from tbl_chitietchi group by mact ";
	
	$result=mysqli_query($conn,$sqlselect) or die('Không thể liên kết tới bảng chi');
    $tongchi=array();
    while ($set=mysqli_fetch_array($result,MYSQLI_ASSOC)) 
	{
		$tongchi[$set['mact']] = $set['tongtien'];
	}
     */
	 
    $xhtmlItem='<table class="table table-hover scroll" >
                    <thead> 
                        <tr>  
                            <th width="320px" >Thông tin HĐ</th>  
                            <th width="120px">Mã KH</th>  
                            <th width="110px">Ngày nộp L1</th> 
                            <th width="130px">Ngày PHHĐ /<br/> Ngày hủy HĐ</th> 
                            <th width="150px">Trạng thái HĐ</th> 
                            <th width="380px">TT NV</th> 
                            <th width="150px">Action</th> 
                        </tr>
                    </thead>
                    <tbody style="border:solid;"> ';

    //Display items
	//Xây dựng giá trị cho trạng thái hợp đồng select:
	$status_Agent = '';
	if (isset($_POST['filter_status'])) {
		$status_Agent = trim($_POST['filter_status']);
	} elseif (isset($_GET['v'])) {
		$status_Agent = trim(urldecode(base64_decode($_GET['v'])));
	}

    // Build WHERE
    $whereHD = "WHERE 1";

    // Filter trạng thái
    $allowed = ['Dang_hoat_dong','Tam_dung','Da_ket_thuc','Da_huy_trong_21_ngay','Da_huy_sau_21_ngay'];
    if ($status_Agent !== '' && in_array($status_Agent, $allowed)) {
        $whereHD .= " AND TrangThaiHD = '" . mysqli_real_escape_string($conn, $status_Agent) . "'";
    }
    if (isset($_POST['search']) && !empty($_POST['textcond'])) {
        $textcond = mysqli_real_escape_string($conn, $_POST['textcond']);
        $whereHD .= " AND (SoHD  LIKE '%$textcond%'
               OR HSs LIKE '%$textcond%'
               OR KB LIKE '%$textcond%'
               OR  LoaiHD LIKE '%$textcond%'
               OR  MaKH LIKE '%$textcond%'
               OR  maNV_banhang LIKE '%$textcond%')";
    }

    // --------------------------------------------------
    // PHÂN TRANG: đếm tổng
    // --------------------------------------------------
    $countSqlHD = "
        SELECT COUNT(*) AS total
        FROM `tbl_hopdong_ttchung` hd
        INNER JOIN tbl_khachhang kh ON hd.khachhang_id = kh.id
        $whereHD";
    $totalRowsHD = (int)mysqli_fetch_assoc(mysqli_query($conn, $countSqlHD))['total'];

    $requestedPage = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    [$currentPage, $totalPages, $offset] = getPaginationParams($totalRowsHD, $requestedPage, TTCHUNG_PER_PAGE);

    // --------------------------------------------------
    // QUERY CHÍNH có LIMIT/OFFSET
    // --------------------------------------------------
    $sql = "SELECT hd.`SoHD`,hd.`Iv`,hd.`HSs`,hd.`KB`, kh.`MaKH`, hd.`LoaiHD`,
                   hd.`NgayNopTien1`, hd.`NgayPHHD`, hd.`NgayhuyHD`,hd.`TrangThaiHD`,
                   hd.`GhiChu`,hd.`maNV_nhap`, hd.`fullname_NVnhap`,
                   hd.`maNV_banhang`, hd.agent_id_banhang,hd.TrangThaiHDCho,
                   hd.SoDVTC * hd.SonamHD * 1260000 AS trigia_hd
            FROM `tbl_hopdong_ttchung` hd
            INNER JOIN tbl_khachhang kh ON hd.khachhang_id = kh.id
            $whereHD
            ORDER BY NgayNopTien1 DESC
            LIMIT $offset, " . TTCHUNG_PER_PAGE;

    $result = mysqli_query($conn, $sql) or die('Could not select data'.mysqli_error($conn));
    $no=0;
	if(mysqli_num_rows($result)){
		while ($set=mysqli_fetch_array($result,MYSQLI_ASSOC))  {
			$Trangthaiclass = '';
			if ($set['TrangThaiHD'] == 'Dang_hoat_dong') {$Trangthaiclass = 'text-success';}
			if ($set['TrangThaiHD'] == 'Tam_dung') {$Trangthaiclass = 'text-warning';}
			if ($set['TrangThaiHD'] == 'Da_ket_thuc') {$Trangthaiclass = 'text-muted';}
			if (str_contains($set['TrangThaiHD'],'Da_huy')) {$Trangthaiclass = 'text-danger';}
			
			$TTNV = '<strong> NVTV: </strong>'.htmlspecialchars($set['maNV_banhang']).'<br/> <small class="text-muted"><strong>NVNL: </strong>'.htmlspecialchars($set['maNV_nhap']).' - '.htmlspecialchars($set['fullname_NVnhap']).'<small>';
			$no++;
			$xhtmlItem.='<tr class = "'.$Trangthaiclass.'">';
			$xhtmlItem.= ' <td width="320px">';
			if ($set['TrangThaiHD']==='Da_huy_trong_21_ngay'){
				$xhtmlItem.= 'KB: '.htmlspecialchars($set['KB']).' - Loại: '.htmlspecialchars($set['LoaiHD']).'<br/>';
				if(!empty($set['KB'])) $xhtmlItem.= '<small class="text-muted"> - KB: '.htmlspecialchars($set['KB']).' / ';
				$xhtmlItem.= 'Trị giá: '.number_format(htmlspecialchars($set['trigia_hd'])).' đ</small>';
			}
			else 
				$xhtmlItem.= 'Số: <a href ="TT_Hopdong_Chitiet.php?var='.urlencode(base64_encode($set['SoHD'])).'"><strong>'.htmlspecialchars($set['SoHD']).' </strong></a> - Loại: '.htmlspecialchars($set['LoaiHD']).'<br/> <small class="text-muted">HSs: '.htmlspecialchars($set['HSs']).'/ Số KB: '.htmlspecialchars($set['KB']).'<br/>Trị giá: '.number_format(htmlspecialchars($set['trigia_hd'])).' đ</small>'; 	
						 
			$xhtmlItem.= ' </td><td width="120px">'.htmlspecialchars($set['MaKH']).'</td>
							<td width="110px">'.htmlspecialchars($set['NgayNopTien1']).'</td>
							 <td width="110px">';
			if (str_contains($set['TrangThaiHD'],'Da_huy')) $xhtmlItem.=htmlspecialchars($set['NgayhuyHD']);
			else	$xhtmlItem.= htmlspecialchars($set['NgayPHHD']);
			$xhtmlItem.= '</td>
							 <td width="150px">'.htmlspecialchars($set['TrangThaiHD']);
			if($set['TrangThaiHDCho']==1)
				$xhtmlItem.='<br> <small class="text-danger"><em>(HĐ chờ)</em></small>';
	 
							 
			$xhtmlItem.= '</td>
							 <td width="380px">'.$TTNV.'</td>' ;	
			if($set['TrangThaiHD'] == 'Dang_hoat_dong')
				$xhtmlItem.='<td width="100px"> <a href="#"  onclick="editCT(\''.$set['SoHD'].'\');">Sửa</a></td></tr>';
			else $xhtmlItem.='<td width="100px"> </td></tr>';
        }
	}
	else
		$xhtmlItem.='<tr><td colspan="9"> </td></tr>';
    $xhtmlItem.=' </tbody></table>';
    $xhtmlItem .= renderPagination($currentPage, $totalPages, $totalRowsHD, TTCHUNG_PER_PAGE);
	
	
	if(isset($_POST['excel'])){
		$textcond=!empty($_POST['textcond'])?mysqli_real_escape_String($conn,$_POST['textcond']):'';
		header('location:thuchiFunction/CT_Info-exportExcel.php?textcond='.$textcond);
	}
	
	if(isset($_POST['delete-submit'])) {
		$id_delete=$_POST['id_delete'];
		header('location:thuchiFunction/CT_Info-delCT.php?id_delete='.$id_delete);
	}

?>