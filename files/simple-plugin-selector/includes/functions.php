<?php
  // Topcode Website Services
  // Simple Plugin Selector

  defined( 'ABSPATH' ) or die( 'Direct access is not permitted' );

  if ( ! function_exists( 'write_log' ) ) {
    function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
        error_log( print_r( $log, true ) );
      } else {
        error_log( $log );
      }
    }
  }

  // print a radio button
  // if the value is the current_value, the button is selected
  function sps_print_radio_button( $value, $current_value, $name, $text ) {
    print '<p>';
    if ( $value == $current_value ) {
      print '<input type="radio" name="'.$name.'" value="'.$value.'" checked="checked" class="sps_radio"/>'.PHP_EOL;
    } else {
      print '<input type="radio" name="'.$name.'" value="'.$value.'" class="sps_radio"/>'.PHP_EOL;
    }
    print ' ' . $text;
    print '</p>';
  } // end function

  // print a radio button in a table cell
  // if the value is the current_value, the button is selected
  function sps_print_radio_button_cell( $value, $current_value, $name ) {
    print '<td>';
    if ( $value == $current_value ) {
      print '<input type="radio" name="'.$name.'" value="'.$value.'" checked="checked" class="sps_radio"/>'.PHP_EOL;
    } else {
      print '<input type="radio" name="'.$name.'" value="'.$value.'" class="sps_radio"/>'.PHP_EOL;
    }
    print '</td>';
  } // end function

  // print a select option
  // if the option is the current_value, the option is selected
  function sps_print_option( $value, $current_value, $text ) {
    if ( $value == $current_value ) {
      print '<option value="'.$value.'" selected="selected">'.$text.'</option>'.PHP_EOL;
    } else {
      print '<option value="'.$value.'">'.$text.'</option>'.PHP_EOL;
    }
  } // end function

  function sps_woocommerce_active() {
    $plugins = get_option( 'active_plugins' );
    return in_array( 'woocommerce/woocommerce.php', $plugins );
  } // end function
