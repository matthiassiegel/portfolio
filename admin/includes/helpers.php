<?php

// Convert bytes into a more readable format
// Taken from http://php.net/manual/en/function.filesize.php
function formatBytes($bytes, $precision = 2) {
	$units = array('B', 'KB', 'MB', 'GB', 'TB');

	$bytes = max($bytes, 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);

	$bytes /= pow(1024, $pow);

	return round($bytes, $precision) . ' ' . $units[$pow];
}	


// Recursively convert arrays to objects
function makeObject($array) {
	if (!is_array($array)) :
		return $array;
	endif;
	
	$object = new stdClass();
	
	if (is_array($array) && count($array) > 0) :
	  foreach ($array as $name => $value) :
	     if (!is_null($name)) :
	        $object->$name = makeObject($value);
	     endif;
	  endforeach;
	
      return $object;

    else :
      return false;
    endif;
}

?>