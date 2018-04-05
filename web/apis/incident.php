<?php
	//access control
header('Header always set Access-Control-Allow-Credentials: true');
	//allow access from outside the server
	header('Access-Control-Allow-Origin: *');
	//allow methods
	header('Access-Control-Allow-Methods: GET, POST');
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

	//use Incident class
	require_once($_SERVER['DOCUMENT_ROOT'].'/kuautli/models/incident.php');

	//GET (Read)
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		//parameters
		if (isset($_GET['id'])) {
			try {
				//create object
				$p = new Incident($_GET['id']);
				//display
				echo json_encode(array(
					'status' => 0,
					'Incident' => json_decode($p->toJson())
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
			echo Incident::getAllJson();
		}

	}


	//POST (insert)
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		//check parameters
		if (isset($_POST['dispatcherId']) &&
			isset($_POST['userId']) &&
			isset($_POST['incidentTypeId']) &&
			isset($_POST['description']) &&
			isset($_POST['latitude']) &&
			isset($_POST['longitude']) &&
			isset($_POST['summon'])) {
			//error
			$error = false;
			//dispatcher
			try{
				$d = new Dispatcher($_POST['dispatcherId']);
			}
			catch(RecordNotFoundException $ex){
				echo json_encode(array(
					'status' => 2,
					'errorMessage' => 'Invalid Dispatcher'
				));
				$error = true;  //found error
			}
			//User
			try{
				$u = new User($_POST['userId']);
			}
			catch(RecordNotFoundException $ex){
				echo json_encode(array(
					'status' => 3,
					'errorMessage' => 'Invalid User'
				));
				$error = true;  //found error
			}
			//IncidentType
			try{
				$it = new IncidentType($_POST['incidentTypeId']);
			}
			catch(RecordNotFoundException $ex){
				$error = true;  //found error
				echo json_encode(array(
					'status' => 4,
					'errorMessage' => 'Invalid Incident Type'
				));
			}
			//add Incident
			if (!$error) {
				//create empty object
				$i = new Incident();
				//set values
				$i->setDispatcher($d);
				$i->setUser($u);
				$i->setIncidentType($it);
				$i->setDescription($_POST['description']);
				$i->setDate($_POST['dateTime']);
				$i->setLocation(new Location($_POST['latitude'], $_POST['longitude']));
				$i->setSummon($_POST['summon']);
				$i->setStatus($_POST['status']);
				//add
				if ($i->add())
					echo json_encode(array(
						'status' => 0,
						'message' => 'Incident added successfully',
                        'id' => $i->getId()
					));
				else
					echo json_encode(array(
						'status' => 5,
						'errorMessage' => 'Could not add Incident',
						'id' => $i->getId()
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
