<?php
include_once 'PHP/Agent_PHP.php';
?>
<!DOCTYPE html>
<html>
<head>
    <?php include_once 'html/headertitle.php'; ?>
   <link href="css/agent.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <!-- NAVBAR -->
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <?php include_once 'html/topmenu-left.php'; ?>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <?php include_once 'html/topmenu-right.php'; ?>
            </div>
        </div>
    </nav>
</div>

<!-- CONTENT -->
<div class="container">
    <h2>QUẢN LÝ NHÂN VIÊN BÁN HÀNG</h2>
    <form action="" method="POST" id="search-form">
        <a href="#" class="btn btn-primary" onclick="showNewForm();">
            <span class="glyphicon glyphicon-plus"></span> Thêm nhân viên mới
        </a>
        &nbsp;
        <select name="filter_status" id="filter_status" class="input-sm"
                style="width:170px;" onchange="document.getElementById('search-form').submit();">
            <option value="">-- Tất cả trạng thái --</option>
            <option value="active"    <?php echo (isset($_POST['filter_status']) && $_POST['filter_status']==='active')    ? 'selected' : ''; ?>>Đang hoạt động</option>
            <option value="inactive"  <?php echo (isset($_POST['filter_status']) && $_POST['filter_status']==='inactive')  ? 'selected' : ''; ?>>Ngừng hoạt động</option>
            <option value="suspended" <?php echo (isset($_POST['filter_status']) && $_POST['filter_status']==='suspended') ? 'selected' : ''; ?>>Tạm đình chỉ</option>
        </select>
        <span class="pull-right">
            <input type="text" name="textcond"
                   value="<?php echo isset($_POST['textcond']) ? htmlspecialchars($_POST['textcond']) : ''; ?>"
                   placeholder="Tìm tên, CCCD, SĐT, mã cấp..."
                   style="width:230px;" class="input-sm"/>
            <button class="btn btn-default btn-sm" type="submit" name="search">
                <span class="glyphicon glyphicon-search"></span>
            </button>
        </span>
    </form>
</div>

<!-- MESSAGE BOX -->
<div class="container" style="padding-top:10px;">
    <div class="alert alert-success alert-dismissable alert-nonedisplay" id="success-alert">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <b><span id="text-success-message"></span></b>
    </div>
    <div class="alert alert-warning alert-dismissable alert-nonedisplay" id="warning-alert">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <b><span id="text-warning-message"></span></b>
    </div>
    <div class="alert alert-danger alert-dismissable alert-nonedisplay" id="danger-alert">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <b><span id="text-danger-message"></span></b>
    </div>
</div>

<!-- BẢNG DỮ LIỆU -->
<div class="container">
    <?php echo $xhtmlItem; ?>
</div>

<!-- ===================================================== -->
<!-- MODAL CÂY ĐA CẤP                                      -->
<!-- ===================================================== -->
<div class="modal fade modal-tree" id="modal-agent-tree" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#1a3c5e; color:#fff; border-radius:5px 5px 0 0;">
                <button type="button" class="close" data-dismiss="modal"
                        style="color:#fff; opacity:0.8;">&times;</button>
                <h4 class="modal-title">
                    <span class="glyphicon glyphicon-tree-conifer"></span>
                    &nbsp; Cây mạng lưới: <span id="tree-modal-name"></span>
                </h4>
            </div>
            <div class="modal-body" id="tree-modal-body">
                <!-- Nội dung được render bởi JS -->
                <div class="tree-loading">
                    <div class="spinner"></div><br/>
                    Đang tải dữ liệu mạng lưới...
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted pull-left">
                    <span class="glyphicon glyphicon-info-sign"></span>
                    Hoa hồng override: chỉ nhận từ nhân viên có cấp thấp hơn (ô xanh)
                </small>
                <button type="button" class="btn btn-default btn-sm"
                        data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL XÁC NHẬN XÓA -->
<div class="modal fade" id="modal-confirmDelete" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header alert-danger">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">
                    <span class="glyphicon glyphicon-exclamation-sign"></span> Xác nhận xóa
                </h4>
            </div>
            <div class="modal-body">
                <span id="modal-delete-text"></span>
            </div>
            <div class="modal-footer">
                <form action="" method="POST">
                    <input type="hidden" name="id_delete" id="id_delete" value=""/>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger" name="delete-submit">
                        <span class="glyphicon glyphicon-trash"></span> Xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ===================================================== -->
