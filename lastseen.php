<?php
include("auth/user.php");
$USER = new User();
include("util.php");

$rkey  = get_get_value("rkey");
$num   = intval(get_get_value("n"));
$pt_str = "point";
$nums = array( 1, 2, 10, 25, 50, 100, 250, 500, 1000 );
if ( ! in_array($num, $nums)) {
  $num = 1;
}
if ( $num != 1 ) {
  $pt_str = "points";
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
  $style = 0;
} else if ( $style > 999999 ) {
  $style = 0;
}
$lat = 56.33836;
$lon = 12.89557;

$layer = get_get_value("l");
if ( $layer === "" ) {
  $layer = "clmd";
}
?>
<html>
<head>
<style type="text/css">
.info {
	font-size: 14px;
	line-height: 80%;
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

	<script src="http://maps.google.com/maps/api/js?v=3.2&sensor=false"></script>
  <script src="js/Google.js"></script>
  
  <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
  <script type="text/javascript" src="js/geo.js"></script>
</head>
<script>
  // Globals
	var liveupdate;// = 10*1000;//update freq
	var livetime;// = 10*60*1000; //total live tracking time
	var pline;
	var latlngs;
  var l = "<?=$layer?>";
	  
$(document).ready(function() {
 	var map;
	var geojsonLayer;
	var accLayer;
	var geojsonMarkerOptions;
  var ans;

	ans = get_points(1); // 1 = init_map
	//stop_live();
	$("#live_str").html("<button type=\"button\">Live tracking is off</button>");
	$("#live_str").off("click");
	$("#live_str").click(function() {
    start_live();
  });
  
  /*var p1 = new LatLon(51.5136, -0.0983);
  var p2 = new LatLon(51.4778, -0.0015);
  var dist = p1.distanceTo(p2);    
  alert(dist);*/
});
function choose_n() {
  var chosen_n = $("#choose_n option:selected");
  if ( chosen_n.val() != 0 ) {
    n = chosen_n.val();
    window.location.href="lastseen.php?rkey=<?=$rkey?>&n="+n+"&s="+<?=$style?>+"&l="+l;
  }
}
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
			cloudmade = new L.TileLayer(cloudmadeUrl, {maxZoom: 18, attribution: cloudmadeAttribution});
			cloudmade.name = "clmd";
			//https://gist.github.com/crofty/2197701
			gglr = new L.Google('ROADMAP');
			gglr.name = "gglr";
			ggls = new L.Google('SATELLITE');
			ggls.name = "ggls";
			gglh = new L.Google('HYBRID');
			gglh.name = "gglh";
			gglt = new L.Google('TERRAIN');
			gglt.name = "gglt";
			
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
    	/*
    	var polygon = L.polygon([
      [51.509, -0.08],
      [51.503, -0.06],
      [51.51, -0.047]
  ]).addTo(map);
  */        
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
  	},//each
  	// function that decides whether to show a feature or not (optional)
  	filter: function (feature, layer) {
  		return !(feature.properties && feature.properties.isHidden);
  	}
  }).addTo(map);

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
  var linesLayer = {
      "Lines": pline
  };
  lcl = L.control.layers(baseLayers,extraLayers).addTo(map);
  //lcl.addOverlay(linesLayer, "Lines");
  if (l != "clmd") {
    map.removeLayer(cloudmade);
    if ( l== "gglr") {
      map.addLayer(gglr);
    } else if ( l == "ggls") {
      map.addLayer(ggls);
    } else if ( l == "gglh") {
      map.addLayer(gglh);
    } else {
      map.addLayer(gglt);
      l = "gglt";
    }
  }
  //  var ll = e.geometry.coordinates;
	//  L.circle(ll, e.properties.acc).addTo(map);

  map.on('layeradd', function (el) {
    // Switch to the Population legend...
    /*if (eventLayer.name === 'Population') {
        this.removeControl(populationChangeLegend);
        populationLegend.addTo(this);
    } else { // Or switch to the Population Change legend...
        this.removeControl(populationLegend);
        populationChangeLegend.addTo(this);
    }*/
    console.log(el.layer.name);
    l = el.layer.name;
  });

  if (<?=$num?> > 1) {
    map.fitBounds(geojsonLayer.getBounds());
  }
  //map.removeLayer(accLayer);
  //console.log("pl="+pline.getLatLngs()
  var popup = L.popup();
  function onMapClick(e) {
    coord = e.latlng;
    var p1 = new LatLon(parseFloat(coord.lat),parseFloat(coord.lng));
    var p2 = new LatLon(ans.lat,ans.lon);
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
/* 
{"type":"FeatureCollection","features":[{"geometry":{"type":"Point","coordinates":[12.89471,56.3374]},"type":"Feature","properties":{"prop1":"test","acc":400,"speed":0,"bearing":0,"alt":89,"dt":"1342622401","dt_local":"2012-07-18 16:40:01","td":1133,"td_str":"18m 53s ","boe":"bah","icon_url":"leaflet\/dist\/images\/lwt_map_icons\/blue\/0.png"},"id":"1"}]}
*/
function start_live() {
  liveupdate = parseInt($("#live_str").attr("liveupdate"))*1000;
  livetime = parseInt($("#live_str").attr("livetime"))*1000;
  update();
	handle = setInterval("update()",liveupdate);
	to_handle = setInterval("stop_live()",livetime);
	//$("#live_str").html("Live tracking is on (every "+liveupdate/1000+"s)");
	$("#live_str").html("<button type=\"button\">Live tracking is on (every "+liveupdate/1000+"s)</button>")
	$("#live_str").off("click");
	$("#live_str").click(function() {
    stop_live();
  });
}
function update() {
  get_points(2);
  //$('#live_str').fadeOut(liveupdate, function() {
  $('#live_str').fadeTo(liveupdate, 0.1, function() {
    // Animation complete.
    $("#live_str").show();
    $("#live_str").css('opacity', 1);
  });

}
function stop_live() {
  clearTimeout(handle);
  clearTimeout(to_handle);
  $("#live_str").stop(1,1);
  $("#live_str").show();
  $("#live_str").html("<button type=\"button\">Live tracking is off</button>");
	$("#live_str").off("click");
	$("#live_str").click(function() {
    start_live();
  });
}

function get_last_stationary() {
  $.getJSON('get_last_stationary.php', {'rkey':'<?=$rkey?>'}, function(res) {
    console.log(res);
    var other_info_str = "";
    if ( res.dist == 0 ) {
      other_info_str = "Stationary for "+res.tdiff_str;
    }
    $("#stationary").html(other_info_str);
    //$("#info_str").append(other_info_str);
  });
}
 
function get_last_info() {
  $.getJSON('get_last_info.php', {'rkey':'<?=$rkey?>'}, function(res) {
    console.log(res);
    if ( parseInt(res.td) > 3600 ) {
      $("#other_info").html("");
      return;
    }
    if ( parseInt(res.type) == 100 ) {
      $("#other_info").html("Battery low");
    }
    if ( parseInt(res.type) == 200 ) {
      $("#other_info").html("Tracker disappeared");
    }
  });
}

function get_points(action) {
  ans = new Object();
  $.getJSON('get_last_geojson.php', {'rkey':'<?=$rkey?>', 'n':'<?=$num?>'}, function(res) {
    ans.pts = res.pts;
    ans.last_idx = 0;//pts-1; //points came in reverse.
    if ( ans.pts == 0 ) {
      alert("This user has no points.");
      init_map(<?=$lat?>,<?=$lon?>);
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
    if ( action == 2 ) {
      geojsonLayer.clearLayers();
      map.panTo(new L.LatLng(ans.lat,ans.lon));
      geojsonLayer.addData(res);
    }
  }); //.success(function() { alert("second success"); });
  //
  get_last_stationary(); //update stationary str
  get_last_info();
}
</script>
<body>

	<div>
	<div id="map" style="width: 100%; height: 80%"></div>

	<div class="info" id="other_info" style="float:right;margin-top:4px;""></div>
	<div>
	<?php /*$USER->header();*/?>
	<p class="info" id="info_str"></p>
	<p class="info">Showing <?=$num_menu?> <?=$pt_str?>. View the <a href="ondate.php?rkey=<?=$rkey?>">whole day</a></p>
	<p class="info" id="live_str" livetime="28800" liveupdate="60"><button type=\"button\">Live tracking is off</button></p>
	<p class="info" id="stationary"></p>
	</div>
	
	</div>
</body>
</html>
