<?php
get_header();

require_once( ABSPATH . 'wp-includes/class-phpass.php');

global $wpdb;

$_POST = stripslashes_deep( $_POST );
$_GET = stripslashes_deep( $_GET );

$redirect_login = get_permalink( get_option("oerplugin_login_page_id") );

if( get_option( 'permalink_structure') )
    if(isset($_GET['lang']))
      $m = '?lang=' . $_GET['lang'] . '&';
    else
      $m = '?';
  else
    $m = '&';

$new_password = $_POST['oer_password'];
$code_id = intval($_POST['oer_code_id']);

$verify_code_id = intval($_GET['code_id']);

$verify_row = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM OER_Resetpassword WHERE code_id = %d",$verify_code_id) );

if(! $verify_row){
  $verify_code_id = false;
  wp_redirect($redirect_login);
  exit;
} 

if(isset($_POST['oer_password']) && isset($_POST['oer_confirmpassword']) && 
    isset($code_id) && 
    ( $_POST['oer_password'] == $_POST['oer_confirmpassword'] ) ) {
  
  $code_row = $wpdb->get_results( 
      $wpdb->prepare("SELECT user_id FROM OER_Resetpassword WHERE code_id = %d LIMIT 1", $code_id) );
  
  if($code_row) {
    $hasher = new PasswordHash(8,TRUE);
    $hashed_password = $hasher->HashPassword($new_password);
    
    $user_id = $code_row[0]->user_id;
    $rows_updated = $wpdb->update('OER_User',
            array( 'password'=>$hashed_password ),
            array( 'id'=>$user_id ),
            '%s',
            '%d');
    $wpdb->query(
        $wpdb->prepare("DELETE * FROM OER_Resetpassword WHERE user_id = %s", $user_id));
    
    $message=__('Успешно ја променивте лозинката.','oerplugin');
  } else {
    $fail_message = __('Грешка при промена на лозинката','oerplugin');
  }
}

?>

<div id="primary">
    <div id="content">

        <div id="wrapper" style="width: 100%;">
        
        <?php if(isset($message)) { ?>
            <div id="info" style="background: #38f590; padding: 10px; text-align: center;">
                <?php echo $message; ?>
            </div>
        <?php } else if(isset($fail_message)) { ?>
            <div id="info" style="background: #FAACAC; padding: 10px; text-align: center;">
                <?php echo $fail_message; ?>
            </div>
        <?php } else { ?>
       
         <div id="login" style="background: #f5f5f5; width: 70%; padding: 20px; margin-left: auto; margin-right: auto;">
         <div id="holder" style=" width: 360px; margin-left: auto; margin-right: auto;"> 
            <form action="" method="POST">
                <span style="font-size: 1.3em;">
                  <?php _e('Внеси нова лозинка', 'oerplugin'); ?>
                </span>
                <br />
                <br />
                <div>
                  <?php _e('Нова лозинка','oerplugin') ?>
                  <input type="password" name="oer_password" maxlength="30" value="" style="width: 100%;" />
                  <?php _e('Повторно новата лозинка','oerplugin'); ?>
                  <input type="password" name="oer_confirmpassword" maxlength="30" value="" style="width: 100%;" />
                  <input type="submit" name="submit" value="<?php _e('Ресетирај','oerplugin') ?>"/>
                  <input type="hidden" name="oer_code_id" value="<?php 
                  if($verify_code_id) echo $verify_code_id ?>" />
                </div>
            </form> 
          </div> <!-- end-holder -->      
        </div> <!-- end-login -->  
        <?php } ?>
        <div id="useless" style="clear: both;">
                &nbsp;
        </div> <!-- end-useless -->  

        </div> <!-- end-wrapper -->

    </div> <!-- end-content -->
</div> <!-- end-primary -->



<?php 
get_footer();
?>
