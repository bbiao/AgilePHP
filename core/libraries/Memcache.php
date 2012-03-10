<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CI_Memcache extends Memcache
{
	var $host = NULL;
	var $port = 0;
	var $persistent = TRUE;
	var $expiration = 0;
	
	function CI_Memcache($config = NULL)
	{
		if ($config == NULL)
		{
			$config = array
			(
				'host' => 'localhost',
				'port' => '11211',
				'persistent' => TRUE
			);
		}
		
		foreach ($config as $key => $val)
		{
			$this->$key = $val;
		}
		
		$this->host = explode(',', $this->host);
		foreach ($this->host as $h)
		{
			$this->add_server($h, $this->port, $this->persistent);
		}
	}
	
	function initailize()
	{
		
	}
	
	function add_server($host, $port = '11211', $persistent = TRUE)
	{
		$this->addServer($host, $port, $persistent);
	}
	
	function get_version()
	{
		return $this->getVersion();
	}
	
	function get_server_status($host, $port = 11211)
	{
		return parent::getServer($host, $port);
	}
	
	function get_stats()
	{
		return parent::getStats();	
	}
	
	function get_extended_stats()
	{
		return parent::getExtendedStats();
	}
}

?>