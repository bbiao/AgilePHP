<?php
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.COREPATH."vendors/");
require_once ('zend/Loader.php');

class Zend 
{
	function Zend()
	{
		
	}
	
	function load($class)
	{
		Zend_Loader::loadClass($class);
	}
}