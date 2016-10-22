<?php
include("the_db.php");
include("util.php");

$rkey  = get_get_value("rkey");
$num   = intval(get_get_value("n"));
if ( $num <= 0 ) {
  $num = 8;
} else if ( $num > 100 ) {
  $num = 100;
}

// check if userid exists
//
$db = get_db();
//rkey is needed to read users position
$result = get_userid_from_rkey($db, $rkey);
if ( count($result) > 0 ) {
  $userid = $result[0];
  $dates = get_dates( $db, $userid, $num );
  //Array ( [0] => Array ( [d] => 2012-07-23 [0] => 2012-07-23 ) )
  //print_r($dates);
}

// Take last as starting point, or parameter.
$dt  = get_get_value("dt");
if ( $dt == "" ) {
  $dt = $dates[0]['d'];
}

$dts_menu = "<select id=\"choose_d\" onchange=\"choose_d();\">";
foreach( $dates as $d) {
  if ( $d['d'] == $dt ) {
    $dts_menu .= "<option selected='true' value=\"".$d['d']."\">".$d['d']."</option>";
  } else {
    $dts_menu .= "<option value=\"".$d['d']."\">".$d['d']."</option>";
  }
}
$dts_menu .= "</select>";
//print_r( $dts_menu );

$style = intval(get_get_value("s"));
if ( $style <= 0 ) {
  $style = 0;
} else if ( $style > 999999 ) {
  $style = 0;
}
$lat = 56;
$lon = 12;

//print_r(get_points_on_date( $db, $userid, $dt ));
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

	<!--<link rel="stylesheet" href="leaflet/dist/leaflet.css" />
	<!--[if lte IE 8]><link rel="stylesheet" href="leaflet/dist/leaflet.ie.css" /><![endif]-->
	<!--<script src="leaflet/dist/leaflet.js"></script>-->
	
  <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7/leaflet.css" />
  <!--[if lte IE 8]>
     <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7/leaflet.ie.css" />
  <![endif]-->
  <script src="http://cdn.leafletjs.com/leaflet-0.7/leaflet.js"></script>

	<script src="http://maps.google.com/maps/api/js?v=3&sensor=false"></script>
  <script src="js/Google.js"></script>
  
  <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
  <script type="text/javascript" src="js/geo.js"></script>
</head>
<script>
function choose_d() {
  var chosen_d = $("#choose_d option:selected");
  if ( chosen_d.val() != 0 ) {
    //bkid = bids_menu.attr("bid");
    //alert(chosen_n.val());
    dt = chosen_d.val();
    window.location.href="ondate.php?rkey=<?=$rkey?>&dt="+dt;
  }
}
	
$(document).ready(function() {
	var pline;
	var latlngs;

 	var map;
	var geojsonLayer;
	var accLayer;
	var geojsonMarkerOptions;
  var ans;

	ans = get_points(1); // 1 = init_map
});

