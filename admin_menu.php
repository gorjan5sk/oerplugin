<?php

function oerplugin_admin_menu() {
  add_menu_page('OER Plugin', 'OER Plugin', 'manage_options', 'oerplugin', 'oerplugin_admin_main', '',30);
  add_submenu_page('oerplugin','User Management', 'Users', 'manage_options','oerplugin_users', 'oerplugin_admin_users');
  add_submenu_page('oerplugin','Parameters Configuration', 'Parameters', 'manage_options', 'oerplugin_parameters', 'oerplugin_admin_parameters');
  add_submenu_page('oerplugin','Resource Approval', 'Resources', 'manage_options','oerplugin_resources', 'oerplugin_admin_resources');
}

function oerplugin_admin_init() {
  register_setting("oerplugin_options", 'oerplugin_admin_email');
}

function oerplugin_admin_main() {

  ?>
  <div class="wrap"><?php screen_icon(); ?>
  <h2>Open Educational Resources Management Plugin</h2>
  <p>Welcome to the Open Educational Resources Management Plugin. Here you can edit the email you
  wish to have your resource approval notifications sent to.</p>
  
  <form action="options.php" method="post" id="oerplugin-email-options-form">
    <?php settings_fields('oerplugin_options'); ?>
    <h3><label for="oerplugin_admin_email" >Email to send resource notifications to:</label>
	<input type="text" id="oerplugin_admin_email" name ="oerplugin_admin_email"
	  value="<?php echo esc_attr(get_option("oerplugin_admin_email")); ?>" />
  </h3>
    <p><input type="submit" name="submit" value="Update Email" /></p>
  </form>
  
  </div>
  <?php
}

function oerplugin_admin_parameters() {
  global $wpdb;
  
  $nonce=wp_create_nonce ('oerpluginnonce');
  
  $oer_message="";
  if(isset($_POST['type']) && isset($_POST['action']) && isset($_POST['id'])) {
    $nonce_rcvd=$_REQUEST['_wpnonce'];
    if (! wp_verify_nonce($nonce_rcvd, 'oerpluginnonce') ) die("Security check");
    
    if($_POST['type'] == 'p') {
      $param_id = intval($_POST['id']);
      if($_POST['action'] == 'u' && isset($_POST['mk']) && isset($_POST['sq']) && $param_id ){
	$wpdb->update('OER_Parameters', 
	  array ( 'title_mk'=>$_POST['mk'], 'title_sq'=>$_POST['sq'] ),
	  array ( 'id' => $param_id ),
	  array ( '%s', '%s' ),
	  array ( '%d'));
	$oer_message="Sucessfully updated parameter";
      } else {
	$oer_message="Invalid request";
      }
    } else if ($_POST['type'] == 'v') {
	
      if($_POST['action'] == 'a') {
	$param_id = intval($_POST['id']);
	$value_table = $wpdb->get_var("SELECT table_name FROM OER_Parameters WHERE id = {$param_id}");
      } else {
	$ids_array = explode( "_" , $_POST['id'] , 2 );
	$param_id = intval($ids_array['0']);
	$value_id = intval($ids_array['1']);
	$value_table = $wpdb->get_var("SELECT table_name FROM OER_Parameters WHERE id = {$param_id}");
      }
      
      if( $_POST['action'] == 'a' && isset($_POST['mk']) && isset($_POST['sq'])
	  && $param_id && isset($value_table)){
	
	$wpdb->insert($value_table,
		array( 'title_mk' => $_POST['mk'], 'title_sq' => $_POST['sq']),
		array( '%s', '%s' ));
	$oer_message="Sucessfully added a value to the parameter";
	
      } else if($_POST['action'] == 'u' && isset($_POST['mk']) && isset($_POST['sq'])
		&& $param_id && $value_id && isset($value_table)){
		
	$wpdb->update($value_table,
		array ( 'title_mk'=>$_POST['mk'], 'title_sq'=>$_POST['sq'] ),
		array ( 'id' => $value_id ),
		array ( '%s', '%s' ),
		array ( '%d'));
	$oer_message="Sucessfully updated a value from the parameter";
	
      } else if($_POST['action'] == 'd' && $param_id && $value_id && isset($value_table)){
      
	$wpdb->query("DELETE FROM {$value_table} WHERE id = {$value_id}");
	$oer_message="Sucessfully deleted a value from the parameter";
	
      } else {
	$oer_message="Invalid request";
      }
    } else {
      $oer_message="Invalid request";
    }
    
    if($oer_message!="")
      echo "<div id='message' class='updated'>{$oer_message}</div>";
  }
  
  ?>
  <div class="wrap"><?php screen_icon(); ?>
    <h2>OER Plugin Parameter Administration</h2>
    <br />
    <br />
    <div>
    
    <?php 
    
    $parameters = $wpdb->get_results("SELECT * FROM OER_Parameters WHERE position > 0 ORDER BY position");
    
    foreach($parameters as $p){
    
      //Update field for every parameter
      ?>
      
      <div> 
      <form method="post" style="clear:both">
	<b>
	<label>Parameter: </label> 
	<label>MK: </label>
	<input type="text" name="mk" value="<?php echo esc_attr($p->title_mk)?>"/>
	<label style="margin-left:20px">SQ: </label>
	<input type="text" name="sq" value="<?php echo esc_attr($p->title_sq)?>"/>
	</b>
	<input type="submit" name="submit" value="Update"/>
	<input type="hidden" name="type" value="p" />
	<input type="hidden" name="action" value="u" />
	<input type="hidden" name="id" value="<?php echo $p->id?>" />
	<input type="hidden" name="_wpnonce" value="<?php echo $nonce ?>" />
      </form>
      </div>
      <?php
      
      $values = $wpdb->get_results("SELECT * FROM {$p->table_name}");
      foreach($values as $v) {
	//Update field for every value of parameter
	?>
	<div style="clear:both">
	<form method="post" style="float:left">
	  <label><?php echo $v->id ?>. </label>
	  <label style="margin-left:53px">MK:</label>
	  <input type="text" name="mk" value="<?php echo esc_attr($v->title_mk)?>"/>
	  <label style="margin-left:43px">SQ:</label>
	  <input type="text" name="sq" value="<?php echo esc_attr($v->title_sq)?>"/>
	  <input type="submit" name="submit" value="Update"/>
	  <input type="hidden" name="type" value="v" />
	  <input type="hidden" name="action" value="u" />
	  <input type="hidden" name="id" value="<?php echo $p->id."_".$v->id?>" />
	  <input type="hidden" name="_wpnonce" value="<?php echo $nonce ?>" />
	</form> 
	<form method="post" style="float:left">
	  <input type="submit" name="submit" value="Delete" />
	  <input type="hidden" name="type" value="v" />
	  <input type="hidden" name="action" value="d" />
	  <input type="hidden" name="id" value="<?php echo $p->id."_".$v->id; ?>" />
	  <input type="hidden" name="_wpnonce" value="<?php echo $nonce ?>" />
	</form>
	</div>
	<?php
      }
      
      ?>
      <div style="clear:both">
      <form method="post" style="float:left">
	<label><?php echo (($v->id) +1) ?>. </label>
	<label style="margin-left:53px">MK:</label>
	<input type="text" name="mk" value=""/>
	<label style="margin-left:43px">SQ:</label>
	<input type="text" name="sq" value=""/>
	<input type="submit" name="submit" value="Add"/>
	<input type="hidden" name="type" value="v" />
	<input type="hidden" name="action" value="a" />
	<input type="hidden" name="id" value="<?php echo $p->id ?>" />
	<input type="hidden" name="_wpnonce" value="<?php echo $nonce ?>" />
      </form>
      </div>
      <?php
      
      echo "<br /> <br />";
    }
    
    ?>
    </div>
  </div>
  <?php
}

