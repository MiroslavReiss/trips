<?php
include("the_db.php");
include("util.php");

//$userid  = get_get_value("userid"); // only writekey maybe
$wkey    = get_get_value("wkey"); // write key
$trackid = get_get_value("trackid"); // calculate self from datetime? insert trigger?
$lat     = get_get_value("lat");
$lon     = get_get_value("lon");
$acc     = get_get_value("acc");
$type    = get_get_value("type");
$speed   = get_get_value("speed");
$bearing = get_get_value("bearing");
$alt     = get_get_value("alt");
$dt      = get_get_value("dt");// unix epoch
$comment = get_get_value("comment");// unix epoch

if ( $dt == "" ) {
  $dt = time();
}

// check if userid exists
//
$db = get_db();
$userid = get_userid_from_wkey($db, $wkey);
$userid = $userid[0];
//print_r($userid);

if ( 1==1 ) {
  if ( ($lat != 0) and ( $lon != 0) ) {
    add_info( $db, $userid, $wkey, $lat, $lon, $acc, $speed, $bearing, $alt, $type, $dt, $trackid, $comment );
  } else {
    print "zero latlon.";
    add_info( $db, $userid, $wkey, $lat, $lon, $acc, $speed, $bearing, $alt, $type, $dt, $trackid, $comment );
  }
}
//print $userid." ".$wkey." ".$lat." ".$lon." ".$dt;
?>