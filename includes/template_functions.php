<?php
/**
 * Helper Functions
 *
 * @package    AMP Base\Theme Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
if( !class_exists( 'AMP_Base_Theme_Functions' ) ) {
	 /**
     * AMP_Base_Theme_Functions class
     * It class allow to the themes use filters and actions to use AMP features.
     * @since  1.0.0
     */
	class AMP_Base_Theme_Functions {
		
		/**
		* Static function hooks
		* @access public
		* @return void
		* @since 1.0.0
		*/
		public static function hooks() {
			add_filter('amp_base_html_tag', array(__CLASS__, 'get_html_tag'), 10, 1);
			add_action('get_header', array(__CLASS__, 'get_script_styles') );
			add_action('wp_head', array(__CLASS__, 'head') );
			add_filter('amp_base_get_content', array(__CLASS__, 'content'), 10, 1);

			
		}
		
		/**
		* Static function get_html_type
		* @access public
		* @return void
		* @since 1.0.0
		*/
		public static function get_html_tag($html_tag) {
			$html_tag = 'html amp ';
			return $html_tag;
		}
		/**
		* Static function head
		* @access public
		* @return void
		* @since 1.0.0
		*/
		public static function head() {
			AMP_Base_Theme_Styles::all_styles();
		}
		/**
		* Static function amp_content
		* @access public
		* @return $html_content String with html of post content.
		* @since 1.0.0
		*/
		public static function content($html_content) {
			global $content_width;
			$html_content = self::get_amp_content($html_content);
			return $html_content;
		}
		/**
		* Static function get_amp_content
		* @access public
		* @return $html_content String with html of post content.
		* @since 0.2.0
		*/
		public static function get_amp_content($html_content) {
			global $content_width;
			/** 
			* @deprecated 
			if (!self::$styles_scripts_added && !self::$ignore_enqueue) { 
				return $html_content;
			}
			*/

			$amp_content = new amp_base_content($html_content,
				apply_filters( 'amp_base_content_embed_handlers', array(
					'amp_base_twitter_embed_handler' => array(),
					'amp_base_youTube_embed_handler' => array(),
					'amp_base_instagram_embed_Handler' => array(),
					'amp_base_vine_embed_handler' => array(),
					'amp_base_facebook_embed_handler' => array(),
				), null ),
				apply_filters( 'amp_base_content_sanitizers', array(
					 'amp_base_style_sanitizer' => array(),
					 'amp_base_blacklist_sanitizer' => array(),
					 'amp_base_img_sanitizer' => array(),
					 'amp_base_video_sanitizer' => array(),
					 'amp_base_audio_sanitizer' => array(),
					 'amp_base_iframe_sanitizer' => array(
						 'add_placeholder' => true,
					 ),
				), null ),
				array(
					'content_max_width' => $content_width,
				)
			);
			$content_amp_string = $amp_content->get_amp_content();
			if (!empty($content_amp_string)) {
				$html_content = $content_amp_string;
			}
			return $html_content;
		}
		/**
		* Static function get_script_styles
		* @access public
		* @return void
		* @since 1.0.0
		*/
		public static function get_script_styles() {
			while (have_posts()) : the_post();
			  	AMP_Base_Theme_Posts::post_scripts_styles();        
			endwhile;
		}
	
	}
}
AMP_Base_Theme_Functions::hooks();