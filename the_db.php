<?php

function get_db() {
  $DBNAME="sqlite:trips.sqll";
  $db = null;

  try {
    //create or open the database
    $db = new PDO($DBNAME);
  } catch(Exception $e) {
    die("error");
  }
  $db->setAttribute(PDO::ATTR_TIMEOUT, 10);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
  return $db;
}

// --

function get_userid( $db, $ui ) {
  if ( $db == NULL ) {
    $db = get_db();
  }
  $stmt = $db->prepare('select userid from users where userid = :userid');
  $stmt->execute( array('userid' => $ui) );
  $result = $stmt->fetchAll();
  return $result;
}

function get_userid_from_wkey( $db, $wk ) {
  if ( $db == NULL ) {
    $db = get_db();
  }
  $stmt = $db->prepare('select userid from users where wkey = :wkey');
  $stmt->execute( array('wkey' => $wk) );
  $result = $stmt->fetchAll();
  return $result[0];
}

function get_userid_from_rkey( $db, $rk ) {
  if ( $db == NULL ) {
    $db = get_db();
  }
  $stmt = $db->prepare('select userid from users where rkey = :rkey');
  $stmt->execute( array('rkey' => $rk) );
  $result = $stmt->fetchAll();
  return $result[0];
}

function DBG($s) {
  if ( is_writable("DBG.txt") ) {
		$fh = fopen("DBG.txt", 'a');
	  fwrite($fh, date("Y-m-d H:i:s")." ".$_SERVER['REMOTE_ADDR']."\n");
	  fwrite($fh, $s);
	  fwrite($fh, "\n");
	  fclose($fh);
	 }
}

function userid2name($db, $ui) {
	if ( $db == NULL ) {
    $db = get_db();
  }
  $stmt = $db->prepare('select name from users where userid = :ui');
  $stmt->execute( array('ui' => $ui) );
  $result = $stmt->fetchAll();
  if ($result) {
	  return $result[0]['name'];
	}
	return "UNKNOWN";
}

// Hard coded for B&B at the moment.
function send_mail($s, $e="__NONE__", $a) {
	require 'PHPMailer/PHPMailerAutoload.php';
	DBG("mail");
	$mail = new PHPMailer;

	$mail->isSMTP();
	$mail->Host = '__SMTP__';
	$mail->Port = 587;
	$mail->SMTPAuth = true;
	$mail->Username = '__USRT__';
	$mail->Password = '__PAST__';
	$mail->SMTPSecure = 'tls';

	$mail->From = '__USRT__'; // OR f_email
	$mail->FromName = 'TRIPS';
	$mail->addAddress('__ADR1__');
	if ( $e !== "__NONE__" ) {
		$mail->addAddress($e); 
	}
	$mail->CharSet = 'UTF-8';

	$mail->WordWrap = 70;            // Set word wrap to 50 characters
	$mail->isHTML(true);     // Set email format to HTML (hmmmm)

	$mail->Subject = $s;
	$message  = "<p>trips webservice\n";
	$message .= "<p><a href=\"http://berck.se/trips/lastseen2.php?rkey=__RKYB__\">trips website</a>\n";
	$message .= "<p>".$a;
	$mail->Body    = $message;
	$mail->AltBody = $message;

	if(!$mail->send()) {
		DBG("error sending.");
		DBG($mail->ErrorInfo);
	} else {
		DBG("mail sent.");
	}
}

function distance($lat1, $lng1, $lat2, $lng2) {
  if (($lat1==$lat2) && ($lng1==$lng2)) {
    return 0;
  }
	$pi80 = M_PI / 180;
	$lat1 *= $pi80;
	$lng1 *= $pi80;
	$lat2 *= $pi80;
	$lng2 *= $pi80;
 
	$r = 6372.797; // mean radius of Earth in km
	$dlat = $lat2 - $lat1;
	$dlng = $lng2 - $lng1;
	$a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
	$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
	$m = $r * $c * 1000.0; // return in meters
 
	return $m;
}

function rev_geocode($lat, $lon) {
	$feedUrl = 'http://nominatim.openstreetmap.org/reverse.php?email=__ADR1__&addressdetails=0&format=xml&lat='.$lat.'&lon='.$lon;
	DBG( $feedUrl );
	$rawFeed = file_get_contents($feedUrl);
	$xml = new SimpleXmlElement($rawFeed);
	DBG( $xml->asXML() );
	$adr = "Unable to get address.";
	if ( $xml->result ) {
		$adr = $xml->result;
	}
	DBG( $adr );
	return $adr;
}

