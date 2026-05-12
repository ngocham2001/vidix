 <?php 
 /*   session_start() ;
    include_once 'define.php';
    include_once PATH_MAIN_FUNCTION.'/conn-login-logout.php';
   	$conn = connection_to_database();	
    $xhtmlItem='<table class="table table-hover scroll" >
                    <thead> 
                        <tr>  
                            <th width="180px" >Mã KH</th>  
                            <th width="280px"> Tên KH</th>  
                            <th width="100px">Điện thoại</th>  
                            <th width="300px">Địa chỉ</th>  
                            <th width="250px">Tài khoản NH</th>  
                            <th width="100px">Action</th>  
                        </tr>
                    </thead>
                    <tbody style="border:solid;"> ';

    //Display items
    $sql="SELECT `MaKH`, `TenKH`, `DT1`, `Diachi`,`Ghichu`,TKNH FROM `tbl_kh` WHERE 1 ";
    if(isset($_POST['search'])){
        $textcond=mysqli_real_escape_String($conn,$_POST['textcond']);
       
        if(!empty($textcond)) {
            $sql.="AND (upper(MaKH) LIKE '%".$textcond."%'
                        OR upper(`TenKH`) LIKE '%".$textcond."%'
                        OR upper(`DT1`) LIKE '%".$textcond."%'
                        OR upper(`Ghichu`) LIKE '%".$textcond."%'
                        OR upper(Diachi) LIKE '%".$textcond."%') ";}
    }

    $sql.="ORDER BY MaKH ASC ";
//echo $sql;
    $result=mysqli_query($conn,$sql) or die('Could not select Khach hang'.mysqli_error($conn));
    $data=array();
    $no=0;
    while ($set=mysqli_fetch_array($result,MYSQLI_ASSOC))  {
        $no++;
        $xhtmlItem.='<tr>
                         <td width="180px" ><strong>'.$set['MaKH'].' </strong>
						 <br/><a href="Cus_Info_nhapxuatVT.php?k='.urlencode(base64_encode($set['MaKH'])).'">Chi tiết vật tư</a>
						 <br/><a  href="Cus_Info_thanhtoan.php?k='.urlencode(base64_encode($set['MaKH'])).'">Chi tiết thanh toán</a>
						 </td>
                         <td width="280px">'.$set['TenKH'].'</td>
                         <td width="100px">';
		if(isset($set['DT1']) && !empty($set['DT1']))
			$xhtmlItem.=$set['DT1'];			
		$xhtmlItem.=' </td>
                       <td width="300px" >';
		if(isset($set['Diachi']) && !empty($set['Diachi']))
			$xhtmlItem.=$set['Diachi'];			  
		if(isset($set['Ghichu']) && !empty($set['Ghichu']))
			$xhtmlItem.='<br/>Ghi chú: '.$set['Ghichu'];			   		   
		$xhtmlItem.=' </td>
                      <td width="250px">';
		if(isset($set['TKNH']) && !empty($set['TKNH']))
			$xhtmlItem.= $set['TKNH'];   
					   			  
		$xhtmlItem.=' </td>
					  <td width="100px"> <a href="#"  onclick="editCustomer(\''.$set['MaKH'].'\');">Sửa</a> &nbsp; <a href="#"  onclick="DelCus(\''.$set['MaKH'].'\');">Xóa</a> 
					  </td></tr>';
        }
    $xhtmlItem.=' </tbody></table>';
	
	
	if(isset($_POST['excel'])){
		$textcond=!empty($_POST['textcond'])?mysqli_real_escape_String($conn,$_POST['textcond']):'';
		header('location:thuchiFunction/Cus_Info-exportExcel.php?textcond='.$textcond);
	}
	
	if(isset($_POST['delete-submit'])) {
		$id_delete=$_POST['id_delete'];
		header('location:thuchiFunction/Cus_Info-delCus.php?id_delete='.$id_delete);
	}

?> */