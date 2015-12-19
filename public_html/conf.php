<?php

ini_set("display_errors", 'On');
ini_set("error_reporting", "E_ALL");

require_once '../conf/params.php';

define('INCLUDES_PATH', '../include');
define('CLASS_PATH', INCLUDES_PATH.'/classes');

require_once CLASS_PATH.'/DbUtils.php';
$dbUtils=new DbUtils();

?>
