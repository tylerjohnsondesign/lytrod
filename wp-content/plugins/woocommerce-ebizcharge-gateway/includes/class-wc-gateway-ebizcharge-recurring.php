<?php
/**
 * EbizCharge Recurring Class Frontend
 *
 */
//set_time_limit(0);
ini_set('default_socket_timeout', 1000);

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WC_Gateway_EBizCharge_Recurring
{
    public $software;
    public $wc_ebizcharge_recurring_table;
    public $wc_ebizcharge_recurring_dates_table;
    public $wc_ebizcharge_recurring_order_table;
    public $wc_ebiz_rec_post_subscription;
    public $wc_ebiz_rec_post_coupon;
    public $wc_ebiz_rec_post_frequency;
    public $wc_ebiz_rec_post_sdate;
    public $wc_ebiz_rec_post_edate;
    public $wc_ebiz_rec_post_indefinitely;

    public function __construct()
    {
        global $post, $wpdb;
        $this->wc_ebizcharge_recurring_table = $wpdb->prefix . 'ebizcharge_recurring';
        $this->wc_ebizcharge_recurring_dates_table = $wpdb->prefix . 'ebizcharge_recurring_dates';
        $this->wc_ebizcharge_recurring_order_table = $wpdb->prefix . 'ebizcharge_recurring_order';
        $this->software = "Woocommerce";
        $this->wc_ebiz_rec_post_subscription = $_POST['subscription'] ?? "";
        $this->wc_ebiz_rec_post_coupon = $_POST['coupon'] ?? "";
        $this->wc_ebiz_rec_post_frequency = $_POST['frequency'] ?? "";
        $this->wc_ebiz_rec_post_sdate = $_POST['sdate'] ?? "";
        $this->wc_ebiz_rec_post_edate = $_POST['edate'] ?? "";
        $this->wc_ebiz_rec_post_indefinitely = !empty($_POST['indefinitely']) ? '1' : '0';
    }
	
	// Add Ebiz-Recurring logs to Woocommerce logs
	public function log($message, $level = 'info') : void
	{
		global $WC_Gateway_EBizCharge;
		$logFileName = 'Ebiz Recurring';
		try {

			if (!is_string($message)) {
				$message = print_r($message, true);
			}
			$WC_Gateway_EBizCharge->log_write($level, $message, $logFileName);
		} catch (Exception $e) {
			$WC_Gateway_EBizCharge->log_write('critical', $e, $logFileName); // critical/exception
		}
	}

    public function isUserLogedin()
    {
        return is_user_logged_in();
    }

    public function getCurrentUserId()
    {
        return get_current_user_id();
    }

    public function infiniteYears()
    {
        return '+10 years';
    }

    public function setEndDate($edate, $sdate)
    {
        $startDate = strtotime($sdate);
        if (empty($edate)) {
            $endDate = date('Y-m-d', strtotime($this->infiniteYears(), $startDate));
        } else {
            $endDate = $edate;
        }
        return $endDate;
    }

    /* Frequency active frequencies list from admin */
    public function getFrequencyOptions($selectedFrequency = null)
    {
        global $WC_Gateway_EBizCharge;
        echo "<option value=''>Choose a Frequency</option>";
        $activeFrequencies = $WC_Gateway_EBizCharge->get_active_frequencies();

        foreach ($activeFrequencies as $frequency) { ?>
            <option value="<?php echo $frequency; ?>"<?php if ($frequency == $selectedFrequency) {
                echo 'selected';
            } ?>>
                <?php echo $this->getFrequencyName($frequency); ?>
            </option>
            <?Php
        }
    }

    public function addScheduleRecurringPayment($order_id, $order, $user, $item, $addRecurrParameters)
    {
        global $WC_Gateway_EBizCharge;
        $recurringIndefinitely = $addRecurrParameters['recurringIndefinitely'];
        unset($addRecurrParameters['recurringIndefinitely']);

	    $scheduledPaymentInternalId = $this->addScheduleRecurringPaymentToGateway($addRecurrParameters);
        $order_data = $order->get_data();
        $coupon_data = $order->get_coupon_codes();
        $coupon_code = !empty($coupon_data[0]) ? $coupon_data[0] : '';

        $variation_id = $item->get_variation_id();
        $todayDate = date('Y-m-d H:i:s');

        $shippingData = $this->getOrderShipping($order);

        if (!empty($scheduledPaymentInternalId)) {
            $product = $item->get_product();
            $this->log('Recurring Payment added for Item #[' . $product->get_id() . ']-' . $scheduledPaymentInternalId);

            $recurringDates = $this->getRecurringScheduledDates($scheduledPaymentInternalId);
            $ebzRecurringTotal = count($recurringDates);
            $ebzRecurringNext = $this->getNextRecurringDate($recurringDates);
            $recurringParameters = $addRecurrParameters['recurringBilling'];

            $rec_pmethod_name = $WC_Gateway_EBizCharge->get_payment_method_details($addRecurrParameters['paymentMethodProfileId'], $user->ID);

            if (empty($product->get_parent_id()) || $product->get_parent_id() == 0) {
                $item_id = $product->get_id();
                $item_variation_id = 0;
            } else {
                $item_id = $product->get_parent_id();
                $item_variation_id = $variation_id;
            }

            $insertRecurringData = array(
                'id' => NULL,
                'rec_status' => 0,
                'rec_indefinitely' => $recurringIndefinitely,
                'customer_id' => $user->ID,
                'order_id' => $order_id,
                'item_id' => $item_id,
                'item_variation_id' => $item_variation_id,
                'item_name' => $item->get_name(),
                'rec_item_qty' => $item->get_quantity(),
                'rec_start_date' => $recurringParameters['Start'],
                'rec_end_date' => $recurringParameters['Expire'],
                'rec_frequency' => $recurringParameters['Schedule'],
                'rec_pmethod_id' => $addRecurrParameters['paymentMethodProfileId'],
                'rec_pmethod_name' => $rec_pmethod_name,
                'rec_scheduled_payment_internal_id' => $scheduledPaymentInternalId,
                'rec_total' => $ebzRecurringTotal,
                'rec_processed' => 0,
                'rec_next_due' => $ebzRecurringNext,
                'rec_remaining' => $ebzRecurringTotal,
                'billing_address' => serialize($order_data['billing']),
                'shipping_address' => serialize($order_data['shipping']),
                'rec_coupon' => $coupon_code,
                'rec_amount' => $recurringParameters['Amount'],
                'rec_shipping_method' => $shippingData['shipping_method_id'],
                'rec_shipping_amount' => $shippingData['order_shipping_amount'],
                'rec_shipping_tax' => $shippingData['order_shipping_tax'],
                'rec_failed_attempts' => 0,
                'created_at' => $todayDate,
                'updated_at' => $todayDate
            );

            $insertRecurringDataFormat = array(
                '', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%d', '%s', '%d', '%s', '%s', '%s', '%.2f', '%s', '%.2f', '%.2f', '%d', '%s', '%s'
            );

            // insert recurring to db table
            $recurringId = $this->insertRecurringToDb($insertRecurringData, $insertRecurringDataFormat);

            // insert recurring dates to recurring dates table
            $this->insertRecurringDatesToDb($recurringId, $recurringDates);

            $this->log('Subscription successfully saved. ID: ' . $scheduledPaymentInternalId);
        } else {
            $this->log('Unable to save subscription.');
        }
    }

    public function getOrderShipping($order)
    {
        $order_shipping_method = @array_shift($order->get_shipping_methods());

        return [
            'shipping_method_name' => $order->get_shipping_method(),
            'shipping_method_id' => $order_shipping_method['method_id'],
            'shipping_method_amount' => $order_shipping_method['total'],
            'order_shipping_amount' => $order->get_shipping_total(),
            'order_shipping_tax' => $order->get_shipping_tax() ?: 0,
        ];
    }

    public function addScheduleRecurringPaymentToGateway($addRecurrParameters)
    {
        global $WC_Gateway_EBizCharge;
        $securityToken = $WC_Gateway_EBizCharge->_getUeSecurityToken();
        $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $WC_Gateway_EBizCharge->SoapParams());
        $addRecurrParameters['securityToken'] = $securityToken;
        $subscription = $client->ScheduleRecurringPayment($addRecurrParameters);
        $scheduledPaymentInternalId = $subscription->ScheduleRecurringPaymentResult;
        $this->log('Subscription added on gateway with InternalId: ' . $scheduledPaymentInternalId);

        return $scheduledPaymentInternalId;
    }

    public function getShippingAmount($shippingMethod)
    {
        if (!empty($shippingMethod)) {
            $delivery_zones = WC_Shipping_Zones::get_zones();

            if (!empty($delivery_zones)) {

                foreach ((array)$delivery_zones as $key => $the_zone) {

                    foreach ($the_zone['shipping_methods'] as $value) {
                        $shipping_cost = !empty($value->cost) ? $value->cost : 0;
                        if ($shippingMethod == $value->id) {
                            return $shipping_cost;
                        }
                    }
                }
            }

            return 0;
        }
    }

    public function modifyScheduleRecurringPayment($editFormData, $recurringID)
    {
        global $wpdb, $WC_Gateway_EBizCharge;
        $itemID = $editFormData['itemID'];
        $customerID = $editFormData['customerID'];
        $itemName = $editFormData['itemName'];
        $savedPMethod = $editFormData['savedPMethod'];
        $edate = $editFormData['edate'] ?? '';
        $amount = ($editFormData['qty'] * $editFormData['itemPrice']);
        $coupon = $editFormData['coupon'];
        $shippingMethod = $editFormData['shippingMethod'];
        $shippingAmount = $this->getShippingAmount($editFormData['shippingMethod']);
        $paymentMethodProfileStatus = 1;
		$amountDiscounted = '';

        if (!empty($coupon)) {
            $ebiz = new WC_ebizcharge();
            $amountDiscounted = $ebiz->checkCouponCodeDiscount($amount, $coupon, $customerID, $itemID);
			
			if($amountDiscounted > 0 && $amount > $amountDiscounted) {
				$finalAmount = $amountDiscounted;
			} else {
				$this->log('Sorry, This coupon code is not valid for this subscription. Final amount after discount should be greater than zero.');
				return false;
			}
        } else {
			$finalAmount = $amount;
		}

        if (!empty($editFormData['selectdivPayment']) && $savedPMethod != $editFormData['selectdivPayment']) {
            $paymentMethodProfileStatus = $this->modifyRecurringPaymentMethod($editFormData['selectdivPayment'], $editFormData['scheduledPaymentInternalId']);
            $savedPMethod = $editFormData['selectdivPayment'];
        }

        if ($paymentMethodProfileStatus !== 1) {
            $this->log('Unable to update subscription payment method.');
            return false;
        }

        $recurringBilling = array(
            'Amount' => $finalAmount,
            'Enabled' => true,
            'Start' => $editFormData['sdate'],
            'Expire' => $this->setEndDate($edate, $editFormData['sdate']),
            'Next' => $this->setEndDate($edate, $editFormData['sdate']),
            'Schedule' => $editFormData['frequency'],
            'ScheduleName' => 'WooCommerce-' . $itemID . "-" . ($editFormData['variationID'] ?? 0) . "-"
                . $customerID . "-" . $editFormData['frequency'] . "-" . $savedPMethod . "-" . $editFormData['qty'],
            'ReceiptNote' => 'Item [' . $itemID . '-' . $itemName . '] recurring payment updated.',
            'ReceiptTemplateName' => 0,
            'SendCustomerReceipt' => 1
        );
        $recurringObject = array(
            'securityToken' => $WC_Gateway_EBizCharge->_getUeSecurityToken(),
            'scheduledPaymentInternalId' => trim($editFormData['scheduledPaymentInternalId']),
            'recurringBilling' => $recurringBilling
        );

        $modifyScheduledResult = $this->modifyScheduleRecurringPaymentOnGateway($recurringObject);
        if ($modifyScheduledResult->StatusCode == 1 && $paymentMethodProfileStatus == 1) {
            $recurringDates = $this->getRecurringScheduledDates($editFormData['scheduledPaymentInternalId']);
            $ebzRecurringTotal = count($recurringDates);
            $nextRecDate = $this->getNextRecurringDate($recurringDates);

            $rec_pmethod_name = $WC_Gateway_EBizCharge->get_payment_method_details($editFormData['selectdivPayment'], $editFormData['customerID']);

            $customer = new WC_Customer($editFormData['customerID']);
            $recurring_billing = $customer->get_billing();
            $recurring_shipping = $customer->get_shipping();
            $todayDate = date('Y-m-d H:i:s');

            $recurringData = array(
                'rec_indefinitely' => ($editFormData['indefinitely'] ?? 0),
                'rec_frequency' => $editFormData['frequency'],
                'rec_item_qty' => $editFormData['qty'],
                'rec_start_date' => $editFormData['sdate'],
                'rec_end_date' => $this->setEndDate($edate, $editFormData['sdate']),
                'rec_pmethod_id' => $editFormData['selectdivPayment'],
                'rec_pmethod_name' => $rec_pmethod_name,
                'rec_total' => $ebzRecurringTotal,
                'rec_processed' => '0',
                'rec_next_due' => $nextRecDate,
                'rec_remaining' => $ebzRecurringTotal,
                'billing_address' => serialize($recurring_billing),
                'shipping_address' => serialize($recurring_shipping),
                'rec_coupon' => $coupon,
                'rec_amount' => $finalAmount,
                'rec_shipping_method' => $shippingMethod,
                'rec_shipping_amount' => $shippingAmount,
                'rec_shipping_tax' => 0,
                'rec_failed_attempts' => 0,
                'updated_at' => $todayDate
            );

            $recurringDataFormat = array(
                '%d', '%s', '%d', '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%d', '%s', '%s', '%s', '%.2f', '%s', '%.2f', '%.2f', '%d', '%s'
            );

            $updateDB = $this->updateRecurringToDb($recurringID, $recurringData, $recurringDataFormat);

            if ($updateDB) {
                $this->deleteRecurringDatesBeforeUpdate($recurringID);
                $this->insertRecurringDatesToDb($recurringID, $recurringDates);
            }
            $this->log('Successfully Updated. ID: ' . $editFormData['scheduledPaymentInternalId']);

            return true;
        }

        $this->log('Unable to update subscription.');

        return false;
    }

    public function modifyScheduleRecurringPaymentOnGateway($recurringObject)
    {
        try {
            global $WC_Gateway_EBizCharge;
            $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $WC_Gateway_EBizCharge->SoapParams());
            $modifyScheduled = $client->ModifyScheduledRecurringPayment_RecurringBilling($recurringObject);
            $modifyScheduledResult = $modifyScheduled->ModifyScheduledRecurringPayment_RecurringBillingResult;
            $this->log('Subscription updated on gateway having InternalId: ' . $recurringObject['scheduledPaymentInternalId']);
            return $modifyScheduledResult;

        } catch (Exception $exception) {
            $this->log(__METHOD__ . ' Error:' . $exception->getMessage());
        }
    }

    // having issue with CC update on gateway end
    public function modifyRecurringPaymentMethod($methodId, $schedulePaymentInternalId)
    {
        try {
            global $WC_Gateway_EBizCharge;
            $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $WC_Gateway_EBizCharge->SoapParams());

            $paymentMethodProfile = array(
                'securityToken' => $WC_Gateway_EBizCharge->_getUeSecurityToken(),
                'scheduledPaymentInternalId' => trim($schedulePaymentInternalId),
                'paymentMethodProfileId' => $methodId
            );

            $paymentMethodProfileResponse = $client->ModifyScheduledRecurringPayment_PaymentMethodProfile($paymentMethodProfile);
            $paymentMethodProfileResult = $paymentMethodProfileResponse->ModifyScheduledRecurringPayment_PaymentMethodProfileResult;
            if (isset($paymentMethodProfileResult) && ($paymentMethodProfileResult->StatusCode == 1)) {
                return 1;
            }
            return 0;

        } catch (Exception $exception) {
            $this->log(__METHOD__ . ' Error:' . $exception->getMessage());
            return 0;
        }
    }

    public function getRecurringScheduledDates($scheduledPaymentInternalId)
    {
        global $WC_Gateway_EBizCharge;
        $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $WC_Gateway_EBizCharge->SoapParams());

        $transaction = $client->GetScheduledDates(
            [
                'securityToken' => $WC_Gateway_EBizCharge->_getUeSecurityToken(),
                'scheduledPaymentInternalId' => $scheduledPaymentInternalId,
            ]
        );

        if (!empty($transaction->GetScheduledDatesResult)) {
            $dates = $transaction->GetScheduledDatesResult->ScheduledDates;
            return json_decode($dates);
        }

        return [];
    }

    public function getNextRecurringDate($recurringDates)
    {
        $nextDate = null;
        if (!empty($recurringDates)) {

            $todayDate = date('Y-m-d');

            foreach ($recurringDates as $count => $recurringDate) {
                if (strtotime($todayDate) < strtotime($recurringDate)) {
                    $nextDate = date('Y-m-d', strtotime($recurringDate));
                    break;
                }
            }
        }
        return $nextDate;
    }

    /* DB Queries start */
    public function getCustomerAllRecurringsFromDb()
    {
        global $wpdb;
        $table_name = $this->wc_ebizcharge_recurring_table;
        $currentUserId = $this->getCurrentUserId();
        $sql = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE customer_id = %d", $currentUserId);
        return $wpdb->get_results($sql);
    }

    /**
     * @param $product_id
     * @param $status
     * @param $current_user_id
     * @return mixed
     */
    public function getRecordsFromDb($product_id, $status, $current_user_id)
    {
        global $wpdb;
        $table_name = $this->wc_ebizcharge_recurring_table;
        $sql = $wpdb->prepare("select * from " . $table_name . " where item_id='%d' and rec_status='%d' and customer_id='%d'", $product_id, $status, $current_user_id);
        return $wpdb->get_results($sql);
    }

    public function getAllRecordsFromDb($status = null)
    {
        global $wpdb;
        $todayDate = date('Y-m-d');
        $table_name = $this->wc_ebizcharge_recurring_table;
        $sql = $wpdb->prepare("select * from " . $table_name . " where rec_status = '%d' and rec_end_date >= '%s'", $status, $todayDate);
        return $wpdb->get_results($sql);
    }

    public function insertRecurringToDb($recurringData, $recurringDataFormat)
    {
        global $wpdb;
        $table_name = $this->wc_ebizcharge_recurring_table;
        $wpdb->insert($table_name, $recurringData, $recurringDataFormat);
        return $wpdb->insert_id;
    }

    public function updateRecurringToDb($recurringID, $recurringData, $recurringDataFormat)
    {
        global $wpdb;
        $table_name = $this->wc_ebizcharge_recurring_table;
        $wpdb->update($table_name, $recurringData, array('id' => $recurringID), $recurringDataFormat);
        $lastid = $wpdb->insert_id;
        if ($lastid) {
            return true;
        }
        return false;
    }

    public function deleteRecurringDatesBeforeUpdate($recurringId)
    {
        global $wpdb;
        $table_name = $this->wc_ebizcharge_recurring_dates_table;
        //return $wpdb->delete($table_name, array('rec_id' => $recurringId), array('%d'));
        return $wpdb->query("DELETE FROM ".$table_name." WHERE rec_id =".$recurringId);
    }

    public function insertRecurringDatesToDb($recurringId, $recurringDates)
    {
        global $wpdb;
        $table_name = $this->wc_ebizcharge_recurring_dates_table;
        $datesFormat = array('', '%d', '%s');
        foreach ($recurringDates as $dates) {
            $wpdb->insert($table_name, array('id' => NULL, 'rec_id' => $recurringId, 'rec_date' => $dates), $datesFormat);
            $lastid = $wpdb->insert_id;
        }

        if ($lastid) {
            return true;
        }
        return false;
    }

    /* DB Queries end */

    public function checkBetweenDates($rec_date, $start_date, $end_date)
    {
        $new_rec_date = date('Y-m-d', strtotime($rec_date));
        $old_start_date = date('Y-m-d', strtotime($start_date));
        $old_end_date = date('Y-m-d', strtotime($end_date));

        if (($new_rec_date >= $old_start_date) && ($new_rec_date <= $old_end_date)) {
            return true;
        }

        return false;
    }

    public function get_store_currency()
    {
        return get_woocommerce_currency_symbol();
    }

    public function get_subscription_status($status)
    {
        if ($status == 0) {
            $class = 'dot_green';
            $title = 'On';
        }
        if ($status == 1) {
            $class = 'dot_red';
            $title = 'Off';
        }
        if ($status == 3) {
            $class = 'dot_red';
            $title = 'Off'; // Deleted
        }
        $final_status['class'] = $class;
        $final_status['title'] = $title;
        return $final_status;
    }

    public function get_payment_status($status)
    {
        if ($status == 'A') {
            $status_title = 'Approved';
            $status_class = 'dot_green';
        } elseif ($status == 'D') {
            $status_title = 'Declined';
            $status_class = 'dot_red';
        } else {
            $status_title = 'Error';
            $status_class = 'dot_red';
        }
        $final_status['class'] = $status_class;
        $final_status['title'] = $status_title;
        return $final_status;
    }

    // used in admin subscription list and history
    public function get_payment_method_data($payment_method_name, $width, $height)
    {
        if (strpos($payment_method_name, 'XXXX') !== false) {
            $cardDigits = $this->lastFourDigit($payment_method_name);
            $payment_method_type = $this->methodName($payment_method_name);
        } else {
            $cardDigits = $this->first4digit($payment_method_name);
            $payment_method_type = strtok($payment_method_name, " ");
        }
        $get_pmethod_type_image_type = $this->getCCTypeImage($payment_method_type);
        $get_pmethod_type_image_path = plugin_dir_url(dirname(__FILE__)) . 'assets/images/' . $get_pmethod_type_image_type;
        $get_pmethod_type_image = '<img src="' . $get_pmethod_type_image_path . '" class="payment_method_image" alt="" loading="lazy" width="' . $width . '" height="' . $height . '" alt="' . $get_pmethod_type_image_type . '">';

        if (empty($cardDigits) || empty($get_pmethod_type_image_type)) {
            return 'N/A';
        }
        return $get_pmethod_type_image . ' ending in ' . $cardDigits;
    }

    // used in ajax array customer my account subscription list
    public function get_payment_method_array($payment_method_name, $width, $height)
    {
        $get_4digits = $this->first4digit($payment_method_name);
        $payment_method_type = strtok($payment_method_name, " ");
        $get_pmethod_type_image_type = $this->getCCTypeImage($payment_method_type);
        $get_pmethod_type_image_path = plugin_dir_url(dirname(__FILE__)) . 'assets/images/' . $get_pmethod_type_image_type;

        if (empty($get_4digits) || empty($get_pmethod_type_image_type)) {
            $pmethod['payment_method_type'] = null;
            $pmethod['four_digits'] = null;
            $pmethod['pmethod_image_type'] = null;
            $pmethod['pmethod_image_url'] = null;
        } else {
            $pmethod['payment_method_type'] = $payment_method_type;
            $pmethod['four_digits'] = $get_4digits;
            $pmethod['pmethod_image_type'] = $get_pmethod_type_image_type;
            $pmethod['pmethod_image_url'] = $get_pmethod_type_image_path;
        }
        return $pmethod;
    }

    public function get_ebiz_rec_payment_array($number, $type, $width, $height)
    {
        $get_4digits = substr($number, -4);
        $payment_method_type = $type;
        $get_pmethod_type_image_type = $this->getCCTypeImage($type);
        $get_pmethod_type_image_path = plugin_dir_url(dirname(__FILE__)) . 'assets/images/' . $get_pmethod_type_image_type;

        if (empty($get_4digits) || empty($get_pmethod_type_image_type)) {
            $pmethod['payment_method_type'] = null;
            $pmethod['four_digits'] = null;
            $pmethod['pmethod_image_type'] = null;
            $pmethod['pmethod_image_url'] = null;
        } else {
            $pmethod['payment_method_type'] = $payment_method_type;
            $pmethod['four_digits'] = $get_4digits;
            $pmethod['pmethod_image_type'] = $get_pmethod_type_image_type;
            $pmethod['pmethod_image_url'] = $get_pmethod_type_image_path;
        }
        return $pmethod;
    }

    public function getCCTypeImage($cardType)
    {
        $image = '';
        switch (strtolower($cardType)) {
            case 'v':
            case 'vi':
            case 'visa':
                $image = 'visa.png';
                break;
            case 'ae':
            case 'a':
            case 'american':
            case 'americanexpress':
            case 'american express':
                $image = 'american_express.png';
                break;
            case 'mc':
            case 'm':
            case 'master':
            case 'mastercard':
            case 'master card':
                $image = 'mastercard.png';
                break;
            case 'ds':
            case 'discover':
                $image = 'discover.png';
                break;
            case 'jcb':
                $image = 'jcb.png';
                break;
            case 'checking':
            case 'savings':
                $image = 'Icon-Bank-Account.svg';
                break;
            case 'Default':
                $image = 'Icon-Bank-Account.svg';
                break;
        }
        return $image;
    }

    public function lastFourDigit($string)
    {
        if (!empty($string)) {
            $nums = explode(" ", $string);
            return !empty($nums[0]) ? substr($nums[0], -4) : null;
        }
        return null;
    }

    public function methodName($string)
    {
        if (!empty($string)) {
            $names = explode(" - ", $string);
            return !empty($names[1]) ? $names[1] : null;
        }
        return null;
    }

    public function first4digit($string)
    {
        if (!empty($string)) {
            $nums = explode(" ", $string);
            foreach ($nums as $num) {
                if ((strlen($num) == 4) && (preg_match_all('!\d+!', $num))) {
                    return $num;
                }
            }
        } else {
            return null;
        }
    }

    /* to check subscriptions as end point */
    public function subscriptions_edit_is_endpoint($endpoint = false)
    {
        global $wp_query;
        if (!$wp_query) {
            return false;
        }
        return isset($wp_query->query[$endpoint]);
    }

    public function getFullUser($user_id)
    {
        return get_user_by('id', $user_id);
    }

    // Get Active Shipping Methods
    public function getActiveShippingMethods($selectedShippingMethod = null)
    {
        $shipping_packages = WC()->shipping->get_shipping_methods();
        echo "<option value=''>Please Select Shipping Method</option>";
        foreach ($shipping_packages as $shippingMethods) { ?>
            <option value="<?php echo $shippingMethods->id ?>"<?php if ($shippingMethods->id == $selectedShippingMethod || $shippingMethods->method_title == $selectedShippingMethod) {
                echo 'selected';
            } ?>>
                <?php echo $shippingMethods->method_title; ?>
            </option>
            <?Php
        }
    }

    // Get Credit Cards
    public function getSavedCC($paymentMethods, $paymentType, $selectedMID)
    {
        global $WC_Gateway_EBizCharge;
        $saved_cc = $WC_Gateway_EBizCharge->groupCustomerPaymentMethodsByType($paymentMethods, $paymentType);
        echo "<option value=''>Please Select Credit Card</option>";
        foreach ($saved_cc as $cc) {
            $card_type = $WC_Gateway_EBizCharge->get_card_type($cc->CardType);
            ?>
            <option value="<?php echo $cc->MethodID; ?>" <?php if ($cc->MethodID == $selectedMID) {
                echo 'selected';
            } ?>>
                <?php
                echo $card_type . ' ending in ' . substr($cc->CardNumber, -4) . ' (' . substr($cc->CardExpiration, -2) . '/' . substr($cc->CardExpiration, 2, 2) . ')';
                ?>
            </option>
            <?Php
        }
    }

    // Get Saved Bank Accounts
    public function getSavedBanks($paymentMethods, $paymentType, $selectedMID)
    {
        global $WC_Gateway_EBizCharge;
        $saved_bank = $WC_Gateway_EBizCharge->groupCustomerPaymentMethodsByType($paymentMethods, $paymentType);
        echo "<option value=''>Please Select Bank Account</option>";
        foreach ($saved_bank as $bank) { ?>
            <option value="<?php echo $bank->MethodID; ?>"<?php if ($bank->MethodID == $selectedMID) {
                echo 'selected';
            } ?>>
                <?php
                echo ucfirst(trim($bank->AccountType)) . " ending in " . substr($bank->Account, -4);
                ?>
            </option>
            <?Php
        }
    }

    /**
     * @param null $customerId
     * @param int $start
     * @param int $limit
     * @return array
     */
    public function getMergedListSearchTransactions($customerId = null, $limit = 1000, $startdate = "2021-01-01", $orderId = null)
    {
        //---- Fetch all Records in an array list Start ----//
        // Condition start
        $maxSize = 0;
        $position = 0;
        $recurringDetail = array();
        do {
            // Search Criteria
            $filterClerk = array(
                'FieldName' => 'Clerk',
                'ComparisonOperator' => 'eq',
                'FieldValue' => 'Recurring'
            );
            $filterStart = array(
                'FieldName' => 'created',
                'ComparisonOperator' => 'gt',
                'FieldValue' => $startdate
            );
            $searchFilters['SearchFilter'][0] = $filterClerk;
            $searchFilters['SearchFilter'][1] = $filterStart;

            if (!empty($customerId)) {
                $filterCustomer = array(
                    'FieldName' => 'CustID',
                    'ComparisonOperator' => 'eq',
                    'FieldValue' => $customerId
                );
                $searchFilters['SearchFilter'][2] = $filterCustomer;
            }

            if (!empty($orderId)) {
                $filterOrder = array(
                    'FieldName' => 'OrderID',
                    'ComparisonOperator' => 'eq',
                    'FieldValue' => $orderId
                );
                $searchFilters['SearchFilter'][3] = $filterOrder;
            }

            $response = $this->getListSearchTransactions($searchFilters, $position, $limit);

            if (!isset ($response->SearchTransactionsResult->Transactions->TransactionObject)) {
                $recurringDetail = array();
                $resultCount = 0;
            } elseif ((is_array($response->SearchTransactionsResult->Transactions->TransactionObject)) &&
                (count($response->SearchTransactionsResult->Transactions->TransactionObject)) > 1) {
                $recurringList = $response->SearchTransactionsResult->Transactions->TransactionObject;
                $resultCount = count($response->SearchTransactionsResult->Transactions->TransactionObject);
                $recurringDetail = array_merge($recurringDetail, $recurringList);
            } else {
                $recurringDetail[] = $response->SearchTransactionsResult->Transactions->TransactionObject;
                $resultCount = 1;
            }

            if ($resultCount < 1000) {
                $maxSize = 1;
            }
            $position += 1000;

        } while ($maxSize == 0);

        return ($recurringDetail);
    }

    public function getListSearchTransactions($searchFilters, $position, $limit)
    {
        global $WC_Gateway_EBizCharge;
        $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $WC_Gateway_EBizCharge->SoapParams());

        $searchTransactionsReq = array(
            'securityToken' => $WC_Gateway_EBizCharge->_getUeSecurityToken(),
            'filters' => $searchFilters,
            'matchAll' => 1,
            'countOnly' => 0,
            'start' => $position,
            'limit' => $limit,
            'sort' => 'DateTime DESC'
        );
        return $client->SearchTransactions($searchTransactionsReq);
    }

    public function getCustomerAllTransactionsFromGateway()
    {
        $current_user_id = $this->getCurrentUserId();
        $current_full_user = $this->getFullUser($current_user_id);
        $current_user_registeration_date = date('Y-m-d', strtotime($current_full_user->data->user_registered));
        return $this->getMergedListSearchTransactions($current_user_id, $limit = 1000, $current_user_registeration_date, null);
    }

    public function getReceiptRefNumber()
    {
        global $WC_Gateway_EBizCharge;
        $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $WC_Gateway_EBizCharge->SoapParams());

        $receiptsList = array(
            'securityToken' => $WC_Gateway_EBizCharge->_getUeSecurityToken(),
            'receiptType' => 'email'
        );

        $receiptsList = $client->GetReceiptsList($receiptsList);
        $getReceiptsListResult = $receiptsList->GetReceiptsListResult;
        $receiptName = 'Transaction API and Payment Form (Customer)';
        $receiptRefNum = '';
        if (isset($getReceiptsListResult)) {
            foreach ($getReceiptsListResult as $array) {
                foreach ($array as $item) {
                    if ($item->Name == $receiptName) {
                        $receiptRefNum = $item->ReceiptRefNum;
                        break;
                    }
                }
            }
        }

        return $receiptRefNum;
    }

    public function emailReceipt($refNumber, $email = null)
    {
        global $WC_Gateway_EBizCharge;

        $current_user_id = $this->getCurrentUserId();
        $current_full_user = $this->getFullUser($current_user_id);
        $current_user_email = $current_full_user->data->user_email;
        $final_email = !empty($email) ? $email : $current_user_email;

        if (!empty($refNumber)) {
            try {
                $params = array(
                    'securityToken' => $WC_Gateway_EBizCharge->_getUeSecurityToken(),
                    'transactionRefNum' => $refNumber,
                    'receiptRefNum' => $this->getReceiptRefNumber(),
                    'emailAddress' => $final_email
                );
                $emailReceiptResult = $this->emailReceiptToCustomer($params);
                if ($emailReceiptResult->StatusCode == 1) {
                    return true;
                }
            } catch (\Exception $ex) {
                $this->log($ex, __('No Receipt Found on Server to Email.'));
            }

        }
        return false;
    }

    public function emailReceiptToCustomer($params)
    {
        global $WC_Gateway_EBizCharge;
        $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $WC_Gateway_EBizCharge->SoapParams());
        $this->log(__METHOD__);
        $emailReceipt = $client->EmailReceipt($params);
        $emailReceiptResult = $emailReceipt->EmailReceiptResult;
        $this->log('Email sent status: ' . $emailReceiptResult->Status);
        $this->log('Email sent to: ' . $params['emailAddress']);
        return $emailReceiptResult;
    }

    public function printReceiptData($refNumber)
    {
        global $WC_Gateway_EBizCharge;
        $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $WC_Gateway_EBizCharge->SoapParams());
        $this->log(__METHOD__);
        if (!empty($refNumber)) {
            try {
                $params = array(
                    'securityToken' => $WC_Gateway_EBizCharge->_getUeSecurityToken(),
                    'transactionRefNum' => $refNumber,
                    'receiptRefNum' => $this->getReceiptRefNumber(),
                    'contentType' => 'html'
                );

                $GetRenderReceipt = $client->RenderReceipt($params);
                $GetRenderReceiptResult = $GetRenderReceipt->RenderReceiptResult;

                if ($GetRenderReceiptResult) {
                    return $GetRenderReceiptResult;
                }
            } catch (\Exception $ex) {
                $this->log($ex, __('No Receip Found on Server to Print.'));
            }
        }
        return false;
    }

    /** Items stock functions start **/
    public function checkRecurringItemsStock()
    {
        global $Susbscriptions_Econnect;
        $this->log(__METHOD__);
        $existing_recurrings = $this->getAllRecordsFromDb(0);
        foreach ($existing_recurrings as $recurring) {
            $product = new WC_Product($recurring->item_id);
            if ($product->is_in_stock()) {
                if ($product->managing_stock()) {
                    if ($recurring->rec_item_qty > $product->get_stock_quantity()) {
                        /*
                         * don't suspend the subscription
                        $suspend = $Susbscriptions_Econnect->suspendDeleteBulkSubscription(array($recurring->rec_scheduled_payment_internal_id), 1);
                        if ($suspend) {
                            $this->sendLowStockMail($recurring->item_id, $recurring->item_name);
                        }*/

                        $this->sendLowStockMail($recurring->item_id, $recurring->item_name);
                    }
                }

            } else {
                $suspend = $Susbscriptions_Econnect->suspendDeleteBulkSubscription(array($recurring->rec_scheduled_payment_internal_id), 1);
                if ($suspend) {
                    $this->sendLowStockMail($recurring->item_id, $recurring->item_name);
                }
            }
        }
    }

    public function sendLowStockMail($itemId, $itemName)
    {
        $toAdminEmail = get_bloginfo('admin_email');
        $subject = "Item Low Stock Notification";

        /*        $messageCust = '
		<html>
		<head>
		<title>Item Low Stock Notification</title>
		</head>
		<body>
		<p>This is system generated email for item low stock!</p>
		<table>
			<tr>
			   <td>
				   <div class="greeting">Hi,</div>
				   <div>
					   We need to inform you that below items marked for subscription are out of stock. Please contact your store support.
					   <p class="greeting">' . $itemName . '</p>
				   </div>
			   </td>
		   </tr>
		</table>
		</body>
		</html>';*/

        $messageAdmin = '
		<html>
		<head>
		<title>Item Low Stock Notification</title>
		</head>
		<body>
		<p>This is system generated email for item low stock!</p>
		<table>
			<tr>
			   <td>
				   <div class="greeting">Dear store admin,</div>
				   <div>
					   We need to inform you that below items marked for subscription are out of stock. Please check item stock.
					   <p class="greeting">[' . $itemId . '] ' . $itemName . '</p>
				   </div>
			   </td>
		   </tr>
		</table>
		</body>
		</html>
		';

        // Always set content-type when sending HTML email
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type:text/html;charset=UTF-8";

        // More headers
        $headers[] = 'From: <' . $toAdminEmail . '>';
        //$headers[] = 'Cc: ' . $toAdminEmail;
        //$headers[] = 'Bcc: '.$toAdminEmail;

        if (wp_mail($toAdminEmail, $subject, $messageAdmin, $headers)) {
            $this->log($subject . ' for [' . $itemId . '-' . $itemName . '], Email sent!');
        } else {
            $this->log($subject . ' for [' . $itemId . '-' . $itemName . '], Email not sent!');
        }

    }

    public function sendLowStockMailPhp($itemId, $itemName)
    {
        $toAdminEmail = get_bloginfo('admin_email');
        $subject = "Item Low Stock Notification";

        /*        $messageCust = '
		<html>
		<head>
		<title>Item Low Stock Notification</title>
		</head>
		<body>
		<p>This is system generated email for item low stock!</p>
		<table>
			<tr>
			   <td>
				   <div class="greeting">Hi,</div>
				   <div>
					   We need to inform you that below items marked for subscription are out of stock. Please contact your store support.
					   <p class="greeting">' . $itemName . '</p>
				   </div>
			   </td>
		   </tr>
		</table>
		</body>
		</html>';*/

        $messageAdmin = '
		<html>
		<head>
		<title>Item Low Stock Notification</title>
		</head>
		<body>
		<p>This is system generated email for item low stock!</p>
		<table>
			<tr>
			   <td>
				   <div class="greeting">Dear store admin,</div>
				   <div>
					   We need to inform you that below items marked for subscription are out of stock. Please check item stock.
					   <p class="greeting">[' . $itemId . '] ' . $itemName . '</p>
				   </div>
			   </td>
		   </tr>
		</table>
		</body>
		</html>
		';

        // Always set content-type when sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

        // More headers
        $headers .= 'From: <' . $toAdminEmail . '>' . "\r\n";
        //$headers .= 'Cc: ' . $toAdminEmail . "\r\n";
        //$headers .= 'Bcc: '.$toAdminEmail . "\r\n";

        $mail = mail($toAdminEmail, $subject, $messageAdmin, $headers);

        if ($mail == true) {
            $this->log($subject . ', Email sent!');
        } else {
            $this->log($subject . ', Email not sent!');
        }

    }

    /** Items stock functions end **/
    public function check_recurring_items_after_add_to_cart()
    {
        global $woocommerce;
        $items = $woocommerce->cart->get_cart();

        foreach ($items as $item => $values) {
            if (!empty($values['frequency'])) {
                $this->log($values['data']->get_id() . ' Item is subscribed in the cart.');
                return true;
            }

        }
        return false;
    }

    public function getFrequencyName($frequency)
    {
        $frequencyName = '';
        switch (strtolower($frequency)) {
            case 'daily':
                $frequencyName = 'Daily';
                break;
            case 'weekly':
                $frequencyName = 'Weekly';
                break;
            case 'monthly':
                $frequencyName = 'Monthly';
                break;
            case 'quarterly':
                $frequencyName = 'Quarterly';
                break;
            case 'bi-weekly':
                $frequencyName = 'Every two weeks';
                break;
            case 'bi-monthly':
                $frequencyName = 'Twice per month';
                break;
            case 'bi-annually':
                $frequencyName = 'Twice per year';
                break;
            case 'four-week':
                $frequencyName = 'Every four weeks';
                break;
            case 'two-month':
                $frequencyName = 'Every two months';
                break;
            case 'three-month':
                $frequencyName = 'Every 3 months';
                break;
            case 'four-month':
                $frequencyName = 'Every four months';
                break;
            case 'five-month':
                $frequencyName = 'Every five months';
                break;
            case 'six-month':
                $frequencyName = 'Every 6 months';
                break;
            case '90-days':
                $frequencyName = 'Every 90 days';
                break;
            case '180-days':
                $frequencyName = 'Every 180 days';
                break;
            case 'annually':
                $frequencyName = 'Yearly';
                break;
            case 'Default':
                $frequencyName = 'N/A';
                break;
        }
        return $frequencyName;
    }

    public function getPermalinkStructure(): bool
    {
        return !empty(get_option('permalink_structure'));
    }

    public function getPermalinkUrl(): string
    {
        $permalink = get_permalink(wc_get_page_id('myaccount'));

        if ($this->getPermalinkStructure()) {
            $trail = substr($permalink, -1);
            if ($trail === '/') {
                return $permalink;
            }
            return $permalink . '/';
        }
        return $permalink;
    }

    public function makePermalinkUrl($params)
    {
        $permalink = get_permalink(wc_get_page_id('myaccount'));
        $trail = substr($permalink, -1);

        if ($this->getPermalinkStructure()) {
            if ($trail === '/') {
                $permalinkFinal = $permalink . $params;
            } else {
                $permalinkFinal = $permalink . '/' . $params;
            }

        } else {
            if ($trail === '&') {
                $permalinkFinal = $permalink;
            } else {
                $permalinkFinal = $permalink . '&';
            }
            $url = $permalinkFinal . $params;
            $permalinkFinal = $this->replaceAfterFirstQuestionMark($url);
        }

        return $permalinkFinal;
    }

    private function replaceAfterFirstQuestionMark($url)
    {
        $questionMarkPosition = strpos($url, '?');

        if ($questionMarkPosition !== false) {
            $firstPart = substr($url, 0, $questionMarkPosition + 1);
            $secondPart = substr($url, $questionMarkPosition + 1);
            $secondPart = str_replace('?', '&', $secondPart);
            $secondPart = str_replace('/', '&', $secondPart);
            return $firstPart . $secondPart;
        }

        return $url;
    }
    // class ends
}

