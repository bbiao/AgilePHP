<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Parser extends CI_Parser
{
	
	public function MY_Parser()
	{
	
	}
	
	public function layout($layout, $essential, $data = array(), $format = 'xhtml', $return = FALSE)
	{
		$data['essential'] = $essential;
		return $this->parse($layout, $data, $format, $return);
	}
	
	public function parse($template, $data = array(), $format = 'xhtml', $return = FALSE)
	{
		$CI =& get_instance();
		$template = $CI->load->view($template, $data, $format, TRUE);
		if ($template == '')
		{
			return FALSE;
		}
		
		foreach ($data as $key => $val)
		{
			if (is_array($val))
			{
				$template = $this->_parse_pair($key, $val, $template);		
			}
			else
			{
				$template = $this->_parse_single($key, (string)$val, $template);
			}
		}
		
		if ($return == FALSE)
		{
			$CI->output->append_output($template);
		}
		
		return $template;
	}
	
}

?>