/*
sqlite> .schema points
CREATE TABLE points (
        id INTEGER PRIMARY KEY,
  lat TEXT,
  lon TEXT,
  acc FLOAT,
  speed FLOAT,
  bearing FLOAT,
  alt FLOAT,
  type INTEGER,
  datetime TEXT,
  gpstime TEXT,
        userid VARCHAR(32),
        trackid VARCHAR(32),
        comment TEXT ,
        dist FLOAT
        );
*/
// Higher types (>100) for user warnings/info? Seperate info table! similar to points (identical)
function add_pt_OLD($db, $userid, $wkey, $lat, $lon, $acc, $speed, $bearing, $alt, $dt, $trackid, $comment) {

  if ( $db == NULL ) {
    $db = get_db(); // maybe name depending on userid? But Db contains users also
  }
  //$data = array( 'lat' => 53, 'lon' => 12, 'userid' => "xxx", 'datetime' => "2011-09-12 20:02:11" );

  // Get previous latlon.
  $stmt = $db->prepare("select * from points where userid = :userid order by id desc limit 1");
  $stmt->execute( array(':userid' => $userid) );
  $result = $stmt->fetchAll();
  $dist = -1;
  $type = 0; // store
  $ptype = 0; // default type
  $id = -1;
  if ( $result ) {
    $row = $result[0];
    $lat1 = $row['lat'];
    $lon1 = $row['lon'];
    $id   = intVal($row['id']);
    $dist = abs(distance($lat1, $lon1, $lat, $lon));
    $tdiff = intVal($dt)-intVal($row['gpstime']);
    $ptype = intVal($row['type']); // previous type
    // if stationary, first point is type=1, then update second point type=2 until
    // we move. 
    // should be a timediff limit too, if > 24hrs, reset type=2 or something.
    // instead of type=2 we could have a counter 
    
    // If less than 40 meters, less than 24 hrs and more than 5 minutes, we determine
    // we are stationary by setting type to 2.
    // maybe better to check accuracy...
    
    if ( ($dist < 50) && ($tdiff < (24*3600)) ) { // less than 50 meters COULD BE A USER PARAMETER!
      if ( $ptype == 0 ) {
        $type = 2; //PJB  store // this was 1 before until 20161031
      } else {
        $type = 2;
      }
    }
  }
  // too many mails - maybe uses type-counter, and mail when type > n
  // or a timediff, and not send more than x mails per n minutes.
  if ( ($ptype==2) && ($type==0) && ($dist >=50) ) { // we started moving after stationary
	  
 		$feedUrl = 'http://nominatim.openstreetmap.org/reverse.php?email=__ADR1__&addressdetails=0&format=xml&lat='.$lat.'&lon='.$lon;
		DBG( $feedUrl );
		$rawFeed = file_get_contents($feedUrl);
		$xml = new SimpleXmlElement($rawFeed);
		//$foo = array( (string) $xml->channel->item->title );
		//DBG($foo);
		DBG( $xml->asXML() );
		$adr = "Unable to get address.";
		if ( $xml->result ) {
			$adr = $xml->result;
		}
		DBG( $adr );

	  DBG( "Started moving ".$userid );
	  if ( ($userid==="__UIDB__") || ($userid==="__UIDE__") ) {
		  send_mail("Movement detected", "__USRT__", $adr);
		} else {
			//send_mail("Movement detected(".$userid.")", "__NONE__", $adr);
		}
	} else if ( ($ptype == 0) && ($type == 2) ) {
 		$feedUrl = 'http://nominatim.openstreetmap.org/reverse.php?email=__ADR1__&addressdetails=0&format=xml&lat='.$lat.'&lon='.$lon;
		DBG( $feedUrl );
		$rawFeed = file_get_contents($feedUrl);
		$xml = new SimpleXmlElement($rawFeed);
		//$foo = array( (string) $xml->channel->item->title );
		//DBG($foo);
		DBG( $xml->asXML() );
		$adr = "Unable to get address.";
		if ( $xml->result ) {
			$adr = $xml->result;
		}
		DBG( $adr );

		DBG( "Stopped moving ".$userid );
	  if ( ($userid==="__UIDB__") || ($userid==="__UIDE__") ) {
		  send_mail("Stopped moving", "__USRT__", $adr);
		} else {
			//send_mail("Stopped moving (".$userid.")", "__NONE__", $adr);
		}	
	}
  // uit timediff kunnen we uitrekenen hoelang we stationair zijn
  //$dist = 9999;
  if ( $type == 2 ) {
    $stmt = $db->prepare("UPDATE points SET datetime=".$dt.",gpstime=".$dt.",type=".$type." WHERE (id=".$id.");");
    //error_log("UPDATE points SET datetime=".$dt.",gpstime=".$dt.",type=".$type." WHERE (id=".$id.");");
    $data = array('datetime' => $dt, 'gpstime' => $dt, 'type' => $type);
    $stmt->execute();
  } else { // unless time > 60mins or something? Use Locus filter only? As parameter?
    $data = array( 'lat' => $lat, 'lon' => $lon, 'userid' => $userid, 'acc' => $acc, 'speed' => $speed, 'bearing' => $bearing, 'alt' => $alt, 'type' => $type, 'datetime' => $dt, 'gpstime' => $dt, 'trackid' => $trackid, 'comment' => $comment, 'dist' => $dist );
    $stmt = $db->prepare("INSERT INTO points (userid, lat, lon, acc, speed, bearing, alt, type, datetime, gpstime, trackid, comment, dist) VALUES (:userid, :lat, :lon, :acc, :speed, :bearing, :alt, :type, :datetime, :gpstime, :trackid, :comment, :dist);");
    $stmt->execute( $data );
  }
  $db->close();
}

