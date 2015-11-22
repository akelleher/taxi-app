<script type="text/javascript">
  function myFunction() {
	var pass = document.getElementById("pass").value;
    var confirm = document.getElementById("confirm").value;
    var ok = true;
    if (pass != confirm) {
        //alert("Passwords Do not match");
        //document.getElementById("pass").style.borderColor = "#E34234";
        //document.getElementById("confirm").style.borderColor = "#E34234";
		alert("Passwords Mismetch!!!");
        ok = false;
    }
    else {
        alert("Passwords Match!!!");
    }
    return ok;
}
</script>
<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(SITE_ROOT . '/PHP/User.php');

$message = '';
$message_class = 'hidden';

if( isset($_POST['form']) ) {
	
	switch ($_POST['form']) {
		case 'PasswordChange':
			try {
				
				//$password = $_POST['confirm'];
				//$email = $_GET['email'];
				$seckey = $_GET['seckey'];
				$sk = 1;
				if($sk = USER::reset_password_update($seckey, $_POST['confirm'])) {
					$message = 'Success!';
					$message_class = 'success';
				}
				else {
					$message = 'ERROR: could not update password.';
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
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Taxiping Login Page">

    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <!--<link rel="stylesheet" href="login.css">-->
	
	<link rel="stylesheet" type="text/css" href="./resources/user.css">
	<link rel="stylesheet" type="text/css" href="./resources/table.css">
	
  </head>
  <body>
    <div class = "container" id = "outer_container">
	
	<div class="wrapper">
		
      <div class = "row vertical-align">
        <div class = "col-xs-0 col-sm-3">
        </div>
        <div class = "col-xs-12 col-sm-6">
          <center>
            <div id = "change_pass">
              <form class="loginForm" id="PasswordChange" onsubmit="return myFunction()" method="POST">
                <div class = "form-group">
					<input type="hidden" name="form" value="PasswordChange" />
                  <label for = "pwd" class = "label-align">Enter a new password:</label>
                  <input type = "password" class = "form-control" autofocus="autofocus" id = "pass" name = "pass" placeholder="New Password">
				</div>
                <div class = "form-group">
                  <label for = "pwd" class = "label-align">Confirm password:</label>
                  <input type = "password" class = "form-control" id = "confirm" name = "confirm" placeholder="Confirm Password">
                </div>
				<a class="btn btn-link link-align" href="login.php" id="loginpage">Login</a>
                <button type="submit" class="btn btn-lg btn-default submit-align">Submit</button>
              </form>
            </div>
						<section id="login_error"></section>
          </center>
        </div>
        <div class = "col-xs-0 col-sm-3">
        </div>
      </div>
    </div>
  </body>
  
</html>
