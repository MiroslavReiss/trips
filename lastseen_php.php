<?php
include("the_db.php");
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

// check if userid exists
//
$db = get_db();
//rkey is needed to read users position
$result = get_userid_from_rkey($db, $rkey);
if ( count($result) > 0 ) {
  $userid = $result[0];
  //$result = get_user($db, $userid);
  $result = get_last_point( $db, $userid, $num );
  $lat = $result[0]['lat'];
  $lon = $result[0]['lon'];
  $speed = $result[0]['speed']; // m/s
  $bearing = $result[0]['bearing'];
  $dt = $result[0]['gpstime'];
  $dt_local = $result[0]['dt_local'];
  $acc = $result[0]['acc'];
  $td = $result[0]['td'];
  $td_str = secs2str(intval($td));
  //print_r($result);
  $lastseen = db_result_to_geojson( $result );
  print "<!-- \n";
  print_r( $lastseen );  
  print "\n-->\n";
}
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
	<!--[if lte IE 8]><link rel="stylesheet" href="../dist/leaflet.ie.css" /><![endif]-->

	<script src="leaflet/dist/leaflet.js"></script>
	<!--<script src="../debug/leaflet-include.js"></script>-->
	<!--<script src="sample-geojson.js" type="text/javascript"></script>-->

</head>
<!-- body.onload is called once the page is loaded (call the 'init' function) -->
<body>

	<div id="map" style="width: 100%; height: 80%"></div>

	<script>
		var map = new L.Map('map');

		var cloudmadeUrl = 'http://{s}.tile.cloudmade.com/0590975ab4694e2fab9444e8166cd2ff/<?=$style?>/256/{z}/{x}/{y}.png',
			cloudmadeAttribution = 'Map data &copy; 2012 OpenStreetMap contributors, Imagery &copy; 2012 CloudMade',
			cloudmade = new L.TileLayer(cloudmadeUrl, {maxZoom: 18, attribution: cloudmadeAttribution});

		map.setView(new L.LatLng(<?=$lat?>,<?=$lon?>), 12).addLayer(cloudmade);

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
		    var popupContent = "<p>"+<?=$lat?>+","+<?=$lon?>+"<br/>"+e.properties.dt+"<br/>accuracy: "+e.properties.acc+"<br/>speed: "+e.properties.speed+"<br/>bearing: "+e.properties.bearing+"<br/>altitude: "+e.properties.alt+"</p>";
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

		geojsonLayer.addGeoJSON(<?=$lastseen?>);

	</script>
	<p class="info">
	Last seen at: <?=$lat?>, <?=$lon?> (+/- <?=$acc?> m) on <?=$dt_local?> (<?=$td_str?> ago).<br/>
	Speed: <?=$speed*3.6?> km/h, bearing: <?=$bearing?> degrees.
	</p>
</body>
</html>
