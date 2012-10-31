<?php
get_header();

$_POST = stripslashes_deep( $_POST );
$_GET = stripslashes_deep( $_GET );

if( get_option( 'permalink_structure') )
    if(isset($_GET['lang']))
      $m = '?lang=' . $_GET['lang'] . '&';
    else
      $m = '?';
  else
    $m = '&';

if(isset($_POST['oer_email']) && is_email($_POST['oer_email'])) {
  
  $email = $_POST['oer_email'];
  global $wpdb;
  
  $user_row = $wpdb->get_results( 
      $wpdb->prepare("SELECT * FROM OER_User WHERE email = %s", $email) );
  
  if($user_row) {
    $code_id = rand(0, 2147483647);
    $user_id = $user_row[0]->id;
    
    $wpdb->insert('OER_Resetpassword', 
      array( 'user_id'=>$user_id, 'code_id'=>$code_id ),
      array( '%d','%d'));
   
    $to = $user_row->email;
    $subject = __('Отворени Образовни Ресурси - Ресетирање на лозинка','oerplugin');
    $e_message = __('Ја добивте оваа порака бидејќи побаравте ресетирање на лозинката. За ресетирање кликнете на следниот линк: ','oerplugin');
    $e_message .= "\r\n";
    $e_message .= get_permalink( get_option('oerplugin_resetpassword_page_id') ) . $m . "code_id=" . $code_id;
    $e_message .= "\r\n";
    $e_message .= "\r\n";
  
    if( !wp_mail( $email, $subject, $e_message) ) {
      $fail_message = __('Грешка при праќањето e-mail. Ве молиме контактирајте го администраторот.','oerplugin');
    } else {
      $message = __('Проверете го вашиот e-mail за линк за активација на лозинката.','oerplugin');
    }
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
        <?php } ?>
       
         <div id="login" style="background: #f5f5f5; width: 70%; padding: 20px; margin-left: auto; margin-right: auto;">
         <div id="holder" style=" width: 360px; margin-left: auto; margin-right: auto;"> 
            <form action="" method="POST">
                <span style="font-size: 1.3em;">
                  <?php _e('Внесете го вашиот e-mail за ресетирање на лозинката', 'oerplugin'); ?>
                </span>
                <br />
                <br />
                <div>
                  E-mail:
                  <input type="text" name="oer_email" maxlength="50" value="<?php echo esc_attr($email); ?>" style="width: 100%;" />
                  <input type="submit" name="submit" value="<?php _e('Прати','oerplugin') ?>"/>
                </div>
            </form> 
          </div> <!-- end-holder -->      
        </div> <!-- end-login -->  

        <div id="useless" style="clear: both;">
                &nbsp;
        </div> <!-- end-useless -->  

        </div> <!-- end-wrapper -->

    </div> <!-- end-content -->
</div> <!-- end-primary -->



<?php 
get_footer();
?>
