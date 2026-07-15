<?php
/**
 * EbizCharge Subscription Class Admin
 *
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Susbscriptions_Econnect
{
    public $enableEconnect = false;

    public function __construct()
    {
        global $post, $wpdb;
    }

    public function prepare_items($object,$perPage)
    {
        $columns = $object->get_columns();
        $hidden = $object->get_hidden_columns();
        $sortable = $object->get_sortable_columns();
        $per_page = $object->get_items_per_page('customers_per_page', $perPage);
        $current_page = $object->get_pagenum();
        $total_items = $object->record_count();
        $data = $object->table_data($per_page, $current_page);
        $object->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page
        ));
        $object->column_headers(array($columns, $hidden, $sortable));
        $object->process_bulk_action();
        $object->data_items($data);
    }

    public function sendBulkEmail(array $selected, $rid)
    {
        global $WC_Gateway_EBiz_Rec;
		$ebiz = new WC_ebizcharge();
        $tran = $ebiz->_initTransaction();
        $securityToken = $tran->_getUeSecurityToken();
        $client = new SoapClient($tran->_getWsdlUrl(), $tran->SoapParams());
		
		if (!empty($selected))
		{
            foreach ($selected as $id) {
                $selectedStrArray = explode('+++', $id);
                $tid = $selectedStrArray[0];
                $email = $selectedStrArray[1];
                $params = array(
                    'securityToken' => $securityToken,
                    'transactionRefNum' => $tid,
                    'receiptRefNum' => $rid,
                    'emailAddress' => $email,
                );
				$GetEmailReceiptResult = $WC_Gateway_EBiz_Rec->emailReceiptToCustomer($params);
            }
            return $GetEmailReceiptResult->StatusCode;
		}
    }

    public function suspendDeleteBulkSubscription(array $array, $statusId)
    {
        $ebiz = new WC_ebizcharge();
        $tran = $ebiz->_initTransaction();
        $securityToken = $tran->_getUeSecurityToken();
        $client = new SoapClient($tran->_getWsdlUrl(), $tran->SoapParams());

        try {
            //0 Active //1 Suspended //2 Expired //3 Canceled
            if (!empty($array)) {
                foreach ($array as $scheduleInteranleId) {
                    $params = array(
                        'securityToken' => $securityToken,
                        'scheduledPaymentInternalId' => $scheduleInteranleId,
                        'statusId' => $statusId
                    );
                    $res = $client->ModifyScheduledRecurringPaymentStatus($params);
                    $recurringPaymentStatusResult = $res->ModifyScheduledRecurringPaymentStatusResult;

                    if (!empty($recurringPaymentStatusResult)) {
                        if ($recurringPaymentStatusResult->StatusCode == 1) {
                            $this->update_recurring_status($statusId, $scheduleInteranleId);
                        }
                    }
                }
                return true;
            }
            return false;
        } catch (\Exception $e) {
            print_r($e->getMessage());
            return false;
        }
    }

    public function update_recurring_status($statusId, $scheduleInternalId)
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'ebizcharge_recurring';
        $data = array('rec_status' => $statusId);
        $condition = array('rec_scheduled_payment_internal_id' => trim($scheduleInternalId));
        $wpdb->update($tableName, $data, $condition, ['%d'], ['%s']);
    }

    public function exportCsvData(array $array)
    {
        global $wpdb;
        $tableNameRec = $wpdb->prefix . 'ebizcharge_recurring';
        $tableNameUser = $wpdb->prefix . 'users';

        $file_path = wp_upload_dir();
        $csv_path = get_home_path() . 'wp-content/uploads/';
        $csv_name = "Subscriptions_" . date('Y-m-d_h-i-s');

        if (!empty($array)) {
            $delimiter = ",";
            $filename = $csv_path . $csv_name . '.csv';
            $f = @fopen($filename, "w") or die("Unable to create a file on your server!");
            fprintf($f, "\xEF\xBB\xBF");
            $fields = array("Customer ID", "Customer", "Customer Email", "SKU", "Product", "Quantity", "Start Date", "End Date", "Next Date", "Frequency", "Amount", "Payment Method", "Status");
            fputcsv($f, $fields, $delimiter);

            $query = "SELECT *,user_nicename,user_email 
                        FROM $tableNameRec,$tableNameUser 
                        where $tableNameRec.customer_id =$tableNameUser.ID 
                        and rec_scheduled_payment_internal_id in('" . implode("', '", $array) . "')";

            $rows = $wpdb->get_results($query, 'ARRAY_A');
			
            foreach ($rows as $row) {
                if ($row['rec_status'] == 0)
                    {$status = 'Active';}
                if ($row['rec_status'] == 1)
                    {$status = 'Unsubscribed';}
                if ($row['rec_status'] == 3)
                    {$status = 'Deleted';}
				
                $product_id = $row['item_id'];
                $product = wc_get_product($product_id);
                $sku = $product->get_sku();
                $startDate = $row['rec_start_date'];
                $startDate = date("Y-m-d", strtotime($startDate));

                $expireDate = $row['rec_end_date'];
                $expireDate = date("Y-m-d", strtotime($expireDate));

                $nextDate = $row['rec_next_due'];
                $nextDate = date("Y-m-d", strtotime($nextDate));

                $lineData = array($row['customer_id'], 
								  $row['user_nicename'], 
								  $row['user_email'], 
								  $sku, 
								  $row['item_name'], 
								  $row['rec_item_qty'], 
								  $startDate, 
								  $expireDate, 
								  $nextDate, 
								  ucfirst($row['rec_frequency']), 
								  $row['rec_amount'], 
								  $row['rec_pmethod_name'], 
								  $status);
                fputcsv($f, $lineData, $delimiter);
            }
            $file = $csv_name . '.csv';
            return $url = $file_path['baseurl'] . "/" . $csv_name . ".csv";
        } else {
            return null;
        }
		
    }

    public function exportCsvFutureData(array $array)
    {
        global $wpdb;
        $tableNameRec = $wpdb->prefix . 'ebizcharge_recurring';
        $tableNameDates = $wpdb->prefix . 'ebizcharge_recurring_dates';

        $file_path = wp_upload_dir();
        $csv_path = get_home_path() . 'wp-content/uploads/';
        $csv_name = "Future_subscriptions_" . date('Y-m-d_h-i-s');

        if (!empty($array)) {
            $delimiter = ",";
            $filename = $csv_path . $csv_name . '.csv';
            $f = @fopen($filename, "w") or die("Unable to create a file on your server!");
            fprintf($f, "\xEF\xBB\xBF");
            $fields = array('Due Date', 'Item Name', 'Customer Id', 'Customer Name', 'Customer Email', 'Frequency', 'Billing Address', 'Shipping Address', 'Amount', 'Status');
            fputcsv($f, $fields, $delimiter);
			
			$query = "SELECT *, $tableNameDates.id, rec_date,rec_id 
				FROM $tableNameRec, $tableNameDates
				where $tableNameRec.id = $tableNameDates.rec_id 
				and $tableNameDates.id in('" . implode("', '", $array) . "')";
            $rows = $wpdb->get_results($query, 'ARRAY_A');
			
            foreach ($rows as $row)
			{
                $umetarow = get_user_meta( $row['customer_id'] );				
				$customerName = $umetarow['first_name'][0] . ' ' . $umetarow['last_name'][0];
				$customerBillingEmail = $umetarow['billing_email'][0];
                
                $status = '';
                if ($row['rec_status'] == 0)
                    {$status = 'Active';}
                else if ($row['rec_status'] == 1)
                    {$status = 'Unsubscribed';}
                else if ($row['rec_status'] == 3)
                    {$status = 'Deleted';}

                if (!empty($billAddress = @unserialize(stripslashes($row['billing_address'])))) {
                    $billAddress = implode(', ', $billAddress);
                } else {
                    $billAddress = '';
                }
				
                if (!empty($shipAddress = @unserialize(stripslashes($row['shipping_address'])))) {
                    $shipAddress = implode(', ', $shipAddress);
                } else {
                    $shipAddress = '';
                }

                $lineData = array($row['rec_date'],
                    $row['item_name'],
                    $row['customer_id'],
                    $customerName,
					$customerBillingEmail,
                    ucfirst($row['rec_frequency']),
                    $billAddress,
                    $shipAddress,
                    $row['rec_amount'],
                    $status);
				
                fputcsv($f, $lineData, $delimiter);
            }
            return $file_path['baseurl'] . "/" . $csv_name . '.csv';
        } else {
            return null;
        }
    }
}
$GLOBALS['Susbscriptions_Econnect'] = new Susbscriptions_Econnect();