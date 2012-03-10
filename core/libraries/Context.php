<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Context
{
	var $table = NULL;
	
	function Context()
	{
		$this->table = array ();
	}
	
	function register($ctrl, $path)
	{
		$ctrl = strtolower($ctrl);
		$this->table[$ctrl] = $path;
	}
	
	function get($ctrl)
	{
		$ctrl = strtolower($ctrl);
		if (isset($this->table[$ctrl]))
		{
			return $this->table[$ctrl];
		}
		else
		{
			return NULL;
		}		
	}
	
}

?>