function oerplugin_admin_users() {
  global $wpdb;
  
  $nonce=wp_create_nonce ('oerpluginnonce');
  
  $oer_message="";
  if(isset($_POST['id']) && isset($_POST['action']) ){
    
    $nonce_rcvd=$_REQUEST['_wpnonce'];
    if (! wp_verify_nonce($nonce_rcvd, 'oerpluginnonce') ) die("Security check");
    
    $user_id = intval($_POST['id']);
    if($user_id){
      if($_POST['action'] == "u"){
	$wpdb->update('OER_User',
		array(
		  'username'=> $_POST['username'],
		  'full_name'=>$_POST['full_name'],
		  'email'=>$_POST['email']
		  ),
		array('id'=>$user_id),
		array('%s','%s', '%s', '%s'),
		array('%d')
		);
      } else if($_POST['action'] == "d") {
	$wpdb->query("DELETE FROM OER_User WHERE id = {$user_id}");
	$wpdb->query("DELETE FROM OER_Resource WHERE user_id = {$user_id}");
      }
    }
  }
  
  ?>
  <div class="wrap"><?php screen_icon(); ?>
  <h2>OER Plugin User Administration</h2>
  
  <?php
  $users = $wpdb->get_results("SELECT * FROM OER_User");
  foreach($users as $u){
    ?>
    <div style="clear:both">
      <form method="post" style="float:left">
	<label><?php echo $u->id ?>. </label>
	<label >Username</label>
	<input type="text" name="username" value="<?php echo esc_attr($u->username)?>"/>
	<label style="margin-left:30px">Full Name</label>
	<input type="text" name="full_name" value="<?php echo esc_attr($u->full_name)?>"/>
	<label style="margin-left:30px">E-mail</label>
	<input type="text" name="email" value="<?php echo esc_attr($u->email)?>"/>
	<input type="submit" name="submit" value="Update"/>
	<input type="hidden" name="action" value="u" />
	<input type="hidden" name="id" value="<?php echo $u->id?>" />
	<input type="hidden" name="_wpnonce" value="<?php echo $nonce ?>" />
      </form> 
      <form method="post" style="float:left">
	<input type="submit" name="submit" value="Delete" onClick="return confirm('Are you sure you want to delete that user and all his resources?')"/>
	<input type="hidden" name="action" value="d" />
	<input type="hidden" name="id" value="<?php echo $u->id?>" />
	<input type="hidden" name="_wpnonce" value="<?php echo $nonce ?>" />
      </form>
    </div>
    <?php
  }
  ?>
  
  </div>
  <?php
}


