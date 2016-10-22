<?php
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