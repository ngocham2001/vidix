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
				$("#text-success-message").text('Đã sao lưu dữ liệu thành công vào thư mục Thuchi_DB_Backup của ổ D!');
				$("#success-alert").fadeTo("slow",1).fadeOut(5000, function(){
					$("#success-alert").alert('close');
				});
			}
			
			if($.urlParam('fmess')==2)
			{
				$('#changepass').fadeIn('fast');	
			}
			
			if($.urlParam('fmess')==3)
			{
				$("#text-danger-message").text('Mật khẩu đang sử dụng không khớp. Mời nhập lại!');
				$("#danger-alert").fadeTo("slow",1).fadeOut(10000, function(){
					$("#danger-alert").alert('close');
				});
			}
			if($.urlParam('fmess')==4)
			{
				$("#text-success-message").text('Đã đổi mật khẩu thành công!');
				$("#success-alert").fadeTo("slow",1).fadeOut(5000, function(){
					$("#success-alert").alert('close');
				});
			}
		});
		
		$("#cancel").click(function(){
			$('#cPass').val('');
			$('#nPass1').val('');
			$('#nPass2').val('');
			$("#changepass").fadeOut("fast");
			return false;
		});
		
		$("#submit").click(function(){
			
			var errors=0;
			var cPass= $('#cPass').val();
			var nPass1= $('#nPass1').val();
			var nPass2= $('#nPass2').val();
			var oPass;
			$("#err_Mss").html('');

			if(!$.trim(cPass).length){
				$('#cPass').addClass('error_show');
				$("#err_Mss").append('<small><i>Thiếu mật khẩu hiện tại!</i></small><br/>');
				errors++;
			}
			else{
				$('#cPass').removeClass('error_show');
			}
			
			if(!$.trim(nPass1).length){
				$('#nPass1').addClass('error_show');
				$("#err_Mss").append('<small><i>Thiếu dữ liệu!</i></small><br/>');
				errors++;
			}
			else{
				$('#nPass1').removeClass('error_show');
			}
			
			if(!$.trim(nPass2).length){
				$('#nPass2').addClass('error_show');
				$("#err_Mss").append('<small><i>Thiếu dữ liệu!</i></small><br/>');
				errors++;
			}
			else{
				$('#nPass2').removeClass('error_show');
			}
			
			if(nPass1 != nPass2){
				$('#nPass1').addClass('error_show');
				$('#nPass2').addClass('error_show');
				$("#err_Mss").append('<small><i>Hai mật khẩu mới không khớp!</i></small><br/>');
				errors++;
			}
			else{
				$('#nPass1').removeClass('error_show');
				$('#nPass2').removeClass('error_show');
			}
			
			
			
			if(errors>0){ return false;}			
			//if(errors>0){ return false;}

		});
	
	}); 
	
    </script>