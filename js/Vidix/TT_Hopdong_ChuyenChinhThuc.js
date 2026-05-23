// ============================================================
// js/Vidix/TT_Hopdong_ChuyenChinhThuc.js
// Xử lý giao diện trang Hợp đồng chờ chuyển chính thức
// ============================================================

$(document).ready(function () {

    // ----------------------------------------------------------
    // THÔNG BÁO SAU REDIRECT (fmess qua GET)
    // ----------------------------------------------------------
    $(window).on('load', function () {
        var msg = getUrlParam('fmess');
        if (msg === 'huy_ok')    { showAlert('success', 'Hủy hợp đồng thành công!'); }
        if (msg === 'huy_err')   { showAlert('danger',  'Có lỗi khi hủy hợp đồng. Vui lòng thử lại!'); }
    });

    // ----------------------------------------------------------
    // CHỌN TẤT CẢ / BỎ CHỌN TẤT CẢ (chỉ HĐ đủ 21 ngày)
    // ----------------------------------------------------------
    $('#chk-all').on('change', function () {
        var checked = $(this).is(':checked');
        $('.chk-hd[data-status="du_dk"]').prop('checked', checked);
        capNhatSoLuongChon();
    });

    $(document).on('change', '.chk-hd', function () {
        var total   = $('.chk-hd[data-status="du_dk"]').length;
        var checked = $('.chk-hd[data-status="du_dk"]:checked').length;
        $('#chk-all').prop('indeterminate', checked > 0 && checked < total);
        $('#chk-all').prop('checked', checked === total && total > 0);
        capNhatSoLuongChon();
    });

    // ----------------------------------------------------------
    // NÚT CHUYỂN CHÍNH THỨC — mở modal xác nhận
    // ----------------------------------------------------------
    $('#btn-chuyen-ct').on('click', function () {
        var ids = layDanhSachIdDaChon();
        if (ids.length === 0) {
            showAlert('warning', 'Vui lòng chọn ít nhất một hợp đồng đủ 21 ngày để chuyển!');
            return false;
        }

        // Hiển thị danh sách HĐ đã chọn trong modal xác nhận
        var danhSach = '';
        ids.forEach(function (id) {
            var row   = $('input.chk-hd[value="' + id + '"]').closest('tr');
            var soHD  = row.find('.col-sohd').text().trim();
            var loai  = row.find('.col-loaihd').text().trim();
            var trigia = row.find('.col-trigia').text().trim();
            danhSach += '<li><strong>' + escHtml(soHD) + '</strong>'
                     +  ' (' + escHtml(loai) + ')'
                     +  ' — Trị giá: ' + escHtml(trigia) + '</li>';
        });

        $('#modal-ct-list').html('<ul style="margin:0;padding-left:20px;">' + danhSach + '</ul>');
        $('#modal-ct-count').text(ids.length);
        $('#modal-confirmCT').modal('show');
        return false;
    });

    // ----------------------------------------------------------
    // XÁC NHẬN CHUYỂN CHÍNH THỨC — gọi AJAX
    // ----------------------------------------------------------
    $('#btn-ct-xacnhan').on('click', function () {
		var hdId = $(this).data('hd-id');
		var soHD = $(this).data('so-hd');

		if (!hdId) return;

		$(this).prop('disabled', true)
			   .html('<span class="glyphicon glyphicon-refresh spinning"></span> Đang xử lý...');
		$('#modal-ct-result').html('').hide();

		$.ajax({
			url:      'VIDIX_function/HopDong_ChuyenChinhThuc_Process.php',
			type:     'POST',
			dataType: 'json',
			data:     { hopdong_ids: hdId },
			success: function (resp) {
				xuLyKetQua(resp, 1);
			},
			error: function (xhr, status, err) {
				hienThiLoiAjax(err || 'Không thể kết nối máy chủ.');
			},
			complete: function () {
				$('#btn-ct-xacnhan').prop('disabled', false)
									.html('<span class="glyphicon glyphicon-ok"></span> Xác nhận chuyển');
			}
		});
	});

    // ----------------------------------------------------------
    // ĐÓNG MODAL CT → reload trang nếu có bản ghi thành công
    // ----------------------------------------------------------
    $('#modal-confirmCT').on('hidden.bs.modal', function () {
        if ($(this).data('co-thanh-cong')) {
            location.reload();
        }
    });

}); // end document.ready


