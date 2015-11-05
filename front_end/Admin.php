<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
require(SITE_ROOT . '/PHP/User.php');
require(SITE_ROOT . '/PHP/Course.php');
require(SITE_ROOT . '/PHP/relations.php');
require(SITE_ROOT . '/PHP/check_logged_in.php');

$users = USER::getAllUsers();
$courses = COURSE::getAllCourses();

$message = '';
$message_class = 'hidden';
/*
	Determines what is input into the form and creates
	a new user, upon success, the user will be created
	and success will be written on the screen

	The second part of the case is if a user is deleted
	upon success, the user will be removed from the database
	and success will be written on the screen
*/
if( isset($_POST['form']) ) {
	switch ($_POST['form']) {
		case 'AddUser':
			try {
				if (isset($_POST['admin']))
					$admin = true;
				else
					$admin = false;
				if (isset($_POST['Driver']))
					$Driver = true;
				else
					$Driver = false;
				if (isset($_POST['Dispatcher']))
					$Dispatcher = true;
				else
					$Dispatcher = false;
				if (isset($_POST['firstTime']))
					$firstTime = true;
				else
					$firstTime = false;

				if( USER::withValues($_POST['email'], $_POST['password'], $Driver, $Dispatcher, $firstTime, $admin, $_POST['firstname'], $_POST['lastname'])) {
					$message = 'Success!';
					$message_class = 'success';
				}
				else {
					$message = 'ERROR: could not add user to database.';
					$message_class = 'error';
				}
			}
			catch( Exception $e ) {
				$message = $e->getMessage();
				$message_class = 'error';
			}
			break;
		case 'DeleteUser':
			try {
				if( USER::deleteFromDB($_POST['email']) ) {
					$message = 'Success!';
					$message_class = 'success';
				}
				else {
					$message = 'ERROR: could not delete user from database.';
					$message_class = 'error';
				}
			}
			catch( Exception $e ) {
				$message = $e->getMessage();
				$message_class = 'error';
			}
			break;
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>TaxiPing</title>
	<link rel="stylesheet" type="text/css" href="<?php echo SITE_URL; ?>/front+end/resources/better-timeinput-polyfill.css">
	<link rel="stylesheet" type="text/css" href="<?php echo SITE_URL; ?>/front_end/resources/user.css">
	<link rel="stylesheet" type="text/css" href="<?php echo SITE_URL; ?>/front_end/resources/admin.css">

	<!--[if lt IE 9]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>
<body>
	<div class="wrapper">
		<?php	$firstname = $_SESSION['user']->getFirstName();
						$lastname = $_SESSION['user']->getLastName();
						echo "<div id= 'namesize' align='right'> Welcome " . $firstname . " " . $lastname . " </div>";
					?>
		<div id="upperright">
			<a href="logout.php">Logout</a>
		</div>
		<?php include(SITE_ROOT . '/front_end/header.php') ?>
		<section id="Message" class="<?php echo $message_class ?>">
			<?php echo $message ?>
		</section>
		<section class="courses">
			<form id="AddUser" action="" method="POST">
				<h2>Add a User</h2>
				<input type="hidden" name="form" value="AddUser" />
				<div class="input_block">
					<label for="AddUser_Email">Email</label>
					<input id="AddUser_Email" type="text" name="email" required />
				</div>
				<div class="input_block">
					<label for="AddUser_FirstName">First Name</label>
					<input id="AddUser_FirstName" type="text" name="firstname" />
				</div>
				<div class="input_block">
					<label for="AddUser_LastName">Last Name</label>
					<input id="AddUser_LastName" type="text" name="lastname" />
				</div>
				<div class="input_block">
					<label for="AddUser_Password">Password</label>
					<input id="AddUser_Password" type="password" name="password" required />
				</div>
				<div class="input_inline">
					<input id="AddUser_Admin" type="checkbox" name="admin"/>
					<label for="AddUser_Admin">Admin</label>
				</div>
				<div class="input_inline">
					<input id="AddUser_Driver" type="checkbox" name="Driver" />
					<label for="AddUser_Driver">Driver</label>
				</div>
				<div class="input_inline">
					<input id="AddUser_Dispatcher" type="checkbox" name="Dispatcher" />
					<label for="AddUser_Dispatcher">Dispatcher</label>
				</div>
				<div class="input_inline">
					<input id="AddUser_firstTime" type="checkbox" name="firstTime" />
					<label for="AddUser_firstTime">First time?</label>
				</div>
				<input class="input_block" type="submit" value="Add User" />
			</form>
			<form id="DeleteUser" action="#" method="POST">
				<h2>Delete a User</h2>
				<input type="hidden" name="form" value="DeleteUser" />
				<label for="DeleteUser_User">User</label>
				<select id="DeleteUser_User" name="email" required>
				<?php foreach($users as $user) : ?>
					<option value="<?php echo $user->getEmail(); ?>"><?php echo $user->getLastName() . ', ' . $user->getFirstName() . ' (' . $user->getEmail() . ')'; ?></option>
				<?php endforeach; ?>
				</select>
				<input class="input_block" type="submit" value="Delete User" />
			</form>
		</section>

		<footer>
			© 2014 TaxiPing
		</footer>
	</div><!-- .wrapper -->
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<script type="text/javascript" src="<?php SITE_URL ?>/front_end/resources/webshim/polyfiller.js"></script>
	<script type="text/javascript">
		webshim.polyfill('forms-ext');
	</script>
</body>
</html>
