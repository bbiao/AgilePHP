<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
define('DB_NAME_TEST', '_model');

class MY_Model extends Model
{
	function MY_Model()
	{
		parent::Model();
		$this->load->helper('inflector');
	}
	
	function join($tbl, $cond, $column = '*', $extra = '')
	{
		$tbl = plural($tbl);
		
		list($first, $second) = explode('=', $cond);
		
		list($t, $c) = explode('.', $first);
		if ($c != NULL)
		{
			$t = plural($t);
			$first = "{$t}.{$c}";
		}
		
		list($t, $c) = explode('.', $second);
		if ($c != NULL)
		{
			$t = plural($t);
			$second = "{$t}.{$c}";
		}
		
		$cond = "{$first}={$second}";
		
		if (is_array($column))
		{
			foreach($column as $col)
			{
				$this->db->select("{$tbl}.{$col}");
			}
		}
		else
		{
			$this->db->select("{$tbl}.{$column}");
		}
		
		$this->db->join($tbl, $cond, $extra);
	}
}

class ActiveRecord extends MY_Model
{
	var $table = "";
 	
    function ActiveRecord($table = '')
    {
        parent::MY_Model();
        
        if (@$this->db == NULL)
        {
			$this->load->database();
        }
        
        if ($table != NULL)
        {
        	$this->table = $table;
        }
        else
        {
        	$clazz = get_class($this);
        	$clazz = strtolower($clazz);
        	
        	$len = strlen($clazz);
        	
        	if (strrpos($clazz, DB_NAME_TEST) == $len - 6)
        	{
        		$this->table = substr($clazz, 0, $len - 6);
        	}
        	else
        	{
        		$this->table = $clazz;
        	}
        	
        	$this->table = plural($this->table);
        }
    }
 
	function insert($data)
	{
		$this->db->insert($this->table, $data);
		return $this->db->insert_id();
	}
 
	function find($id)
	{
		if ($id == NULL)
		{
			return NULL;
		}
		
		if (!is_array($id))
		{
			$arr = array ( 'id' => $id);
			
			return $this->find($arr);
		}
 
		$this->db->select("{$this->table}.*")->from($this->table);
		
		foreach ($id as $key => $val)
		{
			if (is_int($key))
			{
				$this->db->where($val, NULL, FALSE);
			}
			else
			{
				$this->db->where("{$this->table}.{$key}", $val);
			}
		}
		
		$query = $this->db->get();
 
		$result = $query->result_array();
		return (count($result) > 0 ? $result[0] : NULL);
	}
 
	function find_all($cond = array(), $offset = 0, $limit = 20, $sort = 'id', $order = 'DESC')
	{		
		$this->db->select("{$this->table}.*")->from($this->table);
		
		if ($cond != NULL)
		{
			if (is_array($cond))
			{
				foreach ($cond as $key => $val)
				{
					if (is_int($key))
					{
						$this->db->where($val, NULL, FALSE);
					}
					else
					{
						$this->db->where($key, $val);
					}
				}
			}
			else
			{
				$this->db->where($cond, NULL, FALSE);
			}
		}
		
		$this->db->order_by("{$this->table}.{$sort}", $order);
		$this->db->limit($limit, $offset);
		$query = $this->db->get();
		return $query->result_array();
	}
	
	function find_first($cond, $orderby = 'id', $direction = 'ASC')
	{
		foreach ($cond as $key => $val)
		{
			if (is_int($key))
			{
				$this->db->where($val, NULL, FALSE);
			}
			else
			{
				$this->db->where($key, $val);
			}
		}
		$this->db->order_by($orderby, $direction);
		
		$query = $this->db->get($this->table);
		$result = $query->row_array();
		
		return count($result) == 0 ? NULL : $result;
	}
	
	function find_range($ids)
	{
		if (empty($ids))
		{
			return array();
		}
		
		$this->db->where_in($this->db->dbprefix($this->table).'.id', $ids);
		$query = $this->db->get($this->table);
		return $query->result_array();
	}
 
