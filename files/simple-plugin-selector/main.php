<?php
  // Topcode Website Services
  // Simple Plugin Selector

  defined( 'ABSPATH' ) or die( 'Direct access is not permitted' );

  define( 'SPS_PATH', plugin_dir_path( __FILE__ ) );
  // eg: SPS_MU_PLUGINS_PATH = '/home/user/public_html/domain/wp-content/mu-plugins/';

  define( 'SPS_URL', plugin_dir_url( __FILE__ ) );
  // eg: SPS_URL = 'http://www.domain.com/wp-content/mu-plugins/simple-plugin-selector/';

  // load translations
  add_action( 'init', 'sps_init' );
  function sps_init() {
    load_plugin_textdomain( 'simple-plugin-selector', false, SPS_PATH.'languages/' );
  }

  // register scripts
  add_action( 'wp_loaded', 'sps_register_assets' );
  function sps_register_assets() {
    global $sps_version;
    // wp_register_script( $handle, $src, $deps = array(), $ver, $in_footer );
    // $src = full url or path relative to wordpress root
    wp_register_script( 'sps_script', SPS_URL.'js/admin.js', array(), $sps_version );
    wp_register_style( 'sps_styles', SPS_URL.'css/admin.css', array(), $sps_version );
  }

  // enqueue scripts
  // no need to repeat src
  add_action( 'admin_enqueue_scripts', 'sps_admin_enqueue_assets' );
  function sps_admin_enqueue_assets () {
    wp_enqueue_script( 'sps_script' );
    wp_enqueue_style( 'sps_styles' );
  }

  // includes
  include_once( 'includes/functions.php' );
  include_once( 'includes/pages.php' );
  if( is_admin() ) {
    include_once( 'includes/plugins.php' );
    include_once( 'includes/settings.php' );
  } else {
    include_once( 'includes/filter.php' );
  }

  // add setting page to admin menu
  add_action( 'admin_menu', 'sps_register_menu_items' );
  function sps_register_menu_items() {
    // add_options_page( page_title, menu_title, capability, menu_slug, function);
    add_options_page ( __( 'Settings', 'simple-plugin-selector' ), __( 'Simple Plugin Selector', 'simple-plugin-selector' ), 'manage_options', 'simple-plugin-selector', 'sps_settings' );
  } // end function
