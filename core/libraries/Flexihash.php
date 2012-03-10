<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class CI_Flexihash extends Flexihash
{
	function CI_Flexihash()
	{
		parent::Flexihash();	
	}
	
	function get_all_targets()
	{
		return parent::getAllTargets();
	}
	
	function add_target($target, $weight = 1)
	{
		if (!is_array($target))
		{
			$target = array ($target);
		}
		
		return parent::addTarget($target, $weight);
	}
	
	function remove_target($target)
	{
		return parent::removeTarget($target);
	}
	
	function lookup($resource, $count = 1)
	{
		if ($count < 1) return;
		
		if ($count === 1) 
		{
			return parent::lookup($resource);
		}
		else
		{
			return parent::lookupList($resource, $count);
		}
	}
}