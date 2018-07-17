<?php
# Database PDO utilities for MySQL and PostgreSQL
# Writen By Kakhaber Kashmadze <info@soft.ge>
# Licensed under MIT License
# Version 1.5

namespace Shared\DbUtils;

use DbUtils\Helper\HelperDb;

define('LIKE_ANY', 1);
define('LIKE_LEFT', 2);
define('LIKE_RIGHT', 3);

define('NOT_LIKE_ANY', 1);
define('NOT_LIKE_LEFT', 2);
define('NOT_LIKE_RIGHT', 3);

class Db{

    protected static $dbType = null; // by default set to mysql. can be changed to mysql, pgsql, mssql ...
    
    protected static $attrEmulatePrepares = 0; // needed for supporting multiple queries
    protected static $errorMessages = array();
    protected static $error = false;
    
    protected static $dsn = null;
    
    public static $endLine="<br />";
    
    private static $db = null;
    private static $dbParams = array();
    
    
    private static $params=array(
        'dateFrom'=>'dd/mm/yyyy',
        'dateTo'=>'yyyy/mm/dd'
    );
    
    public static function init($params=array(), $dbPdo)
    {
        self::$dbParams=$params;
        if(empty($_SERVER['HTTP_HOST'])){
            self::$endLine="\n";
        }
        
        self::$db=$dbPdo;
        
        self::setSearchPath();
    }
    
    
    /* Returns Database Type: mysql or pgsql */
    public static function getDbType()
    {
        return self::$dbParams['type'];
    }
    
    /* Set ATTR_EMULATE_PREPARES for connection */
    public static function setAttrEmulatePrepares($value)
    {
        self::$attrEmulatePrepares = $value;
    }
    
    
    /* Get ATTR_EMULATE_PREPARES for connection */
    public static function getAttrEmulatePrepares()
    {
        return self::$attrEmulatePrepares;
    }
    
    /* Set connectin dsn */
    public static function setDsn($dsn = null)
    {
        if (isset($dsn) && !empty($dsn))
            self::$dsn = $dsn;
    }
    /* Get connection dsn */
    public static function getDsn()
    {
        return self::$dsn;
    }
    
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
    
    /* Set port for connection */
    public static function setPort($value)
    {
        self::$dbParams['port'] = $value;
    }
    
    /* Get port for connection */
    public static function getPort()
    {
        return self::$dbParams['port'];
    }
    
    /* Set error message string */
    public static function setErrorMessage($message)
    {
        
        self::$errorMessages=array();
        self::$errorMessages[0]=$message;
        self::setError(true);
    }
    
    /* Add error message string */
    public static function addErrorMessage($message)
    {
        array_push(self::$errorMessages, $message);
        self::setError(true);
    }
    
    /* Get error message string */
    public static function getErrorMessage($formatted)
    {
        $str           = '';
        $errorMessages = self::$errorMessages;
        
        for ($i = 0; $i < count($errorMessages); $i++) {
            if ($formatted === true){
                $str .= '<div class="errormessage">' . $errorMessages[$i] . '</div>';
            }else{
                $str .= $errorMessages[$i] . "\n";
            }
        }
        
        return $str;
    }
    
    /* Set error state */
    public static function setError($error)
    {
        self::$error = $error;
    }
    
    /* Get error state */
    public static function getError()
    {
        return self::$error;
    }
    
    
    /* parse like conditions in query */
    public static function like($value, $likeIndex)
    {
        $str = null;
        if (!is_null($likeIndex) && $likeIndex != 0) {
            if ($likeIndex == 1)
                $str = '%' . $value . '%'; // like is any
                elseif ($likeIndex == 2)
                $str = $value . '%'; // like is left
                elseif ($likeIndex == 3)
                $str = '%' . $value; // like is right
        }
        return $str;
    }
    
    
    /* parse not like conditions in query */
    public static function notLike($value, $notLikeIndex)
    {
        $str = null;
        if (!is_null($notLikeIndex) && $notLikeIndex != 0) {
            if ($notLikeIndex == 1)
                $str = '%' . $value . '%'; // like is any
                elseif ($notLikeIndex == 2)
                $str = $value . '%'; // like is left
                elseif ($notLikeIndex == 3)
                $str = '%' . $value; // like is right
        }
        return $str;
    }
    
