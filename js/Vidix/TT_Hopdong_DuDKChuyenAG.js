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
						
			if($.urlParam('fmess')==2)
			{
				$("#text-success-message").text('Đã tăng giá trị hợp đồng sang  tùy chọn AG thành công!');
				$("#warning-success").fadeTo("slow",1).fadeOut(10000, function(){
					$("#warning-success").alert('close');
				});
			}
			
			
		});
		
		//DATEPICKER for Issue date
		var S_nkc=$('input[name="S_NgayKC"]'); //our date input has the name "date"
		var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
		var options={
			format: 'yyyy-mm-dd',
			container: container,
			todayHighlight: true,
			autoclose: true,
		};
		
		S_nkc.datepicker(options);
		
		//Check errors when submit form
		
		
		
		
	
	}); 
	
	function XacnhanChuyenAG(soHD){
	$('.modal-header').addClass("alert-danger");
	$('#modal-submit').addClass("btn-danger");
	$(".modal-body").text('Lưu ý đã xác nhận với khách hàng chuyển hợp đồng từ A sang AG?');
	$("#modal-submit").html('Xác nhận chuyển');
	$('#modal-submit').addClass("btn-danger");//idsoHD
	$('#idsoHD').val(soHD);
	$('#modal-confirmChange').modal('show');
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