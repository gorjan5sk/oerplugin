<?php

$redirect_to_login = get_permalink( get_option("oerplugin_login_page_id"));

$_POST = stripslashes_deep( $_POST );
$_GET = stripslashes_deep( $_GET );

function useritem_logged_in() {
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


$user_id = intval($_COOKIE['oer_user_id']);

if(!useritem_logged_in()) {
  wp_redirect($redirect_to_login);
  exit;
}

function resources_display_item_user($id) {
  global $wpdb;
  $output = "";
    
    
  $user_id = intval($_COOKIE['oer_user_id']);
    
  $myresources_page_id = get_option("oerplugin_myresources_page_id");
  $redirect_to_myresources = get_permalink( $myresources_page_id );

  if( get_option( 'permalink_structure') )
    if(isset($_GET['lang']))
      $m = '?lang=' . $_GET['lang'] . '&';
    else
      $m = '?';
  else
    $m = '&';

?>

<script language="javascript">

function decision(message, url){
if(confirm(message)) location.href = url;
}

</script>

<?php 
    $query_resource = "SELECT OER_Resource.id AS ID,";
    $query_resource .= " OER_Resource.title AS Title,";
    $query_resource .= " OER_Resource.description AS Description,";
    $query_resource .= " OER_Resource.author AS Author,";
    $query_resource .= " OER_Resource.data_file AS File,";
    $query_resource .= " OER_Resource.data_link AS Link,";
    $query_resource .= " OER_Resource.data_embed AS Embed,";
    $query_resource .= " OER_User.full_name AS UserFullName,";
    $query_resource .= " OER_Subject_Area.title_mk AS Subject_Area,";
    $query_resource .= " OER_Subject.title_mk AS Subject,";
    $query_resource .= " OER_Grade_Level.title_mk AS Grade_Level,";
    $query_resource .= " OER_Resource_Type.title_mk AS Resource_Type,";
    $query_resource .= " OER_Resource_Format.title_mk AS Resource_Format,";
    $query_resource .= " OER_Language.title_mk AS Language,";
    $query_resource .= " OER_Licence.title_mk AS Licence";
    $query_resource .= " FROM OER_Resource,";
    $query_resource .= " OER_User,";
    $query_resource .= " OER_Subject_Area,";
    $query_resource .= " OER_Subject,";
    $query_resource .= " OER_Grade_Level,";
    $query_resource .= " OER_Resource_Type,";
    $query_resource .= " OER_Resource_Format,";
    $query_resource .= " OER_Language,";
    $query_resource .= " OER_Licence";
    $query_resource .= " WHERE OER_Resource.id = %d AND";
    $query_resource .= " OER_User.id = OER_Resource.user_id AND";
    $query_resource .= " OER_User.id = %d AND";
    $query_resource .= " OER_Subject_Area.id = OER_Resource.subject_area_id AND";
    $query_resource .= " OER_Subject.id = OER_Resource.subject_id AND";
    $query_resource .= " OER_Grade_Level.id = OER_Resource.grade_level_id AND";
    $query_resource .= " OER_Resource_Type.id = OER_Resource.resource_type_id AND";
    $query_resource .= " OER_Resource_Format.id = OER_Resource.resource_format_id AND";
    $query_resource .= " OER_Licence.id = OER_Resource.Licence_id AND";
    $query_resource .= " OER_Language.id = OER_Resource.language_id";

    $result = $wpdb->get_results($wpdb->prepare($query_resource,$id,$user_id));

    if($result) {
        foreach($result as $row) {
            $output .= "<div id=\"resource\" style=\"padding: 10px; margin-bottom: 5px; color: black;\">";
            $output .= "<span style=\"font-size: 2.5em;\">";
		    $output .= $row->Title;
		    $output .= "</span>";
		    $output .= "<span>";
		    $output .= " - " . __('прикачен од', 'oerplugin') . " " . $row->UserFullName;
		    $output .= "</span>";        
           
            
		    $output .= "<hr />";

		    $data_file = $row->File;
		    if($data_file != '') {
		        $output .= "<br />";
		        $output .= "<a href=\"{$data_file}\">";
		        $output .= __('Превземи го ресурсот', 'oerplugin');
		        $output .= "</a>";
		    }

            $data_link = $row->Link;
            if($data_link != '') {
                $output .= "<br />";
                $output .= __('Линк до ресурсот: ', 'oerplugin');
                $output .= "<a href=\"{$data_link}\" target=\"_blank\">Ресурс</a>";
            }

            $data_embed = $row->Embed;
            if($data_embed != '') {
                $output .= "<br />";
                $output .= $data_embed;
            }

		    $output .= "<br />";
		    $output .= __('Опис: ', 'oerplugin') . $row->Description;
		    $output .= "<br />";
            $output .= __('Автор: ', 'oerplugin') . $row->Author;
		    $output .= "<br />";
            $output .= __('Област: ', 'oerplugin') . $row->Subject_Area;
		    $output .= "<br />";
            $output .= __('Предмет: ', 'oerplugin') . $row->Subject;
		    $output .= "<br />";
            $output .= __('Ниво на образование: ', 'oerplugin') . $row->Grade_Level;
		    $output .= "<br />";
		    $output .= __('Јазик: ', 'oerplugin') . $row->Language;
		    $output .= "<br />";
            $output .= __('Тип на ресурс: ', 'oerplugin') . $row->Resource_Type;
		    $output .= "<br />";
            $output .= __('Формат: ', 'oerplugin') . $row->Resource_Format;
		    $output .= "<br />";
            $output .= __('Лиценца: ', 'oerplugin') . $row->Licence;
		    $output .= "<br />";

            
            
		    $output .= "</div>";

        } //end-foreach
        
        
        
        echo $output;
    } //end-if
    else {
        wp_redirect($redirect_to_login);
        exit;
    } //end-else
}

?>
