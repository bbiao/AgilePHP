<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Common Functions
 *
 * Loads the base classes and executes the request.
 *
 * @package		CodeIgniter
 * @subpackage	codeigniter
 * @category	Common Functions
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/
 */

// ------------------------------------------------------------------------

/**
 * Tests for file writability
 *
 * is_writable() returns TRUE on Windows servers when you really can't write to 
 * the file, based on the read-only attribute.  is_writable() is also unreliable
 * on Unix servers if safe_mode is on. 
 *
 * @access	private
 * @return	void
 */
function is_really_writable($file)
{	
	// If we're on a Unix server with safe_mode off we call is_writable
	if (DIRECTORY_SEPARATOR == '/' AND @ini_get("safe_mode") == FALSE)
	{
		return is_writable($file);
	}

	// For windows servers and safe_mode "on" installations we'll actually
	// write a file then read it.  Bah...
	if (is_dir($file))
	{
		$file = rtrim($file, '/').'/'.md5(rand(1,100));

		if (($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE)
		{
			return FALSE;
		}

		fclose($fp);
		@chmod($file, DIR_WRITE_MODE);
		@unlink($file);
		return TRUE;
	}
	elseif (($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE)
	{
		return FALSE;
	}

	fclose($fp);
	return TRUE;
}
// ------------------------------------------------------------------------

/**
 * Search a class
 * @param $class
 * @param $base_path
 * @param $sub_dir
 * @param $type
 * @return unknown_type
 */
function search_class($class, $base_path = APPPATH, $subdir = '', $type = 'library')
{	
	$dest_path = NULL;
	
	$type_path = NULL;
	
	$module_path = config_item('modules');
	if (!is_array($base_path))
	{
		$base_path = array ($base_path);
	}
	
	$app_path = array (APPPATH);
	$core_path = array (COREPATH);
	$sys_path = array (BASEPATH);
	
	$search_path = array_merge($base_path, $app_path, $module_path, $core_path, $sys_path);
	$search_path = array_unique($search_path);
	
	foreach ($search_path as &$s_path)
	{
		if ($s_path[strlen($s_path) - 1] !== '/')
		{
			$s_path .= '/';
		}
	}
	
	$type_map = array
	(
		'library' => 'libraries/',
		'controller' => 'controllers/',
		'model' => 'models/',
		'view' => 'views/',
		'helper' => 'helpers/',
		'plugin' => 'plugins/',
		'vendor' => 'vendors/'	
	);
	
	if (isset($type_map[$type]))
	{
		$type_path = $type_map[$type];
	}
	else
	{
		return FALSE;
	}
	
	$is_subclass = FALSE;
	$subclass_prefix = config_item('subclass_prefix');
	
	if ($subdir != '')
	{
		$subdir = trim($subdir, '/');
		$subdir .= '/';
	}
	
	foreach ($search_path as $path)
	{		 
		if (file_exists($path.$type_path.$subdir.$subclass_prefix.$class.EXT))
		{
			$dest_path = $path;
			$is_subclass = TRUE;
			break;
		}
		elseif (file_exists($path.$type_path.$subdir.$class.EXT))
		{
			$dest_path = $path;
			$is_subclass = FALSE;
			break;
		}
		elseif (in_array($type, array ('library', 'vendor')) && $subdir == '')
		{
			if (file_exists($path.$type_path.$class.'/'.$class.EXT))
			{
				$dest_path = $path;
				$is_subclass = FALSE;
				$subdir = $class.'/';
				break; 
			}
		}
	}
	
	if ($dest_path == NULL)
	{
		return FALSE;
	}
	else
	{
		return array ('path' => $dest_path, 'is_subclass' => $is_subclass, 'subdir' => $subdir);
	}
}

// ------------------------------------------------------------------------

/**
 * 
 * @param unknown_type $class
 * @param unknown_type $base_path
 * @param unknown_type $subdir
 * @return unknown_type
 */
function search_controller($class, $base_path = APPPATH, $subdir = '')
{
	$dest_path = NULL;
	
	$type_path = NULL;
	
	$module_path = config_item('modules');
	if (!is_array($base_path))
	{
		$base_path = array ($base_path);
	}
	
	$app_path = array (APPPATH);
	$core_path = array (COREPATH);
	$sys_path = array (BASEPATH);
	$mod_path = array (MODPATH);
	
	$search_path = array_merge($base_path, $app_path, $mod_path, $module_path, $core_path, $sys_path);
	$search_path = array_unique($search_path);
	
	foreach ($search_path as &$s_path)
	{
		if ($s_path[strlen($s_path) - 1] !== '/')
		{
			$s_path .= '/';
		}
	}
	
	$type_path = 'controllers/';
	
	$is_subclass = FALSE;
	$is_modclass = FALSE;
	
	if ($subdir != '')
	{
		$subdir = trim($subdir, '/');
		$subdir .= '/';
	}

	foreach ($search_path as $path)
	{
		if (file_exists($path.$type_path.$subdir.$class.EXT))
		{
			$dest_path = $path;
			$is_subclass = FALSE;
			$is_modclass = FALSE;
			break;
		}
		elseif (file_exists($path.$subdir.$type_path.$class.EXT))
		{
			$dest_path = $path;
			$is_subclass = FALSE;
			$is_modclass = TRUE;
			break;
		}
	}
	
	if ($dest_path == NULL)
	{
		return FALSE;
	}
	else
	{
		return array ('path' => $dest_path, 'is_subclass' => $is_subclass, 'is_modclass' => $is_modclass);
	}
}

// ------------------------------------------------------------------------

/**
* Class registry
*
* This function acts as a singleton.  If the requested class does not
* exist it is instantiated and set to a static variable.  If it has
* previously been instantiated the variable is returned.
*
* @access	public
* @param	string	the class name being requested
* @param	bool	optional flag that lets classes get loaded but not instantiated
* @return	object
*/
function &load_class($class, $base_path = APPPATH, $instantiate = TRUE)
{
	static $objects = array();

	$class_path = search_class($class, $base_path);
	
	// If the requested class does not exist in the application/libraries
	// folder we'll load the native class from the system/libraries folder.
	if ($class_path != FALSE)
	{
		// Does the class exist?  If so, we're done...
		if (isset($objects[$class_path['path']]) && isset($objects[$class_path['path']][$class]))
		{
			return $objects[$class_path['path']][$class];
		}
		
		if ($class_path['is_subclass'])
		{
			require(BASEPATH.'libraries/'.$class.EXT);
			require($class_path['path'].'libraries/'.config_item('subclass_prefix').$class.EXT);
		}
		else
		{
			require($class_path['path'].'libraries/'.$class.EXT);
		}
	}

	if (!isset($objects[$class_path['path']]))
	{
		$objects[$class_path['path']] = array ();
	}
	
	$is_subclass = $class_path['is_subclass'];
	if ($instantiate == FALSE)
	{
		$objects[$class_path['path']][$class] = TRUE;
		return $objects[$class_path['path']][$class];
	}

	if ($is_subclass == TRUE)
	{
		$name = config_item('subclass_prefix').$class;
		$objects[$class_path['path']][$class] =& new $name();
		return $objects[$class_path['path']][$class];
	}

	if (class_exists($class))
	{
		$name = $class;
	}
	else
	{
		$name = ($class != 'Controller') ? 'CI_'.$class : $class;
	}

	$objects[$class_path['path']][$class] =& new $name();
	return $objects[$class_path['path']][$class];
}

/**
* Loads the main config.php file
*
* @access	private
* @return	array
*/
function &get_config()
{
	static $main_conf;

	if ( ! isset($main_conf))
	{
		if ( ! file_exists(APPPATH.'config/config'.EXT))
		{
			exit('The configuration file config'.EXT.' does not exist.');
		}

		require(APPPATH.'config/config'.EXT);

		if ( ! isset($config) OR ! is_array($config))
		{
			exit('Your config file does not appear to be formatted correctly.');
		}

		$main_conf[0] =& $config;
	}
	return $main_conf[0];
}

/**
* Gets a config item
*
* @access	public
* @return	mixed
*/
function config_item($item)
{
	static $config_item = array();

	if ( ! isset($config_item[$item]))
	{
		$config =& get_config();

		if ( ! isset($config[$item]))
		{
			return FALSE;
		}
		$config_item[$item] = $config[$item];
	}

	return $config_item[$item];
}


/**
* Error Handler
*
* This function lets us invoke the exception class and
* display errors using the standard error template located
* in application/errors/errors.php
* This function will send the error page directly to the
* browser and exit.
*
* @access	public
* @return	void
*/
function show_error($message)
{
	$error =& load_class('Exceptions');
	echo $error->show_error('An Error Was Encountered', $message);
	exit;
}


/**
* 404 Page Handler
*
* This function is similar to the show_error() function above
* However, instead of the standard error template it displays
* 404 errors.
*
* @access	public
* @return	void
*/
function show_404($page = '')
{
	$error =& load_class('Exceptions');
	$error->show_404($page);
	exit;
}


/**
* Error Logging Interface
*
* We use this as a simple mechanism to access the logging
* class and send messages to be logged.
*
* @access	public
* @return	void
*/
function log_message($level = 'error', $message, $php_error = FALSE)
{
	static $LOG;
	
	$config =& get_config();
	if ($config['log_threshold'] == 0)
	{
		return;
	}

	$LOG =& load_class('Log');
	$LOG->write_log($level, $message, $php_error);
}

/**
* Exception Handler
*
* This is the custom exception handler that is declaired at the top
* of Codeigniter.php.  The main reason we use this is permit
* PHP errors to be logged in our own log files since we may
* not have access to server logs. Since this function
* effectively intercepts PHP errors, however, we also need
* to display errors based on the current error_reporting level.
* We do that with the use of a PHP error template.
*
* @access	private
* @return	void
*/
function _exception_handler($severity, $message, $filepath, $line)
{	
	 // We don't bother with "strict" notices since they will fill up
	 // the log file with information that isn't normally very
	 // helpful.  For example, if you are running PHP 5 and you
	 // use version 4 style class functions (without prefixes
	 // like "public", "private", etc.) you'll get notices telling
	 // you that these have been deprecated.
	
	if ($severity == E_STRICT)
	{
		return;
	}

	$error =& load_class('Exceptions');

	// Should we display the error?
	// We'll get the current error_reporting level and add its bits
	// with the severity bits to find out.
	
	if (($severity & error_reporting()) == $severity)
	{
		$error->show_php_error($severity, $message, $filepath, $line);
	}
	
	// Should we log the error?  No?  We're done...
	$config =& get_config();
	if ($config['log_threshold'] == 0)
	{
		return;
	}

	$error->log_exception($severity, $message, $filepath, $line);
}

function &get_context()
{
	$context =& load_class('Context');
	
	return $context;
}


/* End of file Common.php */
/* Location: ./system/codeigniter/Common.php */