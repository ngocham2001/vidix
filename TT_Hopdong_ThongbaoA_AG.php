<?php 
    include_once 'PHP/TT_Hopdong_ThongbaoA_AG_PHP.php';
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
			<h2>THÔNG TIN CHI TIẾT TÙY CHỌN A & AG </h2>
			<h4>HĐ: <?php echo $soHD;?> - <?php echo $LoaiHD;?> / <?php echo $TTNVBH;?></h4>
			<?php echo $xhtmlChuyenAG;
			if(!empty($xhtmlChuyenAG)){	
				if($LoaiHD =='A'){?>
			<a href="#" onclick="XacnhanChuyenAG(<?php echo $soHD; ?>)" class="btn btn-danger" >Xác nhận chuyển AG</a>
				<?php } else{?>
			<a href="VIDIX_function/Hopdong_chuyenAlenAG.php?var=<?php echo urlencode(base64_encode($soHD))?>"  class="btn btn-danger" > Xác nhận chuyển AG </a>
			<?php 	} 
			}?>
			<!--	<a href="functions/export_excel_contract_detail.php"  class="btn btn-primary" >Excel </a>
			<a href="functions/export_excel_PaymentRequest.php"  class="btn btn-primary" >Payment Request </a> -->
        </div>
		
		 <!-- Message box -->
            <div class="container" style="padding-top:15px;">
                <div class="alert alert-warning alert-dismissable alert-nonedisplay" id="warning-alert" >
                    <i class="fa fa-check"></i>
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <b><span id="text-warning-message"></span></b>
                </div>
				
				<div class="alert alert-success alert-dismissable alert-nonedisplay" id="success-alert">
                    <i class="fa fa-check"></i>
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <b><span id="text-success-message"></span></b>
                </div>
                
				<div class="alert alert-danger alert-dismissable alert-nonedisplay" id="danger-alert">
                    <i class="fa fa-check"></i>
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <b><span id="text-danger-message"></span></b>
                </div>
                
			</div>
            <!-- Thông tin chung -->
		<div class="container">
			<p><strong>Số tín chỉ đăng ký:</strong> <span class ="error"> <?php echo $TinChi;?> ( = <?php echo number_format($trigiaHD);?> đ)</span>
			&emsp; <strong>Số tiền đã nộp: </strong><span class ="error"> <?php echo number_format($tongnop);?> đ</span> &emsp; <strong>Số dư tùy chọn A:</strong><span class ="error"> <?php echo number_format($tongA);?> đ &emsp; </span><span class = "error"><strong>*Lưu ý: Hợp đồng <?php if($TudongTangAG ==0) echo ' không '?> tự động tăng từ A > AG.  </strong></p> 	
			
			
			
		</div> 
		
		<div class="container">
			<?php echo $xhtmlTTAG;	?>
			<?php echo $xhtmlTTA;	?>

			
		</div>
		
		<!--<div class="container">
			<p><strong>Contract Payment:</strong> 
			<?php /*
				if(number_format($RemainVal)>0){ echo '&emsp; <a href="P_Contract_payment.php" >Add more payment..</a>';}
				echo $xhtmlPaymentInfo;	
				*/
			?>
			</p>
		</div>-->

	<!-- Confirm Change -->
		
		<div class="modal fade" id="modal-confirmChange" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header " id ="modal-DelProcess-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h3 class="modal-title" id="myModalLabel">
							<i class="glyphicon glyphicon-exclamation-sign"></i> 
							<span id="modal-header-text">Xác nhận chuyển hợp đồng từ A lên AG</span>
						</h3>
					</div>
					<div class="modal-body" id ="modal-DelProcess">
						
					</div>
					<div class="modal-footer">
					<form action="" name="delete-item" method="POST">
						<input type="hidden" name="idsoHD" id="idsoHD" value="<?php echo $soHD;?>"/>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-danger-alert" name="modal_submit" id="modal_submit">Chuyển AG</button>
						
					</form>
					</div>
				</div>
			</div>
		</div>    

	
		
        <?php 	
			include_once 'html/emb_js.php';
			include_once 'js/vidix/TT_Hopdong_ThongbaoA_AG.js';
			
		?>

        
    </body>

</html>