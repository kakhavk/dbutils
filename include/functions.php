<?php
/* Unlike standart empty function isEmpty also assigns true if value contains whitespaces, newlines, tabs */
function isEmpty($value){
	if(!isset($value) || empty($value) || trim($value)=='') return true;
	return false;
}


/* alternative of var_dump with pre or json formating */
function vardump($var, $task=null){
	if(!empty($task)){
		if($task=='pre'){
			echo '<div><pre>';
			var_dump($var, true);
			echo '</pre></div>';			
		}elseif($task=='json'){
			$json = json_encode((array)$var );
			echo($json);
		}
	}else{
		var_dump($var);		
	}
}
