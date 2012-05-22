var portfolioCategories;
(function($) {

	portfolioCategories = {

		init : function() {
			var categories = $('.portfolio-projects-sortables');

			// close/open categories
			$('#portfolio-categories').children('.portfolio-category').children('.portfolio-category-name').click(function() {
				var category = $(this).parent();
				var category_inner = $(this).siblings('.portfolio-projects-sortables');
				
				if (!category.hasClass('closed')) {
					category_inner.sortable('disable');
					category.addClass('closed');
				} else {
					category.removeClass('closed');
					$(this).siblings('.portfolio-projects-sortables').sortable('enable').sortable('refresh');
				}
			});
			
			
			// doubleclick to create a project copy
			categories.find('.portfolio-project').live('dblclick', function() {
				$(this).clone().appendTo($(this).parent());
				$(this).next().attr('id', $(this).next().attr('id') + '_copy');
				$(this).next().find('.portfolio-project-delete').hide();
				portfolioCategories.setDrag();
			});
			
			
			// resize categories
			categories.each(function() {
				var category_width = $(this).innerWidth() - 30;
				var project_width = $(this).children('.portfolio-project:first').outerWidth() + 20;
				var projects_number = $(this).children('.portfolio-project').length;
				
				if (projects_number > 0) {
					var projects_per_row_max = (category_width - (category_width % project_width)) / project_width;
					var rows = Math.round(projects_number / projects_per_row_max) + 1;
					$(this).css('min-height', parseInt(rows * 48));
				}
			});


			portfolioCategories.setDrag();
			portfolioCategories.setSort();
			portfolioCategories.setDrop();

		},


		setDrag: function() {
			// drag projects around
			$('.portfolio-category-inner').children('.portfolio-project').draggable({
				connectToSortable: '.portfolio-projects-sortables',
				handle: '> .portfolio-project-top > .portfolio-project-title',
				distance: 2,
				helper: 'clone',
				zIndex: 5,
				refreshPositions: true,
				containment: 'document',
				start: function(event, ui) {
					portfolioCategories.fixWebkit(1);
				},
				stop: function(event, ui) {
					portfolioCategories.fixWebkit();
					$('.portfolio-category-inner .portfolio-project.deleting').remove();
				}
			});
		},
		
		
		setSort: function() {
			// sort projects within categories
			var categories = $('.portfolio-projects-sortables');
			
			categories.sortable({
				placeholder: 'portfolio-project-placeholder',
				items: '> .portfolio-project',
				handle: '> .portfolio-project-top > .portfolio-project-title',
				cursor: 'move',
				distance: 2,
				containment: 'document',
				start: function(event, ui) {
					portfolioCategories.fixWebkit(1);
					ui.item.css({'marginLeft':'','width':''});
				},
				stop: function(event, ui) {
					if (ui.item.hasClass('ui-draggable')) {
						ui.item.draggable('destroy');
					}
					if (ui.item.hasClass('deleting')) {
						ui.item.remove();
						return;
					}
					
					ui.item.css({'marginLeft':'','width':''});
					ui.item.parent().prev().find('img.ajax-feedback').css('visibility', 'visible');
					portfolioCategories.fixWebkit();
					portfolioCategories.resize(categories);
					
					portfolioCategories.save();
				},
				receive: function(event, ui) {
					if (!$(this).is(':visible')) {
						$(this).sortable('cancel');
						return;
					}					
				}
				
			// connect categories so we can drag from one category to another - but exclude closed ones
			}).sortable('option', 'connectWith', '.portfolio-projects-sortables').parent().filter('.closed').children('.portfolio-projects-sortables').sortable('disable');	
		},
		
		
		setDrop: function() {
			// set drag/drop targets
			$('.portfolio-category').droppable({
				tolerance: 'pointer',
				accept: '.portfolio-project',
				drop: function(event, ui) {
					ui.draggable.removeClass('deleting');
				},
				over: function(event, ui) {
					ui.draggable.addClass('deleting');
				},
				out: function(event, ui) {
					ui.draggable.removeClass('deleting');
					$('.portfolio-project-placeholder').show();
				}
			});
		},


		save: function() {
			var a = {
				'abspath': portfolio_wp,
				'portfolio-action': 'save-categories',
				'categories': []
			};
			
			$('.portfolio-category').each(function() {
				var ids = new Array();
				var projects = $(this).find('.portfolio-project:not(.deleting) .portfolio-project-id');
				$.unique(projects);
				
				projects.each(function(index) {
					ids[index] = $(this).val();
				});
				
				a['category[' + $(this).children('.portfolio-category-id:first').val() + ']'] = ids.join(',');
			});
			
			$.post(portfolio_ajax, a, function(data) {
				$('img.ajax-feedback').css('visibility', 'hidden');
			});
		},


		resize : function(categories) {
			categories.each(function() {
				var category_width = $(this).innerWidth() - 30;
				var project_width = $(this).children('.portfolio-project:first').outerWidth() + 20;
				var projects_number = $(this).children('.portfolio-project').length;
				
				if (projects_number > 0) {
					var projects_per_row_max = (category_width - (category_width % project_width)) / project_width;
					var rows = Math.round(projects_number / projects_per_row_max) + 1;
					$(this).css('min-height', parseInt(rows * 48));
				}
			});
		},

	    fixWebkit : function(n) {
	        n = n ? 'none' : '';
	        $('body').css({
				WebkitUserSelect: n,
				KhtmlUserSelect: n
			});
	    }
	};

	$(document).ready(function($) {
		portfolioCategories.init();
	});

})(jQuery);



