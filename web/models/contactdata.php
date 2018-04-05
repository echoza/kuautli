<?php
    class ContactData{
        //Attributes
        private $phone;
        private $email;
        
        //Setters and Getters
        public function getPhone() { return $this->phone; }
        public function setPhone($value) { $this->phone = $value; }
        public function getEmail() { return $this->email; }
        public function setEmail($value) { $this->email = $value; }
        
        //Constructors
        public function __construct() {
            //empty object
            if(func_num_args() == 0) {
                $this->phone = '';
                $this->email = '';
            }
            //object with data from arguments
			if (func_num_args() == 2) {
				//get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
				$this->phone = $arguments[0];
                $this->email = $arguments[1];
			}
        }
        
        //represents the object in JSON format
		public function toJson() {
			return json_encode(array(
					'phone' =>  $this->phone,
                    'email' => $this->email
			));
		}
    }
?>