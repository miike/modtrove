<?php

include_once("{$ct_config['pwd']}/lib/functions_database.php");

function checkzone($zone_id, $remote = 0,$blog_id=0){
global $user_can_edit,$user_can_post,$ct_config;

if(!$zone_id){
	//$user_can_post = 1;
	return 1;
}elseif(substr($_SERVER['REMOTE_ADDR'],0,4)=="127."){
	return 1;
}elseif( lookup_or_default('user_admin', $_SESSION, 0) > 1){
	return 1;
}elseif($zone_id==-1){
	if($blog_id){
		$blog = db_get_blog_by_id($blog_id);
		if(array_key_exists('user_name', $_SESSION) && $blog['blog_user']==$_SESSION['user_name']){
				$user_can_edit = 1;
			return 1;
		}else{
			return 0;
		}
	}
	if($_SESSION['user_name'] !=""){
		return 1;
	}else{
		return 0;
	}
}else{
	$pass = 0;
	$sql = "SELECT * FROM  `{$ct_config['blog_db']}`.`blog_zone` WHERE `zone_id` = $zone_id";
	if($remote)
		$sql .= " AND `zone_type` = 'user'";
	
	if(isset($ct_config['zones_c'][$zone_id]) && $remote){
		return $ct_config['zones_c'][$zone_id];
	}
	$result = runQuery($sql,'Blogs');
		while($rowb = mysql_fetch_array($result)){
		switch($rowb['zone_type']){
			case "user":
				$users = split(";",$rowb['zone_res']);
				foreach($users as $user){
				if($user == 'any'){
					if( is_set_not_empty('user_name', $_SESSION) && $_SESSION['user_admin'] != 0 ){
						$pass = 1;
					}else{
						$pass = 0;
					}
					}elseif($user == 'all'){
						$pass = 1;
					}elseif(!isset($_SESSION['user_name']) || "" == $_SESSION['user_name']){
                          $pass = 0;
                    }elseif($user == $_SESSION['user_name']){
						$pass = 1;
					}
				}
				break;
			case "user_edit":
				$users = split(";",$rowb['zone_res']);
				foreach($users as $user){
				if("" == $_SESSION['user_name'] && $user != 'all'){
                                      $user_can_edit = 0;        
                                        }elseif($user == $_SESSION['user_name']){
					 $user_can_edit= 1;
					}elseif($user == 'any' && $_SESSION['user_name'] !=""){
					$user_can_edit = 1;
					}elseif($user == 'all' ){
					$user_can_edit = 1;
					}
				}
				break;
				case "user_post":
			 	$user_can_post= 0;
				$users = split(";",$rowb['zone_res']);
				foreach($users as $user){
				if("" == $_SESSION['user_name'] && $user != 'all'){
                                      $user_can_post = 0;        
                                        }elseif($user == $_SESSION['user_name']){
					 $user_can_post= 1;
					}elseif($user == 'any' && $_SESSION['user_name'] !=""){
					$user_can_post = 1;
					}elseif($user == 'all'){
					$user_can_post = 1;
					}
				}
				break;


			default:
		}


		}

	if($remote){
		$ct_config['zones_c'][$zone_id] = $pass;
	}
	
	return $pass;

	
}


}
?>
