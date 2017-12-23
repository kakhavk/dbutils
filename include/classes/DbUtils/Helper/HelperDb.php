<?php
# Database Helper class
# Writen By Kakhaber Kashmadze <info@soft.ge>
# Licensed under MIT License
# Version 0.1

namespace DbUtils\Helper;

class HelperDb{

    private static $params=array(
		'dateFrom'=>'dd/mm/yyyy',
		'dateTo'=>'yyyy/mm/dd'
    );
	 /* For mysql true and false is 0 and 1 for postgresql true and false so it will change by database type values
     * For example:
     * insert into table (id, active) values (12, retBooleanCondition(1));
     * where active is boolean type field inserts in mysql 1 and postgresql true
     */
    public static function setBoolean($value=null){
        $retStr="";
        $dbType=self::$retDbType();
        
        if(!HiUtils::isEmpty($value)){
			
			if($dbType=="mysql"){
				if($value=="false" || $value=="f") $retStr="0";
				elseif($value=="true" || $value=="t") $retStr="1";            
				else $retStr=$value;
			 }elseif($dbType=="pgsql"){
				if((is_bool($value) && $value===false) || $value==0) $retStr="false";
				elseif((is_bool($value) && $value===true) || $value==1) $retStr="true";
				else $retStr=$value;
			 }
		}else{
			if($dbType=="mysql") $retStr="0"; 
			elseif($dbType=="pgsql") $retStr="false";
		}
                
        return $retStr;
    }
    
    /* Returns string or null
     * Since 0.10
     */
    public static function strNull($str, $addslashes=0)
    {
        if (!is_null($str) && $str !== "") {
            if ($addslashes==0) {
                $str = addslashes($str);
            }
            return trim("'" . $str . "'");
        }
        return "NULL";
    }
    
    /* Returns integer or null
     * Since 0.10
     */
    public static function intNull($digit)
    {
        if (!is_null($digit) && trim($digit) !== "" && intval($digit) != 0)
            return $digit;
        return "NULL";
    }
    
    /* Returns double or null
     * Since 0.10
     */
    public static function isDouble($digit)
    {
    
		if(!empty($digit)){
			$digit=str_replace(',','.',$digit);
			if(is_number($digit)===false){
				return false;
			}
			
			if(doubleval($digit) == 0.00){
				return false;
			}
			return true;
		}
        return false;
    }
    
    
    /**
    * Parse date and change format, or return null if is not set properly 
    * $date must with yyyy format for year and not yy, month and day must with two simbol format : dd/mm/yyyy,
    * default parameters is set to $params array with dateFrom and dateTo formats
    * @return format date 
	* default values in params:
	*	'dateFrom'='dd/mm/yyyy'
	*	'dateTo'='yyyy/mm/dd'
	*	'dateFromSeparator'='/'
	*	'dateToSeparator'='/'
	* Custom usage:
	* setParam('dateFrom','yyyy-mm-dd');
	* setParam('dateTo','mm/yyyy/dd');
    */
	public static function formatDate($date){

		$dateFrom=array();
		$dateTo=array();

		$dateFromSeparator=null;
		$dateToSeparator=null;
		
		$dateFrom2=array();
		$dateTo2=array();
		
		$dateExplode=array();
		$parsedDate=null;
		
		$tmpArray=array();
		$tmpInt=null;

		if(!empty(self::$params['dateFrom']) && trim(self::$params['dateFrom'])!==''){
			
			$dateFromSeparator=substr(trim((preg_replace('/[a-zA-Z]/','', self::$params['dateFrom']))),0, 1);
			$dateFrom=array();
			$tmpArray=explode($dateFromSeparator, self::$params['dateFrom']);

			for($i=0; $i<count($tmpArray); $i++){
				echo $tmpArray[$i].self::$endLine;
				 $dateFrom[$i]=$tmpArray[$i];
			}

		}


		if(!empty(self::$params['dateTo']) && trim(self::$params['dateTo'])!==''){
			$dateToSeparator=substr(trim((preg_replace('/[a-zA-Z]/','', self::$params['dateTo']))),0, 1);
			$dateTo=array();
			$tmpArray=explode($dateToSeparator, self::$params['dateTo']);
			
			foreach($tmpArray as $key => $value){
				 $dateTo[$key]=$value;
			}
		}
		
		foreach($dateTo as $key => $value){
			$dateTo2[$value]=null;
		}		
		

		if(!empty($date) && trim($date)!==''){
			
			$dateExplode=explode($dateFromSeparator, $date);
			if(count($dateExplode)==3){
				
				for($i=0; $i<3; $i++){
					$tmpInt=$dateFrom[$i];
					$dateFrom2[$tmpInt]=$dateExplode[$i];
				}

				foreach($dateFrom2 as $key => $value){
					$dateTo2[$key]=$value;
				}
				
				$parsedDate='';
				$i=0;
				foreach($dateTo2 as $key => $value){
					if($i!=0) $parsedDate.=$dateToSeparator;
					$parsedDate.=$dateTo2[$key];
					$i++;
				}
				
			}
			if(!empty($parsedDate) && trim($parsedDate)!=='') $date=$parsedDate;
		}
		
		return $date;
	}
}
