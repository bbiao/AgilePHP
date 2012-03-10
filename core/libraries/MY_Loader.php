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
 * Loader Class
 *
 * Loads views and files
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @author		ExpressionEngine Dev Team
 * @category	Loader
 * @link		http://codeigniter.com/user_guide/libraries/loader.html
 */
class MY_Loader extends CI_Loader
{
	var $_fc_base_path		= '';
	var $_fc_ctrl			= NULL;
	
	var $_fc_controllers	= array();
	var $_fc_classes		= array();
	var $_fc_loaded_files	= array();
	var $_fc_models			= array();
	var $_fc_helpers		= array();
	var $_fc_plugins		= array();
	
	/**
	 * Constructor
	 *
	 * Sets the path to the view files and gets the initial output buffering level
	 *
	 * @access	public
	 */
	function MY_Loader($ctrl = NULL)
	{	
		parent::CI_Loader();
	}
	
	function &_get_context()
	{
		$context =& get_context();
		return $context;
	}
	
	function zend($class, $params = NULL, $object_name = NULL)
	{
		if (!class_exists('Zend_Loader') && $this->vendor('zend'))
		{
			return FALSE;
		}
		
		Zend_Loader::loadClass($class);
		
		return $this->_ci_init_class($class, '', $params, $object_name, COREPATH);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * 
	 * @return unknown_type
	 */
	function controller($controller = '', $name = '')
	{		
		if (is_array($controller))
		{
			foreach ($controller as $c)
			{
				$this->controller($c);
			}
			
			return;
		}
		
		if ($controller == '')
		{
			return FALSE;
		}
		
			// Is the model in a sub-folder? If so, parse out the filename and path.
		if (strpos($controller, '/') === FALSE)
		{
			$path = '';
		}
		else
		{
			$x = explode('/', $controller);
			$controller = end($x);			
			unset($x[count($x)-1]);
			$path = implode('/', $x).'/';
		}
	
		if ($name == '')
		{
			$name = $controller;
		}
		
		if (in_array($name, $this->_fc_controllers, TRUE))
		{
			return;
		}
		
		$CI =& $this->_fc_ctrl;
		if (isset($CI->$name))
		{
			show_error('The model name you are loading is the name of a resource that is already being used: '.$name);
		}
	
		$controller = strtolower($controller);
		
		$class_path = $this->_ci_search_class($controller, 'controller', $path);
		if ($class_path === FALSE)
		{
			show_error('Unable to locate the model you have specified: '.$controller);
		}
		
		require_once($class_path['path'].'controllers/'.$path.$controller.EXT);

		$controller = ucfirst($controller);
		
		$context =& $this->_get_context();
		$context->register($controller, $class_path['path']);
		$CI->$name = new $controller();
		
		$this->_fc_controllers[] = $name;
	}
	
	function vendor($vendor = '', $params = NULL, $object_name = NULL)
	{
		if ($vendor == '')
		{
			return FALSE;
		}

		if ( ! is_null($params) AND ! is_array($params))
		{
			$params = NULL;
		}

		if (is_array($vendor))
		{
			foreach ($vendor as $class)
			{
				$this->_fc_load_class($class, $params, $object_name, 'vendor');
			}
		}
		else
		{
			$this->_fc_load_class($vendor, $params, $object_name, 'vendor');
		}
		
		$this->_ci_assign_to_models();
	}
	
	function _fc_load_class($class, $params = NULL, $object_name = NULL, $type = 'library')
	{
		$is_duplicate = FALSE;
		$is_subclass = FALSE;

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
		
		// Get the class name, and while we're at it trim any slashes.  
		// The directory path can be included as part of the class name, 
		// but we don't want a leading slash
		$class = str_replace(EXT, '', trim($class, '/'));
	
		// Was the path included with the class name?
		// We look for a slash to determine this
		$subdir = '';
		if (strpos($class, '/') !== FALSE)
		{
			// explode the path so we can separate the filename from the path
			$x = explode('/', $class);	
			
			// Reset the $class variable now that we know the actual filename
			$class = end($x);
			
			// Kill the filename from the array
			unset($x[count($x)-1]);
			
			// Glue the path back together, sans filename
			$subdir = implode($x, '/').'/';
		}

		// We'll test for both lowercase and capitalized versions of the file name
		foreach (array(ucfirst($class), strtolower($class)) as $class)
		{
			$class_path = $this->_ci_search_class($class, $type, $subdir);
			if ($class_path !== FALSE)
			{
				if (isset($class_path['subdir']) && $subdir != $class_path['subdir'])
				{
					$subdir = $class_path['subdir'];
				}
			
				// Is this a class extension request?
				if ($class_path['is_subclass'])
				{
					$subclass = $class_path['path'].$type_path.$subdir.config_item('subclass_prefix').$class.EXT;
					$baseclass = BASEPATH.$type_path.ucfirst($class).EXT;
				
					if ( ! file_exists($baseclass))
					{
						log_message('error', "Unable to load the requested class: ".$class);
						show_error("Unable to load the requested class: ".$class);
					}
	
					// Safety:  Was the class already loaded by a previous call?
					if (in_array($subclass, $this->_fc_loaded_files))
					{
						// Before we deem this to be a duplicate request, let's see
						// if a custom object name is being supplied.  If so, we'll
						// return a new instance of the object
						if ( ! is_null($object_name))
						{
							$CI =& $this->_fc_ctrl;
							if ( ! isset($CI->$object_name))
							{
								return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name, $class_path['path']);			
							}
						}
						
						$is_duplicate = TRUE;
						log_message('debug', $class." class already loaded. Second attempt ignored.");
						return;
					}
		
					include_once($baseclass);				
					include_once($subclass);
					$this->_fc_loaded_files[] = $subclass;
		
					return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name, $class_path['path']);
				}
				else
				{
					// Lets search for the requested library file and load it.
					$is_duplicate = FALSE;
					$path = $class_path['path'];
					$filepath = $path.$type_path.$subdir.$class.EXT;

					// Does the file exist?  No?  Bummer...
					if ( ! file_exists($filepath))
					{
						continue;
					}
					
					// Safety:  Was the class already loaded by a previous call?
					if (in_array($filepath, $this->_fc_loaded_files))
					{
						// Before we deem this to be a duplicate request, let's see
						// if a custom object name is being supplied.  If so, we'll
						// return a new instance of the object
						if ( ! is_null($object_name))
						{
							$CI =& $this->_fc_ctrl;
							if ( ! isset($CI->$object_name))
							{
								return $this->_ci_init_class($class, '', $params, $object_name, $class_path['path']);
							}
						}
					
						$is_duplicate = TRUE;
						log_message('debug', $class." class already loaded. Second attempt ignored.");
						return;
					}
					
					include_once($filepath);
					$this->_fc_loaded_files[] = $filepath;
					return $this->_ci_init_class($class, '', $params, $object_name, $class_path['path']);
				}
				
			}
		} // END FOREACH

