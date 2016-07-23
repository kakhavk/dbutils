<?php
# Database PDO utilities for MySQL and PostgreSQL
# Writen By Kakhaber Kashmadze <info@soft.ge>
# Licensed under MIT License
# Version 1.9

define('LIKE_ANY', 1);
define('LIKE_LEFT', 2);
define('LIKE_RIGHT', 3);

define('NOT_LIKE_ANY', 1);
define('NOT_LIKE_LEFT', 2);
define('NOT_LIKE_RIGHT', 3);

class DbUtils extends PDO{
    
    protected $dbType = 'mysql'; // by default set to mysql. can be changed to mysql, pgsql, mssql ...
    
    protected $attrEmulatePrepares = 0; // needed for supporting multiple queries
    protected $errorMessages = array();
    protected $isError = 0;
    protected $options = array(PDO::ATTR_TIMEOUT => 30, PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_EMULATE_PREPARES => 0);
    protected $dsn = null;
    protected $port = null;
    
    private static $params = array(
		'dateFrom'=>null,
		'dateTo'=>null
    );

    public function __construct($dbType=null)
    {
        if(!empty($dbType))  $this->setDbType($dbType);       

		$this->constructConfiguration();
		parent::__construct($this->dsn, DBUSER, DBPASS, $this->options);
		if ($this->dbType == "mysql") {
			parent::prepare("SET NAMES 'utf8'")->execute();
		}        
    }
        
    /* make construct */
    function constructConfiguration(){
       $dbType = $this->getDbType();
        
        $host   = DBHOST;
        $dbname = DBNAME;
        
        $port = null;
        $dsn  = null;
        if (!is_null($this->port) && trim($this->port !== '')) $port = $this->port;
        if (is_null($this->dsn)){
			$this->dsn = $dbType . ':host=' . $host . (!is_null($port) ? ';port=' . $port : '') . ';dbname=' . $dbname;
			if ($dbType == 'mssql') {
				$this->dsn    = 'dblib:host=' . $host . (!is_null($port) ? ':' . $port : '') . ';dbname=' . $dbname;
			}
		}        
    }
    
