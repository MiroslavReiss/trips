<?php
include("the_db.php");
include("util.php");

$rkey  = get_get_value("rkey");
$num   = intval(get_get_value("n"));
$nums = array( 1, 2, 10, 25, 50, 100, 250, 500, 1000 );
if ( ! in_array($num, $nums)) {
  $num = 1;
}

// check if userid exists
//
$lastseen = "{'foo':'bar'}";
$db = get_db();
//rkey is needed to read users position
$result = get_userid_from_rkey($db, $rkey);
if ( count($result) > 0 ) {
  $userid = $result[0];
  $result = get_last_point( $db, $userid, $num );
  //print_r($result);
  $lastseen = db_result_to_geojson( $result );
  //print_r(get_last_info( $db, $userid ));
}
echo $lastseen;
?>