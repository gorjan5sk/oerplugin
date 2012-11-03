<?php
/*
    Template Name: OER Upload
*/

//Raboti za linkovi i embedded, ne raboti za upload
//Treba da se validira embedded

global $wpdb;

$_POST = stripslashes_deep( $_POST );
$_GET = stripslashes_deep( $_GET );

$login_page_id = get_option("oerplugin_login_page_id");
$redirect_to_login = get_permalink( $login_page_id );

$is_albanian = false;
if( isset( $_GET['lang'] ) && $_GET['lang']=='sq')
  $is_albanian = true;

function logged_in() {
  if ( isset($_COOKIE['oer_cookie_id']) && isset($_COOKIE['oer_user_id']) ) {
    $cookie_id = intval($_COOKIE['oer_cookie_id']);
    $user_id = intval($_COOKIE['oer_user_id']);
    if(isset($cookie_id) && isset($user_id)){
      global $wpdb;
      $cookie_pairs = $wpdb->get_results(
                        $wpdb->prepare("SELECT user_id, cookie_id from OER_Userlog WHERE cookie_id = %d AND user_id = %d",$cookie_id, $user_id));
      if($cookie_pairs) {
	return true;
      }
    }
  }
  return false; 
}


if (!logged_in()) {
    wp_redirect($redirect_to_login);
    exit;
}

