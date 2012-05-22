<?php

// Stop direct calls to this file
if (!isset($this)) die('You cannot access this file directly.');


// Set thickbox actions
add_action('wp_ajax_portfolio-image1', 'portfolioThickboxImage1');
add_action('wp_ajax_portfolio-image2', 'portfolioThickboxImage2');
add_action('wp_ajax_portfolio-image3', 'portfolioThickboxImage3');
add_action('wp_ajax_portfolio-images', 'portfolioThickboxImages');
add_action('wp_ajax_portfolio-other-files', 'portfolioThickboxOtherFiles');




function portfolioThickboxImage1() {
	
	global $wpdb, $portfolio;
	
	
	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;


	date_default_timezone_set(get_option('timezone_string'));
	
	// Load all projects from the media table
	$media = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->portfoliomedia WHERE type = 'image' ORDER BY uploaded"));
	
		
	if (count($media)) :

?>
			<div id="portfolio-media-frame-header">
				<ul>
					<li><a class="current" href="#">Media Library</a></li>
					<li><a href="#">Upload</a></li>
				</ul>
			</div>
	
			<div class="wrap">
				<h2>Select image<span class="alignright"><?php echo count($media); ?> images</span></h2>
		
				<form id="portfolio-thickbox-image1" method="post">
					<input type="hidden" name="page" value="portfolio-thickbox" />
		
					<table cellspacing="0" class="widefat post fixed">
						<thead>
							<tr>
								<th class="manage-column column-icon" scope="col"></th>
								<th class="manage-column" scope="col">File</th>
								<th class="manage-column" scope="col">Projects attached</th>
								<th class="manage-column date-column" scope="col">Date</th>
							</tr>
						</thead>
	
						<tbody>
		
<?php

		foreach ($media as $med) :

			// Get related projects
			$related_projects = $wpdb->get_results($wpdb->prepare("SELECT $wpdb->portfolioprojects.name FROM $wpdb->portfolioprojects INNER JOIN $wpdb->portfoliorelations ON ($wpdb->portfolioprojects.id = $wpdb->portfoliorelations.project_id) WHERE $wpdb->portfoliorelations.media_id = $med->id ORDER BY $wpdb->portfolioprojects.name"));

			$projects = array();
			foreach ($related_projects as $rel) :
				$projects[] = $rel->name;
			endforeach;


			if (strrchr($med->filename, '.') != false) :
				$extension = strtoupper(substr(strrchr($med->filename, '.'), 1));
			else :
				$extension = '';
			endif;


			if (!empty($med->thumb2_filename)) :
				$thumb_link = $portfolio->options['media_url'] . '/' . $med->thumb2_filename;
			elseif (!empty($med->thumb1_filename)) :
				$thumb_link = $portfolio->options['media_url'] . '/' . $med->thumb1_filename;
			else :
				$thumb_link = $portfolio->options['media_url'] . '/' . $med->filename;
			endif;

			echo '
							<tr class="thickbox-media" id="media-'. $med->id .'">
								<td class="column-icon media-icon">
									<img title="' . $med->name . '" alt="' . $med->alt . '" class="attachment-80x60" src="' . $thumb_link . '">
								</td>
								<td>
									<input type="hidden" id="media-'. $med->id .'-id" value="'. $med->id .'" class="media-id" />
									<strong>
										' . $med->name . '
									</strong>
									<br />
									<span>' . (empty($extension) ? "" : $extension) . '</span>
								</td>
								<td>' . (empty($projects) ? "None" : implode("<br />", $projects)) . '</td>
								<td class="date-column">' . date(get_option('date_format'), $med->uploaded) . '</td>
							</tr>';
		endforeach;

?>

						</tbody>
		
						<tfoot>
							<tr>
								<th class="manage-column column-icon" scope="col"></th>
								<th class="manage-column" scope="col">File</th>
								<th class="manage-column" scope="col">Projects attached</th>
								<th class="manage-column date-column" scope="col">Date</th>
							</tr>
						</tfoot>
					</table>
	
				</form>
			</div>
			
			
<?php

	else :
		echo '
			<div class="clear"></div>';
		echo '
			<p>No images available. Please upload files in the media section first.</p>';
	endif;

	
	exit();
}





