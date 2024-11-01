<?php
  // Topcode Website Services
  // Simple Plugin Selector

  defined( 'ABSPATH' ) or die( 'Direct access is not permitted' );

  // load_type: 0 = always, 1 = never, 2 = sometimes
  // maybe_load: 0 = don't load, 1 = load

  // set default options
  // add_option() will not change the option if it exists

  // global
  add_option( 'sps_enabled', 1 ); // 0 = plugin filter disabled, 1 = enabled
  add_option( 'sps_per_page', 50 ); // maximum items per tab

  // plugins
  add_option( 'sps_next_plugin_id', 0 );
  add_option( 'sps_plugins', array() ); // array ( plugin_id => array ( 'plugin' => $plugin, 'name' => $name ) )
  add_option( 'sps_plugin_load_types', array() ); // array ( plugin_id, load_type )

  // pages
  add_option( 'sps_pages', array() ); // array ( page_id => array ( 'title' => $title, 'url' => $url, 'page_type' => $page_type ) )
  add_option( 'sps_page_settings', array() );// array ( page_id => array ( plugin_id, mmaybe_load ) )

  function sps_settings() {
    if ( ! current_user_can( 'manage_options' ) )  {
      wp_die( __( 'You do not have permission to access this page.', 'simple-plugin-selector' ) );
    }

    global $sps_active_tab;

    // sps_plugins
    sps_update_sps_plugins();

    // sps_pages
    sps_update_sps_pages();

    // tabs
    $tabs = array(
      'global' => __( 'Global', 'simple-plugin-selector' ),
      'plugins' => __( 'Plugins', 'simple-plugin-selector' ),
      'pages' => __( 'Pages', 'simple-plugin-selector' ),
      'posts' => __( 'Posts', 'simple-plugin-selector' ),
      'categories' => __( 'Categories', 'simple-plugin-selector' ),
    );
    // woocommerce page types
    // WooCommerce does not work with Polylang (free version)
    // SPS not tested with "Polylang for WooCommerce"
    if( sps_woocommerce_active() ) {
      $tabs['products'] = __( 'Products', 'simple-plugin-selector' );
      $tabs['product_categories'] = __( 'Product Categories', 'simple-plugin-selector' );
    }

    print '<div class="wrap">';
    print '<h2>'.__( 'Simple Plugin Selector Settings', 'simple-plugin-selector' ).'</h2>'.PHP_EOL;

    $sps_active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '';
    if( ! in_array( $sps_active_tab, array_keys( $tabs ) ) ) {
      $sps_active_tab = 'global';
    }
    $links = array();
    foreach ( $tabs as $slug => $name ) {
      if ( $slug == $sps_active_tab ) {
        $links[] = '<a href="options-general.php?page=simple-plugin-selector&tab='.$slug.'" class="nav-tab nav-tab-active">'.$name.'</a>'.PHP_EOL;
      } else {
        $links[] = '<a href="options-general.php?page=simple-plugin-selector&tab='.$slug.'" class="nav-tab">'.$name.'</a>'.PHP_EOL;
      } // end if
    } // end foreach
    print '<form method="post" action="options.php" enctype="multipart/form-data" novalidate="novalidate">'.PHP_EOL;
    print '<nav class="nav-tab-wrapper">'.PHP_EOL;
    foreach ( $links as $link ) {
      print $link;
    } // end foreach
    print '</nav>'.PHP_EOL; // end tabs nav
    // settings_fields( option_group )
    settings_fields( 'sps_'.$sps_active_tab );
    // do_settings_sections( page )
    do_settings_sections( 'sps_'.$sps_active_tab );
    submit_button();
    print '</form>'.PHP_EOL;
    echo '</div>'.PHP_EOL; // end wrap div
  } // end function

  // setup the settings

  function sps_setup_setting( $data ) {
    // add_settings_field( id, title, callable, page, section, array $args )
    $args = array( 'id' => $data['name'], 'name' => $data['name'], 'current_value' => get_option( $data['name'] ), 'help' =>  $data['help'], 'page_type' => $data['page_type'] );
    add_settings_field( $data['name'], $data['label'], $data['input'], $data['page'], $data['section'], $args );
    // register_setting( option_group, option_name, array $args )
    $args = array('type' => 'string', 'description' => '', 'sanitize_callback' => $data['validate'], 'show_in_rest' => false, 'default' => get_option( $data['name'] ) );
    register_setting( $data['group'], $data['name'], $args );
  }

  add_action( 'admin_init', 'sps_setup_settings' );
  function sps_setup_settings() {

    // global tab

    // add_settings_section( id, title, callback, page )
    $section_title = __( 'Global', 'simple-plugin-selector' );
    add_settings_section( 'sps_global', $section_title, 'sps_global_help', 'sps_global' );

    // maximum items per tab
    $data = array (
      'group' => 'sps_global',
      'page' => 'sps_global',
      'section' => 'sps_global',
      'name' => 'sps_per_page',
      'label' => __( 'Maximum items per tab', 'simple-plugin-selector' ),
      'input' => 'sps_per_page',
      'page_type' => '',
      'help' => __( 'Larger numbers may cause settings tabs to load slowly.', 'simple-plugin-selector' ),
      'validate' => ''
    );
    sps_setup_setting( $data );

    // plugins tab

    // add_settings_section( id, title, callback, page )
    $section_title = __( 'Plugins', 'simple-plugin-selector' );
    add_settings_section( 'sps_plugins', $section_title, 'sps_plugins_help', 'sps_plugins' );

    $data = array (
      'group' => 'sps_plugins',
      'page' => 'sps_plugins',
      'section' => 'sps_plugins',
      'name' => 'sps_plugin_load_types',
      'label' => __( 'Load type', 'simple-plugin-selector' ),
      'input' => 'sps_plugin_load_types_input',
      'page_type' => '',
      'help' => '',
      'validate' => ''
    );
    sps_setup_setting( $data );

    // pages tab

    // add_settings_section( id, title, callback, page )
    $section_title = __( 'Pages', 'simple-plugin-selector' );
    add_settings_section( 'sps_pages', $section_title, 'sps_page_help', 'sps_pages' );

    $data = array (
      'group' => 'sps_pages',
      'page' => 'sps_pages',
      'section' => 'sps_pages',
      'name' => 'sps_page_settings',
      'label' => __( 'Pages', 'simple-plugin-selector' ),
      'input' => 'sps_page_settings_input',
      'page_type' => 'page',
      'help' => '',
      'validate' => 'validate_sps_page_settings'
    );
    sps_setup_setting( $data );

    // posts tab

    // add_settings_section( id, title, callback, page )
    $section_title = __( 'Posts', 'simple-plugin-selector' );
    add_settings_section( 'sps_posts', $section_title, 'sps_post_help', 'sps_posts' );

    $data = array (
      'group' => 'sps_posts',
      'page' => 'sps_posts',
      'section' => 'sps_posts',
      'name' => 'sps_page_settings',
      'label' => __( 'Posts', 'simple-plugin-selector' ),
      'input' => 'sps_page_settings_input',
      'page_type' => 'post',
      'help' => '',
      'validate' => 'validate_sps_page_settings'
    );
    sps_setup_setting( $data );

    // categories tab

    // add_settings_section( id, title, callback, page )
    $section_title = __( 'Categories', 'simple-plugin-selector' );
    add_settings_section( 'sps_categories', $section_title, 'sps_category_help', 'sps_categories' );

    $data = array (
      'group' => 'sps_categories',
      'page' => 'sps_categories',
      'section' => 'sps_categories',
      'name' => 'sps_page_settings',
      'label' => __( 'Categories', 'simple-plugin-selector' ),
      'input' => 'sps_page_settings_input',
      'page_type' => 'category',
      'help' => '',
      'validate' => 'validate_sps_page_settings'
    );
    sps_setup_setting( $data );

    // products tab

    // add_settings_section( id, title, callback, page )
    $section_title = __( 'Products', 'simple-plugin-selector' );
    add_settings_section( 'sps_products', $section_title, 'sps_product_help', 'sps_products' );

    $data = array (
      'group' => 'sps_products',
      'page' => 'sps_products',
      'section' => 'sps_products',
      'name' => 'sps_page_settings',
      'label' => __( 'Products', 'simple-plugin-selector' ),
      'input' => 'sps_page_settings_input',
      'page_type' => 'product',
      'help' => '',
      'validate' => 'validate_sps_page_settings'
    );
    sps_setup_setting( $data );

    // product_categories tab

    // add_settings_section( id, title, callback, page )
    $section_title = __( 'Product Categories', 'simple-plugin-selector' );
    add_settings_section( 'sps_product_categories', $section_title, 'sps_product_cat_help', 'sps_product_categories' );

    $data = array (
      'group' => 'sps_product_categories',
      'page' => 'sps_product_categories',
      'section' => 'sps_product_categories',
      'name' => 'sps_page_settings',
      'label' => __( 'Product Categories', 'simple-plugin-selector' ),
      'input' => 'sps_page_settings_input',
      'page_type' => 'product_cat',
      'help' => '',
      'validate' => 'validate_sps_page_settings'
    );
    sps_setup_setting( $data );

  } // end function

  // input functions

  function sps_enabled_input( $args ) {
    $id = $args['id'];
    $name = $args['name'];
    $enabled = $args['current_value']; // array ( plugin_id => load_type )
    sps_print_radio_button( 0, $enabled, $name, __( 'Plugin filter disabled. All plugins will be loaded as normal.', 'simple-plugin-selector' ) );
    sps_print_radio_button( 1, $enabled, $name, __( 'Plugin filter enabled. Plugins will be loaded selectively in accordance with the settings.', 'simple-plugin-selector' ) );
    sps_print_help( $args );
  } // end function

  function sps_per_page( $args ) {
    $id = $args['id'];
    $name = $args['name'];
    $sps_per_page = $args['current_value']; // integer
    print '<select name="'.$name.'">'.PHP_EOL;
      sps_print_option( 20, $sps_per_page, '20' );
      sps_print_option( 50, $sps_per_page, '50' );
      sps_print_option( 100, $sps_per_page, '100' );
      sps_print_option( 200, $sps_per_page, '200' );
      sps_print_option( 500, $sps_per_page, '500' );
      sps_print_option( 1000, $sps_per_page, '1000' );
    print '</select>'.PHP_EOL;
    sps_print_help( $args );
  } // end function

  function sps_plugin_load_types_input( $args ) {
    $id = $args['id'];
    $name = $args['name'];
    $plugins = get_option( 'active_plugins' ); // array ( plugin )
    if( ! count( $plugins ) ) {
      print '<p class="sps_help">'.__( 'There are no active plugins.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
      return;
    }
    sort( $plugins );
    $sps_plugins = get_option( 'sps_plugins' ); // array ( plugin_id => array ( 'plugin' => $plugin, 'name' => $name ) )
    $sps_plugin_load_types = $args['current_value']; // array ( plugin_id => load_type )
    print '<table class="sps_table">'.PHP_EOL;
    print '<thead>'.PHP_EOL;
    print '<tr>'.PHP_EOL;
    print '<th class="plugin_name">'.__( 'Plugin', 'simple-plugin-selector' ).'</th>'.PHP_EOL;
    print '<th class="plugin_option">'.__( 'Always', 'simple-plugin-selector' ).'</th>'.PHP_EOL;
    print '<th class="plugin_option">'.__( 'Never', 'simple-plugin-selector' ).'</th>'.PHP_EOL;
    print '<th class="plugin_option">'.__( 'Sometimes', 'simple-plugin-selector' ).'</th>'.PHP_EOL;
    print '</tr>'.PHP_EOL;
    print '</thead>'.PHP_EOL;
    print '<tbody>'.PHP_EOL;
    foreach( $plugins as $plugin ) {
      // get plugin id
      $plugin_id = sps_get_plugin_id( $plugin );
      if( false !== $plugin_id ) { // false means we don't have plugin properties
        $plugin_properties = $sps_plugins[ $plugin_id ];
        $plugin_name = $plugin_properties['name'];
        // get plugin load_type
        if( isset( $sps_plugin_load_types[$plugin_id] ) ) {
          $load_type = $sps_plugin_load_types[$plugin_id];
        } else {
          $load_type = 0; // default = always
        }
        $group_name = $name.'['.$plugin_id.']';
        print '<tr>'.PHP_EOL;
        print '<td>'.$plugin_name.'</td>';
        sps_print_radio_button_cell( 0, $load_type, $group_name );
        sps_print_radio_button_cell( 1, $load_type, $group_name );
        sps_print_radio_button_cell( 2, $load_type, $group_name );
        print '</tr>'.PHP_EOL;
      }
    }
    print '</tbody>'.PHP_EOL;
    print '</table>'.PHP_EOL;
    sps_print_help( $args );
  } // end function

  function sps_page_settings_input( $args ) {
    $id = $args['id'];
    $name = $args['name'];
    $page_type = $args['page_type'];
    switch ( $page_type ) {
      case 'page':
        $page_type_str = __( '', 'simple-plugin-selector' );
        break;
      case 'post':
        $page_type_str = __( 'post', 'simple-plugin-selector' );
        break;
      case 'category':
        $page_type_str = __( 'category', 'simple-plugin-selector' );
        break;
      case 'product':
        $page_type_str = __( 'product', 'simple-plugin-selector' );
        break;
      case 'product_cat':
        $page_type_str = __( 'product category', 'simple-plugin-selector' );
        break;
      default:
    }

    $plugins = get_option( 'active_plugins' ); // array ( plugin )
    if( ! count( $plugins ) ) {
      print '<p class="sps_help">'.__( 'There are no active plugins.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
      return;
    }
    sort( $plugins );
    $sps_plugins = get_option( 'sps_plugins' ); // array ( plugin_id => array ( 'plugin' => $plugin, 'name' => $name ) )
    $sps_plugin_load_types = get_option( 'sps_plugin_load_types' ); // array ( plugin_id => load_type )
    $sps_pages = get_option( 'sps_pages' );
    foreach( $sps_pages as $page_id => $properties ) {
      $this_page_type = isset( $properties['page_type'] ) ? $properties['page_type'] : 'page';
      if( $page_type != $this_page_type ) {
        unset( $sps_pages[$page_id] );
      }
    }
    $sps_page_settings = $args['current_value']; // array ( page_id => array ( plugin_id => maybe_load ) )
    // have any Sometimes plugins been set
    $nr_maybe_plugins = 0;
    foreach( $plugins as $plugin ) {
      $plugin_id = sps_get_plugin_id( $plugin );
      if( false !== $plugin_id ) { // false means we don't have plugin properties
        // get plugin load_type
        if( isset( $sps_plugin_load_types[$plugin_id] ) ) {
          $load_type = $sps_plugin_load_types[$plugin_id];
          if( 2 == (int) $load_type ) {
            $nr_maybe_plugins = $nr_maybe_plugins + 1;
          }
        }
      }
    }
    if( ! $nr_maybe_plugins ) {
      print '<p class="sps_help">'.__( 'No plugins have been set to load "Sometimes". These can be set on the Plugins tab.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
      return;
    }

    // show "Copy settings" button:
    $nr_items = count( $sps_pages );
    if( $nr_items > 1 ) {
      print '<p><button class="button button-secondary no_submit" onclick="sps_copy_settings()">';
      print __( 'Copy settings', 'simple-plugin-selector' );
      print '</button></p>'.PHP_EOL;
      print '<p class="sps_help">';
      /* translators: %$s: type of page */
      printf ( __( 'Copy the settings for the first %s page to all the remaining %s pages in the list.', 'simple-plugin-selector' ), $page_type_str, $page_type_str  );
      print ' '.__( 'There is no "undo", although you can navigate away without saving.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
      print '<p id="sps_alert">'.__( 'Copy complete. Don\'t forget to "Save Changes".', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
    } // end show "Copy settings" button

    $per_page = get_option( 'sps_per_page', 50 );
    $nr_pages = ceil( $nr_items / $per_page );
    $page_nr = isset( $_GET['page_nr'] ) ? $_GET['page_nr'] : 1;
    $set_nr = $page_nr - 1;
    $item_nr = 0;
    foreach( $sps_pages as $page_id => $properties ) {
      if( $item_nr < $set_nr * $per_page ) {
        $item_nr++;
        continue;
      }
      if( $item_nr >= ( $set_nr + 1 ) * $per_page ) {
        break;
      }
      $page_title = $properties['title'];
      $url = $properties['url'];
      print '<h4>'.$page_title.'</h4>';
      print '<div class="sps_url"><a href="'.$url.'" target="_blank">'.$url.'</a></div>'.PHP_EOL;
      print '<table class="sps_table">'.PHP_EOL;
      print '<thead>'.PHP_EOL;
      print '<tr>'.PHP_EOL;
      print '<th class="plugin_name">Plugin</th>'.PHP_EOL;
      print '<th class="plugin_option">Load</th>'.PHP_EOL;
      print '<th class="plugin_option">Don\'t load</th>'.PHP_EOL;
      print '</tr>'.PHP_EOL;
      print '</thead>'.PHP_EOL;
      print '<tbody>'.PHP_EOL;
      $plugin_nr = 0;
      foreach( $plugins as $plugin ) {
        $plugin_id = sps_get_plugin_id( $plugin );
        if( false !== $plugin_id ) { // false means we don't have plugin properties
          $plugin_properties = $sps_plugins[ $plugin_id ];
          $plugin_name = $plugin_properties['name'];
          // get plugin load_type
          if( isset( $sps_plugin_load_types[$plugin_id] ) ) {
            $load_type = $sps_plugin_load_types[$plugin_id];
            if( 2 == (int) $load_type ) {
              // look for the value of maybe_load in settings
              $maybe_load = 1; // default = load
              if( isset( $sps_page_settings[$page_id] ) ) {
                $page_settings = $sps_page_settings[$page_id];
                if( isset( $page_settings[$plugin_id] ) ) {
                  $maybe_load = $page_settings[$plugin_id];
                }
              }
              print '<tr class="plugin_nr_'.$plugin_nr.'">'.PHP_EOL;
              print '<td>'.$plugin_name.'</td>';
              $group_name = 'sps_page_settings['.$page_id.']['.$plugin_id.']';
              sps_print_radio_button_cell( 1, $maybe_load, $group_name );
              sps_print_radio_button_cell( 0, $maybe_load, $group_name );
              print '</tr>'.PHP_EOL;
              $plugin_nr++;
            }
          }
        }
      }
      print '</tbody>'.PHP_EOL;
      print '</table>'.PHP_EOL;
      $item_nr++;
    }

    // pagination
    if( $nr_pages > 1 ) {
      $this_page_nr = $page_nr;
      global $sps_active_tab;
      print '<div class="sps_page_nrs">Page No: '.PHP_EOL;
      for( $set_nr = 0; $set_nr < $nr_pages; $set_nr++ ) {
        $page_nr = $set_nr + 1;
        $is_current = $this_page_nr == $page_nr ? 'current' : '';
        print '<a href="?page=simple-plugin-selector&tab='.$sps_active_tab.'&page_nr='.$page_nr.'" class="sps_page_nr '.$is_current.'">'.$page_nr.'</a>'.PHP_EOL;
      }
      print '</div>'.PHP_EOL;
    }

  } // end function

  // validate

  function validate_sps_page_settings( $new_sps_page_settings ) {
     // array ( page_id => array ( plugin_id, mmaybe_load ) )
     // any entries having the same page_id overwrite existing entries if present
     // other page_ids remain unchanged
     $sps_page_settings = get_option ( 'sps_page_settings' ); // array ( page_id => array ( plugin_id, mmaybe_load ) )
     foreach( $new_sps_page_settings as $page_id => $page_settings ) {
       $sps_page_settings[$page_id] = $page_settings;
     }
     return $sps_page_settings;
  }

  // show help text

  function sps_global_help( $args ) {
    print '<p class="sps_help">'.__( 'The plugin selector works on front-side pages only.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
    print '<p class="sps_help">'.__( 'Activate or deactivate the plugin filter on the:', 'simple-plugin-selector' ).'<br>'.PHP_EOL;
    print __( 'Dashboard &gt; Plugins &gt; Installed Plugins page.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
    print '<p class="sps_help">'.__( 'If you have cache or optimize plugins, clear them after changing any settings.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
  } // end function

  function sps_plugins_help( $args ) {
    print '<p class="sps_help">'.__( 'The plugin selector works on front-side pages only.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
    print '<p class="sps_help">'.__( 'Set which plugins should load on every page, which should never load and which should load only on some pages.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
    print '<p class="sps_help">'.__( 'Use the other tabs to set which "Sometimes" plugins should load on which page.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
  } // end function

  function sps_page_help( $args ) {
    print '<p class="sps_help">'.__( 'The plugin selector works on front-side pages only.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
    print '<p class="sps_help">'.__( "Set which plugins should load on which pages.", 'simple-plugin-selector' ).'</p>'.PHP_EOL;
    print '<p class="sps_help">'.__( 'Only plugins set to "Sometimes" on the Plugins tab will appear here.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
  } // end function

  function sps_post_help( $args ) {
    print '<p class="sps_help">'.__( 'The plugin selector works on front-side pages only.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
    print '<p class="sps_help">'.__( "Set which plugins should load on which post pages.", 'simple-plugin-selector' ).'</p>'.PHP_EOL;
    print '<p class="sps_help">'.__( 'Only plugins set to "Sometimes" on the Plugins tab will appear here.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
  } // end function

  function sps_category_help( $args ) {
    print '<p class="sps_help">'.__( 'The plugin selector works on front-side pages only.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
    print '<p class="sps_help">'.__( "Set which plugins should load on which category pages.", 'simple-plugin-selector' ).'</p>'.PHP_EOL;
    print '<p class="sps_help">'.__( 'Only plugins set to "Sometimes" on the Plugins tab will appear here.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
  } // end function

  function sps_product_help( $args ) {
    print '<p class="sps_help">'.__( 'The plugin selector works on front-side pages only.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
    print '<p class="sps_help">'.__( "Set which plugins should load on which product pages.", 'simple-plugin-selector' ).'</p>'.PHP_EOL;
    print '<p class="sps_help">'.__( 'Only plugins set to "Sometimes" on the Plugins tab will appear here.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
  } // end function

  function sps_product_cat_help( $args ) {
    print '<p class="sps_help">'.__( 'The plugin selector works on front-side pages only.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
    print '<p class="sps_help">'.__( "Set which plugins should load on which product category pages.", 'simple-plugin-selector' ).'</p>'.PHP_EOL;
    print '<p class="sps_help">'.__( 'Only plugins set to "Sometimes" on the Plugins tab will appear here.', 'simple-plugin-selector' ).'</p>'.PHP_EOL;
  } // end function

  function sps_print_help( $args ) {
    $help = $args['help'];
    if ( $help ) {
      print '<p class="sps_help">'.$help.'</p>'.PHP_EOL;
    }
  } // end function