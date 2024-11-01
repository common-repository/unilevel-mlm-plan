<?php
function ump_first_user_created()
{
    global $wpdb;
    $user_count = $wpdb->get_var("SELECT count(*) FROM {$wpdb->prefix}ump_users");
    if ($user_count > 0) {
        return true;
    } else {
        return false;
    }
}

function ump_generateKey()
{
    $characters = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];

    $keys = array();

    $length = 9;

    while (count($keys) < $length) {
        $x = mt_rand(0, count($characters) - 1);
        if (!in_array($x, $keys))
            $keys[] = $x;
    }

    // extract each key from array
    $random_chars = '';
    foreach ($keys as $key)
        $random_chars .= $characters[$key];

    // display random key
    return $random_chars;
}

function sanitize_text_array($array)
{
    foreach ((array) $array as $k => $v) {
        if (is_array($v)) {
            $array[$k] =  sanitize_text_array($v);
        } else {
            $array[$k] = sanitize_text_field($v);
        }
    }

    return $array;
}

function ump_get_page_id($page)
{
    $page = apply_filters('ump_get_' . $page . '_page_id', get_option('ump_' . $page . '_page_id'));
    return $page ? absint($page) : -1;
}

function ump_create_page($slug = '', $option = '', $page_title = '', $page_content = '', $post_parent = 0)
{
    global $wpdb;
    $option_value = get_option($option);
    if ($option_value > 0 && ($page_object = get_post($option_value))) {
        if ('page' === $page_object->post_type && !in_array($page_object->post_status, array('pending', 'trash', 'future', 'auto-draft'))) {
            return $page_object->ID;
        }
    }
    if (strlen($page_content) > 0) {
        $valid_page_found = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_content LIKE %s LIMIT 1;", "%{$page_content}%"));
    } else {
        $valid_page_found = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;", $slug));
    }
    $valid_page_found = apply_filters('ump_create_page_id', $valid_page_found, $slug, $page_content);
    if ($valid_page_found) {
        if ($option) {
            update_option($option, $valid_page_found);
        }

        return $valid_page_found;
    }


    if (strlen($page_content) > 0) {
        $trashed_page_found = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%"));
    } else {
        $trashed_page_found = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", $slug));
    }

    if ($trashed_page_found) {
        $page_id   = $trashed_page_found;
        $page_data = array(
            'ID'          => $page_id,
            'post_status' => 'publish',
        );
        wp_update_post($page_data);
    } else {
        $page_data = array(
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'post_author'    => 1,
            'post_name'      => $slug,
            'post_title'     => $page_title,
            'post_content'   => $page_content,
            'post_parent'    => $post_parent,
            'comment_status' => 'closed',
        );

        $page_id   = wp_insert_post($page_data);
        update_post_meta($page_id, 'is_ump_page', true);
    }

    if ($option) {
        update_option($option, $page_id);
    }
    return $page_id;
}

function ump_base_name_information()
{
    echo '<meta name="ump_adminajax" content="' . admin_url('admin-ajax.php') . '" />';
    echo '<meta name="ump_base_url" content="' . site_url() . '" />';
    echo '<meta name="ump_author_url" content="https://www.letscms.com" />';
}

