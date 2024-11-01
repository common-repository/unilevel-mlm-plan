<?php
global $wp, $wp_session;
$error = [];
$data = '';
$page = sanitize_text_field($_GET['page']);
$adminurl = admin_url('admin.php') . '?page=' . $page . '&tab=epins';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ump_general_settings = get_option('ump_general_settings');
    $ump_epin_length = $ump_general_settings['ump_epin_length'];
    $ump_no_of_epins = sanitize_text_field($_POST['ump_no_of_epins']);
    $ump_epin_type = sanitize_text_field($_POST['ump_epin_type']);
    if (empty($ump_epin_type)) {
        $error['epin_error'] = __('Please Select ePin Type.', 'umw');
    }
    if (empty($ump_no_of_epins)) {
        $error['no_epin_error'] = __('No of epins is required.', 'umw');
    } elseif ($ump_no_of_epins > 5000) {
        $error['no_epin_error'] = __('Only 5000 ePin is generated on single request.', 'umw');
    }
    if (empty($error)) {

        for ($i = 0; $i <= $ump_no_of_epins - 1; $i++) {
            $epin_no = ump_epin_genarate($ump_epin_length);
            //if generated key is already exist in the DB then again re-generate key
            do {
                $check = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ump_epins WHERE `epin_no` = '" . $epin_no . "'");
                $flag = 1;
                if ($check == 1) {
                    $epin_no = ump_epin_genarate($ump_epin_length);
                    $flag = 0;
                }
            } while ($flag == 0);
            $query = "INSERT INTO {$wpdb->prefix}ump_epins SET price='0', epin_no='$epin_no',type='$ump_epin_type', date_generated=now();";

            $wpdb->query($query);
            $data = __('You have successfully create ' . $ump_no_of_epins . ' no of ePins.', 'umw');
        }
    }
    //print_r($_POST);
}
?>
<div class="setting-right-section">
    <div class="container">
        <div class="col-md-12">
            <h2 class="d-inline-block"><?php echo __('ePin Settings', 'ump') ?>
            </h2>
            <span class="ml-5 text-success"><?php echo $data; ?></span>
            <form action="<?php echo $adminurl; ?>" method="post" name="ump_epins_settings">
                <table class="table border">
                    <tr>
                        <th><?php echo __('ePin Type', 'ump') ?></th>
                        <td></td>
                        <td><select required name="ump_epin_type" class="form-control" id="ump_epin_type">
                                <option value=""><?php _e('Select ePin Type', 'ump'); ?></option>
                                <option value="free"><?php _e('Free', 'ump'); ?></option>
                                <option value="paid"><?php _e('Paid', 'ump'); ?></option>
                            </select>
                            <span class="text-danger"><?php echo (isset($error['epin_error']) && !empty($error['epin_error'])) ? $error['epin_error'] : ''; ?></span>
                        </td>
                    </tr>

                    <tr>
                        <th><?php echo __('No. of ePins', 'ump') ?></th>
                        <td></td>
                        <td>
                            <input required type="text" name="ump_no_of_epins" id="ump_no_of_epins" class="form-control">
                            <span class="text-danger"><?php echo (isset($error['no_epin_error']) && !empty($error['no_epin_error'])) ? $error['no_epin_error'] : ''; ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-center">
                            <input type="submit" name="" value="<?php echo __('Generate', 'ump'); ?>" class="btn btn-primary">
                        </td>
                    </tr>

                </table>
            </form>


        </div>

    </div>


</div>