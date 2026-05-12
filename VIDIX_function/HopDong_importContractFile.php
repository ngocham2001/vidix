<?php
session_start();
include_once '../define.php';
include_once PATH_MAIN_FUNCTION.'/conn-login-logout.php';
$conn = connection_to_database();

	$prefix = $_POST['file_type'];
	if($prefix == "Khac"){$prefix = $_POST['typeOfContract'];}
	$soHD = $_POST['soHD_contract'];
	$currentdate=date("Y-m-d");
	$currentDateAndTime = date("Y-m-d H:i:s"); 
	$ext = explode('.', basename($_FILES['choose_file']['name']));   // Explode file name from dot(.)
	$file_extension = end($ext); // Store extensions in the variable.	
	
	//Random string for attach file
	$arrCharacter=array_merge(array('A','Z'),array('a','z'),array('0','9'));	
	$arrCharacter=implode('',$arrCharacter);
	$arrCharacter=str_shuffle($arrCharacter);
	$resultCharacter=substr($arrCharacter,0,2);
	
	//Get ID for contract file
	
	$validextensions = array("doc","docx","xls","xlsx","pdf","xlsm","jpg","jpeg","bmp");      // Extensions which are allowed.
	$url_file = $prefix."-".$soHD."-".$currentdate."-".$resultCharacter.".".$file_extension;
	$target_path = PATH_UPLOAD."/Hopdong/".$prefix."-".$soHD."-".$currentdate."-".$resultCharacter.".".$file_extension;     // Set the target path with a new name of image.
	if (($_FILES["choose_file"]["size"] < 100000000)     // Approx. 100kb files can be uploaded.
		&& in_array($file_extension, $validextensions)) {
		if (move_uploaded_file($_FILES['choose_file']['tmp_name'], $target_path)) {
	// If file moved to uploads folder.
			mysqli_query($conn,"INSERT INTO `tbl_hopdong_file`(`ID`, `SoHD`, `LoaiFile`, `linkFile`) VALUES (NULL, '".$soHD."','".$prefix."','".$url_file."')") or die( mysqli_error($conn));
		} else {     //  If File Was Not Moved.
			echo '<span id="error">  ***Please try again!***</span><br/><br/>';}
	
	} else {     //   If File Size And File Type Was Incorrect.
	echo '<span id="error">***Invalid file Size or Type***</span><br/><br/>';
	}
	$sqlinsert = "INSERT INTO `tbl_theodoi`(`ID`, `IDlogon`, `Action`, `AtTime`) VALUES (NULL,'".$_SESSION['user_info']['logon_id']."','Upload file: ".$url_file." to tbl_hopdong_file','".$currentDateAndTime."') ";
	mysqli_query($conn,$sqlinsert) or die("Could not connect. " .mysqli_error($conn));	
	header("location:../TT_Hopdong_Chitiet.php?var=".urlencode(base64_encode($soHD)));
	
?>