/*
	NEW.
*/
function add_pt($db, $userid, $wkey, $lat, $lon, $acc, $speed, $bearing, $alt, $dt, $trackid, $comment) {

  if ( $db == NULL ) {
    $db = get_db(); // maybe name depending on userid? But Db contains users also
  }
  //$data = array( 'lat' => 53, 'lon' => 12, 'userid' => "xxx", 'datetime' => "2011-09-12 20:02:11" );

  // Get previous latlon.
  $stmt = $db->prepare("select * from points where userid = :userid order by id desc limit 1");
  $stmt->execute( array(':userid' => $userid) );
  $result = $stmt->fetchAll();
  $dist = -1;
  $type = 0; // store
  $ptype = 0; // default type
  $id = -1;
  $dist_limit = 50; // inside this is stationary
  if ( $userid==="__UIDB__" ) { // Berit
	  $dist_limit = 200;
	}

  if ( $result ) { // The point last saved
    $row = $result[0];
    $lat1 = $row['lat'];
    $lon1 = $row['lon'];
    $id   = intVal($row['id']);
    $dist = abs(distance($lat1, $lon1, $lat, $lon));
    $tdiff = intVal($dt)-intVal($row['gpstime']);
    $ptype = intVal($row['type']); // previous type
    
    if ( ($dist < $dist_limit) && ($tdiff < (24*3600)) ) { // less than 50 meters COULD BE A USER PARAMETER!
      if ( $ptype == 0 ) {
        $type = 1; //PJB  store as new point type 1
      } else {
        $type = $ptype+1; // increment
      }
    }
  } // end result
  
  // A special "?" type is accuracy large, which does not trigger stationary/moving?
  if ( intVal($acc) > 200 ) { // arbitrary accuracy...
    /*
      We get an extra "stopped moving" mail because of the -1
id|lat|lon|acc|speed|bearing|alt|type|datetime|gpstime|userid|trackid|comment|dist
319100|56.24548|12.88680|10.0|0.2|312.1|26.9| 6|1478252167|1478252167|__UIDB__|||10.285348929898
319099|56.24553|12.88666|18.0|0.0| 51.7|24.7| 0|1478251049|1478251049|__UIDB__|||37.86443668298
319098|56.24568|12.88611|25.0|0.3| 77.1|26.3|-1|1478250988|1478250988|__UIDB__|||59.436627498317
319097|56.33824|12.89541| 8.0|0.2| 76.3|98.2| 1|1478207126|1478207126|18657add1922436ed56bdeeb1b91cd23|||14.053842046595
319096|56.33833|12.89557| 6.0|0.0| 76.3|95.5| 0|1478207089|1478207089|18657add1922436ed56bdeeb1b91cd23|||210.50477811318
*/
		if ( $type == 0 ) {
  		$type = -1;  
    } else {
      $type = -$ptype;
    }
    DBG("minus one ".$type);
	}
  // We can mail if:
  // type == 3, then we are stationary a few points,
  // we go to 0, then we start moving, but we should check if the last 2 (or more)
  // points are 0..., after one that is > 0 ? how to count moves?
  
  if ( $type == 3 ) {
	  DBG("TYPE is 3, considered stopped.");
	  $dt_str = date("Y-m-d H:i:s");
	  if ( $userid==="__UIDB__" ) {
		  $adr = rev_geocode($lat, $lon);
		  send_mail("Berit stopped moving ".$dt_str, "__USRT__", $adr);
		} else if ( $userid==="__UIDE__" ) {
		  $adr = rev_geocode($lat, $lon);
		  send_mail("Bengt stopped moving ".$dt_str, "__USRT__", $adr);
		} else {
			$name = userid2name($db, $userid);
			//send_mail("Movement stopped(".$name.") ".$dt_str, "__NONE__", $adr);
		}
	}
	// Previous type is 3 or larger, and new type is 0 again
	if ( ($ptype >= 2) && ($type == 0) ) {
		DBG("TYPE is 0 again, considered moving.");
		$dt_str = date("Y-m-d H:i:s");
	  if ( $userid==="__UIDB__" ) {
		  $adr = rev_geocode($lat, $lon);
		  send_mail("Berit started moving ".$dt_str, "__USRT__", $adr);
		} else if ( $userid==="__UIDE__" ) {
		  $adr = rev_geocode($lat, $lon);
		  send_mail("Bengt started moving ".$dt_str, "__USRT__", $adr);
		} else {
			$name = userid2name($db, $userid);
			//send_mail("Movement started(".$name.") ".$dt_str, "__NONE__", $adr);
		}
	}
  
  // if type==0 after a long time, we notice new activity
  if ( ($type==0) && ($tdiff >= (24*3600)) ) {
	  DBG("New activity.");
	  $dt_str = date("Y-m-d H:i:s");
		$name = userid2name($db, $userid);
	  send_mail("New activity(".$name.") ".$dt_str, "__NONE__", "");
	}
	
  // types 0 and one are saved as new points, a higher type updates the current
  // type with new time and type.
  if ( $type > 1 ) {
	  // We update the time and the type, not the position/bearing/etc!
    $stmt = $db->prepare("UPDATE points SET datetime=".$dt.",gpstime=".$dt.",type=".$type.",acc=".$acc." WHERE (id=".$id.");");
    //error_log("UPDATE points SET datetime=".$dt.",gpstime=".$dt.",type=".$type." WHERE (id=".$id.");");
    $data = array('datetime' => $dt, 'gpstime' => $dt, 'type' => $type);
    $stmt->execute();
  } else { 
    $data = array( 'lat' => $lat, 'lon' => $lon, 'userid' => $userid, 'acc' => $acc, 'speed' => $speed, 'bearing' => $bearing, 'alt' => $alt, 'type' => $type, 'datetime' => $dt, 'gpstime' => $dt, 'trackid' => $trackid, 'comment' => $comment, 'dist' => $dist );
    $stmt = $db->prepare("INSERT INTO points (userid, lat, lon, acc, speed, bearing, alt, type, datetime, gpstime, trackid, comment, dist) VALUES (:userid, :lat, :lon, :acc, :speed, :bearing, :alt, :type, :datetime, :gpstime, :trackid, :comment, :dist);");
    $stmt->execute( $data );
  }
  $db->close();
}

