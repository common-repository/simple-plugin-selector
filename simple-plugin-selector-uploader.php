<?php
/*
Plugin Name:       Simple Plugin Selector
Plugin URI:        http://www.topcode.co.uk/developments/simple-plugin-selector/
Description:       Improve website performance by loading only the plugins needed for a page.
Version:           1.3.2
Requires at least: 6.0
Requires PHP:      7.4
Author:            lorro
Author URI:        http://www.topcode.co.uk/
License:           GPL v2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:       simple-plugin-selector
Domain Path:       /languages
*/
        
  defined( 'ABSPATH' ) or die( 'Direct access is not permitted' );
  
  $sps_build_nr = 106;
  
  if( get_option( 'sps_build_nr', 0 ) < $sps_build_nr ) {
    // create directories
    $sps_directories = array(
      WPMU_PLUGIN_DIR.'/simple-plugin-selector/css',
      WPMU_PLUGIN_DIR.'/simple-plugin-selector/includes',
      WPMU_PLUGIN_DIR.'/simple-plugin-selector/js',
      WPMU_PLUGIN_DIR.'/simple-plugin-selector/languages' );
    foreach( $sps_directories as $sps_directory ) {
      // generates a PHP warning if the directory exists
      if( ! file_exists( $sps_directory ) ) {
        // makes any necessary intervening directories
        // suppress any warning with '@'
        @mkdir( $sps_directory, 0777, true );
      }
    }
    // upload files
    $sps_files = sps_get_files();
    foreach( $sps_files as $sps_file ) {
      $source_file = plugin_dir_path( __FILE__ ).'files'.$sps_file;
      $target_file = WPMU_PLUGIN_DIR.$sps_file;
      // suppress any warning with '@'
      @copy( $source_file, $target_file );
    }
    update_option( 'sps_build_nr', $sps_build_nr );
  }
  
  // plugin deactivation
  register_deactivation_hook( __FILE__, 'sps_plugin_deactivation' );
  function sps_plugin_deactivation() {
    // remove files
    $sps_files = sps_get_files();
    foreach( $sps_files as $sps_file ) {
      $full_path = WPMU_PLUGIN_DIR.$sps_file;
      if( file_exists( $full_path ) ) {
        // suppress any warning with '@'
        @unlink( $full_path );
      }
    }
    // remove directories
    $sps_directories = array(
      WPMU_PLUGIN_DIR.'/simple-plugin-selector/css',
      WPMU_PLUGIN_DIR.'/simple-plugin-selector/includes',
      WPMU_PLUGIN_DIR.'/simple-plugin-selector/js',
      WPMU_PLUGIN_DIR.'/simple-plugin-selector/languages',
      WPMU_PLUGIN_DIR.'/simple-plugin-selector' );
    foreach( $sps_directories as $sps_directory ) {
      // rmdir generates a PHP warning if the directory does not exist
      if( file_exists( $sps_directory ) ) {
        // suppress any warning with '@'
        @rmdir( $sps_directory );
      }
    }
    // do not remove /mu-plugins because something else may be using it
    update_option( 'sps_build_nr', 0);
  } // end function
  
  function sps_get_files() {
    $sps_files = array(
      '/simple-plugin-selector/css/admin.css',
      '/simple-plugin-selector/includes/filter.php',
      '/simple-plugin-selector/includes/functions.php',
      '/simple-plugin-selector/includes/pages.php',
      '/simple-plugin-selector/includes/plugins.php',
      '/simple-plugin-selector/includes/settings.php',
      '/simple-plugin-selector/js/admin.js',
      '/simple-plugin-selector/languages/simple-plugin-selector.pot',
      '/simple-plugin-selector/main.php',
      '/simple-plugin-selector.php' );
    return $sps_files;
  } // end function
  
  // for debuging
  if ( ! function_exists( 'write_log' ) ) {
    function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
        error_log( print_r( $log, true ) );
      } else {
        error_log( $log );
      }
    }
  }
  