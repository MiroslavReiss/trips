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
  
*/
include("the_db.php");
include("util.php");

#$wkey = "9981dfe16d098a7a";
$wkey = "95a074fe80a50c40"; //xplane

$db = get_db();
$userid = get_userid_from_wkey($db, $wkey);
$userid = $userid[0];

for ( $i = 0; $i < 1; $i++ ) {
  $dt  = time();//gmstrftime( "%s", time() );
  $lat = sprintf("%.4f", 55.0+(rand(0,50)/10.0));
  $lon = sprintf("%.4f", 12.0+(rand(0,40)/10.0));
#add_pt($db, $userid, $wkey, $lat, $lon, $acc, $speed, $bearing, $alt, $dt, $trackid, $comment) 
  add_pt( $db, $userid, $wkey, $lat, $lon, 0, 0, 0, 0, $dt, "random","random" );
}
print $userid."<br/>".$dt."</br>".$lat."<br/>".$lon."</br>";
?>
