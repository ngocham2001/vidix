<?php
function connection_To_Database(){	
	$mysql_hostname = 'localhost';
	$mysql_user = 'root';
	$mysql_password = '123456';
	$mysql_database = 'tcox.vidix';
	$conn = mysqli_connect($mysql_hostname, $mysql_user, $mysql_password,$mysql_database) or die("Could not connect database");
	mysqli_set_charset($conn,'utf8');
	return $conn;

}