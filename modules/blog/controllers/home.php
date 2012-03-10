<?php
class Home extends Action
{
	function Home()
	{
		parent::Action();
		$this->load->model('blog_model', 'blog');
	}
	
	function index()
	{
		$b = $this->blog->find(1);
		print_r($b);
	}
}