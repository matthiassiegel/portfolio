function uploadComplete(event, queue_id, file, response, data) {
	
	var container = jQuery('#fileInput' + queue_id);
	var response = eval('(' + response + ')');

	// remove the progress bar
	container.children('.uploadifyProgress').fadeOut('fast', function() { jQuery(this).remove(); });
	
	// inject file info link
	container.append('<a href="#" id="fileInfoLink' + queue_id + '" class="fileInfoLink">Show Info</a>');
	
	// response.id is the ID of the database object. get all info and append it.
	jQuery.post(portfolio_ajax, {'abspath': portfolio_wp, 'portfolio-action': 'get-media-info', 'portfolio-media-id': response.id}, function(data) {
		container.append(data);
	});
	
	return false;
}

jQuery(document).ready(function($) {
	
	$('#fileInput').uploadify({
		'uploader' : portfolio_url + '/admin/js/uploadify.swf',
		'script' : portfolio_url + '/admin/includes/uploader.php',
		'auto' : true,
		'folder' : portfolio_media_dir,
		'buttonImg' : portfolio_url + '/admin/img/upload.png',
		'width' : 134,
		'height' : 24,
		'rollover' : true,
		'multi' : true,
		'simUploadLimit' : 99,
		'fileDataName' : 'media',
		'scriptData' : {'path': portfolio_media_dir, 'abspath': portfolio_wp},
		'onComplete' : uploadComplete
	});


	// delete media files
	$('.trash a').click(function() {
		var media = $(this).parent().parent().parent().parent();
		var media_id = media.find('.media-id').val();
		var media_type = media.find('.media-type').val();
		var link = media.find('a.row-title');

		if (confirm('Are you sure you want to delete the file \'' + link.text() + '\'?')) {

			$.post(portfolio_ajax, {'abspath': portfolio_wp, 'portfolio-action': 'delete-media', 'portfolio-media-id': media_id}, function(data) {
				media.css('background-color', '#ff0000');
				media.fadeOut('normal', function() {
					media.remove();
					
					$('#count-' + media_type).html(parseInt($('#count-' + media_type).html() - 1));
					$('#count-all').html(parseInt($('#count-all').html() - 1));

					if ($('.pagination-pagetotal').length > 0) {
						$('.pagination-pagetotal').html(parseInt($('.pagination-pagetotal').html() - 1));
						$('.pagination-total').html(parseInt($('.pagination-total').html() - 1));
					}
				});
			});

		}
		return false;
	});
	
	
	// attach projects to media files
	$('.media_projects').live('change', function() {
		var project_id = $(this).val();
		var td = $(this).parent();
		var media_id = td.children('.media_id').val();
		var i = this.selectedIndex;
		var project_name = this.options[i].text;
		
		if (!$('#media-' + media_id + '-attached-project-' + project_id).length > 0) {
			var project = '<div id="media-' + media_id + '-attached-project-' + project_id + '" class="media-attached-project"><input type="hidden" name="media_attached_projects[]" value="' + project_id + '" />' + project_name + '<a href="#" class="media-attached-remove">remove</a></div>';
		
			td.append(project);
		}
	});
	
	$('.media-attached-remove').live('click', function() {
		$(this).parent().css('background-color', '#ff0000').fadeOut('fast', function() {
			$(this).remove();
		});
		return false;
	});


	// info link for newly uploaded files
	$('.fileInfoLink').live('click', function() {
		var link = $(this), info = link.parent().children('.portfolio-media-edit-small');
		
		info.slideToggle();
		link.html() == 'Show Info' ? link.html('Hide Info') : link.html('Show Info');
		return false;
	});
	
	
	// AJAX media edit for save
	$('.portfolio-media-edit-small .portfolio-media-editform').live('submit', function(event) {
		event.preventDefault();
		
		var media_id = $('.portfolio-media-edit-small .portfolio-media-editform .media-item > .media_id').val();
		var form = $(this);
		var spinner = form.find('.media_update_feedback img');
		var feedback = form.find('.media_update_feedback > img + div');
		var content = form.serializeArray();
		var content = $.toJSON(content);

		$.post(portfolio_ajax, {'abspath': portfolio_wp, 'portfolio-action': 'save-media-info', 'portfolio-media-id': media_id, 'portfolio-media-content': content}, function(data) {
			spinner.show();
			if (data == 'success') {
				spinner.hide();
				feedback.removeClass('error').addClass('success').html('File information saved!').show();
			} else {
				spinner.hide();
				feedback.html(data).removeClass('success').addClass('error').show();
			}
		});
	});

});