<?php
	//allow access
header('Header always set Access-Control-Allow-Credentials: true');
	header('Access-Control-Allow-Origin: *');
	//allow methods
	header('Access-Control-Allow-Methods: GET');
	//allow headers
	header('Access-Control-Allow-Headers: user, password');
	//read headers
	$headers = getallheaders();
	//use user class
	require_once($_SERVER['DOCUMENT_ROOT'].'/kuautli/models/dispatcher.php');
	//use security
	require_once($_SERVER['DOCUMENT_ROOT'].'/kuautli/security/security.php');
	//check if headers were received
	if (isset($headers['user']) && isset($headers['password'])) {
		//authenticate user
		try {
			//create user
			$d = new Dispatcher($headers['user'], $headers['password']);
			//diplay
			echo json_encode(array(
				'status' => 0,
				'username' => json_decode($d->toJson()),
				'token' => Security::generateToken($headers['user'])
			));
		}
		catch (InvalidUserException $ex) {
			echo json_encode(array(
				'status' => 2,
				'errorMessage' => $ex->get_message()
			));
		}
	}
	else
		echo json_encode(array(
			'status' => 1,
			'errorMessage' => 'Missing headers'
		));
?>
