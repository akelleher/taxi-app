
<?php
	
	require(dirname(dirname(dirname(__FILE__))) . '/config.php');
	require(SITE_ROOT . '/PHP/DB.php');
	require(SITE_ROOT . '/PHP/User.php');


	try {
		//$dbconn = new DB(); 
		$dbconn = DB::getInstance();

		if (isset($_POST['login'])) {
			$empty = empty($_POST['user']) || empty($_POST['pass']); 
			if( isset($_POST['user'], $_POST['pass']) && $empty == false) {

				$username = mysql_real_escape_string(stripslashes($_POST['user']));
				$password = mysql_real_escape_string(stripslashes($_POST['pass']));

				
				$user = User::fromDatabase($username);
				if ($user) {
					if ($user->login($password)) {
						//echo 1;
                        if( $user->getIsfirstTime() && $user->getIsAdmin() ) {
							echo 'loginpage/register.php';
						}
                        elseif( $user->getIsfirstTime() && $user->getIsDriver() ) {
							echo 'loginpage/register.php';
						}
                        elseif( $user->getIsfirstTime() && $user->getIsDispatcher() ) {
							echo 'loginpage/register.php';
						}
                        elseif( $user->getIsAdmin() ) {
							echo 'admin.php';
						}
						elseif( $user->getIsDriver() ) {
							echo 'Driver.php';
						}
						elseif( $user->getIsDispatcher() ) {
							echo 'Dispatcher.php';
						}
						else {
							echo '_profile.php';
						}
					}
					else {
						echo 0;
					}
				}
				else {
					echo 0;
				}
				
				exit();
				
			}
			else {
				echo "form not completed";
			}
		}

		if (isset($_POST['register'])) {
			$empty = empty($_POST['firstName']) || empty($_POST['lastName']) || empty($_POST['email']) || empty($_POST['password']) 
					|| empty($_POST['isDriver']) || empty($_POST['isDispatcher']) || empty($_POST['isfirstTime']); 
			if( isset($_POST['firstName'], $_POST['lastName'], $_POST['email'], $_POST['password'], $_POST['isDriver'], $_POST['isDispatcher'], $_POST['isfirstTime']) && $empty == false) { 

				$firstName = mysql_real_escape_string(stripslashes($_POST['firstName']));
				$lastName = mysql_real_escape_string(stripslashes($_POST['lastName']));
				$email = mysql_real_escape_string(stripslashes($_POST['email']));
				$password = mysql_real_escape_string(stripslashes($_POST['password']));
				$isDriver = mysql_real_escape_string(stripslashes($_POST['isDriver']));
				$isDispatcher = mysql_real_escape_string(stripslashes($_POST['isDispatcher']));
				$isfirstTime = mysql_real_escape_string(stripslashes($_POST['isfirstTime']));
				
				// Transforms strings into boolean values
				$isDriver = ($isDriver === 'true') ? true : false;
				$isDispatcher = ($isDispatcher === 'true') ? true : false;
				$isfirstTime = ($isfirstTime === 'true') ? true : false;

				// If user in database
				if( USER::fromDatabase($email) !== null ) {
					echo 1;			
				}
				else {
					$user = User::withValues($email, $password, $isDriver, $isDispatcher, $isfirstTime, false, $firstName, $lastName);
					//var_dump($user);

					if ($user === null) { //check if error instantiating user (password too short)
						echo 0;
					}
					else {
						$user->store();
						$user->login($password);
						echo 2;
					}
				}
			}
		}
	}
	catch (Exception $e) {
		echo "Error: " . $e->getMessage();
	}




?>