		// One last attempt.  Maybe the library is in a subdirectory, but it wasn't specified?
		if ($subdir == '')
		{
			$path = strtolower($class).'/'.$class;
			return $this->_fc_load_class($path, $params, $object_name, $type);
		}
		
		// If we got this far we were unable to find the requested class.
		// We do not issue errors if the load call failed due to a duplicate request
		if ($is_duplicate == FALSE)
		{
			log_message('error', "Unable to load the requested class: ".$class);
			show_error("Unable to load the requested class: ".$class);
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Class Loader
	 *
	 * This function lets users load and instantiate classes.
	 * It is designed to be called from a user's app controllers.
	 *
	 * @access	public
	 * @param	string	the name of the class
	 * @param	mixed	the optional parameters
	 * @param	string	an optional object name
	 * @return	void
	 */	
	function library($library = '', $params = NULL, $object_name = NULL)
	{
		if ($library == '')
		{
			return FALSE;
		}

		if ( ! is_null($params) AND ! is_array($params))
		{
			$params = NULL;
		}

		if (is_array($library))
		{
			foreach ($library as $class)
			{
				$this->_ci_load_class($class, $params, $object_name);
			}
		}
		else
		{
			$this->_ci_load_class($library, $params, $object_name);
		}
		
		$this->_ci_assign_to_models();
	}

	// --------------------------------------------------------------------
	
	/**
	 * Model Loader
	 *
	 * This function lets users load and instantiate models.
	 *
	 * @access	public
	 * @param	string	the name of the class
	 * @param	string	name for the model
	 * @param	bool	database connection
	 * @return	void
	 */	
	function model($model, $name = '', $db_conn = FALSE)
	{
		if (is_array($model))
		{
			foreach($model as $babe)
			{
				$this->model($babe);	
			}
			return;
		}

		if ($model == '')
		{
			return;
		}
	
		// Is the model in a sub-folder? If so, parse out the filename and path.
		if (strpos($model, '/') === FALSE)
		{
			$path = '';
		}
		else
		{
			$x = explode('/', $model);
			$model = end($x);			
			unset($x[count($x)-1]);
			$path = implode('/', $x).'/';
		}
	
		if ($name == '')
		{
			$name = $model;
		}
		
		if (in_array($name, $this->_fc_models, TRUE))
		{
			return;
		}
		
		$CI =& $this->_fc_ctrl;
		if (isset($CI->$name))
		{
			show_error('The model name you are loading is the name of a resource that is already being used: '.$name);
		}
	
		$model = strtolower($model);

		$class_path = $this->_ci_search_class($model, 'model', $path);
		if ($class_path === FALSE)
		{
			show_error('Unable to locate the model you have specified: '.$model);
		}
				
		if ($db_conn !== FALSE AND ! class_exists('CI_DB'))
		{
			if ($db_conn === TRUE)
				$db_conn = '';
		
			$CI->load->database($db_conn, FALSE, TRUE);
		}
	
		if ( ! class_exists('Model'))
		{
			load_class('Model', APPPATH, '', 'library', FALSE);
		}

		require_once($class_path['path'].'models/'.$path.$model.EXT);

		$model = ucfirst($model);
				
		$CI->$name = new $model();
		$CI->$name->_assign_libraries();
		
		$this->_fc_models[] = $name;	
	}
		
	// --------------------------------------------------------------------
	
	/**
	 * Database Loader
	 *
	 * @access	public
	 * @param	string	the DB credentials
	 * @param	bool	whether to return the DB object
	 * @param	bool	whether to enable active record (this allows us to override the config setting)
	 * @return	object
	 */	
	function database($params = '', $return = FALSE, $active_record = FALSE)
	{
		// Grab the super object
		$CI =& $this->_fc_ctrl;
		
		// Do we even need to load the database class?
		if (class_exists('CI_DB') AND $return == FALSE AND $active_record == FALSE AND isset($CI->db) AND is_object($CI->db))
		{
			return FALSE;
		}	
	
		// MODIFIED TO ADAPTIVE MULTI_DB
		require_once(COREPATH.'database/DB'.EXT);

		if ($return === TRUE)
		{
			return DB($params, $active_record, $this->_fc_base_path);
		}
		
		// Initialize the db variable.  Needed to prevent   
		// reference errors with some configurations
		$CI->db = '';
		
		// Load the DB class
		$CI->db =& DB($params, $active_record, $this->_fc_base_path);	
		
		// Assign the DB object to any existing models
		$this->_ci_assign_to_models();
	}
	
	// --------------------------------------------------------------------

	/**
	 * Load the Utilities Class
	 *
	 * @access	public
	 * @return	string		
	 */		
	function dbutil()
	{
		if ( ! class_exists('CI_DB'))
		{
			$this->database();
		}
		
		$CI =& $this->_fc_ctrl;

		// for backwards compatibility, load dbforge so we can extend dbutils off it
		// this use is deprecated and strongly discouraged
		$CI->load->dbforge();
	
		require_once(BASEPATH.'database/DB_utility'.EXT);
		require_once(BASEPATH.'database/drivers/'.$CI->db->dbdriver.'/'.$CI->db->dbdriver.'_utility'.EXT);
		$class = 'CI_DB_'.$CI->db->dbdriver.'_utility';

		$CI->dbutil =& new $class();

		$CI->load->_ci_assign_to_models();
	}
	
	// --------------------------------------------------------------------

	/**
	 * Load the Database Forge Class
	 *
	 * @access	public
	 * @return	string		
	 */		
	function dbforge()
	{
		if ( ! class_exists('CI_DB'))
		{
			$this->database();
		}
		
		$CI =& $this->_fc_ctrl;
	
		require_once(BASEPATH.'database/DB_forge'.EXT);
		require_once(BASEPATH.'database/drivers/'.$CI->db->dbdriver.'/'.$CI->db->dbdriver.'_forge'.EXT);
		$class = 'CI_DB_'.$CI->db->dbdriver.'_forge';

		$CI->dbforge = new $class();
		
		$CI->load->_ci_assign_to_models();
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Load View
	 *
	 * This function is used to load a "view" file.  It has three parameters:
	 *
	 * 1. The name of the "view" file to be included.
	 * 2. An associative array of data to be extracted for use in the view.
	 * 3. TRUE/FALSE - whether to return the data or load it.  In
	 * some cases it's advantageous to be able to return data so that
	 * a developer can process it in some way.
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	void
	 */
	function view($view, $vars = array(), $return = FALSE)
	{
		return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return));
	}
	
	
	/**
	 * Load view in layout
	 */
	function layout($layout, $view, $data = array(), $return = FALSE) {
		$data['inner_view'] = $view;
		return $this->view($layout, $data, $return);		
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Load File
	 *
	 * This is a generic file loader
	 *
	 * @access	public
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	function file($path, $return = FALSE)
	{
		return $this->_ci_load(array('_ci_path' => $path, '_ci_return' => $return));
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set Variables
	 *
	 * Once variables are set they become available within
	 * the controller class and its "view" files.
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function vars($vars = array(), $val = '')
	{
		if ($val != '' AND is_string($vars))
		{
			$vars = array($vars => $val);
		}
	
		$vars = $this->_ci_object_to_array($vars);
	
		if (is_array($vars) AND count($vars) > 0)
		{
			foreach ($vars as $key => $val)
			{
				$this->_ci_cached_vars[$key] = $val;
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Load Helper
	 *
	 * This function loads the specified helper file.
	 *
	 * @access	public
	 * @param	mixed
	 * @return	void
	 */
	function helper($helpers = array())
	{
		if ( ! is_array($helpers))
		{
			$helpers = array($helpers);
		}
	
		foreach ($helpers as $helper)
		{		
			$helper = strtolower(str_replace(EXT, '', str_replace('_helper', '', $helper)).'_helper');

			if (isset($this->_ci_helpers[$helper]))
			{
				continue;
			}
			
			$helper_path = $this->_ci_search_class($helper, 'helper');
			if ($helper_path !== FALSE)
			{
				$path = $helper_path['path'];
				if ($helper_path['is_subclass'])
				{
					$ext_helper = $path.'helpers/'.config_item('subclass_prefix').$helper.EXT;
					include_once($ext_helper);
					$base_helper = BASEPATH.'helpers/'.$helper.EXT;
					include_once($base_helper);
				}
				else
				{
					$base_helper = $path.'helpers/'.$helper.EXT;
					include_once($base_helper);
				}
			}
			else
			{
				show_error('Unable to load the requested file: helpers/'.$helper.EXT);
			}

			$this->_fc_helpers[$helper] = TRUE;
			log_message('debug', 'Helper loaded: '.$helper);	
		}		
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Load Helpers
	 *
	 * This is simply an alias to the above function in case the
	 * user has written the plural form of this function.
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function helpers($helpers = array())
	{
		$this->helper($helpers);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Load Plugin
	 *
	 * This function loads the specified plugin.
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function plugin($plugins = array())
	{
		if ( ! is_array($plugins))
		{
			$plugins = array($plugins);
		}
	
		foreach ($plugins as $plugin)
		{	
			$plugin = strtolower(str_replace(EXT, '', str_replace('_pi', '', $plugin)).'_pi');		

			if (isset($this->_fc_plugins[$plugin]))
			{
				continue;
			}

			$plugin_path = $this->_ci_search_class($plugin, 'plugin');
			if ($plugin_path !== FALSE)
			{
				$path = $plugin_path['path'];
				include_once($path.'plugins/'.$plugin.EXT);
				if ($plugin_path['is_subclass'])
				{
					include_once(BASEPATH.'plugins/'.$plugin.EXT);
				}
			}
			else
			{
				show_error('Unable to load the requested file: plugins/'.$plugin.EXT);
			}
			
			$this->_fc_plugins[$plugin] = TRUE;
			log_message('debug', 'Plugin loaded: '.$plugin);
		}		
	}

	// --------------------------------------------------------------------
	
	/**
	 * Load Plugins
	 *
	 * This is simply an alias to the above function in case the
	 * user has written the plural form of this function.
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	function plugins($plugins = array())
	{
		$this->plugin($plugins);
	}
		
	// --------------------------------------------------------------------
	
	/**
	 * Loads a language file
	 *
	 * @access	public
	 * @param	array
	 * @param	string
	 * @return	void
	 */
	function language($file = array(), $lang = '')
	{
		$CI =& $this->_fc_ctrl;

		if ( ! is_array($file))
		{
			$file = array($file);
		}

		foreach ($file as $langfile)
		{	
			$CI->lang->load($langfile, $lang);
		}
	}

	/**
	 * Loads language files for scaffolding
	 *
	 * @access	public
	 * @param	string
	 * @return	arra
	 */
	function scaffold_language($file = '', $lang = '', $return = FALSE)
	{
		$CI =& $this->_fc_ctrl;
		return $CI->lang->load($file, $lang, $return);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Loads a config file
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function config($file = '', $use_sections = FALSE, $fail_gracefully = FALSE)
	{			
		$CI =& $this->_fc_ctrl;
		$CI->config->load($file, $use_sections, $fail_gracefully);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Scaffolding Loader
	 *
	 * This initializing function works a bit different than the
	 * others. It doesn't load the class.  Instead, it simply
	 * sets a flag indicating that scaffolding is allowed to be
	 * used.  The actual scaffolding function below is
	 * called by the front controller based on whether the
	 * second segment of the URL matches the "secret" scaffolding
	 * word stored in the application/config/routes.php
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */	
	function scaffolding($table = '')
	{		
		if ($table === FALSE)
		{
			show_error('You must include the name of the table you would like to access when you initialize scaffolding');
		}
		
		$CI =& $this->_fc_ctrl;
		$CI->_ci_scaffolding = TRUE;
		$CI->_ci_scaff_table = $table;
	}

	// --------------------------------------------------------------------
		
	/**
	 * Loader
	 *
	 * This function is used to load views and files.
	 * Variables are prefixed with _ci_ to avoid symbol collision with
	 * variables made available to view files
	 *
	 * @access	private
	 * @param	array
	 * @return	void
	 */
	function _ci_load($_ci_data)
	{
		// Set the default data variables
		foreach (array('_ci_view', '_ci_vars', '_ci_path', '_ci_return') as $_ci_val)
		{
			$$_ci_val = ( ! isset($_ci_data[$_ci_val])) ? FALSE : $_ci_data[$_ci_val];
		}

		// Set the path to the requested file
		if ($_ci_path == '')
		{
			$_fc_view_path = $this->_ci_search_class($_ci_view, 'view');
			
			if ($_fc_view_path == FALSE)
			{
				show_error('Unable to load the requested file: '.$_ci_file);
			}
			else
			{
				$_fc_view_path = $_fc_view_path['path'].'views/';
			}
			
			$_ci_ext = pathinfo($_ci_view, PATHINFO_EXTENSION);
			$_ci_file = ($_ci_ext == '') ? $_ci_view.EXT : $_ci_view;
			$_ci_path = $_fc_view_path.$_ci_file;
		}
		else
		{
			$_ci_x = explode('/', $_ci_path);
			$_ci_file = end($_ci_x);
		}
	
		// This allows anything loaded using $this->load (views, files, etc.)
		// to become accessible from within the Controller and Model functions.
		// Only needed when running PHP 5
		
		if ($this->_ci_is_instance())
		{
			$_ci_CI =& $this->_fc_ctrl;
			foreach (get_object_vars($_ci_CI) as $_ci_key => $_ci_var)
			{
				if ( ! isset($this->$_ci_key))
				{
					$this->$_ci_key =& $_ci_CI->$_ci_key;
				}
			}
		}

		/*
		 * Extract and cache variables
		 *
		 * You can either set variables using the dedicated $this->load_vars()
		 * function or via the second parameter of this function. We'll merge
		 * the two types and cache them so that views that are embedded within
		 * other views can have access to these variables.
		 */	
		if (is_array($_ci_vars))
		{
			$this->_ci_cached_vars = array_merge($this->_ci_cached_vars, $_ci_vars);
		}
		extract($this->_ci_cached_vars);
				
		/*
		 * Buffer the output
		 *
		 * We buffer the output for two reasons:
		 * 1. Speed. You get a significant speed boost.
		 * 2. So that the final rendered template can be
		 * post-processed by the output class.  Why do we
		 * need post processing?  For one thing, in order to
		 * show the elapsed page load time.  Unless we
		 * can intercept the content right before it's sent to
		 * the browser and then stop the timer it won't be accurate.
		 */
		ob_start();
				
		// If the PHP installation does not support short tags we'll
		// do a little string replacement, changing the short tags
		// to standard PHP echo statements.
		
		if ((bool) @ini_get('short_open_tag') === FALSE AND config_item('rewrite_short_tags') == TRUE)
		{
			echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($_ci_path))));
		}
		else
		{
			include($_ci_path); // include() vs include_once() allows for multiple views with the same name
		}
		
		log_message('debug', 'File loaded: '.$_ci_path);
		
		// Return the file data if requested
		if ($_ci_return === TRUE)
		{		
			$buffer = ob_get_contents();
			@ob_end_clean();
			return $buffer;
		}

		/*
		 * Flush the buffer... or buff the flusher?
		 *
		 * In order to permit views to be nested within
		 * other views, we need to flush the content back out whenever
		 * we are beyond the first level of output buffering so that
		 * it can be seen and included properly by the first included
		 * template and any subsequent ones. Oy!
		 *
		 */	
		if (ob_get_level() > $this->_ci_ob_level + 1)
		{
			ob_end_flush();
		}
		else
		{
			// PHP 4 requires that we use a global
			global $OUT;
			$OUT->append_output(ob_get_contents());
			@ob_end_clean();
		}
	}

	// --------------------------------------------------------------------
	
	/**
	 * 
	 */
	function _ci_search_class($class, $type = 'library', $subdir = '')
	{
		return search_class($class, $this->_fc_base_path, $subdir, $type);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Load class
	 *
	 * This function loads the requested class.
	 *
	 * @access	private
	 * @param 	string	the item that is being loaded
	 * @param	mixed	any additional parameters
	 * @param	string	an optional object name
	 * @return 	void
	 */
	function _ci_load_class($class, $params = NULL, $object_name = NULL)
	{	
		// Get the class name, and while we're at it trim any slashes.  
		// The directory path can be included as part of the class name, 
		// but we don't want a leading slash
		$class = str_replace(EXT, '', trim($class, '/'));
	
		// Was the path included with the class name?
		// We look for a slash to determine this
		$subdir = '';
		if (strpos($class, '/') !== FALSE)
		{
			// explode the path so we can separate the filename from the path
			$x = explode('/', $class);	
			
			// Reset the $class variable now that we know the actual filename
			$class = end($x);
			
			// Kill the filename from the array
			unset($x[count($x)-1]);
			
			// Glue the path back together, sans filename
			$subdir = implode($x, '/').'/';
		}

		// We'll test for both lowercase and capitalized versions of the file name
		foreach (array(ucfirst($class), strtolower($class)) as $class)
		{
			$class_path = $this->_ci_search_class($class, 'library', $subdir);
					
			if ($class_path !== FALSE)
			{
				// Is this a class extension request?
				if ($class_path['is_subclass'])
				{
					$subclass = $class_path['path'].'libraries/'.$subdir.config_item('subclass_prefix').$class.EXT;
					$baseclass = BASEPATH.'libraries/'.ucfirst($class).EXT;
				
					if ( ! file_exists($baseclass))
					{
						log_message('error', "Unable to load the requested class: ".$class);
						show_error("Unable to load the requested class: ".$class);
					}
	
					// Safety:  Was the class already loaded by a previous call?
					if (in_array($subclass, $this->_fc_loaded_files))
					{
						// Before we deem this to be a duplicate request, let's see
						// if a custom object name is being supplied.  If so, we'll
						// return a new instance of the object
						if ( ! is_null($object_name))
						{
							$CI =& $this->_fc_ctrl;
							if ( ! isset($CI->$object_name))
							{
								return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name, $class_path['path']);			
							}
						}
						
						$is_duplicate = TRUE;
						log_message('debug', $class." class already loaded. Second attempt ignored.");
						return;
					}
		
					include_once($baseclass);				
					include_once($subclass);
					$this->_fc_loaded_files[] = $subclass;
		
					return $this->_ci_init_class($class, config_item('subclass_prefix'), $params, $object_name, $class_path['path']);
				}
				else
				{
					// Lets search for the requested library file and load it.
					$is_duplicate = FALSE;
					$path = $class_path['path'];
					$filepath = $path.'libraries/'.$subdir.$class.EXT;
					
					// Does the file exist?  No?  Bummer...
					if ( ! file_exists($filepath))
					{
						continue;
					}
					
					// Safety:  Was the class already loaded by a previous call?
					if (in_array($filepath, $this->_fc_loaded_files))
					{
						// Before we deem this to be a duplicate request, let's see
						// if a custom object name is being supplied.  If so, we'll
						// return a new instance of the object
						if ( ! is_null($object_name))
						{
							$CI =& $this->_fc_ctrl;
							if ( ! isset($CI->$object_name))
							{
								return $this->_ci_init_class($class, '', $params, $object_name, $class_path['path']);
							}
						}
					
						$is_duplicate = TRUE;
						log_message('debug', $class." class already loaded. Second attempt ignored.");
						return;
					}
					
					include_once($filepath);
					$this->_fc_loaded_files[] = $filepath;
					return $this->_ci_init_class($class, '', $params, $object_name, $class_path['path']);
				}
				
			}
		} // END FOREACH

		// One last attempt.  Maybe the library is in a subdirectory, but it wasn't specified?
		if ($subdir == '')
		{
			$path = strtolower($class).'/'.$class;
			return $this->_ci_load_class($path, $params);
		}
		
		// If we got this far we were unable to find the requested class.
		// We do not issue errors if the load call failed due to a duplicate request
		if ($is_duplicate == FALSE)
		{
			log_message('error', "Unable to load the requested class: ".$class);
			show_error("Unable to load the requested class: ".$class);
		}
	}
	
	// --------------------------------------------------------------------

	/**
	 * Instantiates a class
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @param	string	an optional object name
	 * @return	null
	 */
	function _ci_init_class($class, $prefix = '', $config = FALSE, $object_name = NULL, $path = APPPATH)
	{	
		// Is there an associated config file for this class?
		if ($config === NULL)
		{
			// We test for both uppercase and lowercase, for servers that
			// are case-sensitive with regard to file names
			if (file_exists($path.'config/'.strtolower($class).EXT))
			{
				include_once($path.'config/'.strtolower($class).EXT);
			}			
			else
			{
				if (file_exists($path.'config/'.ucfirst(strtolower($class)).EXT))
				{
					include_once($path.'config/'.ucfirst(strtolower($class)).EXT);
				}			
			}
		}
		
		if ($prefix == '')
		{			
			if (class_exists('CI_'.$class)) 
			{
				$name = 'CI_'.$class;
			}
			elseif (class_exists(config_item('subclass_prefix').$class)) 
			{
				$name = config_item('subclass_prefix').$class;
			}
			else
			{
				$name = $class;
			}
		}
		else
		{
			$name = $prefix.$class;
		}
		
		// Is the class name valid?
		if ( ! class_exists($name))
		{
			log_message('error', "Non-existent class: ".$name);
			show_error("Non-existent class: ".$class);
		}
		
		// Set the variable name we will assign the class to
		// Was a custom class name supplied?  If so we'll use it
		$class = strtolower($class);
		
		if (is_null($object_name))
		{
			$classvar = ( ! isset($this->_ci_varmap[$class])) ? $class : $this->_ci_varmap[$class];
		}
		else
		{
			$classvar = $object_name;
		}

		// Save the class name and object name		
		$this->_fc_classes[$class] = $classvar;

		// Instantiate the class		
		$CI =& $this->_fc_ctrl;
		if ($config !== NULL)
		{
			$CI->$classvar = new $name($config);
		}
		else
		{		
			$CI->$classvar = new $name;
		}	
	} 	
	
	// --------------------------------------------------------------------
	
	/**
	 * Autoloader
	 *
	 * The config/autoload.php file contains an array that permits sub-systems,
	 * libraries, plugins, and helpers to be loaded automatically.
	 *
	 * @access	private
	 * @param	array
	 * @return	void
	 */
	function _ci_autoloader()
	{	
		include_once(APPPATH.'config/autoload'.EXT);
		
		if ( ! isset($autoload))
		{
			return FALSE;
		}
		
		// Load any custom config file
		if (count($autoload['config']) > 0)
		{			
			$CI =& $this->_fc_ctrl;
			foreach ($autoload['config'] as $key => $val)
			{
				$CI->config->load($val);
			}
		}		

		// Autoload plugins, helpers and languages
		foreach (array('helper', 'plugin', 'language') as $type)
		{			
			if (isset($autoload[$type]) AND count($autoload[$type]) > 0)
			{
				$this->$type($autoload[$type]);
			}		
		}

		// A little tweak to remain backward compatible
		// The $autoload['core'] item was deprecated
		if ( ! isset($autoload['libraries']))
		{
			$autoload['libraries'] = $autoload['core'];
		}
		
		// Load libraries
		if (isset($autoload['libraries']) AND count($autoload['libraries']) > 0)
		{
			// Load the database driver.
			if (in_array('database', $autoload['libraries']))
			{
				$this->database();
				$autoload['libraries'] = array_diff($autoload['libraries'], array('database'));
			}

			// Load scaffolding
			if (in_array('scaffolding', $autoload['libraries']))
			{
				$this->scaffolding();
				$autoload['libraries'] = array_diff($autoload['libraries'], array('scaffolding'));
			}
		
			// Load all other libraries
			foreach ($autoload['libraries'] as $item)
			{
				$this->library($item);
			}
		}		

		// Autoload models
		if (isset($autoload['model']))
		{
			$this->model($autoload['model']);
		}

	}
	
	// --------------------------------------------------------------------

	/**
	 * Assign to Models
	 *
	 * Makes sure that anything loaded by the loader class (libraries, plugins, etc.)
	 * will be available to models, if any exist.
	 *
	 * @access	private
	 * @param	object
	 * @return	array
	 */
	function _ci_assign_to_models()
	{
		if (count($this->_ci_models) == 0)
		{
			return;
		}
	
		if ($this->_ci_is_instance())
		{
			$CI =& $this->_fc_ctrl;
			foreach ($this->_fc_models as $model)
			{			
				$CI->$model->_assign_libraries(TRUE, $CI);
			}
		}
		else
		{		
			foreach ($this->_fc_models as $model)
			{			
				$this->$model->_assign_libraries(TRUE, $CI);
			}
		}
	}



}

/* End of file Loader.php */
/* Location: ./system/libraries/Loader.php */