	function update($id, $data = array(), $escape = TRUE)
	{
		if (is_array($id))
		{
			$cond = $id;
			foreach ($cond as $key => $val)
			{
				if (is_int($key))
				{
					$this->db->where($val, NULL, FALSE);
				}
				else
				{
					$this->db->where($key, $val);
				}
			}
		}
		else
		{
			$this->db->where('id', $id);
		}
		
		///*********************
		foreach ($data as $k => $v)
		{
			if ($v == NULL)
			{
				unset($data[$k]);
			}
		}
		
		if ($escape === FALSE)
		{
			$this->db->set($data, '', FALSE);
			return $this->db->update($this->table);
		}
		else
		{
			return $this->db->update($this->table, $data);
		}
	}
 
	function delete($id)
	{
		if ($id != NULL)
		{
			$this->db->where('id', $id);			
			$this->db->delete($this->table);			
		}
	}	
	
	function count($cond = NULL)
	{
		if ($cond == NULL)
		{
			return $this->db->count_all($this->table);
		}
		else
		{
			foreach ($cond as $key => $val)
			{
				if (is_int($key))
				{
					$this->db->where($val, NULL, FALSE);
				}
				else
				{
					$this->db->where($key, $val);
				}
			}
			
			$this->db->select('COUNT(*) AS count')
					->from($this->table);
					
			$query = $this->db->get();
			$row = $query->row_array();
			
			return $row['count'];
		}
	}
	
	function exist($cond)
	{
		if ($cond == NULL)
		{
			return FALSE;
		}
		else
		{
			foreach ($cond as $key => $val)
			{
				if (is_int($key))
				{
					$this->db->where($val, NULL, FALSE);
				}
				else
				{
					$this->db->where($key, $val);
				}
			}
			
			$this->db->select('id')
					->from($this->table);
					
			$query = $this->db->get();
			$row = $query->row_array();
			
			return count($row) > 0 ? $row['id'] : FALSE;
		}
	}
	
	function insert_or_update($el)
	{
		$id = $this->exist($el);
		if ($id !== FALSE)
		{
			$this->update($id, $el);
		}
		else
		{
			$id = $this->insert($el);
		}
		
		return $id;
	}
	
	function list_fields()
	{
		$fields = $this->db->list_fields($this->table);
		
		return $fields;
	}
}

class LogicDelete extends ActiveRecord
{
	function find($id)
	{
		if (is_array ($id))
		{
			$id['is_delete'] = '0';
		}
		else
		{
			$id = array ('id' => $id, 'is_delete' => '0');
		}
		
		return parent::find($id);
	}
	
	function exist($cond = array())
	{
		$cond['is_delete'] = '0';
		return parent::exist($cond);
	}
	
	function find_all($cond = array(), $offset = 0, $limit = 20, $sort = 'id', $order = 'ASC')
	{
		$cond['is_delete'] = '0';
		return parent::find_all($cond, $offset, $limit, $sort, $order);
	}
	
	function delete($id)
	{
		return parent::update($id, array ('is_delete' => '1'));
	}
	
	function count($cond = array())
	{
		$cond['is_delete'] = '0';
		return parent::count($cond);
	}
}

class MemcachedActiveRecord extends ActiveRecord
{
	var $memcache;
	
	function MemcachedActiveRecord($table = NULL)
	{
		parent::ActiveRecord($table);
		$this->_initialize_memcached();
	}
	
	function _initialize_memcached()
	{
		$this->load->library('memcache');
	}
	
	function find($id)
	{
		if (!is_array($id))
		{
			$result = $this->memcache->get($this->table.$id);
			if ($result != NULL)
			{
				return $result;
			}
			
			$result = parent::find($id);
			$this->memcache->add($this->table.$id, $result);
			return $result;
		}
		
		$result = parent::find($id);
		return $result;
	}
}
 
/* End of file MY_Model.php */
/* Location: ./application/libraries/MY_Model.php */