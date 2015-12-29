<?php
# Database PDO utilities for MySQL and PostgreSQL
# Writen By Kakhaber Kashmadze <info@soft.ge>
# Licensed under MIT License
# Version 1.4.1

class DbUtils
{
    
    protected $dbType; // mysql, pgsql, mssql
    
    protected $attrEmulatePrepares=0; // needed for supporting multiple queries
    protected $errorMessages = array();
    protected $isError = 0;
    protected $options = array();
    protected $dsn=null;
    
    protected $port=null;
    
    private $params=array();
    
    
    public function __construct()
    {
        $this->setDbType('pgsql'); //by default database type is postgresql
        $this->setAttrEmulatePrepares(0);
        $this->options=array(
				PDO::ATTR_TIMEOUT => 30,
				PDO::ATTR_PERSISTENT => true,
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
				PDO::ATTR_EMULATE_PREPARES => $this->attrEmulatePrepares
            );
        $this->setParams(array('dbhost'=>DBHOST, 'dbuser'=>DBUSER, 'dbpass'=>DBPASS, 'dbname'=>DBNAME));
        $this->setDsn(null);
        
    }
    
    /* Sets Database Type: mysql or pgsql */
    public function setDbType($dbType)
    {    
        $dbtype = strtolower(trim($dbType));
        if ($dbtype == 'microsoftsql') $dbtype = 'mssql';
        if ($dbtype == 'mysqli') $dbtype = 'mysql';
        if ($dbtype == 'postgresql') $dbtype = 'pgsql';
        
        $this->dbType = $dbtype;
    }
    
    /* Returns Database Type: mysql or pgsql */
    public function getDbType()
    {
        return $this->dbType;
    }
    /* Set ATTR_EMULATE_PREPARES for connection */
    public function setAttrEmulatePrepares($value)
    {
        $this->attrEmulatePrepares = $value;
    }
    

    /* Set Host for database */
    public function setParams($params=array())
    {
        if(is_array($params)){
			foreach($params as $k => $v) $this->params[$k]=$v;
        }
    }
    
    
    
    /* Get ATTR_EMULATE_PREPARES for connection */
    public function getAttrEmulatePrepares()
    {
        return $this->attrEmulatePrepares;
    }
    
    /* Set connectin dsn */
    public function setDsn($dsn=null)
    {
		if(isset($dsn) && !empty($dsn)) $this->dsn = $dsn;		
    }
    /* Get connection dsn */
    public function getDsn()
    {
        return $this->options;
    }
    
    /* Set connectin options */
    public function setOptions($options=array())
    {
		if(isset($options) && is_array($options) && !is_null($options)) $this->options = $options;
		else $this->addErrorMessage('options was not set properly');
		
    }
    /* Get connection options */
    public function getOptions()
    {
        return $this->options;
    }
    
    /* Set port for connection */
    public function setPort($value)
    {
        $this->port = $value;
    }
    /* Get port for connection */
    public function getPort()
    {
        return $this->port;
    }
    
    /* Set error message string */
    public function addErrorMessage($message)
    {
        array_push($this->errorMessages, $message);
        $this->isError=1;
    }
    /* Get error message string */
    public function getErrorMessage()
    {
		$formatedStr='';
		$errorMessages=$this->errorMessages;		
		
		for($i=0; $i<count($errorMessages); $i++) $formatedStr.='<div class="errormessage">'.$errorMessages[$i].'</div>';
		
		return $formatedStr;
    }
    
    /* Set error state */
    public function setIsError($error)
    {
        $this->isError = $error;
    }
    