function init_map(lat, lon, res) {
  map = new L.Map('map');

		//var cloudmadeUrl = 'http://{s}.tile.cloudmade.com/0590975ab4694e2fab9444e8166cd2ff/<?=$style?>/256/{z}/{x}/{y}.png',
<?php
if ( $style === 0 ) {
?>
		var cloudmadeUrl = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
<?php
} else {
?>
  var cloudmadeUrl = 'http://{s}.tile.cloudmade.com/0590975ab4694e2fab9444e8166cd2ff/<?=$style?>/256/{z}/{x}/{y}.png';
<?php
}
?>
		cloudmadeAttribution = 'Map data &copy; 2013 OpenStreetMap contributors, Imagery &copy; 2013 CloudMade',
		cloudmade = new L.TileLayer(cloudmadeUrl, {maxZoom: 19, attribution: cloudmadeAttribution});
		
		//https://gist.github.com/crofty/2197701
		gglr = new L.Google('ROADMAP');
		ggls = new L.Google('SATELLITE');
		gglh = new L.Google('HYBRID');
		gglt = new L.Google('TERRAIN');
			
		map.setView(new L.LatLng(lat,lon), 12).addLayer(cloudmade);
  	L.control.scale().addTo(map);
  	latlngs = new Array();

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
      latlngs.push( new L.LatLng(parseFloat(coords[1]),parseFloat(coords[0])) ); //NB REVERSED!

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
  }).addTo(map)

  //console.log(latlngs);
  //pline = new L.MultiPolyline(new Array(latlngs), {color: 'red', weight:3,opacity: 1});//.addTo(map);
  pline = new L.Polyline(latlngs, {color: 'red', weight:3,opacity: 1});//.addTo(map);

  // Draw a circle on the last-seen point.
  //L.circle([lat,lon], e.properties.acc).addTo(map);
  geojsonMarkerOptions = {
    radius: 8,
    fillColor: "#ff7800",
    color: "#000",
    weight: 1,
    opacity: 1,
    fillOpacity: 0.8
  };
	accLayer = L.geoJson( res,{
      pointToLayer: function (feature, latlng) {
      //console.log(feature.geometry.coordinates);    
      return new L.circle(latlng, feature.properties.acc);//feature.properties.acc);
      //return new L.circleMarker(latlng, {radius:feature.properties.acc});
  	}
  });//.addTo(map);

  var baseLayers = {
      "Cloudmade": cloudmade,
      'Google Rd':gglr,
      'Google Sat':ggls,
      'Google Hybrid':gglh,
      'Google Terrain':gglt
  };
  var extraLayers = {
      "Accuracy": accLayer ,
      "Lines": pline,
      "Arrows": geojsonLayer
  };
  /*var linesLayer = {
      "Lines": pline
  };*/
  lcl = L.control.layers(baseLayers,extraLayers).addTo(map);
  //lcl.addOverlay(linesLayer, "Lines");
  
  //map.removeLayer(accLayer);
  //console.log("pl="+pline.getLatLngs()
  
  var popup = L.popup();
  function onMapClick(e) {
    var coord = e.latlng;
    var p1 = new LatLon(parseFloat(coord.lat),parseFloat(coord.lng));
    var p2 = new LatLon(lat,lon);
    var dist = p1.distanceTo(p2);
    var brng = p2.bearingTo(p1);
    brng = Math.round(brng);
      popup
          .setLatLng(e.latlng)
          .setContent("Position: " + e.latlng.toString() + "<br/>Distance to last: "+dist+" km<br/> Bearing from last: "+brng )
          .openOn(map);
  }
  map.on('click', onMapClick);
}

function get_points(action) {
  var ans = new Object();
  $.getJSON('get_points_on_date_geojson.php', {'rkey':'<?=$rkey?>', 'dt':'<?=$dt?>'}, function(res) {
    ans.pts = res.pts;
    ans.last_idx = 0;//pts-1; //points came in reverse.
    if ( ans.pts == 0 ) {
      alert("This user has no points.");
      init_map(parseFloat("<?=$lat?>"),parseFloat("<?=$lon?>"));
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

    ans.info_str = "Last seen at <img style=\"vertical-align: middle;\" src='"+ans.icon_url+"'/>: "+ans.latlon+" (+/- "+ans.acc+" m) on "+ans.dt_local+" ("+ans.td_str+" ago).<br/>Speed: "+ans.speed.toFixed(1)+" km/h ("+ans.kts.toFixed(1)+" kts), bearing: "+ans.bearing.toFixed(0)+" degrees. Altitude: "+ans.alt.toFixed(0)+" m ("+ans.alt_ft.toFixed(0)+" ft). Distance from previous point: "+ans.dist_m.toFixed(0)+" m.";
    $("#info_str").html(ans.info_str);
    ans.res = res;

    if ( action == 1 ) {
      init_map(ans.lat,ans.lon,res);
    }
  }).fail(function() {
    console.log( "error" );
  }).done(function() {
    if (<?=$num?> > 1) {
      //map.invalidateSize(false);
      //map.fitBounds(geojsonLayer.getBounds());//better without?
    }
  }); 

  return ans;
}
</script>
<body>

	<div>
	<div id="map" style="width: 100%; height: 80%"></div>

	<div>
	<p class="info" id="info_str"></p>
	<p class="info">Showing points on: <?=$dts_menu?>   Back to <a href="lastseen.php?rkey=<?=$rkey?>">live</a> view.</p>
	</div>
	
	</div>
</body>
</html>
