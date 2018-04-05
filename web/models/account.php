<?php
    class Account {
        //Attributes
        private $username;
		private $password;
		private $lastLogin;

        //Setters and Getters
        public function getUsername() { return $this->username; }
        public function setUsername($value) { return $this->username = $value;}
        public function getPassword() { return $this->password;}
        public function setPassword($value) { return $this->password = $value; }
		public function getLastLogin(){ return $this->lastLogin; }
		public function setLastLogin($value){ return $this->lastLogin = $value; }

        //Constructors

        public function __construct() {
            //empty object
            if(func_num_args() == 0){
                $this->username = '';
				$this->lastLogin = '';
            }
			//Object with data from arguments
            if(func_num_args() == 1) {
                //get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
                $this->username = $arguments[0];
            }

            //Object with data from arguments
            if(func_num_args() == 2) {
                //get arguments
				$arguments = func_get_args();
				//pass arguments to attributes
                $this->username = $arguments[0];
				$this->lastLogin = $arguments[1];
			}
        }
        //represents the object in JSON format
		public function toJson() {
			return json_encode(array(
				'username' => $this->username,
				'lastLogin' => $this->lastLogin
			));
		}
		 //represents the object in JSON format
		public function toJsonL() {
			return json_encode(array(
				'username' => $this->username
			));
		}
    }
?>
