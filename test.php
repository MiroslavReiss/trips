<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" version="-//W3C//DTD XHTML 1.1//EN" xml:lang="en"> 
<head> 
<!-- --> 
<meta http-equiv="content-type" content="text/html; charset=utf-8" /> 
<meta name="author" content="Peter Berck" /> 
<meta name="copyright" content="Peter Berck" /> 
<meta name="description" content="Peter Berck" /> 
<meta name="keywords" content="Peter Berck" /> 
<!-- --> 
<title>www.berck.se</title> 
<!-- --> 
<style type="text/css" media="screen">@import 'berck.se.css';</style> 
<!-- --> 
</head> 
<body> 
<div id="page"> 
<h1 id="title">www.berck.se</h1> 
<div id="container1"> 
<pre>
<?php
include("util.php");
include("the_db.php");

$u = get_get_value("url");
//print $u;

add_gpx($u);
?>
</pre>
</div><!-- container --> 
</div><!-- page --> 
</body> 
</html> 
