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
				$("#text-success-message").text('Add new receiv successfull!');
				$("#success-alert").fadeTo("slow",1).fadeOut(5000, function(){
					$("#success-alert").alert('close');
				});
			}
			
			if($.urlParam('fmess')==2)
			{
				$("#text-warning-message").text('Update record successfull');
				$("#warning-alert").fadeTo("slow",1).fadeOut(10000, function(){
					$("#warning-alert").alert('close');
				});
			}
			
			if($.urlParam('fmess')==3)
			{
				$("#text-success-message").text('Add new sparepart successfull!');
				$("#success-alert").fadeTo("slow",1).fadeOut(5000, function(){
					$("#success-alert").alert('close');
				});
			}
			
			if($.urlParam('fmess')==4)
			{
				$("#text-success-message").text('The record was be deleted!');
				$("#success-alert").fadeTo("slow",1).fadeOut(10000, function(){
					$("#success-alert").alert('close');
				});
			}
		});
		

		//DATEPICKER for Issue date
		var date_input=$('input[name="iss_date"]'); //our date input has the name "date"
		var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
		var options={  
			format: 'yyyy-mm-dd',
			container: container,
			todayHighlight: true,
			autoclose: true,
		};
		date_input.datepicker(options);

		//SHOW - HIDE new receive window
		
		$("#add_new").click(function(){
			$("#newissue-entryform").fadeIn("fast");	
			return false;
		});
		
		
		$("[id^='Btn-ordering_']").click(function(){
			var acc="";
			var acc_id=$(this).attr('id');
			var pos=acc_id.indexOf("_");
			var end=acc_id.length;
			var acc=acc_id.slice(pos+1,end);
			//$("#modal-header-text").text('Delete Confirmation');
			//alert(acc);
			
			$.ajax({
					url		: 'M_Officer_functions/get_Detail_Item_Ordering.php',
					async: false,
					type: "POST",
					dataType: "JSON",
					data	: {acc:acc},
					success	: function(response){
						$(".modal-title").text("Detail Ordering: " + acc);
							var dem=0;
							var content="";
							//var content="There is not any data";
						//if(response.length>0){
							$.each(response, function(key,value) {
								dem++;
								content= content + '<br/>' + dem + ') PR No: ' + value.pr_no + '. Issue date: ' + value.issuedate + '. Qty Order: ' + value.qty ;
								
								if(value.qty_received>0){
									content = content + '. Qty received: ' + value.qty_received
								}
								
								if(value.budget_price){
									content = content + '. Budget: ' + value.budget_price
								}
								
									
							});
							$(".modal-body").html('<strong>' + content + '</strong>') ;
						
					}
			  });
			
			
			$('#myModal').modal('toggle');;
			
		});
		
		$("#cancel").click(function(){
			
			$("#newissue-entryform").fadeOut("fast");
			$("#iss_date").val('');
			$("#acccode").val('');
			$("#quant").val('');
			$("#note").val('');
			$("#id_hidden").val('');
			$("#stock_hidden").val('');
			$('#type')
				.find('option')
				.remove()
				.end()
			;
			$("#position").val('');
			$("#spp_name").val('');
			$("#spp_des").val('');
			$("#min_bal").val('');


			return false;
		});
		
		$("#cancel_uploadform").click(function(){
			$("#upload-file").fadeOut("fast");
		})
		
		$("#cancel_spptype").click(function(){
			$("#prefix_type").val("");
			$("#spptype_name").val("");
			$("#spptype_des").val("");

			$("#new_spptype_form").fadeOut("fast");
		})
		
		$("#submit_entryform").click(function(){
			
			var levelentry=$('#level_hidden').val();
			var errors=0;
			var d = new Date();
			var cdate=formatDate(d);
				
			//IF ISSUE
			if(levelentry==1){
				
				var entryDate= $('#iss_date').val();
				var issuedate= formatDate(entryDate);
				var issuetype= $('#type').val();
				var technician= $('#officer').val();
				var quantity= $('#quant').val();
				var closingdate= $('#closingdate').val();
				var stock= $('#stock_hidden').val();
				var tempval=stock-quantity;
				
				if(!$.trim(issuedate).length){
					$('#iss_date').addClass('error_show');
					errors++;
				}
				else{
					$('#iss_date').removeClass('error_show');
				}
				
				if(issuedate<=closingdate || issuedate>cdate){
					alert("Invalid issue date");
					errors++;
				}

				if(!$.trim(issuetype).length){
					$('#type').addClass('error_show');
					errors++;
				}
				else{
					$('#type').removeClass('error_show');
				}
				
				if(!$.trim(technician).length){
					$('#officer').addClass('error_show');
					errors++;
				}
				else{
					$('#officer').removeClass('error_show');
				}
				
				if(!$.trim(quantity).length || tempval<0 ){
					$('#quant').addClass('error_show');
					errors++;
				} 
				else{
					$('#quant').removeClass('error_show');
				}

			}
			
			
			//IF RECEIVE
			if(levelentry==2){
				
				var entryDate= $('#iss_date').val();
				var issuedate= formatDate(entryDate);
				var issuetype= $('#type').val();
				var quantity= $('#quant').val();
				var closingdate= $('#closingdate').val();
				
				if(!$.trim(issuedate).length){
					$('#iss_date').addClass('error_show');
					errors++;
				}
				else{
					$('#iss_date').removeClass('error_show');
				}
				
				if(issuedate<=closingdate || issuedate>cdate){
					alert("Invalid receiving date");
					errors++;
				}

				
				if(!$.trim(quantity).length){
					$('#quant').addClass('error_show');
					errors++;
				} 
				else{
					$('#quant').removeClass('error_show');
				}

			}
			
			if(levelentry==3){
				
				
				var sname= $('#spp_name').val();
				var sdes= $('#spp_des').val();
								
				
				
				if(!$.trim(sname).length){
					$('#spp_name').addClass('error_show');
					errors++;
				}
				else{
					$('#spp_name').removeClass('error_show');
				}
				
				if(!$.trim(sdes).length){
					$('#spp_des').addClass('error_show');
					errors++;
				} 
				else{
					$('#spp_des').removeClass('error_show');
				}

			}
			
			if(levelentry==4){
				
				var spptype= $('#type').val();
									
				if(!$.trim(spptype).length){
					$('#type').addClass('error_show');
					errors++;
				}
				else{
					$('#type').removeClass('error_show');
				}
				

			}
			
			
			if(errors>0){ return false;}

		});
		