// Calling recurring WC_Gateway_EBizCharge_Recurring class
$GLOBALS['WC_Gateway_EBiz_Rec'] = new WC_Gateway_EBizCharge_Recurring();

// Include and Calling main WC_Gateway_EBizCharge class
if (!class_exists('WC_Gateway_EBizCharge')) {
    include_once plugin_dir_path(__FILE__) . 'class-wc-gateway-ebizcharge.php';
}
$GLOBALS['WC_Gateway_EBizCharge'] = new WC_Gateway_EBizCharge();

// Include and Calling frontend subscription menu class
if (!class_exists('Subscriptions_My_Account_Endpoint')) {
    include_once plugin_dir_path(__FILE__) . 'ebizcharge-recurrings-frontend-menu.php';
}
$GLOBALS['Subscriptions_My_Account_Endpoint'] = new Subscriptions_My_Account_Endpoint();
register_activation_hook(__FILE__, array('Subscriptions_My_Account_Endpoint', 'install'));

// Include and Calling main WC_Gateway_EBizCharge class
if (!class_exists('Susbscriptions_Econnect')) {
    include_once plugin_dir_path(__FILE__) . 'class-wc-gateway-ebizcharge-subscription.php';
}
$GLOBALS['Susbscriptions_Econnect'] = new Susbscriptions_Econnect();

