<script type="text/javascript">

	$(document).ready(function() {
		
		
		//Tach lay gia tri duoc truyen qua bien GET khi su dung ham header
		$.urlParam = function(name){
			var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
			if (results && results.length > 1) {
				return results[1] || 0;
			}
		}
		$(window).load(function(){
			if($.urlParam('fmess')==1)
			{
				$("#text-success-message").text('Thêm hợp đồng thành công!');
				$("#success-alert").fadeTo("slow",1).fadeOut(5000, function(){
					$("#success-alert").alert('close');
				});
			}
			
			if($.urlParam('fmess')==2)
			{
				$("#text-warning-message").text('Cập nhật thông tin công trình thành công!');
				$("#warning-alert").fadeTo("slow",1).fadeOut(10000, function(){
					$("#warning-alert").alert('close');
				});
			}
			
			if($.urlParam('fmess')==4)
			{
				$("#text-danger-message").text('Đã xóa thành công thông tin công trình!');
				$("#danger-alert").fadeTo("slow",1).fadeOut(10000, function(){
					$("#danger-alert").alert('close');
				});
			}
		});
		
		//DATEPICKER for Issue date
		var N_NgayCapNLH=$('input[name="N_NgayCapNLH"]'); //our date input has the name "date"
		var N_NgaySinhNLH=$('input[name="N_NgaySinhNLH"]'); //our date input has the name "date"
		var N_NgayCapNLH_OnlyCon=$('input[name="N_NgayCapNLH_OnlyCon"]'); //our date input has the name "date"
		var N_NgaySinhNLH_OnlyCon=$('input[name="N_NgaySinhNLH_OnlyCon"]'); //our date input has the name "date"
		var date_input=$('input[name="N_NgaySinh"]'); //our date input has the name "date"
		var N_NgayCap=$('input[name="N_NgayCap"]'); //our date input has the name "date"
		//var N_NgayPHHD=$('input[name="N_NgayPHHD"]'); //our date input has the name "date"
		var N_Ngaynop1=$('input[name="N_Ngaynop1"]'); //our date input has the name "date"
		var S_nkc=$('input[name="S_NgayKC"]'); //our date input has the name "date"
		var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
		var options={
			format: 'yyyy-mm-dd',
			container: container,
			todayHighlight: true,
			autoclose: true,
		};
		N_NgayCapNLH.datepicker(options);
		N_NgaySinhNLH.datepicker(options);
		//N_NgayPHHD.datepicker(options);
		N_Ngaynop1.datepicker(options);
		N_NgayCap.datepicker(options);
		date_input.datepicker(options);
		S_nkc.datepicker(options);



		$("#cancel_newCus").click(function(){
			$("#newissue-entryform").fadeOut("fast");
			return false;
		});
		
		$("#cancel_newConOnly").click(function(){
			$("#HopDongNewContract_entryform").fadeOut("fast");
			return false;
		});
		
		$("#cancel_editCT").click(function(){
			$("#editCustomer").fadeOut("fast");
			return false;
		});
		
		//Check errors when submit form
		$("#submit_newCus").click(function(){
			
			var errors=0;
			$("#err_Mss").html('');
			var maKH= $('#N_MaKhach').val();
			var tenKH= $('#N_TenKhach').val();
			var CCCD= $('#N_CCCD').val();
			var SoHD= $('#N_SoHD').val();
			var LoaiHD= $('#select_LoaiHD').val();
			var NgayNop1= $('#N_Ngaynop1').val();
			var SoTC= $('#N_SoTC').val();
			var DVTC= $('#N_DVTC').val();
			var SoTK= $('#N_SoTK').val();
			var NganHang= $('#N_TenNH').val();
			var select_MaNV= $('#select_MaNV').val();
			
			if(!$.trim(maKH).length){
				$('#N_MaKhach').addClass('error_show');
				errors++;
				$("#err_Mss").append('<small><i>Thiếu mã khách hàng</i></small><br/>');
			}
			else{
				$('#N_MaKhach').removeClass('error_show');
			}
			
			if(!$.trim(tenKH).length){
				$('#N_TenKhach').addClass('error_show');
				$("#err_Mss").append('<small><i>Thiếu tên khách hàng!</i></small><br/>');
				errors++;
			}
			else{
				$('#N_TenKhach').removeClass('error_show');
			}
			
			if(!$.trim(CCCD).length){
				$('#N_CCCD').addClass('error_show');
				$("#err_Mss").append('<small><i>Thiếu số CCCD khách hàng!</i></small><br/>');
				errors++;
			}
			else{
				$('#N_CCCD').removeClass('error_show');
			}
			
			 if(!$.trim(SoHD).length){
				$('#N_SoHD').addClass('error_show');
				$("#err_Mss").append('<small><i>Thiếu số hợp đồng!</i></small><br/>');
				errors++;
			}
			else{
				$('#N_SoHD').removeClass('error_show');
			}
			
			$.ajax({
				url		: 'VIDIX_function/getSoHD_fromHD_TT_chung.php',
				async: false,
				type: "POST",
				dataType: "JSON",
				data	: {SoHD:SoHD},
				success	: function(response){
					//$('#OnlyCon_HotenKH').val(response);
					
					if(response == 1){
						$('#N_SoHD').addClass('error_show');
						$("#err_Mss").append('<small><i>Số hợp đồng đã có trong cơ sỏ dữ liệu</i></small><br/>');
						errors++;
					}
					else {
						$('#N_SoHD').removeClass('error_show');
					}
				}
			
						
			});
			if(!$.trim(LoaiHD).length){
				$('#select_LoaiHD').addClass('error_show');
				$("#err_Mss").append('<small><i>Thiếu loại hợp đồng!</i></small><br/>');
				errors++;
			}
			else{
				$('#select_LoaiHD').removeClass('error_show');
			}
			
			if(!$.trim(NgayNop1).length){
				$('#N_Ngaynop1').addClass('error_show');
				$("#err_Mss").append('<small><i>Thiếu ngày nộp tiền lần 1!</i></small><br/>');
				errors++;
			}
			else{
				$('#N_Ngaynop1').removeClass('error_show');
			} 
			if(!$.trim(SoTC).length && !$.trim(DVTC).length){
				$('#N_DVTC').addClass('error_show');
				$('#N_SoTC').addClass('error_show');
				$("#err_Mss").append('<small><i>Phải nhập ít nhất 1 giá trị số tín chỉ hoặc số đơn vị tín chỉ!</i></small><br/>');
				errors++;
			}
			
			else{
				$('#N_DVTC').removeClass('error_show');
				$('#N_SoTC').removeClass('error_show');
			}
 			if($.trim(SoTC).length && $.trim(DVTC).length){
				$('#N_DVTC').addClass('error_show');
				$('#N_SoTC').addClass('error_show');
				$("#err_Mss").append('<small><i>Không nhập đồng thời số tín chỉ và số đơn vị tín chỉ!</i></small><br/>');
				errors++;
			}
			else{
				$('#N_DVTC').removeClass('error_show');
				$('#N_SoTC').removeClass('error_show');
			}
			
			if(!$.trim(SoTK).length){
				$('#N_SoTK').addClass('error_show');
				$("#err_Mss").append('<small><i>Thiếu số tài khoản ngân hàng!</i></small><br/>');
				errors++;
			}
			else{
				$('#N_SoTK').removeClass('error_show');
			}
			/* if(!$.trim(tenKH).length){
				$('#N_TenKhach').addClass('error_show');
				$("#err_Mss").append('<small><i>Thiếu tên khách hàng!</i></small><br/>');
				errors++;
			}
			else{
				$('#N_TenKhach').removeClass('error_show');
			} */
			if(!$.trim(N_TenNH).length){
				$('#N_TenNH').addClass('error_show');
				$("#err_Mss").append('<small><i>Thiếu tên ngân hàng!</i></small><br/>');
				errors++;
			}
			else{
				$('#N_TenNH').removeClass('error_show');
			} 
			if(!$.trim(select_MaNV).length){
				$('#select_MaNV').addClass('error_show');
				$("#err_Mss").append('<small><i>Chọn mã nhân viên bán hàng!</i></small><br/>');
				errors++;
			}
			else{
				$('#select_MaNV').removeClass('error_show');
			} 
			
			
			
			if(errors>0){ return false;}			
			//if(errors>0){ return false;}

		});
		
		$("#HopDongNewContract_entryform").click(function(){			
			var errors=0;
			$("#OnlyCon_err_Mss").html('');
			var SoHD= $('#OnlyCon_N_SoHD').val();
			var LoaiHD= $('#select_LoaiHD_OnlyCon').val();
			var NgayNop1= $('#OnlyCon_N_Ngaynop1').val();
			var SoTC= $('#OnlyCon_N_SoTC').val();
			var DVTC= $('#OnlyCon_N_DVTC').val();
			var SoTK= $('#OnlyCon_N_SoTK').val();
			var NganHang= $('#OnlyCon_N_TenNH').val();
			var select_MaNV= $('#select_MaNV_OnlyCon').val();
			
			if(!$.trim(SoHD).length){
				$('#OnlyCon_N_SoHD').addClass('error_show');
				$("#err_Mss_ContractOnly").append('<small><i>Thiếu số hợp đồng!</i></small><br/>');
				errors++;
			}
			else{
				$('#OnlyCon_N_SoHD').removeClass('error_show');
			}
			
			$.ajax({
				url		: 'VIDIX_function/getSoHD_fromHD_TT_chung.php',
				async: false,
				type: "POST",
				dataType: "JSON",
				data	: {SoHD:SoHD},
				success	: function(response){
					if(response == 1){
						$('#OnlyCon_N_SoHD').addClass('error_show');
						$("#err_Mss_ContractOnly").append('<small><i>Số hợp đồng đã có trong cơ sỏ dữ liệu</i></small><br/>');
						errors++;
					}
					else {
						$('#OnlyCon_N_SoHD').removeClass('error_show');
					}
				}					
			});
			if(!$.trim(LoaiHD).length){
				$('#select_LoaiHD_OnlyCon').addClass('error_show');
				$("#err_Mss_ContractOnly").append('<small><i>Thiếu loại hợp đồng!</i></small><br/>');
				errors++;
			}
			else{
				$('#select_LoaiHD_OnlyCon').removeClass('error_show');
			}
			if(!$.trim(NgayNop1).length){
				$('#OnlyCon_N_Ngaynop1').addClass('error_show');
				$("#err_Mss_ContractOnly").append('<small><i>Thiếu ngày nộp tiền lần 1!</i></small><br/>');
				errors++;
			}
			else{
				$('#OnlyCon_N_Ngaynop1').removeClass('error_show');
			} 
			if(!$.trim(SoTC).length && !$.trim(DVTC).length){
				$('#OnlyCon_N_DVTC').addClass('error_show');
				$('#OnlyCon_N_SoTC').addClass('error_show');
				$("#err_Mss_ContractOnly").append('<small><i>Phải nhập ít nhất 1 giá trị số tín chỉ hoặc số đơn vị tín chỉ!</i></small><br/>');
				errors++;
			}
			else{
				$('#OnlyCon_N_DVTC').removeClass('error_show');
				$('#OnlyCon_N_SoTC').removeClass('error_show');
			}
 			if($.trim(SoTC).length && $.trim(DVTC).length){
				$('#OnlyCon_N_DVTC').addClass('error_show');
				$('#OnlyCon_N_SoTC').addClass('error_show');
				$("#err_Mss_ContractOnly").append('<small><i>Không nhập đồng thời số tín chỉ và số đơn vị tín chỉ!</i></small><br/>');
				errors++;
			}
			else{
				$('#OnlyCon_N_DVTC').removeClass('error_show');
				$('#OnlyCon_N_SoTC').removeClass('error_show');
			}
			if(!$.trim(SoTK).length){
				$('#OnlyCon_N_SoTK').addClass('error_show');
				$("#err_Mss_ContractOnly").append('<small><i>Thiếu số tài khoản ngân hàng!</i></small><br/>');
				errors++;
			}
			else{
				$('#OnlyCon_N_SoTK').removeClass('error_show');
			}
			if(!$.trim(N_TenNH).length){
				$('#OnlyCon_N_TenNH').addClass('error_show');
				$("#err_Mss_ContractOnly").append('<small><i>Thiếu tên ngân hàng!</i></small><br/>');
				errors++;
			}
			else{
				$('#OnlyCon_N_TenNH').removeClass('error_show');
			} 
			if(!$.trim(select_MaNV).length){
				$('#select_MaNV_OnlyCon').addClass('error_show');
				$("#err_Mss_ContractOnly").append('<small><i>Chọn mã nhân viên bán hàng!</i></small><br/>');
				errors++;
			}
			else{
				$('#select_MaNV_OnlyCon').removeClass('error_show');
			} 			
			if(errors>0){ return false;}			
		});
		
	
	}); 
	
	function newConAndCus(){
		//alert("Hello, World!");
		 $('#N_DVTC').prop("readonly",false);
		$('#N_SoTC').prop("readonly",false);
		$('#N_NgayPHHD').prop("readonly",true);
		$("#err_Mss").html("");
		$('#newissue-entryform').fadeIn('fast');	
		return false;	 
	
	}
	
	function newConOnly(){
		$('#OnlyCon_HotenKH').prop("disabled",true);
		$("#err_Mss_ContractOnly").html("");
		$('#HopDongNewContract_entryform').fadeIn('fast');	
		return false;	 
	
	}
	 function editCT(maKH){
		$('#S_MaKhach').prop("readonly",false);
		$('#S_MaKhach').val(maKH);
		$('#S_MaKhach').prop("readonly",true);
		$('#S_MaKhach').prop("disabled",true);
		$.ajax({
				url		: 'thuchiFunction/CT_Info-getCTInfo_toEdit.php',
				async: false,
				type: "POST",
				dataType: "JSON",
				data	: {maKH:maKH},
				success	: function(response){
					
					$('#S_maCT').val(maKH);
					$('#S_TenCT').val(response.TenCT);
					$('#S_MaNV').val(response.MaNV);
					$('#S_NgayKC').val(response.NgayKC);
					$('#S_Diadiem').val(response.Diadiem);
					$('#S_Trigia').val(response.Trigia);
					$('#S_Ghichu').val(response.GhiChu);

				}
			});
		$('#editCustomer').fadeIn('fast');	
		return false;	
	
	}
		
	// //Delete item
	// function DelCT(maKH){
		
		// $('.modal-header').addClass("alert-danger");
		// $('#modal-submit').addClass("btn-danger");
		// $("#modal-header-text").text('Delete Confirmation');
		// $(".modal-body").text('Bạn chắc chắn muốn xóa thông tin khách hàng: ' + maKH + '?');
		// $("#modal-submit").html('Delete');
		// $("#id_delete").val(maKH);
		// $('#modal-confirmDelete').modal('show');
		// return false;
		
		// }
	function formatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;

    return [year, month, day].join('-');
}
$("#N_DVTC").change(function() {
	var dvtc = $(this).val();
	if($.trim(dvtc).length){
		$('#N_SoTC').val('');
		$('#N_SoTC').prop("readonly",true);
	}	
	else{$('#N_SoTC').prop("readonly",false);}
});

