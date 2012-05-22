<?php  

// Stop direct calls to this file
if (!isset($this)) die('You cannot access this file directly.');


global $portfolio;

// check if the settings form was submitted
if (!empty($_POST['settings_save'])) :
	portfolioSettingsSave();
else :
	portfolioSettings();
endif;



function portfolioSettings($message = false) {
	
	global $portfolio;

	
	echo '
		<div class="wrap">
		<h2>Settings</h2>';
		
	echo $message;
?>	

		<form method="post">

			<h3>General Settings</h3>
			
			<table class="form-table">

				<tr>
					<th>
						<label for="settings_debug">
							<span class="alignleft">
								Debug mode
							</span>
						</label>
					</th>
					<td>
						<fieldset>
							<label title="Verbose error messages will be displayed where available">
								<input type="radio" value="1" name="settings_debug" id="settings_debug_on" 
									<?php 
										if (!empty($_POST['settings_debug']) && $_POST['settings_debug']) :
											echo 'checked="checked" ';
										elseif (!empty($portfolio->options['debug']) && $portfolio->options['debug']) :
											echo 'checked="checked" ';
										endif;
									?>
								/>
								On
							</label>
							<br />
							<label title="Only generic error messages will be displayed">
								<input type="radio" value="0" name="settings_debug" id="settings_debug_off" 
									<?php 
										if (!empty($_POST['settings_debug']) && !$_POST['settings_debug']) :
											echo 'checked="checked" ';
										elseif (empty($portfolio->options['debug']) || (!empty($portfolio->options['debug']) && !$portfolio->options['debug'])) :
											echo 'checked="checked" ';
										endif;
									?>
								/>
								Off
							</label>
						</fieldset>
					</td>
				</tr>
		
			</table>
			
			<h3>Image Settings</h3>
			
			<p>Changing image settings will not affect existing media files.</p>

			<table class="form-table">
			
				<tr>
					<th>
						<label for="settings_thumb1_width">
							<span class="alignleft">
								Thumbnail 1 width
							</span>
							<span class="alignright">
								<abbr class="required" title="required">*</abbr>
							</span>
						</label>
					</th>
					<td>
						<input class="regular-text" type="text" name="settings_thumb1_width" id="settings_thumb1_width" value="<?php echo (!empty($_POST['settings_thumb1_width'])) ? stripslashes($_POST['settings_thumb1_width']) : $portfolio->options['thumb1_width']; ?>" />
					</td>
				</tr>

				<tr>
					<th>
						<label for="settings_thumb1_square">
							<span class="alignleft">
								Create square thumbnail?
							</span>
						</label>
					</th>
					<td>
						<input type="checkbox" name="settings_thumb1_square" id="settings_thumb1_square" value="1"<?php echo ($portfolio->options['thumb1_square'] == '1') ? ' checked="checked"' : ''; ?> />
					</td>
				</tr>

				<tr>
					<th>
						<label for="settings_thumb2_width">
							<span class="alignleft">
								Thumbnail 2 width
							</span>
							<span class="alignright">
								<abbr class="required" title="required">*</abbr>
							</span>
						</label>
					</th>
					<td>
						<input class="regular-text" type="text" name="settings_thumb2_width" id="settings_thumb2_width" value="<?php echo (!empty($_POST['settings_thumb2_width'])) ? stripslashes($_POST['settings_thumb2_width']) : $portfolio->options['thumb2_width']; ?>" />
					</td>
				</tr>

				<tr>
					<th>
						<label for="settings_thumb2_square">
							<span class="alignleft">
								Create square thumbnail?
							</span>
						</label>
					</th>
					<td>
						<input type="checkbox" name="settings_thumb2_square" id="settings_thumb2_square" value="1"<?php echo ($portfolio->options['thumb2_square'] == '1') ? ' checked="checked"' : ''; ?> />
					</td>
				</tr>

		
			</table>
			

		    <p class="submit">
				<input type="submit" class="button-primary" name="settings_save" id="settings_save" value="Save Changes" />
		    </p>

		</form>
		</div>

<?php

}


function portfolioSettingsSave() {
	
	global $portfolio;
	
	$errors = array();
	

	if (!empty($_POST['settings_thumb1_width'])) :
		if (is_numeric($_POST['settings_thumb1_width'])) :
			$thumb1_width = $_POST['settings_thumb1_width'];
		else :
			$errors = '\'Thumbnail 1 width\': only numbers are allowed';
		endif;
	else :
		$errors[] = '\'Thumbnail 1 width\' is a required field';
	endif;


	if (!empty($_POST['settings_thumb2_width'])) :
		if (is_numeric($_POST['settings_thumb2_width'])) :
			$thumb2_width = $_POST['settings_thumb2_width'];
		else :
			$errors = '\'Thumbnail 2 width\': only numbers are allowed';
		endif;
	else :
		$errors[] = '\'Thumbnail 2 width\' is a required field';
	endif;


	if (!empty($_POST['settings_thumb1_square'])) :
		$thumb1_square = '1';
	else :
		$thumb1_square = '0';
	endif;


	if (!empty($_POST['settings_thumb2_square'])) :
		$thumb2_square = '1';
	else :
		$thumb2_square = '0';
	endif;


	// If there are no errors: save options, else restore
	if (empty($errors)) :
		
		// prepare options here
		$portfolio->options['debug'] = $_POST['settings_debug'];
		$portfolio->options['thumb1_width'] = $thumb1_width;
		$portfolio->options['thumb2_width'] = $thumb2_width;
		$portfolio->options['thumb1_square'] = $thumb1_square;
		$portfolio->options['thumb2_square'] = $thumb2_square;
	
		update_option('portfolio_options', $portfolio->options);
		$message = '
			<div id="message" class="updated">
				<p>
					<strong>Settings saved.</strong>
				</p>
			</div>';
		portfolioSettings($message);
	else :
		$message = '
			<div id="message" class="error">
				<ul>';
		foreach ($errors as $e) :
			$message .= '<li>' . $e . '</li>';
		endforeach;

		$message .= '
				</ul>
			</div>';
		portfolioSettings($message);
	endif;
}

?>