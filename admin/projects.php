<?php

// Stop direct calls to this file
if (!isset($this)) die('You cannot access this file directly.');


include_once (dirname (__FILE__) . '/includes/pagination.php');


// If bulk action was submitted and an action was selected
if ((isset($_POST['doaction']) && $_POST['action'] != '0') || (isset($_POST['doaction2']) && $_POST['action2'] != '0')) :
	// If some projects were actually selected
	if (isset($_POST['projects'])) :
		portfolioProjectsActions();
	else :
		$message = '
			<div id="message" class="error">
				<p>
					No projects selected.
				</p>
			</div>';
		portfolioProjects('all', $message);
	endif;
// If bulk action was submitted without selecting an option
elseif ((isset($_POST['doaction']) && $_POST['action'] == '0') || (isset($_POST['doaction2']) && $_POST['action2'] == '0'))	:
	$message = '
		<div id="message" class="error">
			<p>
				No action selected.
			</p>
		</div>';
	portfolioProjects('all', $message);
endif;


// If a specific status is requested and it's not returning from a bulk action (prevents double listing)
if (isset($_GET['status']) && !isset($_GET['action'])) :
	switch ($_GET['status']) :
		case 'published' :
			portfolioProjects('published');
			break;
		case 'draft' :
			portfolioProjects('draft');
			break;
		case 'archived' :
			portfolioProjects('archived');
			break;
		case 'all' :
		default:
			portfolioProjects('all');
			break;
	endswitch;
elseif (isset($_GET['action']) && $_GET['action'] == 'new' && !empty($_POST['projects_submit'])) :
	portfolioProjectsSave();
elseif (isset($_GET['action']) && $_GET['action'] == 'new') :
	portfolioProjectsNew();
elseif (isset($_GET['action']) && $_GET['action'] == 'edit' && !empty($_POST['projects_update'])) :
	portfolioProjectsUpdate();
elseif (isset($_GET['action']) && $_GET['action'] == 'edit') :
	portfolioProjectsEdit();
elseif (empty($_POST)) :
	portfolioProjects('all');
endif;



// Main function, displays the listings
function portfolioProjects($status = 'all', $message = false) {
	
	global $wpdb, $portfolio;
	

	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;

		
	echo '
		<div class="wrap">
			<h2>Projects <a href="?page=portfolio&amp;action=new" class="button add-new-h2">Add New</a></h2>';

	echo $message;
	
	// Load all projects from the projects table
	$projects = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->portfolioprojects ORDER BY name"));
	
	if (count($projects)) :

		echo '
			<form id="projects-filter" method="post">
				<input type="hidden" name="page" value="portfolio" />';
			
		$draft = 0;
		$published = 0;
		$archived = 0;
		$draft_projects = array();
		$published_projects = array();
		$archived_projects = array();
		
		foreach ($projects as $project) :
			switch ($project->status) :
				case 'draft' :
					$draft_projects[] = $project;
					$draft++;
					break;
				case 'published' :
					$published_projects[] = $project;
					$published++;
					break;
				case 'archived' :
					$archived_projects[] = $project;
					$archived++;
					break;
			endswitch;
		endforeach;

		
		// If no page was specified, display the first one
		if (empty($_GET['p'])) :
			$page = 1;
		else :
			$page = $_GET['p'];
		endif;
		
		$all_projects = $projects;


		// Continue with only the projects for the current page
		// Get the pagination HTML
		switch ($status) :
			case 'draft' :
				$projects = portfolioPaginationItems($draft_projects, $page);
				$pagination = portfolioPaginationHTML($draft_projects, $page, '?page=portfolio&amp;status=draft&amp;');
				break;
			case 'published' :
				$projects = portfolioPaginationItems($published_projects, $page);
				$pagination = portfolioPaginationHTML($published_projects, $page, '?page=portfolio&amp;status=published&amp;');
				break;
			case 'archived' :
				$projects = portfolioPaginationItems($archived_projects, $page);
				$pagination = portfolioPaginationHTML($archived_projects, $page, '?page=portfolio&amp;status=archived&amp;');
				break;
			case 'all' :
			default :
				$projects = portfolioPaginationItems($all_projects, $page);
				$pagination = portfolioPaginationHTML($all_projects, $page, '?page=portfolio&amp;');
		endswitch;	

		


		echo '
				<ul class="subsubsub">';
		echo '		
					<li><a ' . ($status == 'all' ? 'class="current" ' : '') . 'href="?page=portfolio">All (<span id="count-all">' . count($all_projects) . '</span>)</a> | </li>';
					
		if ($published > 0)	:		
			echo '	<li><a ' . ($status == 'published' ? 'class="current" ' : '') . 'href="?page=portfolio&amp;status=published">Published (<span id="count-published">' . $published . '</span>)</a> | </li>';
		else :
			echo '<li>Published (<span id="count-published">' . $published . '</span>) | </li>';
		endif;
		
		if ($draft > 0)	:		
			echo '	<li><a ' . ($status == 'draft' ? 'class="current" ' : '') . 'href="?page=portfolio&amp;status=draft">Draft (<span id="count-draft">' . $draft . '</span>)</a> | </li>';
		else :
			echo '<li>Draft (<span id="count-draft">' . $draft . '</span>) | </li>';
		endif;
		
		if ($archived > 0) :		
			echo '	<li><a ' . ($status == 'archived' ? 'class="current" ' : '') . 'href="?page=portfolio&amp;status=archived">Archived (<span id="count-archived">' . $archived . '</span>)</a></li>';
		else :
			echo '<li>Archived (<span id="count-archived">' . $archived . '</span>)</li>';
		endif;

		echo '
				</ul>';
				
				
		if ($status == 'all' || ($status == 'published' && $published > 0) || ($status == 'draft' && $draft > 0) || ($status == 'archived' && $archived > 0)) :
			echo '
				<div class="tablenav">
					<div class="alignleft actions">
						<select name="action">
							<option value="0" selected="selected">Bulk Actions</option>
							<option value="publish">Publish</option>
							<option value="archive">Archive</option>
							<option value="draft">Mark as Draft</option>
							<option value="delete">Delete Permanently</option>
						</select>
						<input type="submit" id="doaction" name="doaction" class="button-secondary action" value="Apply" />
					</div>' .
					
					$pagination . '
					
				</div>
				
				<table cellspacing="0" class="widefat post fixed">
					<thead>
						<tr>
							<th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>
							<th class="manage-column" scope="col">Name</th>
							<th class="manage-column" scope="col">Date</th>
							<th class="manage-column" scope="col">Short description</th>
							<th class="manage-column date-column" scope="col">Last modified</th>
						</tr>
					</thead>
			
					<tbody>';
		else :
			echo '
					<div class="clear"></div>
					<p>No projects here at the moment.</p>';
		endif;

		foreach ($projects as $project) :
			if ($status == $project->status || $status == 'all') :
				echo '
						<tr id="project-'. $project->id .'">
							<th class="check-column" scope="row"><input type="checkbox" value="' . $project->id . '" name="projects[]" /></th>
							<td>
								<input type="hidden" id="project-'. $project->id .'-id" value="'. $project->id .'" class="project-id" />
								<input type="hidden" id="project-'. $project->id .'-status" value="'. $project->status .'" class="project-status" />
								<strong>
									<a class="row-title" title="Edit &quot;' . $project->name . '&quot;" href="?page=portfolio&amp;action=edit&amp;project=' . $project->id . '">' . $project->name . '</a>
								</strong>
								<div class="row-actions">
									<span class="edit"><a title="Edit this project" href="?page=portfolio&amp;action=edit&amp;project=' . $project->id . '">Edit</a> | </span>
									<span class="trash"><a title="Delete this project" href="#">Delete</a></span>
								</div>
							</td>
							<td class="date-column">' . $project->date . '</td>
							<td>' . (strlen($project->shortdesc) > 100 ? substr($project->shortdesc, 0, 100) . '...' : $project->shortdesc) . '</td>
							<td class="date-column">' . date(get_option('date_format'), $project->lastmodified) . '<br />' . ucfirst($project->status) . '</td>
						</tr>';
			endif;
		endforeach;
		
		if ($status == 'all' || ($status == 'published' && $published > 0) || ($status == 'draft' && $draft > 0) || ($status == 'archived' && $archived > 0)) :
			echo '
					</tbody>
				
					<tfoot>
						<tr>
							<th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>
							<th class="manage-column" scope="col">Name</th>
							<th class="manage-column" scope="col">Date</th>
							<th class="manage-column" scope="col">Short description</th>
							<th class="manage-column date-column" scope="col">Last modified</th>
						</tr>
					</tfoot>
				</table>
				
				<div class="tablenav">
					<div class="alignleft actions">
						<select name="action2">
							<option value="0" selected="selected">Bulk Actions</option>
							<option value="publish">Publish</option>
							<option value="archive">Archive</option>
							<option value="draft">Mark as Draft</option>
							<option value="delete">Delete Permanently</option>
						</select>
						<input type="submit" id="doaction2" name="doaction2" class="button-secondary action" value="Apply" />
					</div>' .
					
					$pagination . '
					
				</div>';
				
		endif;
		
		echo '</form>';
		
	
	else :
		echo '<div class="clear"></div>';
		echo '<p>No projects yet. Click <em>Add New</em> to add a project.</p>';
	endif;
	
	
	echo '</div>';
	
}


