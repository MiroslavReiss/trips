<?php
include("the_db.php");
include("util.php");

$rkey  = get_get_value("rkey");

// check if userid exists
//
$lastseen = "{'foo':'bar'}";
$db = get_db();
//rkey is needed to read users position
$result = get_userid_from_rkey($db, $rkey);
if ( count($result) > 0 ) {
  $userid = $result[0];
  $result = get_all_points( $db, $userid );
  //print_r($result);
  $lastseen = db_result_to_geojson( $result );
}
//print_r($lastseen);

echo $lastseen;
?>