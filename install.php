<?php


  	global $wpdb;


// Load the Wordpress upgrade helper functions, in case we're upgrading an existing Portfolio installation
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');


// Add charset & collate like wp core
$charset_collate = '';
if (version_compare(mysql_get_server_info(), '4.1.0', '>=')) :
	if (!empty($wpdb->charset)) $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	if (!empty($wpdb->collate)) $charset_collate .= " COLLATE $wpdb->collate";
endif;


// load existing options if available
$portfolio_options = get_option("portfolio_options");


  	$portfolioprojects = $wpdb->prefix . 'portfolio_projects';
$portfoliocategories = $wpdb->prefix . 'portfolio_categories';
$portfoliomedia = $wpdb->prefix . 'portfolio_media';
$portfoliorelations = $wpdb->prefix . 'portfolio_relations';

if ($wpdb->get_var("SHOW TABLES LIKE '$portfolioprojects'") != $portfolioprojects || $portfolio_options['db_version'] != PORTFOLIO_DBVERSION) :
	$sql = "CREATE TABLE " . $portfolioprojects . " (
		id INT(10) NOT NULL AUTO_INCREMENT,
		name VARCHAR(255) NOT NULL,
		url VARCHAR(255) NULL,
		date VARCHAR(255) NULL,
		management VARCHAR(255) NULL,
		shortdesc MEDIUMTEXT NULL,
		longdesc MEDIUMTEXT NULL,
		lastmodified VARCHAR(10) NULL,
		added VARCHAR(10) NULL,
		status VARCHAR(20) NOT NULL DEFAULT 'draft',
		UNIQUE KEY id (id)
	) " . $charset_collate;
	dbDelta($sql);
endif;


if ($wpdb->get_var("SHOW TABLES LIKE '$portfoliocategories'") != $portfoliocategories || $portfolio_options['db_version'] != PORTFOLIO_DBVERSION) :
	$sql = "CREATE TABLE " . $portfoliocategories . " (
		id INT(10) NOT NULL AUTO_INCREMENT,
		name VARCHAR(255) NOT NULL,
		UNIQUE KEY id (id)
	) " . $charset_collate;
	dbDelta($sql);
endif;


if ($wpdb->get_var("SHOW TABLES LIKE '$portfoliomedia'") != $portfoliomedia || $portfolio_options['db_version'] != PORTFOLIO_DBVERSION) :
	$sql = "CREATE TABLE " . $portfoliomedia . " (
		id INT(10) NOT NULL AUTO_INCREMENT,
		filename VARCHAR(255) NOT NULL,
		name VARCHAR(255) NOT NULL,
		type VARCHAR(255) NOT NULL,
		alt VARCHAR(255) NULL,
		description MEDIUMTEXT NULL,
		width VARCHAR(10) NULL,
		height VARCHAR(10) NULL,
		mimetype VARCHAR(100) NULL,
		thumb1_filename VARCHAR(255) NULL,
		thumb1_width VARCHAR(10) NULL,
		thumb1_height VARCHAR(10) NULL,
		thumb2_filename VARCHAR(255) NULL,
		thumb2_width VARCHAR(10) NULL,
		thumb2_height VARCHAR(10) NULL,
		lastmodified VARCHAR(10) NULL,
		uploaded VARCHAR(10) NULL,
		UNIQUE KEY id (id)
	) " . $charset_collate;
	dbDelta($sql);
endif;


if ($wpdb->get_var("SHOW TABLES LIKE '$portfoliorelations'") != $portfoliorelations || $portfolio_options['db_version'] != PORTFOLIO_DBVERSION) :
	$sql = "CREATE TABLE " . $portfoliorelations . " (
		id INT(10) NOT NULL AUTO_INCREMENT,
		category_id INT(10) NULL,
		project_id INT(10) NULL,
		media_id INT(10) NULL,
		image1_id VARCHAR(10) NULL,
		image2_id VARCHAR(10) NULL,
		image3_id VARCHAR(10) NULL,
		UNIQUE KEY id (id)
	) " . $charset_collate;
	dbDelta($sql);
endif;




// Check that all tables are there
if ($wpdb->get_var("show tables like '$portfolioprojects'") != $portfolioprojects) :
	update_option('portfolio_check', 'There was an error while creating the projects table for Portfolio, please check your database and system settings.');
	return;
endif;

if ($wpdb->get_var("show tables like '$portfoliocategories'") != $portfoliocategories) :
	update_option('portfolio_check', 'There was an error while creating the categories table for Portfolio, please check your database and system settings.');
	return;
endif;

if ($wpdb->get_var("show tables like '$portfoliomedia'") != $portfoliomedia) :
	update_option('portfolio_check', 'There was an error while creating the categories table for Portfolio, please check your database and system settings.');
	return;
endif;

if ($wpdb->get_var("show tables like '$portfoliorelations'") != $portfoliorelations) :
	update_option('portfolio_check', 'There was an error while creating the categories table for Portfolio, please check your database and system settings.');
	return;
endif;



// Get current upload directory
$upload = wp_upload_dir();

// Set portfolio options
$this->options['version'] = PORTFOLIO_VERSION;
$this->options['db_version'] = PORTFOLIO_DBVERSION;
$this->options['debug'] = 0;
$this->options['media_dir'] = $upload['basedir'] . '/portfolio';
$this->options['media_url'] = $upload['baseurl'] . '/portfolio';
$this->options['thumb1_width'] = '300';
$this->options['thumb2_width'] = '150';
$this->options['thumb1_square'] = '0';
$this->options['thumb2_square'] = '1';

update_option('portfolio_options', $this->options);


// Create the upload directory for Portfolio
if (!is_dir($this->options['media_dir'])) :
	mkdir($this->options['media_dir']);
endif;

?>