<?php

function oer_template_setup( $page_template ) {
  global $post;
  
  if($post->post_type == "page") {
    if($post->post_name == "resources") {
      $page_template = dirname( __FILE__ ) . "/templates/resources.php";
    } else if($post->post_name == "useroer") {
      $page_template = dirname( __FILE__ ) . "/templates/myresources.php";
    } else if($post->post_name == "upload") {
      $page_template = dirname( __FILE__ ) . "/templates/upload.php";
    } else if($post->post_name == "myresources") {
      $page_template = dirname( __FILE__ ) . "/templates/myresources.php";
    } else if($post->post_name == "profile") {
      $page_template = dirname( __FILE__ ) . "/templates/profile.php";
    } else if($post->post_name == "logout") {
      $page_template = dirname( __FILE__ ) . "/templates/logout.php";
    } else if($post->post_name == "login") {
      $page_template = dirname( __FILE__ ) . "/templates/login.php";
    } else if($post->post_name == "registration") {
      $page_template = dirname( __FILE__ ) . "/templates/registration.php";
    } else if($post->post_name == "editresource") {
      $page_template = dirname( __FILE__ ) . "/templates/editresource.php";
    } else if($post->post_name == "resetpassword") {
      $page_template = dirname( __FILE__ ) . "/templates/resetpassword.php";
    } else if($post->post_name == "emailresetpassword") {
      $page_template = dirname( __FILE__ ) . "/templates/emailresetpassword.php";
    } 
  }  

  return $page_template;
}

?>
