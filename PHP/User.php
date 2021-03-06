<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once(SITE_ROOT . '/PHP/DB.php');

class Dispatcher {
   var $subj;
   var $email;
   var $week_day;
   var $start_time;
   var $end_time;

   public function addDispatcher( $subj, $email, $week_day, $start_time, $end_time ) {
		if( !is_string($subj) || strlen($subj) !== 4 ) {
			throw new InvalidArgumentException('USER::addDispatcherOfficeHours( string $subj, int $email, string $week_day, string $start_time, string $end_time ) => $subj should be a 4 character string.');
		}
		if( !is_int($email) ) {
			throw new InvalidArgumentException('USER::addDispatcherOfficeHours( string $subj, int $email, string $week_day, string $start_time, string $end_time ) => $email should be an integer.');
		}
		if( !is_string($week_day) ) {
			throw new InvalidArgumentException('USER::addDispatcherOfficeHours( string $subj, int $email, string $week_day, string $start_time, string $end_time ) => $week_day should be a string.');
		}
		$week_day = strtoupper($week_day);
		if( $week_day !== 'SUNDAY' && $week_day !== 'MONDAY' && $week_day !== 'TUESDAY' && $week_day !== 'WEDNESDAY' && $week_day !== 'THURSDAY' && $week_day !== 'FRIDAY' && $week_day !== 'SATURDAY' ) {
			throw new InvalidArgumentException('USER::addDispatcherOfficeHours( string $subj, int $email, string $week_day, string $start_time, string $end_time ) => $week_day should be any of the following: "SUNDAY", "MONDAY", "TUESDAY", "WEDNESDAY", "THURSDAY", "FRIDAY", or "SATURDAY".');
		}
		if( !is_string( $start_time ) || !preg_match('/\d{1,2}:\d{2}/', $start_time) ) {
			throw new InvalidArgumentException('USER::addDispatcherOfficeHours( string $subj, int $email, string $week_day, string $start_time, string $end_time ) => $start_time should be a string of the format "HH:MM".');
		}
		if( !is_string( $end_time ) || !preg_match('/\d{1,2}:\d{2}/', $end_time) || $end_time <= $start_time ) {
			throw new InvalidArgumentException('USER::addDispatcherOfficeHours( string $subj, int $email, string $week_day, string $start_time, string $end_time ) => $end_time should be a string of the format "HH:MM" and be later in the day then $start_time.');
		}
	
		if( !$this->isDispatcher ) {
			return false;
		}
		
		$db = DB::getInstance();
		try {
			$result = $db->prep_execute('INSERT INTO Dispatcher_hours (email, subj, email, week_day, start_time, end_time) VALUES (:email, :subj, :email, :week_day, :start_time, :end_time);', array(
				':email' => $this->email,
				':subj' => $subj,
				':email' => $email,
				':week_day' => $week_day,
				':start_time' => $start_time,
				':end_time' => $end_time
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
	
	public function removeDispatcher( $subj, $email, $week_day ) {
		// --- ARGUMENT ERROR HANDLING ---
		if( !is_string($subj) || strlen($subj) !== 4 ) {
			throw new InvalidArgumentException('USER::removeDispatcherOfficeHours( string $subj, int $email, string $week_day, string $start_time, string $end_time ) => $subj should be a 4 character string.');
		}
		if( !is_int($email) ) {
			throw new InvalidArgumentException('USER::removeDispatcherOfficeHours( string $subj, int $email, string $week_day, string $start_time, string $end_time ) => $email should be an integer.');
		}
		if( !is_string($week_day) ) {
			throw new InvalidArgumentException('USER::removeDispatcherOfficeHours( string $subj, int $email, string $week_day, string $start_time, string $end_time ) => $week_day should be a string.');
		}
		// Sets $week_day to all uppercase characters after checking if string
		$week_day = strtoupper($week_day);
		if( $week_day !== 'SUNDAY' && $week_day !== 'MONDAY' && $week_day !== 'TUESDAY' && $week_day !== 'WEDNESDAY' && $week_day !== 'THURSDAY' && $week_day !== 'FRIDAY' && $week_day !== 'SATURDAY' ) {
			throw new InvalidArgumentException('USER::removeDispatcherOfficeHours( string $subj, int $email, string $week_day, string $start_time, string $end_time ) => $week_day should be any of the following: "SUNDAY", "MONDAY", "TUESDAY", "WEDNESDAY", "THURSDAY", "FRIDAY", or "SATURDAY".');
		}
	
		if( !$this->isDispatcher ) {
			return false;
		}
		
		$db = DB::getInstance();
		try {
			$result = $db->prep_execute('DELETE FROM Dispatcher_hours WHERE email = :email AND subj = :subj AND email = :email AND week_day = :week_day;', array(
				':email' => $this->email,
				':subj' => $subj,
				':email' => $email,
				':week_day' => $week_day
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
}
 
 
class Driver {
   var $Title;
   var $Keywords;
   var $Content;
   
   // Return an array of User objects of all the Driverss in the database.
	public static function getAllDrivers() {
		$allDispatchers = array();
		$db = DB::getInstance();
		$Dispatcher_rows = $db->prep_execute('SELECT email FROM users WHERE isDriver = 1;', array());
		foreach( $Dispatcher_rows as $row ) {
			$allDispatchers[] = USER::fromDatabase($row['email']);
		}
		return $allDispatchers;
	}
    public function Driver_check( $rel, $subj, $email ) {
		// Argument Type Error Handling
		
		// $rel is a string
		if( !is_string($rel) ) {
			throw new InvalidArgumentException('USER::removeDriverCourse(string $rel, string $subj, int $email) => $rel should be one of the following strings: "Driver", "Dispatcher"');
		}
		else { // $rel is either 'Driver' or 'Dispatcher'
			$rel = strtolower($rel);
			if( $rel !== 'Driver' && $rel !== 'Dispatcher' ) {
				throw new DomainException('USER::removeDriverCourse(string $rel, string $subj, int $email) => $rel should be one of the following strings: "Driver", "Dispatcher"');
			}
		}
		// $subj is a string
		if( !is_string($subj) || strlen($subj) !== 4 ) {
			throw new InvalidArgumentException('USER::removeDriverCourse(string $rel, string $subj, int $email) => $subj should be a 4 character string.');
		}
		// $email is an int
		if( !is_int($email) ) {
			throw new InvalidArgumentException('USER::removeDriverCourse(string $rel, string $subj, int $email) => $email should be an integer.');
		}

		// If user is in database, remove user-course mapping to database
		if( $this->inDB ) {
			$db = DB::getInstance();
			try {
				return $db->prep_execute('DELETE FROM ' . $rel . 's_courses WHERE email = :email AND subj = :subj AND email = :email', array(
					':email' => $this->email,
					':subj' => strtoupper($subj),
				));
			}
			catch( PDOException $Exception ) {
				// Return false if a database error occures
				return false;
			}
		}
		// Return false if user not in database
		return false;
	}
   
}
 
 
class Admin {
   var $subj;
   var $email;
   var $week_day;
   var $start_time;
   var $end_time;
    
    // Removes mapping in the database table Drivers_Dispatchers or Dispatchers_Drivers
	// depending on $rel's value.
	public function add_dispatch_driver( $rel, $subj, $email ) {
		// Argument Type Error Handling
		
		// $rel is a string
		if( !is_string($rel) ) {
			throw new InvalidArgumentException('USER::removeDriverCourse(string $rel, string $subj, int $email) => $rel should be one of the following strings: "Driver", "Dispatcher"');
		}
		else { // $rel is either 'Driver' or 'Dispatcher'
			$rel = strtolower($rel);
			if( $rel !== 'Driver' && $rel !== 'Dispatcher' ) {
				throw new DomainException('USER::removeDriverCourse(string $rel, string $subj, int $email) => $rel should be one of the following strings: "Driver", "Dispatcher"');
			}
		}
		// $subj is a string
		if( !is_string($subj) || strlen($subj) !== 4 ) {
			throw new InvalidArgumentException('USER::removeDriverCourse(string $rel, string $subj, int $email) => $subj should be a 4 character string.');
		}
		// $email is an int
		if( !is_int($email) ) {
			throw new InvalidArgumentException('USER::removeDriverCourse(string $rel, string $subj, int $email) => $email should be an integer.');
		}

		// If user is in database, remove user-course mapping to database
		if( $this->inDB ) {
			$db = DB::getInstance();
			try {
				return $db->prep_execute('DELETE FROM ' . $rel . 's_courses WHERE email = :email AND subj = :subj AND email = :email', array(
					':email' => $this->email,
					':subj' => strtoupper($subj),
					':email' => $email
				));
			}
			catch( PDOException $Exception ) {
				// Return false if a database error occures
				return false;
			}
		}
		// Return false if user not in database
		return false;
	}
}

class User {
	// ############################### VARIABLES ###############################
	
	protected $inDB = false;
	protected $password = '';
	protected $email = '';
	protected $isAdmin = false;
	protected $isDispatcher = false;
	protected $isfirstTime = false;
	protected $isDriver = false;
	protected $firstName = null;
	protected $lastName = null;
	
	
	// ############################# CONSTRUCTORS ##############################
	
	// Private constructor helper function. Called by fromDatabase and
	// withValues
	private function __construct( $email, $isDriver = false, $isDispatcher = false, $isfirstTime = false, $isAdmin = false, $firstName = null, $lastName = null, $inDB = false ) {
		// Calls set functions of all variables for invalid argument errors
		if( !($this->setEmail($email) &&
		$this->setIsAdmin($isAdmin) &&
		$this->setIsDispatcher($isDispatcher) &&
		$this->setIsfirstTime($isfirstTime) &&
		$this->setIsDriver($isDriver) &&
		$this->setFirstName($firstName) &&
		$this->setLastName($lastName)) ) {
			return null;
		}
		$this->inDB = $inDB;
	}
	
	// Constructor loads user data from the database using a unique key
	// Returns null if user not found.
	public static function fromDatabase( $email ) {
		
		// Get user info from database's user table
		$db = DB::getInstance();
		try {
			$usersRows = $db->prep_execute('SELECT u.email, u.firstName, u.lastName, u.isAdmin, u.isDispatcher, u.isfirstTime, u.isDriver, p.password FROM users AS u INNER JOIN passwords AS p ON u.email = p.email WHERE u.email = :value', array(
				':value' => $email
			));
		}
		catch( PDOException $e ) {
			return null;
		}
		
		// Return null if no user found
		if( empty($usersRows) ) {
			return null;
		}
		
		// Call private user constructor with database information
		$instance = new self( $usersRows[0]['email'], (bool)$usersRows[0]['isDriver'], (bool)$usersRows[0]['isDispatcher'], (bool)$usersRows[0]['isfirstTime'], (bool)$usersRows[0]['isAdmin'], $usersRows[0]['firstName'], $usersRows[0]['lastName'], true );
		
		// Set Password
		$instance->password = $usersRows[0]['password'];
		
		// Return new user object
		return $instance;
	}
	
	// Constructor builds a new user from parameters
	public static function withValues( $email, $password, $isDriver = false, $isDispatcher = false, $isfirstTime = false, $isAdmin = false, $firstName = null, $lastName = null ) {
		// Calls private user constructor with provided arguments
		$instance = new self($email, $isDriver, $isDispatcher, $isfirstTime, $isAdmin, $firstName, $lastName );

		// Sets password. Returns null on error
		try {
			$instance->setPassword($password); 
		}
		catch (InvalidArgumentException $e) {
			return null;
		}
		
		$db = DB::getInstance();
		try {
			$result = $db->multi_prep_execute(['INSERT INTO users (email, isAdmin, isDispatcher, isfirstTime, isDriver, firstName, lastName) VALUES (:email, :isAdmin, :isDispatcher, :isfirstTime, :isDriver, :firstName, :lastName);', 'INSERT INTO passwords (email, password) VALUES (:email, :password);'], array(
				[
					':email' => $instance->getEmail(),
					':isAdmin' => $instance->getIsAdmin(),
					':isDriver' => $instance->getIsDriver(),
					':isDispatcher' => $instance->getIsDispatcher(),
					':isfirstTime' => $instance->getIsfirstTime(),
					':firstName' => $instance->getFirstName(),
					':lastName' => $instance->getLastName()
				],
				[
					':email' => $instance->getEmail(),
					':password' => $instance->password
				]
			));
			if( $result ) {
				$instance->setInDB(true);
			}
		}
		catch( PDOException $e ) {}
		
		// Returns new user object
		return $instance;
	}
	public static function forgot_pass($email,$sec_key){
		
		$db = DB::getInstance();
		
		//$sec_key = $this->get_random_string();
		
		
		//$sec_key = $this->get_random_string();
		//echo $sec_key;
		//exit;
		$pstmt = "UPDATE passwords SET secrate_key='$sec_key'  WHERE email=:email";
		
		$pstmt_array[] = $pstmt;
		
		//$pstmt = "SELECT email FROM password  WHERE email=:email";
		
		//$pstmt_array[] = $pstmt;
		
		
		try{
			//$stmt = $db->prepare($sql);
			//$stmt->execute();
			
			//$results = $db->multi_prep_execute( $pstmt_array);
			
			$results = $db->multi_prep_execute( $pstmt_array, array(
				[
					':email' => $email
				]
			));
		}
		catch( PDOException $e ) {
			echo $e->getMessage();
			exit;
			//return false;
		}
		
		return $results;
	}

	//new password update when it forgot
	//Change Password
	public static function new_password_update($email, $password ){
		
		
		$db = DB::getInstance();
		
		//$sql = "UPDATE MyGuests SET lastname='Doe' WHERE id=2";
		// Prepare statement
		//$stmt = $conn->prepare($sql);
		// execute the query
		//$stmt->execute();
		// Sets password. Returns null on error
		//$instance = new self($email, $isDriver, $isDispatcher, $isfirstTime, $isAdmin, $firstName, $lastName );

		// Sets password. Returns null on error
		/*try {
			$instance->setPassword($password); 
		}
		catch (InvalidArgumentException $e) {
			return null;
		}*/
		
		$pstmt = "UPDATE passwords SET password=:password, secrate_key = '' WHERE email=:email";
		
		$pstmt_array[] = $pstmt;
		
		$pstmt = "UPDATE users SET isFirstTime=:isfirstTime WHERE email=:email";
		$pstmt_array[] = $pstmt;
		
		try{
			//$stmt = $db->prepare($sql);
			//$stmt->execute();
			
			//$results = $db->multi_prep_execute( $pstmt_array);
			
			$results = $db->multi_prep_execute( $pstmt_array, array(
				[
					':email' => $email,
					':password' => password_hash( $password, PASSWORD_DEFAULT )
				],
				[
					':email' => $email,
					':isfirstTime' => 0
				]
			));
		}
		catch( PDOException $e ) {
			echo $e->getMessage();
			exit;
			//return false;
		}
		
		return $results;
	}
	
	public static function get_random_string($length = 12) {
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
		$token = "";
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < $length; $i++) {
			$n = rand(0, $alphaLength);
			$token.= $alphabet[$n];
		}
		return $token;
	}

	public static function reset_password_update($sec_key, $password ){
		
		$db = DB::getInstance();
		
		$pstmt = "UPDATE passwords SET password=:password, secrate_key = '' WHERE secrate_key=:secrate_key";
		
		$pstmt_array[] = $pstmt;
		
		
		try{
			
			$results = $db->multi_prep_execute( $pstmt_array, array(
				[
					':secrate_key' => $sec_key,
					':password' => password_hash( $password, PASSWORD_DEFAULT )
				]
			));
		}
		catch( PDOException $e ) {
			//echo $e->getMessage();
			//exit;
			return false;
		}
		
		return $results;
	}

	// ########################## ACCESSOR FUNCTIONS ###########################
	
	// Returns whether the password matches the password hash when hashed.
	public function verify_password( $password ) {
		return password_verify( $password, $this->password );
	}
	
	// Creates a user session after verifying their password. User class is stored
	// in $_SESSION['user']. Returns true on successful login, otherwise false.
	public function login( $password ) {
		if( $this->inDB && $this->verify_password( $password ) ) {
			session_start();
			$_SESSION['user'] = $this;
			return true;
		}
		return false;
	}
	
	// Destroys the current user session
	public function logout() {
		// Check if session exists before destroying it
		if( isset($_SESSION) && isset($_SESSION['user']) ) {
			// Erase local session data
			$_SESSION = array();
			
			// Remove client's cookie
			if (ini_get("session.use_cookies")) {
				$params = session_get_cookie_params();
				setcookie(session_name(), '', time() - 42000,
					$params["path"], $params["domain"],
					$params["secure"], $params["httponly"]
				);
			}

			// Destroy the session
			session_destroy();
			return true;
		}
		return false;
	}
	
	// Inserts the user in the database. If the user exists and the update flag
	// is set, it updates the user if the information is different.
	public function store($update = False) {
		$db = DB::getInstance();
		
		$pstmt = 'INSERT INTO users (email, isDriver, isDispatcher, isfirstTime, isAdmin, firstName, lastName) VALUES (:email, :isDriver, :isDispatcher, :isfirstTime, :isAdmin, :firstName, :lastName)';
		if( $update ) {
			$pstmt .= ' ON DUPLICATE KEY UPDATE email = VALUES(email), isDriver = VALUES(isDriver), isDispatcher = VALUES(isDispatcher), isfirstTime = VALUES(isfirstTime), isAdmin = VALUES(isAdmin), firstName = VALUES(firstName), lastName = VALUES(lastName)';
		}
		$pstmt .= ';';
		$pstmt_array[] = $pstmt;
		$pstmt = 'INSERT INTO passwords (email,password) VALUES (:email,:password)';
		if( $update ) {
			$pstmt .= ' ON DUPLICATE KEY UPDATE password = VALUES(password)';
		}
		$pstmt .= ';';
		$pstmt_array[] = $pstmt;
		
		try{
			$results = $db->multi_prep_execute( $pstmt_array, array(
				[
					':email' => $this->email,
					':isDriver' => ($this->isDriver) ? 1 : 0,
					':isDispatcher' => ($this->isDispatcher) ? 1 : 0,
					':isfirstTime' => ($this->isfirstTime) ? 1 : 0,
					':isAdmin' => ($this->isAdmin) ? 1 : 0,
					':firstName' => $this->firstName,
					':lastName' => $this->lastName
				],
				[
					':email' => $this->email,
					':password' => $this->password
				]
			));
		}
		catch( PDOException $e ) {
			return false;
		}
		
		return $results;
	}
	
	//Change Password
	public static function password_update($email, $password ){
		
		
		$db = DB::getInstance();
		
		//$sql = "UPDATE MyGuests SET lastname='Doe' WHERE id=2";
		// Prepare statement
		//$stmt = $conn->prepare($sql);
		// execute the query
		//$stmt->execute();
		// Sets password. Returns null on error
		//$instance = new self($email, $isDriver, $isDispatcher, $isfirstTime, $isAdmin, $firstName, $lastName );

		// Sets password. Returns null on error
		/*try {
			$instance->setPassword($password); 
		}
		catch (InvalidArgumentException $e) {
			return null;
		}*/
		
		$pstmt = "UPDATE passwords SET password=:password WHERE email=:email";
		
		$pstmt_array[] = $pstmt;
		
		$pstmt = "UPDATE users SET isFirstTime=:isfirstTime WHERE email=:email";
		$pstmt_array[] = $pstmt;
		
		try{
			//$stmt = $db->prepare($sql);
			//$stmt->execute();
			
			//$results = $db->multi_prep_execute( $pstmt_array);
			
			$results = $db->multi_prep_execute( $pstmt_array, array(
				[
					':email' => $email,
					':password' => password_hash( $password, PASSWORD_DEFAULT )
				],
				[
					':email' => $email,
					':isfirstTime' => 0
				]
			));
		}
		catch( PDOException $e ) {
			echo $e->getMessage();
			exit;
			//return false;
		}
		
		return $results;
	}
	
	// GET FUNCTIONS
	
	// Return email
	public function getEmail() {
		return $this->email;
	}
	
	public function getInDB() {
		return $this->inDB;
	}
	
	// Return admin flag
	public function getIsAdmin() {
		return $this->isAdmin;
	}
	
	// Return Driver flag
	public function getIsDriver() {
		return $this->isDriver;
	}
	
	// Return Dispatcher flag
	public function getIsDispatcher() {
		return $this->isDispatcher;
	}
	
	// Return firstTime flag
	public function getIsfirstTime() {
		return $this->isfirstTime;
	}
	
	// Return User's first name
	public function getFirstName() {
		return $this->firstName;
	}
	
	// Return User's last name
	public function getLastName() {
		return $this->lastName;
	}
	
	
	// ########################## MODIFIER FUNCTIONS ###########################

	
	// Validates and sets the email address of the user. DOES NOT STORE IN
	// DATABASE! Call USER::store(bool) to store in database.
	public function setEmail( $email, $setDB = false ) {
		if( !is_string($email) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
			throw new InvalidArgumentException('USER::setEmail(string $email, bool $setDB) => $email should be a valid email address.');
		}
		
		if( !$this->setVarInDB($setDB, 'users', 'email', $email) ) {
			return false;
		}
		
		$this->email = $email;
		return true;
	}
	
	public function setInDB( $inDB ) {
		if( !is_bool($inDB) ) {
			throw new InvalidArgumentException('USER::setInDB(string $isAdmin, bool $setDB) => $inDB should be a boolean.');
		}
		
		$this->inDB = $inDB;
	}
	
	// Validates and sets the admin flag of the user. DOES NOT STORE IN
	// DATABASE! Call USER::store(bool) to store in database.
	public function setIsAdmin( $isAdmin, $setDB = false ) {
		if( !is_bool($isAdmin) ) {
			throw new InvalidArgumentException('USER::setIsAdmin(string $isAdmin, bool $setDB) => $isAdmin should be a boolean.');
		}
		
		if( !$this->setVarInDB($setDB, 'users', 'isAdmin', $isAdmin) ) {
			return false;
		}
		
		$this->isAdmin = $isAdmin;
		return true;
	}
	
	// Validates and sets the Dispatcher flag of the user. DOES NOT STORE IN
	// DATABASE! Call USER::store(bool) to store in database.
	public function setIsDispatcher( $isDispatcher, $setDB = false ) {
		if( !is_bool($isDispatcher) ) {
			throw new InvalidArgumentException('USER::setIsDispatcher(string $isDispatcher, bool $setDB) => $isDispatcher should be a boolean.');
		}
		
		if( !$this->setVarInDB( $setDB, 'users', 'isDispatcher', $isDispatcher ) ) {
			return false;
		}
		
		$this->isDispatcher = $isDispatcher;
		return true;
	}
	
	// Validates and sets the firstTime flag of the user. DOES NOT STORE IN
	// DATABASE! Call USER::store(bool) to store in database.
	public function setIsfirstTime( $isfirstTime, $setDB = false ) {
		if( !is_bool($isfirstTime) ) {
			throw new InvalidArgumentException('USER::setIsfirstTime(string $isfirstTime, bool $setDB) => $isfirstTime should be a boolean.');
		}
		
		if( !$this->setVarInDB( $setDB, 'users', 'isfirstTime', $isfirstTime ) ) {
			return false;
		}
		
		$this->isfirstTime = $isfirstTime;
		return true;
	}
	
	// Validates and sets the Driver flag of the user. DOES NOT STORE IN
	// DATABASE! Call USER::store(bool) to store in database.
	public function setIsDriver( $isDriver, $setDB = false ) {
		if( !is_bool($isDriver) ) {
			throw new InvalidArgumentException('USER::setIsDriver(string $isDriver, bool $setDB) => $isDriver should be a boolean.');
		}
		
		if( !$this->setVarInDB( $setDB, 'users', 'isDriver', $isDriver ) ) {
			return false;
		}
		
		$this->isDriver = $isDriver;
		return true;
	}
	
	// Validates, hashes, and sets the password of the user. DOES NOT STORE IN
	// DATABASE! Call USER::store(bool) to store in database.
	public function setPassword( $password, $setDB = false ) {
		if( strlen($password) <= 8 ) {
			throw new InvalidArgumentException('USER::setPassword(string $password, bool $setDB) => $password should be at least 8 characters long.');
		}
		
		// Hash password with random salt
		$password = password_hash( $password, PASSWORD_DEFAULT );
		
		// Store hashed password in database if $setDB = true
		if( !$this->setVarInDB( $setDB, 'passwords', 'password', $password ) ) {
			return false;
		}

		// Set object password to hashed password
		$this->password = $password;
		return true;
	}
	
	// Validates and sets the first name of the user. DOES NOT STORE IN 
	// DATABASE! Call USER::store(bool) to store in database.
	public function setFirstName( $firstname, $setDB = false ) {
		if( !is_string($firstname) || empty($firstname) ) {
			throw new InvalidArgumentException('USER::setFirstName(string $firstname, bool $setDB) => $firstname should be a non-empty string.');
		}
		
		if( !$this->setVarInDB( $setDB, 'users', 'firstName', $firstname ) ) {
			return false;
		}
		
		$this->firstName = $firstname;
		return true;
	}
	
	// Validates and sets the last name of the user. DOES NOT STORE IN 
	// DATABASE! Call USER::store(bool) to store in database.
	public function setLastName( $lastname, $setDB = false ) {
		if( !is_string($lastname) || empty($lastname) ) {
			throw new InvalidArgumentException('USER::setFirstName(string $lastname, bool $setDB) => $lastname should be a non-empty string.');
		}
		
		if( !$this->setVarInDB( $setDB, 'users', 'lastName', $lastname ) ) {
			return false;
		}
		
		$this->lastName = $lastname;
		return true;
	}
	
	
	// ########################### STATIC FUNCTIONS ############################
	
	public static function getAllUsers() {
		$allUsers = array();
		$db = DB::getInstance();
		$user_rows = $db->prep_execute('SELECT email FROM users ORDER BY lastName, firstName, email;', array());
		foreach( $user_rows as $row ) {
			$allUsers[] = USER::fromDatabase($row['email']);
		}
		return $allUsers;
	}
	
	// Return an array of User objects of all the Driverss in the database.
	public static function getAllDrivers() {
		$allDispatchers = array();
		$db = DB::getInstance();
		$Dispatcher_rows = $db->prep_execute('SELECT email FROM users WHERE isDriver = 1;', array());
		foreach( $Dispatcher_rows as $row ) {
			$allDispatchers[] = USER::fromDatabase($row['email']);
		}
		return $allDispatchers;
	}
	
	// Return an array of User objects of all the Dispatchers in the database.
	public static function getAllDispatchers() {
		$allDispatchers = array();
		$db = DB::getInstance();
		$Dispatcher_rows = $db->prep_execute('SELECT email FROM users WHERE isDispatcher = 1;', array());
		foreach( $Dispatcher_rows as $row ) {
			$allDispatchers[] = USER::fromDatabase($row['email']);
		}
		return $allDispatchers;
	}
	
	// Removes user with unique id from database
	public static function deleteFromDB($email) {
		// Argument Validation
		if( !is_string($email) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
			throw new InvalidArgumentException('USER::deleteFromDB(string $email) => $email should be a valid email address.');
		}
		
		// Removed the user row with unique column from database
		$db = DB::getInstance();
		$result = $db->prep_execute('DELETE FROM users WHERE email = :email;', array(
			':email' => $email
		));
		
		// Return true if a user was deleted. Otherwise, return false.
		if($result) {
			return true;
		}
		return false;
	}
	
	
	// ########################### PRIVATE FUNCTIONS ###########################
	
	// Update a variable in the user or password database tables. Used to
	// prevent repeat code in all the 'set' functions.
	private function setVarInDB($setDB, $table, $var_name, $var_value) {
		if( $setDB ) {
			$db = DB::getInstance();
			try {
				$result = $db->prep_execute('UPDATE ' . $table . ' SET ' . $var_name . ' = :new_' . $var_name . ' WHERE email = :email;', array(
					':email' => $this->email,
					':new_' . $var_name => $var_value
				));
				if( !$result ) {
					$result = $db->prep_execute('SELECT ' . $var_name . ' FROM ' . $table);
					if( $result[0][$var_name] !== $var_value ) {
						$this->setInDB(false);
					}
				}
			}
			catch( PDOException $e) {
				return false;
			}
		}
		else {
			$this->setInDB(false);
		}
		
		return true;
	}
	
}
?>