// Handles the bulk actions
function portfolioProjectsActions() {
	
	global $wpdb, $portfolio;
	
	
	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;
	
	
	if (isset($_POST['doaction']) && $_POST['action'] != '0') :
		$action = $_POST['action'];
	elseif (isset($_POST['doaction2']) && $_POST['action2'] != '0')	:
		$action = $_POST['action2'];
	endif;
	
	if (isset($action) && !empty($_POST['projects'])) :
		switch ($action) :
			case 'publish' :
				foreach ($_POST['projects'] as $project) :
					$data = array('status' => 'published');
					$where = array('id' => $project);
					$wpdb->update($wpdb->portfolioprojects, $data, $where);
				endforeach;
				$message = '
					<div id="message" class="updated fade">
						<p>
							<strong>' . count($_POST['projects']) . ' Project' . (count($_POST['projects']) > 1 ? 's' : '') . ' published.</strong>
						</p>
					</div>';
				$_GET['action'] = 'complete';
				if (isset($_GET['status'])) :
					portfolioProjects($_GET['status'], $message);
				else :
					portfolioProjects('all', $message);
				endif;
				return;
				
			case 'archive' :
				foreach ($_POST['projects'] as $project) :
					$data = array('status' => 'archived');
					$where = array('id' => $project);
					$wpdb->update($wpdb->portfolioprojects, $data, $where);
				endforeach;
				$message = '
					<div id="message" class="updated fade">
						<p>
							<strong>' . count($_POST['projects']) . ' Project' . (count($_POST['projects']) > 1 ? 's' : '') . ' archived.</strong>
						</p>
					</div>';
				$_GET['action'] = 'complete';
				if (isset($_GET['status'])) :
					portfolioProjects($_GET['status'], $message);
				else :
					portfolioProjects('all', $message);
				endif;
				return;
				
			case 'draft' :
				foreach ($_POST['projects'] as $project) :
					$data = array('status' => 'draft');
					$where = array('id' => $project);
					$wpdb->update($wpdb->portfolioprojects, $data, $where);
				endforeach;
				$message = '
					<div id="message" class="updated fade">
						<p>
							<strong>' . count($_POST['projects']) . ' Project' . (count($_POST['projects']) > 1 ? 's' : '') . ' marked as draft.</strong>
						</p>
					</div>';
				$_GET['action'] = 'complete';
				if (isset($_GET['status'])) :
					portfolioProjects($_GET['status'], $message);
				else :
					portfolioProjects('all', $message);
				endif;
				return;
				
			case 'delete' :
				foreach ($_POST['projects'] as $project) :
					$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->portfolioprojects WHERE id = $project"));
					$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->portfoliorelations WHERE project_id = $project"));
				endforeach;
				$message = '
					<div id="message" class="updated fade">
						<p>
							<strong>' . count($_POST['projects']) . ' Project' . (count($_POST['projects']) > 1 ? 's' : '') . ' deleted.</strong>
						</p>
					</div>';
				$_GET['action'] = 'complete';
				if (isset($_GET['status'])) :
					portfolioProjects($_GET['status'], $message);
				else :
					portfolioProjects('all', $message);
				endif;
				return;
		endswitch;
	else :
		portfolioProjects('all');
	endif;
}



