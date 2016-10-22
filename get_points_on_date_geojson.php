<?php
include("the_db.php");
include("util.php");

$rkey = get_get_value("rkey");
$dt   = get_get_value("dt");

// check if userid exists
//
$db = get_db();
//rkey is needed to read users position
$result = get_userid_from_rkey($db, $rkey);
if ( count($result) > 0 ) {
  $userid = $result[0];
  $result = get_points_on_date( $db, $userid, $dt );
  //print_r($result);
  $lastseen = db_result_to_geojson( $result );
}
//print_r($lastseen);

echo $lastseen;
?>