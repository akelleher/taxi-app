<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once(SITE_ROOT . '/PHP/DB.php');

class taxiCompany {

	// ############################### VARIABLES ###############################
	private $companyName = '';
	private $phone = '';
	private $address = '';

	public function addTaxiCompany($companyName, $phone, $address){
		// Argument type error handling

		// $companyName is a string
		if( !is_string($companyName) ) {
			throw new InvalidArgumentException('taxiCompany::addTaxiCompany( $companyName, $phone, $address) => $companyName should be a string');
		}

		// $phone is a string
		if( !is_string($phone) ) {
			throw new InvalidArgumentException('taxiCompany::addTaxiCompany( $companyName, $phone, $address) => $phone should be a string');
		}

		// $address is a string
		if( !is_string($address) ) {
			throw new InvalidArgumentException('taxiCompany::addTaxiCompany( $companyName, $phone, $address) => $address should be a string');
		}

		//Add to database
		$db = DB::getInstance();
		try {
			$result = $db->prep_execute('INSERT INTO taxiCompanies (companyName, phone, address) VALUES (:companyName, :phone, :address);', array(
				':companyName' => $this->companyName,
				':phone' => $this->phone,
				':address' => $this->address,
			));
		}
		catch( PDOException $e ) {
			return false;
		}
		
		if( $result ) {
			return true;
		}
		return false;
	}

	// ############################# CONSTRUCTORS ##############################
	
	// Private constructor helper function. Called by fromDatabase
	private function __construct($companyName, $phone, $address) {
		// Calls set functions of all variables for invalid argument errors
		if( !($this->setCompanyName($companyName) &&
		$this->setPhone($phone) &&
		$this->setAddress($address)) ) {
			return null;
		}
	}
	
	// Constructor loads user data from the database using compnany name
	// Requires unique company name
	// Returns null if user not found.
	public static function fromDatabase( $companyName ) {
		
		// Get user info from database's taxiCompany table
		$db = DB::getInstance();
		try {
			$companyRows = $db->prep_execute('SELECT c.companyName, c.phone, c.address FROM taxiCompanies c WHERE c.companyName = :value', array(
				':value' => $companyName
			));
		}
		catch( PDOException $e ) {
			return null;
		}
		
		// Return null if no user found
		if( empty($companyRows) ) {
			return null;
		}
		
		// Call private user constructor with database information
		$instance = new self( $companyRows[0]['companyName'], $companyRows[0]['phone'], (bool)$companyRows[0]['address']);
		
		// Return new user object
		return $instance;
	}

	// ############################### ACCESSORS ###############################
	public function getCompanyName() {
		return $this->companyName;
	}

	public function getPhone() {
		return $this->phone;
	}

	public function getaddress() {
		return $this->address;
	}

	// Inserts the company in the database. If the company exists and the update flag
	// is set, it updates the company if the information is different.
	public function store($update = False) {
		$db = DB::getInstance();
		
		$pstmt = 'INSERT INTO taxiCompanies(companyName, phone, address) VALUES (:companyName, :phone, :address)';
		if( $update ) {
			$pstmt .= ' ON DUPLICATE KEY UPDATE companyName = VALUES(email), phone = VALUES(phone), address = VALUES(address)';
		}
		$pstmt .= ';';
		$pstmt_array[] = $pstmt;
		
		try{
			$results = $db->multi_prep_execute( $pstmt_array, array(
				[
					':companyName' => $this->companyName,
					':phone' => ($this->phone),
					':address' => ($this->address)
				]
			));
		}
		catch( PDOException $e ) {
			return false;
		}
		
		return $results;
	}


	// ########################## MODIFIER FUNCTIONS ###########################

	
	// Validates and sets the company name. DOES NOT STORE IN
	// DATABASE! Call taxiCompany::store(bool) to store in database.
	public function setCompanyName( $companyName, $setDB = false ) {
		if( !is_string($companyName) || empty($companyName) ) {
			throw new InvalidArgumentException('USER::setCompanyName(string $companyName, bool $setDB) => $companyName should be a valid string.');
		}
		
		if( !$this->setVarInDB($setDB, 'users', 'companyName', $companyName) ) {
			return false;
		}
		
		$this->companyName = $companyName;
		return true;
	}

	public function setPhone( $phone, $setDB = false ) {
		if( !is_string($phone) || empty($phone) ) {
			throw new InvalidArgumentException('USER::setPhone(string $phone, bool $setDB) => $phone should be a valid string.');
		}
		
		if( !$this->setVarInDB($setDB, 'users', 'phone', $phone) ) {
			return false;
		}
		
		$this->phone = $phone;
		return true;
	}

	public function setAddress( $address, $setDB = false ) {
		if( !is_string($address) || empty($address) ) {
			throw new InvalidArgumentException('USER::setAddress(string $address, bool $setDB) => $address should be a valid string.');
		}
		
		if( !$this->setVarInDB($setDB, 'users', 'address', $address) ) {
			return false;
		}
		
		$this->address = $address;
		return true;
	}	

	// ########################### PRIVATE FUNCTIONS ###########################
	
	// Update a variable in the taxiCompanies table. Used to
	// prevent repeat code in all the 'set' functions.
	private function setVarInDB($setDB, $table, $var_name, $var_value) {
		if( $setDB ) {
			$db = DB::getInstance();
			try {
				$result = $db->prep_execute('UPDATE taxiCompanies SET ' . $var_name . ' = :new_' . $var_name . ' WHERE companyName = :companyName;', array(
					':companyName' => $this->companyName,
					':new_' . $var_name => $var_value
				));
				if( !$result ) {
					$result = $db->prep_execute('SELECT ' . $var_name . ' FROM taxiCompanies');
				}
			}
			catch( PDOException $e) {
				return false;
			}
		}
		else {
		}
		
		return true;
	}
}
?>