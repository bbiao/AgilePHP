<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require "Smarty-2.6.20/libs/Smarty.class.php";

/**
 * @file core/libraries/Smarty.php
 */
class CI_Smarty extends Smarty
{
	function CI_Smarty()
	{
		parent::Smarty();

		$config =& get_config();

		// absolute path prevents "template not found" errors
		$this->template_dir = (!empty($config['smarty_template_dir']) ? $config['smarty_template_dir'] : BASEPATH.'application/views/');

		$this->compile_dir = (!empty($config['smarty_compile_dir']) ? $config['smarty_compile_dir'] : BASEPATH.'cache/');
		//use CI's cache folder

		if (function_exists('site_url'))
		{
			// URL helper required
			$this->assign("site_url", site_url()); // so we can get the full path to CI easily
		}
	}

	/**
	 * @param $resource_name string
	 * @param $params array holds params that will be passed to the template
	 * @desc loads the template
	 */
	function view($resource_name, $params = array())
	{
		if (strpos($resource_name, '.') === false)
		{
			$resource_name .= '.html';
		}

		if (is_array($params) && count($params))
		{
			foreach ($params as $key => $value)
			{
				$this->assign($key, $value);
			}
		}

		// check if the template file exists.
		if (!is_file($this->template_dir . $resource_name))
		{
			show_error("template: [$resource_name] cannot be found.");
		}

		return parent::display($resource_name);
	}
} // END class smarty_library
?>