if (isset($_POST['submit_resource'])) { //Upload Resource

    $up_res_user_id = $_COOKIE['oer_user_id'];

    $up_res_title = sanitize_text_field($_POST['oer_up_res_title']);
    $up_res_description = sanitize_text_field($_POST['oer_up_res_description']);
    $up_res_note = sanitize_text_field($_POST['oer_up_res_note']);
    $up_res_author = sanitize_text_field($_POST['oer_up_res_author']);
    $up_res_subject_area = sanitize_text_field($_POST['oer_up_res_subject_area']);
    $up_res_subject = sanitize_text_field($_POST['oer_up_res_subject']);
    $up_res_grade_level = sanitize_text_field($_POST['oer_up_res_grade_level']);
    $up_res_language = sanitize_text_field($_POST['oer_up_res_language']);
    $up_res_type = sanitize_text_field($_POST['oer_up_res_type']);
    $up_res_format = sanitize_text_field($_POST['oer_up_res_format']);
    $up_res_licence = sanitize_text_field($_POST['oer_up_res_licence']);
    
    $rdb_selection = sanitize_text_field($_POST['tab']);
    
    $up_res_file = sanitize_text_field($_POST['oer_up_res_file']);   
    
    if($rdb_selection == '1') {
        $data_key = "data_file";
        $data_value = "";
    }
    else if($rdb_selection == '2') {
        $data_key = "data_link";
        $data_value = sanitize_text_field($_POST['oer_up_res_link']);
        $icon = site_url( "/wp-content/plugins/oerplugin/icons/" . "link.png" );
    }
    else if($rdb_selection == '3') {
        $data_key = "data_embed";
        $data_value = $_POST['oer_up_res_embed'];
        if(strpos($data_value,'youtube')) {
        		$icon = site_url( "/wp-content/plugins/oerplugin/icons/" . "youtube.png" );
        } else if(strpos($data_value,'slideshare')) {
        		$icon = site_url( "/wp-content/plugins/oerplugin/icons/" . "slideshare.png" );
        } else if(strpos($data_value,'scribd')) {
        		$icon = site_url( "/wp-content/plugins/oerplugin/icons/" . "scribd.png" );
        } else if(strpos($data_value,'vimeo')) {
        		$icon = site_url( "/wp-content/plugins/oerplugin/icons/" . "vimeo.png" );
        } else if(strpos($data_value,'soundcloud')) {
        		$icon = site_url( "/wp-content/plugins/oerplugin/icons/" . "soundcloud.png" );
        } else {
        		$icon = site_url( "/wp-content/plugins/oerplugin/icons/" . "embed.png" );
        }
    }
    
    if( !isset($up_res_note) )
      $up_res_note='';
 
    if ( isset($up_res_title) && $up_res_title != '' &&
        isset($up_res_description) && $up_res_description != '' &&
        isset($up_res_author) && $up_res_author != '' &&
        isset($up_res_user_id) && $up_res_user_id != '' &&
        isset($up_res_subject) && $up_res_subject != '' &&
        isset($up_res_type) && $up_res_type != '' &&
        isset($up_res_grade_level) && $up_res_grade_level != '' &&
        isset($up_res_subject_area) && $up_res_subject_area != '' &&
        isset($up_res_licence) && $up_res_licence != '' &&
        isset($up_res_format) && $up_res_format != '' &&
        isset($up_res_language) && $up_res_language != '' &&
        isset($data_key) && $data_key != '') {
        
        $result = $wpdb->insert(
            'OER_Resource',
            array(
                'title' => $up_res_title,
                'description' => $up_res_description,
                'note' => $up_res_note,
                'author' => $up_res_author,
                'user_id' => $up_res_user_id,
                'subject_id' => $up_res_subject,
                'resource_type_id' => $up_res_type,
                'grade_level_id' => $up_res_grade_level,
                'subject_area_id' => $up_res_subject_area,
                'licence_id' => $up_res_licence,
                'resource_format_id' => $up_res_format,
                'language_id' => $up_res_language,
                'approved' => 0,
                $data_key => $data_value,
            )
        );
                
        if($result) {
        
            $id = $wpdb->insert_id;
            
            if($_FILES["oer_up_res_file"]["error"] == 0) {
            
                $dot = strrpos($_FILES["oer_up_res_file"]["name"], '.');
            
                $extension =  substr($_FILES["oer_up_res_file"]["name"], $dot);
                  
                $_FILES["oer_up_res_file"]["name"] = $_COOKIE['oer_user_id'] . '_' .  (string) $id . $extension;
                
                $filename = $_FILES["oer_up_res_file"]["name"];
                
                $upload_folder = get_option('oerplugin_root_directory') . "/oer_uploads/resources/" . $filename;
               move_uploaded_file($_FILES["oer_up_res_file"]["tmp_name"], $upload_folder);
               $data_value = site_url("/wp-content/plugins/oerplugin/oer_uploads/resources/". $filename);
               
               $icon_name = str_replace('.','',$extension) . '.png' ;
               $icon_loc = get_option('oerplugin_root_directory') . '/icons/' . $icon_name;
               if(file_exists($icon_loc))
                  $icon = site_url( "/wp-content/plugins/oerplugin/icons/" . $icon_name );
               else
                  $icon = site_url( "/wp-content/plugins/oerplugin/icons/" . 'file.png' );
            }
            
            $result2 = $wpdb->update(
                'OER_Resource',
                array(
                    $data_key => $data_value,
                    'icon'=>$icon
                ),
                array(
                    'id' => $id
                )
            );
            
            
            $to = get_option("oerplugin_admin_email");
            $subject = "Open Educational Resources - OER Resource Uploaded";
            $message = "The resource {$up_res_title} has to be approved by the admin.\r\n";
            $message .= "Link to the resource administration page:\r\n";
            $message .= site_url('/wp-admin/admin.php?page=oerplugin_resources')." \r\n\r\n";
            
            if($up_res_note!='')
              $message .= "Note from the user: \r\n" . $up_res_note ." \r\n\r\n";
            
            if(!wp_mail($to, $subject, $message)) {
                $fail_message = __('Проблем при прикачувањето на ресурсот, ве молиме контактирајте го администраторот.','oerplugin');
            }
            else {
                $message = __('Успешно е прикачен ресурсот. Ќе стане достапен откако е одобрен од администраторот.','oerplugin'); 
            }
        } //end-inner-if
        
        else {
            $fail_message = __('Проблем при прикачувањето на ресурсот, ве молиме контактирајте го администраторот.','oerplugin');
        } //end-inner-else
        
    } //end-middle-if
    
    else {
        $fail_message = __('Ги немате пополнето сите задолжителни полиња.', 'oerplugin');
    } //end-middle-else
    
} // end-outer-if