if (!class_exists('Recurring_Order')) {
    include_once plugin_dir_path(__FILE__) . 'class-wc-gateway-ebizcharge-orders.php';
}
$GLOBALS['Recurring_Order'] = new Recurring_Order();

//------------- Recurring Functions ------------//

// Display Select field before add to cart button
add_action('woocommerce_before_add_to_cart_button', 'recurring_before_add_to_cart_button', 0);
function recurring_before_add_to_cart_button()
{
    global $WC_Gateway_EBiz_Rec, $WC_Gateway_EBizCharge;
    $frequency_value = '';
    $sdate_value = '';
    $edate_value = '';
    $indefinitely_value = '';
    $user_logged_in = $WC_Gateway_EBiz_Rec->isUserLogedin();
    $ebizcharge_enabled = $WC_Gateway_EBizCharge->is_ebizcharge_enabled();
    $recurring_enabled = $WC_Gateway_EBizCharge->is_recurring_enabled();

    if ($user_logged_in && $ebizcharge_enabled && $recurring_enabled) {

        if (isset($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_frequency) && !empty($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_frequency)) {
            $frequency_value = preg_replace("/\s+/", "", $WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_frequency);
        }

        if (isset($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_sdate) && !empty($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_sdate)) {
            $sdate_value = preg_replace("/\s+/", "", $WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_sdate);
        }

        if (isset($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_edate) && !empty($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_edate)) {
            $edate_value = preg_replace("/\s+/", "", $WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_edate);
        }

        if (isset($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_indefinitely) && !empty($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_indefinitely)) {
            $indefinitely_value = $WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_indefinitely;
        }

        ?>
        <div class="subs_fields">
            <!--Custom Subscription Radio Button-->
            <div class="subs_radio">
                <div>
                    <input type="radio" name="subscription" value="" id="otp" checked>
                    <label for="otp">One time purchase</label>
                </div>
                <div>
                    <input type="radio" name="subscription" value="1" id="subscription">
                    <label for="subscription">Subscribe to product</label>
                </div>
            </div>
            <!--Custom Frequency Dropdown Field-->
            <div class="subs_frequency">
                <label for="frequency">Frequency <span class="required" title="required">*</span></label>
                <select name="frequency" id="frequency" class="input-select frequency">
                    <?php echo $WC_Gateway_EBiz_Rec->getFrequencyOptions($frequency_value); ?>
                </select>
                <div class="woocommerce woocommerce-recurring-frequency">
                    <div class="error">Please select a frequency.</div>
                </div>
            </div>
            <!--Custom Recurring Dates Field-->
            <div class="subs_dates">
                <div class="subs_sdate">
                    <label for="sdate">Start Date <span class="required" title="required">*</span></label>
                    <input type="text" autocomplete="on" id="sdate" name="sdate"
                           class="sdate _has-datepicker input-text"
                           value="<?php echo $sdate_value; ?>" placeholder="YYYY-MM-DD">
                </div>
                <div class="woocommerce woocommerce-recurring-sdate">
                    <div class="error">Please select a start date.</div>
                </div>
                <div class="subs_edate">
                    <label for="edate">End Date</label>
                    <input type="text" autocomplete="on" id="edate" name="edate"
                           class="edate _has-datepicker input-text"
                           value="<?php echo $edate_value; ?>" placeholder="YYYY-MM-DD">
                </div>
                <div class="woocommerce woocommerce-recurring-invalid-dates">
                    <div class="error">Please select valid dates according to frequency.</div>
                </div>
            </div>
            <!--Custom Recurring Indefinite Checkbox-->
            <div class="subs_indefinitely">
                <input type="checkbox" class="indefinitely" name="indefinitely" id="indefinitely"
                       value="1" <?php if ($indefinitely_value == 1): ?> checked="checked"<?php endif; ?>>
                <label for="indefinitely"> <span>Recur indefinitely</span></label>
            </div>
            <!--Custom Recurring Text-->
            <div class="subs_message">
                <label>Please select a start date for when you want to have your product subscription begin. You are
                    placing an order for the product today as well. </label>
            </div>
        </div>
        <?php
    }

    if ($ebizcharge_enabled && $recurring_enabled && !$user_logged_in) {
        ?>
        <div class="subs_fields">
            <div class="subs_radio">
                <div>
                    <input type="radio" name="subscription" value="" id="otp" checked>
                    <label for="otp">One time purchase</label>
                </div>
                <div disabled>
                    <input type="radio" name="subscription" value="1" id="subscription" disabled>
                    <label for="subscription">Subscribe to product (Login to access subscriptions)</label>
                </div>
            </div>
        </div>
        <?php
    }
}

