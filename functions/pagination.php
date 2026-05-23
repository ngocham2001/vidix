<?php
/**
 * renderPagination()
 * Render Bootstrap 3 pagination + thông tin số bản ghi.
 *
 * @param int $currentPage  Trang hiện tại (1-based)
 * @param int $totalPages   Tổng số trang
 * @param int $totalRows    Tổng số bản ghi
 * @param int $perPage      Số dòng mỗi trang
 * @return string           HTML pagination
 */
function renderPagination($currentPage, $totalPages, $totalRows, $perPage) {
    if ($totalPages <= 1 && $totalRows <= $perPage) {
        // Chỉ hiện thống kê, không hiện nút trang
        return '<div style="margin-top:8px;"><small class="text-muted">Tổng cộng: <strong>'
             . number_format($totalRows) . '</strong> bản ghi</small></div>';
    }

    $from = ($currentPage - 1) * $perPage + 1;
    $to   = min($currentPage * $perPage, $totalRows);

    $html  = '<div class="row" style="margin-top:12px;">';

    // Thông tin số bản ghi
    $html .= '<div class="col-sm-5" style="padding-top:7px;">';
    $html .= '<small class="text-muted">Hiển thị <strong>' . number_format($from) . ' – ' . number_format($to)
           . '</strong> / <strong>' . number_format($totalRows) . '</strong> bản ghi'
           . ' &nbsp;|&nbsp; ' . $perPage . ' dòng/trang</small>';
    $html .= '</div>';

    // Nút phân trang
    $html .= '<div class="col-sm-7"><nav><ul class="pagination pagination-sm" style="margin:0;float:right;">';

    // « Trang đầu
    if ($currentPage > 1) {
        $html .= '<li><a href="#" onclick="goToPage(1);return false;" title="Trang đầu">&laquo;</a></li>';
        $html .= '<li><a href="#" onclick="goToPage(' . ($currentPage - 1) . ');return false;" title="Trang trước">&lsaquo;</a></li>';
    } else {
        $html .= '<li class="disabled"><a>&laquo;</a></li>';
        $html .= '<li class="disabled"><a>&lsaquo;</a></li>';
    }

    // Cửa sổ số trang (hiển thị 5 trang xung quanh trang hiện tại)
    $winStart = max(1, $currentPage - 2);
    $winEnd   = min($totalPages, $currentPage + 2);

    if ($winStart > 1) {
        $html .= '<li><a href="#" onclick="goToPage(1);return false;">1</a></li>';
        if ($winStart > 2) $html .= '<li class="disabled"><a>…</a></li>';
    }

    for ($i = $winStart; $i <= $winEnd; $i++) {
        $active = ($i === $currentPage) ? ' class="active"' : '';
        $html .= '<li' . $active . '>'
               . '<a href="#" onclick="goToPage(' . $i . ');return false;">' . $i . '</a>'
               . '</li>';
    }

    if ($winEnd < $totalPages) {
        if ($winEnd < $totalPages - 1) $html .= '<li class="disabled"><a>…</a></li>';
        $html .= '<li><a href="#" onclick="goToPage(' . $totalPages . ');return false;">' . $totalPages . '</a></li>';
    }

    // › Trang cuối
    if ($currentPage < $totalPages) {
        $html .= '<li><a href="#" onclick="goToPage(' . ($currentPage + 1) . ');return false;" title="Trang sau">&rsaquo;</a></li>';
        $html .= '<li><a href="#" onclick="goToPage(' . $totalPages . ');return false;" title="Trang cuối">&raquo;</a></li>';
    } else {
        $html .= '<li class="disabled"><a>&rsaquo;</a></li>';
        $html .= '<li class="disabled"><a>&raquo;</a></li>';
    }

    $html .= '</ul></nav></div>';
    $html .= '</div>'; // /row

    return $html;
}

/**
 * getPaginationParams()
 * Tính toán các tham số phân trang từ total rows.
 *
 * @param int $totalRows
 * @param int $requestedPage  Từ POST['page']
 * @param int $perPage
 * @return array [currentPage, totalPages, offset]
 */
function getPaginationParams($totalRows, $requestedPage, $perPage = 20) {
    $totalPages  = max(1, (int)ceil($totalRows / $perPage));
    $currentPage = max(1, min((int)$requestedPage, $totalPages));
    $offset      = ($currentPage - 1) * $perPage;
    return [$currentPage, $totalPages, $offset];
}
