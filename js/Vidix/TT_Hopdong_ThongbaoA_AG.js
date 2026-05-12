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
				$("#text-danger-message").text('Số tiền nộp chưa đủ chuyển giá trị tín chỉ đăng ký AG!');
				$("#danger-alert").fadeTo("slow",1).fadeOut(10000, function(){
					$("#danger-alert").alert('close');
				});
			}
			
			if($.urlParam('fmess')==2)
			{
				$("#text-success-message").text('Giá trị A đã được chuyển thành AG thành công!');
				$("#success-alert").fadeTo("slow",1).fadeOut(10000, function(){
					$("#success-alert").alert('close');
				});
			}
			
			if($.urlParam('fmess')==3)
			{
				$("#text-warning-message").text('Payment Value is greater than Contract Value. Please check again!');
				$("#warning-alert").fadeTo("slow",1).fadeOut(10000, function(){
					$("#warning-alert").alert('close');
				});
			}
			
			if($.urlParam('fmess')==4)
			{
				$("#text-success-message").text('Modify was success!');
				$("#success-alert").fadeTo("slow",1).fadeOut(10000, function(){
					$("#success-alert").alert('close');
				});
			}
		});
});

function XacnhanChuyenAG(soHD){
	$('.modal-header').addClass("alert-danger");
	$('#modal-submit').addClass("btn-danger");
	$(".modal-body").text('Lưu ý đã xác nhận với khách hàng chuyển hợp đồng từ A sang AG?');
	$("#modal-submit").html('Xác nhận chuyển');
	//$('#modal-submit').addClass("btn-danger");
	$('#modal-confirmChange').modal('show');
	return false;
}
    </script>