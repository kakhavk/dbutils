<?php
# Example for class DbUtils
# Writen By Kakhaber Kashmadze <info@soft.ge> 
//$dbType='mysql';
$title               = "Import SQL";
$attrEmulatePrepares = 1;

$dbType = null;
if (isset($_REQUEST['dbtype']) && trim($_REQUEST['dbtype']) !== "")
    $dbType = trim($_REQUEST['dbtype']);

require_once 'header.php';

$id        = 0;
$lname     = null;
$fname     = null;
$min       = 0;
$max       = 20;
$fetchMode = PDO::FETCH_ASSOC;

$action   = null;
$database = null;

if (isset($_REQUEST['action']) && trim($_REQUEST['action']) !== "")
    $action = trim($_REQUEST['action']);
if (isset($_REQUEST['database']) && trim($_REQUEST['database']) !== "")
    $database = trim($_REQUEST['database']);


?>
<style>
 .fieldlabel{
	float:left; margin-left:10px; text-align:right;
 }
 .fieldinput{
	float:left; margin-left:10px; text-align:left;
 }
 .fieldinputtext{
	width:100px;;
 }
 .submit_button{
	margin-left:10px; text-align:center;
 }
 .hrepasratorline{
	clear:both; margin-top:20px; margin-bottom:10px; height:1px; background-color:#CCCCCC;
 }
 .error{
	color:red;
 }
</style>
<div id="content">
<div class="hrepasratorline"></div>
<form id="exampleform" method="post" action="import.php" enctype="multipart/form-data">
	<input type="hidden" id="action" name="action" value="import" />
    <div style="clear:both; height:10px;"></div>    
    <div class="fieldlabel">Database: </div>
    <div class="fieldinput"><input class="fieldinputtext" type="text" id="database" name="database" value="<?php
if (!is_null($database))
    echo $database;
?>" /></div>
    <div class="fieldlabel">File to import: </div>
    <div class="fieldinput"><input type="file" id="dumpfile" name="dumpfile" /></div>
    <div style="clear:both; height:10px;"></div>
    <div class="fieldlabel">Type of database: </div>
    <div class="fieldinput">
		<select id="dbtype" name="dbtype">
			<option value="pgsql">PostgreSQL Database</option>
			<option value="mysql">MySQL Database</option>
		</select>
    </div>
    <div style="clear:both; height:10px;"></div>
    <div class="fieldinput">
		<button class="submit_button" type="buttom" id="submit" name="submit" value="1" onclick="submitForm()">Import File</button>
	</div>
    <div style="clear:both; height:1px;"></div>
</form>
<div class="hrepasratorline"></div>
<?php
$errorMessage = trim($errorMessage);


$sqldump      = null;
$sqlline      = '';
$sqlfile      = null;
$filetoimport = null;
$dbUtils->setIsError(0);
if ($errorMessage == '') {
    if (!is_null($action) && $action == 'import') {
        
        $filetoimport = $_FILES['dumpfile']['tmp_name'];
        
        
        $sqlfile = file($filetoimport);
        
        foreach ($sqlfile as $v) {
            if (substr($v, 0, 4) == 'COPY') {
                $dbUtils->setErrorMessage('THIS TYPE OF DUMP IS NOT SUPPORTED');
                $dbUtils->setIsError(1);
                break;
            }
        }
        
        
        foreach ($sqlfile as $v) {
            if (trim($v) !== '\.' && strpos($v, '--') !== 0 && strpos($v, ' --') !== 0 && !empty(trim($v))) {
                $sqldump .= $v;
            }
        }
        
        if ($dbUtils->getIsError() == 0) {
            $dbUtils->update($conn, $sqldump);
            if ($dbUtils->getIsError() == 1) {
                echo $dbUtils->getErrorMessage();
            } else {
                echo 'Imported successfully';
            }
        } else {
            echo '<div class="error">' . $dbUtils->getErrorMessage() . '</div>';
        }
        
    }
}



?>   
</div> 
<?php
require_once 'footer.php';
?>
