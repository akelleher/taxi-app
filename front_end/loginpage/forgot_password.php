<script type="text/javascript">
  function myFunction() {
	var email = document.getElementById("email").value;
    var ok = true;
    if (email == '') {
        //alert("Passwords Do not match");
        //document.getElementById("pass").style.borderColor = "#E34234";
        //document.getElementById("confirm").style.borderColor = "#E34234";
		alert("Please enter value in email field");
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
				
				$sec_key = USER::get_random_string();
				if(USER::forgot_pass($_POST['email'], $sec_key)) {
					
					//header( 'Location: ' . SITE_URL . '/front_end/loginpage/new_password.php?email='.$_POST['email'] );
					
					$from = "Taxiping <noreply@taxiping.xyz>";
					$from_name = "Taxiping.xyz";
					$headers  = "From: $from\r\n";
					$headers .= "Reply-To: noreply@taxiping.xyz\r\n";
					$headers .= "Content-type: text/html\r\n";
					$to = $_POST['email'];
					$subject = "Taxiping : Forgot password";
				
					$link = 'http://taxiping.xyz/front_end/loginpage/reset_password.php?seckey='.$sec_key;
					$message = 'Hi,
					<br/><br/>
					Please Reset Your Password by clicking this link.
					<br/>
					<a href='.$link.'><h3>Click Here</h3></a>
					<br/>
					Thank you,
					<br/><br/>
					Your friends at Taxiping.xyz
					<br/> <br/>';
			
					@mail( $to, $subject, $message, $headers);
					//$mail_sent = @mail( $to, $subject, $message, $headers);
					
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
		<title>Forgot Password</title>
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
                  <label id = "forgot_password_text" for = "email" class = "label-align">An Email Will Be Sent To Your Email. Make Sure You Check Your Spam Folder. Please Enter your Email Id And Press Send Mail Button: </label>
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
