<?php
if (!defined('ABSPATH')) {
    exit;
}
get_header();
global $wpdb;
do_action('ump_user_check_validate');
$downlines = new UMP_Genealogy();
$downlines->downlinesFunction();
get_footer();
