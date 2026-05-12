<?php include_once 'PHP/Contract_PHP.php'; ?>
<!DOCTYPE html>
<html>
<head><?php include_once 'html/headertitle.php'; ?></head>
<body>

<div class="container">
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header"><?php include_once 'html/topmenu-left.php'; ?></div>
            <div id="navbar" class="navbar-collapse collapse"><?php include_once 'html/topmenu-right.php'; ?></div>
        </div>
    </nav>
</div>

<!-- CONTENT -->
<div class="container">
    <h2>QUẢN LÝ HỢP ĐỒNG</h2>
    <form action="" method="POST" id="search-form">
        <a href="#" class="btn btn-primary" onclick="showNewForm();">
            <span class="glyphicon glyphicon-plus"></span> Thêm hợp đồng mới
        </a>
        &nbsp;
        <select name="filter_status" class="input-sm" style="width:150px;"
                onchange="document.getElementById('search-form').submit();">
            <option value="">-- Tất cả trạng thái --</option>
            <option value="pending"   <?= isset($_POST['filter_status'])&&$_POST['filter_status']==='pending'   ?'selected':'' ?>>Chờ kích hoạt</option>
            <option value="active"    <?= isset($_POST['filter_status'])&&$_POST['filter_status']==='active'    ?'selected':'' ?>>Đang hoạt động</option>
            <option value="cancelled" <?= isset($_POST['filter_status'])&&$_POST['filter_status']==='cancelled' ?'selected':'' ?>>Đã hủy</option>
            <option value="expired"   <?= isset($_POST['filter_status'])&&$_POST['filter_status']==='expired'   ?'selected':'' ?>>Hết hiệu lực</option>
        </select>
        &nbsp;
        <select name="filter_payment" class="input-sm" style="width:130px;"
                onchange="document.getElementById('search-form').submit();">
            <option value="">-- Loại đóng tiền --</option>
            <option value="annual"  <?= isset($_POST['filter_payment'])&&$_POST['filter_payment']==='annual'  ?'selected':'' ?>>Theo năm</option>
            <option value="monthly" <?= isset($_POST['filter_payment'])&&$_POST['filter_payment']==='monthly' ?'selected':'' ?>>Theo tháng</option>
        </select>
        <span class="pull-right">
            <input type="text" name="textcond"
                   value="<?= isset($_POST['textcond']) ? htmlspecialchars($_POST['textcond']) : '' ?>"
                   placeholder="Tìm tên KH, CCCD, mã HĐ, NV..."
                   style="width:230px;" class="input-sm"/>
            <button class="btn btn-default btn-sm" type="submit" name="search">
                <span class="glyphicon glyphicon-search"></span>
            </button>
        </span>
    </form>
</div>

<!-- MESSAGES -->
<div class="container" style="padding-top:10px;">
    <div class="alert alert-success alert-dismissable alert-nonedisplay" id="success-alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <b><span id="text-success-message"></span></b>
    </div>
    <div class="alert alert-warning alert-dismissable alert-nonedisplay" id="warning-alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <b><span id="text-warning-message"></span></b>
    </div>
    <div class="alert alert-danger alert-dismissable alert-nonedisplay" id="danger-alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <b><span id="text-danger-message"></span></b>
    </div>
</div>

<!-- BẢNG DỮ LIỆU -->
<div class="container"><?php echo $xhtmlItem; ?></div>

<!-- MODAL HỦY HỢP ĐỒNG -->
<div class="modal fade" id="modal-cancel" tabindex="-1" role="dialog">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header alert-danger">
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            <h4 class="modal-title"><span class="glyphicon glyphicon-exclamation-sign"></span> Xác nhận hủy hợp đồng</h4>
        </div>
        <div class="modal-body"><span id="modal-cancel-text"></span></div>
        <div class="modal-footer">
            <form action="" method="POST">
                <input type="hidden" name="id_cancel" id="id_cancel" value=""/>
                <button type="button" class="btn btn-default" data-dismiss="modal">Không</button>
                <button type="submit" class="btn btn-danger" name="cancel-submit">
                    <span class="glyphicon glyphicon-remove"></span> Xác nhận hủy
                </button>
            </form>
        </div>
    </div></div>
</div>

