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
      var selectedEmail = ""
      var currentTaxiMarkers = []
      var naming = 0;
      ws.onmessage = function(evt) {
        var recv_msg = evt.data;
        console.log(recv_msg)
        var jobj = JSON.parse(recv_msg);
        if (jobj.type === "driver_coordination")
        {
          var index = -1;
          var found = false;
          for (i = 0; i < currentTaxiMarkers.length; i++)
          {
            if (currentTaxiMarkers[i][1].email === jobj.email){
                for (j = 0; j < distances.length; j++){
                  if (distances[j][1] === i){
                    found = true
                    break
                  }
                }
                if (found === false){
                  currentTaxiMarkers[i][0].setMap(null);
                }
                index = i;
                break
            }
          }
          if (found === true){
            currentTaxiMarkers[i][0].setIcon("resources/taxi_close.png")
          }
          else {
            var latlng = {lat: parseFloat(jobj.la), lng: parseFloat(jobj.lo)}
            var marker
            if (jobj.status === "busy")
            {
              marker = new google.maps.Marker({
              position: latlng,
              map: map,
              //animation: google.maps.Animation.DROP,
              icon:'resources/taxi_busy.png',
              title: jobj.name
              });
            }
            else {
              marker = new google.maps.Marker({
              position: latlng,
              map: map,
              //animation: google.maps.Animation.DROP,
              icon:'resources/taxi.png',
              title: jobj.name
              });
              marker.addListener('click', function() {
                map.setCenter(marker.getPosition());
                $('#notificationBox').slideDown("slow");
                $('#address').val(oldMarker.title)
                $('#driver').text("To " + jobj.name)
                $('#message').focus();
                selectedEmail = jobj.email
              });
            }

            if (index >= 0)
            {
              currentTaxiMarkers[index] = [marker,jobj];
            }
            else
            {
              currentTaxiMarkers.push([marker,jobj]);
            }
          }
        }
        else if (jobj.type === "reply_notification")
        {
          if (jobj.reply === "N")
          {
            $('#masterNote').clone().attr('id', naming.toString()).prependTo(notificationBar);
            $('#'+naming.toString()).children('#noteTitle').text(jobj.name + " Rejected Ride");
            $('#'+naming.toString()).children('#noteText').text(jobj.addr);
            $('#'+naming.toString()).show();
            naming += 1;
          }
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
      window.onload = function () {
        setInterval( get_all_drivers, 1000);
      }





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
    <div id= "notificationBar">
      <center>
        <div class="notification" id = "masterNote">
          <div class="noteTitle"></div>
          <div class="noteText"></div>
        </div>
      </center>
    </div>
    <div id="notificationButton">
    </div>
    <div id = "notificationBox">
      <form id="sendForm">
          <label for = "message" class = "notiLabel">Message:</label>
          <input type = "text" autofocus="autofocus" id = "message" class = "notiText">
          <label for = "address" class = "notiLabel">Address:</label>
          <input type = "text" id = "address" class = "notiText">
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
          "<note>" + $('#message').val() + "</note>" +
          "</notify>";
      //alert(data);
      console.log(data)
      ws.send(data);
    });
    $("#notificationBar").hide();
    $("#notificationButton").click(function(){
      if (!$('#notificationBar').is(":visible")){
          $("#notificationBar").show();
      }
      else {
        $("#notificationBar").hide();
      }
      $("#notificationBar").css("background-color","white");
    });
    $(".notification").click(function(){
      $('#addressBar').val($(this).children('.noteText').text());
      $("#notificationBar").hide();
      $(this).remove();
    });
    $('#masterNote').hide()
  </script>
</html>
