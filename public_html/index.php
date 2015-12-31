<?php
require_once 'header.php';
echo '<br />';
ob_start();
require_once '../README';
$hi=nl2br(ob_get_contents());
ob_end_clean();
echo '<div id="content">'.$hi.'</div>';
require_once 'footer.php';
?>
