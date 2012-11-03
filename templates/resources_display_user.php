<?php

$_POST = stripslashes_deep( $_POST );
$_GET = stripslashes_deep( $_GET );

function user_exists() {
  if( isset($_COOKIE['oer_user_id']) && isset($_COOKIE['oer_cookie_id']) ){
    $user_id = intval($_COOKIE['oer_user_id']);
    $cookie_id = intval($_COOKIE['oer_cookie_id']);
    
    if($user_id && $cookie_id){
      global $wpdb;
      $logged_in = $wpdb->get_results(
          $wpdb->prepare("SELECT user_id, cookie_id from OER_Userlog WHERE cookie_id = %d AND user_id = %d",$cookie_id,$user_id));
      
      if($logged_in)
	return true;
    }
  }
  return false;
}

function resources_display_user() {
    global $wpdb;
    $resources_page_id = get_option("oerplugin_myresources_page_id");
    $resources_url = get_permalink($resources_page_id);

    if( get_option( 'permalink_structure') )
      if(isset($_GET['lang']))
        $m = '?lang=' . $_GET['lang'] . '&';
      else
        $m = '?';
    else
      $m = '&'; 
    
    if(user_exists()) {
        $user_id_cookie = $_COOKIE['oer_user_id'];
        
        $output = "";

        $query_all_resources_from_user = "SELECT OER_Resource.id AS ID,";
        $query_all_resources_from_user .= " OER_Resource.title AS Title,";
        $query_all_resources_from_user .= " OER_Resource.icon AS Icon,";
        $query_all_resources_from_user .= " OER_Resource.description AS Description,";
        $query_all_resources_from_user .= " OER_Resource.user_id,";
        $query_all_resources_from_user .= " OER_Language.title_mk AS Language,";
        $query_all_resources_from_user .= " OER_User.full_name AS UploadedBy";
        $query_all_resources_from_user .= " FROM OER_Resource, OER_User, OER_Language";
        $query_all_resources_from_user .= " WHERE OER_Resource.user_id = OER_User.id";
        $query_all_resources_from_user .= " AND OER_Resource.language_id = OER_Language.id";
        $query_all_resources_from_user .= " AND OER_Resource.user_id = %d";

        $result = $wpdb->get_results(
            $wpdb->prepare($query_all_resources_from_user,$user_id_cookie));
        $i = 0;
        $len = count($result);
        if($result) {
	        foreach($result as $row) {
            if ($i == $len - 1) {
                $output .= "<div id=\"all_resources\" style=\"padding: 10px; margin-bottom: 5px;\">";
            }
            else {
                $output .= "<div id=\"all_resources\" style=\"padding: 10px; margin-bottom: 5px; border-bottom: dotted 1px;\">";
            }

            $output .= "<div id=\"float\" style=\"float: left;\">";
            $output .= "<img src='" . $row->Icon  . "' />";
            $output .= "</div>"; //end div icon

            $output .= "<div id=\"text\" style=\"margin-left: 70px;\">";

            $output .= "<a style=\"font-weight: bold;\" href=\"{$resources_url}{$m}resource_id={$row->ID}\">";
            //$output .= "<span id=\"title\">";
		    $output .= $row->Title;
            //$output .= "</span>";
            $output .= "</a>";

            //$output .= "<hr style=\"margin-bottom: 0;\"/>";

		    $output .= "<br />";
		    $output .= "<strong>" . __('Опис: ', 'oerplugin') . "</strong>" . $row->Description;
		    $output .= "<br />";
		    $output .= "<strong>" . __('Јазик: ', 'oerplugin') . "</strong>" .  $row->Language;
		    $output .= "<br />";
		    $output .= "<strong>" . __('Прикачен од: ', 'oerplugin') . "</strong>" . $row->UploadedBy;
		    $output .= "</br>";
		    
		    $myresources_page_id = get_option("oerplugin_myresources_page_id");
            $redirect_to_myresources = get_permalink( $myresources_page_id );
            
            $edit_page_id = get_option("oerplugin_editresource_page_id");
            $redirect_to_edit = get_permalink( $edit_page_id );
            
            if( get_option( 'permalink_structure') )
                  $m = '?';
                else
                  $m = '&';
		    $edit = __('Променете го ресурсот', 'oerplugin');
		    $output .= "<a href=\"{$redirect_to_edit}{$m}res_id={$row->ID}\">{$edit}</a>";
		    $output .= "</br>";
		    $del = __('Избришете го ресурсот', 'oerplugin');
		    $msg = __('Дали сте сигурни дека сакате да го избришете ресурсот?', 'oerplugin');
            $output .= "<a href=\"{$redirect_to_myresources}{$m}del_res_id={$row->ID}\" onClick=\"return confirm('{$msg}')\">{$del}</a>";

            $output .= "</div>"; //end div id text

		    $output .= "</div>"; //end div id all resources


            $i++;
	    } //end-foreach
	        echo $output;
        } //end-inner-if
        else {
            _e('Немате поставено ресурси.', 'oerplugin');
        } //end-inner-else
    } //end-outer-if
    
    else {
        _e('Мора да бидете логирани за да ги прегледате вашите ресурси.', 'oerplugin');
    } //end-inner-else
    
} //end-function

?>
