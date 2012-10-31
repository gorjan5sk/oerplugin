<?php
/*
    Template Name: OER Login
*/

require_once( ABSPATH . 'wp-includes/class-phpass.php');

global $wpdb;

$_POST = stripslashes_deep( $_POST );
$_GET = stripslashes_deep( $_GET );

$user_id = $_COOKIE['oer_user_id'];

$resources_page_id = get_option("oerplugin_resources_page_id");
$redirect_to_resources = get_permalink( $resources_page_id );

$login_page_id = get_option("oerplugin_login_page_id");
$redirect_to_login = get_permalink( $login_page_id );

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

if (!logged_in()) {
    wp_redirect($redirect_to_login);
    exit;
}

if (isset($_POST['submit_edit'])) {
   
    $reg_password = $_POST['oer_reg_password']; 

    $reg_full_name = $_POST['oer_reg_full_name'];
    $reg_new_password = $_POST['oer_reg_new_password'];
    $reg_new_confirmpassword = $_POST['oer_reg_new_confirmpassword'];
    $reg_institution = $_POST['oer_reg_institution'];
    $reg_link_to_site = $_POST['oer_reg_link_to_site'];
    $reg_short_bio = $_POST['oer_reg_short_bio'];
    
    //$reg_username = sanitize_user( $reg_username );
    //$reg_email = sanitize_email( $reg_email ); 
    
    $user_row = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM OER_User WHERE id = %d LIMIT 1",$user_id));

    if($user_row) {
      $hasher = new PasswordHash(8, TRUE);
      if($hasher->CheckPassword($reg_password, $user_row[0]->password)){
        
        if( (! isset($reg_new_password)) || $reg_new_password==''){
          $reg_new_password=$reg_password;
        }
    
        if( (! isset($reg_new_confirmpassword)) || $reg_new_confirmpassword==''){
          $reg_new_confirmpassword=$reg_password;
        }
        
        //Check for required fields
        if( isset($reg_full_name) && $reg_full_name != ''
          && isset($reg_new_password) && $reg_new_password != ''
          && isset($reg_new_confirmpassword) && $reg_new_confirmpassword != ''
          && $reg_new_password == $reg_new_confirmpassword ){
          
          //Upload photo if present
          if($_FILES["oer_reg_photo"]["error"] == 0) {
            
            $dot = strrpos($_FILES["oer_reg_photo"]["name"], '.');
            
            $extension =  substr($_FILES["oer_reg_photo"]["name"], $dot);
          
            $_FILES["oer_reg_photo"]["name"] = $user_id . "_profile_pic" . $extension;
            
            $filename = $_FILES["oer_reg_photo"]["name"];
            
            $upload_folder = get_option('oerplugin_root_directory') . "/oer_uploads/profile_pics/";
            
            $reg_photo = site_url("/wp-content/plugins/oerplugin/oer_uploads/profile_pics/". $filename);
            
             move_uploaded_file($_FILES["oer_reg_photo"]["tmp_name"], $upload_folder . $filename);
             
          } else {
            $reg_photo = $row->photo_url;
          }
          
          $hashed_password = $hasher->HashPassword($reg_new_password);
          
          $user_data = array(
            'full_name' => $reg_full_name,
            'password' => $hashed_password
          );
          $user_format = array('%s', '%s');

          if(isset($reg_institution) && $reg_institution != "") {
            $user_data['institution'] = $reg_institution;
            array_push($user_format, '%s');
          }
          if(isset($reg_link_to_site) && $reg_link_to_site !=""){
            $user_data['link_to_site'] = $reg_link_to_site;
            array_push($user_format, '%s');
          }
          if(isset($reg_photo) && $reg_photo != ""){
            $user_data['photo_url'] = $reg_photo;
            array_push($user_format, '%s');
          }
          if(isset($reg_short_bio) && $reg_short_bio != ""){
            $user_data['short_bio'] = $reg_short_bio;
            array_push($user_format, '%s');
          }
          
          $rows_updated = $wpdb->update( 'OER_User',
                $user_data,
                array( 'id' => $user_id ),
                $user_format,
                array('%d'));
          
          if(!(isset($rows_updated)) || $rows_updated < 1) 
            $fail_message = "Error communicating with the database!";
          else
            $message = __('Вашиот профил успешно е ажуриран.', 'oerplugin');

        } else if($reg_new_password != $reg_new_confirmpassword) {
          $fail_message = __('Лозинките не се совпаѓаат. Доколку сакате да ја смените лозинката, внесете ја два пати новата лозинка во соодветните полиња.', 'oerplugin');
        } else {
          $fail_message = __('Ги немате пополнето сите задолжителни полиња.', 'oerplugin');
        }
      } else {
        $fail_message = __('Погрешна лозинка.','oerplugin');
      }
    } else { //user doesn't exist
      $fail_message = __('Корисникот не е пронајден.','oerplugin');
    }   
} //end-outer-if

else {
    
    $query =  "SELECT * FROM OER_User";
    $query .= " WHERE id = %d";
    $query .= " LIMIT 1";
    
    $result = $wpdb->get_results(
                $wpdb->prepare($query,$user_id));
    
    if($result) {
        foreach($result as $row) {
            $reg_full_name = $row->full_name;
            $reg_email = $row->email;
            $reg_username = $row->username;
            
            $reg_institution = $row->institution;
            $reg_link_to_site = $row->link_to_site;
            $reg_short_bio = $row->short_bio;
            $reg_photo = $row->photo_url;
        } ///end-foreach
	} //end-if
    else {
        $fail_message = "Error communicating with the database!";
    }
} //end-outer-else

get_header();

?>



<div id="primary">
    <div id="content">
        
        <div id="wrapper" style="width: 100%;">
            
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
            
            <div id="register" style="background: #f5f5f5; width: 70%; padding: 20px; margin-left: auto; margin-right: auto;">
            
            <div id="holder" style=" width: 360px; margin-left: auto; margin-right: auto;">
            
                <span style="font-size: 1.3em;">
                    <?php printf(__('Ажурирање на личниот профил на корисникот %s', 'oerplugin'), $reg_username); ?>
                </span>
                <br />
                <br />

                <form action="" method="post" enctype="multipart/form-data">
                    
                    <?php if($reg_photo != "") { ?>
                    <div>
                        <img src="<?php echo $reg_photo; ?>" />
                    </div>
                    <?php } ?>
                    
                    <div>
                        <?php _e('Внесете ја вашата лозинка за да можете да ги промените личните информации', 'oerplugin'); ?>
                        <span style="color: red;">*</span>
                        <br />
                        <input type="password" name="oer_reg_password" maxlength="30" value=""style="width: 100%;" />
                        <br />
                        <br />
                    </div>

                    <div>
                        <?php _e('Име и презиме:', 'oerplugin'); ?>
                        <span style="color: red;">*</span>
                        <br />
                        <input type="text" name="oer_reg_full_name" maxlength="30" value="<?php if(isset($reg_full_name)) echo esc_attr($reg_full_name); ?>" style="width: 100%;" />
                    </div>

                    <div>
                        <?php _e('Нова лозинка:', 'oerplugin'); ?>
                        <br />
                        <input type="password" name="oer_reg_new_password" maxlength="30" value="" style="width: 100%;" />
                    </div>

                    <div>
                        <?php _e('Потврдете ја новата лозинка:', 'oerplugin'); ?>
                        <br />
                        <input type="password" name="oer_reg_new_confirmpassword" maxlength="30" value="" style="width: 100%;" />
                    </div>
                    
                    <div>
                        <?php _e('Институција:', 'oerplugin'); ?><br />
                        <input type="text" name="oer_reg_institution" maxlength="30" value="<?php if(isset($reg_institution)) echo esc_attr($reg_institution); ?>" style="width: 100%;" />
                    </div>
                    
                    <div>
                        <?php _e('Линк до вашата веб-страница:', 'oerplugin'); ?><br />
                        <input type="text" name="oer_reg_link_to_site" value="<?php  if(isset($reg_link_to_site)) echo esc_attr($reg_link_to_site); ?>" style="width: 100%;" />
                    </div>
                    
                    <div>
                        <?php _e('Кратка биографија:', 'oerplugin'); ?><br />
                        <textarea rows="5" name="oer_reg_short_bio" style="width: 100%;"><?php if(isset($reg_short_bio)) echo esc_attr_e($reg_short_bio); ?></textarea>
                    </div>
                    
                    
                    
                    <div>
                        <?php _e('Промена на фотографија:', 'oerplugin'); ?>
                        <input type="file" name="oer_reg_photo" accept="image/*" value="<?php _e('Изберете фотографија...', 'oerplugin'); ?>" style="" />
                    </div>
                    
                    <span style="color: red;">* <?php _e('Овие полиња се задолжителни:', 'oerplugin'); ?></span>
                    <br />
                    <br />
                    <input type="submit" name="submit_edit" value="<?php _e('Ажурирање на профил', 'oerplugin'); ?>" />
                    
                </form>

            </div> <!-- end-holder -->

            </div> <!-- end-register -->
            
            <br />
            
            <div id="useless" style="clear: both;">
                &nbsp;
            </div> <!-- end-useless -->

        </div> <!-- end-wrapper -->

    </div> <!-- end-content -->
</div> <!-- end-primary -->
<?php
get_footer();
?>
