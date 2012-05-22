<?php  

// Stop direct calls to this file
if (!isset($this)) die('You cannot access this file directly.');


global $wpdb, $portfolio;


if ($portfolio->options['debug']) :
	$wpdb->show_errors();
else :
	$wpdb->hide_errors();
endif;


$cat = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->portfoliocategories"));
$categories = portfolioCategoriesOutput($cat);

echo '
	<div class="wrap">
		<h2>Categories <div id="portfolio-categories-add-new" class="button add-new-h2">Add New</div></h2>
		
		<form action="" id="portfolio-categories-add">
			<fieldset>
				<div>
					<input type="hidden" name="portfolio-action" value="add-category" />
					<input type="hidden" name="abspath" value="' . ABSPATH . '" />
				</div>
				<div>
					<input type="text" name="portfolio-add-category-name" value="" />
				</div>
				<div>
					<input type="submit" class="button" name="portfolio-add-category-submit" value="Add New" />
				</div>
				<div>
					<img src="images/wpspin_light.gif" alt="" />
				</div>
				<div id="portfolio-categories-add-response"></div>
			</fieldset>
		</form>';
		
if (empty($categories))	:
	echo '
		<p>No categories yet. Click <em>Add New</em> to add a new category.</p>';
else :
	echo '
		<p>Assign projects to different categories by <em>dragging</em> them into a category box.<br /><em>Double-click</em> a project to duplicate it, in case you want to add it to more than one category.</p>';
endif;
		
echo '
		<div id="portfolio-categories">
			' . $categories . '
		</div>';
	

		
echo '		
	</div>';




function portfolioCategoriesOutput($categories) {
	
	global $wpdb, $portfolio;
	

	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;


	$result = '';
	
	foreach ($categories as $category) :
		$projects = $wpdb->get_results($wpdb->prepare("SELECT $wpdb->portfolioprojects.id, $wpdb->portfolioprojects.name, $wpdb->portfoliorelations.id AS rel_id FROM $wpdb->portfolioprojects INNER JOIN $wpdb->portfoliorelations ON ($wpdb->portfolioprojects.id = $wpdb->portfoliorelations.project_id) WHERE $wpdb->portfoliorelations.category_id = $category->id ORDER BY $wpdb->portfoliorelations.id"));
	
		$result .= '
			<div class="portfolio-category" id="portfolio-category-' . $category->id . '">
				<input type="hidden" class="portfolio-category-id" value="' . $category->id . '" />
				<div class="portfolio-category-name">
					<div class="portfolio-category-toggle"><br /></div>
					<div class="portfolio-category-meta">
						<div class="portfolio-category-rename">rename</div>
						<div class="portfolio-category-delete">delete</div>
					</div>
					<h3>' . $category->name . '<span><img class="ajax-feedback" src="images/wpspin_dark.gif"  alt="" /></span></h3>
				</div>
				<div id="category-inner-' . $category->id . '" class="portfolio-category-inner portfolio-projects-sortables ui-sortable">';
				
		foreach ($projects as $project)	:
			$result .= '
					<div id="portfolio-project-' . $project->id . '" class="portfolio-project ui-draggable">
						<input type="hidden" class="portfolio-project-id" value="' . $project->id . '" />
						<div class="portfolio-project-top">
							<div class="portfolio-project-title">
								<span class="portfolio-project-delete">delete</span>
								<h4>' . $project->name . '</h4>
							</div>
						</div>
					</div>';
		endforeach;
				
		$result .= '
				</div>
			</div>';
	endforeach;
	
	return $result;
}


?>