function setCategoryLinks() {
	// toggle delete category link
	jQuery('.portfolio-category').mouseenter(function() {
		jQuery(this).find('.portfolio-category-meta').show();
	}).mouseleave(function() {
		jQuery(this).find('.portfolio-category-meta').hide();
	});
	
	
	// delete a category
	jQuery('.portfolio-category-delete').click(function() {
		var cat = jQuery(this).parent().parent().parent();
		var cat_name = cat.find('.portfolio-category-name h3').text();
		var cat_id = cat.children('.portfolio-category-id').val();
		var projects = cat.find('.portfolio-project');

		if (projects.length > 0) {
			alert('Category "' + cat_name + '" cannot be deleted because it is not empty. Remove the projects first.');
			
		} else {
		
			if (confirm('Are you sure you want to delete the category "' + cat_name + '"?')) {
			
				jQuery(this).parent().parent().find('.ajax-feedback').show();
				jQuery.post(portfolio_ajax, {'abspath': portfolio_wp, 'portfolio-action': 'delete-category', 'portfolio-category-id': cat_id}, function(data) {

					if (data == "not empty") {
						var message = '<div id="message" class="error"><p><strong>Category "' + cat_name + '" cannot be deleted because it is not empty. Remove the projects first.</strong></p></div>';
						if (jQuery('#wpbody-content .wrap #message').length == 0) {
							jQuery('#wpbody-content .wrap h2').after(message);
						} else {
							jQuery('#wpbody-content .wrap #message').fadeOut('fast', function() {
								jQuery('#wpbody-content .wrap #message').remove();
								jQuery('#wpbody-content .wrap h2').after(message);
							});
						}
					
					} else {
						cat.fadeOut('normal', function() {
							cat.remove();
							var message = '<div id="message" class="updated"><p><strong>Category "' + cat_name + '" deleted.</strong></p></div>';
							if (jQuery('#wpbody-content .wrap #message').length == 0) {
								jQuery('#wpbody-content .wrap h2').after(message);
							} else {
								jQuery('#wpbody-content .wrap #message').fadeOut('fast', function() {
									jQuery('#wpbody-content .wrap #message').remove();
									jQuery('#wpbody-content .wrap h2').after(message);
								});
							}
						})
					}
				
				});
			}
		}
		
		return false;
	});
	
	
	// rename a category
	jQuery('.portfolio-category-rename').click(function() {
		var cat = jQuery(this).parent().parent().parent();
		var cat_name = cat.find('.portfolio-category-name h3').text();
		var cat_id = cat.children('.portfolio-category-id').val();
		var new_name = prompt('Please enter the new name for category "' + cat_name + '":', '');
		
		if (new_name != null && new_name.length > 0 && new_name != cat_name) {
			
			var duplicate_exists = false;
			var categories = jQuery('.portfolio-category-name h3');
			
			categories.each(function() {
				if (jQuery(this).text() == new_name) duplicate_exists = true;
			});
			
			if (duplicate_exists) {
				while (duplicate_exists) {
					new_name = prompt('A category "' + new_name + '" already exists. Please enter a different new name for category "' + cat_name + '":', '');
					
					if (new_name == null) {
						return false;
					} else if (new_name.length > 0 && new_name != cat_name) {
						duplicate_exists = false;
						categories.each(function() {
							if (jQuery(this).text() == new_name) duplicate_exists = true;
						});
					}
				}
			}
			
			jQuery.post(portfolio_ajax, {'abspath': portfolio_wp, 'portfolio-action': 'rename-category', 'portfolio-category-id': cat_id, 'portfolio-category-new-name': new_name}, function(data) {
				
				if (data == "duplicate name") {
					var message = '<div id="message" class="error"><p><strong>A category with this name already exists.</strong></p></div>';
					if (jQuery('#wpbody-content .wrap #message').length == 0) {
						jQuery('#wpbody-content .wrap h2').after(message);
					} else {
						jQuery('#wpbody-content .wrap #message').fadeOut('fast', function() {
							jQuery('#wpbody-content .wrap #message').remove();
							jQuery('#wpbody-content .wrap h2').after(message);
						});
					}
				
				} else {
					cat.find('.portfolio-category-name h3').html(new_name + '<span><img alt="" src="images/wpspin_dark.gif" class="ajax-feedback" /></span>');
					
					var message = '<div id="message" class="updated"><p><strong>Category "' + cat_name + '" renamed to "' + new_name + '".</strong></p></div>';
					if (jQuery('#wpbody-content .wrap #message').length == 0) {
						jQuery('#wpbody-content .wrap h2').after(message);
					} else {
						jQuery('#wpbody-content .wrap #message').fadeOut('fast', function() {
							jQuery('#wpbody-content .wrap #message').remove();
							jQuery('#wpbody-content .wrap h2').after(message);
						});
					}
				}
				
				
			});
			
		}
		
		return false;
	});

	
}



