### Database Utilities

Access and Manage MySQL, PostgreSQL, MicrosoftSQL database using PHP Data Objects (PDO)


### Install:
```sh
require_once 'DbUtils.php'
$dbUtils=new DbUtils();
$dbUtils->setDbType('mysql');
$conn=$dbUtils->connect();
$rows=$dbUtils->fetchRows($conn, "select * from table", null, null);
```
