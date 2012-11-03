<?php

function resources_display_item($id) {
    $_POST = stripslashes_deep( $_POST );
    $_GET = stripslashes_deep( $_GET );

    global $wpdb;
    $output = "";

    $query_resource = "SELECT OER_Resource.title AS Title,";
    $query_resource .= "OER_Resource.description AS Description,";
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
    $query_resource .= " OER_Licence.title_mk AS Licence,";
    $query_resource .= " OER_Resource.subject_area_id AS SubjectAreaID,";
    $query_resource .= " OER_Resource.subject_id AS SubjectID,";
    $query_resource .= " OER_Resource.grade_level_id AS GradeLevelID,";
    $query_resource .= " OER_Resource.language_id AS LanguageID,";
    $query_resource .= " OER_Resource.resource_type_id AS ResourceTypeID,";
    $query_resource .= " OER_Resource.resource_format_id AS ResourceFormatID,";
    $query_resource .= " OER_Resource.licence_id AS LicenceID";
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
    $query_resource .= " OER_Subject_Area.id = OER_Resource.subject_area_id AND";
    $query_resource .= " OER_Subject.id = OER_Resource.subject_id AND";
    $query_resource .= " OER_Grade_Level.id = OER_Resource.grade_level_id AND";
    $query_resource .= " OER_Resource_Type.id = OER_Resource.resource_type_id AND";
    $query_resource .= " OER_Resource_Format.id = OER_Resource.resource_format_id AND";
    $query_resource .= " OER_Licence.id = OER_Resource.Licence_id AND";
    $query_resource .= " OER_Language.id = OER_Resource.language_id";

    $result = $wpdb->get_results( 
                $wpdb->prepare($query_resource,$id));

    if($result) {
        foreach($result as $row) {
            $output .= "<div id=\"resource\" style=\"padding: 10px; margin-bottom: 5px; color: black;\">";
            $output .= "<span style=\"font-size: 2.5em;\">";
		    $output .= $row->Title;
		    $output .= "</span><br />";
		    $output .= "<hr />";

		    $data_file = $row->File;

		    if($data_file != '') {
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

					if( get_option( 'permalink_structure') )
      		if(isset($_GET['lang']))
        	$m = '?lang=' . $_GET['lang'] . '&';
      		else
        	$m = '?';
    		else
      		$m = '&';
      	
      	//link base
      	$lb = get_permalink(get_option("oerplugin_resources_page_id")) . $m . "keyword=&"; 				
					
		    $output .= "<br />";
		    $output .= "<strong>" . __('Опис: ', 'oerplugin') . "</strong>"  . $row->Description ;
		    $output .= "<br />";
		    $output .= "<strong>" . __('Автор: ', 'oerplugin') . "</strong>" . $row->Author;
		    $output .= "<br />";
		    $output .= "<strong>" . __('Област: ', 'oerplugin') . "</strong>" . "<a href='" . $lb."8_".$row->SubjectAreaID . "=1'>" . $row->Subject_Area . "</a>";
		    $output .= "<br />";
		    $output .= "<strong>" . __('Предмет: ', 'oerplugin') . "</strong>" . "<a href='" . $lb."7_".$row->SubjectID . "=1'>" . $row->Subject . "</a>";
		    $output .= "<br />";
		    $output .= "<strong>" . __('Ниво на образование: ', 'oerplugin') . "</strong>" . "<a href='" . $lb."1_".$row->GradeLevelID . "=1'>" . $row->Grade_Level . "</a>";
		    $output .= "<br />";
		    $output .= "<strong>" . __('Јазик: ', 'oerplugin') . "</strong>" . "<a href='" . $lb."2_".$row->LanguageID . "'>" . $row->Language . "</a>";
		    $output .= "<br />";
		    $output .= "<strong>" . __('Тип на ресурс: ', 'oerplugin') . "</strong>" . "<a href='" . $lb."6_".$row->ResourceTypeID . "=1'>" . $row->Resource_Type . "</a>";
		    $output .= "<br />";
		    $output .= "<strong>" . __('Формат: ', 'oerplugin') . "</strong>" . "<a href='" . $lb."5_".$row->ResourceFormatID . "=1'>" . $row->Resource_Format . "</a>";
		    $output .= "<br />";
		    $output .= "<strong>" . __('Лиценца: ', 'oerplugin') ."</strong>" . "<a href='" . $lb."3_".$row->LicenceID . "=1'>" . $row->Licence . "</a>";
		    $output .= "<br />";
        $output .= "<strong>" . __('Прикачен од: ', 'oerplugin') . "</strong>" . $row->UserFullName;
        $output .= "<br />";

		    $output .= "</div>";

        } //end-foreach
        echo $output;
    } //end-if
    else {
        echo "No rows returned.";
    } //end-else
}

?>
