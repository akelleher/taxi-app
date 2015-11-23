var map
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
var distances = []
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
    center: new google.maps.LatLng(42.7300, -73.6775),
    zoom: 15,
    disableDefaultUI:true,
    mapTypeControlOptions: {
      mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'map_style']
    }
  };


  /*
    The google map class is what creates the map on the screen.
    It needs a map_canvas element where it paints the map, and
    it needs map_options given to it.

    Google 'Google maps javascript class' for more information.
  */
  map = new google.maps.Map(document.getElementById('mapCanvas'),
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

  var rad = function(x) {
    return x * Math.PI / 180;
  };

  var getDistance = function(p1, p2) {
    var R = 6378137; // Earth’s mean radius in meter
    var dLat = rad(p2.lat() - p1.lat());
    var dLong = rad(p2.lng() - p1.lng());
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(rad(p1.lat())) * Math.cos(rad(p2.lat())) *
      Math.sin(dLong / 2) * Math.sin(dLong / 2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    var d = R * c;
    return d; // returns the distance in meter
  };

  function calcDistances(latLng)
  {
    distances = []
    var max = 3
    if (currentTaxiMarkers.length < max)
    {
      max = currentTaxiMarkers.length
    }
    for (i = 0; i < max; i++)
    {
      distances.push([getDistance(currentTaxiMarkers[i][0].position, latLng),i])
      console.log(distances[0])
      console.log(currentTaxiMarkers.length)
    }
    distances.sort()
    for (i = 3; i < currentTaxiMarkers.length; i++)
    {
      var aDistance = getDistance(currentTaxiMarkers[i][0].position, latLng)
      for (j = 0; j < 3; j++)
      {
        if (aDistance < distances[j][0])
        {
          distances[j] = [aDistance,i]
          break
        }
      }
      distances.sort()
    }
  }
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
    calcDistances(event.latLng)
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
    console.log(place.geometry.location);
});

}

google.maps.event.addDomListener(window, 'load', initialize);
