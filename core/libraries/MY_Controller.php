<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Maybe this class should be configble
 *
 */

class MY_Controller extends Controller
{
	var $_fc_base_path = NULL;
	
	public function MY_Controller()
	{
		$context =& get_context();
		$class = get_class($this);
		$this->_fc_base_path = $context->get($class);
		
		parent::Controller();
	}
	
	// --------------------------------------------------------------------

	/**
	 * Initialize
	 *
	 * Assigns all the bases classes loaded by the front controller to
	 * variables in this class.  Also calls the autoload routine.
	 *
	 * @access	private
	 * @return	void
	 */
	function _ci_initialize()
	{
		if ($this->_fc_base_path == '')
		{
			return;	
		}
		
		// Assign all the class objects that were instantiated by the
		// front controller to local class variables so that CI can be
		// run as one big super object.
		$classes = array(
							'config'	=> 'Config',
							'input'		=> 'Input',
							'benchmark'	=> 'Benchmark',
							'uri'		=> 'URI',
							'output'	=> 'Output',
							'lang'		=> 'Language',
							'router'	=> 'Router'
							);
		
		foreach ($classes as $var => $class)
		{
			$this->$var =& load_class($class);
		}

		// In PHP 5 the Loader class is run as a discreet
		// class.  In PHP 4 it extends the Controller
		if (floor(phpversion()) >= 5)
		{
			$this->load =& load_class('Loader');
			
			//+Added by ZHANG Biao
			$this->load->_fc_ctrl = $this;
			$this->load->_fc_base_path = $this->_fc_base_path;
			//+Added end
			
			$this->load->_ci_autoloader();
		}
		else
		{
			//+Added by ZHANG Biao
			$this->load->_fc_ctrl = $this;
			$this->load->_fc_base_path = $this->_fc_base_path;
			//+Added end
			
			$this->_ci_autoloader();
			
			// sync up the objects since PHP4 was working from a copy
			foreach (array_keys(get_object_vars($this)) as $attribute)
			{
				if (is_object($this->$attribute))
				{
					$this->load->$attribute =& $this->$attribute;
				}
			}
		}
	}
	
	function set_base_path($base_path)
	{
		$this->_fc_base_path = $base_path;
		$this->load->_fc_base_path = $this->_fc_base_path;
		
		$this->_ci_initialize();
	}
}

class Action extends MY_Controller
{
	public function Action()
	{
		parent::MY_Controller();
	}
}

define('MODEL_SUFFIX', '_model');

class ScaffoldAction extends Action
{
	var $clazz = NULL;
	var $table = NULL;
	var $model = NULL;
	
	var $fields = NULL;
	
	function ScaffoldAction()
	{
		parent::Action();

		$this->load->helpers(array ('form', 'html', 'language', 'url'));
		$this->load->helper('inflector');
		{
			$clazz = get_class($this);
        	$clazz = strtolower($clazz);
        	
        	$this->clazz = $clazz;
        	$this->table = plural($clazz);
        	$this->model = $clazz.MODEL_SUFFIX;
        	
        	if ($this->db->table_exists($this->table))
			{
				$this->load->model($this->model, $this->clazz);
			}
			else
			{
				show_error('Table not found: '.$this->table.'.');
			}
		}
	}
	
	function _parse_pagination()
	{
		parse_str($_SERVER['QUERY_STRING'], $_GET);
		$start = $this->input->get('start');
		$limit = $this->input->get('limit');
		$sort = $this->input->get('sort');
		$dir = $this->input->get('dir');
		
		$query = $this->input->get('query');
		
		if ($start == '')
		{
			$start = 0;
		}
		
		if ($limit == '')
		{
			$limit = 20;
		}
		
		if ($sort == '')
		{
			$sort = 'id';
		}
		
		if ($dir == '')
		{
			$dir = 'ASC';
		}
		
		return array ($start, $limit, $sort, $dir, $query);
	}
	