function setProjectLinks() {
	
	// toggle delete project link
	jQuery('.portfolio-project').mouseenter(function() {
		jQuery(this).find('.portfolio-project-delete').show();
	}).mouseleave(function() {
		jQuery(this).find('.portfolio-project-delete').hide();
	});
	
	
	// delete a project, but only if there is at least one duplicate
	jQuery('.portfolio-project-delete').click(function() {
		var project = jQuery(this).parent().parent().parent();
		var project_id = project.children('.portfolio-project-id').val();
		var duplicates = jQuery('#portfolio-categories').find('.portfolio-project-id[value=' + project_id + ']');
		
		if (duplicates.length > 1) {
			var category = project.parent().parent();
			var category_id = project.parent().prev().prev().val();
			var project_title = project.find('h4').text();
			var category_title = category.find('h3').text();
			
			if (confirm('Are you sure you want to remove "' + project_title + '" from category "' + category_title + '"?')) {			
				category.find('img.ajax-feedback').css('visibility', 'visible');
			
				jQuery.post(portfolio_ajax, {'abspath': portfolio_wp, 'portfolio-action': 'delete-category-project', 'portfolio-category-id': category_id, 'portfolio-project-id': project_id}, function(data) {
					project.fadeOut('normal', function() {
						project.remove();
					});
					category.find('img.ajax-feedback').css('visibility', 'hidden');
				});
			}
			
		} else {
			alert('The project cannot be deleted. Each project must be in at least one category.')
		}
	});
	
}



jQuery(document).ready(function($) {

	// toggle add new category form
	$('#portfolio-categories-add-new').click(function() {
		$('#portfolio-categories-add').toggle();
	});
	
	
	// add new category
	$('#portfolio-categories-add').submit(function() {
		
		if ($('#portfolio-categories-add input[type=text]').val() != '') {
			$('#portfolio-categories-add img').show();
		
			$.post(portfolio_ajax, $('#portfolio-categories-add').serialize(), function(data) {
				$('#portfolio-categories-add img').hide();
			
				if (data == 'category exists') {
					$('#portfolio-categories-add-response').html('A category with that name already exists.');
				} else {
					$('#portfolio-categories-add-response').html('');
					$('#portfolio-categories').append(data);
					portfolioCategories.init();
				
					$('#portfolio-categories-add input[type=text]').val('');
				
					var message = '<div id="message" class="updated"><p><strong>Category "' + $(data).find('.portfolio-category-name h3').text() + '" added.</strong></p></div>';
					if ($('#wpbody-content .wrap #message').length == 0) {
						$('#wpbody-content .wrap h2').after(message);
					} else {
						$('#wpbody-content .wrap #message').fadeOut('fast', function() {
							$('#wpbody-content .wrap #message').remove();
							$('#wpbody-content .wrap h2').after(message);
						});
					}
					
					setCategoryLinks();

				}
			});
		}
		
		return false;
	});
	

	// set delete and rename links for categories
	setCategoryLinks();
	
	// set delete links for projects
	setProjectLinks();

});