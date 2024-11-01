<?php
  // Topcode Website Services
  // Simple Plugin Selector

  defined( 'ABSPATH' ) or die( 'Direct access is not permitted' );

  // filter 'option_active_plugins' does not fire in admin context
  if( get_option( 'sps_enabled', 0 ) ) { // 0 = plugin filter disabled, 1 = enabled
    add_filter( 'option_active_plugins', 'sps_filter_plugins' );
  }

  function sps_filter_plugins( $plugins ) {
    // $plugins = array ( 'plugin_directory/plugin_filename' );

    // check that $plugins is an array
    if( ! is_array( $plugins ) ) {
      return $plugins;
    }
    
    // check that the simple-plugin-selector plugin is active
    if( ! in_array( 'simple-plugin-selector/sps-uploader.php', $plugins ) ) {
      return $plugins;
    }
    
    // disable filtering in admin context
    // filter 'option_active_plugins' does not fire in admin context, but make sure
    if( is_admin() ) {
      return $plugins;
    }    

    // its possible to get different uris from the same page load where later calls are by ajax
    $this_uri = $_SERVER['REQUEST_URI']; // /?wc-ajax=get_refreshed_fragments (unknown page)

    // executes several times per page load, so cache the filtered list in $filtered_plugins
    static $filtered_plugins = array(); // only set to $plugins on first call
    static $applicable_uri = ''; // the uri that applies to the filtered plugins list

    if( $this_uri == $applicable_uri ) {
      // same uri, use cached filtered plugins array
      return $filtered_plugins;
    } else {
      // this is a new uri
      $filtered_plugins = $plugins;
      $applicable_uri = $this_uri;
    }

    // get the url of the current page
    // $_SERVER['SCRIPT_URI'] only exists if mod_rewrite is enabled, it is not listed in the documentation & may not exist for XAMPP
    // $_SERVER['HTTP_REFERER'] is not reliable

    // get scheme
    // $_SERVER['REQUEST_SCHEME'] is not reliable
    $scheme = 'http';
    if( isset( $_SERVER['HTTPS'] ) ) {
      if( ! empty( $_SERVER['HTTPS'] ) ) {
        $scheme = 'https';
      }
    }

    // get host
    // $_SERVER['HTTP_HOST']; is not supported by HTTP 1.0
    if( ! isset( $_SERVER['HTTP_HOST'] ) ) {
      return $plugins;
    }
    $host = $_SERVER['HTTP_HOST']; // www.example.com

    // get request_uri
    if( ! isset( $_SERVER['REQUEST_URI'] ) ) {
      return $plugins;
    }
    $request_uri = $_SERVER['REQUEST_URI']; // /?wc-ajax=get_refreshed_fragments (unknown page)
    // '#anchor' fragment is not present server side

    $url = $scheme . '://' . $host . $request_uri;
    
    // get array of pages
    $sps_pages = get_option( 'sps_pages' ); // array ( page_id => array ( 'title' => $title, 'url' => $url ) )
    if( ! is_array( $sps_pages ) ) {
      return $plugins;
    }

    // get id of the current page
    // get_queried_object_id() doesn't work this early
    // global $wp_query; $wp_query->post->ID - $wp_query is a non-object this early
    // url_to_postid() doesn't work this early

    $current_page_id = 0;
    foreach( $sps_pages as $page_id => $properties ) {
      if( $url == $properties['url'] ) {
        $current_page_id = $page_id;
        break;
      }
    }

    if( ! $current_page_id ) { // the current page is not in sps_pages
      return $plugins;
    }

    // get plugin data
    $sps_plugins = get_option( 'sps_plugins' ); // array ( $plugin_id => array ( 'plugin' => $plugin, 'name' => $name )
    if( ! is_array( $sps_plugins ) ) {
      // plugin types have not yet been set in sps settings
      return $plugins;
    }
    $sps_plugin_load_types = get_option( 'sps_plugin_load_types' ); // array ( plugin_id => load_type )
    if( ! is_array( $sps_plugin_load_types ) ) {
      return $plugins;
    }

    // get page settings
    $sps_page_settings = get_option( 'sps_page_settings', array() ); // array ( page_id => array ( plugin_id, load ) )
    if( ! is_array( $sps_page_settings ) ) {
      // this are no page settings so load all plugins
      return $plugins;
    }
    if( ! isset( $sps_page_settings[$current_page_id] ) ) {
      // this page has no setting so load all plugins
      return $plugins;
    }

    // go through the current active plugins and remove any that are not required for this page
    foreach( $plugins as $index => $plugin ) {
      foreach( $sps_plugins as $plugin_id => $properties ) {
        // is this one in our list
        if( $plugin != $properties['plugin'] ) {
          continue;
        }
        // get load type
        if( isset( $sps_plugin_load_types[$plugin_id] ) ) {
          $load_type = $sps_plugin_load_types[$plugin_id];
        } else {
          $load_type = 0; // default: 0 "always"
        }
        switch( $load_type ) {
          case 0:
            // "always": leave in $plugins
            break;
          case 1:
            // "never": remove from $plugins
            unset( $plugins[$index] );
            break;
          case 2:
            // "sometimes": depends on the settings for this page
            $this_page_settings = $sps_page_settings[$current_page_id]; // array ( page_id => array ( plugin_id, maybe_load ) )
            if( isset( $this_page_settings[$plugin_id] ) ) {
              if( ! $this_page_settings[$plugin_id] ) { // 0 = don't load, 1 = load
                unset( $plugins[$index] );
              }
            }
            break;
          default:
            // unknown load type
            // leave the plugin in the list
        }
      }
    }
    $filtered_plugins = $plugins;
    return $plugins;
    // execution time full filter: 0.50 milliseconds (more for bigger sites)
    // exection time using the cached plugin list: 0.02 milliseconds (same for bigger sites)
  } // end function
  