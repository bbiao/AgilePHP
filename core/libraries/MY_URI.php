<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* MY_URI Class
*
* Adds a suffix getter and setter.  Intended to be used in conjunction
* with MY_Output to support headers for cached files.  Woot!
*
* @package		CodeIgniter
* @subpackage	Libraries
* @category		URI
* @author		Josh Surber
* @transcriber	Bradford Mar
* @link			http://codeigniter.com/forums/viewthread/76260/
*/
class MY_URI extends CI_URI {
    
    var $suffix;

    // --------------------------------------------------------------------
    
    /**
     * Remove the suffix from the URL if needed
     *
     * @access    private
     * @return    void
     */    
    function _remove_url_suffix()
    {
        /*
         * Pulls between 1 and 5 characters from the end of the URI string,
         * if they follow a period. The uri_string var is stripped of these
         * characters (including the period), and the suffix is placed in the
         * suffix var, without the period. Maybe should be conditional? <josh@surber.us>
         */
        $regexp = "/\.\w{1,5}$/";
        $old = $this->uri_string;
        $new = preg_replace($regexp, "", $old);
        $suf = substr($old, strlen($new) + 1);
        $this->uri_string = $new;
        $this->suffix = $suf;
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Fetch the URI suffix
     *
     * @access    public
     * @param    string
     * @return    string
     */
    function suffix($nosuf = '') {
        /*
         * Return the URI suffix. If a variable is passed thru, and there
         * is no suffix, return said variable. <josh@surber.us>
         */
        $suf = $this->suffix;
        return ($suf == '') ? $nosuf : $suf;
    }
    
	function _filter_uri($str)
	{
		if ($str != '' AND $this->config->item('permitted_uri_chars') != '')
		{
			$str = urlencode($str);
			if ( ! preg_match("|^[".preg_quote($this->config->item('permitted_uri_chars'))."]+$|i", $str))
			{
				exit('The URI you submitted has disallowed characters.');
			}
			$str = urldecode($str);
		}
		
		// Convert programatic characters to entities
		$bad	= array('$', 		'(', 		')',	 	'%28', 		'%29');
		$good	= array('&#36;',	'&#40;',	'&#41;',	'&#40;',	'&#41;');

		return str_replace($bad, $good, $str);
	}

/**
	 * Parse the REQUEST_URI
	 *
	 * Due to the way REQUEST_URI works it usually contains path info
	 * that makes it unusable as URI data.  We'll trim off the unnecessary
	 * data, hopefully arriving at a valid URI that we can use.
	 *
	 * @access	private
	 * @return	string
	 */	
	function _parse_request_uri()
	{
		if ( ! isset($_SERVER['REQUEST_URI']) OR $_SERVER['REQUEST_URI'] == '')
		{
			return '';
		}
		
		$request_uri = preg_replace("|/(.*)|", "\\1", str_replace("\\", "/", $_SERVER['REQUEST_URI']));
		
		if ($request_uri == '' OR $request_uri == SELF)
		{
			return '';
		}
		
		/*
		 * Add by bbbiao@gmail.com
		 * Since the nignx server doesn't cut the REQUEST_URI down
		 */
		if (($pos = strpos($request_uri, '?')) !== FALSE) {
			$request_uri = substr($request_uri, 0, $pos);
		}
		
		$fc_path = FCPATH;		
		if (strpos($request_uri, '?') !== FALSE)
		{
			$fc_path .= '?';
		}

		$parsed_uri = explode("/", $request_uri);
				
		$i = 0;
		foreach(explode("/", $fc_path) as $segment)
		{
			if (isset($parsed_uri[$i]) AND $segment == $parsed_uri[$i])
			{
				$i++;
			}
		}
		
		$parsed_uri = implode("/", array_slice($parsed_uri, $i));
		
		if ($parsed_uri != '')
		{
			$parsed_uri = '/'.$parsed_uri;
		}

		return $parsed_uri;
	}
}
// END MY_URI Class

/* End of file MY_URI.php */