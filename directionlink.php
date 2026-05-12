<?php 
	session_start(); 
	require_once ('define.php');
	require_once (PATH_MAIN_FUNCTION.'/conn-login-logout.php');
	$xhtml='';
	$res = mysqli_query($conn,"SELECT `id`, `logon_id`, `database`, `level`,`location`, `procurement_display`,`procurement_receive` FROM `permission` WHERE logon_id='".$_SESSION['user_info']['logon_id']."'") or die ("Could not connect to log on data");
	/* echo '<pre>';
	print_r($_SESSION);
	echo '</pre>'; */
	if(mysqli_num_rows($res)>=1){
		while($rows=mysqli_fetch_array($res,MYSQLI_ASSOC)){
			
			if($rows['database']==NULL && $rows['level']=='Administrator')
			{
				$_SESSION['user_info']['level']='Administrator';
				header('location:'.URL_ADMIN.'/index');					
			}
			else
			{
				if($rows['database']=='LAB' && $rows['level']=='Officer')
				{
					$_SESSION['user_info']['permission_id'] = $rows['id'];
					header('location:'.URL_LAB.'/inputFabric_fault.php');					
				}
				
				if($rows['procurement_display'] != 0 ){
					$xhtml.='<li>
								<a href="/'.$rows['database'].'/index.php?p='.urlencode(base64_encode($rows['id'])).'">'.$rows['database'].' - '.$rows['level'];
					if(!empty($rows['location']))
					$xhtml.=' - '.$rows['location'].'</a>
							 </li>';
				}
			
			}
		}
	}
	else
		$xhtml.='<li>You could not access into the system</li>';


?>
<!DOCTYPE>
<html>
<head>
<meta charset="utf-8">
<title>MAS - Sparepart Sys.</title>
	<link rel="shortcut icon" href="img/Mascot_logo.ico" />
    <link href="css/directionlink.style.css" rel="stylesheet">
</head>

<body>
<div id="main">
	<div id="mascotimg">
    	<img src="img/logo.jpg" />
    </div>
    <div id="wraptext">
    
    	<ul>
        	<!--<li> Software temporarily stop working for upgrades. Please come back in a few minutes. Sorry for this inconvenience.</li> -->
			<?php echo $xhtml;	?>
        </ul> 
    </div>
</div>
</body>
</html>
