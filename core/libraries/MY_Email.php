<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Email extends CI_Email {
	
	// --------------------------------------------------------------------

	/**
	 * Set Email Subject
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public function subject($subject)
	{
		$subject = $this->_prep_b_encoding($subject);
		$this->_set_header('Subject', $subject);
		return $this;
	}
	
	private function _prep_b_encoding($str, $from = FALSE)
	{
		$str = str_replace(array("\r", "\n"), array('', ''), $str);

		// Line length must not exceed 76 characters, so we adjust for
		// a space, 7 extra characters =??Q??=, and the charset that we will add to each line
		$limit = 75 - 7 - strlen($this->charset);

		// these special characters must be converted too
		$convert = array('_', '=', '?');

		$str = base64_encode($str);
		
		// wrap each line with the shebang, charset, and transfer encoding
		// the preceding space on successive lines is required for header "folding"
		$str = trim(preg_replace('/^(.*)$/m', ' =?'.$this->charset.'?B?$1?=', $str));

		return $str;
	}
	
	/**
	 * Add a Header Item
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	void
	 */
	private function _set_header($header, $value)
	{
		$this->_headers[$header] = $value;
	}
}