    /* Get error state */
    public function getIsError()
    {
        return $this->isError;
    }
    
    
    /* Create connection */
    public function connect()
    {
        
        static $con;
        $dbType = $this->getDbType();
        
        $host=$this->params['dbhost'];
        $user=$this->params['dbuser'];
        $pass=$this->params['dbpass'];
        $dbname=$this->params['dbname'];
        
        $port=null;
        $dsn=null;
        if(!is_null($this->port) && trim($this->port!=='')) $port=$this->port;
        if(!is_null($this->dsn)) $dsn=$this->dsn;
        
        $options = $this->options;
        
        if (!isset($con) || is_null($con)) {
            try {
				if(is_null($dsn)){
					$dsn = $dbType.':host='.$host .(!is_null($port)?';port='.$port:''). ';dbname='.$dbname;
					if($dbType=='mssql'){
						$dsn='dblib:host='.$host.(!is_null($port)?':'.$port:'').';dbname='.$dbname;
						$options=null;
					}					
				}

				$con = new PDO($dsn, $user, $pass, $options);                
                if ($dbType == "mysql") {
                    $con->prepare("SET NAMES 'utf8'")->execute();
                }
            }
            catch (PDOException $e) {

                $this->addErrorMessages("Problem connecting to database: ".$e->getMessage());
                return false;
            }
            
        }
        
        return $con;
    }    
    
    /* parse like condition is query */
    public function like($value, $likeIndex)
    {
        $str = null;
        if (!is_null($likeIndex) && $likeIndex != 0) {
            if ($likeIndex == 1)
                $str = '%'.$value.'%'; // like is any
            elseif ($likeIndex == 2)
                $str = $value.'%'; // like is left
            elseif ($likeIndex == 3)
                $str = '%'.$value; // like is right
        }
        return $str;
    }
    
    
    /* Return number value from sql like "COUNT, SUM" and other similar methods which returns one number value
     * query must be full sql string and must return one value.
     * For example: select count(id) from tablename
     */
    public function getNumber($conn, $sqlStr, $bindValues = array())
    {
        $number    = 0;
        $row       = array();
        $stmt      = null;
        $bindValue = null;
        
        $fetchMode = PDO::FETCH_ASSOC;
        if (!is_null($fetch_mode)) {
            $fetchMode = $fetch_mode;
        }
        
        $stmt = $conn->prepare($sqlStr);
        if (!is_null($bindValues)) {
            if (is_array($bindValues['fields'])) {
                for ($i = 0; $i < count($bindValues['fields']); $i++) {
                    $bindValue = $bindValues['fields'][$i]["value"];
                    
                    if (!is_null($bindValues['fields'][$i]['like'])) {
                        $bindValue = $this->like($bindValue, $bindValues['fields'][$i]['like']);
                    }
                    $stmt->bindValue($bindValues['fields'][$i]['name'], $bindValue, $bindValues['fields'][$i]['dataType']);
                }
            } elseif (isset($bindValues['name'])) {
                $stmt->bindValue($bindValues['name'], $bindValues['value'], $bindValues['dataType']);
            }
        }
        $stmt->execute();
        try {
            if (false !== ($row = $stmt->fetch(PDO::FETCH_NUM))) {
                unset($stmt);
                $number = $row[0];
                return $number;
            }
        }
        catch (PDOException $exception) {
            $this->addErrorMessage("Error: code:".$exception->getCode().", info:".$exception->getMessage());
            throw new Exception($exception->getCode()." ".$exception->getMessage());
        }
        return $number;
    }
    
