<?php
    class Time {
        //Attribute
        private $start;
		private $end;
        
        //Setters and Getters
        public function getStart() { return $this->start; }
        public function setStart($value) { $this->start = $value;}
        public function getEnd() { return $this->end; }
        public function setEnd($value) { $this->end = $value;}
        
        //Constructors
        
        public function __construct() {
            //empty object
            if(func_num_args() == 0){
                $this->start = '';
                $this->end = '';
            }
            //Object with data from arguments
            if(func_num_args() == 2) {
                //get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
                $this->start = $arguments[0];
                $this->end = $arguments[1];
            }
        }
        		//represents the object in JSON format
		public function toJson() {
			return json_encode(array(
				'start' => $this->start,
                'end' => $this->end
			));
		}
    } 
?>