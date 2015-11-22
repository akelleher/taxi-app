<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
require(SITE_ROOT . '/PHP/check_logged_in.php');
require(SITE_ROOT . '/PHP/Course.php');
require(SITE_ROOT . '/PHP/relations.php');

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
    <script>

      // start a websocket
      // IP address: a random remote host for testing. gonna move it to our server next week
      // port number: 8787
      url = 'ws://52.25.194.53:8787/chat-server';
      ws = new WebSocket( url );
      // websocket is event-driven
      // define the behavior when receiving message
      var currentTaxiMarkers = []
      ws.onmessage = function(evt) {
        var recv_msg = evt.data;
        var jobj = JSON.parse(recv_msg);
        var index = -1;
        for (i = 0; i < currentTaxiMarkers.length; i++)
        {
          if (currentTaxiMarkers[i][1].email === jobj.email)
          {
              currentTaxiMarkers[i][0].setMap(null);
              index = i;
          }
        }
        var latlng = {lat: parseFloat(jobj.la), lng: parseFloat(jobj.lo)}
        var marker = new google.maps.Marker({
        position: latlng,
        map: map,
        //animation: google.maps.Animation.DROP,
        icon:'resources/taxi.png',
        title: jobj.name
        });
        if (index >= 0)
        {
          currentTaxiMarkers[i] = [marker,jobj];
        }
        else
        {
          currentTaxiMarkers.push([marker,jobj]);
        }
      }

      ws.onclose = function() {
        alert("Connection is Closed.");		// comment this later
      }

      ws.onopen = function() {
        alert("Connection is set up.");		// comment this later
      }

      // function that use to get all the drivers' location
      function get_all_drivers() {
        ws.send("RETRIEVE_ALL");	// RETRIEVE_ALL is a command on server side
      }
      window.onload = function () { setInterval( get_all_drivers, 1000);}
    </script>
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
  </body>
</html>
