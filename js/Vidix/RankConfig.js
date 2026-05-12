
$(document).ready(function () {

    // -------------------------------------------------------
    // THÔNG BÁO SAU KHI REDIRECT (fmess qua GET)
    // -------------------------------------------------------
    $.urlParam = function (name) {
        var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results && results.length > 1) { return results[1] || 0; }
    };

    $(window).load(function () {
        var msg = $.urlParam('fmess');
        if (msg == 1) {
            showAlert('success', 'Thêm cấp bậc mới thành công!');
        }
        if (msg == 2) {
            showAlert('warning', 'Cập nhật thông tin cấp bậc thành công!');
        }
        if (msg == 4) {
            showAlert('danger', 'Đã xóa cấp bậc thành công!');
        }
        if (msg == 'duplicate') {
            showAlert('danger', 'Mã cấp bậc đã tồn tại trong hệ thống!');
        }
        if (msg == 'used') {
            showAlert('danger', 'Không thể xóa: đang có nhân viên sử dụng cấp bậc này!');
        }
    });

    // -------------------------------------------------------
    // NÚT HỦY FORM
    // -------------------------------------------------------
    $('#cancel_new').click(function () {
        $('#new-rank-form').fadeOut('fast');
        return false;
    });

    $('#cancel_edit').click(function () {
        $('#edit-rank-form').fadeOut('fast');
        return false;
    });

    // -------------------------------------------------------
    // VALIDATION FORM THÊM MỚI
    // -------------------------------------------------------
    $('#submit_new').click(function () {
        var errors = 0;
        $('#err_new').html('');

        var rankCode = $('#new_rank_code').val();
        var rankName = $.trim($('#new_rank_name').val());
        var rate     = $.trim($('#new_commission_rate').val());

        if (!rankCode.length) {
            $('#new_rank_code').addClass('error_show');
            $('#err_new').append('<small><i>Vui lòng chọn mã cấp!</i></small><br/>');
            errors++;
        } else {
            $('#new_rank_code').removeClass('error_show');
        }

        if (!rankName.length) {
            $('#new_rank_name').addClass('error_show');
            $('#err_new').append('<small><i>Vui lòng nhập tên cấp bậc!</i></small><br/>');
            errors++;
        } else {
            $('#new_rank_name').removeClass('error_show');
        }

        if (!rate.length || isNaN(rate) || parseFloat(rate) < 0 || parseFloat(rate) > 100) {
            $('#new_commission_rate').addClass('error_show');
            $('#err_new').append('<small><i>Tỷ lệ hoa hồng phải là số từ 0 đến 100!</i></small><br/>');
            errors++;
        } else {
            $('#new_commission_rate').removeClass('error_show');
        }

        if (errors > 0) { return false; }
    });

    // -------------------------------------------------------
    // VALIDATION FORM SỬA
    // -------------------------------------------------------
    $('#submit_edit').click(function () {
        var errors = 0;
        $('#err_edit').html('');

        var rankName = $.trim($('#edit_rank_name').val());
        var rate     = $.trim($('#edit_commission_rate').val());

        if (!rankName.length) {
            $('#edit_rank_name').addClass('error_show');
            $('#err_edit').append('<small><i>Vui lòng nhập tên cấp bậc!</i></small><br/>');
            errors++;
        } else {
            $('#edit_rank_name').removeClass('error_show');
        }

        if (!rate.length || isNaN(rate) || parseFloat(rate) < 0 || parseFloat(rate) > 100) {
            $('#edit_commission_rate').addClass('error_show');
            $('#err_edit').append('<small><i>Tỷ lệ hoa hồng phải là số từ 0 đến 100!</i></small><br/>');
            errors++;
        } else {
            $('#edit_commission_rate').removeClass('error_show');
        }

        if (errors > 0) { return false; }
    });

    // -------------------------------------------------------
    // TỰ ĐỘNG GỢI Ý tên cấp khi chọn mã cấp
    // -------------------------------------------------------
    var defaultNames = {
        'C1': 'Nhân viên bán hàng',
        'C2': 'Nhân viên cấp 2',
        'C3': 'Chuyên viên',
        'C4': 'Chuyên viên cấp 2',
        'C5': 'Trưởng nhóm',
        'C6': 'Trưởng phòng',
        'C7': 'Giám đốc khu vực',
        'C8': 'Giám đốc vùng'
    };
    var defaultRates = {
        'C1': 10, 'C2': 15, 'C3': 20, 'C4': 25,
        'C5': 30, 'C6': 35, 'C7': 40, 'C8': 45
    };

    $('#new_rank_code').change(function () {
        var code = $(this).val();
        if (code && defaultNames[code]) {
            if (!$.trim($('#new_rank_name').val()).length) {
                $('#new_rank_name').val(defaultNames[code]);
            }
            if (!$.trim($('#new_commission_rate').val()).length) {
                $('#new_commission_rate').val(defaultRates[code]);
            }
            // Tự động tick checkbox từ C3 trở lên
            $('#new_is_specialist').prop('checked', parseInt(code.replace('C','')) >= 3);
            $('#new_monthly_salary_eligible').prop('checked', parseInt(code.replace('C','')) >= 4);
        }
    });

}); // end document.ready