$("#N_SoTC").change(function() {
	var sotc = $(this).val();
	if($.trim(sotc).length){
		$('#N_DVTC').val('');
		$('#N_DVTC').prop("readonly",true);
	}	
	else{$('#N_DVTC').prop("readonly",false);}
});

$("#OnlyCon_SoCCCD_MaKH").change(function() {
	var soCCCD_maKhach = $(this).val();
	$('#OnlyCon_HotenKH').prop("disabled",false);
	//$('#OnlyCon_HotenKH').val(soCCCD_maKhach);
	$.ajax({
			url		: 'VIDIX_function/getHotenKH_fromSoCCCD_Makhach.php',
			async: false,
			type: "POST",
			dataType: "JSON",
			data	: {soCCCD_maKhach:soCCCD_maKhach},
			success	: function(response){
				//$('#OnlyCon_HotenKH').val(response);
				
				$("#HotenMess").html(response + '<br/>');
			}
						
		});
	$('#OnlyCon_HotenKH').prop("disabled",true);
});

$("#OnlyCon_N_SoHD").change(function() {
	var soHD = $(this).val();
	//$('#OnlyCon_HotenKH').prop("disabled",false);
	//$('#OnlyCon_HotenKH').val(soCCCD_maKhach);
	$.ajax({
			url		: 'VIDIX_function/getSoHD_fromHD_TT_chung.php',
			async: false,
			type: "POST",
			dataType: "JSON",
			data	: {soHD:soHD},
			success	: function(response){
				//$('#OnlyCon_HotenKH').val(response);
				if(response == 1)
					$("#err_Mss_ContractOnly").html('<small><i>Số hợp đồng đã tồn tại trong cơ sở dữ liệu!</i></small><br/>');
				else 
					$("#err_Mss_ContractOnly").html('');
			}
			
						
		});
	$('#err_Mss_ContractOnly').prop("disabled",true);
});


