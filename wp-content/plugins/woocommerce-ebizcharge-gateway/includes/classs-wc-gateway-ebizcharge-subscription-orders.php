<?php
/**
 * EbizCharge Subscription Orders List Class Admin
 *
 */
class Subscription_Orders extends General_List
{
    const perPage = 50;

    /**
     * Get the search parametter
     *
     * @return null|string
     */

    public function search_parameters()
    {
        if (isset($_REQUEST['search'])) {
            $sql = ' WHERE 1=1 ';
            if ($_REQUEST['start_date']) {
                $sql .= ' and DATE(order_date) >= "' . DATE($_REQUEST['start_date']) . '"';
            }

            if ($_REQUEST['expire_date']) {
                $sql .= ' and DATE(order_date) <= "' . DATE($_REQUEST['expire_date']) . '"';
            }

            if ($_REQUEST['schedule']) {
                $sql .= ' and rec_frequency = "' . esc_sql($_REQUEST['schedule']) . '"';
            }

            if ($_REQUEST['status']) {
                $sql .= ' and status = "' . esc_sql($_REQUEST['status'] === 'success' ? 1 : 0) . '"';
            }

            if ($_REQUEST['search_keyword']) {
                global $wpdb;
				$recurringTable = "{$wpdb->prefix}ebizcharge_recurring";
                $recurringOrderTable = "{$wpdb->prefix}ebizcharge_recurring_order";
                $searchKeyword = esc_sql($_REQUEST['search_keyword']);
                $sql .= " and ( customer_id = '" . $searchKeyword . "'
                        OR  $recurringOrderTable.rec_order_id = '" . $searchKeyword . "'
                        OR  $recurringTable.order_id = '" . $searchKeyword . "'
                        OR  item_name LIKE '%" . $searchKeyword . "%'
                        OR  user_email LIKE '%" . $searchKeyword . "%'
                        OR  $recurringOrderTable.message LIKE '%" . $searchKeyword . "%'
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
        $recurringTable = "{$wpdb->prefix}ebizcharge_recurring";
        $recurringOrderTable = "{$wpdb->prefix}ebizcharge_recurring_order";
        $tableNameUser = $wpdb->prefix . 'users';
        $searchConditions = $this->search_parameters();

        $sql = "SELECT count(*),user_email
                FROM $recurringTable
                inner join $recurringOrderTable ON $recurringTable.id = $recurringOrderTable.rec_id 
                inner join $tableNameUser ON $recurringTable.customer_id = $tableNameUser.ID $searchConditions";

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
            'rec_order_id' => 'Order ID',
            'order_id' => 'Parent Order ID',
            'customer_id' => 'Customer ID',
            'order_date' => 'Payment Date',
            'user_email' => 'Customer Email',
            'item_name' => 'Product',
            'rec_frequency' => 'Frequency',
            'created_date' => 'Date Created',
            'status' => 'Status',
            'message' => 'Message'
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
            'rec_order_id' => array('rec_order_id', false),
            'order_id' => array('order_id', false),
            'customer_id' => array('customer_id', false),
            'order_date' => array('order_date', false),
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
        $recurringTable = "{$wpdb->prefix}ebizcharge_recurring";
        $recurringOrderTable = "{$wpdb->prefix}ebizcharge_recurring_order";
        $tableNameUser = $wpdb->prefix . 'users';
        $searchConditions = $this->search_parameters();

        $sql = "SELECT *,user_email
                FROM $recurringTable
                inner join $recurringOrderTable ON $recurringTable.id = $recurringOrderTable.rec_id
                inner join $tableNameUser ON $recurringTable.customer_id = $tableNameUser.ID $searchConditions";

        if (!empty($_REQUEST['orderby'])) {
            $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
            $sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        } else {
            $sql .= " ORDER BY $recurringOrderTable.id DESC";
        }

        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

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
            case 'rec_order_id':
            case 'order_id':
            case 'customer_id':
            case 'created_date':
            case 'user_email':
            case 'item_name':
            case 'order_date':
            case 'status':
            case 'message':
                return $item[$column_name];
            case 'rec_frequency':
                return ucfirst($item[$column_name]);
            default:
                return print_r($item, true);
        }
    }

    public function column_order_id($item)
    {
        $id = $item['order_id'];
        if ($id == 0)
		{
			return $id;
		}
        else
		{
			return '<a href="' . admin_url('post.php?post=' . absint($id) . '&action=edit') . '" >' . $id . '</a>';
		}
    }

	public function column_rec_frequency($item)
    {
        $WC_Gateway_EBiz_Rec = new WC_Gateway_EBizCharge_Recurring();
		return $WC_Gateway_EBiz_Rec->getFrequencyName($item['rec_frequency']);
	}

    public function column_rec_order_id($item)
    {
        $id = $item['rec_order_id'];
        return '<a href="' . admin_url('post.php?post=' . absint($id) . '&action=edit') . '" >' . $id . '</a>';
    }

    public function column_status($item)
    {
        if ($item['status'] == 1) {
            $class = 'dot_green';
            $title = 'Success';
        } else {
            $class = 'dot_red';
            $title = 'Failed';

        }
        return sprintf(
            "<span class=$class></span> $title ",
            $item['status']
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
        $orderby = 'customerId';
        $order = 'desc';
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

    public function process_bulk_action()
    {
        $recObject = new Recurring_Order();
        if (isset($_REQUEST['order_create'])) {
            if (isset($_REQUEST['order_date']) && $_REQUEST['order_date'] != "") {
                $recObject->cronlog('CreateOrder run start. The time is ' . date("Y-m-d h:i:sa"));
                $recObject->checkRecurringOrders($_REQUEST['order_date'], $_REQUEST['order_end_date']);

				$messageArray = $recObject->createOrderMessage();
				$messageString = '&error='.base64_encode($messageArray['error']).'&success='.base64_encode($messageArray['success']).'&notice='.base64_encode($messageArray['notice']);

				if (isset($_REQUEST['search'])) {
                    $form = '&form=search';
                } else {
                    $form = '&form=order';
                }

				$url = admin_url('admin.php?page=subscription-orders'.$messageString);
				wp_redirect($url);
				exit();
            }
        }
    }

    public function search_box($text, $input_id)
    {
        $WC_Gateway_EBiz_Rec = new WC_Gateway_EBizCharge_Recurring();
        $status = !empty($_REQUEST['status']) ? $_REQUEST['status'] : '';
        ?>
        <div class="search-box">

            <select name="status" id="status" class="input status">
                <option value="">Status</option>
                <option value="success"<?php if ($status === 'success') {echo 'selected';} ?>>Success</option>
                <option value="failed"<?php if ($status === 'failed') {echo 'selected';} ?>>Failed</option>
            </select>

            <select name="schedule" id="frequency" class="input frequency">
                <?php $WC_Gateway_EBiz_Rec->getFrequencyOptions(!empty($_REQUEST['schedule']) ? $_REQUEST['schedule'] : null); ?>
            </select>

            <input type="text" class="dateclass widthFix120" name="start_date" id="start_date_search" autocomplete="off"
                   value="<?php echo !empty($_REQUEST['start_date']) ? $_REQUEST['start_date'] : ''; ?>"
                   placeholder="Start Date"/>
            <input type="text" class="dateclass widthFix120" name="expire_date" id="expire_date_search"
                   autocomplete="off"
                   value="<?php echo !empty($_REQUEST['expire_date']) ? $_REQUEST['expire_date'] : ''; ?>"
                   placeholder="End Date"/>
            <input type="text" class="search_keyword_orders" name="search_keyword"
                   value="<?php echo !empty($_REQUEST['search_keyword']) ? $_REQUEST['search_keyword'] : ''; ?>"
                   placeholder="Search Keyword"/>

            <input type="hidden" id="<?php echo esc_attr($input_id); ?>" name="s" value="search"/>
            <?php submit_button('Search', 'primary', 'search', false, array('id' => 'search-submit')); ?>

            <button type="button" class="button action" id="clearDates">Clear</button>
            <input type="hidden" id="urlClear" name="x" value="<?php echo admin_url('/admin.php?page=subscription-orders');?>"/>
        </div>
        <div class="create-orders">
			<div class="both-dates">
				<input type="text" class="dateclassorder widthFix120 orders-date" name="order_date" id="order_date" autocomplete="off"
					   value="<?php echo !empty($_REQUEST['order_date']) ? $_REQUEST['order_date'] : date("Y-m-d"); ?>"
					   placeholder="Start Date"/> to
				<input type="text" class="dateclassorder widthFix120 orders-end-date" name="order_end_date" id="order_end_date" autocomplete="off"
					   value="<?php echo date("Y-m-d"); ?>"
					   placeholder="End Date"/>
			</div>
            <input type="hidden" name="page_action" id="page_action" value="create_order" />
			<div class="create-orders-button">
            	<?php submit_button('Manually Create Failed Orders', 'primary', 'order_create', false, array('id' => 'download-submit')); ?>
			</div>
        </div>
        <?php
    }
}
if (!class_exists('Recurring_Order')) {
    include_once plugin_dir_path(__FILE__) . 'class-wc-gateway-ebizcharge-orders.php';
}
$GLOBALS['Recurring_Order'] = new Recurring_Order();