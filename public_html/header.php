<?php
require_once 'conf.php';

$rows=array();
$rowsCount=0;
$sqlStr="";
$i=0;
$erStr="";

$tablename="users";

$id=0;
$lname=null;
$fname=null;
$email=null;
$active=true;

$min=0;
$max=100;

if(isset($dbType)) $dbUtils->setDbType($dbType);
else $dbUtils->setDbType("pgsql");

try{
    $conn=$dbUtils->dbConnect(DBHOST, DBUSER, DBPASS, DBNAME);
}catch(Exception $e) {
    $erStr="Unable connect to database";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title><?php echo $title; ?></title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link href="styles/main.css" rel="stylesheet" type="text/css" />
	<meta name="generator" content="Geany 1.26" />
	<script src="jscripts/main.js" type="text/javascript"></script>
</head>

<body>
<div id="topnav">
	<div class="navmenuitem"><a href="index.php">Main</a></div>
	<div class="topnavseparator"></div>
	<div class="navmenuitem"><a href="example.php">Example</a></div>
	<div class="topnavseparator"></div>
	<div class="navmenuitem"><a href="test.php">Test</a></div>
	<div class="topnavseparator"></div>
</div>
