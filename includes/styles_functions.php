<?php
/**
 * Helper Functions
 *
 * @package    AMP Base\Styles Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
if( !class_exists( 'AMP_Base_Theme_Styles' ) ) {
	 /**
     * AMP_Base_Theme_Styles class
     * @since  1.0.0
     */
	class AMP_Base_Theme_Styles {
		
		public static $extra_style_before = '';
		public static $extra_style_after = '';
		public static $style_files = array();
		public static $printed_style = false;
		/**
		* Static function hooks
		* @access public
		* @return void
		* @since 1.0.0
		*/
		public static function hooks() {
			if (!is_user_logged_in()) {
				add_action('wp_enqueue_scripts', array(__CLASS__, 'dequeue_styles'), 9999);
			}
		}
		
		/**
		* Static function get_style_theme
		* @access public
		* @return void
		* @since 1.0.0
		*/
		public static function all_styles() {
			self::get_styles_files();
			self::get_boilerplate_styles();
			self::get_noscript_boilerplate_styles();
		}
		/**
		* Static function get_default_styles
		* @access public
		* @return void
		* @since 1.0.0
		*/
		public static function get_styles_files() {

			$extra_style_files = apply_filters('amp_base_style_files', array());
			
			$content_style = '';
			/* Append content of all styles on custom tag.
			foreach (self::$style_files as $style_src) {
				$content_style .= $wp_filesystem->get_contents($style_src);
			}
			*/
			foreach ($extra_style_files as $style_path) {
				$content_style .= file_get_contents($style_path);
			}

			$content_style = self::$extra_style_before.$content_style;
			$content_style = $content_style.self::$extra_style_after;

			/* Example: array('{$logo}' => 'logo_image.png') */
			$css_replacers = apply_filters('amp_base_css_replacers', array());
			foreach ($css_replacers as $css_var => $remplace) {
				$content_style = str_replace($css_var, $remplace, $content_style);
			}
			$content_style = AMP_Base_Converter::minify_css($content_style);
			echo '<style amp-custom>'.$content_style.'  </style>'."\n\n";
		}

		/**
		* Static function get_boilerplate_styles
		* @access public
		* @return void
		* @since 1.0.0
		*/
		public static function get_boilerplate_styles() {
			$extra_style_files = apply_filters('amp_base_boilerplate_style_files', array());
			
			$content_style = '';
			foreach ($extra_style_files as $style_path) {
				$content_style .= file_get_contents($style_path);
			}

			/* Example: array('{$logo}' => 'logo_image.png') */
			$css_replacers = apply_filters('amp_base_boilerplate_css_replacers', array());
			foreach ($css_replacers as $css_var => $remplace) {
				$content_style = str_replace($css_var, $remplace, $content_style);
			}
			$content_style = AMP_Base_Converter::strip_comments($content_style);
			echo '<style amp-boilerplate>'.$content_style.'</style>';
		}
		/**
		* Static function get_noscript_boilerplate_styles
		* @access public
		* @return void
		* @since 1.0.0
		*/
		public static function get_noscript_boilerplate_styles() {
			$extra_style_files = apply_filters('amp_base_noscript_boilerplate_style_files', array());
			
			$content_style = '';
			foreach ($extra_style_files as $style_path) {
				$content_style .= file_get_contents($style_path);
			}

			/* Example: array('{$logo}' => 'logo_image.png') */
			$css_replacers = apply_filters('amp_base_noscript_boilerplate_css_replacers', array());
			foreach ($css_replacers as $css_var => $remplace) {
				$content_style = str_replace($css_var, $remplace, $content_style);
			}
			$content_style = AMP_Base_Converter::strip_comments($content_style);
			echo '<noscript><style amp-boilerplate>'.$content_style.'</style></noscript>';
		}
		/**
		* Static function dequeue_styles
		* @access public
		* @return void
		* @since 1.0.0
		*/
		public static function dequeue_styles() {
			global $wp_styles;
		    foreach( $wp_styles->queue as $style ) {
		      self::$style_files[] =  $wp_styles->registered[$style]->src;
		    }
		    foreach ($wp_styles->registered as $handle => $data){
		    	if ('amp_theme_google_fonts' == $handle) {
		    		continue;
		    	}
		    	wp_deregister_style($handle);
				wp_dequeue_style($handle);
			}
		}	
	}
}
AMP_Base_Theme_Styles::hooks();