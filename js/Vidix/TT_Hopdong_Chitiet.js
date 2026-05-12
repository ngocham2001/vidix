<script type="text/javascript">
$(document).ready(function() {
	//Hiện thông báo trên trang khi sửa SL giao không khớp với SL trong hợp đồng
		$.urlParam = function(name){
			var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
			if (results && results.length > 1) {
				return results[1] || 0;
			}
		}
		$(window).load(function(){
			
			if($.urlParam('fmess')==1)
			{
				$("#text-warning-message").text('Quantity in Contract is less than Quantity modified. Please try again!');
				$("#warning-alert").fadeTo("slow",1).fadeOut(10000, function(){
					$("#warning-alert").alert('close');
				});
			}
			
			if($.urlParam('fmess')==2)
			{
				$("#text-success-message").text('Bản ghi được thêm thành công!');
				$("#success-alert").fadeTo("slow",1).fadeOut(10000, function(){
					$("#success-alert").alert('close');
				});
			}
			
			if($.urlParam('fmess')==3)
			{
				$("#text-warning-message").text('Số tiền nộp không đủ số tiền đăng ký tín chỉ AG!');
				$("#warning-alert").fadeTo("slow",1).fadeOut(10000, function(){
					$("#warning-alert").alert('close');
				});
			}
			
			if($.urlParam('fmess')==4)
			{
				$("#text-warning-message").text('Không đủ điều kiện tăng TCOx của hợp đồng AG này!');
				$("#warning-alert").fadeTo("slow",1).fadeOut(10000, function(){
					$("#warning-alert").alert('close');
				});
			}
		});
	//Check all and not check all
		var $all = $('#checkAll')
		var $checks = $('input:checkbox[id^="order-"]').not($all)

		$all.on('click', function () {
			$checks.prop('checked', $(this).is(':checked'))
		})

		$checks.on('click', function() {
			var checked = $checks.filter(':checked').length
			$all.prop('checked', checked === $checks.length)
		})
		$('#checkAll').prop('checked', true);
		$('input:checkbox[id^="order-"]').prop('checked', true);

		
	//Hiện datepicker trong phần nhập date deli và invoice date
	var ETAdate = $('input[name^="ETA"]'); //our date input has the name "date"
	//var edit_ETAdate = $('input[name="edit_itemETAInContract"]'); //our date input has the name "date"
	var edit_DateDelivery = $('input[name="edit_DeliDate"]'); //our date input has the name "date"
	var N_Noptien_ngay = $('input[name="N_Noptien_ngay"]'); //our date input has the name "date"
	var input_PaymentDate = $('input[name="input_PaymentDate"]'); //our date input has the name "date"
	var input_PaymentInvoiceDate = $('input[name="input_PaymentInvoiceDate"]'); //our date input has the name "date"
	var edit_PaymentInvoiceDate = $('input[name="edit_Payment_InvoiceDate"]'); //our date input has the name "date"
	var edit_PaymentDate = $('input[name="edit_Payment_Date"]'); //our date input has the name "date"
	var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
	var options={
		format: 'yyyy-mm-dd',
		container: container,
		todayHighlight: true,
		autoclose: true,
	  };
	  
	ETAdate.datepicker(options);
	//edit_ETAdate.datepicker(options);
	edit_DateDelivery.datepicker(options);
	N_Noptien_ngay.datepicker(options);
	input_PaymentDate.datepicker(options);
	input_PaymentInvoiceDate.datepicker(options);
	edit_PaymentInvoiceDate.datepicker(options);
	edit_PaymentDate.datepicker(options);
	
	//Kiểm tra số hóa đơn và số hợp đồng nhập vào có tồn tại trong bảng TT hợp đồng không
	$("#N_tinNopTien").change(function(){
		
		var tinNopTien = $( this ).val();
		var soHD_goc = $('#N_Noptien_SoHD').val();
		var soDT_goc = $('#N_Noptien_SoDT').val();
		$('#N_tinNopTien_soHD').prop("readonly",false);
		$('#N_tinNopTien_soDT').prop("readonly",false);
		var ktra1 = tinNopTien.includes(soHD_goc);
		var ktra2 = tinNopTien.includes(soDT_goc);
		if (ktra1) {
			$('#N_tinNopTien_soHD').val(soHD_goc);
		}
		else{$('#N_tinNopTien_soHD').val("Số HĐ của tin nhắn không khớp");}
		if (ktra2) {
			$('#N_tinNopTien_soDT').val(soDT_goc);
		}
		else{$('#N_tinNopTien_soDT').val("Số ĐT của tin nhắn không khớp");}
		$('#N_tinNopTien_soHD').prop("readonly",true);
		$('#N_tinNopTien_soDT').prop("readonly",true);
	});


	
	//Hiện textbox typeOfContract khi chọn Other trong file_type selectbox:
	$("#file_type").change(function(){
	//	console.log('test');
			var temp = $( this ).val();
			if(temp == 'Khac'){
				$("#typeOfContract").show();
				$("#typeOfContract").attr("placeholder", "Nhập kiểu file cần upload");
			}
		});
	//Hiện Ngày phát hành hợp đồng sau 21 ngày:
	$("#N_Ngaynop1").change(function(){
		var temp = $( this ).val();
		if(temp == 'Khac'){
			$("#typeOfContract").show();
			$("#typeOfContract").attr("placeholder", "Nhập kiểu file cần upload");
		}
	});
	//Kiểm tra dữ liệu đầu vào khi import Contract file:
	$("#submit_ContractFileForm").click(function(){
		var choose_file = $('#choose_file').val(),
			typeOfContract = $('#typeOfContract').val(),
			file_type = $('#file_type').val();
		var errors = 0;
		if(!$.trim(choose_file).length){
			$('#choose_file').css({"border-color": "red", 
									 "border-width":"1px", 
									 "border-style":"solid"});
			errors++;
		}
		else{
			$('#choose_file').css({	"border-color": "red", 
									 "border-width":"0px", 
									 });
		}
		
		if(!$.trim(file_type).length){
			$('#file_type').css('border-color', 'red');
			errors++;
		}
		else{
			$('#file_type').css('border-color','');
		}
		
		if(file_type == 'Other' && !$.trim(typeOfContract).length){
			$('#typeOfContract').css('border-color', 'red');
			errors++;
		}
		else{
			$('typeOfContract').css('border-color','');
		}
		//$("#choose_file").val('');
		if(errors>0){ return false;}
		
	});
	//Kiểm tra dữ liệu đầu vào khi input Quantity Delivery:
	$("#submit_N_noptien").click(function(){
		var errors = 0;
		var N_Noptien_SoHD = $('#N_Noptien_SoHD').val();
		var N_Noptien_SoDT = $('#N_Noptien_SoDT').val();
		var N_tinNopTien = $('#N_tinNopTien').val();
		var N_Noptien_ngay = $('#N_Noptien_ngay').val();
		var N_Noptien_sotien = $('#N_Noptien_sotien').val();		
		var N_tinNopTien_soHD = $('#N_tinNopTien_soHD').val();
		var N_tinNopTien_soDT = $('#N_tinNopTien_soDT').val();
		var N_Noptien_LoaiHD = $('#N_Noptien_LoaiHD').val();
		var N_Noptien_DVTC = $('#N_Noptien_DVTC').val();
		var N_Noptien_sonamHD = $('#N_Noptien_sonamHD').val();
		var N_Noptien_soLanNop = $('#N_Noptien_soLanNop').val();
		var tienTC = N_Noptien_DVTC*N_Noptien_sonamHD*1260000;	 
		//console.log(tienTC);
		if(!$.trim(N_tinNopTien).length){
			$('#N_tinNopTien').css('border-color', 'red');
			errors++;
		}
		else{
			$('#N_tinNopTien').css('border-color','');
		}
		
		if(!$.trim(N_Noptien_ngay).length){
			$('#N_Noptien_ngay').css('border-color', 'red');
			errors++;
		}
		else{
			$('#N_Noptien_ngay' ).css('border-color','');
		}
		
		if(!$.trim(N_Noptien_sotien).length){
			$('#N_Noptien_sotien').css('border-color', 'red');
			errors++;
		}
		else{
			$('#N_Noptien_sotien').css('border-color','');
		}
		
		if(!$.trim(N_tinNopTien_soDT).length){
			$("#err_Mss").append('<small><i>Sai số điện thoại trong tin nhắn nộp tiền!</i></small><br/>');
			$('#N_tinNopTien').css('border-color', 'red');
			errors++;
		}
		else{
			$('#N_tinNopTien').css('border-color','');
		}
		
		if(!$.trim(N_tinNopTien_soHD).length){
			$('#N_tinNopTien').css('border-color', 'red');
			$("#err_Mss").append('<small><i>Sai số hợp đồng trong tin nhắn nộp tiền!</i></small><br/>');
			errors++;
		}
		else{
			$('#N_tinNopTien').css('border-color','');
		}
		if(N_Noptien_LoaiHD =="AG" && N_Noptien_sotien < tienTC && N_Noptien_soLanNop==0){
			$('#N_Noptien_sotien').css('border-color', 'red');
			$("#err_Mss").append('<small><i>Số tiền nộp phải lớn hơn hoặc bằng ' + addCommas(tienTC) + '</i></small><br/>');
			errors++;
		}
		else{
			$('#N_Noptien_sotien').css('border-color','');
		}
		
		//return false;
		if(errors>0){ return false;}
		
	});
	
	/* //Khi nhập dữ liệu vào Payment Qty thì disabled dữ liệu ở Payment Value
	$('#input_PaymentQty').on('input', function() {
		$('#input_PaymentVal').prop("disabled",true);
		$('#P_Qty').prop( "checked", true );
	});
	$('#input_PaymentVal').on('input', function() {
		$('#input_PaymentQty').prop("disabled",true);
		$('#P_Val').prop( "checked", true );
	}); */
	
	/* $('input:radio[name="Ptype"]').change(
		function(){
			if (this.checked && this.value == 'PaymentValue') {
			   $('#input_PaymentQty').val('');
			   $('#input_PaymentQty').prop("disabled",true);
			   $('#input_PaymentVal').prop("disabled",false);
			   
			}
			if (this.checked && this.value == 'PaymentQty') {
			   $('#input_PaymentVal').val('');
			   $('#input_PaymentVal').prop("disabled",true);
			   $('#input_PaymentQty').prop("disabled",false);
			   
			}
    });
	
	
   $('#P_Val').on('input', function() {
		$('#input_PaymentVal').prop("disabled",true);
		$('#P_Val').prop( "checked", true );
	}); */
		


	//Kiểm tra dữ liệu đầu vào khi input Payment:
	/* $("#submit_InputPayment").click(function(){
		var errors = 0;
		var input_PaymentQty = $('#input_PaymentQty').val();
		var input_PaymentVal = $('#input_PaymentVal').val();
//		var input_TotalVal = $('#input_TotalVal').val();
		
		
		if(!$.trim(input_PaymentQty).length && !$.trim(input_PaymentVal).length){
			$('#input_PaymentQty').css('border-color', 'red');
			$('#input_PaymentVal').css('border-color', 'red');
			errors++;
		}
		else{
			$('#input_PaymentVal').css('border-color','');
			$('#input_PaymentQty').css('border-color','');
		}
		
		//$("#choose_file").val('');
		if(errors>0){ return false;}
		
	}); */
	
	//Kiểm tra dữ liệu đầu vào khi edit Delivery:
	/* $("#submit_EditDelivery").click(function(){
		var errors = 0;
		var edit_qtyDeli = $('#edit_QtyDelivery').val();
		var edit_dateDeli = $('#edit_DateDelivery').val();
//		var input_TotalVal = $('#input_TotalVal').val();
		
		
		if(!$.trim(edit_qtyDeli).length){
			$('#edit_QtyDelivery').css('border-color', 'red');
			errors++;
		}
		else{
			$('#edit_QtyDelivery').css('border-color','');
		}
		if(!$.trim(edit_dateDeli).length){
			$('#edit_DateDelivery').css('border-color', 'red');
			errors++;
		}
		else{
			$('#edit_DateDelivery').css('border-color','');
		}
		
		//$("#choose_file").val('');
		if(errors>0){ return false;}
		
	}); */

	//Cancel Edit PettyCash
	$("#cancel_ContractFileForm").click(function(){
			$("#importContractFile_form").fadeOut("fast");
			//$("#choose_file").val('');
			return false;
		});
	
	$("#cancel_N_noptien").click(function(){
		$("#input_QtyDelivery").val('');
		$("#input_DateDelivery").val('');
		$("#input_QtyDelivery").css('border-color','');
		$("#input_DateDelivery").css('border-color','');
		$("#input_NopTien").fadeOut("fast");
		//$("#choose_file").val('');
		return false;
	}); 
	
	$("#Tang_TCOx").on('click',function(){
		$('#modal-confirmDelete').modal('hide');
		var soHD = $('#myElement').attr('data-value');
		var hesoChuyenTCOx = $('#myElement2').attr('data-value');
		$.ajax({
			url		: 'VIDIX_function/Hopdong_tangTCOX.php',
			type: "POST",
			dataType: "JSON",
			data	: { action: 'modal_clicked', soHD:soHD, hesoChuyenTCOx: hesoChuyenTCOx},  
			success	: function(data){
					
					
			}
		});
		
				
	}); 
	
});

