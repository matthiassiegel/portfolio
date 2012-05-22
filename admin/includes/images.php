<?php

function createThumbnail($path, $file, $width, $square = false) {
	
	ini_set('memory_limit','64M');
	
	
	// Get image size info
	list($width_orig, $height_orig, $image_type) = getimagesize($path . '/' . $file);

	switch ($image_type) :
		case 1:
			$image = imagecreatefromgif($path . '/' . $file);
			break;
		case 2:
			$image = imagecreatefromjpeg($path . '/' . $file);
			break;
		case 3:
			$image = imagecreatefrompng($path . '/' . $file);
			break;
	endswitch;

	// Calculate the aspect ratio
	$aspect_ratio = (float) $height_orig / $width_orig;
	

	if ($square) :
		$height = $width;
	
		// Landscape format
		if ($width_orig > $height_orig) :
			$width_new = $height_orig;
			$height_new = $height_orig;
			$x_coord = ($width_orig / 2) - ($width_new / 2);
			$y_coord = 0;
		// Portrait format
		elseif ($width_orig < $height_orig) :
			$width_new = $width_orig;
			$height_new = $width_orig;
			$x_coord = 0;
			$y_coord = ($height_orig / 2) - ($height_new / 2);
		// Square format
		elseif ($width_orig == $height_orig) :
			$width_new = $width_orig;
			$height_new = $height_orig;
			$x_coord = 0;
			$y_coord = 0;
		endif;

	
		// Set filenames and paths
		$extension = strtolower(substr(strrchr($file, '.'), 1));
		$name = substr($file, 0, strrpos($file, '.'));
		$thumbfilename = $name . '-' . $width . 'x' . $height . '.' . $extension;
		$thumbpath = $path . '/' . $name . '-' . $width . 'x' . $height . '.' . $extension;
	
		$thumb = imagecreatetruecolor($width, $height);

		// Check if this image is PNG or GIF, then set transparency if necessary
		if (($image_type == 1) || ($image_type == 3)) :
			imagealphablending($thumb, false);
			imagesavealpha($thumb, true);
			$transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
			imagefilledrectangle($thumb, 0, 0, $width, $height, $transparent);
		endif;
	
		imagecopyresampled($thumb, $image, 0, 0, $x_coord, $y_coord, $width, $height, $width_new, $height_new);
		
	else :
	
		// Calculate the thumbnail height based on the width
		$height = round($width * $aspect_ratio);

		// Set filenames and paths
		$extension = strtolower(substr(strrchr($file, '.'), 1));
		$name = substr($file, 0, strrpos($file, '.'));
		$thumbfilename = $name . '-' . $width . 'x' . $height . '.' . $extension;
		$thumbpath = $path . '/' . $name . '-' . $width . 'x' . $height . '.' . $extension;

		$thumb = imagecreatetruecolor($width, $height);

		// Check if this image is PNG or GIF, then set transparency if necessary
		if (($image_type == 1) || ($image_type == 3)) :
			imagealphablending($thumb, false);
			imagesavealpha($thumb, true);
			$transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
			imagefilledrectangle($thumb, 0, 0, $width, $height, $transparent);
		endif;

		imagecopyresampled($thumb, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
	
	endif;

	// Generate the file, and save it
	switch ($image_type) :
		case 1:
			imagegif($thumb, $thumbpath);
			break;
		case 2:
			imagejpeg($thumb, $thumbpath, 85);
			break;
		case 3:
			imagepng($thumb, $thumbpath, 9);
			break;
	endswitch;
	
	
	$thumbnail = array();
	$thumbnail['filename'] = $thumbfilename;
	$thumbnail['width'] = $width;
	$thumbnail['height'] = $height;
	
	return $thumbnail;
}

?>