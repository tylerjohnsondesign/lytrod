<?php

/**
 * EbizCharge Subscription List Class Admin
 *
 */
class Subscription_List extends General_List
{
    const perPage = 50;

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    /**
     * Get the search parametter
     *
     * @return null|string
     */
    public function search_parameters()
    {
        global $wpdb;
        if (!empty($_REQUEST['s'])) {
            $sql = ' WHERE 1=1 ';
            if ($_REQUEST['start_date']) {
                $startDate = $_REQUEST['start_date'];
                $sql .= ' and DATE(rec_start_date) >= "' . DATE(esc_sql($startDate)) . '"';
            }
            if ($_REQUEST['expire_date']) {
                $expireDate = $_REQUEST['expire_date'];
                $sql .= ' and DATE(rec_end_date) <= "' . DATE(esc_sql($expireDate)) . '"';
            }
            if ($_REQUEST['schedule']) {
                $sql .= ' and rec_frequency = "' . esc_sql($_REQUEST['schedule']) . '"';
            }
            
            if ($_REQUEST['status'] !== '') {
                $sql .= ' and rec_status = ' . esc_sql($_REQUEST['status']);
            }

            if ($_REQUEST['search_keyword']) {
                $recurringTable = "{$wpdb->prefix}ebizcharge_recurring";
                $searchKeyword = esc_sql($_REQUEST['search_keyword']);
                $sql .= " and ( customer_id = '" . $searchKeyword . "'
                or  $recurringTable.id = '" . $searchKeyword . "'
                or  rec_amount like '%" . $searchKeyword . "%'
                or  rec_pmethod_name like '%" . $searchKeyword . "%'
                or  item_name like '%" . $searchKeyword . "%'
                or  meta_value = '" . $searchKeyword . "'
                or  user_email like '%" . $searchKeyword . "%'
                )";
            }
        }

        return !empty($sql) ? $sql : '';
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public function record_count()
    {
        global $wpdb;
        $searchConditions = $this->search_parameters();
        $recurringTable = "{$wpdb->prefix}ebizcharge_recurring";
        $productTable = "{$wpdb->prefix}posts";
        $productMetaTable = "{$wpdb->prefix}postmeta";
        $usersTable = "{$wpdb->prefix}users";

        $sql = "SELECT count(*) 
                FROM $recurringTable 
                    INNER JOIN $usersTable ON $recurringTable.customer_id = $usersTable.ID 
                    INNER JOIN $productTable ON $recurringTable.item_id = $productTable.ID 
                                            AND $productTable.post_type = 'product' 
                    LEFT JOIN $productMetaTable ON $productTable.ID = $productMetaTable.post_id 
                                              AND meta_key = '_sku' $searchConditions";

        return $wpdb->get_var($sql);
    }

    public function data_items($array = [])
    {
        $this->items = $array;
    }

