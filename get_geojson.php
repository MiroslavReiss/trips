<?php
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