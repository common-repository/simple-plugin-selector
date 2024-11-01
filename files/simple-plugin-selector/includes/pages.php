<?php
  // Topcode Website Services
  // Simple Plugin Selector

  defined( 'ABSPATH' ) or die( 'Direct access is not permitted' );

  // update pages data

  /*
   * For Polylang sites where the language code is in the url, get_pages() and
   * get_posts() return only the pages which are in the current language. Settings
   * needs pages for all languages, so this function uses direct database access.
   * For non-Polylang sites, there won't be any difference.
   */

  function sps_update_sps_pages() {
    $sps_pages = array(); // array ( page_id => array ( 'title' => $title, 'url' => $url, 'post_type' => $post_type ) )

    // pages
    $sps_pages = array_merge( $sps_pages, sps_get_post_pages( 'Page: ', 'page' ) );

    // posts
    $sps_pages = array_merge( $sps_pages, sps_get_post_pages( 'Post: ', 'post' ) );

    // categories
    $sps_pages = array_merge( $sps_pages, sps_get_taxonomy_pages(  'category', 'Category', 'category' ) );

    // woocommerce page types
    if( sps_woocommerce_active() ) {

      // products
      $sps_pages = array_merge( $sps_pages, sps_get_post_pages( 'Product: ', 'product' ) );

      // product categories
      $sps_pages = array_merge( $sps_pages, sps_get_taxonomy_pages( 'product_cat', 'Product category', 'product_cat' ) );

    } // end woocommerce active
    
    update_option( 'sps_pages', $sps_pages );
  } // end function

  function sps_get_post_pages( $label, $page_type ) {
    $sps_pages = array();
    global $wpdb;
    $table = $wpdb->prefix.'posts';
    $query = 'SELECT * FROM '.$table.' WHERE post_type = "'.$page_type.'" AND post_status = "publish" ORDER BY `post_title`';
    $posts = $wpdb->get_results( $query );
    foreach( $posts as $post ) {
      $post_id = $post->ID;
      $title = $label.$post->post_title;
      $url = get_permalink( $post_id ); // will includes the language code if any
      $sps_pages[$post_id] = array( 'title' => $title, 'url' => $url, 'page_type' => $page_type );
    }
    return $sps_pages;
  } // end function

  function sps_get_taxonomy_pages( $taxonomy, $label, $page_type ) {
    $sps_pages = array();
    $args = array(
      'taxonomy' => $taxonomy,
      'hide_empty' => true,
      'orderby' => 'slug'
    );
    $terms = get_terms( $args );
    if( ! is_wp_error( $terms ) ) {
      foreach( $terms as $term ) {
        $slug = $term->slug;
        $post_id = $taxonomy.' - '.$slug;
        $title = 'Taxonomy: '.$label.', Term name: '.$term->name;
        $url = get_category_link( $term ); // will includes the language code if any
        $sps_pages[$post_id] = array( 'title' => $title, 'url' => $url, 'page_type' => $page_type );
      }
    }
    return $sps_pages;
  } // end function
  
  // update sps_pages after the wp_loaded hook in case any plugin activation
  // deactivation, plugin settings or permalink settings have changed page urls
  add_action( 'wp_loaded', 'sps_update_sps_pages', 99 );
