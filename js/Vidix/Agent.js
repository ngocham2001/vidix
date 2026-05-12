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
        if (msg == 1)              { showAlert('success', 'Thêm nhân viên mới thành công!'); }
        if (msg == 2)              { showAlert('warning', 'Cập nhật thông tin nhân viên thành công!'); }
        if (msg == 4)              { showAlert('danger',  'Đã xóa nhân viên thành công!'); }
        if (msg == 'duplicate')    { showAlert('danger',  'Số CCCD/CMND đã tồn tại trong hệ thống!'); }
        if (msg == 'has_sub')      { showAlert('danger',  'Không thể xóa: nhân viên này đang có người cấp dưới trong mạng lưới!'); }
        if (msg == 'has_contract') { showAlert('danger',  'Không thể xóa: nhân viên này đã có hợp đồng được ghi nhận!'); }
    });

    // -------------------------------------------------------
    // DATEPICKER cho ngày tham gia
    // -------------------------------------------------------
    var dpOptions = {
        format: 'yyyy-mm-dd',
        todayHighlight: true,
        autoclose: true
    };
    $('input[name="join_date"]').datepicker(dpOptions);

    // -------------------------------------------------------
    // NÚT HỦY FORM
    // -------------------------------------------------------
    $('#cancel_new').click(function () {
        $('#new-agent-form').fadeOut('fast');
        return false;
    });

    $('#cancel_edit').click(function () {
        $('#edit-agent-form').fadeOut('fast');
        return false;
    });

    // -------------------------------------------------------
    // VALIDATION FORM THÊM MỚI
    // -------------------------------------------------------
    $('#submit_new').click(function () {
        var errors = 0;
        $('#err_new').html('');

        var fullName = $.trim($('#new_full_name').val());
        var idNumber = $.trim($('#new_id_number').val());
        var phone    = $.trim($('#new_phone').val());
        var rankId   = $('#new_current_rank_id').val();
        var joinDate = $.trim($('#new_join_date').val());

        if (!fullName.length) {
            $('#new_full_name').addClass('error_show');
            $('#err_new').append('<small><i>Vui lòng nhập họ và tên!</i></small><br/>');
            errors++;
        } else { $('#new_full_name').removeClass('error_show'); }

        if (!idNumber.length) {
            $('#new_id_number').addClass('error_show');
            $('#err_new').append('<small><i>Vui lòng nhập số CCCD/CMND!</i></small><br/>');
            errors++;
        } else { $('#new_id_number').removeClass('error_show'); }

        if (!phone.length) {
            $('#new_phone').addClass('error_show');
            $('#err_new').append('<small><i>Vui lòng nhập số điện thoại!</i></small><br/>');
            errors++;
        } else { $('#new_phone').removeClass('error_show'); }

        if (!rankId || rankId === '') {
            $('#new_current_rank_id').addClass('error_show');
            $('#err_new').append('<small><i>Vui lòng chọn cấp bậc!</i></small><br/>');
            errors++;
        } else { $('#new_current_rank_id').removeClass('error_show'); }

        if (!joinDate.length) {
            $('#new_join_date').addClass('error_show');
            $('#err_new').append('<small><i>Vui lòng nhập ngày tham gia!</i></small><br/>');
            errors++;
        } else { $('#new_join_date').removeClass('error_show'); }

        if (errors > 0) { return false; }
    });

    // -------------------------------------------------------
    // VALIDATION FORM SỬA
    // -------------------------------------------------------
    $('#submit_edit').click(function () {
        var errors = 0;
        $('#err_edit').html('');

        var fullName = $.trim($('#edit_full_name').val());
        var phone    = $.trim($('#edit_phone').val());
        var rankId   = $('#edit_current_rank_id').val();

        if (!fullName.length) {
            $('#edit_full_name').addClass('error_show');
            $('#err_edit').append('<small><i>Vui lòng nhập họ và tên!</i></small><br/>');
            errors++;
        } else { $('#edit_full_name').removeClass('error_show'); }

        if (!phone.length) {
            $('#edit_phone').addClass('error_show');
            $('#err_edit').append('<small><i>Vui lòng nhập số điện thoại!</i></small><br/>');
            errors++;
        } else { $('#edit_phone').removeClass('error_show'); }

        if (!rankId || rankId === '') {
            $('#edit_current_rank_id').addClass('error_show');
            $('#err_edit').append('<small><i>Vui lòng chọn cấp bậc!</i></small><br/>');
            errors++;
        } else { $('#edit_current_rank_id').removeClass('error_show'); }

        if (errors > 0) { return false; }
    });

}); // end document.ready


