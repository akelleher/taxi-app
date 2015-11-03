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

    <script>
		function initialize()
		{
        var styles = [
        {
          featureType: "road",
          elementType: "geometry",
          stylers: [
            { color: "#545454" },
            { visibility: "on" }
          ]
        },{
          featureType: 'landscape',
          elementType: 'all',
          stylers: [
            { color: '#000000' }
          ]
        },{
          featureType: "road",
          elementType: "labels",
          stylers: [
            { visibility: "on" }
          ]
        },{
          featureType: "poi",
          elementType: "all",
          stylers:[
            {visibility:"off"}
          ]
        },{
          featureType: "water",
          elementType: "all",
          stylers:[
            {visibility: "off"}
          ]
        },{
          featureType: "transit",
          elementType: "all",
          stylers:[
            {visibility: "off"}
          ]
        },{
          featureType: "all",
          elementType: "labels.text.fill",
          stylers:[
            {color:'#FFFFFF'}
          ]
        },{
          featureType: "all",
          elementType: "labels.text.stroke",
          stylers:[
            {color:'#00008B'}
          ]
        },{
          featureType: "administrative",
          elementType: "labels.text.stroke",
          stylers:[
            {color:'#297ACC'}
          ]
        },{
          featureType: "all",
          elementType: "labels",
          stylers:[
            {weight:'7'}
          ]
        }
      ];

      var styledMap = new google.maps.StyledMapType(styles,{name: "Styled Map"});
      var mapCanvas = document.getElementById('mapCanvas');
      var input = document.getElementById('addressBar');
      var searchBox = new google.maps.places.SearchBox(input);
			var mapOptions =
			{
			  center: new google.maps.LatLng(40.7577, -73.9857),
			  zoom: 15,
        disableDefaultUI:true,
        mapTypeControlOptions: {
          mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'map_style']
        }
			};

      var oldMarker = new google.maps.Marker({});
      var geocoder = new google.maps.Geocoder();
      var map = new google.maps.Map(document.getElementById('mapCanvas'),
      mapOptions);

      map.addListener('bounds_changed', function() {
        searchBox.setBounds(map.getBounds());
      });
      map.mapTypes.set('map_style', styledMap);
      map.setMapTypeId('map_style');
      google.maps.event.addListener(map, 'click', function(event) {
        oldMarker.setMap(null);
        var address
        geocoder.geocode({'location': event.latLng}, function(results, status) {
          if (status === google.maps.GeocoderStatus.OK) {
            if (results[0]) {
              address = results[0].formatted_address;
              var marker = new google.maps.Marker({
              position: event.latLng,
              map: map,
              //animation: google.maps.Animation.DROP,
              icon:'resources/customer.png',
              title: address
              });
              oldMarker = marker;
              map.panTo(event.latLng);
            } else {
              window.alert('No results found');
            }
          } else {
            window.alert('Geocoder failed due to: ' + status);
          }
        });
      });
      searchBox.addListener('places_changed', function() {
        var places = searchBox.getPlaces();
        if (places.length == 0) {
          return;
        }
        var place = places[0];
        oldMarker.setMap(null);
        var marker = new google.maps.Marker({
          position: place.geometry.location,
          map: map,
          animation: google.maps.Animation.DROP,
          icon:'resources/customer.png',
          title: place.name
        });
        // Clear out the old markers.
        oldMarker = marker;
        map.panTo(place.geometry.location);
        $('#addressBar:text').val('').focus();
    });

    }

		google.maps.event.addDomListener(window, 'load', initialize);


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
