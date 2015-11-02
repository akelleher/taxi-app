<?php
require_once(dirname(dirname(__FILE__)) . '/config.php');
require(SITE_ROOT . '/php/check_logged_in.php');
require(SITE_ROOT . '/PHP/Course.php');
require(SITE_ROOT . '/PHP/relations.php');

$courses = $_SESSION['user']->getDispatcherCourses();
$Dispatcherhours = $_SESSION['user']->getDispatcherOfficeHours();

if( isset($_POST['form']) ) {
	switch ($_POST['form']) {
		case 'AddDispatcherOfficeHours':
			try {
				list($subj, $crse) = split('-', $_POST['course']);
				$_SESSION['user']->addDispatcherOfficeHours($subj, intval($crse), $_POST['week_day'], $_POST['startTime'], $_POST['endTime']);
			}
			catch( Exception $e ) {
			}
			break;

		case 'DeleteDispatcherOfficeHours':
			list( $subj, $crse, $week_day ) = split( ' ', $_POST['Dispatcher_hours'] );
			try {
				$_SESSION['user']->removeDispatcherOfficeHours( $subj, intval($crse), $week_day );
			}
			catch( Exception $e ) {
			}
			break;
	}
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>

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
      var map_canvas = document.getElementById('map_canvas');
			var mapOptions =
			{
			  center: new google.maps.LatLng(40.7577, -73.9857),
			  zoom: 15,
        disableDefaultUI:true,
        mapTypeControlOptions: {
          mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'map_style']
        }
			}

      var map = new google.maps.Map(document.getElementById('map_canvas'),
      mapOptions);
      map.mapTypes.set('map_style', styledMap);
      map.setMapTypeId('map_style');
      google.maps.event.addListener(map, 'click', function(event) {
        placeMarker(event.latLng);
      });
      var oldMarker = new google.maps.Marker({});
      var geocoder = new google.maps.Geocoder();
      function placeMarker(location) {
        oldMarker.setMap(null);
        var address
        geocoder.geocode({'location': location}, function(results, status) {
          if (status === google.maps.GeocoderStatus.OK) {
            if (results[0]) {
              address = results[0].formatted_address;
              var marker = new google.maps.Marker({
              position: location,
              map: map,
              //animation: google.maps.Animation.DROP,
              icon:'resources/taxi.png',
              title: address
              });
              oldMarker = marker;
              map.panTo(location);
            } else {
              window.alert('No results found');
            }
          } else {
            window.alert('Geocoder failed due to: ' + status);
          }
        });
      }
    }

		google.maps.event.addDomListener(window, 'load', initialize);


		</script>
  </head>
  <body>
    <div id="map_canvas">
      <div class = "row vertical-align">
        <div class = "col-xs-12 col-sm-9">
          <center>
          </center>
        </div>
        <div class = "col-xs-0 col-sm-3">
        </div>
      </div>
    </div>
  </body>
</html>