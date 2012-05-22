<?php

define('WP_ADMIN', true);
define('WP_PATH', $_POST['abspath']);

require_once(WP_PATH . '/wp-load.php');
require_once(WP_PATH . '/wp-admin/includes/admin.php');

@header('Content-Type: text/html; charset=' . get_option('blog_charset'));

do_action('admin_init');

global $wpdb;

$wpdb->portfolioprojects = $wpdb->prefix . 'portfolio_projects';
$wpdb->portfoliocategories = $wpdb->prefix . 'portfolio_categories';



//if (is_user_logged_in()) :
	
	if (!empty($_POST['portfolio-action']) && $_POST['portfolio-action'] == 'add-category') :
		portfolioAddCategory();
	elseif (!empty($_POST['portfolio-action']) && $_POST['portfolio-action'] == 'add-category-option') :
		portfolioAddCategoryOption();
	elseif (!empty($_POST['portfolio-action']) && $_POST['portfolio-action'] == 'delete-category') :
		portfolioDeleteCategory();
	elseif (!empty($_POST['portfolio-action']) && $_POST['portfolio-action'] == 'save-categories') :
		portfolioSaveCategories();
	elseif (!empty($_POST['portfolio-action']) && $_POST['portfolio-action'] == 'delete-category-project') :
		portfolioDeleteCategoryProject();
	elseif (!empty($_POST['portfolio-action']) && $_POST['portfolio-action'] == 'rename-category') :
		portfolioRenameCategory();
	elseif (!empty($_POST['portfolio-action']) && $_POST['portfolio-action'] == 'delete-project') :
		portfolioDeleteProject();
	elseif (!empty($_POST['portfolio-action']) && $_POST['portfolio-action'] == 'delete-media') :
		portfolioDeleteMedia();
	elseif (!empty($_POST['portfolio-action']) && $_POST['portfolio-action'] == 'get-media-info') :
		portfolioGetMediaInfo();
	elseif (!empty($_POST['portfolio-action']) && $_POST['portfolio-action'] == 'save-media-info') :
		portfolioSaveMediaInfo();
	endif;

//else :
	
//	die('You are not logged in.');

//endif;



function portfolioAddCategory() {
	
	global $wpdb;
	

	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;


	if (!empty($_POST['portfolio-add-category-name'])) :
		
		$new = $_POST['portfolio-add-category-name'];
		$cat = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->portfoliocategories WHERE name = '$new' LIMIT 1"));
		
		
		if (!empty($cat)) :
			die('category exists');
		else: 
			$wpdb->query("INSERT INTO $wpdb->portfoliocategories (name) VALUES ('" . $new . "')");
			$category = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->portfoliocategories WHERE name = '$new' LIMIT 1"));
			
			$result = '
				<div class="portfolio-category" id="portfolio-category-' . $category[0]->id . '">
					<div class="portfolio-category-name">
						<div class="portfolio-category-toggle"><br /></div>
						<div class="portfolio-category-meta">
							<div class="portfolio-category-rename">rename</div>
							<div class="portfolio-category-delete">delete</div>
						</div>
						<h3>' . $category[0]->name . '<span><img class="ajax-feedback" src="images/wpspin_dark.gif"  alt="" /></span></h3>
					</div>
					<div id="category-inner-' . $category[0]->id . '" class="portfolio-category-inner portfolio-projects-sortables ui-sortable">

					</div>
				</div>';

			echo $result;
			
		endif;
		
	endif;
}



function portfolioAddCategoryOption() {
	
	global $wpdb;
	

	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;


	if (!empty($_POST['portfolio-category-name'])) :
		
		$new = $_POST['portfolio-category-name'];
		$cat = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->portfoliocategories WHERE name = '$new' LIMIT 1"));
		
		
		if (!empty($cat)) :
			die('category exists');
		else: 
			$wpdb->query("INSERT INTO $wpdb->portfoliocategories (name) VALUES ('" . $new . "')");
			$category = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->portfoliocategories WHERE name = '$new' LIMIT 1"));

			echo $category[0]->id;
			
		endif;
		
	endif;
}



