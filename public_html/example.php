<?php
# Example for class DbUtils
# Writen By Kakhaber Kashmadze <info@soft.ge> 

#for this example use test-mysql.sql from folder ../sql/


require_once 'conf.php';
require_once '../include/classes/DbUtils.php';

/* By default database type is MySQL: 'mysql'
* To change database type add database type  argument shortly : for PostgreSQL 'pgsql', for Microsoft SQL 'mssql', for MySQL 'mysql'
* for example Microsoft SQL Seerver will: $dbUtils=new DbUtils('mssql');
*/

$dbUtils=new DbUtils();

/* If not set global parameters is possible set custom parameters
/*	$dbUtils->setParams(array('dbhost'=>'localhost','dbuser'=>'username','dbpass'=>'password','dbname'=>'database'));
*/



$fetchMode=PDO::FETCH_ASSOC;


// Retrieve one row from table users
$row=$dbUtils->fetchRow("select * from users where id=1");
echo $row['lname']." ".$row['fname']."\n";



// Retrieve multiple rows from table users
$rows=$dbUtils->fetchRows("select * from users");
for($i=0; $i<count($rows); $i++){
    echo $rows[$i]['lname']." ".$rows[$i]['fname']."\n";
}

// Insert into table users
$sqlStr="insert into users (lname, fname) values ('corn', 'mc')";
$dbUtils->insert($sqlStr, null);

// Update table users
$sqlStr="update users set lname='corn' where id=1";
$dbUtils->update($sqlStr, null);



### Advanced Usage:

// Retrieve one row from table users
$bindValue=array('name'=>'id', 'value'=>1, 'dataType'=>PDO::PARAM_INT);
$sqlStr="select id, lname, fname, email from users where id=:id";
$row=$dbUtils->fetchRow($sqlStr, $bindValue, $fetchMode);
echo 'ID:'.$row['id'].' | Name: '.$row['lname'].' '.$row['fname'].(!is_null($row['email'])?' | Email: '.$row['email']:'')."\n";


// Retrieve multiple rows from table users
$sqlStr="select * from users where lname like :lname_like and fname like :fname and lname not like :lname_not_like";
$bindValues=array(
				'fields'=>array(
						array('name'=>'lname_like','value'=>'cor','dataType'=>PDO::PARAM_STR, 'like'=>LIKE_LEFT),
						array('name'=>'lname_not_like','value'=>'cor','dataType'=>PDO::PARAM_STR, 'notLike'=>NOT_LIKE_RIGHT),
						array('name'=>'fname','value'=>'mc','dataType'=>PDO::PARAM_STR)
					)
				);
$rows=$dbUtils->fetchRows($sqlStr, $bindValues, $fetchMode);
for($i=0; $i<count($rows); $i++){
	echo 'ID:'.$rows[$i]['id'].' | Name: '.$rows[$i]['lname'].' '.$rows[$i]['fname'].(!is_null($rows[$i]['email'])?' | Email: '.$rows[$i]['email']:'')."\n";
}

// Insert into table users
$sqlStr="insert into users (lname, fname, email) values (:lname, :fname, :email)";
$bindValues=array(
				'fields'=>array(
						array('name'=>'lname','value'=>'corn2','dataType'=>PDO::PARAM_STR),
						array('name'=>'fname','value'=>'mc2','dataType'=>PDO::PARAM_STR),
						array('name'=>'email','value'=>'corn2@mc.mail.hi','dataType'=>PDO::PARAM_STR)
					)
				);
$dbUtils->insert($sqlStr, $bindValues);				

// Update table users
$sqlStr="update users set lname=:lname where id=:id";
$bindValues=array(
				'fields'=>array(
						array('name'=>'id','value'=>1,'dataType'=>PDO::PARAM_INT),
						array('name'=>'lname','value'=>'corn1','dataType'=>PDO::PARAM_STR)
					)
				);
$dbUtils->update($sqlStr, $bindValues);


if($dbUtils->getIsError()==1) echo $dbUtils->getErrorMessage(0);


/**
* Format date
*/

$params=array(
	'dateFrom'=>'dd-mm-yyyy',
	'dateTo'=>'yyyy/mm/dd'
);

$date='22-07-2016';

echo $dbUtils->formatDate($date);


/**
* Check empty value (Unlike standart empty function isEmpty also assigns true if value contains whitespaces, newlines, tabs):
*/

$value=" ";

isEmpty($value); /* Returns true */
empty($value); /* Returns false */

var_dump(isEmpty($value));
var_dump(empty($value));
?>
