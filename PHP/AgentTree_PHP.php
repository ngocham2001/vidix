<?php
/**
 * AgentTree_PHP.php
 * Backend cho trang AgentTree.php
 *
 * LƯU Ý: File này được gọi từ AgentTree.php sau khi define.php
 * và connection đã được khởi tạo sẵn — KHÔNG include lại define.php ở đây.
 * Biến $conn đã có sẵn từ AgentTree.php.
 */

// -------------------------------------------------------
// Nhận agent_id từ URL (?agent_id=123)
// -------------------------------------------------------
$agentId = isset($_GET['agent_id']) ? (int)$_GET['agent_id'] : 0;

if ($agentId <= 0) {
    header('Location: TT_Hopdong_TCB.php');
    exit;
}

// -------------------------------------------------------
// Lấy tên hiển thị cho tiêu đề trang (số HĐ B hoặc Iv)
// -------------------------------------------------------
$rowTitle = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT
        a.full_name,
        a.agent_code,
        rc.rank_code,
        COALESCE(hdb.SoHD, hdb.Iv) AS so_hd_b
    FROM   agent a
    JOIN   rank_config rc ON rc.rank_id = a.current_rank_id
    LEFT JOIN tbl_hopdong_ttchung hdb
           ON  hdb.agent_id_banhang = a.agent_id
           AND hdb.HDTuychonB = 1
    WHERE  a.agent_id = $agentId
    LIMIT  1
"));

if (!$rowTitle) {
    header('Location: TT_Hopdong_TCB.php');
    exit;
}

// Tiêu đề trang: ưu tiên số HĐ B, fallback về mã NV
$pageTitle = !empty($rowTitle['so_hd_b'])
    ? $rowTitle['so_hd_b']
    : '[' . $rowTitle['agent_code'] . '] ' . $rowTitle['full_name'];
