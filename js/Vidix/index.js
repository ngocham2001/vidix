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
				$("#text-warning-message").text('Nhập tên truy cập!');
				$("#warning-alert").fadeTo("slow",1).fadeOut(5000, function(){
					$("#warning-alert").alert('close');
				});
			}
			
			if($.urlParam('fmess')==2)
			{
				$("#text-warning-message").text('Nhập mật khẩu!');
				$("#warning-alert").fadeTo("slow",1).fadeOut(10000, function(){
					$("#warning-alert").alert('close');
				});
			}
			
			if($.urlParam('fmess')==3)
			{
				$("#text-danger-message").text('Sai tên truy cập hoặc mật khẩu!');
				$("#danger-alert").fadeTo("slow",1).fadeOut(10000, function(){
					$("#danger-alert").alert('close');
				});
			}
			if($.urlParam('fmess')==4)
			{
				$("#text-danger-message").text('Tài khoản đang tạm dừng hoạt động!');
				$("#danger-alert").fadeTo("slow",1).fadeOut(10000, function(){
					$("#danger-alert").alert('close');
				});
			}
		});

	}); 
	
	
	//Delete item
	
	
    </script>