<?php
# Example for class DbUtils
# Writen By Kakhaber Kashmadze <info@soft.ge> 

$dbType="pgsql";
$title="Test";
require_once 'header.php';
?>
<div id="content">
<?php
$connectionAttributes=$dbUtils->connectionAttributes($conn,'');
echo $connectionAttributes;
?>
</div>
<?php require_once 'footer.php'; ?>
