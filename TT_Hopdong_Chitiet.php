<?php 
    include_once 'PHP/TT_Hopdong_Chitiet_PHP.php';
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
			<h2>THÔNG TIN CHI TIẾT HỢP ĐỒNG - <?php echo $soHD;?> (<?php echo $Trangthaiclass;?>)</h2>
			<?php if($TrangThaiHD == 'Đang hoạt động'){ ?>
			<a href="#" onclick="ThemTTNopTien('<?php echo $soHD; ?>')" class="btn btn-primary strong-text">Nhập TT nộp tiền</a>
			<?php echo $xhtmlButtonUngTien;?>
			<?php } ?>
			<a href="VIDIX_function/PDF_HD_Giatrihoanlai.php?var=<?php echo urlencode(base64_encode($soHD));?>"  class="btn btn-primary strong-text" >In bản minh họa</a>
			
			<?php if(($LoaiHD =='AG' && $soduDVTC>0 && $TrangThaiHD == 'Đang hoạt động')||($LoaiHD =='A' && $hesoChuyenTCOx>0 )){?>
				<a href="#" onclick="XacNhanTangTCOx()" class="btn btn-warning strong-text" >Xác nhận tăng TCOx</a>
			<?php } ?>
			<!--<a href="functions/export_excel_PaymentRequest.php"  class="btn btn-primary" >Payment Request </a> -->
			
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
                
			</div>
            <!-- Thông tin chung -->
		<div class="container">
			<div class = "row" style="height: 35px;"	>
				<div class = "col-md-2"><strong>Thông tin Khách hàng</strong></div>
				<div class = "col-md-10"><strong><?php echo $MaKhach.' - '.$HoTen.' - &nbsp; &nbsp; Số điện thoại: '.$SoDT;?></strong></div>
			</div>
			<div class = "row" style="height: 35px;">
				<div class = "col-md-2"></div>
				<div class = "col-md-10">
					<p><strong>CCCD/Hộ chiếu: </strong><?php echo $CCCD.'. Cấp ngày: '.$NgaycapCCCD?> | &nbsp; <strong>Ngày sinh:</strong> <?php echo $NgaySinh;?> <strong> | &nbsp; Giới tính: </strong><?php echo $GioiTinh;?> <strong> | &nbsp; Email: </strong><?php echo $Email;?> </p>
					<p> <strong>Nơi ở hiện tại: </strong><?php echo $NoiOHientai; ?> </p>
					<p><strong>Hộ khẩu thường trú: </strong> <?php echo $HKThuongtru;?></p>
					<p><strong>Dân tộc: </strong><?php echo $DanToc;?> &nbsp; | &nbsp; <strong>Quốc tịch:</strong> <?php echo $QuocTich;?> &nbsp; |&nbsp; <strong>Trình độ HV:</strong> <?php echo $TrinhDoHocVan;?> &nbsp; | &nbsp; <strong>Tình trạng hôn nhân:</strong> <?php echo $TinhTrangHonnhan;?> </p>
					<p><strong>Tình trạng sức khỏe:</strong> <?php echo $TinhTrangSucKhoe;?></p>
				</div>
			</div>
			<div class = "row" style="height: 35px;">
				<div class = "col-md-2"><strong>Thông tin Hợp đồng</strong></div>
				<div class = "col-md-10">
					<p><strong>Loại HĐ: <?php echo $LoaiHD.' &nbsp; - &nbsp; Số tín chỉ: '.$soTinChiChung.'  &nbsp; - &nbsp; Ngày PHHĐ: '.$NgayPHHD.'  &nbsp; - &nbsp; Ngày nộp tiền L1: '.$NgayNopTien1;?></strong></p>
					<p><strong>TT Ngân hàng: &nbsp; &nbsp;  Số tài khoản: </strong><?php echo $Sotaikhoan;?> &nbsp; | &nbsp; <strong>Ngân hàng:</strong> <?php echo $TenNganHang;?> &nbsp; |&nbsp; <strong>Chủ tài khoản:</strong> <?php echo $HotenChuTK;?> </p>
				
				</div>
				<!--<div class = "col-md-9">
					<?php //echo $vendorname;?> &emsp; <a data-toggle="collapse" href="#collapseExample" aria-expanded="false" aria-controls="collapseExample">See more...</a>
					
				</div>-->
			</div>
			<div class = "row" style="height: 35px;">
				<div class = "col-md-2"><strong>Trị giá Hợp đồng</strong></div>
				<div class = "col-md-10">
					<p><strong>Trị giá HĐ: </strong> <span class = "text-danger"><?php echo number_format($trigiaHD,0);?> đ </span> &nbsp; - &nbsp; <strong>Tổng số tiền đã nộp: </strong><span class = "text-danger"><?php echo number_format($tongnop,0);?> đ</span> &nbsp; - &nbsp; <?php echo $tomtatUngTien;?></p>
					<p><span class = "strong-text">Giá trị hoàn lại của HĐ đến ngày <?php echo $ngayhientai;?>:</span></p>
				</div>
			</div>
			  <!-- Vendor information 
			<div class="collapse" id="collapseExample">
			  <div class="card card-body">
				<?php //echo $xhtmlContentVendor;?>
			  </div>
			</div>-->
			
			
			<div class = "row" style="height: 25px;">
				<div class = "col-md-2"><strong>File: </strong></div>
				<div class = "col-md-10">
					<?php if(!empty($xhtmlContractFile)) echo $xhtmlContractFile;?>
					<p><a href="#" onclick="addContractfile()" >Upload file... </a></p>
				</div>
			</div>
		</div> 
		
		<div class="container">
			
			<?php echo $xhtmlNoptien;	?>
			<?php echo $xhtmlUngTien;	?>
			<?php if (isset($xhtmlTTA)) echo $xhtmlTTA;	?>
			<?php if (isset($xhtmlTTAG)) echo $xhtmlTTAG;	?>
			
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

	<!-- Confirm Delete -->
		
		<div class="modal fade" id="modal-confirmDelete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header " id ="modal-DelProcess-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h3 class="modal-title" id="myModalLabel">
							<i class="glyphicon glyphicon-exclamation-sign"></i> 
							<span id="modal-header-text">Tăng TCOx</span>
						</h3>
					</div>
					<div class="modal-body strong-text" id ="modal-DelProcess">
						
					</div>
					<div class="modal-footer">
						<div id="myElement" data-value="<?php echo $soHD?>"></div>
						<div id="myElement2" data-value="<?php echo $hesoChuyenTCOx ?>"></div>
						<button type="button" class="btn btn-default" data-dismiss="modal">Bỏ qua</button>
						<button type="button" class="btn btn-danger" name="Tang_TCOx" id="Tang_TCOx">Đồng ý tăng</button> 
					</div>
				</div>
			</div>
		</div>    

		<!---Input Delivery --->
		<div class="entry-form-medium" id="input_NopTien">
			<form action="VIDIX_function/HopDong_Chitiet_NhapNopTien.php"  name="input_Delivery" method="POST">
				<p><strong>Thông tin nộp tiền</strong></p>
				<div class = "error " id = "err_Mss"> </div>
				<strong>Tin nhắn nộp tiền: </strong><input type="text" name="N_tinNopTien" id="N_tinNopTien" value=""/>
				<span style="font-style: italic; font-weight: normal;">*Cú pháp: VIDIX số HĐ TCOX số ĐT chủ HĐ</span><br/>
				<input type="text" style="width:220px;" name="N_tinNopTien_soHD" id="N_tinNopTien_soHD" placeholder="Số hợp đồng"/> &emsp; &emsp;
				<input type="text" style="width:220px;" name="N_tinNopTien_soDT" id="N_tinNopTien_soDT" placeholder="Số điện thoại"/>
				<strong>Ngày nộp &nbsp; </strong><input type="text" style="width:110px;"  name="N_Noptien_ngay" id="N_Noptien_ngay" value=""/> &nbsp; &emsp; 
				<strong>Số tiền nộp: &nbsp; </strong><input type="text" style="width:170px;"  name="N_Noptien_sotien" id="N_Noptien_sotien" value=""/>
				<textarea rows="2" class="form-control " id="N_Ghichu_Noptien" placeholder="Ghi chú" name="N_Ghichu_Noptien"></textarea>
			
				<input type="hidden" name="N_Noptien_SoHD" id="N_Noptien_SoHD" value="<?php echo $soHD;?>"/>
				<input type="hidden" name="N_Noptien_SoDT" id="N_Noptien_SoDT" value="<?php echo $SoDT;?>"/>
				<input type="hidden" name="N_Noptien_sonamHD" id="N_Noptien_sonamHD" value="<?php echo $SonamHD;?>"/>
				<input type="hidden" name="N_Noptien_DVTC" id="N_Noptien_DVTC" value="<?php echo $SoDVTC;?>"/>
				<input type="hidden" name="N_Noptien_LoaiHD" id="N_Noptien_LoaiHD" value="<?php echo $LoaiHD;?>"/>
				<input type="hidden" name="N_Noptien_soLanNop" id="N_Noptien_soLanNop" value="<?php echo $soLanNop;?>"/>
				
				<input type="submit" value="Save" name="submit_N_noptien" id="submit_N_noptien" />
				<input type="button" value="Cancel" name="cancel_N_noptien" id="cancel_N_noptien"/> 
            </form>
		</div>
		
		<!---Edit Delivery
		<div class="entry-form" id="edit_Delivery">
			<form action="functions/edit_Delivery_form.php"  name="edit_Delivery" method="POST">
				<strong><p>Edit Delivery </p></strong>
				<strong>Deli. Qty: &emsp; </strong><input type="text" style="width:165px; padding-left: 10px;" name="edit_QtyDelivery" id="edit_QtyDelivery"/><br/>
				<strong>Deli. date: &emsp;  </strong><input type="text" style="width:160px; padding-left: 10px;" name="edit_DeliDate" id="edit_DateDelivery"/>
				<input type="hidden" name="edit_IDDeli" id="edit_IDDeli"/>
				
				<input type="submit" value="Save" name="submit_EditDelivery" id="submit_EditDelivery" />
				<input type="button" value="Cancel" name="cancel_EditDelivery" id="cancel_EditDelivery"/> 
            </form>
		</div> --->
		
		<!---Edit Payment
		<div class="entry-form" id="edit_Payment">
			<form action="functions/edit _Payment_form.php"  name="edit_Payment" method="POST">
				<strong><p>Edit Payment </p></strong>
				PR Inf.:<br/>
				<strong>Code: </strong><input type="text" style="width:235px;" name="edit_Payment_Item" id="edit_Payment_Item" value=""/>
				
				<strong>Description: </strong> <input type="text" style="width:192px;" name="edit_Payment_Des" id="edit_Payment_Des" value=""/>
				<hr style = "border-top: 1px solid #c0c0c0;">
				Payment Inf.:<br/>
				<strong>Payment Value: </strong><input type="text" style="width:170px;" name="edit_Payment_Val" id="edit_Payment_Val" value=""/>
				
				<strong>Payment Date: </strong><input type="text" style="width:175px;" name="edit_Payment_Date" id="edit_Payment_Date" value=""/>
				
				<strong>Invoice No: </strong><input type="text" style="width:195px;" name="edit_Payment_InvoiceNo" id="edit_Payment_InvoiceNo" value=""/>
				
				<strong>Invoice Date: </strong><input type="text" style="width:183px;" name="edit_Payment_InvoiceDate" id="edit_Payment_InvoiceDate" value=""/>
				
				<input type="hidden" name="edit_ID_payment" id="edit_ID_payment" value=""/>
				<input type="hidden" name="edit_ID_prdetail_payment" id="edit_ID_prdetail_payment" value=""/>
				<input type="hidden" name="edit_PaymentVal_old" id="edit_PaymentVal_old" value=""/>
				
				<input type="submit" value="Save" name="submit_EditPayment" id="submit_EditPayment" />
				<input type="button" value="Cancel" name="cancel_EditPayment" id="cancel_EditPayment"/> 
            </form>
		</div> --->
		
		<!---Input Payment 
		<div class="entry-form" id="input_Payment">
			<form action="functions/input_Payment_form.php"  name="input_payment" method="POST">
				<strong><p >Input Payment </p></strong>
				
				 <input type="radio" name="Ptype" id="P_Qty" value="PaymentQty">
				 <input type="text" style="width:100px;" name="input_PaymentQty" id="input_PaymentQty" value="" placeholder = "Payment Qty"/> &emsp;
				
				<input type="radio" name="Ptype" id="P_Val" value="PaymentValue">
				<input type="text" style="width:120px;" name="input_PaymentVal" id="input_PaymentVal" value="" placeholder = "Payment Value"/>
				
				<strong>Payment date: </strong><input type="text" style="width:180px;" name="input_PaymentDate" id="input_PaymentDate" value="" />
				
				<strong>Invoice: </strong><input type="text" style="width:100px;" name="input_PaymentInvoiceNo" id="input_PaymentInvoiceNo" value="" placeholder = "Invoice No"/>
				
				<input type="text" style="width:120px;" name="input_PaymentInvoiceDate" id="input_PaymentInvoiceDate" value="" placeholder = "Invoice Date"/>
				
				<input type="hidden" name="input_PaymentContractNo" id="input_PaymentContractNo" value=""/>
				<input type="hidden" name="input_Paymentidprdetail" id="input_Paymentidprdetail" value=""/>
				<input type="hidden" name="input_TotalVal" id="input_TotalVal" value=""/>
				
				<input type="submit" value="Save" name="submit_InputPayment" id="submit_InputPayment" /> 
				<input type="button" value="Cancel" name="cancel_InputPayment" id="cancel_InputPayment"/> 
            </form>
		</div>--->
		
		
	<!-- Import Contract file-->
		<div class="entry-form" id="importContractFile_form">
			<form action="VIDIX_function/HopDong_importContractFile.php"  enctype="multipart/form-data" name="editItemInPC" method="POST">
				<strong><p>Upload File Hợp đồng </p></strong>
				<p><input type="file" name="choose_file" id="choose_file"/></p>
				<p><select name = "file_type" id="file_type">
					<option value="">Loại File</option>
					<option value="Hop_Dong">Hợp Đồng</option>
					<option value="Phu_Luc">Phụ Lục</option>
					<option value="Bien_ban">Biên Bản</option>
					<option value="Thanh_Ly_Hop_Dong">Thanh Lý Hợp Đồng</option>
					<option value="Khac">Khác</option>
				</select>
				<input type="hidden" name="soHD_contract" id="soHD_contract" value = "<?php echo $soHD;?>"/></p>
				<input style="display:none; width:275px;" name="typeOfContract" id="typeOfContract" value = ""/></p>
				
			<input type="submit" value="Save" name="submit_ContractFileForm" id="submit_ContractFileForm" />
			<input type="button" value="Cancel" name="cancel_ContractFileForm" id="cancel_ContractFileForm"/> 
            </form>
		</div>	
		
		<!-- Edit item in Contract
		<div class="entry-form" id="editItemInContract_form">
			<form action="functions/editItemIncontract.php" name="editItemInPC" method="POST">
				<strong>Edit Item</strong> <br/>
				PR Infor.:<br/>
			<strong>Code: </strong><input type="text" style="width:230px;" name="edit_itemCode" id="edit_itemCode" value=""/>
			<strong>Description: </strong><input type="text" style="width:190px;"name="edit_itemdescription" id="edit_itemDescription" value=""/>
			<hr style = "border-top: 1px solid #c0c0c0;">
			Contract Infor.:<br/>

			<strong>Quant. In Contract: </strong>&emsp;<input type="text" style="width:85px;" name="edit_itemQtyInContract" id="edit_itemQtyInContract" value=""/> <br/>
			<strong>Deliveried days: </strong><input type="text" style="width:100px;" name="edit_itemETAInContract" id="edit_itemETAInContract" value=""/><br/>
			<strong>Price: </strong><input type="text" style="width:125px;" name="edit_itemPriceInContract" id="edit_itemPriceInContract" value=""/>
			<label for edit_itemTaxInContract>Tax. </label>
			<select name = "edit_itemTaxInContract" id="edit_itemTaxInContract" >
				<option value='0.00' >0%</option>
				<option value='0.05'>5%</option>
				<option value='0.07'>7%</option>
				<option value='0.08'>8%</option>
				<option value='0.10' >10%</option>
			</select>
			
			<input type="hidden" name="edit_idIteminContract" id="edit_idIteminContract" value=""/>
			<input type="hidden" name="edit_ContractNo" id="edit_ContractNo" value=""/>
			<input type="hidden" name="edit_ContractDate" id="edit_ContractDate" value=""/>
			<input type="hidden" name="edit_oldQty" id="edit_oldQty" value=""/>
			<input type="hidden" name="edit_idprdetail" id="edit_idprdetail" value=""/>
			<input type="submit" value="Save" name="submit_EditItemInContract" id="submit_EditItemInContract" />
			<input type="button" value="Cancel" name="cancel_EditItemInContract" id="cancel_EditItemInContract"/> 
            </form>
		</div>	-->
        <?php 	
			include_once 'html/emb_js.php';
			include_once 'js/vidix/TT_Hopdong_Chitiet.js';
			
		?>

        
    </body>

</html>