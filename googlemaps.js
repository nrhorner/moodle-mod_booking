
var geocoder;
var map;
var initlat = 50.82386;
var initlng = -0.13622;
var marker;


 
    


function initialize() {
  
   
    
  document.getElementById('id_lat').value = initlat;
  document.getElementById('id_lng').value = initlng;
  
  geocoder = new google.maps.Geocoder();
  var latlng = new google.maps.LatLng(initlat, initlng);
  var mapOptions = {
    zoom: 14,
    center: latlng,
    mapTypeId: google.maps.MapTypeId.ROADMAP
    
  }
  map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
  marker = new google.maps.Marker({
      position: latlng,
      map: map,
      title:"event here!",
      draggable:true
  });
        // adds a listener to the marker - to get new coords
        google.maps.event.addListener(marker, 'dragend', function(evt){
	document.getElementById('id_lat').value = evt.latLng.lat().toFixed(5);
	document.getElementById('id_lng').value = evt.latLng.lng().toFixed(5);       
});

}

function codeAddress() {
  //Query Google maps with address and set marker
  var address = document.getElementById('address').value;
  address += " Brighton"; //Bias, but not limit, results to our location
  
  geocoder.geocode( { 'address': address, region: 'GB'}, function(results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
      map.setCenter(results[0].geometry.location);
      //latlng = new google.maps.LatLng(results[0].geometry.location.lat, results[0].geometry.location.lng);
      marker.setPosition(results[0].geometry.location);
     
      var addTextArea = document.getElementById('id_address');
      var addFromGoogle = results[0].formatted_address;
      //addForDisplay.value = addFromGoogle.replace(/,\s/g, '\n');
      addTextArea.value = addFromGoogle;
      
      //Update coords
      document.getElementById('id_lat').value = results[0].geometry.location.lat().toFixed(5);
      document.getElementById('id_lng').value = results[0].geometry.location.lng().toFixed(5); 
    
    } else {
      alert('Could not get address from Google for the following reason: ' + status);
    }
  });
}

function addressKeyUp(e) {
    e.which = e.which || e.keyCode;
    if(e.which == 13) {
        codeAddress();
    }
}


function emailCheckBoxes(cbox){
    if (cbox.checked){
        document.getElementById('hidemail').style.display = 'block';    
    }
    else{
        document.getElementById('hidemail').style.display = 'none';        
    }
}

function bookPolCheckbox(cbox){
    if (cbox.checked){
        document.getElementById('hidebookpol').style.display = 'block';
    }
    else {
        document.getElementById('hidebookpol').style.display = 'none';
    }
}

google.maps.event.addDomListener(window, 'load', initialize);


   
    