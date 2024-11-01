<?php
if (!defined('ABSPATH')) {
    exit;
}
get_header();
global $wpdb, $wp_query, $current_user;
if (!empty($_REQUEST['k']) && isset($_REQUEST['k'])) {
    $sp = sanitize_text_field($_REQUEST['k']);
    $sp_name = $wpdb->get_var("SELECT u.user_login from {$wpdb->prefix}users as u,{$wpdb->prefix}ump_users as ump where u.Id=ump.user_id AND user_key=$sp");
}
$ump_users = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ump_users");

if (is_user_logged_in()) {
    $sponsor_id = $current_user->ID;
} else {
    $sponsor_id = '';
}

if (is_user_logged_in() && empty($sp_name)) {
    $sp_name = $current_user->user_login;
}

if (!empty($sp_name)) {
    $readonly = 'readonly';
    $disabled = 'disabled';
} else {
    $readonly = '';
    $disabled = '';
}

$ump_manage_general = get_option('ump_general_settings'); ?>
<div class="container" id="myTabContent">
    <div class="tab-pane show active" id="home" role="tabpanel" aria-labelledby="home-tab">
        <h3 class="text-center"><?php esc_html_e('Apply For Unilevel MLM Plan', 'ump'); ?></h3>
        <form id="ump_register_form" name="ump_register_form" action="" method="POST">
            <div class="col-md-12 row ml-2">
                <div class="col-md-6">
                    <div class="form-group">
                        <input type="hidden" name="redirect_URL" value="<?php echo get_url_ump('downlines'); ?>">
                        <input type="hidden" name="action" value="ump_user_register">
                        <input id="ump_username" name="ump_username" type="text" class="form-control" placeholder="<?php esc_html_e('user Name *', 'ump'); ?>" value="">
                        <div class="ump_username_message"></div>
                    </div>
                    <div class="form-group">
                        <input id="ump_first_name" name="ump_first_name" type="text" class="form-control" placeholder="<?php esc_html_e('First Name *', 'ump'); ?>" value="">
                        <div class="ump_first_name_message"></div>
                    </div>

                    <div class="form-group">
                        <input id="ump_last_name" name="ump_last_name" type="text" class="form-control" placeholder="<?php esc_html_e('Last Name *', 'ump'); ?>" value="">
                        <div class="ump_last_name_message"></div>
                    </div>

                    <div class="form-group">
                        <input id="ump_password" name="ump_password" type="password" class="form-control" placeholder="<?php esc_html_e('Password *', 'ump'); ?>" value="">
                        <div class="ump_password_message"></div>
                    </div>

                    <div class="form-group">
                        <input id="ump_confirm_password" name="ump_confirm_password" type="password" class="form-control" placeholder="<?php esc_html_e('Confirm Password *', 'ump'); ?>" value="">
                        <div class="ump_confirm_password_message"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <input id="ump_address" name="ump_address" type="text" class="form-control" placeholder="<?php esc_html_e('Your Address *', 'ump'); ?>" value="">
                        <div class="ump_address_message"></div>
                    </div>

                    <div class="form-group">
                        <input id="ump_email" name="ump_email" type="email" class="form-control" placeholder="<?php esc_html_e('Your Email *', 'ump'); ?>" value="">
                        <div class="ump_email_message"></div>
                    </div>

                    <div class="form-group">
                        <input id="ump_sponsor" name="ump_sponsor" type="text" class="form-control" placeholder="<?php _e('Sponsor Name *', 'ump'); ?>" value="<?php echo (isset($sp_name) && $sp_name != $current_user) ? $sp_name : ""; ?>">
                        <div class="ump_sponsor_message"></div>
                    </div>

                    <div class="form-group">
                        <input id="ump_phone" name="ump_phone" type="text" minlength="10" maxlength="10" class="form-control" placeholder="<?php esc_html_e('Your Phone *', 'ump'); ?>">
                        <div class="ump_phone_message"></div>
                    </div>
                    <?php if (isset($ump_manage_general['activate_epin']) && $ump_manage_general['activate_epin'] == 'on') { ?>
                        <div class="form-group">
                            <input id="ump_epin" name="ump_epin" type="text" class="form-control" placeholder="<?php esc_html_e('Your ePin *', 'ump'); ?>">
                            <div class="ump_epin_message"></div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="col-md-12 text-center">
                <input type="submit" class="btn btn-primary ump_button" value="<?php esc_html_e('Register', 'ump'); ?>" />
            </div>
        </form>
    </div>
</div>
<?php
get_footer();
?>