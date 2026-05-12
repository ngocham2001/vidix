<?php 
    include_once 'PHP/TT_Hopdong_DuDKChuyenAG_PHP.php';
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
        </div>
            <!-- CONTENT -->
            <div class="container">
                <h2>DANH SÁCH HỢP ĐỒNG ĐỦ ĐIỀU KIỆN CHUYỂN AG</h2>
                <form action="" method="POST">
                      <!--<a href="#" class="btn btn-primary" onclick="newConAndCus();">Thêm Hợp đồng & Khách hàng mới</a>
                      <a href="#" class="btn btn-primary" onclick="newConOnly();">Thêm Hợp đồng mới</a>
                   
					<button type="submit" name="excel" id="excel" class="btn btn-primary">  Excel </button> -->
                    <span class='pull-right'>
                       
                        <input type="text" name="textcond" <?php if(!empty($_POST['textcond'])){echo "value=".$_POST['textcond'];} ?> size="30"/>
						
                        <button class="btn btn-default" type="submit" name="search">
                            <span class="glyphicon glyphicon-search"></span>
                        </button>
                        <!-- <button type="submit" name="search" value="Search"/> -->
                    </span>
                </form>
            </div>
                
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

			<!-- Confirm Change -->
		
		<div class="modal fade" id="modal-confirmChange" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header " id ="modal-DelProcess-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<div class="modal-title text-danger-message strong-text" id="myModalLabel">
							<i class="glyphicon glyphicon-exclamation-sign"></i> 
							<span id="modal-header-text">Xác nhận chuyển hợp đồng từ A lên AG</span>
						</div>
					</div>
					<div class="modal-body" id ="modal-DelProcess">
						
					</div>
					<div class="modal-footer">
					<form action="" name="delete-item" method="POST">
						<input type="hidden" name="idsoHD" id="idsoHD" />
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-danger strong-text" name="modal_submit" id="modal_submit">Chuyển AG</button>
						
					</form>
					</div>
				</div>
			</div>
		</div>    
			
                <!-- Data table -->
            <div class="container">		
                
                    <?php echo $xhtmlItem; ?>

                    </tbody>
                </table>                
            </div>  

        <!-- *******Entry form**************-->
			     
           
		 <!-- *******Entry form**************<input type="text" id="OnlyCon_HotenKH" name="OnlyCon_HotenKH" style = "width:305px;"/>-->
			     
        <?php include_once 'html/emb_js.php';?>
		<?php include_once 'js/vidix/TT_Hopdong_DuDKChuyenAG.js';?>

        
    </body>

</html>