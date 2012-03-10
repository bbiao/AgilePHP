<?php

class Platform extends Action
{
	public function Platform()
	{
		parent::Action();
		
		$this->load->helper(array ('html', 'url', 'form', 'core'));
	}
	
	public function index()
	{
		hello();
		
		$this->load->library('test');
		$this->test->hello();
	}
	
	public function version()
	{
		echo FC_VERSION;
	}
	
	public function zend()
	{
		$this->load->vendor('zend');
		$this->load->zend('Zend_Service_Flickr', '2817769066d25c8c7ddb90fda676ec09', 'flickr');

		
		$results = $this->flickr->tagSearch("dog");
		if ($results->totalResults() > 0)
		{
			foreach ($results as $result)
			{
				echo '<img src="'.$result->Square->uri.'" />';
			}
		}
	}
	
	public function feed()
	{
		$this->load->vendor('Zend');
		$this->zend->load('Zend_Feed_Reader');
		
		$feed = Zend_Feed_Reader::import('http://www.agilephp.net/feed/');
		$data = array(
		    'title'        => $feed->getTitle(),
		    'link'         => $feed->getLink(),
		    'dateModified' => $feed->getDateModified(),
		    'description'  => $feed->getDescription(),
		    'language'     => $feed->getLanguage(),
		    'entries'      => array(),
		);
		
		foreach ($feed as $entry) {
		    $edata = array(
		        'title'        => $entry->getTitle(),
		        'description'  => $entry->getDescription(),
		        'dateModified' => $entry->getDateModified(),
		        'author'       => $entry->getAuthor(),
		        'link'         => $entry->getLink(),
		        'content'      => $entry->getContent()
		    );
		    $data['entries'][] = $edata;
		}
		
		echo '<pre>';
		print_r($data);
		echo '</pre>';
	}
	
	function gdocs()
	{
		$this->load->vendor('Zend');
		$this->zend->load('Zend_Gdata_Docs');
		$this->zend->load('Zend_Gdata_ClientLogin');
		
		$service = Zend_Gdata_Docs::AUTH_SERVICE_NAME;
		$client = Zend_Gdata_ClientLogin::getHttpClient('bbbiao', 't123fira', $service);
		$docs = new Zend_Gdata_Docs($client);
		$feed = $docs->getDocumentListFeed();
		
		echo '<pre>';
		print_r($feed->entries);
		echo '</pre>';
		
	}
	
}

?>