// Validations on Selected fields before add to cart button if jQuery does not work
add_filter('woocommerce_add_to_cart_validation', 'filter_add_to_cart_validation', 10, 4);
function filter_add_to_cart_validation($passed, $product_id, $quantity, $variation_id = 0)
{
    global $WC_Gateway_EBiz_Rec;

    if (isset($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_subscription) && empty($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_subscription)) {
        if ($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_subscription != 1) {
            return true;
            exit;
        }
    }

    if (isset($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_frequency) && empty($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_frequency)) {
        wc_add_notice(__("Please choose your frequency.", "woocommerce"), 'error');
        $passed = false;
    }

    if (isset($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_sdate) && empty($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_sdate)) {
        wc_add_notice(__("Please select Start Date.", "woocommerce"), 'error');
        $passed = false;
    }

    $existing_recurrings = $WC_Gateway_EBiz_Rec->getRecordsFromDb($product_id, 0, $WC_Gateway_EBiz_Rec->getCurrentUserId());

    if (!empty($existing_recurrings)) {
        foreach ($existing_recurrings as $recurring) {
            $recurrings_data = $WC_Gateway_EBiz_Rec->checkBetweenDates($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_sdate, $recurring->rec_start_date, $recurring->rec_end_date);
            if ($recurrings_data) {
                wc_add_notice(__("Subscription already exists between selected dates for this product. Please choose other dates or update existing subscription.", "woocommerce"), 'error');
                $passed = false;
                return;
            }
        }
    }

    return $passed;
}

