<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/define.php';
include_once PATH_MAIN_FUNCTION . '/conn-login-logout.php';
$conn = connection_to_database();
session_write_close();

$agentId = isset($_GET['agent_id']) ? (int)$_GET['agent_id'] : 0;
if ($agentId <= 0) { header('Location: TT_Hopdong_TCB.php'); exit; }

$rowTitle = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT a.full_name, a.agent_code, rc.rank_code,
            COALESCE(hdb.SoHD, hdb.Iv) AS so_hd_b
     FROM   agent a
     JOIN   rank_config rc ON rc.rank_id = a.current_rank_id
     LEFT JOIN tbl_hopdong_ttchung hdb
            ON hdb.agent_id_banhang = a.agent_id AND hdb.HDTuychonB = 1
     WHERE  a.agent_id = $agentId LIMIT 1"
));
if (!$rowTitle) { header('Location: TT_Hopdong_TCB.php'); exit; }

$pageTitle = !empty($rowTitle['so_hd_b'])
    ? $rowTitle['so_hd_b']
    : '[' . $rowTitle['agent_code'] . '] ' . $rowTitle['full_name'];

$apiUrl = 'VIDIX_function/getAgentTreeNew.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cây HĐ — <?php echo htmlspecialchars($pageTitle); ?></title>
<style>
* { box-sizing: border-box; }
body {
    margin: 0;
    background: #f0f2f5;
    font-family: 'Segoe UI', Arial, sans-serif;
    font-size: 13px;
    color: #2c3e50;
    line-height: 1.5;
}
.page-wrapper { max-width: 960px; margin: 0 auto; padding: 16px 14px 60px; }

/* TOPBAR */
.tree-topbar {
    display: flex; align-items: center; justify-content: space-between;
    background: #1a3c5e; color: #fff;
    padding: 10px 18px; border-radius: 6px 6px 0 0;
}
.tree-topbar h4 { margin: 0; font-size: 15px; font-weight: 700; }
.btn-close {
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.3);
    color: #fff; border-radius: 4px; padding: 5px 16px;
    font-size: 12px; cursor: pointer;
}
.btn-close:hover { background: rgba(255,255,255,.28); }

/* CARD */
.tree-card {
    background: #fff; border: 1px solid #d8e3ec;
    border-top: none; border-radius: 0 0 6px 6px; padding: 20px;
}

/* SPONSOR */
.block-sponsor {
    padding: 9px 14px; background: #f5f5f5;
    border-left: 4px solid #95a5a6; border-radius: 4px; margin-bottom: 2px;
}
.label-tiny {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .7px; color: #7f8c8d; display: block; margin-bottom: 2px;
}

