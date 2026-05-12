<?php
session_start() ;
include_once '../define.php';
require PATH_CLASS.'/vendor/autoload.php';
include_once PATH_MAIN_FUNCTION.'/conn-login-logout.php';

// create new PDF document
$pdf = new \Mpdf\Mpdf([
		'mode' =>'utf-8',
		'format' => 'A4-L',
		'orientation' => 'L'
		]);

$pdf->SetFont('times', 'I', 9);
//Gán Giá trị khởi tạo:
$conn = connection_to_database();
$soHD = urldecode(base64_decode($_GET['var']));
$ngayhientai = date('Y-m-d');
$TTNVBH ='';
$TTNVnhap ='';
//Get data from database
//BẢNG HOPDONG_TTCHUNG:
$sql = "SELECT `MaKhach`, `LoaiHD`, `NgayNopTien1`, `NgayPHHD`, `SoDVTC`, `SonamHD`, `TudongTangAG`, `maNV_banhang`, `fullname_NVbanhang`, `maNV_nhap`, `fullname_NVnhap` FROM `tbl_hopdong_ttchung` WHERE `SoHD` = '".$soHD."'";
$result=mysqli_query($conn,$sql) or die('Could not select Machine'.mysqli_error($conn));
$setselect=mysqli_fetch_array($result,MYSQLI_ASSOC);
$maNV_banhang = $setselect['maNV_banhang'];
$fullname_NVbanhang = $setselect['fullname_NVbanhang'];
$maNV_nhap = $setselect['maNV_nhap'];
$fullname_NVnhap = $setselect['fullname_NVnhap'];
if(empty($maNV_banhang) && empty($fullname_NVbanhang)) {
	$TTNVBH = '<span class = "error strong-text" >Thiếu thông tin nhân viên tư vấn</span>';
}
else{
	$TTNVBH = $maNV_banhang.' - '.$fullname_NVbanhang;
	}
if(empty($maNV_nhap) && empty($fullname_NVnhap)) {
	$TTNVnhap = '<span class = "error strong-text" >Thiếu thông tin nhân viên tư vấn</span>';
}
else{
	$TTNVnhap = $maNV_nhap.' - '.$fullname_NVnhap;
	}	
$LoaiHD = $setselect['LoaiHD'];
$MaKH = $setselect['MaKhach'];
$SoDVTC = $setselect['SoDVTC'];
$NgayNopTien1 = $setselect['NgayNopTien1'];
$SonamHD = $setselect['SonamHD'];
$TudongTangAG = $setselect['TudongTangAG'];
//Tính toán:	
$trigiaHD = $SoDVTC*$SonamHD*1260000;$sotienNopHangNam = $SoDVTC*1260000;
if ($SoDVTC >=12 && $SoDVTC%12 ==0) {$TinChi = $SoDVTC/12; $TinChi = $TinChi.' TCOx';} else {$TinChi = $SoDVTC.' ĐVTC';}
$tienTrian = 13000000*($SoDVTC/12)*0.5;
//BẢNG KHACHHANG:
$sql = "SELECT `HoTen` FROM `tbl_khachhang` WHERE `maKH` = '".$MaKH."'";
$result=mysqli_query($conn,$sql) or die('Could not select Machine'.mysqli_error($conn));
$setselect=mysqli_fetch_array($result,MYSQLI_ASSOC);
$HoTenKH = $setselect['HoTen'];

//Tinh BẢNG MINH HOA:
$data = array();
$nam=0;
for($nam=0;$nam<$SonamHD ;$nam++){
	
	if($nam==0) {
		$data[$nam]['Ngaynoptien'] = $NgayNopTien1;
		$data[$nam]['Laisuat'] = 0;
		$data[$nam]['GocNLaisuat'] = $sotienNopHangNam;
		$data[$nam]['NgaybatdauCK'] = date('Y-m-d',strtotime($NgayNopTien1.' + 90 days'));
	}
	else {
		$Ngaynoptien_temp = date('Y-m-d',strtotime($NgayNopTien1.' + '.$nam.' years'));
		$data[$nam]['Ngaynoptien'] = $Ngaynoptien_temp;
		$data[$nam]['Laisuat'] = $data[$nam-1]['GocNLaisuat'] * 0.05;
		$data[$nam]['GocNLaisuat'] = $data[$nam-1]['GocNLaisuat'] +  $sotienNopHangNam + $data[$nam]['Laisuat'];
		$data[$nam]['NgaybatdauCK'] = date('Y-m-d',strtotime($Ngaynoptien_temp.' + 90 days'));
	}
	if($nam>2){
		$data[$nam]['GiatriHoanLai'] = $data[$nam]['GocNLaisuat'];
	}
	else{
		$data[$nam]['GiatriHoanLai'] = $data[$nam]['GocNLaisuat']-11000*12*($nam+1);
	}
	
}

