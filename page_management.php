<?php

function oer_create_page( $page_title, $page_name , $menu_order , $page_parent_id = NULL) {
  global $wpdb;
  
  delete_option("oerplugin_" . $page_name . "_page_title");
  add_option("oerplugin_" . $page_name . "_page_title" , $page_title);

  delete_option("oerplugin_" . $page_name . "_page_id");
  add_option("oerplugin_" . $page_name  . "_page_id", '0', '', 'yes');                    

  $page = get_page_by_title ( $page_title );

  if ( $page ) {
    wp_delete_post ( $page->ID , true); //delete the page entirely
  }
  
  $_p = array();
  $_p['post_title'] = $page_title;
  $_p['post_name'] = $page_name;
  $_p['post_content'] = "This text should not be visible.";
  $_p['post_status'] = 'publish';
  $_p['post_type'] = 'page';
  $_p['comment_status'] = 'closed';
  $_p['ping_status'] = 'closed';
  $_p['post_category'] = array(1); // the default 'Uncategorised"
  $_p['menu_order'] = $menu_order;

  if( $page_parent_id ) {
    $_p['post_parent'] = $page_parent_id;
  }
    
  // Insert the post into the database
  $page_id = wp_insert_post( $_p );

  delete_option( "oerplugin_" . $page_name  . "_page_id" );
  add_option( "oerplugin_" . $page_name  . "_page_id", $page_id );
  
  return $page_id;
}

function oer_delete_page($page_name) {
  global $wpdb;
  
  $page_title = get_option("oerplugin_" . $page_name . "_page_title");
  $page_id = get_option( "oerplugin_" . $page_name . "_page_id");

  if( $page_id) {
    wp_delete_post( $page_id , true ); //delete the page entirely
  }
  
  delete_option("oerplugin_" . $page_name . "_page_title"); 
  delete_option("oerplugin_" . $page_name . "_page_id");
}

?>
