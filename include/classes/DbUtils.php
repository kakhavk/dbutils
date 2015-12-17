<?php
# Database PDO utilities for MySQL and PostgreSQL
# Version 0.8
# Writen By Kakhaber Kashmadze <info@soft.ge>
# Licensed under MIT License

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
    function retDbType(){
        return $this->dbType;
    }
    
    function databaseConnectionAttributes($conn, $attributeName=""){
		$returnStr="";
		

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
			$returnStr=$conn->getAttribute(constant("PDO::ATTR_$attributeName"));
		}else{
			foreach($pdoAttributes as $k => $v){
				if(null!==($conn->getAttribute(constant("PDO::ATTR_$v"))))
					$returnStr.="<br /><br /><span style=\"color:navy;\">".$k."</span><br />".$conn->getAttribute(constant("PDO::ATTR_$v"));
			}
		}
		return $returnStr;
	}
    
    /* Create connection */
    function dbConnect($host, $user, $pass, $dbname) {
        
        static $con;
        $dbType=$this->retDbType();
        
        $options = array(
            PDO::ATTR_TIMEOUT => 30,
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,        		
        );
        

        if(!isset($con)){
            $dsn = $dbType.':host='.$host.';dbname='.$dbname;
            if($dbType=='oracle' || $dbType=='oci' || $dbType=='oci8') $dsn = 'OCI:dbname='.$dbname.';charset=UTF-8';
            $con=new PDO($dsn, $user, $pass, $options);
            
            if(!$con) {
                $this->errorMessage.="Problem conbnecting to database ";
                return false;
            }
            
            if($dbType=="mysql"){
                $con->prepare("SET NAMES 'utf8'")->execute();
            }
        }
        
        return $con;
    }

     
    /* Return number value from sql like "COUNT, SUM" and other similar methods which returns one number value
     * query must be full sql string and must return one value. 
     * For example: select count(id) from tablename
     * Since 0.3
     */
    function retNumber($conn, $sqlStr) {
        $ret=0;
        $retrow=array();
        $retrow=$conn->query($sqlStr)->fetch(PDO::FETCH_NUM);
        if(is_array($retrow) && count($retrow)!=0 && is_numeric($retrow[0])) $ret=$retrow[0];
        return $ret;
    }
    
     
    /* Return sql records or null if error detected
     * query must be full sql string
     * @Since 0.3
     */
    function retSqlRows($conn, $sqlStr, $min=0, $max=0) {
        
        $dbType=$this->retDbType();
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
    
    /* Return one sql record or null if error detected
     * query must be full sql string
     */
    function retSqlRow($conn, $sqlStr) {
        
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
    function execSqlUpdate($conn, $sqlStr) {
        
        $stmt=null;
        $stmt=$conn->prepare($sqlStr);
		$stmt->execute();
		
        return $stmt;
        
    }
    
    /* Executes query for insert and if database type is mysql returns last inserted id */
    function execSqlInsert($conn, $sqlStr) {
        $ret=$this->execSqlUpdate($conn, $sqlStr);
        if($this->retDbType()=="mysql") return $conn->lastInsertId();
        return $ret;
    }
    
    /* Unset connection object */
    function conClose(&$conn) {
        $conn=null;
    }
    
    /* Returns next sequence for postgresql 
     */
    function retNextval($conn, $nextvalStr) {
        $dbType=$this->retDbType();
        $seqId=0;
        $ret=array();
        $sqlStr="";
        
        if($dbType=='pgsql'){
            $sqlStr="SELECT nextval('".$nextvalStr."')"; 
            $ret=$conn->query($sqlStr)->fetch(PDO::FETCH_NUM);
            $seqId=$ret[0];
        }
        
        return $seqId;
    }

    /* Returns last inserted id for mysql */
    function retLastInsertedId($conn, $nextvalStr) {
        $seqId=0;
        $ret=array();
        $sqlStr="";
        
        if($this->retDbType()=='mysql'){
            $seqId=$conn->lastInsertId();
        }
        
        return $seqId;
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
    function retLimitSqlStr($min, $max){
    	$dbType=$this->retDbType();
        $retStr=array();
        $retStr['mysql']=" limit ".$min.", ".$max;
        $retStr['pgsql']=" limit ".$max." offset ".$min;
        
        return $retStr[$dbType];
    }
    
    /* For mysql true and false is 0 and 1 for postgresql true and false so it will change by database type values
     * For example: 
     * insert into table (id, active) values (12, retBooleanCondition(1));
     * where active is boolean type field inserts in mysql 1 and postgresql true
     */
    function retBooleanCondition($value){
        $retStr="";
        $dbType=$this->retDbType();
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
     * Since 0.3
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
     * Since 0.3
     */
	function intNull($digit){
		if(!is_null($digit) && trim($digit)!=="" && intval($digit)!=0) return $digit;
		return "NULL";
	}   
    
    /* Returns double or null
     * Since 0.3
     */
	function doubleNull($digit){
		if(!is_null($digit) && trim($digit)!=="" && doubleval($digit)!=0.00) return $digit;
		return "NULL";
	}
    
}


















?>
