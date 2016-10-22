<?php
include("the_db.php");
include("util.php");

$rkey  = get_get_value("rkey");
$db = get_db();
//rkey is needed to read users position
$result = get_userid_from_rkey($db, $rkey);
if ( count($result) > 0 ) {
  $userid = $result[0];
  $result = get_last_stationary( $db, $userid );
}
echo json_encode($result);
?>