function ump_front_register_function()
{
    global $wpdb;

    $ump_general_setting = get_option('ump_general_settings');
    //print_r($ump_general_setting);die;
    $jsonarray = array();
    $jsonarray['status'] = true;
    //print_r($_POST);
    $firstname = sanitize_text_field($_POST['ump_first_name']);
    $lastname = sanitize_text_field($_POST['ump_last_name']);
    $username = sanitize_text_field($_POST['ump_username']);
    $password = sanitize_text_field($_POST['ump_password']);
    $confirm_password = sanitize_text_field($_POST['ump_confirm_password']);
    $email = sanitize_text_field($_POST['ump_email']);
    $telephone = sanitize_text_field($_POST['ump_phone']);
    $sponsor = sanitize_text_field($_POST['ump_sponsor']);
    $address = sanitize_text_field($_POST['ump_address']);
    $epin = sanitize_text_field($_POST['ump_epin']);


    if (empty($sponsor)) {
        $sponsor = ump_get_top_level_user();
    }

    if (isset($ump_general_setting['activate_epin']) && $ump_general_setting['activate_epin'] == 'on') {
        if (empty($epin)) {
            $jsonarray['error']['ump_epin_message'] = __('ePin could Not be empty', 'ump');
            $jsonarray['status'] = false;
        }
    }

    if (empty($firstname)) {
        $jsonarray['error']['ump_first_name_message'] = __('First Name could Not be empty', 'ump');
        $jsonarray['status'] = false;
    }
    if (empty($lastname)) {
        $jsonarray['error']['ump_last_name_message'] = __('Last Name could Not be empty', 'ump');
        $jsonarray['status'] = false;
    }

    if (empty($username)) {
        $jsonarray['error']['ump_username_message'] = __('Userame could Not be empty', 'ump');
        $jsonarray['status'] = false;
    }

    if ($password != $confirm_password) {
        $jsonarray['error']['ump_confirm_password_message'] = __('Password not matched', 'ump');
        $jsonarray['status'] = false;
    }

    if (empty($email)) {
        $jsonarray['error']['ump_email_message'] = __('Email could Not be empty', 'ump');
        $jsonarray['status'] = false;
    } else if (!is_email($email)) {
        $jsonarray['error']['ump_email_message'] = __("E-mail address is invalid.", 'ump');
        $jsonarray['status'] = false;
    } else if (email_exists($email)) {
        $jsonarray['error']['ump_email_message'] = __("E-mail address is already in use.", 'mlm');
        $jsonarray['status'] = false;
    }

    if (empty($sponsor)) {
        $jsonarray['error']['ump_sponsor_message'] = __('Sponsor could Not be empty', 'ump');
        $jsonarray['status'] = false;
    }
    if (empty($address)) {
        $jsonarray['error']['ump_address_message'] = __('Address could Not be empty', 'ump');
        $jsonarray['status'] = false;
    }

    if (empty($telephone)) {
        $jsonarray['error']['ump_phone_message'] = __('Phone could Not be empty', 'ump');
        $jsonarray['status'] = false;
    }

    if (empty($jsonarray['error'])) {

        $sponsor_id = $wpdb->get_var("SELECT `ID` FROM {$wpdb->prefix}users WHERE user_login = '" . $sponsor . "'");

        $sponsor_key = $wpdb->get_var("SELECT `user_key` FROM {$wpdb->prefix}ump_users WHERE `user_id` = '" . $sponsor_id . "'");

        $ump_manage_general = get_option('ump_general_settings');

        $referrals = $wpdb->get_var("SELECT COUNT(*) ck FROM {$wpdb->prefix}ump_users WHERE `sponsor_key` = '" . $sponsor_key . "'");

        if (!empty($sponsor_key)) {

            // key check continue while the record does match  
            $user_key = ump_generateKey();
            do {
                $count_key = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ump_users WHERE `user_key` = '" . $user_key . "'");
                $flag = 1;
                if ($count_key == 1) {
                    $user_key = ump_generateKey();
                    $flag = 0;
                }
            } while ($flag == 0);

            // key check continue while the record does match

            //find parent key
            if (!empty($_GET['pk']) && $_GET['pk'] != '') {

                $parent_key = sanitize_text_field($_GET['pk']);
            } else {

                $parent_key = $sponsor_key;
            }


            $user = array(
                'user_login' => $username,
                'user_pass' => $password,
                'first_name' => $firstname,
                'last_name' => $lastname,
                'user_email' => $email
            );

            $user_id = wp_insert_user($user);
            $user = new WP_User($user_id);
            //$user->add_role( 'ump_user' );
            $user->set_role('ump_user');
            add_user_meta($user_id, 'ump_first_name', $firstname);
            add_user_meta($user_id, 'ump_last_name', $lastname);
            add_user_meta($user_id, 'ump_username', $username);
            add_user_meta($user_id, 'ump_address', $address);
            add_user_meta($user_id, 'ump_sponsor_id', $sponsor_id);
            add_user_meta($user_id, 'ump_phone', $telephone);

            $wpdb->query("INSERT INTO {$wpdb->prefix}ump_users (user_id, user_name, user_key, parent_key, sponsor_key, payment_status)
          VALUES('" . $user_id . "','" . $username . "', '" . $user_key . "', '" . $parent_key . "', '" . $sponsor_key . "','2')");
            ump_insert_hirerchyrecord($user_key);
            if (isset($epin) && !empty($epin)) {
                $wpdb->query("UPDATE {$wpdb->prefix}ump_epins SET user_key='" . $user_key . "', date_used='" . current_time('mysql') . "', status='1' WHERE epin_no='" . $epin . "'");
                $wpdb->query("UPDATE {$wpdb->prefix}ump_users SET payment_status='0' WHERE user_key='" . $user_key . "'");
                $epin_data = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}ump_epins WHERE epin_no='" . $epin . "'");
                if ($epin_data->type == 'paid') {
                    $wpdb->query("UPDATE {$wpdb->prefix}ump_users SET payment_status='1',payment_date=curdate() WHERE user_key='" . $user_key . "'");
                }
            }

            $jsonarray['status'] = true;
            $jsonarray['message'] = __('Unilevel MLM User has been created successfully.', 'ump');
        } else {
            $jsonarray['status'] = false;
            $jsonarray['message'] = __('Unilevel MLM User is Not created.', 'ump');
        }
    }
    echo json_encode($jsonarray);
    wp_die();
}

