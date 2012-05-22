<?php

// Stop direct calls to this file
if (!isset($this)) die('You cannot access this file directly.');


include_once (dirname (__FILE__) . '/includes/pagination.php');


// If bulk action was submitted and an action was selected
if ((isset($_POST['doaction']) && $_POST['action'] != '0') || (isset($_POST['doaction2']) && $_POST['action2'] != '0')) :
	// If some files were actually selected
	if (isset($_POST['media'])) :
		portfolioMediaActions();
	else :
		$message = '
			<div id="message" class="error">
				<p>
					No files selected.
				</p>
			</div>';
		portfolioMedia('all', $message);
	endif;
// If bulk action was submitted without selecting an option
elseif ((isset($_POST['doaction']) && $_POST['action'] == '0') || (isset($_POST['doaction2']) && $_POST['action2'] == '0'))	:
	$message = '
		<div id="message" class="error">
			<p>
				No action selected.
			</p>
		</div>';
	portfolioMedia('all', $message);
endif;


// If a specific type is requested and it's not returning from a bulk action (prevents double listing)
if (isset($_GET['type']) && !isset($_GET['action'])) :
	switch ($_GET['type']) :
		case 'image' :
			portfolioMedia('image');
			break;
		case 'other' :
			portfolioMedia('other');
			break;
		case 'all' :
		default:
			portfolioMedia('all');
			break;
	endswitch;
elseif (isset($_GET['action']) && $_GET['action'] == 'new') :
	portfolioMediaNew();
elseif (isset($_GET['action']) && $_GET['action'] == 'edit' && !empty($_POST['media_update'])) :
	portfolioMediaUpdate();
elseif (isset($_GET['action']) && $_GET['action'] == 'edit') :
	portfolioMediaEdit();
elseif (empty($_POST)) :
	portfolioMedia();
endif;



