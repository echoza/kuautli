<?php
	//access control
    header('Header always set Access-Control-Allow-Credentials: true');
	//allow access from outside the server
	header('Access-Control-Allow-Origin: *');
	//allow methods
	header('Access-Control-Allow-Methods: POST, PUT');
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
	//use Assignment class
	require_once($_SERVER['DOCUMENT_ROOT'].'/kuautli/models/assignment.php');

	//POST
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		//check parameters
		if (isset($_POST['guard']) &&
			isset($_POST['incident']) &&
			isset($_POST['dispatcher'])) {
			//validation
			$error = false;
			//guard
			try{
				$g = new Guard($_POST['guard']);
			}
			catch(RecordNotFoundException $ex){
				$error = true;  //found error
				echo json_encode(array(
					'status' => 2,
					'errorMessage' => 'Invalid guard'
				));
			}
			//incident
			try{
				$i = new Incident($_POST['incident']);
			}
			catch(RecordNotFoundException $ex){
				$error = true;  //found error
				echo json_encode(array(
					'status' => 3,
					'errorMessage' => 'Invalid Incident'
				));
			}
			//dispatcher
			try{
				$d = new Dispatcher($_POST['dispatcher']);
			}
			catch(RecordNotFoundException $ex){
				$error = true;  //found error
				echo json_encode(array(
					'status' => 4,
					'errorMessage' => 'Invalid Dispatcher'
				));
			}
			if(!$error){
				//create dispatcher object
				$a = new Assignment();
				//assisgn values
				$a->setGuard($g);
				$a->setIncident($i);
				$a->setDispatcher($d);
				//add
				if($a->add())
					echo json_encode(array(
						'status' => 0,
						'errorMessage' => 'Assignment added successfully',
						'id' => $a->getId()
					));
				else
					echo json_encode(array(
						'status' => 5,
						'errorMessage' => 'Could not add Assignment',
						//'id' => $a->getId()
					));

			}
		}
		else
			echo json_encode(array(
				'status' =>1,
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
			if (isset($jsonData['id']) &&
				isset($jsonData['responseTime'])) {
				//error
				$error = false;
				//edit building
				if (!$error) {
					//create empty object
					try {
						$a = new Assignment($jsonData['id']);
						//set values
						$a->setResponseTime($jsonData['responseTime']);
						//add
						if ($a->edit())
							echo json_encode(array(
								'status' => 0,
								'message' => 'Assignment edited successfully'
							));
						else
							echo json_encode(array(
								'status' => 4,
								'errorMessage' => 'Could not edit Assignment'
							));
					}
					catch (RecordNotFoundException $ex) {
						echo json_encode(array(
							'status' => 3,
							'errorMessage' => 'Invalid Assignment id'
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
	//GET (Read)
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		echo Assignment::getAllAssignmentJson();	
	}
?>
