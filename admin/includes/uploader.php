<?php

define('WP_ADMIN', true);
define('WP_PATH', $_POST['abspath']);

require_once(WP_PATH . '/wp-load.php');
require_once(WP_PATH . '/wp-admin/includes/admin.php');

@header('Content-Type: text/html; charset=' . get_option('blog_charset'));


ini_set('memory_limit','64M');


do_action('admin_init');

global $wpdb;

$wpdb->portfoliomedia = $wpdb->prefix . 'portfolio_media';
$settings = get_option('portfolio_options');


if ($settings['debug']) :
	$wpdb->show_errors();
else :
	$wpdb->hide_errors();
endif;



// Remove special signs from filename
function cleanFilename($filename) {
	$temp = $filename;

	// Replace spaces with a '-'
	$temp = str_replace(" ", "-", $temp);
	
	$ext = strtolower(substr(strrchr($temp, '.'), 1));
	$temp = substr($temp, 0, strrpos($temp, '.'));

	// Replace dots with a '_'
	$temp = str_replace(".", "_", $temp);

	// Loop through string
	$result = '';
	for ($i = 0; $i < strlen($temp); $i++) {
		if (preg_match('([0-9]|[a-z]|[A-Z]|_|-)', $temp[$i])) {
			$result = $result . $temp[$i];
		}
	}
	$result = substr($result, 0, 40);

	// Return new filename with extension
	return $result . "." . $ext;
}


// Check if the file already exists
function duplicateFilename($filename, $dir) {
	if ($handle = opendir($dir)) :
		while (false !== ($file = readdir($handle))) :
			if ($file != "." && $file != "..") :
				if (strtolower($filename) == strtolower($file)) :
					return true;
				endif;
			endif;
		endwhile;
		closedir($handle);
		
		return false;
	endif;	
}



if (!empty($_FILES)) :
	
	foreach ($_FILES as $file) :
	
		// The temp file
		$old = $file['tmp_name'];
		
		// Cleanup special signs
		$new = cleanFilename($file['name']);
		
		// Rename if filename already exists
		if (duplicateFilename($new, $_POST['path']) == true) :
			$extension = strtolower(substr(strrchr($new, '.'), 1));
			$name = substr($new, 0, strrpos($new, '.'));
			$i = 1;
			
			while (duplicateFilename($new, $_POST['path']) == true) :
				$new = $name . '-' . $i . '.' . $extension;
				$i++;
			endwhile;
		endif;
		
		// The new filename with upload path
		$filename = $new;
		$name = substr($filename, 0, strrpos($filename, '.'));
		$new = $_POST['path'] . '/' . $new;
		
		// Move the temp file to the media directory
		if (!move_uploaded_file($old, $new)) :
			die('0');
		endif;
		
		
		// Check if file is an image
		$images = array('image/jpeg', 'image/png', 'image/gif');
		$info = getimagesize($new);
		
		
		// Try to determine MIME type
		if (in_array($info['mime'], $images)) :
			$mimetype = $info['mime'];
		elseif (function_exists('mime_content_type')) :
			$mimetype = mime_content_type($new);
		elseif (function_exists('finfo_file')) :
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mimetype = finfo_file($finfo, $new);
			finfo_close($finfo);
		else :
			$mimetype = '';
		endif;
		
		if (strpos($mimetype, ';') != false) :
			$mimetype = substr($mimetype, 0, strpos($mimetype, ';'));
		endif;
		
		// If file is an image, create thumbnails and store additional info
		if (in_array($info['mime'], $images)) :
		
			$width = $info[0];
			$height = $info[1];
			
			
			// Create thumbnail with medium width
			if ($width > (int) $settings['thumb1_width']) :
			
				// Check if GD extension is loaded
				if (!extension_loaded('gd') && !extension_loaded('gd2')) :
					die('GD extension not available');
				endif;
			
				include_once(dirname(__FILE__) . '/images.php');
				
				$square = ($settings['thumb1_square'] == '1') ? true : false;
				
				$medium = createThumbnail($_POST['path'], $filename, (int) $settings['thumb1_width'], $square);
			
			endif;
			
			
			// Create thumbnail with small width
			if ($width > (int) $settings['thumb2_width']) :

				// Check if GD extension is loaded
				if (!extension_loaded('gd') && !extension_loaded('gd2')) :
					die('GD extension not available');
				endif;
		
				include_once (dirname (__FILE__) . '/images.php');
			
				$square = ($settings['thumb2_square'] == '1') ? true : false;
				
				$small = createThumbnail($_POST['path'], $filename, (int) $settings['thumb2_width'], $square);
			
			endif;
			
			
			$thumb1_name = empty($medium['filename']) ? NULL : $medium['filename'];
			$thumb1_width = empty($medium['width']) ? NULL : $medium['width'];
			$thumb1_height = empty($medium['height']) ? NULL : $medium['height'];
			
			$thumb2_name = empty($small['filename']) ? NULL : $small['filename'];
			$thumb2_width = empty($small['width']) ? NULL : $small['width'];
			$thumb2_height = empty($small['height']) ? NULL : $small['height'];
			
						
			if ($wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->portfoliomedia} (filename, name, type, width, height, mimetype, thumb1_filename, thumb1_width, thumb1_height, thumb2_filename, thumb2_width, thumb2_height, lastmodified, uploaded) VALUES (%s, %s, %s, %d, %d, %s, %s, %s, %s, %s, %s, %s, %d, %d)", $filename, $name, 'image', $width, $height, $mimetype, $thumb1_name, $thumb1_width, $thumb1_height, $thumb2_name, $thumb2_width, $thumb2_height, time(), time())) === false) :
				die('0');
			endif;
		
		
		// If file is not an image, simply store it
		else :			
			if ($wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->portfoliomedia} (filename, name, type, mimetype, lastmodified, uploaded) VALUES (%s, %s, %s, %s, %d, %d)", $filename, $name, 'other', $mimetype, time(), time())) === false) :
				die('0');
			endif;
		endif;


		echo '{"id": "' . $wpdb->insert_id . '", "real_filename": "' . $filename . '"}';
	
	endforeach;

endif;

?>