// ============================================================
// XỬ LÝ KẾT QUẢ TRẢ VỀ TỪ SERVER
// ============================================================
function xuLyKetQua(resp, tongSo) {
    if (!resp || !resp.results) {
        hienThiLoiAjax('Phản hồi từ máy chủ không hợp lệ.');
        return;
    }

    var results    = resp.results;
    var soThanhCong = 0;
    var soLoi       = 0;
    var htmlRows    = '';

    results.forEach(function (r) {
        if (r.success) {
            soThanhCong++;
            htmlRows += '<tr class="success">'
                     +  '<td><span class="glyphicon glyphicon-ok text-success"></span></td>'
                     +  '<td><strong>' + escHtml(r.soHD) + '</strong></td>'
                     +  '<td class="text-success">' + escHtml(r.message) + '</td>'
                     +  '</tr>';
        } else {
            soLoi++;
            htmlRows += '<tr class="danger">'
                     +  '<td><span class="glyphicon glyphicon-remove text-danger"></span></td>'
                     +  '<td><strong>' + escHtml(r.soHD) + '</strong></td>'
                     +  '<td class="text-danger">' + escHtml(r.message) + '</td>'
                     +  '</tr>';
        }
    });

    // Tóm tắt
    var tomTat = '';
    if (soThanhCong > 0) {
        tomTat += '<div class="alert alert-success" style="margin-bottom:8px;">'
               +  '<span class="glyphicon glyphicon-ok-circle"></span> '
               +  '<strong>' + soThanhCong + '/' + tongSo + '</strong> hợp đồng chuyển chính thức thành công.'
               +  '</div>';
    }
    if (soLoi > 0) {
        tomTat += '<div class="alert alert-danger" style="margin-bottom:8px;">'
               +  '<span class="glyphicon glyphicon-exclamation-sign"></span> '
               +  '<strong>' + soLoi + '</strong> hợp đồng gặp lỗi — xem chi tiết bên dưới.'
               +  '</div>';
    }

    // Bảng chi tiết
    var bangChiTiet = '<table class="table table-condensed table-bordered" style="margin-top:8px;font-size:12px;">'
                   +  '<thead><tr><th width="30"></th><th width="120">Số HĐ</th><th>Kết quả</th></tr></thead>'
                   +  '<tbody>' + htmlRows + '</tbody>'
                   +  '</table>';

    $('#modal-ct-result').html(tomTat + bangChiTiet).show();

    // Ẩn nút xác nhận nếu đã xử lý xong
    $('#btn-ct-xacnhan').hide();

    // Đánh dấu cần reload khi đóng modal (nếu có ít nhất 1 thành công)
    if (soThanhCong > 0) {
        $('#modal-confirmCT').data('co-thanh-cong', true);
        // Bỏ chọn checkbox của các HĐ thành công
        results.forEach(function (r) {
            if (r.success) {
                $('input.chk-hd[data-sohd="' + r.soHD + '"]').closest('tr')
                    .addClass('success').find('input').prop('disabled', true);
            }
        });
    }
}


// ============================================================
// HỦY HỢP ĐỒNG — mở modal xác nhận
// ============================================================
function CancelContract(contractId, soHD) {
    $('#id_delete').val(contractId);
    $('#modal-delete-text').html(
        '⚠️ Bạn có chắc chắn muốn <strong>hủy hợp đồng ' + escHtml(soHD) + '</strong>?<br/>'
      + '<small class="text-danger">Thao tác này <strong>không thể hoàn tác</strong>.</small>'
    );
    $('#modal-confirmDelete').modal('show');
}


// ============================================================
// CÂY ĐA CẤP — hiển thị trong modal
// ============================================================
var DEPTH_COLORS = {
    1: { bg: '#f0f6ff', border: '#2a5f96', text: '#1a3c5e' },
    2: { bg: '#f0fff5', border: '#27ae60', text: '#1e8449' },
    3: { bg: '#fff8f0', border: '#e67e22', text: '#d35400' },
    4: { bg: '#fdf0ff', border: '#8e44ad', text: '#7d3c98' },
    5: { bg: '#fff0f0', border: '#c0392b', text: '#a93226' },
    6: { bg: '#f0fffe', border: '#16a085', text: '#0e8074' },
    7: { bg: '#fffff0', border: '#d4ac0d', text: '#b7950b' },
    8: { bg: '#f5f5f5', border: '#7f8c8d', text: '#626567' }
};