function portfolioDeleteCategory() {
	
	global $wpdb;
	

	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;


	if (!empty($_POST['portfolio-category-id'])) :
		
		$id = $_POST['portfolio-category-id'];
		$projects = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->portfoliorelations WHERE category_id = $id AND project_id IS NOT NULL LIMIT 1"));
	
		if (!empty($projects)) :
			die('not empty');
		else: 
			$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->portfoliocategories WHERE id = $id"));
			echo 'success';
		endif;
		
	endif;
}



function portfolioSaveCategories() {
	
	global $wpdb;
	

	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;


	foreach ($_POST['category'] as $id => $project_ids) :
		
		// Delete all existing category-project relations for this category
		$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->portfoliorelations WHERE category_id = $id AND project_id IS NOT NULL"));
		
		if (!empty($project_ids)) :
			$projects = array();
			$project_ids = array_unique(explode(',', $project_ids));
			foreach ($project_ids as $p) :
				$projects[] = "('" . $id . "', '" . $p . "')";
			endforeach;
			$projects = implode(',', $projects);
		
			// Write new category-project relations for this category
			$wpdb->query($wpdb->prepare("INSERT INTO $wpdb->portfoliorelations (category_id, project_id) VALUES $projects"));
		endif;
	
	endforeach;
}



function portfolioDeleteCategoryProject() {
	
	global $wpdb;
	

	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;


	if (!empty($_POST['portfolio-category-id']) && !empty($_POST['portfolio-project-id'])) :
	
		$cat_id = $_POST['portfolio-category-id'];
		$proj_id = $_POST['portfolio-project-id'];
	
		// Delete the category-project relation
		$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->portfoliorelations WHERE category_id = $cat_id AND project_id = $proj_id"));
		
	endif;

}



function portfolioRenameCategory() {
	
	global $wpdb;
	

	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;


	if (!empty($_POST['portfolio-category-id']) && !empty($_POST['portfolio-category-new-name'])) :
	
		$id = $_POST['portfolio-category-id'];
		$new_name = $_POST['portfolio-category-new-name'];
		
		// Check if a category with the desired new name already exists
		$name_exists = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->portfoliocategories WHERE name = '$new_name' LIMIT 1"));

		if (!empty($name_exists)) :
			die('duplicate name');
		else :
			$wpdb->update($wpdb->portfoliocategories, array('name' => $new_name), array('id' => $id));
		endif;
		
	endif;

}



function portfolioDeleteProject() {
	
	global $wpdb;
	

	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;


	if (!empty($_POST['portfolio-project-id'])) :

		$project_id = $_POST['portfolio-project-id'];
	
		// Delete the project
		$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->portfolioprojects WHERE id = $project_id"));
	
		// Delete the project-category relations
		$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->portfoliorelations WHERE project_id = $project_id"));
		
	endif;

}



function portfolioDeleteMedia() {
	
	global $wpdb, $portfolio;
	

	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;


	if (!empty($_POST['portfolio-media-id'])) :
	
		$media_id = $_POST['portfolio-media-id'];
		$media_dir = $portfolio->options['media_dir'];

		// Load the media entry
		$file = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->portfoliomedia WHERE id = $media_id"));

		// Delete files
		@unlink($media_dir . '/' . $file->filename);
		@unlink($media_dir . '/' . $file->thumb1_filename);
		@unlink($media_dir . '/' . $file->thumb2_filename);
		
		// Delete the file
		$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->portfoliomedia WHERE id = $media_id"));
	
		// Delete all relations to the file
		$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->portfoliorelations WHERE media_id = $media_id"));
		
		// If media file was an image, check if any projects have it set as main image and if yes, remove the reference
		if ($file->type == 'image') :
			$wpdb->query($wpdb->prepare("UPDATE {$wpdb->portfolioprojects} SET main_image = %s WHERE main_image = %d", NULL, $file->id));
		endif;
				
	endif;

}



