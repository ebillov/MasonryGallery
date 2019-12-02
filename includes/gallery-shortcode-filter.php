<?php
/*
Codes below are intended for filter hook on Wordpress default Gallery shortcode
*/

//Exit on unecessary file requests
defined('ABSPATH') or exit;

function simple_masonry_gallery_filter($output, $attr, $instance){
	
	//Run if masonry attribute is set to true
	if($attr['masonry'] == 'true'){
		
		//The "link" attribute is not used for now. Perhaps in future updates.
		$atts = shortcode_atts( array(
			'order'      => 'ASC',
			'orderby'    => 'menu_order ID',
			'size'       => 'thumbnail',
			'include'    => '',
			'exclude'    => '',
			'link'       => '',
			'init_output' => 12,
			'item_fetch' => 5,
			'load_method' => 'button',
			'columns' => 3
		), $attr, 'gallery' );
		
		//Place the ids
		$atts['include'] = $attr['ids'];
		
		if ( ! empty( $atts['include'] ) ) {
			
			//Begin get_posts query
			$_attachments = get_posts(array(
				'include' => $atts['include'],
				'post_status' => 'inherit',
				'post_type' => 'attachment',
				'post_mime_type' => 'image',
				'order' => $atts['order'],
				'orderby' => $atts['orderby']
			));
			
			//Define empty array
			$attachments = array();
			
			//Place all the ids in the array
			//The empty array now becomes an array of objects
			foreach ( $_attachments as $key => $val ) {
				
				//Place it as a variable object
				$attachment_object = $_attachments[$key];
				
				//Add the new alt_text object property
				$attachment_object->alt_text = get_post_meta($val->ID, '_wp_attachment_image_alt', true);
				
				//Get the medium size of the attachment
				$attachment_object->post_thumbnail = wp_get_attachment_image_src($val->ID, $atts['size']);
				
				//Get attachment url in full size
				$attachment_object->post_full_image = wp_get_attachment_url($val->ID);
				
				//Finally add it to the array
				$attachments[$val->ID] = $attachment_object;
				
			}
			
			//if no attachements found, return as empty.
			if( empty( $attachments ) ) {
				return;
			}
			
			ob_start();
			?>
				<style>
				.masonry_instance_<?php echo $instance; ?> .masonry_gallery_item {
					width: calc(<?php echo intval( 100 / intval($atts['columns']) ); ?>% - 10px);
					margin-bottom: 10px;
				}
				</style>
				<div class="masonry_gallery_wrapper masonry_instance_<?php echo $instance; ?>" data_all_ids="<?php echo implode(',', array_keys($attachments)); ?>" data_init_output="<?php echo intval($atts['init_output']); ?>" data_item_fetch="<?php echo intval($atts['item_fetch']); ?>" data_load_method="<?php echo $atts['load_method']; ?>" data_key_fetch="0">
					<?php
					$counter = 0;
					foreach( $attachments as $att_id => $attachment ):
					
					//Get the title, caption and description of the image
					$data_sub_html = ((!empty($attachment->post_title)) ? '<h4>' . $attachment->post_title . '</h4>' : '') . ((!empty($attachment->post_excerpt)) ? '<p>' . $attachment->post_excerpt . '</p>' : '') . ((!empty($attachment->post_content)) ? '<p>' . $attachment->post_content . '</p>' : '');
					
					?>
					<div class="masonry_gallery_item" data-src="<?php echo $attachment->post_full_image; ?>" data-sub-html="<?php echo $data_sub_html; ?>">
						<a href="<?php echo $attachment->post_thumbnail[0]; ?>" title="<?php echo $attachment->alt_text; ?>"><img src="<?php echo $attachment->post_thumbnail[0]; ?>" alt="<?php echo $attachment->alt_text; ?>"/></a>
					</div>
					<?php
					$counter++;
					if(intval($atts['init_output']) == $counter){
						break;
					}
					endforeach; ?>
				</div>
				<?php if($atts['load_method'] == 'button' && count($attachments) > intval($atts['init_output'])): ?>
				<div class="masonry_load_more_wrapper masonry_instance_<?php echo $instance; ?>">
					<a href="#" class="masonry_load_more_btn" data_thumbnail_size="<?php echo $atts['size']; ?>" data_button_instance="<?php echo $instance; ?>">Load More</a>
				</div>
				<?php endif; ?>
			<?php
			return ob_get_clean();
			
		}
		
	} else {
		return;
	}
}