<?php
	require_once(dirname(dirname(__FILE__)) . '/config.php');
	require_once(SITE_ROOT . '\PHP\User.php');
	session_start();

    //automatically destroys user session data
	if( isset($_SESSION) && !empty($_SESSION['user']) ) {
		$_SESSION['user']->logout();
		header("Location: loginpage/login.php"); /* Redirect browser */
		exit();
	}
?>
