<?php
session_start();
include_once '../define.php';
include_once PATH_MAIN_FUNCTION.'/conn-login-logout.php';
$conn = connection_to_database();
if ($_SERVER['REQUEST_METHOD'] == "POST"){
	$sql=	"SELECT Hoten FROM `tbl_khachhang` WHERE  CCCD = '".$_POST['soCCCD_maKhach']."' or MaKH = '".$_POST['soCCCD_maKhach']."'";
	$result = mysqli_query($conn,$sql) or die('Không thể kết nối');
	$receivInfo='Mã KH hoặc CCCD không chính xác';
	if(mysqli_num_rows($result)){
		$set=mysqli_fetch_array($result,MYSQLI_ASSOC);
		$receivInfo ='Họ tên khách hàng: '.$set['Hoten'];
	}
	echo json_encode($receivInfo );
}