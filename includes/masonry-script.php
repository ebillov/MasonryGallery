<?php
//Exit on unecessary file requests
defined('ABSPATH') or exit;
?>
<link href="<?php echo MASONRY_GALLERY_PLUGIN_URL; ?>dist/css/lightgallery.css" rel="stylesheet">
<script src="<?php echo MASONRY_GALLERY_PLUGIN_URL; ?>lib/picturefill.min.js"></script>
<script src="<?php echo MASONRY_GALLERY_PLUGIN_URL; ?>lib/jquery.mousewheel.min.js"></script>
<script src="<?php echo MASONRY_GALLERY_PLUGIN_URL; ?>dist/js/lightgallery-all.min.js"></script>

<style>
.masonry_gallery_wrapper .masonry_gallery_item a img {
	margin: auto;
    display: block;
}
.masonry_load_more_wrapper {
	margin: 20px 0;
}
a.masonry_load_more_btn {
    background-color: #f5f5f5;
    color: #333333;
    display: block;
    font-weight: 600;
    padding: 10px 20px;
    text-transform: uppercase;
    border: 1px solid #bbbbbb;
    border-radius: 3px;
    max-width: 160px;
    text-align: center;
    margin: auto;
	transition: all 0.5s;
	-webkit-transition: all 0.5s;
	-moz-transition: all 0.5s;
	-ms-transition: all 0.5s;
}
a.masonry_load_more_btn:hover {
	background-color: #ffffff;
}
.lg-backdrop {
	z-index: 9999999991;
}
.lg-outer {
	z-index: 9999999992;
}
.lg-sub-html h4 {
    color: #ffffff;
    padding-bottom: 5px;
    font-size: 16px;
    font-weight: 600;
}
.lg-sub-html p {
    font-size: 14px;
    padding-bottom: 0px;
}
</style>
<script>
var masonry_ajax_url = '<?php echo admin_url("admin-ajax.php"); ?>',
	initialize_masonry_layout = function(destroy){
		
		if(destroy != true){
			var destroy = false;
		}
		//Iterate through the elements
		jQuery('.masonry_gallery_wrapper').each(function(e){
			
			//Destroy lightGallery instance
			if(destroy){
				jQuery(this).data('lightGallery').destroy(true);
			}
			
			//Initialize the lightGallery
			jQuery(this).lightGallery({
				selector: '.masonry_gallery_item',
				hash: false //Disables the hash url in the address bar
			});
			
			//Begin masonry layout
			var masonry_grid_wrapper = jQuery(this).imagesLoaded(function(){
				masonry_grid_wrapper.masonry({
					// set itemSelector so .grid-sizer is not used in layout
					itemSelector: '.masonry_gallery_item',
					// use element for option
					columnWidth: '.masonry_gallery_item',
					percentPosition: true,
					gutter: 10
				});
			});
		});
	};

jQuery(document).ready(function(){
	
	//Initialize masonry layout
	initialize_masonry_layout();
	
	//Get all the button instances for each shortcode displayed
	var masonry_instance_class = [];
	jQuery('.masonry_gallery_wrapper').each(function(e){
		masonry_instance_class.push('.masonry_instance_' + (e + 1));
	});
	
	//Join arrays in a single comma separated string
	masonry_instance_class = masonry_instance_class.join();
	
	//Target each load more button based on the masonry instance class
	jQuery(masonry_instance_class).find('.masonry_load_more_btn').off('click').on('click', function(e){
		e.preventDefault();
		jQuery(this).attr('style', 'pointer-events: none!important; opacity: 0.3!important;');
		
		//Get the button instance
		var button_target = jQuery(this),
			button_instance = button_target.attr('data_button_instance'),
			data_thumbnail_size = button_target.attr('data_thumbnail_size'),
		
			//Always get the first instance of the class that will target the main masonry gallery wrapper class
			main_gallery_wrapper_instance = jQuery(jQuery('.masonry_instance_' + button_instance)[0]),
			
			//Get the attributes
			all_item_ids = main_gallery_wrapper_instance.attr('data_all_ids').split(','),
			all_item_ids_query = main_gallery_wrapper_instance.attr('data_all_ids').split(','),
			init_output = parseInt(main_gallery_wrapper_instance.attr('data_init_output')),
			item_fetch = parseInt(main_gallery_wrapper_instance.attr('data_item_fetch')),
			key_fetch = parseInt(main_gallery_wrapper_instance.attr('data_key_fetch')),
			
			//Calculate the number of items and get the ceil quantity
			fetch_multiplier = Math.ceil((all_item_ids.length - init_output) / item_fetch),
			
			//Get the next query items based on the number of items to fetch
			next_query_items = all_item_ids_query.splice(((key_fetch * item_fetch) + init_output), item_fetch);
			
		//Include the Ajax preloader
		button_target.before('<div id="masonry_spinner_' + button_instance + '" class="masonry_spinner">');
		
		//Do the ajax request
		jQuery.ajax({
			url: masonry_ajax_url,
			type: 'POST',
			dataType: "json",
			data: {
				action: 'masonry_load_more',
				instance: button_instance,
				thumbnail_size: data_thumbnail_size,
				query_ids: next_query_items
			},
			error: function(data){
				if(data_1.statusText == 'error') {
					console.log(data);
				}
			},
			success: function(data){
				
				//Convert the object output to array of objects
				var attachments = jQuery.map(data.query_ids, function(value, index){
					return value;
				});
				
				//Remove ajax loader
				if(attachments.length > 0){
					jQuery('#masonry_spinner_' + data.instance).remove();
				} else {
					return;
				}
				
				//Loop through the items and add it as a single string
				items = '';
				for(i = 0; i < attachments.length; i++){
					
					data_sub_html = ((attachments[i].post_title != '') ? '<h4>' + attachments[i].post_title + '</h4>' : '') + 
						((attachments[i].post_excerpt != '') ? '<p>' + attachments[i].post_excerpt + '</p>' : '') +
						((attachments[i].post_content != '') ? '<p>' + attachments[i].post_content + '</p>' : '');
						
					items += '\
						<div class="masonry_gallery_item" data-src="' + attachments[i].post_full_image + '" data-sub-html="' + data_sub_html + '">\
							<a href="' + attachments[i].post_thumbnail[0] + '" title="' + attachments[i].alt_text + '"><img src="' + attachments[i].post_thumbnail[0] + '" alt="' + attachments[i].alt_text + '"/></a>\
						</div>';
				}
				
				//Had to do it this way so that it will work with Ajax
				//See notes: https://masonry.desandro.com/methods.html#appended
				$items = jQuery(items);
				
				//Start append and masonry layout
				main_gallery_wrapper_instance.append( $items ).masonry('appended', $items);
				
				//Had to initialize masonry layout again due to masonry layout inconsistencies
				//Reference of true to destroy lightGallery instance
				initialize_masonry_layout(true);
				
				//Incerement the key_fetch counter
				if(key_fetch < fetch_multiplier){
					main_gallery_wrapper_instance.attr('data_key_fetch', key_fetch + 1);
				}
				
				//Disable the load more button otherwise, just enable back the button
				if(key_fetch == (fetch_multiplier - 1)){
					button_target.remove();
				} else {
					button_target.removeAttr('style');
				}
				
			}
		});
		
	});
});
</script>