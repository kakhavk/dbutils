<?php
# Database Helper class
# Writen By Kakhaber Kashmadze <info@soft.ge>
# Licensed under MIT License
# Version 0.2

namespace DbUtils\Helper;

class HelperDb{

	private $db = null;
	private $dsn = null;
    private $dbParams = array();
    private $options=array(
		PDO::ATTR_TIMEOUT => 30, 
		PDO::ATTR_PERSISTENT => true, 
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, 
		PDO::ATTR_EMULATE_PREPARES => 0
    );
    
    public function __construct($params){
		$dbtype=null;
		$dbParams=array(
			'type'=>null,
			'host'=>null,
			'name'=>null,
			'user'=>null,
			'password'=>null,
			'port'=>null,		    
		    'search_path'=>null,
		    'returnlastInsertId'=>true
		);
		
		$dbParamsRequired=array(
			'type',
			'host',
			'name',
			'user'
		);
		
		if(!empty($params) && is_array($params)){
			foreach($dbParams as $k => $v){
				
				if(isset($params[$k])){
					$dbParams[$k]=$params[$k];
				}
			}
		}
		
        foreach($dbParamsRequired as $k){
			if(is_null($dbParams[$k])){
				return false;
			}
        }
		
		$this->dbParams=$params;		
		
		$dbtype = strtolower(trim($this->dbParams['type']));
        if ($dbtype == 'microsoftsql')
            $dbtype = 'mssql';
        if ($dbtype == 'mysqli')
            $dbtype = 'mysql';
        if ($dbtype == 'postgresql')
            $dbtype = 'pgsql';
		$dbParams['type']=$dbtype;
        $this->dbParams['type'] = $dbParams['type'];
        
        if (is_null($this->dsn)){
			$this->dsn = $dbParams['type'] . ':host=' . $dbParams['host'] . (!is_null($dbParams['port']) ? ';port=' . $dbParams['port'] : '') . ';dbname=' . $dbParams['name'];
			if ($dbParams['type'] == 'mssql') {
				$this->options=null;
				$this->dsn='dblib:host=' . $dbParams['host'] . (!is_null($dbParams['port']) ? ':' . $dbParams['port'] : '') . ';dbname=' . $dbParams['name'];
			}
		}        
		
		$this->db=new Pdo($this->dsn, $dbParams['user'], $dbParams['password'], $this->options);
		
		if ($this->dbParams['type'] == "mysql") {
			$this->db->prepare("SET NAMES 'utf8'")->execute();
		}
    }
    
    public function getDb(){
		return $this->db;
    }
    
    public function getDbParams(){
		return $this->dbParams;
    }
    
    public function addDbParams($params=array()){
        foreach($params as $k=>$v){
            if(!isset($this->dbParams[$k])){
                $this->dbParams[$k]=$v;
            }
        }
    }
    
    public function addDbParam($key, $value){
        if(!isset($this->dbParams[$key])){
            $this->dbParams[$key]=$value;
        }
    }
    
    public function setDbParams($params=array()){
        foreach($params as $k=>$v){
            $this->dbParams[$k]=$v;
        }
    }
    
    public function setDbParam($key, $value){
        $this->dbParams[$key]=$value;
    }

}