function ump_returnMemberParentkey($user_key)
{
    global $wpdb;
    $parent_key = $wpdb->get_var("SELECT parent_key FROM {$wpdb->prefix}ump_users WHERE user_key = '" . $user_key . "'");
    return $parent_key;
}

function ump_insert_hirerchyrecord($user_key)
{
    global $wpdb;

    $ump_general_setting = get_option('ump_general_settings');
    $ump_no_of_level = $ump_general_setting['ump_no_of_levels'];
    $parentUserkey[0] = $user_key;
    for ($i = 1; $i <= $ump_no_of_level; $i++) {
        $parentUserkey[$i] = ump_returnMemberParentkey($parentUserkey[$i - 1]);
        if ($parentUserkey[$i] == 0 || $parentUserkey[$i] == '') {
            break;
        } else {
            $query_insert = "INSERT INTO {$wpdb->prefix}ump_hierarchy
                   (parent_id, child_id, level) VALUES
                    ('" . $parentUserkey[$i] . "','" . $user_key . "', '" . $i . "')";
            $wpdb->query($query_insert);

            $wpdb->query("UPDATE {$wpdb->prefix}ump_users SET level='" . $i . "' WHERE user_key='" . $parentUserkey[$i] . "'");
        }
    }
}
//////////////. user name exist ////////////////

