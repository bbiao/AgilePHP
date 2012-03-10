<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CouchDB_Client extends CouchClient
{
	function CouchDB_Client($dsn, $dbname)
	{
		parent::CouchClient($dsn, $dbname);
	}
	
	function list_databases()
	{
		return parent::listDatabases();
	}
	
	function create_database($dbname)
	{
		return parent::createDatabase();
	}
	
	function delete_database($dbname)
	{
	}
	
	function database_exists($dbname)
	{
	}
	
	function get_database_infos($dbname) 
	{
	}
	
	function get_database_uri($dbname) 
	{
	}
	
	function get_uuids($cnt)
	{
	}
	
	function get_all_docs($ids = NULL)
	{
	}
	
	function get_all_docs_by_seq()
	{
	}
	
	function get_doc($id)
	{
		return parent::getDoc($id);
	}
	
	function store_doc($doc)
	{
	}
	
	function store_docs($docs = array())
	{
	}
	
	function delete_doc($id)
	{
	}
	
	function copy_doc($id, $new_id) 
	{
	}
	
	function store_attachment($id, $filepath, $mime, $filename)
	{
	}
	
	function store_as_attachment($id, $data, $mime, $filename)
	{
	}
	
	function delete_attachment($id, $filename)
	{
	}
}

class CouchDB_Connection
{	
	var $pool = array ();
	
	function CouchDB_Connection($host, $port = 5984, $username = NULL, $password = NULL)
	{
		$dsn = "http://{$host}:{$port}";
	}
	
	function db($dbname)
	{
		$db = NULL;
		
		if (isset($pool[$dbname]))
		{
			$db = $pool[$dbname];
		}
		else
		{
			$db = new CouchDB_Client($this->dsn, $dbname);
			$pool[$dbname] = $db;
		}
		
		return $db;
	}
	
	static $instance = NULL;
	static function &instance()
	{
		if (self::$instance == NULL)
		{
			self::$instance = new CouchDB_Connection();
		}
		
		return self::$instance;
	}
}

class CouchDB_Database
{
	var $connection = NULL;
	var $dbname = NULL;
	
	function CouchDB_Database($dbname = 'blog')
	{
		$connection = CouchDB_Connection::instance();
		$this->dbname = $dbname;
	}
	
	function get($id)
	{
		return $connection->db($this->dbname)->get_doc($id);
	}
	
	function delete($doc)
	{
		return $connection->db($this->dbname)->delete($doc);
	}
	
	function store($doc)
	{
		return $connection->db($this->dbname)->store($doc);
	}
}

class CouchDB_Doucument
{
	var $db = NULL;
	function  CouchDB_Document($dbname)
	{
		$this->db = new CouchDB_Database($dbname);
	}
}

class CI_CouchDB
{
	var $client = NULL;
	
	var $host = NULL;
	var $port = NULL;
	
	var $username = NULL;
	var $password = NULL;
	
	var $format = array ('json', 'array');
	
	function CouchDB($config)
	{
	}
}
