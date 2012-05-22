<?php  

// Stop direct calls to this file
if (!isset($this)) die('You cannot access this file directly.');


global $wpdb, $portfolio;


if ($portfolio->options['debug']) :
	$wpdb->show_errors();
else :
	$wpdb->hide_errors();
endif;


echo '
	<div class="wrap">
		<h2>About</h2>
		Author, Thanks to, Donate, Websites, Links to Doku, DB Stats, Version, Twitter/www/email, License etc.<br>
		<br>
		External resources: jQuery Uploadify, Icons';
		

echo '<h3>Stats</h3>';

$projects = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->portfolioprojects"));
$categories = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->portfoliocategories"));
$media = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->portfoliomedia"));

echo 'Version: ' . $portfolio->options['version'] . '<br />';
echo 'Media directory: ' . $portfolio->options['media_url'] . '<br />';
echo 'Projects: ' . $projects . '<br />';
echo 'Categories: ' . $categories . '<br />';
echo 'Media files: ' . $media . '<br />';
		
		
		
echo '		
	</div>
	';

?>