<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

/**
 * Handle admin orders interface + status transitions
 */
class WC_Gateway_EBizCharge_Admin
{
    const PAYMENT_STATUS_AUTHORIZED = 'Authorized';
    const PAYMENT_STATUS_CAPTURED = 'Captured';
    const PAYMENT_STATUS_PARTIALLY_CAPTURED = 'Partially_Captured';
    const PAYMENT_STATUS_REFUNDED = 'Refunded';
    const PAYMENT_STATUS_PARTIALLY_REFUNDED = 'Partially_Refunded';
    const PAYMENT_STATUS_VOIDED = 'Voided';

    /**
     * Constructor
     */
    public function __construct()
    {
        global $post, $wpdb;

        add_action('add_meta_boxes', array($this, 'meta_box'));
        add_action('wp_ajax_ebiz_order_action', array($this, 'order_actions'));
        add_action('woocommerce_order_status_completed', [$this, 'capture_on_completed'], 10, 1);
        add_action('woocommerce_order_status_cancelled', [$this, 'void_on_cancelled'], 10, 1);

        add_action('bulk_actions-edit-shop_order', [$this, 'ebizcharge_update_custom_dropdown_bulk_actions_shop_order']);
        add_filter('bulk_actions-woocommerce_page_wc-orders', [$this, 'ebizcharge_update_custom_dropdown_bulk_actions_shop_order'], 20, 1);

        add_filter('handle_bulk_actions-edit-shop_order', [$this, 'ebizcharge_handle_custom_bulk_actions_shop_order'], 20, 3);
        add_filter('handle_bulk_actions-woocommerce_page_wc-orders', [$this, 'ebizcharge_handle_custom_bulk_actions_shop_order'], 20, 3);

        add_action('admin_notices', [&$this, 'capture_bulk_admin_notices']);
        // for admin css

        $subscriptionPages = ['view-subscription', 'future-subscription', 'payment-history', 'subscription-orders',
            'add-subscription', 'edit-subscription', 'ebizcharge-help-contact-us'];

        if (isset($_GET['page']) && in_array($_GET['page'], $subscriptionPages)) {
            add_action('admin_enqueue_scripts', array(&$this, 'add_subscription_css'));
        }

        if (!class_exists('Backwards_Compatible_Order')) {
            include_once 'class-wc-gateway-ebizcharge-migration-helper.php';
        }
        //Fires immediately after user is created.
        add_action('edit_user_created_user', array($this, 'sync_customer'));
        // Fires immediately after an existing user is updated. - called from checkout as well(issue)
        add_action('profile_update', array($this, 'sync_customer'));
        // Fires immediately after post is created.
        //add_action('wp_insert_post', array($this, 'sync_order'));
        //Fires before the page loads on the ‘Edit User’ screen.
        // add_action('edit_user_profile_update',  array($this, 'sync_customer'));
    }

    /**
     * Include admin side CSS
     */
    public function add_ebizcharge_css()
    {
        wp_enqueue_style('admin-styles', PLUGIN_DIR . 'assets/css/ebizcharge.css', '', 1.0);
    }

    public function add_subscription_css()
    {
        wp_enqueue_script('jquery-ui-datepicker');
        // Enqueue some theme-roller or default style...
        wp_enqueue_style('jquery-ui-css', PLUGIN_DIR . 'assets/css/jquery-ui.css');
        wp_enqueue_style('admin-styles', PLUGIN_DIR . 'assets/css/subscription.css', '', 2.0);
        wp_enqueue_script('subscription', PLUGIN_DIR . 'assets/js/subscription.js', array('jquery'), 3.0);

        wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css');
        wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js');
        wp_enqueue_script('form-validation-js', PLUGIN_DIR . 'assets/js/jquery.validate.min.js', array('jquery'), 1.0);

        wp_enqueue_script('jquery-ui-core');// enqueue jQuery UI Core
        wp_enqueue_script('jquery-ui-tabs');// enqueue jQuery UI Tabs

        /* for confirm box css */
        wp_enqueue_style('confirm_css', PLUGIN_DIR . 'assets/css/jquery-confirm.min.css');
        /* for confirm box js */
        wp_enqueue_script('confirm_js', PLUGIN_DIR . 'assets/js/jquery-confirm.min.js', array('jquery'), 3.3);
    }

    public function ebizcharge_update_custom_dropdown_bulk_actions_shop_order($actions)
    {
        $actions['capture_payment'] = __('Capture Payment', 'woocommerce');
        $actions['void_payment'] = __('Void Payment', 'woocommerce');

        return $actions;
    }

