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
} else {
  die("No points to show!");
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

  <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
  <script type="text/javascript" src="js/geo.js"></script>

	<script src="http://maps.google.com/maps/api/js?v=3.2&sensor=false"></script>
  <script src="js/Google.js"></script>
  <!-- Leaflet Plugins -->
  <script src="js/leaflet-providers.js" type="text/javascript"></script>
</head>
<script>
function choose_d() {
  var chosen_d = $("#choose_d option:selected");
  if ( chosen_d.val() != 0 ) {
    //bkid = bids_menu.attr("bid");
    //alert(chosen_n.val());
    dt = chosen_d.val();
    window.location.href="ondate2.php?rkey=<?=$rkey?>&dt="+dt;
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
  console.log("res");console.log(res);
  
  map = new L.Map('map');

		var cloudmadeUrl = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
		cloudmadeAttribution = 'Map data &copy; 2013 OpenStreetMap contributors, Imagery &copy; 2013 CloudMade',
		cloudmade = new L.TileLayer(cloudmadeUrl, {maxZoom: 19, attribution: cloudmadeAttribution});
		
		//https://gist.github.com/crofty/2197701
		var gglr = new L.Google( 'ROADMAP' );
		var ggls = new L.Google( 'SATELLITE' );
		var gglh = new L.Google( 'HYBRID' );
		var gglt = new L.Google('TERRAIN');
			
		map.setView(new L.LatLng(lat,lon), 12);//.addLayer(cloudmade);
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

  var baseLayers = [
      'OpenStreetMap.Mapnik',
			'OpenStreetMap.BlackAndWhite',
			'OpenStreetMap.DE',
			'Thunderforest.Landscape',
			'MapQuestOpen.OSM',
			'MapQuestOpen.Aerial',
			'OpenMapSurfer.Roads',
			'Esri.WorldStreetMap',
			'Esri.DeLorme',
			'Esri.WorldTopoMap',
			'Esri.WorldImagery',
			'Esri.WorldTerrain',
			'Esri.WorldShadedRelief',
			'Esri.NatGeoWorldMap'//,
      //'Google Rd':gglr,
      //'Google Sat':ggls,
      //'Google Hybrid':gglh,
      //'Google Terrain':gglt
  ];
  var extraLayers = {
      "Accuracy": accLayer ,
      "Lines": pline,
      "Arrows": geojsonLayer
  };
  
		//L.control.layers.provided(baseLayers, overlayLayers, {collapsed: true}).addTo(map);
    lcl = L.control.layers.provided(baseLayers, extraLayers, {collapsed: true});
    lcl.addBaseLayer(gglr, 'Google Road');
    lcl.addBaseLayer(ggls, 'Google Satellite');
    lcl.addBaseLayer(gglh, 'Google Hybrid');
    lcl.addBaseLayer(gglt, 'Google Terrain');
    map.addControl(lcl);
      
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
    console.log(res.features);
    allpts = [];
    total_dist = 0;
    for (var i = 0; i < res.features.length; i++) {
      //console.log(res.features[i].geometry.coordinates);
      ll = new L.LatLng(res.features[i].geometry.coordinates[0], res.features[i].geometry.coordinates[1]);
      if ( i < res.features.length-1 ) { // because dist_m is dist from previous, we ignore the last (which is first)
	      total_dist += res.features[i].properties.dist_m;
	    }
      allpts.push(ll);
    }
    console.log("total_dist="+total_dist);
    ans.pts = res.pts;
    ans.last_idx = 0;//pts-1; //points came in reverse.
    if ( ans.pts == 0 ) {
      alert("This user has no points.");
      init_map(parseFloat("<?=$lat?>"),parseFloat("<?=$lon?>"));
    }
    ans.latlon = res.features[ans.last_idx].geometry.coordinates;
    ans.lat = parseFloat(ans.latlon[1]);//res.features[0].geometry.coordinates[0];
    ans.lon = parseFloat(ans.latlon[0]);
    allpts.push(ans.latlon);
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
		
		total_dist_str = "(total dist: "+total_dist.toFixed(0)+" m";
		if ( total_dist > 10000 ) {
			total_dist_str = "(total dist: "+(total_dist/1000.0).toFixed(1)+" km";
		}
		total_dist_str += ", "+(res.features.length).toFixed(0)+" points).";
		
    ans.info_str = "Last seen at <img style=\"vertical-align: middle;\" src='"+ans.icon_url+"'/>: "+ans.latlon+" (+/- "+ans.acc+" m) on "+ans.dt_local+" ("+ans.td_str+" ago).<br/>Speed: "+ans.speed.toFixed(1)+" km/h ("+ans.kts.toFixed(1)+" kts), bearing: "+ans.bearing.toFixed(0)+" degrees. Altitude: "+ans.alt.toFixed(0)+" m ("+ans.alt_ft.toFixed(0)+" ft). Distance from previous point: "+ans.dist_m.toFixed(0)+" m. "+total_dist_str;
    $("#info_str").html(ans.info_str);
    ans.res = res;

    if ( action == 1 ) {
      init_map(ans.lat,ans.lon,res);
    }
  }).fail(function() {
    console.log( "error" );
    alert("No points to show.");
  }).success(function() {
      console.log("success");
      map.invalidateSize(false);
      bnds = geojsonLayer.getBounds();
      console.log(bnds);      
      map.fitBounds(bnds); //geojsonLayer.getBounds());//better without?
  }); 

  return ans;
}
</script>
<body>

	<div>
	<div id="map" style="width: 100%; height: 80%"></div>

	<div>
	<p class="info" id="info_str"></p>
	<p class="info">Showing points on: <?=$dts_menu?>   Back to <a href="lastseen2.php?rkey=<?=$rkey?>">live</a> view.</p>
	</div>
	
	</div>
</body>
</html>