else if (isset($_GET['res_id'])) { //Change existing resource
    
    $res_id = $_GET['res_id'];
    $user_id = $_COOKIE['oer_user_id'];
    
    $query = "SELECT *";
    $query .= " FROM OER_Resource";
    $query .= " WHERE id = '{$res_id}'";
    $query .= " AND user_id = '{$user_id}'";
    $query .= " LIMIT 1";
    
    $result = $wpdb->get_results(
                        $wpdb->prepare("SELECT * FROM OER_Resource WHERE id = %d and user_id = %d",$res_id,$user_id));
    
    if($result) {
        foreach($result as $row) {
            $up_res_title = $row->title;
            $up_res_description = $row->description;
            $up_res_note = $row->note;
            $up_res_author = $row->author;
            $up_res_subject_area = $row-subject_area_id;
            $up_res_subject = $row->subject_id;
            $up_res_grade_level = $row->grade_level_id;
            $up_res_language = $row->language_id;
            $up_res_type = $row-type_id;
            $up_res_format = $row->format_id;
            $up_res_licence = $row->licence_id;
        }       
    }
    
    else {
        $fail_message = __('Ресурсот не постои','oerplugin');
    }
    
} // end-outer-else-if

get_header();

?>

<!-- pasted code -->
<style type="text/css" media="screen">
  .hide{
    display:none;
  }
</style>
<!-- end of pasted code -->


