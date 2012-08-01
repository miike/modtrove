<?php

//includes

include("../../../lib/default_config.php");

require_once('nusoap-0/lib/nusoap.php');
// Create the server instance
$server = new soap_server();
// Initialize WSDL support
$server->configureWSDL('chemblogwsdl', 'urn:chemblogwsdl');
// Register the method to expose
$server->register('addpost_api',                // method name
//($bit_blog, $bit_title = NULL, $bit_content = NULL, $bit_meta = NULL, $bit_group = NULL)

    array('bit_blog' => 'xsd:string',
			'bit_title' => 'xsd:base64Binary', 
			'bit_content' => 'xsd:base64Binary',
			'bit_meta' => 'xsd:base64Binary',
			'bit_group' => 'xsd:base64Binary',
			'fdate' => 'xsd:base64Binary',
			'user' => 'xsd:base64Binary',
			'password' => 'xsd:base64Binary' ),
    array('bit_id' => 'xsd:string', 'result' => 'xsd:string', 'error' => 'xsd:string'),      // output parameters
    'urn:chemblogwsdl',                      // namespace
        'urn:chemblogwsdl#addpost_api',                // soapaction
    'rpc',                                // style
        'encoded',                            // use
    'The Add post for the blog'            // documentation
    );

$server->register('adddata_api',                // method name
//($bit_blog, $bit_title = NULL, $bit_content = NULL, $bit_meta = NULL, $bit_group = NULL)

    array(	'bit_data' => 'xsd:base64Binary',
			'user' => 'xsd:base64Binary',
			'password' => 'xsd:base64Binary' ),
    array('data_id' => 'xsd:string', 'result' => 'xsd:string', 'error' => 'xsd:string'),      // output parameters
    'urn:chemblogwsdl',                      // namespace
        'urn:chemblogwsdl#adddata_api',                // soapaction
    'rpc',                                // style
        'encoded',                            // use
    'Adds Data to a blog post'            // documentation
    );

  // Define the method as a PHP function


function adddata_api($bit_data, $user, $password){
		$ret['result'] = 0;
		global $ct_config;


		$usern = ereg_replace( "[^A-Za-z0-9]", "", $user);
		$pass =  addslashes($password);

		if(strlen($pass) && strlen($usern)){
			//input params
				$param = array('user' => base64_encode($usern), 'host' => base64_encode($ct_config['soap_host']),    'service'=>base64_encode('chemtools'), 'password' => base64_encode($pass), 'soton_ldap_only' => '0', 'ip' => $_SERVER['REMOTE_ADDR']);
				$client = new nu_soap_client($ct_config['soap_login'].'index.php?wsdl', true);
				$result = $client->call('chemlogin', $param);
				$_SESSION['user_name'] = $result['user'];
                $_SESSION['user_fname'] = $result['name'];
                $_SESSION['user_admin'] = $result['access'];
                $_SESSION['user_email'] = $result['email'];
                $_SESSION['user_uid'] = $result['uid'];


			if($result['result']==1){
					$ret['result'] = 1;
					$ret['error'] = "none: {$result['user']}";
				}else{
					$ret['result'] = 0;
					$ret['error'] = "Login Failed";
			}
		}

		
		if($ret['result'] == 1 && $bit_data){
		
		$metada = readxml($bit_data);

		if($metada['METADATA']['TYPE']){
			$dtype = $metada['METADATA']['TYPE'];
		}else{
			$dtype = 'img_meta';
		}	
		
		$metad = $metada;
		foreach($metad['METADATA'] as $key=>$value){
	
			if(substr($key,0,5) == 'DATA_'){
	
				if($value['TYPE']=="inline" && strlen($value['DATA'])){
						$sql = "INSERT INTO `{$ct_config['blog_db']}`.`blog_data` (`data_id`, `data_datetime`, `data_type`, `data_data`) VALUES (NULL, NOW(), '".strtolower(substr($key,5))."', '".addslashes(base64_decode($value['DATA']))."');";
					runQuery($sql,'add inline data');
					$id = mysql_insert_id();
					$metada['METADATA'][$key]['TYPE']="local";
					unset($metada['METADATA'][$key]['DATA']);
					$metada['METADATA'][$key]['ID']=$id;
				}
			}

		}

		$metad = writexml($metada);

		$sql = "INSERT INTO `{$ct_config['blog_db']}`.`blog_data` (`data_id`, `data_datetime`, `data_type`, `data_data`) VALUES (NULL, NOW(), '".addslashes($dtype)."', '".addslashes($metad)."');";

		runQuery($sql,'Blogs');

		$ret['data_id'] = mysql_insert_id(); 


		}

		return $ret;
	}




	function addpost_api($bit_blog, $bit_title, $bit_content, $bit_meta, $bit_group, $fdate, $user, $password){
		$ret['result'] = 2;
		global $ct_config;


		$usern = ereg_replace( "[^A-Za-z0-9]", "", $user);
		$pass =  addslashes($password);

		if(strlen($pass) && strlen($usern)){
			//input params
				$param = array('user' => base64_encode($usern), 'host' => base64_encode($ct_config['soap_host']),    'service'=>base64_encode('chemtools'), 'password' => base64_encode($pass), 'soton_ldap_only' => '0', 'ip' => $_SERVER['REMOTE_ADDR']);
				$client = new nu_soap_client($ct_config['soap_login'].'index.php?wsdl', true);
				$result = $client->call('chemlogin', $param);
				$_SESSION['user_name'] = $result['user'];
                $_SESSION['user_fname'] = $result['name'];
                $_SESSION['user_admin'] = $result['access'];
                $_SESSION['user_email'] = $result['email'];
                $_SESSION['user_uid'] = $result['uid'];


			if($result['result']==1){
					$ret['result'] = 1;
					$ret['error'] = "none: {$result['user']}";
				}else{
					$ret['result'] = 0;
					$ret['error'] = "Login Failed";
			}
		}
		if($ret['result'] == 1 && $bit_blog && $bit_title && $bit_content && $bit_group){
		
			if(strlen($fdate) && $fdate == (int)$fdate){
				$fdate = "FROM_UNIXTIME($fdate)";
			}else if(!strlen($fdate)){
				$fdate = "";
			}

			$ret['bit_id'] = add_blog(addslashes($bit_blog), addslashes($bit_title), addslashes($bit_content), addslashes($bit_meta), addslashes($bit_group), $fdate);
			
		}

		return $ret;
	}

// Use the request to (try to) invoke the service
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

?>
