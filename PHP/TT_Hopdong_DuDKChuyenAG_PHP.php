<?php 
    session_start() ;
    include_once 'define.php';
    include_once PATH_MAIN_FUNCTION.'/conn-login-logout.php';
   	$conn = connection_to_database();
	$xhtmlItem='<table class="table table-hover scroll" >
                    <thead> 
                        <tr>  
                            <th width="50px" >No</th>  
                            <th width="150px" >Số HĐ</th>  
                            <th width="100px">Mã KH</th>  
                            <th width="130px">Số ĐVTC HĐ/<br/>Trị giá HĐ</th>  
                            <th width="135px">Số TCOx</th> 
							<th width="120px">Giá trị thực tế HĐ</th> 
                            <th width="380px">TTNV</th> 
                            <th width="120px">Action</th> 
                        </tr>
                    </thead>
                    <tbody style="border:solid;"> ';
	
    //Display items
	$dataArr = array();
	//Lấy những HĐ có số tiền dư trong tbl_TTchung_A đủ để chuyển AG: (bao gồm cả hđ A và HĐ AG):
	$soHD_String="(";
	
	$sql="SELECT hd.SoHD, hd.LoaiHD, sum(nt.SotienChuyen) as TongTienFree_A FROM tbl_hopdong_ttchung hd INNER JOIN tbl_ttchung_a nt ON hd.SoHD = nt.SoHD WHERE hd.TrangThaiHD = 'Đang hoạt động' and nt.`TrangthaiTinhLai`= 'Continue' and nt.`TrangthaiVonGop`!='Đã chuyển AG' and nt.`NgàyChuyenAG` IS NULL GROUP BY hd.soHD, hd.SoDVTC, hd.SonamHD,hd.LoaiHD HAVING SUM(nt.SotienChuyen)";
	$result=mysqli_query($conn,$sql) or die('Could not select data'.mysqli_error($conn));
	while ($set=mysqli_fetch_array($result,MYSQLI_ASSOC))  {
		$dataArr[$set['SoHD']]['TongTienChuyen_Free'] = $set['TongTienFree_A'];
		$soHD_String.="'".$set['SoHD']."',";
		//$dataArr[$set['SoHD']]['ChuyenAG']= $set['LoaiHD']=='A'?'<a href="#" onclick="XacnhanChuyenAG(\''.$set['SoHD'].'\')" class="text-danger">Chuyển AG</a>':'<a href="#" class="text-danger">Tăng AG</a>';
		//$dataArr[$set['SoHD']]['TangTCOx'] = '<a href="#"><span class="text-danger">Tăng TCOx</span></a>';
	}
	
	//AG cần tăng TCOx trong hợp đồng
	$sql="SELECT `SoHD`,sum(case when `NgayChuyenTCOx` is NULL then SoDVTC else 0 end) as TongDVTC_ChuaChuyenTCOx, sum(case when `NgayChuyenTCOx` is not NULL then SoDVTC else 0 end) as TongDVTC_DaChuyenTCOx FROM `tbl_ttchung_ag` group by SoHD ";
	$result=mysqli_query($conn,$sql) or die('Could not select data'.mysqli_error($conn));
	while ($set=mysqli_fetch_array($result,MYSQLI_ASSOC))  {
		if(!isset($dataArr[$set['SoHD']])) $soHD_String.="'".$set['SoHD']."',";
		$dataArr[$set['SoHD']]['TongDVTC_ChuaChuyenTCOx'] = (int)$set['TongDVTC_ChuaChuyenTCOx'];
		$dataArr[$set['SoHD']]['TongDVTC_DaChuyenTCOx'] = (int)$set['TongDVTC_DaChuyenTCOx']; 
		//if($set['TongDVTC_ChuaChuyenTCOx']>0)$dataArr[$set['SoHD']]['TangTCOx'] = '<a href="#"><span class="text-danger">Tăng TCOx</span></a>';
	}
	
	//Lấy những hợp đồng A dư số tín chỉ chưa tăng TCOx trong hợp đồng
	$sql="SELECT c.SoHD, a.TongTienChuyen, t.TongTCOx, floor((a.TongTienChuyen -coalesce( t.TongTCOx,0) * c.SoDVTC * c.SonamHD * 1260000)/ (c.SoDVTC * c.SonamHD * 1260000)) as SoDVTC_ChuaChuyenTCOx FROM tbl_hopdong_ttchung c JOIN ( SELECT SoHD, SUM(SotienChuyen) AS TongTienChuyen FROM tbl_ttchung_a GROUP BY SoHD ) a ON a.soHD = c.SoHD LEFT JOIN ( SELECT SoHD, SUM(SoTCOx) AS TongTCOx FROM tbl_ttchunga_tcox GROUP BY SoHD ) t ON t.SoHD = c.SoHD WHERE c.LoaiHD = 'A' AND ( a.TongTienChuyen -coalesce( t.TongTCOx,0) * c.SoDVTC * c.SonamHD * 1260000 ) > (c.SoDVTC * c.SonamHD * 1260000)";
	$result=mysqli_query($conn,$sql) or die('Could not select data'.mysqli_error($conn));
	while ($set=mysqli_fetch_array($result,MYSQLI_ASSOC))  {
		if(!isset($dataArr[$set['SoHD']])) $soHD_String.="'".$set['SoHD']."',";
		//$dataArr[$set['SoHD']]['TangTCOx'] = '<a href="#" class="text-danger">Tăng TCOx</a>';
		$dataArr[$set['SoHD']]['TongTienNop'] = $set['TongTienChuyen'];
		$dataArr[$set['SoHD']]['TongDVTC_DaChuyenTCOx'] = (int)$set['TongTCOx'];
		$dataArr[$set['SoHD']]['TongDVTC_ChuaChuyenTCOx'] =$set['SoDVTC_ChuaChuyenTCOx'];
		
	}
	$soHD_String.="'')";
	//Lấy các thông tin về HĐ:
    $sql="SELECT `SoHD`, `MaKhach`, `LoaiHD`, `SonamHD`, `SoDVTC`,`maNV_nhap`, `fullname_NVnhap`, `maNV_banhang`, `fullname_NVbanhang` FROM `tbl_hopdong_ttchung` WHERE TrangThaiHD ='Đang hoạt động' and `SoHD` in ".$soHD_String;
    if(isset($_POST['search'])){
        $textcond=mysqli_real_escape_String($conn,$_POST['textcond']);
        if(!empty($textcond)) {
            $sql.="AND (upper(soHD) LIKE '%".$textcond."%') ";}
    }
    $sql.=" ORDER BY NgayNopTien1 DESC ";
    $result=mysqli_query($conn,$sql) or die('Could not select data'.mysqli_error($conn));
    $no=0;
	if(mysqli_num_rows($result))
    while ($set=mysqli_fetch_array($result,MYSQLI_ASSOC))  {
		if(isset($dataArr[$set['SoHD']])){
			$soDVTC_hd = $set['SoDVTC'];
			$sotienTinChi = $set['SonamHD']*$soDVTC_hd*1260000;

			//if ($soDVTC_hd >=12 && $soDVTC_hd %12 ==0) {$dataArr[$set['SoHD']]['TrangThaiTinChi'] = $soDVTC_hd/12; $dataArr[$set['SoHD']]['TrangThaiTinChi'].= ' TCOx';} else {$dataArr[$set['SoHD']]['TrangThaiTinChi'] = $soDVTC_hd.' ĐVTC';}
			$dataArr[$set['SoHD']]['soDVTC_hd']= $soDVTC_hd;
			$dataArr[$set['SoHD']]['MaKhach']= $set['MaKhach'];
			$dataArr[$set['SoHD']]['SonamHD']= $set['SonamHD'];	
			$dataArr[$set['SoHD']]['LoaiHD']= $set['LoaiHD'];	
			$dataArr[$set['SoHD']]['TrigiaHD_Dky']= $sotienTinChi;		
			$dataArr[$set['SoHD']]['TTNV_TV']='';
			if(!empty($set['maNV_banhang'])) {
				$dataArr[$set['SoHD']]['TTNV_TV'].= 'BH: '.$set['maNV_banhang'].'-'.$set['fullname_NVbanhang'];
			}
			else $dataArr[$set['SoHD']]['TTNV_TV'].= 'Thiếu thông tin NV tư vấn BH';
			if(!empty($set['maNV_nhap'])) {
				$dataArr[$set['SoHD']]['TTNV_TV'].= '<br/>Nhập DL: '.$set['maNV_nhap'].'-'.$set['fullname_NVnhap'];
			}
			$dataArr[$set['SoHD']]['MaKhach']= $set['MaKhach'];
			
		}
	}
		
	//Hiện dữ liệu:
	$no=0;
	if(!empty($dataArr)){
		foreach($dataArr as $soHD_key=>$soHD_value){
			if($soHD_value['TongDVTC_ChuaChuyenTCOx']>0 || $soHD_value['TongTienChuyen_Free']>$soHD_value['TrigiaHD_Dky'])	{
				if ($soHD_value['soDVTC_hd'] >=12 && $soHD_value['soDVTC_hd'] %12 ==0) {
					$TrangThaiTinChi = $soHD_value['soDVTC_hd']/12; 
					$TrangThaiTinChi.= ' TCOx';
				} 
				else {
					$TrangThaiTinChi = $soHD_value['soDVTC_hd'].' ĐVTC';
				}
				
				//Tính để hiển thị số TCOx thực của hợp đồng		
				if($soHD_value['TongDVTC_DaChuyenTCOx']>=12 && $soHD_value['TongDVTC_DaChuyenTCOx'] %12 ==0) {
					$SoTCOx_thuc = (int)($soHD_value['TongDVTC_DaChuyenTCOx']/12); 
					$ChuTCOx_thuc = $SoTCOx_thuc.' TCOx';
				}
				else{
					$SoTCOx_thuc = $soHD_value['TongDVTC_DaChuyenTCOx'];
					$ChuTCOx_thuc = $SoTCOx_thuc.' ĐVTC';
				}
				// Tính để hiển thị giá trị thực tế của hợp đồng
				if($soHD_value['LoaiHD'] == 'AG' ){
					$tongTienNop_HD = $soHD_value['TongTienChuyen_Free']+($soHD_value['TongDVTC_DaChuyenTCOx']+$soHD_value['TongDVTC_ChuaChuyenTCOx'])*1260000*$soHD_value['SonamHD'];
					$TCOx_ChoChuyen = $soHD_value['TongDVTC_ChuaChuyenTCOx'];
				} 
				else {
					$tongTienNop_HD = $soHD_value['TongTienNop'];
					if($soHD_value['TongDVTC_DaChuyenTCOx']>0){
						$TCOx_ChoChuyen = max((int)(($soHD_value['TongTienChuyen_Free'] - $soHD_value['TongDVTC_DaChuyenTCOx'] * $soHD_value['SonamHD']*1260000)/($soHD_value['TongDVTC_DaChuyenTCOx'] * $soHD_value['SonamHD']*1260000)),0);
						$TCOx_ChoChuyen = max((int)(($soHD_value['TongTienChuyen_Free'] - $soHD_value['TongDVTC_DaChuyenTCOx'] * $soHD_value['SonamHD']*1260000)/($soHD_value['TongDVTC_DaChuyenTCOx'] * $soHD_value['SonamHD']*1260000)),0);
					}
					else {
						
						$TCOx_ChoChuyen = $soHD_value['TongDVTC_ChuaChuyenTCOx'];
					}
				}
				
				//Tính tiền dư (Giá trị thực tế(tiền nộp + lãi) - (Số TCOx chờ tăng + Số TCOx đã tăng)* Trị giá HĐ)
				$TienDu = $tongTienNop_HD - ($soHD_value['TongDVTC_DaChuyenTCOx']+(int)($soHD_value['TongDVTC_ChuaChuyenTCOx'])) * $soHD_value['SonamHD']*1260000;
				$no++;
				$xhtmlItem.='<tr>
								 <td width="50px" > '.$no.' </td>
								 <td width="150px" align="right">'.$soHD_key.' / <span class="strong-text"> '.$soHD_value['LoaiHD'].'</span><br/><em>('.$soHD_value['SonamHD'].' năm) </em></td>
								 <td width="100px">'.$soHD_value['MaKhach'].'</td>
								 <td width="120px" align="right"><span class="strong-text">'.$TrangThaiTinChi.'<br/>('.number_format($soHD_value['TrigiaHD_Dky']).')</span></td>
								 <td width="145px" align="right">Thực: '.$ChuTCOx_thuc.'<br/>Chờ: '.$TCOx_ChoChuyen;
				$xhtmlItem.='</td>
								 <td width="120px" align="right"><span class="strong-text">'.number_format($tongTienNop_HD).'</span><br/><span class ="text-danger">'.number_format($TienDu).'</span></td>
								 
								 <td width="380px">'.$soHD_value['TTNV_TV'].'</td>
								 <td width="120px">';
				if($soHD_value['TongTienChuyen_Free']>$soHD_value['TrigiaHD_Dky'] && $soHD_value['LoaiHD'] =='A')				 
					$xhtmlItem.='<a href="#" onclick="XacnhanChuyenAG(\''.$soHD_key.'\')" class="text-danger">Chuyển AG</a><br/>';
				if($soHD_value['TongTienChuyen_Free']>$soHD_value['TrigiaHD_Dky'] && $soHD_value['LoaiHD'] =='AG')
					$xhtmlItem.='<a href="#" onclick="XacnhanChuyenAG(\''.$soHD_key.'\')" class="text-danger">Tăng AG</a><br/>';
				if($soHD_value['TongDVTC_ChuaChuyenTCOx']>0 && $soHD_value['LoaiHD'] =='AG')
					$xhtmlItem.='<a href="#" onclick="XacnhanTangTCOx(\''.$soHD_key.'\')" class="text-danger">Tăng TCOx</a><br/>';
				elseif($soHD_value['TongTienChuyen_Free']>=(($soHD_value['TongDVTC_DaChuyenTCOx']+1)*$soHD_value['SonamHD']*$soHD_value['soDVTC_hd']))
					$xhtmlItem.='<a href="#" onclick="XacnhanTangTCOx(\''.$soHD_key.'\')" class="text-danger">Tăng TCOx</a><br/>';
				$xhtmlItem.='</td></tr>';
			}
		}
	}
	else{
		$xhtmlItem.='<tr><td colspan="8"><span class = "text-danger strong-text"><em>Không có hợp đồng đủ điều kiện chuyển AG hoặc tăng TCOx!</em></span></td></tr>';
	}
	$xhtmlItem.='</tbody></table>';
	if(isset($_POST['excel'])){
		$textcond=!empty($_POST['textcond'])?mysqli_real_escape_String($conn,$_POST['textcond']):'';
		header('location:thuchiFunction/CT_Info-exportExcel.php?textcond='.$textcond);
	}
	
	if(isset($_POST['modal_submit'])) {
		$soHD=$_POST['idsoHD'];
		header('location:VIDIX_function/Hopdong_chuyenAlenAG.php?var='.urlencode(base64_encode($soHD)).'&var2=1');
	}

?>