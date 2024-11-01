<?php
global $wp_session, $wpdb;
$page = sanitize_text_field($_GET['page']);
$adminurl = admin_url('admin.php') . '?page=' . $page . '&tab=general';
$ref_url = admin_url('admin.php') . '?page=' . $page . '&ttab=eligibility';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $general = sanitize_text_array($_POST['general']);
    update_option('ump_general_settings', $general);
?>
    <script type="text/javascript">
        window.location = "<?php echo $ref_url; ?>";
    </script>
<?php
}
$currencies = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ump_currency");

$general_settings = get_option('ump_general_settings');

?>

<div class="setting-right-section">
    <div class="container">
        <div class="col-md-12 border p-3">

            <h2><?php echo __('General Setting', 'bmp') ?></h2>
            <div class="col-md-12">
                <form action="<?php echo $adminurl; ?>" method="post" name="ump_general_settings.php">
                    <table class="table border">
                        <tr>
                            <th><?php echo __('Currency', 'ump'); ?></th>
                            <td><select name="general[ump_currency]" class="form-control" id="ump_currency" required>
                                    <option value=""><?php echo __('Select Currency', 'ump'); ?></option>
                                    <?php foreach ($currencies as $currency) {
                                        if (!empty($general_settings['ump_currency']) && $general_settings['ump_currency'] == $currency->iso3) {
                                    ?>
                                            <option selected value="<?php echo $currency->iso3; ?>"><?php echo $currency->currency; ?></option>
                                        <?php
                                        } else {
                                        ?>

                                            <option value="<?php echo $currency->iso3; ?>"><?php echo $currency->currency; ?></option>
                                    <?php }
                                    }
                                    ?>
                                </select></td>
                        </tr>
                        <tr>
                            <th><?php echo __('No. of Levels', 'bmp') ?></th>
                            <td><input type="text" name="general[ump_no_of_levels]" class="form-control" id="ump_no_of_levels" value="<?php echo !empty($general_settings['ump_no_of_levels']) ? $general_settings['ump_no_of_levels'] : ''; ?>" required></td>
                        </tr>
                        <tr>
                            <th><?php echo __('No. of Referrals', 'bmp') ?></th>
                            <td><input type="text" name="general[ump_referrals]" class="form-control" id="ump_referrals" value="<?php echo !empty($general_settings['ump_referrals']) ? $general_settings['ump_referrals'] : ''; ?>"></td>
                        </tr>
                        <tr>
                            <th><?php echo __('Activate ePin', 'bmp') ?></th>
                            <td><input type="checkbox" name="general[activate_epin]" class="form-control" id="activate_epin" <?php if (isset($general_settings['activate_epin']) && $general_settings['activate_epin'] == 'on') {
                                                                                                                                    echo 'checked="checked"';
                                                                                                                                } ?>>
                            </td>
                        </tr>
                        <tr id="epin_length_id">
                            <th><?php echo __('ePin Length', 'bmp') ?></th>
                            <td>
                                <select class="form-control" name="general[ump_epin_length]">
                                    <?php for ($i = 6; $i <= 12; $i++) { ?>
                                        <option value="<?php echo $i; ?>" <?php if (isset($general_settings['ump_epin_length']) && $general_settings['ump_epin_length'] == $i) {
                                                                                echo 'selected="selected"';
                                                                            } ?>><?php echo $i; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="2" class="text-center">
                                <input type="submit" name="" value="<?php echo __('Update Options', 'ump') ?>" class="btn btn-primary">
                            </td>
                        </tr>

                    </table>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    if (jQuery("#activate_epin").is(":checked")) {
        jQuery("#epin_length_id").show();
    }
    jQuery(function() {
        jQuery("#activate_epin").click(function() {
            if (jQuery(this).is(":checked")) {
                jQuery("#epin_length_id").show();
            } else {
                jQuery("#epin_length_id").hide();
            }
        });
    });
</script>