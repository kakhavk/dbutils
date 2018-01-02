<?php
# Database Helper class
# Writen By Kakhaber Kashmadze <info@soft.ge>
# Licensed under MIT License
# Version 0.2

namespace DbUtils\Helper;

class HelperDb{

    public static function isInt($name){
		$item=null;

		if(!empty($name)){
			if(ctype_digit((string)$name)===true){
				return true;
			}
		}
		return false;
	}
	
	public static function isDouble($name){
		$item=null;
		$itemExplode=array();
		
		if(isset($name) && !empty($name) && trim($name)!==""){
			$item=$name;
		}
		if(!is_null($item)){
			$item=str_replace(',','.',$item);
			if(is_numeric($item)===false){
				return false;
			}
									
			$itemExplode=explode('.',$item);
			if(count($itemExplode)==1){
				return false;
			}
			
			for($i=0; $i<count($itemExplode); $i++){
				if(ctype_digit((string)$itemExplode[$i])===false){
					return false;
				}
			}			
			return true;
		}
		return false;
	}
}
