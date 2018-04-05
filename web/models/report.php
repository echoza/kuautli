<?php
	//use files
	require_once('reportphotograph.php');
	//Class
	Class Report {
		//Attributes
		private $id;
		private $date;
		private $falseAlarm;
		private $description;
		private $photograph;
		
		//setters and getters
		public function getId() { return $this->id; }
		public function setId($value) { $this->id = $value; }
		public function getDate() { return $this->date; }
		public function setDate($value) { return $this->date = $value; }
		public function getFalseAlarm() { return $this->falseAlarm; }
		public function setFalseAlarm($value) { return $this->falseAlarm = $value; }
		public function getDescription() { return $this->description; }
		public function setDescription($value) { return $this->description = $value; }
		public function getPhotograph() { return $this->photograph; }
		public function setPhotograph($value) { return $this->photograph = $value; }
		
		//constructor
		public function __construct(){
			//empty object
			if(func_num_args() == 0){
				$this->id = '';
				$this->falseAlarm = 0;
				$this->description = '';
				$this->date = '';
				$this->photograph = new ReportPhotograph();
			}
			//object with data from arguments
			if(func_num_args() == 4){
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes 
				$this->id = $arguments[0];
				$this->falseAlarm = $arguments[1];
				$this->description = $arguments[2];
				$this->date = $arguments[3];
			}
		}
		
		//represents the object in JSON format
		public function toJson() {
			return json_encode(array(
				'id' => $this->id,		
				'falseAlarm' =>  $this->falseAlarm,	
				'description' => $this->description,
				'date' => $this->date,
				'Photographs' => json_decode(ReportPhotograph::getAllJson($this->id))
			));
		}
		
	}
?>