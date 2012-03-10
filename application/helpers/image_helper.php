<?php
if (! function_exists('image_sizefy'))
{
	function image_sizefy($path, $new_width, $new_height, $create_thumb = FALSE, $thumb_marker = '_thumb')
	{
		if ($path == '' || !file_exists($path))
		{
			return FALSE;
		}
		
		$val = getimagesize($path);
		$old_width = $val[0];
		$old_height = $val[1];

		if ($old_width == $new_width && $old_height == $new_height)
		{
			return TRUE;
		}

		$CI =& get_instance();
		if (isset($CI->image) == FALSE)
		{
			$CI->load->library('image_lib', NULL, 'image');
		}
		
		$image_config = array 
		(
			'image_library' => 'GD2',
			'source_image' => $path,
			'create_thumb' => FALSE,
			'maintain_ratio' => TRUE,
			'create_thumb' => $create_thumb,
			'thumb_marker' => $thumb_marker			
		);
		
		$old_ratio = $old_width / $old_height;
		$new_ratio = $new_width / $new_height;
		
		// Resize one side of the old image to the same as the new one
		if ($old_ratio < $new_ratio)
		{
			$image_config['master_dim'] = 'width';
			$image_config['width'] = $new_width;
			$image_config['height'] = $new_height;
		}
		else
		{
			$image_config['master_dim'] = 'height';
			$image_config['width'] = $new_width;
			$image_config['height'] = $new_height;
		}
		$CI->image->clear();
		$CI->image->initialize($image_config);
		if (!$CI->image->resize())
		{
			return FALSE;
		}
		
		// Crop the old image
		$xn = $CI->image->explode_name($path);
		if ($create_thumb)
		{
			$path = $xn['name'].$thumb_marker.$xn['ext'];
			$image_config['create_thumb'] = FALSE;
			$image_config['source_image'] = $path;
		}
		
		$image_config['maintain_ratio'] = FALSE;
		if ($image_config['master_dim'] == 'width')
		{
			$image_config['y_axis'] = ($new_width / $old_ratio - $new_height) / 2;
			$image_config['height'] = $new_height;
		}
		else
		{
			$image_config['x_axis'] = ($new_height * $old_ratio - $new_width) / 2;
			$image_config['width'] = $new_width;
		}
		$CI->image->clear();
		$CI->image->initialize($image_config);
		
		if (!$CI->image->crop())
		{
			return FALSE;
		}
		
		return TRUE;
	}
}