<?php
	//access control
header('Header always set Access-Control-Allow-Credentials: true');
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
	
	//use Message class
	require_once($_SERVER['DOCUMENT_ROOT'].'/kuautli/models/message.php');
	
	//GET (Read)
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		//parameters
		if (isset($_GET['guardId']) &&
			isset($_GET['dispatcherId'])) {
			//error
			$error = false;
			try {
				$g = new Guard($_GET['guardId']); // validate
			}
			catch(RecordNotFoundException $ex) {
				echo json_encode(array(
					'status' => 2,
					'errorMessage' => "Invalid Guard Id"
				));
				$error = true; //found error
			}
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
				echo Message::getAllJson($g->getAccount()->getUsername(),$d->getAccount()->getUsername());
			}
		}
		else 
			echo json_encode(array(
					'status' => 1,
					'errorMessage' => 'Missing Parameters'
				));	
	}				
		
		//POST (insert)
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		//check parameters
		if (isset($_POST['guardId']) &&
			isset($_POST['dispatcherId']) &&
			isset($_POST['message']) &&
			isset($_POST['sender'])) {
			//error
			$error = false;
			//guard
			try {
				$g = new Guard($_POST['guardId']);
			}
			catch (RecordNotFoundException $ex) {
				echo json_encode(array(
					'status' => 2,
					'errorMessage' => 'Invalid guard id'
				));
				$error = true; //found error
			}
			//dispatcher
			try {
				$d = new Dispatcher($_POST['dispatcherId']);
			}
			catch (RecordNotFoundException $ex) {
				echo json_encode(array(
					'status' => 3,
					'errorMessage' => 'Invalid dispatcher id'
				));
				$error = true; //found error
			}
			//add message
			if (!$error) {
				//create empty object
				$m = new Message();
				//set values
				$m->setGuard($g);
				$m->setDispatcher($d);
				$m->setMessage($_POST['message']);
				$m->setSender($_POST['sender']);
				//add
				if ($m->add())
					echo json_encode(array(
						'status' => 0,
						'message' => 'message added successfully'

					));
				else
					echo json_encode(array(
						'status' => 4,
						'errorMessage' => 'message not add guard'
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