// this is prolly only called from tk10n-server
function add_info($db, $userid, $wkey, $lat, $lon, $acc, $speed, $bearing, $alt, $type, $dt, $trackid, $comment) {

  if ( $db == NULL ) {
    $db = get_db();
  }

  // Get previous latlon.
  $stmt = $db->prepare("select * from info where userid = :userid order by id desc limit 1");
  $stmt->execute( array(':userid' => $userid) );
  $result = $stmt->fetchAll();
  $dist = -1;
  $id = -1;
  if ( $result ) {
    $row = $result[0];
    $lat1 = $row['lat'];
    $lon1 = $row['lon'];
    $id   = intVal($row['id']);
    $dist = abs(distance($lat1, $lon1, $lat, $lon));
  }
  $data = array( 'lat' => $lat, 'lon' => $lon, 'userid' => $userid, 'acc' => $acc, 'speed' => $speed, 'bearing' => $bearing, 'alt' => $alt, 'type' => $type, 'datetime' => $dt, 'gpstime' => $dt, 'trackid' => $trackid, 'comment' => $comment, 'dist' => $dist );
  $stmt = $db->prepare("INSERT INTO info (userid, lat, lon, acc, speed, bearing, alt, type, datetime, gpstime, trackid, comment, dist) VALUES (:userid, :lat, :lon, :acc, :speed, :bearing, :alt, :type, :datetime, :gpstime, :trackid, :comment, :dist);");
  $stmt->execute( $data );
}

