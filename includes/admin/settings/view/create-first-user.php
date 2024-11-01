<?php
global $wp_session, $wpdb;
$page = sanitize_text_field($_GET['page']);
$adminurl = admin_url('admin.php') . '?page=' . $page . '&tab=first_user';
$error = array();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = sanitize_text_field($_POST['ump_username']);
    $email = sanitize_text_field($_POST['ump_email']);
    $password = sanitize_text_field($_POST['ump_password']);
    $confirm_password = sanitize_text_field($_POST['ump_confirm_password']);

    if (username_exists($username)) {
        $error['error_username'] = __('User Name Already Exist', 'ump');
    } elseif (empty($username)) {
        $error['error_username'] = __('User Name Could Not be Empty', 'ump');
    }

    if (email_exists($email)) {
        $error['error_email'] = __('Email Already Exist', 'ump');
    } elseif (empty($email)) {
        $error['error_email'] = __('Email Could Not be Empty', 'ump');
    } elseif (!is_email($email)) {
        $error['error_email'] = __('Email is not valid. Please correct email', 'ump');
    }

    if (empty($password)) {
        $error['error_password'] = __('Password could not be empty', 'ump');
    } elseif (empty($confirm_password)) {
        $error['error_confirm_password'] = __('Confirm Password could not be empty', 'ump');
    } elseif ($password !== $confirm_password) {
        $error['error_confirm_password'] = __('Password Does not match', 'ump');
    }


    if (empty($error)) {

        $userdata = array(
            'user_login'  =>  $username,
            'user_email' => $email,
            'user_pass'   => $password,
        );

        $user_id = wp_insert_user($userdata);

        if (!is_wp_error($user_id)) {
            $ump_user = new WP_User($user_id);
            $ump_user->set_role('ump_user');
            $user_key = ump_generateKey();
            $insert = "INSERT INTO {$wpdb->prefix}ump_users (user_id, user_name, user_key, parent_key, sponsor_key,payment_status)
                  VALUES('" . $user_id . "','" . $username . "', '" . $user_key . "', '0', '0', '1')";
            $wpdb->query($insert);
            $wp_session['ump_success_message'] = __('Unilevel MLM Plan User created successfully.', 'ump');
        }
    }
}
?>
<div class="setting-right-section">
    <div class="container">
        <div class="col-md-12">
            <h2><?php echo __('Create First User', 'ump'); ?></h2>
            <form action="<?php echo $adminurl; ?>" method="POST" name="ump_first_user_create">
                <table class="table border">
                    <tr>
                        <th><?php echo __('User Name', 'ump'); ?></th>
                        <td><input name="ump_username" type="text" class="form-control" id="ump-user-name" placeholder="Enter User Name">
                            <?php if (!empty($error['error_username'])) { ?>
                                <span class="err text-danger">
                                    <?php echo $error['error_username']; ?>
                                </span>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo __('Email', 'ump'); ?></th>
                        <td><input name="ump_email" type="Email" class="form-control" id="ump-email" placeholder="eg..example@gmail.com">
                            <?php if (!empty($error['error_email'])) { ?>
                                <span class="err text-danger">
                                    <?php echo $error['error_email']; ?>
                                </span>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo __('Password', 'ump'); ?></th>
                        <td><input name="ump_password" type="Password" class="form-control" id="ump-password" placeholder="Enter your Password">
                            <?php if (!empty($error['error_password'])) { ?>
                                <span class="err text-danger"><?php echo $error['error_password']; ?></span>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo __('Confirm Password', 'ump'); ?></th>
                        <td><input name="ump_confirm_password" type="Password" class="form-control" id="ump-confirm_password" placeholder="Confirm your Password">
                            <?php if (!empty($error['error_confirm_password'])) { ?>
                                <span class="err text-danger">
                                    <?php echo $error['error_confirm_password']; ?>
                                </span>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>

                        <td colspan="2" class="text-center"><button name="ump_first_user" type="submit" class="btn btn-primary"><?php echo __('Submit', 'ump'); ?></button></td>
                    </tr>
                </table>

            </form>
        </div>
    </div>
</div>