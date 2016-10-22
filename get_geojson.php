<?php
/*
  http://berck.se/trips/show_user.php?userid=f1a242745ed071207894f25ea30d18db
  http://localhost:8888/oderland/berck.se/trips/show_user.php?userid=ad70e0bfa78c7e021d32f5d429fee30e

  http://berck.se/trips/get_geojson.php?userid=f1a242745ed071207894f25ea30d18db&day=20110911
  
  Cloudmade APIkey: 0590975ab4694e2fab9444e8166cd2ff
  
  http://78.69.179.24:8080/trips/
*/
include("the_db.php");
include("util.php");

$userid  = get_get_value("userid");
$day     = get_get_value("day");
$key     = get_get_value("key");
$val     = get_get_value("val");

// check if userid exists
//
$db = get_db();
$result = get_userid($db, $userid);
if ( count($result) > 0 ) {
  $result = get_user($db, $userid);
  
  if ( $key === "day" ) {
    $result = get_points( $db, $userid, $val );
    print_r( db_result_to_geojson( $result ) );
  } else if ( $key === "last" ) {
    $result = get_last_point( $db, $userid );
    print_r( db_result_to_geojson( $result ) );  
  }
}
?>