function portfolioGetMediaInfo() {
	
	global $wpdb, $portfolio;
	

	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;


	if (!empty($_POST['portfolio-media-id'])) :
	
		include_once(dirname(__FILE__) . '/helpers.php');

		// get media file entry
		$id = $_POST['portfolio-media-id'];
		$media = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->portfoliomedia WHERE id = $id"));
		$all_projects = $wpdb->get_results($wpdb->prepare("SELECT id, name FROM $wpdb->portfolioprojects"));
		$related_projects = $wpdb->get_results($wpdb->prepare("SELECT $wpdb->portfolioprojects.id, $wpdb->portfolioprojects.name FROM $wpdb->portfolioprojects INNER JOIN $wpdb->portfoliorelations ON ($wpdb->portfolioprojects.id = $wpdb->portfoliorelations.project_id) WHERE $wpdb->portfoliorelations.media_id = $id ORDER BY $wpdb->portfolioprojects.name"));

?>

				<div class="portfolio-media-edit-small">

					<form method="post" class="portfolio-media-editform">

						<div class="media-single">
							<div class="media-item" id="media-item-<?php echo $media->id ?>">


								<input type="hidden" class="media_mode" name="media_mode" value="edit" />
								<input type="hidden" class="media_id" name="media_id" value="<?php echo $media->id ?>" />

								<table class="form-table describe">

									<thead id="media-head-<?php echo $media->id ?>" class="media-item-info">
										<tr>
											<td id="thumbnail-head-<?php echo $media->id ?>" class="A1B1" rowspan="6">
												<?php
													if ($media->type == 'image') :
														if (!empty($media->thumb2_filename)) :
															echo '<img class="thumbnail" src="' . $portfolio->options["media_url"] . '/' . $media->thumb2_filename . '" alt="Image thumbnail" title="" />';
														else :
															echo '<img class="thumbnail" src="' . $portfolio->options["media_url"] . '/' . $media->filename . '" alt="Image thumbnail" title="" />';
														endif;
													else: 
														echo '<div class="no-preview-available">No preview<br />available</div>';
													endif;										
												?>
											</td>
											<td><strong>File name: </strong><?php echo $media->filename; ?></td>
										</tr>

										<tr>
											<td><strong>File size: </strong><?php echo formatBytes(filesize($portfolio->options['media_dir'] . '/' . $media->filename)); ?></td>
										</tr>

										<tr>
											<td><strong>Type: </strong><?php echo $media->mimetype; ?></td>
										</tr>

		<?php if ($media->type == 'image') : ?>
										<tr>
											<td><strong>Dimensions: </strong><?php echo $media->width . ' x ' . $media->height . ' px'; ?></td>
										</tr>
		<?php endif; ?>

										<tr>
											<td><strong>Uploaded: </strong><?php echo date(get_option('date_format'), $media->uploaded); ?></td>
											<td style="padding-bottom:50px"></td>
										</tr>
									</thead>

									<tbody>
										<tr>
											<th>
												<label for="media_name">
													<span class="alignleft">
														Name
													</span>
													<span class="alignright">
														<abbr class="required" title="required">*</abbr>
													</span>
												</label>
											</th>
											<td>
												<input class="regular-text" type="text" name="media_name" id="media_name" value="<?php echo (!empty($_POST['media_name'])) ? stripslashes($_POST['media_name']) : stripslashes($media->name); ?>" />
											</td>
										</tr>

				<?php if ($media->type == 'image') : ?>						
										<tr>
											<th>
												<label for="media_alt">
													<span class="alignleft">
														Alternative text
													</span>
												</label>
											</th>
											<td>
												<input class="regular-text" type="text" name="media_alt" id="media_alt" value="<?php echo (!empty($_POST['media_alt'])) ? stripslashes($_POST['media_alt']) : stripslashes($media->alt); ?>" />
												<p class="help">Alternative text for images</p>
											</td>
										</tr>
				<?php endif; ?>

										<tr>
											<th>
												<label for="media_description">
													<span class="alignleft">
														Description
													</span>
												</label>
											</th>
											<td>
												<textarea name="media_description" id="media_description"><?php echo (!empty($_POST['media_description'])) ? stripslashes($_POST['media_description']) : stripslashes($media->description); ?></textarea>
											</td>
										</tr>

										<tr>
											<th>
												<label for="media_file_url">
													<span class="alignleft">
														Attach to project
													</span>
												</label>
											</th>
											<td>
												<input class="media_id" type="hidden" value="<?php echo $media->id; ?>" />
												<select name="media_projects" class="media_projects">
													<option value="0">Select project</option>
											<?php
												foreach ($all_projects as $p) :
													echo '<option value="' . $p->id . '">' . $p->name . '</option>';
												endforeach;
											?>
												</select>
												<div class="clear"></div>

											<?php
												foreach ($related_projects as $p) :
													echo '
												<div id="media-' . $media->id . '-attached-project-' . $p->id . '" class="media-attached-project">
													<input type="hidden" name="media_attached_projects[]" value="' . $p->id . '" />
													' . $p->name . '
													<a href="#" class="media-attached-remove">remove</a>
												</div>';
												endforeach;									
											?>	
											</td>
										</tr>

									</tbody>
								</table>


								<div class="clear" style="padding-top:20px"></div>

							    <p class="submit">
									<input type="submit" class="button-primary media_update" name="media_update" value="Save changes" />
									<div class="media_update_feedback">
										<img src="images/wpspin_light.gif" alt="" />
										<div></div>
									</div>
							    </p>

							</div>
						</div>

					</form>

				</div>
<?php
	
	endif;

}



