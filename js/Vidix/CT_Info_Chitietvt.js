<script type="text/javascript">

	$(document).ready(function() {
		
		
		//Tach lay gia tri duoc truyen qua bien GET khi su dung ham header
		$.urlParam = function(name){
			var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
			return results[1] || 0;
		}
		$(window).load(function(){
			if($.urlParam('fmess')==1)
			{
				$("#text-success-message").text('Thêm thông tin chi tiết thu thành công!');
				$("#success-alert").fadeTo("slow",1).fadeOut(5000, function(){
					$("#success-alert").alert('close');
				});
			}
			
			if($.urlParam('fmess')==2)
			{
				$("#text-warning-message").text('Cập nhật thông tin thành công!');
				$("#warning-alert").fadeTo("slow",1).fadeOut(10000, function(){
					$("#warning-alert").alert('close');
				});
			}
			
			if($.urlParam('fmess')==4)
			{
				$("#text-danger-message").text('Không thể nhập thông tin nộp tiền. Nguyên nhân: Số tiền nộp nhỏ hơn số tín chỉ đăng ký tương ứng. Xin hãy kiểm tra lại!');
				$("#danger-alert").fadeTo("slow",1).fadeOut(10000, function(){
					$("#danger-alert").alert('close');
				});
			}
		});
		
		//DATEPICKER for Issue date
		var date_input=$('input[name="N_Ngayxuat"]'); //our date input has the name "date"
		var S_Ngayxuat=$('input[name="S_Ngayxuat"]'); //our date input has the name "date"
		var fromCond=$('input[name="fromCond"]'); //our date input has the name "date"
		var toCond=$('input[name="toCond"]'); //our date input has the name "date"
		var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
		var options={
			format: 'yyyy-mm-dd',
			container: container,
			todayHighlight: true,
			autoclose: true,
		};
		date_input.datepicker(options);
		S_Ngayxuat.datepicker(options);
		fromCond.datepicker(options);
		toCond.datepicker(options);
		
		
		$('#N_SL').on('change', function () {
			var dongia = $('#N_Dongia').val();
			if($.trim(dongia).length){
			var sl = this.value;
			var thanhtien = dongia*sl;
			$('#N_ThanhTien').val(thanhtien.toLocaleString());
			}
		});
		
		$('#N_Dongia').on('change', function () {
			var sl = $('#N_SL').val(); 
			if($.trim(sl).length){
			var dongia = this.value;
			var thanhtien = dongia*sl;
			$('#N_ThanhTien').val(thanhtien.toLocaleString());
			}
		});		
		
		$('#S_SL').on('change', function () {
			var dongia = $('#S_Dongia').val();
			if($.trim(dongia).length){
			var sl = this.value;
			var thanhtien = dongia*sl;
			$('#S_Thanhtien').val(thanhtien.toLocaleString());
			}
		});
		
		$('#S_Dongia').on('change', function () {
			var sl = $('#S_SL').val(); 
			if($.trim(sl).length){
			var dongia = this.value;
			var thanhtien = dongia*sl;
			$('#S_Thanhtien').val(thanhtien.toLocaleString());
			}
		});		
		
		
		$("#cancel_editCTVT").click(function(){
			$("#edit_err_Mss").html('');
			$('#editCTVT').fadeOut('fast');
			return false;
		});


		$("#cancel_newVTCT").click(function(){
			$('#select_VT').val('');
			$('#N_Ngayxuat').val('');
			$('#select_Makh').val('');
			$('#N_SL').val('');
			$('#N_Dongia').val('');
			$('#N_ThanhTien').val('');
			$('#N_Ghichu').val('');
			$  ("#err_Mss").html('');
			$("#newissue-entryform").fadeOut("fast");
			
			return false;
		});
		
		
		
		//Check errors when submit form
		$("#submit_newVTCT").click(function(){
			
			var errors=0;
			$("#err_Mss").html('');
			var N_Ngayxuat= $('#N_Ngayxuat').val();
			var select_VT= $('#select_VT').val();
			var select_Makh= $('#select_Makh').val();
			var N_SL= $('#N_SL').val();
			var N_Dongia= $('#N_Dongia').val();
			
			if(!$.trim(select_VT).length){
				$('#select_VT').addClass('error_show');
				errors++;
				$("#err_Mss").append('<small><i>Chọn vật tư!</i></small><br/>');
			}
			else{
				$('#select_VT').removeClass('error_show');
			}
			
			if(!$.trim(select_Makh).length){
				$('#select_Makh').addClass('error_show');
				$("#err_Mss").append('<small><i>Chọn nhà cung cấp!</i></small><br/>');
				errors++;
			}
			else{
				$('#select_Makh').removeClass('error_show');
			}
			
			if(!$.trim(N_Ngayxuat).length){
				$('#N_Ngayxuat').addClass('error_show');
				$("#err_Mss").append('<small><i>Nhập ngày xuất!</i></small><br/>');
				errors++;
			}
			else{
				$('#N_Ngayxuat').removeClass('error_show');
			}
			
			if(!$.trim(N_SL).length){
				$('#N_SL').addClass('error_show');
				$("#err_Mss").append('<small><i>Nhập số lượng!</i></small><br/>');
				errors++;
			}
			else{
				$('#N_SL').removeClass('error_show');
			}
			
			if(!$.trim(N_Dongia).length || N_Dongia==0){
				$('#N_Dongia').addClass('error_show');
				$("#err_Mss").append('<small><i>Nhập đơn giá!</i></small><br/>');
				errors++;
			}
			else{
				$('#N_Dongia').removeClass('error_show');
			}
			
			
			if(errors>0){ return false;}			
			//if(errors>0){ return false;}

		});
		
		$("#submit_editCTVT").click(function(){
			var errors=0;
			$("#edit_err_Mss").html('');
			
			var S_select_VT= $('#S_select_VT').val();
			var S_Ngayxuat= $('#S_Ngayxuat').val();
			var S_MaKH= $('#S_MaKH').val();
			var S_SL= $('#S_SL').val();
			var S_Dongia= $('#S_Dongia').val();

			if(!$.trim(S_select_VT).length){
				$('#S_select_VT').addClass('error_show');
				$("#edit_err_Mss").append('<small><i>Chọn loại vật tư!</i></small><br/>');
				errors++;
			}
			else{
				$('#S_select_VT').removeClass('error_show');
			}
			
			if(!$.trim(S_Ngayxuat).length){
				$('#S_Ngayxuat').addClass('error_show');
				$("#edit_err_Mss").append('<small><i>Thiếu ngày xuất vật tư!</i></small><br/>');
				errors++;
			}
			else{
				$('#S_Ngayxuat').removeClass('error_show');
			}
			
			if(!$.trim(S_MaKH).length){
				$('#S_MaKH').addClass('error_show');
				$("#edit_err_Mss").append('<small><i>Chọn khách hàng!</i></small><br/>');
				errors++;
			}
			else{
				$('#S_MaKH').removeClass('error_show');
			}
			
			if(!$.trim(S_SL).length){
				$('#S_SL').addClass('error_show');
				$("#edit_err_Mss").append('<small><i>Nhập số lượng!</i></small><br/>');
				errors++;
			}
			else{
				$('#S_SL').removeClass('error_show');
			}
			
			if(!$.trim(S_Dongia).length){
				$('#S_Dongia').addClass('error_show');
				$("#edit_err_Mss").append('<small><i>Nhập đơn giá!</i></small><br/>');
				errors++;
			}
			else{
				$('#S_Dongia').removeClass('error_show');
			}
			
			if(errors>0){ return false;}			

		});
		
	}); 
	
	function newkhoanChi(){
		$('#select_VT').val('');
		$('#N_Ngayxuat').val('');
		$('#select_Makh').val('');
		$('#N_SL').val('');
		$('#N_Dongia').val('');
		$('#N_ThanhTien').val('');
		$('#N_Ghichu').val('');
		$("#err_Mss").html('');	
		$("#newissue-entryform").fadeIn("fast");
		return false;	
	
	}
	function editCTVT(maxuat){
		$('#S_maxuat').val(maxuat);
		$("#edit_err_Mss").html('');
		$.ajax({
				url		: 'thuchiFunction/CT_Info-getCTVTInfo_toEdit.php',
				async: false,
				type: "POST",
				dataType: "JSON",
				data	: {maxuat:maxuat},
				success	: function(response){
					var thanhTien = response.SL * response.Dongia;
					//$('#S_maxuat').val(response.maxuat);
					$('#S_select_VT').val(response.maVT);
					$('#S_Ngayxuat').val(response.Ngaynhap);
					$('#S_MaKH').val(response.maKH);			
					$('#S_SL').val(response.SL);
					$('#S_Dongia').val(response.Dongia);
					$('#S_Thanhtien').val(thanhTien);
					$('#S_Ghichu').val(response.Ghichu);

				}
			});
		$('#editCTVT').fadeIn('fast');	
	
	}
		
	//Delete item
	function DelCTChi(maKH){
		
		$('.modal-header').addClass("alert-danger");
		$('#modal-submit').addClass("btn-danger");
		$("#modal-header-text").text('Delete Confirmation');
		$(".modal-body").text('Bạn chắc chắn muốn xóa thông tin đã chọn ?');
		$("#modal-submit").html('Delete');
		$("#id_delete").val(maKH);
		$('#modal-confirmDelete').modal('show');
		return false;
		
		}
	function formatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;

    return [year, month, day].join('-');
}
    </script>