// -------------------------------------------------------
// HÀM HIỆN FORM THÊM MỚI
// -------------------------------------------------------
function showNewForm() {
    $('#edit-agent-form').fadeOut('fast');
    $('#err_new').html('');
    $('#new_full_name, #new_id_number, #new_phone, #new_email').val('').removeClass('error_show');
    $('#new_bank_account, #new_bank_name, #new_join_date').val('').removeClass('error_show');
    $('#new_current_rank_id').val('').removeClass('error_show');
    $('#new_sponsor_agent_id').val('');
    $('#new_status').val('active');
    $('#new-agent-form').fadeIn('fast');
    return false;
}

// -------------------------------------------------------
// HÀM HIỆN FORM SỬA
// -------------------------------------------------------
function editAgent(agentId, fullName, idNumber, phone, email,
                   bankAccount, bankName, rankId, status) {

    $('#new-agent-form').fadeOut('fast');
    $('#err_edit').html('');

    $('#edit_agent_id').val(agentId);
    $('#edit_agent_name_label').text(fullName);
    $('#edit_id_number_display').val(idNumber);
    $('#edit_full_name').val(fullName).removeClass('error_show');
    $('#edit_phone').val(phone).removeClass('error_show');
    $('#edit_email').val(email);
    $('#edit_bank_account').val(bankAccount);
    $('#edit_bank_name').val(bankName);
    $('#edit_current_rank_id').val(rankId).removeClass('error_show');
    $('#edit_status').val(status);

    $('#edit-agent-form').fadeIn('fast');
    $('html, body').animate({ scrollTop: $('#edit-agent-form').offset().top - 80 }, 400);
    return false;
}

