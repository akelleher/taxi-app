<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
require(SITE_ROOT . '/PHP/check_logged_in.php');

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://maps.googleapis.com/maps/api/js?sensor=false&libraries=places"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="map.css">

    <script type="text/javascript" src="map.js"></script>
    <script type="text/javascript" src="dispatcher.js"></script>
  </head>
  <body>
    <div id="mapCanvas"></div>
    <div id = "topRight">
			<input id = "addressBar" class = "controls" type = "text" placeholder = "Search Address" autofocus="autofocus">
			<a href="logout.php" id ="logout">Logout</a>
      <?php	$firstname = $_SESSION['user']->getFirstName();
						$lastname = $_SESSION['user']->getLastName();
						echo "<div id= 'name'>" . $firstname . "</div>";
			?>
    </div>
      <div id= "notificationBar">
        <div class="notification" id = "masterNote">
          <div class="noteTitle"></div>
          <div class="noteText"></div>
          <div class="clickHere">Click Here to Pick Again</div>
        </div>
      </div>
    <div id="notificationButton">
    </div>
    <div id = "notificationBox">
      <form id="sendForm">
          <label for = "message" class = "notiLabel">Message:</label>
          <input type = "text" autofocus="autofocus" id = "message" class = "notiText" autocomplete="off">
          <label for = "address" class = "notiLabel">Address:</label>
          <input type = "text" id = "address" class = "notiText" autocomplete="off">
          <label for = "sendNotification" id = "driver" class = "notiLabel"></label>
        <button type="submit" id = "sendNotification">Send</button>
      </form>
    </div>
  </body>
  <script>
    $('#sendForm').submit(function (event) {
      event.preventDefault();
      $('#notificationBox').slideUp("slow");
      var data = "<notify>" + "<email>" + selectedEmail + "</email>" +
          "<addr>" + $('#address').val() + "</addr>" +
          "<note>" + $('#message').val() + " " + "</note>" +
          "</notify>";
      //alert(data);
      console.log(data)
      ws.send(data);
      $('#message').val("")
    });
    $("#notificationBar").hide();
    $("#notificationButton").click(function(){
      if (!$('#notificationBar').is(":visible")){
          $("#notificationBar").show();
      }
      else {
        $("#notificationBar").hide();
      }
      $("#notificationButton").css("background-color","white");
    });
    $('#notificationBar').on('click', '.notification', function(){
      $('#addressBar').val($(this).children('.noteText').text());
      $("#notificationBar").hide();
      $(this).remove();
      $('#addressBar').focus();
    });
    $('#masterNote').hide()
  </script>
</html>
