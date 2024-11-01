<?php
global $wpdb, $wp_session;
$ump_general_settings = get_option('ump_general_settings');
$activate_epin = !empty($ump_general_settings['activate_epin']) ? $ump_general_settings['activate_epin'] : '';

$tabs = array();
if (!ump_first_user_created()) {
    $tabs['first_user'] = __("FIrst User", 'ump');
}
$tabs['general'] = __("General", 'ump');
if ($activate_epin == true) {
    $tabs['epins'] = __("Generate ePins", 'ump');
}
$page = sanitize_text_field($_GET['page']);
$adminurl = admin_url('admin.php') . '?page=' . $page;
if (isset($_GET['tab']) && !empty($_GET['tab'])) {
    $current_tab = sanitize_text_field($_GET['tab']);
} else {
    if (!ump_first_user_created()) {
        $current_tab = 'general';
    } else {
        $current_tab = 'first_user';
    }
} ?>
<h1><?php echo __("UMP Settings", 'ump'); ?></h1>
<div class="setting-left-section">
    <ul>
        <?php foreach ($tabs as $key => $value) {
            $url = $adminurl . '&tab=' . $key;
            if ($key === $current_tab) {
                $class = "active";
            } else {
                $class = "";
            }
        ?>
            <li class="<?php echo $class; ?> text-center"><a href="<?php echo $url; ?>"><?php echo $value; ?></a></li>
        <?php } ?>
    </ul>
</div>



<?php
switch ($current_tab) {
    case "first_user":
        include_once dirname(__FILE__) . "/create-first-user.php";
        break;

    case "general":
        include_once dirname(__FILE__) . "/general-settings.php";
        break;
    case "epins":
        include_once dirname(__FILE__) . "/epins-settings.php";
        break;

    default:
        include_once dirname(__FILE__) . "/general-settings.php";
        break;
}

?>

<?php if (ump_first_user_created() && $current_tab == 'first_user') {
    $radminurl = $adminurl . '&tab=general';
?>
    <script type="text/javascript">
        window.location = "<?php echo $radminurl; ?>";
    </script>
<?php
    exit;
} else if (!ump_first_user_created() && $current_tab != 'first_user') {
    $radminurl = $adminurl . '&tab=first_user';
?>
    <script type="text/javascript">
        window.location = "<?php echo $radminurl; ?>";
    </script>
<?php
    exit;
}
?>