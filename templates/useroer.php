<?php
/*
    Template Name: OER User
*/

$_POST = stripslashes_deep( $_POST );
$_GET = stripslashes_deep( $_GET );

get_header();
?>

<div id="primary">
<?php echo __FILE__; ?>
</div>

<?php
get_footer();
?>
