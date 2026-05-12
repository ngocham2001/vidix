<?php
include_once 'PHP/rank_upgrade_condition_PHP.php';
?>
<!DOCTYPE html>
<html>
<head>
    <?php include_once 'html/headertitle.php'; ?>
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
    <h2>CẤU HÌNH THĂNG CẤP NHÂN VIÊN</h2>
    <form action="" method="POST">
        <a href="#" class="btn btn-primary" onclick="showNewForm();">
            <span class="glyphicon glyphicon-plus"></span> Thêm điều kiện thăng cấp mới
        </a>
        <span class="pull-right">
            <input type="text" name="textcond"
                   value="<?php echo isset($_POST['textcond']) ? htmlspecialchars($_POST['textcond']) : ''; ?>"
                   placeholder="Tìm theo mã hoặc tên cấp..."
                   style="width:220px;" class="input-sm"/>
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

<!-- MODAL XÁC NHẬN XÓA -->
<div class="modal fade" id="modal-confirmDelete" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header alert-danger">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">
                    <span class="glyphicon glyphicon-exclamation-sign"></span>
                    Xác nhận xóa
                </h4>
            </div>
            <div class="modal-body">
                <span id="modal-delete-text"></span>
            </div>
            <div class="modal-footer">
                <form action="" method="POST" name="delete-form">
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

<!-- FORM THÊM MỚI -->
<div class="entry-form" id="new-rank-form" style="display:none;">
    <form action="" method="POST" name="new_rank_form" id="new_rank_form">
        <div class="error" id="err_new"></div>
        <strong>Thêm cấp bậc mới</strong>
        <div class="row">
            <div class="col-xs-12">		
				 <input type="text" id="new_rank_code" name="rank_code"
                       placeholder="Mã cấp bậc" style="width:80px;" class="input-sm"/> <span class="text-danger">*</span>&nbsp;
                <input type="text" id="new_rank_name" name="rank_name"
                       placeholder="Tên cấp bậc" style="width:180px;" class="input-sm"/><span class="text-danger">*</span>
            </div>
        </div>
        <div class="row" style="margin-bottom:8px;">
            <div class="col-xs-12">
				<label>Tỷ lệ hoa hồng (%)</label> &nbsp;&nbsp;
                <input type="number" id="new_commission_rate" name="commission_rate"
                       placeholder="VD: 10" min="0" max="100" step="0.01"
                       style="width:135px;" class="input-sm"/>
                &nbsp;&nbsp;&nbsp;
                <label>
                    <input type="checkbox" name="is_specialist" id="new_is_specialist" value="1"/>
                    Cấp chuyên viên (nhận hoa hồng nhánh)
                </label>
                <label>
                    <input type="checkbox" name="monthly_salary_eligible" id="new_monthly_salary_eligible" value="1"/>
                    Hưởng lương hàng tháng
                </label>
            </div>
        </div>
        <div class="row" style="margin-bottom:8px;">
            <div class="col-xs-12">
                <label>Mô tả quyền lợi</label><br/>
                <textarea rows="2" name="description" id="new_description"
                          placeholder="Mô tả chi tiết quyền lợi của cấp bậc này..."
                          class="form-control" style="width:600px; max-width:100%;"></textarea>
            </div>
        </div>
        <input type="submit" value="Lưu" name="submit_new" id="submit_new" class="btn btn-primary btn-sm"/>
        <input type="button" value="Hủy" id="cancel_new" class="btn btn-default btn-sm"/>
    </form>
</div>

<!-- FORM SỬA -->
<div class="entry-form-large" id="edit-rank-form" style="display:none;">
    <form action="" method="POST" name="edit_rank_form" id="edit_rank_form">
        <div class="error" id="err_edit"></div>
        <strong>Cập nhật cấp bậc: <span id="edit_rank_code_label" class="text-primary"></span></strong>
        <hr>
        <input type="hidden" name="edit_rank_id" id="edit_rank_id"/>

        <div class="row" style="margin-bottom:8px;">
            <div class="col-xs-12">
                <label>Mã cấp</label>
                <input type="text" id="edit_rank_code_display" class="input-sm"
                       style="width:90px; background:#eee;" readonly/>
                &nbsp;&nbsp;
                <label>Tên cấp bậc <span class="text-danger">*</span></label>
                <input type="text" id="edit_rank_name" name="edit_rank_name"
                       style="width:220px;" class="input-sm"/>
            </div>
        </div>
        <div class="row" style="margin-bottom:8px;">
            <div class="col-xs-12">
                <label>Tỷ lệ hoa hồng (%) <span class="text-danger">*</span></label>
                <input type="number" id="edit_commission_rate" name="edit_commission_rate"
                       min="0" max="100" step="0.01"
                       style="width:90px;" class="input-sm"/>
                <small class="text-muted">% trị giá HĐ năm đầu</small>
                &nbsp;&nbsp;&nbsp;
                <label>
                    <input type="checkbox" name="edit_is_specialist" id="edit_is_specialist" value="1"/>
                    Cấp chuyên viên (nhận hoa hồng nhánh)
                </label>
                &nbsp;&nbsp;&nbsp;
                <label>
                    <input type="checkbox" name="edit_monthly_salary_eligible" id="edit_monthly_salary_eligible" value="1"/>
                    Hưởng lương hàng tháng
                </label>
            </div>
        </div>
        <div class="row" style="margin-bottom:8px;">
            <div class="col-xs-12">
                <label>Mô tả quyền lợi</label><br/>
                <textarea rows="2" name="edit_description" id="edit_description"
                          class="form-control" style="width:600px; max-width:100%;"></textarea>
            </div>
        </div>
        <input type="submit" value="Cập nhật" name="submit_edit" id="submit_edit" class="btn btn-warning btn-sm"/>
        <input type="button" value="Hủy" id="cancel_edit" class="btn btn-default btn-sm"/>
    </form>
</div>

<?php include_once 'html/emb_js.php'; ?>
<script src="js/vidix/rank_upgrade_condition.js" type="text/javascript"></script>

</body>
</html>