$html = '<link href="../../css/MPDF_style.css" rel="stylesheet">
		<table border = "0" style="line-height: 1.8">
			<tr>
				<td colspan="2" width = "800" align = "center" >
					<h2>VIDIX - BẢNG MINH HỌA QUYỀN LỢI TÙY CHỌN A</h2> 
					<p style="font: Times;";><i> Hợp đồng số: '.$soHD.' / Ngày tham gia: '.date('d-m-Y',strtotime($NgayNopTien1)).'</i>
				</td>
				<td align = "right"><img src="'.PATH_APPLICATION.'\img\VIDIX_logo.jpg" width="200"></td>
			</tr>
			<tr>
				<td></td>
				<td></td>			
				<td></td>			
			</tr>
			<tr>
				<td><strong>Khách hàng: </strong>'.$HoTenKH.'<br/>
				<strong>Số năm tham gia: </strong>'.$SonamHD.'
				</td>
				<td><strong>Nhân viên bán hàng: </strong>'.$TTNVBH.'<br/>
				<strong>Số tín chỉ đăng ký: </strong>'.$TinChi.'</td>
				<td></td>
			</tr>
		</table>
		<p style="line-height: 1.8">
		Bảng minh họa được xây dựng với: <br/>
		<span style="color: #FF0000; "><i>1. Tổng số tiền phải nộp khi đăng ký '.$TinChi.' thời hạn '.$SonamHD.' năm: '.number_format($trigiaHD,0).' đ.Số tiền nộp trong năm đầu tiên và mỗi năm sau:  '.number_format($sotienNopHangNam,0).' đ.</i></span><br/>
		<span style="color: #FF0000; "><i>2. Trị giá tối thiểu là 1 đơn vị tín chỉ bằng 1/12 tín chỉ (tương đương 105 000 đồng/tháng).</i></span><br/>
		<span style="color: #FF0000; "><i>3. Quyền lợi bảo vệ rủi ro do tai nạn trong 21 ngày xem xét hợp đồng được tính 50% tiền tri ân ('.number_format($tienTrian,0).' đ) và được nhận 100% giá trị tiền tri ân ('.number_format($tienTrian*2,0).' đ) khi hợp đồng phát hành.</i></span><br/>
		<span style="color: #FF0000; "><i>4. Nếu tử vong không do tai nạn trong vòng 36 tháng khi hợp đồng phát hành thì hợp đồng không được nhận tiền tri ân và bị trừ 21% giá trị hợp đồng thường niên năm thứ nhất. Sau 36 tháng, tiền tri ân được tính với mọi trường hợp tử vong (tử vong thường).</i></span><br/>
		<span style="color: #FF0000; "><i>5. Giá trị tiền tri ân bằng 13 lần chỉ tiêu tín chỉ đăng ký, sau mỗi 3 năm (36 tháng) được nhận thêm 01 tháng tiền tri ân.</i></span><br/>
	
		</p>
<table cellspacing="0" cellpadding="1" border="1">
	<thead>
    <tr>
        <th width="60" align="center">NĂM HĐ</th>
        <th width="130" align="center">THỜI GIAN <br/> NỘP TIỀN</th>
        <th width="140" align="center">SỐ TIỀN NỘP</th>
        <th width="130" align="center">THỜI GIAN <BR/> bắt đầu CK</th>
		<th width="170" align="center">LÃI SUẤT NHẬN ĐƯỢC</th>
        <th width="170" align="center">CỘNG VỐN GỐC</th>
        <th width="200" align="center">SỐ TIỀN NHẬN ĐƯỢC <br/> KHI THANH LÝ HĐ</th>
    </tr>
	</thead><tbody>';

//get data PR from database
$no=0;
 foreach($data as $key=>$value){
	 $no++;
	$html.='<tr >
		<td align="center">'.$no.'</td>
		<td align="center">'.$value['Ngaynoptien'].'</td>
		<td style="padding: 5px 10px;" align = "right">'.number_format($sotienNopHangNam,0).'</td>
		<td style="padding: 5px 10px;" align="center">'.$value['NgaybatdauCK'].'</td>
		<td style="padding: 5px 10px;" align="right">'.number_format($value['Laisuat']).'</td>
		<td style="padding: 5px 10px;" align="right">'.number_format($value['GocNLaisuat']).'</td>
		<td style="padding: 5px 10px;" align="right">'.number_format($value['GiatriHoanLai']).'</td>
	</tr>';
}

$html.='</tbody></table>
<table border = "0" style="font-size:12pt; line-height: 5;">
			<tr>
				<td width = "600" align = "center"></td>
				<td align = "center" ><p>Người lập biểu </p><span style="line-height: 30;"> '.$TTNVnhap.'</span> </td>
			</tr>
		</table>';

$pdf->WriteHTML($html);
// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('BanMinhHoaQuyenLoi_'.$HoTenKH.'.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
