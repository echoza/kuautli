	<?php
	//access control 
	//allow access from autside the server
	header('Access-Control-Allow-Origin: *');
	//allow methods
	header('Access-Control-Allow-Methods: GET');
	//allow headers
	header('Access-Control-Allow-Headers: user, token');
	//get headers
	$headers = getallheaders();
	//check if headers were received
	if (isset($headers['user']) && isset($headers['token'])) {
		//authenticate token
		require_once($_SERVER['DOCUMENT_ROOT'].'/kuautli/security/security.php');
		if ($headers['token'] != Security::generateToken($headers['user'])) {
			echo json_encode(array(
			'status' => 998,
			'errorMessage' => 'Invalid security token for user '.$headers['user']
			));
			//kill the script
			die();
		}
	}
	else {
		echo json_encode(array(
			'status' => 999,
			'errorMessage' => 'Missing security headers'
		));
		//kill the script
		die();
	}
	
	//use guard class
	require_once($_SERVER['DOCUMENT_ROOT'].'/kuautli/models/guard.php');

	//GET (read)
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		//parameters 
		if(isset($_GET['type'])) {
			if($_GET['type'] == '1')
				echo Guard::getCountActiveGuardsJson();
			else 
				echo json_encode(array(
					'status' => 2,
					'errorMessage' => 'Invalid type'
				));
		}
		else 
			echo json_encode(array(
				'status' => 1,
				'errorMessage' => 'Missing parameters'
			));
	}
?>