// Main function, lists the media files
function portfolioMedia($type = 'all', $message = false) {
	
	global $wpdb, $portfolio;
	

	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;


	date_default_timezone_set(get_option('timezone_string'));
	
	echo '
		<div class="wrap">
			<h2>Media Library <a href="?page=portfolio-media&amp;action=new" class="button add-new-h2">Add New</a></h2>';

	echo $message;
	
	// Load all entries from the media table
	$media = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->portfoliomedia ORDER BY uploaded"));
	

	if (count($media)) :
	
		echo '
			<form id="projects-filter" method="post">
				<input type="hidden" name="page" value="portfolio" />';
		
		$images = 0;
		$others = 0;
		$images_items = array();
		$others_items = array();
	
		// Sort and count media by type
		foreach ($media as $med) :
			switch ($med->type) :
				case 'image' :
					$images_items[] = $med;
					$images++;
					break;
				case 'other' :
					$others_items[] = $med;
					$others++;
					break;
			endswitch;
		endforeach;
	
	
		// If no page was specified, display the first one
		if (empty($_GET['p'])) :
			$page = 1;
		else :
			$page = $_GET['p'];
		endif;
	
		$all_media = $media;
		
		
		// Continue with only the files for the current page
		// Get the pagination HTML
		switch ($type) :
			case 'image' :
				$media = portfolioPaginationItems($images_items, $page);
				$pagination = portfolioPaginationHTML($images_items, $page, '?page=portfolio-media&amp;type=image&amp;');
				break;
			case 'other' :
				$media = portfolioPaginationItems($others_items, $page);
				$pagination = portfolioPaginationHTML($others_items, $page, '?page=portfolio-media&amp;type=other&amp;');
				break;
			case 'all' :
			default :
				$media = portfolioPaginationItems($all_media, $page);
				$pagination = portfolioPaginationHTML($all_media, $page, '?page=portfolio-media&amp;');
		endswitch;	

		


		echo '
				<ul class="subsubsub">';
		echo '		
					<li><a ' . ($type == 'all' ? 'class="current" ' : '') . 'href="?page=portfolio-media">All (<span id="count-all">' . count($all_media) . '</span>)</a> | </li>';
				
		if ($images > 0) :		
			echo '	<li><a ' . ($type == 'image' ? 'class="current" ' : '') . 'href="?page=portfolio-media&amp;type=image">Images (<span id="count-image">' . $images . '</span>)</a> | </li>';
		else :
			echo '<li>Images (<span id="count-image">' . $images . '</span>) | </li>';
		endif;

		if ($others > 0) :		
			echo '	<li><a ' . ($type == 'other' ? 'class="current" ' : '') . 'href="?page=portfolio-media&amp;type=other">Other (<span id="count-other">' . $others . '</span>)</a></li>';
		else :
			echo '<li>Other (<span id="count-other">' . $others . '</span>)</li>';
		endif;

		echo '
				</ul>';
			
			
		if ($type == 'all' || ($type == 'image' && $images > 0) || ($type == 'other' && $others > 0)) :
			echo '
				<div class="tablenav">
					<div class="alignleft actions">
						<select name="action">
							<option value="0" selected="selected">Bulk Actions</option>
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
							<th class="manage-column column-icon" scope="col"></th>
							<th class="manage-column" scope="col">File</th>
							<th class="manage-column" scope="col">Projects attached</th>
							<th class="manage-column date-column" scope="col">Date</th>
						</tr>
					</thead>
		
					<tbody>';
		else :
			echo '
					<div class="clear"></div>
					<p>No files here at the moment.</p>';
		endif;
	
	
		foreach ($media as $med) :
			if ($type == $med->type || $type == 'all') :
			
				// Get related projects
				$related_projects = $wpdb->get_results($wpdb->prepare("SELECT $wpdb->portfolioprojects.name FROM $wpdb->portfolioprojects INNER JOIN $wpdb->portfoliorelations ON ($wpdb->portfolioprojects.id = $wpdb->portfoliorelations.project_id) WHERE $wpdb->portfoliorelations.media_id = $med->id OR $wpdb->portfoliorelations.image1_id = $med->id OR $wpdb->portfoliorelations.image2_id = $med->id OR $wpdb->portfoliorelations.image3_id = $med->id ORDER BY $wpdb->portfolioprojects.name"));

				$projects = array();
				foreach ($related_projects as $rel) :
					$projects[] = $rel->name;
				endforeach;
			

				if (strrchr($med->filename, '.') != false) :
					$extension = strtoupper(substr(strrchr($med->filename, '.'), 1));
				else :
					$extension = '';
				endif;

				
				if ($med->type == 'image') :
					if (!empty($med->thumb2_filename)) :
						$thumb_link = $portfolio->options['media_url'] . '/' . $med->thumb2_filename;
					elseif (!empty($med->thumb1_filename)) :
						$thumb_link = $portfolio->options['media_url'] . '/' . $med->thumb1_filename;
					else :
						$thumb_link = $portfolio->options['media_url'] . '/' . $med->filename;
					endif;
				endif;
			
				echo '
						<tr id="media-'. $med->id .'">
							<th class="check-column" scope="row"><input type="checkbox" value="' . $med->id . '" name="media[]" /></th>
							<td class="column-icon media-icon">';
							
				if ($med->type == 'image') :
					echo '
								<a title="Edit &quot;' . $med->name . '&quot;" href="?page=portfolio-media&amp;action=edit&amp;file=' . $med->id . '">
									<img title="' . $med->name . '" alt="' . $med->alt . '" class="attachment-80x60" src="' . $thumb_link . '">
								</a>';
				endif;

				echo '
							</td>
							<td>
								<input type="hidden" id="media-'. $med->id .'-id" value="'. $med->id .'" class="media-id" />
								<input type="hidden" id="media-'. $med->id .'-type" value="'. $med->type .'" class="media-type" />
								<strong>
									<a class="row-title" title="Edit &quot;' . $med->name . '&quot;" href="?page=portfolio-media&amp;action=edit&amp;file=' . $med->id . '">' . $med->name . '</a>
								</strong>
								<br />
								<span>' . (empty($extension) ? "" : $extension) . '</span>
								<div class="row-actions">
									<span class="edit"><a title="Edit &quot;' . $med->name . '&quot;" href="?page=portfolio-media&amp;action=edit&amp;file=' . $med->id . '">Edit</a> | </span>
									<span class="trash"><a title="Delete &quot;' . $med->name . '&quot;" href="#">Delete</a></span>
								</div>
							</td>
							<td>' . (empty($projects) ? "None" : implode("<br />", array_unique($projects))) . '</td>
							<td class="date-column">' . date(get_option('date_format'), $med->uploaded) . '</td>
						</tr>';
			endif;
		endforeach;
	
	
	
		if ($type == 'all' || ($type == 'image' && $images > 0) || ($type == 'other' && $others > 0)) :
			echo '
					</tbody>
			
					<tfoot>
						<tr>
							<th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>
							<th class="manage-column column-icon" scope="col"></th>
							<th class="manage-column" scope="col">File</th>
							<th class="manage-column" scope="col">Projects attached</th>
							<th class="manage-column date-column" scope="col">Date</th>
						</tr>
					</tfoot>
				</table>
			
				<div class="tablenav">
					<div class="alignleft actions">
						<select name="action2">
							<option value="0" selected="selected">Bulk Actions</option>
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
		echo '<p>No files uploaded yet. Click <em>Add New</em> to add a new media file.</p>';
	endif;
	
	
	echo '</div>';
	
}



