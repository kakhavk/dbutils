<?php
# Example for class DbUtils
# Writen By Kakhaber Kashmadze <info@soft.ge> 

#for this example use test-mysql.sql from folder ../sql/
require_once 'conf.php';
require_once '../include/classes/DbUtils.php';

$dbUtils=new DbUtils();

/* If not set global parameters is possible set custom parameters
/*	$dbUtils->setParams(array('dbhost'=>'localhost','dbuser'=>'username','dbpass'=>'password','dbname'=>'database'));
*/

$dbUtils->connect();

$rows=$dbUtils->fetchRows("select * from users");

for($i=0; $i<count($rows); $i++){
	echo $rows[$i]['id'].", ".$rows[$i]['lname'].", ".$rows[$i]['fname'].", ".$rows[$i]['email']."\n";
}
echo $dbUtils->getErrorMessage();
?>
