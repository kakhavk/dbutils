<?php
# Example for class DbUtils
# Writen By Kakhaber Kashmadze <info@soft.ge> 

$title="DbUtilsPDO Example Script";
require_once 'header.php';
?>
<div id="content">
<form id="exampleform" method="get" action="example.php">
    <div style="clear:both; height:10px;"></div>
    <div style="float:left; margin-left:10px; width:150px; text-align:right;">Last name: </div>
    <div style="float:left; margin-left:10px; width:200px; text-align:left;"><input type="text" id="lname" name="lname" value="" /></div>
    <div style="clear:both; height:10px;"></div>
    <div style="float:left; margin-left:10px; width:150px; text-align:right;">First name: </div>
    <div style="float:left; margin-left:10px; width:200px; text-align:left;"><input type="text" id="fname" name="fname" value="" /></div>
    <div style="clear:both; height:10px;"></div>
    <div style="float:left; margin-left:10px; width:150px; text-align:right;">Email: </div>
    <div style="float:left; margin-left:10px; width:200px; text-align:left;"><input type="text" id="email" name="email" value="" /></div>
    <div style="clear:both; height:10px;"></div>
    <div style="float:left; margin-left:170px; text-align:left;"><button type="buttom" id="newuser" name="newuser" value="1" onclick="submitForm()">Add User</button></div>
    <div style="clear:both; height:10px;"></div>
</form>
<?php
echo "<div><div style=\"clear:both; margin-top:5px; margin-bottom:10px; height:1px; width:500px; background-color:#CCCCCC;\"></div>";
if(trim($erStr)==""){
    $sqlStr="select id, lname, fname, email from ".$tablename." where id=5";
    $row=$dbUtils->retSqlRow($conn, $sqlStr);
    if(count($row)!=0){
        echo "<div style=\"color:green;\">ID:".$row['id']." | Name: ".$row['lname']." ".$row['fname'].(!is_null($row['email'])?" | Email: ".$row['email']:"")."</div>";
        echo "</div><div style=\"clear:both; margin-top:5px; height:1px; width:500px; background-color:#CCCCCC;\"></div>";
    }
    
    $min=0;
    $max=20;
    $sqlStr="select id, lname, fname, email from ".$tablename." order by id desc";
    $rows=$dbUtils->retSqlRows($conn, $sqlStr, $min, $max);
    $rowsCount=count($rows);
    if($rowsCount!=0){
        for($i=0; $i<$rowsCount; $i++){
            echo "<div>ID:".$rows[$i]['id']." | Name: ".$rows[$i]['lname']." ".$rows[$i]['fname'];
            if(!is_null($rows[$i]['email'])) echo " | Email: ".$rows[$i]['email'];
            echo "</div><div style=\"clear:both; margin-top:5px; height:1px; width:500px; background-color:#CCCCCC;\"></div>";
        }
    }
    
    $id=0;
    $lname="Gabadadze 1";
    $fname="Ushangi 1";
    
    if(isset($_REQUEST['lname']) && trim($_REQUEST['lname'])!==""){
        $lname=trim($_REQUEST['lname']);
    }
    if(isset($_REQUEST['fname']) && trim($_REQUEST['fname'])!==""){
        $fname=trim($_REQUEST['fname']);
    }
    if(isset($_REQUEST['email']) && trim($_REQUEST['email'])!==""){
        
        if(filter_var(trim($_REQUEST['email']), FILTER_VALIDATE_EMAIL)){
            $email=trim($_REQUEST['email']);
        }else{
            $erStr="Email syntax for address: <span style=\"color:#0000FF;\">".trim($_REQUEST['email'])."</span> is not valid";
        }
    }
    
    if(trim($erStr)=="" && isset($_REQUEST['newuser']) && $_REQUEST['newuser']==1){
        $id=$dbUtils->retNextval($conn, 'users_id_seq');
        $sqlStr="insert into ".$tablename." (id, lname, fname, email, active) values (".$id.", '".$lname."', '".$fname."', ".$dbUtils->parsStrNull($email).", ".$dbUtils->retBooleanCondition($active).")";  
        $dbUtils->execSqlInsert($conn, $sqlStr);
        echo "<script type=\"text/javascript\">    gotoPage(); </script>";
        
    }elseif(trim($erStr)=="" && isset($_REQUEST['updateuser']) && $_REQUEST['updateuser']==1){
        $id=$_REQUEST['id'];
        $sqlStr="update ".$tablename." set".
        " lname='".$lname."'".
        ", fname='".$fname."'".
        ", email=".$dbUtils->parsStrNull($email).
        ", active=".$dbUtils->retBooleanCondition($active).
        " where id=".$id;
        
        $dbUtils->execSqlUpdate($conn, $sqlStr);
        echo "<script type=\"text/javascript\"> gotoPage(); </script>";
    }
}

if(trim($erStr)!==""){
    echo "<div style=\"margin-top:10px; color:red;\">Error: ".$erStr."</div>";
}

?>   
</div> 
<?php require_once 'footer.php'; ?>