    public function column_headers($array = [])
    {
        $this->_column_headers = $array;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        return array(
            'cb' => '<input type="checkbox" />',
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'user_email' => 'Customer Email',
            'item_name' => 'Product',
            'item_id' => 'SKU',
            'rec_item_qty' => 'Quantity',
            'rec_start_date' => 'Start Date',
            'rec_end_date' => 'End Date',
            'rec_next_due' => 'Next Payment Date',
            'rec_frequency' => 'Frequency',
            'rec_amount' => 'Amount',
            'rec_pmethod_name' => 'Payment Method',
            'rec_status' => 'Status',
//            'billing_address' => 'Billing Address',
//            'shipping_address' => 'Shipping Address',
            'actions' => 'Actions'
        );
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        global $wpdb;
        return array(
            'id' => array('id', false),
            'customer_id' => array("{$wpdb->prefix}users.ID}", false),
            'user_email' => array("user_email", false),
            'item_name' => array('item_name', false),
            'item_id' => array('item_id', false),
            'rec_item_qty' => array('rec_item_qty', false),
            'rec_start_date' => array('rec_start_date', false),
            'rec_end_date' => array('rec_end_date', false),
            'rec_next_due' => array('rec_next_due', false),
            'rec_frequency' => array('rec_frequency', false),
            'rec_amount' => array('rec_amount', false),
            'rec_status' => array('rec_status', false),
        );
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    public function table_data($per_page = 0, $page_number = 0)
    {
        global $wpdb;
        $searchConditions = $this->search_parameters();
        $recurringTable = "{$wpdb->prefix}ebizcharge_recurring";
        $productTable = "{$wpdb->prefix}posts";
        $productMetaTable = "{$wpdb->prefix}postmeta";
        $usersTable = "{$wpdb->prefix}users";

        $fromTable = '';

        $sql = "SELECT
                    $recurringTable.*,
                    DATE(rec_start_date) as rec_start_date,
                    DATE(rec_end_date) as rec_end_date,
                    DATE(rec_next_due) as rec_next_due,
                    $usersTable.user_email
                FROM
                    $recurringTable
                INNER JOIN
                    $usersTable ON $recurringTable.customer_id = $usersTable.ID
                INNER JOIN
                    $productTable ON $recurringTable.item_id = $productTable.ID AND $productTable.post_type = 'product'
                LEFT JOIN
                    $productMetaTable ON $productTable.ID = $productMetaTable.post_id AND $productMetaTable.meta_key = '_sku' $searchConditions";

        if (!empty($_REQUEST['orderby'])) {
            if ($_REQUEST['orderby'] == 'id') {
                $fromTable = $recurringTable . ".";
            }
            $sql .= ' ORDER BY ' . $fromTable . esc_sql($_REQUEST['orderby']);
            $sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';

        } else {
            $sql .= " ORDER BY $recurringTable.rec_start_date DESC";
        }
        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

        return $wpdb->get_results($sql, 'ARRAY_A');
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param Array $item Data
     * @param String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default($item, $column_name)
    {
        global $wpdb;
        switch ($column_name) {
            case "id":
            case 'customer_id':
            case 'user_email':
            case 'item_name':
            case 'item_id':
            case 'rec_item_qty':
            case 'rec_start_date':
            case 'rec_end_date':
            case 'rec_next_due':
                return $item[$column_name];
            case 'rec_amount':
                return '$' . number_format($item[$column_name], 2);
            case 'rec_status':
                return $item[$column_name];
//			case 'billing_address':
//            case 'shipping_address':
//                if (!empty($address = @unserialize(stripslashes($item[$column_name])))) {
//                    return implode(', ', $address);
//                } else {
//                    return '';
//                }
            case 'rec_frequency':
                return ucfirst($item[$column_name]);
            default:
                print_r($item, true);
        }
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['rec_scheduled_payment_internal_id']
        );
    }

    public function column_item_id($item)
    {
        return $item['meta_value'] ?? '';
    }

    public function column_actions($item)
    {
        $edit_subscription = esc_html(admin_url('admin.php?page=edit-subscription'));
        $edit_query = '&id=' . $item['id'] . '&cid=' . $item['customer_id'] . '&mid=' . $item['rec_scheduled_payment_internal_id'];
        $page_ref = '&pref=view-subscription';
        return '<a href=' . $edit_subscription . $edit_query . $page_ref . ' />Manage</a>';
    }

    public function column_rec_pmethod_name($item)
    {
        $WC_Gateway_EBiz_Rec = new WC_Gateway_EBizCharge_Recurring();
        return $WC_Gateway_EBiz_Rec->get_payment_method_data($item['rec_pmethod_name'], 25, 20);
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data($a, $b)
    {
        // Set defaults
        $orderby = 'id';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if (!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if (!empty($_GET['order'])) {
            $order = $_GET['order'];
        }

        $result = strcmp($a[$orderby], $b[$orderby]);

        if ($order === 'asc') {
            return $result;
        }

        return -$result;
    }

    public function column_rec_frequency($item)
    {
        $WC_Gateway_EBiz_Rec = new WC_Gateway_EBizCharge_Recurring();
        return $WC_Gateway_EBiz_Rec->getFrequencyName($item['rec_frequency']);
    }

    public function column_rec_status($item)
    {
        global $wpdb;
        $recurringDatesTable = "{$wpdb->prefix}ebizcharge_recurring_dates";
        $rid = $item['id'];
        $query = "select * from $recurringDatesTable where rec_id = $rid order by rec_date desc  limit 1";
        $row = $wpdb->get_row($query, 'ARRAY_A');
        if (!empty($row['rec_date'])) {

            $today = date("Y-m-d");
            $expire = $row['rec_date'];
            $today_time = strtotime($today);
            $expire_time = strtotime($expire);
            if (!empty($item['rec_status']) == 1 && $expire_time < $today_time) {
                $class = 'dot_blue';
                $title = 'Completed';
            } else {

                if ($item['rec_status'] == 0) {
                    $class = 'dot_green';
                    $title = 'On';
                }
                if ($item['rec_status'] == 1) {
                    $class = 'dot_red';
                    $title = 'Off';
                }
                if ($item['rec_status'] == 3) {
                    $class = 'dot_red';
                    $title = 'Deleted';
                }
            }

        } else {
            if ($item['rec_status'] == 0) {
                $class = 'dot_green';
                $title = 'On';
            }
            if ($item['rec_status'] == 1) {
                $class = 'dot_red';
                $title = 'Off';
            }
            if ($item['rec_status'] == 3) {
                $class = 'dot_red';
                $title = 'Deleted';
            }
        }

        return sprintf("<span class=" . $class . "></span><span class='status-title'>" . $title . "</span>", $item['rec_status']);
    }

    public function get_bulk_actions()
    {
        global $WC_Gateway_EBizCharge;

        $actions =  [
            'unsub' => 'Unsubscribe',
            'exportCSV' => 'Export selection(s)',
        ];

        if($WC_Gateway_EBizCharge->is_recurring_enabled()) {
            $actions = array_merge($actions, ['resub' => 'Resubscribe']);    
        }

        return $actions;    
    }

    public function process_bulk_action()
    {
        $selectedItems = $_REQUEST['bulk-delete'] ?? '';
        include_once 'class-wc-gateway-ebizcharge-subscription.php';
        $subecon = new Susbscriptions_Econnect();

        if ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'exportCSV') ||
            (isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'exportCSV')
        ) {
            if (!empty($selectedItems)) {
                $url = $subecon->exportCsvData($selectedItems);
                if (!empty($url)) {
                    header("Location:" . $url);
                    exit;
                }
            }
        }

        // If the bulk action is triggered
        if ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'unsub') ||
            (isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'unsub')
        ) {
            $subecon->suspendDeleteBulkSubscription($selectedItems, 1);
            wp_redirect(esc_url_raw(add_query_arg(array('unsub' => 'yes', 'sus' => ''))));
            exit;
        }

