<?php 

class PortfolioAdmin {
	
	public function PortfolioAdmin() {

		// Build the admin menu
		add_action('admin_menu', array(&$this, 'portfolioAdminMenu'));
		
		// Load scripts depending on page
		add_action('admin_print_scripts', array(&$this, 'portfolioAdminScripts'));
		
		// load admin CSS
		add_action('admin_print_styles', array(&$this, 'portfolioAdminStyles'));
		
		// print global script variables in the header section
		add_action('admin_print_scripts', array(&$this, 'portfolioPrintVars'), 1);
		
		// include thickbox functions
		include_once(dirname(__FILE__) . '/includes/thickbox.php');
	}


	// Add the admin menu pages to Wordpress backend
	public function portfolioAdminMenu()  {
	
		add_menu_page('Portfolio', 'Portfolio', 'edit_posts', PORTFOLIO_DIR, array(&$this, 'portfolioAdminPages'));
	    add_submenu_page(PORTFOLIO_DIR, 'Projects', 'Projects', 'edit_posts', 'portfolio', array(&$this, 'portfolioAdminPages'));
	    add_submenu_page(PORTFOLIO_DIR, 'Categories', 'Categories', 'edit_posts', 'portfolio-categories', array(&$this, 'portfolioAdminPages'));
		add_submenu_page(PORTFOLIO_DIR, 'Media', 'Media', 'edit_posts', 'portfolio-media', array(&$this, 'portfolioAdminPages'));
		add_submenu_page(PORTFOLIO_DIR, 'Settings', 'Settings', 'edit_posts', 'portfolio-settings', array(&$this, 'portfolioAdminPages'));
	    add_submenu_page(PORTFOLIO_DIR, 'About', 'About', 'edit_posts', 'portfolio-about', array(&$this, 'portfolioAdminPages'));		
	}
	
	
	// Display the admin menu pages depending on what page was requested
	public function portfolioAdminPages()  {
		
  		switch ($_GET['page']) :
			case 'portfolio-categories' :
				include_once(dirname(__FILE__) . '/categories.php');
				break;
			case 'portfolio-media' :
				include_once(dirname(__FILE__) . '/media.php');
				break;
			case 'portfolio-settings' :
				include_once(dirname(__FILE__) . '/settings.php');
				break;
			case 'portfolio-about' :
				include_once(dirname(__FILE__) . '/about.php');
				break;
			case 'portfolio' :
			default :
				include_once(dirname(__FILE__) . '/projects.php');
				break;
		endswitch;
	}
	
	
	// Load script files depending on requested page
	public function portfolioAdminScripts() {

		if (empty($_GET['page'])) return;

  		switch ($_GET['page']) :
			case 'portfolio-categories' :
				wp_enqueue_script('jquery-ui-core');
				wp_enqueue_script('jquery-ui-sortable');
				wp_enqueue_script('jquery-ui-draggable');
				wp_enqueue_script('jquery-ui-droppable');
				wp_enqueue_script('portfolio-categories', PORTFOLIO_URLPATH .'/admin/js/categories.js', false, PORTFOLIO_VERSION);
				break;
			case 'portfolio-media' :
				wp_enqueue_script('swfobject');
				wp_enqueue_script('portfolio-upload', PORTFOLIO_URLPATH .'/admin/js/jquery.uploadify.min.js', false, PORTFOLIO_VERSION);
				wp_enqueue_script('portfolio-json', PORTFOLIO_URLPATH .'/admin/js/json.js', false, PORTFOLIO_VERSION);
				wp_enqueue_script('portfolio-media', PORTFOLIO_URLPATH .'/admin/js/media.js', false, PORTFOLIO_VERSION);
				break;
			case 'portfolio-settings' :
				break;
			case 'portfolio-about' :
				break;
			case 'portfolio' :
			default :
				add_thickbox();
				wp_enqueue_script('media-upload');
				wp_enqueue_script('post');
				wp_enqueue_script('word-count');
				if (user_can_richedit()) wp_enqueue_script('editor');
				if (function_exists('wp_tiny_mce')) wp_tiny_mce();
				wp_enqueue_script('portfolio-projects', PORTFOLIO_URLPATH .'/admin/js/projects.js', false, PORTFOLIO_VERSION);
				break;
		endswitch;
	}

	
	// Load CSS depending on requested page
	public function portfolioAdminStyles() {

		if (empty($_GET['page'])) return;

  		switch ($_GET['page']) :
			case 'portfolio-categories' :
				wp_enqueue_style('widgets');
				wp_enqueue_style('portfolio', PORTFOLIO_URLPATH .'/admin/css/styles.css', false, PORTFOLIO_VERSION, 'all');
				break;
			case 'portfolio-media' :
				wp_enqueue_style('portfolio', PORTFOLIO_URLPATH .'/admin/css/styles.css', false, PORTFOLIO_VERSION, 'all');
				break;
			case 'portfolio-settings' :
				wp_enqueue_style('portfolio', PORTFOLIO_URLPATH .'/admin/css/styles.css', false, PORTFOLIO_VERSION, 'all');
				break;
			case 'portfolio-about' :
				wp_enqueue_style('portfolio', PORTFOLIO_URLPATH .'/admin/css/styles.css', false, PORTFOLIO_VERSION, 'all');
				break;
			case 'portfolio' :
			default :
				wp_enqueue_style('portfolio', PORTFOLIO_URLPATH .'/admin/css/styles.css', false, PORTFOLIO_VERSION, 'all');
				wp_enqueue_style('thickbox');
				break;
		endswitch;
	}
	
	
	// Print global script variables in the header section
	public function portfolioPrintVars() {
		
		global $portfolio;
	
		echo '
<script type="text/javascript">
	var portfolio_ajax = \'' . PORTFOLIO_AJAX . '\';
	var portfolio_dir =  \'' . PORTFOLIO_ABSPATH . '\';
	var portfolio_url =  \'' . PORTFOLIO_URLPATH . '\';
	var portfolio_media_dir =  \'' . $portfolio->options['media_dir'] . '\';
	var portfolio_wp =  \'' . ABSPATH . '\';
</script>
';
	}
	
}

?>