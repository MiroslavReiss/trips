<?php
include("util.php");

$rkey  = get_get_value("rkey");
$num   = intval(get_get_value("n"));
$nums = array( 1, 2, 10, 25, 50 );
if ( ! in_array($num, $nums)) {
  $num = 1;
}
$num_menu = "<select id=\"choose_n\" onchange=\"choose_n();\">";
foreach( $nums as $n) {
  if ( $n == $num ) {
    $num_menu .= "<option selected='true' value=\"".$n."\">".$n."</option>";
  } else {
    $num_menu .= "<option value=\"".$n."\">".$n."</option>";
  }
}
$num_menu .= "</select>";
//print_r( $num_menu );

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
	<!--<script src="http://maps.google.com/maps/api/js?v=3.2&sensor=false"></script>
	<script src="js/shramov-leaflet-plugins/layer/tile/Google.js"></script>-->
  <script type="text/javascript" src="js/jquery-1.6.3.js"></script>
</head>
<body onload="init();">

	<div id="map" style="width: 100%; height: 80%"></div>

	<script>
function choose_n() {
  var chosen_n = $("#choose_n option:selected");
  if ( chosen_n.val() != 0 ) {
    //bkid = bids_menu.attr("bid");
    //alert(chosen_n.val());
    n = chosen_n.val();
    window.location.href="lastseen.php?rkey=<?=$rkey?>&n="+n;
  }
}
	var map;
	var geojsonLayer;
function init_map(lat, lon) {
    map = new L.Map('map');    
		var cloudmadeUrl = 'http://{s}.tile.cloudmade.com/0590975ab4694e2fab9444e8166cd2ff/<?=$style?>/256/{z}/{x}/{y}.png',
			cloudmadeAttribution = 'Map data &copy; 2012 OpenStreetMap contributors, Imagery &copy; 2012 CloudMade',
			cloudmade = new L.TileLayer(cloudmadeUrl, {maxZoom: 18, attribution: cloudmadeAttribution});

		map.setView(new L.LatLng(lat,lon), 12).addLayer(cloudmade);

  var LSIcon = L.Icon.extend({
			iconUrl: 'leaflet/dist/images/mapiconscollection-numbers/letter_a.png'
			/*shadowUrl: null,
			iconSize: new L.Point(32, 37),
			shadowSize: null,
			iconAnchor: new L.Point(14, 37),
			popupAnchor: new L.Point(2, -32)*/
		});
	
	/*
	jQuery.getJSON("url_of_your_geojson_file",
    function(data) {
        if (data){
            var geojson = new L.GeoJSON();
            geojson.on("featureparse", function(e){
                if (e.properties && e.properties.icon){
                    e.layer.setIcon(new L.Icon({
                        iconUrl: e.properties.icon,
                        iconSize: new L.Point( e.properties.icon_size[0], e.properties.icon_size[1] ),
                        iconAnchor: new L.Point( e.properties.icon_anchor[0], e.properties.icon_anchor[1] ),
                    }));
                }
                if (e.properties && e.properties.title){
                    e.layer.bindPopup(e.properties.title);
                }
            });
            map1.addLayer(geojson);
            geojson.addGeoJSON(data);
            
        }
    }
);

Demo page here :

http://labo.eliaz.fr/spip.php?page=carte
*/	
		geojsonLayer = new L.GeoJSON(null, {
		    pointToLayer_off: function (latlng){
		        return new L.CircleMarker(latlng, {
		            radius: 8,
		            fillColor: "#ff7800",
		            color: "#000",
		            weight: 1,
		            opacity: 1,
		            fillOpacity: 0.8
		        });
		    },
		    pointToLayer_0: function (latlng){
		        return new L.Marker(latlng, {
		            //icon: new LSIcon()
		        });
		    }
		});

		geojsonLayer.on("featureparse", function (e) {
		    var popupContent = "Point "+e.id+"<p>"+e.geometry.coordinates+"<br/>"+e.properties.dt_local+"<br/>accuracy: "+e.properties.acc+"<br/>speed: "+e.properties.speed+"<br/>bearing: "+e.properties.bearing+"<br/>altitude: "+e.properties.alt+"</p>";
		    //e.layer.setIcon(new L.Icon({iconUrl: 'leaflet/dist/images/mapiconscollection-numbers/number_0.png' }));
		    //e.layer.setIcon(new L.Icon({iconUrl: 'leaflet/dist/images/lwt_map_icons/blue/0.png' }));
		    e.layer.setIcon(new L.Icon({"iconUrl":e.properties.icon_url, iconAnchor: new L.Point(16,34)}));//, "zIndexOffset":e.id

        /*var circleLocation = new L.LatLng(51.508, -0.11),
            circleOptions = {
                color: 'red',
                fillColor: '#f03',
                fillOpacity: 0.5
            };
        
        var circle = new L.Circle(circleLocation, 500, circleOptions);
        map.addLayer(circle);*/

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
}
/* 
{"type":"FeatureCollection","features":[{"geometry":{"type":"Point","coordinates":[12.89471,56.3374]},"type":"Feature","properties":{"prop1":"test","acc":400,"speed":0,"bearing":0,"alt":89,"dt":"1342622401","dt_local":"2012-07-18 16:40:01","td":1133,"td_str":"18m 53s ","boe":"bah","icon_url":"leaflet\/dist\/images\/lwt_map_icons\/blue\/0.png"},"id":"1"}]}
*/
function update_map() {
  $.getJSON('get_last_geojson.php', {'rkey':'<?=$rkey?>', 'n':'<?=$num?>'}, function(res) {
    var pts = res.pts;
    var last_idx = 0;//pts-1;
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
        
    var info_str = "Last seen at: "+latlon+" (+/- "+acc+" m) on "+dt_local+" ("+td_str+" ago).<br/>Speed: "+speed+" km/h, bearing: "+bearing+" degrees.";
    $("#info_str").html(info_str);

    map.removeLayer(geojsonLayer);
    map.addLayer(geojsonLayer);
    geojsonLayer.addGeoJSON(res);
    //geojsonLayer.redraw();//
  });
}
function init() {
  $.getJSON('get_last_geojson.php', {'rkey':'<?=$rkey?>', 'n':'<?=$num?>'}, function(res) {
    var pts = res.pts;
    var last_idx = pts-1; //points came in reverse.
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
    
    var info_str = "Last seen at <img src='"+icon_url+"'/>: "+latlon+" (+/- "+acc+" m) on "+dt_local+" ("+td_str+" ago).<br/>Speed: "+speed+" km/h, bearing: "+bearing+" degrees.";
    $("#info_str").html(info_str);

    init_map(lat, lon);
    
    /*var ggl = new L.Google('ROADMAP');
    map.addLayer(ggl);//{type:'ROADMAP'}
    map.addControl(new L.Control.Layers( {'Cloud':cloudmade, 'Google':ggl}, {'marker':geojsonLayer}));*/

    geojsonLayer.addGeoJSON(res);
    //map.panTo(new L.LatLng(lat,lon));
  });
  
  /*window.setInterval(function() {update_map();}, 10000);*/
}
	</script>
	<p class="info" id="info_str"></p>
	<p class="info">Showing <?=$num_menu?> points.</p>
</body>
</html>
