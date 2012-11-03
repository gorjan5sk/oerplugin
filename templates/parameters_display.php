<?php

function parameters_display() {

    $_POST = stripslashes_deep( $_POST );
    $_GET = stripslashes_deep( $_GET );    

    $resources_page_id = get_option("oerplugin_resources_page_id");
    $resources_url = get_permalink($resources_page_id);

    global $wpdb;
    
    $is_albanian = false;
    if( isset($_GET['lang']) && $_GET['lang'] == 'sq')
      $is_albanian = true;
    
    $first_query = "SELECT * FROM OER_Parameters ORDER BY position";
    $result1 = $wpdb->get_results($first_query);
    $output = "";

			

    $output .= "<form action='' method='GET'>";
    if(isset($_GET['page_id']))
    		$output .= "<input type='hidden' name='page_id' value='" . $_GET['page_id'] . "'/>";
    if( isset($_GET['keyword']) ){
      $output .= "<input type='text' name = 'keyword' placeholder='" . __('Пребарај низ ресурсите','oerplugin') . "' value='{$_GET['keyword']}' style='width: 195px' />";
    } else {
      $output .= "<input type='text' name = 'keyword' placeholder='" . __('Пребарај низ ресурсите','oerplugin') . "' style='width: 195px' />";
    }
    $output .= "<br />";
    $output .= "<input type='submit' class='searchsubmit' value='". __('Барај','oerplugin') . "'";
    $output .= "<br />";
    $output .= "<br />";

    if($result1) {
        foreach($result1 as $row1) {
            if($row1->position == -1) {
                continue;
            }
            $output .= "<div id='parameter_type' style='padding-top: 10px; line-height: 100%;'>";
            if( $is_albanian)
              $output .= "<span id='parameter_title' style=' font-weight: bold; color: gray;'>" . $row1->title_sq . "</span>";
            else
              $output .= "<span id='parameter_title' style=' font-weight: bold; color: gray;'>" . $row1->title_mk . "</span>";
            
            
            $output .= "<br />";

            
            $second_query = "SELECT * FROM {$row1->table_name}";
            $result2 = $wpdb->get_results($second_query);

            if($result2) {
                foreach($result2 as $row2) {
                    $c_row_id = (string)$row1->id . "_" . (string)$row2->id;

                    $checked = (isset( $_GET[$c_row_id] ) && $_GET[$c_row_id]) ? "1" : "0";

                    if($checked == "1")
                      $output .= "<input type='checkbox' name='{$c_row_id}' value='1' checked='checked'>";
                    else
                      $output .= "<input type='checkbox' name='{$c_row_id}' value='1'>";
                    if($is_albanian)
                      $output .= $row2->title_sq;
                    else
                      $output .= $row2->title_mk;

                    $output .= "<br />";
                } //end-inner-foreach
            } //end-inner-if
            $output .= "</div>";
        } //end-outer-foreach
        $output .= "</form>";

        echo $output;
    } //end-outer-if
}

?>