function portfolioThickboxImage2() {
	
	global $wpdb, $portfolio;
	
	
	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;
	
	
	date_default_timezone_set(get_option('timezone_string'));
	
	// Load all projects from the media table
	$media = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->portfoliomedia WHERE type = 'image' ORDER BY uploaded"));
	
		
	if (count($media)) :

?>
	
			<div class="wrap">
				<h2>Select image<span class="alignright"><?php echo count($media); ?> images</span></h2>
		
				<form id="portfolio-thickbox-image2" method="post">
					<input type="hidden" name="page" value="portfolio-thickbox" />
		
					<table cellspacing="0" class="widefat post fixed">
						<thead>
							<tr>
								<th class="manage-column column-icon" scope="col"></th>
								<th class="manage-column" scope="col">File</th>
								<th class="manage-column" scope="col">Projects attached</th>
								<th class="manage-column date-column" scope="col">Date</th>
							</tr>
						</thead>
	
						<tbody>
		
<?php

		foreach ($media as $med) :

			// Get related projects
			$related_projects = $wpdb->get_results($wpdb->prepare("SELECT $wpdb->portfolioprojects.name FROM $wpdb->portfolioprojects INNER JOIN $wpdb->portfoliorelations ON ($wpdb->portfolioprojects.id = $wpdb->portfoliorelations.project_id) WHERE $wpdb->portfoliorelations.media_id = $med->id ORDER BY $wpdb->portfolioprojects.name"));

			$projects = array();
			foreach ($related_projects as $rel) :
				$projects[] = $rel->name;
			endforeach;


			if (strrchr($med->filename, '.') != false) :
				$extension = strtoupper(substr(strrchr($med->filename, '.'), 1));
			else :
				$extension = '';
			endif;


			if (!empty($med->thumb2_filename)) :
				$thumb_link = $portfolio->options['media_url'] . '/' . $med->thumb2_filename;
			elseif (!empty($med->thumb1_filename)) :
				$thumb_link = $portfolio->options['media_url'] . '/' . $med->thumb1_filename;
			else :
				$thumb_link = $portfolio->options['media_url'] . '/' . $med->filename;
			endif;

			echo '
							<tr class="thickbox-media" id="media-'. $med->id .'">
								<td class="column-icon media-icon">
									<img title="' . $med->name . '" alt="' . $med->alt . '" class="attachment-80x60" src="' . $thumb_link . '">
								</td>
								<td>
									<input type="hidden" id="media-'. $med->id .'-id" value="'. $med->id .'" class="media-id" />
									<strong>
										' . $med->name . '
									</strong>
									<br />
									<span>' . (empty($extension) ? "" : $extension) . '</span>
								</td>
								<td>' . (empty($projects) ? "None" : implode("<br />", $projects)) . '</td>
								<td class="date-column">' . date(get_option('date_format'), $med->uploaded) . '</td>
							</tr>';
		endforeach;

?>

						</tbody>
		
						<tfoot>
							<tr>
								<th class="manage-column column-icon" scope="col"></th>
								<th class="manage-column" scope="col">File</th>
								<th class="manage-column" scope="col">Projects attached</th>
								<th class="manage-column date-column" scope="col">Date</th>
							</tr>
						</tfoot>
					</table>
	
				</form>
			</div>
			
			
<?php

	else :
		echo '
			<div class="clear"></div>';
		echo '
			<p>No images available. Please upload files in the media section first.</p>';
	endif;

	
	exit();
}





