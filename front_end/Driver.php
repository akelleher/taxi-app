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

    <SCRIPT SRC="map.js">
    </SCRIPT>
    <script type = "text/javascript">
      //---------these code run as soon as the page is loaded-------------------
      // (boolean)Variable judge if the system is Android
      var isAndroid = navigator.userAgent.indexOf("Android") > -1;
      var firstname = "<?php echo $_SESSION['user']->getFirstName(); ?>"
		  var lastname = "<?php echo $_SESSION['user']->getLastName(); ?>"
      var name = firstname + " " + lastname;
      var email = "<?php echo $_SESSION['user']->getEmail(); ?>"
      var status = "active";
      // start a websocket
      // IP address: 52.25.194.53:8787
      // port number: 8787
      url = 'ws://52.25.194.53:8787/chat-server';
      ws = new WebSocket( url );

      // send location every 5 seconds
      sendLatestCoord();

      //----------------------------------------------------------------------

      // websocket is event-driven
      // define the behavior when receiving message
      ws.onmessage = function(evt) {

        var recv_msg = evt.data;

    		var jobj = JSON.parse(recv_msg);

    		// pop out a dialog
    		// assume sender with email address: pomaj@rpi.edu
    		// assume answer is 'Y' (stands for Yes)
    		if(jobj.type == "notification") {

    			var display = "Address: " + jobj.addr + "\n\nNote: " + jobj.note;
    			var r = confirm(display);
    			if (r == true) {
    				ws.send("<response><reply>Y</reply><email>"+email+
            "</email></response>");
    			} else {
            ws.send("<response><reply>N</reply><email>"+email+
            "</email></response>");
    			}
    		}
      }

      ws.onclose = function() {
        //alert("Connection is Closed."); // comment this line if need
      }

      ws.onopen = function() {
        //alert("Connection is set up.") // comment this line if need
      }

      function sendLatestCoord() {

        if(navigator.geolocation) {

          // __warpper is a function
          // setInterval --> __warpper --> __send
          setInterval( __warpper, 5000);	// every 5 seconds
        }
      }

      function __warpper() {

        // getCurrentLocation()
        // is a callback function takes __send as parameter
        // after it finish it will throw an object into __send
        // recognized as "obj"
        // I HATE JAVASCRIPT
        navigator.geolocation.getCurrentPosition(__send);
      }

      function __send(obj) {

        //var obj = navigator.geolocation.getCurrentPosition();

        var data = "<driver>" + "<email>" + email + "</email>" +
            "<name>" + name + "</name>" +
            "<latitude>" + obj.coords.latitude + "</latitude>" +
            "<longitude>" + obj.coords.longitude + "</longitude>" +
            "<note>" + status + "</note>" +
            "</driver>";
        //alert(data);

        ws.send(data);
      }
    </script>

  </head>
  <body>
    <div id="mapCanvas"></div>
			<center>
				<input id = "driverAddressBar" class = "controls" type = "text" placeholder = "Search Address">
				<div id="bottom">
					<div class="bottomButton" id="pickedPassenger">Picked up Passenger</div>
					<div class="bottomButton" id="takeBreak">Take a Break</div>
          <a href="logout.php">
					  <div class="bottomButton" id="logoutButton">Logout</div>
          </a>
				</div>
			</center>
  </body>
  <script>
    $('#takeBreak').click(function()
    {
      if ($('#takeBreak').text() === "Take a Break"){
        status = "offline"
        $('#takeBreak').text("End Break");
      }
      else {
        status = "active"
        $('#takeBreak').text("Take a Break");
      }
      $('#pickedPassenger').slideToggle();
      $('#logoutButton').slideToggle();
    });
    $('#pickedPassenger').click(function()
    {
      if ($('#pickedPassenger').text() === "Picked up Passenger"){
        status = "busy"
        $('#pickedPassenger').text("Dropped off Passenger");
      }
      else {
        status = "active"
        $('#pickedPassenger').text("Picked up Passenger");
      }
      $('#takeBreak').slideToggle();
      $('#logoutButton').slideToggle();
    });
  </script>
</html>
