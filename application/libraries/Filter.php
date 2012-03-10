<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!defined('FILTERS_FOLDER'))
{
	define('FILTERS_FOLDER', APPPATH.'word_filters/');
}

class Filter
{
	private $rules = array ();

	private static $marks = array (
		'*',
		'**',
		'***',
		'****',
		'*****',
		'******',
		'*******',
		'********',
		'*********',
		'**********'
	);
	
	public function __construct($config) {
		if (is_dir(FILTERS_FOLDER)) {
			if ($dh = opendir(FILTERS_FOLDER)) {
				while (($file = readdir($dh)) !== false) {
					if (is_file(FILTERS_FOLDER.$file)) {
						$temp_array = file(FILTERS_FOLDER.$file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES | FILE_TEXT);
						
						foreach ($temp_array as $line) {
							if ($line[0] != '#') { // NOT comments
								if(strpos($line, '=')) {
									$rule = explode('=', $line);
									$this->rules[$rule[0]] = $rule[1];
								} else {
									$len = mb_strlen($line);
									$this->rules[$line] = Filter::$marks[($len - 1) % 10];
								}
							}
						}
					}
				}
				closedir($dh);
			}
		}
	}
	
	public function harmony($str) {
		$str = strtr($str, $this->rules);
		
		return $str;
	}
}
