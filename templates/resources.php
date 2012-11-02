<?php
/*
    Template Name: OER Resources
*/

$_POST = stripslashes_deep( $_POST );
$_GET = stripslashes_deep( $_GET );

require_once('parameters_display.php');
require_once('resources_display_item.php');
require_once('resources_display_all.php');
get_header();
?>
<div id="primary">
    <div id="wrapper" style="width=100%; padding: 10px;">
    
        <?php
            if( isset($_GET['resource_id']) ) {
                $resource_id = $_GET['resource_id'];
        ?> 
        <div id="main-content" style="padding-left: 50px; padding-top:5px; padding-right:50px; padding-bottom:50px;">
        <?php
            resources_display_item($resource_id);
            
            } 
            else {
        ?>
        <div id="sidebar" style="background: #f5f5f5; width: 250px; float: left; padding: 10px;">

        <?php
            parameters_display();
        ?>
        </div>
        <div id="main-content" style="margin-left: 285px;">
        <?php
              resources_display_all();
            }

        ?>
        </div>
        <div id="useless" style="clear: both;">

        </div>
        
    </div>
</div>
<?php
get_footer();
?>