function oerplugin_admin_resources() {
  global $wpdb;
  
  $nonce=wp_create_nonce ('oerpluginnonce');
  
  if(isset($_POST['resource_id']) && isset($_POST['action'])){
  
    $nonce_rcvd=$_REQUEST['_wpnonce'];
    if (! wp_verify_nonce($nonce_rcvd, 'oerpluginnonce') ) die("Security check");
    
    if($_POST['action']=='a'){
      $resource_id = intval($_POST['resource_id']);
      if($resource_id) {
        $wpdb->update("OER_Resource",
        array( 'approved'=>1),
        array( 'id'=>$resource_id),
        array( '%d' ),
        array( '%d' ));
      }
    } else if ($_POST['action']=='d'){
        $resource_id = intval($_POST['resource_id']);
        $res_row = $wpdb->get_results(
          $wpdb->prepare("SELECT * FROM OER_Resource WHERE id = %d", $resource_id));

        $data_file = $res_row[0]->data_file;
        $user_id = $res_row[0]->user_id;
        $wpdb->query(
          $wpdb->prepare( "DELETE FROM OER_Resource WHERE id = %d", $resource_id )); 
       
        $res_filename = $res_row[0]->data_file; 
        if( (!empty($res_filename)) && $res_filename!='' ){
          
          $res_filename = str_replace( site_url("/wp-content/plugins/oerplugin"), '', $res_filename);
          $res_file = get_option('oerplugin_root_directory') . $res_filename;
          unlink($res_file);
        }
    } 
  }
  
  ?>
  <div class="wrap"><?php screen_icon(); ?>
  <h2>OER Plugin Resource Approval</h2>
  <h3>Non-approved resources:</h3>
  <?php
  $resources_page_id = get_option("oerplugin_resources_page_id");
  $resources_url = get_permalink($resources_page_id);
  
  if( get_option( 'permalink_structure') ) 
    if(isset($_GET['lang']))
      $m = '?lang=' . $_GET['lang'] . '&';
    else
      $m = '?';
  else
    $m = '&';
  
  $query_all_resources = "SELECT OER_Resource.id AS ID,";
  $query_all_resources .= " OER_Resource.title AS Title,";
  $query_all_resources .= " OER_Resource.description AS Description,";
  $query_all_resources .= " OER_Language.title_mk AS Language,";
  $query_all_resources .= " OER_User.full_name AS UploadedBy";
  $query_all_resources .= " FROM OER_Resource, OER_User, OER_Language";
  $query_all_resources .= " WHERE OER_Resource.user_id = OER_User.id";
  $query_all_resources .= " AND OER_Resource.language_id = OER_Language.id";
  $query_all_resources .= " AND OER_Resource.approved = 0";
  $output = "";

  if(isset($message))
    $output .= "<div style='border:1px solid grey''>{$message}</div>";

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

      $output .= "<div id=\"text\">";

      $output .= "<a style=\"font-weight: bold;\" href=\"{$resources_url}{$m}resource_id={$row->ID}\">";
      $output .= $row->Title;
      $output .= "</a>";

      //$output .= "<hr style=\"margin-bottom: 0;\"/>";

      $output .= "<br />";
      $output .= "Опис: " . $row->Description;
      $output .= "<br />";
      $output .= "Јазик: " . $row->Language;
      $output .= "<br />";
      $output .= "Прикачен од: " . $row->UploadedBy;

      $output .= "</div>"; //end div id text
      $output .= "<div>";
      $output .= "<form method='post' style='float:left'> 
		 <input type='submit' name='submit' value='Approve' />
		 <input type='hidden' name='resource_id' value='{$row->ID}' />
		 <input type='hidden' name='_wpnonce' value='{$nonce}' />
     <input type='hidden' name='action' value='a' />
		 </form>";
      
      $output .= "<form method='post' style='float:left'>
      <input type='submit' name='submit' value='Delete' />
      <input type='hidden' name='resource_id' value='{$row->ID}' />
      <input type='hidden' name='_wpnonce' value='{$nonce}' />
      <input type='hidden' name='action' value='d' />
      </form>";
      $output .= "</div>";
      $output .= "<div style='clear:both'></div>";
      $output .= "</div>"; //end div id all resources
      

      $i++;
    } //end-foreach
    echo $output;
  } //end-inner-if
  else {
      echo "No resources found.";
  } //end-inner-else
    
}

?>
