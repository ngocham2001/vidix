<?php session_start(); 
  require_once ('define.php');
  include_once PATH_MAIN_FUNCTION.'/conn-login-logout.php';
  
  $conn = connection_To_Database();
 
  if(isset($_POST['submit'])) {
    $uname=mysqli_real_escape_String($conn,$_POST['uname']); 
	$pword=mysqli_real_escape_String($conn,$_POST['pword']);
	if(empty($uname))header("location: index.php?fmess=1"); 
	if(empty($pword))header("location: index.php?fmess=2");
	
	
	
	$sql = "select maNV,fullname,uname, pword,status from tbl_login where uname ='".$uname."' and `pword`=PASSWORD('".$pword."')";

	$result=mysqli_query($conn,$sql) or die('Wrong user or password'.mysqli_error($conn));
	if(mysqli_num_rows($result)==1){
		$set=mysqli_fetch_array($result,MYSQLI_ASSOC);
		$status = $set['status'];
		
		if($status == 'active') {
			$_SESSION['user_info']['logon_id']=$set['maNV'];
			$_SESSION['user_info']['fullname']=$set['fullname'];
			header("location: dashboard.php"); 
		}
		else {
			header("location: index.php?fmess=4");
		}
	}
	else
		header("location: index.php?fmess=3"); 
	
  }
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>PHẦN MỀM QUẢN LÝ TCOX - VIDIX</title>
	 <?php include_once'html/headertitle.php';?>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/signin.css" rel="stylesheet">
</head>

<body>
   <!-- Message box -->
	<div class="container" style="padding-top:15px;">
		<div class="alert alert-success alert-dismissable alert-nonedisplay" id="success-alert">
			<i class="fa fa-check"></i>
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
			<b><span id="text-success-message"></span></b>
		</div>
		
		<div class="alert alert-warning alert-dismissable alert-nonedisplay" id="warning-alert" >
			<i class="fa fa-check"></i>
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
			<b><span id="text-warning-message"></span></b>
		</div>
		
		<div class="alert alert-danger alert-dismissable alert-nonedisplay" id="danger-alert" >
			<i class="fa fa-check"></i>
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
			<b><span id="text-danger-message"></span></b>
		</div>
	</div>

    <div class="container">

      <form class="form-signin" method="POST" action="">
        <h2 class="form-signin-heading">Please sign in</h2>
        <label for="inputEmail" class="sr-only">User name:</label>
        <input type="text" name="uname" id="inputEmail" class="form-control" placeholder="User name" required autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" name="pword" id="inputPassword" class="form-control" placeholder="Password" required>
        <div class="checkbox">
          <label>
            <input type="checkbox" value="remember-me"> Remember me
          </label>
        </div>
        <input class="btn btn-lg btn-primary btn-block" type='submit' name='submit' value='Sign in' />
        <p>
    
    </p>
     </form>

    </div> <!-- /container -->
    <?php include_once 'html/emb_js.php';?>
	<?php include_once 'js/vidix/index.js';?>
</body>
</html>
