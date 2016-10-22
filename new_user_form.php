<?php

// This should be called from the register.php to create
// the keys from there and link the two databases.
// or use the same DB...

// get name &c, generate keys, store, email?
include("the_db.php");
include("util.php");

$json_str = stripslashes($_POST['json']);
$json_data = json_decode( $json_str );

$name  = $json_data->username;
$email = $json_data->email;
$userid = $json_data->userid;

//print_r($username.",".$email.",".$name);

$res = 0;

if ( $userid === "" ) {
  $res = -1;
}

// check if email already there, can't happen now that they
// are coupled.
//
$db = get_db();

if ( $res != -1 ) {
  //$userid = substr(md5(uniqid(rand(), true)), 0, 32); //now POST parameter
  $rkey   = substr(md5(uniqid(rand(), true)), 0, 16);
  $wkey   = substr(md5(uniqid(rand(), true)), 0, 16);
  $dt     = gmstrftime( "%Y-%m-%d %H:%M:%S", time() );

  $data   = array( 'name' => $name, 'email' => $email, 'userid' => $userid, 'rkey' => $rkey, 'wkey' => $wkey, 'datetime' => $dt );
  
  // Create a prepared statement
  $stmt = $db->prepare("INSERT INTO users (userid, name, email, rkey, wkey, datetime) VALUES (:userid, :name, :email, :rkey, :wkey, :datetime);");
  
  $stmt->execute( $data ); //http://www.php.net/manual/en/pdostatement.execute.php
  if ( $stmt === false ) {
    //print "error\n";
    $res = -2;
  } else {
    //print "ok\n";
  }
}
echo json_encode( array( "res" => $res, "userid" => $userid ) );
?>