// Creates a new project
function portfolioProjectsNew($message = false) {
	
	global $wpdb, $portfolio;
		

	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;

		
	// get categories
	$categories = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->portfoliocategories"));

?>
	
		<div class="wrap">
			<h2>Add a new project <a href="?page=portfolio" class="button add-new-h2">Cancel</a></h2>
			
			<?php echo $message; ?>

			<form method="post" class="portfolio-project-editform" id="poststuff">				
				<input type="hidden" id="project_mode" name="project_mode" value="new" />
				
				<h3>General information</h3>
				<table class="form-table">
				
					<tr>
						<th>
							<label for="project_name">
								<span class="alignleft">
									Name
								</span>
								<span class="alignright">
									<abbr class="required" title="required">*</abbr>
								</span>
							</label>
						</th>
						<td>
							<input class="regular-text" type="text" name="project_name" id="project_name" maxlength="250" value="<?php if (!empty($_POST['project_name'])) echo $_POST['project_name']; ?>" />
						</td>
			        </tr>

					<tr>
						<th>URL</th>
						<td>
							<input class="regular-text" type="text" name="project_url" id="project_url" maxlength="250" value="<?php if (!empty($_POST['project_url'])) echo $_POST['project_url']; ?>" />
						</td>
			        </tr>

					<tr>
						<th>
							<label for="project_categories">
								<span class="alignleft">
									Categories
								</span>
								<span class="alignright">
									<abbr class="required" title="required">*</abbr>
								</span>
							</label>
						</th>
						<td>
							<select name="project_categories[]" id="project_categories" multiple="multiple">';
							<?php
								foreach ($categories as $cat) :
									if (!empty($_POST['project_categories'])) :
										if (in_array($cat->id, $_POST['project_categories'])) :
											echo '<option value="' . $cat->id . '" selected="selected">' . $cat->name . '</option>';
										else :
											echo '<option value="' . $cat->id . '">' . $cat->name . '</option>';
										endif;
									else :
										echo '<option value="' . $cat->id . '">' . $cat->name . '</option>';
									endif;
								endforeach;
							?>	
							</select>
							
							<a id="project_add_category" class="button" href="#">Add new</a>
							<p id="project_add_category_feedback"><p>
						</td>
			        </tr>

					<tr>
						<th>Date</th>
						<td>
							<input class="regular-text" type="text" name="project_date" id="project_date" value="<?php if (!empty($_POST['project_date'])) echo $_POST['project_date']; ?>" />
						</td>
			        </tr>

					<tr>
						<th>Project management</th>
						<td>
							<input class="regular-text" type="text" name="project_management" id="project_management" value="<?php if (!empty($_POST['project_management'])) echo $_POST['project_management']; ?>" />
						</td>
			        </tr>

					<tr>
						<th>Short description</th>
						<td>
							<textarea name="project_shortdesc" id="project_shortdesc"><?php if (!empty($_POST['project_shortdesc'])) echo $_POST['project_shortdesc']; ?></textarea>
						</td>
			        </tr>

					<tr>
						<th>Long description</th>
						<td>

							<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
								
								<?php 
									if (!empty($_POST['content'])) :
										the_editor($_POST['content']);
									else :
										the_editor('');
									endif;
								?>

								<table id="post-status-info" cellspacing="0">
									<tbody>
										<tr>
											<td id="wp-word-count"></td>
											<td class="autosave-info"><span id="autosave">&nbsp;</span></td>
										</tr>
									</tbody>
								</table>
							
							</div>
	
						</td>
			        </tr>
			
					<tr>
						<th>Status</th>
						<td>
							<fieldset>
								<label title="Will be visible on your website immediately">
									<input type="radio" value="published" name="project_status" id="project_status_published" <?php if (!empty($_POST['project_status']) && $_POST['project_status'] == 'published') echo 'checked="checked" '; ?>/>
									Published
								</label>
								<br />
								<label title="Drafts are not visible on your website">
									<input type="radio" value="draft" name="project_status" id="project_status_draft" <?php if ((!empty($_POST['project_status']) && $_POST['project_status'] == 'draft') || empty($_POST['project_status'])) echo 'checked="checked" '; ?>/>
									Draft
								</label>
								<br />
								<label title="Archived projects may be visible on your website">
									<input type="radio" value="archived" name="project_status" id="project_status_archived" <?php if (!empty($_POST['project_status']) && $_POST['project_status'] == 'archived') echo 'checked="checked" '; ?>/>
									Archived
								</label>
							</fieldset>
						</td>
			        </tr>
						
				</table>
			

				
				<h3 style="margin-top:40px;">Images and file attachments</h3>
								
				<table class="form-table">
			
					<tr>
						<th>Image 1</th>
						<td id="portfolio-project-image-1">
							<?php
								if (!empty($_POST['project_image_1'])) :
									$image = $wpdb->get_row("SELECT * FROM $wpdb->portfoliomedia WHERE id = '" . $_POST['project_image_1'] . "'");									

									if (!empty($image->thumb2_filename)) :
										$thumb_link = $portfolio->options['media_url'] . '/' . $image->thumb2_filename;
									elseif (!empty($image->thumb1_filename)) :
										$thumb_link = $portfolio->options['media_url'] . '/' . $image->thumb1_filename;
									else :
										$thumb_link = $portfolio->options['media_url'] . '/' . $image->filename;
									endif;
									
									if (strrchr($image->filename, '.') != false) :
										$extension = strtoupper(substr(strrchr($image->filename, '.'), 1));
									else :
										$extension = '';
									endif;
									
									echo '
										<div class="portfolio-project-image-1">
											<input type="hidden" name="project_image_1" value="' . $image->id . '" />
											<img src="' . $thumb_link . '" alt="" />' . $image->name . '<br />' . $extension . '<br />
											<a href="#">Delete</a>
										</div>';
								endif;
							?>
							<a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php?action=portfolio-image1" class="thickbox button" title="Select image"><?php echo empty($_POST['project_image_1']) ? 'Add' : 'Replace'; ?></a>
						</td>
			        </tr>

					<tr>
						<th>Image 2</th>
						<td id="portfolio-project-image-2">
							<?php
								if (!empty($_POST['project_image_2'])) :
									$image = $wpdb->get_row("SELECT * FROM $wpdb->portfoliomedia WHERE id = '" . $_POST['project_image_2'] . "'");									

									if (!empty($image->thumb2_filename)) :
										$thumb_link = $portfolio->options['media_url'] . '/' . $image->thumb2_filename;
									elseif (!empty($image->thumb1_filename)) :
										$thumb_link = $portfolio->options['media_url'] . '/' . $image->thumb1_filename;
									else :
										$thumb_link = $portfolio->options['media_url'] . '/' . $image->filename;
									endif;
									
									if (strrchr($image->filename, '.') != false) :
										$extension = strtoupper(substr(strrchr($image->filename, '.'), 1));
									else :
										$extension = '';
									endif;
									
									echo '
										<div class="portfolio-project-image-2">
											<input type="hidden" name="project_image_2" value="' . $image->id . '" />
											<img src="' . $thumb_link . '" alt="" />' . $image->name . '<br />' . $extension . '<br />
											<a href="#">Delete</a>
										</div>';
								endif;
							?>
							<a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php?action=portfolio-image2" class="thickbox button" title="Select image"><?php echo empty($_POST['project_image_2']) ? 'Add' : 'Replace'; ?></a>
						</td>
			        </tr>

					<tr>
						<th>Image 3</th>
						<td id="portfolio-project-image-3">
							<?php
								if (!empty($_POST['project_image_3'])) :
									$image = $wpdb->get_row("SELECT * FROM $wpdb->portfoliomedia WHERE id = '" . $_POST['project_image_3'] . "'");									

									if (!empty($image->thumb2_filename)) :
										$thumb_link = $portfolio->options['media_url'] . '/' . $image->thumb2_filename;
									elseif (!empty($image->thumb1_filename)) :
										$thumb_link = $portfolio->options['media_url'] . '/' . $image->thumb1_filename;
									else :
										$thumb_link = $portfolio->options['media_url'] . '/' . $image->filename;
									endif;
									
									if (strrchr($image->filename, '.') != false) :
										$extension = strtoupper(substr(strrchr($image->filename, '.'), 1));
									else :
										$extension = '';
									endif;
									
									echo '
										<div class="portfolio-project-image-3">
											<input type="hidden" name="project_image_3" value="' . $image->id . '" />
											<img src="' . $thumb_link . '" alt="" />' . $image->name . '<br />' . $extension . '<br />
											<a href="#">Delete</a>
										</div>';
								endif;
							?>
							<a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php?action=portfolio-image3" class="thickbox button" title="Select image"><?php echo empty($_POST['project_image_3']) ? 'Add' : 'Replace'; ?></a>
						</td>
			        </tr>

					<tr>
						<th>Additional images</th>
						<td id="portfolio-project-image-additional">
							<?php
								if (!empty($_POST['project_image_additional'])) :
								
									foreach (array_unique($_POST['project_image_additional']) as $img) :
										$image = $wpdb->get_row("SELECT * FROM $wpdb->portfoliomedia WHERE id = $img");

										if (!empty($image->thumb2_filename)) :
											$thumb_link = $portfolio->options['media_url'] . '/' . $image->thumb2_filename;
										elseif (!empty($image->thumb1_filename)) :
											$thumb_link = $portfolio->options['media_url'] . '/' . $image->thumb1_filename;
										else :
											$thumb_link = $portfolio->options['media_url'] . '/' . $image->filename;
										endif;

										if (strrchr($image->filename, '.') != false) :
											$extension = strtoupper(substr(strrchr($image->filename, '.'), 1));
										else :
											$extension = '';
										endif;

										echo '
											<div class="portfolio-project-image-additional">
												<input type="hidden" name="project_image_additional[]" value="' . $image->id . '" />
												<img src="' . $thumb_link . '" alt="" />' . $image->name . '<br />' . $extension . '<br />
												<a href="#">Delete</a>
											</div>';
									endforeach;
									
								endif;
							?>
							<a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php?action=portfolio-images" class="thickbox button" title="Select image">Add</a>
						</td>
			        </tr>

					<tr>
						<th>Other files</th>
						<td id="portfolio-project-files-other">
							<?php
								if (!empty($_POST['project_other_file'])) :
								
									foreach (array_unique($_POST['project_other_file']) as $f_id) :
										$file = $wpdb->get_row("SELECT * FROM $wpdb->portfoliomedia WHERE id = $f_id");

										if (strrchr($file->filename, '.') != false) :
											$extension = strtoupper(substr(strrchr($file->filename, '.'), 1));
										else :
											$extension = '';
										endif;

										echo '
											<div class="portfolio-project-other-file">
												<input type="hidden" name="project_other_file[]" value="' . $file->id . '" />
												' . $file->name . '<br />' . $extension . '&nbsp;&nbsp;&nbsp;<a href="#">Delete</a>
											</div>';
									endforeach;
									
								endif;
							?>
							<a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php?action=portfolio-other-files" class="thickbox button" title="Select file">Add</a>
						</td>
			        </tr>

			
		
				</table>
				
				
			    <p class="submit">
					<input type="submit" class="button-primary" name="projects_submit" id="projects_submit" value="Save Project" />
					
			    </p>
				
				
			</form>
		
		</div>

<?php

}