function portfolioThickboxImage3() {
	
	global $wpdb, $portfolio;
	

	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;

	
	date_default_timezone_set(get_option('timezone_string'));
	
	// Load all projects from the media table
	$media = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->portfoliomedia WHERE type = 'image' ORDER BY uploaded"));
	
		
	if (count($media)) :

?>
	
			<div class="wrap">
				<h2>Select image<span class="alignright"><?php echo count($media); ?> images</span></h2>
		
				<form id="portfolio-thickbox-image3" method="post">
					<input type="hidden" name="page" value="portfolio-thickbox" />
		
					<table cellspacing="0" class="widefat post fixed">
						<thead>
							<tr>
								<th class="manage-column column-icon" scope="col"></th>
								<th class="manage-column" scope="col">File</th>
								<th class="manage-column" scope="col">Projects attached</th>
								<th class="manage-column date-column" scope="col">Date</th>
							</tr>
						</thead>
	
						<tbody>
		
<?php

		foreach ($media as $med) :

			// Get related projects
			$related_projects = $wpdb->get_results($wpdb->prepare("SELECT $wpdb->portfolioprojects.name FROM $wpdb->portfolioprojects INNER JOIN $wpdb->portfoliorelations ON ($wpdb->portfolioprojects.id = $wpdb->portfoliorelations.project_id) WHERE $wpdb->portfoliorelations.media_id = $med->id ORDER BY $wpdb->portfolioprojects.name"));

			$projects = array();
			foreach ($related_projects as $rel) :
				$projects[] = $rel->name;
			endforeach;


			if (strrchr($med->filename, '.') != false) :
				$extension = strtoupper(substr(strrchr($med->filename, '.'), 1));
			else :
				$extension = '';
			endif;


			if (!empty($med->thumb2_filename)) :
				$thumb_link = $portfolio->options['media_url'] . '/' . $med->thumb2_filename;
			elseif (!empty($med->thumb1_filename)) :
				$thumb_link = $portfolio->options['media_url'] . '/' . $med->thumb1_filename;
			else :
				$thumb_link = $portfolio->options['media_url'] . '/' . $med->filename;
			endif;

			echo '
							<tr class="thickbox-media" id="media-'. $med->id .'">
								<td class="column-icon media-icon">
									<img title="' . $med->name . '" alt="' . $med->alt . '" class="attachment-80x60" src="' . $thumb_link . '">
								</td>
								<td>
									<input type="hidden" id="media-'. $med->id .'-id" value="'. $med->id .'" class="media-id" />
									<strong>
										' . $med->name . '
									</strong>
									<br />
									<span>' . (empty($extension) ? "" : $extension) . '</span>
								</td>
								<td>' . (empty($projects) ? "None" : implode("<br />", $projects)) . '</td>
								<td class="date-column">' . date(get_option('date_format'), $med->uploaded) . '</td>
							</tr>';
		endforeach;

?>

						</tbody>
		
						<tfoot>
							<tr>
								<th class="manage-column column-icon" scope="col"></th>
								<th class="manage-column" scope="col">File</th>
								<th class="manage-column" scope="col">Projects attached</th>
								<th class="manage-column date-column" scope="col">Date</th>
							</tr>
						</tfoot>
					</table>
	
				</form>
			</div>
			
			
<?php

	else :
		echo '
			<div class="clear"></div>';
		echo '
			<p>No images available. Please upload files in the media section first.</p>';
	endif;

	
	exit();
}









function portfolioThickboxImages() {
	
	global $wpdb, $portfolio;
	

	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;

	
	date_default_timezone_set(get_option('timezone_string'));
	
	// Load all projects from the media table
	$media = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->portfoliomedia WHERE type = 'image' ORDER BY uploaded"));
	
		
	if (count($media)) :

?>
	
			<div class="wrap">
				<h2>Select image<span class="alignright"><?php echo count($media); ?> images</span></h2>
		
				<form id="portfolio-thickbox-images" method="post">
					<input type="hidden" name="page" value="portfolio-thickbox" />
		
					<table cellspacing="0" class="widefat post fixed">
						<thead>
							<tr>
								<th class="manage-column column-icon" scope="col"></th>
								<th class="manage-column" scope="col">File</th>
								<th class="manage-column" scope="col">Projects attached</th>
								<th class="manage-column date-column" scope="col">Date</th>
							</tr>
						</thead>
	
						<tbody>
		
<?php

		foreach ($media as $med) :

			// Get related projects
			$related_projects = $wpdb->get_results($wpdb->prepare("SELECT $wpdb->portfolioprojects.name FROM $wpdb->portfolioprojects INNER JOIN $wpdb->portfoliorelations ON ($wpdb->portfolioprojects.id = $wpdb->portfoliorelations.project_id) WHERE $wpdb->portfoliorelations.media_id = $med->id ORDER BY $wpdb->portfolioprojects.name"));

			$projects = array();
			foreach ($related_projects as $rel) :
				$projects[] = $rel->name;
			endforeach;


			if (strrchr($med->filename, '.') !== false) :
				$extension = strtoupper(substr(strrchr($med->filename, '.'), 1));
			else :
				$extension = '';
			endif;


			if (!empty($med->thumb2_filename)) :
				$thumb_link = $portfolio->options['media_url'] . '/' . $med->thumb2_filename;
			elseif (!empty($med->thumb1_filename)) :
				$thumb_link = $portfolio->options['media_url'] . '/' . $med->thumb1_filename;
			else :
				$thumb_link = $portfolio->options['media_url'] . '/' . $med->filename;
			endif;

			echo '
							<tr class="thickbox-media" id="media-'. $med->id .'">
								<td class="column-icon media-icon">
									<img title="' . $med->name . '" alt="' . $med->alt . '" class="attachment-80x60" src="' . $thumb_link . '">
								</td>
								<td>
									<input type="hidden" id="media-'. $med->id .'-id" value="'. $med->id .'" class="media-id" />
									<strong>
										' . $med->name . '
									</strong>
									<br />
									<span>' . (empty($extension) ? "" : $extension) . '</span>
								</td>
								<td>' . (empty($projects) ? "None" : implode("<br />", $projects)) . '</td>
								<td class="date-column">' . date(get_option('date_format'), $med->uploaded) . '</td>
							</tr>';
		endforeach;

?>

						</tbody>
		
						<tfoot>
							<tr>
								<th class="manage-column column-icon" scope="col"></th>
								<th class="manage-column" scope="col">File</th>
								<th class="manage-column" scope="col">Projects attached</th>
								<th class="manage-column date-column" scope="col">Date</th>
							</tr>
						</tfoot>
					</table>
	
				</form>
			</div>
			
			
<?php

	else :
		echo '
			<div class="clear"></div>';
		echo '
			<p>No images available. Please upload files in the media section first.</p>';
	endif;

	
	exit();
}








