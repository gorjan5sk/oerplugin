<?php
/*
    Template Name: OER Logout
*/

$_POST = stripslashes_deep( $_POST );
$_GET = stripslashes_deep( $_GET );

$resources_page_id = get_option("oerplugin_resources_page_id");
$resources_url = get_permalink($resources_page_id);

function logged_in() {
  if ( isset($_COOKIE['oer_cookie_id']) && isset($_COOKIE['oer_user_id']) ) {
    $cookie_id = intval($_COOKIE['oer_cookie_id']);
    $user_id = intval($_COOKIE['oer_user_id']);
    if(isset($cookie_id) && isset($user_id)){
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

if(logged_in()) {
  setcookie('oer_user_id', '', time()-42000, '/');
  setcookie('oer_cookie_id', '', time()-42000, '/');
  
  $cookie_id = intval($_COOKIE['oer_cookie_id']);
  $user_id = intval($_COOKIE['oer_user_id']);
  
  global $wpdb;
  
  $wpdb->query("DELETE FROM OER_Userlog WHERE cookie_id = $cookie_id and user_id = $user_id");
}

wp_redirect($resources_url);
exit;

//get_footer();
?>
