<?php
  
  function oer_init() {
    add_filter('get_pages','oer_exclude_pages');
    $plugin_dir = basename(dirname(__FILE__));
    load_plugin_textdomain( 'oerplugin', false, $plugin_dir );
  }
  
  function init_logged_in() {
    if ( isset($_COOKIE['oer_cookie_id']) && isset($_COOKIE['oer_user_id']) ) {
      $cookie_id = intval($_COOKIE['oer_cookie_id']);
      $user_id = intval($_COOKIE['oer_user_id']);
      if($cookie_id && $user_id){
	global $wpdb;
	$cookie_pairs = $wpdb->get_results(
                    $wpdb->prepare("SELECT user_id, cookie_id from OER_Userlog WHERE cookie_id = %d AND user_id = %d",$cookie_id,$user_id));
	if($cookie_pairs) {
	  return true;
	}
      }
    }
    return false; 
  }
  
  function oer_exclude_pages( $pages ){
    $bail_out = ( ( defined( 'WP_ADMIN' ) && WP_ADMIN == true ) || ( strpos( $_SERVER[ 'PHP_SELF' ], 'wp-admin' ) !== false ) );
    $bail_out = apply_filters ( 'oer_admin_bail_out', $bail_out );
    if( $bail_out ) return $pages;
    
    $excluded_ids = oer_get_excluded_ids();
    $length = count($pages);
    
    for ( $i=0; $i<$length; $i++ ) {
      $page = & $pages[$i];
      if ( oer_ancestor_excluded( $page, $excluded_ids, $pages ) ) {
        $excluded_ids[] = $page->ID;
      }
    }
    
    $delete_ids = array_unique( $excluded_ids );
    
    for ( $i=0; $i<$length; $i++ ) {
      $page = & $pages[$i];
      if ( in_array( $page->ID, $delete_ids ) ) {
        unset( $pages[$i] );
      }
    }

    if ( ! is_array( $pages ) ) $pages = (array) $pages;
    $pages = array_values( $pages );

    return $pages;
  }

  function oer_get_excluded_ids() {
    //Insert here pages to be excluded
    $login_id = get_option("oerplugin_login_page_id");
    $reg_id = get_option("oerplugin_registration_page_id");
    $useroer_id= get_option("oerplugin_useroer_page_id");
    $editresource_id = get_option("oerplugin_editresource_page_id");
    $resetpassword_id = get_option("oerplugin_resetpassword_page_id"); 
    $emailresetpassword_id = get_option("oerplugin_emailresetpassword_page_id");
    

    if(init_logged_in()){
      $excluded_ids = array(
			$login_id,
			$reg_id,
			$editresource_id,
      $resetpassword_id,
      $emailresetpassword_id
			);
    } else {
      $excluded_ids = array(
			$useroer_id,
			$editresource_id,
      $resetpassword_id,
      $emailresetpassword_id
      );
    }
    
    return $excluded_ids;
  }
  
  function oer_ancestor_excluded( $page, $excluded_ids, $pages ) {
    $parent = & oer_get_page( $page->post_parent, $pages );
    // Is there a parent?
    if ( ! $parent )
      return false;
    // Is it excluded?
    if ( in_array( $parent->ID, $excluded_ids ) )
      return (int) $parent->ID;
    // Is it the homepage?
    if ( $parent->ID == 0 )
      return false;
    // Otherwise we have another ancestor to check
    return oer_ancestor_excluded( $parent, $excluded_ids, $pages );
  }

  function oer_get_page( $page_id, $pages ) {
    $length = count($pages);
    for ( $i=0; $i<$length; $i++ ) {
      $page = & $pages[$i];
      if ( $page->ID == $page_id ) return $page;
    }
    return false;
  }
  
?>
