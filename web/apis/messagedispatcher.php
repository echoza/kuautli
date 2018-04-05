<?php
	//access control
	//allow access from outside the server
	header('Access-Control-Allow-Origin: *');
	//allow methods
	header('Access-Control-Allow-Methods: GET');
	//allow headers
	header('Access-Control-Allow-Headers: user, token');
header('Header always set Access-Control-Allow-Credentials: true');
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
	
	//use Message class
	require_once($_SERVER['DOCUMENT_ROOT'].'/kuautli/models/message.php');
	
	//GET (Read)
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		//parameters
		if (isset($_GET['dispatcherId'])) {
			//error
			$error = false;
			try {
				$d = new Dispatcher($_GET['dispatcherId']); // validate
			}
			catch(RecordNotFoundException $ex) {
				echo json_encode(array(
					'status' => 2,
					'errorMessage' => "Invalid Dispatcher Id"
				));
				$error = true; //found error
			}
			if (!$error) {
				echo Message::getAllLastMessagesDispatcherJson($d->getAccount()->getUsername());
			}
		}
		else 
			echo json_encode(array(
					'status' => 1,
					'errorMessage' => 'Missing Parameters'
				));		
	}

	
?>