    /* Return number value from sql like "COUNT, SUM" and other similar methods which returns one number value
     * query must be full sql string and must return one value.
     * For example: select count(id) from tablename
     */
    public static function getNumber($sqlStr, $bindValues = array())
    {
        $number    = 0;
        $row       = array();
        $stmt      = null;
        $bindValue = null;
        
       
        $stmt = self::$db->prepare($sqlStr);
        if (isset($bindValues) && is_array($bindValues)) {
            if (isset($bindValues['fields']) && is_array($bindValues['fields'])) {
                for ($i = 0; $i < count($bindValues['fields']); $i++) {
                    $bindValue = $bindValues['fields'][$i]["value"];
                    
                    if (isset($bindValues['fields'][$i]['like']) && !is_null($bindValues['fields'][$i]['like']))
                        $bindValue = self::like($bindValue, $bindValues['fields'][$i]['like']);
                        if (isset($bindValues['fields'][$i]['notLike']) && !is_null($bindValues['fields'][$i]['notLike']))
                            $bindValue = self::notLike($bindValue, $bindValues['fields'][$i]['notLike']);
                            
                            $stmt->bindValue($bindValues['fields'][$i]['name'], $bindValue, $bindValues['fields'][$i]['dataType']);
                }
            } elseif (isset($bindValues['name'])) {
                $bindValue = $bindValues["value"];
                
                if (isset($bindValues['like']) && !is_null($bindValues['like']))
                    $bindValue = self::like($bindValue, $bindValues['like']);
                    if (isset($bindValues['notLike']) && !is_null($bindValues['notLike']))
                        $bindValue = self::notLike($bindValue, $bindValues['notLike']);
                        
                        $stmt->bindValue($bindValues['name'], $bindValue, $bindValues['dataType']);
            }
        }
        $stmt->execute();
        try {
            if (false !== ($row = $stmt->fetch(PDO_FETCH_NUM))) {
                unset($stmt);
                $number = $row[0];
                return $number;
            }
        }
        catch (\PDOException $exception) {
            self::addErrorMessage("Error: code:" . $exception->getCode() . ", info:" . $exception->getMessage());
            throw new \Exception($exception->getCode() . " " . $exception->getMessage());
        }
        return $number;
    }
    
    /* Fetch single sql record or null if error detected
     * query must be full sql string
     */
    public static function fetchRow($sqlStr, $bindValues = array(), $fetch_mode = null)
    {
        
        $stmt = null;
        $row  = array();
        
        $bindValue = null;
        /*
         $fetchMode = PDO_FETCH_ASSOC;
         if (!is_null($fetch_mode)) {
         $fetchMode = $fetch_mode;
         }
         */
        
        
        
        try {
            $stmt = self::$db->prepare($sqlStr);
            
            if (isset($bindValues['fields']) && is_array($bindValues['fields'])) {
                for ($i = 0; $i < count($bindValues['fields']); $i++) {
                    $bindValue = $bindValues['fields'][$i]["value"];
                    
                    if (isset($bindValues['fields'][$i]['like']) && !is_null($bindValues['fields'][$i]['like']))
                        $bindValue = self::like($bindValue, $bindValues['fields'][$i]['like']);
                        if (isset($bindValues['fields'][$i]['notLike']) && !is_null($bindValues['fields'][$i]['notLike']))
                            $bindValue = self::notLike($bindValue, $bindValues['fields'][$i]['notLike']);
                            
                            $stmt->bindValue($bindValues['fields'][$i]['name'], $bindValue, $bindValues['fields'][$i]['dataType']);
                }
            } elseif (isset($bindValues['name'])) {
                $bindValue = $bindValues["value"];
                
                if (isset($bindValues['like']) && !is_null($bindValues['like']))
                    $bindValue = self::like($bindValue, $bindValues['like']);
                    if (isset($bindValues['notLike']) && !is_null($bindValues['notLike']))
                        $bindValue = self::notLike($bindValue, $bindValues['notLike']);
                        
                        $stmt->bindValue($bindValues['name'], $bindValue, $bindValues['dataType']);
            }
            
            
            $stmt->execute();
            
            
            if (false !== ($row = $stmt->fetch(PDO_FETCH_ASSOC))) {
                unset($stmt);
                return $row;
            }
            
        }
        catch (\PDOException $exception) {
            self::addErrorMessage("Error: code:" . $exception->getCode() . ", info:" . $exception->getMessage());
            throw new \Exception($exception->getCode() . " " . $exception->getMessage());
        }
        return null;
        
    }
    
