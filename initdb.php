<?php

$DBNAME="sqlite:CHANGEMEtrips.sqll";

try {
  //create or open the database
  $db = new PDO($DBNAME);
} catch(Exception $e) {
  die("error");
}
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

//SELECT name FROM sqlite_master WHERE type='table' AND name='table_name';

$db->beginTransaction();
$res = $db->exec('PRAGMA encoding = "UTF-8";'); 
$res = $db->exec("DROP TABLE IF EXISTS users;");
$res = $db->exec("CREATE TABLE users (
	id INTEGER PRIMARY KEY,
 	userid VARCHAR(32) UNIQUE,
 	rkey VARCHAR(16) UNIQUE,
 	wkey VARCHAR(16) UNIQUE,
  name TEXT,
  email INTEGER UNIQUE,
  datetime TEXT,
  offset INTEGER,
  max_points INTEGER,
  max_age INTEGER,
  visibility INTEGER
	)"
);
// display comment in popup baloon on map?
$res = $db->exec("DROP TABLE IF EXISTS points;");
$res = $db->exec("CREATE TABLE points (
	id INTEGER PRIMARY KEY,
  lat TEXT,
  lon TEXT,
  acc FLOAT,
  speed FLOAT,
  bearing FLOAT,
  alt FLOAT,
  type INTEGER,
  datetime TEXT,
  gpstime INTEGER,
	userid VARCHAR(32),
	trackid VARCHAR(32),
	comment TEXT 
	)"
);
$db->commit();

try {
  $name="Peter Berck";
  $email="peter@berck.se";
  $userid = "f1a242745ed071207894f25ea30d18db"; //substr(md5(uniqid(rand(), true)), 0, 32);
  $rkey = "31e9c3d694d960b1"; //substr(md5(uniqid(rand(), true)), 0, 16);
  $wkey = "9981dfe16d098a7a"; //substr(md5(uniqid(rand(), true)), 0, 16);
  $data = array( 'name' => $name, 'email' => $email, 'userid' => $userid, 'rkey' => $rkey, 'wkey' => $wkey, 
    'offset' => 0, 'max_points' => 0, 'max_age' => 0, 'visiblity' => 0 );
  //$userid="pb000001";
  // Create a prepared statement
  $stmt = $db->prepare("INSERT INTO users (userid, name, email, rkey, wkey) VALUES (:userid, :name, :email, :rkey, :wkey);");
  //$stmt->bindParam(':userid', $userid);
  //$stmt->bindParam(':name', $name);
  //$stmt->bindParam(':email', $email);
  $stmt->execute( $data ); //http://www.php.net/manual/en/pdostatement.execute.php
  if ( $stmt === false ) {
    print "error\n";
  } else {
    print "ok\n";
  }
} catch (Exception $e) {
  echo "error...";
  die ($e);
}

/*
/trips/add_pt.php?lat=56.33804&lon=12.89562&wkey=9981dfe16d098a7a&acc=30.0&speed=1&bearing=170.7&alt=68&time=1341735161
*/
try {
  $lat="56.33804";
  $lon="12.89562";
  $wkey="9981dfe16d098a7a";
  $acc="30";
  $speed="0";
  $bearing="180";
  $alt="68"; 
  $dt=time();//"1341735161";

  $data = array( 'lat' => $lat, 'lon' => $lon, 'userid' => $userid, 'acc' => $acc, 'speed' => $speed, 'bearing' => $bearing, 'alt' => $alt, 'datetime' => $dt, 'gpstime' => $dt );
  $stmt = $db->prepare("INSERT INTO points (userid, lat, lon, acc, speed, bearing, alt, datetime, gpstime) VALUES (:userid, :lat, :lon, :acc, :speed, :bearing, :alt, :datetime, :gpstime);");
  $stmt->execute( $data );
  if ( $stmt === false ) {
    print "error\n";
  } else {
    print "ok\n";
  }
} catch (Exception $e) {
  echo "error...";
  die ($e);
}

/*
// Kijk of een bepaalde tabel bestaat, zoniet, maak hem aan
$q = $db->query("SELECT name FROM sqlite_master WHERE type = 'table'" .
                  " AND name = 'klant'");
 
if ($q->fetch() === false) {
	$db->exec(<<<_SQL_
		CREATE TABLE klant (
		id INT UNSIGNED NOT NULL,
		naam VARCHAR(64),
		plaats VARCHAR(64),
		PRIMARY KEY(id))
_SQL_
	);
	$db->commit();
}
 
// Klaar!
$db->rollback();
*/
	
//$db->exec("CREATE TABLE points (id INTEGER PRIMARY KEY, lat TEXT, lon TEXT, type INTEGER)"); 

//$posts = $db->prepare('SELECT * FROM posts;');
//$posts->execute();

/*
    try {
        // Create a prepared statement
        $stmt = $db->prepare("INSERT INTO POSTS (title, content) VALUES (:title, :content);");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
 
        // Fill in the values
        $title = $_POST['title'];
        $content = $_POST['content'];
        $stmt->execute();
    } catch (Exception $e) {
        die ($e);
    }
*/
?>
