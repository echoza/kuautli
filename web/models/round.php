<?php
	//use files
	require_once('route.php');
	require_once('mysqlconnection.php');
	require_once('exceptions/recordnotfoundexception.php');
	//class
	class Round{
		//attributes
		private $id;
		private $name;
		private $day;
		private $route;
		
		//setters and getters 
		public function getId(){ return $this->id; }
		public function setId($value){ return $this->id = $value; }
		public function getName(){ return $this->name; }
		public function setName($value){ return $this->name = $value; }
		public function getDay(){ return $this->day; }
		public function setDay($value){ return $this->day = $value; }
		public function getRoute(){ return $this->route; }
		public function serRoute($value){ return $this->route = $value;}
		
		//constructors 
		public function __construct(){
			//empty object
			if(func_num_args() == 0){
				$this->id = '';
				$this->name = '';
				$this->day = '';
				$this->route = new Route();
			}
			//empty object
			if(func_num_args() == 1){
				//get id
				$id = func_get_arg(0);
				//get connection
				$connection = MySqlConnection::getConnection();
				//query
				$query = 'SELECT id, name, startEndLatitude, startEndLongitude FROM rounds where id = ?';
				//command
				$command = $connection->prepare($query);
				//bind parameters
				$command->bind_param('s', $id);
				//execute
				$command->execute();
				//bind results
				$command->bind_result($id, $name, $startEndLatitude, $startEndLongitude);
				//fetch data
				$found = $command->fetch();
				//close command
				mysqli_stmt_close($command);
				//close connection
				$connection->close();
				//pass values to the attributes
				if ($found) {
					$this->id = $id;
					$this->name = $name;
					$location = new Location($startEndLatitude, $startEndLongitude);
					$this->route = new Route($location);
				}
				else {
					//throw exception if record not found
					throw new RecordNotFoundException();
				}
			}
			//object with data from arguments
			if(func_num_args() == 4){
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
				$this->id = $arguments[0];
				$this->name = $arguments[1];
				$this->day = $arguments[2];
				$this->route = $arguments[3];
			}
			if(func_num_args() == 3){
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
				$this->id = $arguments[0];
				$this->name = $arguments[1];
				$this->route = $arguments[2];
			}
			
		}
		
		//instance methods
		
		//represents the object in JSON format
		public function toJson() {
			return json_encode(array(
					'id' => $this->id,
					'name' => $this->name,
					'day' => $this->day,
					'route' => json_decode($this->route->toJson($this->id))
				));
		}
		
		//represents the object in JSON format
		public function toJsonAllRound() {
			return json_encode(array(
					'id' => $this->id,
					'name' => $this->name,
					'route' => json_decode($this->route->toJson($this->id))
				));
		}
		
		//class methods
		
		//get all
		public static function getAll($username) {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getRoundsForOneGuard(?);';
			//command
			$command = $connection->prepare($query);
			//bind parameters
			$command->bind_param('s', $username);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($id, $name, $day, $startEndLatitude, $startEndLongitude);
			//fetch data
			while ($command->fetch()) {
				$id = $id;
				$name = $name;	
				$day = $day;
				$location = new Location($startEndLatitude, $startEndLongitude);
				$route = new Route($location);
				array_push($list, new Round($id, $name, $day, $route));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}	
		
		//get all in JSON format
		public static function getAllJson($username){
			//list
			$list = array();
			//get all
			foreach(self::getAll($username) as $item) {
				array_push($list, json_decode($item->toJson()));
			}
			//return json encoded array
			return json_encode(array(
				'round' => $list
			));
		}		
		
		//get all
		public static function getAllRound() {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'SELECT id, name, startEndLatitude, startEndLongitude FROM rounds';
			//command
			$command = $connection->prepare($query);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($id, $name, $startEndLatitude, $startEndLongitude);
			//fetch data
			while ($command->fetch()) {
				$id = $id;
				$name = $name;	
				$location = new Location($startEndLatitude, $startEndLongitude);
				$route = new Route($location);
				array_push($list, new Round($id, $name, $route));
			}
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return list
			return $list;
		}	
		
		//get all in JSON format
		public static function getAllRoundJson(){
			//list
			$list = array();
			//get all
			foreach(self::getAllRound() as $item) {
				array_push($list, json_decode($item->toJsonAllRound()));
			}
			//return json encoded array
			return json_encode(array(
				'round' => $list
			));
		}		
	}
?>