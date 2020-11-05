<?php
/**
*Plugin Name: Frequently Bought Together For Woocommerce
*Description: This plugin allows you to Create Combo of Products.
* Version: 1.0
* Copyright: 2019 
*/

if (!defined('ABSPATH')) {
    die('-1');
}
if (!defined('FBTFW_PLUGIN_NAME')) {
    define('FBTFW_PLUGIN_NAME', 'Frequently Bought Together For Woocommmerce');
}
if (!defined('FBTFW_PLUGIN_VERSION')) {
    define('FBTFW_PLUGIN_VERSION', '1.0.0');
}
if (!defined('FBTFW_PLUGIN_FILE')) {
    define('FBTFW_PLUGIN_FILE', __FILE__);
}
if (!defined('FBTFW_PLUGIN_DIR')) {
    define('FBTFW_PLUGIN_DIR',plugins_url('', __FILE__));
}
if (!defined('FBTFW_BASE_NAME')) {
    define('FBTFW_BASE_NAME', plugin_basename(FBTFW_PLUGIN_FILE));
}
if (!defined('FBTFW_DOMAIN')) {
    define('FBTFW_DOMAIN', 'fbtfw');
}



if (!class_exists('FBTFW')) {
    add_action('plugins_loaded', array('FBTFW', 'FBTFW_instance'));
    class FBTFW {

        protected static $FBTFW_instance;

        public static function FBTFW_instance() {
          if (!isset(self::$FBTFW_instance)) {
            self::$FBTFW_instance = new self();
            self::$FBTFW_instance->init();
            self::$FBTFW_instance->includes();
          }
          return self::$FBTFW_instance;
        }

        function init() {
          add_action( 'admin_enqueue_scripts', array($this, 'FBTFW_load_admin_script_style'));
          add_action( 'wp_enqueue_scripts',  array($this, 'FBTFW_load_script_style'));
          add_filter( 'plugin_row_meta', array( $this, 'FBTFW_plugin_row_meta' ), 10, 2 );
        }

        
        function FBTFW_load_admin_script_style() {
            wp_enqueue_style( 'FBTFW-back-style', FBTFW_PLUGIN_DIR . '/includes/css/back-style.css', false, '1.0.0' );
            $ocscreen = get_current_screen();
            if($ocscreen->id == 'product') {
              wp_enqueue_script('FBTFW-back-script', FBTFW_PLUGIN_DIR .'/includes/js/backend-script.js', array( 'jquery', 'select2'));
              wp_localize_script( 'ajaxloadpost', 'ajax_postajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
            }
        }

        
        function FBTFW_load_script_style() {
          wp_enqueue_style( 'FBTFW-front-style', FBTFW_PLUGIN_DIR . '/includes/css/front-style.css', false, '1.0.0' );
          wp_enqueue_script( 'FBTFW-front-script', FBTFW_PLUGIN_DIR . '/includes/js/front-script.js', false, '1.0.0' );
        }

        function includes() {
          include_once('admin/fbtfw-backend.php');
          include_once('front/fbtfw-front.php');
        }

        function FBTFW_plugin_row_meta( $links, $file ) {
            if ( FBTFW_BASE_NAME === $file ) {
                $row_meta = array(
                    'rating'    =>  '<a href="#" target="_blank"><img src="'.FBTFW_PLUGIN_DIR.'/includes/images/star.png" class="fbtfw_rating_div"></a>',
                );

                return array_merge( $links, $row_meta );
            }
            return (array) $links;
        } 
        
    }   
}