<!-- MODAL SỬA HỢP ĐỒNG -->
<div class="modal fade" id="modal-edit" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            <h4 class="modal-title">Cập nhật hợp đồng <span id="edit_contract_id_label" class="text-primary"></span></h4>
        </div>
        <form action="" method="POST">
            <div class="modal-body">
                <div class="error" id="err_edit"></div>
                <input type="hidden" name="edit_contract_id" id="edit_contract_id"/>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Tên khách hàng <span class="text-danger">*</span></label>
                            <input type="text" name="edit_customer_name" id="edit_customer_name"
                                   class="form-control input-sm"/>
                        </div>
                        <div class="form-group">
                            <label>Điện thoại</label>
                            <input type="text" name="edit_customer_phone" id="edit_customer_phone"
                                   class="form-control input-sm"/>
                        </div>
                        <div class="form-group">
                            <label>Trạng thái</label>
                            <select name="edit_status" id="edit_status" class="form-control input-sm">
                                <option value="pending">Chờ kích hoạt</option>
                                <option value="active">Đang hoạt động</option>
                                <option value="expired">Hết hiệu lực</option>
                                <option value="cancelled">Đã hủy</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Trị giá năm đầu (đồng) <span class="text-danger">*</span></label>
                            <input type="number" name="edit_annual_value" id="edit_annual_value"
                                   class="form-control input-sm" min="0"/>
                        </div>
                        <div class="form-group">
                            <label>Hình thức đóng tiền</label>
                            <select name="edit_payment_type" id="edit_payment_type" class="form-control input-sm">
                                <option value="monthly">Theo tháng</option>
                                <option value="annual">Theo năm</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nhân viên bán</label>
                            <?php echo $xhtmlSelectAgentEdit; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-warning" name="submit_edit">
                    <span class="glyphicon glyphicon-floppy-disk"></span> Cập nhật
                </button>
            </div>
        </form>
    </div></div>
</div>

<!-- FORM THÊM MỚI -->
<div class="entry-form-large" id="new-contract-form" style="display:none;">
    <form action="" method="POST" id="new_contract_form">
        <div class="error" id="err_new"></div>
        <strong>Thêm hợp đồng mới</strong><hr>

        <strong><small>Thông tin khách hàng</small></strong><br/><br/>
        <input type="text" id="new_customer_name" name="customer_name"
               placeholder="Họ và tên khách hàng *" style="width:220px;" class="input-sm"/>
        <input type="text" id="new_customer_cccd" name="customer_id_number"
               placeholder="Số CCCD/CMND *" style="width:140px;" class="input-sm"/>
        <input type="text" id="new_customer_phone" name="customer_phone"
               placeholder="Điện thoại" style="width:120px;" class="input-sm"/>
        <br/><br/>

        <strong><small>Thông tin hợp đồng</small></strong><br/><br/>
        <input type="text" id="new_product_code" name="product_code"
               placeholder="Mã sản phẩm *" style="width:130px;" class="input-sm"/>
        <input type="number" id="new_annual_value" name="annual_value"
               placeholder="Trị giá năm đầu (đ) *" style="width:170px;" class="input-sm" min="0"/>
        &nbsp;
        <label>Hình thức đóng:</label>
        <select name="payment_type" id="new_payment_type" class="input-sm" style="width:130px;">
            <option value="monthly">Theo tháng</option>
            <option value="annual">Theo năm</option>
        </select>
        &nbsp;
        <label>Ngày hiệu lực *</label>
        <input type="text" id="new_start_date" name="start_date"
               placeholder="yyyy-mm-dd" style="width:110px;" class="input-sm"/>
        <small class="text-muted" id="deadline_display"></small>
        <br/><br/>

        <label>Nhân viên bán hàng *</label>
        <?php echo $xhtmlSelectAgent; ?>
        <br/><br/>

        <input type="submit" value="Lưu hợp đồng" name="submit_new" id="submit_new"
               class="btn btn-primary btn-sm"/>
        <input type="button" value="Hủy" id="cancel_new" class="btn btn-default btn-sm"/>
    </form>
</div>

<?php include_once 'html/emb_js.php'; ?>
<script src="js/vidix/Contract.js" type="text/javascript"></script>
</body>
</html>
