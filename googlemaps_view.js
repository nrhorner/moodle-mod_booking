
var geocoder;
var map;

function initialize() {
    
  var lat = document.getElementById('lat').value;
  var lng = document.getElementById('lng').value;
  
  geocoder = new google.maps.Geocoder();
  var latlng = new google.maps.LatLng(lat, lng);
  var mapOptions = {
    zoom: 14,
    center: latlng,
    mapTypeId: google.maps.MapTypeId.ROADMAP
    
  }
  map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
  var marker = new google.maps.Marker({
      position: latlng,
      map: map,
      title:"event here!"
  });
}

google.maps.event.addDomListener(window, 'load', initialize);  
    