<?php
	//use files
	require_once('mysqlconnection.php');
	//class
	class IncidentType{
		//attributes
		private $id;
		private $description;
		private $quantity;

		//setters and getters
		public function getId(){ return $this->id; }
		public function setId($value){ return $this->id = $value; }
		public function getDescription(){ return $this->description; }
		public function setDescription($value){ return $this->description = $value; }
		public function getQuantity(){ return $this->quantity; }
		public function setQuantity($value){ return $this->quantity = $value; }
		//constructors
		public function __construct() {
			//empty object
			if (func_num_args() == 0){
				$this->id = '';
				$this->description = '';
			}
			//object with data from database
			if (func_num_args() == 1){
				//get id
				$id = func_get_arg(0);
				//get connection
				$connection = MySqlConnection::getConnection();
				//query
				$query = 'call usp_getOneIncidentType(?);';
				//command
				$command = $connection->prepare($query);
				//bind parameters
				$command->bind_param('s', $id);
				//execute
				$command->execute();
				//bind results
				$command->bind_result($id, $description);
				//fetch data
				$found = $command->fetch();
				//close command
				mysqli_stmt_close($command);
				//close connection
				$connection->close();
				//pass values to the attributes
				if ($found) {
					$this->id = $id;
					$this->description = $description;
				}
				else {
					//throw exception if record not found
					throw new RecordNotFoundException();
				}
			}
			//object with data from arguments
			if (func_num_args() == 2) {
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
				$this->id = $arguments[0];
				$this->description = $arguments[1];
			}

		}

		//represents the object in JSON format
		public function toJson() {
			return json_encode(array(
				'id' =>  $this->id,
				'description' => $this->description
			));
		}

		public function toJsonInc() {
			return json_encode(array(
				'description' => $this->description
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
			$query = 'select id, description from incidenttype';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($id, $description);
			//fetch data
			while ($command->fetch()) {
				$id = $id;
				$description = $description;
				array_push($list, new IncidentType($id, $description));
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
				'IncidentType' => $list
			));
		}
	}
?>
