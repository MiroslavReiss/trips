<?php
include("auth/user.php");
$USER = new User();

//print_r($USER);

// get name &c, generate keys, store, email?
include("the_db.php");
include("util.php");

$un = $USER->un;
$account = $USER->get_user( null, $un );

//check if guest
if ( $un === "guest" ) {
  //die("not for guests");
  header("Location: http://www.berck.se/trips/");
  exit;
}

$email = $account['email'];
$userid =$account['userid'];

$db = get_db();
$u = get_user( $db, $userid );
if ( count($u) == 0 ) {
  $res = -1;
  print_r($u);
  die("Error 01");
}

$pts = get_point_count( $db, $userid );
$pts = $pts['count(*)'];
?>
<html>
<head>
<style type="text/css">

body {
	font-size: 14px;
	font-family: "Lucida Grande", Verdana, Arial, sans-serif;
}
.lbl {
  display: inline-block;
  width: 100px;
}
.val {
  display: inline-block;
  width: 200px;
}
</style>
	<title>Info</title>
  <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
</head>
<body>
<div><span class="lbl">User id:</span><span class="val"><?= $u['userid'] ?></span></div>
<div><span class="lbl">Write key:</span><span class="val"><?= $u['wkey'] ?></span></div>
<div><span class="lbl">Read key:</span><span class="val"><?= $u['rkey'] ?></span></div>
<div>
You have saved <?= $pts ?> points.
<pre>
<?php print_r($account); ?>
<?php print_r($USER); ?>
</pre>
<p/>
Click <a href="lastseen.php?rkey=<?= $u['rkey']?>">here</a> to go to the map, or <a href="auth/login.php">here</a> to log out.
</body>
</html>
