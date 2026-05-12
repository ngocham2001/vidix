<?php 
    session_start() ;
    include_once 'define.php';
    include_once PATH_MAIN_FUNCTION.'/conn-login-logout.php';
	
?>
<!DOCTYPE html>
<html>
    <head>
        <?php include_once'html/headertitle.php';?>
    </head>
    <body>
        <div class="container">
            <!-- NAVBAR -->
            <nav class="navbar navbar-default">
                <div class="container-fluid">
                	<!--LEFT NAVBAR -->
                    <div class="navbar-header">
                        <?php include_once 'html/topmenu-left.php'; ?>
                    </div>
                  	<!--RIGHT NAVBAR -->
                    <div id="navbar" class="navbar-collapse collapse">
                        <?php include_once 'html/topmenu-right.php';  ?>
                    </div><!--/.nav-collapse -->
                </div><!--/.container-fluid -->
            </nav>
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
            <!-- CONTENT -->
			
            <div class="bs-example">
                <img src="img/vidix_logo.jpg" alt="Center Align image in bootstrap" class="img-rounded img-responsive center-block">
            </div>    
			<?php if (!empty($message)){ ?>
			<p style = "margin-top: 25px;" class = "alert alert-danger"><strong><?php echo $message;?></strong></p><?php } ?>
        </div>
		
		<!-- *******Entry form**************-->
			<div class="entry-form" id="changepass">
				<form action="VIDIX_function/changepass.php" method="POST" name="changepass_frm" class="form-inline" id="changepass_frm">
					<strong><p id="titleEditReceiv" >Đổi mật khẩu</p></strong>
					<div  class = "error " id = "err_Mss"> </div>
					<p>
					<input type="password" id="cPass" style = "width: 273px;" placeholder="Nhập mật khẩu hiện tại" name="cPass"/></p>
					<p>
					<input type="password" id="nPass1" style = "width: 273px;" placeholder="Nhập mật khẩu mới" name="nPass1"/></p>
					
					<p><input type="password" id="nPass2" style = "width: 273px;" placeholder="Nhập lại mật khẩu mới" name="nPass2"/></p>
						
					<input type="submit" value="Save" name="submit" id="submit" />
					<input type="button" value="Cancel" name="cancel" id="cancel"/> 
				</form>
			</div>
			
			
    <?php include_once 'html/emb_js.php';?>
    <?php include_once 'js/vidix/login.js';?>
          
    </body>

</html>