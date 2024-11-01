<?php
  // Topcode Website Services
  // Simple Plugin Selector

  defined( 'ABSPATH' ) or die( 'Direct access is not permitted' );

  // update plugins data

  function sps_update_sps_plugins() {
    $sps_plugins = get_option( 'sps_plugins' ); // array ( plugin_id => array ( 'plugin' => $plugin, 'name' => $name ) )
    if( 'array' != gettype( $sps_plugins ) ) {
      $sps_plugins = array();
    }
    // remove data needed by previous version
    foreach( $sps_plugins as $plugin_id => $properties ) {
      unset( $sps_plugins[$plugin_id]['load_type'] );
    }
    // look for new plugins
    // existing sps_plugins entries are retained because plugin_id is used in sps_plugin_load_types
    $plugins = get_option( 'active_plugins' ); // array ( plugin )
    foreach( $plugins as $plugin ) {
      $is_new = true;
      foreach( $sps_plugins as $plugin_id => $properties ) {
        if( $plugin == $properties['plugin'] ) {
          $is_new = false;
          continue;
        }
      }
      if( $is_new ) {
        $plugin_id = get_option( 'sps_next_plugin_id' );
        // eg: WP_PLUGIN_DIR = /home/user/public_html/domain/wp-content/plugins (no trailing slash)
        $plugin_path = WP_PLUGIN_DIR.'/'.$plugin; // absolute path to plugin file
        $plugin_data = get_plugin_data( $plugin_path, false, false );
        $name = $plugin_data['Name'];
        $sps_plugins[$plugin_id] = array( 'plugin' => $plugin, 'name' => $name );
        $plugin_id++;
        update_option( 'sps_next_plugin_id', $plugin_id );
       }
     }
     update_option( 'sps_plugins', $sps_plugins );
  } // end function

  function sps_get_plugin_id( $plugin ) {
    $sps_plugins = get_option( 'sps_plugins' );  // array ( plugin_id => array ( 'plugin' => $plugin, 'name' => $name ) )
    foreach( $sps_plugins as $plugin_id => $properties ) {
      if( $plugin == $properties['plugin'] ) {
        return $plugin_id;
      }
    }
    return false;
  } // end function