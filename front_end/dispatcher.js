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

/*Upon receiving a message with driver information
  this function will determine the location of the
  driver and create a new marker to place on the map.*/
ws.onmessage = function(evt) {
  var recv_msg = evt.data;
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
          if (found === false || jobj.note != "active"){
            currentTaxiMarkers[i][0].setMap(null);
          }
          index = i;
          break
      }
    }
    if (found === true && jobj.note === "active"){
      currentTaxiMarkers[index][0].setIcon("resources/taxi_close.png")
      return;
    }
    else {
      var latlng = {lat: parseFloat(jobj.la), lng: parseFloat(jobj.lo)}
      var marker
      if (jobj.note === "busy"){
        marker = new google.maps.Marker({
        position: latlng,
        map: map,
        //animation: google.maps.Animation.DROP,
        icon:'resources/taxi_busy.png',
        title: jobj.name
        });
      }
      else if (jobj.note === "active"){
        marker = new google.maps.Marker({
        position: latlng,
        map: map,
        //animation: google.maps.Animation.DROP,
        icon:'resources/taxi.png',
        title: jobj.name
        });
      }
      else {
        marker = new google.maps.Marker({});
      }
      marker.addListener('click', function() {
        map.setCenter(marker.getPosition());
        $('#notificationBox').slideDown("slow");
        $('#address').val(oldMarker.title)
        $('#driver').text("To " + jobj.name)
        $('#message').focus();
        selectedEmail = jobj.email
      });
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
      $('#masterNote').clone().attr('id', naming.toString()).attr('class', 'notification').prependTo(notificationBar);
      $('#'+naming.toString()).children('.noteTitle').text(jobj.name + " Rejected Request");
      $('#'+naming.toString()).children('.noteText').text(jobj.addr);
      $('#'+naming.toString()).show();
      $('#notificationButton').css('background-color','red');
      naming += 1;
    }
    else
    {
      $('#masterNote').clone().attr('id', naming.toString()).attr('class', 'notificationAccept').prependTo(notificationBar);
      $('#'+naming.toString()).children('.noteTitle').text(jobj.name + " Accepted Request");
      $('#'+naming.toString()).children('.noteText').text(jobj.addr);
      $('#'+naming.toString()).children('.clickHere').text("");
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
