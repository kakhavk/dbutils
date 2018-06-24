<?php
# Database Helper class
# Writen By Kakhaber Kashmadze <info@soft.ge>
# Licensed under MIT License
# Version 0.3

namespace Shared\DbUtils\Helper;

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
	
	public static function isDouble($item){

		if(isset($item)){
			$item=str_replace(',','.',$item);
			
			if(is_numeric($item)===true){
				return true;
			}
			
			return false;
		}
		
		return false;
	}
	
	public static function isDecimal($item){
	    if(is_null($item)){
	       return false;
	    }
	    
	    $item=str_replace(',','.',$item);
	    
	    $itemExplode=explode('.',$item);
	    if(count($itemExplode)>1){
	        return true;
	    }
	    
	    return false;
	}
}