    /* Fetch sql records or null if error detected
     * query must be full sql string
     */
    public function fetchRows($conn, $sqlStr, $bindValues = array(), $fetch_mode = null)
    {
        $dbType    = $this->getDbType();
        $stmt      = null;
        $rows      = array();
        $bindValue = null;
        $fetchMode = PDO::FETCH_ASSOC;
        if (!is_null($fetch_mode)) {
            $fetchMode = $fetch_mode;
        }
        
        if (is_array($bindValues['offset'])) {
            
            if ($dbType == 'mysql')
                $sqlStr .= " limit :min, :max";
            elseif ($dbType == 'pgsql')
                $sqlStr .= " limit :max offset :min";
        }
        
        
        $stmt = $conn->prepare($sqlStr);
        
        if (is_array($bindValues['fields'])) {
            for ($i = 0; $i < count($bindValues['fields']); $i++) {
                $bindValue = $bindValues['fields'][$i]["value"];
                
                if (!is_null($bindValues['fields'][$i]['like'])) {
                    $bindValue = $this->like($bindValue, $bindValues['fields'][$i]['like']);
                }
                
                $stmt->bindValue($bindValues['fields'][$i]['name'], $bindValue, $bindValues['fields'][$i]['dataType']);
            }
        }
        if (is_array($bindValues['offset'])) {
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
        catch (PDOException $exception) {
            $this->addErrorMessage("Error: code:".$exception->getCode().", info:".$exception->getMessage());
            throw new Exception($exception->getCode()." ".$exception->getMessage());
        }
        
        return null;
    }
    
    
    /* Fetch single sql record or null if error detected
     * query must be full sql string
     */
    public function fetchRow($conn, $sqlStr, $bindValues = array(), $fetch_mode = null)
    {
        
        $stmt = null;
        $row  = array();
        
        $bindValue = null;
        $fetchMode = PDO::FETCH_ASSOC;
        if (!is_null($fetch_mode)) {
            $fetchMode = $fetch_mode;
        }
        
        $stmt = $conn->prepare($sqlStr);
        
        if (is_array($bindValues['fields'])) {
            for ($i = 0; $i < count($bindValues['fields']); $i++) {
                $bindValue = $bindValues['fields'][$i]["value"];
                
                if (!is_null($bindValues['fields'][$i]['like'])) {
                    $bindValue = $this->like($bindValue, $bindValues['fields'][$i]['like']);
                }
                $stmt->bindValue($bindValues['fields'][$i]['name'], $bindValue, $bindValues['fields'][$i]['dataType']);
            }
        } elseif (isset($bindValues['name'])) {
            $stmt->bindValue($bindValues['name'], $bindValues['value'], $bindValues['dataType']);
        }
        
        $stmt->execute();
        
        try {
            if (false !== ($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
                unset($stmt);
                return $row;
            }
        }
        catch (PDOException $exception) {
            $this->addErrorMessage("Error: code:".$exception->getCode().", info:".$exception->getMessage());
            throw new Exception($exception->getCode()." ".$exception->getMessage());
        }
        return null;
    }
    
    /* Executes query for "insert / update / delete methods" */
    public function update($conn, $sqlStr)
    {
        $stmt = null;
        try {
            $stmt = $conn->prepare($sqlStr);
            $stmt->execute();
        }
        catch (PDOException $pdoe) {
            $this->addErrorMessage($pdoe->getMessage());
            $this->setIsError(1);
        }
        return $stmt;
    }
    
    /* Executes query for insert and if database type is mysql returns last inserted id */
    public function insert($conn, $sqlStr)
    {
        $ret = $this->update($conn, $sqlStr);
        if ($this->getDbType() == "mysql")
            return $conn->lastInsertId();
        return $ret;
    }
    
    /* Unset connection object */
    public function close(&$conn)
    {
        $conn = null;
    }
    /* get next sequence value
     * Since 0.10
     */
    public function nextVal($conn, $sequence)
    {
        $dbType = $this->getDbType();
        $seqId  = 0;
        $ret    = array();
        $sqlStr = "";
        
        if ($dbType == 'pgsql') {
            $sqlStr = "SELECT nextval('".$sequence."')";
            $ret    = $conn->query($sqlStr)->fetch(PDO::FETCH_NUM);
            $seqId  = $ret[0];
        }
        
        return $seqId;
    }
    
    /* Returns last inserted id for mysql */
    public function lastInsertedId($conn)
    {
        $lastId = 0;
        $ret    = array();
        $sqlStr = '';
        
        if ($this->getDbType() == 'mysql') {
            $lastId = $conn->lastInsertId();
        }
        
        return $lastId;
    }
    
    /* Start transaction */
    public function beginTransaction($conn)
    {
        $conn->beginTransaction();
    }
    
    /* Rollback transaction */
    public function rollback($conn)
    {
        $conn->rollBack();
    }
    
    /* Commit transaction */
    public function commit($conn)
    {
        $conn->commit();
    }
    
    /* Checks if row is array and has records */
    public function hasRows($row)
    {
        if (!is_null($row) && is_array($row) && count($row) > 0)
            return true;
        return false;
    }
    
    /* This returned  sql limitation code can be add at last of sql string and when changing database type this will changed automaticaly */
    public function limitStr($min, $max)
    {
        $dbType       = $this->getDbType();
        $str          = array();
        $str['mysql'] = " limit ".$min.", ".$max;
        $str['pgsql'] = " limit ".$max." offset ".$min;
        
        return $str[$dbType];
    }
    
    /* For mysql true and false is 0 and 1 for postgresql true and false so it will change by database type values
     * For example:
     * insert into table (id, active) values (12, retBooleanCondition(1));
     * where active is boolean type field inserts in mysql 1 and postgresql true
     */
    public function booleanCondition($value)
    {
        $retStr   = "";
        $dbType   = $this->getDbType();
        $valueStr = trim($value);
        
        if ($dbType == "mysql") {
            if ($valueStr == "false" || $valueStr == "f")
                $retStr = "0";
            elseif ($valueStr == "true" || $valueStr == "t")
                $retStr = "1";
            else
                $retStr = $valueStr;
        } elseif ($dbType == "pgsql") {
            if ($valueStr == 0)
                $retStr = "false";
            elseif ($valueStr == 1)
                $retStr = "true";
            else
                $retStr = $valueStr;
        }
        
        return $retStr;
    }
    
    /* Returns string or null
     * Since 0.10
     */
    public function strNull($str)
    {
        if (!is_null($str) && $str !== "") {
            if (!get_magic_quotes_gpc()) {
                $str = addslashes($str);
            }
            return trim("'".$str."'");
        }
        return "NULL";
    }
    
    /* Returns integer or null
     * Since 0.10
     */
    public function intNull($digit)
    {
        if (!is_null($digit) && trim($digit) !== "" && intval($digit) != 0)
            return $digit;
        return "NULL";
    }
    
    /* Returns double or null
     * Since 0.10
     */
    public function doubleNull($digit)
    {
        if (!is_null($digit) && trim($digit) !== "" && doubleval($digit) != 0.00)
            return $digit;
        return "NULL";
    }

	/* Returns connection attributes*/
    public function connectionAttributes($conn, $attributeName = "")
    {
        $attr = "";
        
        
        $pdoAttributes = array(
            //"Autocommit"=>'AUTOCOMMIT',
            "Case" => "CASE",
            "Client Version" => "CLIENT_VERSION",
            "Connection Status" => "CONNECTION_STATUS",
            "Driver Name" => "DRIVER_NAME",
            "Error Mode" => "ERRMODE",
            "ORACLE Nulls" => "ORACLE_NULLS",
            "Persistent" => "PERSISTENT",
            //"Prefetch"=>"PREFETCH",
            "Server info" => "SERVER_INFO",
            "Server Version" => "SERVER_VERSION"
            //"Timeout"=>"TIMEOUT"
        );
        
        if (isset($attributeName) && !is_null($attributeName) && trim($attributeName) !== "") {
            $attr = $conn->getAttribute(constant("PDO::ATTR_$attributeName"));
        } else {
            foreach ($pdoAttributes as $k => $v) {
                if (null !== ($conn->getAttribute(constant("PDO::ATTR_$v"))))
                    $attr .= '<div><span style="color:navy;">'.$k.'</span>:'.$conn->getAttribute(constant('PDO::ATTR_'.$v)).'</div>';
            }
        }
        return $attr;
    }    
    
    
}

?>