function portfolioSaveMediaInfo() {
	
	global $wpdb, $portfolio;
	

	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;


	if (!empty($_POST['portfolio-media-id'])) :
	
		$content = json_decode(stripslashes($_POST['portfolio-media-content']), true);
		
		$id = $_POST['portfolio-media-id'];
		$name = '';
		$alt = '';
		$description = '';
		$projects = array();
		
		foreach ($content as $c) :
			if ($c['name'] == 'media_name') $name = $c['value'];
			if ($c['name'] == 'media_alt') $alt = $c['value'];
			if ($c['name'] == 'media_description') $description = $c['value'];
			if ($c['name'] == 'media_attached_projects[]') $projects[] = $c['value'];
		endforeach;
		

		// name must be set
		if (empty($name)) :
			echo '\'Name\' is a required field.';
			return;
		endif;
		
		
		// if no errors were found, update the media file
		$lastmodified = time();
		
		// save changes
		if ($wpdb->query($wpdb->prepare("UPDATE {$wpdb->portfoliomedia} SET name = %s, alt = %s, description = %s, lastmodified = %d WHERE id = %d", $name, $alt, $description, $lastmodified, $id)) !== false) :
		
			// delete old image relations
			$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->portfoliorelations WHERE media_id = $id AND project_id IS NOT NULL"));
		
			// save new image - project relations
			$relations = array();
			foreach ($projects as $project_id) :
				$relations[] = "('" . $project_id . "', '" . $id . "')";
			endforeach;
			$relations = implode(',', $relations);
						
			if (!empty($relations)) :

				if ($wpdb->query($wpdb->prepare("INSERT INTO $wpdb->portfoliorelations (project_id, media_id) VALUES $relations")) !== false) :

					// if everything went successful
					echo 'success';
					return;
					
				else :
				
					echo 'An unknown error occured when trying to save the changes.';
					return;
				
				endif;
				
			else :

				echo 'success';
				return;
			
			endif;

		endif;
		
		echo 'An unknown error occured when trying to save the changes.';
		return;
		
	endif;

}



?>