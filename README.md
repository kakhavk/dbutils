### Database Utilities

Access and Manage MySQL, PostgreSQL, MicrosoftSQL database using PHP Data Objects (PDO)


### Simple Usage:
```sh
require_once 'DbUtils.php';
$dbUtils=new DbUtils();

$dbUtils->setParams(
    array(
        'dbhost'=>'hostname', 
        'dbuser'=>'username', 
        'dbpass'=>'password', 
        'dbname'=>'database'
    )
);

$dbUtils->setDbType('mysql');
$dbUtils->connect();
$rows=$dbUtils->fetchRows("select * from users");
for($i=0; $i<count($rows); $i++){
    echo $rows[$i]['lname']." ".$rows[$i]['fname']."\n";
}
```
