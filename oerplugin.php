<?php

/*
Plugin Name: Open Educational Resources Plugin
Plugin URI:
Description: A plugin for managing Educational Resources.
Author: Mello Creations
Author URI: http://mello.mk
Version: 1.0
*/

global $wp_version;

if( !version_compare($wp_version, "3.0", ">=") ) {
  die("You need at least version 3.0 of Wordpress to use OER Plugin");
}

/* <DEBUGGING> */
function oer_save_error() {
  file_put_contents( dirname( __FILE__) . "/error.log", ob_get_contents() );
}
add_action( 'activated_plugin', 'oer_save_error');
/* </DEBUGGING> */

require_once('page_management.php');
require_once('template_setup.php');
require_once('install_tables.php');
require_once('oer_init.php');
require_once('admin_menu.php');

register_activation_hook( __FILE__ , 'oerplugin_activate');
register_deactivation_hook( __FILE__, 'oerplugin_deactivate' );

add_action('init', 'oer_init');
add_action('admin_menu', 'oerplugin_admin_menu');
add_action('admin_init', 'oerplugin_admin_init');

add_filter("page_template", "oer_template_setup");

/**
* Create Resources Page and register Template for it
*/ 
function oerplugin_activate() {
  
  add_option('oerplugin_root_directory',dirname(__FILE__));
  
  $resources_id = oer_create_page( __('Ресурси','oerplugin'), "resources", 2000); 
  
  $user_id = oer_create_page( __('Лични ресурси','oerplugin') , "useroer", 2010);
  $user_upload_id = oer_create_page( __('Прикачи ресурс','oerplugin') , "upload", 2011, $user_id);
  $user_resources_id = oer_create_page( __('Раководење со лични ресурси','oerplugin'), "myresources", 2012, $user_id);
  $user_profile_id = oer_create_page( __('Профил','oerplugin'), "profile", 2013, $user_id);
  $user_logout_id = oer_create_page(__('Одјава','oerplugin'), "logout", 2014, $user_id);
  $edit_resource_id = oer_create_page(__('Промени ресурс','oerplugin'), "editresource", 2015, $user_id);
  
  $login_id = oer_create_page( __('Најава','oerplugin'), "login", 2020);
  $registration_id = oer_create_page( __('Регистрација','oerplugin'), "registration", 2030);
  
  $resetpassword_id = oer_create_page( __('Ресетирање на лозинка','oerplugin'),'resetpassword', 2040);
  
  $emailresetpassword_id = oer_create_page (__('E-mail за ресетирање на лозинка', 'oerplugin'), 'emailresetpassword', 2041);
  oer_install_tables();
  
  wp_schedule_event( current_time( 'timestamp' ), "daily" , 'userlog_cleanup');

}

/**
* Remove Resources Page and unregister Template
*/ 
function oerplugin_deactivate() {
  oer_delete_page("resources");
  oer_delete_page("useroer");
  oer_delete_page("upload");
  oer_delete_page("myresources");
  oer_delete_page("profile");
  oer_delete_page("logout");
  oer_delete_page("login");
  oer_delete_page("registration");
  oer_delete_page("editresource");
  oer_delete_page("resetpassword");
  oer_delete_page("emailresetpassword");
}

?>
