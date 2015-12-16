<?php
# Example for class DbUtils
# Writen By Kakhaber Kashmadze <info@soft.ge> 

$dbType="pgsql";
$title="Test";
require_once 'header.php';
?>
<div id="content">
<?php
$databaseConnectionAttributes=$dbUtils->databaseConnectionAttributes($conn,'');
echo $databaseConnectionAttributes;
?>
</div>
<?php require_once 'footer.php'; ?>
