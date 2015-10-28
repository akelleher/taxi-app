<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once(SITE_ROOT . '/PHP/User.php');
require_once(SITE_ROOT . '/PHP/Course.php');
require(SITE_ROOT . '/php/check_logged_in.php');


if (isset($_GET['Dispatcher_name']) && !empty($_GET['Dispatcher_name'])) {
	try {
		$dbconn = DB::getInstance();
		$Dispatcher_name = mysql_real_escape_string(stripslashes($_GET['Dispatcher_name']));
		$name_arr = explode(' ', $Dispatcher_name);

		$result = null;
		//more than one name entered
		if (count($name_arr) > 1) {
			$search_arr = array(':firstName' => $name_arr[0], ':lastName' => $name_arr[1]);
			$result = $dbconn->prep_execute("SELECT firstName, lastName, users.email, courses.subj AS subj, courses.crse AS crse, name FROM users, Dispatchers_courses, courses WHERE isDispatcher = 1 AND firstName LIKE CONCAT('%', :firstName,'%')  AND lastName LIKE CONCAT('%', :lastName,'%') AND users.email = Dispatchers_courses.email AND Dispatchers_courses.crse = courses.crse AND Dispatchers_courses.subj = courses.subj", $search_arr);
			
		}
		else { //either first name or last name entered
			$search_arr = array(':firstName' => $name_arr[0], ':lastName' => $name_arr[0]);
			$result = $dbconn->prep_execute("SELECT firstName, lastName, users.email, courses.subj AS subj, courses.crse AS crse, name FROM users, Dispatchers_courses, courses WHERE isDispatcher = 1 AND (firstName LIKE CONCAT('%', :firstName,'%') OR lastName LIKE CONCAT('%', :lastName,'%')) AND users.email = Dispatchers_courses.email AND Dispatchers_courses.crse = courses.crse AND Dispatchers_courses.subj = courses.subj", $search_arr);
	
		}

		if ($result != null && sizeof($result) > 0) {
			$all = '';
			foreach ($result as $row) {
				$chk_box_val = $row['subj'] . ' ' . $row['crse'];
        		$all .= ('<tr><td>' . $row['firstName'] . '</td><td>' . $row['lastName'] . '</td><td>' . $row['email'] . '</td><td>' . $row['subj'] . '</td><td>' . $row['crse'] .  '</td><td>' . $row['name'] . '</td><td><input type="checkbox" value="' . $chk_box_val .  '"></td></tr>');
        	}
        	$all.=('<tr><td></td><td></td><td></td><td></td><td></td><td></td><td><input type="submit" value="Add"></td></tr>');
        	echo $all;
		}
		else {
			echo '';
		}
    }

	catch (Exception $e) {
		echo "Error: " . $e->getMessage();
	}
}

if (isset($_GET['class_name']) && !empty($_GET['class_name'])) {
	try {
		$dbconn = DB::getInstance();
		$class_name = mysql_real_escape_string(stripslashes($_GET['class_name']));
		$school_name = mysql_real_escape_string(stripslashes($_GET['school_name']));

		$result = null;
		//school not selected
		
		$search_arr = array(':class_name' => $class_name);
		$result = $dbconn->prep_execute("SELECT courses.subj, courses.crse, name, firstName, lastName, users.email FROM Dispatchers_courses, courses, users WHERE courses.name LIKE CONCAT('%', :class_name,'%') AND Dispatchers_courses.subj = courses.subj AND Dispatchers_courses.crse = courses.crse AND Dispatchers_courses.email = users.email", $search_arr);
			
	
		if ($result != null && sizeof($result) > 0) {
			$all = '';
			foreach ($result as $row) {
				$chk_box_val = $row['subj'] . ' ' . $row['crse'];
        		$all .= ('<tr><td>' . $row['subj'] . '</td><td>' . $row['crse'] . '</td><td>' . $row['name'] . '</td><td>' . $row['firstName'] . ' ' . $row['lastName'] .  '</td><td>' . $row['email'] . '</td><td><input type="checkbox" value="' . $chk_box_val .  '"></td></tr>');
        	}
        	$all.=('<tr><td></td><td></td><td></td><td></td><td></td><td><input type="submit" value="Add"></td></tr>');
        	echo $all;
		}
		else {
			echo '';
		}
    }

	catch (Exception $e) {
		echo "Error: " . $e->getMessage();
	}
}

if (isset($_GET['add']) && isset($_GET['checked_vals']) && !empty($_GET['checked_vals'])) {
	$results = $_GET['checked_vals'];
	try {
		$user = User::fromDatabase($_SESSION['user']->getEmail());
		foreach ($results as $val) {
			$arr = explode(' ', $val); //[0] = subj, [1] = crse#
			if ($user->addUserCourse('Driver', $arr[0], (int) $arr[1])) {
				 //return true if added successfully
				echo 1;
			}
			else {
				echo 0;
			}
		}
	}
	catch(Exception $e) {
		echo "Error: " . $e->getMessage();
	}
	
}

?>