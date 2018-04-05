<?php
	//access control
header('Header always set Access-Control-Allow-Credentials: true');
	//allow access from outside the server
	header('Access-Control-Allow-Origin: *');
	//allow methods
	header('Access-Control-Allow-Methods: GET, POST, PUT');
	//allow headers
	header('Access-Control-Allow-Headers: user, token');
	//get headers
	$headers = getallheaders();
	//check if headers were received
	/*if (isset($headers['user']) && isset($headers['token'])) {
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
	}*/

	//use guard class
	require_once($_SERVER['DOCUMENT_ROOT'].'/models/guard.php');

	//GET (Read)
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		//parameters
		if (isset($_GET['id'])) {
			try {
				//create object
				$p = new Guard($_GET['id']);
				//display
				echo json_encode(array(
					'status' => 0,
					'guard' => json_decode($p->toJson())
				));
			}
			catch (RecordNotFoundException $ex) {
				echo json_encode(array(
					'status' => 1,
					'errorMessage' => $ex->get_message()
				));
			}
		}
		else {
			echo Guard::getAllJson();
		}

	}

	//POST (insert)
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		//check parameters
		if (isset($_POST['username']) &&
			isset($_POST['firstName']) &&
			isset($_POST['lastName']) &&
			isset($_POST['birthdate']) &&
			isset($_POST['gender']) &&
			isset($_POST['photo']) &&
			isset($_POST['shiftId']) &&
			isset($_POST['phone']) &&
			isset($_POST['email'])) {
			//error
			$error = false;
			//shift
			try {
				$s = new Shift($_POST['shiftId']);
			}
			catch (RecordNotFoundException $ex) {
				echo json_encode(array(
					'status' => 2,
					'errorMessage' => 'Invalid shift id'
				));
				$error = true; //found error
			}
			//add guard
			if (!$error) {
				//create empty object
				$g = new Guard();
				//set values
				$g->setAccount(new Account($_POST['username']));
				$g->setFirstName($_POST['firstName']);
				$g->setLastName($_POST['lastName']);
				$g->setBirthdate($_POST['birthdate']);
				$g->setGender($_POST['gender']);
				$g->setPhoto($_POST['photo']);
				$g->setShift($s);
				$g->setContactData(new ContactData($_POST['phone'], $_POST['email']));

				//add
				if ($g->add())
					echo json_encode(array(
						'status' => 0,
						'message' => 'Guard added successfully'

					));
				else
					echo json_encode(array(
						'status' => 3,
						'errorMessage' => 'Could not add guard'
					));
			}
		}
		else
			echo json_encode(array(
				'status' => 1,
				'errorMessage' => 'Missing parameters'
			));
	}

	//PUT (update)
	if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
		//read data
		parse_str(file_get_contents('php://input'), $putData);
		if (isset($putData['data'])) {
			//decode json
			$jsonData = json_decode($putData['data'], true);
			//check parameters
			if (isset($jsonData['username']) &&
				isset($jsonData['latitude']) &&
				isset($jsonData['longitude'])) {
				//error
				$error = false;
				//edit guard
				if (!$error) {
					//create empty object
					try {
						$g = new Guard($jsonData['username']);

						//set values
						$g->getAccount()->setUsername($jsonData['username']);
						$g->setLocation(new Location($jsonData['latitude'], $jsonData['longitude']));
						//add
						if ($g->editLocation())
							echo json_encode(array(
								'status' => 0,
								'message' => 'Guard edited successfully'
							));
						else
							echo json_encode(array(
								'status' => 5,
								'errorMessage' => 'Could not edit Guard'
							));
					}
					catch (RecordNotFoundException $ex) {
						echo json_encode(array(
							'status' => 4,
							'errorMessage' => 'Invalid Guard id'
						));
					}
				}
			}
			else
				echo json_encode(array(
					'status' => 2,
					'errorMessage' => 'Missing parameters'
				));
		}
		else
			echo json_encode(array(
				'status' => 1,
				'errorMessage' => 'Missing data parameter'
			));
	}

?>