        if ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'resub') ||
            (isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'resub')
        ) {
            $subecon->suspendDeleteBulkSubscription($selectedItems, 0);
            wp_redirect(esc_url_raw(add_query_arg(array('resub' => 'yes', 'resus' => ''))));
            exit;
        }

        if ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'suspend') ||
            (isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'suspend')
        ) {
            $subecon->suspendDeleteBulkSubscription($selectedItems, 3);
            wp_redirect(esc_url_raw(add_query_arg(array('sus' => 'yes', 'unsub' => ''))));
            exit;
        }

    }

    public function search_box($text, $input_id)
    {
        $WC_Gateway_EBiz_Rec = new WC_Gateway_EBizCharge_Recurring();
        $selected_status = $_REQUEST['status'] ?? '';
        ?>
        <div class="search-box" style="padding-top: 15px">
            <select name="schedule" id="freqId" class="input">
                <?php $WC_Gateway_EBiz_Rec->getFrequencyOptions(!empty($_REQUEST['schedule']) ? $_REQUEST['schedule'] : null); ?>
            </select>
            <input type="text" class="dateclass widthFix120" name="start_date" id="start_date_search" autocomplete="off"
                   value="<?php echo !empty($_REQUEST['start_date']) ? $_REQUEST['start_date'] : ''; ?>"
                   placeholder="Start Date"/>
            <input type="text" class="dateclass widthFix120" name="expire_date" id="expire_date_search"
                   autocomplete="off"
                   value="<?php echo !empty($_REQUEST['expire_date']) ? $_REQUEST['expire_date'] : ''; ?>"
                   placeholder="End Date"/>
            <select name="status" id="statusId" class="input" style="vertical-align:top">
                <option value="">Choose a Status</option>
                <option value="0" <?php echo ($selected_status == 0) ? 'selected' : ''; ?>>On</option>
                <option value="1" <?php echo ($selected_status == 1) ? 'selected' : ''; ?>>Off</option>
            </select>

            <input type="text" name="search_keyword" id="search_keyword"
                   value="<?php echo !empty($_REQUEST['search_keyword']) ? $_REQUEST['search_keyword'] : ''; ?>"
                   placeholder="Search Keyword"/>
            <input type="hidden" id="<?php echo esc_attr($input_id); ?>" name="s" value="search"/>
            <?php submit_button($text, 'primary', '', false, array('id' => 'search-submit')); ?>
            <button type="button" class="button action" id="clearDates">Clear</button>

            <input type="hidden" id="urlClear" name="x"
                   value="<?php echo admin_url('/admin.php?page=view-subscription'); ?>"/>

        </div>
        <?php
    }
}