// Saves a new project and does the validation
function portfolioProjectsSave() {
	
	global $wpdb, $portfolio;


	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;


	if (!empty($_POST['project_mode']) && $_POST['project_mode'] == 'new') :
		
		// validate all entries
		$errors = array();
		
		// name must be set
		if (empty($_POST['project_name'])) :
			$errors[] = '\'Name\' is a required field.';
		endif;
		
		// at least one category must be set
		if (empty($_POST['project_categories'])) :
			$errors[] = 'You must select at least one category.';
		endif;
		
		// if errors were found, return to the 'new project' form
		if (!empty($errors)) :
			$message = '
				<div id="message" class="error">
					<p>Please correct the following errors:
						<ul>';
					
			foreach ($errors as $e) :
				$message .= '<li>' . $e . '</li>';
			endforeach;		
						
			$message .=	'
						</ul>
					</p>
				</div>';
			
			portfolioProjectsNew($message);
			return;
		endif;
		
		
		// if no errors were found, save a new entry
		$name = stripslashes($_POST['project_name']);
		$shortdesc = stripslashes($_POST['project_shortdesc']);
		$longdesc = stripslashes($_POST['content']);
		$url = stripslashes($_POST['project_url']);
		$date = stripslashes($_POST['project_date']);
		$status = $_POST['project_status'];
		$management = stripslashes($_POST['project_management']);
		$lastmodified = time();
		$added = time();
		
		$image1 = empty($_POST['project_image_1']) ? NULL : $_POST['project_image_1'];
		$image2 = empty($_POST['project_image_2']) ? NULL : $_POST['project_image_2'];
		$image3 = empty($_POST['project_image_3']) ? NULL : $_POST['project_image_3'];
		
		// save project
		if ($wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->portfolioprojects} (name, shortdesc, longdesc, url, date, status, management, lastmodified, added) VALUES (%s, %s, %s, %s, %s, %s, %s, %d, %d)", $name, $shortdesc, $longdesc, $url, $date, $status, $management, $lastmodified, $added)) === false) :
			$message = '
				<div id="message" class="error">
					<p>An error occured when trying to save the project.</p>
				</div>';
			portfolioProjectsNew($message);
			return;
		endif;
		
		$id = $wpdb->insert_id;
		
		
		// prepare category relations
		$categories = array();
		foreach ($_POST['project_categories'] as $cat_id) :
			$categories[] = "('" . $cat_id . "', '" . $id . "')";
		endforeach;
		$categories = implode(',', $categories);
		
		// save category relations
		if ($wpdb->query("INSERT INTO $wpdb->portfoliorelations (category_id, project_id) VALUES $categories") === false) :
			$message = '
				<div id="message" class="error">
					<p>An error occured when trying to save the categories.</p>
				</div>';
			portfolioProjectsNew($message);
			return;
		endif;



		// save image1 relation
		if (!empty($image1)) :
			if ($wpdb->query($wpdb->prepare("INSERT INTO $wpdb->portfoliorelations (project_id, image1_id) VALUES ($id, $image1)")) === false) :
				$message = '
					<div id="message" class="error">
						<p>An error occured when trying to save the new Image 1 relation.</p>
					</div>';
				portfolioProjectsEdit($message);
				return;
			endif;
		endif;
		
		// save new image2 relation
		if (!empty($image2)) :
			if ($wpdb->query($wpdb->prepare("INSERT INTO $wpdb->portfoliorelations (project_id, image2_id) VALUES ($id, $image2)")) === false) :
				$message = '
					<div id="message" class="error">
						<p>An error occured when trying to save the new Image 2 relation.</p>
					</div>';
				portfolioProjectsEdit($message);
				return;
			endif;
		endif;

		// save new image3 relation
		if (!empty($image3)) :
			if ($wpdb->query($wpdb->prepare("INSERT INTO $wpdb->portfoliorelations (project_id, image3_id) VALUES ($id, $image3)")) === false) :
				$message = '
					<div id="message" class="error">
						<p>An error occured when trying to save the new Image 3 relation.</p>
					</div>';
				portfolioProjectsEdit($message);
				return;
			endif;
		endif;



		// prepare general image relations
		if (!empty($_POST['project_image_additional'])) :
			$images = array();
			foreach (array_unique($_POST['project_image_additional']) as $media_id) :
				$images[] = "('" . $id . "', '" . $media_id . "')";
			endforeach;
			$images = implode(',', $images);
		endif;

		// save general image relations
		if (!empty($images) && $wpdb->query($wpdb->prepare("INSERT INTO $wpdb->portfoliorelations (project_id, media_id) VALUES $images")) === false) :
			$message = '
				<div id="message" class="error">
					<p>An error occured when trying to save the images.</p>
				</div>';
			portfolioProjectsNew($message);
			return;
		endif;


		// prepare relations to other files
		if (!empty($_POST['project_other_file'])) :
			$files = array();
			foreach (array_unique($_POST['project_other_file']) as $media_id) :
				$files[] = "('" . $id . "', '" . $media_id . "')";
			endforeach;
			$files = implode(',', $files);
		endif;

		// save relations to other files
		if (!empty($files) && $wpdb->query($wpdb->prepare("INSERT INTO $wpdb->portfoliorelations (project_id, media_id) VALUES $files")) === false) :
			$message = '
				<div id="message" class="error">
					<p>An error occured when trying to save the files.</p>
				</div>';
			portfolioProjectsNew($message);
			return;
		endif;


		
		// if everything went successful, return to the project list
		$message = '
			<div id="message" class="updated fade">
				<p>Project added!</p>
			</div>';
		portfolioProjects('all', $message);
		return;
	
	endif;
	
}





