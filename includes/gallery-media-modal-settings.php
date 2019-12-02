<?php
/*
Codes below are intended for adding the masonry settings right inside the media modal
*/

//Exit on unecessary file requests
defined('ABSPATH') or exit;

function simple_masonry_gallery_media_modal_settings(){
	?>
	<script type="text/html" id="tmpl-masonry-gallery-settings">
		<label class="setting">
			<span>Enable Masonry?</span>
			<select id="masonry_trigger" data-setting="masonry">
				<option value="false">No</option>
				<option value="true">Yes</option>
			</select>
		</label>
		<div id="masonry_settings">
			<label class="setting">
				<span style="text-align: left; font-weight: 600;">Initial Output</span><br />
				<span style="text-align: left; margin-bottom: 10px;">Initial gallery items output. Default: 12.</span><br />
				<input style="float: left; max-width: 60px;" type="number" data-setting="init_output"/>
			</label>
			<div style="clear: both"></div>
			<label class="setting">
				<span style="text-align: left; font-weight: 600;">Item Load</span><br />
				<span style="text-align: left; margin-bottom: 10px;">Number of items to fetch when pressing load more button. Default: 5.</span><br />
				<input style="float: left; max-width: 60px;" type="number" data-setting="item_fetch"/>
			</label>
			<div style="clear: both"></div>
		</div>
	</script>

	<script>
	/**
	* Custom Gallery Setting
	*/
	//Select the node that will be observed for mutations
	body_node = document.querySelector('body');
	
	//Options for the observer (which mutations to observe)
	var config = { attributes: true, childList: true, subtree: true },
		
		//Define the callback
		callback = function(mutationsList) {
			for(var mutation of mutationsList) {
				
				//Search for the id string
				var target_id = mutation.target.id,
					target_id_index = target_id.indexOf('__wp-uploader-id');
				
				//If found, run the code
				if(target_id_index === 0){
					
					//Only run if selector was found
					if(jQuery('#' + target_id).find('#' + wp.media.gallery.frame.el.id ).length > 0){
						
						//Get the string ID's
						var added_node_id = jQuery('#' + target_id).find('#' + wp.media.gallery.frame.el.id )[0].id,
							modal_frame_id = wp.media.gallery.frame.el.id;
						
						//If it matches the two ID's manipulate the data fields
						if(added_node_id === modal_frame_id) {
							//Get the options
							var masonry_obj_options = wp.media.gallery.frame.options.selection.gallery.attributes,
								masonry = masonry_obj_options.masonry,
								init_output = masonry_obj_options.init_output,
								item_fetch = masonry_obj_options.item_fetch;
								
							//Set the values
							jQuery('#' + modal_frame_id).find('#masonry_trigger').val(masonry.toString());
							jQuery('#' + modal_frame_id).find('#masonry_settings input[data-setting="init_output"]').val(init_output);
							jQuery('#' + modal_frame_id).find('#masonry_settings input[data-setting="item_fetch"]').val(item_fetch);
						}
						
						//Change event for the masonry options
						jQuery('#' + modal_frame_id).find('#masonry_trigger').off('change').on('change', function(e){
							if(jQuery(this).val() == 'true'){
								jQuery('#' + modal_frame_id).find('#masonry_settings').show();
							} else {
								jQuery('#' + modal_frame_id).find('#masonry_settings').hide();
							}
						});
						jQuery('#' + modal_frame_id).find('#masonry_trigger').trigger('change');
						
					}

				}
				
			}
		};
	
	jQuery(document).ready(function(){
		
		// Create an observer instance linked to the callback function
		var observer = new MutationObserver(callback);
		
		// Start observing the target node for configured mutations
		observer.observe(body_node, config);
		
		//jQuery('#__wp-uploader-id-' + wp.media.gallery.defaults.id)
		
		var media = wp.media;

		// Wrap the render() function to append controls
		media.view.Settings.Gallery = media.view.Settings.Gallery.extend({
			render: function() {
				media.view.Settings.prototype.render.apply( this, arguments );
				
				// Append the custom template
				this.$el.append( media.template( 'masonry-gallery-settings' ) );
				
				//Save the setting
				media.gallery.defaults.masonry = false;
				media.gallery.defaults.init_output = 12;
				media.gallery.defaults.item_fetch = 5;
				
				//Apply updates to settings
				this.update.apply( this, [
					'masonry',
					'init_output',
					'item_fetch'
				] );
				
				return this;
			}
		} );
	});
	</script>
	<?php
}