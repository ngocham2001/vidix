<?php 
    
    $xhtmlMenu='';
	//$condition = 'CE';

    /***** MAINTENANCE OFFICER *****/
          $arrMenus = array(
            array('class' => 'InputData','name' => 'Hợp đồng - Khách hàng','link' =>  '#', 'children' => array(
				array('name' => 'Tạo hợp đồng mới','link' => '/Hopdong_nhapmoi.php'),
				 array('name' => 'Hệ thống tư vấn bán hàng','link' => '/TT_Hopdong_TCB.php'),
                // array('name' => 'Thông tin Khách hàng','link' => '/Cus_Info.php'),
                // array('name' => 'Thông tin Vật tư','link' => '/VT_Info.php'),
				
            )),
             array('class' => 'StaffManage','name' => 'Nhân viên','link' =>  '#', 'children' => array(
				array('name' => 'Danh sách nhân viên','link' =>  '/agent.php'),
               // array('name' => 'Type machine','link' =>  'TypeMachine'),
                //array('name' => 'New type machine','link' => 'M_new'),            
            )), 
			 array('class' => 'Cofig','name' => 'Cấu hình','link' =>  '#', 'children' => array(
                array('name' => 'Thông tin cấp bậc','link' =>  '/RankConfig.php'),
				array('name' => 'Thông tin thăng cấp','link' =>  '/rank_upgrade_condition.php'),
				
               // array('name' => 'Type machine','link' =>  'TypeMachine'),
                //array('name' => 'New type machine','link' => 'M_new'),            
            )), 
           array('class' => 'System_M','name' => 'Hệ thống','link' =>  '#' , 'children' => array(
                array('name' => 'Sao lưu','link' =>   '../thuchifunction/backupdata.php'),
              //  array('name' => 'Receiv','link' =>  '/directionlink.php'),
                array('name' => 'Đổi mật khẩu','link' =>'login.php?fmess=2'),
            ))
		)			;
       
	   /* echo '<pre>'; 
	   print_r($arrMenus);
	   echo '</pre>';*/
        foreach ($arrMenus as $menu){
            //if($menu['class']!='M-logout'){
                $xhtmlMenu.=sprintf('<li class="dropdown">
                                        <a href="%s" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">%s <span class="caret"></span></a>
                                        <ul class="dropdown-menu">',$menu['link'],$menu['name']);
                foreach($menu['children'] as $menuChild){
                $xhtmlMenu.=sprintf('<li><a href="%s">%s</a></li>',$menuChild['link'],$menuChild['name'])	;
                }		
            $xhtmlMenu.='</ul> </li>';
           /* }
            else {
                $xhtmlMenu.=sprintf('   <li class="%s">
                                            <a href="%s" >%s </a>
                                        </li>',$menu['class'],$menu['link'],$menu['name']);	
            }*/
        }
 
    
    /***** ACCOUNTANT*****/
    /* if(!empty($permission['level']) && $permission['level']=='Accountant'){
        $arrMenus = array(
            array('class' => 'Accountant-Report','name' => 'Report','link' =>  '#', 'children' => array(
                array('name' => 'Monthly','link' =>  'A_Rep_monthly'),
                array('name' => 'Weekly','link' =>  'A_Rep_weekly'),
                array('name' => 'Closing','link' =>  'A_Rep_closing'),
                array('name' => 'Machine Detail','link' =>  'Rep_machinedetail'),
                array('name' => 'Machine List','link' =>  'Rep_machinelist'),
             )),
            array('class' => 'Accountant-Machine','name' => 'Machine','link' =>  '#', 'children' => array(
                array('name' => 'New machine','link' =>  'A_newMachine'),
                array('name' => 'Type machine','link' =>  'TypeMachine'),
                array('name' => 'Moving machine','link' =>  'MovingMachine'),
                //array('name' => 'New type machine','link' => 'Ma_new'),            
            )),
            array('class' => 'Accountant-Sparepart','name' => 'Sparepart','link' =>  '#', 'children' => array(
                array('name' => 'Issue','link' =>  URL_MAINTENANCE.'/sp_list_rep.php'),
                array('name' => 'Receiv','link' => URL_MAINTENANCE.'/sp_list.php'),
                array('name' => 'Sparepart list','link' => URL_MAINTENANCE.'/sp_list.php'),
            )),
           // array('class' => 'M-logout','name' => 'Logout','link' =>  '../../functions/logout.php'),
        );
       
        foreach ($arrMenus as $menu){
            //if($menu['class']!='M-logout'){
            $xhtmlMenu.=sprintf('<li class="dropdown">
                                    <a href="%s" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">%s <span class="caret"></span></a>
                                    <ul class="dropdown-menu">',$menu['link'],$menu['name']);
            foreach($menu['children'] as $menuChild){
                $xhtmlMenu.=sprintf('<li><a href="%s">%s</a></li>',$menuChild['link'],$menuChild['name'])	;
            }		
            $xhtmlMenu.='</ul> </li>';
           /* }
            else {
                $xhtmlMenu.=sprintf('   <li class="%s">
                                            <a href="%s" >%s </a>
                                        </li>',$menu['class'],$menu['link'],$menu['name']);	
            }
        }
    } */
	
	
?>

<ul class="nav navbar-nav navbar-right">
    <?php echo $xhtmlMenu;  ?>
	
</ul>