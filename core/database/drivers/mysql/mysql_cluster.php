<?php
class CI_DB_mysql_cluster extends CI_DB_mysql_driver
{
	var $master_group = NULL;
	var $slave_group = NULL;
	
	var $db_config = NULL;
	
	/* Write to the master, and read from the slave */
	var $master_conn_id = NULL;
	var $slave_conn_id = NULL;

	function CI_DB_mysql_cluster($db_config, $master_group, $slave_group)
	{
		$this->db_config = $db_config;
		$this->master_group = explode(',', $master_group);
		$this->slave_group = explode(',', $slave_group);
		
		parent::CI_DB_mysql_driver($db_config[$this->master_group[0]]);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Simple Query
	 * This is a simplified version of the query() function.  Internally
	 * we only use it when running transaction commands since they do
	 * not require all the features of the main query() function.
	 *
	 * @access	public
	 * @param	string	the sql query
	 * @return	mixed		
	 */	
	function simple_query($sql)
	{
		if (stristr($sql, 'SELECT'))
		{
			if ($this->slave_conn_id == NULL)
			{
				$this->initialize(FALSE);
			}
			
			$this->conn_id = $this->slave_conn_id;
		}
		else if (is_write_type($sql))
		{
			if ($this->master_conn_id == NULL)
			{
				$this->initialize(TRUE);
			}
			$this->conn_id = $this->master_conn_id;
		}

		return $this->_execute($sql);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Non-persistent database connection
	 *
	 * @access	private called by the base class
	 * @return	resource
	 */	
	function db_connect($config = NULL)
	{
		$hostname = NULL; $port = NULL; $username = NULL; $password = NULL;
		
		if (is_array($config))
		{
			$hostname = $config['hostname'];
			$port = $config['port'];
			$username = $config['username'];
			$password = $config['password'];
		}
		else
		{
			$hostname = $this->hostname;
			$port = $this->port;
			$username = $this->username;
			$password = $this->password;
		}
		
		if ($port != '')
		{
			$hostname .= ':'.$port;
		}
		
		return @mysql_connect($hostname, $username, $password, TRUE);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Persistent database connection
	 *
	 * @access	private called by the base class
	 * @return	resource
	 */	
	function db_pconnect($config = NULL)
	{
		$hostname = NULL; $port = NULL; $username = NULL; $password = NULL;
		
		if (is_array($config))
		{
			$hostname = $config['hostname'];
			$port = isset($config['port']) ? $config['port'] : '';
			$username = $config['username'];
			$password = $config['password'];
		}
		else
		{
			$hostname = $this->hostname;
			$port = isset($this->port) ? $this->port : '';
			$username = $this->username;
			$password = $this->password;
		}
		
		if ($port != '')
		{
			$hostname .= ':'.$port;
		}
		
		return @mysql_connect($hostname, $username, $password);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Initialize Database Settings
	 *
	 * @access	private Called by the constructor
	 * @param	mixed
	 * @return	void
	 */	
	function initialize($is_master = TRUE)
	{
		if ($is_master) // Master connction
		{
			if (is_resource($this->master_conn_id) OR is_object($this->master_conn_id))
			{
				$this->conn_id = $this->master_conn_id;
				return TRUE;
			}
			else
			{
				$this->conn_id = NULL;
				
				if (parent::initialize())
				{
					$this->master_conn_id = $this->conn_id;
					return TRUE;
				}
				else
				{
					return FALSE;
				}
			}
		}
		else // Slave connection
		{
			if (is_resource($this->slave_conn_id) OR is_object($this->slave_conn_id))
			{
				$this->conn_id = $this->slave_conn_id;
				return TRUE;
			}
			else
			{
				$slave_id = rand(0, count($this->slave_group) - 1);
				$slave = $this->slave_group[$slave_id];
				$config = $this->db_config[$slave];
				
				// Connect to the database and set the connection ID
				$this->conn_id = ($this->pconnect == FALSE) ? $this->db_connect($config) : $this->db_pconnect($config);
				
				$this->slave_conn_id = $this->conn_id;
				
				// No connection resource?  Throw an error
				if ( ! $this->conn_id)
				{
					log_message('error', 'Unable to connect to the database');
					
					if ($this->db_debug)
					{
						$this->display_error('db_unable_to_connect');
					}
					return FALSE;
				}
				
				// ----------------------------------------------------------------

				// Select the DB... assuming a database name is specified in the config file
				if ($config['database'] != '')
				{
					if ( ! $this->db_select($config['database']))
					{
						log_message('error', 'Unable to select database: '.$config['database']);
					
						if ($this->db_debug)
						{
							$this->display_error('db_unable_to_select', $config['database']);
						}
						return FALSE;			
					}
					else
					{
						// We've selected the DB. Now we set the character set
						if ( ! $this->db_set_charset($this->char_set, $this->dbcollat))
						{
							return FALSE;
						}
				
						return TRUE;
					}
				}
			}	
		}

		return TRUE;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Select the database
	 *
	 * @access	private called by the base class
	 * @return	resource
	 */	
	function db_select($database = NULL)
	{
		if (! $database) $database = $this->database;
		return @mysql_select_db($database, $this->conn_id);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Close DB Connection
	 *
	 * @access	public
	 * @return	void		
	 */	
	function close()
	{
		if (is_resource($this->conn_id) OR is_object($this->conn_id))
		{
			$this->_close($this->conn_id);
		}
		$this->conn_id = FALSE;
	}
}