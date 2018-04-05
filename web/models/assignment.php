<?php
	//use files
	require_once('mysqlconnection.php');
	require_once('exceptions/recordnotfoundexception.php');
	require_once('guard.php');
	require_once('incident.php');
	require_once('dispatcher.php');
	require_once('report.php');
	//class
	class Assignment
	{
		//Attributes
		private $id;
		private $guard;
		private $incident;
		private $dispatcher;
		private $responseTime;
		private $report;

		//Setters and getters
		public function getId(){return $this->id;}
		public function setId($value){$this->id = $value;}
		public function getGuard(){return $this->guard;}
		public function setGuard($value){$this->guard = $value;}
		public function getIncident(){return $this->incident;}
		public function setIncident($value){$this->incident = $value;}
		public function getDispatcher(){return $this->dispatcher;}
		public function setDispatcher($value){$this->dispatcher = $value;}
		public function getResponseTime(){return $this->responseTime;}
		public function setResponseTime($value){$this->responseTime = $value;}
		public function getReport(){ $this->report; }
		public function setReport($value){ $this->report = $value; }

	    //constructor
		function __construct() {
			//empty object
			if(func_num_args() == 0){
				$this->id = '';
				$this->guard = new Guard();
				$this->Incident = new Incident();
				$this->dispatcher = new Dispatcher();
				$this->responseTime = 0;
				$this->report = new Report();
			}
			//empty object
			if(func_num_args() == 2){
				//get arguments
				$arguments = func_get_args();
				$this->average = $arguments[1];
			}
			//object with data from database
			if(func_num_args() == 1){
				//get id
				$id = func_get_arg(0);
				//get connection
				$connection = MySqlConnection::getConnection();
				//query
				$query = 'call usp_getResponseTimeAssignment(?)';
				//command
				$command = $connection->prepare($query);
				//bind parameters
				$command->bind_param('s', $id);
				//execute
				$command->execute();
				//bind results
				$command->bind_result($id, $responseTime);
				//fetch data
				$found = $command->fetch();
				//close command
				mysqli_stmt_close($command);
				//close connection
				$connection->close();
				//pass values to the attributes
				if ($found) {
					$this->id = $id;
					$this->responseTime = $responseTime;
				}
				else {		
					//throw exception if record not found
					throw new RecordNotFoundException();
				}
			}
			//object with data from arguments
			if(func_num_args()== 5){
			  //get arguments
			  $arguments = func_get_args();
			  //pass arguments to attributes
			  $this->id = $arguments[0];			  
			  $this->responseTime = $arguments[1];
			  $this->dispatcher = $arguments[2];
			  $this->guard = $arguments[3];
			  $this->report = $arguments[4];
			}
			//object with data from arguments
			if(func_num_args()== 3){
			  //get arguments
			  $arguments = func_get_args();
			  //pass arguments to attributes
			  $this->id = $arguments[0];			
			  $this->guard = $arguments[1];
			  $this->incident = $arguments[2];
			}
		}

		//represents the object in JSON format
		public function toJson() {
			return json_encode(array(
				'id' => $this->id,
				'guard' => json_decode($this->guard->toJson()),
				'dispatcher' => json_decode($this->dispatcher->toJson()),
				'responseTime' => $this->responseTime,
				'report' => json_decode($this->report->toJson())
			));
		}	
		public function toJsonResponse(){
			return json_encode(array(
				'Average' => $this->average
			));
		}
		public function toJsonAllAssignment(){
			return json_encode(array(
				'id' => $this->id,
				'guard' => $this->guard,
				'incident' => $this->incident,
			));
		}

		//class methods

		//get all
		public static function getAll($id) {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getAssignmentperIncident(?);';
			//command
			$command = $connection->prepare($query);
			//bind parameters
			$command->bind_param('s', $id);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($id, $responseTime, 
			$idReport, $dateTime, $description, $falseAlarm, 
			$username, $lastLogin, $firstName, $lastName, $birthdate, $gender, $photo, $phone, $email, $status,
			$idShift, $name, $start, $end,
			$disUsername, $acoDisLastLogin, $disFirstName, $disLastName, $disBirthdate, $disGender, $disPhotograph, $disPhone, $disEmail
			);
			//fetch data
			while ($command->fetch()) {
					$report = new Report($idReport, $falseAlarm, $description, $dateTime);
					$contactData = new ContactData($phone, $email);
					$account = new Account($username, $lastLogin);
                    $time = new Time($start, $end);
					$shift = new Shift($idShift, $name, $time);
					$guard = new Guard($account, $firstName, $lastName, $birthdate, $gender, $photo, $contactData, $status, $shift);			
					$contactDataDispacher = new ContactData($disPhone, $disEmail);
					$accountDipacher = new Account($disUsername, $acoDisLastLogin);
					$dispatcher = new Dispatcher($accountDipacher, $disFirstName, $disLastName, $disBirthdate, $disGender, $disPhotograph, $contactDataDispacher);					
				array_push($list, new Assignment($id, $responseTime, $dispatcher, $guard, $report));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}
		//get all in JSON format
		public static function getAllJson($id) {
			//list
			$list = array();
			//get all
			foreach(self::getAll($id) as $item) {
				array_push($list, json_decode($item->toJson()));
			}
			//return json encoded array
			return json_encode(array(
				'Assignment' => $list
			));
		}
		
		
		//instance methods

		//add
		public function add() {
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_addAssignment(?, ?, ?, ?)';
			//command
			$command = $connection->prepare($query);
			//parameters
			$command->bind_param('ssss',
			$this->id = $this->createId(),
			$this->guard->getAccount()->getUsername(),
			$this->incident->getId(),
			$this->dispatcher->getAccount()->getUsername());
			//execute
			$result = $command->execute();
			//close statement
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return result
			return $result;
		}
		//create id
		public static function createId(){
				$id = substr ( date(m) , -1 , 1 ) . substr ( date(d) , 0 , 2 ) . substr( time(h), -1, 1) . substr(time(s), -2, 2);
			return $id;
		}
		//edit
  		public function edit() {
  			//update
  			$update = 'call usp_updateResponseTime( ?, ?)';
  			//connection
  			$connection = MySqlConnection::getConnection();
  			//command
  			$command = $connection->prepare($update);
  			//parameters
  			$command->bind_param('ss',
  				$this->id,
  				$this->responseTime);
  			//execute
  			$edited = $command->execute();
  			//close statement
  			mysqli_stmt_close($command);
  			//close connection
  			$connection->close();
  			//return result
  			return $edited;
  		}
		
		public static function getAveragePerIncidents(){
			//list 
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'CALL usp_KPIAverageperResponse();';
			//command 
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($average);
			//fetch data
			while($command->fetch()) {
				
				array_push($list, new Assignment(null, $average));				
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}
		//Average in JSON format
		public static function getAverageperIncidentJson(){
			//list
			$list = array();
			//get all 
			foreach (self::getAveragePerIncidents() as $item) {
				array_push($list, json_decode($item->toJsonResponse()));
			}
			//return json encoded array
			return json_encode(array(
				'Incidents' => $list
			));
		}
		
		//get all
		public static function getAllAssignment() {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getAssignmentwithIncidentActive();';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($id, $guard, $incident);
			//fetch data
			while ($command->fetch()) {
				$id = $id;
				$guard = $guard;
				$incident = $incident;
				array_push($list, new Assignment($id, $guard, $incident));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		//get all in JSON format
		public static function getAllAssignmentJson() {
			//list
			$list = array();
			//get all
			foreach(self::getAllAssignment() as $item) {
				array_push($list, json_decode($item->toJsonAllAssignment()));
			}
			//return json encoded array
			return json_encode(array(
				'assignments' => $list
			));
		}
	
	}
 ?>
