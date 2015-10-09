<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(SITE_ROOT . '../PHP/User.php');
require_once(SITE_ROOT . '../PHP/Course.php');

session_start();
if( isset($_SESSION) && isset($_SESSION['user']) ) {
	$t = $_SESSION['user']->getIsAdmin();
	if ($t == true) {
		header( 'Location: ' . SITE_URL . '/front_end/Admin.php' ) ;
	}

	$t = $_SESSION['user']->getIsTA();
	if ($t == true) {
		header( 'Location: ' . SITE_URL . '/front_end/TA.php' ) ;
	}

	$t = $_SESSION['user']->getIsStudent();
	if ($t == true) {
		header( 'Location: ' . SITE_URL . '/front_end/student.php') ;
	}
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Inah Nail Art Salon is the place for beautiful nail art, massages, waxing, eyelash extensions, permanent makeup and more!">

    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="login.css">
  </head>
  <body>
    <div class = "container" id = "outer_container">
      <div class = "row vertical-align">
        <div class = "col-xs-0 col-sm-3">
        </div>
        <div class = "col-xs-12 col-sm-6">
          <center>
            <div id = "login">
              <form role = "form" class="loginForm">
                <div class = "form-group">
                  <label for = "email" class = "label-align">Email Address:</label>
                  <input type = "email" class = "form-control" autofocus="autofocus" id = "email" placeholder="E-mail Address">
                </div>
                <div class = "form-group">
                  <label for = "pwd" class = "label-align">Password:</label>
                  <input type = "password" class = "form-control" id = "pass" placeholder="Password">
                </div>
                <button type="button" class="btn btn-link link-align" href="forgot_password.html" id="forgotPassword">Forgot Password</button>
                <button type="submit" class="btn btn-lg btn-default submit-align">Login</button>
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

	<script src="http://code.jquery.com/jquery-1.11.1.js"></script>
	<script type="text/javascript">
		//move to seperate js later
		$('.loginForm').submit(function (event) {
			event.preventDefault();
			console.log("logging in...");
			var user = $('#email').val();
			var pass = $('#pass').val();

			$('input').removeClass('error');
			$('#login_error').empty();

			if (!user || !pass) {
				//highlight fields that aren't completed
				if (!user) {
					$('#email').addClass('error');
				}
				if (!pass) {
					$('#pass').addClass('error');
				}
				$('#login_error').html("Field(s) blank");
				console.log("error");
			}
			else {

				$.ajax({
	                url: "<?php echo SITE_URL; ?>/front_end/loginpage/validate_login.php",
	                data: { 'login': '1', 'user': user, 'pass': pass },
	                method: 'POST',
	                success: function (data) {
	                    data = $.trim(data);

	                    console.log("data: " + data);

	                    /*
	                      data is codes sent by validate_login.php
	                      1 is success
	                      0 is login error
	                    */

	                    if (data === "0") {
	                    	console.log("can't log in");
	                        $('#login_error').html("User name and/or password invalid");
	                    }
						else {
	                    	console.log("login successful")
	                        document.location.href = '<?php echo SITE_URL; ?>/front_end/' + data;
						}

	                },
	                error: function () {
	                    console.log("error");
	                }
	            });

			}
		});
	</script>
</html>
