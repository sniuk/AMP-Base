<?php
/**
 * Plugin Name:     AMP Base
 * Plugin URI:      @todo
 * Description:     This is an plugin that allows the compatibility of AMP (Accelerated Mobile Pages) to be used in WordPress Themes with great ease. The whole website will benefit from the advantages of AMP. 
 * Version:         1.0.0
 * Author:          sniuk
 * Author URI:      http://www.netmdp.com
 * Text Domain:     amp-base
 *
 * @package         sniuk\AMP Base
 * @author          Sebastian Robles
 * @author          Esteban Truelsegaard
 * @copyright       Copyright (c) 2017
 *
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}
// Plugin version
if(!defined('AMP_BASE_VER')) {
    define('AMP_BASE_VER', '1.0.0' );
} 

if( !class_exists( 'AMP_Base' ) ) {

	
	
    /**
     * Main AMP_Base class
     *
     * @since       1.0.0
     */
    class AMP_Base {

        /**
         * @var         $instance If this class was initiated or not.
         * @since       1.0.0
         */
        private static $initiated;


        /**
         * It initiate all features of AMP Base
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public static function init() {
            if(!self::$initiated) {
                self::setup_constants();
                self::includes();
                self::load_textdomain();
                self::hooks();
            }
            self::$initiated = true;
        }


        /**
         * Setup plugin constants
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
       public static function setup_constants() {
			// Plugin root file
			if(!defined('AMP_BASE_ROOT_FILE')) {
				define('AMP_BASE_ROOT_FILE', __FILE__ );
			}
            // Plugin path
			if(!defined('AMP_BASE_DIR')) {
				define('AMP_BASE_DIR', plugin_dir_path( __FILE__ ) );
			}
            // Plugin URL
			if(!defined('AMP_BASE_URL')) {
				define('AMP_BASE_URL', plugin_dir_url( __FILE__ ) );
			}

        }


        /**
         * Include necessary files
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
         public static function includes() {
            // Include scripts
			require_once AMP_BASE_DIR . 'includes/scripts.php';
			require_once AMP_BASE_DIR . 'includes/settings.php';
            require_once AMP_BASE_DIR . 'includes/functions.php';
            require_once AMP_BASE_DIR . 'includes/template_functions.php';
            require_once AMP_BASE_DIR . 'includes/styles_functions.php';
            require_once AMP_BASE_DIR . 'includes/scripts_functions.php';
            require_once AMP_BASE_DIR . 'includes/converter.php';
            require_once AMP_BASE_DIR . 'includes/comments.php';
            require_once AMP_BASE_DIR . 'includes/post_contents.php';
           
            if( !class_exists( 'amp_base_content' ) ) {
    
                require_once AMP_BASE_DIR . 'includes/amp-libs/utils/class-amp-dom-utils.php';
                require_once AMP_BASE_DIR . 'includes/amp-libs/sanitizers/class-amp-base-sanitizer.php';
                require_once AMP_BASE_DIR . 'includes/amp-libs/embeds/class-amp-base-embed-handler.php';


                require_once AMP_BASE_DIR . 'includes/amp-libs/utils/class-amp-html-utils.php';
                require_once AMP_BASE_DIR . 'includes/amp-libs/utils/class-amp-string-utils.php';

                require_once AMP_BASE_DIR . 'includes/amp-libs/class-amp-content.php';

                require_once AMP_BASE_DIR . 'includes/amp-libs/sanitizers/class-amp-style-sanitizer.php';
                require_once AMP_BASE_DIR . 'includes/amp-libs/sanitizers/class-amp-blacklist-sanitizer.php';
                require_once AMP_BASE_DIR . 'includes/amp-libs/sanitizers/class-amp-img-sanitizer.php';
                require_once AMP_BASE_DIR . 'includes/amp-libs/sanitizers/class-amp-video-sanitizer.php';
                require_once AMP_BASE_DIR . 'includes/amp-libs/sanitizers/class-amp-iframe-sanitizer.php';
                require_once AMP_BASE_DIR . 'includes/amp-libs/sanitizers/class-amp-audio-sanitizer.php';

                require_once AMP_BASE_DIR . 'includes/amp-libs/embeds/class-amp-twitter-embed.php';
                require_once AMP_BASE_DIR . 'includes/amp-libs/embeds/class-amp-youtube-embed.php';
                require_once AMP_BASE_DIR . 'includes/amp-libs/embeds/class-amp-instagram-embed.php';
                require_once AMP_BASE_DIR . 'includes/amp-libs/embeds/class-amp-vine-embed.php';
                require_once AMP_BASE_DIR . 'includes/amp-libs/embeds/class-amp-facebook-embed.php';

                require_once AMP_BASE_DIR . 'includes/amp-libs/utils/class-amp-image-dimension-extractor.php';


            }
        }


        /**
         * Run action and filter hooks
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         *
         */
         public static function hooks() {
            // Register settings
           
        }
		
        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
         public static function load_textdomain() {
            // Set filter for language directory
            $lang_dir = AMP_BASE_DIR . '/languages/';
            $lang_dir = apply_filters( 'amp_base_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'amp-base' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'amp-base', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/amp-base/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/amp-base/ folder
                load_textdomain('amp-base', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/amp-base/languages/ folder
                load_textdomain('amp-base', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain('amp-base', false, $lang_dir );
            }
        }

    }
} // End if class_exists check

add_action( 'plugins_loaded', array('AMP_Base', 'init') ,999);

