<?php
/*

IMPORTANT NOTICE!!!

IF EDITING THIS FILE KEEP IN MIND THAT for dbDelta to work:

* You have to put each field on its own line in your SQL statement.
* You have to have two spaces between the words PRIMARY KEY and the definition
  of your primary key.
* You must use the key word KEY rather than its synonym INDEX and you must
  include at least one KEY.
* You must not use any apostrophe around field's name. (otherwise dbDelta will
  encounter a bug during preg_match)

from: http://codex.wordpress.org/Creating_Tables_with_Plugins

*/

function oer_install_tables() {
  global $wpdb;
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

  $sql = "CREATE TABLE OER_User (
    id INT(5) NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
    email VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL,
    password TEXT NOT NULL COLLATE utf8_general_ci,
    institution VARCHAR(100) COLLATE utf8_general_ci,
    link_to_site VARCHAR(100),
    photo_url VARCHAR(100),
    short_bio MEDIUMTEXT COLLATE utf8_general_ci,
    activated INT (1) NOT NULL,
    confirm_code INT(10),
    ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY  (id)
    );";
  dbDelta($sql);

  $sql = "CREATE TABLE OER_Subject (
    id INT(5) NOT NULL AUTO_INCREMENT,
    title_mk VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
    title_sq VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
    PRIMARY KEY  (id)
    );";
  dbDelta($sql);

  $sql = "CREATE TABLE OER_Resource_Type (
    id INT(5) NOT NULL AUTO_INCREMENT,
    title_mk VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
    title_sq VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
    PRIMARY KEY  (id)
    );";
  dbDelta($sql);

  $sql = "CREATE TABLE OER_Grade_Level (
    id INT(5) NOT NULL AUTO_INCREMENT,
    title_mk VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
    title_sq VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
    PRIMARY KEY  (id)
    );";
  dbDelta($sql);

  $sql = "CREATE TABLE OER_Subject_Area (
    id INT(5) NOT NULL AUTO_INCREMENT,
    title_mk VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
    title_sq VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
    PRIMARY KEY  (id)
    );";
  dbDelta($sql);

  $sql = "CREATE TABLE OER_Resource_Format (
    id INT(5) NOT NULL AUTO_INCREMENT,
    title_mk VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
    title_sq VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
    PRIMARY KEY  (id)
    );";
  dbDelta($sql);

  $sql = "CREATE TABLE OER_Licence (
    id INT(5) NOT NULL AUTO_INCREMENT,
    title_mk VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
    title_sq VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
    PRIMARY KEY  (id)
    );";
  dbDelta($sql);

  $sql = "CREATE TABLE OER_Language (
    id INT(5) NOT NULL AUTO_INCREMENT,
    title_mk VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
    title_sq VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
    PRIMARY KEY  (id)
    );";
  dbDelta($sql);

  $sql = "CREATE TABLE OER_Resource (
    id INT(5) NOT NULL AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
    description MEDIUMTEXT NOT NULL COLLATE utf8_general_ci,
    note MEDIUMTEXT NOT NULL COLLATE utf8_general_ci,
    author VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
    user_id INT(5) NOT NULL,
    subject_id INT(5) NOT NULL,
    resource_type_id INT(5) NOT NULL,
    grade_level_id INT(5) NOT NULL,
    subject_area_id TINYINT NOT NULL,
    licence_id INT(5) NOT NULL,
    resource_format_id INT(5) NOT NULL,
    language_id INT(5) NOT NULL,
    approved INT(1) NOT NULL,
    data_file MEDIUMTEXT,
    data_link MEDIUMTEXT,
    data_embed MEDIUMTEXT,
    icon MEDIUMTEXT,
    FULLTEXT resource_fulltext_index (title, description, note, author),
    PRIMARY KEY  (id)
    ) ENGINE = MyISAM;";
  dbDelta($sql);

  $sql = "CREATE TABLE OER_Parameters (
    id INT(5) NOT NULL AUTO_INCREMENT,
    table_name VARCHAR(100) NOT NULL,
    resources_name VARCHAR(30) NOT NULL,
    title_mk VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
    title_sq VARCHAR(100) NOT NULL COLLATE utf8_general_ci,
    position INT(2) NOT NULL,
    PRIMARY KEY  (id)
    );";
  dbDelta($sql);

  $sql = "REPLACE INTO `OER_Parameters`
          SET `id` = 1,
          `table_name` = 'OER_Grade_Level',
          `resources_name` = 'grade_level_id',
          `title_mk` = 'mk_grade_level',
          `title_sq` = 'sq_grade_level',
          `position` = '1';";
  $wpdb->query($sql);

  $sql = "REPLACE INTO `OER_Parameters`
          SET `id` = 2,
          `table_name` = 'OER_Language',
          `resources_name` = 'language_id',
          `title_mk` = 'mk_language',
          `title_sq` = 'sq_language',
          `position` = '2';";
  $wpdb->query($sql);

  $sql = "REPLACE INTO `OER_Parameters`
          SET `id` = 3,
          `table_name` = 'OER_Licence',
          `resources_name` = 'licence_id',
          `title_mk` = 'mk_licence',
          `title_sq` = 'sq_licence',
          `position` = '3';";
  $wpdb->query($sql);

  $sql = "REPLACE INTO `OER_Parameters`
          SET `id` = 4,
          `table_name` = 'OER_Resource',
          `resources_name` = 'resource',
          `title_mk` = 'mk_resource',
          `title_sq` = 'sq_resource',
          `position` = '-1';";
  $wpdb->query($sql);

  $sql = "REPLACE INTO `OER_Parameters`
          SET `id` = 5,
          `table_name` = 'OER_Resource_Format',
          `resources_name` = 'resource_format_id',
          `title_mk` = 'mk_resource_format',
          `title_sq` = 'sq_resource_format',
          `position` = '4';";
  $wpdb->query($sql);

  $sql = "REPLACE INTO `OER_Parameters`
          SET `id` = 6,
          `table_name` = 'OER_Resource_Type',
          `resources_name` = 'resource_type_id',
          `title_mk` = 'mk_resource_type',
          `title_sq` = 'sq_resource_type',
          `position` = '5';";
  $wpdb->query($sql);

  $sql = "REPLACE INTO `OER_Parameters`
          SET `id` = 7,
          `table_name` = 'OER_Subject',
          `resources_name` = 'subject_id',
          `title_mk` = 'mk_subject',
          `title_sq` = 'sq_subject',
          `position` = '6';";
  $wpdb->query($sql);

  $sql = "REPLACE INTO `OER_Parameters`
          SET `id` = 8,
          `table_name` = 'OER_Subject_Area',
          `resources_name` = 'subject_area_id',
          `title_mk` = 'mk_subject_area',
          `title_sq` = 'sq_subject_area',
          `position` = '7';";
  $wpdb->query($sql);

  $sql = "REPLACE INTO `OER_Parameters`
          SET `id` = 9,
          `table_name` = 'OER_User',
          `resources_name` = 'user_id',
          `title_mk` = 'mk_user',
          `title_sq` = 'sq_user',
          `position` = '-1';";
  $wpdb->query($sql);
  
  $wpdb->query("DROP TABLE IF EXISTS OER_Userlog");
  $sql = "CREATE TABLE OER_Userlog (
    user_id INT(5) NOT NULL,
    cookie_id INT(10) NOT NULL,
    ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );";
  dbDelta($sql);  
  
  $wpdb->query("DROP TABLE IF EXISTS OER_Resetpassword");
   $sql = "CREATE TABLE OER_Resetpassword (
    user_id INT(5) NOT NULL,
    code_id INT(10) NOT NULL,
    ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );";
  dbDelta($sql);  
}

function userlog_cleanup() {
  global $wpdb;
  $wpdb->query('DELETE FROM OER_Userlog WHERE NOW() > DATE_ADD( ts, INTERVAL 2 WEEKS)');
  $wpdb->query('DELETE FROM OER_User WHERE activated = 0 AND NOW() > DATE_ADD( ts, INTERVAL 1 WEEK)');
  $wpdb->query('DELETE FROM OER_Resetpassword WHERE NOW() > DATE_ADD( ts, INTERVAL 2 WEEKS)');
}

?>
