<script type="text/javascript">
  function myFunction() {
	var email = document.getElementById("pass").value;
    var ok = true;
    if (email != '') {
        //alert("Passwords Do not match");
        //document.getElementById("pass").style.borderColor = "#E34234";
        //document.getElementById("confirm").style.borderColor = "#E34234";
		alert("Email field is not blank pleasecheck enter value!!!");
        ok = false;
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
				
				if(USER::forgot_pass($_POST['email'], USER::get_random_string())) {
					
					header( 'Location: ' . SITE_URL . '/front_end/loginpage/new_password.php?email='.$_POST['email'] );
					
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
<html>
	<head>
		<title>Forgot Password Page</title>
		<script type="text/javascript"></script>
		<link rel="stylesheet" type="text/css" href="login.css">
		
		
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
                  <label for = "email" class = "label-align">Email Id:</label>
                  <input type = "text" class = "form-control" autofocus="autofocus" id = "email" name = "email" placeholder="Enter your email id here">
				</div>
                
                <button type="submit" class="btn btn-lg btn-default submit-align">Send Mail</button>
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
