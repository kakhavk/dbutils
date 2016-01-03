### Database Utilities

Access and Manage MySQL, PostgreSQL, MicrosoftSQL database using PHP Data Objects (PDO)


### Install:

```sh
require_once '../include/classes/DbUtils.php';
```

### Simple Usage

```sh


$dbUtils=new DbUtils();
$dbUtils->setDbType('mysql');
$dbUtils->connect();

$fetchMode=PDO::FETCH_ASSOC;

```
Retrieve one row from table users:
```sh
$row=$dbUtils->fetchRow("select * from users where id=1");
echo $row['lname']." ".$row['fname']."\n";


```
Retrieve multiple rows from table users:
```sh
$rows=$dbUtils->fetchRows("select * from users");
for($i=0; $i<count($rows); $i++){
    echo $rows[$i]['lname']." ".$rows[$i]['fname']."\n";
}

```
Insert into table users:
```sh
$sqlStr="insert into users (lname, fname) values ('corn', 'mc')";
$dbUtils->insert($sqlStr, null);
```
Update table users:
```sh
$sqlStr="update users set lname='corn' where id=1";
$dbUtils->update($sqlStr, null);
```


### Advanced Usage

Retrieve one row from table users:
```sh
$bindValue=array('name'=>'id', 'value'=>1, 'dataType'=>PDO::PARAM_INT);
$sqlStr="select id, lname, fname, email from users where id=:id";
$row=$dbUtils->fetchRow($sqlStr, $bindValue, $fetchMode);
echo 'ID:'.$row['id'].' | Name: '.$row['lname'].' '.$row['fname'].(!is_null($row['email'])?' | Email: '.$row['email']:'')."\n";
```
Retrieve multiple rows from table users:
```sh
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
```
Insert into table users:
```sh
$sqlStr="insert into users (lname, fname, email) values (:lname, :fname, :email)";
$bindValues=array(
				'fields'=>array(
						array('name'=>'lname','value'=>'corn2','dataType'=>PDO::PARAM_STR),
						array('name'=>'fname','value'=>'mc2','dataType'=>PDO::PARAM_STR),
						array('name'=>'email','value'=>'corn2@mc.mail.hi','dataType'=>PDO::PARAM_STR)
					)
				);
$dbUtils->insert($sqlStr, $bindValues);				
```
Update table users:
```sh
$sqlStr="update users set lname=:lname where id=:id";
$bindValues=array(
				'fields'=>array(
						array('name'=>'id','value'=>1,'dataType'=>PDO::PARAM_INT),
						array('name'=>'lname','value'=>'corn1','dataType'=>PDO::PARAM_STR)
					)
				);
$dbUtils->update($sqlStr, $bindValues);


echo $dbUtils->getErrorMessage(0);
```
