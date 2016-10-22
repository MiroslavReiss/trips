<?php
function get_post_value($name) {
  if ( $_POST[$name] ) {
    return $_POST[$name];
  }
  return "";
}

function get_get_value($name) {
  if (array_key_exists($name, $_GET)) {
    if ( $_GET[$name] ) {
      return $_GET[$name];
    }
  }
  return "";
}

function parse_gpx($url) {
  //$url = "http://some.com/file.xml";
  $xml = simplexml_load_file($url);
  //print_r( $xml );
  foreach($xml->children() as $child) {
  	foreach($child->children() as $child) { 

  		//print_r($child);

  		foreach($child->children() as $child2){ 
  			$name = $child2->getName();
  			//print_r($child2);
  			/*foreach($child2->attributes() as $a => $b) {
           echo $a,'="',$b,"\"\n"; #lat and lon
        }*/
        /*foreach($child2 as $a => $e){
          echo $a,"=",$e,"\n";
        }*/
        $lat=$child2->attributes()->lat;
        $lon=$child2->attributes()->lon;
        $dt = $child2->children()->time;
        echo "$dt $lat $lon\n";
        
  		}

  	}
  }
}

function secs2str($t) {
  $div = array(604800, 86400, 3600, 60, 60);
  $ind = array("w ", "d ", "h ", "m ", "s ");

  // if 0 we need this fix.
  //
  if ( $t == 0 ) {
    return "00s ";
  }

  $res = "";
  $rest = 0;
  $max = 3; //max so many fields returned
  for ($i = 0; $i < 4; $i++) {
    $rest = intval($t / $div[$i]);
    //print $i." ".$rest;
    if ( ($rest > 0) and ($max > 0) ) { // use $rest >= 0 if you want "00h" etc included.
      $t -= $rest * $div[$i];
      $res = $res . sprintf("%02d", $rest) . $ind[$i];
      $max -= 1;
    }
  }
  if ( ($t > 0) and ($max > 0) ) { // just zero seconds becomes nothing.
    $res = $res . sprintf("%02d",$t) . $ind[4];
  }
  return $res;
}

?>
