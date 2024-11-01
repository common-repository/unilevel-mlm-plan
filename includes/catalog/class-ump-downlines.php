<?php
ini_set('memory_limit', '-1');
if (!defined('ABSPATH')) {
    exit;
}
class UMP_Genealogy
{

    //$clients array contain the nodes
    public $clients = array();

    //$add_page_id variable take the wordpress pageID for the network registration
    public $add_page_id;

    //$view_page_id take the wordpress pageID where the netwok to open
    public $view_page_id;

    //$counter varibale take the how many level you want to shows the network
    public $counter = 1;
    public $referral = 1000000;

    //addLeftLeg() function build the Left leg registration node of the network
    function __construct()
    {
        $ump_manage_general = get_option('ump_general_settings');
        $referral = isset($ump_manage_general['ump_referrals']) ? $ump_manage_general['ump_referrals'] : 0;
        $this->referral = (int)$referral;
        if ($this->referral == 0) {
            $this->referral = 1000000;
        }
    }

    function addchild($key)
    {
        //echo $key.'<br/>';
        $username = ump_getUsername($key);
        $str = "[{v:'" . $key . "ADD',f:'<a href=" . get_url_ump('register') . "?k=" . $key . ">ADD</a><br>'},'" . $username . "',''],";
        return $str;
    }

    //addRightLeg() function build the Right leg registration node of the network

    //buildRootNetwork() function take the key and build the root node of the network
    function buildRootNetwork($key)
    {
        $level = array();

        if (!empty($key)) {
            $username = ump_getUsername($key);
            $mlm_user = ump_getUserInfoByKey($key);
            $payment = $this->checkPaymentStatus($key, $mlm_user->payment_status, $mlm_user->user_name, $mlm_user->sponsor_key);
            $myclients[] = "[{v:'" . $mlm_user->user_name . "',f:'" . $payment . "'}, '" . $username . "', ''],";
            $this->clients[] = $myclients;
            $level[] = $key;
            return $level;
        } else {
            return $level;
        }
    }

    //buildLevelByLevelNetwork() function build the 1st and more level network
    function buildLevelByLevelNetwork($key, $counter, $level)
    {
        global $wpdb;
        //print_r($counter);
        $ump_manage_general = get_option('ump_general_settings');
        //$referral=$ump_manage_general['ump_referrals'];
        $referral = $this->referral;
        $level1 = array();

        for ($i = 0; $i < $counter; $i++) {

            if (!isset($level[$i])) {
                $level[$i] = null;
            }
            // echo '<pre>';print_r($level);echo '</pre>';
            $myclients = array();
            if ($level[$i] != 'add' && $level[$i] != '') {
                $sql = "SELECT user_name, payment_status, user_key,sponsor_key,parent_key,level FROM {$wpdb->prefix}ump_users WHERE sponsor_key = '" . $level[$i] . "'";
                $results = $wpdb->get_results($sql);

                $num = $wpdb->num_rows;

                // no child case

                if (!$num) {
                    $level1[] = 'add';
                    $myclients[] = $this->addchild($level[$i]);
                }
                //if child exist
                else if ($num > 0) {
                    $username = ump_getUsername($level[$i]);
                    //echo '<pre>';print_r($results);echo '</pre>'; die;
                    foreach ($results as $key => $row) {
                        $user_key = $row->user_key;
                        $payment_status = $row->payment_status;

                        //check user paid or not

                        $payment = $this->checkPaymentStatus($user_key, $row->payment_status, $row->user_name, $row->sponsor_key);

                        //if only one child exist
                        if ($num < $referral) {
                            //if right leg child exist
                            $myclients[] = $this->addchild($level[$i]);
                            $myclients[] = "[{v:'" . $row->user_name . "',f:'" . $payment . "'}, '" . $username . "', ''],";
                            $level1[] = $row->user_key;
                        } else if ($num == $referral) {

                            $myclients[] = "[{v:'" . $row->user_name . "',f:'" . $payment . "'}, '" . $username . "', ''],";
                            $level1[] = $row->user_key;
                        } else if ($referral == 0) {
                            $myclients[] = $this->addchild($level[$i]);
                            $myclients[] = "[{v:'" . $row->user_name . "',f:'" . $payment . "'}, '" . $username . "', ''],";
                            $level1[] = $row->user_key;
                        }
                    } //end foreach loop
                }
                $this->clients[] = $myclients;
            } // end most outer if statement
        } //end for loop
        return $level1;
    }

    //checkPaymentStatus() function check the node user is paid or not

