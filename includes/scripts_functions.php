<?php
/**
 * Helper Functions
 *
 * @package    AMP Base\Scripts Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
if( !class_exists( 'AMP_Base_Theme_Scripts' ) ) {
	 /**
     * AMP_Base_Theme_Styles class
     * @since  1.0.0
     */
	class AMP_Base_Theme_Scripts {
		
		public static $script_files = array();
		public static $script_to_use = array();

		/**
		* Static function init
		* @access public
		* @return void
		* @since 1.0.0
		*/
		public static function init() {
			self::$script_files = array(
				'amp-main' => 'https://cdn.ampproject.org/v0.js',
				'amp-form' => 'https://cdn.ampproject.org/v0/amp-form-0.1.js',
				'amp-sidebar' => 'https://cdn.ampproject.org/v0/amp-sidebar-0.1.js',
				'amp-mustache' => 'https://cdn.ampproject.org/v0/amp-mustache-0.1.js',
				'amp-ad' => 'https://cdn.ampproject.org/v0/amp-ad-0.1.js',
				'amp-audio' => 'https://cdn.ampproject.org/v0/amp-audio-0.1.js',
				'amp-analytics' => 'https://cdn.ampproject.org/v0/amp-analytics-0.1.js',
				'amp-accordion' => 'https://cdn.ampproject.org/v0/amp-accordion-0.1.js',
				'amp-carousel' => 'https://cdn.ampproject.org/v0/amp-carousel-0.1.js',
				'amp-install-serviceworker' => 'https://cdn.ampproject.org/v0/amp-install-serviceworker-0.1.js',
			);

			self::hooks();
		}
		/**
		* Static function hooks
		* @access public
		* @return void
		* @since 1.0.0
		*/
		public static function hooks() {
			add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'), 11 );
			add_filter('script_loader_tag', array(__CLASS__, 'add_script_attribute'), 10, 2);
			
			if (!is_user_logged_in()) {
				add_action('wp_enqueue_scripts', array(__CLASS__, 'dequeue_scripts'), 9999);

				/**
				*	AMP HTML uses a set of contributed but centrally managed and hosted custom elements to implement advanced functionality such as image galleries that might be found in an AMP HTML document. 
				*	While it does allow styling the document using custom CSS, it does not allow author written JavaScript beyond what is provided through the custom elements to reach its performance goals.
				*   @link  https://www.ampproject.org/docs/reference/spec
				*/
				remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
				remove_action( 'wp_print_styles', 'print_emoji_styles' ); 
			}

			
			
		}
		/**
		* Static function scripts_print
		* @access public
		* @return void
		* @since version
		*/
		public static function enqueue_scripts() {
			self::$script_to_use = apply_filters('amp_base_script_files', self::$script_to_use);
			foreach (self::$script_to_use as $name => $enqueue) {
				if ($enqueue && !empty(self::$script_files[$name])) {
					wp_register_script($name, self::$script_files[$name], array(), null, false); 
	       			wp_enqueue_script($name); 
				}

        	}

		}


		public static function dequeue_scripts() {
			global $wp_scripts;
			/**
			*	AMP HTML uses a set of contributed but centrally managed and hosted custom elements to implement advanced functionality such as image galleries that might be found in an AMP HTML document. 
			*	While it does allow styling the document using custom CSS, it does not allow author written JavaScript beyond what is provided through the custom elements to reach its performance goals.
			*   @link  https://www.ampproject.org/docs/reference/spec
			*/
		    $array = array();
		    // Runs through the queue scripts
		    foreach ($wp_scripts->queue as $handle) :
		    	if (!empty(self::$script_to_use[$handle])) {
		    		continue;
		    	}
		        $array[] = $handle;
		    endforeach;
		    wp_dequeue_script($array);

		    $array = array();
		    foreach ($wp_scripts->registered as $idenfier => $handle) :
		    	if (!empty(self::$script_to_use[$idenfier])) {
		    		continue;
		    	}
		        $array[] = $idenfier;
		    endforeach;
		    self::deregister_script($array);
		    wp_dequeue_script($array);
		   
		}
		/**
		* Static function deregister_script.
		* @access public
		* @return void
		* @since 1.0.0
		*/
		public static function deregister_script( $handle ) {
			$current_filter = current_filter();
			if ( 'wp-login.php' === $GLOBALS['pagenow'] && 'login_enqueue_scripts' !== $current_filter ) {
				$no = array(
					'jquery', 'jquery-core', 'jquery-migrate', 'jquery-ui-core', 'jquery-ui-accordion',
					'jquery-ui-autocomplete', 'jquery-ui-button', 'jquery-ui-datepicker', 'jquery-ui-dialog',
					'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-menu', 'jquery-ui-mouse',
					'jquery-ui-position', 'jquery-ui-progressbar', 'jquery-ui-resizable', 'jquery-ui-selectable',
					'jquery-ui-slider', 'jquery-ui-sortable', 'jquery-ui-spinner', 'jquery-ui-tabs',
					'jquery-ui-tooltip', 'jquery-ui-widget', 'underscore', 'backbone',
				);

				if ( in_array( $handle, $no ) ) {
					_doing_it_wrong( __FUNCTION__, $message, '3.6.0' );
					return;
				}
			}

			wp_scripts()->remove( $handle );
		}

		/**
		* Static function add_script_attribute
		* @access public
		* @return void
		* @since 1.0.0
		*/
		public static function add_script_attribute($tag, $handle) {
	
			if (!empty(self::$script_to_use[$handle])) {
				$tag = str_replace(" type='text/javascript'", '', $tag );
				$tag = str_replace( ' src', ' async src', $tag );
				if ($handle == 'amp-main') {
					return $tag;
				}
				if ($handle == 'amp-mustache') {
					$tag = str_replace( ' src', " custom-template='".$handle."' src", $tag );
				} else {
					$tag = str_replace( ' src', " custom-element='".$handle."' src", $tag );
				}

			}

		    return $tag;
		}
		
	}
}
AMP_Base_Theme_Scripts::init();