<?php

ini_set("display_errors", 'On');
ini_set("error_reporting", "E_ALL");

define('LIKE_ANY',1);
define('LIKE_LEFT',2);
define('LIKE_RIGHT',3);


/*
if does not exists file ../conf/params.php
it must be created and added parameters for database connection

define('DBHOST','host');
define('DBUSER','username');
define('DBPASS','password');
define('DBNAME','databasename');
*/

require_once '../conf/params.php';

define('INCLUDES_PATH', '../include');
define('CLASS_PATH', INCLUDES_PATH.'/classes');

require_once CLASS_PATH.'/DbUtils.php';
$dbUtils=new DbUtils();

?>
