<?php
/**
 * Helper Functions
 *
 * @package    AMP Base\Posts Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
if( !class_exists( 'AMP_Base_Theme_Posts' ) ) {
	 /**
     * AMP_Base_Theme_Posts class
     * It class allow to the themes use filters and actions to use AMP features.
     * @since  1.0.0
     */
	class AMP_Base_Theme_Posts {
		
		/**
		* Static function hooks
		* @access public
		* @return void
		* @since 1.0.0
		*/
		public static function hooks() {
			add_filter('the_content', array(__CLASS__, 'get_post_atributes' ), 9999, 1);
			add_filter('post_thumbnail_html', array(__CLASS__, 'post_thumbnail_html'), 89, 1);
		}

		/**
		* Static function get_post_scripts_styles
		* @access public
		* @return $html_content String with html of post content.
		* @since 1.0.0
		*/
		public static function get_post_atributes($html_content) {
			global $content_width;

			$amp_content = new amp_base_content($html_content,
				apply_filters( 'amp_base_content_embed_handlers', array(
					'amp_base_twitter_embed_handler' => array(),
					'amp_base_youTube_embed_handler' => array(),
					'amp_base_instagram_embed_handler' => array(),
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
			if (!AMP_Base_Theme_Styles::$printed_style) {
				foreach ($amp_content->get_amp_styles() as $selector => $atributes) {
					AMP_Base_Theme_Styles::$extra_style_before .= $selector.'{';
					foreach ($atributes as $key => $value) {
						AMP_Base_Theme_Styles::$extra_style_before .= $value.';';
					}
					AMP_Base_Theme_Styles::$extra_style_before .= '}';
				}
				AMP_Base_Theme_Styles::$printed_style = true;
			}
			
			foreach ($amp_content->get_amp_scripts() as $name => $src) {
				if (empty(AMP_Base_Theme_Scripts::$script_to_use[$name])) {
					AMP_Base_Theme_Scripts::$script_to_use[$name] = true;
				}
			}
			$content_amp_string = $amp_content->get_amp_content();
			if (!empty($content_amp_string)) {
				$html_content = $content_amp_string;
			}
			
			return $html_content;
		}
		/**
		* Static function post_scripts_styles
		* @access public
		* @since 1.0.0
		*/
		public static function post_scripts_styles($more_link_text = null, $strip_teaser = false) {
	        $content = get_the_content( $more_link_text, $strip_teaser );
	        $content = apply_filters( 'the_content', $content );
	        return true;
		}

		/**
		* Static function post_thumbnail_html
		* @access public
		* @return void
		* @since 1.0.0
		*/
		public static function post_thumbnail_html($html) {
			$html = apply_filters('amp_base_get_content', $html);
			return $html;
		}
		
	
	}
}
AMP_Base_Theme_Posts::hooks();