    /* Fetch sql records or null if error detected
     * query must be full sql string
     */
    public static function fetchRows($sqlStr, $bindValues = array(), $fetch_mode = null)
    {
        $dbType    = self::$dbParams['type'];
        $stmt      = null;
        $rows      = array();
        $bindValue = null;
        $fetchMode = PDO_FETCH_ASSOC;
        if (!is_null($fetch_mode)) {
            $fetchMode = $fetch_mode;
        }
        
        if (isset($bindValues['offset']) && is_array($bindValues['offset'])) {
            
            if ($dbType == 'mysql')
                $sqlStr .= " limit :min, :max";
                elseif ($dbType == 'pgsql')
                $sqlStr .= " limit :max offset :min";
        }
        
        
        $stmt = self::$db->prepare($sqlStr);
        
        if (isset($bindValues['fields']) && is_array($bindValues['fields'])) {
            for ($i = 0; $i < count($bindValues['fields']); $i++) {
                $bindValue = $bindValues['fields'][$i]["value"];
                
                if (isset($bindValues['fields'][$i]['like']) && !is_null($bindValues['fields'][$i]['like']))
                    $bindValue = self::like($bindValue, $bindValues['fields'][$i]['like']);
                    if (isset($bindValues['fields'][$i]['notLike']) && !is_null($bindValues['fields'][$i]['notLike']))
                        $bindValue = self::notLike($bindValue, $bindValues['fields'][$i]['notLike']);
                        
                        $stmt->bindValue($bindValues['fields'][$i]['name'], $bindValue, $bindValues['fields'][$i]['dataType']);
            }
        } elseif (isset($bindValues['name'])) {
            
            $bindValue = $bindValues["value"];
            
            if (isset($bindValues['like']) && !is_null($bindValues['like']))
                $bindValue = self::like($bindValue, $bindValues['like']);
                if (isset($bindValues['notLike']) && !is_null($bindValues['notLike']))
                    $bindValue = self::notLike($bindValue, $bindValues['notLike']);
                    
                    $stmt->bindValue($bindValues['name'], $bindValue, $bindValues['dataType']);
        }
        
        if (isset($bindValues['offset']) && is_array($bindValues['offset'])) {
            for ($i = 0; $i < count($bindValues['offset']); $i++) {
                $stmt->bindValue($bindValues['offset'][$i]['name'], $bindValues['offset'][$i]['value'], $bindValues['offset'][$i]['dataType']);
            }
        }
        $stmt->execute();
        
        try {
            if (false !== ($rows = $stmt->fetchAll($fetchMode))) {
                unset($stmt);
                return $rows;
            }
        }
        catch (\PDOException $exception) {
            self::addErrorMessage("Error: code:" . $exception->getCode() . ", info:" . $exception->getMessage());
            throw new \Exception($exception->getCode() . " " . $exception->getMessage());
        }
        
        return null;
    }
    
    /* Executes query for "insert / update / delete methods" */
    public static function update($sqlStr, $bindValues = array())
    {
        $stmt = null;
        try {
            $stmt = self::$db->prepare($sqlStr);
            
            if (!empty($bindValues['fields']) && is_array($bindValues['fields'])) {
                for ($i = 0; $i < count($bindValues['fields']); $i++) {
                    $bindValue = $bindValues['fields'][$i]["value"];
                    
                    if (isset($bindValues['fields'][$i]['like']) && !is_null($bindValues['fields'][$i]['like'])) {
                        $bindValue = self::like($bindValue, $bindValues['fields'][$i]['like']);
                    }
                    if (isset($bindValues['fields'][$i]['notLike']) && !is_null($bindValues['fields'][$i]['notLike'])) {
                        $bindValue = self::notLike($bindValue, $bindValues['fields'][$i]['notLike']);
                    }
                    $stmt->bindValue($bindValues['fields'][$i]['name'], $bindValue, $bindValues['fields'][$i]['dataType']);
                }
            } elseif (isset($bindValues['name'])) {
                $bindValue = $bindValues["value"];
                
                if (!empty($bindValues['like']))
                    $bindValue = self::like($bindValue, $bindValues['like']);
                    if (!empty($bindValues['notLike']))
                        $bindValue = self::notLike($bindValue, $bindValues['notLike']);
                        
                        $stmt->bindValue($bindValues['name'], $bindValue, $bindValues['dataType']);
            }
            
            $stmt->execute();
            
        }catch (\PDOException $pdoe) {            
            self::addErrorMessage("Error insert or updating table:\n" . $sqlStr . "\n" . $pdoe->getMessage());
            self::setError(true);
        }
        
        if(self::getError()===true){
            return null;
        }
        
        return $stmt;
    }
    
