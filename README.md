### Database Utilities

Access and Manage MySQL, PostgreSQL, MicrosoftSQL database using PHP Data Objects (PDO)


### Simple Usage:
```sh
require_once 'DbUtils.php';
$dbUtils=new DbUtils();
$dbUtils->setDbType('mysql');
$conn=$dbUtils->connect();
$rows=$dbUtils->fetchRows($conn, "select * from table", null, null);
for($i=0; $i<count($rows); $i++){
    echo $rows['columnname'];
}
```