/* 		$(".modal").on("hidden.bs.modal", function(){
			$(".modal-body").html("");
		});	 */
	
	}); 
	
	function entryspptype(level_spptype){
		$('#level_spptype').val(level_spptype);
		$.ajax({
				url		: 'M_Officer_functions/getnewspptype.php',
				async: false,
				type: "POST",
				dataType: "JSON",
				success	: function(response){
					$('#prefix_type').val(response);
					$('#prefix_type').prop("readonly",true);
					/* 
					var opts = [];
					var len=Object.keys(response).length;
					 for (var i = 1; i <= len; i++) {
						opts += "<option value='" + response[i].spptypeid + "'>" +response[i].sppinfo  + "</option>";
					}
					$('#type').append(opts);
					 */
				}
			});
			$('#new_spptype_form').fadeIn('fast');

		return false;	
	}
	
	function Editspptype(id_spptype,level_spptype){
		$('#level_spptype').val(level_spptype);

		$.ajax({
				url		: 'M_Officer_functions/getspptype_toedit.php',
				async: false,
				type: "POST",
				dataType: "JSON",
				data	: {id_spptype:id_spptype},
				success	: function(response){
					$('#prefix_type').prop("readonly",false);
					$('#prefix_type').val(response.spp_prefix);
					$('#spptype_name').val(response.spptypeName);
					$('#spptype_des').val(response.spptypeDes);
					$('#prefix_type').prop("readonly",true);
					/* 
					var opts = [];
					var len=Object.keys(response).length;
					 for (var i = 1; i <= len; i++) {
						opts += "<option value='" + response[i].spptypeid + "'>" +response[i].sppinfo  + "</option>";
					}
					$('#type').append(opts);
					 */
				}
			});
			$('#new_spptype_form').fadeIn('fast');

		return false;	
	}
	
	
	function entrydata(idVal,levelentry,stockVal){
		$('#acc_hidden').val(idVal);
		$('#level_hidden').val(levelentry);
		$('#newissue-entryform').fadeIn('fast');	
		$('#acccode').val(idVal);
		$('#acccode').prop("readonly",true);
		$("#min_bal").hide();
		$("#position").hide();
		$("#spp_name").hide();
		$("#spp_des").hide();
		$("#stock" ).hide();
		$("#stock_label").hide();
		$("#officer").hide();
		$("#quant").hide();
		$("#type").hide();
		$("#m-code").hide();
		
		$('#iss_date').hide();
		$('#note').hide();
		$('#acccode').hide();
		
		if(levelentry==1){
			$("#titleEditReceiv").html("New Issue");
			$('#acccode').show();
			$("#stock" ).show();
			$("#stock_label").show();
			$("#position").show();
			$("#position").attr("placeholder", "Machine Code");
			$("#type").show();
			$("#m-code").show();
			$("#officer").show();
			$('#iss_date').show();
			$('#note').show();
			$("#quant").show();
			$('#stock').val(stockVal);
			$('#stock_hidden').val(stockVal);
			$('#type').append("<option value=''>Issue type</option>");
			$('#type').append("<option value='Delivery'>Delivery</option>");
			$('#type').append("<option value='Changing'>Changing</option>");
			$('#newissue-entryform').fadeIn('fast');
		}
		
		if(levelentry==2){
			
			$("#titleEditReceiv").html("Add Receiv - Only for Free sparepart");
			$('#acccode').show();
			$('#iss_date').show();
			$('#note').show();
			$("#quant").show();
			$("#type").show();
			$('#type').append("<option value='Free'>Free</option>");
			$('#iss_date').attr("placeholder", "Receiving date: YYYY-MM-DD");
			$('#newissue-entryform').fadeIn('fast');
		}
		
		if(levelentry==3){
			$("#titleEditReceiv").html("Edit Info");
			
			$("#position").show();
			$("#spp_name").show();
			$("#spp_des").show();
			$("#min_bal").show();
			
			$.ajax({
				url		: 'M_Officer_functions/getValtoEditspp.php',
				async: false,
				type: "POST",
				dataType: "JSON",
				data	: {idVal:idVal},
				success	: function(response){
					
				//	alert(response.sname);
					$('#position').val(response.pos);
					$('#spp_name').val(response.sname);
					$('#spp_des').val(response.sdes);
					$('#min_bal').val(response.min);

				}
			});
			$('#newissue-entryform').fadeIn('fast');
		}
		
		if(levelentry==4){
			$("#titleEditReceiv").html("New sparepart");
			$('#position').show();
			$('#spp_name').show();
			$('#spp_des').show();
			$('#min_bal').show();
			$('#note').show();
			$("#stock").show();
			$("#stock").attr("placeholder", "ACC Code");
			$("#type").on("change", function(event) { 
				var accVal = this.value;
				$.ajax({
						url		: 'M_Officer_functions/getnewAccCode.php',
						type	: 'POST',
						data	: {accVal:accVal},
						success	: function(result){
							$('#stock').val(result);
							$('#stock_hidden').val(result);
						}
				});
			});
			$("#type").show();
			$('#type').append("<option value=''>Sparepart type</option>");
			
			$.ajax({
				url		: 'M_Officer_functions/getspptype.php',
				async: false,
				type: "POST",
				dataType: "JSON",
				success	: function(response){
					var opts = [];
					var len=Object.keys(response).length;
					 for (var i = 1; i <= len; i++) {
						opts += "<option value='" + response[i].spptypeid + "'>" +response[i].sppinfo  + "</option>";
					}
					$('#type').append(opts);
					
				}
			});
			$('#newissue-entryform').fadeIn('fast');
	
		}

		if(levelentry==5){
			$('#newissue-entryform').fadeOut('fast');
			$('#upload-file').fadeIn('fast');
			$('#acccode_hidden').val(idVal);
	
		}
		
		return false;	
		
		
		
	}
	
	//Delete item
	function delReceiv(idReceiv,acc){
		$('.modal-header').addClass("alert-danger");
		$('#modal-submit').addClass("btn-danger");
		$("#modal-header-text").text('Delete Confirmation');
		$(".modal-body").text('Are you sure delete the item: '+acc);
		$("#modal-submit").html('Delete');
		$("#id_delete").val(idReceiv);
		$('#modal-confirmDelete').modal('show');
		
		return false;
		}
		
/* 	function selectChangeHandler(selectVal) {
		var spptype = selectVal.value;
		$.ajax({
                    url		: 'M_Officer_functions/get_acc.php',
                    type	: 'POST',
                    data	: {spptype:spptype},
                    success	: function(result){
						$('#acccode').val(result);
						$('#acc_hidden').val(result);
					}
              });  
		alert(spptype);
		
        } */


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