    /* Executes query for insert and if database type is mysql returns last inserted id */
    public static function insert($sqlStr, $bindValues = array(), $params=array())
    {
        $paramsLocal=array(
            'returnlastInsertId'=>self::$dbParams['returnlastInsertId']
        );
        
        if(isset($params['returnlastInsertId']) && is_bool($params['returnlastInsertId'])){
            $paramsLocal['returnlastInsertId']=$params['returnlastInsertId'];
        }

        $stmt = self::update($sqlStr, $bindValues);
        
        if ($paramsLocal['returnlastInsertId']===true && self::getDbType() == "mysql"){
            return self::$db->lastInsertId();
        }
        
        return $stmt;
    }
    
    /* Unset connection object */
    public static function close()
    {
        self::$db = null;
    }
    /* get next sequence value
     * Since 0.10
     */
    public static function nextVal($sequence)
    {
        $dbType = self::getDbType();
        $seqId  = 0;
        $ret    = array();
        $sqlStr = "";
        
        if ($dbType == 'pgsql') {
            $sqlStr = "SELECT nextval('" . $sequence . "')";
            $ret    = self::$db->query($sqlStr)->fetch(PDO_FETCH_NUM);
            $seqId  = $ret[0];
        }
        
        return $seqId;
    }
    
    /* Returns last inserted id for mysql */
    public static function lastInsertedId()
    {
        $lastId = 0;
        $ret    = array();
        $sqlStr = '';
        
        if (self::getDbType() == 'mysql') {
            $lastId = self::$db->lastInsertId();
        }
        
        return $lastId;
    }
    
    /* Start transaction */
    public static function beginTransaction()
    {
        self::$db->beginTransaction();
    }
    
    /* Rollback transaction */
    public static function rollback()
    {
        self::$db->rollBack();
    }
    
    /* Commit transaction */
    public static function commit()
    {
        self::$db->commit();
    }
    
    /* Checks if row is array and has records */
    public static function hasRows($row)
    {
        if (!is_null($row) && is_array($row) && count($row) > 0)
            return true;
            return false;
    }
    
    /* This returned  sql limitation code can be add at last of sql string and when changing database type this will changed automaticaly */
    public static function limitStr($min, $max)
    {
        $dbType       = self::getDbType();
        $str          = array('mysql'=>null,'pgsql'=>null);  
        
        if(isset($min) && isset($max)){
            $str['mysql'] = " limit " .$min." , ".$max;
            $str['pgsql'] = " limit ".$max." offset ".$min;
        }else{
            if(isset($min) && !isset($max)){
                $str['mysql'] = " limit " .$min;
                $str['pgsql'] = " limit ".$min;
            }elseif(!isset($min) && isset($max)){
                $str['mysql'] = " limit " .$max;
                $str['pgsql'] = " limit ".$max;
            }
        }
        
        
        return $str[$dbType];
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
     * Since 0.10
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
    
    
    /* Returns integer or null
     * Since 0.10
     */
    public static function setInt($digit, $params=array())
    {
        
        $paramsLocal=array(
            'defaultReturnValue'=>"NULL"
        );
        
        if(isset($params['defaultReturnValue'])){
            $paramsLocal['defaultReturnValue']=$params['defaultReturnValue'];
        }
        
        if (isset($digit) && HelperDb::isInt($digit)===true){
            return (int)$digit;
        }
        
        return $paramsLocal['defaultReturnValue'];
    }
    
    /* Returns double or null
     * Since 0.10
     */
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
                if(doubleval($digit)==0.00){
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
}
?>
