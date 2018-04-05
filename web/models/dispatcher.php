<?php
	//use files
	require_once('mysqlconnection.php');
	require_once('exceptions/recordnotfoundexception.php');
	require_once('exceptions/invaliduserexception.php');
	require_once('person.php');
	
	class Dispatcher extends person  {
		//attributes
		private $id;
		private $photo;
		
		//Setters and getters 
		public function getId(){ return $this->id; }
		public function setId($value){ return $this->id = $value; }
		public function getPhoto(){ return $this->photo; }
		public function setPhoto($value){ return $this->photo = $value; }
		
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
			}
			//object with data from database
			if (func_num_args() == 2) {
				//get arguments
				$arguments = func_get_args();
				$user = $arguments[0];
				$password = $arguments[1];
				//get connection
				$connection = MySqlConnection::getConnection();
				//query
				$query = 'call usp_loginDispatcher(?, ?);';
				//command
				$command = $connection->prepare($query);
				//bind parameters
				$command->bind_param('ss', $user, $password);
				//execute
				$command->execute();
				//bind results
				$command->bind_result($username, $lastLogin, $firstName, $lastName, $birthdate, $gender, $photo, $phone, $email);
				//fetch data
				$found = $command->fetch();
				//close command
				mysqli_stmt_close($command);
				//close connection
				$connection->close();
				//throw exception if record not found
				if ($found) {
					$this->account = new Account($username, $lastLogin);
					$this->firstName = $firstName;
					$this->lastName = $lastName;
					$this->birthdate = $birthdate;
					$this->gender = $gender;
					$this->photo = $photo;
					$this->contactData = new ContactData($phone, $email);
				}
				else {		
					//throw exception if record not found
					throw new InvalidUserException($user);
				}
			}				
			//empty with data from database 
			if(func_num_args() == 1) {
				//get id
				$id = func_get_arg(0);
				//get connection
				$connection = MySqlConnection::getConnection();
				//query
				$query = 'CALL usp_getOneDispatcherEmpty(?)';
				//command
				$command = $connection->prepare($query);
				//bind parameters
				$command->bind_param('s', $id);
				//execute
				$command->execute();
				//bind results
				$command->bind_result($username, $lastLogin, $firstName, $lastName, $birthdate, $gender, $photo, $phone, $email);
				//fetch data
				$found = $command->fetch();
				//close command
				mysqli_stmt_close($command);
				//close connection
				$connection->close();
				//pass values to the attributes
				if ($found) {			
					$this->account = new Account($username, $lastLogin);
					$this->firstName = $firstName;
					$this->lastName = $lastName;
					$this->birthdate = $birthdate;
					$this->gender = $gender;
					$this->photo = $photo;
					$this->contactData = new ContactData($phone, $email);
				}
				else {		
					//throw exception if record not found
					throw new RecordNotFoundException();
				}
			}
			//object with data from arguments
			if (func_num_args() == 7) {
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
				'contactData' => json_decode($this->contactData->toJson())
			));
		}
		
		//class methods
		
		//get all
		public static function getAll() {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getAllDispatchers()';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($username, $lastLogin, $firstName, $lastName, $birthdate, $gender, $photo, $phone, $email);
			//fetch data
			while ($command->fetch()) {
				$contactData = new ContactData($phone, $email);
				$account = new Account($username, $lastLogin);
				array_push($list, new Dispatcher($account, $firstName, $lastName, $birthdate, $gender, $photo, $contactData));
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
				'Dispatchers' => $list
			));
		}
	}
?>