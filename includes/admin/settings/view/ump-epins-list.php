<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


class ump_admin_epin_list extends WP_List_Table
{

    /** Class constructor */
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => __('id', 'bmp'),
            'plural'   => __('id', 'bmp'),
            'ajax'     => false

        ));
    }

    function get_sortable_columns()
    {
        $sortable_columns = array();
        return $sortable_columns;
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
                // case 'product_id':
            case 'epin_no':
            case 'type':
            case 'price':
            case 'date_generated':
            case 'user_key':
            case 'date_used':
            case 'status':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    function get_columns()
    {
        $columns = array(
            // 'product_id'    => __( 'Product Name', 'bmp' ),
            'epin_no' => __('ePin No', 'ump'),
            'type'    => __('Type', 'ump'),
            'price'    => __('Price', 'ump'),
            'date_generated'    => __('Date Generated', 'ump'),
            'user_key'    => __('User Key', 'ump'),
            'date_used'    => __('Date Used', 'ump'),
            'status'    => __('Status', 'ump')

        );

        return $columns;
    }
    public function search_box($text, $input_id)
    {

        if (isset($_REQUEST['epin_no'])) {
            $epin_no = sanitize_text_field($_REQUEST['epin_no']);
        }
        if (isset($_REQUEST['type'])) {
            $type = sanitize_text_field($_REQUEST['type']);
        }
        if (isset($_REQUEST['user_key'])) {
            $user_key = sanitize_text_field($_REQUEST['user_key']);
        }
?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <input type="text" id="<?php echo $input_id ?>" name="epin_no" value="<?php echo !empty($epin_no) ? $epin_no : ''; ?>" placeholder="Epin No" />
            <input type="text" id="type" name="type" value="<?php echo !empty($type) ? $type : ''; ?>" placeholder="Type" />
            <input type="text" id="<?php echo $input_id ?>" name="user_key" value="<?php echo !empty($user_key) ? $user_key : ''; ?>" placeholder="User Key" />
            <?php submit_button($text, 'button', false, false, array('id' => 'search-submit')); ?>
        </p>
<?php }


    function prepare_items($search = '')
    {
        // if(!empty($_REQUEST)){print_r($_REQUEST);}
        // if(!empty($search)){print_r($search);die;}
        /**
         * Retrieve customer’s data from the database
         *
         * @param int $per_page
         * @param int $page_number
         *
         * @return mixed
         */
        global $wpdb;
        global $date_format;
        $per_page = 10;
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] - 1) * 10) : 0;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $sql = "SELECT * FROM {$wpdb->prefix}ump_epins WHERE 1=1";

        if (!empty($_REQUEST['type'])) {
            $type = sanitize_text_field($_REQUEST['type']);
            $sql .= " AND type LIKE '%{$type}%'";
        }

        if (!empty($_REQUEST['epin_no'])) {
            $epin_no = sanitize_text_field($_REQUEST['epin_no']);
            $sql .= " AND epin_no LIKE '%{$epin_no}%'";
        }

        if (!empty($_REQUEST['user_key'])) {
            $user_key = sanitize_text_field($_REQUEST['user_key']);
            $sql .= " AND user_key LIKE '%{$user_key}%'";
        }




        $results = $wpdb->get_results($sql, ARRAY_A);

        $i = 0;
        $listdata = array();
        $num = $wpdb->num_rows;
        if ($num > 0) {
            foreach ($results as $row) {
                $listdata[$i]['epin_no'] = $row['epin_no'];
                $listdata[$i]['type'] = $row['type'];
                $listdata[$i]['price'] = $row['price'];
                $listdata[$i]['date_generated'] = $row['date_generated'];
                $listdata[$i]['user_key'] = $row['user_key'];
                $listdata[$i]['date_used'] = $row['date_used'];
                $listdata[$i]['status'] = $row['status'];
                $i++;
            }
        }
        $data = $listdata;

        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        $this->items = $data;
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ));
    }
}
