<?php
	//use files
	require_once('exceptions/recordnotfoundexception.php');
	require_once('mysqlconnection.php');
	require_once('dispatcher.php');
	require_once('guard.php');
	//class
	class Message{
		//attributes
		private $id;
		private $sender;
		private $message;
		private $date;
		private $addressee;
		private $guard;
		private $dispatcher;
		
		//setters and getters
		public function getId(){ return $this->id; }
		public function setId($value){ return $this->id = $value; }
		public function getSender(){ return $this->sender; }
		public function setSender($value){ return $this->sender = $value; }
		public function getMessage(){ return $this->message; }
		public function setMessage($value){ return $this->message = $value; }
		public function getDate(){ return $this->date; }
		public function setDate($value){ return $this->date; }
		public function getAddressee(){ return $this->addressee; }
		public function setAddressee($value){ return $this->addressee = $value; }
		public function getGuard(){ return $this->guard; }
		public function setGuard($value){ return $this->guard = $value; }
		public function getDispatcher(){ return $this->dispatcher; }
		public function setDispatcher($value){ return $this->dispatcher = $value; }
		//constructors
		public function __construct(){
			//empty object 0
			if (func_num_args() == 0) {
					$this->id = '';
					$this->addressee = '';
					$this->sender = '';
					$this->message = '';
					$this->date = '';
					$this->guard = new Guard();
					$this->dispatcher = new Dispatcher();
			}
			//object with data from arguments
			if (func_num_args() == 4) {
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
				$this->id = $arguments[0];
				$this->message = $arguments[1];
				$this->date = $arguments[2];
				$this->sender = $arguments[3];
			}
			//object with data from arguments
			if(func_num_args() == 5) {
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
				$this->id = $arguments[0];
				$this->message = $arguments[1];
				$this->date = $arguments[2];
				$this->sender = $arguments[3];
				$this->addressee = $arguments[4];
			}
		}
		
		//represents the object in JSON format
		public function toJson() {
			return json_encode(array(
				'id' => $this->id,
				'date' => $this->date,
				'message' => $this->message,
				'sender' => $this->sender
			));
		}
		//represents the object in JSON format
		public function toJsonLastMessages() {
			return json_encode(array(
				'id' => $this->id,
				'date' => $this->date,
				'message' => $this->message,
				'sender' => $this->sender,
				'addressee' => $this->addressee
			));
		}
		//instance methods
		//add
		public function add(){
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_addMessage(?, ?, ?, ?);';
			//command
			$command = $connection->prepare($query);
			//bind parameters
			$command->bind_param('sssd',
			$this->guard->getAccount()->getUsername(),
			$this->dispatcher->getAccount()->getUsername(),
			$this->message,
			$this->sender);
			//execute
			$result = $command->execute();
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return result
			return $result;
		}
		
		//class methods
		
		//get last Message in one conversation
		public static function getLastMessageInOneConversation($guardId, $dispatcherId) {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getLastMessageintoOneConversation(?,?)';
			//command
			$command = $connection->prepare($query);
			//bind parameters
			$command->bind_param('ss', $guardId, $dispatcherId);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($id, $message, $dateTime, $sender);
			//fetch data
			while ($command->fetch()) {
				$id = $id;
				$message = $message;
				$dateTime = $dateTime;
				$sender = $sender;
				array_push($list, new Message($id, $message, $dateTime, $sender));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		//get last Message in one conversation in JSON format
		public static function getLastMessageConversationJson($guardId, $dispatcherId) {
			//list
			$list = array();
			//get all
			foreach(self::getLastMessageInOneConversation($guardId, $dispatcherId) as $item) {
				array_push($list, json_decode($item->toJson()));
			}
			//return json encoded array
			return json_encode(array(
				'messages' => $list
			));
		}
		
		
		//get all
		public static function getAll($guardId, $dispatcherId) {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getAllMessagesintoOneConversation(?, ?);';
			//command
			$command = $connection->prepare($query);
			//bind parameters
			$command->bind_param('ss', $guardId, $dispatcherId);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($id, $message, $dateTime, $sender);
			//fetch data
			while ($command->fetch()) {
				$id = $id;
				$message = $message;
				$dateTime = $dateTime;
				$sender = $sender;
				array_push($list, new Message($id, $message, $dateTime, $sender));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}

		//get all in JSON format
		public static function getAllJson($guardId, $dispatcherId) {
			//list
			$list = array();
			//get all
			foreach(self::getAll($guardId, $dispatcherId) as $item) {
				array_push($list, json_decode($item->toJson()));
			}
			//return json encoded array
			return json_encode(array(
				'messages' => $list
			));
		}
		
		//get all the last messages guard
		public static function getAllLastMessagesGuard($guardId) {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getLastMessagesperGuard(?);';
			//command
			$command = $connection->prepare($query);
			//bind parameters
			$command->bind_param('s', $guardId );
			//execute
			$command->execute();
			//bind results
			$command->bind_result($id, $message, $dateTime, $sender, $addressee);
			//fetch data
			while ($command->fetch()) {
				$id = $id;
				$message = $message;
				$dateTime = $dateTime;
				$sender = $sender;
				$addressee = $addressee; 
				array_push($list, new Message($id, $message, $dateTime, $sender, $addressee));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}
		
		//get all in JSON format
		public static function getAllLastMessagesGuardJson($guardId) {
			//list
			$list = array();
			//get all
			foreach(self::getAllLastMessagesGuard($guardId) as $item) {
				array_push($list, json_decode($item->toJsonLastMessages()));
			}
			//return json encoded array
			return json_encode(array(
				'messages' => $list
			));
		}
	
		//get all the last messages dispatcher
		public static function getAllLastMessagesDispatcher($dispatcherId) {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getLastMessagesperDispatcher(?);';
			//command
			$command = $connection->prepare($query);
			//bind parameters
			$command->bind_param('s', $dispatcherId);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($id, $message, $dateTime, $sender, $addressee);
			//fetch data
			while ($command->fetch()) {
				$id = $id;
				$message = $message;
				$dateTime = $dateTime;
				$sender = $sender;
				$addressee = $addressee; 
				array_push($list, new Message($id, $message, $dateTime, $sender, $addressee));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}
		
		//get all in JSON format
		public static function getAllLastMessagesDispatcherJson($dispatcherId) {
			//list
			$list = array();
			//get all
			foreach(self::getAllLastMessagesDispatcher($dispatcherId) as $item) {
				array_push($list, json_decode($item->toJsonLastMessages()));
			}
			//return json encoded array
			return json_encode(array(
				'messages' => $list
			));
		}
	
	}
?>