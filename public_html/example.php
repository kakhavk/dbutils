<?php
# Example for class DbUtils\Db
# Writen By Kakhaber Kashmadze <info@soft.ge> 

#for this example use test-mysql.sql from folder ../sql/

use DbUtils\Db;
use DbUtils\Helper\HelperDb;

define('INCLUDE_DIR',dirname(dirname(__FILE__)).'/include');
define('CLASSES_DIR',INCLUDE_DIR.'/classes');

require_once 'params.php';

require_once 'include/classes/DbUtils/Db.php';
require_once 'include/classes/DbUtils/DbMapper.php';
require_once 'include/classes/DbUtils/Helper/HelperDb.php';


DbUtils\Db::init($dbMapper->getDbparams(), $dbMapper->getDb());


Retrieve one row from table users:

$row=Db::fetchRow("select lname, fname from users where id=1");
echo $row['lname']." ".$row['fname']."\n";

?>
