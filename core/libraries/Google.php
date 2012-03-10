<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.APPPATH."vendors/");
require_once ('Zend/Loader.php');

class Google
{
	public function __construct()
	{
		Zend_Loader::loadClass('Zend_Gdata');
		Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
		Zend_Loader::loadClass('Zend_Http_Client');
		Zend_Loader::loadClass('Zend_Gdata_Query');
		Zend_Loader::loadClass('Zend_Gdata_Feed');
	}

	public function get_contacts($user, $pass)
	{
		$results = array ();
		
		try {
			$client = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, 'cp');
			$gdata = new Zend_Gdata($client);
			$gdata->setMajorProtocolVersion(3);

			// perform query and get result feed
			$query = new Zend_Gdata_Query('http://www.google.com/m8/feeds/contacts/default/full?max-results=1000');
			$feed = $gdata->getFeed($query);

			foreach($feed as $entry){
				$xml = simplexml_load_string($entry->getXML());
				$obj = array();
				$obj['name'] = (string) $entry->title;
				$obj['orgName'] = (string) $xml->organization->orgName;
				$obj['orgTitle'] = (string) $xml->organization->orgTitle;
					
				$obj['email'] = array();
				foreach ($xml->email as $e) {
					$obj['email'][] = (string) $e['address'];
				}

				$obj['phone'] = array();
				foreach ($xml->phoneNumber as $p) {
					$obj['phone'][] = (string) $p;
				}
					
				$obj['website'] = array();
				foreach ($xml->website as $w) {
					$obj['website'][] = (string) $w['href'];
				}

				$results[] = $obj;
			}
		} catch (Exception $e) {
			die('ERROR:' . $e->getMessage());  
		}

		return $results;
	}
}
