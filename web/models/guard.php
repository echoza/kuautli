<?php
	//use files
	require_once('mysqlconnection.php');
	require_once('exceptions/recordnotfoundexception.php');
	require_once('exceptions/invaliduserexception.php');
	require_once('person.php');
	require_once('shift.php');
	require_once('time.php');
	require_once('round.php');

	class Guard extends Person{
		//attributes
		private $photo;
		private $shift;
		private $round;
		private $status;
		private $location;
		private $quantity;
		//Setter and Getters
		public function getPhoto(){ return $this->photo; }
		public function setPhoto($value){ return $this->photo = $value; }
		public function getShift(){ return $this->shift; }
		public function setShift($value){ return $this->shift = $value; }
		public function getRound(){ return $this->round; }
		public function serRound($value){ return $this->round = $value; }
		public function getStatus(){ return $this->status; }
		public function setStatus($value){ return $this->status = $value; }
		public function getLocation() { return $this->location; }
		public function setLocation($value) { $this->location = $value; }
		public function getQuality(){ return $this->quantity; }
		public function setQuality($value) {return $this->quantity = $value; }
		//constructors

		public function __construct() {
			//empty object
			if(func_num_args() == 0) {
				$this->firstName = '';
				$this->lastName = '';
				$this->birthdate = '';
				$this->gender = '';
				$this->photo = '';
				$this->contactData = new ContactData();
				$this->account = new Account();
				$this->shift = new Shift();
				$this->round = new Round();
			}
			//object with data from database
			if (func_num_args() == 1){
				//get id
				$id = func_get_arg(0);
				//get connection
				$connection = MySqlConnection::getConnection();
				//query
				$query = 'call usp_getOneGuard(?);';
				//command
				$command = $connection->prepare($query);
				//bind parameters
				$command->bind_param('s', $id);
				//execute
				$command->execute();
				//bind results
				$command->bind_result($username, $lastLogin, $firstName, $lastName, $birthdate, $gender, $photo, $phone, $email, $status, $idShift, $name, $start, $end);
				//fetch data
				$found = $command->fetch();
				//close command
				mysqli_stmt_close($command);
				//close connection
				$connection->close();
				//pass values to the attributes
				if ($found) {
					$this->firstName = $firstName;
					$this->lastName = $lastName;
					$this->birthdate = $birthdate;
					$this->gender = $gender;
					$this->photo = $photo;
					$this->contactData = new ContactData($phone, $email);
					$this->account = new Account($username, $lastLogin);
					$this->status = $status;
					$time = new Time($start, $end);
					$this->shift = new Shift($idShift, $name, $time);
				}
				else {
					//throw exception if record not found
					throw new RecordNotFoundException();
				}
			}
			//object with data from database
			if (func_num_args() == 2){
				//get arguments
				$arguments = func_get_args();
				$user = $arguments[0];
				$password = $arguments[1];
				//get connection
				$connection = MySqlConnection::getConnection();
				//query
				$query = 'call usp_loginGuard(?, ?);';
				//command
				$command = $connection->prepare($query);
				//bind parameters
				$command->bind_param('ss', $user, $password);
				//execute
				$command->execute();
				//bind results
				$command->bind_result($username, $lastLogin, $firstName, $lastName, $birthdate, $gender, $photo, $phone, $email, $status, $idShift, $name, $start, $end);
				//fetch data
				$found = $command->fetch();
				//close command
				mysqli_stmt_close($command);
				//close connection
				$connection->close();
				//pass values to the attributes
				if ($found) {
					$this->firstName = $firstName;
					$this->lastName = $lastName;
					$this->birthdate = $birthdate;
					$this->gender = $gender;
					$this->photo = $photo;
					$this->contactData = new ContactData($phone, $email);
					$this->account = new Account($username, $lastLogin);
					$this->status = $status;
					$time = new Time($start, $end);
					$this->shift = new Shift($idShift, $name, $time);
				}
				else {
					//throw exception if record not found
					throw new InvalidUserException($user);
				}
			}		
			//object with data from arguments
			if (func_num_args() == 9) {
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
				$this->account = $arguments[0];
				$this->firstName = $arguments[1];
				$this->lastName = $arguments[2];
				$this->birthdate = $arguments[3];
				$this->gender = $arguments[4];
				$this->photo = $arguments[5];
				$this->contactData = $arguments[6];
				$this->status =$arguments[7];
				$this->shift = $arguments[8];
			}
			//object with data from arguments
			if(func_num_args() == 5) {
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
				$this->shift = $arguments[0];
				$this->quantity = $arguments[1];
			}
			//object with data from arguments
			if (func_num_args() == 4) {
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
				$this->account = $arguments[0];
				$this->location = $arguments[1];
			}
			//object with data from arguments
			if(func_num_args() == 3){
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
				$this->status = $arguments[0];
			}
		}

		//represents the object in JSON format
		public function toJson() {
			return json_encode(array(
				'account' => json_decode($this->account->toJson()),
				'firstName' => $this->firstName,
				'lastName' => $this->lastName,
				'birthdate' => $this->birthdate,
				'gender' => $this->gender,
				'age' => $this->age(),
				'photo' => $this->photo,
				'contactData' => json_decode($this->contactData->toJson()),
				'status' => $this->status,
				'shift' => json_decode($this->shift->toJson()),
				'rounds' => json_decode(Round::getAllJson($this->account->getUsername()))
			));
		}
		//represents the object in JSON format
		public function toJsonActive() {
			return json_encode(array(
				'Quantity' => $this->status
			));
		}
		//represents the object in JSON format
		public function QualityToJson() {
			return json_encode(array(
				'shift' => $this->shift,
				'quantity' =>$this->quantity
			));
		}
		//represents the object in JSON format
		public function LocationToJson() {
			return json_encode(array(
				'Account' => json_decode($this->account->toJsonL()),
				'Location' => json_decode($this->location->toJson())
			));
		}
		//instance methods

		//add
		public function add(){
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'CALL usp_addGuard(?, ?, ?, ?, ?, ?, ?, ?, ?)';
			//command
			$command = $connection->prepare($query);
			//bind parameters
			$command->bind_param('ssssdssss',
			$this->account->getUsername(),
			$this->firstName,
			$this->lastName,
			$this->birthdate,
			$this->gender,
			$this->photo,
			$this->shift->getId(),
			$this->contactData->getPhone(),
			$this->contactData->getEmail());
			//execute
			$result = $command->execute();
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return result
			return $result;
		}
		//create photograph
		public static function createIdPhoto($user){
			$id = "guard-".$user.".jpg";
			return $id;
		}
		public static function createEmail($user){
			$email = $user."@security.com.mx";
			return $email;
			
		}
		//class methods

		//get all
		public static function getAll() {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getAllGuardsperLastLogin();';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($username, $lastLogin, $firstName, $lastName, $birthdate, $gender, $photo, $phone, $email, $status, $idShift, $name, $start, $end);
			//fetch data
			while ($command->fetch()) {
					$contactData = new ContactData($phone, $email);
					$account = new Account($username, $lastLogin);
                    $time = new Time($start, $end);
					$shift = new Shift($idShift, $name, $time);
				array_push($list, new Guard($account, $firstName, $lastName, $birthdate, $gender, $photo, $contactData, $status, $shift));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		//get all in JSON format
		public static function getAllJson() {
			//list
			$list = array();
			//get all
			foreach(self::getAll() as $item) {
				array_push($list, json_decode($item->toJson()));
			}
			//return json encoded array
			return json_encode(array(
				'guards' => $list
			));
		}

		//get all for status
		public static function getAllStatus() {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getAllStatus();';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($username, $lastLogin, $firstName, $lastName, $birthdate, $gender, $photo, $phone, $email, $status, $idShift, $name, $start, $end);
			//fetch data
			while ($command->fetch()) {
					$contactData = new ContactData($phone, $email);
					$account = new Account($username, $lastLogin);
                    $time = new Time($start, $end);
					$shift = new Shift($idShift, $name, $time);
				array_push($list, new Guard($account, $firstName, $lastName, $birthdate, $gender, $photo, $contactData, $status, $shift));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}


		//get all for status in JSON format
		public static function getAllStatusJson() {
			//list
			$list = array();
			//get all
			foreach(self::getAllStatus() as $item) {
				array_push($list, json_decode($item->toJson()));
			}
			//return json encoded array
			return json_encode(array(
				'guards' => $list
			));
		}

		//get all order by shift  for status
		public static function getAllOrderShift() {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getAllGuardsPerShift();';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($username, $lastLogin, $firstName, $lastName, $birthdate, $gender, $photo, $phone, $email, $status, $idShift, $name, $start, $end);
			//fetch data
			while ($command->fetch()) {
					$contactData = new ContactData($phone, $email);
					$account = new Account($username, $lastLogin);
                    $time = new Time($start, $end);
					$shift = new Shift($idShift, $name, $time);
				array_push($list, new Guard($account, $firstName, $lastName, $birthdate, $gender, $photo, $contactData, $status, $shift));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		//get all  order by shift in JSON format
		public static function getAllOrderShiftJson() {
			//list
			$list = array();
			//get all
			foreach(self::getAllOrderShift() as $item) {
				array_push($list, json_decode($item->toJson()));
			}
			//return json encoded array
			return json_encode(array(
				'guards' => $list
			));
		}

		//get all order by status
		public static function getAllOrderStatus() {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getAllGuardsperStatus()';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($username, $lastLogin, $firstName, $lastName, $birthdate, $gender, $photo, $phone, $email, $status, $idShift, $name, $start, $end);
			//fetch data
			while ($command->fetch()) {
					$contactData = new ContactData($phone, $email);
					$account = new Account($username, $lastLogin);
                    $time = new Time($start, $end);
					$shift = new Shift($idShift, $name, $time);
				array_push($list, new Guard($account, $firstName, $lastName, $birthdate, $gender, $photo, $contactData, $status, $shift));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		//get all  order by status in JSON format
		public static function getAllOrderStatusJson() {
			//list
			$list = array();
			//get all
			foreach(self::getAllOrderStatus() as $item) {
				array_push($list, json_decode($item->toJson()));
			}
			//return json encoded array
			return json_encode(array(
				'guards' => $list
			));
		}

		//get all order by status
		public static function getAllOrderFirstName() {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getAllGuardsperFirstName()';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($username, $lastLogin, $firstName, $lastName, $birthdate, $gender, $photo, $phone, $email, $status, $idShift, $name, $start, $end);
			//fetch data
			while ($command->fetch()) {
					$contactData = new ContactData($phone, $email);
					$account = new Account($username, $lastLogin);
                    $time = new Time($start, $end);
					$shift = new Shift($idShift, $name, $time);
				array_push($list, new Guard($account, $firstName, $lastName, $birthdate, $gender, $photo, $contactData, $status, $shift));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		//get all  order by status in JSON format
		public static function getAllOrderFirstNameJson() {
			//list
			$list = array();
			//get all
			foreach(self::getAllOrderFirstName() as $item) {
				array_push($list, json_decode($item->toJson()));
			}
			//return json encoded array
			return json_encode(array(
				'guards' => $list
			));
		}

		//get all guards for shift with where
		public static function getAllWhereShift($shift) {
			//list
			$list = array();
			//connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getAllGuardsFilterperShift(?);';
			//command
			$command = $connection->prepare($query);
			//bind parameters
			$command->bind_param('s', $shift);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($username, $lastLogin, $firstName, $lastName, $birthdate, $gender, $photo, $phone, $email, $status, $idShift, $name, $start, $end);
			//fetch data
			while($command -> fetch()) {
				$contactData = new ContactData($phone, $email);
					$account = new Account($username, $lastLogin);
                    $time = new Time($start, $end);
					$shift = new Shift($idShift, $name, $time);
				array_push($list, new Guard($account, $firstName, $lastName, $birthdate, $gender, $photo,$contactData, $status, $shift));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		//get ll guards for shift with where in JSON format
		public static function getAllWhereShiftJson($shift) {
			//list
			$list = array();
			//get all
			foreach(self::getAllWhereShift($shift) as $item) {
				array_push($list, json_decode($item->toJson()));
			}
			//return json encoded array
			return json_encode(array (
				'Guard' => $list
			));
		}

		//quantity of Guards Active
		public static function getCountActiveGuards() {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_KPIGuardsInService();';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($quantity);
			//fetch data
			while($command->fetch()) {
				$quantity = $quantity;
				array_push($list, new Guard($quantity, null, null));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		public static function getCountActiveGuardsJson() {
			//list
			$list = array();
			//get all
			foreach(self::getCountActiveGuards() as $item) {
				array_push($list, json_decode($item->toJsonActive()));
			}
			//return json encoded array
			return json_encode(array(
				'Guards active' => $list,
			));
		}

		//quantity guards for shift
		public static function getGuardsQuantity() {
			//list
			$list = array();
			//connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_KPITotalGuardsperShift();';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//bind parameters
			$command->bind_result($shift, $quantity);
			//fetch data
			while($command->fetch()) {
				$shift = $shift;
				$quantity = $quantity;
				array_push($list, new Guard($shift, $quantity, null, null, null));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		public function getGuardsQuantityToJson() {
			//list
			$list = array();
			//get all
			foreach(self::getGuardsQuantity() as $item) {
				array_push($list, json_decode($item->QualityToJson()));
			}
			//return json encoded array
			return json_encode(array (
				'Guard' => $list
			));
		}

		//quantity guards for shift
		public static function getGuardsLocation() {
			//list
			$list = array();
			//connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'CALL usp_KPILastGuardLocation();';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//bind parameters
			$command->bind_result($username, $latitude, $longitude);
			//fetch data
			while($command->fetch()) {
				$account = new Account($username);
				$location = new Location($latitude, $longitude);
				array_push($list, new Guard($account, $location, null, null));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		public function getGuardsLocationToJson() {
			//list
			$list = array();
			//get all
			foreach(self::getGuardsLocation() as $item) {
				array_push($list, json_decode($item->LocationToJson()));
			}
			//return json encoded array
			return json_encode(array (
				'Guards' => $list
			));
		}
	}
?>