$("#N_Ngaynop1").change(function() {
	var ngay1 = $(this).val();
	var newDate = new Date(ngay1); // Create a copy to avoid modifying the original date
	newDate.setDate(newDate.getDate() + 21);
	// Get components
	var year = newDate.getFullYear();
	// Months are 0-indexed, so add 1
	var month = String(newDate.getMonth() + 1).padStart(2, '0');
	var day = String(newDate.getDate()).padStart(2, '0');

	// Combine into YYYY-MM-DD format
	var ngayPH = year + '-' + month + '-' + day;

	if($.trim(ngay1).length){
		$('#N_NgayPHHD').prop("readonly",false);
		$('#N_NgayPHHD').val(ngayPH);
		$('#N_NgayPHHD').prop("readonly",true);
	}	
	else{$('#N_NgayPHHD').prop("readonly",true);}
});

$("#select_LoaiHD").change(function() {
		var loaiHD = $(this).val();
		if(loaiHD == "A"){
			$('#N_DVTC').prop("readonly",true);
			$('#N_SoTC').prop("readonly",true);
			$('#N_TudongTangTC').prop("disabled",true);
		}
		if(loaiHD == "AG"){
			$('#N_DVTC').prop("readonly",false);
			$('#N_SoTC').prop("readonly",false);
			$('#N_TudongTangTC').prop("disabled",false);
		}
		
	});
    </script>