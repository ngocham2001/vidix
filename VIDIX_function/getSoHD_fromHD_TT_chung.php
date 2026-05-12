<?php
session_start();
include_once '../define.php';
include_once PATH_MAIN_FUNCTION.'/conn-login-logout.php';
$conn = connection_to_database();
if ($_SERVER['REQUEST_METHOD'] == "POST"){
	$sql=	"SELECT 1 FROM `tbl_hopdong_ttchung` WHERE SoHD = '".$_POST['SoHD']."'";
	$result = mysqli_query($conn,$sql) or die('Không thể kết nối');
	$receivInfo=0;
	if(mysqli_num_rows($result)){
		$receivInfo =1;
	}
	echo json_encode($receivInfo );
}