<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

if (! function_exists('css'))
{
	function css() {
		$len = func_num_args();
		if ($len == 0) {
			return '';
		} else {
			$CI = &get_instance();
			$uri = func_get_args();
				
			$link = '';
			foreach ($uri as $val)
			{
				$val = $CI->config->item('static_base_url').$CI->config->item('static_base_path').$val;
				$link .= '<link rel="stylesheet" type="text/css" href="' . $val . '" />';
			}

			return $link;
		}
	}
}

if (! function_exists('js'))
{
	function js($js, $base = '') {
		$CI = &get_instance();
		if (! is_array ($js)) {
			$js = array (
				$js
			);
		}

		$script_tags = '';

		foreach ($js as $key => $js_uri) {
			if (is_string ( $key )) {
				$script_tags .= js ( $js_uri, $base . '/' . $key );
			} else {
				$val = $CI->config->item('static_base_url').$CI->config->item('static_base_path').$base . '/' . $js_uri;
				$script_tags .= '<script type="text/javascript" src="' . $val . '"></script>';
			}
		}

		return $script_tags;
	}
}

if (! function_exists('img'))
{
	function img($uri, $attributes = array()) {
		$CI = &get_instance();
		if (strpos($uri, 'http') === FALSE)
		{
			$uri = $CI->config->item('static_base_url').$CI->config->item('static_base_path').$uri;
		}

		$uri = '<img src="' . $uri . '"';

		if (is_array($attributes) AND count($attributes) > 0)
		{
			foreach ($attributes as $key => $val)
			{
				$uri .= ' ' . $key . '="' . $val . '"';
			}
		}

		$uri .= '></img>';
		return $uri;
	}
}

if (! function_exists('favorite_icon'))
{
	function favorite_icon($uri) {
		$CI = &get_instance();
		$uri = $CI->config->item('static_base_url') . $uri;
		$fav_icon = <<<URL
<link rel="icon" href="${uri}" type="image/x-icon" />
<link rel="shortcut icon" href="${uri}" type="image/x-icon" />
<link rel="bookmark" href="${uri}" type="image/x-icon" />
URL;

		return $fav_icon;
	}
}

if (! function_exists('avatar'))
{
	function avatar($uri, $type = "middle", $attr = array()) {
		
		$suffix_map = array
		(
			'icon' => '_s',
			'small' => '_s',
			'middle' => '_m',
			'large' => '_l'
		);
		
		$size_map = array
		(
			'icon' => array 
			(
				'width' => 24,
				'height' => 24
			),
			'small' => array
			(
				'width' => '50',
				'height' => '50'
			),
			'middle' => array
			(
				'width' => '98',
				'height' => '98'
			),
			'large' => array
			(
				'width' => '200',
				'height' => '200'
			)
		);
		
		if ($uri == '') $uri = 'static/img/default_avatar.png';

		$pos = strrpos($uri, '.');
		$uri_new = substr($uri, 0, $pos).$suffix_map[$type].substr($uri, $pos);
		$attr = array_merge($size_map[$type], $attr);
		
		return img(site_url($uri_new), $attr);
	}
}

if (! function_exists('design_img'))
{
	function design_img($uri, $type = "middle", $attr = array()) {
		
		$suffix_map = array
		(
			'small' => '_s',
			'middle' => '_m',
			'large' => ''
		);
		
		$size_map = array
		(
			'small' => array
			(
				'width' => '50',
				'height' => '50'
			),
			'middle' => array
			(
				'width' => '140',
				'height' => '140'
			),
			'large' => array
			(
				'width' => '300',
				'height' => '300'
			)
		);
		
//		if ($uri == '') $uri = $is_designer ? 'statics/images/avatar/designer.jpg' : 'statics/images/avatar/user.jpg';

		$pos = strrpos($uri, '.');
		$uri_new = substr($uri, 0, $pos).$suffix_map[$type].substr($uri, $pos);
		$attr = array_merge($size_map[$type], $attr);
		
		return img($uri_new, $attr);
	}
}

if (! function_exists('birthday'))
{
	function birthday($attr = array(), $selected = '', $start = 1910, $end = 2009)
	{
		$select = '<select';
		
		foreach ($attr as $key => $val)
		{
			$str = " {$key}=\"{$val}\"";
			$select .= $str;
		}
		
		$select .= '>';
		for($i = $start; $i <= $end; $i++)
		{
			$option = "<option value=\"{$i}\"";
			
			if ($i == $selected)
			{
				$option .= ' selected="selected"';
			}
			
			$option .= ">{$i}</option>";
			$select .= $option;
		}
		$select .= '</select>';
		
		return $select;
	}
}

?>