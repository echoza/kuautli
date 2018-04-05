<?php
	//use files
	require_once('incidenttype.php');
	require_once('mysqlconnection.php');
	require_once('exceptions/recordnotfoundexception.php');
	require_once('location.php');
	require_once('user.php');
	require_once('dispatcher.php');
	require_once('assignment.php');

	//class
	class Incident{

		//attributes
		private $id ;
		private $user;
		private $dispatcher;
		private $incidentType;
		private $description;
		private $date;
		private $location;
		private $summon;
		private $status;
		private $quantity;
		private $month;
		private $other;
		//setters and getters
		public function getId() { return $this->id; }
		public function setId($value) { $this->id = $value; }
		public function getUser() { return $this->user; }
		public function setUser($value) { return $this->user = $value; }
		public function getDispatcher() { return $this->dispatcher; }
		public function setDispatcher($value) { return $this->dispatcher = $value; }
		public function getIncidentType(){ return $this->incidentType; }
		public function setIncidentType($value){ return $this->incidentType = $value; }
		public function getDescription() { return $this->description; }
		public function setDescription($value) { return $this->description = $value; }
		public function getDate() { return $this->date; }
		public function setDate($value) { return $this->date = $value; }
		public function getLocation() { return $this->location; }
		public function setLocation($value) { $this->location = $value; }
		public function getSummon(){ return $this->summon; }
		public function setSummon($value){ return $this->summon = $value; }
		public function getStatus(){ return $this->status; }
		public function setStatus($value){ return $this->status = $value; }
		public function getAssignment(){ return $this->assignment; }
		public function setAssignment($value){ return $this->assignment = $value; }
		public function getQuantity(){ return $this->quantity; }
		public function setQuantity($value){ return $this->quantity = $value; }
		public function getMonth(){ return $this->month; }
		public function setMonth($value){ return $this->month = $value; }
		public function getOther(){ return $this->other; }
		public function setOther($value){ return $this->other = $value; }
		//constructors
		public function __construct(){
			//empty object
			if(func_num_args() == 0){
				$this->id = '';
				$this->user = new User();
				$this->dispatcher = new Dispatcher();
				$this->incidentType = new IncidentType();
				$this->description = '';
				$this->date = '';
				$this->location = new Location();
				$this->summon = 0;
				$this->status = 0;
			}
			//object with data from database
			if(func_num_args() == 1){
				//get id
				$id = func_get_arg(0);
				//get connection
				$connection = MySqlConnection::getConnection();
				//query
				$query = 'call usp_getOneIncident(?);';
				//command
				$command = $connection->prepare($query);
				//bind parameters
				$command->bind_param('s', $id);
				//execute
				$command->execute();
				//bind results
				$command->bind_result($id,
				$disUsername, $disFirstName, $disLastName, $disBirthdate, $disGender, $disPhotograph, $disPhone, $disEmail, $acoDisLastLogin,
				$usUsername, $usFirstName, $usLastName, $usBirthdate, $usGender, $usPhotograph, $usPhoneNumber, $usEmail, $acoUsLastLogin,
				$itId, $itDescription, $description, $dateTime, $latitude, $longitude, $summon, $status);
				//fetch data
				$found = $command->fetch();
				//close command
				mysqli_stmt_close($command);
				//close connection
				$connection->close();
				//pass values to the attributes
				if ($found) {
					$this->id = $id;
					$accountDipacher = new Account($disUsername, $acoDisLastLogin);
					$contactDataDispacher = new ContactData($disPhone, $disEmail);
					$this->dispatcher = new Dispatcher($accountDipacher, $disFirstName, $disLastName, $disBirthdate, $disGender, $disPhotograph, $contactDataDispacher);
					$contacDataUser = new ContactData($usPhoneNumber, $usEmail);
					$accountUser = new Account($usUsername, $acoUsLastLogin);
					$this->user = new User($accountUser, $usFirstName, $usLastName, $usBirthdate, $usGender, $usPhotograph, $contacDataUser);
					$this->incidentType = new IncidentType($itId, $itDescription);
					$this->description = $description;
					$this->date = $dateTime;
					$this->location = new Location($latitude, $longitude);
					$this->summon = $summon;
					$this->status = $status;
				}
				else {
					//throw exception if record not found
					throw new RecordNotFoundException();
				}
			}
			//object with data from arguments
			if(func_num_args() == 9){
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
				$this->id = $arguments[0];
				$this->dispatcher = $arguments[1];
				$this->user = $arguments[2];
				$this->incidentType = $arguments[3];
				$this->description = $arguments[4];
				$this->date = $arguments[5];
				$this->location = $arguments[6];
				$this->summon = $arguments[7];
				$this->status = $arguments[8];
			}
			//object with data from arguments
			if(func_num_args() == 2){
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
				$this->incidentType = $arguments[0];
				$this->quantity = $arguments[1];
			}
			//object with data from arguments
			if(func_num_args() == 3){
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
				$this->month = $arguments[0];
				$this->quantity = $arguments[1];
			}
			//object with data from arguments
			if(func_num_args() == 4){
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
				$this->other = $arguments[0];
			}
			//object with data from arguments
			if(func_num_args() == 5){
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
				$this->id = $arguments[0];
				$this->location = $arguments[1];
			}
			//nuevo constructor
			if(func_num_args() == 6){
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
				$this->id = $arguments[0];
				$this->user = $arguments[1];
				$this->incidentType = $arguments[2];
				$this->description = $arguments[3];
				$this->date = $arguments[4];
				$this->status = $arguments[5];
			}
		}


		//represents the object in JSON format
		public function toJson() {
			return json_encode(array(
					'id' => $this->id,
					'dispatcher' => json_decode($this->dispatcher->toJson()),
					'user' => json_decode($this->user->toJson()),
					'incidentType' => json_decode($this->incidentType->toJson()),
					'description' => $this->description,
					'date' => $this->date,
					'location' => json_decode($this->location->toJson()),
					'summon' => $this->summon,
					'status' => $this->status,
					'Assignments' => json_decode(Assignment::getAllJson($this->id))

			));
		}

		//represents the object in JSON format
		public function toJsonR() {
			return json_encode(array(
				'id' => $this->id,
				'dispatcher' => json_decode($this->dispatcher->toJson()),
				'user' => json_decode($this->user->toJson()),
				'incidentType' => json_decode($this->incidentType->toJson()),
				'description' => $this->description,
				'date' => $this->date,
				'location' => json_decode($this->location->toJson()),
				'summon' => $this->summon,
				'status' => $this->status,
				'Assignments' => json_decode(Assignment::getAllJson($this->id))
			));
		}

		////represents the object in JSON format
		public function toJsonTypeInc(){
			return json_encode(array(
				'incidentType' => json_decode($this->incidentType->toJson()),
				'quantity' => $this->quantity
			));
		}
		//represents the object in JSON format
		public function toJsonMonthInc(){
			return json_encode(array(
				'month' => $this->month,
				'quantity' => $this->quantity
			));
		}
		//represents the object in JSON format
		public function toJsonLocation(){
			return json_encode(array(
				'Id' => $this->id,
				'Location' => json_decode($this->location->toJson())
			));
		}
		//represents the object in JSON format
		public function toJsonSummon(){
			return json_encode(array(
				'Summon' => $this->month,
				'Quantity' => $this->quantity
			));
		}
		//represents the object in JSON format
		public function toJsonActive(){
			return json_encode(array(
				'Quantity' => $this->quantity
			));
		}
		//represents the object in JSON format
		public function toShortJsonIncident(){
			return json_encode(array(
				'incident' => $this->id,
				'user' => json_decode($this->user->toJsonWatch()),
				'incidentType' => json_decode($this->incidentType->toJson()),
				'status' => $this->status,
				'date' => $this->date
			));
		}



		//instance methods

		//add
		public function add(){
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_addOneIncident(?, ?, ?, ?, ?, ?, ?, ?)';
			//command
			$command = $connection->prepare($query);
			//bind parameters
			$command->bind_param('sssssddd',
			$this->id = $this->createId(),
			$this->dispatcher->getAccount()->getUsername(),
			$this->user->getAccount()->getUsername(),
			$this->incidentType->getId(),
			$this->description,
			$this->location->getLatitude(),
			$this->location->getLongitude(),
			$this->summon);
			//execute
			$result = $command->execute();
			//close command
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
		public function editIncident() {
			//update
			$update = ' call usp_updateOneIncident(?, ?, ?, ?, ?, ?, ?, ?)';
			//connection
			$connection = MySqlConnection::getConnection();
			//command
			$command = $connection->prepare($update);
			//parameters
			$command->bind_param('ssssdddd',
			$this->id->getId(),
			$this->incidentType->getId(),
			$this->description,
			$this->date,
			$this->location->getLatitude(),
			$this->location->getLongitude(),
			$this->summon,
			$this->status
				 );
			//execute
			$edited = $command->execute();
			//close statement
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return result
			return $edited;
		}
		//class methods

		//get all
		public static function getAll() {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getIncidentsWithStatusActive();';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($id,
			$disUsername, $disFirstName, $disLastName, $disBirthdate, $disGender, $disPhotograph, $disPhone, $disEmail, $acoDisLastLogin,
			$usUsername, $usFirstName, $usLastName, $usBirthdate, $usGender, $usPhotograph, $usPhoneNumber, $usEmail, $acoUsLastLogin,
			$itId, $itDescription, $description, $dateTime, $latitude, $longitude, $summon, $status);
			//fetch data
			while($command->fetch()) {
				$id = $id;
				$contactDataDispacher = new ContactData($disPhone, $disEmail);
				$contacDataUser = new ContactData($usPhoneNumber, $usEmail);
				$accountDipacher = new Account($disUsername, $acoDisLastLogin);
				$accountUser = new Account($usUsername, $acoUsLastLogin);
				$dispatcher = new Dispatcher($accountDipacher, $disFirstName, $disLastName, $disBirthdate, $disGender, $disPhotograph, $contactDataDispacher);
				$user = new User($accountUser, $usFirstName, $usLastName, $usBirthdate, $usGender, $usPhotograph, $contacDataUser);
				$incidentType = new IncidentType($itId, $itDescription);
				$description = $description;
				$date = $dateTime;
				$location = new Location($latitude, $longitude);
				$summon = $summon;
				$status = $status;
				array_push($list, new Incident($id, $dispatcher, $user, $incidentType, $description, $dateTime, $location, $summon, $status));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		//get all in JSON format
		public static function getAllJson(){
			//list
			$list = array();
			//get all
			foreach (self::getAll() as $item) {
				array_push($list, json_decode($item->toJson()));
			}
			//return json encoded array
			return json_encode(array(
				'Incidents' => $list
				));
		}
		//get all Order By Date
		public static function getAllOrderDate() {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getAllIncidentsperTime();';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//binding result
			$command->bind_result($id, $idUser, $userFName, $userLName, $descriptionInc, $status, $incTypeId ,$incType, $dateIncident);
			//fetch data
			while($command->fetch()) {
				$id = $id;
				//User
				$idUser = $idUser;
				$firstName = $userFName;
				$lastName = $userLName;
				$description = $descriptionInc;
				$account = new Account($idUser);
				$user = new User($account, $firstName, $lastName);
				//incidentType
				$typeId = $incTypeId;
				$type = $incType;
				$incidentType = new IncidentType($typeId, $type);
				$status = $status;
				$date = $dateIncident;
				array_push($list, new Incident($id, $user, $incidentType, $description, $date, $status));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		//get all Order By Date in JSON format
		public static function getAllOrderDateJson(){
			//list
			$list = array();
			//get all
			foreach (self::getAllOrderDate() as $item) {
				array_push($list, json_decode($item->toShortJsonIncident()));
			}
			//return json encoded array
			return json_encode(array(
				'Incidents' => $list
				));
		}

		//get all Order By Type
		public static function getAllOrderType() {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getAllincidentsperIncidentType();';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//binding result
			$command->bind_result($id, $idUser, $userFName, $userLName, $descriptionInc, $status, $incTypeId ,$incType, $dateIncident);
			//fetch data
			while($command->fetch()) {
				$id = $id;
				//User
				$idUser = $idUser;
				$firstName = $userFName;
				$lastName = $userLName;
				$description = $descriptionInc;
				$account = new Account($idUser);
				$user = new User($account, $firstName, $lastName);
				//incidentType
				$typeId = $incTypeId;
				$type = $incType;
				$incidentType = new IncidentType($typeId, $type);
				$status = $status;
				$date = $dateIncident;
				array_push($list, new Incident($id, $user, $incidentType, $description, $date, $status));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		//get all Order By Type in JSON format
		public static function getAllOrderTypeJson(){
			//list
			$list = array();
			//get all
			foreach (self::getAllOrderType() as $item) {
				array_push($list, json_decode($item->toShortJsonIncident()));
			}
			//return json encoded array
			return json_encode(array(
				'Incidents' => $list
				));
		}

		//get all Order By Type
		public static function getAllOrderSummon() {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getAllincidentsperSummon();';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//binding result
			$command->bind_result($id, $idUser, $userFName, $userLName, $descriptionInc, $status, $incTypeId ,$incType, $dateIncident);
			//fetch data
			while($command->fetch()) {
				$id = $id;
				//User
				$idUser = $idUser;
				$firstName = $userFName;
				$lastName = $userLName;
				$description = $descriptionInc;
				$account = new Account($idUser);
				$user = new User($account, $firstName, $lastName);
				//incidentType
				$typeId = $incTypeId;
				$type = $incType;
				$incidentType = new IncidentType($typeId, $type);
				$status = $status;
				$date = $dateIncident;
				array_push($list, new Incident($id, $user, $incidentType, $description, $date, $status));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		//get all Order By Type in JSON format
		public static function getAllOrderSummonJson(){
			//list
			$list = array();
			//get all
			foreach (self::getAllOrderSummon() as $item) {
				array_push($list, json_decode($item->toShortJsonIncident()));
			}
			//return json encoded array
			return json_encode(array(
				'Incidents' => $list
				));
		}

		//get all Order By Type
		public static function getAllOrderBetween($date1, $date2) {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getAllIncidentsBetweenDates(?, ?);';
			//command
			$command = $connection->prepare($query);
			//bind parameters
			$command->bind_param('ss', $date1, $date2);
			//execute
			$command->execute();
			//binding result
			$command->bind_result($id, $idUser, $userFName, $userLName, $descriptionInc, $status, $incTypeId ,$incType, $dateIncident);
			//fetch data
			while($command->fetch()) {
				$id = $id;
				//User
				$idUser = $idUser;
				$firstName = $userFName;
				$lastName = $userLName;
				$description = $descriptionInc;
				$account = new Account($idUser);
				$user = new User($account, $firstName, $lastName);
				//incidentType
				$typeId = $incTypeId;
				$type = $incType;
				$incidentType = new IncidentType($typeId, $type);
				$status = $status;
				$date = $dateIncident;
				array_push($list, new Incident($id, $user, $incidentType, $description, $date, $status));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		//get all Between dates in JSON format
		public static function getAllOrderBetweenJson($date1, $date2){
			//list
			$list = array();
			//get all
			foreach (self::getAllOrderBetween($date1, $date2) as $item) {
				array_push($list, json_decode($item->toShortJsonIncident()));
			}
			//return json encoded array
			return json_encode(array(
				'Incidents' => $list
				));
		}

		//get all filter per summon
		public static function getIncidentsFilterperSummon($summon) {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getIncidentsFilterperSummon(?);';
			//command
			$command = $connection->prepare($query);
			//bind parameters
			$command->bind_param('s', $summon);
			//execute
			$command->execute();
			//binding result
			$command->bind_result($id, $idUser, $userFName, $userLName, $descriptionInc, $status, $incTypeId ,$incType, $dateIncident);
			//fetch data
			while($command->fetch()) {
				$id = $id;
				//User
				$idUser = $idUser;
				$firstName = $userFName;
				$lastName = $userLName;
				$description = $descriptionInc;
				$account = new Account($idUser);
				$user = new User($account, $firstName, $lastName);
				//incidentType
				$typeId = $incTypeId;
				$type = $incType;
				$incidentType = new IncidentType($typeId, $type);
				$status = $status;
				$date = $dateIncident;
				array_push($list, new Incident($id, $user, $incidentType, $description, $date, $status));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}
		//get all filter per summon in JSON format
		public static function getIncidentFilterperSummonJson($summon){
			//list
			$list = array();
			//get all
			foreach (self::getIncidentsFilterperSummon($summon) as $item) {
				array_push($list, json_decode($item->toShortJsonIncident()));
			}
			//return json encoded array
			return json_encode(array(
				'Incidents' => $list
				));
		}

		public static function getIncidentFilterperIncidentType($incidentType) {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query ='call usp_getIncidentsFilterperIType(?)';
			//commmand
			$command = $connection->prepare($query);
			//bind parameters
			$command->bind_param('s', $incidentType);
			//execute
			$command->execute();
			//binding result
			$command->bind_result($id, $idUser, $userFName, $userLName, $descriptionInc, $status, $incTypeId ,$incType, $dateIncident);
			//fetch data
			while($command->fetch()) {
				$id = $id;
				//User
				$idUser = $idUser;
				$firstName = $userFName;
				$lastName = $userLName;
				$description = $descriptionInc;
				$account = new Account($idUser);
				$user = new User($account, $firstName, $lastName);
				//incidentType
				$typeId = $incTypeId;
				$type = $incType;
				$incidentType = new IncidentType($typeId, $type);
				$status = $status;
				$date = $dateIncident;
				array_push($list, new Incident($id, $user, $incidentType, $description, $date, $status));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		public static function getIncidentFilterperIncidentTypeJson($incidentType) {
			//list
			$list = array();
			//get all
			foreach (self::getIncidentFilterperIncidentType($incidentType) as $item) {
				array_push($list, json_decode($item->toShortJsonIncident()));
			}
			//return json encoded array
			return json_encode(array(
				'Incidents' => $list
			));
		}

		//KPAIS//

		//Quantity of IncidentType
		public static function getCountTypeIncident(){
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_KPIIncidentsprType();';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($id, $name, $quantity);
			//fetch data
			while($command->fetch()) {
				$id = $id;
				$name = $name;
				$incidentType = new IncidentType($id, $name);
				$quantity = $quantity;
				array_push($list, new Incident($incidentType, $quantity));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		//Quantity of IncidentType in JSON format
		public static function getCountTypeIncidentJson(){
			//list
			$list = array();
			//get all
			foreach (self::getCountTypeIncident() as $item) {
				array_push($list, json_decode($item->toJsonTypeInc()));
			}
			//return json encoded array
			return json_encode(array(
				'Incidents' => $list
			));
		}

		//Quantity of incident for month
		public static function getCountMonth($year){
			//list
			$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'CALL usp_quantityMonth(?);';
			//command
			$command = $connection->prepare($query);
			//bind parameters
			$command->bind_param('s', $year);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($month, $quantity);
			$i = 0;
			//fetch data
			while($command->fetch()) {
				$m = $month;
				$q = $quantity;
				$found = false;
				while(!$found){
					if(strcmp ($months[$i] , $m ) == 0){
						array_push($list, new Incident($m, $q, null));
						$found = true;
					}else
						array_push($list, new Incident($months[$i], 0, null));
					$i++;
				}
			}
			if($i < 11){
				for($a = $i; $a <12; $a++){
					array_push($list, new Incident($months[$a], 0, null));
				}
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		//Quantity of IncidentType in JSON format
		public static function getCountMonthJson($year){
			//list
			$list = array();
			//get all
			foreach (self::getCountMonth($year) as $item) {
				array_push($list, json_decode($item->toJsonMonthInc()));
			}
			//return json encoded array
			return json_encode(array(
				'Incidents' => $list
			));
		}

		//Get All location of Incidents
		public static function getAllLocation(){
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getAllIncidentLocation();';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($id, $latitude, $longitude);
			//fetch data
			while($command->fetch()) {
				$id = $id;
				$location = new Location($latitude, $longitude);
				array_push($list, new Incident($id, $location, null, null, null));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		//Get All location of Incidents in JSON format
		public static function getAllLocationJson(){
			//list
			$list = array();
			//get all
			foreach (self::getAllLocation() as $item) {
				array_push($list, json_decode($item->toJsonLocation()));
			}
			//return json encoded array
			return json_encode(array(
				'Incidents' => $list
			));
		}

		//Quantity of Incidents for summon
		public static function getCountSummon(){
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'CALL usp_KPIQuantityperSummon();';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($button, $call);
			//fetch data
			while($command->fetch()) {
				$button = $button;
				$call = $call;
				array_push($list, new Incident($button, $call, null));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		//Quantity of Incidents for summon in JSON format
		public static function getCountSummonJson(){
			//list
			$list = array();
			//get all
			foreach (self::getCountSummon() as $item) {
				array_push($list, json_decode($item->toJsonSummon()));
			}
			//return json encoded array
			return json_encode(array(
				'Incidents' => $list
			));
		}

		//Quantity of Incidents Active
		public static function getCountActive(){
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getActiveIncidents();';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($quantity);
			//fetch data
			while($command->fetch()) {
				$quantity= $quantity;
				array_push($list, new Incident(null, $quantity));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		//Quantity of Incidents for summon in JSON format
		public static function getCountActiveJson(){
			//list
			$list = array();
			//get all
			foreach (self::getCountActive() as $item) {
				array_push($list, json_decode($item->toJsonActive()));
			}
			//return json encoded array
			return json_encode(array(
				'Incidents' => $list
			));
		}

	//Quantity of IncidentType
		public static function getIncidentsToday(){
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_KPIgetQuantityIncidentsToday();';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($quantity);
			//fetch data
			while($command->fetch()) {
				$quantity = $quantity;
				array_push($list, new Incident(null, $quantity));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		//Quantity of IncidentType in JSON format
		public static function getIncidentsTodayJson(){
			//list
			$list = array();
			//get all
			foreach (self::getIncidentsToday() as $item) {
				array_push($list, json_decode($item->toJsonActive()));
			}
			//return json encoded array
			return json_encode(array(
				'Incidents' => $list
			));
		}
		
	}
?>