// Upload form for new media
function portfolioMediaNew($message = false) {
	
	echo '
		<div class="wrap">
			<h2>Upload New Media <a href="?page=portfolio-media" class="button add-new-h2">Cancel</a></h2>';

	echo $message;
	
	
	if (!current_user_can('upload_files')) :
		die('You do not have permission to upload files.');
	else :
?>

			<div class="clear"></div>
			<p>Click <em>Select Files</em> to select the files you want to upload. You can upload multiple files at once.<br />
				Note: the Flash player plugin is required for the upload to work.</p>
			
			<input id="fileInput" name="fileInput" type="file" />
			

<?php			
	endif;
			
	echo '
		</div>';

}



// Handles the bulk actions
function portfolioMediaActions() {
	
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
	
	if (isset($action) && !empty($_POST['media'])) :
		switch ($action) :				
			case 'delete' :
				foreach ($_POST['media'] as $med) :
				
					$media_dir = $portfolio->options['media_dir'];

					// Load the file entry
					$file = $wpdb->get_row("SELECT * FROM $wpdb->portfoliomedia WHERE id = $med");
				
					// Delete the file entry
					$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->portfoliomedia WHERE id = $med"));
					
					// Delete all relations to this file
					$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->portfoliorelations WHERE media_id = $med"));
					
					// Delete files
					@unlink($media_dir . '/' . $file->filename);
					@unlink($media_dir . '/' . $file->thumb1_filename);
					@unlink($media_dir . '/' . $file->thumb2_filename);
					
					// If media file was an image, check if any projects have it set as main image and if yes, remove the reference
					if ($file->type == 'image') :
						$wpdb->query($wpdb->prepare("UPDATE {$wpdb->portfolioprojects} SET main_image = %s WHERE main_image = %d", NULL, $file->id));
					endif;
					
				endforeach;
				$message = '
					<div id="message" class="updated fade">
						<p>
							<strong>' . count($_POST['media']) . ' File' . (count($_POST['media']) > 1 ? 's' : '') . ' deleted.</strong>
						</p>
					</div>';
				$_GET['action'] = 'complete';
				if (isset($_GET['status'])) :
					portfolioMedia($_GET['type'], $message);
				else :
					portfolioMedia('all', $message);
				endif;
				return;
		endswitch;
	else :
		portfolioMedia('all');
	endif;
}