<!-- FORM THÊM MỚI                                         -->
<!-- ===================================================== -->
<div class="entry-form-medium" id="new-agent-form" style="display:none;">
    <form action="" method="POST" id="new_agent_form">
        <div class="error" id="err_new"></div>
        <strong>Thêm nhân viên mới</strong><br/>
        <span class="strong-text"><small>Thông tin cá nhân</small></span><br/>
        <input type="text" id="new_full_name" name="full_name"
               placeholder="Họ và tên *" style="width:150px;" class="input-sm"/>
        <input type="text" id="new_id_number" name="id_number"
               placeholder="Số CCCD/CMND *" style="width:120px;" class="input-sm"/>
        <input type="text" id="new_phone"     name="phone"
               placeholder="Điện thoại *" style="width:100px;" class="input-sm"/>
        <input type="text" id="new_email"     name="email"
               placeholder="Email" style="width:100px;" class="input-sm"/>
        <br/>
        <span class="strong-text"><small>Tài khoản ngân hàng</small></span><br/>
        <input type="text" id="new_bank_account" name="bank_account"
               placeholder="Số tài khoản" style="width:160px;" class="input-sm"/>
        <input type="text" id="new_bank_name"    name="bank_name"
               placeholder="Tên ngân hàng" style="width:320px;" class="input-sm"/>
        <br/>
        <span class="strong-text"><small>Thông tin hệ thống</small></span><br/>
        <?php echo $xhtmlSelectRank; ?>
        &nbsp;
        <?php echo $xhtmlSelectSponsor; ?>
        &nbsp;
        <input type="text" id="new_join_date" name="join_date"
               placeholder="Ngày tham gia" style="width:110px;" class="input-sm"/>
        &nbsp;&nbsp;&nbsp;
        <span class="strong-text"><small>Trạng thái: </small></span>
        <select name="status" id="new_status" class="input-sm" style="width:160px;">
            <option value="active">Đang hoạt động</option>
            <option value="inactive">Ngừng hoạt động</option>
            <option value="suspended">Tạm đình chỉ</option>
        </select>
        <br/>
        <input type="submit" value="Lưu" name="submit_new" id="submit_new"
               class="btn btn-primary btn-sm"/>
        <input type="button" value="Hủy" id="cancel_new" class="btn btn-default btn-sm"/>
    </form>
</div>

<!-- ===================================================== -->
<!-- FORM SỬA                                              -->
<!-- ===================================================== -->
<div class="entry-form-medium" id="edit-agent-form" style="display:none;">
    <form action="" method="POST" id="edit_agent_form">
        <div class="error" id="err_edit"></div>
        <strong>Cập nhật nhân viên:
            <span id="edit_agent_name_label" class="text-primary"></span>
        </strong>
        <input type="hidden" name="edit_agent_id" id="edit_agent_id"/>
        <br/>
        <small class="strong-text">Thông tin cá nhân</small>
        <small class="text-muted">(CCCD không thể thay đổi)</small><br/>
        <input type="text" id="edit_id_number_display" class="input-sm"
               style="width:120px; background:#eee;" readonly placeholder="Số CCCD/CMND"/>
        <input type="text" id="edit_full_name" name="edit_full_name"
               placeholder="Họ và tên *" style="width:150px;" class="input-sm"/>
        <input type="text" id="edit_phone"     name="edit_phone"
               placeholder="Điện thoại *" style="width:100px;" class="input-sm"/>
        <input type="text" id="edit_email"     name="edit_email"
               placeholder="Email" style="width:100px;" class="input-sm"/>
        <br/>
        <span class="strong-text"><small>Tài khoản ngân hàng</small></span><br/>
        <input type="text" id="edit_bank_account" name="edit_bank_account"
               placeholder="Số tài khoản" style="width:160px;" class="input-sm"/>
        <input type="text" id="edit_bank_name"    name="edit_bank_name"
               placeholder="Tên ngân hàng" style="width:320px;" class="input-sm"/>
        <br/>
        <span class="strong-text"><small>Thông tin trong hệ thống</small></span>
        <small class="text-muted">(Người tuyển dụng không thể thay đổi)</small><br/>
        <span class="strong-text"><small>Cấp bậc *</small></span>
        <?php echo $xhtmlSelectRankEdit; ?>
        &nbsp;&nbsp;
        <span class="strong-text"><small>Trạng thái</small></span>
        <select name="edit_status" id="edit_status" class="input-sm" style="width:130px;">
            <option value="active">Đang hoạt động</option>
            <option value="inactive">Ngừng hoạt động</option>
            <option value="suspended">Tạm đình chỉ</option>
        </select>
        <br/>
        <input type="submit" value="Cập nhật" name="submit_edit" id="submit_edit"
               class="btn btn-warning btn-sm"/>
        <input type="button" value="Hủy" id="cancel_edit" class="btn btn-default btn-sm"/>
    </form>
</div>

<?php include_once 'html/emb_js.php'; ?>
<script src="js/vidix/Agent.js" type="text/javascript"></script>

</body>
</html>