function XacNhanTangTCOx(){
	$('.modal-header').addClass("alert-danger");
	$('#modal-submit').addClass("btn-danger");
	$(".modal-body").text('Hợp đồng đủ điều kiện tăng TCOx. Xác nhận khách hàng đã đồng ý tăng TCOx của hợp đồng?');
	$('#Del_item').addClass("btn-danger");
	$('#modal-confirmDelete').modal('show');
	return false;
}
function addContractfile(){
	$("#importContractFile_form").fadeIn("fast");			
}
function ThemTTNopTien(soHD){
	$('#N_tinNopTien').val("");
	$('#N_Noptien_ngay').val("");
	$('#N_Noptien_sotien').val("");
	$('#N_tinNopTien_soHD').val("");
	$('#N_tinNopTien_soDT').val("");
	$('#N_tinNopTien_soHD').prop("readonly",true);
	$('#N_tinNopTien_soDT').prop("readonly",true);
	$("#input_NopTien").fadeIn("fast");			
}
/* function ViewDetailDelivery(idcontractpr){
	
	//$('.modal-body').text('Detail Delivery');
	 $.ajax({
		url		: 'functions/getDeliveryInfo.php',
		async: false,
		type: "POST",
		dataType: "JSON",
		data	: {idcontractpr:idcontractpr },
		success	: function(data){
			var content = '';
			var para = "DelDelivery";
				for(var x in data["data"]) {
					content += '<p>';
					for(var y in data["data"][x]) {
						
						//console.log (data);	
						if(y=="qty" ){ content += 'Delivery Qty.:' + data["data"][x][y] + '&emsp; ';}							
						if(y=="datedelivery"){content+= 'Delivery date: ' + data["data"][x][y] + '&emsp; ';}//console.log (data["data"][x][y]);}
						if(y=="id"){content+= '<input type="hidden" value="' + data["data"][x][y] +'" name="id_Delivery[]" id="id_Delivery[]"/> &emsp; <a href="#" onclick="EditDelivery(' + data["data"][x][y] + ')">Edit</a> &emsp; <a href="#" onclick="DelProcess(' + data["data"][x][y] + ',\'' + para + '\')">Del</a>';}
						
					//<input type = "button" id = "btnEdit_' + data["data"][x][y] + '" value = "Edit" />  &emsp; <button id = "btnDel_' + data["data"][x][y] + '">Del</button>
					}
					
					content += '</p>';
				}
				 
						//alert( "ID of " + x + " is: "+data["data"][x][y] );
				//console.log(content)
	 
			$("#modal-viewdetailDelivery").html(content);
			   } 
		});    
	
	$('#exampleModalCenter').modal('show');
	
}
function ViewDetailPayment(idcontractpr){
	
	//$('.modal-body').text('Detail Delivery');
	 $.ajax({
		url		: 'functions/getPaymentInfo.php',
		async: false,
		type: "POST",
		dataType: "JSON",
		data	: {idcontractpr:idcontractpr},
		success	: function(data){
			var content = '';
			var para = "delpayment";
				for(var x in data["data"]) {
					for(var y in data["data"][x]) {
						//console.log (data);	
						if(y=="PaymentValue" ){ content += 'P. Val.:' + addCommas(data["data"][x][y]) + '&emsp; ';}							
						if(y=="PaymentDate"){content+= 'P. date: ' + data["data"][x][y]+ '&emsp; ';}//console.log (data["data"][x][y]);}
						if(y=="Invoice"){content+= 'Inv. No: ' + data["data"][x][y]+ '&emsp; ';}//console.log (data["data"][x][y]);}
						//if(y=="Invoicedate"){content+= 'Inv. Date: ' + data["data"][x][y]+ '&emsp; ';}//console.log (data["data"][x][y]);}
						if(y=="id"){content+= '<input type="hidden" value="' + data["data"][x][y] +'" name="id_Payment[]" id="id_Payment[]"/> &emsp; <a href="#" onclick="EditPayment(' + data["data"][x][y] + ')">Edit</a> &emsp; <a href="#" onclick="DelProcess(' + data["data"][x][y] + ',\'' + para + '\')">Del</a>';}
					 
					}
					content += '<br/>';
				}
				 
						//alert( "ID of " + x + " is: "+data["data"][x][y] );
				//console.log(content)
	 
			$("#modal-viewdetailPayment").html(content);
			   } 
		});    
	
	$('#ViewPaymentModal').modal('show');
	
} */
	
