<?php
session_start();
include_once '../define.php';
include_once PATH_MAIN_FUNCTION.'/conn-login-logout.php';
$conn = connection_to_database();

$maNV = $_SESSION['user_info']['logon_id'];
/* backup the db OR just a table */
$cPass = $_POST['cPass'];
$nPass1 = $_POST['nPass1'];
$nPass2 = $_POST['nPass2'];

$sql=	"SELECT `pword` FROM `tbl_login` WHERE `pword`= PASSWORD('".$cPass."') and maNV = '".$maNV."'";
$result = mysqli_query($conn,$sql) or die('Không thể kết nối tới bảng');
$set = mysqli_fetch_array($result,MYSQLI_ASSOC);
if(mysqli_num_rows($result)==1)
{
	$sqlupdate = "UPDATE `tbl_login` SET `pword`=PASSWORD('".$nPass1."') WHERE maNV = '".$maNV."'";
	mysqli_query($conn,$sqlupdate) or die('Không thể kết nối tới bảng');
	header('location:../Login.php?fmess=4');
} 
else
{
	header('location:../Login.php?fmess=3');
	exit();
}
	

/* if($oPass != $cPass) {
	
}
else {
	
	//header('location:../Login.php?fmess=3');
} */
	

?>