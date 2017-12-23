<?php
# Database PDO utilities for MySQL and PostgreSQL
# Writen By Kakhaber Kashmadze <info@soft.ge>
# Licensed under MIT License
# Version 1.0

namespace DbUtils;

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
    
    public static function init($params=array(), $dbPdo)
    {
		self::$dbParams=$params;
		if(empty($_SERVER['HTTP_HOST'])){
			self::$endLine="\n";
		}

       self::$db=$dbPdo;
       
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
    public static function getErrorMessage($formatted = 1)
    {
        $str           = '';
        $errorMessages = self::$errorMessages;
        
        for ($i = 0; $i < count($errorMessages); $i++) {
            if ($formatted == 1)
                $str .= '<div class="errormessage">' . $errorMessages[$i] . '</div>';
            else
                $str .= $errorMessages[$i] . "\n";
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
        
        $fetchMode = PDO_FETCH_ASSOC;
        if (!is_null($fetch_mode)) {
            $fetchMode = $fetch_mode;
        }
        
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
        catch (Exception $exception) {
            self::addErrorMessage("Error: code:" . $exception->getCode() . ", info:" . $exception->getMessage());
            throw new Exception($exception->getCode() . " " . $exception->getMessage());
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
        catch (Exception $exception) {
            self::addErrorMessage("Error: code:" . $exception->getCode() . ", info:" . $exception->getMessage());
            throw new Exception($exception->getCode() . " " . $exception->getMessage());
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
        catch (Exception $exception) {
            self::addErrorMessage("Error: code:" . $exception->getCode() . ", info:" . $exception->getMessage());
            throw new Exception($exception->getCode() . " " . $exception->getMessage());
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
        }
        catch (Exception $pdoe) {
            self::addErrorMessage("Error insert or updating table:\n" . $sqlStr . "\n" . $pdoe->getMessage());
            self::setError(true);
        }
        return $stmt;
    }
    
    /* Executes query for insert and if database type is mysql returns last inserted id */
    public static function insert($sqlStr, $bindValues = array())
    {
        $ret = self::update($sqlStr, $bindValues);
        if (self::getDbType() == "mysql")
            return self::$db->lastInsertId();
        return $ret;
    }
    
    /* Unset connection object */
    public static function close()
    {
        self::$object = null;
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
        $str          = array();
        $str['mysql'] = " limit " . $min . ", " . $max;
        $str['pgsql'] = " limit " . $max . " offset " . $min;
        
        return $str[$dbType];
    }
    
}
?>
