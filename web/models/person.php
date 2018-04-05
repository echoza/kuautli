<?php
	//use files 
	require_once('exceptions/recordnotfoundexception.php');
	require_once('contactdata.php');
	require_once('account.php');
	//Class
	abstract class person {
		//attributes 
		protected $firstName;
		protected $lastName;
		protected $birthdate;
		protected $gender;
		protected $contactData;
		protected $account;
        public function age() {
			list($Y,$m,$d) = explode("-",$this->birthdate);
			return( date("md") < $m.$d ? date("Y")-$Y-1 : date("Y")-$Y );
		}
		public function getFirstName(){ return $this->firstName; }
		public function setFirstName($value){ return $this->firstName = $value; }
		public function getLastName(){ return $this->lastName; }
		public function setLastName($value){ return $this->lastName = $value; }
		public function getBirthdate(){ return $this->birthdate; }
		public function setBirthdate( $value ){ return $this->birthdate = $value; }
		public function getGender() { return $this->gender; }
		public function setGender($value) { return $this->gender = $value; }
		public function getContactData() { return $this->contactData; }
		public function setContactData($value) { return $this->contactData = $value; } 
		public function getAccount() { return $this->account; }
		public function setAccount($value) { return $this->account = $value; }
	}
?>