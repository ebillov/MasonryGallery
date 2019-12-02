<?php
/**
* Plugin Name: Masonry Gallery
* Plugin URI: https://virson.wordpress.com/
* Description: A simple masonry gallery built on top of the default Wordpress [gallery] shortcode. Just place an attribute to enable masonry style like this [gallery ids="1,2,3,4,5" masonry="true"]. Available attributes: init_output, item_fetch, and columns that accepts all integer numbers. Please read the README.txt file inside the plugin for more details.
* Version: 1.0.1
* Author: Virson Ebillo
*/

//Exit on unecessary file requests
defined('ABSPATH') or exit;

//Define plugin path
define('MASONRY_GALLERY_PLUGIN_URL', plugin_dir_url( __FILE__ ));

//Add custom image size for masonry gallery thumbnails
//Needed a rework on how the thumbnails should be regenerated again on plugin activation
/*
add_theme_support('post-thumbnails');
add_image_size('masonry_thumb_size', 300, 380);
*/

//Add the filter hook to modify wordpress default gallery shortcode
require('includes/gallery-shortcode-filter.php');
add_filter('post_gallery', 'simple_masonry_gallery_filter', 10, 3);

//Also include an action hook to add the masonry gallery shortcode attribute in the media modal box
require('includes/gallery-media-modal-settings.php');
add_action('print_media_templates', 'simple_masonry_gallery_media_modal_settings');

//Add the script before the </body> tag
add_action('wp_footer', function(){
	
	//Get the current post object
	global $post;
	
	//Detect if the shortcode is placed in the content
	if( has_shortcode( $post->post_content, 'gallery' ) ) {
		//Eneque masonry lib from Wordpress Core
		//Add jQuery just in case it was not loaded
		//imagesloaded is a dependecy for the masonry script
		wp_enqueue_script('jquery');
		wp_enqueue_script('imagesloaded');
		wp_enqueue_script('jquery-masonry');
		wp_enqueue_style(
			'masonry-template',
			MASONRY_GALLERY_PLUGIN_URL . '/css/masonry-template.css',
			array(),
			'1.0.1'
		);
		
		//Include the main js file
		include('includes/masonry-script.php');
	}
	
});

/*
Had to place the load more Ajax hooks in here. Otherwise, 401 error is immenent.
*/
require('includes/load-more-ajax.php');
add_action('wp_ajax_masonry_load_more', 'masonry_load_more_ajax'); //For admin query actions
add_action('wp_ajax_nopriv_masonry_load_more', 'masonry_load_more_ajax'); //For users that are not logged in