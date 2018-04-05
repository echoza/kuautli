<?php
	//use files
	require_once('location.php');
	
	//class
	class Stop{
		//attributes
		private $id;
		private $name;
		private $location;
		
		//setters and getters 
		public function getId(){ return $this->id; }
		public function setId($value){ return $this->id = $value; }
		public function getName(){ return $this->name; }
		public function setName($value){ return $this->name = $value; }
		public function getLocation(){ return $this->location; }
		public function setLocation($value){ return $this->location = $value; }
		
		//constructor
		public function __construct(){
			//empty object
			if(func_num_args() == 0){
				$this->id = '';
				$this->name = '';
				$this->location = new Location();
			}
			//object with data from arguments
			if(func_num_args() == 3){
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
				$this->id = $arguments[0];
				$this->name = $arguments[1];
				$this->location = $arguments[2];
			}
		}
		
		//instance methods
		
		//represents the object in JSON format
		public function toJson() {
			return json_encode(array(
					'id' => $this->id,
					'name' => $this->name,
					'location' => json_decode($this->location->toJson())
				));
		}
		
		//class methods
		
		//get all
		public static function getAll($idRound) {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getOneRound(?)';
			//command
			$command = $connection->prepare($query);
			//bind parameters
			$command->bind_param('s', $idRound);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($id, $name, $latitude, $longitude);
			//fetch data
			while ($command->fetch()) {
				$id = $id;
				$name = $name;
				$location = new Location($latitude, $longitude);
				array_push($list, new Stop($id, $name, $location));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}	
		
		//get all in JSON format
		public static function getAllJson($idRound){
			//list
			$list = array();
			//get all
			foreach(self::getAll($idRound) as $item) {
				array_push($list, json_decode($item->toJson()));
			}
			//return json encoded array
			return json_encode(array(
				'stop' => $list
			));
		}		
	}
?>