// Edits an existing project
function portfolioProjectsEdit($message = false) {
	
	global $wpdb, $portfolio;


	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;


	// get categories
	$categories = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->portfoliocategories"));
	
	
	// get image 1
	if (!empty($_POST['project_image_1'])) :
		$image1 = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->portfoliomedia WHERE id = '" . $_POST['project_image_1'] . "'"));
	else :
		$image1 = $wpdb->get_row($wpdb->prepare("SELECT $wpdb->portfoliomedia.id, $wpdb->portfoliomedia.filename, $wpdb->portfoliomedia.name, $wpdb->portfoliomedia.type, $wpdb->portfoliomedia.thumb1_filename, $wpdb->portfoliomedia.thumb2_filename FROM $wpdb->portfoliomedia INNER JOIN $wpdb->portfoliorelations ON ($wpdb->portfoliomedia.id = $wpdb->portfoliorelations.image1_id) WHERE $wpdb->portfoliorelations.project_id = '" . $_GET['project'] . "' AND $wpdb->portfoliorelations.image1_id IS NOT NULL AND $wpdb->portfoliomedia.type = 'image'"));
	endif;


	// get image 2
	if (!empty($_POST['project_image_2'])) :
		$image2 = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->portfoliomedia WHERE id = '" . $_POST['project_image_2'] . "'"));
	else :
		$image2 = $wpdb->get_row($wpdb->prepare("SELECT $wpdb->portfoliomedia.id, $wpdb->portfoliomedia.filename, $wpdb->portfoliomedia.name, $wpdb->portfoliomedia.type, $wpdb->portfoliomedia.thumb1_filename, $wpdb->portfoliomedia.thumb2_filename FROM $wpdb->portfoliomedia INNER JOIN $wpdb->portfoliorelations ON ($wpdb->portfoliomedia.id = $wpdb->portfoliorelations.image2_id) WHERE $wpdb->portfoliorelations.project_id = '" . $_GET['project'] . "' AND $wpdb->portfoliorelations.image2_id IS NOT NULL AND $wpdb->portfoliomedia.type = 'image'"));
	endif;


	// get image 3
	if (!empty($_POST['project_image_3'])) :
		$image3 = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->portfoliomedia WHERE id = '" . $_POST['project_image_3'] . "'"));
	else :
		$image3 = $wpdb->get_row($wpdb->prepare("SELECT $wpdb->portfoliomedia.id, $wpdb->portfoliomedia.filename, $wpdb->portfoliomedia.name, $wpdb->portfoliomedia.type, $wpdb->portfoliomedia.thumb1_filename, $wpdb->portfoliomedia.thumb2_filename FROM $wpdb->portfoliomedia INNER JOIN $wpdb->portfoliorelations ON ($wpdb->portfoliomedia.id = $wpdb->portfoliorelations.image3_id) WHERE $wpdb->portfoliorelations.project_id = '" . $_GET['project'] . "' AND $wpdb->portfoliorelations.image3_id IS NOT NULL AND $wpdb->portfoliomedia.type = 'image'"));
	endif;


	// get other images
	$images = $wpdb->get_results($wpdb->prepare("SELECT $wpdb->portfoliomedia.id, $wpdb->portfoliomedia.filename, $wpdb->portfoliomedia.name, $wpdb->portfoliomedia.type, $wpdb->portfoliomedia.thumb1_filename, $wpdb->portfoliomedia.thumb2_filename FROM $wpdb->portfoliomedia INNER JOIN $wpdb->portfoliorelations ON ($wpdb->portfoliomedia.id = $wpdb->portfoliorelations.media_id) WHERE $wpdb->portfoliorelations.project_id = '" . $_GET['project'] . "' AND $wpdb->portfoliorelations.media_id IS NOT NULL AND $wpdb->portfoliomedia.type = 'image' ORDER BY $wpdb->portfoliorelations.id", ARRAY_A));


	// get other files
	$files = $wpdb->get_results($wpdb->prepare("SELECT $wpdb->portfoliomedia.id, $wpdb->portfoliomedia.filename, $wpdb->portfoliomedia.name, $wpdb->portfoliomedia.type FROM $wpdb->portfoliomedia INNER JOIN $wpdb->portfoliorelations ON ($wpdb->portfoliomedia.id = $wpdb->portfoliorelations.media_id) WHERE $wpdb->portfoliorelations.project_id = '" . $_GET['project'] . "' AND $wpdb->portfoliorelations.media_id IS NOT NULL AND $wpdb->portfoliomedia.type = 'other' ORDER BY $wpdb->portfoliorelations.id", ARRAY_A));

	
	// get project
	if (empty($_GET['project'])) :
		$message = '
			<div id="message" class="error">
				<p>No project specified.</p>
			</div>';
		portfolioProjects($message);
		return;
	else :
		$project = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->portfolioprojects WHERE id = '" . $_GET['project'] . "'"));
		$category_relations = $wpdb->get_results($wpdb->prepare("SELECT category_id FROM $wpdb->portfoliorelations WHERE project_id = '" . $_GET['project'] . "'", ARRAY_A));
		
		$categories_selected = array();
		if (!empty($category_relations)) :
			foreach ($category_relations as $c) :
				$categories_selected[] = $c->category_id;
			endforeach;
		endif;
	endif;
?>
	
		<div class="wrap">
			<h2>Edit project <a href="?page=portfolio" class="button add-new-h2">Cancel</a></h2>
			
			<?php echo $message; ?>

			<form method="post" class="portfolio-project-editform" id="poststuff">				
				<input type="hidden" id="project_mode" name="project_mode" value="edit" />
				
				<h3>General information</h3>
				<table class="form-table">
				
					<tr>
						<th>
							<label for="project_name">
								<span class="alignleft">
									Name
								</span>
								<span class="alignright">
									<abbr class="required" title="required">*</abbr>
								</span>
							</label>
						</th>
						<td>
							<input class="regular-text" type="text" name="project_name" id="project_name" maxlength="250" value="<?php echo (!empty($_POST['project_name'])) ? stripslashes($_POST['project_name']) : stripslashes($project->name); ?>" />
						</td>
			        </tr>

					<tr>
						<th>URL</th>
						<td>
							<input class="regular-text" type="text" name="project_url" id="project_url" maxlength="250" value="<?php echo (!empty($_POST['project_url'])) ? stripslashes($_POST['project_url']) : stripslashes($project->url); ?>" />
						</td>
			        </tr>

					<tr>
						<th>
							<label for="project_categories">
								<span class="alignleft">
									Name
								</span>
								<span class="alignright">
									<abbr class="required" title="required">*</abbr>
								</span>
							</label>
						</th>
						<td>
							<select name="project_categories[]" id="project_categories" multiple="multiple">';
							<?php
								foreach ($categories as $cat) :
									if (!empty($_POST['project_categories'])) :
										if (in_array($cat->id, $_POST['project_categories'])) :
											echo '<option value="' . $cat->id . '" selected="selected">' . $cat->name . '</option>';
										else :
											echo '<option value="' . $cat->id . '">' . $cat->name . '</option>';
										endif;
									else :
										if (in_array($cat->id, $categories_selected)) :
											echo '<option value="' . $cat->id . '" selected="selected">' . $cat->name . '</option>';
										else :
											echo '<option value="' . $cat->id . '">' . $cat->name . '</option>';
										endif;
									endif;
								endforeach;
							?>	
							</select>
							
							<a id="project_add_category" class="button" href="#">Add new</a>
							<p id="project_add_category_feedback"><p>
						</td>
			        </tr>

					<tr>
						<th>Date</th>
						<td>
							<input class="regular-text" type="text" name="project_date" id="project_date" value="<?php 
									if (!empty($_POST['project_date'])) :
										echo $_POST['project_date'];
									elseif (!empty($project->date)) :
										echo $project->date;
									endif;
								?>" />
						</td>
			        </tr>

					<tr>
						<th>Project management</th>
						<td>
							<input class="regular-text" type="text" name="project_management" id="project_management" value="<?php echo (!empty($_POST['project_management'])) ? stripslashes($_POST['project_management']) : stripslashes($project->management); ?>" />
						</td>
			        </tr>

					<tr>
						<th>Short description</th>
						<td>
							<textarea name="project_shortdesc" id="project_shortdesc"><?php echo (!empty($_POST['project_shortdesc'])) ? stripslashes($_POST['project_shortdesc']) : stripslashes($project->shortdesc); ?></textarea>
						</td>
			        </tr>

					<tr>
						<th>Long description</th>
						<td>

							<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
								
								<?php 
									if (!empty($_POST['content'])) :
										the_editor(stripslashes($_POST['content']));
									else :
										the_editor(stripslashes($project->longdesc));
									endif;
								?>

								<table id="post-status-info" cellspacing="0">
									<tbody>
										<tr>
											<td id="wp-word-count"></td>
											<td class="autosave-info"><span id="autosave">&nbsp;</span></td>
										</tr>
									</tbody>
								</table>
							
							</div>
	
						</td>
			        </tr>
					<tr>
						<th>Status</th>
						<td>
							<fieldset>
								<label title="Will be visible on your website immediately">
									<input type="radio" value="published" name="project_status" id="project_status_published" 
										<?php 
											if (!empty($_POST['project_status']) && $_POST['project_status'] == 'published') :
												echo 'checked="checked" ';
											elseif (!empty($project) && $project->status == 'published') :
												echo 'checked="checked" ';
											endif;
										?>
									/>
									Published
								</label>
								<br />
								<label title="Drafts are not visible on your website">
									<input type="radio" value="draft" name="project_status" id="project_status_draft" 
										<?php
											if ((!empty($_POST['project_status']) && $_POST['project_status'] == 'draft') || (empty($_POST['project_status']) && empty($project))) :
												echo 'checked="checked" ';
											elseif 	(!empty($project) && $project->status == 'draft') :
												echo 'checked="checked" ';
											endif;
										?>
									/>
									Draft
								</label>
								<br />
								<label title="Archived projects may be visible on your website">
									<input type="radio" value="archived" name="project_status" id="project_status_archived" 
										<?php 
											if (!empty($_POST['project_status']) && $_POST['project_status'] == 'archived') :
												echo 'checked="checked" ';
											elseif (!empty($project) && $project->status == 'archived') :
												echo 'checked="checked" ';
											endif;
										?>
									/>
									Archived
								</label>
							</fieldset>
						</td>
			        </tr>

				</table>


				<h3 style="margin-top:40px;">Images and file attachments</h3>
				<table class="form-table" id="portfolio-project-attachments">

					<tr>
						<th>Image 1</th>
						<td id="portfolio-project-image-1">
							<?php
								if (!empty($image1)) :
									if (!empty($image1->thumb2_filename)) :
										$thumb_link = $portfolio->options['media_url'] . '/' . $image1->thumb2_filename;
									elseif (!empty($image1->thumb1_filename)) :
										$thumb_link = $portfolio->options['media_url'] . '/' . $image1->thumb1_filename;
									else :
										$thumb_link = $portfolio->options['media_url'] . '/' . $image1->filename;
									endif;
									
									if (strrchr($image1->filename, '.') != false) :
										$extension = strtoupper(substr(strrchr($image1->filename, '.'), 1));
									else :
										$extension = '';
									endif;
									
									echo '
										<div class="portfolio-project-image">
											<input type="hidden" name="project_image_1" value="' . $image1->id . '" />
											<img src="' . $thumb_link . '" alt="" />' . $image1->name . '<br />' . $extension . '<br />
											<a href="#">Delete</a>
										</div>';
								endif;
							?>
							<a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php?action=portfolio-image1" class="thickbox button" title="Select image"><?php echo empty($image1) ? 'Add' : 'Replace'; ?></a>
						</td>
			        </tr>

					<tr>
						<th>Image 2</th>
						<td id="portfolio-project-image-2">
							<?php
								if (!empty($image2)) :
									if (!empty($image2->thumb2_filename)) :
										$thumb_link = $portfolio->options['media_url'] . '/' . $image2->thumb2_filename;
									elseif (!empty($image2->thumb1_filename)) :
										$thumb_link = $portfolio->options['media_url'] . '/' . $image2->thumb1_filename;
									else :
										$thumb_link = $portfolio->options['media_url'] . '/' . $image2->filename;
									endif;
									
									if (strrchr($image2->filename, '.') != false) :
										$extension = strtoupper(substr(strrchr($image2->filename, '.'), 1));
									else :
										$extension = '';
									endif;
									
									echo '
										<div class="portfolio-project-image">
											<input type="hidden" name="project_image_2" value="' . $image2->id . '" />
											<img src="' . $thumb_link . '" alt="" />' . $image2->name . '<br />' . $extension . '<br />
											<a href="#">Delete</a>
										</div>';
								endif;
							?>
							<a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php?action=portfolio-image2" class="thickbox button" title="Select image"><?php echo empty($image2) ? 'Add' : 'Replace'; ?></a>
						</td>
			        </tr>

					<tr>
						<th>Image 3</th>
						<td id="portfolio-project-image-3">
							<?php
								if (!empty($image3)) :
									if (!empty($image3->thumb2_filename)) :
										$thumb_link = $portfolio->options['media_url'] . '/' . $image3->thumb2_filename;
									elseif (!empty($image3->thumb1_filename)) :
										$thumb_link = $portfolio->options['media_url'] . '/' . $image3->thumb1_filename;
									else :
										$thumb_link = $portfolio->options['media_url'] . '/' . $image3->filename;
									endif;
									
									if (strrchr($image3->filename, '.') != false) :
										$extension = strtoupper(substr(strrchr($image3->filename, '.'), 1));
									else :
										$extension = '';
									endif;
									
									echo '
										<div class="portfolio-project-image">
											<input type="hidden" name="project_image_3" value="' . $image3->id . '" />
											<img src="' . $thumb_link . '" alt="" />' . $image3->name . '<br />' . $extension . '<br />
											<a href="#">Delete</a>
										</div>';
								endif;
							?>
							<a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php?action=portfolio-image3" class="thickbox button" title="Select image"><?php echo empty($image3) ? 'Add' : 'Replace'; ?></a>
						</td>
			        </tr>

					<tr>
						<th>Additional images</th>
						<td id="portfolio-project-image-additional">
							<?php
								if (!empty($_POST['project_image_additional'])) :
								
									foreach (array_unique($_POST['project_image_additional']) as $img) :
										$image = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->portfoliomedia WHERE id = $img"));

										if (!empty($image->thumb2_filename)) :
											$thumb_link = $portfolio->options['media_url'] . '/' . $image->thumb2_filename;
										elseif (!empty($image->thumb1_filename)) :
											$thumb_link = $portfolio->options['media_url'] . '/' . $image->thumb1_filename;
										else :
											$thumb_link = $portfolio->options['media_url'] . '/' . $image->filename;
										endif;

										if (strrchr($image->filename, '.') != false) :
											$extension = strtoupper(substr(strrchr($image->filename, '.'), 1));
										else :
											$extension = '';
										endif;

										echo '
											<div class="portfolio-project-image-additional">
												<input type="hidden" name="project_image_additional[]" value="' . $image->id . '" />
												<img src="' . $thumb_link . '" alt="" />' . $image->name . '<br />' . $extension . '<br />
												<a href="#">Delete</a>
											</div>';
									endforeach;
									
								elseif (!empty($images)) :
								
									foreach ($images as $image) :
										if (!empty($image->thumb2_filename)) :
											$thumb_link = $portfolio->options['media_url'] . '/' . $image->thumb2_filename;
										elseif (!empty($image->thumb1_filename)) :
											$thumb_link = $portfolio->options['media_url'] . '/' . $image->thumb1_filename;
										else :
											$thumb_link = $portfolio->options['media_url'] . '/' . $image->filename;
										endif;

										if (strrchr($image->filename, '.') != false) :
											$extension = strtoupper(substr(strrchr($image->filename, '.'), 1));
										else :
											$extension = '';
										endif;

										echo '
											<div class="portfolio-project-image-additional">
												<input type="hidden" name="project_image_additional[]" value="' . $image->id . '" />
												<img src="' . $thumb_link . '" alt="" />' . $image->name . '<br />' . $extension . '<br />
												<a href="#">Delete</a>
											</div>';
									endforeach;
								
								endif;
							?>
							<a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php?action=portfolio-images" class="thickbox button" title="Select image">Add</a>
						</td>
			        </tr>

					<tr>
						<th>Other files</th>
						<td id="portfolio-project-files-other">
							<?php
								if (!empty($_POST['project_other_file'])) :
								
									foreach (array_unique($_POST['project_other_file']) as $f_id) :
										$file = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->portfoliomedia WHERE id = $f_id"));

										if (strrchr($file->filename, '.') != false) :
											$extension = strtoupper(substr(strrchr($file->filename, '.'), 1));
										else :
											$extension = '';
										endif;

										echo '
											<div class="portfolio-project-other-file">
												<input type="hidden" name="project_other_file[]" value="' . $file->id . '" />
												' . $file->name . '<br />' . $extension . '&nbsp;&nbsp;&nbsp;<a href="#">Delete</a>
											</div>';
									endforeach;
									
								elseif (!empty($files)) :
								
									foreach ($files as $file) :
										if (strrchr($file->filename, '.') != false) :
											$extension = strtoupper(substr(strrchr($file->filename, '.'), 1));
										else :
											$extension = '';
										endif;

										echo '
											<div class="portfolio-project-other-file">
												<input type="hidden" name="project_other_file[]" value="' . $file->id . '" />
												' . $file->name . '<br />' . $extension . '&nbsp;&nbsp;&nbsp;<a href="#">Delete</a>
											</div>';
									endforeach;
								
								endif;
							?>
							<a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin-ajax.php?action=portfolio-other-files" class="thickbox button" title="Select file">Add</a>
						</td>
			        </tr>



				</table>
				
				
			    <p class="submit">
					<input type="submit" class="button-primary" name="projects_update" id="projects_update" value="Save changes" />
			    </p>
				
				
			</form>
		
		</div>

<?php

}



