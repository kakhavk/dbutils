<?php
echo '<div style="margin-top:20px; margin-left:10px;"><ul>';
$exclude=array('.','..','index.php','LICENSE', 'conf.php');
if($dir=opendir('./')){
	while(false!==($item=readdir($dir))){
		if(!in_array($item, $exclude) && is_file($item)) echo '<li><a href="'.$item.'">'.$item.'</li>';
	}
	closedir($dir);
}
echo '</ul><div>';
?>