function ump_username_exist_function()
{
    global $wpdb;

    $json = array();
    $json['status'] = false;
    $username = sanitize_text_field($_POST['username']);

    $ump_user = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}ump_users WHERE user_name='" . $username . "'");

    if (!empty($ump_user)) {
        $json['status'] = false;
        $json['message'] = '<span style="color:red">' . __('User Already Exist. Please try another user', 'ump') . '</span>';
    } elseif (empty($username)) {
        $json['status'] = false;
        $json['message'] = '<span style="color:red">' . __('User Name could not be empty.', 'ump') . '</span>';
    } else {
        $json['status'] = true;
        $json['message'] = '<span style="color:green">' . __('Congratulation! This username is avaiable.', 'ump') . '</span>';
    }

    echo json_encode($json);

    wp_die();
}
function ump_email_exist_function()
{
    global $wpdb;

    $json = array();
    $json['status'] = false;
    $email = sanitize_text_field($_POST['email']);

    if (email_exists($email)) {
        $json['status'] = false;
        $json['message'] = '<span style="color:red">' . __('Email Already Used by someone. Please try another Email', 'ump') . '</span>';
    } else {
        if (empty($email)) {
            $json['status'] = false;
            $json['message'] = '<span style="color:red">' . __('Email could not be empty.', 'ump') . '</span>';
        } else {

            $json['status'] = true;
            $json['message'] = '<span style="color:green">' . __('Congratulation!This Email is avaiable.', 'ump') . '</span>';
        }
    }

    echo json_encode($json);

    wp_die();
}
function ump_epin_exist_function()
{
    global $wpdb;

    $json = array();
    $json['status'] = false;
    $epin = sanitize_text_field($_POST['epin']);

    $check_epin = $wpdb->get_var("SELECT user_key FROM {$wpdb->prefix}ump_epins WHERE epin_no='" . $epin . "'");
    $check_epin_a = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}ump_epins WHERE epin_no='" . $epin . "' AND status=0");
    $name = get_user_name_by_key($check_epin);

    if (isset($check_epin) && $check_epin != 0) {
        $json['status'] = false;
        $json['message'] = '<span style="color:red">' . __('ePin Already Used by ' . $name . '. Please try another ePin', 'ump') . '</span>';
    } else {
        if (empty($epin)) {
            $json['status'] = false;
            $json['message'] = '<span style="color:red">' . __('ePin could not be empty.', 'ump') . '</span>';
        } else if (isset($check_epin_a) && $check_epin == 0) {
            $json['status'] = true;
            $json['message'] = '<span style="color:green">' . __('Congratulation!This ePin is avaiable.', 'ump') . '</span>';
        } else if (empty($check_epin) && empty($check_epin_a)) {
            $json['status'] = false;
            $json['message'] = '<span style="color:red">' . __('Your ePin is invalid', 'ump') . '</span>';
        }
    }

    echo json_encode($json);

    wp_die();
}
function ump_sponsor_exist_function()
{
    global $wpdb;

    $json = array();
    $sponsor = sanitize_text_field($_POST['sponsor']);
    $ump_manage_general = get_option('ump_general_settings');

    $sponsor_id = $wpdb->get_var("SELECT `user_id` FROM {$wpdb->prefix}ump_users WHERE user_name = '" . $sponsor . "'");
    $sponsor_key = $wpdb->get_var("SELECT `user_key` FROM {$wpdb->prefix}ump_users WHERE `user_id` = '" . $sponsor_id . "'");



    $referrals = $wpdb->get_var("SELECT COUNT(*) ck FROM {$wpdb->prefix}ump_users WHERE `sponsor_key` = '" . $sponsor_key . "'");
    if (!empty($sponsor_id) && $ump_manage_general['ump_referrals'] != $referrals) {
        $json['status'] = true;
        $json['message'] = '<span style="color:green">' . __('Sponsor is available', 'ump') . '</span>';
    } else if (empty($sponsor)) {
        $json['status'] = false;
        $json['message'] = '<span style="color:red">' . __('Sponsor could not be empty.', 'ump') . '</span>';
    } else {
        $json['status'] = false;
        $json['message'] = '<span style="color:red">' . __('Sponsor is not available', 'ump') . '</span>';
    }

    echo json_encode($json);

    wp_die();
}
function ump_password_validation_function()
{

    global $wpdb;

    $json = array();
    $json['status'] = false;
    $password = sanitize_text_field($_POST['password']);
    $confirm_password = sanitize_text_field($_POST['confirm_password']);

    if ($password == $confirm_password) {
        $json['status'] = true;
        $json['message'] = '<span style="color:green">' . __('Congratulation! Password is valid.', 'ump') . '</span>';
    } else {
        $json['status'] = false;
        $json['message'] = '<span style="color:red">' . __('Sorry Password does not match.', 'ump') . '</span>';
    }

    echo json_encode($json);

    wp_die();
}
function get_url_ump($string)
{
    global $wpdb;
    $result = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts As post INNER JOIN {$wpdb->prefix}postmeta AS postmeta ON post.ID=postmeta.post_id WHERE post_name='" . $string . "'");
    $url = get_permalink($result);
    return $url;
}

