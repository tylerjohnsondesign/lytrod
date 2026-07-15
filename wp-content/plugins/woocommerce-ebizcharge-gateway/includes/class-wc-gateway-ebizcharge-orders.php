<?php
/**
 * EbizCharge Subscription Orders Class
 *
 */
ini_set('default_socket_timeout', 1000);

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Recurring_Order
{
    public $enableEconnect = false;
    public $orders;
    public $failedOrders;
    public $paymentInternalId;
    public bool $isCron = false;

    public function __construct()
    {
        global $post, $wpdb;
    }

    // Add Ebiz-Cron-Orders logs to Woocommerce logs
    public function cronlog($message, $level = 'info')
    {
        global $WC_Gateway_EBizCharge;
        $logFileName = 'Ebiz Cron Orders';
        try {

            if (!is_string($message)) {
                $message = print_r($message, true);
            }
            $WC_Gateway_EBizCharge->log_write($level, $message, $logFileName);
        } catch (Exception $e) {
            $WC_Gateway_EBizCharge->log_write('critical', $e, $logFileName); // critical/exception
        }
    }

    private function getRecurringOrders($startDate, $endDate)
    {
        global $wpdb;
        $recurringOrderTable = "{$wpdb->prefix}ebizcharge_recurring_order";
        $today = date('Y-m-d');
        $finalEndDate = $endDate ? $endDate : $today;

        $sql = "Select rec_id, order_date FROM " . $recurringOrderTable . "
                WHERE date(order_date) BETWEEN '" . $startDate . "' AND  '" . $finalEndDate . "'
                AND status=1 limit 1000";

        $orders = $wpdb->get_results($sql, 'ARRAY_A');
        $recurringOrders = [];

        foreach ($orders as $order) {
            $orderDate = date('Y-m-d', strtotime($order['order_date']));
            $recurringId = $order['rec_id'];
            $uniqueKey = $recurringId . '_' . $orderDate;
            $recurringOrders[$uniqueKey] = $recurringId;
        }
        return $recurringOrders;
    }

    /**
     * @param $startDate
     */
    public function getRecurringList($startDate, $endDate)
    {
        global $wpdb;
        $recurringTable = "{$wpdb->prefix}ebizcharge_recurring";
        $recurringDateTable = "{$wpdb->prefix}ebizcharge_recurring_dates";
        $today = date('Y-m-d');
        $finalEndDate = $endDate ? $endDate : $today;

        $sql = "Select * ,r.id as rec_id FROM " . $recurringTable . " r
                INNER JOIN  $recurringDateTable rd on rd.rec_id = r.id
                WHERE rd.rec_date BETWEEN '" . $startDate . "' AND  '" . $finalEndDate . "'
                AND rec_status = 0 ORDER BY rd.rec_date ASC";

        return $wpdb->get_results($sql, 'ARRAY_A');
    }

    /**
     * @param $recurring
     * @param $status
     */
    public function suspendScheduledRecurringPaymentStatus($scheduledPaymentInternalId, $status)
    {
        $Susbscriptions_Econnect = new Susbscriptions_Econnect();

        try {
            $scheduledRecurringStatusResult = $Susbscriptions_Econnect->suspendDeleteBulkSubscription(array($scheduledPaymentInternalId), $status);
            if (!empty($scheduledRecurringStatusResult)) {
                if ($scheduledRecurringStatusResult->StatusCode == 1) {
                    //return true;
                    $this->cronlog($scheduledPaymentInternalId . ' - ScheduledPaymentInternalId is suspended!');
                } else {
                    //return false;
                    $this->cronlog($scheduledPaymentInternalId . ' - ScheduledPaymentInternalId is not suspended!');
                }

            } else {
                $this->cronlog('No response from gateway!');
            }

        } catch (Exception $ex) {
            $this->cronlog('There is an error in updating ScheduledPaymentInternalId status', 'critical');
        }
    }

    /**
     * @param $customerInternalId
     * @param $scheduledPaymentInternalId
     * @param $orderDate
     * @return string|null
     */
    public function searchRecurringPayment($customerInternalId, $scheduledPaymentInternalId, $orderDate)
    {
        global $wpdb;
        $ebiz = new WC_ebizcharge();
        $tran = $ebiz->_initTransaction();
        $securityToken = $tran->_getUeSecurityToken();
        $client = new SoapClient($tran->_getWsdlUrl(), $tran->SoapParams());
        $this->cronlog(__METHOD__);

        try {
            // Get full schedule
            $parametersSearch = array(
                'securityToken' => $securityToken,
                'scheduledPaymentInternalId' => $scheduledPaymentInternalId,
                'customerInternalId' => $customerInternalId,
                'fromDateTime' => '2021-11-01',
                'toDateTime' => date('Y-m-d'),
                'start' => 0,
                'limit' => 1000
            );
            $searchRecurringPayments = $client->SearchRecurringPayments($parametersSearch);

            $recurringPaymentsResult = $searchRecurringPayments->SearchRecurringPaymentsResult;

            if (!empty($recurringPaymentsResult)) {

                if (isset($recurringPaymentsResult->Payment)) {

                    $payments = $recurringPaymentsResult->Payment;

                    if (is_object($payments)) {
                        $paymentData[] = $payments;
                    } else {
                        $paymentData = $payments;
                    }

                    foreach ($paymentData as $payment) {
                        if ($orderDate == date('Y-m-d', strtotime($payment->DatePaid))) {
                            $paymentInternalId = $payment->PaymentInternalId;
                            $this->cronlog('PaymentInternalId (' . $paymentInternalId . ') found against scheduledPaymentInternalId = ' . $scheduledPaymentInternalId);
                            return $paymentInternalId;
                        }
                    }

                } else {
                    $this->cronlog('No payment found against scheduledPaymentInternalId = ' . $scheduledPaymentInternalId);
                }

            } else {
                $this->cronlog('No payment found against scheduledPaymentInternalId = ' . $scheduledPaymentInternalId);
            }

            return null;
        } catch (Exception $ex) {
            $this->cronlog(__METHOD__ . $ex->getMessage(), 'critical');
        }
    }

    /**
     * if the Subscription fails on the Third time it is put on Suspension.
     * @param $recurring
     */
    private function addFailedAttempts($recurring)
    {
        global $wpdb;
        $failedAttempts = (int)$recurring['rec_failed_attempts'] + 1;
        $tableName = "{$wpdb->prefix}ebizcharge_recurring";

        $updateQueryParameters = array(
            'rec_failed_attempts' => $failedAttempts
        );
        $condition = array(
            'id' => $recurring['rec_id']
        );
        // Updating db recurring table end
        $wpdb->update($tableName, $updateQueryParameters, $condition, ['%d'], ['%d']);

        if ($failedAttempts > 4) {
            $this->cronlog('This is the 5th failed attempt of recurring record# ' . $recurring['rec_id'] . ' Suspending subscription.');
            $this->suspendScheduledRecurringPaymentStatus($recurring['rec_scheduled_payment_internal_id'], 1);
        }
    }

    /**
     * @param $recurring
     * @param $status
     * @param $message
     * @param $orderDate
     * @param null $orderId
     */
    protected function insertInRecurringOrder($recurring, $status, $message, $orderId = null)
    {
        global $wpdb;
        $tableName = "{$wpdb->prefix}ebizcharge_recurring_order";
        $orderDate = $recurring['rec_date'];
        $parameters = array(
            'rec_id' => $recurring['rec_id'],
            'rec_order_id' => $orderId,
            'created_date' => date('Y-m-d H:i:s'),
            'order_date' => date('Y-m-d', strtotime($orderDate)),
            'status' => $status,
            'message' => $message

        );
        $parametersFormat = array('%d', '%d', '%s', '%s', '%s', '%s');
        $wpdb->insert($tableName, $parameters, $parametersFormat);

        if ($status == 1) {
            $this->orders[] = $orderId;
        } else {
            $this->failedOrders[] = 0;

            if ($this->isCron) {
                $this->addFailedAttempts($recurring);
            }
        }
    }

    /**
     * @param $orderId
     * @return mixed
     */
    public function loadOrder($orderId)
    {
        $order = wc_get_order($orderId);
        if (!empty($order)) {
            return $order->get_data();
        }
        return null;
    }

    public function loadItem($itemId): array
    {
        $product = wc_get_product($itemId);
        // Get each Item remaining quantity in Stock
        $productInStock = $product->is_in_stock();
        $productManagingStock = $product->managing_stock();
        $productQuantity = $product->get_stock_quantity();
        $productType = get_the_terms($itemId, 'product_type')[0]->slug;

        if (empty($productQuantity)) {
            $productQuantity = 0;
        }

        $itemPrice = $product->get_price();
        $this->cronlog('Product ID: ' . $itemId . ', slug: ' . $productType . ', price ' . $itemPrice);

        return array(
            'itemId' => $product->get_id(),
            'itemPrice' => $itemPrice,
            'itemType' => $productType,
            'QtyOnHand' => (int)$productQuantity,
            'inStock' => (int)$productInStock,
            'managingStock' => (int)$productManagingStock
        );
    }

    /**
     * @param $paymentInternalId
     */
    public function markRecurringPaymentAsApplied($paymentInternalId): void
    {
        $this->cronlog(__METHOD__);
        $ebiz = new WC_ebizcharge();
        $tran = $ebiz->_initTransaction();
        $securityToken = $tran->_getUeSecurityToken();
        $client = new SoapClient($tran->_getWsdlUrl(), $tran->SoapParams());
        try {
            // Get full schedule
            $parametersPayment = array(
                'securityToken' => $securityToken,
                'paymentInternalId' => $paymentInternalId
            );

            $markRecurringPaymentAsApplied = $client->MarkRecurringPaymentAsApplied($parametersPayment);
            $markRecurringPaymentAsAppliedResult = $markRecurringPaymentAsApplied->MarkRecurringPaymentAsAppliedResult;

            if (!empty($markRecurringPaymentAsAppliedResult)) {
                if ($markRecurringPaymentAsAppliedResult->StatusCode == 1) {
                    $this->cronlog('Payment is marked as applied against PaymentInternalId = ' . $paymentInternalId);
                } else {
                    $this->cronlog('Payment is not marked as applied against PaymentInternalId = ' . $paymentInternalId);
                }
            } else {
                $this->cronlog('Payment is not marked as applied against PaymentInternalId = ' . $paymentInternalId);
            }

        } catch (Exception $ex) {
            $this->cronlog('There is an error in mark as applied process.', 'critical');
        }

    }

    public function createOrderRecurring($recurring, $orderData): void
    {
        $this->cronlog(__METHOD__);
        // Select 1st active shipping method
        $order = $this->createOrder($recurring, $orderData);

        if ($order['id']) {

            $this->cronlog("New recurring order #" . $order['id'] . " created in woocom.");
            // Updating db recurring table start
            $this->updateRecurringTable($recurring);
            // mark this recurring payment as applied
            if (!empty($this->paymentInternalId)) {
                $this->markRecurringPaymentAsApplied($this->paymentInternalId);
            }
            // add new order recurring order
            $this->insertInRecurringOrder($recurring, 1, 'Order added.', $order['id']);

            $this->cronlog('End: Order saved successfully.');
        } else {
            $this->insertInRecurringOrder($recurring, 0, 'Order not saved!');
            $this->cronlog("Order not saved!");
        }
    }

    // 1st step to check if subscription is due today
    public function checkRecurringOrders($startDate, $endDate, $isCron = false): void
    {
	    set_time_limit(600);

	    $this->cronlog(__METHOD__ . ' Start Date: ' . $startDate . ', End Date: ' . $endDate);
        $this->isCron = $isCron;

        $ebiz = new WC_ebizcharge();
        $this->orders = [];
        $this->failedOrders = [];

        $recurringItems = $this->getRecurringList($startDate, $endDate);
        $recurringOrders = $this->getRecurringOrders($startDate, $endDate);

        if (count($recurringItems) > 0) {

            foreach ($recurringItems as $recurring) {

                $recurringDate = $recurring['rec_date'];
                try {
                    if ($recurring['rec_remaining'] == 0) {
                        $this->cronlog('All recurring are completed for (' . $recurring['item_name'] . '). Status is marked as suspended on gateway.');
                        // Suspend subscription on Econnect //0 Active //1 Suspended //2 Expired //3 Canceled
                        $this->suspendScheduledRecurringPaymentStatus($recurring['rec_scheduled_payment_internal_id'], 1);
                        continue;
                    }
                    // if order is already created, skip it
                    $orderKey = $recurring['rec_id'] . '_' . $recurringDate;
                    if (array_key_exists($orderKey, $recurringOrders)) {
                        $this->cronlog('Order is already created for recurring ' . $orderKey);
                        continue;
                    }
                    $magCustomerId = $recurring['customer_id'];
                    $user = get_user_by('ID', $magCustomerId);

                    if (empty($user) || empty($user->ec_internal_id)) {
                        $this->cronlog('User not exist or not synced on gateway(ec_internal_id is empty)). user Id: ' . $magCustomerId);
                        continue;
                    }

                    $this->paymentInternalId = $this->searchRecurringPayment(
                        $user->ec_internal_id, $recurring['rec_scheduled_payment_internal_id'], $recurringDate
                    );

                    if (empty($this->paymentInternalId)) {
                        $msg = 'Payment failed.';
                        $this->cronlog($msg);
                        $this->insertInRecurringOrder($recurring, 0, $msg);
                        continue;
                    }

                    $customerData = $ebiz->getCustomerData($user->ID);

                    if (empty($customerData)) {
                        $msg = 'Error in loading customer ID(' . $magCustomerId . ')  against order (' . $recurring['order_id'] . ')';
                        $this->cronlog($msg);
                        $this->insertInRecurringOrder($recurring, 0, $msg);
                        continue;
                    }

                    $savedShippingMethod = $recurring['rec_shipping_method'];
                    // load existing order info
                    if (!empty($recurring['order_id'])) {
                        $savedOrderData = $this->loadOrder($recurring['order_id']);

                        if (empty($savedOrderData)) {
                            $msg = 'Error in loading parent saved Order (' . $recurring['order_id'] . ')';
                            $this->cronlog($msg);
                            $this->insertInRecurringOrder($recurring, 0, $msg);
                            continue;
                        }

                        if (empty($savedShippingMethod)) {
                            $savedShippingMethod = $savedOrderData['shipping_method'];
                        }

                    }

                    $savedBillingAddress = $recurring['billing_address'];
                    $savedShippingAddress = $recurring['shipping_address'];

                    if (empty($savedShippingAddress)) {
                        $msg = 'Shipping address is empty or invalid.';
                        $this->cronlog($msg);
                        $this->insertInRecurringOrder($recurring, 0, $msg);
                        continue;
                    }
                    $item = $this->loadItem($recurring['item_id']);

                    if (empty($item)) {
                        $msg = 'Product not found or has configurable type.';
                        $this->cronlog($msg);
                        $this->insertInRecurringOrder($recurring, 0, $msg);
                        continue;
                    }

                    if (!(int)$item['inStock']) {
                        $msg = "Product #" . $recurring['item_id'] . ' is out of stock.';
                        $this->insertInRecurringOrder($recurring, 0, $msg);
                        $this->cronlog($msg);
                        continue;
                    }

                    if ((int)$item['managingStock'] && (int)$item['QtyOnHand'] == 0) {
                        $msg = "Product #" . $recurring['item_id'] . ' is out of stock.';
                        $this->insertInRecurringOrder($recurring, 0, $msg);
                        $this->cronlog($msg);
                        continue;
                    }

                    if ((int)$item['managingStock'] && (int)$item['QtyOnHand'] < (int)$recurring['rec_item_qty']) {
                        $msg = "Product #" . $recurring['item_id'] . ' quantity not available.';
                        $this->insertInRecurringOrder($recurring, 0, $msg);
                        $this->cronlog($msg);
                        continue;
                    }

                    $shipmentMethod = $savedShippingMethod;

                    if (empty($shipmentMethod)) {
                        $this->cronlog('Shipping method is empty and order cannot be created.');
                        $this->insertInRecurringOrder($recurring, 0, 'Shipping method is empty.');
                        continue;
                    }

                    $excludeAmount = ($recurring['rec_item_qty'] * $item['itemPrice']);
                    $excludeAmountDb = $recurring['rec_amount'];

                    if (!empty($excludeAmountDb)) {
                        // using already subscribed amount not new one
                        if ($excludeAmount != $excludeAmountDb) {
                            $excludeAmount = $excludeAmountDb;
                        }
                    }

                    $orderData = [
                        'items' => [
                            'item' => [
                                'product_id' => $recurring['item_id'],
                                'qty' => $recurring['rec_item_qty'],
                                'price' => $item['itemPrice'],
                                'qtyOnHand' => $item['QtyOnHand'],
                                'itemType' => $item['itemType'],
                                'excludeAmount' => $excludeAmount
                            ]
                        ],
                        'excludeAmount' => $excludeAmount,
                        'custId' => $recurring['customer_id'],
                        'billing_address' => $savedBillingAddress,
                        'shipping_address' => $savedShippingAddress
                    ];

                    $dateToday = date("Y-m-d");
                    $recurringDate = date("Y-m-d", strtotime($recurring['rec_date']));

                    $this->cronlog('Today = ' . $dateToday . ', Recurring due date = ' . $recurringDate);

                    if (strtotime($recurringDate) <= strtotime($dateToday)) {

                        if (!(int)$item['managingStock'] || (int)$item['QtyOnHand'] >= (int)$recurring['rec_item_qty']) {
                            $this->createOrderRecurring($recurring, $orderData);
                        }

                    } else {
                        $this->cronlog('No recurring order is due today for recurring product #' . $recurring['item_id']);
                    }

                } catch (\Exception $ex) {
                    $msg = 'Exception: ' . $ex->getMessage();
                    $this->cronlog($msg, 'critical');
                    $this->insertInRecurringOrder($recurring, 0, $msg);
                }

            }
        } else {
            $this->cronlog('No new order found.');
        }
    }

    // 2nd step to create order in Woocommerce
    private function createOrder($recurring, $orderData)
    {
        global $woocommerce;
        $this->cronlog(__METHOD__);
        $ebiz = new WC_ebizcharge();
        $tran = $ebiz->_initTransaction();

        if (!empty($orderData) && !empty($recurring)) {
            $productId = $orderData['items']['item']['product_id'];
            $qty = $orderData['items']['item']['qty'];
            $customerId = $orderData['custId'];
            $coupon = $recurring['rec_coupon'] ?: '';
            $recMethodName = strtok($recurring['rec_pmethod_name'], " ");
            $command = 'sale';

            if (in_array($recMethodName, ['checking', 'savings'])) {
                $command = 'check';
            }

            $billAddress = @unserialize(stripslashes($orderData['billing_address']));
            $shipAddress = @unserialize(stripslashes($orderData['shipping_address']));

            // create order payment to gateway with exclude amount and lineitems start
            $order = wc_create_order();
            $order->set_customer_id($customerId);
            $order->add_product(wc_get_product($productId), $qty);
            $order->set_address($billAddress, 'billing');
            $order->set_address($shipAddress, 'shipping');

            //---------------------- coupons added -----------------
            $order->apply_coupon($coupon);
            $order->calculate_totals();

            //---------------------- add shipping ------------------
            $shippingMethod = $recurring['rec_shipping_method'];
            $shippingMethodTitle = ucfirst(str_replace("_", ' ', $shippingMethod));
            $shippingAmount = $recurring['rec_shipping_amount'];
            $shippingTax = $recurring['rec_shipping_tax'];

            if ($shippingMethod == 'free_shipping' || $shippingAmount == 0) {
                $shippingAmount = 0;
            }

            // Get a new instance of the WC_Order_Item_Shipping Object
            $shipping = new WC_Order_Item_Shipping();
            $shipping->set_method_title($shippingMethodTitle);
            $shipping->set_method_id($shippingMethod); // set an existing Shipping method rate ID
            $shipping->set_total($shippingAmount); // (optional)
            $shipping->calculate_taxes($shippingTax);
            $order->add_item($shipping);
            $order->calculate_totals();

            //---------------------- deduct extra payment ------------------
            $discountTotal = $order->get_discount_total();
            $totalAmount = $order->get_total();
            $alreadyPaid = $orderData['excludeAmount'];
            $finalPayable = ($totalAmount - $alreadyPaid);
            $lineItem = [];
            foreach ($order->get_items() as $item) {
                $product = wc_get_product($item->get_product_id());
                $lineItem['LineItem'] = array(
                    'SKU' => $product->get_sku(),
                    'ProductName' => $item->get_name(),
                    'Description' => $item->get_name(),
                    'UnitPrice' => $item->get_total() / $item->get_quantity(),
                    'Taxable' => ($item->get_total_tax() > 0) ? 1 : 0,
                    'TaxAmount' => $item->get_total_tax(),
                    'Qty' => $item->get_quantity(),
                );
            }

            $order->calculate_totals();
            $orderId = $order->get_id();

            $CustNum = get_user_meta($customerId, 'CustNum', true);
            $paymentMethodDetails = $tran->getCustomerPaymentMethodProfile($CustNum, $recurring['rec_pmethod_id']);
            $payMethod = $paymentMethodDetails->MethodType;

            if (empty($paymentMethodDetails)) {
                $this->cronlog('Associated PaymentMethodProfile Not Found!');
                return false;
            }

            if ($payMethod == 'check') {
                $command = 'check';
            }

            if ($finalPayable > 0) {
                $transactionArray = array(
                    'isRecurring' => 0,
                    'IgnoreDuplicate' => 1,
                    'Details' => array(
                        'NonTax' => 1,
                        'OrderID' => $orderId,
                        'Invoice' => $orderId,
                        'PONum' => $orderId,
                        'Description' => $tran->software . ' order created with ID #' . $orderId,
                        'Comments' => 'Amount ' . $alreadyPaid . ' already paid.',
                        'Amount' => $finalPayable,
                        'Tax' => $order->get_total_tax(),
                        'Currency' => '',
                        'Shipping' => $order->get_shipping_total(),
                        'ShipFromZip' => $order->get_shipping_postcode(),
                        'Discount' => $discountTotal,
                        'Subtotal' => 0,
                        'AllowPartialAuth' => 0,
                        'Duty' => 0,
                        'Tip' => 0,
                    ),
                    'Software' => $tran->software,
                    'MerchReceipt' => 1,
                    'CustReceiptName' => $tran->custreceipt_template,
                    'CustReceiptEmail' => '',
                    'CustReceipt' => $tran->custreceipt,
                    'ClientIP' => $tran->ip,
                    'Command' => $command,
                    'LineItems' => $lineItem
                );

                $transactionResult = $this->runCustomerTransactionExtraAmount($customerId, $recurring['rec_pmethod_id'], $transactionArray, $payMethod);

                if ($transactionResult) {
                    $order->set_transaction_id($transactionResult['transaction_id']);
                    $order->update_meta_data('_ebiz_custnum', $CustNum);//set custNum
                    $order->update_meta_data($transactionResult['tranMetaKey'], $transactionResult['tranMetaValue']);

                    $order->add_order_note(__("EBizCharge payment Captured. Transaction ID: " . $transactionResult['transaction_id']));
                }

            }
            // create order payment to gateway with exclude amount and lineitems end
            //---------------------- add payment data to db ------------------
            $order->update_meta_data('_payment_method', 'ebizcharge');
            $order->update_meta_data('_payment_method_title', 'EBizCharge');
            $order->update_meta_data('is_subscription_order', 'yes');
            $order->update_meta_data('_payment_status', 'Captured');
            $order->update_meta_data('_payment_method_id', $recurring['rec_pmethod_id']);
            $order->update_meta_data('_ebiz_payment_type', $payMethod);


            if ($payMethod == 'cc') {
                $cutomerName = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                $cardExpiry = substr($paymentMethodDetails->CardExpiration, -2) . substr($paymentMethodDetails->CardExpiration, 2, 2);
                $cardType = $tran->get_card_type($paymentMethodDetails->CardType);

                $order->update_meta_data('_card_holder', $cutomerName);
                $order->update_meta_data('_card_number', $paymentMethodDetails->CardNumber);
                $order->update_meta_data('_card_expiry', $cardExpiry);
                $order->update_meta_data('_card_type', $cardType);

            }

            if ($payMethod == 'check') {
                $accountType = $tran->get_card_type($paymentMethodDetails->AccountType);
                $order->update_meta_data('_account_holder', $paymentMethodDetails->AccountHolderName);
                $order->update_meta_data('_account_type', $accountType);
                $order->update_meta_data('_account_number', $paymentMethodDetails->Account);
                $order->update_meta_data('_routing_number', $paymentMethodDetails->Routing);
            }

            $order->add_order_note(__("EBizCharge gateway already captured: " . $alreadyPaid));
            $order->update_status("wc-processing", 'Imported Recurring ', true);
            return $order->get_data();
        }
        return null;
    }

    // 3rd step to check if need to deduct extra amount
    public function runCustomerTransactionExtraAmount($wpCustomerId, $paymentMethodId, $transactionArray, $payMethod)
    {
        $this->cronlog(__METHOD__);
        $ebiz = new WC_ebizcharge();
        $tran = $ebiz->_initTransaction();
        $client = new SoapClient($tran->_getWsdlUrl(), $tran->SoapParams());
        // get customer token from db
        $custNum = get_user_meta($wpCustomerId, 'CustNum', true);
        $webCustomerId = $tran->getWpCustomerMapedId($wpCustomerId);

        try {
            // Get full transaction array
            $transactionClient = array(
                'securityToken' => $tran->_getUeSecurityToken(),
                'custNum' => $custNum,
                'paymentMethodID' => $paymentMethodId,
                'tran' => $transactionArray,
            );

            $transactionResult = $client->runCustomerTransaction($transactionClient);
            $transaction = $transactionResult->runCustomerTransactionResult;

            if (isset($transaction) && $transaction->ResultCode == 'A') {
                $this->cronlog('Payment is deducted.');

                $tranMetaKey = "[EBIZCHARGE]|methodid|refnum|authcode|avsresultcode|cvv2resultcode|woocommerceorderid|paymethod|webcustomerid";
                $tranMetaValue = "[EBIZCHARGE]" . "|" . $paymentMethodId . "|" . $transaction->RefNum . "|" . $transaction->AuthCode . "|" . $transaction->AvsResultCode . "|" . $transaction->CardCodeResult . "|" . $transactionArray['Details']['OrderID'] . "|" . $payMethod . "|" . $webCustomerId;

                $successParams = [
                    'tranMetaKey' => $tranMetaKey,
                    'tranMetaValue' => $tranMetaValue,
                    'transaction_id' => $transaction->RefNum
                ];
                $tran->setTransactionResult($transaction);
                return $successParams;
            }

        } catch (Exception $ex) {
            $this->cronlog('There is an error in payment process. ' . $ex->getMessage(), 'critical');
        }
        return false;
    }

    public function updateRecurringTable($recurring)
    {
        global $wpdb;
        $recProcessedNew = ((int)$recurring['rec_processed'] + 1);
        $recRemainingNew = ((int)$recurring['rec_remaining'] - 1);
        $recNextDueDateArray = !empty($recurring['rec_due_dates']) ? unserialize($recurring['rec_due_dates']) : '';
        $recurringTable = "{$wpdb->prefix}ebizcharge_recurring";

        $updateQueryParameters = [
            'rec_processed' => $recProcessedNew,
            'rec_next_due' => $recNextDueDateArray[$recProcessedNew] ?? '',
            'rec_remaining' => $recRemainingNew
        ];
        $updateQueryParametersFormat = ['%d', '%s', '%d'];
        $condition = ['id' => $recurring['rec_id']];
        // Updating db recurring table end
        $wpdb->update($recurringTable, $updateQueryParameters, $condition, $updateQueryParametersFormat, ['%d']);

    }

    public function getExistingOrderAddress($orderId)
    {
        $order = new WC_Order($orderId);
        $orderBilling = $order->get_address('billing');
        $orderShipping = $order->get_address('shipping');
        return [
            'billingAddress' => $this->address($orderBilling),
            'shippingAddress' => $this->address($orderShipping)
        ];
    }

    private function address($data)
    {
        return [
            'firstname' => $data['first_name'],
            'lastname' => $data['last_name'],
            'company' => $data['company'],
            'address_1' => $data['address_1'],
            'address_2' => $data['address_2'],
            'city' => $data['city'],
            'postcode' => $data['postcode'],
            'phone' => $data['phone'],
            'country' => $data['country']
        ];
    }

    public function createOrderMessage()
    {
        $message = 'No new orders were found to be created.';
        $numberOfOrders = count($this->orders);
        $failedOrders = count($this->failedOrders);

        if ($numberOfOrders > 0) {
            $success = $numberOfOrders . ' previously failed order(s) have been successfully created.';
            $message = '';
        }
        if ($failedOrders > 0) {
            $error = $failedOrders . ' order(s) failed to create.';
            $message = '';
        }

        return array(
            'notice' => $message,
            'success' => $success,
            'error' => $error,
        );
    }
}

$GLOBALS['Recurring_Order'] = new Recurring_Order();