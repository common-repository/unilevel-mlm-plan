<?php

/**
 * 
 *
 * @package  
 * @version  
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('UMP_Admin_ePin_Reports', false)) :
    /**
     * 
     */
    include_once dirname(__FILE__) . "/ump-epins-list.php";

    class UMP_Admin_ePin_Reports
    {

        public function get_epins_reports()
        {
            global $wpdb;
            $ump_admin_epin_list = new ump_admin_epin_list();
            $ump_admin_epin_list->prepare_items(); ?>
            <div class='wrap'>
                <div id="icon-users" class="icon32"></div>
                <h4><?php echo __('ePin reports', 'bmp'); ?></h4>
                <form method="GET">
                    <input type="hidden" name="page" value="<?php echo sanitize_text_field($_REQUEST['page']); ?>" />
                    <?php
                    $ump_admin_epin_list->search_box('Search', 'search');
                    ?>
                </form>

                <form id="epin-report" method="GET" action="">
                    <input type="hidden" name="page" value="<?php echo sanitize_text_field($_REQUEST['page']); ?>" />
                    <?php
                    $ump_admin_epin_list->display();
                    ?>
                </form>
            </div>
<?php
        }
    }
endif;