// -------------------------------------------------------
// HÀM HIỆN FORM THÊM MỚI
// -------------------------------------------------------
function showNewForm() {
    $('#edit-rank-form').fadeOut('fast');
    $('#err_new').html('');
    $('#new_rank_code').val('').removeClass('error_show');
    $('#new_rank_name').val('').removeClass('error_show');
    $('#new_commission_rate').val('').removeClass('error_show');
    $('#new_is_specialist').prop('checked', false);
    $('#new_monthly_salary_eligible').prop('checked', false);
    $('#new_description').val('');
    $('#new-rank-form').fadeIn('fast');
    return false;
}

// -------------------------------------------------------
// HÀM HIỆN FORM SỬA — nhận dữ liệu từ onclick trong bảng
// -------------------------------------------------------
function editRank(rankId, rankCode, rankName, commissionRate, isSpecialist, monthlySalary, description) {
    $('#new-rank-form').fadeOut('fast');
    $('#err_edit').html('');

    $('#edit_rank_id').val(rankId);
    $('#edit_rank_code_label').text(rankCode);
    $('#edit_rank_code_display').val(rankCode);
    $('#edit_rank_name').val(rankName).removeClass('error_show');
    $('#edit_commission_rate').val(commissionRate).removeClass('error_show');
    $('#edit_is_specialist').prop('checked', isSpecialist == 1);
    $('#edit_monthly_salary_eligible').prop('checked', monthlySalary == 1);
    $('#edit_description').val(description);

    $('#edit-rank-form').fadeIn('fast');
    // Cuộn lên để thấy form
    $('html, body').animate({ scrollTop: $('#edit-rank-form').offset().top - 80 }, 400);
    return false;
}

// -------------------------------------------------------
// HÀM XÓA — mở modal xác nhận
// -------------------------------------------------------
function delRank(rankId, rankCode) {
    $('#id_delete').val(rankId);
    $('#modal-delete-text').html(
        'Bạn chắc chắn muốn xóa cấp bậc <strong>' + rankCode + '</strong>?<br/>' +
        '<small class="text-danger">Lưu ý: Không thể xóa nếu đang có nhân viên thuộc cấp này.</small>'
    );
    $('#modal-confirmDelete').modal('show');
    return false;
}

// -------------------------------------------------------
// HÀM HIỆN THÔNG BÁO (dùng chung)
// -------------------------------------------------------
function showAlert(type, message) {
    var alertId = '#' + type + '-alert';
    var textId  = '#text-' + type + '-message';
    $(textId).text(message);
    $(alertId).fadeTo('slow', 1).fadeOut(5000, function () {
        $(alertId).alert('close');
    });
}

