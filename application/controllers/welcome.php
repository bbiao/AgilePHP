<?php

class Welcome extends Action {

	function Welcome()
	{
		parent::Action();	
	}
	
	function index()
	{
		$this->load->view('welcome_message');
	}
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */