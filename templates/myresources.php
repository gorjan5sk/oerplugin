<?php
/*
    Template Name: OER Login
*/
require_once('parameters_display.php');
require_once('resources_display_user.php');
require_once('resources_display_item_user.php');

global $wpdb;

$_POST = stripslashes_deep( $_POST );
$_GET = stripslashes_deep( $_GET );

$user_id = $_COOKIE['oer_user_id'];

$login_page_id = get_option("oerplugin_login_page_id");
$redirect_to_login = get_permalink( $login_page_id );

function user_logged_in() {
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

if (!user_logged_in()) {
    wp_redirect($redirect_to_login);
    exit;
}

if (isset($_GET['del_res_id'])) {
    
    $user_id = intval($_COOKIE['oer_user_id']);
    $id = $_GET['del_res_id'];
    
    $select_query = "SELECT * FROM OER_Resource WHERE id = %d and user_id = %d LIMIT 1";
    
    $delete_query = "DELETE FROM OER_Resource";
    $delete_query .= " WHERE id = %d";
    $delete_query .= " AND user_id = %d";
    
    $res_row = $wpdb->get_results( $wpdb->prepare($select_query, $id, $user_id) );
    
    if($res_row) {
        $res_filename = $res_row[0]->data_file;
        
        if( (!empty($res_filename)) && $res_filename!='' ){
          $res_filename = str_replace( site_url("/wp-content/plugins/oerplugin"), '', $res_filename);
          $res_filename = get_option('oerplugin_root_directory') . $res_filename;
          unlink($res_filename);
        }        


        $del_row = $wpdb->query( $wpdb->prepare($delete_query, $id, $user_id));
        
        if($del_row)
          $message = __('Ресурсот е избришан.', 'oerplugin') . $temp_msg;
        else
          $fail_message = __('Ресурсот не може да биде избришан.', 'oerplugin');

    }
    else {
        $fail_message = __('Ресурсот не постои.', 'oerplugin');
    }
}

get_header();
?>

<div id="primary">
    <div id="wrapper" style="width=100%; padding: 10px;">

        <div id="main-content" style="padding-left: 50px; padding-right: 50px;">
        
            <?php if(isset($message)) { ?>
            <div id="info" style="background: #38F590; padding: 10px;">
                <?php echo $message; ?>
            </div>
            <?php }
            else if(isset($fail_message)) { ?>
            <div id="info" style="background: #FAACAC; padding: 10px; text-align: center;">
                <?php echo $fail_message; ?>
            </div>
            <?php } ?> 
        
            <?php

                if( isset($_GET['resource_id']) ) {
                  $resource_id = intval($_GET['resource_id']);
                  resources_display_item_user($resource_id);
                } else {
                  resources_display_user();
                }

            ?>
        </div>
        <div id="useless" style="clear: both;">

        </div>
    </div>
</div>
<?php
get_footer();
?>
