<?php
	//use files
	require_once('exceptions/recordnotfoundexception.php');
	require_once('mysqlconnection.php');
	require_once('time.php');
	class Shift {
		//attributes
		private $id;
		private $name;
		private $time;
		
		//Setter and Getters
		public function getId(){ return $this->id; }
		public function setId($value){ return $this->id = $value; }
		public function getName() { return $this->name; }
		public function setName($value) { return $this->name = $value; }
		public function getTime() { return $this->time; }
		public function setTime($value) { return $this->time = $value; }
		
		//constructor
		
		public function __construct() {
			//empty object 0
			if (func_num_args() == 0) {
					$this->id = '';
					$this->name = '';
					$this->time = new Time();
			}
			//object with data from database 
			if (func_num_args() == 1) {
				//get id
				$id = func_get_arg(0);
				//get connection
				$connection = MySqlConnection::getConnection();
				//query
				$query = 'call usp_getOneShift(?);';
				//command
				$command = $connection->prepare($query);
				//bind parameters
				$command->bind_param('s', $id);
				//execute
				$command->execute();
				//bind results
				$command->bind_result($id, $name, $start, $end);
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
					$this->time = new Time($start, $end);
				}
				else {		
					//throw exception if record not found
					throw new RecordNotFoundException();
				}
			}
			//object with data from arguments
			if (func_num_args() == 3) {
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
				$this->id = $arguments[0];
				$this->name = $arguments[1];
				$this->time = $arguments[2];
			}
		}
		
		//represents the object in JSON format
		public function toJson() {
			return json_encode(array(
				'id' => $this->id,
				'name' => $this->name,
				'time' => json_decode($this->time->toJson())
			));
		}
	}
?>