function ump_get_current_user_key()
{
    global $current_user, $wpdb;
    $username = $current_user->user_login;
    $user_key = $wpdb->get_var("SELECT user_key FROM {$wpdb->prefix}ump_users WHERE user_name = '" . $username . "'");
    return $user_key;
}
function ump_get_top_user_key()
{
    global $current_user, $wpdb;
    $username = $current_user->user_login;
    $user_key = $wpdb->get_var("SELECT user_key FROM {$wpdb->prefix}ump_users WHERE parent_key = 0 AND sponsor_key=0");
    return $user_key;
}

function ump_getUsername($key)
{
    global $wpdb;

    $sql = "SELECT user_name FROM {$wpdb->prefix}ump_users WHERE user_key = '" . $key . "'";

    $username = $wpdb->get_var($sql);
    return $username;
}
function ump_getUserInfoByKey($key)
{
    global $wpdb;

    $sql = "SELECT * FROM {$wpdb->prefix}ump_users WHERE user_key = '" . $key . "'";
    $user = $wpdb->get_row($sql);

    return $user;
}
function ump_user_downlines_by_key($key)
{
    global $wpdb, $current_user;
    $results = $wpdb->get_var("SELECT  count(`user_key`) FROM {$wpdb->prefix}ump_users Where sponsor_key='" . $key . "'");
    return $results;
}
function ump_user_level_by_key($key)
{
    global $wpdb;
    $results = $wpdb->get_var("SELECT  level FROM {$wpdb->prefix}ump_users Where user_key='" . $key . "'");
    return $results;
}

function ump_user_personal_detail_by_userid($user_id)
{
    global $wpdb;
    $results = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}ump_users where user_id=" . $user_id);
    return $results;
}
function ump_user_count_by_user_key($user_key)
{
    global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ump_users where sponsor_key=" . $user_key);
    count($results);

    return count($results);
}
function ump_downliners_of_current_user($user_id)
{
    global $wpdb;
    $user_key = get_user_key_by_user_id($user_id);
    $user_data = array();
    $results = $wpdb->get_results("SELECT user_id FROM {$wpdb->prefix}ump_users where sponsor_key=$user_key");
    foreach ($results as $result) {
        $user_data[] = $result->user_id;
    }
    return $user_data;
}
function get_user_key_by_user_id($user_id)
{
    global $wpdb;
    $results = $wpdb->get_var("SELECT user_key FROM {$wpdb->prefix}ump_users where user_id=" . $user_id);
    return $results;
}
function get_user_id_by_user_key($user_key)
{
    global $wpdb;
    $results = $wpdb->get_var("SELECT user_id FROM {$wpdb->prefix}ump_users where user_key=" . $user_key);
    return $results;
}
function get_user_name_by_key($key)
{
    global $wpdb;
    $user_id = $wpdb->get_var("SELECT user_id FROM {$wpdb->prefix}ump_users where user_key='" . $key . "'");
    $user_name = $wpdb->get_var("SELECT user_login FROM {$wpdb->prefix}users where ID='" . $user_id . "'");
    return $user_name;
}
function get_user_name_by_id($id)
{
    global $wpdb;
    $user_name = $wpdb->get_var("SELECT user_login FROM {$wpdb->prefix}users where ID='" . $id . "'");
    return $user_name;
}
function get_upliners_amount($user_key)
{
    global $wpdb;
    $ump_manage_general = get_option('ump_general_settings');
    $parents = array();
    $parent_key = get_parent($user_key);
    for ($i = 1; $i <= $ump_manage_general['ump_referrals']; $i++) {
        if ($parent_key != 0) {
            $parents[] = $parent_key;
            $parent_key = get_parent($parent_key);
        }
    }
    return $parents;
}

function get_parent($user_key)
{
    global $wpdb;
    $parent_key = $wpdb->get_var("SELECT sponsor_key FROM {$wpdb->prefix}ump_users WHERE user_key=$user_key");
    return $parent_key;
}

