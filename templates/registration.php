<?php
/*
    Template Name: OER Login
*/

require_once( ABSPATH . 'wp-includes/class-phpass.php');


global $wpdb;

$_POST = stripslashes_deep( $_POST );
$_GET = stripslashes_deep( $_GET );

$login_page_id = get_option("oerplugin_login_page_id");
$redirect_to_login = get_permalink( $login_page_id );

$resources_page_id = get_option("oerplugin_resources_page_id");
$redirect_to_resources = get_permalink( $resources_page_id );

if( get_option( 'permalink_structure') )
  if(isset($_GET['lang']))
    $m = '?lang=' . $_GET['lang'] . '&';
  else
    $m = '?';
else
  $m = '&';

function logged_in() {
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

if (logged_in()) {
	wp_redirect($redirect_to_resources);
	exit;
}

function oer_username_exists($username) {
  global $wpdb;
  $user_row = $wpdb->get_results(
		$wpdb->prepare( "SELECT username FROM OER_User WHERE username = %s",$username));
  
  if($user_row) 
    return true;
  else
    return false;
}

function oer_email_exists($email) {
  global $wpdb;
  $email_row = $wpdb->get_results(
		$wpdb->prepare("SELECT email FROM OER_User WHERE email = %s", $email));
  
  if($email_row) 
    return true;
  else
    return false;
}

if (isset($_POST['submit_register'])) {
    
    $reg_full_name = sanitize_text_field($_POST['oer_reg_full_name']);
    $reg_email = sanitize_text_field($_POST['oer_reg_email']);
    $reg_username = sanitize_text_field($_POST['oer_reg_username']);
    $reg_password = $wpdb->escape($_POST['oer_reg_password']);
    $reg_confirmpassword = $wpdb->escape($_POST['oer_reg_confirmpassword']);
    $reg_institution = sanitize_text_field($_POST['oer_reg_institution']);
    $reg_link_to_site = sanitize_text_field($_POST['oer_reg_link_to_site']);
    $reg_short_bio = sanitize_text_field($_POST['oer_reg_short_bio']);
    
    $reg_username = sanitize_user( $reg_username );
    $reg_email = sanitize_email( $reg_email ); 
    
    $username_exists = oer_username_exists( $reg_username );
    $email_exists = oer_email_exists( $reg_email );
    
    if( isset($reg_full_name) && $reg_full_name != ''
        && isset($reg_email) && $reg_email != '' && is_email($reg_email) 
        && isset($reg_username) && $reg_username != '' 
        && isset($reg_password) && $reg_password != '' 
        && isset($reg_confirmpassword) && $reg_confirmpassword != ''
        && $reg_password == $reg_confirmpassword 
        && ! $username_exists && ! $email_exists) {
        
        if($_FILES["oer_reg_photo"]["error"] == 0) {
            
            $dot = strrpos($_FILES["oer_reg_photo"]["name"], '.');
            
            $extension =  substr($_FILES["oer_reg_photo"]["name"], $dot);
            
            $_FILES["oer_reg_photo"]["name"] = $user_id . "_profile_pic" . $extension;
            
            $filename = $_FILES["oer_reg_photo"]["name"];
            
            $upload_folder = get_option('oerplugin_root_directory') . "/oer_uploads/profile_pics/";
            
            $reg_photo = site_url("/wp-content/plugins/oerplugin/oer_uploads/profile_pics/". $filename);
            
            move_uploaded_file($_FILES["oer_reg_photo"]["tmp_name"], $upload_folder . $filename);
        }
        
        $confirm_code = rand(0, 2147483647);
        
        $hasher = new PasswordHash(8,TRUE);
        $hashed_password = $hasher->HashPassword($reg_password);
         
        $registration_data = array( 
	    'full_name' => $reg_full_name, 
            'email' => $reg_email,
            'username' => $reg_username,
            'password' => $hashed_password,
            'confirm_code' => $confirm_code,
	    );
	    
	    
	    if(isset($reg_institution)) {
	        $registration_data['institution'] = $reg_institution;
	    }
	    if(isset($reg_link_to_site)) {
	        $registration_data['link_to_site'] = $reg_link_to_site;
	    }
	    if(isset($reg_photo)) {
	        $registration_data['photo_url'] = $reg_photo;
	    }
	    if(isset($reg_short_bio)) {
	        $registration_data['short_bio'] = $reg_short_bio;
	    }
	    
	    $result = $wpdb->insert( 
		    'OER_User',
		    $registration_data 
	    );

	    if($result) {
	      $to = $reg_email;
	      $subject = __('Регистрација за отворени образовни ресурси.', 'oerplugin');
	      $mail_message = __('Кликнете на следниот линк за да ја активирате вашата корисничка сметка.', 'oerplugin');
	      $mail_message .= "\r\n";
	      $mail_message .= get_permalink(get_option("oerplugin_login_page_id")) . $m . "reg=" . $confirm_code;
	      $mail_message .= "\r\n";
	      $mail_message .= __('Активацискиот код ќе биде достапен 1 недела.', 'oerplugin');
	      $mail_message .= "\r\n";
	      $mail_message .= "\r\n";
	       
	      if(!wp_mail($to, $subject, $mail_message)) {
		  $fail_message = __('Не може да се прати е-маил.', 'oerplugin');
	      }
	      else {
	      
		  $message = __('Регистрацијата е успешна. Треба да добиете е-маил со линк преку кој ќе ја потврдите вашата регистрација.', 'oerplugin');
	      }
	    } //end-inner-inner-if
	
	    else {
	        $fail_message = __('Има проблем со регистрацијата.', 'oerplugin');
	    } //end-inner-inner-else
    } 
    else {
    if( ! is_email($reg_email))
      $fail_message = __('Е-маил адресата што ја внесовте содржи невалидни карактери.', 'oerplugin');
    else if ( $username_exists )
      $fail_message = __('Веќе постои корисник регистриран со тоа корисничко име.', 'oerplugin');
    else if ( $reg_password != $reg_confirmpassword )
      $fail_message = __('Лозинките не се совпаѓаат.', 'oerplugin');
    else if ( $email_exists )
      $fail_message = __('Веќе постои корисник регистриран со таа е-маил адреса.', 'oerplugin');
    else
      $fail_message = __('Ги немате пополнето сите задолжителни полиња.', 'oerplugin');

    } //end-middle-else
    
} //end-outer-if

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
                        <?php _e('Регистрирајте се на<br /> Отворени Образовни Ресурси', 'oerplugin'); ?>
                    </span>
                    <br />
                    <br />

                    <form action="" method="post" enctype="multipart/form-data">

                        <div>
                            <?php _e('Име и презиме:', 'oerplugin'); ?>
                            <span style="color: red;">*</span>
                            <br />
                            <input type="text" name="oer_reg_full_name" maxlength="30" value="<?php if(isset($reg_full_name)) echo esc_attr($reg_full_name); ?>" style="width: 100%;" />
                        </div>

                        <div>
                            <?php _e('Е-Маил:', 'oerplugin'); ?>
                            <span style="color: red;">*</span>
                            <br />
                            <input type="text" name="oer_reg_email" maxlength="50" value="<?php if(isset($reg_email)) echo esc_attr($reg_email); ?>" style="width: 100%;" />
                        </div>

                        <div>
                            <?php _e('Корисничко име:', 'oerplugin'); ?>
                            <span style="color: red;">*</span>
                            <br />
                            <input type="text" name="oer_reg_username" maxlength="30" value="<?php if(isset($reg_username)) echo esc_attr($reg_username); ?>" style="width: 100%;" />
                        </div>

                        <div>
                            <?php _e('Нова лозинка:', 'oerplugin'); ?>
                            <span style="color: red;">*</span>
                            <br />
                            <input type="password" name="oer_reg_password" maxlength="30" value="<?php if(isset($reg_password)) echo esc_attr($reg_password); ?>" style="width: 100%;" />
                        </div>

                        <div>
                            <?php _e('Потврдете ја новата лозинка:', 'oerplugin'); ?>
                            <span style="color: red;">*</span>
                            <br />
                            <input type="password" name="oer_reg_confirmpassword" maxlength="30" value="<?php if(isset($reg_confirmpassword)) echo esc_attr($reg_confirmpassword); ?>" style="width: 100%;" />
                        </div>
                        
                        <div>
                            <?php _e('Институција:', 'oerplugin'); ?><br /><br />
                            <input type="text" name="oer_reg_institution" maxlength="30" value="<?php if(isset($reg_institution)) echo esc_attr($reg_institution); ?>" style="width: 100%;" />
                        </div>
                        
                        <div>
                            <?php _e('Линк до вашата веб-страница:', 'oerplugin'); ?><br />
                            <input type="text" name="oer_reg_link_to_site" value="<?php  if(isset($reg_link_to_site)) echo esc_attr($reg_link_to_site); ?>" style="width: 100%;" />
                        </div>
                        
                        <div>
                            <?php _e('Кратка биографија:', 'oerplugin'); ?><br />
                            <textarea rows="5" name="oer_reg_short_bio" style="width: 100%;"><?php if(isset($reg_short_bio)) echo esc_attr($reg_short_bio); ?></textarea>
                        </div>
                        
                        <div>
                            <?php _e('Прикачете фотографија:', 'oerplugin'); ?>
                            <input type="file" name="oer_reg_photo" accept="image/*" style="" />
                        </div>
                        
                        <span style="color: red;">* <?php _e('Овие полиња се задолжителни:', 'oerplugin'); ?></span>
                        <br />
                        <br />
                        <input type="submit" name="submit_register" value="<?php _e('Регистрација', 'oerplugin'); ?>" />
                        
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
