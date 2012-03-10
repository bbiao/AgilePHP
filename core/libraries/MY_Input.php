<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Input extends CI_Input
{
	public function MY_Input()
	{
		parent::CI_Input();
	}
	
	function ip_location($ip)
	{
		
		return '';
	}
	
	function post($index = '', $xss_clean = FALSE)
	{
		if (is_array($index))
		{
			$result_array = array();
			foreach ($index as $i)
			{
				$v = parent::post($i, $xss_clean);
				$result_array[$i] = $v;
			}
			
			return $result_array;
		}
		else
		{
			return parent::post($index, $xss_clean);
		}
	}
	
	function raw_data()
	{
		if (isset($GLOBALS['HTTP_RAW_POST_DATA']))
		{
			return $GLOBALS['HTTP_RAW_POST_DATA'];
		}	
		else
		{
			return FALSE;
		}
	}
	
	function referer()
	{
		return $this->input->server('HTTP_REFERER');
	}
}
?>