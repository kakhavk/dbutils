<?php
# Database PDO utilities for MySQL and PostgreSQL
# Writen By Kakhaber Kashmadze <info@soft.ge>
# Licensed under MIT License
# Version 1.0

class DbUtils {

    private $dbType; /* mysql, pgsql */
    public $errorMessage=null;

    function __construct() {
        $this->dbType="";
    }
    
    /* Sets Database Type: mysql or pgsql */
    function setDbType($dbType){
        $dbtype=strtolower(trim($dbType));
        if($dbtype=='mysqli') $dbtype='mysql';
        if($dbtype=='postgresql') $dbtype='pgsql';
        
        $this->dbType=$dbtype;
    }
    
    /* Returns Database Type: mysql or pgsql */
    function getDbType(){
        return $this->dbType;
    }
    
    function connectionAttributes($conn, $attributeName=""){
		$attr="";
		

		$pdoAttributes=array(
			//"Autocommit"=>'AUTOCOMMIT',
			"Case"=>"CASE", 
			"Client Version"=>"CLIENT_VERSION",
			"Connection Status"=>"CONNECTION_STATUS",
			"Driver Name"=>"DRIVER_NAME",
			"Error Mode"=>"ERRMODE",
			"ORACLE Nulls"=>"ORACLE_NULLS", 
			"Persistent"=>"PERSISTENT",
			//"Prefetch"=>"PREFETCH",
			"Server info"=>"SERVER_INFO",
			"Server Version"=>"SERVER_VERSION",
			//"Timeout"=>"TIMEOUT"
			);

		if(isset($attributeName) && !is_null($attributeName) && trim($attributeName)!==""){
			$attr=$conn->getAttribute(constant("PDO::ATTR_$attributeName"));
		}else{
			foreach($pdoAttributes as $k => $v){
				if(null!==($conn->getAttribute(constant("PDO::ATTR_$v"))))
					$attr.='<div><span style="color:navy;">'.$k.'</span></div>'.$conn->getAttribute(constant('PDO::ATTR_'.$v));
			}
		}
		return $attr;
	}
    
    /* Create connection */
    function connect($host, $user, $pass, $dbname) {
        
        static $con;
        $dbType=$this->getDbType();
        
        $options = array(
            PDO::ATTR_TIMEOUT => 30,
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,        		
        );

        if(!isset($con) || is_null($con)){			
			try{
				$dsn = $dbType.':host='.$host.';dbname='.$dbname;
				if($dbType=='oracle' || $dbType=='oci' || $dbType=='oci8') $dsn = 'OCI:dbname='.$dbname.';charset=UTF-8';
				$con=new PDO($dsn, $user, $pass, $options);
				if($dbType=="mysql"){
					$con->prepare("SET NAMES 'utf8'")->execute();
				}
            }catch(PDOException $e){
				$this->errorMessage.="Problem connecting to database: ".$e.getMessage();
				return false;
            }            
            
        }
        
        return $con;
    }

     
    /* Return number value from sql like "COUNT, SUM" and other similar methods which returns one number value
     * query must be full sql string and must return one value. 
     * For example: select count(id) from tablename
     */
    function getNumber($conn, $sqlStr) {
        $number=0;
        $row=array();
        $row=$conn->query($sqlStr)->fetch(PDO::FETCH_NUM);
        if(is_array($row) && count($row)!=0 && is_numeric($row[0])) $number=$row[0];
        return $number;
    }
    
    /* Fetch sql records or null if error detected
     * query must be full sql string
     */
    function fetchRows($conn, $sqlStr, $min=0, $max=0){
        $dbType=$this->getDbType();
        $rs=null;
        $rows=array();
        
	
        if($max != 0) {
            if($dbType=='mysql')
                $sqlStr.=" limit ".$min.", ".$max;
            elseif($dbType=='pgsql')
                $sqlStr .=" limit ".$max." offset ".$min."";
        }

		$rs=$conn->prepare($sqlStr);
        $rs->execute();
        try{
            if(false!==($rows=$rs->fetchAll(PDO::FETCH_ASSOC))) {
                unset($rs);            
                return $rows;
            }
        }catch(PDOException $exception){
            $this->errorMessage="Error: code:".$exception->getCode().", info:".$exception->getMessage();
            throw new Exception($exception->getCode()." ".$exception->getMessage());
        }

        return null;	
    }
     
    
    /* Fetch single sql record or null if error detected
     * query must be full sql string
     */
    function fetchRow($conn, $sqlStr){

        $rs=null;
        $row=array();
        
        $rs=$conn->prepare($sqlStr);
        $rs->execute();
        
		try{
            if(false!==($row=$rs->fetch(PDO::FETCH_ASSOC))) {
                unset($rs);            
                return $row;
            }
        }catch(PDOException $exception){
            $this->errorMessage="Error: code:".$exception->getCode().", info:".$exception->getMessage();
            throw new Exception($exception->getCode()." ".$exception->getMessage());
        }
        return null;
    }
        