    function checkPaymentStatus($key, $payment, $username, $sponsor_key)
    {
        $downlines = ump_user_downlines_by_key($key);
        $user_level = ump_user_level_by_key($key);

        if ($payment == 1) {

            $payment_str = '<div ><a href="' . get_url_ump('downlines') . $key . '" class="tooltip"><img src="' . UMP()->plugin_url() . '/image/user_paid.png" class="img">'/*.$username.'<br><a href="'.get_site_url().'/downlines/'.$key.'">View</a>'*/;
            /*$payment_str.='<br><span class=\"paid\">PAID</span>';*/
        } else {
            $payment_str = '<div  ><a class="tooltip"  href="' . get_url_ump('downlines') . $key . '"><img src="' . UMP()->plugin_url() . '/image/user.png" class="img">'/*.$username.'<br><a href="'.get_site_url().'/downlines/'.$key.'">View</a>'*/;
            /*$payment_str.='<br><span class=\"paid\">UNPAID</span>';*/
        }
        $payment_str .= '<span><table class="tab_t table-striped border"><tr class="tooltip_design"><td>' . __('Name', 'bmp') . '</td><td class="text-capitalize">' . $username . '</td></tr><tr class="tooltip_design"><td>' . __('UserKey', 'bmp') . '</td><td>' . $key . '</td></tr><tr class="tooltip_design"><td>' . __('Sponser', 'bmp') . '</td><td>' . $sponsor_key . '</td></tr><tr class="tooltip_design"><td>' . __('Level', 'bmp') . '</td><td>' . $user_level . '</td></tr><tr class="tooltip_design"><td>' . __('Downlines', 'bmp') . '</td><td>' . $downlines . '</td></tr></table></span>';
        $payment_str .= '</a></div>';

        return $payment_str;
    }

    function network()
    {
        global $wpdb, $wp_query;
        global $current_user;
        $ump_manage_general = get_option('ump_general_settings');
        $ump_levels = isset($ump_manage_general['ump_no_of_levels']) ? $ump_manage_general['ump_no_of_levels'] : 1;
        if (isset($_POST['downlines_username']) && !empty($_POST['downlines_username'])) {
            $username = sanitize_text_field($_POST['downlines_username']);
        } else {
            $username = $current_user->user_login;
        }

        $result = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}ump_users WHERE user_name = '" . $username . "'");

        $cur_user_key = ump_get_current_user_key();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($result)) {
            $search_query = "SELECT child_id FROM {$wpdb->prefix}ump_hierarchy WHERE parent_id = '" . $cur_user_key . "' AND child_id = '" . $result->user_key . "'";
            $search_query_key = $wpdb->get_var($search_query);
            if (!empty($search_query_key)) {
                $key = $search_query_key;
            } else {
                $key = $cur_user_key;
            }
        } else {
            $key = ump_get_current_user_key();
            // if (!empty($wp_query->query_vars['page']) && $wp_query->query_vars['page'] != '') {
            //     $key = $wp_query->query_vars['page'];
            // } else {
            // }
        }

        /*********************************************** Root/owner node ******************************************/
        $level = $this->buildRootNetwork($key);
        /*********************************************** First level ******************************************/
        $level = $this->buildLevelByLevelNetwork($key, 1, $level);
        /*********************************************** 2 and more level's ******************************************/
        if ($this->counter <= $this->referral) {
            $j = 1;
            for ($i = 2; $i <= $ump_levels; $i++, $j++) {
                $j = COUNT($level);
                $level = $this->buildLevelByLevelNetwork($key, $j, $level);
            }
        }

        return $this->clients;
    } //end of function network()

    public function downlinesFunction()
    {
        global $wpdb, $wp_query;
        global $current_user;
        $username = $current_user->user_login;
        $downlinesarray = $this->network();
        $ump_manage_general = get_option('ump_general_settings');
        $referral = $this->referral;


        $owner_user_key = $wpdb->get_var("SELECT user_key FROM {$wpdb->prefix}ump_users WHERE user_name = '" . $username . "'");
?>
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-1">
                <a class="btn" href="<?php echo get_url_ump('downlines'); ?><?php echo $owner_user_key; ?>"><?php echo __('You', 'bmp'); ?></a>

            </div>

            <div class="col-md-5">
                <form name="downlinessearch" id="downlines-usersearch" action="" method="POST">
                    <input type="text" name="downlines_username" id="downlines-username" required>
                    <input id="downlines-search" type="submit" name="downlines_search" value="<?php echo __('Search', 'bmp'); ?>" class="btn p-down-search bmp_button btn-info">
                </form>
                <div class="row col-md-12 search-message"></div>
            </div>
            <div class="col-md-3"></div>
        </div>
        <div class="row">
        </div>

        <script type='text/javascript' src='https://www.google.com/jsapi'></script>
        <script type='text/javascript'>
            google.load('visualization', '1', {
                packages: ['orgchart']
            });
            google.setOnLoadCallback(drawChart);

            function drawChart() {
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Name');
                data.addColumn('string', 'Manager');
                data.addColumn('string', 'ToolTip');
                data.addRows([<?php for ($i = 0; $i < count($downlinesarray); $i++) {
                                    for ($j = 0; $j < $referral * 2; $j++) {
                                        if (!empty($downlinesarray[$i][$j])) {
                                            echo $downlinesarray[$i][$j];
                                        }
                                    }
                                } ?>['', null, '']]);
                var chart = new google.visualization.OrgChart(document.getElementById('chart_div'));
                chart.draw(data, {
                    allowHtml: true
                });
            }
        </script>


        <div style="margin:0 auto;padding:0px;clear:both; width:90%!important;" align="center">
            <div id='chart_div'></div>
        </div>
<?php
    }
}//end of Class
