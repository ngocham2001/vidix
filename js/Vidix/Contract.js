$(document).ready(function () {

    // -------------------------------------------------------
    // THÔNG BÁO SAU REDIRECT
    // -------------------------------------------------------
    $.urlParam = function (name) {
        var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results && results.length > 1) { return decodeURIComponent(results[1]) || 0; }
    };

    $(window).load(function () {
        var msg = $.urlParam('fmess');
        if (msg == 1)              { showAlert('success', 'Thêm hợp đồng mới thành công!'); }
        if (msg == 2)              { showAlert('warning', 'Cập nhật hợp đồng thành công!'); }
        if (msg == 'cancelled')    { showAlert('danger',  'Đã hủy hợp đồng.'); }
        if (msg == 'payment_ok')   { showAlert('success', 'Ghi nhận thanh toán thành công! Hoa hồng sẽ được tính sau 22 ngày.'); }
        if (msg == 'payment_del')  { showAlert('warning', 'Đã xóa lần nộp tiền.'); }
        if (msg == 'err_cancelled'){ showAlert('danger',  'Hợp đồng đã bị hủy, không thể ghi nhận thanh toán.'); }
        if (msg == 'err_closed')   { showAlert('danger',  'Hợp đồng đã đóng đủ năm đầu, không tính hoa hồng thêm.'); }
        if (msg == 'err_full')     { showAlert('danger',  'Khách hàng đã nộp đủ trị giá năm đầu.'); }
        if (msg == 'err_processed'){ showAlert('danger',  'Không thể xóa: lần nộp này đã được tính hoa hồng.'); }
    });

    // -------------------------------------------------------
    // DATEPICKER
    // -------------------------------------------------------
    var dpOpts = { format: 'yyyy-mm-dd', todayHighlight: true, autoclose: true };
    $('input[name="start_date"]').datepicker(dpOpts);
    $('input[name="payment_date"]').datepicker(dpOpts);

    // Tự động hiển thị ngày hết hạn hủy khi chọn ngày hiệu lực
    $('input[name="start_date"]').on('changeDate', function () {
        var d = new Date($(this).val());
        d.setDate(d.getDate() + 21);
        var deadline = d.getFullYear() + '-'
            + String(d.getMonth() + 1).padStart(2, '0') + '-'
            + String(d.getDate()).padStart(2, '0');
        $('#deadline_display').html(
            '&nbsp;&nbsp;<i class="text-muted">Hạn hủy HĐ: <strong>' + deadline + '</strong></i>'
        );
    });

    // -------------------------------------------------------
    // VALIDATION FORM THÊM MỚI
    // -------------------------------------------------------
    $('#submit_new').click(function () {
        var errors = 0;
        $('#err_new').html('');

        var fields = [
            { id: '#new_customer_name', msg: 'Vui lòng nhập tên khách hàng!' },
            { id: '#new_customer_cccd', msg: 'Vui lòng nhập số CCCD/CMND!' },
            { id: '#new_product_code',  msg: 'Vui lòng nhập mã sản phẩm!' },
            { id: '#new_annual_value',  msg: 'Vui lòng nhập trị giá hợp đồng năm đầu!' },
            { id: '#new_start_date',    msg: 'Vui lòng chọn ngày hiệu lực!' },
        ];

        fields.forEach(function (f) {
            if (!$.trim($(f.id).val()).length) {
                $(f.id).addClass('error_show');
                $('#err_new').append('<small><i>' + f.msg + '</i></small><br/>');
                errors++;
            } else {
                $(f.id).removeClass('error_show');
            }
        });

        var agentId = $('#new_agent_id').val();
        if (!agentId) {
            $('#new_agent_id').addClass('error_show');
            $('#err_new').append('<small><i>Vui lòng chọn nhân viên bán hàng!</i></small><br/>');
            errors++;
        } else {
            $('#new_agent_id').removeClass('error_show');
        }

        var annualVal = parseFloat($('#new_annual_value').val());
        if (!isNaN(annualVal) && annualVal <= 0) {
            $('#new_annual_value').addClass('error_show');
            $('#err_new').append('<small><i>Trị giá hợp đồng phải lớn hơn 0!</i></small><br/>');
            errors++;
        }

        if (errors > 0) { return false; }
    });

    // -------------------------------------------------------
    // NÚT HỦY FORM
    // -------------------------------------------------------
    $('#cancel_new').click(function () {
        $('#new-contract-form').fadeOut('fast');
        return false;
    });

    // -------------------------------------------------------
    // VALIDATION FORM NHẬP TIỀN (trang Contract_detail)
    // -------------------------------------------------------
    $('#payment-form').submit(function () {
        var amount = parseFloat($('#amount_paid').val());
        var date   = $.trim($('#payment_date').val());
        if (isNaN(amount) || amount <= 0) {
            alert('Số tiền nộp phải lớn hơn 0!');
            return false;
        }
        if (!date.length) {
            alert('Vui lòng nhập ngày nộp tiền!');
            return false;
        }
    });

}); // end document.ready

// -------------------------------------------------------
// HIỆN FORM THÊM MỚI
// -------------------------------------------------------
function showNewForm() {
    $('#err_new').html('');
    $('#new_customer_name, #new_customer_cccd, #new_customer_phone').val('').removeClass('error_show');
    $('#new_product_code, #new_annual_value, #new_start_date').val('').removeClass('error_show');
    $('#new_agent_id').val('').removeClass('error_show');
    $('#new_payment_type').val('monthly');
    $('#deadline_display').html('');
    $('#new-contract-form').fadeIn('fast');
    return false;
}

// -------------------------------------------------------
// HIỆN MODAL SỬA
// -------------------------------------------------------
function editContract(id, customerName, customerPhone, annualValue, paymentType, status, agentId) {
    $('#edit_contract_id').val(id);
    $('#edit_contract_id_label').text('#' + id);
    $('#edit_customer_name').val(customerName);
    $('#edit_customer_phone').val(customerPhone);
    $('#edit_annual_value').val(annualValue);
    $('#edit_payment_type').val(paymentType);
    $('#edit_status').val(status);
    $('#edit_agent_id').val(agentId);
    $('#modal-edit').modal('show');
    return false;
}

// -------------------------------------------------------
// HIỆN MODAL HỦY HỢP ĐỒNG
// -------------------------------------------------------
function cancelContract(id, customerName) {
    $('#id_cancel').val(id);
    $('#modal-cancel-text').html(
        'Bạn chắc chắn muốn hủy hợp đồng #<strong>' + id + '</strong> của khách hàng <strong>'
        + customerName + '</strong>?<br/>'
        + '<small class="text-danger">Hành động này không thể hoàn tác. Hợp đồng sẽ chuyển sang trạng thái "Đã hủy".</small>'
    );
    $('#modal-cancel').modal('show');
    return false;
}

// -------------------------------------------------------
// HÀM HIỆN THÔNG BÁO
// -------------------------------------------------------
function showAlert(type, message) {
    var alertId = '#' + type + '-alert';
    var textId  = '#text-' + type + '-message';
    $(textId).text(message);
    $(alertId).fadeTo('slow', 1).fadeOut(7000, function () { $(alertId).alert('close'); });
}
