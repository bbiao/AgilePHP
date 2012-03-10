<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Router extends CI_Router
{
	public function MY_Router()
	{
		parent::CI_Router();
	}
	
	function _search_class($class, $subdir = '')
	{
		return search_controller($class, APPPATH, $subdir);
	}
	
	/**
	 * Validates the supplied segments.  Attempts to determine the path to
	 * the controller.
	 *
	 * @access	private
	 * @param	array
	 * @return	array
	 */	
	function _validate_request($segments)
	{
		$controller_path = $this->_search_class($segments[0]);
		
		if ($controller_path !== FALSE)
		{
			return $segments;
		}
		else
		{
			$subdir = $segments[0];
			$segments = array_slice($segments, 1);
			
			if (count($segments) > 0)
			{
				$controller_path = $this->_search_class($segments[0], $subdir);
			
				if ($controller_path !== FALSE)
				{
					$this->set_directory($subdir);
				}
				else
				{
					show_404($this->fetch_directory().$segments[0]);
				}
			}
			else
			{
				$this->set_class($this->default_controller);
				$this->set_method('index');
			
				$controller_path = $this->_search_class($this->default_controller, $subdir);
			
				if ($controller_path === FALSE)
				{
					$this->directory = '';
					return array();
				}
				else
				{
					$this->set_directory($subdir);
				}			
			}

			return $segments;
		}

		// Is the controller in a sub-folder?
		if (is_dir(APPPATH.'controllers/'.$segments[0]))
		{		
			// Set the directory and remove it from the segment array
			$this->set_directory($segments[0]);
			$segments = array_slice($segments, 1);
			
			if (count($segments) > 0)
			{
				// Does the requested controller exist in the sub-folder?
				if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$segments[0].EXT))
				{
					show_404($this->fetch_directory().$segments[0]);
				}
			}
			else
			{
				$this->set_class($this->default_controller);
				$this->set_method('index');
			
				// Does the default controller exist in the sub-folder?
				if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$this->default_controller.EXT))
				{
					$this->directory = '';
					return array();
				}
			
			}

			return $segments;
		}
	}
	
}

?>