// -------------------------------------------------------
// HÀM XÓA — mở modal xác nhận
// -------------------------------------------------------
function delAgent(agentId, fullName) {
    $('#id_delete').val(agentId);
    $('#modal-delete-text').html(
        'Bạn chắc chắn muốn xóa nhân viên <strong>' + fullName + '</strong>?<br/>' +
        '<small class="text-danger">Lưu ý: Không thể xóa nếu nhân viên đang có người cấp dưới hoặc đã có hợp đồng.</small>'
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
    $(alertId).removeClass('alert-nonedisplay').fadeTo('slow', 1).fadeOut(6000, function () {
        $(alertId).alert('close');
    });
}

// -------------------------------------------------------
// CÂY ĐA CẤP — màu theo depth (tối đa 8 cấp)
// -------------------------------------------------------
var DEPTH_COLORS = {
    1: { bg: '#f0f6ff', border: '#2a5f96', text: '#1a3c5e' },
    2: { bg: '#f0fff5', border: '#27ae60', text: '#1e8449' },
    3: { bg: '#fff8f0', border: '#e67e22', text: '#d35400' },
    4: { bg: '#fdf0ff', border: '#8e44ad', text: '#7d3c98' },
    5: { bg: '#fff0f0', border: '#c0392b', text: '#a93226' },
    6: { bg: '#f0fffe', border: '#16a085', text: '#0e8074' },
    7: { bg: '#fffff0', border: '#d4ac0d', text: '#b7950b' },
    8: { bg: '#f5f5f5', border: '#7f8c8d', text: '#626567' },
};

var STATUS_LABEL = {
    'active':    '<span style="background:#d4efdf;color:#1e8449;padding:1px 7px;border-radius:10px;font-size:10px;font-weight:700;">Đang HĐ</span>',
    'inactive':  '<span style="background:#eee;color:#666;padding:1px 7px;border-radius:10px;font-size:10px;font-weight:700;">Ngừng HĐ</span>',
    'suspended': '<span style="background:#fdecea;color:#c0392b;padding:1px 7px;border-radius:10px;font-size:10px;font-weight:700;">Đình chỉ</span>',
};

// -------------------------------------------------------
// HÀM CHÍNH: Gọi AJAX, render modal cây đa cấp
// -------------------------------------------------------
function showAgentTree(agentId, agentName) {

    // Đặt tiêu đề modal và show loading
    $('#tree-modal-name').text(agentName);
    $('#tree-modal-body').html(
        '<div class="tree-loading">' +
        '<div class="spinner"></div><br/>Đang tải dữ liệu mạng lưới...</div>'
    );
    $('#modal-agent-tree').modal('show');

    // Gọi AJAX
    $.ajax({
        url:      'VIDIX_function/getAgentTree.php',
        type:     'POST',
        dataType: 'json',
        data:     { agent_id: agentId },
        success: function (data) {
            if (data.error) {
                $('#tree-modal-body').html(
                    '<div class="text-danger text-center" style="padding:30px;">' +
                    '<span class="glyphicon glyphicon-warning-sign"></span> ' +
                    data.error + '</div>'
                );
                return;
            }
            renderTree(data);
        },
        error: function () {
            $('#tree-modal-body').html(
                '<div class="text-danger text-center" style="padding:30px;">' +
                '<span class="glyphicon glyphicon-warning-sign"></span> ' +
                'Không thể tải dữ liệu. Vui lòng thử lại.</div>'
            );
        }
    });
}

// -------------------------------------------------------
// RENDER TOÀN BỘ NỘI DUNG MODAL
// -------------------------------------------------------
function renderTree(data) {
    var self   = data.self;
    var tree   = data.tree;
    var stats  = data.stats;
    var html   = '';

    // ---- 1. Ô người tuyển dụng (sponsor) ----
    if (self.sponsor_id) {
        html += '<div class="sponsor-card">';
        html += '<span class="sp-icon">👤</span>';
        html += '<div>';
        html += '<div class="sp-label">Người tuyển dụng trực tiếp</div>';
        html += '<div class="sp-name">';
        html += escHtml(self.sponsor_name) + ' &nbsp;';
        html += '<span class="label label-primary">' + escHtml(self.sponsor_rank_code) + '</span>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
    } else {
        html += '<div class="sponsor-card" style="background:#f5f5f5;border-color:#ccc;">';
        html += '<span class="sp-icon">🌱</span>';
        html += '<div>';
        html += '<div class="sp-label">Người tuyển dụng</div>';
        html += '<div class="sp-name text-muted"><i>Nhân viên gốc (không có người tuyển dụng)</i></div>';
        html += '</div>';
        html += '</div>';
    }

    // ---- 2. Thẻ thông tin bản thân ----
    html += '<div class="self-card">';
    html += '<div class="self-avatar">👤</div>';
    html += '<div>';
    html += '<div class="self-name">' + escHtml(self.full_name) + '</div>';
    html += '<div class="self-meta">';
    html += '<span class="label label-warning" style="margin-right:5px;">'
         +  escHtml(self.rank_code) + ' - ' + escHtml(self.rank_name) + '</span>';
    html += escHtml(self.phone);
    if (self.email) html += ' &bull; ' + escHtml(self.email);
    html += '<br/><small>Ngày tham gia: ' + escHtml(self.join_date) + '</small>';
    html += '</div>';
    html += '</div>';
    html += '<div class="self-stats">';
    html += '<div class="self-stat-item">';
    html += '<span class="self-stat-num">' + stats.tong_cap_duoi + '</span>';
    html += '<span class="self-stat-lbl">Cấp dưới</span>';
    html += '</div>';
    html += '<div class="self-stat-item">';
    html += '<span class="self-stat-num">' + self.so_hd + '</span>';
    html += '<span class="self-stat-lbl">Hợp đồng</span>';
    html += '</div>';
    html += '</div>';
    html += '</div>'; // end self-card

    // ---- 3. Thống kê tổng hợp ----
    html += '<div class="tree-stats">';
    html += renderStatBox(stats.tong_cap_duoi, 'Tổng cấp dưới');
    html += renderStatBox(stats.tong_hd,       'Tổng hợp đồng');

    // Phân bổ theo cấp
    var phanBo = stats.phan_bo_cap;
    var phanBoArr = [];
    for (var key in phanBo) { phanBoArr.push(phanBo[key]); }
    // Sắp xếp theo rank_code
    phanBoArr.sort(function(a, b) { return a.rank_code > b.rank_code ? 1 : -1; });
    for (var i = 0; i < phanBoArr.length; i++) {
        html += renderStatBox(phanBoArr[i].count, phanBoArr[i].rank_code);
    }
    html += '</div>';

    // ---- 4. Cây cấp dưới ----
    if (tree.length === 0) {
        html += '<div class="no-data" style="text-align:center;padding:20px;color:#7f8c9a;">';
        html += '<span style="font-size:28px;display:block;margin-bottom:6px;">🌿</span>';
        html += 'Nhân viên này chưa có người cấp dưới trong mạng lưới.';
        html += '</div>';
    } else {
        html += '<div class="tree-section-title">';
        html += '<span class="glyphicon glyphicon-tree-conifer"></span>';
        html += ' Mạng lưới cấp dưới (' + tree.length + ' người)';
        html += '</div>';

        // Chú thích màu
        html += '<div style="font-size:11px;color:#7f8c9a;margin-bottom:8px;">';
        html += '<span style="background:#d4efdf;color:#1e8449;padding:1px 8px;border-radius:10px;font-size:10px;font-weight:700;margin-right:6px;">✓ Đang nhận HH</span>';
        html += '<span style="background:#fdecea;color:#c0392b;padding:1px 8px;border-radius:10px;font-size:10px;font-weight:700;">✗ Không nhận HH</span>';
        html += ' (do ngang hoặc vượt cấp bạn)</div>';

        html += '<div class="tree-scroll">';
        for (var j = 0; j < tree.length; j++) {
            html += renderTreeNode(tree[j]);
        }
        html += '</div>';
    }

    $('#tree-modal-body').html(html);
}

// -------------------------------------------------------
// RENDER 1 NODE TRONG CÂY
// -------------------------------------------------------
function renderTreeNode(node) {
    var depth  = Math.min(parseInt(node.depth) || 1, 8);
    var colors = DEPTH_COLORS[depth] || DEPTH_COLORS[8];
    var indent = (depth - 1) * 20; // px thụt lề

    // Biểu tượng nối cây
    var treeConnector = '';
    for (var i = 1; i < depth; i++) {
        treeConnector += '<span style="display:inline-block;width:20px;text-align:center;color:#ddd;">│</span>';
    }
    treeConnector += '<span style="display:inline-block;width:20px;text-align:center;color:#aaa;">├─</span>';

    // Badge nhận hoa hồng
    var hhBadge = (node.is_active == 1)
        ? '<span style="background:#d4efdf;color:#1e8449;padding:1px 7px;border-radius:10px;font-size:10px;font-weight:700;">✓ Nhận HH</span>'
        : '<span style="background:#fdecea;color:#c0392b;padding:1px 7px;border-radius:10px;font-size:10px;font-weight:700;">✗ HH</span>';

    // Badge trạng thái agent
    var agentStatus = STATUS_LABEL[node.status] || node.status;

    // Badge cấp bậc
    var rankBadge = '<span style="background:' + colors.border + ';color:#fff;'
        + 'padding:1px 7px;border-radius:10px;font-size:10px;font-weight:700;">'
        + escHtml(node.rank_code) + '</span>';

    var html = '';
    html += '<div style="'
        + 'display:flex;align-items:flex-start;'
        + 'padding:6px 8px;border-radius:5px;margin-bottom:3px;'
        + 'border-left:3px solid ' + colors.border + ';'
        + 'background:' + colors.bg + ';'
        + '">';

    // Thụt lề + connector
    html += '<div style="flex-shrink:0;font-size:12px;color:#bbb;white-space:nowrap;">'
         + treeConnector + '</div>';

    // Icon theo depth
    var depthIcon = depth === 1 ? '👤' : (depth === 2 ? '👥' : '•');
    html += '<div style="flex-shrink:0;margin:0 8px;font-size:14px;">' + depthIcon + '</div>';

    // Nội dung chính
    html += '<div style="flex:1;min-width:0;">';
    html += '<div style="font-weight:700;font-size:13px;color:' + colors.text + ';">';
    html += escHtml(node.full_name);
    html += '</div>';
    html += '<div style="font-size:11px;color:#7f8c9a;margin-top:2px;display:flex;flex-wrap:wrap;gap:6px;">';
    html += '<span>' + escHtml(node.agent_code) + '</span>';
    html += '<span>📞 ' + escHtml(node.phone) + '</span>';
    html += '<span>📅 ' + escHtml(node.join_date) + '</span>';
    if (node.sponsor_name) {
        html += '<span>👤 Tuyển bởi: ' + escHtml(node.sponsor_name) + '</span>';
    }
    html += '</div>';
    html += '</div>';

    // Badges bên phải
    html += '<div style="flex-shrink:0;text-align:right;padding-left:8px;">';
    html += rankBadge + ' ';
    html += hhBadge + '<br/>';
    html += '<span style="font-size:11px;color:#7f8c9a;margin-top:3px;display:inline-block;">';

    if (node.so_cap_duoi > 0) {
        html += '<span class="glyphicon glyphicon-user" style="font-size:10px;"></span> '
             +  node.so_cap_duoi + ' cấp dưới &nbsp;';
    }
    if (node.so_hd > 0) {
        html += '<span class="glyphicon glyphicon-file" style="font-size:10px;"></span> '
             +  node.so_hd + ' HĐ';
    }
    html += '</span>';
    html += '<br/>' + agentStatus;
    html += '</div>';

    html += '</div>'; // end node div
    return html;
}

// -------------------------------------------------------
// HELPER: Render ô thống kê nhỏ
// -------------------------------------------------------
function renderStatBox(num, label) {
    return '<div class="tree-stat-box">'
        + '<div class="tsb-num">' + num + '</div>'
        + '<div class="tsb-lbl">' + escHtml(String(label)) + '</div>'
        + '</div>';
}

// -------------------------------------------------------
// HELPER: Escape HTML để tránh XSS
// -------------------------------------------------------
function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g,  '&amp;')
        .replace(/</g,  '&lt;')
        .replace(/>/g,  '&gt;')
        .replace(/"/g,  '&quot;')
        .replace(/'/g,  '&#039;');
}