function showAgentTree(agentId, agentName) {
    $('#tree-modal-name').text(agentName);
    $('#tree-modal-body').html(
        '<div class="tree-loading">'
      + '<div class="spinner"></div><br/>Đang tải dữ liệu mạng lưới...</div>'
    );
    $('#modal-agent-tree').modal('show');

    $.ajax({
        url:      'VIDIX_function/getAgentTree.php',
        type:     'POST',
        dataType: 'json',
        data:     { agent_id: agentId },
        success: function (data) {
            if (data.error) {
                $('#tree-modal-body').html(
                    '<div class="text-danger text-center" style="padding:30px;">'
                  + '<span class="glyphicon glyphicon-warning-sign"></span> '
                  + escHtml(data.error) + '</div>'
                );
                return;
            }
            renderTree(data);
        },
        error: function () {
            $('#tree-modal-body').html(
                '<div class="text-danger text-center" style="padding:30px;">'
              + 'Không thể tải dữ liệu. Vui lòng thử lại.</div>'
            );
        }
    });
}

function renderTree(data) {
    var self  = data.self;
    var tree  = data.tree;
    var stats = data.stats;
    var html  = '';

    if (self.sponsor_id) {
        html += '<div class="sponsor-card">'
             +  '<span class="sp-icon">👤</span><div>'
             +  '<div class="sp-label">Người tuyển dụng trực tiếp</div>'
             +  '<div class="sp-name">' + escHtml(self.sponsor_name)
             +  ' <span class="label label-primary">' + escHtml(self.sponsor_rank_code) + '</span></div>'
             +  '</div></div>';
    } else {
        html += '<div class="sponsor-card" style="background:#f5f5f5;border-color:#ccc;">'
             +  '<span class="sp-icon">🌱</span><div>'
             +  '<div class="sp-label">Người tuyển dụng</div>'
             +  '<div class="sp-name text-muted"><i>Nhân viên gốc</i></div>'
             +  '</div></div>';
    }

    html += '<div class="self-card">'
         +  '<div class="self-name">' + escHtml(self.full_name) + '</div>'
         +  '<div class="self-meta">'
         +  '<span class="label label-warning" style="margin-right:5px;">'
         +  escHtml(self.rank_code) + ' - ' + escHtml(self.rank_name) + '</span>'
         +  escHtml(self.phone) + '</div></div>';

    html += '<div class="tree-stats">'
         +  renderStatBox(stats.tong_cap_duoi, 'Cấp dưới')
         +  renderStatBox(stats.tong_hd, 'Hợp đồng')
         +  '</div>';

    if (tree.length === 0) {
        html += '<div class="no-data" style="text-align:center;padding:20px;color:#7f8c9a;">'
             +  'Nhân viên này chưa có người cấp dưới.</div>';
    } else {
        html += '<div class="tree-section-title">Mạng lưới cấp dưới (' + tree.length + ' người)</div>';
        for (var i = 0; i < tree.length; i++) {
            html += renderTreeNode(tree[i]);
        }
    }
    $('#tree-modal-body').html(html);
}

