jQuery(document).ready(function($) {
	
	// delete projects
	$('.trash a').click(function() {
		var project = $(this).parent().parent().parent().parent();
		var project_id = project.find('.project-id').val();
		var project_status = project.find('.project-status').val();
		var link = project.find('a.row-title');
		
		if (confirm('Are you sure you want to delete the project \'' + link.text() + '\'?')) {
			
			$.post(portfolio_ajax, {'abspath': portfolio_wp, 'portfolio-action': 'delete-project', 'portfolio-project-id': project_id}, function(data) {
				project.css('background-color', '#ff0000');
				project.fadeOut('normal', function() {
					project.remove();
					
					$('#count-' + project_status).html(parseInt($('#count-' + project_status).html() - 1));
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
	
	
	// add a new category option
	$('#project_add_category').click(function(event) {
		event.preventDefault();
		
		var new_category = prompt('Name of new category:');
		if (new_category.length > 0) {


				$.post(portfolio_ajax, {'abspath': portfolio_wp, 'portfolio-action': 'add-category-option', 'portfolio-category-name': new_category}, function(data) {

					if (data == 'category exists') {
						$('#project_add_category_feedback').html('A category with that name already exists.');
					} else {
						$('#project_add_category_feedback').html('');
						
						var option = $('<option />').attr('value', data).html(new_category);
						$('#project_categories').append(option);

						var message = '<div id="message" class="updated"><p><strong>Category "' + new_category + '" added.</strong></p></div>';
						if ($('#wpbody-content .wrap #message').length == 0) {
							$('#wpbody-content .wrap h2').after(message);
						} else {
							$('#wpbody-content .wrap #message').fadeOut('fast', function() {
								$('#wpbody-content .wrap #message').remove();
								$('#wpbody-content .wrap h2').after(message);
							});
						}

					}
				});
			
		}
	});
	
	
	// Thickbox for image 1
	jQuery('#portfolio-thickbox-image1 table tr.thickbox-media').live('click', function() {
		var id = jQuery(this).find('.media-id').val();
		var name = jQuery(this).find('td strong').text();
		var image = jQuery(this).find('td.media-icon img').attr('src');
		var extension = jQuery(this).find('td span').text();
		var td = jQuery('#portfolio-project-image-1');
		var existing = td.find('.portfolio-project-image');
		
		if (existing.length > 0) {
			existing.remove();
		}
		td.prepend('<div class="portfolio-project-image"><input type="hidden" name="project_image_1" value="' + id + '" /><img src="' + image + '" alt="" />' + name + '<br />' + extension + '<br /><a href="#">Delete</a></div>');
		td.find('a.thickbox').text('Replace');
		td.find('.portfolio-project-image a').click(function() {
			jQuery(this).parent().css('background-color', '#ff0000').fadeOut('fast', function() {
				jQuery(this).remove();
				td.find('a.thickbox').text('Add');
			});
			return false;
		});
		td.find('.portfolio-project-image').mouseenter(function() {
			jQuery(this).find('a').show();
		}).mouseleave(function() {
			jQuery(this).find('a').hide();
		});
		
		jQuery('#TB_overlay').fadeOut('fast');
		jQuery('#TB_window').fadeOut('fast', function() {
			jQuery('#TB_overlay').remove();
			jQuery('#TB_window').remove();
		});
	});


	// Thickbox for image 2
	jQuery('#portfolio-thickbox-image2 table tr.thickbox-media').live('click', function() {
		var id = jQuery(this).find('.media-id').val();
		var name = jQuery(this).find('td strong').text();
		var image = jQuery(this).find('td.media-icon img').attr('src');
		var extension = jQuery(this).find('td span').text();
		var td = jQuery('#portfolio-project-image-2');
		var existing = td.find('.portfolio-project-image');
		
		if (existing.length > 0) {
			existing.remove();
		}
		td.prepend('<div class="portfolio-project-image"><input type="hidden" name="project_image_2" value="' + id + '" /><img src="' + image + '" alt="" />' + name + '<br />' + extension + '<br /><a href="#">Delete</a></div>');
		td.find('a.thickbox').text('Replace');
		td.find('.portfolio-project-image a').click(function() {
			jQuery(this).parent().css('background-color', '#ff0000').fadeOut('fast', function() {
				jQuery(this).remove();
				td.find('a.thickbox').text('Add');
			});
			return false;
		});
		td.find('.portfolio-project-image').mouseenter(function() {
			jQuery(this).find('a').show();
		}).mouseleave(function() {
			jQuery(this).find('a').hide();
		});
		
		jQuery('#TB_overlay').fadeOut('fast');
		jQuery('#TB_window').fadeOut('fast', function() {
			jQuery('#TB_overlay').remove();
			jQuery('#TB_window').remove();
		});
	});


	// Thickbox for image 3
	jQuery('#portfolio-thickbox-image3 table tr.thickbox-media').live('click', function() {
		var id = jQuery(this).find('.media-id').val();
		var name = jQuery(this).find('td strong').text();
		var image = jQuery(this).find('td.media-icon img').attr('src');
		var extension = jQuery(this).find('td span').text();
		var td = jQuery('#portfolio-project-image-3');
		var existing = td.find('.portfolio-project-image');
		
		if (existing.length > 0) {
			existing.remove();
		}
		td.prepend('<div class="portfolio-project-image"><input type="hidden" name="project_image_3" value="' + id + '" /><img src="' + image + '" alt="" />' + name + '<br />' + extension + '<br /><a href="#">Delete</a></div>');
		td.find('a.thickbox').text('Replace');
		td.find('.portfolio-project-image a').click(function() {
			jQuery(this).parent().css('background-color', '#ff0000').fadeOut('fast', function() {
				jQuery(this).remove();
				td.find('a.thickbox').text('Add');
			});
			return false;
		});
		td.find('.portfolio-project-image').mouseenter(function() {
			jQuery(this).find('a').show();
		}).mouseleave(function() {
			jQuery(this).find('a').hide();
		});
		
		jQuery('#TB_overlay').fadeOut('fast');
		jQuery('#TB_window').fadeOut('fast', function() {
			jQuery('#TB_overlay').remove();
			jQuery('#TB_window').remove();
		});
	});


	// Thickbox for additional images
	jQuery('#portfolio-thickbox-images table tr.thickbox-media').live('click', function() {
		var id = jQuery(this).find('.media-id').val();
		var name = jQuery(this).find('td strong').text();
		var image = jQuery(this).find('td.media-icon img').attr('src');
		var extension = jQuery(this).find('td span').text();
		var td = jQuery('#portfolio-project-image-additional');
		
		td.find('a.thickbox').before('<div class="portfolio-project-image-additional"><input type="hidden" name="project_image_additional[]" value="' + id + '" /><img src="' + image + '" alt="" />' + name + '<br />' + extension + '<br /><a href="#">Delete</a></div>');
		td.find('.portfolio-project-image-additional a').click(function() {
			jQuery(this).parent().css('background-color', '#ff0000').fadeOut('fast', function() {
				jQuery(this).remove();
			});
			return false;
		});
		td.find('.portfolio-project-image-additional').mouseenter(function() {
			jQuery(this).find('a').show();
		}).mouseleave(function() {
			jQuery(this).find('a').hide();
		});
		
		jQuery('#TB_overlay').fadeOut('fast');
		jQuery('#TB_window').fadeOut('fast', function() {
			jQuery('#TB_overlay').remove();
			jQuery('#TB_window').remove();
		});
	});


	// Thickbox for other files
	jQuery('#portfolio-thickbox-other table tr.thickbox-media').live('click', function() {
		var id = jQuery(this).find('.media-id').val();
		var name = jQuery(this).find('td strong').text();
		var extension = jQuery(this).find('td span').text();
		var td = jQuery('#portfolio-project-files-other');
		
		td.find('a.thickbox').before('<div class="portfolio-project-other-file"><input type="hidden" name="project_other_file[]" value="' + id + '" />' + name + '<br />' + extension + '&nbsp;&nbsp;&nbsp;<a href="#">Delete</a></div>');
		td.find('.portfolio-project-files-other a').click(function() {
			jQuery(this).parent().css('background-color', '#ff0000').fadeOut('fast', function() {
				jQuery(this).remove();
			});
			return false;
		});
		td.find('.portfolio-project-other-file').mouseenter(function() {
			jQuery(this).find('a').show();
		}).mouseleave(function() {
			jQuery(this).find('a').hide();
		});
		
		jQuery('#TB_overlay').fadeOut('fast');
		jQuery('#TB_window').fadeOut('fast', function() {
			jQuery('#TB_overlay').remove();
			jQuery('#TB_window').remove();
		});
	});
	
	
	
	// Events for existing files
	jQuery('.portfolio-project-image a').click(function() {
		jQuery(this).parent().css('background-color', '#ff0000').fadeOut('fast', function() {
			jQuery(this).parent().parent().find('a.thickbox').text('Add');
			jQuery(this).remove();
		});
		return false;
	});
	jQuery('.portfolio-project-image').mouseenter(function() {
		jQuery(this).find('a').show();
	}).mouseleave(function() {
		jQuery(this).find('a').hide();
	});
	
	jQuery('.portfolio-project-image-additional a').click(function() {
		jQuery(this).parent().css('background-color', '#ff0000').fadeOut('fast', function() {
			jQuery(this).remove();
		});
		return false;
	});
	jQuery('.portfolio-project-image-additional').mouseenter(function() {
		jQuery(this).find('a').show();
	}).mouseleave(function() {
		jQuery(this).find('a').hide();
	});	

	jQuery('.portfolio-project-other-file a').click(function() {
		jQuery(this).parent().css('background-color', '#ff0000').fadeOut('fast', function() {
			jQuery(this).remove();
		});
		return false;
	});
	jQuery('.portfolio-project-other-file').mouseenter(function() {
		jQuery(this).find('a').show();
	}).mouseleave(function() {
		jQuery(this).find('a').hide();
	});

});