// Updates an existing project and does the validation
function portfolioProjectsUpdate() {
	
	global $wpdb, $portfolio;


	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;


	if (!empty($_POST['project_mode']) && $_POST['project_mode'] == 'edit') :
	
		// validate all entries
		$errors = array();
		
		// name must be set
		if (empty($_POST['project_name'])) :
			$errors[] = '\'Name\' is a required field.';
		endif;
		
		// at least one category must be set
		if (empty($_POST['project_categories'])) :
			$errors[] = 'You must select at least one category.';
		endif;
		
		// if errors were found, return to the 'new project' form
		if (!empty($errors)) :
			$message = '
				<div id="message" class="error">
					<p>Please correct the following errors:
						<ul>';
					
			foreach ($errors as $e) :
				$message .= '<li>' . $e . '</li>';
			endforeach;		
						
			$message .=	'
						</ul>
					</p>
				</div>';
			
			portfolioProjectsEdit($message);
			return;
		endif;
				
		
		// if no errors were found, update the project
		$id = $_GET['project'];
		$name = stripslashes($_POST['project_name']);
		$shortdesc = stripslashes($_POST['project_shortdesc']);
		$longdesc = stripslashes($_POST['content']);
		$url = stripslashes($_POST['project_url']);
		$date = stripslashes($_POST['project_date']);
		$status = $_POST['project_status'];
		$management = stripslashes($_POST['project_management']);
		$lastmodified = time();
		
		$image1 = empty($_POST['project_image_1']) ? NULL : $_POST['project_image_1'];
		$image2 = empty($_POST['project_image_2']) ? NULL : $_POST['project_image_2'];
		$image3 = empty($_POST['project_image_3']) ? NULL : $_POST['project_image_3'];

		
		// save updated project
		if ($wpdb->query($wpdb->prepare("UPDATE {$wpdb->portfolioprojects} SET name = %s, shortdesc = %s, longdesc = %s, url = %s, date = %s, status = %s, management = %s, lastmodified = %d WHERE id = %d", $name, $shortdesc, $longdesc, $url, $date, $status, $management, $lastmodified, $id)) === false) :
			$message = '
				<div id="message" class="error">
					<p>An error occured when trying to save the project.</p>
				</div>';
			portfolioProjectsEdit($message);
			return;
		endif;
		
		

		// delete old image1 relation
		if ($wpdb->query($wpdb->prepare("DELETE FROM $wpdb->portfoliorelations WHERE project_id = $id AND image1_id IS NOT NULL")) === false) :
			$message = '
				<div id="message" class="error">
					<p>An error occured when trying to delete the old Image 1 relation.</p>
				</div>';
			portfolioProjectsEdit($message);
			return;
		endif;
		
		// save new image1 relation
		if (!empty($image1)) :
			if ($wpdb->query($wpdb->prepare("INSERT INTO $wpdb->portfoliorelations (project_id, image1_id) VALUES ($id, $image1)")) === false) :
				$message = '
					<div id="message" class="error">
						<p>An error occured when trying to save the new Image 1 relation.</p>
					</div>';
				portfolioProjectsEdit($message);
				return;
			endif;
		endif;
		
		
		
		// delete old image2 relation
		if ($wpdb->query($wpdb->prepare("DELETE FROM $wpdb->portfoliorelations WHERE project_id = $id AND image2_id IS NOT NULL")) === false) :
			$message = '
				<div id="message" class="error">
					<p>An error occured when trying to delete the old Image 2 relation.</p>
				</div>';
			portfolioProjectsEdit($message);
			return;
		endif;
		
		// save new image2 relation
		if (!empty($image2)) :
			if ($wpdb->query($wpdb->prepare("INSERT INTO $wpdb->portfoliorelations (project_id, image2_id) VALUES ($id, $image2)")) === false) :
				$message = '
					<div id="message" class="error">
						<p>An error occured when trying to save the new Image 2 relation.</p>
					</div>';
				portfolioProjectsEdit($message);
				return;
			endif;
		endif;
		
		
		
		// delete old image3 relation
		if ($wpdb->query($wpdb->prepare("DELETE FROM $wpdb->portfoliorelations WHERE project_id = $id AND image3_id IS NOT NULL")) === false) :
			$message = '
				<div id="message" class="error">
					<p>An error occured when trying to delete the old Image 3 relation.</p>
				</div>';
			portfolioProjectsEdit($message);
			return;
		endif;
		
		// save new image3 relation
		if (!empty($image3)) :
			if ($wpdb->query($wpdb->prepare("INSERT INTO $wpdb->portfoliorelations (project_id, image3_id) VALUES ($id, $image3)")) === false) :
				$message = '
					<div id="message" class="error">
						<p>An error occured when trying to save the new Image 3 relation.</p>
					</div>';
				portfolioProjectsEdit($message);
				return;
			endif;
		endif;
		
		
		
		// delete old general media relations
		if ($wpdb->query($wpdb->prepare("DELETE FROM $wpdb->portfoliorelations WHERE project_id = $id AND media_id IS NOT NULL")) === false) :
			$message = '
				<div id="message" class="error">
					<p>An error occured when trying to delete the old file relations.</p>
				</div>';
			portfolioProjectsEdit($message);
			return;
		endif;
			
			
		// prepare new image relations
		if (!empty($_POST['project_image_additional'])) :
			$images = array();
			foreach (array_unique($_POST['project_image_additional']) as $media_id) :
				$images[] = "('" . $id . "', '" . $media_id . "')";
			endforeach;
			$images = implode(',', $images);
		endif;
		
		// save new image relations
		if (!empty($images) && $wpdb->query($wpdb->prepare("INSERT INTO $wpdb->portfoliorelations (project_id, media_id) VALUES $images")) === false) :
			$message = '
				<div id="message" class="error">
					<p>An error occured when trying to save the new image relations.</p>
				</div>';
			portfolioProjectsEdit($message);
			return;
		endif;
			
			
		// prepare new relations to other files
		if (!empty($_POST['project_other_file'])) :
			$files = array();
			foreach (array_unique($_POST['project_other_file']) as $media_id) :
				$files[] = "('" . $id . "', '" . $media_id . "')";
			endforeach;
			$files = implode(',', $files);
		endif;
			
		// save new file relations
		if (!empty($files) && $wpdb->query($wpdb->prepare("INSERT INTO $wpdb->portfoliorelations (project_id, media_id) VALUES $files")) === false) :
			$message = '
				<div id="message" class="error">
					<p>An error occured when trying to save the new file relations.</p>
				</div>';
			portfolioProjectsEdit($message);
			return;
		endif;
					
					
		// prepare new category relations
		$categories = array();
		foreach ($_POST['project_categories'] as $cat_id) :
			$categories[] = "('" . $cat_id . "', '" . $id . "')";
		endforeach;
		$categories = implode(',', $categories);
		
		// delete old category relations
		if ($wpdb->query($wpdb->prepare("DELETE FROM $wpdb->portfoliorelations WHERE project_id = $id AND category_id IS NOT NULL")) === false) :
			$message = '
				<div id="message" class="error">
					<p>An error occured when trying to delete the old categories.</p>
				</div>';
			portfolioProjectsEdit($message);
			return;
		endif;

		// save new category relations
		if ($wpdb->query($wpdb->prepare("INSERT INTO $wpdb->portfoliorelations (category_id, project_id) VALUES $categories")) === false) :
			$message = '
				<div id="message" class="error">
					<p>An error occured when trying to save the new categories.</p>
				</div>';
			portfolioProjectsEdit($message);
			return;
		endif;



		// if everything went successful, return to the project list
		$message = '
			<div id="message" class="updated fade">
				<p>Project updated!</p>
			</div>';
		portfolioProjectsEdit($message);
		return;
	
	endif;
	
}


?>