<?php
# Example for class DbUtils
# Writen By Kakhaber Kashmadze <info@soft.ge> 


$title="Test";

require_once 'header.php';
require_once 'init.php';
?>
<div id="content">
<br />
<?php

$connectionAttributes=$dbUtils->connectionAttributes($conn,'');
echo $connectionAttributes;
?>
</div>
<?php require_once 'footer.php'; ?>
