<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hostpool
{
	var $hosts = array ();
	var $length = 0;
	
	function Hostpool($hosts_str)
	{
		if ($hosts_str == '')
		{
			return;
		}
		
		$temp_array = explode(',', $hosts_str);
		$this->hosts = array_merge($this->hosts, $temp_array);
		$this->length = count($this->hosts);
	}
	
	function get_first()
	{
		return $this->hosts[0];
	}
	
	function get_random()
	{
		$host_id = rand(0, $this->length - 1);
		return $this->hosts[$host_id];
	}
	
	function get_all()
	{
		return $this->hosts;
	}
	
	function add_host($host)
	{
		$this->hosts[] = $host;
		$this->length++;
	}
	
}

?>