// Add selected recurring data as custom cart item data
add_filter('woocommerce_add_cart_item_data', 'add_recurring_to_cart_item_data', 10, 3);
function add_recurring_to_cart_item_data($cart_item_data, $product_id, $variation_id)
{
    global $WC_Gateway_EBiz_Rec;
    if (isset($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_subscription) && $WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_subscription == 1)
    {
       
        if (!empty($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_frequency)) {
            $cart_item_data['frequency'] = esc_attr($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_frequency);
        }
        
        if (!empty($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_sdate)) {
            $cart_item_data['sdate'] = esc_attr($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_sdate);
        }

        if (!empty($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_frequency)) {

            if (!empty($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_edate)) {
                $cart_item_data['edate'] = esc_attr($WC_Gateway_EBiz_Rec->wc_ebiz_rec_post_edate);

            } else {
                $cart_item_data['edate'] = esc_attr(date('Y-m-d', strtotime($WC_Gateway_EBiz_Rec->infiniteYears())));
            }
        }
    }

    return $cart_item_data;
}

// Display selected recurring under product name in cart and checkout pages
add_filter('woocommerce_get_item_data', 'display_recurring_on_cart_item', 10, 2);
function display_recurring_on_cart_item($cart_item_data, $cart_item)
{
    global $WC_Gateway_EBiz_Rec;

    if (!empty($cart_item['frequency'])) {
        $cart_item_data[] = array(
            'name' => __('Frequency'),
            'value' => $cart_item['frequency'],
        );
    }
    if (!empty($cart_item['sdate'])) {
        $cart_item_data[] = array(
            'name' => __('Start Date'),
            'value' => $cart_item['sdate'],
        );
    }

    if (!empty($cart_item['frequency'])) {

        if (!empty($cart_item['edate'])) {
            $cart_item_data[] = array(
                'name' => __('End Date'),
                'value' => $cart_item['edate'],
            );
        } else {
            $cart_item_data[] = array(
                'name' => __('End Date'),
                'value' => date('Y-m-d', strtotime($WC_Gateway_EBiz_Rec->infiniteYears())),
            );
        }
    }

    return $cart_item_data;
}

