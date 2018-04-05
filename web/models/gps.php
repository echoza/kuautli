<?php
	//use files
	require_once('guard.php');
	require_once('location.php');
	//class
	class GPS {
		//attributes
		private $guard;
		private $location;
		//setter and getters
		public function getGuard(){ return $this->guard; }
		public function setGuard($value){ return $this->guard = $value; }
		public function getLocation(){ return $this->location; }
		public function setLocation($value){ return $this->location = $value; }
		
		//constructor
		public function __construct(){
			//empty object
			if(func_num_args() == 0){
				$this->guard = new Guard();
				$this->location = new Location();
			}
		}
		
		//add
		public function add(){
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_addGuardLocation(?, ?, ?)';
			//command
			$command = $connection->prepare($query);
			//bind parameters
			$command->bind_param('sdd',
			$this->guard->getAccount()->getUsername(),
			$this->location->getLatitude(),
			$this->location->getLongitude());
			//execute
			$result = $command->execute();
			//close command
			mysqli_stmt_close($command);
			//close connection
			$connection->close();
			//return result
			return $result;
		}

	}
?>