	/**
	 * GET /model?offset=0&size=20
	 * @param $return
	 * @return unknown_type
	 */
	function index($return = FALSE)
	{
		$clazz = $this->clazz;
		$table = $this->table;
		$model = $this->$clazz;
		
		$this->lang->load('scaffolding');
		$language = $this->lang->load('table_fields/'.$clazz, '', TRUE);
		
		$fields = $model->list_fields();
		
		list($start, $limit, $sort, $dir) = $this->_parse_pagination();
		$result = $model->find_all(array(), $start, $limit, $sort, $dir);
		
		$count = $model->count();
		
		$data = array (
			'fields' => $fields,
			'result' => $result,
			'count' => $count,
			'language' => $language
		);
		
		if ($return == FALSE)
		{
			$this->load->layout('scaffold/layout', 'scaffold/index', $data);
		}
		else
		{
			return array (
				'layout' => 'scaffold/layout',
				'view' => 'scaffold/index',
				'data' => $data
			);
		}
	}
	
	/**
	 * GET /model/view/{$item_id}
	 * @param $return
	 * @return unknown_type
	 */
	function view($id = 0, $return = FALSE)
	{
		$clazz = $this->clazz;
		$table = $this->table;
		$model = $this->$clazz;

		$result = $model->find($id);
		
		$this->lang->load('scaffolding');
		$language = $this->lang->load('table_fields/'.$clazz, '', TRUE);
		
		$fields = $model->list_fields();

		$data = array (
			'fields' => $fields,
			'result' => $result,
			'language' => $language
		);
		
		if ($return == FALSE)
		{
			if ($result !== NULL)
			{
				$this->load->layout('scaffold/layout', 'scaffold/view', $data);
			}
			else
			{
				show_404('Item not found, id: '.$id.'.');				
			}
		}
		else
		{
			return array (
				'layout' => 'scaffold/layout',
				'view' => 'scaffold/view',
				'data' => $data
			);
		}
	}
	
	/**
	 * POST /model/insert
	 * @return unknown_type
	 */
	function insert($return = FALSE)
	{
		$clazz = $this->clazz;
		$table = $this->table;
		$model = $this->$clazz;
		
		$clazz = $this->clazz;
		$table = $this->table;
		$model = $this->$clazz;

		$fields = $model->list_fields();
		
		$record = $this->input->post($fields);
		
		if (isset($record['id']))
		{
			unset($record['id']);
		}
		$id = $model->insert($record);
		
		$data = array (
			'success' => TRUE,
			'message' => 'Create successed!',
			'id' => $id
		);
		
		if ($return == TRUE)
		{
			return $id;
		}
		else
		{
			echo json_encode($data);
		}
	}
	
	function create($return = FALSE)
	{
		return $this->insert($return);
	}
	
	/**
	 * POST /model/update
	 * @return unknown_type
	 */
	function update()
	{
		$clazz = $this->clazz;
		$table = $this->table;
		$model = $this->$clazz;

		$fields = $model->list_fields();
		
		$record = $this->input->post($fields);
		$id = $record['id'];
		unset($record['id']);
		
		$model->update($id, $record);
		
		$row = $model->find($id);
		if ($row != FALSE) 
		{
			$data = array (
				'success' => TRUE,
				'message' => 'Update succeed!',
				'record' => $row
			);
		}
		else
		{
			$data = array (
				'success' => FALSE,
				'message' => 'No such record'
			);
		}
		
		echo json_encode($data);
	}
	
	/**
	 * POST /model/delete/
	 * @return unknown_type
	 */
	function delete()
	{
		$clazz = $this->clazz;
		$table = $this->table;
		$model = $this->$clazz;
		
		$id = $this->input->post('id');
		$result = $model->delete($id);
		
		return $id;
	}
	
	/**
	 * GET /model/edit/{item_id}
	 * @return unknown_type
	 */
	function edit($id = 0)
	{
		$clazz = $this->clazz;
		$table = $this->table;
		$model = $this->$clazz;

		$result = $model->find($id);
		
		$this->lang->load('scaffolding');
		$language = $this->lang->load('table_fields/'.$clazz, '', TRUE);
		
		$fields = $model->list_fields();

		$data = array (
			'fields' => $fields,
			'result' => $result,
			'language' => $language
		);
		
		if ($return == FALSE)
		{
			if ($result !== NULL)
			{
				$this->load->layout('scaffold/layout', 'scaffold/edit', $data);
			}
			else
			{
				show_404('Item not found, id: '.$id.'.');				
			}
		}
		else
		{
			return array (
				'layout' => 'scaffold/layout',
				'view' => 'scaffold/edit',
				'data' => $data
			);
		}
	}
}



?>