<?php
# Example for class DbUtils
# Writen By Kakhaber Kashmadze <info@soft.ge> 

$title="Example Script";

$tablename="users";

$id=0;
$lname=null;
$fname=null;
$email=null;
$active=true;

$min=0;
$max=20;
$fetchMode=PDO::FETCH_ASSOC;

$action=null;

if(isset($_REQUEST['action']) && trim($_REQUEST['action'])!=="") $action=trim($_REQUEST['action']);
if(isset($_REQUEST['lname']) && trim($_REQUEST['lname'])!=="") $lname=trim($_REQUEST['lname']); 
if(isset($_REQUEST['fname']) && trim($_REQUEST['fname'])!=="") $fname=trim($_REQUEST['fname']);
if(isset($_REQUEST['email']) && trim($_REQUEST['email'])!=="") $email=trim($_REQUEST['email']);

require_once 'header.php';



?>
<style>
 .fieldlabel{
	float:left; margin-left:10px; width:150px; text-align:right;
 }
 .fieldinput{
	float:left; margin-left:10px; width:200px; text-align:left;
 }
</style>
<div id="content">
<form id="exampleform" method="get" action="example.php">
	<input type="hidden" id="action" name="action" value="update" />
    <div style="clear:both; height:10px;"></div>    
    <div class="fieldlabel">Last name: </div>
    <div class="fieldinput"><input type="text" id="lname" name="lname" value="<?php if(!is_null($lname)) echo $lname; ?>" /></div>
    <div style="clear:both; height:10px;"></div>
    <div class="fieldlabel">First name: </div>
    <div class="fieldinput"><input type="text" id="fname" name="fname" value="<?php if(!is_null($fname)) echo $fname; ?>" /></div>
    <div style="clear:both; height:10px;"></div>
    <div class="fieldlabel">Email: </div>
    <div class="fieldinput"><input type="text" id="email" name="email" value="<?php if(!is_null($email)) echo $email; ?>" /></div>
    <div style="clear:both; height:10px;"></div>
    <div style="float:left; margin-left:170px; text-align:left;">
		<button type="buttom" id="submit" name="submit" value="1" onclick="submitForm()">Add User</button>
	</div>
    <div style="clear:both; height:10px;"></div>
</form>
<?php
echo '<div style="margin-top:10px; height:1px; width:500px; background-color:#CCCCCC;"></div>';
$errorMessage=trim($errorMessage);



if($errorMessage=='' && !is_null($action) && $action=='update'){	
	
	if(is_null($lname)) $errorMessage.='Last name field is empty<br />';
	if(is_null($fname)) $errorMessage.='First name field is empty<br />';
	if(is_null($email)) $errorMessage.='Email field is empty<br />';
	elseif(filter_var($email, FILTER_VALIDATE_EMAIL)==false) $errorMessage.='Email '.trim($_REQUEST['email']).' is not valid';
    if($errorMessage==''){
		if($id==0){
			$id=$dbUtils->nextval('users_id_seq');        
			$sqlStr='insert into '.$tablename.' (id, lname, fname, email, active) values ('.$id.', '.$dbUtils->strNull($lname).', '.$dbUtils->strNull($fname).', '.$dbUtils->strNull($email).', '.$dbUtils->booleanCondition($active).')';  
			$dbUtils->insert($sqlStr);
			echo '<script type="text/javascript"> gotoPage("./example.php"); </script>';
			
		}else{
			$id=$_REQUEST['id'];
			$sqlStr='update '.$tablename.' set'.
			' lname='.$dbUtils->strNull($lname).
			', fname='.$dbUtils->strNull($fname).
			', email='.$dbUtils->strNull($email).
			', active='.$dbUtils->booleanCondition($active).
			' where id='.$id;
			
			$dbUtils->update($sqlStr);
			echo '<script type="text/javascript"> gotoPage("./example.php"); </script>';
		}    
    }
    
    if($errorMessage!==''){
		echo '<div style="margin-top:10px; color:red;">'.$errorMessage.'</div>';
	}
}

$sqlStr="select id, lname, fname, email from ".$tablename." where id=:id";
$row=$dbUtils->fetchRow($sqlStr, array('name'=>id, 'value'=>1, 'dataType'=>PDO::PARAM_INT), $fetchMode);
if(count($row)!=0){
	echo '<div style="color:green;">ID:'.$row['id'].' | Name: '.$row['lname'].' '.$row['fname'].(!is_null($row['email'])?' | Email: '.$row['email']:'').'</div>';
	echo '<div style="clear:both; margin-top:5px; height:1px; width:500px; background-color:#CCCCCC;"></div>';
}
$bindValues=null;
/*
$bindValues=array(
		'fields'=>array(				
			array('name'=>':lname', 'value'=>'lname3', 'dataType'=>PDO::PARAM_STR, 'like'=>LIKE_ANY)			
		),
		
		'offset'=>array(
			array('name'=>':min', 'value'=>0, 'dataType'=>PDO::PARAM_INT),
			array('name'=>':max', 'value'=>7, 'dataType'=>PDO::PARAM_INT)
		)
	);
$sqlStr="select id, lname, fname, email from ".$tablename." where lname like :lname order by id desc";	
*/
$sqlStr="select id, lname, fname, email from ".$tablename." order by id desc";
$rows=$dbUtils->fetchRows($sqlStr, $bindValues, $fetchMode);

$rowsCount=count($rows);
if($rowsCount!=0){
	for($i=0; $i<$rowsCount; $i++){
		echo '<div>ID:'.$rows[$i]['id'].' | Name: '.$rows[$i]['lname'].' '.$rows[$i]['fname'];
		if(!is_null($rows[$i]['email'])) echo ' | Email: '.$rows[$i]['email'];
		echo '</div>';
		echo '<div style="clear:both; margin-top:5px; height:1px; width:500px; background-color:#CCCCCC;"></div>';
	}
}



?>   
</div> 
<?php require_once 'footer.php'; ?>
