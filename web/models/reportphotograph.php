<?php
	//class
	class ReportPhotograph {
		//attributes
		private $id;
		private $photo;
		
		public function getId(){ return $this->id; }
		public function setId($value){ return $this->id = $value; }
		public function getPhoto(){ return $this->photo; }
		public function setPhoto(){ return $this->photo = $value; }
		
		//constructor
		public function __construct(){
			//empty object 
			if(func_num_args() == 0){
				$this->id = 0;
				$this->photo = '';
			}
			//object with data from arguments
			if(func_num_args() == 2){
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes 
				$this->id = $arguments[0];
				$this->photo = $arguments[1];
			}
		}
		
		//represents the object in JSON format
		public function toJson() {
			return json_encode(array(
				'id' => $this->id,		
				'photograph' => $this->photo
			));
		}
		
				//get all
		public static function getAll($id) {
			//list
			$list = array();
			//get connection
			$connection = MySqlConnection::getConnection();
			//query
			$query = 'call usp_getAllPhotosperReport(?);';
			//command
			$command = $connection->prepare($query);
			//bind parameters
			$command->bind_param('s', $id);
			//execute
			$command->execute();
			//bind results
			$command->bind_result($id, $photograph);
			//fetch data
			while ($command->fetch()) {				
				array_push($list, new ReportPhotograph($id, $photograph));
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
				'Photograph' => $list
			));
		}
		
		
	}
?>