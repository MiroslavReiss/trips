<?php
/*
  http://berck.se/trips/show_user.php?userid=f1a242745ed071207894f25ea30d18db
  http://localhost:8888/oderland/berck.se/trips/show_user.php?userid=ad70e0bfa78c7e021d32f5d429fee30e

  http://berck.se/trips/get_geojson.php?userid=f1a242745ed071207894f25ea30d18db&day=20110911
  
  Cloudmade APIkey: 0590975ab4694e2fab9444e8166cd2ff
  
  http://78.69.179.24:8080/trips/
  http://78.69.179.24:8080/trips/lastseen.php?rkey=31e9c3d694d960b1
*/

include("util.php");

print secs2str( 364 );
print "<pre>";
print_r( $_SERVER );
print "</pre>";
?>
