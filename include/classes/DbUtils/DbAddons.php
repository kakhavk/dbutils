<?php
# Database Addons
# Writen By Kakhaber Kashmadze <info@soft.ge>
# Licensed under MIT License
# Version 0.1

# use as addons if already have db class and need only these methods

/*
* Example to use DbAddons class:
* use Shared\DbUtils\DbAddons;
* require 'DbUtils/DbAddons.php';
*/

namespace Shared\DbUtils;

class DbAddons{
    
    private static $params=array(
        'dateFrom'=>'dd/mm/yyyy',
        'dateTo'=>'yyyy/mm/dd'
    );
    
    
    
    /* Set connectin options */
    public static function setOptions($options = array())
    {
        if (isset($options) && is_array($options) && !is_null($options))
            self::$options = $options;
            else
                self::addErrorMessage('options was not set properly');
                
    }
    /* Get connection options */
    public static function getOptions()
    {
        return self::$options;
    }
    
    /* Set or change parameter value */
    public static function setParam($key, $value){
        if(!empty($key)) self::$params[$key]=$value;
    }
    
    /* Set or change parameter values */
    public static function setParams($params){
        foreach($params as $key => $value){
            if(!empty($key)) self::$params[$key]=$value;
        }
    }
    
    /* Set search path for db wchich supports search_path: PostgreSQL etc... */
    public static function setSearchPath(){
        $stmt=null;
        if(isset(self::$dbParams['search_path'])){
            if(self::$dbParams['type']=='pgsql'){
                $stmt=self::$db->prepare("SET search_path TO ".self::$dbParams['search_path']);
                $stmt->execute();
            }
        }
        
    }
    
    /* Retrieve parameters */
    public static function getDbParams(){
        return self::$params;
    }
    
    /* Retrieve parameters */
    public static function getDbParam($key){
        return self::$params[$key];
    }
   
    
    /* Checks if row is array and has records */
    public static function hasRows($row)
    {
        if (!is_null($row) && is_array($row) && count($row) > 0)
            return true;
            return false;
    }
    
    
    /* Returns decoded string
     */
    public static function strDecode($str, $params=array())
    {
        $paramsLocal=array(
            'stripslashes'=>true,
            'htmlspecialchars_decode'=>true
        );
        
        if(isset($params['stripslashes'])){
            $paramsLocal['stripslashes']=$params['stripslashes'];
        }
        
        if(isset($params['htmlspecialchars_decode'])){
            $paramsLocal['htmlspecialchars_decode']=$params['htmlspecialchars_decode'];
        }
        
        if (!empty($str)) {
            $str=trim($str);
            if ($paramsLocal['stripslashes']===true) {
                $str = stripslashes($str);
            }
            if ($paramsLocal['htmlspecialchars_decode']===true) {
                $str = htmlspecialchars_decode($str);
            }
        }
        
        return $str;
    }
    