/* function ViewPaymentDetail(idprdetail,contractno){
	
	//$('.modal-body').text('Detail Delivery');
	 $.ajax({
		url		: 'functions/getPaymentInfo.php',
		async: false,
		type: "POST",
		dataType: "JSON",
		data	: {idprdetail:idprdetail,contractno:contractno },
		success	: function(data){
			var content = '';
				for(var x in data["data"]) {
					for(var y in data["data"][x]) {
						
						if(y=="PaymentValue" ){ content += 'Payment: ' + addCommas(data["data"][x][y]) + '&emsp; ';}							
						if(y=="PaymentDate"){content+= 'P.Date: ' + data["data"][x][y] + '&emsp; ';}
						if(y=="Invoice"){content+= 'Invoice: ' + data["data"][x][y] + '&emsp; ';}
						if(y=="Invoicedate"){content+= 'Date: ' + data["data"][x][y] + '&emsp; ';}
					 
					}
					content += '<br/>';
				}
				 
						//alert( "ID of " + x + " is: "+data["data"][x][y] );
				//console.log(content)
	 
			$("#modal-viewdetailDelivery").html(content);
			   } 
		});    
	$("#exampleModalLongTitle").text('Detail Payment');
	$('#exampleModalCenter').modal('show');
	
} */
	
