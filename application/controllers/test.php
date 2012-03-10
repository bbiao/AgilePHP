<?php

class Test extends Action
{
	function Test()
	{
		parent::Action();
		
		$this->load->helper(array ('html', 'url', 'form', 'core'));
	}
	
	function list_dirs()
	{
		echo '<pre>';
			echo FCPATH."\n";
			echo BASEPATH."\n";
			echo APPPATH."\n";
			echo DOCPATH."\n";
			echo COREPATH."\n";
			echo MODPATH."\n";
			
			hello();
		echo '</pre>';
	}
	
	function memcache()
	{
		$this->load->library('memcache');
		//$this->memcache->add('foo', 'bar');
		$foo = $this->memcache->get('blogs1');
		print_r($foo);
	}
	
	function memcached_ar()
	{
		$this->load->model('blog_model', 'blog');
		$b = $this->blog->find(1);
		
		print_r($b);
	}
}

?>