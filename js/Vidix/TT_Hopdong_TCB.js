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

// ===============================================================
// CÂY HỢP ĐỒNG — Layout dạng dòng (phiên bản mới)
// ===============================================================

// -------------------------------------------------------
// HÀM CHÍNH: Gọi AJAX, render modal cây đa cấp
// -------------------------------------------------------
function showAgentTree(agentId, agentName) {
    $('#tree-modal-name').text(agentName);
    $('#tree-modal-body').html(
        '<div class="tree-loading">' +
        '<div class="spinner"></div><br/>Đang tải dữ liệu mạng lưới...</div>'
    );
    $('#modal-agent-tree').modal('show');

    $.ajax({
        url:      'VIDIX_function/getAgentTreeNew.php',   // ← file API mới
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
            renderContractTree(data);
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
// RENDER TOÀN BỘ NỘI DUNG MODAL (layout dòng mới)
// -------------------------------------------------------
function renderContractTree(data) {
    var self            = data.self;
    var selfHdB         = data.self_hd_b;
    var selfPoints      = data.self_points;
    var selfTotalHd     = data.self_total_hd;
    var selfDirectHd    = data.self_direct_hd;
    var upgrade         = data.upgrade;
    var sponsorHdB      = data.sponsor_hd_b;
    var childrenByRank  = data.children_by_rank;  // mảng nhóm theo rank
    var html            = '';

    // CSS nội tuyến cho wrapper
    html += '<div style="font-family:\'Segoe UI\',Arial,sans-serif;font-size:13px;line-height:1.6;">';

    // ===========================================================
    // DÒNG 1 — HĐ tuyển dụng (Sponsor)
    // ===========================================================
    html += '<div style="padding:8px 12px;background:#f5f5f5;border-left:4px solid #95a5a6;border-radius:4px;margin-bottom:6px;">';
    html += '<span style="color:#7f8c8d;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;">HĐ tuyển dụng (cấp trên trực tiếp)</span><br/>';

    if (self.sponsor_id && sponsorHdB) {
        var spHdLabel = escHtml(sponsorHdB.so_hd_b || '—');
        html += '<strong style="font-size:15px;color:#2c3e50;">' + spHdLabel + '</strong>';
        html += ' &nbsp;<span style="color:#95a5a6;">|</span>&nbsp; ';
        html += '<span style="color:#555;">[' + escHtml(self.sponsor_code) + '] '
             +  escHtml(self.sponsor_name) + '</span>';
        if (self.sponsor_rank_code) {
            html += ' <span style="background:#2980b9;color:#fff;padding:1px 7px;border-radius:10px;font-size:10px;font-weight:700;margin-left:4px;">'
                 +  escHtml(self.sponsor_rank_code) + '</span>';
        }
    } else if (self.sponsor_id) {
        html += '<span style="color:#7f8c8d;">[' + escHtml(self.sponsor_code) + '] '
             +  escHtml(self.sponsor_name) + ' &mdash; <em>Chưa có HĐ tùy chọn B</em></span>';
    } else {
        html += '<span style="color:#27ae60;font-weight:700;">&#9733; Nhân viên gốc (không có người tuyển dụng)</span>';
    }
    html += '</div>';

    // Đường kẻ nối dọc
    html += '<div style="margin-left:16px;border-left:2px dashed #bdc3c7;height:12px;"></div>';

    // ===========================================================
    // DÒNG 2 — Thông tin HĐ đang tra cứu (bản thân)
    // ===========================================================
    var selfHdLabel = selfHdB ? escHtml(selfHdB.so_hd_b || '—') : '— (Chưa có HĐ B)';
    var selfHdStatus = '';
    if (selfHdB) {
        if (selfHdB.hd_cho === '1' && !selfHdB.so_hd_b) {
            selfHdStatus = ' <em style="color:#e74c3c;font-size:11px;">(HĐ đang chờ)</em>';
        } else {
            var soNgay = parseInt(selfHdB.so_ngay) || 0;
            selfHdStatus = ' <span style="color:#7f8c8d;font-size:11px;">'
                + '&bull; Đã kéo dài: <strong>' + soNgay + ' ngày</strong></span>';
        }
    }

    html += '<div style="padding:10px 12px;background:#eaf4fb;border-left:4px solid #2980b9;border-radius:4px;margin-bottom:4px;">';
    html += '<span style="color:#1a5276;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;">'
         +  escHtml(self.rank_code) + ' — HĐ tra cứu</span><br/>';

    html += '<strong style="font-size:16px;color:#154360;">' + selfHdLabel + '</strong>' + selfHdStatus;
    html += '<br/>';

    // Thống kê trong dòng
    html += '<span style="display:inline-block;margin-right:14px;">'
         +  '<span style="color:#7f8c8d;">Tổng HĐ phụ trách:</span> '
         +  '<strong style="color:#2980b9;">' + selfTotalHd + '</strong></span>';
    html += '<span style="display:inline-block;margin-right:14px;">'
         +  '<span style="color:#7f8c8d;">HĐ trực tiếp:</span> '
         +  '<strong style="color:#2980b9;">' + selfDirectHd + '</strong></span>';
    html += '<span style="display:inline-block;margin-right:14px;">'
         +  '<span style="color:#7f8c8d;">Điểm thưởng:</span> '
         +  '<strong style="color:#e67e22;">' + formatNumber(selfPoints) + '</strong></span>';

    // Tiến độ tăng cấp
    if (upgrade) {
        html += '<br/><div style="margin-top:6px;padding:6px 10px;background:#fdfefe;border:1px solid #d5e8f5;border-radius:4px;">';
        html += '<span style="font-size:11px;color:#1a5276;font-weight:700;">&#128200; Điều kiện lên cấp '
             +  escHtml(upgrade.to_rank_code) + ':</span>&nbsp;&nbsp;';

        // HĐ
        var hd_ok = upgrade.lack_hd <= 0;
        html += renderCondTag(
            'HĐ: ' + upgrade.have_hd + '/' + upgrade.need_hd,
            hd_ok ? 0 : upgrade.lack_hd,
            'HĐ', hd_ok
        );

        // Điểm
        var pt_ok = upgrade.lack_points <= 0;
        html += ' &nbsp;';
        html += renderCondTag(
            'Điểm: ' + formatNumber(upgrade.have_points) + '/' + formatNumber(upgrade.need_points),
            pt_ok ? 0 : upgrade.lack_points,
            'điểm', pt_ok
        );

        // Thành viên trực tiếp (nếu có điều kiện)
        if (upgrade.need_direct > 0) {
            var dir_ok = upgrade.lack_direct <= 0;
            html += ' &nbsp;';
            html += renderCondTag(
                'TV trực tiếp: ' + upgrade.have_direct + '/' + upgrade.need_direct,
                dir_ok ? 0 : upgrade.lack_direct,
                'người', dir_ok
            );
        }
        html += '</div>';
    } else {
        html += '<br/><small style="color:#27ae60;">&#10003; Đã đạt cấp cao nhất hoặc chưa có điều kiện tăng cấp.</small>';
    }

    html += '</div>';

    // ===========================================================
    // DÒNG 3 — Cấp dưới trực tiếp (nhóm theo rank)
    // ===========================================================
    if (!childrenByRank || childrenByRank.length === 0) {
        html += '<div style="margin-left:16px;border-left:2px dashed #bdc3c7;height:12px;"></div>';
        html += '<div style="padding:10px 12px;color:#7f8c8d;font-style:italic;">'
             +  '&mdash; Chưa có thành viên cấp dưới trực tiếp.</div>';
    } else {
        // Tổng số cấp dưới
        var totalChildren = 0;
        for (var gi = 0; gi < childrenByRank.length; gi++) {
            totalChildren += childrenByRank[gi].members.length;
        }

        html += '<div style="margin-left:16px;border-left:2px dashed #bdc3c7;height:12px;"></div>';

        // Mỗi nhóm rank
        for (var g = 0; g < childrenByRank.length; g++) {
            var group = childrenByRank[g];
            var gCount = group.members.length;
            var rankColor = getRankColor(group.rank_code);

            html += '<div style="margin-left:16px;border-left:2px dashed #bdc3c7;padding-left:12px;margin-bottom:4px;">';

            // Tiêu đề nhóm — hiển thị dạng link/collapsible
            var groupId = 'grp_' + escHtml(group.rank_code).replace(/[^a-zA-Z0-9]/g, '_');
            html += '<div style="padding:7px 10px;background:' + rankColor.bg + ';border-left:3px solid ' + rankColor.border + ';border-radius:4px;cursor:pointer;" '
                 +  'onclick="toggleGroup(\'' + groupId + '\')">';
            html += '<span style="background:' + rankColor.border + ';color:#fff;padding:1px 8px;border-radius:10px;font-size:11px;font-weight:700;">'
                 +  escHtml(group.rank_code) + '</span>&nbsp;';
            html += '<span style="font-weight:700;color:' + rankColor.text + ';">'
                 +  escHtml(group.rank_name) + '</span>';
            html += ' &mdash; <strong>' + gCount + ' HĐ</strong>';
            html += ' <span style="float:right;color:#aaa;font-size:11px;">▼ nhấn để xem chi tiết</span>';
            html += '</div>'; // end group header

            // Chi tiết thành viên — mặc định ẩn
            html += '<div id="' + groupId + '" style="display:none;margin-top:2px;">';
            for (var m = 0; m < group.members.length; m++) {
                html += renderChildMember(group.members[m], rankColor);
            }
            html += '</div>'; // end group detail

            html += '</div>'; // end group wrapper
        }
    }

    html += '</div>'; // end outer wrapper

    $('#tree-modal-body').html(html);
}

// -------------------------------------------------------
// Toggle hiện/ẩn nhóm
// -------------------------------------------------------
function toggleGroup(groupId) {
    var el = document.getElementById(groupId);
    if (!el) return;
    el.style.display = (el.style.display === 'none') ? 'block' : 'none';
}

// -------------------------------------------------------
// Render 1 thành viên cấp dưới (có tiến độ tăng cấp)
// -------------------------------------------------------
function renderChildMember(m, rankColor) {
    var hdLabel = m.so_hd_b ? escHtml(m.so_hd_b) : '—';
    var hdCho   = (!m.so_hd_b && m.hd_cho === '1')
        ? ' <em style="color:#e74c3c;font-size:11px;">(chờ)</em>'
        : '';

    var html = '';
    html += '<div style="padding:6px 10px 6px 14px;margin:2px 0;background:#fafafa;'
         +  'border-left:2px solid ' + rankColor.border + ';border-radius:3px;">';

    // Số HĐ B + tên
    html += '<strong style="color:#2c3e50;">' + hdLabel + '</strong>' + hdCho;
    html += ' <span style="color:#7f8c8d;font-size:11px;">'
         +  '(' + escHtml(m.full_name) + ' &bull; ' + escHtml(m.agent_code) + ')</span>';

    // Thống kê con
    html += '<br/>';
    html += '<span style="font-size:11px;color:#555;margin-right:10px;">'
         +  'Tổng HĐ phụ trách: <strong>' + m.tong_hd_phu_trach + '</strong></span>';
    html += '<span style="font-size:11px;color:#555;margin-right:10px;">'
         +  'HĐ trực tiếp: <strong>' + m.hd_truc_tiep + '</strong></span>';
    html += '<span style="font-size:11px;color:#e67e22;margin-right:10px;">'
         +  'Điểm: <strong>' + formatNumber(m.tong_diem) + '</strong></span>';

    // Tiến độ tăng cấp
    if (m.upgrade) {
        var u = m.upgrade;
        html += '<br/><span style="font-size:11px;color:#7f8c8d;">Lên '
             +  escHtml(u.to_rank_code) + ': </span>';

        var hd_ok = u.lack_hd <= 0;
        html += renderCondTag(
            u.have_hd + '/' + u.need_hd + ' HĐ',
            hd_ok ? 0 : u.lack_hd,
            'HĐ', hd_ok
        );
        html += ' ';
        var pt_ok = u.lack_points <= 0;
        html += renderCondTag(
            formatNumber(u.have_points) + '/' + formatNumber(u.need_points) + ' đ',
            pt_ok ? 0 : u.lack_points,
            'điểm', pt_ok
        );

        if (!hd_ok || !pt_ok) {
            html += ' <span style="font-size:11px;color:#c0392b;">'
                 +  '(Thiếu: ';
            if (!hd_ok)  html += u.lack_hd + ' HĐ ';
            if (!pt_ok)  html += formatNumber(u.lack_points) + ' điểm';
            html += ')</span>';
        } else {
            html += ' <span style="font-size:11px;color:#27ae60;font-weight:700;">&#10003; Đủ điều kiện lên cấp!</span>';
        }
    }

    html += '</div>';
    return html;
}

// -------------------------------------------------------
// Render tag điều kiện (đạt/chưa đạt)
// -------------------------------------------------------
function renderCondTag(label, lack, unit, ok) {
    if (ok) {
        return '<span style="background:#d5f5e3;color:#1e8449;padding:1px 8px;border-radius:10px;font-size:11px;font-weight:700;">'
             + '&#10003; ' + escHtml(String(label)) + '</span>';
    } else {
        return '<span style="background:#fdecea;color:#c0392b;padding:1px 8px;border-radius:10px;font-size:11px;font-weight:700;">'
             + '&#10005; ' + escHtml(String(label))
             + ' (thiếu ' + formatNumber(lack) + ' ' + unit + ')'
             + '</span>';
    }
}

// -------------------------------------------------------
// Màu sắc theo rank_code (tối đa 8 cấp + fallback)
// -------------------------------------------------------
var RANK_COLORS_MAP = {
    'C1': { bg: '#f0f6ff', border: '#2a5f96', text: '#1a3c5e' },
    'C2': { bg: '#f0fff5', border: '#27ae60', text: '#1e8449' },
    'C3': { bg: '#fff8f0', border: '#e67e22', text: '#d35400' },
    'C4': { bg: '#fdf0ff', border: '#8e44ad', text: '#7d3c98' },
    'C5': { bg: '#fff0f0', border: '#c0392b', text: '#a93226' },
    'C6': { bg: '#f0fffe', border: '#16a085', text: '#0e8074' },
    'C7': { bg: '#fffff0', border: '#d4ac0d', text: '#b7950b' },
    'C8': { bg: '#f5f5f5', border: '#7f8c8d', text: '#626567' },
};

function getRankColor(rankCode) {
    return RANK_COLORS_MAP[rankCode] || { bg: '#f9f9f9', border: '#aaa', text: '#555' };
}

// -------------------------------------------------------
// HELPER: Format số (ngăn cách nghìn)
// -------------------------------------------------------
function formatNumber(n) {
    n = parseFloat(n) || 0;
    return n.toLocaleString('vi-VN');
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