/* function DelProcess(idfile,typeaccess) {
	
	$('#exampleModalCenter').modal('hide');
	$('#ViewPaymentModal').modal('hide');
	
	$('#modal-DelProcess-header').addClass("alert-danger");
	$('#Del_item').addClass("btn-danger");
	$("#modal-header-text").text('Delete Confirmation');
	$("#iditemToRemove").val('');
	$("#idContractfile").val('');
	$("#id_Payment").val('');
	$("#id_Delivery").val('');
	
	if(typeaccess == 'delfile') {
		$("#modal-DelProcess").text('Are you sure delete the file?');
		$("#idContractfile").val(idfile);
		$('#Del_item').html('Delete file');
	}
	
	if(typeaccess == 'removeitem'){
		$("#modal-DelProcess").text('Are you sure remove the item from this contract?');
		$("#iditemToRemove").val(idfile);
		$('#Del_item').html('Remove item');
	}
	
	if(typeaccess == 'delpayment'){
		$("#modal-DelProcess").text('Are you sure delete this payment?');
		$("#id_Payment").val(idfile);
		$('#Del_item').html('Delete');
		
	}
	if(typeaccess == 'DelDelivery'){
		$("#modal-DelProcess").text('Are you sure delete this delivering ?');
		$("#id_Delivery").val(idfile);
		$('#Del_item').html('Delete');
		
	}
	//$("#cancelbutton").val(Cancel);
	$('#cancelbutton').show();
	$('#Del_item').show();
	$('#modal-confirmDelete').modal('show');
	
	return false;
	
} */
	
	
/* function deleteitem(selectVal,idPRVal,qtyPR,qtyPC) {
		
	$('.modal-header').addClass("alert-danger");
	$('#Del_item').addClass("btn-danger");
	$("#modal-header-text").text('Delete Confirmation');
	$(".modal-body").text('Are you sure remove the item?');
	$("#id_PC").val(selectVal);
	$("#id_PR").val(idPRVal);
	$("#qty_PR").val(qtyPR);
	$("#id_Payment").val("");
	$("#qty_Rec").val(qtyPC);
	$('#cancelbutton').show();
	$('#Del_item').show();
	//$("#cancelbutton").val(Cancel);
	$('#modal-confirmDelete').modal('show');
	
	return false;
} */