// Save selected recurrings as order item meta data and display it everywhere
add_action('woocommerce_checkout_create_order_line_item', 'save_order_item_product_recurring', 10, 4);
function save_order_item_product_recurring($item, $cart_item_key, $cart_item, $order)
{
    global $WC_Gateway_EBiz_Rec;
    if (!empty($cart_item['frequency'])) {
        $item->update_meta_data('Frequency', $cart_item['frequency']);
    }
    if (!empty($cart_item['sdate'])) {
        $item->update_meta_data('Start Date', $cart_item['sdate']);
    }

    if (!empty($cart_item['frequency'])) {

        if (!empty($cart_item['edate'])) {
            $item->update_meta_data('End Date', $cart_item['edate']);
        } else {
            $item->update_meta_data('End Date', date('Y-m-d', strtotime($WC_Gateway_EBiz_Rec->infiniteYears())));
        }
    }

}

// Save recurring details only one time to database recurring and dates table
add_action('woocommerce_thankyou', 'add_recurring_to_gateway', 10, 1);
function add_recurring_to_gateway($order_id)
{
    global $WC_Gateway_EBiz_Rec, $WC_Gateway_EBizCharge;

    if (!$order_id) {
        return;
    }
    $order = wc_get_order($order_id);

    // Allow code execution only once
    if (empty($order->get_meta('_thankyou_action_done'))) {
        // Get an instance of the WC_Order object
        $user = $order->get_user(); // Get the WP_User Object instance
        // add recurring when we have user
        if (!empty($user)) {

            if ($order->is_paid() && ($order->get_payment_method() == 'ebizcharge')
                && ($WC_Gateway_EBizCharge->is_ebizcharge_enabled())
                && ($WC_Gateway_EBizCharge->is_recurring_enabled())) {

                $ebizData = $order->get_meta('[EBIZCHARGE]|methodid|refnum|authcode|avsresultcode|cvv2resultcode|woocommerceorderid|paymethod|webcustomerid');
                $ebizPaymentMethod = explode("|", $ebizData);  // split the string into an array([0] => '"..."', etc)
                $ebizMethodId = $ebizPaymentMethod[1] ?? '';

                // Iterating through each "line" items in the order
                if (!empty($ebizMethodId)) {

                    foreach ($order->get_items() as $itemId => $item) {
                        // Get an instance of corresponding the WC_Product object
                        $schedule = wc_get_order_item_meta($itemId, 'Frequency');
                        $order_item_data = $item->get_data(); // Get WooCommerce order item meta data in an unprotected array
                        
                        $amount = $order_item_data['total'];

                        if ($amount > 0 && !empty($schedule) && !empty($user->ec_internal_id)) {
                            
                            $product = $item->get_product();
                            $productId = $product->get_id();
                            $productName = $item->get_name(); // Get the item name (product name)
                            $quantity = $item->get_quantity(); // Get the item quantity
	                        $startDate = wc_get_order_item_meta($itemId, 'Start Date');
	                        $endDate = wc_get_order_item_meta($itemId, 'End Date');
	                        $start = new DateTime($startDate);
                            $end = new DateTime($endDate);
	                        $recurringIndefinitely = $start->diff($end)->y >= 9 ? 1 : 0;

                            $scheduleName = 'WooCommerce-' . $productId . '-' . $user->ID . '-' . $schedule . '-' . $ebizMethodId . '-' . $quantity;
                            // Recurring data array
                            $recurringBilling = array(
                                'Amount' => $amount,
                                'Enabled' => 1,
                                'Start' => $startDate,
                                'Expire' => $endDate,
                                'Next' => $startDate,
                                'Schedule' => $schedule,
                                'ScheduleName' => $scheduleName,
                                'ReceiptNote' => 'Item [' . $productId . '-' . $productName . '] recurring payment added.',
                                'ReceiptTemplateName' => 0,
                                'SendCustomerReceipt' => 1
                            );

                            // adding Recurring data to gateway
                            $addRecurringParameters = array(
                                'securityToken' => '',
                                'customerInternalId' => $user->ec_internal_id,
                                'paymentMethodProfileId' => $ebizMethodId,
                                'recurringBilling' => $recurringBilling,
                                'recurringIndefinitely' => $recurringIndefinitely
                            );

                            $WC_Gateway_EBiz_Rec->addScheduleRecurringPayment($order_id, $order, $user, $item, $addRecurringParameters);
                        }
                    }
                }
            }
            // Flag the action as done (to avoid repetitions on reload for example)
            $order->update_meta_data('_thankyou_action_done', true);
            $order->save();
        }
    }
}

