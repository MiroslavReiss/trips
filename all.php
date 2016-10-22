<?php
//include("the_db.php");
include("util.php");

$rkey  = get_get_value("rkey");

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

	<div id="map" style="width: 100%; height: 80%"></div>

	<script>
	var map;
	var geojsonLayer;
	
function init_map(lat, lon, res) {
    map = new L.Map('map');
    
		var cloudmadeUrl = 'http://{s}.tile.cloudmade.com/0590975ab4694e2fab9444e8166cd2ff/<?=$style?>/256/{z}/{x}/{y}.png',
		//var cloudmadeUrl = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
			cloudmadeAttribution = 'Map data &copy; 2012 OpenStreetMap contributors, Imagery &copy; 2012 CloudMade',
			cloudmade = new L.TileLayer(cloudmadeUrl, {maxZoom: 18, attribution: cloudmadeAttribution});

		map.setView(new L.LatLng(lat,lon), 12).addLayer(cloudmade);
  	L.control.scale().addTo(map);
  	
	var geojsonLayer = L.geoJson(res, {
  
  	// style for all vector layers (color, opacity, etc.), either function or object (optional)
  	style: function (feature) {
  		return feature.properties && feature.properties.style;
  	},
  
  	// function for creating layers for GeoJSON point features (optional)
  	pointToLayer_off: function (feature, latlng) {
  		return L.marker(latlng, {
  			icon: properties.icon_url,
  			title: feature.properties && feature.properties.name
  		});
    },
    pointToLayer_off: function (feature, latlng){
		        return new L.CircleMarker(latlng, {
		            radius: 8,//feature.properties.acc,
		            fillColor: "#ff7800",
		            color: "#000",
		            weight: 1,
		            opacity: 1,
		            fillOpacity: 0.8
		        });
  	},
    pointToLayer_off2: function (feature, latlng){
		        return new L.Marker(latlng, {});
  	},
  	
  	// function that gets called on every created feature layer (optional)
  	onEachFeature: function (e, layer) { // "e" = "feature"
  	
  	var popupContent = "Point "+e.id+"<p>"+e.geometry.coordinates+"<br/>"+e.properties.dt_local+"<br/>accuracy: "+e.properties.acc+"<br/>speed: "+e.properties.speed+"<br/>bearing: "+e.properties.bearing+"<br/>altitude: "+e.properties.alt+"</p>";

        //size:18,30 offset:8,30 
		    //layer.setIcon(new L.Icon({"iconUrl":e.properties.icon_url, iconSize: new L.Point(18,30), iconAnchor: new L.Point(8,30)}));//, "zIndexOffset":e.id
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
		    
  	},
  
  	// function that decides whether to show a feature or not (optional)
  	filter: function (feature, layer) {
  		return !(feature.properties && feature.properties.isHidden);
  	}

  }).addTo(map);

  	/*var ggl = new L.Google('ROADMAP');
    map.addLayer(ggl);//{type:'ROADMAP'}
    map.addControl(new L.Control.Layers( {'Cloud':cloudmade, 'Google':ggl}, {'marker':geojsonLayer}));*/

  map.fitBounds(geojsonLayer.getBounds());

}
/* 
{"type":"FeatureCollection","features":[{"geometry":{"type":"Point","coordinates":[12.89471,56.3374]},"type":"Feature","properties":{"prop1":"test","acc":400,"speed":0,"bearing":0,"alt":89,"dt":"1342622401","dt_local":"2012-07-18 16:40:01","td":1133,"td_str":"18m 53s ","boe":"bah","icon_url":"leaflet\/dist\/images\/lwt_map_icons\/blue\/0.png"},"id":"1"}]}
*/
function init() {
  $.getJSON('get_all_geojson.php', {'rkey':'<?=$rkey?>', 'n':-1}, function(res) {
    var pts = res.pts;
    var last_idx = 0;//pts-1; //points came in reverse.
    //alert(pts);
    var latlon = res.features[last_idx].geometry.coordinates;
    var lat = parseFloat(latlon[1]);//res.features[0].geometry.coordinates[0];
    var lon = parseFloat(latlon[0]);
    var acc = res.features[last_idx].properties.acc;
    var speed = res.features[last_idx].properties.speed;
    var bearing = res.features[last_idx].properties.bearing;
    var alt = res.features[last_idx].properties.alt;
    var td = res.features[last_idx].properties.td;
    var td_str = res.features[last_idx].properties.td_str;
    var dt = res.features[last_idx].properties.dt;
    var dt_local = res.features[last_idx].properties.dt_local;
    var pt_id = res.features[last_idx].id;
    var icon_url = res.features[last_idx].properties.icon_url;
    
    var info_str = "Last seen at <img src='"+icon_url+"'/>: "+latlon+" (+/- "+acc+" m) on "+dt_local+" ("+td_str+" ago).<br/>Speed: "+speed+" km/h, bearing: "+bearing+" degrees. <a target='_blank' href='http://maps.google.com/maps?&q=loc:"+lat+","+lon+"'>View on Google Maps.</a>";
    $("#info_str").html(info_str);
    
    init_map(lat, lon, res);
    
    //geojsonLayer.addData(res);
    //map.panTo(new L.LatLng(lat,lon));
  });
  
  /*window.setInterval(function() {update_map();}, 10000);*/
}
	</script>
	<p class="info" id="info_str"></p>
</body>
</html>