/* function deletePayment() {
		
	$('.modal-header').addClass("alert-danger");
	$('#Del_item').addClass("btn-danger");
	$("#modal-header-text").text('Delete Confirmation');
	$(".modal-body").text('Are you sure remove the item?');
	$('#cancelbutton').show();
	$('#Del_item').show();
	//$("#cancelbutton").val(Cancel);
	$('#modal-confirmDelete').modal('show');
	
	return false;
} */
	
/* function EditItem(id){
	$.ajax({
			url		: 'functions/getItemInfoInContract.php',
			async: false,
			type: "POST",
			dataType: "JSON",
			data	: {id:id},  
			success	: function(data){
					//console.log(data.tax);
					$("#edit_itemCode").val(data.Itemname);
					$("#edit_itemDescription").val(data.Description);
					$("#edit_oldQty").val(data.qty);
					$("#edit_itemQtyInContract").val(data.qty);
					$("#edit_itemPriceInContract").val(data.price);
					$("#edit_itemTaxInContract").val(data.tax); 
					var days = 0;
					$("#edit_itemETAInContract").val(data.eta);
					$("#edit_idIteminContract").val(data.id);
					$("#edit_ContractNo").val(data.ContractNo);
					$("#edit_ContractDate").val(data.contractDate);
					$("#edit_idprdetail").val(data.id_prdetail);
			}
	});
	
	$("#edit_itemCode").prop('disabled', true);
	$("#edit_itemDescription").prop('disabled', true);
	$('#editItemInContract_form').fadeIn("fast");
}
   
 function EditPayment(id){
	 $('#ViewPaymentModal').modal('hide');
	$.ajax({
			url		: 'functions/getPaymentInfo_Edit.php',
			async: false,
			type: "POST",
			dataType: "JSON",
			data	: {id:id},
			success	: function(data){
				//	console.log(data.Description);
					$("#edit_Payment_Item").val(data.Itemname);
					$("#edit_Payment_Des").val(data.Description);
					$("#edit_PaymentVal_old").val(data.PaymentValue);
					$("#edit_Payment_Val").val(data.PaymentValue);
					$("#edit_Payment_Date").val(data.PaymentDate);
					$("#edit_Payment_InvoiceNo").val(data.Invoice); 
					$("#edit_Payment_InvoiceDate").val(data.Invoicedate);
					$("#edit_ID_payment").val(data.id);
					$("#edit_ID_prdetail_payment").val(data.prdetail_ID);
					
			}
	});
	
	$("#edit_Payment_Item").prop('disabled', true);
	$("#edit_Payment_Des").prop('disabled', true);
	$('#edit_Payment').fadeIn("fast");
}   
    
function EditDelivery(idDelivery){
	//console.log(idDelivery);
	 $('#exampleModalCenter').modal('hide');
	 $.ajax({
			url		: 'functions/getDeliveryInfo_edit.php',
		async: false,
		type: "POST",
		dataType: "JSON",
		data	: {idDelivery: idDelivery },
		success	: function(data){
			$('#edit_QtyDelivery').val(data.qty);
			$('#edit_DateDelivery').val(data.datedelivery);
			$('#edit_IDDeli').val(data.id);
			}
		});  
		$('#edit_Delivery').fadeIn("fast"); 
	
	
} */
/* function EditPayment(idprdetail,contractno){
	$.ajax({
			url		: 'functions/getPaymentInfo_Edit .php',
		async: false,
		type: "POST",
		dataType: "JSON",
		data	: {idprdetail:idprdetail,contractno:contractno },
		success	: function(data){
			var content = '';var dem = 0;
				for(var x in data["data"]) {
					dem++;
					for(var y in data["data"][x]) {
						
						/* if(y=="qty" ){ content += '<strong>Quant. Delivery ' + dem + ': &emsp; </strong><input type="text" style="width:145px; padding-left: 10px;" name="edit_QtyDelivery[]" id="edit_QtyDelivery[]" value="' + data["data"][x][y] + '"/>';}							
						if(y=="datedelivery"){content+= '<strong>Delivery date ' + dem + ': &emsp;  </strong><input type="text" style="width:160px; padding-left: 10px;" name="edit_DateDelivery[]" id="edit_DateDelivery[]" value="' + data["data"][x][y] + '"/>';}//console.log (data["data"][x][y]);} 
					 
					}
					 
					}
				$("#edit_Delivery_form").html(content);
			}
		}); 
		$('#edit_Delivery').fadeIn("fast");
} */
   
/* function InputDelivery(id){
	 $.ajax({
			url		: 'functions/getItemInfoToDelivery.php',
			async: false,
			type: "POST",
			dataType: "JSON",
			data	: {id:id},
			success	: function(data){
				console.log(data.ContractNo);
					$("#input_ContractNo").val(data.ContractNo);
					$("#input_idprdetail").val(data.id_prdetail);
					$("#input_ID").val(id);
					$("#input_RemainQty").val(data.remainQty);
			}
	}); 

		$('#input_NopTien').fadeIn("fast");
    

}

function Paymentforeachitem(idprdetail,contractno){
	$("#input_PaymentContractNo").val(contractno);
	$("#input_Paymentidprdetail").val(idprdetail);
	  $.ajax({
			url		: 'functions/getItemInfoToPayment.php',
			async: false,
			type: "POST",
			dataType: "JSON",
			data	: {idprdetail:idprdetail,contractno:contractno},
			success	: function(data){
				console.log(data);
					$("#input_TotalVal").val(data);
			}
	}); 
	 
	$('#input_Payment').fadeIn("fast");
    

}*/
function addCommas(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
} 

    </script>