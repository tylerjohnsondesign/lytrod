<?php

class Payment_History extends WP_List_Table
{
    const perPage = 50;

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public function record_count($start = 0, $limit = 0)
    {
        $objEbiz = new WC_ebizcharge();
        $result = $objEbiz->getPaymentHistory(0, $start, $limit);
        return !empty($result) ? count($result) : 0;
    }

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $start = !empty($_GET['show']) ? $_GET['show'] : 0;
        $data = $this->table_data($start);
        $hasData = !empty($data) && is_array($data);

        if ($hasData) {
            usort($data, array(&$this, 'sort_data'));
        }

        $perPage = self::perPage;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $perPage
        ));

        if ($hasData) {
            $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);
        }

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
        $this->items = $data;
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
            'customerId' => 'Customer ID',
            'customerName' => 'Customer',
            'customerEmail' => 'Customer Email',
            'itemDesc' => 'Item Description',
            'itemSku' => 'SKU',
            'paymentDate' => 'Payment Date',
            'paymentAmount' => 'Amount',
            'cardInfo' => 'Payment Method',
            'authCode' => 'Auth Code',
            'refNum' => 'Ref Num',
            'resultStatus' => 'Result',
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
            'customerId' => array('customerId', false),
            'customerName' => array('customerName', false),
            //'customerEmail' => array('customerEmail', false),
            'itemDesc' => array('itemDesc', false),
            'itemSku' => array('itemSku', false),
            'paymentDate' => array('paymentDate', false),
            'paymentAmount' => array('paymentAmount', false),
            'authCode' => array('authCode', false),
            'refNum' => array('refNum', false),
            'resultStatus' => array('resultStatus', false),
        );
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data($start = null)
    {
        !empty($start) ? $start : 0;
        $data = array();
        $objEbiz = new WC_ebizcharge();
        $result = $objEbiz->getPaymentHistory(null, $start, $limit = self::perPage);
        if (count($result) != 0) {
            foreach ($result as $key => $value) {

                if ($value->Response->ResultCode == 'A') {
                    $status = 'Approved';
                } elseif ($value->Response->ResultCode == 'D') {
                    $status = 'Declined';
                } else {
                    $status = 'Rejected';
                }
                $cardInfo = '';
                if (isset($value->CreditCardData->CardType)) {
                    $ccType = $value->CreditCardData->CardType;
                    if ($ccType == 'A') {
                        $cardType = 'American Express';
                    } elseif ($ccType == 'M') {
                        $cardType = 'Master';
                    } elseif ($ccType == 'V') {
                        $cardType = 'Visa';
                    } elseif ($ccType == 'DS') {
                        $cardType = 'Discover';
                    } else {
                        $cardType = $ccType;
                    }
                    $cardInfo = $value->CreditCardData->CardNumber . ' - ' . $cardType;
                } else if (isset($value->CheckData->AccountType)) {
                    $accountType = $value->CheckData->AccountType;
                    $accountNumber = $value->CheckData->Account;
                    $cardInfo = $accountNumber . ' - ' . $accountType;
                }
                $customer = new WC_Customer($value->CustomerID);
                $user_email = $customer->get_email();
                $first_name = $customer->get_first_name();
                $last_name = $customer->get_last_name();
                $itemDesc = $value->LineItems->LineItem->Description ?? '';
                $itemSku = $value->LineItems->LineItem->SKU ?? '';

                $customerName = $first_name . ' ' . $last_name;
                $data[] = array(
                    'customerId' => $value->CustomerID,
                    'customerName' => $customerName,
                    'customerEmail' => $user_email,
                    'itemDesc' => $itemDesc,
                    'itemSku' => $itemSku,
                    'paymentDate' => date("Y-m-d", strtotime($value->DateTime)),
                    'paymentAmount' => "$" . number_format($value->Details->Amount, 2),
                    'cardInfo' => $cardInfo,
                    'authCode' => !empty($value->Response->AuthCode) ? $value->Response->AuthCode : 0,
                    'refNum' => $value->Response->RefNum,
                    'resultStatus' => $status,
                );
            }

            return $data;
        }
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
            case 'customerId':
            case 'customerName':
            case 'customerEmail':
            case 'itemDesc':
            case 'itemSku':
            case 'paymentDate':
            case 'paymentAmount':
            case 'cardInfo':
            case 'authCode':
            case 'refNum':
            case 'resultStatus':
                return $item[$column_name];
            default:
                return print_r($item, true);
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
            '<input type="checkbox" name="bulk-email[]" value="%s" />', $item['refNum'] . '+++' . $item['customerEmail']
        );
    }

    public function column_actions($item)
    {
        $refNumber = $item['refNum'];
        $email = $item['customerEmail'];
        return '<a href="javascript:void()"  class="print_btn" id=' . $refNumber . ' >Print</a>    
              <a href="javascript:void()" class="email_btn"  style="margin-left: 5px;" id=' . $refNumber . ' data-id=' . $email . ' >Email</a>';
    }

    public function column_cardInfo($item)
    {
        $WC_Gateway_EBiz_Rec = new WC_Gateway_EBizCharge_Recurring();
        return $WC_Gateway_EBiz_Rec->get_payment_method_data($item['cardInfo'], 25, 20);
    }

    public function get_ref_number()
    {
        $WC_Gateway_EBiz_Rec = new WC_Gateway_EBizCharge_Recurring();
        $WC_Gateway_EBiz_Rec->log(__METHOD__);
        $invoiceRefNumber = $WC_Gateway_EBiz_Rec->getReceiptRefNumber();
        $WC_Gateway_EBiz_Rec->log('Email invoice reference number: ' . $invoiceRefNumber);
        return $invoiceRefNumber;
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    public function usort_reorder($a, $b)
    {
        // If no sort, default to user_login
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'user_login';

        // If no order, default to asc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';

        // Determine sort order
        $result = strcmp($a[$orderby], $b[$orderby]);

        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }

    private function sort_data($a, $b)
    {
        // Set defaults
        $orderby = 'paymentDate';
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

    public function column_resultStatus($item)
    {
        if ($item['resultStatus'] == 'Approved') {
            $class = 'dot_green';
            $title = 'Approved';
        }
        if ($item['resultStatus'] == 'Declined') {
            $class = 'dot_red';
            $title = 'Declined';
        }
        if ($item['resultStatus'] == 'Error') {
            $class = 'dot_red';
            $title = 'Error';
        }
        if ($item['resultStatus'] == 'Rejected') {
            $class = 'dot_red';
            $title = 'Rejected';
        }
        return sprintf(
            "<span class=$class></span> $title ",
            $item['resultStatus']
        );
    }

    public function get_bulk_actions()
    {
        return array(
            'email' => 'Email receipt(s)',
            'exportHistory' => 'Export selection(s)',
        );
    }

    public function process_bulk_action()
    {
        $WC_Gateway_EBiz_Rec = new WC_Gateway_EBizCharge_Recurring();
        $selectedItems = $_POST['bulk-email'] ?? '';
        $subObject = new Susbscriptions_Econnect();

        if ((isset($_POST['action']) && $_POST['action'] == 'email') ||
            (isset($_POST['action2']) && $_POST['action2'] == 'email')
        ) {
            $refNumber = $WC_Gateway_EBiz_Rec->getReceiptRefNumber();
            if (empty($selectedItems)) {
                wp_redirect(esc_url_raw(add_query_arg(array('email' => 'no'))));
            } else {
                $subObject->sendBulkEmail($selectedItems, $refNumber);
                wp_redirect(esc_url_raw(add_query_arg(array('email' => 'yes'))));
            }
            exit;
        }

    }

    public function pagination($which)
    {
        $page = self::perPage;
        $start = !empty($_GET['show']) ? $_GET['show'] : 0;
        $pre = $start - $page;
        $pre = $pre > 0 ? $pre : 0;
        $next = $start + $page;
        $count = $this->record_count($start, $page);
        ?>
        <div class='tablenav-pages'>
            <?php
            $end = $start + $count;
            echo 'Showing ' . $start . ' - ' . $end . ' entries';
            ?>
            <button type="button" class="button action action-prev" <?php if ($start == 0) {
                echo "disabled";
            } ?>
                    onClick="window.location = '<?php echo admin_url('admin.php?page=payment-history&show=' . $pre); ?>'">
                &lt; Prev
            </button>
            <button type="button" class="button action action-next" <?php if ($count < $page) {
                echo "disabled";
            } ?>
                    onClick="window.location = '<?php echo admin_url('admin.php?page=payment-history&show=' . $next); ?>'">
                Next &gt;
            </button>

        </div>
        <?php
    }

    public function search_box($text, $input_id)
    {
        $date = str_replace(' ', '-', date('Y-m-d H:i:s'));
        $page = self::perPage;
        $start = !empty($_GET['show']) ? $_GET['show'] : 0;
        $count = $this->record_count($start, $page);
        ?>
        <input type="hidden" name="file_name" id="file_name" value="<?php echo $date; ?>">
        <div class="search-box" style="padding-top: 15px">
            <input type="hidden" id="rid" value="<?php echo $this->get_ref_number(); ?>">
            <input type="text" id="searchInput" placeholder="Search Keyword"/>
            <button type="button" class="button action" id="clearDates">Clear</button>
            <input type="hidden" id="urlClear" name="x"
                   value="<?php echo admin_url('/admin.php?page=payment-history'); ?>"/>
            <input type="hidden" id="<?php echo esc_attr($input_id); ?>" name="s" value="search"/>
            <button type="button" class="button action export_btn" style="float: right;" <?php if ($count == 0) {
                echo "disabled";
            } ?>>Export All
            </button>
        </div>
        <?php
    }
}