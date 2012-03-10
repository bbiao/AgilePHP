<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Output extends CI_Output 
{
	public function append_json($obj) {
		parent::append_output(json_encode($obj));
	}
}