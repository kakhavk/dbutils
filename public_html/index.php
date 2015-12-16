<?php
require_once 'header.php';
echo '<br />';
ob_start();
require_once '../README.md';
$hi=nl2br(ob_get_contents());
ob_end_clean();
echo $hi;
require_once 'footer.php';
?>
