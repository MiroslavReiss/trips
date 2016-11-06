<?php
/*
  TRACKID can be a part of a date:
  20110912   per day   %Y%m%d
  2011091213 per hour  %H (%M)
  201109     per month SELECT strftime('%Y%m','now'); where now = point dt
  
  timezones? grouping?
  choose a %s string from a roll menu, then site groups accordingly,
    take all the datetime from the points, apply the %s string &c.
    for a trackdisplay: ..&group=201102&format="YYYYMM" ? Or a list
    for all points, grouped, then click on one.
       20110213
       20110215
       ...
  sqlite> select *,strftime("%Y%m%d", datetime) as grp from points where grp = "20110912";
  
  BATCH upload...
  
  http://berck.se/trips/show_user.php?userid=f1a242745ed071207894f25ea30d18db
*/
include("the_db.php");
include("util.php");

//$userid  = get_get_value("userid"); // only writekey maybe
$wkey    = get_get_value("wkey"); // write key
$trackid = get_get_value("trackid"); // calculate self from datetime? insert trigger?
$lat     = get_get_value("lat");
$lon     = get_get_value("lon");
$acc     = get_get_value("acc");
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
  if ( ($lat != 0) and ( $lon != 0) ) { #0,0 should be ok...use 999,999?
	  if ( $wkey === "e176e1487d5834a0" ) { // fake_python
		  add_pt( $db, $userid, $wkey, $lat, $lon, $acc, $speed, $bearing, $alt, $dt, $trackid, $comment );
		} else {
	    add_pt( $db, $userid, $wkey, $lat, $lon, $acc, $speed, $bearing, $alt, $dt, $trackid, $comment );
	  }
  } else {
    print "zero latlon.";
  }
}
//print $userid." ".$wkey." ".$lat." ".$lon." ".$dt;
?>