function portfolioThickboxOtherFiles() {

	global $wpdb, $portfolio;


	if ($portfolio->options['debug']) :
		$wpdb->show_errors();
	else :
		$wpdb->hide_errors();
	endif;


	date_default_timezone_set(get_option('timezone_string'));

	// Load all projects from the media table
	$media = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->portfoliomedia WHERE type = 'other' ORDER BY uploaded"));


	if (count($media)) :

?>

			<div class="wrap">
				<h2>Select file<span class="alignright"><?php echo count($media); ?> files</span></h2>

				<form id="portfolio-thickbox-other" method="post">
					<input type="hidden" name="page" value="portfolio-thickbox" />

					<table cellspacing="0" class="widefat post fixed">
						<thead>
							<tr>
								<th class="manage-column" scope="col">File</th>
								<th class="manage-column" scope="col">Projects attached</th>
								<th class="manage-column date-column" scope="col">Date</th>
							</tr>
						</thead>

						<tbody>

<?php

		foreach ($media as $med) :

			// Get related projects
			$related_projects = $wpdb->get_results($wpdb->prepare("SELECT $wpdb->portfolioprojects.name FROM $wpdb->portfolioprojects INNER JOIN $wpdb->portfoliorelations ON ($wpdb->portfolioprojects.id = $wpdb->portfoliorelations.project_id) WHERE $wpdb->portfoliorelations.media_id = $med->id ORDER BY $wpdb->portfolioprojects.name"));

			$projects = array();
			foreach ($related_projects as $rel) :
				$projects[] = $rel->name;
			endforeach;


			if (strrchr($med->filename, '.') != false) :
				$extension = strtoupper(substr(strrchr($med->filename, '.'), 1));
			else :
				$extension = '';
			endif;


			echo '
							<tr class="thickbox-media" id="media-'. $med->id .'">
								<td>
									<input type="hidden" id="media-'. $med->id .'-id" value="'. $med->id .'" class="media-id" />
									<strong>
										' . $med->name . '
									</strong>
									<br />
									<span>' . (empty($extension) ? "" : $extension) . '</span>
								</td>
								<td>' . (empty($projects) ? "None" : implode("<br />", $projects)) . '</td>
								<td class="date-column">' . date(get_option('date_format'), $med->uploaded) . '</td>
							</tr>';
		endforeach;

?>

						</tbody>

						<tfoot>
							<tr>
								<th class="manage-column" scope="col">File</th>
								<th class="manage-column" scope="col">Projects attached</th>
								<th class="manage-column date-column" scope="col">Date</th>
							</tr>
						</tfoot>
					</table>

				</form>
			</div>


<?php

	else :
		echo '
			<div class="clear"></div>';
		echo '
			<p>No files available. Please upload files in the media section first.</p>';
	endif;


	exit();
}

?>