function get_email( $db, $em ) {
  if ( $db == NULL ) {
    $db = get_db();
  }
  $stmt = $db->prepare('select * from users where email = :email');
  $stmt->execute( array('email' => $em) );
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  return $result;
}

function get_user( $db, $ui ) {
  if ( $db == NULL ) {
    $db = get_db();
  }
  $stmt = $db->prepare('select * from users where userid = :userid');
  $stmt->execute( array('userid' => $ui) );
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  return $result;
}

// http://kennyheer.blogspot.com/2010/10/geojson-in-php.html
// http://stackoverflow.com/questions/6452748/openlayers-parsed-geojson-points-always-display-at-coords0-0

// grp must match the %Y%m%d format
//
function get_points( $db, $ui, $grp ) {
  if ( $db == NULL ) {
    $db = get_db();
  }
  try {
    $stmt = $db->prepare('select *,strftime("%Y%m%d", datetime) as grp from points where `grp` = :grp and userid = :userid');
    $stmt->execute( array(':grp' => $grp, ':userid' => $ui) );
    $result = $stmt->fetchAll();
    return $result;
  } catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
  }
}

// Used in lastseen.php
function get_last_point( $db, $ui, $n ) {
  if ( $db == NULL ) {
    $db = get_db();
  }
  $now = time();//datetime(gpstime, 'localtime')) strftime('%Y-%m-%d %H:%M:%S',gpstime)
  try {
    $stmt = $db->prepare("select *,datetime(gpstime,'unixepoch','localtime') as dt_local,abs(strftime('%s','now')-gpstime) as td from points where userid = :userid order by id desc limit :n");
    $stmt->execute( array(':userid' => $ui, 'n' => $n) );
    $result = $stmt->fetchAll();
    return $result;
  } catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
  }
}

function get_last_info( $db, $ui ) {
  if ( $db == NULL ) {
    $db = get_db();
  }
  $now = time();//datetime(gpstime, 'localtime')) strftime('%Y-%m-%d %H:%M:%S',gpstime)
  try {
    $stmt = $db->prepare("select *,datetime(gpstime,'unixepoch','localtime') as dt_local,abs(strftime('%s','now')-gpstime) as td from info where userid = :userid order by id desc limit 1");
    $stmt->execute( array(':userid' => $ui) );
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
  } catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
  }
}

function seconds_to_time($secs) {
  $dt = new DateTime('@' . $secs, new DateTimeZone('UTC'));
  return array('days'    => $dt->format('z'),
               'hours'   => $dt->format('G'),
               'minutes' => $dt->format('i'),
               'seconds' => $dt->format('s'));
}

// See if stationary, etc
function get_last_stationary( $db, $ui ) {
  if ( $db == NULL ) {
    $db = get_db();
  }
  try {
    $stmt = $db->prepare("select * from points where userid = :userid order by id desc limit 2");
    $stmt->execute( array(':userid' => $ui) );
    $result = $stmt->fetchAll();
    
    if ( $result > 1 ) {
      $row = $result[0];
      $lat0 = $row['lat'];
      $lon0 = $row['lon'];
      $gpstime0 = $row['gpstime'];
      $type0 = $row['type'];
      
      $row = $result[1];
      $lat1 = $row['lat'];
      $lon1 = $row['lon'];
      $gpstime1 = $row['gpstime'];
      $type1 = $row['type'];
      
      // better to look at type==2 in DB
      $dist = 0;
      $tdiff = 0;
      if ( $type0 > 2 ) { // stationary starts at type=3
	      $dist = abs(distance($lat1, $lon1, $lat0, $lon0));
				$tdiff = $gpstime0-$gpstime1;
			}
      return array( 'tdiff' => $tdiff, 'tbits' => seconds_to_time($tdiff), 'dist' => $dist, "tdiff_str" => secs2str($tdiff));
    }
  } catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
  }
  return array();
}