/*
 * Content for the Subscriptions page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
 */
add_action('woocommerce_account_subscriptions_endpoint', 'subscription_my_account_endpoint_content');

function subscription_my_account_endpoint_content()
{
    global $wpdb, $WC_Gateway_EBiz_Rec;
    $sign = $WC_Gateway_EBiz_Rec->getPermalinkStructure() ? '?' : '&';
    $recurrings_list_url = $WC_Gateway_EBiz_Rec->makePermalinkUrl('subscriptions?recurrings=list');
    $recurrings_edit_url = $WC_Gateway_EBiz_Rec->makePermalinkUrl('subscriptions-edit');
    $subscriptions_list_url = $WC_Gateway_EBiz_Rec->makePermalinkUrl('subscriptions');
    $history_list_url = $WC_Gateway_EBiz_Rec->makePermalinkUrl('history');
    ?>
    <div class="tab-links">
        <div class="subacriptions-link">
            <a href="<?php echo $subscriptions_list_url; ?>" class="tab-a active"
               title="Scheduled Subscriptions">Scheduled Subscriptions
            </a>
        </div>
        <div class="history-link">
            <a href="<?php echo $history_list_url; ?>" class="tab-a" title="Subscription Payment History">Subscription
                Payment History</a>
        </div>
    </div>
    <div class="ajax-loader">
        <img src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/ajax-loader.gif'; ?>" width="50"
             height="50" class="img-responsive"/>
    </div>
    <div class="container-subs">
        <table id="subscriptions" class="display subscriptions" cellspacing="0">
            <thead>
            <tr>
                <th>ID</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Amount</th>
                <th>Item Description</th>
                <th>Frequency</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
        </table>
    </div>
    <script type="text/javascript">
        $(document).ready(function () {
            var arrayReturn = [];
            $.ajax({
                url: "<?php echo $recurrings_list_url; ?>",
                async: false,
                dataType: 'json',
                showLoader: true,
                beforeSend: function () {
                    $('.ajax-loader').css("visibility", "visible");
                },
                complete: function () {
                    $('.ajax-loader').css("visibility", "hidden");
                },
                success: function (data) {
                    if (data == null) {
                        arrayReturn = [];
                    } else {
                        for (var i = 0, len = data.length; i < len; i++) {
                            var amount = '<span class="amount_span">$' + data[i].amount + '</span>';
                            var status = '<div class="status_div"><span class="' + data[i].status_class + '"></span>' + data[i].status + '</div>';
                            var pmethod_image_div = imagediv(data[i].pmethod_image_url, data[i].payment_method_type, data[i].four_digits);
                            var manage = '<a href="<?php echo $recurrings_edit_url; ?>' + '<?php echo $sign; ?>recID=' + data[i].id + '" target="_blank">Manage</a>';

                            arrayReturn.push([
                                data[i].id,
                                data[i].start,
                                data[i].end,
                                amount,//data[i].amount,
                                data[i].product,
                                data[i].frequency,
                                pmethod_image_div,
                                status,
                                manage
                            ]);
                        }
                    }
                    inittable(arrayReturn);
                }
            });

            function inittable(data) {
                $('#subscriptions').DataTable({
                    "aaData": data,
                    "dataSrc": function (json) {
                        for (var i = 0, ien = json.data.length; i < ien; i++) {
                            json.data[i][0] = '<a href="/message/' + json.data[i][0] + '>View message</a>';
                        }
                        return json.data;
                    },
                    "language": {
                        "emptyTable": "You have no scheduled subscriptions right now."
                    }
                });
            }

            function imagesource(img_src, image_type) {
                return '<img src="' + img_src + '" class="payment_method_image" alt="' + image_type + '" loading="lazy" width="20" height="20">';
            }

            function imagediv(img_src, image_type, lastfour) {
                if (lastfour == null) {
                    return 'N/A';
                } else {
                    return '<div class="image_div">' + imagesource(img_src, image_type) + ' ending in ' + lastfour + '</div>';
                }
            }
        });
    </script>
    <?php
}

