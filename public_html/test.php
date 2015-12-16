<?php
# Example for class DbUtils
# Writen By Kakhaber Kashmadze <info@soft.ge> 
header('Content-Type: text/html; charset=utf-8');
ini_set("display_errors", 1);

$rows=array();
$rowsCount=0;
$sqlStr="";
$i=0;
$erStr="";


$min=0;
$max=100;


require_once "DbUtils.php";
$dbUtils=new DbUtils();

$dbUtils->setDbType("pgsql");

try{
    $conn=$dbUtils->dbConnect(DBHOST,DBUSER,DBPASS,DBNAME);
    
}catch(Exception $e) {
    $erStr="Unable connect to database";
}
$databaseConnectionAttributes=$dbUtils->databaseConnectionAttributes($conn,'');
echo $databaseConnectionAttributes;
?>
