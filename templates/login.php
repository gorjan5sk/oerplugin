<?php
/*
    Template Name: OER Login
*/

// AUTHENTICATION PART

require_once( ABSPATH . 'wp-includes/class-phpass.php');

global $wpdb;

$_POST = stripslashes_deep( $_POST );
$_GET = stripslashes_deep( $_GET );

$login_page_id = get_option("oerplugin_login_page_id");
$redirect_to_login = get_permalink( $login_page_id );

$resources_page_id = get_option("oerplugin_resources_page_id");
$redirect_to_resources = get_permalink( $resources_page_id );

$emailresetpassword_url = get_permalink( get_option( "oerplugin_emailresetpassword_page_id"));

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

function confirm_logged_in() {
    if (!logged_in()) {
	    wp_redirect($redirect_to_login);
	    exit;
    }
}

if (logged_in()) {
	wp_redirect($redirect_to_resources);
	exit;
}


if (isset($_POST['submit_login'])) { //Form has been submitted.

    $username = sanitize_user( sanitize_text_field( $_POST['oer_username'] ) );
    $password = $_POST['oer_password'];
    
    $query = "SELECT * from OER_User where username = %s AND activated = 1 LIMIT 1";

    $result = $wpdb->get_results(
        $wpdb->prepare($query,$username));

    if($result) {
      $r = $result[0];
      $hasher = new PasswordHash(8,TRUE);
      echo var_dump($r->password) . " ; " . var_dump($password);
 
      if($hasher->CheckPassword($password, $r->password)){

        setcookie('oer_user_id', $r->id, time() + 60 * 60 * 24 * 14, '/');
        
        $cookie_id = rand(0, 2147483647);
	      setcookie('oer_cookie_id', $cookie_id, time() + 60 * 60 * 24 * 14, '/');
	      
        $wpdb->insert( 'OER_Userlog', 
	        array ( 'user_id' => $r->id, 'cookie_id' => $cookie_id), 
	        array ( '%d', '%d'));
        

        wp_redirect($redirect_to_resources);
        exit;

      } else {
        $message = __('Корисничкото име или лозинката не се точни!<br />
            Бидете сигурни дека копчето Caps Lock ви е исклучено и пробајте повторно.', 'oerplugin');
      }
    }
    else {
        $message = __('Корисничкото име или лозинката не се точни!<br />
            Бидете сигурни дека копчето Caps Lock ви е исклучено и пробајте повторно.', 'oerplugin');
    }
}
else if(isset($_GET['reg'])) {
  $confirm_code = intval($_GET['reg']);
  if($confirm_code){
    $query = "SELECT OER_User.id as ID,";
    $query .= " OER_User.username as Username";
    $query .= " FROM OER_User";
    $query .= " WHERE confirm_code = %d";
    $query .= " AND OER_User.activated = 0";
    $query .= " LIMIT 1";
    
    $result = $wpdb->get_results($wpdb->prepare($query,$confirm_code));
    
    if($result) {
        foreach($result as $r) {
            setcookie('oer_user_id', $r->ID, time() + 60 * 60 * 24 * 14, '/');
            $cookie_id = rand(0, 2147483647);
	    setcookie('oer_cookie_id', $cookie_id, time() + 60 * 60 * 24 * 14, '/');
	    $wpdb->update('OER_User',
	      array('activated' => '1'),
	      array('id' => "{$r->ID}"),
	      array('%d'),
	      array('%d'));
	    $wpdb->insert( 'OER_Userlog', 
	      array ( 'user_id' => $r->ID, 'cookie_id' => $cookie_id), 
	      array ( '%d', '%d'));
        }

        wp_redirect($redirect_to_resources);
        exit;
    }
    else {
        $message = __('Погрешен активациски код.', 'oerplugin');
    }
  } else 
    $message = __('Погрешен активациски код.', 'oerplugin');
}
else { // Form has not been submitted.
    if (isset($_GET['logout']) && $_GET['logout'] == 1) {
        $logout_message = __('Успешно сте одјавени.', 'oerplugin');
    }
    $username = "";
    $password = "";
}

get_header();

?>

<div id="primary">
    <div id="content">
        
        <div id="wrapper" style="width: 100%;">
                    
            <?php if(isset($message)) { ?>
            <div id="info" style="background: #FAACAC; padding: 10px; text-align: center;">
                <?php echo $message; ?>
            </div>
            <?php }
            else if(isset($logout_message)) { ?>
            <div id="info" style="background: #38F590; padding: 10px; text-align: center;">
                <?php echo $logout_message; ?>
            </div>
            <?php } ?>    
        
            <div id="login" style="background: #f5f5f5; width: 70%; padding: 20px; margin-left: auto; margin-right: auto;">
            
            <div id="holder" style=" width: 360px; margin-left: auto; margin-right: auto;">
            
                <form action="" method="post">
                    <span style="font-size: 1.3em;">
                        <?php _e('Најавете се на Отворени образовни ресурси', 'oerplugin'); ?>
                    </span>
                    <br />
                    <br />

                    <div>
                        <?php _e('Корисничко име:', 'oerplugin'); ?><br />
                        <input type="text" name="oer_username" maxlength="30" value="<?php echo esc_attr($username); ?>" style="width: 100%;" />
                    </div>

                    <div style="clear: both;">
                        <?php _e('Лозинка:', 'oerplugin'); ?><br />
                        <input type="password" name="oer_password" maxlength="30" value="<?php echo esc_attr($password); ?>" style="width: 100%;" />
                    </div>
                    <br />
                    <input type="submit" name="submit_login" value="Најава" />

                </form>
                <br /> 
                <a href="<?php echo $emailresetpassword_url  ?>"><?php _e('Ја заборав лозинката?','oerplugin') ?></a>
            </div> <!-- end-holder -->    
                
            </div> <!-- end-login -->
            
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
