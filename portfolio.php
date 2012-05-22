<?php
/*
Plugin Name: Portfolio
Description: Simple but powerful portfolio management
Version: 0.9
Author: Matthias Siegel

Copyright 2010  Matthias Siegel  (email : matthias.siegel@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



if (!class_exists('Portfolio')) :
	class Portfolio {

		// Constructor
		public function Portfolio() {
			
			// Set up the environment
			$this->defineConstants();
			$this->loadOptions();
			$this->defineTables();
			
			// Register plugin activation hook, installs tables and options on first activate
			register_activation_hook(__FILE__, array(&$this, 'activate'));

			// Register uninstall hook, which will remove all tables and options
			register_uninstall_hook(__FILE__, 'uninstall');

			// Start this plugin once all other plugins are fully loaded
			add_action('plugins_loaded', array(&$this, 'start'));

		}
		
		
		// Defines a few static helper values
		protected function defineConstants() {

			define('PORTFOLIO_VERSION', '0.9');
			define('PORTFOLIO_DBVERSION', '0.9');
			define('PORTFOLIO_HOME', 'https://github.com/matthiassiegel/portfolio');
			define('PORTFOLIO_FILE', __FILE__);
			define('PORTFOLIO_DIR', plugin_basename(dirname(__FILE__)));
			define('PORTFOLIO_ABSPATH', dirname(__FILE__));
			define('PORTFOLIO_URLPATH', WP_PLUGIN_URL . '/portfolio');
			define('PORTFOLIO_AJAX', PORTFOLIO_URLPATH . '/admin/includes/ajax.php');
		}
	
			
		// Sets pointers to Portfolio's database tables
		protected function defineTables() {
			
			global $wpdb;

			$wpdb->portfolioprojects = $wpdb->prefix . 'portfolio_projects';
			$wpdb->portfoliocategories = $wpdb->prefix . 'portfolio_categories';
			$wpdb->portfoliomedia = $wpdb->prefix . 'portfolio_media';
			$wpdb->portfoliorelations = $wpdb->prefix . 'portfolio_relations';
		}


		// Load the Portfolio options from the Wordpress options table
		protected function loadOptions() {

			$this->options = get_option('portfolio_options');
		}
		
		
		// Main function
		public function start() {
			
			// If we're in the admin section, load the admin panels
			if (is_admin()) :
				
				require_once(dirname(__FILE__) . '/admin/admin.php');
				$this->admin = new PortfolioAdmin();
				
			endif;
		}


		// On first activation, this installs all tables and options. Otherwise does incremental setup.
		public function activate() {

			require_once(dirname(__FILE__) . '/install.php');
		}

		










		//-----------------------------------------------------------------------
		//
		// The public API functions
		//
		//-----------------------------------------------------------------------
		
		
		// Get single project
		public function getProject($project = null, $object = true) {

			global $wpdb;
			

			if (is_numeric($project)) :
				// Treat as ID
				$p = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->portfolioprojects WHERE id = $project"));
			else :
				// Treat as project name
				$p = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->portfolioprojects WHERE name = '" . $project . "' LIMIT 1"));
			endif;
			
			$project = array();
			if (!empty($p)) :
				$project['id'] = $p->id;
				$project['name'] = $p->name;
				$project['shortdesc'] = $p->shortdesc;
				$project['longdesc'] = $p->longdesc;
				$project['url'] = $p->url;
				$project['date'] = $p->date;
				$project['management'] = $p->management;
				$project['status'] = $p->status;
				$project['lastmodified'] = $p->lastmodified;
				$project['added'] = $p->added;
				
				// get categories
				$c = $wpdb->get_results($wpdb->prepare("SELECT category_id FROM $wpdb->portfoliorelations WHERE project_id = $p->id and category_id IS NOT NULL"));
				
				$project['categories'] = array();
				foreach ($c as $category) :
					$project['categories'][] = $category->category_id;
				endforeach;
				

				$project['media'] = array();

				// get image 1
				$image1 = $wpdb->get_var($wpdb->prepare("SELECT $wpdb->portfoliorelations.image1_id FROM $wpdb->portfoliorelations WHERE $wpdb->portfoliorelations.project_id = $p->id and $wpdb->portfoliorelations.image1_id IS NOT NULL"));				
				$project['media']['image1'] = empty($image1) ? NULL : $this->getMedia($image1);

				// get image 2
				$image2 = $wpdb->get_var($wpdb->prepare("SELECT $wpdb->portfoliorelations.image2_id FROM $wpdb->portfoliorelations WHERE $wpdb->portfoliorelations.project_id = $p->id and $wpdb->portfoliorelations.image2_id IS NOT NULL"));				
				$project['media']['image2'] = empty($image2) ? NULL : $this->getMedia($image2);

				// get image 3
				$image3 = $wpdb->get_var($wpdb->prepare("SELECT $wpdb->portfoliorelations.image3_id FROM $wpdb->portfoliorelations WHERE $wpdb->portfoliorelations.project_id = $p->id and $wpdb->portfoliorelations.image3_id IS NOT NULL"));				
				$project['media']['image3'] = empty($image3) ? NULL : $this->getMedia($image3);

				
				// get additional images
				$i = $wpdb->get_results($wpdb->prepare("SELECT $wpdb->portfoliorelations.media_id FROM $wpdb->portfoliorelations INNER JOIN $wpdb->portfoliomedia ON ($wpdb->portfoliorelations.media_id = $wpdb->portfoliomedia.id) WHERE $wpdb->portfoliorelations.project_id = $p->id and $wpdb->portfoliorelations.media_id IS NOT NULL AND $wpdb->portfoliomedia.type = 'image'"));
				
				$project['media']['additional_images'] = array();
				foreach ($i as $image) :
					$project['media']['additional_images'][] = $this->getMedia($image->media_id);
				endforeach;
				
				// get other files
				$f = $wpdb->get_results($wpdb->prepare("SELECT $wpdb->portfoliorelations.media_id FROM $wpdb->portfoliorelations INNER JOIN $wpdb->portfoliomedia ON ($wpdb->portfoliorelations.media_id = $wpdb->portfoliomedia.id) WHERE $wpdb->portfoliorelations.project_id = $p->id and $wpdb->portfoliorelations.media_id IS NOT NULL AND $wpdb->portfoliomedia.type = 'other'"));
				
				$project['media']['other_files'] = array();
				foreach ($f as $file) :
					$project['media']['other_files'][] = $this->getMedia($image->media_id);
				endforeach;
			endif;
			
			
			if ($object) :
				return makeObject($project);
			else :
				return $project;
			endif;
		}


		// Get all projects
		public function getProjects($status = 'published', $object = true) {

			global $wpdb;
			

			switch ($status) :
				case 'archived' :
					$p = $wpdb->get_results("SELECT id, name FROM $wpdb->portfolioprojects WHERE status = 'archived' ORDER BY name");
					break;
				case 'draft' :
					$p = $wpdb->get_results("SELECT id, name FROM $wpdb->portfolioprojects WHERE status = 'draft' ORDER BY name");
					break;
				case 'all' :
					$p = $wpdb->get_results("SELECT id, name FROM $wpdb->portfolioprojects ORDER BY name");
					break;
				case 'published':
				default :
					$p = $wpdb->get_results("SELECT id, name FROM $wpdb->portfolioprojects WHERE status = 'published' ORDER BY name");
					break;
			endswitch;

			
			$projects = array();
			foreach ($p as $project) :			
				$projects[] = $this->getProject($project->id, $object);
			endforeach;
			
			
			if ($object) :
				return makeObject($projects);
			else :
				return $projects;
			endif;
		}

		
		// Get category
		public function getCategory($category = null, $object = true) {

			global $wpdb;
			
			
			if (is_numeric($category)) :
				// Treat as ID
				$c = $wpdb->get_row("SELECT * FROM $wpdb->portfoliocategories WHERE id = $category");
			else :
				// Treat as category name
				$c = $wpdb->get_row("SELECT * FROM $wpdb->portfoliocategories WHERE name = '" . $category . "'");
			endif;
			
			$category = array();
			if (!empty($c)) :
				$category['id'] = $c->id;
				$category['name'] = $c->name;
				
				$temp_projects = array();
				$p = $wpdb->get_results($wpdb->prepare("SELECT project_id FROM $wpdb->portfoliorelations WHERE category_id = $c->id and project_id IS NOT NULL"));
				
				foreach ($p as $project) :
					$temp_projects[] = $project->project_id;
				endforeach;
				
				$category['projects'] = $temp_projects;
			endif;
			
			
			if ($object) :
				return makeObject($category);
			else :
				return $category;
			endif;
		}
		

		// Get all categories and the associated projects
		public function getCategories($object = true) {

			global $wpdb;
			
			
			$c = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->portfoliocategories"));
			$categories = array();
			
			foreach ($c as $category) :
				$temp = array();
				$temp['id'] = $category->id;
				$temp['name'] = $category->name;
				
				$temp_projects = array();
				$p = $wpdb->get_results($wpdb->prepare("SELECT project_id FROM $wpdb->portfoliorelations WHERE category_id = $category->id and project_id IS NOT NULL"));
				
				foreach ($p as $project) :
					$temp_projects[] = $project->project_id;
				endforeach;
				
				$temp['projects'] = $temp_projects;
				
				$categories[] = $temp;
			endforeach;
			
			
			if ($object) :
				return makeObject($categories);
			else :
				return $categories;
			endif;
		}


		// Get media file entry
		public function getMedia($id = null, $object = true) {

			global $wpdb;

			$m = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->portfoliomedia WHERE id = $id LIMIT 1"));
			$media = array();
			
			$media['id'] = $m->id;
			$media['filename'] = $m->filename;
			$media['link'] = $this->options['media_url'] . '/' . $m->filename;
			$media['name'] = $m->name;
			$media['type'] = $m->type;
			$media['alt'] = $m->alt;
			$media['description'] = $m->description;
			$media['width'] = $m->width;
			$media['height'] = $m->height;
			$media['mimetype'] = $m->mimetype;
			$media['uploaded'] = $m->uploaded;
			$media['lastmodified'] = $m->lastmodified;
			$media['thumb1_filename'] = $m->thumb1_filename;
			$media['thumb1_link'] = (!empty($m->thumb1_filename)) ? $this->options['media_url'] . '/' . $m->thumb1_filename : null;
			$media['thumb1_width'] = $m->thumb1_width;
			$media['thumb1_height'] = $m->thumb1_height;
			$media['thumb2_filename'] = $m->thumb2_filename;
			$media['thumb2_link'] = (!empty($m->thumb2_filename)) ? $this->options['media_url'] . '/' . $m->thumb2_filename : null;
			$media['thumb2_width'] = $m->thumb2_width;
			$media['thumb2_height'] = $m->thumb2_height;

			if ($object) :
				return makeObject($media);
			else :
				return $media;
			endif;
		}
	}
	
	
	
	
	// Include some helper functions
	include(dirname(__FILE__) . '/admin/includes/helpers.php');
	
	
	
	
	
	
	//----------------------
	//
	// Plugin uninstall function. Must not be part of the class.
	//
	//----------------------
	
	// Removes everything, tables, options, media files
	function uninstall() {

        include_once(dirname(__FILE__) . '/uninstall.php');
	}
	
	
	
	
endif;



// Start the plugin

global $portfolio;

$portfolio = new Portfolio();


?>