    /* Executes query for "insert / update / delete methods" */
    function update($conn, $sqlStr){
        $stmt=null;
        $stmt=$conn->prepare($sqlStr);
		$stmt->execute();
		
        return $stmt;
    }
    
    /* Executes query for insert and if database type is mysql returns last inserted id */
    function insert($conn, $sqlStr) {
        $ret=$this->execSqlUpdate($conn, $sqlStr);
        if($this->getDbType()=="mysql") return $conn->lastInsertId();
        return $ret;
    }    
    
    /* Unset connection object */
    function close(&$conn) {
        $conn=null;
    }
    /* get next sequence value 
    * Since 0.10
    */
    function nextVal($conn, $sequence){
        $dbType=$this->getDbType();
        $seqId=0;
        $ret=array();
        $sqlStr="";
        
        if($dbType=='pgsql'){
            $sqlStr="SELECT nextval('".$sequence."')"; 
            $ret=$conn->query($sqlStr)->fetch(PDO::FETCH_NUM);
            $seqId=$ret[0];
        }
        
        return $seqId;    
    }
    
    /* Returns last inserted id for mysql */
    function lastInsertedId($conn) {
        $lastId=0;
        $ret=array();
        $sqlStr="";
        
        if($this->getDbType()=='mysql'){
            $lastId=$conn->lastInsertId();
        }
        
        return $lastId;
    }
   
    /* Start transaction */
    function beginTransaction($conn) {
        $conn->beginTransaction();
    }
    
    /* Rollback transaction */
    function rollback($conn) {
        $conn->rollBack();
    }
    
    /* Commit transaction */
    function commit($conn) {
        $conn->commit();
    }

    /* Checks if row is array and has records */
    function hasRows($row) {
        if(!is_null($row) && is_array($row) && count($row) > 0)
            return true;
        return false;
    }
    
    /* This returned  sql limitation code can be add at last of sql string and when changing database type this will changed automaticaly */    
    function limitStr($min, $max){
    	$dbType=$this->getDbType();
        $str=array();
        $str['mysql']=" limit ".$min.", ".$max;
        $str['pgsql']=" limit ".$max." offset ".$min;
        
        return $str[$dbType];
    }
    
    /* For mysql true and false is 0 and 1 for postgresql true and false so it will change by database type values
     * For example: 
     * insert into table (id, active) values (12, retBooleanCondition(1));
     * where active is boolean type field inserts in mysql 1 and postgresql true
     */
    function booleanCondition($value){
        $retStr="";
        $dbType=$this->getDbType();
        $valueStr=trim($value);
        
        if($dbType=="mysql"){
            if($valueStr=="false" || $valueStr=="f") $retStr="0";
            elseif($valueStr=="true" || $valueStr=="t") $retStr="1";            
            else $retStr=$valueStr;
         }elseif($dbType=="pgsql"){
            if($valueStr==0) $retStr="false";
            elseif($valueStr==1) $retStr="true";
            else $retStr=$valueStr;
         }
        
        return $retStr;
    }
    
    /* Returns string or null
     * Since 0.10
     */
	function strNull($str){
		if(!is_null($str) && $str!==""){
			if (!get_magic_quotes_gpc()) {
				$str=addslashes($str);
			}
			return trim("'".$str."'");
		}
		return "NULL";
	}
    
    /* Returns integer or null
     * Since 0.10
     */
	function intNull($digit){
		if(!is_null($digit) && trim($digit)!=="" && intval($digit)!=0) return $digit;
		return "NULL";
	}   
    
    /* Returns double or null
     * Since 0.10
     */
	function doubleNull($digit){
		if(!is_null($digit) && trim($digit)!=="" && doubleval($digit)!=0.00) return $digit;
		return "NULL";
	}
    
}


















?>
