<?php
	//access control
header('Header always set Access-Control-Allow-Credentials: true');
	//allow access from outside the server
	header('Access-Control-Allow-Origin: *');
	//allow methods
	header('Access-Control-Allow-Methods: POST');
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
	
	//use User class
	require_once($_SERVER['DOCUMENT_ROOT'].'/kuautli/models/gps.php');
	
	
	//POST (insert)
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		//check parameters
		if (isset($_POST['username']) &&
			isset($_POST['latitude']) &&
			isset($_POST['longitude'])) {
			//error
			$error = false;
			//shift
			try {
				$g = new Guard($_POST['username']);
			}
			catch (RecordNotFoundException $ex) {
				echo json_encode(array(
					'status' => 2,
					'errorMessage' => 'Invalid guard username'
				));
				$error = true; //found error
			}
			//add guard
			if (!$error) {
				//create empty object
				$gp = new GPS();
				//set values
				$gp->setGuard($g);
				$gp->setLocation(new Location($_POST['latitude'], $_POST['longitude']));

				//add
				if ($gp->add())
					echo json_encode(array(
						'status' => 0,
						'message' => 'GPS added successfully'
						
					));
				else
					echo json_encode(array(
						'status' => 3,
						'errorMessage' => 'Could not add GPS'
					));
			}
		}
		else
			echo json_encode(array(
				'status' => 1,
				'errorMessage' => 'Missing parameters'
			));
	}

?>