// Edits an existing media file
function portfolioMediaEdit($message = false) {
	
	global $wpdb, $portfolio;


	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;


	date_default_timezone_set(get_option('timezone_string'));


	// get media file entry
	if (empty($_GET['file'])) :
		$message = '
			<div id="message" class="error">
				<p>No file specified.</p>
			</div>';
		portfolioMedia($message);
		return;
	else :
		$id = $_GET['file'];
		$media = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->portfoliomedia WHERE id = $id"));
		$all_projects = $wpdb->get_results($wpdb->prepare("SELECT id, name FROM $wpdb->portfolioprojects"));
		$related_projects = $wpdb->get_results($wpdb->prepare("SELECT $wpdb->portfolioprojects.id, $wpdb->portfolioprojects.name FROM $wpdb->portfolioprojects INNER JOIN $wpdb->portfoliorelations ON ($wpdb->portfolioprojects.id = $wpdb->portfoliorelations.project_id) WHERE $wpdb->portfoliorelations.media_id = $id ORDER BY $wpdb->portfolioprojects.name"));

	endif;
?>

		<div class="wrap">
			<h2>Edit media <a href="?page=portfolio-media" class="button add-new-h2">Cancel</a></h2>

			<?php echo $message; ?>

			<form method="post" class="portfolio-media-editform" id="poststuff">
				
				<div class="media-single">
					<div class="media-item" id="media-item-<?php echo $media->id ?>">
					
				
						<input type="hidden" id="media_mode" name="media_mode" value="edit" />
				
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
								</tr>

								<tr>
									<td><strong>Last modified: </strong><?php echo date(get_option('date_format'), $media->lastmodified); ?></td>
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
						
						
						<div class="clear" style="padding-top:40px"></div>
						
						<h3>File locations</h3>
						
						<table class="form-table describe">
							<tbody>
						
								<tr>
									<th>
										<label for="media_file_url">
											<span class="alignleft">
												File URL
											</span>
										</label>
									</th>
									<td>
										<input class="regular-text" readonly="readonly" type="text" name="media_file_url" id="media_file_url" value="<?php echo $portfolio->options['media_url'] . '/' . $media->filename; ?>" />
										<p class="help">Location of the uploaded file</p>
									</td>
								</tr>

		<?php if ($media->type == 'image' && !empty($media->thumb1_filename)) : ?>							
								<tr>
									<th>
										<label for="media_thumb1_url">
											<span class="alignleft">
												Thumbnail URL
											</span>
										</label>
									</th>
									<td>
										<input class="regular-text" readonly="readonly" type="text" name="media_thumb1_url" id="media_thumb1_url" value="<?php echo $portfolio->options['media_url'] . '/' . $media->thumb1_filename; ?>" />
										<p class="help">Location of the thumbnail with <?php echo $portfolio->options['thumb1_width']; ?>px width</p>
									</td>
								</tr>
		<?php endif; ?>

		<?php if ($media->type == 'image' && !empty($media->thumb2_filename)) : ?>							
								<tr>
									<th>
										<label for="media_thumb2_url">
											<span class="alignleft">
												Thumbnail URL
											</span>
										</label>
									</th>
									<td>
										<input class="regular-text" readonly="readonly" type="text" name="media_thumb2_url" id="media_thumb2_url" value="<?php echo $portfolio->options['media_url'] . '/' . $media->thumb2_filename; ?>" />
										<p class="help">Location of the thumbnail with <?php echo $portfolio->options['thumb2_width']; ?>px width</p>
									</td>
								</tr>
		<?php endif; ?>						
						
							</tbody>
	
						</table>


					    <p class="submit">
							<input type="submit" class="button-primary" name="media_update" id="media_update" value="Save changes" />
					    </p>

					</div>
				</div>

			</form>

		</div>

<?php

}




// Updates an existing media file and does the validation
function portfolioMediaUpdate() {
	
	global $wpdb, $portfolio;


	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;


	if (!empty($_POST['media_mode']) && $_POST['media_mode'] == 'edit') :
	
		// validate all entries
		$errors = array();
		

		// name must be set
		if (empty($_POST['media_name'])) :
			$errors[] = '\'Name\' is a required field.';
		endif;
		
		// if errors were found, return to the 'edit media' form
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
			
			portfolioMediaEdit($message);
			return;
		endif;
		
		
		// if no errors were found, update the media file
		$id = $_GET['file'];
		$name = stripslashes($_POST['media_name']);
		$alt = (!empty($_POST['media_alt']) ? stripslashes($_POST['media_alt']) : '');
		$description = (!empty($_POST['media_description']) ? stripslashes($_POST['media_description']) : '');
		$lastmodified = time();
		
		$projects = (empty($_POST['media_attached_projects'])) ? NULL : array_unique($_POST['media_attached_projects']);
		
		// save changes
		if ($wpdb->query($wpdb->prepare("UPDATE {$wpdb->portfoliomedia} SET name = %s, alt = %s, description = %s, lastmodified = %d WHERE id = %d", $name, $alt, $description, $lastmodified, $id)) !== false) :
		
			// delete old image relations
			$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->portfoliorelations WHERE media_id = $id AND project_id IS NOT NULL"));
		
			// save new image - project relations
			if (!empty($projects)) :
				$relations = array();
				foreach ($projects as $project_id) :
					$relations[] = "('" . $project_id . "', '" . $id . "')";
				endforeach;
				$relations = implode(',', $relations);
						
				if ($wpdb->query($wpdb->prepare("INSERT INTO $wpdb->portfoliorelations (project_id, media_id) VALUES $relations")) !== false) :

					// if everything went successful, return to the media edit screen
					$message = '
						<div id="message" class="updated fade">
							<p>File updated!</p>
						</div>';
					portfolioMediaEdit($message);
					return;
			
				endif;
			endif;
			
			// if everything went successful, return to the media edit screen
			$message = '
				<div id="message" class="updated fade">
					<p>File updated!</p>
				</div>';
			portfolioMediaEdit($message);
			return;
			
		endif;
		
		$message = '
			<div id="message" class="error">
				<p>An unknown error occured when trying to save the changes.</p>
			</div>';
		portfolioMediaEdit($message);
		return;

	endif;
	
}





?>