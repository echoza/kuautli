<?php
	//use files
	require_once('stop.php');
	require_once('location.php');
	
	//class
	class Route{
		//attributes
		private $stop;
		private $location;
		
		//setters and getters 
		public function getLocation(){ return $this->location; }
		public function setLocation($value){ return $this->location = $value; }
		public function getStop(){ return $this->stop; }
		public function setStop($value){ return $this->stop = $value; }
		
		//constructor
		public function __construct(){
			//empty object
			if(func_num_args() == 0){
				$this->startEndLatitude = 0;
				$this->startEndLongitude = 0;
				$this->stop = new Stop();
			}
			//object with data from arguments
			if(func_num_args() == 1){
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
				$this->location = $arguments[0];
			}
		}
		
		//instance methods
		
		//represents the object in JSON format
		public function toJson($idRound) {
				return json_encode(array(
					'StartEndPoint' => json_decode($this->location->toJson()),	
					'Spots' => json_decode(Stop::getAllJson($idRound)) 			
				));
		}
	}
?>