<div id="primary">
    <div id="content">
        
        <div id="wrapper" style="width: 100%;">
            
            <?php if(isset($message)) { ?>
            <div id="info" style="background: #38F590; padding: 10px;">
                <?php echo $message; ?>
            </div>
            <?php }
            else if(isset($fail_message)) { ?>
            <div id="info" style="background: #FAACAC; padding: 10px; text-align: center;">
                <?php echo $fail_message; ?>
            </div>
            <?php } ?> 
            
            <div id="upload" style="background: #f5f5f5; width: 70%; padding: 20px; margin-left: auto; margin-right: auto;">
            
            <div id="holder" style=" width: 360px; margin-left: auto; margin-right: auto;">
            
                <span style="font-size: 1.3em;">
                    <?php _e('Прикачи нов ресурс','oerplugin')?>
                </span>
                <br />
                <br />

                <form action="" method="post" enctype="multipart/form-data">

                    <div>
                        <?php _e('Име на ресурсот','oerplugin')?> :
                        <span style="color: red;">*</span>
                        <br />
                        <input type="text" name="oer_up_res_title" maxlength="30" value="<?php if(isset($up_res_title)) echo esc_attr($up_res_title); ?>" style="width: 100%;" />
                    </div>

                    <div>
                        <?php _e('Опис','oerplugin')?> :
                        <span style="color: red;">*</span>
                        <br />
                        <textarea rows="5" name="oer_up_res_description" style="width: 100%;"><?php if(isset($up_res_description)) echo esc_attr($up_res_description); ?></textarea>
                    </div>

                    <div>
                        <?php _e('Забелешка','oerplugin')?> :
                        <span style="color: red;"> </span>
                        <br />
                        <textarea rows="5" name="oer_up_res_note" style="width: 100%;"><?php if(isset($up_res_note)) echo esc_attr($up_res_note); ?></textarea>
                    </div>

                    <div>
                        <?php _e('Автор','oerplugin')?>:
                        <span style="color: red;">*</span>
                        <br />
                        <input type="text" name="oer_up_res_author" maxlength="30" value="<?php if(isset($up_res_author)) echo esc_attr($up_res_author); ?>" style="width: 100%;" />
                    </div>

                    <div>
                        <?php _e('Област','oerplugin')?>:
                        <span style="color: red;">*</span>
                        <br />
                        
                        <select name="oer_up_res_subject_area" style="width: 100%;">

                        <?php
                            $query = "SELECT *";
                            $query .= " FROM OER_Subject_Area";
                            
                            $result = $wpdb->get_results($query);
                            
                            if($result) {
                                $output = '';
                                
                                foreach($result as $row) {
                                    $id = $row->id;
                                     if( $is_albanian )
                                      $title = $row->title_sq;
                                    else
                                      $title = $row->title_mk;
                                    $output .= "<option value=\"{$id}\">{$title}</option>";
                                }
                                echo $output;
                            }
                            else {
                                echo "No rows returned.";
                            }
                        ?>
                        
                        </select>

                    </div>

                    <div>
                        <?php _e('Предмет','oerplugin')?>:
                        <span style="color: red;">*</span>
                        <br />
                        <select name="oer_up_res_subject" style="width: 100%;">
                        
                        <?php
                            $query = "SELECT *";
                            $query .= " FROM OER_Subject";
                            
                            $result = $wpdb->get_results($query);
                            
                            if($result) {
                                $output = '';
                                
                                foreach($result as $row) {
                                    $id = $row->id;
                                     if( $is_albanian )
                                      $title = $row->title_sq;
                                    else
                                      $title = $row->title_mk;
                                    $output .= "<option value=\"{$id}\">{$title}</option>";
                                }
                                echo $output;
                            }
                            else {
                                echo "No rows returned.";
                            }
                        ?>
                        
                        </select>
                    </div>

                    <div>
                        <?php _e('Степен на образование','oerplugin')?>:
                        <span style="color: red;">*</span>
                        <br />
                        <select name="oer_up_res_grade_level" style="width: 100%;">
                        
                        <?php
                            $query = "SELECT *";
                            $query .= " FROM OER_Grade_Level";
                            
                            $result = $wpdb->get_results($query);
                            
                            if($result) {
                                $output = '';
                                
                                foreach($result as $row) {
                                    $id = $row->id;
                                     if( $is_albanian )
                                      $title = $row->title_sq;
                                    else
                                      $title = $row->title_mk;
                                    $output .= "<option value=\"{$id}\">{$title}</option>";
                                }
                                echo $output;
                            }
                            else {
                                echo "No rows returned.";
                            }
                        ?>
                        
                        </select>
                    </div>

                    <div>
                        <?php _e('Јазик','oerplugin')?>:
                        <span style="color: red;">*</span>
                        <br />
                        <select name="oer_up_res_language" style="width: 100%;">
                        
                        <?php
                            $query = "SELECT *";
                            $query .= " FROM OER_Language";
                            
                            $result = $wpdb->get_results($query);
                            
                            if($result) {
                                $output = '';
                                
                                foreach($result as $row) {
                                    $id = $row->id;
                                     if( $is_albanian )
                                      $title = $row->title_sq;
                                    else
                                      $title = $row->title_mk;
                                    $output .= "<option value=\"{$id}\">{$title}</option>";
                                }
                                echo $output;
                            }
                            else {
                                echo "No rows returned.";
                            }
                        ?>
                        
                        </select>
                    </div>

                    <div>
                        <?php _e('Тип на ресурс','oerplugin')?>:
                        <span style="color: red;">*</span>
                        <br />
                        <select name="oer_up_res_type" style="width: 100%;">
                        
                        <?php
                            $query = "SELECT *";
                            $query .= " FROM OER_Resource_Type";
                            
                            $result = $wpdb->get_results($query);
                            
                            if($result) {
                                $output = '';
                                
                                foreach($result as $row) {
                                    $id = $row->id;
                                     if( $is_albanian )
                                      $title = $row->title_sq;
                                    else
                                      $title = $row->title_mk;
                                    $output .= "<option value=\"{$id}\">{$title}</option>";
                                }
                                echo $output;
                            }
                            else {
                                echo "No rows returned.";
                            }
                        ?>                        
                        
                        </select>
                    </div>

                    <div>
                        <?php _e('Формат на ресурсот','oerplugin')?>:
                        <span style="color: red;">*</span>
                        <br />
                        <select name="oer_up_res_format" style="width: 100%;">
                        
                        <?php
                            $query = "SELECT *";
                            $query .= " FROM OER_Resource_Format";
                            
                            $result = $wpdb->get_results($query);
                            
                            if($result) {
                                $output = '';
                                
                                foreach($result as $row) {
                                    $id = $row->id;
                                     if( $is_albanian )
                                      $title = $row->title_sq;
                                    else
                                      $title = $row->title_mk;
                                    $output .= "<option value=\"{$id}\">{$title}</option>";
                                }
                                echo $output;
                            }
                            else {
                                echo "No rows returned.";
                            }
                        ?>
                        
                        </select>

                    </div>
                    
                    <div>
                        <?php _e('Лиценца под која е објавен ресурсот','oerplugin')?>:
                        <span style="color: red;">*</span>
                        <br />
                        <select name="oer_up_res_licence" style="width: 100%;">
                        
                        <?php
                            $query = "SELECT *";
                            $query .= " FROM OER_Licence";
                            
                            $result = $wpdb->get_results($query);
                            
                            if($result) {
                                $output = '';
                                
                                foreach($result as $row) {
                                    $id = $row->id;
                                     if( $is_albanian )
                                      $title = $row->title_sq;
                                    else
                                      $title = $row->title_mk;
                                    $output .= "<option value=\"{$id}\">{$title}</option>";
                                }
                                echo $output;
                            }
                            else {
                                echo "No rows returned.";
                            }
                        ?>
                        
                        </select>
                    </div>

                    <div id="tabs">
                    
                        <div id="nav">
                            <?php _e('Одберете тип на ресурс','oerplugin')?>:
                            <span style="color: red;">*</span>
                            <br />
                            <input type="radio" name="tab" value="1" class="div1" />
                                                    
                            <?php _e('Прикачи датотека','oerplugin')?>:
                            <br />
                            <div id="div1" class="tab">
                                <input type="file" name="oer_up_res_file" style="" />
                            </div> <!-- end-div1 -->
                            
                            <input type="radio" name="tab" value="2" class="div2" />                           
                            <?php _e('Внеси линк до ресурс','oerplugin')?>:
                            <br />
                            <div id="div2" class="tab">
                                <textarea rows="1" name="oer_up_res_link" style="width: 100%;"><?php if(isset($up_res_link)) echo esc_attr($up_res_link); ?></textarea>
                            </div> <!-- end-div2 -->
                            
                            <input type="radio" name="tab" value="3" class="div3" />
                             <?php _e('Внеси embed tag за ресурс','oerplugin'); ?>
                            <div id="div3" class="tab">
                                <textarea rows="5" name="oer_up_res_embed" style="width: 100%;"><?php if(isset($up_res_embed)) echo esc_attr($up_res_embed); ?></textarea>
                            </div> <!-- end-div1 -->
                        </div> <!-- end-nav -->                    

                    </div> <!-- end-tabs -->



                    <!-- pasted code -->

                    <script type="text/javascript" charset="utf-8">
                        (function(){
                          var tabs =document.getElementById('tabs');
                          var nav = tabs.getElementsByTagName('input');
                          
                          /* 
                          * Hide all tabs
                          */
                          function hideTabs(){
                            var tab = tabs.getElementsByTagName('div');
                            for(i in tab){
                              if(tab[i].className == 'tab'){
                                tab[i].className = tab[i].className + ' hide';
                              }
                            }
                          }
                          
                          /*
                          * Show the clicked tab
                          */
                          function showTab(tab){
                            hideTabs();
                            document.getElementById(tab).className = document.getElementById(tab).className.replace(' hide', '');
                          }
                          
                          hideTabs(); /* hide tabs on load */
                          
                          /* 
                          * Add click events
                          */
                          for(i in nav){
                            nav[i].onclick = function(){
                              showTab(this.className);
                            }
                          }
                        })();
                    </script>

                    <!-- end of pasted code -->



                    <span style="color: red;"><?php _e('* Овие полиња се задолжителни','oerplugin');?></span>
                    <br />
                    <input type="submit" name="submit_resource" value="Upload" />
                    
                </form>

            </div> <!-- end-holder -->

            </div> <!-- end-upister -->
            
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