function ump_user_check_validate_function()
{
    global $wpdb, $current_user;
    $user = wp_get_current_user();
    $roles = (array) $user->roles;
    if (!is_user_logged_in()) {
        echo '<div class="container"><div class="user_error">' . __('You are not the Unilevel Mlm Plan Member. So you are not eligible to access this page.', 'bmp');
        echo  '</div></div>';
        die;
    } else if (!in_array('ump_user', $roles)) {
        echo '<div class="container"><div class="user_error">' . __('You are not the Unilevel Mlm Plan Member. So you are not eligible to access this page.', 'bmp');
        echo  '</div></div>';
        die;
    }
}

function ump_add_custom_column_users($columns)
{
    $columns['epin'] = __('ePin', 'ump');
    $columns['payment_status'] = __('Payment Status', 'ump');
    return $columns;
}
function ump_remove_custom_column_users($column_remove)
{
    unset($column_remove['posts']); // Remove one field
    return $column_remove;
}
function ump_add_custom_column_users_value($value, $column_name, $user_id)
{
    global $wpdb;

    /***************************/
    if ('epin' == $column_name) {

        $not_ump_user = $wpdb->get_row("SELECT id FROM {$wpdb->prefix}ump_users where user_id='" . $user_id . "'");
        if ($wpdb->num_rows == '0') {
            return 'Not Unilevel User';
        } else {
            $sql = "SELECT * FROM {$wpdb->prefix}ump_users WHERE user_id = $user_id";
            $user_data = $wpdb->get_row($sql);
            $user_key = $user_data->user_key;
            /* check that it is mlm user or not */
            $res = $wpdb->get_row("SELECT epin_no FROM {$wpdb->prefix}ump_epins WHERE user_key = '" . $user_key . "'");

            if ($wpdb->num_rows > 0) {
                return $res->epin_no;
            } else {
                return 'Paid Free';
            }
        }
    }
    if ('payment_status' == $column_name) {
        $sql = "SELECT user_id, payment_status FROM {$wpdb->prefix}ump_users WHERE user_id = $user_id";
        $res = $wpdb->get_row($sql);
        $html = '';
        if ($wpdb->num_rows > 0) {
            $currStatus = $res->payment_status;

            $adminajax = "'" . admin_url('admin-ajax.php') . "'";
            global $paymentStatusArr;
            $paymentStatusArr = array(0 => 'Unpaid', 1 => 'Paid');
            $html .= '<select name="payment_status_' . $user_id . '" id="payment_status_' . $user_id . '" onchange="update_payment_status(' . $adminajax . ',' . $user_id . ',this.value)">';
            foreach ($paymentStatusArr as $row => $val) {
                if ($row == $currStatus) {
                    $sel = 'selected="selected"';
                } else {
                    $sel = '';
                }
                $html .= '<option value="' . $row . '" ' . $sel . '>' . $val . '</option>';
            }
            $html .= '</select><span id="resultmsg_' . $user_id . '"></span>';

            return $html;
        } else {
            return __('Not a MLM User', 'bmp');
        }
    }
}
function update_payment_status_ump_function()
{
    global $wpdb;
    $user_id = sanitize_text_field($_POST['user_id']);
    $status = sanitize_text_field($_POST['status']);
    $wpdb->query("UPDATE {$wpdb->prefix}ump_users SET payment_status='" . $status . "' WHERE user_id='" . $user_id . "'");
}

function ump_get_top_level_user()
{
    global $wpdb;
    $name = $wpdb->get_var("SELECT user_name FROM {$wpdb->prefix}ump_users WHERE parent_key = '0' AND sponsor_key = '0'");
    return $name;
}

function check_ump_user($id)
{
    global $wpdb;
    $name = $wpdb->get_var("SELECT user_name FROM {$wpdb->prefix}ump_users WHERE user_id = '" . $id . "'");
    return $name;
}
/********** epin creation **************/
function ump_epin_genarate($number)
{
    $characters = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
    $keys = array();
    while (count($keys) < $number) {
        $x = mt_rand(0, count($characters) - 1);
        if (!in_array($x, $keys))
            $keys[] = $x;
    }
    // extract each key from array
    $random_chars = '';
    foreach ($keys as $key)
        $random_chars .= $characters[$key];

    // display random key
    return $random_chars;
}
