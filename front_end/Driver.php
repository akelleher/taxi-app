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
      /*
        Google map styles, to determine
        what the map looks like
      */
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

      /*
      	The google maps marker class is used to place markers on the map
        see google for more information.
      */
      var oldMarker = new google.maps.Marker({});

      /*
      	The geocoder is used to take an address look up
        the latitude and longitude of the location, or
        reverse-geocoding is taking the latitude and
        longitude and looking up the address that fits
        best... this is used for the click on map functionality
        since clicking on the map only provides the closest
        lat/long.

        Google 'geocoder' for more information about this class.
      */
      var geocoder = new google.maps.Geocoder();

      /*
      	The google map class is what creates the map on the screen.
        It needs a map_canvas element where it paints the map, and
        it needs map_options given to it.

        Google 'Google maps javascript class' for more information.
      */
      var map = new google.maps.Map(document.getElementById('mapCanvas'),
      mapOptions);

      /*
      	This function helps determine the bounds for the map
        and was recommended by the Google tutorial to be added in
        to improve search bar functionality.
      */
      map.addListener('bounds_changed', function() {
        searchBox.setBounds(map.getBounds());
      });

      map.mapTypes.set('map_style', styledMap);
      map.setMapTypeId('map_style');

      /*
      	This function listens for a click on the map, and then
           1. deletes the old marker off the map
           2. uses the geocoder to get the address from the lat/long
           of the click
      */
      google.maps.event.addListener(map, 'click', function(event) {
        oldMarker.setMap(null);
        var address

        /*
        	The geocode function
          1. Converts the lat/long to an address
          2. Creates the marker on the map with the address as the title
          and sets the oldmarker to the new marker
          3. Pans to that location
        */
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

      /*
      	This function waits for the search box to be changed.
          1. Picks only the first place if multiple places were
          returned.
          2. Deletes the old marker
          3. Creates a new marker and sets the old marker to it
          4. Pans to the location
      */
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

		//<a href="logout.php" id ="logout">Logout</a>
		</script>
  </head>
  <body>
    <div id="mapCanvas"></div>
			<center>
				<input id = "driverAddressBar" class = "controls" type = "text" placeholder = "Search Address">
				<div id="bottom">
					<div class="bottomButton">
						Picked up Passenger
					</div>
					<div class="bottomButton">
						Take a Break
					</div>
					<div class="bottomButton">
						Logout
					</div>
				</div>
			</center>
  </body>
</html>
