<?php 
    include_once 'PHP/TT_Hopdong_chung_PHP.php';
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
        </div>
            <!-- CONTENT -->
            <div class="container">
                <h2>DANH SÁCH HỢP ĐỒNG</h2>
				<form action="" method="POST" id="search-form">
                      <!--<a href="#" class="btn btn-primary" onclick="newConAndCus();">Thêm Hợp đồng & Khách hàng mới</a>
                      <a href="#" class="btn btn-primary" onclick="newConOnly();">Thêm Hợp đồng mới</a>-->
                   
					<button type="button" name="excel" id="excel" class="btn btn-primary">  Excel </button>
					<select name="filter_status" id="filter_status" class="input-sm"
							style="width:170px;" onchange="document.getElementById('search-form').submit();">
						<option value=""<?php echo $status_Agent==='' ? ' selected' : ''; ?>>-- Tất cả trạng thái --</option>
						<option value="Dang_hoat_dong"<?php echo $status_Agent==='Dang_hoat_dong' ? ' selected' : ''; ?>>Đang hoạt động</option>
						<option value="Tam_dung"<?php echo $status_Agent==='Tam_dung' ? ' selected' : ''; ?>>Tạm dừng</option>
						<option value="Da_ket_thuc"<?php echo $status_Agent==='Da_ket_thuc' ? ' selected' : ''; ?>>Đã hết hiệu lực</option>
						<option value="Da_huy_trong_21_ngay"<?php echo $status_Agent==='Da_huy_trong_21_ngay' ? ' selected' : ''; ?>>Đã hủy trong 21 ngày</option>
						<option value="Da_huy_sau_21_ngay"<?php echo $status_Agent==='Da_huy_sau_21_ngay' ? ' selected' : ''; ?>>Đã hủy sau 21 ngày</option>
					</select>
                    <span class='pull-right'>
                       <input type="text" name="textcond"
						   value="<?php echo isset($_POST['textcond']) ? htmlspecialchars($_POST['textcond']) : ''; ?>"
						   placeholder="Tìm số HĐ, Mã KH, Loại HĐ, mã cấp..."
						   style="width:230px;" class="input-sm"/>
                        
                        <button class="btn btn-default" type="submit" name="search">
                            <span class="glyphicon glyphicon-search"></span>
                        </button>
                        <!-- <button type="submit" name="search" value="Search"/> -->
                    </span>
                </form>
            </div>
                
                <!-- Message box -->
            <div class="container" style="padding-top:15px;">
                <div class="alert alert-success alert-dismissable alert-nonedisplay" id="success-alert">
                    <i class="fa fa-check"></i>
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <b><span id="text-success-message"></span></b>
                </div>
                
                <div class="alert alert-warning alert-dismissable alert-nonedisplay" id="warning-alert" >
                    <i class="fa fa-check"></i>
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <b><span id="text-warning-message"></span></b>
                </div>
                
                <div class="alert alert-danger alert-dismissable alert-nonedisplay" id="danger-alert" >
                    <i class="fa fa-check"></i>
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <b><span id="text-danger-message"></span></b>
                </div>
			</div>

			<!-- Confirm Delete -->
			
			<div class="modal fade" id="modal-confirmDelete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header ">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h3 class="modal-title" id="myModalLabel">
								<i class="glyphicon glyphicon-exclamation-sign"></i> 
								<span id="modal-header-text"></span>
							</h3>
						</div>
						<div class="modal-body">
							
						</div>
						<div class="modal-footer">
						<form action="" name="delete-item" method="POST">
							<input type="hidden" name="id_delete" id="id_delete" value=""/>
							<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
							<button type="submit" class="btn " name="delete-submit" id="delete-submit">Delete</button>
						</form>
						</div>
					</div>
				</div>
			</div>
			
                <!-- Data table -->
            <div class="container">		
                
                    <?php echo $xhtmlItem; ?>

                    </tbody>
                </table>                
            </div>  

        <!-- *******Entry form**************-->
			<div class="entry-form-large" id="newissue-entryform">
				<form action="VIDIX_function/HopDong_TTChung_newConAndCus.php" method="POST" name="new_item" class="form-inline" id="new_user">
					<div  class = "error " id = "err_Mss"> </div>
					<strong>Nhập thông tin Khách hàng</strong> <hr>				
					<input type="text" id="N_MaKhach" placeholder=	"Mã Khách" name="N_MaKhach" style = "width:85px;"/>	
					<input type="text" id="N_TenKhach" placeholder="Họ và tên" name="N_TenKhach"style = "width:150px;"/>
					<input type="text" id="N_NgaySinh" placeholder="Ngày sinh" name="N_NgaySinh" style = "width:80px;"/>
					<input type="text" id="N_DT" placeholder="Điện thoại" name="N_DT"  style = "width:90px;"/>
					<input type="text" id="N_Email" placeholder="Email" name="N_Email" style = "width:150px;"/>
					<input type="text" id="N_CCCD" placeholder="CCCD/Hộ chiếu" name="N_CCCD" style = "width:120px;"/>
					<input type="text" id="N_NgayCap" placeholder="Ngày cấp" name="N_NgayCap"  style = "width:80px;"/>
					<input type="text" id="N_Diachi" placeholder="Nơi ở hiện tại" name="N_Diachi"/>
					<input type="text" id="N_HoKhau" placeholder="Hộ khẩu thường trú" name="N_HoKhau"/>
					<input type="text" id="N_DanToc" placeholder="Dân tộc" name="N_DanToc" style = "width:90px;"/>
					<input type="text" id="N_QuocTich" placeholder="Quốc tịch" name="N_QuocTich" style = "width:115px;"/>					
					<input type="text" id="N_Suckhoe" placeholder="Tình trạng sức khỏe" name="N_Suckhoe"/><?php echo $xhtmlChonTTSuckhoe; ?>
					<?php echo $xhtmlChonGioiTinh; ?><?php echo $xhtmlChonTTHonNhan; ?> <?php echo $xhtmlChonTrinhDo; ?>
					<textarea rows="2" class="form-control " id="N_Ghichu" placeholder="Ghi chú thông tin khách hàng" name="N_Ghichu"></textarea>
					<br/><strong>Nhập thông tin Hợp đồng</strong><hr>
					<input type="text" id="N_SoHD" placeholder=	"Số HĐ" name="N_SoHD" style = "width:95px;"/>
					<?php echo $xhtmlChonLoaiHD; ?>
					<input type="text" id="N_Ngaynop1" placeholder="Ngày nộp tiền L1" name="N_Ngaynop1" style = "width:130px;"/>
					<input type="text" id="N_NgayPHHD" placeholder="Ngày phát hành HĐ" name="N_NgayPHHD" style = "width:140px;"/>
					<input type="text" id="N_DVTC" placeholder="Số ĐVTC" name="N_DVTC"  style = "width:70px;"/>
					<input type="text" id="N_SoTC" placeholder="Số Tín chỉ" name="N_SoTC"  style = "width:75px;"/>
					<input type="checkbox" id="N_TudongTangTC" name="N_TudongTangTC"  value = "N_TangTC"/>
					<label for="N_TudongTangTC"> Tự động tăng TC </label>
					<?php echo $xhtmlChonsoNam; ?>
					<input type="text" id="N_SoTK" placeholder="Số TK ngân hàng" name="N_SoTK"style = "width:120px;"/>
					<input type="text" id="N_TenNH" placeholder="Tên Ngân hàng" name="N_TenNH"  style = "width:260px;"/>
					<input type="text" id="N_HotenCTK" placeholder="Họ tên chủ TK" name="N_HotenCTK" style = "width:250px;"/>
					<br/><strong>Nhập thông tin Người liên hệ</strong><hr>
					<input type="text" id="N_TenNLH" placeholder="Họ và tên NLH" name="N_TenNLH"style = "width:185px;"/>					
					<input type="text" id="N_NgaySinhNLH" placeholder="Ngày sinh" name="N_NgaySinhNLH" style = "width:95px;"/>
					<input type="text" id="N_DTNLH" placeholder="Điện thoại" name="N_DTNLH"  style = "width:80px;"/>
					<input type="text" id="N_EmailNLH" placeholder="Email" name="N_EmailNLH" style = "width:185px;"/>
					<input type="text" id="N_DanTocNLH" placeholder="Dân tộc" name="N_DanTocNLH" style = "width:90px;"/>
					<input type="text" id="N_QuocTichNLH" placeholder="Quốc tịch" name="N_QuocTichNLH" style = "width:115px;"/>
					<input type="text" id="N_DiachiNLH" placeholder="Nơi ở hiện tại" name="N_DiachiNLH"/>
					<input type="text" id="N_HoKhauNLH" placeholder="Hộ khẩu thường trú" name="N_HoKhauNLH"/>
					<input type="text" id="N_CCCDNLH" placeholder="CCCD/Hộ chiếu" name="N_CCCDNLH" style = "width:120px;"/>
					<input type="text" id="N_NgayCapNLH" placeholder="Ngày cấp" name="N_NgayCapNLH"  style = "width:95px;"/>					
					<?php echo $xhtmlChonMQH; ?><?php echo $xhtmlChonGioiTinhNLH; ?> &nbsp; <span class = "error " span style="margin-right: 110px; "><i>( * 1 tín chỉ = 12 đơn vị tín chỉ)</i></span><?php echo $xhtmlChonNV;?>
					
					<textarea rows="2" class="form-control " id="N_Ghichu_HD" placeholder="Ghi chú thông tin hợp đồng và người liên hệ" name="N_Ghichu_HD"></textarea>
					<input type="submit" value="Save" name="submit_newCus" id="submit_newCus" />
					<input type="button" value="Cancel" name="cancel_newCus" id="cancel_newCus"/> 
				</form>
			</div>      
           
		 <!-- *******Entry form**************<input type="text" id="OnlyCon_HotenKH" name="OnlyCon_HotenKH" style = "width:305px;"/>-->
			<div class="entry-form-large" id="HopDongNewContract_entryform">
				<form action="VIDIX_function/HopDong_TTChung_newConOnly.php" method="POST" name="new_item" class="form-inline" id="new_ContractOnly">
					<div  class = "error " id = "err_Mss_ContractOnly"> </div>
					<strong>Nhập mã Khách hàng hoặc số CCCD: </strong> <input type="text" id="OnlyCon_SoCCCD_MaKH" placeholder="Số CCCD hoăc mã KH" name="OnlyCon_SoCCCD_MaKH" style = "width:175px;"/> &emsp; <span class = "error" id="HotenMess"></span> <hr>	
					<strong>Nhập thông tin Hợp đồng</strong><hr>
					<input type="text" id="OnlyCon_N_SoHD" class= "" placeholder=	"Số HĐ" name="OnlyCon_N_SoHD" style = "width:95px;"/>
					<?php echo $xhtmlChonLoaiHD_OnlyCon; ?>
					<input type="text" id="OnlyCon_N_Ngaynop1" placeholder="Ngày nộp tiền L1" name="OnlyCon_N_Ngaynop1" style = "width:130px;"/>
					<input type="text" id="OnlyCon_N_NgayPHHD" placeholder="Ngày phát hành HĐ" name="OnlyCon_N_NgayPHHD" style = "width:140px;"/>
					<input type="text" class= "" id="OnlyCon_N_DVTC" placeholder="Số ĐVTC" name="OnlyCon_N_DVTC"  style = "width:70px;"/>
					<input type="text" class= "" id="OnlyCon_N_SoTC" placeholder="Số Tín chỉ" name="OnlyCon_N_SoTC"  style = "width:75px;"/>
					<input type="checkbox" id="OnlyCon_N_TudongTangTC" name="OnlyCon_N_TudongTangTC"  value = "N_TangTC"/>
					<label for="OnlyCon_N_TudongTangTC"> Tự động tăng TC </label>
					<?php echo $xhtmlChonsoNam_OnlyCon; ?>
					<input type="text" id="OnlyCon_N_SoTK" class= "" placeholder="Số TK ngân hàng" name="OnlyCon_N_SoTK"style = "width:120px;"/>
					<input type="text" id="OnlyCon_N_TenNH" class= "" placeholder="Tên Ngân hàng" name="OnlyCon_N_TenNH"  style = "width:260px;"/>
					<input type="text" id="OnlyCon_N_HotenCTK" class= "" placeholder="Họ tên chủ TK" name="OnlyCon_N_HotenCTK" style = "width:250px;"/>
					<br/><strong>Nhập thông tin Người liên hệ</strong><hr>
					<input type="text" id="OnlyCon_N_TenNLH" placeholder="Họ và tên NLH" name="OnlyCon_N_TenNLH"style = "width:185px;"/>					
					<input type="text" id="OnlyCon_N_NgaySinhNLH" placeholder="Ngày sinh" name="OnlyCon_N_NgaySinhNLH" style = "width:95px;"/>
					<input type="text" id="OnlyCon_N_DTNLH" placeholder="Điện thoại" name="OnlyCon_N_DTNLH"  style = "width:80px;"/>
					<input type="text" id="OnlyCon_N_EmailNLH" placeholder="Email" name="OnlyCon_N_EmailNLH" style = "width:185px;"/>
					<input type="text" id="OnlyCon_N_DanTocNLH" placeholder="Dân tộc" name="OnlyCon_N_DanTocNLH" style = "width:90px;"/>
					<input type="text" id="OnlyCon_N_QuocTichNLH" placeholder="Quốc tịch" name="OnlyCon_N_QuocTichNLH" style = "width:115px;"/>
					<input type="text" id="OnlyCon_N_DiachiNLH" placeholder="Nơi ở hiện tại" name="OnlyCon_N_DiachiNLH"/>
					<input type="text" id="OnlyCon_N_HoKhauNLH" placeholder="Hộ khẩu thường trú" name="OnlyCon_N_HoKhauNLH"/>
					<input type="text" id="OnlyCon_N_CCCDNLH" placeholder="CCCD/Hộ chiếu" name="OnlyCon_N_CCCDNLH" style = "width:120px;"/>
					<input type="text" id="OnlyCon_N_NgayCapNLH" placeholder="Ngày cấp" name="OnlyCon_N_NgayCapNLH"  style = "width:95px;"/>					
					<?php echo $xhtmlChonMQH_OnlyCon; ?><?php echo $xhtmlChonGioiTinhNLH_OnlyCon; ?> &nbsp; <span class = "error " span style="margin-right: 110px; "><i>( * 1 tín chỉ = 12 đơn vị tín chỉ)</i></span><?php echo $xhtmlChonNV_OnlyCon;?>
					
					<textarea rows="2" class="form-control " id="OnlyCon_N_Ghichu_HD" placeholder="Ghi chú thông tin hợp đồng và người liên hệ" name="OnlyCon_N_Ghichu_HD"></textarea>
					<input type="submit" value="Save" name="submit_newConOnly" id="submit_newConOnly" />
					<input type="button" value="Cancel" name="cancel_newConOnly" id="cancel_newConOnly"/> 
				</form>
			</div>      
        <?php include_once 'html/emb_js.php';?>
		<?php include_once 'js/vidix/TT_Hopdong_chung.js';?>

        
    </body>

</html>