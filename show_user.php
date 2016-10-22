<?php
/*
  http://berck.se/trips/show_user.php?userid=f1a242745ed071207894f25ea30d18db
  http://localhost:8888/oderland/berck.se/trips/show_user.php?userid=ad70e0bfa78c7e021d32f5d429fee30e
*/
include("the_db.php");
include("util.php");

$userid  = get_get_value("userid");

// check if userid exists
//
$db = get_db();
$result = get_userid($db, $userid);

if ( count($result) > 0 ) {
  $result = get_user($db, $userid);
  print_r($result);
  
  $result = get_points( $db, $userid, "20110912" );
  print_r($result);
  print_r( db_result_to_geojson( $result ) );

}
?>