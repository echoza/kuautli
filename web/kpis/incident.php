<?php
	//access control
	//allow access from outside the server
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
	
	//use incident class
	require_once($_SERVER['DOCUMENT_ROOT'].'/kuautli/models/incident.php');
	
	//GET (Read)
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		//parameters
		if (isset($_GET['type'])) {
			if($_GET['type'] == '1')
				echo Incident::getCountTypeIncidentJson();
			else {				
				if($_GET['type'] == '2') 
					echo Incident::getCountSummonJson();
				else {
					if($_GET['type'] == '3')
						echo Incident::getAllLocationJson();//Incident::getCountMonthJson();
					else {
						if($_GET['type'] == '4')
							echo Incident::getCountActiveJson();
						else 
							echo json_encode(array(
								'status' => 2,
								'errorMessage' => 'Invalid type'
							));
					}
				}
			}
		}
		else
			echo json_encode(array(
					'status' => 1,
					'errorMessage' => 'Missing parameters'
				));
		
	}
?>