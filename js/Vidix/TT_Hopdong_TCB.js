// -------------------------------------------------------
// Render 1 thành viên cấp dưới (CẬP NHẬT)
// -------------------------------------------------------
function renderChildMember(m, rankColor) {
    var hdLabel = m.so_hd_b ? escHtml(m.so_hd_b) : '—';
    var hdCho   = (!m.so_hd_b && m.hd_cho === '1')
        ? ' <em style="color:#e74c3c;font-size:11px;">(chờ)</em>'
        : '';

    var html = '';
    html += '<div style="padding:8px 10px 8px 14px;margin:3px 0;background:#fafafa;'
         +  'border-left:3px solid ' + rankColor.border + ';border-radius:3px;">';

    // ===== DÒNG 1: Số HĐ B + Tên =====
    html += '<strong style="color:#2c3e50;font-size:14px;">' + hdLabel + '</strong>' + hdCho;
    html += ' &nbsp;<span style="color:#7f8c8d;font-size:12px;">'
         +  '(' + escHtml(m.full_name) + ' &bull; ' + escHtml(m.agent_code) + ')</span>';

    // ===== DÒNG 2: Thống kê con =====
    html += '<br/>';
    html += '<span style="font-size:12px;color:#555;margin-right:12px;">'
         +  'Tổng HĐ phụ trách: <strong style="color:#2980b9;">' + m.tong_hd_phu_trach + '</strong></span>';
    html += '<span style="font-size:12px;color:#555;margin-right:12px;">'
         +  'HĐ trực tiếp: <strong style="color:#2980b9;">' + m.hd_truc_tiep + '</strong></span>';
    html += '<span style="font-size:12px;color:#e67e22;margin-right:12px;">'
         +  'Điểm: <strong>' + formatNumber(m.tong_diem) + '</strong></span>';

    // ===== DÒNG 3: ĐIỀU KIỆN TĂNG CẤP =====
    if (m.upgrade) {
        var u = m.upgrade;
        
        // Kiểm tra điều kiện nào còn thiếu
        var lackHd     = u.lack_hd;
        var lackPoints = u.lack_points;
        var lackDirect = u.lack_direct;
        
        // Tổng những gì còn thiếu
        var totalLack = Math.max(lackHd, 0) + Math.max(lackPoints, 0) + Math.max(lackDirect, 0);
        
        html += '<br/>';
        html += '<span style="font-size:11px;color:#7f8c8d;font-weight:600;">📈 Lên ' 
             +  escHtml(u.to_rank_code) + ':</span>&nbsp;&nbsp;';

        // HĐ
        var hd_ok = u.lack_hd <= 0;
        html += renderCondTag(
            u.have_hd + '/' + u.need_hd + ' HĐ',
            hd_ok ? 0 : u.lack_hd,
            'HĐ', hd_ok
        );
        html += ' ';

        // Điểm
        var pt_ok = u.lack_points <= 0;
        html += renderCondTag(
            formatNumber(u.have_points) + '/' + formatNumber(u.need_points) + ' đ',
            pt_ok ? 0 : u.lack_points,
            'điểm', pt_ok
        );

        // TV trực tiếp (nếu có yêu cầu)
        if (u.need_direct > 0) {
            html += ' ';
            var dir_ok = u.lack_direct <= 0;
            html += renderCondTag(
                u.have_direct + '/' + u.need_direct + ' TV',
                dir_ok ? 0 : u.lack_direct,
                'người', dir_ok
            );
        }

        // Tóm tắt tình trạng
        if (totalLack === 0) {
            html += '<br/><span style="font-size:11px;color:#27ae60;font-weight:700;">✓ Đủ điều kiện lên cấp!</span>';
        } else {
            html += '<br/>';
            var lacks = [];
            if (lackHd > 0)     lacks.push('Thiếu ' + lackHd + ' HĐ');
            if (lackPoints > 0) lacks.push('Thiếu ' + formatNumber(lackPoints) + ' điểm');
            if (lackDirect > 0) lacks.push('Thiếu ' + lackDirect + ' TV');
            html += '<span style="font-size:11px;color:#c0392b;font-weight:600;">'
                 +  '✗ ' + lacks.join(' + ') + '</span>';
        }
    } else {
        html += '<br/><small style="color:#27ae60;font-size:11px;">✓ Đã đạt cấp cao nhất</small>';
    }

    html += '</div>';
    return html;
}

// -------------------------------------------------------
// Helper: Render tag điều kiện (đạt/chưa đạt) - CẬP NHẬT
// -------------------------------------------------------
function renderCondTag(label, lack, unit, ok) {
    if (ok) {
        return '<span style="background:#d5f5e3;color:#1e8449;padding:2px 10px;border-radius:10px;'
             + 'font-size:11px;font-weight:700;display:inline-block;">'
             + '✓ ' + escHtml(String(label)) + '</span>';
    } else {
        return '<span style="background:#fdecea;color:#c0392b;padding:2px 10px;border-radius:10px;'
             + 'font-size:11px;font-weight:700;display:inline-block;">'
             + '✗ ' + escHtml(String(label)) + '</span>';
    }
}