function renderTreeNode(node) {
    var depth  = Math.min(parseInt(node.depth) || 1, 8);
    var colors = DEPTH_COLORS[depth] || DEPTH_COLORS[8];
    var connector = '';
    for (var i = 1; i < depth; i++) {
        connector += '<span style="display:inline-block;width:20px;text-align:center;color:#ddd;">│</span>';
    }
    connector += '<span style="display:inline-block;width:20px;text-align:center;color:#aaa;">├─</span>';

    var hhBadge = (node.is_active == 1)
        ? '<span style="background:#d4efdf;color:#1e8449;padding:1px 7px;border-radius:10px;font-size:10px;font-weight:700;">✓ HH</span>'
        : '<span style="background:#fdecea;color:#c0392b;padding:1px 7px;border-radius:10px;font-size:10px;font-weight:700;">✗ HH</span>';

    var rankBadge = '<span style="background:' + colors.border + ';color:#fff;'
                  + 'padding:1px 7px;border-radius:10px;font-size:10px;font-weight:700;">'
                  + escHtml(node.rank_code) + '</span>';

    return '<div style="display:flex;align-items:flex-start;padding:6px 8px;border-radius:5px;'
         + 'margin-bottom:3px;border-left:3px solid ' + colors.border + ';background:' + colors.bg + ';">'
         + '<div style="flex-shrink:0;font-size:12px;color:#bbb;white-space:nowrap;">' + connector + '</div>'
         + '<div style="flex:1;min-width:0;padding:0 8px;">'
         + '<div style="font-weight:700;font-size:13px;color:' + colors.text + ';">' + escHtml(node.full_name) + '</div>'
         + '<div style="font-size:11px;color:#7f8c9a;margin-top:2px;">'
         + escHtml(node.agent_code) + ' &bull; 📞 ' + escHtml(node.phone) + ' &bull; 📅 ' + escHtml(node.join_date)
         + '</div></div>'
         + '<div style="flex-shrink:0;text-align:right;">' + rankBadge + ' ' + hhBadge + '</div>'
         + '</div>';
}


// ============================================================
// HELPERS
// ============================================================
function layDanhSachIdDaChon() {
    var ids = [];
    $('.chk-hd[data-status="du_dk"]:checked').each(function () {
        ids.push($(this).val());
    });
    return ids;
}

function capNhatSoLuongChon() {
    var n = layDanhSachIdDaChon().length;
    if (n > 0) {
        $('#btn-chuyen-ct').removeClass('btn-default').addClass('btn-success')
                           .html('<span class="glyphicon glyphicon-share-alt"></span> Chuyển chính thức (' + n + ' HĐ)');
    } else {
        $('#btn-chuyen-ct').removeClass('btn-success').addClass('btn-default')
                           .html('<span class="glyphicon glyphicon-share-alt"></span> Chuyển chính thức');
    }
}

function hienThiLoiAjax(msg) {
    $('#modal-ct-result').html(
        '<div class="alert alert-danger">'
      + '<span class="glyphicon glyphicon-exclamation-sign"></span> '
      + 'Lỗi kết nối: ' + escHtml(msg)
      + '</div>'
    ).show();
}

function renderStatBox(num, label) {
    return '<div class="tree-stat-box">'
         + '<div class="tsb-num">' + num + '</div>'
         + '<div class="tsb-lbl">' + escHtml(String(label)) + '</div>'
         + '</div>';
}

function getUrlParam(name) {
    var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
    return (results && results.length > 1) ? results[1] : null;
}

function showAlert(type, message) {
    var alertId = '#' + type + '-alert';
    var textId  = '#text-' + type + '-message';
    $(textId).text(message);
    $(alertId).removeClass('alert-nonedisplay').fadeTo('slow', 1).fadeOut(6000, function () {
        $(alertId).alert('close');
    });
}

function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g,  '&amp;')
        .replace(/</g,  '&lt;')
        .replace(/>/g,  '&gt;')
        .replace(/"/g,  '&quot;')
        .replace(/'/g,  '&#039;');
}
// -------------------------------------------------------
// MỞ MODAL XÁC NHẬN CHUYỂN CHÍNH THỨC — từng HĐ
// -------------------------------------------------------
function xacNhanChuyenCT(hdId, soHD, loaiHD, trigia) {
    // Hiển thị thông tin HĐ trong modal
    $('#modal-ct-list').html(
        '<ul style="margin:0;padding-left:20px;">'
      + '<li><strong>' + escHtml(soHD) + '</strong>'
      + ' (' + escHtml(loaiHD) + ')'
      + ' — Trị giá: ' + escHtml(trigia) + '</li>'
      + '</ul>'
    );
    $('#modal-ct-count').text('1');
    $('#modal-ct-result').html('').hide();
    $('#btn-ct-xacnhan').show().prop('disabled', false)
                        .html('<span class="glyphicon glyphicon-ok"></span> Xác nhận chuyển');

    // Gán ID vào nút xác nhận để dùng khi gọi AJAX
    $('#btn-ct-xacnhan').data('hd-id', hdId).data('so-hd', soHD);

    $('#modal-confirmCT').removeData('co-thanh-cong').modal('show');
}