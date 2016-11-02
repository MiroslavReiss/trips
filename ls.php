<?php
	$rkey = "0f4da395cf9af2b7";
	$num = 500;
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Leaflet debug page</title>

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.0.0-beta.2/leaflet.css" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.0.0-beta.2/leaflet.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="markercluster/screen.css" />

	<link rel="stylesheet" href="markercluster/dist/MarkerCluster.css" />
	<link rel="stylesheet" href="markercluster/dist/MarkerCluster.Default.css" />
	<script src="markercluster/dist/leaflet.markercluster-src.js"></script>
	<script src="markercluster/realworld.388.js"></script>

	<script src="https://code.jquery.com/jquery-3.1.1.min.js"
			  integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
			  crossorigin="anonymous"></script>
			  
</head>
<script>
  // Globals
	var liveupdate;// = 10*1000;//update freq
	var addressPoints = [];
	
function get_points() {
  ans = new Object();
  jQuery.getJSON('get_last_geojson.php', {'rkey':'<?=$rkey?>', 'n':'<?=$num?>'} ).done( function(res) {
	  console.log(res.pts);
	  console.log( res.features[0].geometry.coordinates );
	  for ( i=0; i < res.pts; i++ ) {
	  	addressPoints.push([res.features[i].geometry.coordinates[1], res.features[i].geometry.coordinates[0], "foo", res.features[i].properties.icon_url]);
	  }
	  
	var markers = L.markerClusterGroup({disableClusteringAtZoom:16,maxClusterRadius:32});
	for (var i = 0; i < addressPoints.length; i++) {
		var a = addressPoints[i];
		var title = a[2];
		var icon_url = a[3];
		var popupContent = "Point "+i+"<p>"+i+"<br/>";
		var the_icon = L.icon({
    	iconUrl: icon_url,
    	iconSize: new L.Point(32,32),
    	iconAnchor: new L.Point(16,16)
    });
		var marker = L.marker(new L.LatLng(a[0], a[1]), { title: popupContent, icon:the_icon });
		marker.bindPopup(popupContent);
		markers.addLayer(marker);
	}
	map.addLayer(markers);

	  });
	  /*
  
  function(res) {
    console.log("get_points("+action+")="+res);
    alert(res);
    if (res != false) {
      ans.pts = res.pts;
      ans.last_idx = 0;//pts-1; //points came in reverse.
      if ( ans.pts == 0 ) {
        alert("This user has no points.");
      }
      ans.latlon = res.features[ans.last_idx].geometry.coordinates;
      ans.lat = parseFloat(ans.latlon[1]);//res.features[0].geometry.coordinates[0];
      ans.lon = parseFloat(ans.latlon[0]);
      ans.acc = res.features[ans.last_idx].properties.acc;
      ans.speed = res.features[ans.last_idx].properties.speed;
      ans.kts = res.features[ans.last_idx].properties.kts;
      ans.bearing = res.features[ans.last_idx].properties.bearing;
      ans.alt = res.features[ans.last_idx].properties.alt;
      ans.alt_ft = res.features[ans.last_idx].properties.alt_ft;
      ans.dist_m = res.features[ans.last_idx].properties.dist_m;
      ans.td = res.features[ans.last_idx].properties.td;
      ans.td_str = res.features[ans.last_idx].properties.td_str;
      ans.dt = res.features[ans.last_idx].properties.dt;
      ans.dt_local = res.features[ans.last_idx].properties.dt_local;
      ans.pt_id = res.features[ans.last_idx].id;
      ans.icon_url = res.features[ans.last_idx].properties.icon_url;  
      ans.res = res;
			addressPoints.push([ans.lat, ans.lon, "foo"]);
    }
  });
  */

}

$(document).ready(function() {
	//alert("!");
	var saddressPoints = [
	[-37.8210922667, 175.2209316333, "2"]
	];
	get_points();
});

    
</script>
<body>

	<div id="map"></div>
	<span>500 points...</span>
	
	<script type="text/javascript">
		var tiles = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
				maxZoom: 18,
				attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors.'
			}),
			latlng = L.latLng(56.3, 12.9);

		var map = L.map('map', {center: latlng, zoom: 12, layers: [tiles]});

	</script>
</body>
</html>
