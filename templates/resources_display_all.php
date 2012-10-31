<?php

function resources_display_all() {

    $_POST = stripslashes_deep( $_POST );
    $_GET = stripslashes_deep( $_GET );
    
    global $wpdb;
    $resources_page_id = get_option("oerplugin_resources_page_id");
    $resources_url = get_permalink($resources_page_id);

    if( get_option( 'permalink_structure') )
      if(isset($_GET['lang']))
        $m = '?lang=' . $_GET['lang'] . '&';
      else
        $m = '?';
    else
      $m = '&';

    $output = "";

    $query_all_resources = "SELECT OER_Resource.id AS ID,";
    $query_all_resources .= " OER_Resource.title AS Title,";
    $query_all_resources .= " OER_Resource.icon AS Icon,";
    $query_all_resources .= " OER_Resource.description AS Description,";
    $query_all_resources .= " OER_Language.title_mk AS Language,";
    $query_all_resources .= " OER_User.full_name AS UploadedBy";
    $query_all_resources .= " FROM OER_Resource, OER_User, OER_Language";
    $query_all_resources .= " WHERE OER_Resource.user_id = OER_User.id";
    $query_all_resources .= " AND OER_Resource.language_id = OER_Language.id";
    $query_all_resources .= " AND OER_Resource.approved = 1";

    // If it's a search string
    if( isset($_GET['keyword']) ){
      $keywords = $wpdb->escape(sanitize_text_field(trim( $_GET['keyword'], " " )));

      // Keyword length is between 3 and 200 chars
      if( strlen($keywords) >= 3 && strlen($keywords) <=200 ){
	$search_string = "+" . str_replace(" ", " +", $keywords);
	$query_all_resources .= " AND MATCH (title, description, note, author) AGAINST ('{$search_string}' IN BOOLEAN MODE)";
      }

      $parameters = $wpdb->get_results("SELECT * FROM OER_Parameters ORDER BY position");
      if($parameters) {
	foreach($parameters as $p) {
	  if($p->position == -1) {
		  continue;
	  }

	  $values_parameters = $wpdb->get_results("SELECT * FROM {$p->table_name}");

	  if($values_parameters){
	    foreach($values_parameters as $v) {
	      $key = (string)$p->id . "_" . (string)$v->id;
	      $checked = (isset( $_GET[$key] ) && $_GET[$key]) ? "1" : "0";

	      if($checked == "1")
		$query_all_resources .= " AND OER_Resource.{$p->resources_name} = {$v->id}";
	    }
	  }
	}
      } //if $parameters
    } //isset($_GET['keyword'])

    $result = $wpdb->get_results($query_all_resources);
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
            
            $output .= "<div id=\"icon\" style=\"float: left; \">";
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
		    $output .= __('Опис: ', 'oerplugin') . $row->Description;
		    $output .= "<br />";
		    $output .= __('Јазик: ', 'oerplugin') . $row->Language;
		    $output .= "<br />";
		    $output .= __('Прикачен од: ', 'oerplugin') . $row->UploadedBy;

            $output .= "</div>"; //end div id text

		    $output .= "</div>"; //end div id all resources


            $i++;
	    } //end-foreach
	    echo $output;
    } //end-inner-if
    else {
        _e('Не се пронајдени ресурси.', 'oerplugin');
    } //end-inner-else
} //end-function

?>
