<?php
$rkey="9ad1b90098faf956";
$num=1;
/*
http://www.berck.se/trips/get_last_geojson.php?rkey=9ad1b90098faf956

//http://benel.net/blog/2013/04/21/show-gpx-on-map-with-leaflet.html
<script language="javascript">
      function init() {
      	 var map = L.map('map').setView([51.505, -0.09], 13);
      	  
      	 //add a tile layer to add to our map, in this case it's the 'standard' OpenStreetMap.org tile server
      	 L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
            maxZoom: 18
         }).addTo(map);

         map.attributionControl.setPrefix(''); // Don't show the 'Powered by Leaflet' text. Attribution overload

         var london = new L.LatLng(51.505, -0.09); // geographical point (longitude and latitude)
         map.setView(london, 13).addLayer(cloudmade);
      }
   </script>
  */
?> 
<!DOCTYPE html>
<html>
<head>
	<title>GJSON</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7/leaflet.css" />
  <!--[if lte IE 8]>
     <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7/leaflet.ie.css" />
  <![endif]-->
  <script src="http://cdn.leafletjs.com/leaflet-0.7/leaflet.js"></script>
  <script src="js/leaflet.ajax.js"></script>
  
  <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
  <script>
$(document).ready(function() {
  var ans = Object();
  var map;
  init_map();
  get_data();
});
function prepare_layer(res) {
  ans = Object();
  geojsonLayer = L.geoJson( res, {
  	// style for all vector layers (color, opacity, etc.), either function or object (optional)
  	style: function (feature) {
  		return feature.properties && feature.properties.style;
  	},
    pointToLayer: function (feature, latlng) {
		  return new L.Marker(latlng, {});
  	},	
  	// function that gets called on every created feature layer (optional)
  	onEachFeature: function (e, layer) { // "e" = "feature"
    	var popupContent = "Point "+e.id+"<p>"+e.geometry.coordinates+"<br/>"+e.properties.dt_local+"<br/>accuracy: "+e.properties.acc+"<br/>speed: "+e.properties.speed+"<br/>bearing: "+e.properties.bearing+"<br/>altitude: "+e.properties.alt+"</p>";
    	//console.log("maybe for a line segment: "+e.geometry.coordinates);
      coord = e.geometry.coordinates+"";//.split(",");
      coords = coord.split(",");
      // check dist, start new array if larger > x
      //console.log([coords[0],coords[1]]);
      //////latlngs.push( new L.LatLng(parseFloat(coords[1]),parseFloat(coords[0])) ); //NB REVERSED!
      layer.setIcon(new L.Icon({"iconUrl":e.properties.icon_url, iconSize: new L.Point(32,32), iconAnchor: new L.Point(16,16)}));
	    if (e.geometryType == "Point") {
	        popupContent += ""; //<p>boe bah "+e.properties.prop1+"</p>";
	    }
	    if (e.properties && e.properties.popupContent) {
	        popupContent += e.properties.popupContent;
	    }
	    layer.bindPopup(popupContent);
	    if (e.properties && e.properties.style && e.layer.setStyle) {
	        e.layer.setStyle(e.properties.style);
	    }
  	},//each
  	// function that decides whether to show a feature or not (optional)
  	filter: function (feature, layer) {
  		return !(feature.properties && feature.properties.isHidden);
  	}
  });
  ans.layer = geojsonLayer;
  ans.pts = 1;
  //features":[{"geometry":{"type":"Point","coordinates":[12.88687,56.24542]}
  //ans.coordinates=res.features[0].geometry.coordinates;
  ans.latlon = res.features[0].geometry.coordinates; //is is the latest one
  ans.lat = parseFloat(ans.latlon[1]);//res.features[0].geometry.coordinates[0];
  ans.lon = parseFloat(ans.latlon[0]);

  return ans;
}
function get_data() {
  $.getJSON('get_last_geojson.php', {'rkey':'<?=$rkey?>', 'n':'<?=$num?>'}, function(res) {
    console.log("get_points()="+res);
    if (res != false) {
      console.log(res);
      //ans.pts = res.pts;
      newLans = prepare_layer(res);
      newL = newLans.layer;
      newL.addTo(map);
      //map.fitBounds(newL.getBounds());
      map.panTo([ans.lat, ans.lon]);
      // layer.addGeolayson(res);
    }
  });
}
function init_map() {
	map = L.map('map').setView([56.2,12.9], 11);//56.2,12.9

	//add a tile layer to add to our map, in this case it's the 'standard' OpenStreetMap.org tile server
  L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
      maxZoom: 18
  }).addTo(map);
       
	var popup = L.popup();
	function onMapClick(e) {
		popup
			.setLatLng(e.latlng)
			.setContent("You clicked the map at " + e.latlng.toString())
			.openOn(map);
	}
	map.on('click', onMapClick);
}
</script>

</head>
<body>
<div id="map" style="width:1200px; height:600px;"></div>
</body>
</html>