/* CONNECTOR */
.tree-connector { margin-left: 18px; border-left: 2px dashed #bdc3c7; height: 12px; }

/* BLOCK-SELF — nền đậm */
.block-self {
    background: #1d4e7a;
    border-radius: 8px;
    padding: 12px 16px 14px;
    margin-bottom: 2px;
    color: #fff;
}
.self-header {
    display: flex; align-items: center; flex-wrap: wrap; gap: 8px;
}
/* Cấp bậc C — to hơn, nổi bật */
.self-rank {
    font-size: 18px; font-weight: 800;
    color: rgba(255,255,255,.75);
    flex-shrink: 0;
}
/* Số HĐ — nhỏ hơn phiên bản trước */
.self-hd {
    font-size: 16px; font-weight: 700; color: #fff;
    flex-shrink: 0; letter-spacing: .3px;
}
.self-spacer { flex: 1 1 0; }

/* Pill TV và Điểm */
.self-pill {
    display: inline-flex; align-items: center; gap: 3px;
    padding: 3px 10px; border-radius: 16px;
    font-size: 11px; font-weight: 700; white-space: nowrap;
    flex-shrink: 0;
}
.self-pill .plbl { font-size: 10px; font-weight: 500; opacity: .75; }
.pill-ok  { background: rgba(39,174,96,.28);  color: #6fe8a0; border: 1px solid rgba(39,174,96,.4); }
.pill-bad { background: rgba(231,76,60,.28);  color: #f1948a; border: 1px solid rgba(231,76,60,.4); }
/* TV pill khi bad → có thể click để expand */
.pill-bad.pill-clickable {
    cursor: pointer;
    text-decoration: underline;
    text-underline-offset: 2px;
}
.pill-bad.pill-clickable:hover { opacity: .8; }

/* Dòng phụ */
.self-sub {
    font-size: 11px; color: rgba(255,255,255,.55); margin-top: 5px;
}
.self-sub strong { color: rgba(255,255,255,.88); }

/* ── CẤP DƯỚI ─────────────────────────────────── */
.group-wrapper {
    margin-left: 18px; border-left: 2px dashed #bdc3c7;
    padding-left: 14px; margin-bottom: 5px;
}
.group-header {
    padding: 7px 12px; border-radius: 4px; cursor: pointer;
    user-select: none; display: flex; align-items: center;
    justify-content: space-between;
}
.group-header:hover { filter: brightness(.96); }
.rank-badge {
    padding: 2px 9px; border-radius: 10px;
    font-size: 11px; font-weight: 700; margin-right: 6px;
}
.toggle-arrow { font-size: 11px; color: #999; flex-shrink: 0; }
.group-detail { display: none; margin-top: 2px; }

/* THÀNH VIÊN — 1 dòng compact */
.member-row {
    padding: 6px 12px 6px 14px;
    margin: 2px 0;
    border-radius: 3px;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
/* Số HĐ link */
.member-hd-link {
    font-size: 13px; font-weight: 700;
    text-decoration: underline; text-underline-offset: 2px;
    cursor: pointer; white-space: nowrap; flex-shrink: 0;
}
.member-hd-link:hover { opacity: .72; }
/* Tên + mã */
.member-meta { font-size: 11px; color: #7f8c8d; flex-shrink: 0; }
/* Tổng HĐ phụ trách */
.member-total { font-size: 11px; color: #555; white-space: nowrap; flex-shrink: 0; }
.member-total strong { color: #2980b9; }
/* Pill nhỏ TV / Điểm trong member */
.m-pill {
    font-size: 11px; font-weight: 700;
    padding: 1px 8px; border-radius: 10px;
    white-space: nowrap; flex-shrink: 0;
}
.m-ok  { background: #d5f5e3; color: #1e8449; }
.m-bad { background: #fdecea; color: #c0392b; }
/* TV pill bad → clickable expand */
.m-bad.m-clickable {
    cursor: pointer;
    text-decoration: underline;
    text-underline-offset: 2px;
}
.m-bad.m-clickable:hover { opacity: .78; }

/* ── EXPAND ──────────────────────────────────── */
.expand-block { margin-top: 2px; margin-bottom: 2px; animation: fadeIn .2s ease; }
.expand-inner {
    margin-left: 12px; padding: 10px 14px;
    background: #f7fbff; border-left: 3px solid #d5e8f5;
    border-radius: 0 0 6px 0;
}
@keyframes fadeIn { from { opacity:0; transform:translateY(-3px); } to { opacity:1; transform:none; } }
.expand-loading {
    padding: 6px 0; color: #7f8c8d; font-size: 12px;
    font-style: italic; display: flex; align-items: center; gap: 7px;
}
.expand-error { color: #c0392b; font-size: 12px; }

/* SPINNERS */
.tree-loading { text-align: center; padding: 60px 0; color: #7f8c8d; }
.spinner {
    display: inline-block; width: 34px; height: 34px;
    border: 4px solid #dde3ea; border-top-color: #2980b9;
    border-radius: 50%; animation: spin .8s linear infinite;
}
.spinner-sm {
    display: inline-block; width: 13px; height: 13px;
    border: 2px solid #dde3ea; border-top-color: #2980b9;
    border-radius: 50%; animation: spin .7s linear infinite; flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }
.tree-error { padding: 40px 20px; color: #c0392b; }

@media print {
    .btn-close { display: none; }
    .group-detail, .expand-block { display: block !important; }
    body { background: #fff; }
    .tree-card { border: none; }
}
</style>
</head>
<body>
<div class="page-wrapper">

    <div class="tree-topbar">
        <h4>&#x25B6; Cây hợp đồng:
            <span id="page-hd-title"><?php echo htmlspecialchars($pageTitle); ?></span>
        </h4>
        <button class="btn-close" onclick="window.close()">&#10005; Đóng</button>
    </div>

    <div class="tree-card" id="tree-content">
        <div class="tree-loading"><div class="spinner"></div><br/>Đang tải...</div>
    </div>

</div>
<script>
var INIT_AGENT_ID = <?php echo (int)$agentId; ?>;
var API_URL       = <?php echo json_encode($apiUrl, JSON_UNESCAPED_UNICODE); ?>;
var _uid          = 0;

// ── RANK → màu sắc giảm dần (C1 đậm → C8 gần trắng) ─────────────
// bg: nền dòng member, border: viền trái, text: chữ số HĐ, badge: nền badge rank
var RANK_PALETTE = {
    'C8': { bg:'#ffffff', border:'#d0d0d0', text:'#999',    badge:'#bbb',    txtBadge:'#fff' },
    'C7': { bg:'#fafafa', border:'#c0c8d0', text:'#888',    badge:'#b0bac4', txtBadge:'#fff' },
    'C6': { bg:'#f5f8fb', border:'#a8c2d8', text:'#5a7a90', badge:'#7fa8c4', txtBadge:'#fff' },
    'C5': { bg:'#f2f7fc', border:'#7aaac8', text:'#2d6080', badge:'#5591b8', txtBadge:'#fff' },
    'C4': { bg:'#eef5fa', border:'#5090b8', text:'#1d5070', badge:'#3d7ea6', txtBadge:'#fff' },
    'C3': { bg:'#e8f2f9', border:'#2e78b0', text:'#154060', badge:'#2a6fa0', txtBadge:'#fff' },
    'C2': { bg:'#e0edf8', border:'#1a5e99', text:'#0d3558', badge:'#185a93', txtBadge:'#fff' },
    'C1': { bg:'#d6e7f5', border:'#104e87', text:'#082c4e', badge:'#104e87', txtBadge:'#fff' },
};
function getRankPalette(code) {
    return RANK_PALETTE[code] || { bg:'#f9f9f9', border:'#bbb', text:'#555', badge:'#aaa', txtBadge:'#fff' };
}

// ── EVENT DELEGATION ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    var content = document.getElementById('tree-content');

    content.addEventListener('click', function (e) {
        // Link số HĐ hoặc pill TV clickable
        var hdLink = e.target.closest('[data-expand-agent]');
        if (hdLink) {
            e.preventDefault();
            var aid = parseInt(hdLink.getAttribute('data-expand-agent'), 10);
            if (aid > 0) expandMember(aid, hdLink);
            return;
        }
        // Tiêu đề nhóm → toggle
        var gh = e.target.closest('.group-header');
        if (gh) { toggleGroup(gh.getAttribute('data-target')); return; }
    });

    loadTree(INIT_AGENT_ID);
});

// ── API ───────────────────────────────────────────────────────────
function fetchTree(agentId, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', API_URL, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.withCredentials = true;
    xhr.onload = function () {
        if (xhr.status !== 200) {
            callback(null, 'Lỗi HTTP ' + xhr.status); return;
        }
        var data;
        try { data = JSON.parse(xhr.responseText); }
        catch (e) {
            callback(null, 'JSON không hợp lệ:<br><pre style="font-size:11px;max-height:120px;'
                + 'overflow:auto;background:#f5f5f5;padding:6px;border-radius:3px;">'
                + xhr.responseText.replace(/&/g,'&amp;').replace(/</g,'&lt;').substring(0,1000)
                + '</pre>');
            return;
        }
        if (data.error) { callback(null, data.error); return; }
        callback(data, null);
    };
    xhr.onerror = function () { callback(null, 'Không thể kết nối: <code>' + API_URL + '</code>'); };
    xhr.send('agent_id=' + encodeURIComponent(agentId));
}

function loadTree(agentId) {
    var c = document.getElementById('tree-content');
    c.innerHTML = '<div class="tree-loading"><div class="spinner"></div><br/>Đang tải...</div>';
    fetchTree(agentId, function (data, err) {
        if (err) { showError(err); return; }
        c.innerHTML = buildTreeBlock(data, true);
    });
}
function showError(msg) {
    document.getElementById('tree-content').innerHTML =
        '<div class="tree-error"><strong>&#9888; Lỗi</strong><br/><br/>' + msg + '</div>';
}

// ── EXPAND ────────────────────────────────────────────────────────
function expandMember(agentId, triggerEl) {
    // Tìm .member-row hoặc .expand-inner cha gần nhất để insert sau
    var anchor = triggerEl;
    while (anchor && !anchor.classList.contains('member-row')) {
        anchor = anchor.parentElement;
    }
    if (!anchor) return;

    // Toggle nếu đã có
    var existId = anchor.getAttribute('data-expand-id');
    if (existId) {
        var bl = document.getElementById(existId);
        if (bl) {
            var hidden = bl.style.display === 'none';
            bl.style.display = hidden ? 'block' : 'none';
            triggerEl.style.opacity = hidden ? '1' : '0.5';
        }
        return;
    }

    _uid++;
    var blockId = 'exp_' + agentId + '_' + _uid;
    anchor.setAttribute('data-expand-id', blockId);
    triggerEl.style.opacity = '0.5';

    var ph = document.createElement('div');
    ph.id = blockId; ph.className = 'expand-block';
    ph.innerHTML = '<div class="expand-inner"><div class="expand-loading">'
        + '<span class="spinner-sm"></span>&nbsp;Đang tải...</div></div>';
    anchor.insertAdjacentElement('afterend', ph);

    fetchTree(agentId, function (data, err) {
        triggerEl.style.opacity = '1';
        if (err) {
            ph.innerHTML = '<div class="expand-inner"><div class="expand-error">&#9888; '
                + escHtml(err) + '</div></div>';
            return;
        }
        ph.innerHTML = '<div class="expand-inner">' + buildTreeBlock(data, false) + '</div>';
    });
}

function toggleGroup(id) {
    var el = document.getElementById(id);
    if (!el) return;
    el.style.display = (el.style.display === 'block') ? 'none' : 'block';
}

// ── HELPER: lấy nhãn số HĐ (SoHD ưu tiên, fallback Iv, kèm badge chờ) ──
function getHdLabel(hdBObj) {
    if (!hdBObj) return { label: '—', isCho: false };
    var soHd = hdBObj.so_hd_b || '';
    var iv   = hdBObj.Iv       || hdBObj.iv || '';
    // COALESCE phía PHP đã gộp SoHD||Iv vào so_hd_b
    // Nhưng để an toàn, nếu so_hd_b rỗng thì thử iv
    var label = soHd || iv || '—';
    var isCho = (!soHd) && (hdBObj.hd_cho === '1' || hdBObj.TrangThaiHDcho === '1');
    return { label: label, isCho: isCho };
}

// ── BUILD TREE BLOCK ──────────────────────────────────────────────
// isRoot=true  → giao diện đầy đủ (lần mở đầu tiên)
// isRoot=false → giao diện gọn trong expand-inner (như ảnh 3)
function buildTreeBlock(data, isRoot) {
    var self           = data.self;
    var selfHdB        = data.self_hd_b;
    var selfTotalHd    = data.self_total_hd;
    var selfDirectHd   = data.self_direct_hd;
    var upgrade        = data.upgrade;
    var sponsorHdB     = data.sponsor_hd_b;
    var childrenByRank = data.children_by_rank;
    var html = '';

    // ── Tính số HĐ hiển thị của bản thân ─────────────────────────
    var selfInfo   = getHdLabel(selfHdB);
    var selfHdLabel = selfInfo.label;
    var selfIsCho   = selfInfo.isCho;
    var soNgay      = selfHdB ? (parseInt(selfHdB.so_ngay) || 0) : 0;

    // ── Tính upgrade pills ────────────────────────────────────────
    var tvHave = 0, tvNeed = 0, ptHave = 0, ptNeed = 0;
    var tvOk = true, ptOk = true, toRank = '';
    if (upgrade) {
        tvHave = upgrade.have_direct || 0; tvNeed = upgrade.need_direct || 0;
        ptHave = upgrade.have_points || 0; ptNeed = upgrade.need_points || 0;
        tvOk   = upgrade.lack_direct <= 0; ptOk   = upgrade.lack_points <= 0;
        toRank = upgrade.to_rank_code || '';
    }

    // ════════════════════════════════════════════════════════════
    // CHẾ ĐỘ ROOT — hiển thị đầy đủ (ảnh 1)
    // ════════════════════════════════════════════════════════════
    if (isRoot) {
        // Sponsor
        var hasSponsor = self.sponsor_id && (sponsorHdB || self.sponsor_name);
        if (hasSponsor) {
            var spLabel = sponsorHdB ? escHtml(sponsorHdB.so_hd_b || '—') : '—';
            html += '<div class="block-sponsor">';
            html += '<span class="label-tiny">HĐ TUYỂN DỤNG (CẤP TRÊN TRỰC TIẾP)</span>';
            html += '<strong style="font-size:15px;color:#2c3e50;">' + spLabel + '</strong>';
            html += ' &nbsp;<span style="color:#aaa;">|</span>&nbsp; ';
            html += '<span style="color:#555;">[' + escHtml(self.sponsor_code) + '] '
                 +  escHtml(self.sponsor_name) + '</span>';
            if (self.sponsor_rank_code) {
                html += ' <span style="background:#2980b9;color:#fff;padding:1px 8px;'
                     +  'border-radius:10px;font-size:10px;font-weight:700;margin-left:4px;">'
                     +  escHtml(self.sponsor_rank_code) + '</span>';
            }
            html += '</div><div class="tree-connector"></div>';
        }

        // Bản thân — block navy đầy đủ
        html += '<div class="block-self" style="background:#1d4e7a;">';
        html += '<div class="self-header">';
        html += '<span class="self-rank">' + escHtml(self.rank_code) + '</span>';
        html += '<span class="self-hd">' + escHtml(selfHdLabel) + '</span>';
        // Tên nhân viên ngay sau số HĐ
        html += '<span style="font-size:13px;color:rgba(255,255,255,.8);font-weight:600;">'
             +  escHtml(self.full_name) + '</span>';
        if (selfIsCho) {
            html += '<span style="font-size:11px;color:#f1948a;font-style:italic;">'
                 +  '&nbsp;Hợp đồng chưa chính thức</span>';
        }
        html += '<span class="self-spacer"></span>';
        if (upgrade && tvNeed > 0) {
            html += '<span class="self-pill ' + (tvOk ? 'pill-ok' : 'pill-bad') + '">'
                 +  '<span class="plbl">TV&nbsp;</span>' + tvHave + '/' + tvNeed + '</span>';
        }
        if (upgrade) {
            html += '<span class="self-pill ' + (ptOk ? 'pill-ok' : 'pill-bad') + '">'
                 +  '<span class="plbl">Điểm&nbsp;</span>'
                 +  fmtNum(ptHave) + '/' + fmtNum(ptNeed) + '</span>';
        }
        html += '</div>';
        html += '<div class="self-sub">';
        if (!selfIsCho && selfHdB) {
            html += '&#128197; <strong>' + soNgay + ' ngày</strong>&nbsp;&bull;&nbsp;';
        }
        html += 'Tổng HĐ phụ trách: <strong>' + selfTotalHd + '</strong>';
        html += '&nbsp;&bull;&nbsp;HĐ trực tiếp: <strong>' + selfDirectHd + '</strong>';
        if (toRank) {
            html += '&nbsp;&bull;&nbsp;Mục tiêu: <strong>' + escHtml(toRank) + '</strong>';
        }
        html += '</div></div>'; // end block-self

        // Cấp dưới — dạng nhóm có toggle
        html += renderChildren(childrenByRank, false);

    // ════════════════════════════════════════════════════════════
    // CHẾ ĐỘ EXPAND — chỉ hiện cấp dưới, KHÔNG lặp lại block bản thân
    // (bản thân đã hiện ở dòng member-row bên trên rồi)
    // ════════════════════════════════════════════════════════════
    } else {
        // Cấp dưới trong expand — render trực tiếp, không có toggle nhóm
        html += renderChildren(childrenByRank, true);
    }

    return html;
}

// ── RENDER CẤP DƯỚI ──────────────────────────────────────────────
// inExpand=false → có tiêu đề nhóm + toggle (chế độ root)
// inExpand=true  → danh sách thẳng, gộp thành block nhạt dần (chế độ expand)
function renderChildren(childrenByRank, inExpand) {
    var html = '';
    if (!childrenByRank || childrenByRank.length === 0) {
        html += '<div class="tree-connector"></div>';
        html += '<div style="padding:7px 14px;color:#7f8c8d;font-style:italic;">'
             +  '&mdash; Chưa có thành viên cấp dưới trực tiếp.</div>';
        return html;
    }

    html += '<div class="tree-connector"></div>';

    for (var g = 0; g < childrenByRank.length; g++) {
        var group  = childrenByRank[g];
        var gCount = group.members.length;
        var pal    = getRankPalette(group.rank_code);

        if (!inExpand) {
            // ── Chế độ root: nhóm có header + toggle ──────────────
            _uid++;
            var gdId = 'gd_' + _uid;
            html += '<div class="group-wrapper">';
            html += '<div class="group-header"'
                 +  ' style="background:' + pal.bg + ';border-left:3px solid ' + pal.border + ';"'
                 +  ' data-target="' + gdId + '">';
            html += '<div>';
            html += '<span class="rank-badge" style="background:' + pal.badge
                 +  ';color:' + pal.txtBadge + ';">' + escHtml(group.rank_code) + '</span>';
            html += '<span style="font-weight:700;color:' + pal.text + ';">'
                 +  escHtml(group.rank_name) + '</span>';
            html += ' &mdash; <strong style="color:' + pal.text + ';">' + gCount + ' HĐ</strong>';
            html += '</div>';
            html += '<span class="toggle-arrow">&#9660;</span>';
            html += '</div>'; // group-header
            html += '<div class="group-detail" id="' + gdId + '">';
            for (var m = 0; m < group.members.length; m++) {
                html += renderMember(group.members[m], pal);
            }
            html += '</div></div>'; // group-detail + group-wrapper

        } else {
            // ── Chế độ expand: mỗi thành viên là block navy nhạt ──
            for (var m2 = 0; m2 < group.members.length; m2++) {
                html += renderMemberCompact(group.members[m2], pal);
            }
        }
    }
    return html;
}

// ── RENDER THÀNH VIÊN (chế độ root) — 1 dòng compact ─────────────
function renderMember(m, pal) {
    var agentId = parseInt(m.agent_id, 10) || 0;
    var rawHd   = m.so_hd_b || '—';
    var isCho   = !m.so_hd_b && (m.hd_cho === '1' || m.TrangThaiHDcho === '1');
    // Chỉ hiện link expand khi có cấp dưới (so_cap_duoi_tt > 0)
    var hasSubAgents = parseInt(m.so_cap_duoi_tt, 10) > 0;
    var canExp  = agentId > 0 && hasSubAgents;

    var html = '<div class="member-row" style="background:' + pal.bg
             + ';border-left:3px solid ' + pal.border + ';">';

    // Số HĐ / Iv — luôn có link nếu canExp
    if (canExp) {
        html += '<a class="member-hd-link" style="color:' + pal.text + ';"'
             +  ' data-expand-agent="' + agentId + '" title="Xem cây">'
             +  escHtml(rawHd)
             +  ' <span style="font-size:9px;opacity:.5;">&#9654;</span></a>';
    } else {
        html += '<strong style="font-size:13px;font-weight:700;color:' + pal.text + ';">'
             +  escHtml(rawHd) + '</strong>';
    }
    if (isCho) {
        html += ' <em style="color:#e74c3c;font-size:10px;">Hợp đồng chưa chính thức</em>';
    }

    // Tên + mã
    html += '<span class="member-meta">' + escHtml(m.full_name)
         +  ' &bull; ' + escHtml(m.agent_code) + '</span>';

    // Tổng HĐ
    html += '<span class="member-total">Tổng: <strong>'
         +  (m.tong_hd_phu_trach || 0) + '</strong> HĐ</span>';

    // Pills điểm + TV
    if (m.upgrade) {
        var u    = m.upgrade;
        var ptOk = u.lack_points <= 0;
        html += '<span class="m-pill ' + (ptOk ? 'm-ok' : 'm-bad') + '">'
             +  (ptOk ? '&#10003;' : '&#10005;') + ' '
             +  fmtNum(u.have_points) + '/' + fmtNum(u.need_points) + ' đ</span>';

        if ((u.need_direct || 0) > 0) {
            var tvOk2 = u.lack_direct <= 0;
            var tvCls = 'm-pill ' + (tvOk2 ? 'm-ok' : 'm-bad');
            // TV pill đỏ → clickable expand CHỈ KHI có cấp dưới
            var tvAttr = (!tvOk2 && canExp)
                ? ' data-expand-agent="' + agentId + '" title="Nhấn để xem cấp dưới"'
                : '';
            if (!tvOk2 && canExp) tvCls += ' m-clickable';
            html += '<span class="' + tvCls + '"' + tvAttr + '>'
                 +  (tvOk2 ? '&#10003;' : '&#10005;') + ' '
                 +  u.have_direct + '/' + u.need_direct + ' TV</span>';
        }

        var totalLack = Math.max(u.lack_hd,0) + Math.max(u.lack_points,0)
                      + Math.max(u.lack_direct||0,0);
        if (totalLack === 0) {
            html += '<span style="font-size:11px;color:#27ae60;font-weight:700;">'
                 +  '&#10003; Lên ' + escHtml(u.to_rank_code) + '!</span>';
        }
    } else {
        html += '<span style="font-size:11px;color:#95a5a6;">&#10003; Cao nhất</span>';
    }

    html += '</div>'; // member-row
    return html;
}

// ── RENDER THÀNH VIÊN (chế độ expand) — block nhỏ gọn, màu nhạt ──
// Không có viền trái nhiều màu, chỉ dùng 1 màu nền nhạt đồng nhất
function renderMemberCompact(m, pal) {
    var agentId = parseInt(m.agent_id, 10) || 0;
    var rawHd   = m.so_hd_b || '—';
    var isCho   = !m.so_hd_b && (m.hd_cho === '1' || m.TrangThaiHDcho === '1');
    // Chỉ hiện link expand khi có cấp dưới
    var hasSubAgents = parseInt(m.so_cap_duoi_tt, 10) > 0;
    var canExp  = agentId > 0 && hasSubAgents;

    // Màu nền nhạt dần — dùng màu bg của palette nhưng giảm thêm opacity
    var bgStyle = 'background:' + pal.bg + ';border-left:3px solid ' + pal.border
                + ';border-radius:4px;margin:3px 0;padding:7px 12px;';

    var html = '<div style="' + bgStyle + '">';

    // Dòng 1: Cấp | Số HĐ | badge chờ
    html += '<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">';
    html += '<span style="font-size:14px;font-weight:800;color:' + pal.text
         +  ';opacity:.7;">' + escHtml(m.rank_code) + '</span>';

    if (canExp) {
        html += '<a class="member-hd-link" style="color:' + pal.text + ';font-size:13px;"'
             +  ' data-expand-agent="' + agentId + '" title="Xem cây">'
             +  escHtml(rawHd)
             +  ' <span style="font-size:9px;opacity:.45;">&#9654;</span></a>';
    } else {
        html += '<strong style="font-size:13px;color:' + pal.text + ';">'
             +  escHtml(rawHd) + '</strong>';
    }
    if (isCho) {
        html += '<em style="font-size:10px;color:#c0392b;">Hợp đồng chưa chính thức</em>';
    }

    // Tên + mã (mờ)
    html += '<span style="font-size:11px;color:#7f8c8d;">'
         +  escHtml(m.full_name) + ' &bull; ' + escHtml(m.agent_code) + '</span>';

    // Spacer + Pills bên phải
    html += '<span style="flex:1"></span>';
    if (m.upgrade) {
        var u    = m.upgrade;
        var ptOk = u.lack_points <= 0;
        html += '<span class="m-pill ' + (ptOk ? 'm-ok' : 'm-bad') + '" style="font-size:10px;">'
             +  fmtNum(u.have_points) + '/' + fmtNum(u.need_points) + ' đ</span>';

        if ((u.need_direct || 0) > 0) {
            var tvOk3  = u.lack_direct <= 0;
            var tvCls3 = 'm-pill ' + (tvOk3 ? 'm-ok' : 'm-bad');
            // Chỉ clickable khi có cấp dưới
            var tvAttr3 = (!tvOk3 && canExp)
                ? ' data-expand-agent="' + agentId + '" title="Nhấn để xem cấp dưới"'
                : '';
            if (!tvOk3 && canExp) tvCls3 += ' m-clickable';
            html += '<span class="' + tvCls3 + '" style="font-size:10px;"' + tvAttr3 + '>'
                 +  u.have_direct + '/' + u.need_direct + ' TV</span>';
        }
    }
    html += '</div>'; // dòng 1

    // Dòng 2: thống kê nhỏ
    html += '<div style="font-size:11px;color:#7f8c8d;margin-top:3px;">';
    html += 'Tổng HĐ phụ trách: <strong style="color:' + pal.text + ';">'
         +  (m.tong_hd_phu_trach || 0) + '</strong>';
    html += '&nbsp;&bull;&nbsp;HĐ trực tiếp: <strong style="color:' + pal.text + ';">'
         +  (m.hd_truc_tiep || 0) + '</strong>';
    if (m.upgrade && m.upgrade.to_rank_code) {
        html += '&nbsp;&bull;&nbsp;Mục tiêu: <strong>' + escHtml(m.upgrade.to_rank_code) + '</strong>';
    }
    html += '</div>';

    html += '</div>'; // end compact block
    return html;
}

// ── HELPERS ───────────────────────────────────────────────────────
function fmtNum(n) { return (parseFloat(n) || 0).toLocaleString('vi-VN'); }
function escHtml(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;')
                    .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}
</script>
</body>
</html>