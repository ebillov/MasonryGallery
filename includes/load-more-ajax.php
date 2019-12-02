<?php
/*
Codes below are intended for the load more Ajax request
*/

//Exit on unecessary file requests
defined('ABSPATH') or exit;

//Define masonry ajax query
function masonry_load_more_ajax(){
	
	if($_POST['action'] = 'masonry_load_more'){
		
		//Begin get_posts query
		$query_attachments = get_posts(array(
			'include' => $_POST['query_ids'],
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
		));
		
		//Define empty array
		$output_attachments = array();
		
		//Place all the ids in the array
		//The empty array now becomes an array of objects
		foreach ( $query_attachments as $key => $val ) {
			
			//Place it as a variable object
			$attachment_object = $query_attachments[$key];
			
			//Add the new alt_text object property
			$attachment_object->alt_text = get_post_meta($val->ID, '_wp_attachment_image_alt', true);
			
			//Get the medium size of the attachment
			$attachment_object->post_thumbnail = wp_get_attachment_image_src($val->ID, $_POST['thumbnail_size']);
			
			//Get attachment url in full size
			$attachment_object->post_full_image = wp_get_attachment_url($val->ID);
			
			//Finally add it to the array
			$output_attachments[$val->ID] = $attachment_object;
			
		}
		
		//Create new object and place the values
		$new_object = new stdClass();
		$new_object->query_ids = $output_attachments;
		$new_object->instance = $_POST['instance'];
		$new_object->thumbnail_size = $_POST['thumbnail_size'];
		
	}
	
	print_r( json_encode( $new_object ) );
	exit;
	
}