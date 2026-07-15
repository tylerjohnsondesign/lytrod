<?php

/**
 * EbizCharge Future Subscription Class Admin
 *
 */
class Future_Subscription extends General_List
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
        if (!empty($_REQUEST['s'])) {
            $sql = ' WHERE 1=1 ';
            if ($_REQUEST['start_date']) {
                $sql .= ' and DATE(rec_date) >= "' . DATE($_REQUEST['start_date']) . '"';
            }
            
            if ($_REQUEST['expire_date']) {
                $sql .= ' and DATE(rec_date) <= "' . DATE($_REQUEST['expire_date']) . '"';
            }

            if ($_REQUEST['user_email']) {
                $sql .= ' and user_email LIKE "%' . esc_sql($_REQUEST['user_email']) . '%"';
            }
            
            if ($_REQUEST['status'] !== '') {
                $sql .= ' and rec_status = ' . esc_sql($_REQUEST['status']);
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
        $recurringTable = $wpdb->prefix."ebizcharge_recurring";
        $datesTable = $wpdb->prefix."ebizcharge_recurring_dates";
        $usersTable = $wpdb->prefix."users";
        $cond = $this->search_parameters();
        $sql = "SELECT count(*)
                FROM $recurringTable
                inner join $datesTable ON $recurringTable.id = $datesTable.rec_id
                inner join $usersTable ON $recurringTable.customer_id = $usersTable.ID $cond";
        return $wpdb->get_var($sql);
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
            'rec_date' => 'Date',
            'customer_id' => 'Customer ID',
            'user_email' => 'Customer Email',
            'item_name' => 'Product',
            'rec_item_qty' => 'Quantity',
            'rec_frequency' => 'Frequency',
            'rec_status' => 'Status',
            'billing_address' => 'Billing Address',
            'shipping_address' => 'Shipping Address',
            'rec_amount' => 'Amount',
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
        return array(
            'rec_date' => array('rec_date', false),
            'customer_id' => array('customer_id', false),
            'user_email' => array('user_email', false),
            'item_name' => array('item_name', false),
            'rec_item_qty' => array('rec_item_qty', false),
            'rec_frequency' => array('rec_frequency', false),
            'rec_status' => array('rec_status', false),
            'rec_amount' => array('rec_amount', false),
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
        $recurringTable = $wpdb->prefix . "ebizcharge_recurring";
        $recurringDatesTable = $wpdb->prefix . "ebizcharge_recurring_dates";
        $usersTable = $wpdb->prefix . "users";

        $cond = $this->search_parameters();

        $sql = "SELECT * ,$recurringDatesTable.id as did,user_email
                FROM $recurringTable
                inner join $recurringDatesTable ON  $recurringTable.id = $recurringDatesTable.rec_id
                inner join $usersTable ON  $recurringTable.customer_id = $usersTable.ID $cond";

        if (!empty($_REQUEST['orderby'])) {
            $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
            $sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';

        } else {
            $sql .= " ORDER BY $recurringDatesTable.rec_date DESC";
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
        switch ($column_name) {
            case 'rec_date':
            case 'customer_id':
            case 'user_email':
            case 'item_name':
            case 'rec_item_qty':
                return $item[$column_name];
            case 'rec_amount':
                return '$' . number_format($item[$column_name], 2);
            case 'rec_frequency':
                return ucfirst($item[$column_name]);
            case 'rec_status':
            case 'billing_address':
            case 'shipping_address':
                if (!empty($address = @unserialize(stripslashes($item[$column_name])))) {
                    return implode(', ', $address);
                }
                return '';
            default:
                print_r($item, true);
        }
    }

    public function column_actions($item)
    {
		$edit_subscription = esc_html(admin_url('admin.php?page=edit-subscription'));
		$edit_query = '&id=' . $item['rec_id'] . '&cid=' . $item['customer_id'] . '&mid=' . $item['rec_scheduled_payment_internal_id'];
		$page_ref = '&pref=future-subscription';
		return '<a href=' . $edit_subscription . $edit_query . $page_ref . ' />Manage</a>';
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
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['did']
        );
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
        return $rec_frequency = $WC_Gateway_EBiz_Rec->getFrequencyName($item['rec_frequency']);
    }

    public function column_rec_status($item)
    {
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

        return sprintf("<span class=$class></span> $title ", $item['rec_status']);
    }

    public function get_bulk_actions()
    {
        return [
            'exportCSV' => 'Export selection(s)'
        ];
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
                $url = $subecon->exportCsvFutureData($selectedItems);
                if (!empty($url)) {
                    header("Location:" . $url);
                    exit;
                }
            }
        }

        // If the delete bulk action is triggered
        if ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'unsub') ||
            (isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'unsub')
        ) {
            $subecon->suspendDeleteBulkSubscription($selectedItems, 1);
            wp_redirect(esc_url_raw(add_query_arg(array('unsub' => 'yes', 'sus' => ''))));
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
        
        $selected_status = $_REQUEST['status'] ?? '';
        ?>
        <div class="search-box" style="padding-top: 15px">

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
                <option value="3" <?php echo ($selected_status == 3) ? 'selected' : ''; ?>>Deleted</option>
            </select>

            <input type="text" name="user_email" id="user_email"
                   value="<?php echo !empty($_REQUEST['user_email']) ? $_REQUEST['user_email'] : ''; ?>"
                   placeholder="Customer Email"/>

            <input type="hidden" id="<?php echo esc_attr($input_id); ?>" name="s" value="search"/>
            <?php submit_button($text, 'primary', '', false, array('id' => 'search-submit')); ?>
            <button type="button" class="button action" id="clearDates">Clear</button>
            <input type="hidden" id="urlClear" name="x"
                   value="<?php echo admin_url('/admin.php?page=future-subscription'); ?>"/>
        </div>
        <?php
    }
}