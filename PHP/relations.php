<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once(SITE_ROOT . '/PHP/DB.php');
require_once(SITE_ROOT . '/PHP/User.php');
require_once(SITE_ROOT . '/PHP/Course.php');

$users = array();
$courses = array();

function getAllDriversCourses() {
	// Get Driver - course key mappings
	$db = DB::getInstance();
	$Drivers_courses = $db->prep_execute('SELECT * FROM Drivers_courses;', array());

	// Global list of user & course objects. Prevents unnecessary DB reads.
	global $users, $courses;

	// Array of user - course object pair mappings to be returned.
	$return = array();

	// Loop through all Driver - course key mappings
	foreach( $Drivers_courses as $row ) {
		// Read user from DB and add to user array if not found in array
		if( !isset($users[$row['email']]) ) {
			$users[$row['email']] = USER::fromDatabase($row['email']);
		}

		// Read course from DB and add to user array if not found in array
		if( !isset($courses[$row['subj'] . '-' . $row['crse']]) ) {
			$courses[$row['subj'] . '-' . $row['crse']] = COURSE::fromDatabase( $row['subj'], intval($row['crse']) );
		}

		// Add Driver - course object pair to return array
		$return[] = [
			'user' => $users[$row['email']],
			'course' => $courses[$row['subj'] . '-' . $row['crse']]
		];
	}

	return $return;
}

function getAllDispatchersCourses() {
	// Get Driver - course key mappings
	$db = DB::getInstance();
	$Dispatchers_courses = $db->prep_execute('SELECT * FROM Dispatchers_courses;', array());

	// Global list of user & course objects. Prevents unnecessary DB reads.
	global $users, $courses;

	// Array of user - course object pair mappings to be returned.
	$return = array();

	// Loop through all Driver - course key mappings
	foreach( $Dispatchers_courses as $row ) {
		// Read user from DB and add to user array if not found in array
		if( !isset($users[$row['email']]) ) {
			$users[$row['email']] = USER::fromDatabase($row['email']);
		}

		// Read course from DB and add to user array if not found in array
		if( !isset($courses[$row['subj'] . '-' . $row['crse']]) ) {
			$courses[$row['subj'] . '-' . $row['crse']] = COURSE::fromDatabase( $row['subj'], intval($row['crse']) );
		}

		// Add Driver - course object pair to return array
		$return[] = [
			'user' => $users[$row['email']],
			'course' => $courses[$row['subj'] . '-' . $row['crse']]
		];
	}

	return $return;
}

function getAllDispatcherOfficeHours() {
	$db = DB::getInstance();
	$Dispatcher_hours = $db->prep_execute('SELECT * FROM Dispatcher_hours;', array());

	// Global list of user & course objects. Prevents unnecessary DB reads.
	global $users, $courses;

	// Array of user - course object pair mappings to be returned.
	$return = array();

	// Loop through all Driver - course key mappings
	foreach( $Dispatcher_hours as $row ) {
		// Read user from DB and add to user array if not found in array
		if( !isset($users[$row['email']]) ) {
			$users[$row['email']] = USER::fromDatabase($row['email']);
		}

		// Read course from DB and add to user array if not found in array
		if( !isset($courses[$row['subj'] . '-' . $row['crse']]) ) {
			$courses[$row['subj'] . '-' . $row['crse']] = COURSE::fromDatabase( $row['subj'], intval($row['crse']) );
		}

		// Add Driver - course object pair to return array
		$return[] = [
			'user' => $users[$row['email']],
			'course' => $courses[$row['subj'] . '-' . $row['crse']],
			'week_day' => $row['week_day'],
			'startTime' => $row['start_time'],
			'endTime' => $row['end_time']
		];
	}

	return $return;
}

?>
