<?php
include("util.php");

$rkey  = get_get_value("rkey");
$num   = intval(get_get_value("n"));
if ( $num <= 0 ) {
  $num = 1;
} else if ( $num > 100 ) {
  $num = 100;
}

$style = intval(get_get_value("s"));
if ( $style <= 0 ) {
  $style = 997;
} else if ( $style > 999999 ) {
  $style = 997;
}
$lat = 56;
$lon = 12;
?>
<html>
<head>
<style type="text/css">

.info {
	font-size: 14px;
	font-family: "Lucida Grande", Verdana, Arial, sans-serif;
}

</style>
	<title>Last Seen</title>
	
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="stylesheet" href="leaflet/dist/leaflet.css" />
	<!--[if lte IE 8]><link rel="stylesheet" href="leaflet/dist/leaflet.ie.css" /><![endif]-->

	<script src="leaflet/dist/leaflet.js"></script>
	<script src="http://maps.google.com/maps/api/js?v=3.2&sensor=false"></script>
	<script src="js/shramov-leaflet-plugins/layer/tile/Google.js"></script>
  <script type="text/javascript" src="js/jquery-1.6.3.js"></script>
</head>
<body onload="init();">

	<div id="map" style="width: 100%; height: 90%"></div>

	<script>
function init() {
  $.getJSON('get_last_geojson.php', {'rkey':'<?=$rkey?>', 'n':'<?=$num?>'}, function(res) {
    var map = new L.Map('map');
    var latlon = res.features[0].geometry.coordinates;
    var lat = parseFloat(latlon[1]);//res.features[0].geometry.coordinates[0];
    var lon = parseFloat(latlon[0]);
    var acc = res.features[0].properties.acc;
    var speed = res.features[0].properties.speed;
    var bearing = res.features[0].properties.bearing;
    var alt = res.features[0].properties.alt;
    var td = res.features[0].properties.td;
    var td_str = res.features[0].properties.td_str;
    var dt = res.features[0].properties.dt;
    var dt_local = res.features[0].properties.dt_local;
    
    var info_str = "Last seen at: "+latlon+" (+/- "+acc+" m) on "+dt_local+" ("+td_str+" ago).<br/>Speed: "+speed+" km/h, bearing: "+bearing+" degrees.";
    $("#info_str").html(info_str);
    
		var cloudmadeUrl = 'http://{s}.tile.cloudmade.com/0590975ab4694e2fab9444e8166cd2ff/<?=$style?>/256/{z}/{x}/{y}.png',
			cloudmadeAttribution = 'Map data &copy; 2012 OpenStreetMap contributors, Imagery &copy; 2012 CloudMade',
			cloudmade = new L.TileLayer(cloudmadeUrl, {maxZoom: 18, attribution: cloudmadeAttribution});

		map.setView(new L.LatLng(lat,lon), 12).addLayer(cloudmade);

		var geojsonLayer = new L.GeoJSON(null, {
		    pointToLayer_off: function (latlng){
		        return new L.CircleMarker(latlng, {
		            radius: 8,
		            fillColor: "#ff7800",
		            color: "#000",
		            weight: 1,
		            opacity: 1,
		            fillOpacity: 0.8
		        });
		    }
		});

		geojsonLayer.on("featureparse", function (e) {
		    var popupContent = "<p>"+e.geometry.coordinates+"<br/>"+e.properties.dt+"<br/>accuracy: "+e.properties.acc+"<br/>speed: "+e.properties.speed+"<br/>bearing: "+e.properties.bearing+"<br/>altitude: "+e.properties.alt+"</p>";
		    if (e.geometryType == "Point") {
		        popupContent += ""; //<p>boe bah "+e.properties.prop1+"</p>";
		    }
		    if (e.properties && e.properties.popupContent) {
		        popupContent += e.properties.popupContent;
		    }
		    e.layer.bindPopup(popupContent);
		    if (e.properties && e.properties.style && e.layer.setStyle) {
		        e.layer.setStyle(e.properties.style);
		    }
		});

		map.addLayer(geojsonLayer);

    /*var ggl = new L.Google('ROADMAP');
    map.addLayer(ggl);//{type:'ROADMAP'}
    map.addControl(new L.Control.Layers( {'Cloud':cloudmade, 'Google':ggl}, {'marker':geojsonLayer}));*/

    geojsonLayer.addGeoJSON(res);
  });
}
	</script>
	<p class="info" id="info_str"></p>
</body>
</html>