    /* Sets Database Type: mysql or pgsql */
    public function setDbType($dbType)
    {
        $dbtype = strtolower(trim($dbType));
        if ($dbtype == 'microsoftsql')
            $dbtype = 'mssql';
        if ($dbtype == 'mysqli')
            $dbtype = 'mysql';
        if ($dbtype == 'postgresql')
            $dbtype = 'pgsql';
        
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
    
    
    /* Set parameters values */
    public function setParams($params = array())
    {
        if (is_array($params)) {
            foreach ($params as $k => $v)
                $this->params[$k] = $v;
        }
    }
    
    /* Set parameter value */
    private function setParam($key, $value){
		if(!empty($key)) $this->params[$key]=$valuel;
    }
    
    /* Get ATTR_EMULATE_PREPARES for connection */
    public function getAttrEmulatePrepares()
    {
        return $this->attrEmulatePrepares;
    }
    
    /* Set connectin dsn */
    public function setDsn($dsn = null)
    {
        if (isset($dsn) && !empty($dsn))
            $this->dsn = $dsn;
    }
    /* Get connection dsn */
    public function getDsn()
    {
        return $this->options;
    }
    
    /* Set connectin options */
    public function setOptions($options = array())
    {
        if (isset($options) && is_array($options) && !is_null($options))
            $this->options = $options;
        else
            $this->addErrorMessage('options was not set properly');
        
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
        $this->isError = 1;
    }
    /* Get error message string */
    public function getErrorMessage($formatted = 1)
    {
        $str           = '';
        $errorMessages = $this->errorMessages;
        
        for ($i = 0; $i < count($errorMessages); $i++) {
            if ($formatted == 1)
                $str .= '<div class="errormessage">' . $errorMessages[$i] . '</div>';
            else
                $str .= $errorMessages[$i] . "\n";
        }
        
        return $str;
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
    
    
    /* parse like conditions in query */
    protected function like($value, $likeIndex)
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
    protected function notLike($value, $notLikeIndex)
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
    public function getNumber($sqlStr, $bindValues = array())
    {
        $number    = 0;
        $row       = array();
        $stmt      = null;
        $bindValue = null;
        
        $fetchMode = PDO::FETCH_ASSOC;
        if (!is_null($fetch_mode)) {
            $fetchMode = $fetch_mode;
        }
        
        $stmt = parent::prepare($sqlStr);
        if (isset($bindValues) && is_array($bindValues)) {
            if (isset($bindValues['fields']) && is_array($bindValues['fields'])) {
                for ($i = 0; $i < count($bindValues['fields']); $i++) {
                    $bindValue = $bindValues['fields'][$i]["value"];
                    
                    if (isset($bindValues['fields'][$i]['like']) && !is_null($bindValues['fields'][$i]['like']))
                        $bindValue = $this->like($bindValue, $bindValues['fields'][$i]['like']);
                    if (isset($bindValues['fields'][$i]['notLike']) && !is_null($bindValues['fields'][$i]['notLike']))
                        $bindValue = $this->notLike($bindValue, $bindValues['fields'][$i]['notLike']);
                    
                    $stmt->bindValue($bindValues['fields'][$i]['name'], $bindValue, $bindValues['fields'][$i]['dataType']);
                }
            } elseif (isset($bindValues['name'])) {
                $bindValue = $bindValues["value"];
                
                if (isset($bindValues['like']) && !is_null($bindValues['like']))
                    $bindValue = $this->like($bindValue, $bindValues['like']);
                if (isset($bindValues['notLike']) && !is_null($bindValues['notLike']))
                    $bindValue = $this->notLike($bindValue, $bindValues['notLike']);
                
                $stmt->bindValue($bindValues['name'], $bindValue, $bindValues['dataType']);
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
            $this->addErrorMessage("Error: code:" . $exception->getCode() . ", info:" . $exception->getMessage());
            throw new Exception($exception->getCode() . " " . $exception->getMessage());
        }
        return $number;
    }
    
    /* Fetch single sql record or null if error detected
     * query must be full sql string
     */
    public function fetchRow($sqlStr, $bindValues = array(), $fetch_mode = null)
    {
        
        $stmt = null;
        $row  = array();
        
        $bindValue = null;
        $fetchMode = PDO::FETCH_ASSOC;
        if (!is_null($fetch_mode)) {
            $fetchMode = $fetch_mode;
        }
        
        try {
            $stmt = parent::prepare($sqlStr);
            
            if (isset($bindValues['fields']) && is_array($bindValues['fields'])) {
                for ($i = 0; $i < count($bindValues['fields']); $i++) {
                    $bindValue = $bindValues['fields'][$i]["value"];
                    
                    if (isset($bindValues['fields'][$i]['like']) && !is_null($bindValues['fields'][$i]['like']))
                        $bindValue = $this->like($bindValue, $bindValues['fields'][$i]['like']);
                    if (isset($bindValues['fields'][$i]['notLike']) && !is_null($bindValues['fields'][$i]['notLike']))
                        $bindValue = $this->notLike($bindValue, $bindValues['fields'][$i]['notLike']);
                    
                    $stmt->bindValue($bindValues['fields'][$i]['name'], $bindValue, $bindValues['fields'][$i]['dataType']);
                }
            } elseif (isset($bindValues['name'])) {
                $bindValue = $bindValues["value"];
                
                if (isset($bindValues['like']) && !is_null($bindValues['like']))
                    $bindValue = $this->like($bindValue, $bindValues['like']);
                if (isset($bindValues['notLike']) && !is_null($bindValues['notLike']))
                    $bindValue = $this->notLike($bindValue, $bindValues['notLike']);
                
                $stmt->bindValue($bindValues['name'], $bindValue, $bindValues['dataType']);
            }
            
            $stmt->execute();
            
            if (false !== ($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
                unset($stmt);
                return $row;
            }
        }
        catch (PDOException $exception) {
            $this->addErrorMessage("Error: code:" . $exception->getCode() . ", info:" . $exception->getMessage());
            throw new Exception($exception->getCode() . " " . $exception->getMessage());
        }
        return null;
    }
    
    /* Fetch sql records or null if error detected
     * query must be full sql string
     */
    public function fetchRows($sqlStr, $bindValues = array(), $fetch_mode = null)
    {
        $dbType    = $this->getDbType();
        $stmt      = null;
        $rows      = array();
        $bindValue = null;
        $fetchMode = PDO::FETCH_ASSOC;
        if (!is_null($fetch_mode)) {
            $fetchMode = $fetch_mode;
        }
        
        if (isset($bindValues['offset']) && is_array($bindValues['offset'])) {
            
            if ($dbType == 'mysql')
                $sqlStr .= " limit :min, :max";
            elseif ($dbType == 'pgsql')
                $sqlStr .= " limit :max offset :min";
        }
        
        
        $stmt = parent::prepare($sqlStr);
        
        if (isset($bindValues['fields']) && is_array($bindValues['fields'])) {
            for ($i = 0; $i < count($bindValues['fields']); $i++) {
                $bindValue = $bindValues['fields'][$i]["value"];
                
                if (isset($bindValues['fields'][$i]['like']) && !is_null($bindValues['fields'][$i]['like']))
                    $bindValue = $this->like($bindValue, $bindValues['fields'][$i]['like']);
                if (isset($bindValues['fields'][$i]['notLike']) && !is_null($bindValues['fields'][$i]['notLike']))
                    $bindValue = $this->notLike($bindValue, $bindValues['fields'][$i]['notLike']);
                
                $stmt->bindValue($bindValues['fields'][$i]['name'], $bindValue, $bindValues['fields'][$i]['dataType']);
            }
        } elseif (isset($bindValues['name'])) {
            
            $bindValue = $bindValues["value"];
            
            if (isset($bindValues['like']) && !is_null($bindValues['like']))
                $bindValue = $this->like($bindValue, $bindValues['like']);
            if (isset($bindValues['notLike']) && !is_null($bindValues['notLike']))
                $bindValue = $this->notLike($bindValue, $bindValues['notLike']);
            
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
        catch (PDOException $exception) {
            $this->addErrorMessage("Error: code:" . $exception->getCode() . ", info:" . $exception->getMessage());
            throw new Exception($exception->getCode() . " " . $exception->getMessage());
        }
        
        return null;
    }
    
    /* Executes query for "insert / update / delete methods" */
    public function update($sqlStr, $bindValues = array())
    {
        $stmt = null;
        try {
            $stmt = parent::prepare($sqlStr);
            
            if (!empty($bindValues['fields']) && is_array($bindValues['fields'])) {
                for ($i = 0; $i < count($bindValues['fields']); $i++) {
                    $bindValue = $bindValues['fields'][$i]["value"];
                    
                    if (isset($bindValues['fields'][$i]['like']) && !is_null($bindValues['fields'][$i]['like'])) {
                        $bindValue = $this->like($bindValue, $bindValues['fields'][$i]['like']);
                    }
                    if (isset($bindValues['fields'][$i]['notLike']) && !is_null($bindValues['fields'][$i]['notLike'])) {
                        $bindValue = $this->notLike($bindValue, $bindValues['fields'][$i]['notLike']);
                    }
                    $stmt->bindValue($bindValues['fields'][$i]['name'], $bindValue, $bindValues['fields'][$i]['dataType']);
                }
            } elseif (isset($bindValues['name'])) {
                $bindValue = $bindValues["value"];
                
                if (!empty($bindValues['like']))
                    $bindValue = $this->like($bindValue, $bindValues['like']);
                if (!empty($bindValues['notLike']))
                    $bindValue = $this->notLike($bindValue, $bindValues['notLike']);
                
                $stmt->bindValue($bindValues['name'], $bindValue, $bindValues['dataType']);
            }
            
            $stmt->execute();
        }
        catch (PDOException $pdoe) {
            $this->addErrorMessage("Error insert or updating table:\n" . $sqlStr . "\n" . $pdoe->getMessage());
            $this->setIsError(1);
        }
        return $stmt;
    }
    
    /* Executes query for insert and if database type is mysql returns last inserted id */
    public function insert($sqlStr, $bindValues = array())
    {
        $ret = $this->update($sqlStr, $bindValues);
        if ($this->getDbType() == "mysql")
            return parent::lastInsertId();
        return $ret;
    }
    
    /* Unset connection object */
    public function close()
    {
        $this->object = null;
    }
    /* get next sequence value
     * Since 0.10
     */
    public function nextVal($sequence)
    {
        $dbType = $this->getDbType();
        $seqId  = 0;
        $ret    = array();
        $sqlStr = "";
        
        if ($dbType == 'pgsql') {
            $sqlStr = "SELECT nextval('" . $sequence . "')";
            $ret    = parent::query($sqlStr)->fetch(PDO::FETCH_NUM);
            $seqId  = $ret[0];
        }
        
        return $seqId;
    }
    
    /* Returns last inserted id for mysql */
    public function lastInsertedId()
    {
        $lastId = 0;
        $ret    = array();
        $sqlStr = '';
        
        if ($this->getDbType() == 'mysql') {
            $lastId = parent::lastInsertId();
        }
        
        return $lastId;
    }
    
    /* Start transaction */
    public function beginTransaction()
    {
        parent::beginTransaction();
    }
    
    /* Rollback transaction */
    public function rollback()
    {
        parent::rollBack();
    }
    
    /* Commit transaction */
    public function commit()
    {
        parent::commit();
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
        $str['mysql'] = " limit " . $min . ", " . $max;
        $str['pgsql'] = " limit " . $max . " offset " . $min;
        
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
    public function strNull($str, $addslashes=0)
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
    public function connectionAttributes($attributeName)
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
        
        if (!empty($attributeName)) {
            $attr = parent::getAttribute(constant("PDO::ATTR_$attributeName"));
        } else {
            foreach ($pdoAttributes as $k => $v) {
                if (null !== (parent::getAttribute(constant("PDO::ATTR_$v"))))
                    $attr .= '<div><span style="color:navy;">' . $k . '</span>:' . parent::getAttribute(constant('PDO::ATTR_' . $v)) . '</div>';
            }
        }
        return $attr;
    }
    
    /* Parse date and convert it's format, or return null if is not set properly */
	function convertDate($date){
		$dateFrom='dd/mm/yyyy';
		$dateTo='yyyy/mm/dd';
		$dateExplode=array();
		$parsedDate=null;
		
		if(!is_null($this->params['dateFrom'])) $dateFrom=$this->params['dateFrom'];
		if(!is_null($this->params['dateTo'])) $dateTo=$this->params['dateTo'];
		
		
		if(!empty($date)){
			if($dateFrom=='dd/mm/yyyy')	$dateExplode=explode('/', $date);
			if(count($dateExplode)==3){
				if($dateTo=='yyyy/mm/dd') $parsedDate=$dateExplode[2].'/'.$dateExplode[1].'/'.$dateExplode[0];
			}
			if(!is_null($parsedDate)) return $parsedDate;
		}
		
		return "NULL";
	}    
    
    
}

?>