    /* For mysql true and false is 0 and 1 for postgresql true and false so it will change by database type values
     * For example:
     * insert into table (id, active) values (12, retBooleanCondition(1));
     * where active is boolean type field inserts in mysql 1 and postgresql true
     */
    public static function setBoolean($value=null){
        $retStr="";
        $dbType=self::getDbType();
        
        if(!empty($value)){
            
            if($dbType=="mysql"){
                if($value=="false" || $value=="f" || $value===false) $retStr="0";
                elseif($value=="true" || $value=="t" || $value===true) $retStr="1";
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
     * also encodes string if needed
     */
    public static function setString($str, $params=array())
    {
        
        $paramsLocal=array(
            'addslashes'=>true,
            'htmlspecialchars'=>true,
            'defaultReturnValue'=>"NULL"
        );
        
        if(isset($params['addslashes'])){
            $paramsLocal['addslashes']=$params['addslashes'];
        }
        
        if(isset($params['htmlspecialchars'])){
            $paramsLocal['htmlspecialchars']=$params['htmlspecialchars'];
        }
        
        if (!empty($str)) {
            $str=trim($str);
            if ($paramsLocal['addslashes']===true) {
                $str = addslashes($str);
            }
            if ($paramsLocal['htmlspecialchars']===true) {
                $str = htmlspecialchars($str);
            }
            return "'".$str."'";
        }
        
        if(isset($params['defaultReturnValue'])){
            $paramsLocal['defaultReturnValue']=$params['defaultReturnValue'];
        }
        
        return $paramsLocal['defaultReturnValue'];
    }
    
    
    /* Returns integer or null */
    public static function setInt($digit, $params=array())
    {
        
        $paramsLocal=array(
            'defaultReturnValue'=>"NULL"
        );
        
        if(isset($params['defaultReturnValue'])){
            $paramsLocal['defaultReturnValue']=$params['defaultReturnValue'];
        }
        
        if (isset($digit) && self::isInt($digit)===true){
            return (int)$digit;
        }
        
        return $paramsLocal['defaultReturnValue'];
    }
    
    /* Returns double or null */
    public static function setDouble($digit, $params=array())
    {
        $paramsLocal=array(
            'defaultReturnValue'=>'NULL',
            'allowZero'=>false
        );

        if(isset($params['defaultReturnValue'])){
            $paramsLocal['defaultReturnValue']=$params['defaultReturnValue'];
        }
        
        foreach($paramsLocal as $k => $v){
            if(isset($params[$k])){
                $paramsLocal[$k]=$params[$k];
            }
        }
        
        if(isset($digit)){
            
            
            if(is_numeric($digit)===false){
                return false;
            }
            
            if('allowZero'===false){
                if(doubleval($digit)==0){
                    return false;
                }
            }
            
            return $digit;
        }
        return $paramsLocal['defaultReturnValue'];
    }
    
    
    /**
     * Parse date and change format, or return null if is not set properly
     * $date must with yyyy format for year and not yy, month and day must with two simbol format : dd/mm/yyyy,
     * default parameters is set to $params array with dateFrom and dateTo formats
     * @return \\format date
     * default values in params:
     *	'dateFrom'='dd/mm/yyyy'
     *	'dateTo'='yyyy/mm/dd'
     *	'dateFromSeparator'='/'
     *	'dateToSeparator'='/'
     * Custom usage:
     * setParam('dateFrom','yyyy-mm-dd');
     * setParam('dateTo','mm/yyyy/dd');
     */
    public static function formatDate($date, $params=array()){
        
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
        
        $paramsLocal=array(
            'defaultReturnValue'=>"NULL",
            'returnQuoted'=>true,
            'returnNull'=>false,
            'returnEmpty'=>false,
            'time'=>null
        );
        
        
        foreach($paramsLocal as $k => $v){
            if(isset($params[$k])){
                $paramsLocal[$k]=$params[$k];
            }
        }
        
        if(empty($date) || trim($date)==""){
            
            if($paramsLocal['returnNull']===true){
                return null;
            }
            
            if($paramsLocal['returnEmpty']===true){
                return "";
            }
            
            return $paramsLocal['defaultReturnValue'];
        }
        
        if(!empty(self::$params['dateFrom']) && trim(self::$params['dateFrom'])!==''){
            
            $dateFromSeparator=substr(trim((preg_replace('/[a-zA-Z]/','', self::$params['dateFrom']))),0, 1);
            $dateFrom=array();
            $tmpArray=explode($dateFromSeparator, self::$params['dateFrom']);
            
            for($i=0; $i<count($tmpArray); $i++){
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
        
        if(!empty($date)){
            if(isset($paramsLocal['time'])){
                $date.=' '.$paramsLocal['time'];
            }
        }
        
        if($paramsLocal['returnQuoted']===true){
            $date="'".$date."'";
        }        
        
        return $date;
    }
    
    
    public static function isInt($name){
        $item=null;
        
        if(!empty($name)){
            if(ctype_digit((string)$name)===true){
                return true;
            }
        }
        return false;
    }
}
?>