function get_all_points( $db, $ui ) {
  if ( $db == NULL ) {
    $db = get_db();
  }
  $now = time();//datetime(gpstime, 'localtime')) strftime('%Y-%m-%d %H:%M:%S',gpstime)
  try {
    $stmt = $db->prepare("select *,datetime(gpstime,'unixepoch','localtime') as dt_local,abs(strftime('%s','now')-gpstime) as td from points where userid = :userid order by id desc");
    $stmt->execute( array(':userid' => $ui) );
    $result = $stmt->fetchAll();
    return $result;
  } catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
  }
}

//select distinct date(gpstime,'unixepoch','localtime') as d from points;
function get_dates( $db, $ui, $n ) {
  if ( $db == NULL ) {
    $db = get_db();
  }
  $now = time();//datetime(gpstime, 'localtime')) strftime('%Y-%m-%d %H:%M:%S',gpstime)
  try {
    $stmt = $db->prepare("select distinct date(gpstime,'unixepoch','localtime') as d from points where userid = :userid order by d desc limit :n");
    $stmt->execute( array(':userid' => $ui, 'n' => $n) );
    $result = $stmt->fetchAll();
    return $result;
  } catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
  }
}

function get_points_on_date( $db, $ui, $dt ) {
  if ( $db == NULL ) {
    $db = get_db();
  }
  $now = time();//datetime(gpstime, 'localtime')) strftime('%Y-%m-%d %H:%M:%S',gpstime)
  try {
	  // SUM(Quantity) AS TotalItemsOrdered
	  // select sum(dist) as total_dist from points where userid = "fc011c0d9d440c5da0d30324f0bf90ce" order by id desc;
    $stmt = $db->prepare("select *,datetime(gpstime,'unixepoch','localtime') as dt_local,strftime('%Y-%m-%d',gpstime,'unixepoch','localtime') as dt,abs(strftime('%s','now')-gpstime) as td from points where dt = :dt and userid = :userid order by id desc");
    $stmt->execute( array(':dt' => $dt, ':userid' => $ui) );
    $result = $stmt->fetchAll();
    return $result;
  } catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
  }
}