/*
 * Content for the Edit Subscriptions page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
 */
add_action('woocommerce_account_subscriptions-edit_endpoint', 'subscription_edit_my_account_endpoint_content');
function subscription_edit_my_account_endpoint_content()
{
    global $WC_Gateway_EBiz_Rec;
    $subscriptions_list_url = $WC_Gateway_EBiz_Rec->makePermalinkUrl('subscriptions');

    if ($WC_Gateway_EBiz_Rec->subscriptions_edit_is_endpoint('subscriptions-edit') && isset($_GET['recID'])) {
        include_once(plugin_dir_path(__DIR__) . '/views/frontend/single-recurring-edit-form.php');
    } else {
        ?>
        <div class="container-subs">
            <div id="subscription-edit" class="subscription-edit">
                Recurring ID not found.
            </div>
            <button type="button" class="button" id="new-cancel-btn"
                    onclick="location.href='<?php echo esc_html($subscriptions_list_url); ?>';">
                <span>Go back</span>
            </button>
        </div>
        <?php
    }
}

/*
 * Content for the Subscriptions history page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
 */
add_action('woocommerce_account_history_endpoint', 'subscription_history_my_account_endpoint_content');
function subscription_history_my_account_endpoint_content()
{
    global $wpdb, $WC_Gateway_EBiz_Rec;
    $subscriptions_list_url = $WC_Gateway_EBiz_Rec->makePermalinkUrl('subscriptions');
    $history_list_url = $WC_Gateway_EBiz_Rec->makePermalinkUrl('history');
    $history_list_ajax_url = $WC_Gateway_EBiz_Rec->makePermalinkUrl('history?payments=list');
    $recurrings_update_url = $WC_Gateway_EBiz_Rec->makePermalinkUrl('subscriptions-update?refNumber=');
    ?>
    <div class="woocommerce" id="sub-alerts">
        <?php
        if (isset($_GET['email'])) {
            $action = $_GET['email'];
            if (($action == 'sent')) {
                ?>
                <ul class="woocommerce-message" role="alert">
                    <li>Email sent successfully.</li>
                </ul>
            <?php } else { ?>
                <ul class="woocommerce-error" role="alert">
                    <li>Email not sent.</li>
                </ul>
                <?php
            }
        }
        if (isset($_GET['print'])) {
            $action = $_GET['print'];
            if (($action == 'ok')) {
                ?>
                <ul class="woocommerce-message" role="alert">
                    <li>Receipt printed successfully.</li>
                </ul>
            <?php } else { ?>
                <ul class="woocommerce-error" role="alert">
                    <li>Receipt not printed.</li>
                </ul>
                <?php
            }
        }
        ?>
    </div>
    <div class="tab-links">
        <div class="subacriptions-link">
            <a href="<?php echo $subscriptions_list_url; ?>" class="tab-a"
               title="Scheduled Subscriptions">Scheduled Subscriptions
            </a>
        </div>
        <div class="history-link">
            <a href="<?php echo $history_list_url; ?>" class="tab-a active"
               title="Subscription Payment History">Subscription Payment History
            </a>
        </div>
    </div>
    <div class="ajax-loader">
        <img src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/images/ajax-loader.gif'; ?>" width="50"
             height="50" class="img-responsive"/>
    </div>
    <div class="container-subs">
        <table id="history" class="display history" cellspacing="0">
            <thead>
            <tr>
                <th>No.</th>
                <th>Amount</th>
                <th>Payment Date</th>
                <th>Payment Method</th>
                <th>Auth Code</th>
                <th>Reference</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
        </table>
    </div>
    <script type="text/javascript">
        $(document).ready(function () {
            var arrayReturn = [];
            $.ajax({
                url: "<?php echo $history_list_ajax_url; ?>",
                async: false,
                dataType: 'json',
                showLoader: true,
                beforeSend: function () {
                    $('.ajax-loader').css("visibility", "visible");
                },
                complete: function () {
                    $('.ajax-loader').css("visibility", "hidden");
                },
                success: function (data) {
                    if (data == null) {
                        arrayReturn = [];
                    } else {
                        for (var i = 0, len = data.length; i < len; i++) {
                            var amount = '<span class="amount_span">$' + data[i].amount + '</span>';
                            var status = '<div class="status_div"><span class="' + data[i].status_class + '"></span>' + data[i].status + '</div>';
                            //var pmethod_image = '<div class="image_div"><img src="' + data[i].pmethod_image_url + '" class="payment_method_image" alt="' + data[i].payment_method_type + '" loading="lazy" width="20" height="20">';
                            var pmethod_image_div = imagediv(data[i].pmethod_image_url, data[i].payment_method_type, data[i].four_digits);
                            var print = '<a class="print_btn" title="Please allow pop-ups before print." href="<?php echo $recurrings_update_url; ?>' + data[i].ref_num + '&print=yes">Print</a>';
                            var email = '<a class="email_btn" href="<?php echo $recurrings_update_url; ?>' + data[i].ref_num + '&email=yes">Email</a>';

                            arrayReturn.push([
                                data[i].counter,
                                amount, //data[i].amount,
                                data[i].p_date,
                                pmethod_image_div,
                                data[i].auth_code,
                                data[i].ref_num,
                                status,
                                print + '/' + email
                            ]);
                        }
                    }
                    inittable(arrayReturn);
                }
            });

            function inittable(data) {
                $('#history').DataTable({
                    "aaData": data,
                    "dataSrc": function (json) {
                        for (var i = 0, ien = json.data.length; i < ien; i++) {
                            json.data[i][0] = '<a href="/message/' + json.data[i][0] + '>View message</a>';
                        }
                        return json.data;
                    },
                    "language": {
                        "emptyTable": "No subscription payments found."
                    }
                });
            }

            function imagesource(img_src, image_type) {
                return '<img src="' + img_src + '" class="payment_method_image" alt="' + image_type + '" loading="lazy" width="20" height="20">';
            }

            function imagediv(img_src, image_type, lastfour) {
                if (lastfour == null) {
                    return 'N/A';
                } else {
                    return '<div class="image_div">' + imagesource(img_src, image_type) + ' ending in ' + lastfour + '</div>';
                }
            }

        });
    </script>
    <?php
}

// Cron functions start
add_filter('cron_schedules', 'subscription_schedule_cron');
function subscription_schedule_cron($schedules)
{
    $schedules['create_order_daily'] = array(
        'interval' => 1800,
        'display' => esc_html__('EBizCharge: Create Subscription Orders'),
    );
    $schedules['check_item_stock_daily'] = array(
        'interval' => 1800,
        'display' => esc_html__('EBizCharge: Notify Low Stock Items'),
    );
    return $schedules;
}

//Schedule an action daily if it's not already scheduled
if (!wp_next_scheduled('ebizcharge_run_low_item_stock')) {
    wp_schedule_event(strtotime('09:00:00'), 'daily', 'ebizcharge_run_low_item_stock');
}

if (!wp_next_scheduled('ebizcharge_create_subscription_orders')) {
    wp_schedule_event(strtotime('11:00:00'), 'daily', 'ebizcharge_create_subscription_orders');
}

///Hook into that action that'll fire daily
add_action('ebizcharge_run_low_item_stock', 'ebizcharge_run_low_item_stock_cron_function');
//create your function, that runs on cron
function ebizcharge_run_low_item_stock_cron_function()
{
    global $WC_Gateway_EBiz_Rec;
    $WC_Gateway_EBiz_Rec->log(__METHOD__ . ", Low stock cron run at: " . date('Y-m-d h:i:sa'));
    $WC_Gateway_EBiz_Rec->checkRecurringItemsStock();
}

///Hook into that action that'll fire every six hours
add_action('ebizcharge_create_subscription_orders', 'ebizcharge_create_subscription_orders_cron_function');
//create your function, that runs on cron
function ebizcharge_create_subscription_orders_cron_function()
{
    global $WC_Gateway_EBiz_Rec, $Recurring_Order;
    $startDate = date('Y-m-d');
    $endDate = date('Y-m-d');
    $WC_Gateway_EBiz_Rec->log(__METHOD__ . ", Create orders cron run at: " . date('Y-m-d h:i:sa'));
    $Recurring_Order->checkRecurringOrders($startDate, $endDate, true);
}
// Cron functions end