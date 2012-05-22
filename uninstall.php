<?php

global $wpdb;


// Remove all Portfolio tables
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}portfolio_projects");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}portfolio_categories");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}portfolio_media");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}portfolio_relations");

// Delete media directory and all files in it
function recursiveDelete($str) {
	if (is_file($str)) :
		return @unlink($str);
	elseif (is_dir($str)) :
		$scan = glob(rtrim($str, '/') . '/*');
		foreach ($scan as $index => $path) :
			recursiveDelete($path);
		endforeach;
		return @rmdir($str);
	endif;
}

$options = get_option('portfolio_options');
recursiveDelete($options['media_dir']);

// Remove all Portfolio options
delete_option('portfolio_options');
delete_option('portfolio_check');

?>