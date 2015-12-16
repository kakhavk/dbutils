<?php
ini_set("display_errors", 1);
ini_set("error_reporting", "On");

require_once '../conf/params.php';

define('INCLUDES_PATH', '../include');
define('CLASS_PATH', INCLUDES_PATH.'/classes');

require_once CLASS_PATH.'/DbUtils.php';
$dbUtils=new DbUtils();

?>
