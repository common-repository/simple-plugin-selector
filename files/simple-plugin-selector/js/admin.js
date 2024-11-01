// Simple Plugin Selector
// Topcode Website Services

// button on settings pages
// to copy first item's settings to remaining items

jQuery( function( $ ) {
  $(document).ready(function() {
    $( ".no_submit" ).click(function( event ) {
      event.preventDefault();
    });
  });
});

function sps_copy_settings() {

  // find the tables
  var tables = {}, nr_tables, first_table = {}, this_table = {}, table_nr;
  tables = jQuery( ".sps_table" );
  nr_tables = tables.length;
  first_table = tables.eq( 0 );

  // find the tbody in the first table
  var tbodies = {}, nr_tbodies, first_tbody = {};
  tbodies = jQuery( first_table ).find( "tbody" );
  nr_tbodies = tbodies.length;
  first_tbody = tbodies.eq( 0 );

  // find the plugins in the first table
  var plugins = {}, nr_plugins, plugin_nr;
  plugins = jQuery( first_tbody ).find( "tr" );
  nr_plugins = plugins.length;

  // for each plugin in turn
  var class_name, rows = {}, first_row = {}, radios = {}, nr_radios, first_radio = {}, group_name, is_set;
  for( plugin_nr = 0; plugin_nr < nr_plugins; plugin_nr++ ) {

    // get the row in the first table for this plugin
    class_name = "plugin_nr_" + plugin_nr;
    rows = jQuery( first_table ).find( "." + class_name );
    first_row = rows.eq( 0 );

    // get the name of the radio button group in this row
    radios = jQuery( first_row ).find( 'input[type="radio"]' );
    nr_radios = radios.length;
    first_radio = radios.eq( 0 );
    group_name = jQuery( first_radio ).attr( "name" );
    is_set = jQuery( 'input[name="' + group_name + '"]:checked' ).val(); // 0 or 1 // goes wrong here

    // set the radios for this plugin in the remaining pages
    for( table_nr = 1; table_nr < nr_tables; table_nr++ ) {
      this_table = tables.eq( table_nr);
      rows = jQuery( this_table ).find( "." + class_name );
      first_row = rows.eq( 0 );
      radios = jQuery( first_row ).find( 'input[type="radio"]' );
      first_radio = radios.eq( 0 );
      group_name =jQuery( first_radio ).attr( "name" );
      jQuery( 'input[name="' + group_name + '"][value="' + is_set + '"]' ).prop( "checked", true );
    }

  }
  jQuery( '#sps_alert' ).css( "display", "inline" );
  setTimeout( function() { jQuery( '#sps_alert' ).css( "display", "none" ); }, 3000);
} // end function