    /**
     * Process bulk capture.
     */
    public function ebizcharge_handle_custom_bulk_actions_shop_order($redirect_to, $doaction, $order_ids)
    {
        $allowed_actions = array('capture_payment', 'void_payment', 'mark_completed', 'mark_cancelled');
        // capture on complete status when enabled in settings
        if ($GLOBALS['completecapture'] == 'no' && $doaction === 'mark_completed') {
            return $redirect_to;
        }

        // void on cancel status when enabled in settings
        if ($GLOBALS['voidOnCancelled'] == 'no' && $doaction === 'mark_cancelled') {
            return $redirect_to;
        }

        if (in_array($doaction, $allowed_actions) && !empty($order_ids)) {

            foreach ($order_ids as $order_id) {

                if (in_array($doaction, ['capture_payment', 'mark_completed'])) {
                    $this->perform_capture_payment($order_id);

                } else {
                    $this->void_on_cancelled($order_id);
                }
            }
        }
        return $redirect_to;
    }

    /**
     * Display notice after capturing payments.
     */
    public function capture_bulk_admin_notices()
    {
        global $post_type, $pagenow;

        $screen = wc_get_container()->get(CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled()
            ? wc_get_page_screen_id('shop-order')
            : 'shop_order';

        if ($pagenow == 'edit.php' && $post_type == 'shop_order' && isset($_REQUEST['captured']) && ( int )$_REQUEST['captured']) {

            if (( int )$_REQUEST['captured'] == 1) {
                $message = sprintf('Capture successful. %s payment captured.', number_format_i18n($_REQUEST['captured']));
            } else {
                $message = sprintf('Capture successful. %s payments captured.', number_format_i18n($_REQUEST['captured']));
            }

            echo "<div class=\"updated\"><p>{$message}</p></div>";
        } elseif ($pagenow == 'admin.php' && ($screen == 'woocommerce_page_wc-orders' || $screen == 'shop_order') && isset($_REQUEST['captured']) && ( int )$_REQUEST['captured']) {
            if (( int )$_REQUEST['captured'] == 1) {
                $message = sprintf('Capture successful. %s payment captured.', number_format_i18n($_REQUEST['captured']));
            } else {
                $message = sprintf('Capture successful. %s payments captured.', number_format_i18n($_REQUEST['captured']));
            }

            echo "<div class=\"updated\"><p>{$message}</p></div>";
        }

    }

    public function perform_capture_payment($order, $checkCompleted = false): bool
    {
        $ebiz = new WC_ebizcharge();
        $tran = $ebiz->_initTransaction();

        $order = $order instanceof WC_Order ? $order : $tran->getOrderByVersion($order);

        if (empty($order)) {
            return false;
        }
        // capture on complete only when setting is enabled.
        if ($checkCompleted && $GLOBALS['completecapture'] == 'no') {
            return false;
        }

        $tran->ebiz_log(__METHOD__ . ' Order Id: ' . $order->get_id());

        $transactionId = $order->get_transaction_id();
        $paymentStatus = $order->get_meta('_payment_status');
        $allowedStatuses = [static::PAYMENT_STATUS_AUTHORIZED, static::PAYMENT_STATUS_PARTIALLY_CAPTURED];

        if (!empty($transactionId) && in_array($paymentStatus, $allowedStatuses)) {

            $tran->refnum = $transactionId;
            $this->captureOrder($tran, $order);

            /*if (!$this->captureTransaction($order, $transactionId, $order->get_total())) {
                return false;
            }*/
        } else {
            $order->add_order_note(__('EBizCharge payment not captured because payment status is ' . $paymentStatus, 'woocommerce'));
        }

        return true;
    }

    /**
     * Captures the payment when the order status is changed to 'Completed'.
     */
    public function capture_on_completed($order): bool
    {
        return $this->perform_capture_payment($order, true);
    }

    public function void_on_cancelled($order): bool
    {
        $ebiz = new WC_ebizcharge();
        $tran = $ebiz->_initTransaction();

        $order = $order instanceof WC_Order ? $order : $tran->getOrderByVersion($order);

        if (empty($order) || $GLOBALS['voidOnCancelled'] == 'no') {
            return false;
        }

        $tran->ebiz_log(__METHOD__ . ' Order Id: ' . $order->get_id());

        $transactionId = $order->get_transaction_id();
        $paymentStatus = $order->get_meta('_payment_status');

        if (!empty($transactionId) && $paymentStatus === static::PAYMENT_STATUS_AUTHORIZED) {
            $tran->command = 'creditvoid';
            $tran->refnum = $transactionId;

            if ($tran->executeTransaction()) {
                $order->add_order_note(__('EBizCharge payment Voided online. Transaction ID: ' . $transactionId, 'woocommerce'));
                $order->update_meta_data('_payment_status', static::PAYMENT_STATUS_VOIDED);
                $order->save();
                return true;

            } else {
                $order->add_order_note(__('EBizCharge payment Void failed. Transaction ID: ' . $transactionId . ' Error: ' . $tran->error, 'woocommerce'));
                return false;
            }

        } else {
            $order->add_order_note(__('EBizCharge payment not voided because payment status is ' . $paymentStatus, 'woocommerce'));
            return false;
        }
    }

    public function captureTransaction($order, $transactionId, $amount): bool
    {
        $ebiz = new WC_ebizcharge();
        $tran = $ebiz->_initTransaction();
        $tran->ebiz_log(__METHOD__);

        $tran->refnum = $transactionId;
        $tran->amount = $amount;
        $tran->command = 'capture';
        $orderId = $order->get_id();

        if ($tran->executeTransaction()) {

            $order->add_order_note(__('EBizCharge payment captured online. Transaction ID: ' . $transactionId, 'woocommerce'));
            $order->update_meta_data('_payment_status', static::PAYMENT_STATUS_CAPTURED);
            $order->update_meta_data('_order_captured_amount', $amount);
            $order->update_meta_data('_order_remaining_amount', 0);
            $order->set_transaction_id($tran->refnum);
            $order->save();
            // if surcharge is enabled, add surcharge details in notes
            if (!empty($order->get_meta('_ebiz_surcharge_enabled'))) {

                $surchargeAmount = $order->get_meta('_ebiz_surcharge_amount');
                $surchargePercentage = $order->get_meta('_ebiz_surcharge_percentage');
                $totalAmount = wc_price((float)$amount + (float)$surchargeAmount);

                $tran->addSurchargeInNotes($order, $amount, $totalAmount, $surchargePercentage, $surchargeAmount);
            }

            // sync order and invoice to Econnect
            $this->sync_order($orderId);
            return true;
        } else {
            $order->add_order_note(__('EBizCharge payment capture failed. Transaction ID: ' . $transactionId . ' Error: ' . $tran->error, 'woocommerce'));
            return false;
        }
    }

    private function captureOrder($tran, $order)
    {
        // we should also check the authorized amount as well
        // authorized amount could be different if admin edit the product details
        $orderAuthorizedAmount = $order->get_meta('_payment_amount');
        $orderTotalAmount = $order->get_total();
        $orderCapturedAmount = 0;
        if (!empty($amount = $order->get_meta('_order_captured_amount'))) {
            $orderCapturedAmount = $amount;
        }
        $orderRemainingAmount = $orderTotalAmount - $orderCapturedAmount;
        $amountToCapture = $_POST['ebiz_partial_capture_amount'] ?? $orderRemainingAmount;

        $transaction_id = $order->get_transaction_id();

        if ($amountToCapture <= 0 || $amountToCapture > $orderRemainingAmount) {
            $order->add_order_note(__('EBizCharge payment capture failed. Entered amount: ' . $amountToCapture . ' Remaining amount: ' . $orderRemainingAmount));
            return;
            //break;
        }

        $shouldPartialCapture = $amountToCapture != $orderTotalAmount || $orderAuthorizedAmount != $orderTotalAmount;

        if (isset($amountToCapture) && $shouldPartialCapture) {
            // $tran->payment_status === static::PAYMENT_STATUS_AUTHORIZED ? 'capture' :
            $tran->command = 'QuickSale';
            // WOO-657: update line items in partial capture
            foreach ($order->get_items() as $item) {
                $itemData = $item->get_data();
                $product = $item->get_product();
                $tran->addLineItem($product->get_sku(), $itemData['name'], $itemData['name'],
                    $product->get_price(), $itemData['quantity'], $itemData['total_tax']);
            }

            if ($tran->partialCaptureTransaction($amountToCapture)) {

                $paymentStatus = static::PAYMENT_STATUS_PARTIALLY_CAPTURED;
                if ($amountToCapture == $orderRemainingAmount) {
                    $paymentStatus = static::PAYMENT_STATUS_CAPTURED;
                }

                $order->add_order_note(__('EBizCharge payment partial captured online. Amount: ' . $amountToCapture .
                    '. Transaction ID: ' . $tran->refnum, 'woocommerce'));
                $order->set_transaction_id($tran->refnum);
                $order->update_meta_data('_payment_status', $paymentStatus);
                $order->update_meta_data('_order_captured_amount', ($orderCapturedAmount + $amountToCapture));
                $order->update_meta_data('_order_remaining_amount', ($orderTotalAmount - (($orderCapturedAmount + $amountToCapture))));

                $surchargeAmount = $order->get_meta('_ebiz_surcharge_amount');
                // Partial case scenario: if surcharge amount is exist, it means, we need to calculate and apply surcharge
                if (!empty($order->get_meta('_ebiz_surcharge_enabled')) && !empty($surchargeAmount)) {

                    $surchargePercentage = $order->get_meta('_ebiz_surcharge_percentage');
                    if ($surchargeAmount === '0.00') {
                        $totalAmount = wc_price((float)$amountToCapture);
                    } else {
                        $surchargeAmount = number_format(($surchargePercentage / 100) * $amountToCapture, 2);
                        $totalAmount = wc_price((float)$amountToCapture + (float)$surchargeAmount);
                    }

                    $tran->addSurchargeInNotes($order, $amountToCapture, $totalAmount, $surchargePercentage, $surchargeAmount);
                }

                if ($order->save()) {
                    // sync order and invoice to Econnect
                    $this->sync_order($order->get_id());
                }

            } else {
                $order->add_order_note(__('EBizCharge partial payment capture failed. Transaction ID: ' . $tran->refnum . ' Error: ' . $tran->error, 'woocommerce'));
            }
        } else {
            $tran->command = 'capture';

            if ($tran->executeTransaction()) {

                $order->add_order_note(__('EBizCharge payment captured online. Transaction ID: ' . $transaction_id, 'woocommerce'));
                $order->update_meta_data('_payment_status', static::PAYMENT_STATUS_CAPTURED);
                $order->set_transaction_id($tran->refnum);
                $order->update_meta_data('_order_captured_amount', $orderTotalAmount);
                $order->update_meta_data('_order_remaining_amount', 0);

                // if surcharge is enabled, add surcharge details in notes - Full capture case
                if (!empty($order->get_meta('_ebiz_surcharge_enabled'))) {

                    $surchargePercentage = $order->get_meta('_ebiz_surcharge_percentage');
                    // if order total is modified, recalculate the surcharge on new total
                    if ($orderAuthorizedAmount != $orderTotalAmount) {
                        $surchargeAmount = number_format(($surchargePercentage / 100) * $orderTotalAmount, 2);
                        $totalAmount = wc_price((float)$orderTotalAmount + (float)$surchargeAmount);

                    } else {
                        $surchargeAmount = $order->get_meta('_ebiz_surcharge_amount');
                        $totalAmount = wc_price((float)$orderAuthorizedAmount + (float)$surchargeAmount);
                    }

                    $tran->addSurchargeInNotes($order, $orderAuthorizedAmount, $totalAmount, $surchargePercentage, $surchargeAmount);
                }

                if ($order->save()) {
                    // sync order and invoice to Econnect
                    $this->sync_order($order->get_id());
                }
            } else {
                $order->add_order_note(__('EBizCharge payment capture failed. Transaction ID: ' . $transaction_id . ' Error: ' . $tran->error, 'woocommerce'));
            }
        }
    }

    /**
     * Perform order actions for ebizcharge
     */
    public function order_actions()
    {
        check_ajax_referer('ebiz_order_action', 'security');
        $order_id = absint($_POST['order_id']);
        $transaction_id = isset($_POST['ebiz_id']) ? wc_clean($_POST['ebiz_id']) : '';
        $action = sanitize_title($_POST['ebiz_action']);

        $ebiz = new WC_ebizcharge();
        $tran = $ebiz->_initTransaction();
        $tran->refnum = $transaction_id;

        $order = $tran->getOrderByVersion($order_id);
        $orderId = $order->get_id();

        $ebizPaymentStatus = $order->get_meta('_payment_status');
        $tran->payment_status = $ebizPaymentStatus;

        switch ($action) {
            case 'capture':
                $this->captureOrder($tran, $order);
                break;

            case 'void':
                $tran->command = 'creditvoid';
                if ($tran->executeTransaction()) {
                    $order->add_order_note(__('EBizCharge payment Voided online. Transaction ID: ' . $transaction_id, 'woocommerce'));
                    $order->update_meta_data('_payment_status', static::PAYMENT_STATUS_VOIDED);

                } else {
                    $order->add_order_note(__('EBizCharge payment Void failed. Transaction ID: ' . $transaction_id . ' Error: ' . $tran->error, 'woocommerce'));
                }
                break;

            case 'refund':
                $tran->command = 'creditvoid';
                $tran->amount = $_POST['ebiz_refund_amount'];
                $orderNote = $_POST['ebiz_refund_note'];

                if ($ebizPaymentStatus == static::PAYMENT_STATUS_AUTHORIZED) {
                    $order->add_order_note(__('EBizCharge payment not Captured yet for Transaction ID: ' . $transaction_id, 'woocommerce'));
                    die();
                }

                if ($ebizPaymentStatus == static::PAYMENT_STATUS_VOIDED) {
                    $order->add_order_note(__('EBizCharge payment already voided for Transaction ID: ' . $transaction_id, 'woocommerce'));
                    die();
                }

                if ($tran->executeTransaction()) {
                    $transaction_id = isset($tran->refnum) ? $tran->refnum : $transaction_id;
                    $order->update_meta_data('_payment_status', static::PAYMENT_STATUS_REFUNDED);

                    $order->add_order_note(__('EBizCharge payment Refunded online amount of ' . $tran->amount . '. Transaction ID: ' . $transaction_id . ' Note: ' . $orderNote, 'woocommerce'));
                    // Update order refund amount
                    $order->update_status('wc-refunded');
                    //update_post_meta( $orderId, '_refund_amount', $tran->amount );
                } else {
                    $order->add_order_note(__('EBizCharge payment Refund failed. Transaction ID: ' . $transaction_id . ' Error: ' . $tran->error, 'woocommerce'));
                }
                break;

            case 'payment':
                $result = $this->process_and_save_payment($_POST);
                break;
        }

        $order->save();

        die();
    }

    public function process_and_save_payment($getPost)
    {
        global $WC_ebizcharge;
        $ebiz = new WC_ebizcharge();
        $tran = $ebiz->_initTransaction();

        $command = $getPost['ebiz_id'];
        $order_id = absint(isset($getPost['order_id']) ? wc_clean($getPost['order_id']) : '');

        $order = $tran->getOrderByVersion($order_id);
        $user = new WP_User($order->get_user_id());

        $tran->command = $command;
        $custId = $user->ec_customer_id ?? $user->ID;
        $orderId = $order->get_id();

        $tran->invoice = $orderId;
        $tran->orderid = $orderId;
        $tran->ponum = $order->get_order_number();
        $tran->ip = $_SERVER['REMOTE_ADDR'];
        $tran->custid = $custId;
        $tran->email = $order->billing_email;
        $tran->tax = $order->get_cart_tax();
        $tran->shipping = $order->get_shipping_total();

        // avs data
        $tran->street = $order->billing_address_1;
        $tran->zip = $order->billing_postcode;
        $tran->description = 'description';
        //items = $order->get_items();

        // line item data
        $order_items = $order->get_items(apply_filters('woocommerce_admin_order_item_types', array(
            'line_item',
            'fee'
        )));
        $totalAmount = 0;

        foreach ($order_items as $item_id => $cart_item) {

            $totalAmount = $cart_item['line_total'] + $totalAmount;

            $_product = (WC()->version < '2.7.0') ? $order->get_product_from_item($cart_item) : $cart_item->get_product();

            $productmeta = new WC_Product($cart_item['item_id']);

            $sku = $productmeta->get_sku();

            if (empty($sku)) {
                $sku = $_product->get_title();
            }

            $prod_description = (!empty($productmeta->get_short_description())) ? $productmeta->get_short_description() : $productmeta->get_description();

            if (empty($prod_description)) {
                $prod_description = $_product->get_title();
            }

            $row_price = wc_get_price_excluding_tax($_product, array('qty' => 1));
            $tran->addLine($sku, $_product->get_title(), $prod_description, $row_price, $cart_item['qty'], $cart_item['line_tax']);
            // for tokenization
            $tran->addLineItem($sku, $_product->get_title(), $prod_description, $row_price, $cart_item['qty'], $cart_item['line_tax']);
        }

        $tran->amount = $totalAmount;
        $CustNum = get_user_meta($order->get_user_id(), 'CustNum', true);
        $paymentMethodId = $getPost['ebiz_payment_method'] ?? 0;

        if (!empty($paymentMethodId) && $paymentMethodId > 0) {

            $paymentMethodDetails = $tran->getCustomerPaymentMethodProfile($CustNum, $paymentMethodId);
            $payMethod = $paymentMethodDetails->MethodType;

            if ($payMethod == 'check') {
                $tran->command = 'check';
            }

            $response = $tran->savedProcess($CustNum, $paymentMethodId);

            if ($tran->resultcode == 'A' && !empty($tran->refnum)) {

                if ($payMethod == 'cc') {
                    $payType = ($command == 'sale') ? static::PAYMENT_STATUS_CAPTURED : static::PAYMENT_STATUS_AUTHORIZED;
                } elseif ($payMethod == 'check') {
                    $payType = static::PAYMENT_STATUS_CAPTURED;
                }

                $tranMetaKey = "[EBIZCHARGE]|methodid|refnum|authcode|avsresultcode|cvv2resultcode|woocommerceorderid|paymethod|webcustomerid";
                $tranMetaValue = "[EBIZCHARGE]" . "|" . $paymentMethodId . "|" . $tran->refnum . "|" . $tran->authcode . "|" .
                    $tran->avs_result_code . "|" . $tran->cvv2_result_code . "|" . $orderId . "|" . $payMethod . "|" . $tran->custid;
                // Success
                $order->set_transaction_id($tran->refnum);
                $order->update_meta_data('_payment_status', $payType);
                $order->update_meta_data($tranMetaKey, $tranMetaValue);
                $order->update_meta_data('_payment_amount', $tran->amount);
                $order->update_meta_data('_payment_method_id', $paymentMethodId);
                $order->update_meta_data('_ebiz_payment_type', $payMethod);
                $order->update_meta_data('_ebiz_custnum', $CustNum);

                if ($payMethod == 'cc') {
                    $cutomerName = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                    $cardExpiry = substr($paymentMethodDetails->CardExpiration, -2) . substr($paymentMethodDetails->CardExpiration, 2, 2);
                    $cardType = $tran->get_card_type($paymentMethodDetails->CardType);

                    $order->update_meta_data('_card_holder', $cutomerName);
                    $order->update_meta_data('_card_number', $paymentMethodDetails->CardNumber);
                    $order->update_meta_data('_card_expiry', $cardExpiry);
                    $order->update_meta_data('_card_type', $cardType);

                    $surchargeSettings = $tran->getSurchargeSettings();
                    $surchargePercentage = 0;
                    if ($isSurchargeEnabled = $surchargeSettings->IsSurchargeEnabled ?? false) {
                        $surchargePercentage = $surchargeSettings->SurchargePercentage;
                    }
                    $order->update_meta_data('_ebiz_surcharge_enabled', $isSurchargeEnabled);

                    if (!empty($isSurchargeEnabled)) {
                        $surcharge = $tran->calculateSurchargeAmount($tran->amount, $tran->zip, $user, $paymentMethodId);
                        $surchargeAmount = $surcharge->SurchargeAmount ?? 0;

                        $order->update_meta_data('_ebiz_surcharge_caption', $surchargeSettings->SurchargeTermsNote);
                        $order->update_meta_data('_ebiz_surcharge_percentage', $surchargePercentage);
                        $order->update_meta_data('_ebiz_surcharge_amount', $surchargeAmount);

                        // if amount is Captured(not Authorized), add surcharge info in the notes.
                        if ($payType === WC_Gateway_EBizCharge_Admin::PAYMENT_STATUS_CAPTURED) {
                            $totalAmount = wc_price((float)$tran->amount + (float)$surchargeAmount);
                            $tran->addSurchargeInNotes($order, $tran->amount, $totalAmount, $surchargePercentage, $surchargeAmount);
                        }
                    }

                }

                if ($payMethod == 'check') {
                    $accountType = $tran->get_card_type($paymentMethodDetails->AccountType);
                    $order->update_meta_data('_account_holder', $paymentMethodDetails->AccountHolderName);
                    $order->update_meta_data('_account_type', $accountType);
                    $order->update_meta_data('_account_number', $paymentMethodDetails->Account);
                    $order->update_meta_data('_routing_number', $paymentMethodDetails->Routing);
                }

	            $order->add_order_note(__('EBizCharge payment ' . $payType . '. Transaction ID: ', 'woocommerce') . $tran->refnum);
	            $order->update_status('wc-processing');
            } else {
	            $errormsg = $tran->error ?? $response['response'];
	            // No response or unexpected response
	            $order->add_order_note(__("EBizCharge payment failed. ", 'woocommerce') . $errormsg);
	            wc_add_notice(__('(Transaction Error) ' . $errormsg, 'ebizcharge'), 'error');
	            $order->update_status('wc-failed');
            }
        } else {
            $errormsg = ($tran->error) ? $tran->error : '';
            $order->add_order_note(__('Sorry, there was an error: ', 'woocommerce') . $errormsg);
        }

        $order->save();

        return true;
    }

    /**
     * meta_box function.
     *
     * @access public
     * @return void
     */
    public function meta_box() {
        global $post;

        if (!$screen = get_current_screen()) {
            return;
        }

        // Determine if HPOS is enabled
        $is_hpos = wc_get_container()
            ->get(\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class)
            ->custom_orders_table_usage_is_enabled();

        // Determine if we're on the order edit screen
        $is_order_screen = (
            (!$is_hpos && $screen->post_type === 'shop_order') || // Classic order screen
            ($is_hpos && $screen->id === 'woocommerce_page_wc-orders') // HPOS screen
        );

        if (!$is_order_screen) {
            return;
        }

        if ($is_hpos) {
            $orderId = isset($_GET['id']) ? absint(wc_clean($_GET['id'])) : 0;
        } else {
            $orderId = isset($_GET['post']) ? absint(wc_clean($_GET['post'])) : 0;
        }

        if (!$orderId) {
            return;
        }

        $order = wc_get_order($orderId);

        if (!$order || $order->get_payment_method() !== 'ebizcharge') {
            return;
        }
        // Determine screen ID for meta box registration
        $meta_box_screen = $is_hpos
            ? wc_get_page_screen_id('shop-order')
            : 'shop_order';

        add_meta_box(
            'woocommerce-ebizcharge-payments',
            __('EBizCharge Actions', 'woocommerce'),
            array($this, 'authorization_box'),
            $meta_box_screen,
            'side',
            'high'
        );
    }

    public function partial_capture_form($order_id)
    {
        $order = wc_get_order($order_id);
        $orderTotal = $order->get_total();

        if (empty($capturedAmount = $order->get_meta('_order_captured_amount'))) {
            $capturedAmount = 0;
        }
        $remainingAmount = $orderTotal - $capturedAmount;

        $amount = $capturedAmount >= $orderTotal ? 0 : $remainingAmount;

        ?>
        </br></br>
        <input type="hidden" class="ebiz_order_remaining_amount" value="<?php echo $amount; ?>"/>
        <input type="number" step="1" class="ebiz_partial_capture_amount full-width"
               data-amount="<?php echo $amount; ?>"
               value="<?php echo $amount; ?>" max="<?php echo $amount; ?>" id="ebiz_partial_capture_amount"/>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                var ebiz_order_remaining_amount = jQuery('.ebiz_order_remaining_amount').val();
                jQuery('.ebiz_partial_capture_amount').val(ebiz_order_remaining_amount)
            });
        </script>
        <?php
    }

    /**
     * pre_auth_box function.
     *
     * @access public
     * @return void
     */
    public function authorization_box($post_or_order_object)
    {
        global $post, $woocommerce;
        $actions = array();
        $ebiz = new WC_ebizcharge();
        $tran = $ebiz->_initTransaction();

        $order = ($post_or_order_object instanceof WP_Post) ? wc_get_order($post_or_order_object->ID) : $post_or_order_object;
        $orderId = $order->get_id();

        $transId = $order->get_transaction_id();
        $amount = $order->get_total();
        $paymentStatus = $order->get_meta('_payment_status');
        $payType = $order->get_meta('_ebiz_payment_type');

        if (!empty($transId) && !empty($paymentStatus)) {
            $tran->refnum = $transId;
            $tran->amount = $amount;
            $tran->payMethod = $payType;
            // show surcharge(if enabled) details for cc
            if ($payType != 'check' && !empty($order->get_meta('_ebiz_surcharge_enabled'))) {

                $surchargePercentage = $order->get_meta('_ebiz_surcharge_percentage') ?? 0;

                echo "<div class='error inline'>
                    <b>Surcharge is enabled. A surcharge fee ($surchargePercentage%) will be added to all eligible payments proceed with credit card.</b>
                </div>";
            }
            echo "<b>Current Status: </b>" . $paymentStatus;
            $this->partial_capture_form($orderId);

            if ($paymentStatus === static::PAYMENT_STATUS_AUTHORIZED && $payType != 'check') {
                $actions['void'] = array( //void transaction
                    'id' => $tran->refnum,
                    'button' => __('Void (' . $tran->amount . ')', 'woocommerce')
                );

                $actions['capture'] = array( //capture
                    'id' => $tran->refnum,
                    'button' => __('Capture', 'woocommerce')
                );

            } elseif (in_array($paymentStatus, [static::PAYMENT_STATUS_PARTIALLY_CAPTURED, static::PAYMENT_STATUS_PARTIALLY_REFUNDED]) && $payType != 'check') {

                $actions['capture'] = array( //capture
                    'id' => $tran->refnum,
                    'button' => __('Capture', 'woocommerce')
                );

            } elseif ($paymentStatus === static::PAYMENT_STATUS_CAPTURED) {
                // refund functionality handled in default woocommerce refund function
            }

        } else {
            $userid = $order->get_user_id();

            $CustNum = get_user_meta($userid, 'CustNum', true);
            $today = date('Y-m');
            $currentMonthYear = new DateTime($today);

            if (!empty($userid) && !empty($CustNum)) {

                $customerPaymentMethods = $tran->getCustomerPaymentMethods($userid);

                if (!empty($customerPaymentMethods)) {

                    $surchargeSettings = $tran->getSurchargeSettings();
                    if ($isSurchargeEnabled = $surchargeSettings->IsSurchargeEnabled ?? false) {
                        $surchargePercentage = $surchargeSettings->SurchargePercentage;

                        echo "<div class='error inline'>
                        <b>Surcharge is enabled. A surcharge fee ($surchargePercentage%) will be added to all eligible payments proceed with credit card.</b>
                        </div>";
                    }

                    foreach ($customerPaymentMethods as $index => $paymentMethod) {

                        if (is_object($paymentMethod)) {

                            if (($tran->is_cc_enabled()) && $paymentMethod->MethodType == 'cc') {

                                $cardExpiration = $paymentMethod->CardExpiration;
                                $expireMonthYear = new DateTime($cardExpiration);
                                if ($expireMonthYear >= $currentMonthYear) {
                                    ?>
                                    <p id="ebiz-payment">
                                        <input type="radio" name="ebiz-payment-method"
                                               id="<?php echo $paymentMethod->MethodID; ?>"
                                               value="<?php echo $paymentMethod->MethodID; ?>" <?php if ($index == 0) {
                                            echo 'checked';
                                        } ?>
                                        /> &nbsp;
                                        <?php
                                        $exp = $paymentMethod->CardExpiration;
                                        $cardNo = $paymentMethod->CardNumber;
                                        echo $cardNo . ' (' . substr($exp, 2, 2) . '/' . substr($exp, -2) . ')';
                                        ?>
                                    </p>
                                    <?php
                                }
                            }

                            if (($tran->is_ach_enabled()) && $paymentMethod->MethodType == 'check') {
                                ?>
                                <p id="ebiz-payment">
                                    <input type="radio" name="ebiz-payment-method"
                                           id="<?php echo $paymentMethod->MethodID; ?>"
                                           value="<?php echo $paymentMethod->MethodID; ?>" <?php if ($index == 0) {
                                        echo 'checked';
                                    } ?>
                                    /> &nbsp;
                                    <?php
                                    $acc = $paymentMethod->Account;
                                    $accType = $paymentMethod->AccountType;
                                    echo $acc . ' (' . ucfirst($accType) . ')';
                                    ?>
                                </p>
                                <?php
                            }
                        }
                    } ?>

                    <a id="admin_pay_by_cc" href="#" class="button" data-action="payment" style="display: none"
                       data-id="<?php echo $ebiz->salemethod; ?>">
                        <?php _e($ebiz->_paymentType[$ebiz->salemethod], 'woocommerce'); ?>
                    </a>
                    <a id="admin_pay_by_bank" href="#" class="button" data-action="payment" style="display: none"
                       data-id="<?php echo $ebiz->salemethod; ?>">
                        Pay via Bank Account
                    </a>

                    <?php

                } else {
                    echo "<b>Current Status: </b> No payment method is found for this user.";
                }
                ?>
                <?php
            } else {
                echo "<b>Current Status: </b> No payment method is found for this user.";
            }
        }

        if (!empty($actions)) {
            echo '<p class="buttons">';
            foreach ($actions as $action_name => $action) {
                echo '<a href="#" class="button" data-action="' . $action_name . '" data-id="' . $action['id'] . '">' . $action['button'] . '</a> ';
            }
            echo '</p>';
        }

        $js = "
			jQuery('#refund_amount').removeAttr('readonly');
			
			function updatePaymentButton() {
                var selected = jQuery('input[name=\"ebiz-payment-method\"]:checked');
                var labelText = selected.parent().text().toLowerCase();
            
                if (labelText.includes(\"checking\") || labelText.includes(\"saving\")) {
                  jQuery('#admin_pay_by_bank').show();
                  jQuery('#admin_pay_by_cc').hide();
                } else {
                  jQuery('#admin_pay_by_bank').hide();
                  jQuery('#admin_pay_by_cc').show();
                }
          }
        
          jQuery('input[name=\"ebiz-payment-method\"]').on('change', updatePaymentButton);
          jQuery(document).ready(updatePaymentButton);
  
			jQuery('#woocommerce-ebizcharge-payments').on( 'click', 'a.button, a.refresh', function(){
				jQuery('#woocommerce-ebizcharge-payments').block(
				{ message: null, overlayCSS: { background: '#fff url(" . $woocommerce->plugin_url() . "/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });
								
				var data = {
					action: 		'ebiz_order_action',
					security: 		'" . wp_create_nonce("ebiz_order_action") . "',
					order_id: 		'" . $orderId . "',
					ebiz_action: 	jQuery(this).data('action'),
					ebiz_id: 		jQuery(this).data('id'),
					ebiz_partial_capture_amount: jQuery('.ebiz_partial_capture_amount').val(),
					ebiz_refund_amount: jQuery('.ebiz_refund_amount').val(),
					ebiz_refund_note: jQuery('.ebiz_refund_note').val(),
					ebiz_payment_method: jQuery('input:radio[name=ebiz-payment-method]:checked').val(),
				};
				// Ajax action
				jQuery.ajax({
					url: '" . admin_url('admin-ajax.php') . "',
					data: data,
					type: 'POST',
					success: function( result ) {
						location.reload();
					}
				});

				return false;
			});

			jQuery('#woocommerce-ebizcharge-payments').on( 'click', 'a.toggle_refund', function(){
				jQuery('.refund_form').slideToggle();
				return false;
			});
		";

        if (function_exists('wc_enqueue_js')) {
            wc_enqueue_js($js);
        } else {
            $woocommerce->add_inline_js($js);
        }
    }

    public function sync_customer($user_id)
    {
        // only sync when customer is updated from admin
        if (is_admin()) {

            $user = new \WC_Customer($user_id);
            $ebiz = new WC_ebizcharge();
            $enableEconnect = isset($ebiz->enableEconnect) && $ebiz->enableEconnect == 'yes';

            if ($enableEconnect && $user->get_role() == 'customer') {
                $econnect = $ebiz->_initTransaction(true);
                $econnect->syncCustomer($user->get_id());
            }
        }
    }

    public function sync_order($order_id)
    {
        if (is_admin()) {
            $ebiz = new WC_ebizcharge();
            $tran = $ebiz->_initTransaction();
            $tran->ebiz_log(__METHOD__);

            if (!did_action('woocommerce_checkout_order_processed') && get_post_type($order_id) == 'shop_order') {
                $order = $tran->getOrderByVersion($order_id);
                if ($order) {
                    if ($tran->HPOS_Enabled()) {
                        $orderStatus = $order->get_status();
                    } else {
                        $orderStatus = $order->post_status;
                    }

                    $statusInArray = array('processing', 'completed');
                    if (in_array($orderStatus, $statusInArray)
                        && isset($ebiz->enableEconnect) && $ebiz->enableEconnect == 'yes'
                        && $order->get_item_count() > 0) {

                        $orderId = $order->get_id();
                        $econnect = $ebiz->_initTransaction(true);
                        $econnect->syncOrder($orderId);
                        $econnect->syncInvoice($orderId);
                    }
                }
            }
        }
    }
}

$GLOBALS['wc_ebizcharge_pa_order_handler'] = new WC_Gateway_EBizCharge_Admin();
