<?php
$xhtmlMenu = '';

$arrMenus = array(
    array('class' => 'Dashboard', 'name' => 'Tổng quan', 'link' => '#', 'children' => array(
        array('name' => 'Dashboard',          'link' => '/Dashboard.php'),
    )),
    array('class' => 'System_Config', 'name' => 'Cấu hình', 'link' => '#', 'children' => array(
        array('name' => 'Cấp bậc nhân viên', 'link' => '/RankConfig.php'),
        array('name' => 'Điều kiện thăng cấp','link'=> '/RankUpgradeCondition.php'),
    )),
    array('class' => 'Agent_Mgmt', 'name' => 'Nhân viên', 'link' => '#', 'children' => array(
        array('name' => 'Danh sách nhân viên','link' => '/Agent.php'),
        array('name' => 'Điểm & Thăng cấp',  'link' => '/Points.php'),
    )),
    array('class' => 'Contract_Mgmt', 'name' => 'Hợp đồng', 'link' => '#', 'children' => array(
        array('name' => 'Danh sách hợp đồng','link' => '/Contract.php'),
    )),
    array('class' => 'Commission_Mgmt', 'name' => 'Hoa hồng', 'link' => '#', 'children' => array(
        array('name' => 'Quản lý hoa hồng',  'link' => '/Commission.php'),
    )),
    array('class' => 'System_M', 'name' => 'Hệ thống', 'link' => '#', 'children' => array(
        array('name' => 'Sao lưu',            'link' => '../thuchifunction/backupdata.php'),
        array('name' => 'Đổi mật khẩu',      'link' => 'login.php?fmess=2'),
    )),
);

foreach ($arrMenus as $menu) {
    $xhtmlMenu .= sprintf(
        '<li class="dropdown">
            <a href="%s" class="dropdown-toggle" data-toggle="dropdown"
               role="button" aria-haspopup="true" aria-expanded="false">
               %s <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">',
        $menu['link'], $menu['name']
    );
    foreach ($menu['children'] as $child) {
        $xhtmlMenu .= sprintf('<li><a href="%s">%s</a></li>', $child['link'], $child['name']);
    }
    $xhtmlMenu .= '</ul></li>';
}
?>
<ul class="nav navbar-nav navbar-right">
    <?php echo $xhtmlMenu; ?>
</ul>
