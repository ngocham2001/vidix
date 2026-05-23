<?php
include_once 'PHP/TT_Hopdong_TCB_PHP.php';
?>
<!DOCTYPE html>
<html>
<head>
    <?php include_once 'html/headertitle.php'; ?>
    <link href="css/agent.css" rel="stylesheet">
    <style>
        /* Modal rộng hơn để hiển thị đủ nội dung */
        #modal-agent-tree .modal-dialog {
            width: 680px;
            max-width: 95vw;
        }
        /* Vùng scroll cho nội dung cây */
        #tree-modal-body {
            max-height: 70vh;
            overflow-y: auto;
            padding: 14px 16px;
        }
        /* Spinner loading */
        .tree-loading {
            text-align: center;
            padding: 40px 0;
            color: #7f8c8d;
        }
        .spinner {
            display: inline-block;
            width: 32px;
            height: 32px;
            border: 4px solid #dde3ea;
            border-top-color: #2980b9;
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
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
    <h2>HỆ THỐNG TƯ VẤN BÁN HÀNG</h2>
    <form action="" method="POST" id="search-form">
        <span class="pull-right">
            <input type="text" name="textcond"
                   value="<?php echo isset($_POST['textcond']) ? htmlspecialchars($_POST['textcond']) : ''; ?>"
                   placeholder="Tìm số hợp đồng..."
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
<!-- MODAL CÂY HỢP ĐỒNG — BẮT BUỘC PHẢI CÓ               -->
<!-- JS gọi $('#modal-agent-tree').modal('show')           -->
<!-- Nếu thiếu block này, click link sẽ không có phản hồi -->
<!-- ===================================================== -->
<div class="modal fade" id="modal-agent-tree" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#1a3c5e; color:#fff; border-radius:5px 5px 0 0;">
                <button type="button" class="close" data-dismiss="modal"
                        style="color:#fff; opacity:0.8;">&times;</button>
                <h4 class="modal-title">
                    <span class="glyphicon glyphicon-indent-left"></span>
                    &nbsp; Cây hợp đồng: <span id="tree-modal-name"></span>
                </h4>
            </div>
            <div class="modal-body" id="tree-modal-body">
                <div class="tree-loading">
                    <div class="spinner"></div><br/>
                    Đang tải dữ liệu mạng lưới...
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted pull-left">
                    <span class="glyphicon glyphicon-info-sign"></span>
                    Nhấn vào nhóm cấp dưới để xem chi tiết từng hợp đồng
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
<script src="js/vidix/TT_Hopdong_TCB.js" type="text/javascript"></script>

</body>
</html>