// Used in lastseen.php
function get_point_count( $db, $ui ) {
  if ( $db == NULL ) {
    $db = get_db();
  }
  try {
    $stmt = $db->prepare("select count(*) from points where userid = :userid");
    $stmt->execute( array(':userid' => $ui) );
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
  } catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
  }
}
//Take bearing, put in correct quadrant (0..7) or (0..23)
function bearing_to_icon( $b ) {
  $q = abs(intval($b / 5)); //45 for 8 steps, 15 for 24 steps, 5 for 72
  $qstr = sprintf("%03d", $q);
  return $qstr;
}
//Take speed (m/s), put in correct range (0..6)
function speed_to_icon( $s ) {
  $kph = $s * 3.6;
  if ( $kph < 1 ) {
    return "00";
  } else if ( $kph < 10 ) {
    return "01";
  } else if ( $kph < 30 ) {
    return "02";
  } else if ( $kph < 60 ) {
    return "03";
  } else if ( $kph < 80 ) {
    return "04";
  } else if ( $kph < 100 ) {
    return "05";
  }
  return "06";
}
/*
{ "type": "Feature",
      "geometry": {"type": "Point", "coordinates": [102.0, 0.5]},
      "properties": {"prop0": "value0"}
}
*/
function db_result_to_geojson( $res ) {
  $i = 0;
  $pts = count($res);
  foreach ($res as $r) {
    $lon = floatval( $r['lon'] );
    $lat = floatval( $r['lat'] );
    $latlon = array($lon,$lat);
    $id = $r['id'];
    $dt = $r['datetime'];
    // arrow_04_058.png
    //       ^speed
    //          ^bearing
    $q = bearing_to_icon(floatval( $r['bearing'] ));
    $sc = speed_to_icon(floatval( $r['speed'] ));
    $icn = "arrow";
    $speed = floatval( $r['speed'] ) * 3.6;
    if ( $i == 0 ) { // last point
      $sc = "green";
    }
    //.$icn.'_'.$sc.'_'.$q.'
    if ( $speed < 1 ) {
      $icn = "circle";
      $sc = "00";
      $q = "000";
    }
		if ( intval($r['type']) > 2 ) { // PJB if in stationary mode (type > 2), explicit circle
			$icn = "stationary";
      $sc = "00";
      $q = "000";
		}
    $kts = sprintf("%.2f", $speed * 0.539956803);
    $alt_ft = sprintf("%.0f", floatval($r['alt']) * 3.2808399);
    $dist_m = sprintf("%.1f", floatval( $r['dist'] )); //meter
    
    $arr[] = array(
      "geometry" => array("type" => "Point", "coordinates" => $latlon),
      "type" => "Feature",
      "properties" => array(
      "prop1" => "test",
      "acc" => floatval( $r['acc'] ),
      "speed" => floatval( $speed ),
      "kts" => floatval($kts),
      "bearing" => floatval( $r['bearing'] ),
      "alt" => floatval( $r['alt'] ),
      "alt_ft" => floatval($alt_ft),
      "dist_m" => floatval($dist_m),
      "dt"  => $dt,
      "dt_local" => $r['dt_local'],
      "td" => intval( $r['td'] ),
      "td_str" => secs2str(intval( $r['td'] )),
      "boe" => 'bah',
      "pointtype" => $r['type'],
      //"icon_url" => 'leaflet/dist/images/lwt_map_icons/blue/'.$i.'.png'
      //"icon_url" => 'http://www.berck.se/trips/js/pp/'.$icn.$q.'.png' // DIRECTION!
      "icon_url" => 'http://www.berck.se/trips/js/arrows/'.$icn.'_'.$sc.'_'.$q.'.png' // DIRECTION!
      //"prop2" => trim(odbc_result($result,4)),
      //"prop3" => trim(odbc_result($result,5))
      ),
      "id" => $id
    );
    // end of array
    $i++;
  }
  $geojson = '{"type":"FeatureCollection","pts":"'.$pts.'","features":'.json_encode($arr).'}';
  return $geojson;
}
/*
{ "type": "Feature",
      "geometry": {
        "type": "LineString",
        "coordinates": [
          [102.0, 0.0], [103.0, 1.0], [104.0, 0.0], [105.0, 1.0]
          ]
        },
      "properties": {
        "prop0": "value0",
        "prop1": 0.0
        }
}
*/
function db_result_to_geojson_ls( $res ) {
  $i=0;
  $latlon = array();
  foreach ($res as $r) {
    $lon = floatval( $r['lon'] );
    $lat = floatval( $r['lat'] );
    $latlon[] = array($lon,$lat);
  }
  $arr[] = array(
    "geometry" => array("type" => "LineString", "coordinates" => $latlon),
    "type" => "Feature",
    "properties" => array(
    "prop1" => "test"
    ),
  );
  $geojson = '{"type":"FeatureCollection","features":'.json_encode($arr).'}';
  return $geojson;
}

function add_gpx($url) {
  //$url = "http://some.com/file.xml";
  $xml = simplexml_load_file($url);
  //print_r( $xml );
  $del = array("T", "Z");
  foreach($xml->children() as $child) {
  	foreach($child->children() as $child) { 

  		//print_r($child);

  		foreach($child->children() as $child2){ 
  			$name = $child2->getName();
  			//print_r($child2);
  			/*foreach($child2->attributes() as $a => $b) {
           echo $a,'="',$b,"\"\n"; //lat and lon
        }*/
        /*foreach($child2 as $a => $e){
          echo $a,"=",$e,"\n";
        }*/
        $lat=$child2->attributes()->lat;
        $lon=$child2->attributes()->lon;
        $dt = $child2->children()->time;
        $dt = str_replace($del, " ", $dt); //oderland doesn't accept T/Z in string
        echo "$dt $lat $lon\n";
        add_pt(NULL, "f1a242745ed071207894f25ea30d18db", "", $lat, $lon, $dt);
  		}

  	}
  }
  
  function fmt_num($s) {
    $sign = "";
    if ( bccomp(0,$s) == 1 ) {
      $sign = "-";
      $s = substr($s, 1);
    }
    $lr = explode( ",", $s );
    if ( count($lr) === 2 ) {
      $l = $lr[0];
      $r = $lr[1];
      return $sign.$this->_fmt_num($l,$r);
    }
    return $sign.$this->_fmt_num($s,"00");
  }

  // no negatives!
  function _fmt_num($s,$r) {
    $la = str_split($s);
    $l = "";
    $i = 3; // array(2,2,4,3) and pop for hindi etc
    foreach(array_reverse($la) as $d ) {
      if ( $i == 0 ) {
        $l = $d.",".$l;
        $i = 3;
      } else {
        $l = $d.$l;
      }
      --$i;
    }
    return $neg.$l.".".$r;
  }

}
?>
