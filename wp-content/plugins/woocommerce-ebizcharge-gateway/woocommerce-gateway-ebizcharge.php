<?php
/*
Plugin Name: Woocommerce EBizCharge Gateway
Plugin URI: https://www.ebizcharge.com/woocommerce-payment-integration
Description: Accept all major credit cards and ACH directly on your WooCommerce site in a seamless and secure checkout environment with EBizCharge. Subscriptions functionality also included.
Version: 9.7.1
Author: EBizCharge
Author URI: https://ebizcharge.com
License: Apache 2.0
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_action('plugins_loaded', 'woocommerce_ebizcharge_commerce_init', 0);
register_activation_hook(__FILE__, 'econnect_install');
register_activation_hook(__FILE__, 'recurring_db_install');

$GLOBALS['action_counter'] = 0;
$GLOBALS['completecapture'] = 'no';
$GLOBALS['voidOnCancelled'] = 'no';

register_activation_hook(__FILE__, 'woocommerce_main_active');
function woocommerce_main_active()
{
    //deactivate_plugins('woocommerce/woocommerce.php');
    // Require "woocommerce" plugin
    if (!is_plugin_active('woocommerce/woocommerce.php') && current_user_can('activate_plugins')) {
        // Stop activation redirect and show error
        wp_die('Sorry, but this plugin requires the "Woocommerce" Plugin to be installed and active. <br><a href="' . admin_url('plugins.php') . '">&laquo; Return to Plugins</a>');
    }

}

add_action('admin_init', 'is_main_woocommerce_active');
function is_main_woocommerce_active()
{
    if (!is_plugin_active('woocommerce/woocommerce.php') && current_user_can('activate_plugins')) {
        deactivate_plugins('woocommerce-ebizcharge-gateway/woocommerce-gateway-ebizcharge.php');
    }
}

add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

add_filter('date_range_filter_query_column', 'my_date_range_filter_query_column', 10, 1);

// Add the filter in your theme's functions.php file or a custom plugin

// include surcharge in the total
add_filter('woocommerce_get_formatted_order_total', 'order_total_with_surcharge', 10, 2);
function order_total_with_surcharge($formatted_total, $order)
{
    if (!empty($order->get_meta('_ebiz_surcharge_enabled'))) {
        return wc_price((float)$order->get_total() + (float)$order->get_meta('_ebiz_surcharge_amount') ?? 0);
    }
    return $formatted_total;
}

add_filter('woocommerce_get_order_item_totals', 'custom_order_item_totals', 10, 3);
function custom_order_item_totals($totalRows, $order, $tax_display)
{
    // Add a custom row to order item totals
    if (!empty($order->get_meta('_ebiz_surcharge_enabled'))) {
        $surchargeRow = array(
            'surcharge' => array(
                'label' => __($order->get_meta('_ebiz_surcharge_caption') . " (" . $order->get_meta('_ebiz_surcharge_percentage') . "%)", 'woocommerce'),
                'value' => wc_price($order->get_meta('_ebiz_surcharge_amount')),
            ),
        );
        // Find the index of the 'total' row in the $total_rows array
        $totalIndex = array_search('order_total', array_keys($totalRows));
        // Insert the custom row before the 'total' row
        array_splice($totalRows, $totalIndex, 0, $surchargeRow);
    }

    return $totalRows;
}

add_action('wp_ajax_calculate_surcharge', 'calculate_surcharge');
add_action('wp_ajax_nopriv_calculate_surcharge', 'calculate_surcharge');
function calculate_surcharge()
{
    if (!empty($_POST['surcharge_enabled'])) {

        $cart = WC()->cart;
        $amount = $cart->get_total('');
        $zipCode = $_POST['zip'] ?? '';
        $creditCard = $_POST['cc'] ?? '';
        $methodId = $_POST['method_id'] ?? '';
        $orderId = $_POST['order_id'] ?? '';

        $ebiz = new WC_ebizcharge();
        $tran = $ebiz->_initTransaction();
        $user = wp_get_current_user();

        //woo-803: Get order billing zip_code
        if (!empty($orderId) && !empty($order = new WC_Order($orderId))) {
            
	        $zipCode = $order->get_billing_postcode();
	        $amount = $order->get_total('');
        }

        // if zip_code not found from order, check user billing zipcode
        if (!empty($user) && empty($zipCode)) {
            $zipCode = get_user_meta($user->ID, 'billing_postcode', true);
        }

        if ($result = $tran->calculateSurchargeAmount($amount, $zipCode, $user, $methodId, $creditCard)) {

            $surchargeAmount = number_format($result->SurchargeAmount, 2);
            $surchargePercentage = $result->SurchargePercentage;

            $data = '<span>
                    <p>Surcharge (' . $surchargePercentage . '%): &nbsp;&nbsp; ' . wc_price($surchargeAmount) . '</p>
                    <p>Total: &nbsp;&nbsp; ' . wc_price($amount + $surchargeAmount) . ' </p>
                </span>';

            wp_send_json(
                [
                    'success' => true,
                    'data' => $data,
                    'surcharge_amount' => $surchargeAmount,
                ]);
            exit;
        }
    }

    wp_send_json(array('success' => false));
    exit;
}

add_action('wp_ajax_send_email', 'send_email');
function send_email()
{
    if (isset($_POST['tid'])) {
        $ebiz = new WC_ebizcharge();
        $ebiz->sendInvoiceEmail($_POST['tid'], $_POST['rid'], $_POST['email']);
    }
    return 0;
}

add_action('wp_ajax_get_print', 'get_print');
function get_print()
{
    if (isset($_POST['tid'])) {
        $ebiz = new WC_ebizcharge();
        $ebiz->getInvoicePrint($_POST['tid'], $_POST['rid']);
    }
    return 0;
}

add_action('wp_ajax_get_customs_paymentmethods', 'get_customs_paymentmethods');
function get_customs_paymentmethods()
{
    $ebiz = new WC_ebizcharge();
    $tran = $ebiz->_initTransaction();
    $customerId = $_POST['customerId'] ?? '';
    $paymentType = $_POST['paymentType'] ?? '';
    $method = "";
    $errMessage = 'Card';

    $custNum = get_user_meta($customerId, 'CustNum', true);

    if (!empty($customerId) && !empty($custNum)) {
        try {
            $paymentMethods = $tran->getCustomerPaymentMethodsByGetCustomer($customerId);

            if ($paymentType == 'ACH') {
                $paymentMethods = $tran->groupCustomerPaymentMethodsByType($paymentMethods, 'check');
                $errMessage = 'Account';
            } else {
                $paymentMethods = $tran->groupCustomerPaymentMethodsByType($paymentMethods, 'cc');
            }

            if (count($paymentMethods) > 0) {
                $method .= "<option value='' selected>Please Select Payment Method</option>";
                foreach ($paymentMethods as $paymentMethod) {
                    if ($paymentMethod->MethodType == 'cc') {
                        $date = explode("-", $paymentMethod->CardExpiration);
                        $month = $date[1];
                        $year = substr($date[0], -2);
                        $newDate = ' (' . $month . '/' . $year . ')';
                        $expDate = $date[0] . $date[1];
                        $pMethodType = $tran->get_card_type($paymentMethod->CardType);
                        $pMethodName = $pMethodType . ' ending in ' . substr($paymentMethod->CardNumber, -4);

                        if ($expDate >= date('Ym')) {
                            $notice = '';
                            $style = '';
                            $pMethodId = $paymentMethod->MethodID;
                        } else {
                            $notice = ' - Expired';
                            $style = 'color: red;';
                            $pMethodId = '';
                        }
                    } else {
                        $pMethodName = ucfirst(trim($paymentMethod->AccountType)) . ' ending in ' . substr($paymentMethod->Account, -4);
                        $newDate = '';
                        $notice = '';
                        $style = '';
                        $pMethodId = $paymentMethod->MethodID;
                    }
                    $method .= "<option value='" . $pMethodId . "' style='" . $style . "'>" . $pMethodName . $newDate . $notice . "</option>";

                }
            } else {
                $method .= "<option value=''>No Saved " . $errMessage . "(s)</option>";
            }
        } catch (\Exception $ex) {
            $method .= "<option value=''>No Saved " . $errMessage . "(s)</option>";
        }
    } else {
        $method .= "<option value=''>No Saved " . $errMessage . "(s)</option>";
    }

    echo $method;
}

add_action('wp_ajax_get_customers_paymentmethodprofile', 'get_customers_paymentmethodprofile');
function get_customers_paymentmethodprofile()
{
    global $WC_Gateway_EBizCharge;
    $ebiz = new WC_ebizcharge();
    $tran = $ebiz->_initTransaction();
    $customerId = $_POST['customerId'] ?? '';
    $paymentMethodId = $_POST['paymentMethodId'] ?? '';
    $response = [];

    $custNum = get_user_meta($customerId, 'CustNum', true);

    if (!empty($paymentMethodId) && !empty($custNum)) {
        try {
            $paymentMethodProfileResult = $tran->getCustomerPaymentMethodProfile($custNum, $paymentMethodId);

            $response = [
                'success' => true,
                'avs_street' => $paymentMethodProfileResult->AvsStreet,
                'avs_zip' => $paymentMethodProfileResult->AvsZip
            ];
        } catch (\Exception $ex) {
            $response = [
                'success' => false
            ];
        }
    }

    wp_send_json($response);
}

add_action('wp_ajax_get_subscriptdate', 'get_subscriptdate');
function get_subscriptdate()
{
    global $wpdb, $WC_Gateway_EBiz_Rec;
    $recurringTable = "{$wpdb->prefix}ebizcharge_recurring";

    $customerId = $_POST['customerId'];
    $startDate = $_POST['start_date'];
    $productId = $_POST['product_id'];
    $recId = $_POST['rec_id'];
    $cond = '';
    if (!empty($recId)) {
        $cond = ' and id !=' . $recId;
    }

    $sql = "select id,rec_scheduled_payment_internal_id, customer_id from " . $recurringTable . " 
                    WHERE customer_id = '" . trim($customerId) . "' and item_id = '" . trim($productId) . "'  
                    and DATE(rec_end_date) >= '" . $startDate . "' $cond";

    $result = $wpdb->get_results($sql, 'ARRAY_A');

    if (count($result) > 0) {
        $response = 'E';
    } else {
        $response = 'P';
    }

    echo $response;
    die;
}

add_action('wp_ajax_get_shipping_amount', 'get_shipping_amount');
function get_shipping_amount()
{
    $WC_Gateway_EBiz_Rec = new WC_Gateway_EBizCharge_Recurring();
    if (!empty($_POST['shipping_method'])) {
        echo $WC_Gateway_EBiz_Rec->getShippingAmount($_POST['shipping_method']);
        wp_die();
    }
}


add_action('wp_ajax_validate_card', 'validate_card');
function validate_card() {

	$ebiz = new WC_ebizcharge();
	$tran = $ebiz->_initTransaction();

	$billing = @unserialize(stripslashes($_POST['billing_address']));
    $shipping = @unserialize(stripslashes($_POST['shipping_address']));
	$tran->card = sanitize_data('cc');
	$tran->cardholder = sanitize_data('cc_owner');
	$tran->exp = sanitize_data('expiry');
    $tran->cvv2 = sanitize_data('cvv');
    $tran->zip = sanitize_data('avs_zip');
	//$tran->invoice = 'validate_card: preauth transaction';
	//$tran->orderid = 'validate_card: preauth transaction';
	//$tran->ponum = 'validate_card: preauth transaction';
	$tran->ip = $_SERVER['REMOTE_ADDR'];
	$tran->custid = sanitize_data('customer_id');
	$tran->tax = 0.0;
	$tran->shipping = 0.0;
    $tran->lineItems = [];
    $tran->amount = 0.05;
    $tran->command = 'authonly';
    $tran->description = 'Woocommerce validate_card: preauth transaction';

	// billing info
	if (!empty($billing)) {
		$tran->billfname = $billing['first_name'] ?? '';
		$tran->billlname = $billing['last_name'] ?? '';
        $tran->email = $billing['email'] ?? '';
		$tran->billcompany = $billing['company'] ?? '';
		$tran->billstreet = $tran->street =  $billing['address1'] ?? '';
		$tran->billstreet2 = $billing['address2'] ?? '';
		$tran->billcity = $billing['city'] ?? '';
		$tran->billstate = $billing['state'] ?? '';
		$tran->billzip = $tran->zip = $billing['postcode'] ?? '';
		$tran->billcountry = $billing['country'] ?? '';
		$tran->billphone = $billing['phone'] ?? '';
	}
    
	// shipping info
	if (!empty($shipping)) {
		$tran->shipfname = $shipping['first_name'] ?? '';
		$tran->shiplname = $shipping['last_name'] ?? '';
		$tran->shipcompany = $shipping['company'] ?? '';
		$tran->shipstreet = $shipping['address1'] ?? '';
		$tran->shipstreet2 = $shipping['address2'] ?? '';
		$tran->shipcity = $shipping['city'] ?? '';
		$tran->shipstate = $shipping['state'] ?? '';
		$tran->shipzip = $shipping['postcode'] ?? '';
		$tran->shipcountry = $shipping['country'] ?? '';
	}

	$success = $tran->RunTransaction();

	$avs_result_code = $tran->avs_result_code ?? '';
    $avs_result = $tran->avs_result;
	$avs_status = 'not_matched';

	$cvv2_result = $tran->cvv2_result;
    $cvv2_result_code = $tran->cvv2_result_code;
	$message = '';

	if($success) {
        // normalize to first 3 chars, uppercase
        $avs_code = strtoupper(substr(trim($avs_result_code), 0, 3));

        if (in_array($avs_code, ['YYY', 'GGG']) && $cvv2_result_code === 'M') {
            $avs_status = 'matched';

        } else {
            $avs_status = 'partially_matched';
            $message = $ebiz->getAvsAndCvvMessages($avs_result_code, $cvv2_result);
        }

        $tran->command = 'creditvoid';
        $tran->executeTransaction();
    }

    wp_send_json([
        'success' => $success,
        'data'    => [
            'avs_status' => $avs_status,
            'avs_result' => $avs_result,
            'cvv2_result' => $cvv2_result,
            'cvv2_result_code' => $cvv2_result_code,
            'error' => $tran->error,
            'message' => $message,
        ],
    ]);
    exit;
}

function sanitize_data($key, $default = null) {
	if (isset($_POST[$key])) {
		return sanitize_text_field($_POST[$key]);
	}
	return $default;
}

add_action('wp_ajax_get_billaddress', 'get_billaddress');
function get_billaddress()
{
    if (!empty($_POST['customerId']) && $_POST['customerId'] > 0) {
        $customer = new WC_Customer($_POST['customerId']);
        $billingData = $customer->get_billing() ?? '';
        $billingData = serialize($billingData);
        $billingFirstName = $customer->get_billing_first_name();
        $billingAddress1 = $customer->get_billing_address_1();
        $billingAddress2 = $customer->get_billing_address_2();
        $billingCity = $customer->get_billing_city();
        $billingState = $customer->get_billing_state();
        $billingPostcode = $customer->get_billing_postcode();

        if (!empty($billingFirstName)) {
            $billingAddress = implode(",", array_filter([$billingAddress1, $billingAddress2, $billingCity, $billingState, $billingPostcode]));
            echo "<select name='addressBill' id='addressBill' style='width: 600px;'>
			 <option value='" . $billingData . "'>$billingAddress</option>
			</select>";
        }
        wp_die();
    }
}

add_action('wp_ajax_get_shipaddress', 'get_shipaddress');
function get_shipaddress()
{
    if (!empty($_POST['customerId']) && $_POST['customerId'] > 0) {
        $customer = new WC_Customer($_POST['customerId']);
        $shippingData = $customer->get_shipping() ?? '';
        $shippingData = serialize($shippingData);
        $shippingFirstName = $customer->get_shipping_first_name();
        $shippingAddress1 = $customer->get_shipping_address_1();
        $shippingAddress2 = $customer->get_shipping_address_2();
        $shippingCity = $customer->get_shipping_city();
        $shippingState = $customer->get_shipping_state();
        $shippingPostcode = $customer->get_shipping_postcode();
        if (!empty($shippingFirstName)) {
            $shippingAddsress = implode(",", array_filter([$shippingAddress1, $shippingAddress2, $shippingCity, $shippingState, $shippingPostcode]));
            echo "<select name='addressShip' id='addressShip' style='width: 600px;'>
			 <option value='" . $shippingData . "'>$shippingAddsress</option>
			</select>";
        }
        wp_die();
    }
}

add_action('admin_menu', 'subscription_menu');
function subscription_menu()
{
    global $WC_Gateway_EBizCharge;
    add_menu_page('Subscriptions', 'Subscriptions', 'manage_options', 'view-subscription', 'view_subscription');
    if (class_exists('WC_Gateway_EBizCharge')) {
        if ($WC_Gateway_EBizCharge->is_ebizcharge_enabled() && $WC_Gateway_EBizCharge->is_recurring_enabled()) {
            add_submenu_page('view-subscription', 'Add Subscription', 'Add Subscription', 'manage_options', 'add-subscription', 'add_subscription');
        }
    }
    add_submenu_page('', 'Edit Subscription', 'Edit Subscription', 'manage_options', 'edit-subscription', 'edit_subscription');
    add_submenu_page('view-subscription', 'Upcoming Subscription Orders', 'Upcoming Subscription Orders', 'manage_options', 'future-subscription', 'future_subscription');
    add_submenu_page('view-subscription', 'Subscription Payment History', 'Subscription Payment History', 'manage_options', 'payment-history', 'payment_history');
    add_submenu_page('view-subscription', 'Subscription Orders', 'Subscription Orders', 'manage_options', 'subscription-orders', 'subscription_orders');
    add_submenu_page('view-subscription', 'EBizCharge Help & Contact Us', 'EBizCharge Help & Contact Us', 'manage_options', 'ebizcharge-help-contact-us', 'ebizcharge_help_contact_us');
}

function ebizcharge_help_contact_us()
{
    ob_start();
    require_once plugin_dir_path(__FILE__) . 'views/adminhtml/ebizcharge_help_contact_us.php';
    $template = ob_get_contents();
    ob_end_clean();
    echo $template;
}

function subscription_orders()
{
    ob_start();
    $ebiz = new WC_ebizcharge();
    if (!class_exists('WP_List_Table')) {
        require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
    }
    include_once 'includes/classs-wc-gateway-ebizcharge-list-general.php';
    include_once 'includes/classs-wc-gateway-ebizcharge-subscription-orders.php';
//    include_once 'includes/class-wc-gateway-ebizcharge-orders.php';
    require_once plugin_dir_path(__FILE__) . 'views/adminhtml/subscription_orders.php';
    $template = ob_get_contents();
    ob_end_clean();
    echo $template;
    if (!empty($_GET['email']) && $_GET['email'] == "yes") {
        $message = 'Email sent successfully.';
        echo $ebiz->getSuccessMessage($message);
    }
    if (!empty($_GET['email']) && $_GET['email'] == "no") {
        $message = 'Email not sent.';
        echo $ebiz->getErrorMessage($message);
    }

}

function payment_history()
{
    ob_start();
    $ebiz = new WC_ebizcharge();
    if (!class_exists('WP_List_Table')) {
        require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
    }
    include_once 'includes/classs-wc-gateway-ebizcharge-payment-history.php';
    include_once 'includes/class-wc-gateway-ebizcharge-subscription.php';
    require_once plugin_dir_path(__FILE__) . 'views/adminhtml/payment_history.php';
    $template = ob_get_contents();
    ob_end_clean();
    echo $template;
    if (!empty($_GET['email']) && $_GET['email'] == "yes") {
        $message = 'Email sent successfully.';
        echo $ebiz->getSuccessMessage($message);
    }

    if (!empty($_GET['email']) && $_GET['email'] == "no") {
        $message = 'Email not sent.';
        echo $ebiz->getErrorMessage($message);
    }

}

function future_subscription()
{
    ob_start();
    if (!class_exists('WP_List_Table')) {
        require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
    }
    include_once 'includes/classs-wc-gateway-ebizcharge-list-general.php';
    include_once 'includes/classs-wc-gateway-ebizcharge-future-subscription.php';
    include_once 'includes/class-wc-gateway-ebizcharge-subscription.php';
    require_once plugin_dir_path(__FILE__) . 'views/adminhtml/view_future_subscriptions.php';
    $template = ob_get_contents();
    ob_end_clean();
    echo $template;
}

function add_subscription()
{
    $ebiz = new WC_ebizcharge();
    // used in add form - don't remove
    $WC_Gateway_EBiz_Rec = new WC_Gateway_EBizCharge_Recurring();
    include_once('views/adminhtml/add_subscription.php');
    global $wpdb;
    if (isset($_POST['selectdivProduct'])) {
        $insert = $ebiz->add_new_subscription();
        if ($insert) {
            wp_redirect('admin.php?page=view-subscription&msg=success');
            exit;
        }
    }
    if (($_GET['msg'] ?? '') == "success") {
        $message = 'Subscription successfully saved.';
        echo $ebiz->getSuccessMessage($message);
    }
}

function edit_subscription()
{
    global $wpdb;
    $ebiz = new WC_ebizcharge();
    // used in edit form - don't remove
    $WC_Gateway_EBiz_Rec = new WC_Gateway_EBizCharge_Recurring();
    include_once('views/adminhtml/edit_subscription.php');
    $table_rec_id = $_REQUEST['table_rec_id'] ?? '';
    $custId = $_REQUEST['custId'] ?? '';
    $mid = $_REQUEST['mid'] ?? '';
    $pref = $_REQUEST['pref'] ?? '';

    $page = 'edit-subscription';
    $queryString = '&id=' . $table_rec_id . '&cid=' . $custId . '&mid=' . $mid . '&pref=' . $pref;

    if (isset($_POST['eb_rec_method_id'])) {
        $update = $ebiz->update_subscription();
        if ($update == 1) {
            wp_redirect("admin.php?page=" . $page . $queryString . "&msg=success");
            exit;
        }
        if ($update == 2) {
            wp_redirect("admin.php?page=" . $page . $queryString . "&msg=error_change");
            exit;
        }
        if ($update == 3) {
            wp_redirect("admin.php?page=" . $page . $queryString . "&msg=error_method");
            exit;
        }
        if ($update == 4) {
            wp_redirect("admin.php?page=" . $page . $queryString . "&msg=error_payment");
            exit;
        }
    }

    if (isset($_POST['unsub']) && $_POST['unsub'] == 'yes') {
        $sid = $_POST['sid'];
        $unsub = $ebiz->unsubscribe();
        if ($unsub == 1 && $sid == 0) {

            wp_redirect("admin.php?page=" . $page . $queryString . "&msg=success_resub");
            exit;
        }
        if ($unsub == 1 && $sid == 1) {

            wp_redirect("admin.php?page=" . $page . $queryString . "&msg=success_unsub");
            exit;
        }
        if ($unsub == 2) {
            wp_redirect("admin.php?page=" . $page . $queryString . "&msg=error_unsub");
            exit;
        }

    }
    if (isset($_POST['delete']) && $_POST['delete'] == 'yes') {
        $del = $ebiz->delete_subscription();
        if ($del == 1) {
            wp_redirect("admin.php?page=" . $page . $queryString . "&msg=success_del");
            exit;
        }

    }

    $_GET['msg'] = $_GET['msg'] ?? '';

    if ($_GET['msg'] == "success") {
        $message = 'Successfully updated.';
        echo $ebiz->getSuccessMessage($message);
    }
    if ($_GET['msg'] == "success_resub") {

        $message = 'Successfully Resubscribed.';
        echo $ebiz->getSuccessMessage($message);
    }
    if ($_GET['msg'] == "success_del") {

        $message = 'Successfully Deleted.';
        echo $ebiz->getSuccessMessage($message);
    }
    if ($_GET['msg'] == "success_unsub") {

        $message = 'Successfully Unsubscribed.';
        echo $ebiz->getSuccessMessage($message);
    }
    if ($_GET['msg'] == "error_change") {
        $message = 'Unable to update changes.';
        echo $ebiz->getErrorMessage($message);
    }
    if ($_GET['msg'] == "error_method") {
        $message = 'Unable to find method ID.';
        echo $ebiz->getErrorMessage($message);
    }
    if ($_GET['msg'] == "error_payment") {
        $message = 'Unable to add payment method.';
        echo $ebiz->getErrorMessage($message);
    }

}

function view_subscription()
{
    ob_start();
    if (!class_exists('WP_List_Table')) {
        require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
    }
    include_once 'includes/classs-wc-gateway-ebizcharge-list-general.php';
    include_once 'includes/classs-wc-gateway-ebizcharge-list-admin.php';
    include_once 'includes/class-wc-gateway-ebizcharge-subscription.php';
    require_once plugin_dir_path(__FILE__) . 'views/adminhtml/view_subscription.php';
    $ebiz = new WC_ebizcharge();

    if ((isset($_GET['sus'])) && ($_GET['sus'] == "yes")) {
        $message = 'Successfully Suspended.';
        echo $ebiz->getSuccessMessage($message);
    }
    if ((isset($_GET['resub'])) && ($_GET['resub'] == "yes")) {
        $message = 'Successfully Resubscribed.';
        echo $ebiz->getSuccessMessage($message);
    }

    if ((isset($_GET['unsub'])) && ($_GET['unsub'] == "yes")) {
        $message = 'Successfully Unsubscribed.';
        echo $ebiz->getSuccessMessage($message);
    }

    $template = ob_get_contents();

    ob_end_clean();

    echo $template;
}

function woocommerce_ebizcharge_commerce_init()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    };

    if (!class_exists('Backwards_Compatible_Order')) {
        include_once 'includes/class-wc-gateway-ebizcharge-migration-helper.php';
    }

    if (!class_exists('WC_Gateway_EBizCharge_Recurring')) {
        require plugin_dir_path(__FILE__) . 'includes/class-wc-gateway-ebizcharge-recurring.php';
    }

    DEFINE('PLUGIN_DIR', plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__)) . '/');

    /**
     * ebizcharge Commerce Gateway Class
     */
    class WC_ebizcharge extends WC_Payment_Gateway
    {
        public $securityid;
        public $username;
        public $password;
        public $allowedPayments;
        public $salemethod;
        public $cardtypes;
        public $saveCardBin;
        public $completecapture;
        public $voidOnCancelled;
        public $saveinfo;
        public $showSavedMethods;
        public $enableRecurring;
        public $recurringFrequencies;
        public $enableEconnect;
        public $econnectProductOption;
        public $econnectTitle;
        public $cvv = 'yes';
        public $_paymentType = array('sale' => 'Authorize &amp; Capture', 'authonly' => 'Authorize Only');

        public function __construct()
        {
            $this->id = 'ebizcharge';
            $this->method_title = __('EBizCharge', 'wc-ebizcharge');
            $this->method_description = __('EBizCharge allows customers to checkout using a credit card', 'wc-ebizcharge');
            $this->icon = PLUGIN_DIR . 'assets/images/cards.png';
            $this->has_fields = true;
            $this->supports = array(
                'products',
                'subscriptions',
                'subscription_cancellation',
                'subscription_suspension',
                'subscription_reactivation',
                'subscription_date_changes',
                'refunds',
            );

            // Load the form fields in admin only to avoid API calls
            if ($this->isAdminWcSettingsPage()) {
                $this->init_form_fields();
            }

            // Load the settings.
            $this->init_settings();

            foreach ($this->settings as $key => $val) {
                $this->$key = $val;
            }

            include_once('includes/class-wc-gateway-ebizcharge-admin.php');

            $this->add_ebizcharge_actions();
        }

        public function add_ebizcharge_actions(): void
        {
            $GLOBALS['completecapture'] = $this->completecapture ?? false;
            $GLOBALS['voidOnCancelled'] = $this->voidOnCancelled ?? false;

            if ($GLOBALS['action_counter'] == 0) {
                add_action('admin_notices', array(& $this, 'ebiz_commerce_ssl_check'));
                add_action('woocommerce_before_my_account', array($this, 'add_payment_method_options'));
                add_action('woocommerce_process_refund', array($this, 'process_refund'));
                add_action('wp_ajax_ebiz_sync_action', array($this, 'sync_actions'));
                add_action('woocommerce_receipt_ebizcharge', array(& $this, 'receipt_page'));
                if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
                    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(& $this, 'process_admin_options'));
                } else {
                    add_action('woocommerce_update_options_payment_gateways', array(& $this, 'process_admin_options'));
                }
                add_action('wp_enqueue_scripts', array(& $this, 'add_ebizcharge_pagination_scripts'));
                add_action('wp_enqueue_scripts', array(& $this, 'add_ebizcharge_scripts'));
                $GLOBALS['action_counter'] = 1;
            }
        }

        public function get_icon(): string
        {
            $selected_cards = $this->cardtypes ?? [];
            $icons = '';

            if (!empty($selected_cards) && is_array($selected_cards)) {

                $base_path = plugin_dir_url(__FILE__) . 'assets/images/';

                if (in_array('Visa', $selected_cards)) {
                    $icons .= '<img src="' . $base_path . 'visa.png" alt="Visa" title="Visa"/>';
                }
                if (in_array('MasterCard', $selected_cards)) {
                    $icons .= '<img src="' . $base_path . 'mastercard.png" alt="MasterCard" title=" title="Visa""/>';
                }
                if (in_array('Discover', $selected_cards)) {
                    $icons .= '<img src="' . $base_path . 'discover.png" alt="Discover" title="Discover"/>';
                }

                if (in_array('American Express', $selected_cards)) {
                    $icons .= '<img src="' . $base_path . 'american_express.png" alt="American Express" title="American Express"/>';
                }
            }

            return $icons;
        }

        /**
         * Include jQuery and our scripts
         */
        public function add_ebizcharge_scripts()
        {
            wp_enqueue_script('jquery');
            //wp_enqueue_script('3DS-main', PLUGIN_DIR . 'assets/js/EBiz3DSecure.js', array('jquery'), 2.0);
            wp_enqueue_script('3DS-main', 'https://cdn1.ebizcharge.net/Scripts/EBiz3DSecure/EBiz3DSecure.js', array('jquery'), 2.0);
            wp_enqueue_script('form-validation-js', PLUGIN_DIR . 'assets/js/jquery.validate.min.js', array('jquery'), 1.0);
            wp_enqueue_script('validate_form', PLUGIN_DIR . 'assets/js/validate_form.js', array('jquery'), 2.0);
            wp_enqueue_script('edit_billing_details', PLUGIN_DIR . 'assets/js/edit_billing_details.js', array('jquery'), 3.0);
            wp_localize_script('edit_billing_details', 'ebiz_ajax_object', array('ebiz_ajax_url' => admin_url('admin-ajax.php')));

            if ($this->cvv == 'yes') {
                wp_enqueue_script('check_cvv', PLUGIN_DIR . 'assets/js/check_cvv.js', array('jquery'), 1.0);
            }

            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('recurring_js', PLUGIN_DIR . 'assets/js/recurring.js', array('jquery'), 2.0);

            wp_enqueue_style('ebiz_css', PLUGIN_DIR . 'assets/css/ebizcharge.css', '', 2.0);
            wp_enqueue_style('jquery-ui-css', PLUGIN_DIR . 'assets/css/jquery-ui.css');
            wp_enqueue_style('recurring_css', PLUGIN_DIR . 'assets/css/recurring.css');

            if (!$this->user_has_stored_data(wp_get_current_user()->ID)) {
                return;
            }
        }

        /* to include files only on subscriptions page */
        public function add_ebizcharge_pagination_scripts()
        {
            if ($this->subscriptions_is_endpoint('subscriptions') || $this->subscriptions_is_endpoint('history')) {
                /* for pagination css */
                wp_enqueue_style('bootstrap_css', PLUGIN_DIR . 'assets/css/bootstrap.min.css');
                wp_enqueue_style('datatables_css', PLUGIN_DIR . 'assets/css/datatables.min.css');
                /* for pagination js */
                wp_enqueue_script('jquery_min_3.6', PLUGIN_DIR . 'assets/js/jquery-3.6.0.min.js', array('jquery'), 3.6);
                wp_enqueue_script('bootstrap_js', PLUGIN_DIR . 'assets/js/bootstrap.min.js', array('jquery'), 5.0);
                wp_enqueue_script('datatables_js', PLUGIN_DIR . 'assets/js/datatables.min.js', array('jquery'), 1.11);
            }

            if ($this->subscriptions_is_endpoint('subscriptions-edit')) {
                /* for confirm box css */
                wp_enqueue_style('bootstrap_css', PLUGIN_DIR . 'assets/css/bootstrap.min.css');
                wp_enqueue_style('confirm_css', PLUGIN_DIR . 'assets/css/jquery-confirm.min.css');
                /* for confirm box js */
                wp_enqueue_script('confirm_js', PLUGIN_DIR . 'assets/js/jquery-confirm.min.js', array('jquery'), 3.3);
            }

        }

        /* to check subscriptions as end point */
        public function subscriptions_is_endpoint($endpoint = false)
        {
            global $wp_query;

            if (!$wp_query) {
                return false;
            }

            return isset($wp_query->query[$endpoint]);
        }

        /**
         * add Econnect sync options
         *
         * @access public
         * @return void
         */
        public function econnect_sync_options()
        {
            global $woocommerce;

            $actions = array();

            $actions['syncCustomer'] = array(
                'button' => __('Upload Customers', 'woocommerce'),
                'class' => 'button button-primary'
            );
            $actions['syncItem'] = array(
                'button' => __('Upload Products', 'woocommerce'),
                'class' => 'button button-primary',
            );
            $actions['syncOrder'] = array(
                'button' => __('Upload Orders', 'woocommerce'),
                'class' => 'button button-primary'
            );
            $actions['syncInvoice'] = array(
                'button' => __('Upload Invoices', 'woocommerce'),
                'class' => 'button button-primary'
            );
            $actions['downloadCustomers'] = array(
                'button' => __('Download Customers', 'woocommerce'),
                'class' => 'button button-success'
            );

            $actions['downloadItems'] = array(
                'button' => __('Download Products', 'woocommerce'),
                'class' => 'button button-success',
            );

            $actions['downloadOrders'] = array(
                'button' => __('Download Orders', 'woocommerce'),
                'class' => 'button button-success'
            );

            $display = $this->enableEconnect == 'yes' ? '' : 'display:none';

            echo '<p id="econnect-buttons" class="buttons" style="' . $display . '">';

            foreach ($actions as $action_name => $action) {
                $class = $action['class'];
                $syncItemDisplay = '';

                if ($action_name == 'downloadCustomers') {
                    echo "</br></br>";
                }

                if ($action_name == 'syncItem' && isset($this->econnectProductOption)) {
                    $syncItemDisplay = ($this->econnectProductOption != 'syncItem') ? 'display:none' : '';

                } else if ($action_name == 'downloadItems' && isset($this->econnectProductOption)) {
                    $syncItemDisplay = ($this->econnectProductOption != 'downloadItems') ? 'display:none' : '';
                }

                echo '<a href="#" style="' . $syncItemDisplay . '" class= ' . "'$class'" . ' data-action="' . $action_name . '" data-id="' . $action_name . '" id="' . $action_name . '">' . $action['button'] . '</a> ';

            }
            echo '</p>';

            $js = "
            jQuery('#woocommerce_ebizcharge_enableEconnect').change(function() {
                jQuery('#econnect-buttons').toggle(this.checked);
                jQuery('#woocommerce_ebizcharge_econnectProductOption').toggle(this.checked);

            });

            jQuery('#woocommerce_ebizcharge_econnectProductOption').change(function() {
                var selectedOption = jQuery(this).val();

                if(selectedOption == 'syncItem') {
                    jQuery('#syncItem').show();
                    jQuery('#downloadItems').hide()
                } else if(selectedOption == 'downloadItems')  {
                    jQuery('#syncItem').hide();
                    jQuery('#downloadItems').show();
                } else {
                    jQuery('#downloadItems').hide()
                    jQuery('#syncItem').hide();
                }

            });

			jQuery('#econnect-buttons').on( 'click', 'a.button, a.refresh', function(){
				jQuery('#econnect-buttons').block(
				{ message: null, overlayCSS: { background: '#fff url(" . $woocommerce->plugin_url() . "/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });

				var data = {
					action: 		'ebiz_sync_action',
					security: 		'" . wp_create_nonce("ebiz_sync_action") . "',
					ebiz_action: 	jQuery(this).data('action'),
					ebiz_id: 		jQuery(this).data('id'),
				};
				// Ajax action
				jQuery.ajax({
					url: '" . admin_url('admin-ajax.php') . "',
					data: data,
					type: 'POST',
					success: function( result ) {
						jQuery('#messages').html(result);
						jQuery('#econnect-buttons').unblock();
						jQuery('.notice-dismiss').click(function () {
                            jQuery(this).parent('div').hide();
                        });
					}
				});

				return false;
			});
			
			jQuery('.woocommerce-save-button').css('text-transform', 'capitalize');
		";

            if (function_exists('wc_enqueue_js')) {
                wc_enqueue_js($js);
            } else {
                $woocommerce->add_inline_js($js);
            }

        }

        public function getSuccessMessage($message)
        {
            if (!empty($message)) {
                return '<div id="setting-success-settings_updated" class="notice notice-success settings-success is-dismissible">
						<p><strong>' . $message . '</strong>
						</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
					</div>';
            }
            return '';
        }

        public function getErrorMessage($message)
        {
            if (!empty($message)) {
                return '<div id="setting-error-settings_updated" class="notice notice-error settings-error is-dismissible">
						<p><strong>' . $message . '</strong>
						</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
					</div>';
            }
            return '';
        }

        public function getAdminMessage($type, $message)
        {
            $messageDiv = '';
            if (!empty($message)) {
                $messageDiv = '<div id="setting-' . $type . '-settings_updated" class="notice notice-' . $type . ' settings-' . $type . ' is-dismissible">
						<p><strong>' . $message . '</strong>
						</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
					</div>';
            }
            return $messageDiv;
        }

        /**
         * Perform sync actions for Econnect
         */
        public function sync_actions()
        {
            check_ajax_referer('ebiz_sync_action', 'security');

            $action = sanitize_title($_POST['ebiz_action']);

            if ($action == 'syncItem' && (!isset($this->econnectProductOption) || $this->econnectProductOption != 'syncItem')) {
                echo $this->getErrorMessage('Please select syncItem method to continue.');
                exit();

            } else if ($action == 'downloadItems' && (!isset($this->econnectProductOption) || $this->econnectProductOption != 'downloadItems')) {
                echo $this->getErrorMessage('Please select downloadItems method to continue.');
                exit();
            }

            $ebiz = new WC_ebizcharge();
            $tran = $ebiz->_initTransaction(true);

            $response = $tran->$action();
            echo $response;
            die;
        }

        /**
         * Process a manual refund in 'Admin - Edit Order' if supported.
         *
         * @param int $order_id Order ID.
         * @param float $amount Refund amount.
         * @param string $reason Refund reason.
         * @return bool|WP_Error
         */
        public function process_refund($order_id, $amount = null, $reason = '')
        {
            $tran = $this->_initTransaction();
            $order = $tran->getOrderByVersion($order_id);
            $transactionId = $order->get_transaction_id();
            $paymentStatus = $order->get_meta('_payment_status');

            if ($paymentStatus === WC_Gateway_EBizCharge_Admin::PAYMENT_STATUS_AUTHORIZED) {
                return new WP_Error('error', __('The Authorized only payment cannot be refunded. Please capture the payment before refund.', 'woocommerce'));
            } else if ($paymentStatus === WC_Gateway_EBizCharge_Admin::PAYMENT_STATUS_REFUNDED) {
                return new WP_Error('error', __('The payment status is Refunded. You cannot refund anymore.', 'woocommerce'));
            } else if ($paymentStatus === WC_Gateway_EBizCharge_Admin::PAYMENT_STATUS_VOIDED) {
                return new WP_Error('error', __('The payment status is Voided. You cannot void anymore.', 'woocommerce'));
            }

            if ($order && $transactionId && $amount > 0) {
                $tran->refnum = $transactionId;
                $tran->amount = $amount;
                $tran->command = 'creditvoid';
                $tran->orderid = $order_id;
                $tran->invoice = $order_id;
                $tran->ponum = $order->get_order_number();
                $tran->tax = $order->get_total_tax();
                $tran->description = 'Refund Reason: ' . $reason;
                $tran->shipping = $order->get_shipping_total();
                // get customer payment method and token
                if (empty($paymentMethodId = $order->get_meta('_payment_method_id'))) {
                    $paymentMethodId = null;
                }

                $userId = $order->get_user_id();
                $ebizCustomerNumber = get_user_meta($userId, 'CustNum', true);
                $refundRemaining = $order->get_remaining_refund_amount() + $amount; // entered amount already deducted

                if (!empty($userId) && !empty($paymentMethodId) && !empty($ebizCustomerNumber) && $amount != $order->get_total()) {

                    if ($amount <= $refundRemaining) {
                        $payType = $order->get_meta('_ebiz_payment_type');

                        if ($payType == 'check') {
                            $order->add_order_note(__('EBizCharge payment via check can be refunded fully only. Partial refund is not allowed.', 'woocommerce'));
                            return new WP_Error('error', __('EBizCharge payment via check can be refund fully. Partial refund not allowed. Error: ' . $tran->error, 'woocommerce'));
                        }

                        $tran->command = 'Credit';
                        // refund transaction
                        if ($tran->savedProcess($ebizCustomerNumber, $paymentMethodId)) {
                            $order->add_order_note(__('EBizCharge payment partially refunded online. Amount: ' . $amount . ' Reason: ' . $reason, 'woocommerce'));
                            $order->update_meta_data('_payment_status', WC_Gateway_EBizCharge_Admin::PAYMENT_STATUS_PARTIALLY_REFUNDED);
                            $order->save();

                        } else {
                            $order->add_order_note(__('EBizCharge payment refund failed. Transaction ID: ' . $transactionId . ' Error: ' . $tran->error, 'woocommerce'));
                            return new WP_Error('error', __('EBizCharge payment refund failed. Error: ' . $tran->error, 'woocommerce'));
                        }
                    } else {
                        $order->add_order_note(__('EBizCharge payment refund failed. 
                        amount: ' . $amount . ' is more than the remain amount . ' . $refundRemaining . ' Error: ' . $tran->error, 'woocommerce'));
                        return false;
                    }
                    return true;

                } else {

                    $tran->command = 'refund';

                    if ($tran->refundTransaction()) {
                        $paymentStatus = ($amount == $order->get_total())
                            ? WC_Gateway_EBizCharge_Admin::PAYMENT_STATUS_REFUNDED
                            : WC_Gateway_EBizCharge_Admin::PAYMENT_STATUS_PARTIALLY_REFUNDED;

                        $order->add_order_note(__('EBizCharge payment ' . $paymentStatus . ' online. Amount: ' . $amount . ' Reason: ' . $reason, 'woocommerce'));

                        $order->update_meta_data('_payment_status', $paymentStatus);
                        $order->save();

                        return true;
                    } else {
                        $errorMessage = 'EBizCharge payment refund failed. Transaction ID: ' . $transactionId . ' Error: ' . $tran->error;
                        $order->add_order_note(__($errorMessage, 'woocommerce'));
                        return new WP_Error('error', __($errorMessage, 'woocommerce'));
                    }
                }

            } else {
                return new WP_Error('error', __('EBizCharge payment refund failed. Please select a valid amount.', 'woocommerce'));
            }
        }

        /**
         * Check if SSL is enabled and notify the user.
         */
        public function ebiz_commerce_ssl_check()
        {
            /* if ( get_option( 'woocommerce_force_ssl_checkout' ) == 'no' && $this->enabled == 'yes' ) {
            echo '<div class="error"><p>' . sprintf( __('ebizcharge is enabled and the <a href="%s">force SSL option</a> is disabled; your checkout is not secure! Please enable SSL and ensure your server has a valid SSL certificate.', 'woothemes' ), admin_url( 'admin.php?page=woocommerce' ) ) . '</p></div>';
            }*/
        }

        /**
         * UI - Admin Panel Options
         */
        public function admin_options()
        {
            $tran = new WC_Gateway_EBizCharge();
            ?>
            <h3><?php _e('EBizCharge', 'woothemes'); ?></h3>
            <p>
                <?php _e('EBizCharge Gateway is simple and powerful. The plugin works by adding credit card fields on the checkout page, and then sending the details to EBizCharge for verification.  <a target="_blank" href="http://ebizcharge.com/">Click here to get paid like the pros</a>.', 'woothemes'); ?>
            </p>

            <?php if ($tran->isPaymentSettingEmpty()) {
            echo '<div class="notice notice-error">
                    <p>EBizCharge settings seems empty or outdated. Please review and save changes.</p>
                </div>';
        } ?>

            <table class="form-table">
                <?php $this->generate_settings_html(); ?>
            </table>
            <?php
            $this->econnect_sync_options();
            echo '<p id="messages"></p>';
        }

        public function getOrderIdFromUrl()
        {
            $url = $_SERVER['REQUEST_URI'];
            if (strpos($url, '/order-pay/') !== false) {
                // Define the regex pattern to match both query parameter and URL path formats
                $pattern = '/(?:[?&]order-pay=|\/order-pay\/)(\d+)/';
                if (preg_match($pattern, $url, $matches)) {
                    // If a match is found, return the captured order ID (matches[1] contains the order ID)
                    return $matches[1] ?? null;
                }
            }

            return null; // Return null if no order ID is found
        }

        /**
         * UI - Payment page fields for ebizcharge payment module.
         */
        public function payment_fields()
        {
            $WC_Gateway_EBiz_Rec = new WC_Gateway_EBizCharge_Recurring();
            $cart_subscriptions = $WC_Gateway_EBiz_Rec->check_recurring_items_after_add_to_cart();
            $tran = $this->_initTransaction();
            $user = wp_get_current_user();
            $allPaymentMethods = $tran->getCustomerPaymentMethodsByGetCustomer($user->ID);

            $surchargeSettings = $tran->getSurchargeSettings();
            $surchargePercentage = 0;
            if ($isSurchargeEnabled = $surchargeSettings->IsSurchargeEnabled ?? false) {
                $surchargePercentage = $surchargeSettings->SurchargePercentage;
            }

            // Description of payment method from settings
            if ($this->description) { ?>
                <p><?php echo $this->description; ?></p>
            <?php }
            if ($isSurchargeEnabled) {
                echo "<div class='woocommerce-info surcharge-alert'>$surchargeSettings->SurchargeTermsNote</div>";
            } ?>

            <fieldset style="border-bottom:none">
                <?php if ($tran->is_cc_enabled()) { ?>
                    <input type="radio" name="ebizcharge-payment-method" id="ebizcharge-cc-payment"
                           class="ebizcharge-cc-payment" value="cc" checked="checked"
                           onclick="document.getElementById('bank-form').style.display='none';
										document.getElementById('bank-form').disabled = true;
										document.getElementById('cc-form').disabled = false;
										document.getElementById('cc-form').style.display='block';
										"/>
                    <?php if ($tran->enabled_cc_ach_both()) { ?>
                        <label for="ebizcharge-cc-payment" class="ebizcharge-cc-payment-label">
                            <?php _e('Pay by Card', 'woocommerce') ?>
                        </label>
                    <?php }
                } ?>
                <?php if ($tran->is_ach_enabled()) { ?>
                    &nbsp;
                    <input type="radio" name="ebizcharge-payment-method" id="ebizcharge-bank-payment"
                           class="ebizcharge-bank-payment" value="check"
                           onclick="document.getElementById('cc-form').style.display='none';
										document.getElementById('cc-form').disabled = true;
										document.getElementById('bank-form').disabled = false;
										document.getElementById('bank-form').style.display='block';
										"/>
                <?php if ($tran->enabled_cc_ach_both()) { ?>
                    <label for="ebizcharge-bank-payment" class="ebizcharge-bank-payment-label">
                        <?php _e('Pay by Bank', 'woocommerce'); ?>
                    </label>
                <?php } ?>
                <?php if (!$tran->is_cc_enabled()) { ?>
                    <script type="text/javascript">
                        jQuery(document).ready(function () {
                            jQuery('#ebizcharge-bank-payment').prop('checked', true);
                            jQuery("#ebizcharge-bank-payment").trigger('click');
                            jQuery("#cc-form").css('display', 'none');
                            jQuery("#cc-form").prop("disabled", true);
                            jQuery("#bank-form").css('display', 'block');
                            jQuery("#bank-form").prop("disabled", false);
                        });
                    </script>
                <?php } ?>
                <?php } ?>
            </fieldset>
            <?php if ($tran->is_cc_enabled()) { ?>
            <fieldset id="cc-form">
                <div class="blockUI blockOverlay" style="display: none;"></div>

                <?php
                $paymentMethods = $tran->groupCustomerPaymentMethodsByType($allPaymentMethods, 'cc');
                if (is_user_logged_in() && $paymentMethods && $tran->showSavedMethods)
                {
                ?>
                <fieldset class="fieldset-style">
                    <input type="radio" name="ebizcharge-use-stored-payment-info"
                           id="ebizcharge-use-stored-payment-info-yes" value="yes" checked="checked"
                           onclick="document.getElementById('ebizcharge-new-info').style.display = 'none';
									document.getElementById('ebizcharge-new-info').disabled = true;
									document.getElementById('ebizcharge-stored-info').style.display = 'block';
									document.getElementById('ebizcharge-stored-info').disabled = false;
									document.getElementById('ebizcharge-update-info').style.display = 'none';
									document.getElementById('ebizcharge-update-info').disabled = true;
									document.getElementById('bank-form').disabled = true;
									"/>
                    <label for="ebizcharge-use-stored-payment-info-yes" style="display: inline;">
                        <?php _e('Use a saved credit card', 'woocommerce') ?>
                    </label>
                    <div id="ebizcharge-stored-info" style="margin-left: 30px">
                        <select id="ebizcharge-stored-card" name="ebizcharge-stored-card">
                            <?php foreach ($paymentMethods as $methodSaved) {
                                $card_type = $tran->get_card_type($methodSaved->CardType);
                                $methodTitle = $card_type . ' ending in ' . substr($methodSaved->CardNumber, -4) . ' (' . substr($methodSaved->CardExpiration, -2) . '/' . substr($methodSaved->CardExpiration, 2, 2) . ')';
                                ?>
                                <option value="<?php echo $methodSaved->MethodID ?>">
                                    <?php echo $methodTitle; ?>
                                </option>
                                <?php
                            }
                            ?>
                        </select>
                        <?php foreach ($paymentMethods as $methodSaved) {
                            $exp = explode('-', $methodSaved->CardExpiration);
                            $expMonth = $exp[1] ?? '';
                            $expYear = $exp[0] ?? '';
                            ?>
                            <input type="text" style="display: none"
                                   id="exp_month_saved_<?php echo $methodSaved->MethodID; ?>"
                                   value="<?php echo $expMonth ?? ''; ?>"/>
                            <input type="text" style="display: none"
                                   id="exp_year_saved_<?php echo $methodSaved->MethodID; ?>"
                                   value="<?php echo $expYear ?? ''; ?>"/>
                            <input type="text" style="display: none"
                                   id="card_number_saved_<?php echo $methodSaved->MethodID; ?>"
                                   value="<?php echo $methodSaved->CardNumber; ?>"/>
                            <input type="text" style="display: none"
                                   id="card_type_saved_<?php echo $methodSaved->MethodID; ?>"
                                   value="<?php echo $methodSaved->CardType; ?>"/>
                        <?php } ?>
                        <div class="clear"></div>
                        <!-- Credit card expiration -->
                        <p class="form-row form-row-first exp-month">
                            <label for="exp_month_saved">
                                <?php echo __('Expiration Date', 'woocommerce'); ?> <span class="required">*</span>
                            </label>
                            <select name="exp_month_saved" id="exp_month_saved"
                                    class="woocommerce-select woocommerce-cc-month-update">
                                <option value=""><?php _e('Month', 'woocommerce'); ?></option>
                                <?php $this->getExpiryMonths(); ?>
                            </select>
                            <select name="exp_year_saved" id="exp_year_saved"
                                    class="woocommerce-select woocommerce-cc-year">
                                <option value=""><?php _e('Year', 'woocommerce'); ?></option>
                                <?php $this->getExpiryYears(); ?>
                            </select>
                        </p>
                        <?php
                        // Credit card security code
                        if ($this->cvv == 'yes') {
                            ?>
                            <p class="form-row clear">
                                <label class="cvv" for="cvv">
                                    <?php _e('CVV', 'woocommerce') ?>
                                    <span class="required">*</span>
                                </label>
                                <input oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                       woocommerce_ebizcharge_salemethod="text" type="password" class="input-text"
                                       id="cvvS"
                                       name="cvv_saved" maxlength="4" style="width: 50px"/>
                            </p>
                            <div class="empty-error cvv-saved-error"></div>
                            <?php
                        } ?>
                        <input type="hidden" id="card_number_saved" name="card_number_saved" value=""/>
                        <input type="hidden" id="card_type_saved" name="card_type_saved" value=""/>
                        <div class="empty-error expmonthyear-saved-error"></div>
                        <div class="clear"></div>
                    </div>
                </fieldset>
                <fieldset class="fieldset-style">
                    <input type="radio" name="ebizcharge-use-stored-payment-info"
                           id="ebizcharge-use-update-payment-info-yes" value="update"
                           onclick="document.getElementById('ebizcharge-new-info').style.display = 'none';
									document.getElementById('ebizcharge-new-info').disabled = true;
									document.getElementById('ebizcharge-stored-info').style.display = 'none';
									document.getElementById('ebizcharge-stored-info').disabled = true;
									document.getElementById('ebizcharge-update-info').style.display = 'block';
									document.getElementById('ebizcharge-update-info').disabled = false;
									document.getElementById('bank-form').disabled = true;
									"/>
                    <label for="ebizcharge-use-update-payment-info-yes" style="display: inline;">
                        <?php _e('Use and update credit card', 'woocommerce') ?>
                    </label>
                    <div id="ebizcharge-update-info" style="display:none; margin-left: 30px">
                        <select name="ebizcharge_update_cc" id="ebizcharge_update_cc">
                            <?php foreach ($paymentMethods as $method) {
                                $card_type = $tran->get_card_type($method->CardType);
                                $methodTitle = $card_type . ' ending in ' . substr($method->CardNumber, -4) . ' (' . substr($method->CardExpiration, -2) . '/' . substr($method->CardExpiration, 2, 2) . ')';
                                ?>
                                <option value="<?php echo $method->MethodID ?>">
                                    <?php echo $methodTitle; ?>
                                </option>
                                <?php
                            } ?>
                        </select>
                        <?php foreach ($paymentMethods as $method) {
                            $exp = explode('-', $method->CardExpiration);
                            $expMonth = $exp[1] ?? '';
                            $expYear = $exp[0] ?? '';
                            ?>
                            <input type="text" style="display: none"
                                   id="exp_month_update_<?php echo $method->MethodID; ?>"
                                   value="<?php echo $expMonth ?? ''; ?>"/>
                            <input type="text" style="display: none"
                                   id="exp_year_update_<?php echo $method->MethodID; ?>"
                                   value="<?php echo $expYear ?? ''; ?>"/>
                            <input type="text" style="display: none" id="avs_street_<?php echo $method->MethodID; ?>"
                                   value="<?php echo $method->AvsStreet ?? ''; ?>"/>
                            <input type="text" style="display: none" id="avs_zip_<?php echo $method->MethodID; ?>"
                                   value="<?php echo $method->AvsZip ?? ''; ?>"/>
                            <input type="text" style="display: none"
                                   id="card_number_update_<?php echo $method->MethodID; ?>"
                                   value="<?php echo $method->CardNumber; ?>"/>
                            <input type="text" style="display: none"
                                   id="card_type_update_<?php echo $method->MethodID; ?>"
                                   value="<?php echo $method->CardType; ?>"/>
                        <?php } ?>
                        <div class="clear"></div>

                        <p class="form-row form-row-first exp-month">
                            <label for="exp_month_update">
                                <?php echo __('Expiration Date', 'woocommerce'); ?> <span class="required">*</span>
                            </label>
                            <select name="exp_month_update" id="exp_month_update"
                                    class="woocommerce-select woocommerce-cc-month-update">
                                <option value=""><?php _e('Month', 'woocommerce'); ?></option>
                                <?php $this->getExpiryMonths(); ?>
                            </select>
                            <select name="exp_year_update" id="exp_year_update"
                                    class="woocommerce-select woocommerce-cc-year-update">
                                <option value=""><?php _e('Year', 'woocommerce'); ?></option>
                                <?php $this->getExpiryYears(); ?>
                            </select>
                        </p>
                        <?php
                        // Credit card security code
                        if ($this->cvv == 'yes') {
                            ?>
                            <p class="form-row clear">
                                <label class="cvv" for="cvv">
                                    <?php _e('CVV', 'woocommerce') ?>
                                    <span class="required">*</span>
                                </label>
                                <input oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                       woocommerce_ebizcharge_salemethod="text" type="password" class="input-text"
                                       id="cvvU"
                                       name="cvv_update" maxlength="4" style="width: 50px"/>
                            </p>
                            <div class="empty-error expmonthyear-update-error"></div>
                            <div class="empty-error cvv-update-error"></div>
                            <div class="clear"></div>
                            <?php
                        } ?>
                        <p class="form-row form-row-first">
                            <label for="avs_street">
                                <?php echo __('Billing Street', 'woocommerce') ?> <span class="required">*</span>
                            </label>
                            <input type="text" class="input-text" id="avs_street" name="avs_street" maxlength="100"
                                   value="<?php echo $method->AvsStreet ?? ''; ?>"/>
                        </p>
                        <p class="form-row form-row-first clear">
                            <label for="avs_zip">
                                <?php echo __('Zip/Postal Code', 'woocommerce') ?> <span class="required">*</span>
                            </label>
                            <input type="text" class="input-text" id="avs_zip" name="avs_zip" maxlength="50"
                                   value="<?php echo $method->AvsZip ?? ''; ?>"
                                   oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"/>
                        </p>
                        <input type="hidden" id="card_number_update" name="card_number_update" value=""/>
                        <input type="hidden" id="card_type_update" name="card_type_update" value=""/>
                        <div class="empty-error billing-update-error"></div>
                        <div class="empty-error avs-update-error"></div>
                        <div class="clear"></div>
                    </div>
                </fieldset>
                <fieldset class="fieldset-style">
                    <p>
                        <input type="radio" name="ebizcharge-use-stored-payment-info"
                               id="ebizcharge-use-stored-payment-info-no" value="no"
                               onclick="document.getElementById('ebizcharge-new-info').disabled = false;
										document.getElementById('ebizcharge-new-info').style.display = 'block';
										document.getElementById('ebizcharge-stored-info').style.display = 'none';
										document.getElementById('ebizcharge-stored-info').disabled = true;
										document.getElementById('ebizcharge-update-info').style.display = 'none';
										document.getElementById('ebizcharge-update-info').disabled = true;
										document.getElementById('bank-form').disabled = true;
										document.getElementById('cc-3ds-fields').disabled = false;
										"/>
                        <label for="ebizcharge-use-stored-payment-info-no" style="display: inline;">
                            <?php _e('Use a new credit card', 'woocommerce') ?>
                        </label>
                    </p>
                    <div id="ebizcharge-new-info" style="display:none; margin-left: 30px">
                        <?php } else { ?>
                        <fieldset style="border: none">
                            <!-- Show input boxes for new data -->
                            <div id="ebizcharge-new-info">
                                <?php } ?>
                                <!-- Credit card Holder Name -->
                                <p class="form-row ">
                                    <label class="ccholder" for="ccholder">
                                        <?php echo __('Name on Card', 'woocommerce') ?> <span class="required">*</span>
                                    </label>
                                    <input type="text" placeholder="Name on Card" class="input-text" id="ccholder"
                                           name="ccholder" maxlength="50"/>
                                </p>
                                <div class="empty-error ccholder-new-error"></div>
                                <!-- Credit card number -->
                                <p class="form-row form-row-first">
                                    <label class="ccnum" for="ccnum">
                                        <?php echo __('Credit Card#', 'woocommerce') ?>
                                        <span class="required">*</span>
                                    </label>
                                    <input onkeyup="getCardType(this.value)" type="text"
                                           placeholder="Credit Card#"
                                           class="input-text" id="ccnum" name="ccnum" minlength="15" maxlength="16"
                                           oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"/>
                                </p>
                                <!-- Credit card type -->
                                <p class="form-row form-row-last">
                                    <label class="cardtype" for="cardtype">
                                        <?php echo __('Card Type', 'woocommerce') ?> <span class="required">*</span>
                                    </label>
                                    <select name="cardtype" id="cardtype" class="card">
                                        <?php $this->getCardOptions() ?>
                                    </select>
                                    <input type="hidden" id="allowedCardTypes" class="allowedCardTypes"
                                           value="<?php echo $this->getAllowedCardOptions(); ?>"/>
                                </p>
                                <div class="empty-error ccnum-new-error"></div>
                                <div class="empty-error cardtype-new-error"></div>
                                <div class="clear"></div>
                                <!-- Credit card expiration -->
                                <p class="form-row form-row-first">
                                    <label class="expmonthyear" for="cc-expire-month">
                                        <?php echo __('Expiration Date', 'woocommerce') ?>
                                        <span class="required">*</span>
                                    </label>
                                    <select name="expmonth" id="expmonth" class="woocommerce-select"
                                            style="width:100px;">
                                        <option value="">
                                            <?php _e('Month', 'woocommerce') ?>
                                        </option>
                                        <?php $this->getExpiryMonths(); ?>
                                    </select>
                                    <select name="expyear" id="expyear" class="woocommerce-select" style="width: 100px;">
                                        <option value="">
                                            <?php _e('Year', 'woocommerce') ?>
                                        </option>
                                        <?php $this->getExpiryYears(); ?>
                                    </select>
                                </p>
                                <?php
                                // Credit card security code
                                if ($this->cvv == 'yes') {
                                    ?>
                                    <p class="form-row form-row-last">
                                        <label class="cvv" for="cvv">
                                            <?php _e('CVV', 'woocommerce') ?>
                                            <span class="required">*</span>
                                        </label>
                                        <input oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                                               style="width: 50px !important;" type="password"
                                               woocommerce_ebizcharge_salemethod="text" class="input-text" id="cvv"
                                               name="cvv" placeholder="cvv" maxlength="4"/>
                                    </p>
                                <?php } ?>

                                <div class="empty-error expmonthyear-new-error"></div>
                                <div class="empty-error cvv-new-error"></div>

                                <?php
                                // Check subscription in the cart if found
                                // Show Option to store credit card data only logged in users
                                // Frank: can we add the check box while creating login ask user if they want to save the card as well
                                //if ( is_user_logged_in() ) {
                                // Option to store credit card data
                                if ($this->saveinfo == 'yes' && !(class_exists('WC_Subscriptions_Cart') && WC_Subscriptions_Cart::cart_contains_subscription())) {
                                    ?>
                                    <div class="clear"></div>
                                    <p class="form-row">
                                        <?php
                                        if ($cart_subscriptions) { ?>
                                            <input type="checkbox" id="saveinfo" name="saveinfo" class="input-checkbox"
                                                   checked disabled>
                                            <input type="hidden" id="saveinfo" name="saveinfo" class="input-checkbox"
                                                   value="1"/>
                                        <?php } else { ?>
                                            <input type="checkbox" id="saveinfo" name="saveinfo" class="input-checkbox">
                                        <?php } ?>
                                        <label for="saveinfo" style="display: inline">
                                            <?php _e('Save for future use?', 'woocommerce') ?>
                                        </label>
                                    </p>
                                    <?php
                                } else { ?>
                                    <input type="hidden" id="saveinfo" name="saveinfo" class="input-checkbox"
                                           value="1"/>
                                <?php }

                                if ($tran->Is3DS2Enabled()) {
                                    $orderId = $this->getOrderIdFromUrl();
                                    $get3DSData = $tran->get3DSFormData($orderId);
                                    ?>
                                    <div id="cc-3ds-fields" class="cc-3ds-fields">
                                        <input type="hidden" id="SecurityId" value="<?php echo $get3DSData['SecurityId']; ?>"/>
                                        <input type="hidden" id="Is3DS2Enabled" name="Is3DS2Enabled" value="<?php echo $get3DSData['Is3DS2Enabled']; ?>"/>
                                        <input type="hidden" id="orderAmount" value="<?php echo $get3DSData['orderAmount']; ?>"/>
                                        <input type="hidden" id="storeCurrency" value="<?php echo $get3DSData['storeCurrency']; ?>"/>
                                        <input type="hidden" id="orderNumber" value="<?php echo $get3DSData['orderNumber']; ?>"/>
                                        <?php if(!empty($orderId)) { ?>
                                            <input type="hidden" id="billing_first_name" value="<?php echo $get3DSData['billing_first_name']; ?>"/>
                                            <input type="hidden" id="billing_last_name" value="<?php echo $get3DSData['billing_last_name']; ?>"/>
                                            <input type="hidden" id="billing_email" value="<?php echo $get3DSData['billing_email']; ?>"/>
                                            <input type="hidden" id="billing_address_1" value="<?php echo $get3DSData['billing_address_1']; ?>"/>
                                            <input type="hidden" id="billing_address_2" value="<?php echo $get3DSData['billing_address_2']; ?>"/>
                                            <input type="hidden" id="billing_city" value="<?php echo $get3DSData['billing_city']; ?>"/>
                                            <input type="hidden" id="billing_phone" value="<?php echo $get3DSData['billing_phone']; ?>"/>
                                            <input type="hidden" id="billing_postcode" value="<?php echo $get3DSData['billing_postcode']; ?>"/>

                                        <?php } ?>
                                        <input type="hidden" id="CAVV" name="CAVV" value=""/>
                                        <input type="hidden" id="XID" name="XID" value=""/>
                                        <input type="hidden" id="ECI" name="ECI" value=""/>
                                        <input type="hidden" id="Pares" name="Pares" value=""/>
                                        <input type="hidden" id="DSTransactionId" name="DSTransactionId" value=""/>
                                        <input type="hidden" id="WCPlaceOrder" name="WCPlaceOrder" value=""/>
                                    </div>
                                    <?php
                                } else { ?>
                                    <div id="cc-3ds-fields" class="cc-3ds-fields">
                                        <input type="hidden" id="Is3DS2Enabled" name="Is3DS2Enabled" value="false"/>
                                    </div>
                                <?php } ?>
                            </div>
                        </fieldset>
                </fieldset>
            </fieldset>
        <?php } ?>
            <?php if ($tran->is_ach_enabled()) { ?>
            <fieldset id="bank-form"
            <?php if (!$tran->is_cc_enabled()) {
                echo 'style="display:block;">';
            } else {
                echo 'style="display:none">';
            } ?>
            <?php
            $paymentBanks = $tran->groupCustomerPaymentMethodsByType($allPaymentMethods, 'check');

            if (is_user_logged_in() && $paymentBanks && $tran->showSavedMethods) {
                ?>
                <fieldset class="fieldset-style">
                    <input type="radio" name="ebizcharge-use-stored-bank-info" id="ebizcharge-use-stored-bank-info-yes"
                           value="yes" checked="checked"
                           onclick="document.getElementById('ebizcharge-new-bank-info').style.display='none';
									document.getElementById('ebizcharge-new-bank-info').disabled = true;
									document.getElementById('ebizcharge-stored-bank-info').disabled = false;
									document.getElementById('ebizcharge-stored-bank-info').style.display='block';
									document.getElementById('cc-form').disabled = true;
									"/>
                    <label for="ebizcharge-use-stored-bank-info-yes" style="display: inline;">
                        <?php _e('Use a saved bank account', 'woocommerce') ?>
                    </label>
                    <div id="ebizcharge-stored-bank-info">
                        <select id="ebizcharge-stored-bank" name="ebizcharge-stored-bank">
                            <?php foreach ($paymentBanks as $method) {
                                $acTitle = ucfirst($method->AccountType) . ' ending in ' . (substr($method->Account, -4));
                                ?>
                                <option value="<?php echo $method->MethodID; ?>">
                                    <?php echo $acTitle; ?>
                                </option>
                            <?php } ?>
                        </select>
                        <?php foreach ($paymentBanks as $method) {
                            ?>
                            <input type="text" style="display: none"
                                   id="ac_holder_saved_<?php echo $method->MethodID; ?>"
                                   value="<?php echo $method->AccountHolderName; ?>"/>
                            <input type="text" style="display: none"
                                   id="ac_type_saved_<?php echo $method->MethodID; ?>"
                                   value="<?php echo $method->AccountType; ?>"/>
                            <input type="text" style="display: none"
                                   id="ac_number_saved_<?php echo $method->MethodID; ?>"
                                   value="<?php echo $method->Account; ?>"/>
                            <input type="text" style="display: none"
                                   id="routing_saved_<?php echo $method->MethodID; ?>"
                                   value="<?php echo $method->Routing; ?>"/>
                        <?php } ?>
                        <input type="hidden" id="ac_holder_saved" name="ac_holder_saved" value=""/>
                        <input type="hidden" id="ac_type_saved" name="ac_type_saved" value=""/>
                        <input type="hidden" id="ac_number_saved" name="ac_number_saved" value=""/>
                        <input type="hidden" id="routing_saved" name="routing_saved" value=""/>
                    </div>
                </fieldset>
                <fieldset class="fieldset-style">
                <p>
                    <input type="radio" name="ebizcharge-use-stored-bank-info"
                           id="ebizcharge-use-stored-bank-info-no" value="no"
                           onclick="document.getElementById('ebizcharge-new-bank-info').disabled = false;
										document.getElementById('ebizcharge-new-bank-info').style.display='block';
										document.getElementById('ebizcharge-stored-bank-info').style.display='none';
										document.getElementById('ebizcharge-stored-bank-info').disabled = true;
										document.getElementById('cc-form').disabled = true;
										document.getElementById('cc-3ds-fields').disabled = true;
										"/>
                    <label for="ebizcharge-use-stored-bank-info-no" style="display: inline;">
                        <?php _e('Use a new bank account', 'woocommerce') ?>
                    </label>
                </p>
                <div id="ebizcharge-new-bank-info" style="display:none">
            <?php } else { ?>
                <fieldset class="fieldset-style">
                <!-- Show input boxes for new bank data -->
                <div id="ebizcharge-new-bank">
            <?php } ?>
            <!-- Account Holder Name -->
            <p class="form-row">
                <label class="acholder" for="acholder">
                    <?php echo __('Account Holder', 'woocommerce') ?>
                    <span class="required">*</span>
                </label>
                <input type="text" class="input-text" id="acholder" name="acholder" maxlength="50"/>
            <div class="empty-error acholder-error"></div>
            </p>
            <!-- Account Number -->
            <p class="form-row">
                <label class="acnum" for="acnum">
                    <?php echo __('Account Number', 'woocommerce') ?>
                    <span class="required">*</span>
                </label>
                <input type="text" class="input-text" id="acnum" name="acnum"
                       minlength="5" maxlength="17"
                       oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"/>
            <div class="empty-error acnum-error"></div>
            </p>

            <div class="clear"></div>
            <!-- Routing Number -->
            <p class="form-row">
                <label class="routingnum" for="routingnum">
                    <?php echo __('Routing Number', 'woocommerce') ?>
                    <span class="required">*</span>
                </label>
                <input type="text" class="input-text" id="routingnum" name="routingnum"
                       minlength="9" maxlength="9"
                       oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"/>
            <div class="empty-error routingnum-error"></div>
            </p>

            <!-- Account Type -->
            <p class="form-row form-row-first
                <label for="actype">
            <?php echo __('Account Type', 'woocommerce') ?> <span class="required">*</span>
            </label>
            <select name="actype" id="actype" class="woocommerce-select">
                <option value="checking">Checking</option>
                <option value="savings">Savings</option>
            </select>
            <div class="empty-error actype-error"></div>
            </p>
            <?php
            // Show Option to store bank account data only logged in users
            if (is_user_logged_in()) {
                // Option to store bank account data
                if ($this->saveinfo == 'yes' && !(class_exists('WC_Subscriptions_Cart') && WC_Subscriptions_Cart::cart_contains_subscription())) {
                    ?>
                    <div style="clear: both;"></div>
                    <p class="savepm">

                        <?php
                        if ($cart_subscriptions) { ?>
                            <input type="checkbox" id="save_info_bank" name="saveinfo"
                                   class="input-checkbox" checked disabled>
                            <input type="hidden" id="save_info_bank" name="saveinfo"
                                   class="input-checkbox" value="1"/>
                        <?php } else { ?>
                            <input type="checkbox" id="save_info_bank" name="saveinfo"
                                   class="input-checkbox">
                        <?php } ?>
                        <label for="save_info_bank">
                            <?php _e('Save for future use?', 'woocommerce') ?>
                        </label>
                    </p>
                    <?php
                } else {
                    if ($cart_subscriptions) { ?>
                        <input type="hidden" id="saveinfo" name="saveinfo" class="input-checkbox"
                               value="1"/>
                    <?php }

                }
            }
            ?>
            </div>
            </fieldset>
            </fieldset>
            </fieldset>
        <?php } ?>
            <input type="hidden" id="surcharge_enabled" name="surcharge_enabled"
                   value="<?php echo $isSurchargeEnabled ?>"/>
            <?php
            if ($isSurchargeEnabled) {
                $orderTotal = WC()->cart->get_total('');
                ?>
                <div id="ebiz-surcharge">
                    <p><?php echo $surchargeSettings->SurchargeCaption . ' (' . $surchargePercentage . '%)' ?>
                        : &nbsp;&nbsp; <?php echo wc_price(0) ?></p>
                    <p>Total: &nbsp;&nbsp; <?php echo wc_price($orderTotal) ?></p>
                </div>

                <input type="hidden" id="surcharge_percentage" name="surcharge_percentage"
                       value="<?php echo $surchargePercentage ?>"/>
                <input type="hidden" id="surcharge_amount" name="surcharge_amount" value=""/>
                <input type="hidden" id="surcharge_caption" name="surcharge_caption"
                       value="<?php echo $surchargeSettings->SurchargeCaption ?>"/>

                <?php
            }
        }

        public function get_instock_items()
        {
            $query = array(
                'status' => 'publish',
                'limit' => -1,
                'stock_status' => 'instock',
                'catalog_visibility' => 'visible',
            );

            return wc_get_products($query);
        }

        public function _initTransaction($econnect = false)
        {
            if ($econnect) {
                include_once 'includes/class-wc-gateway-ebizcharge-econnect.php';
                $tran = new WC_Gateway_EBizCharge_Econnect();

            } else {
                include_once 'includes/class-wc-gateway-ebizcharge.php';
                $tran = new WC_Gateway_EBizCharge();
            }

            $tran->showSavedMethods = (isset($this->showSavedMethods) && $this->showSavedMethods == 'yes');
            $tran->enableEconnect = (isset($this->enableEconnect) && $this->enableEconnect == 'yes');
            return $tran;
        }

        public function getAllowedPayments($merchantApiSettings)
        {
            //$tran = new WC_Gateway_EBizCharge();
            //$get_ebiz_setting = $tran->ebiz_admin_settings();
            // we'll not disable the plugin if api settings not found because sometime Gateway API request failed and cause issues
            $options = array('enableCC' => 'Allow Credit Card Payments Only');

            if ($merchantApiSettings) {

                $allowCCPayments = !empty($merchantApiSettings->AllowCreditCardPayments);
                $allowACHPayments = !empty($merchantApiSettings->AllowACHPayments);

                if ($allowCCPayments && !$allowACHPayments) {
                    $options = array(
                        'enableCC' => 'Allow Credit Card Payments Only',
                    );
                } elseif ($allowACHPayments && !$allowCCPayments) {
                    $options = array(
                        'enableACH' => 'Allow ACH Payments Only',
                    );
                } elseif ($allowCCPayments && $allowACHPayments) {
                    $options = array(
                        'enableCC' => 'Allow Credit Card Payments Only',
                        'enableACH' => 'Allow ACH Payments Only',
                        'enableCCACHBoth' => 'Allow Both Credit Card and ACH Payments',
                    );
                }
            }

            return array(
                'title' => __('Allow Payments', 'woothemes'),
                'type' => 'select',
                'description' => __('Please save settings after adding security credentials to show payment options. This allows customers to view an option to pay with credit card, bank account or both.', 'woothemes'),
                'default' => 'enableCC',
                'options' => $options,
            );
        }

        public static function isAdminWcSettingsPage(): bool
        {
            return isset($_GET['page']) && $_GET['page'] === 'wc-settings'
                && isset($_GET['section']) && $_GET['section'] === 'ebizcharge';
        }

        /**
         * Initialize Gateway Settings Form Fields.
         */
        public function init_form_fields()
        {
            $tran = new WC_Gateway_EBizCharge();
            $merchantApiSettings = $tran->getMerchantTransactionData();

            $tran->cvv = $this->cvv;

            if ($merchantApiSettings) {
                $frequenciesList = $tran->getFrequencies();
                $skey_status = "Valid";
                $skey_style = "color: #FFFFFF;background: green;padding: 0px 6px;";
            } else {
                $frequenciesList = 'No frequency found';
                $skey_status = "Invalid";
                $skey_style = "color: #FFFFFF;background: red;padding: 0px 6px;";
            }

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'woothemes'),
                    'label' => __('Enable EBizCharge', 'woothemes'),
                    'type' => 'checkbox',
                    'description' => '',
                    'default' => 'no'
                ),
                'title' => array(
                    'title' => __('Title', 'woothemes'),
                    'type' => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'woothemes'),
                    'default' => __('Credit Card (EBizCharge)', 'woothemes')
                ),
                'description' => array(
                    'title' => __('Description', 'woothemes'),
                    'type' => 'textarea',
                    'description' => __('This controls the description which the user sees during checkout.', 'woothemes'),
                    'default' => 'Pay with your credit card via EBizCharge.'
                ),
                'securityid' => array(
                    'title' => __('API Security ID', 'woothemes'),
                    'type' => 'password',
                    'description' => __('This is the API Security ID generated for your EBizCharge account.</br><strong>Security ID Status: <span style="' . $skey_style . '">' . $skey_status . '</span></strong>', 'woothemes'),
                    'default' => ''
                ),
                'username' => array(
                    'title' => __('API User ID', 'woothemes'),
                    'type' => 'password',
                    'description' => __('This is the API User ID generated for your EBizCharge account.', 'woothemes'),
                    'default' => ''
                ),
                'password' => array(
                    'title' => __('API Password', 'woothemes'),
                    'type' => 'password',
                    'description' => __('This is the API Password generated for your EBizCharge account.', 'woothemes'),
                    'default' => ''
                ),
                'allowedPayments' => $this->getAllowedPayments($merchantApiSettings),
                'salemethod' => array(
                    'title' => __('Sale Method', 'woothemes'),
                    'type' => 'select',
                    'description' => __("Select which sale method to use. Authorize Only pre-authorizes the customer’s card for purchase amount only. Authorize & Capture pre-authorizes the customer's card and collects funds at the same time.", 'woothemes'),
                    'options' => $this->_paymentType,
                    'default' => 'Authorize &amp; Capture'
                ),
                'cardtypes' => array(
                    'title' => __('Accepted Cards', 'woothemes'),
                    'type' => 'multiselect',
                    'description' => __('Select which card types to accept.', 'woothemes'),
                    'default' => '',
                    'options' => array(
                        'Visa' => 'Visa',
                        'MasterCard' => 'MasterCard',
                        'Discover' => 'Discover',
                        'American Express' => 'American Express',
                        //'JCB' => 'JCB'
                    ),
                ),
                'saveCardBin' => array(
                    'title' => __('Enable BinNumber Storage', 'woothemes'),
                    'label' => __('Save card BIN', 'woothemes'),
                    'type' => 'checkbox',
                    'default' => 'no',
                    'description' => __('If checked, card BIN(First 6 numbers) will be saved in the order meta.', 'woothemes')
                ),
                'completecapture' => array(
                    'title' => __('Automatically Capture on Completed Order', 'woothemes'),
                    'label' => __("Automatically capture on completed order", 'woothemes'),
                    'type' => 'checkbox',
                    'default' => 'no',
                    'description' => __('If checked, the payment will be automatically captured when the order status is changed to "Complete".', 'woothemes')
                ),
                'voidOnCancelled' => array(
                    'title' => __('Automatically Void on Cancelled Order', 'woothemes'),
                    'label' => __("Automatically void on cancelled order", 'woothemes'),
                    'type' => 'checkbox',
                    'default' => 'no',
                    'description' => __('If checked, the authorized payment will be automatically voided when the order status is changed to "Cancelled".', 'woothemes')
                ),
                'saveinfo' => array(
                    'title' => __('Allow to Save Card for Future use', 'woothemes'),
                    'type' => 'checkbox',
                    'label' => __("Allow customers to save card information for future use", 'woothemes'),
                    'description' => __("If checked, customers will have the choice to save their card information for future use. 
                            If unchecked, the customer's card information will be saved forcefully. In this case, guest customers will need to sign up to save their card information.", 'woothemes'),
                    'default' => 'yes'
                ),
                'showSavedMethods' => array(
                    'title' => __('Show Saved Payment Methods on Checkout', 'woothemes'),
                    'type' => 'checkbox',
                    'label' => __("Show customer's saved payment methods on checkout page", 'woothemes'),
                    'description' => __("If checked, customer's saved cards will be visible on checkout page.", 'woothemes'),
                    'default' => 'yes'
                ),
                'enableRecurring' => array(
                    'title' => __('Subscriptions', 'woothemes'),
                    'type' => 'checkbox',
                    'label' => __("Enable subscriptions", 'woothemes'),
                    'description' => __('This allows customers to subscribe to products for recurring orders.', 'woothemes'),
                    'default' => 'no'
                ),
                'recurringFrequencies' => array(
                    'title' => __('Allowed Subscription Frequencies', 'woothemes'),
                    'type' => 'multiselect',
                    'description' => __('Select which frequencies to allow for subscription.', 'woothemes'),
                    'default' => '',
                    'options' => $frequenciesList,
                ),
                'econnectTitle' => array(
                    'title' => __('Sync:', 'woothemes'),
                    'label' => __('', 'woothemes'),
                    'type' => 'title',
                    'description' => '',
                    'default' => ''
                ),
                'enableEconnect' => array(
                    'title' => __('EBizCharge Hub Sync', 'woothemes'),
                    'label' => __('Enable EBizCharge Hub Sync', 'woothemes'),
                    'type' => 'checkbox',
                    'description' => 'This allows you to upload your customers, invoices, sales orders, etc. to the EBizCharge hub so they can appear and be accessed in the EBizCharge Customer Payment Portal, mobile app, your ERP or other connected software.
					If this is enabled, any customers, products, or orders created outside of WooCommerce (like in the Customer Payment Portal, mobile app and ERP) can also 
					be downloaded/imported into WooCommerce by clicking the download buttons.',
                    'default' => 'no'
                ),
                'econnectProductOption' => array(
                    'title' => __('Item Source', 'woothemes'),
                    'type' => 'select',
                    'description' => __('Select whether you use WooCommerce as your source for items and need to upload your items to the EBizCharge hub or
					whether you use an ERP as your item source and need to download items from your ERP and import them into WooCommerce.', 'woothemes'),
                    'options' => array(
                        '' => '---select---',
                        'syncItem' => 'WooCommerce',
                        'downloadItems' => 'ERP',
                    ),
                    'default' => ''
                ),

            );
        }

        /**
         * Process the payment and return the result.
         */
        public function process_payment($order_id)
        {
            global $woocommerce, $WC_Gateway_EBizCharge;

            $tran = $this->_initTransaction();
            $order = $tran->getOrderByVersion($order_id);

            $user = new WP_User($order->get_user_id());

            // Convert CC expiration date from (M)M-YYYY to MMYY
            $expmonth = $this->get_post('expmonth');

            $expyear = '';
            if ($this->get_post('expyear') != null) {
                $expyear = substr($this->get_post('expyear'), -2);
            }

            // general payment data
            $tran->command = $this->salemethod;
            $tran->amount = $order->get_total();

            // for card form post
            if (!empty($this->get_post('ccnum'))) {
                $tran->cardholder = $this->get_post('ccholder');
                $tran->card = $this->get_post('ccnum');
                //$cctype = $this->get_post('cardtype');
                $tran->cardtype = $this->get_post('cardtype');
                $tran->exp = $expmonth . $expyear;
                $tran->cvv2 = $this->get_post('cvv');
                $pType = 'cc';
                // 3DS validation fields
                if (!empty($this->get_post('Is3DS2Enabled'))) {
                    $tran->CAVV = $this->get_post('CAVV');
                    $tran->XID = $this->get_post('XID');
                    $tran->ECI = $this->get_post('ECI');
                    $tran->Pares = $this->get_post('Pares');
                    $tran->DSTransactionId = $this->get_post('DSTransactionId');
                    $tran->WCPlaceOrder = $this->get_post('WCPlaceOrder');
                }
            }

            // for ACH form post
            if (!empty($this->get_post('acnum'))) {
                $tran->accountholder = $this->get_post('acholder');
                $tran->accounttype = $this->get_post('actype');
                $tran->account = $this->get_post('acnum');
                $tran->routingnumber = $this->get_post('routingnum');
                $tran->command = 'check';
                $pType = 'check';
            }

            if (!empty($order)) {
                // Generate a new customer vault id for the payment method
                $new_customer_vault_id = false;
                $custId = $user->ec_customer_id ?? $user->ID;
                $orderId = $order->get_id();
                $tran->invoice = $orderId;
                $tran->orderid = $orderId;
                $tran->ponum = $order->get_order_number();
                $tran->ip = $_SERVER['REMOTE_ADDR'];
                $tran->custid = $custId;
                $tran->email = $order->get_billing_email();
                $tran->tax = $order->get_total_tax();
                $tran->shipping = $order->get_shipping_total();
                // avs data
                $tran->street = $order->get_billing_address_1();
                $tran->zip = $order->get_billing_postcode();
                $tran->description = 'Woocommerce order #' . $orderId;
                $payMethod = 'cc';

                // billing info
                if (!empty($order->get_billing_first_name())) {
                    $tran->billfname = $order->get_billing_first_name();
                    $tran->billlname = $order->get_billing_last_name();
                    $tran->billcompany = $order->get_billing_company();
                    $tran->billstreet = $order->get_billing_address_1();
                    $tran->billstreet2 = $order->get_billing_address_2();
                    $tran->billcity = $order->get_billing_city();
                    $tran->billstate = $order->get_billing_state();
                    $tran->billzip = $order->get_billing_postcode();
                    $tran->billcountry = $order->get_billing_country();
                    $tran->billphone = $order->get_billing_phone();
                }

                // shipping info
                if (!empty($order->get_shipping_first_name())) {
                    $tran->shipfname = $order->get_shipping_first_name();
                    $tran->shiplname = $order->get_shipping_last_name();
                    $tran->shipcompany = $order->get_shipping_company();
                    $tran->shipstreet = $order->get_shipping_address_1();
                    $tran->shipstreet2 = $order->get_shipping_address_2();
                    $tran->shipcity = $order->get_shipping_city();
                    $tran->shipstate = $order->get_shipping_state();
                    $tran->shipzip = $order->get_shipping_postcode();
                    $tran->shipcountry = $order->get_shipping_country();
                }

                // line item data
                foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                    $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

                    $sku = $cart_item['data']->get_sku();
                    if (empty($sku)) {
                        $sku = $_product->get_title();
                    }

                    $row_price = wc_get_price_excluding_tax($_product, array('qty' => 1));

                    $prod_description = (!empty($_product->get_short_description()))
                        ? $_product->get_short_description()
                        : $_product->get_description();

                    if (empty($prod_description)) {
                        $prod_description = $_product->get_title();
                    }
                    // for Lineitems
                    $tran->addLine($sku, $_product->get_title(), $prod_description, $row_price, $cart_item['quantity'], $cart_item['line_tax']);
                    // for tokenization
                    $tran->addLineItem($sku, $_product->get_title(), $prod_description, $row_price, $cart_item['quantity'], $cart_item['line_tax']);
                }
            }
            // Create server request using stored or new payment details
            if (empty($CustNum = $user->ec_customer_token)) {
                $CustNum = get_user_meta($user->ID, 'CustNum', true);
            }

            if ($this->get_post('ebizcharge-use-stored-payment-info') == 'yes' && !empty($CustNum)) {
                // 1. for existing customer use saved card and perform RunCustomerTransaction
                $WC_Gateway_EBizCharge->ebiz_log('Order #' . $order_id . ' placed by using saved credit card.');
                //die('existing credit card');
                // Short request, use stored billing details
                $paymentMethodId = $this->get_post('ebizcharge-stored-card');
                $tran->methodID = $paymentMethodId;
                $exp_month_saved = $this->get_post('exp_month_saved');
                $exp_year_saved = $this->get_post('exp_year_saved');
                $tran->exp = $exp_month_saved . substr($exp_year_saved, -2);

                $tran->cardholder = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                $tran->card = $this->get_post('card_number_saved');
                $card_type = $tran->get_card_type($this->get_post('card_type_saved'));
                $tran->cardtype = $card_type;
                $tran->cvv2 = $this->get_post('cvv_saved');

                $base_request['customer_vault_id'] = $user->user_login;
                $base_request['billing_id'] = $paymentMethodId;
                $base_request['ver'] = 2;
                $response['response'] = $tran->updateProcess($CustNum, $paymentMethodId);

            } else if ($this->get_post('ebizcharge-use-stored-payment-info') == 'update' && !empty($CustNum)) {
                // update payment method
                $WC_Gateway_EBizCharge->ebiz_log('Order #' . $order_id . ' placed by using update credit card.');

                $paymentMethodId = $this->get_post('ebizcharge_update_cc');
                $tran->methodID = $paymentMethodId;

                $exp_month_update = $this->get_post('exp_month_update');
                $exp_year_update = $this->get_post('exp_year_update');
                $tran->exp = $exp_month_update . substr($exp_year_update, -2);

                $tran->street = $this->get_post('avs_street');
                $tran->zip = $this->get_post('avs_zip');

                $tran->cardholder = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                $tran->card = $this->get_post('card_number_update');
                $card_type = $tran->get_card_type($this->get_post('card_type_update'));
                $tran->cardtype = $card_type;
                $tran->cvv2 = $this->get_post('cvv_update');

                $base_request['customer_vault_id'] = $user->user_login;
                $base_request['billing_id'] = $paymentMethodId;
                $base_request['ver'] = 2;
                $response['response'] = $tran->updateProcess($CustNum, $paymentMethodId);

            } elseif ($this->get_post('ebizcharge-use-stored-bank-info') == 'yes' && !empty($CustNum)) {
                // 2. for existing customer use saved ACH and perform RunCustomerTransaction
                $WC_Gateway_EBizCharge->ebiz_log('Order #' . $order_id . ' placed by using saved bank account.');
                $tran->command = 'check';
                $payMethod = 'check';
                $paymentMethodId = $this->get_post('ebizcharge-stored-bank');
                $tran->methodID = $paymentMethodId;

                $tran->accountholder = $this->get_post('ac_holder_saved');
                $tran->accounttype = $tran->get_card_type($this->get_post('ac_type_saved'));
                $tran->account = $this->get_post('ac_number_saved');
                $tran->routingnumber = $this->get_post('routing_saved');

                $base_request['customer_vault_id'] = $user->user_login;
                $base_request['billing_id'] = $paymentMethodId;
                $base_request['ver'] = 2;
                $response['response'] = $tran->savedProcess($CustNum, $paymentMethodId);
            } else {
                if ($pType == 'cc' && $tran->Is3DS2Enabled() && (empty($tran->WCPlaceOrder))) {
                    $order->add_order_note(__('Payment is not Authorized by 3D Secure.', 'woocommerce'));
                    wc_add_notice(__('Payment is not Authorized by 3D Secure.', 'ebizcharge'), 'error');
                    return false;
                }

                $saveCardInfo = $this->get_post('saveinfo');
                $create_account = $this->account_creation_possible();
                if (!$create_account && !empty($CustNum)) {
                    // 3. for existing customer without save payment method to perform RunTransaction
                    // 4. for existing customer with save payment method to perform RunCustomerTransaction
                    //die('IF NewPaymentProcess '.$CustNum);
                    $response['response'] = $tran->NewPaymentProcess($CustNum, $user, $saveCardInfo);
                    $new_customer_vault_id = $tran->methodID;
                } else if (($create_account && $saveCardInfo) || ($user->ID && empty($CustNum))) {
                    // 5. for new customer with save payment method to perform RunCustomerTransaction
                    //die('IF TokenProcess'); //OK
                    $response['response'] = $tran->TokenProcess($user, $saveCardInfo);
                    if ($response['response']) {
                        //update_user_meta( $user->ID, 'CustNum', $tran->custnum );
                        $new_customer_vault_id = $tran->methodID;
                    }
                } else {
                    // 6. for guest customer without save payment method to perform RunTransaction
                    // Send request and get response from server
                    $response['response'] = $tran->RunTransaction();
                }
                // Full request, new customer or new information
                $base_request = array(
                    'ccnumber' => $this->get_post('ccnum'),
                    'cvv' => $this->get_post('cvv'),
                    'ccexp' => $expmonth . $expyear,
                    'firstname' => $order->get_billing_first_name(),
                    'lastname' => $order->get_billing_last_name(),
                    'address1' => $order->get_billing_address_1(),
                    'city' => $order->get_billing_city(),
                    'state' => $order->get_billing_state(),
                    'zip' => $order->get_billing_postcode(),
                    'country' => $order->get_billing_country(),
                    'phone' => $order->get_billing_phone(),
                    'email' => $order->get_billing_email(),
                );

                // If "save billing data" box is checked or order is a subscription, also request storage of customer payment information.
                if (($this->get_post('saveinfo') || $this->is_subscription($order)) && !empty($new_customer_vault_id)) {

                    $base_request['customer_vault'] = 'add_customer';
                    // Set customer ID for new record
                    $base_request['customer_vault_id'] = $new_customer_vault_id;
                    //$base_request['customer_vault_id'] = $tran->methodID;

                    // Set 'recurring' flag for subscriptions
                    if ($this->is_subscription($order)) {
                        $base_request['billing_method'] = 'recurring';
                    }
                }
            }
            // Check response -- transaction processed successfully.
            if ($tran->resultcode == 'A') {

                if (!empty($tran->refnum)) {

                    $payType = ($tran->command === 'sale' || $tran->command === 'check')
                        ? WC_Gateway_EBizCharge_Admin::PAYMENT_STATUS_CAPTURED
                        : WC_Gateway_EBizCharge_Admin::PAYMENT_STATUS_AUTHORIZED;

                    $cc = null; // for surcharge only
                    if (empty($paymentMethodId = $tran->methodID)) {
                        $paymentMethodId = 0;
                        $cc = $tran->card; // use cc when saved method is not used
                    }

                    if (!empty($tran->card)) {

                        //WOO-808: recheck the surcharge setting and don't rely on post data
                        $surchargeSettings = $tran->getSurchargeSettings();
                        $surchargeEnabled = $surchargeSettings->IsSurchargeEnabled ?? false;

                        $order->update_meta_data('_ebiz_surcharge_enabled', $surchargeEnabled);

                        if (!empty($surchargeEnabled)) {
                            // recalculate the surcharge and don't rely on post data
                            $surcharge = $tran->calculateSurchargeAmount($tran->amount, $tran->zip, $user, $paymentMethodId, $cc);
                            $surchargePercentage = $surchargeSettings->SurchargePercentage;
                            $surchargeAmount = $surcharge->SurchargeAmount ?? 0;

                            $tran->ebiz_log('Surcharge is enabled for order_id: ' . $order_id . ' ,Surcharge amount: ' . $surchargeAmount
                                . ' ,Percentage: ' . $surchargePercentage . ' ,Zip: ' . $tran->zip . ' , User ID: '. $user->ID
                                . ', Transaction# '. $tran->refnum . ' , MethodId: ' . $paymentMethodId . ' ,CC: ' . substr($tran->card, 12, 16));

                            $order->update_meta_data('_ebiz_surcharge_caption', $this->get_post('surcharge_caption'));
                            $order->update_meta_data('_ebiz_surcharge_percentage', $surchargePercentage);
                            $order->update_meta_data('_ebiz_surcharge_amount', $surchargeAmount);

                            // if amount is Captured(not Authorized), add surcharge info in the notes.
                            if ($payType === WC_Gateway_EBizCharge_Admin::PAYMENT_STATUS_CAPTURED) {
                                $totalAmount = wc_price((float)$tran->amount + (float)$surchargeAmount);
                                $tran->addSurchargeInNotes($order, $tran->amount, $totalAmount, $surchargePercentage, $surchargeAmount);
                            }
                        }

                        $order->update_meta_data('_card_holder', $tran->cardholder);
                        $order->update_meta_data('_card_number', 'XXXXXXXXXXXX' . substr($tran->card, 12, 16));
                        $order->update_meta_data('_card_expiry', $tran->exp);
                        $order->update_meta_data('_card_type', $tran->cardtype);

                        if ($tran->saveCardBinEnabled()) {
                            $order->update_meta_data('_card_bin', substr($tran->card, 0, 6));
                        }
                    }

                    if (!empty($tran->account)) {
                        $order->update_meta_data('_account_holder', $tran->accountholder);
                        $order->update_meta_data('_account_type', $tran->accounttype);
                        $order->update_meta_data('_account_number', 'XXXXX' . substr($tran->account, -4));
                        $order->update_meta_data('_routing_number', 'XXXXX' . substr($tran->routingnumber, -4));

                        $payMethod = 'check';
                    }

                    $CustNum = get_user_meta($user->ID, 'CustNum', true);
                    if (empty($paymentMethodId = $tran->methodID)) {
                        $paymentMethodId = 0;
                    }

                    $tranMetaKey = "[EBIZCHARGE]|methodid|refnum|authcode|avsresultcode|cvv2resultcode|woocommerceorderid|paymethod|webcustomerid";
                    $tranMetaValue = "[EBIZCHARGE]" . "|" . $paymentMethodId . "|" . $tran->refnum . "|" . $tran->authcode . "|" .
                        $tran->avs_result_code . "|" . $tran->cvv2_result_code . "|" . $orderId . "|" . $payMethod . "|" . $tran->custid;

                    // WOO-644: add ebizcharge meta in json format
                    $ebizMeta = [
                        'woocommerce_order_id' => $orderId,
                        'customer_id' => $tran->custid,
                        'payment_method_id' => $paymentMethodId,
                        'payment_method_type' => $payMethod,
                        'refnum' => $tran->refnum,
                        'authcode' => $tran->authcode,
                        'avs_result_code' => $tran->avs_result_code,
                        'cvv2_result_code' => $tran->cvv2_result_code,
                    ];

                    $order->update_meta_data('_ebizcharge_meta', $ebizMeta);

                    $order->update_meta_data('_payment_amount', $tran->amount);
                    $order->set_transaction_id($tran->refnum);
                    $order->update_meta_data('_payment_status', $payType);
                    $order->update_meta_data('_payment_method_id', $paymentMethodId);
                    $order->update_meta_data('_ebiz_payment_type', $payMethod);
                    $order->update_meta_data($tranMetaKey, $tranMetaValue);
                    //$order->update_meta_data('_order_total', $tran->amount);
                }
                // Success
                $order->add_order_note(__('EBizCharge payment ' . $payType . '. Transaction ID: ', 'woocommerce') . $tran->refnum);
                //$order->update_status('wc-processing');
                $order->payment_complete(); // why this is removed?

                if ($this->get_post('ebizcharge-use-stored-payment-info') == 'yes') {

                    if ($this->is_subscription($order)) {
                        // Store payment method number for future subscription payments
                        $order->update_meta_data('payment_method_number', $this->get_post('ebizcharge-stored-card'));
                    }

                } else if ($this->get_post('ebizcharge-use-stored-bank-info') == 'yes') {

                    if ($this->is_subscription($order)) {
                        // Store payment method number for future subscription payments
                        $order->update_meta_data('payment_method_number', $this->get_post('ebizcharge-stored-bank'));
                    }

                } else if ($this->get_post('saveinfo') || $this->is_subscription($order)) {

                    if (!empty($new_customer_vault_id)) {
                        $customer_vault_ids = get_user_meta($user->ID, 'customer_vault_ids', true);

                        if (!empty($customer_vault_ids)) {
                            $customer_vault_ids[] = $new_customer_vault_id;
                        } else {
                            $customer_vault_ids = array($new_customer_vault_id);
                        }

                        update_user_meta($user->ID, 'customer_vault_ids', $customer_vault_ids);
                    }

                    if ($this->is_subscription($order)) {
                        // Store payment method number for future subscription payments
                        $order->update_meta_data('payment_method_number', count($customer_vault_ids) - 1);
                    }
                }

                $econnect = $this->_initTransaction(true);
                if ($econnect->enableEconnect) {
                    $econnect->syncOrder($orderId);
                    $econnect->syncInvoice($orderId);
                }

                // Return thank you redirect
                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order),
                );

            } else if ($tran->resultcode == 'E') { // Error
                // Other transaction error
                $order->add_order_note(__('EBizCharge payment failed. Error: ', 'woocommerce') . $tran->error);
                wc_add_notice(__('(Transaction Error) ' . $tran->error, 'ebizcharge'), 'error');
                $order->update_status('wc-failed');
            } else {
                $errormsg = isset($tran->error) ? $tran->error : $response['response'];
                // No response or unexpected response
                $order->add_order_note(__("EBizCharge payment failed. ", 'woocommerce') . $errormsg);
                wc_add_notice(__('(Transaction Error) ' . $errormsg, 'ebizcharge'), 'error');
                $order->update_status('wc-failed');
            }
        }

        /**
         * Check if the user has any billing records in the Customer Vault
         */
        public function user_has_stored_data($user_id)
        {
            return get_user_meta($user_id, 'customer_vault_ids', true) != null;
        }

        /**
         * Update a stored billing record with new CC number and expiration
         */
        public function update_payment_method($paymentMethodId, $ccexp, $avsStreet, $avsZip)
        {
            global $woocommerce;

            $user = wp_get_current_user();
            $customerToken = get_user_meta($user->ID, 'CustNum', true);

            $tran = $this->_initTransaction();
            $tran->street = $avsStreet;
            $tran->zip = $avsZip;
            $ccexp = substr($ccexp, 0, 2) . substr($ccexp, -2);

            if ($tran->updatePaymentMethod($customerToken, $paymentMethodId, $ccexp)) {
                wc_add_notice(__('The selected payment method has been updated successfully!', 'ebizcharge'), 'success');
            } else {
                wc_add_notice(__('Sorry, The selected payment method update has been failed. ', 'ebizcharge'), 'error');
            }
            wc_print_notices();

            $this->javascript_pmethod_success('cc');
        }

        public function getRecurringRecordsFromDb($pMethod, $status, $user_id)
        {
            global $wpdb;
            $table_name = $wpdb->prefix . 'ebizcharge_recurring';
            $sql = $wpdb->prepare("select * from " . $table_name . " where rec_pmethod_id='%d' and rec_status='%d' and customer_id='%d'", $pMethod, $status, $user_id);
            return $wpdb->get_results($sql);
        }

        public function deleteRecurringByPmethod($paymentMethodId, $userId)
        {
            $Susbscriptions_Econnect = new Susbscriptions_Econnect();
            $tran = $this->_initTransaction();
            $recurrings = $this->getRecurringRecordsFromDb($paymentMethodId, 0, $userId);
            $payment_internal_id = array();

            if (!empty($recurrings)) {
                foreach ($recurrings as $recurring) {
                    $payment_internal_id[] = $recurring->rec_scheduled_payment_internal_id;
                }

                $deleted = $Susbscriptions_Econnect->suspendDeleteBulkSubscription($payment_internal_id, 3);
                if ($deleted) {
                    $tran->ebiz_log('These subscription(s) are deleted against payment method ' . $paymentMethodId);
                    $tran->ebiz_log(print_r($payment_internal_id, true));
                    return true;
                }
            } else {
                $tran->ebiz_log('No subscription found against payment method ' . $paymentMethodId);
            }
            return false;
        }

        /**
         * Delete a stored billing method
         */
        public function delete_payment_method($paymentMethodId, $method_type)
        {
            global $woocommerce, $WC_Gateway_EBiz_Rec;
            $user = wp_get_current_user();
            $customer_vault_ids = get_user_meta($user->ID, 'customer_vault_ids', true);

            try {
                $tran = $this->_initTransaction();
                $client = new SoapClient($tran->_getWsdlUrl(), $tran->SoapParams());
                $CustNum = get_user_meta($user->ID, 'CustNum', true);
                $deletePayment = $client->deleteCustomerPaymentMethodProfile(
                    array(
                        'securityToken' => $tran->_getUeSecurityToken(),
                        'customerToken' => $CustNum,
                        'paymentMethodId' => $paymentMethodId,
                    ));

                $response['response'] = $deletePayment->DeleteCustomerPaymentMethodProfileResult;

                if ($response['response'] == 1) {
                    // will check subscriptions here to delete against this payment method
                    if ($tran->is_recurring_enabled()) {
                        $this->deleteRecurringByPmethod($paymentMethodId, $user->ID);
                    }
                } else {
                    wc_add_notice(__('Sorry, there was an error:', 'ebizcharge'), 'error');
                    wc_print_notices();
                    return;
                }

            } catch (Exception $e) {
                wc_add_notice(__('Sorry, The selected payment method not deleted: ' . $e->getMessage(), 'ebizcharge'), 'error');
                wc_print_notices();
                return;
            }

            //$last_method = count($customer_vault_ids) - 1;
            $last_method = count(array($customer_vault_ids)) - 1;
            // Update subscription references
            if (class_exists('WC_Subscriptions_Manager')) {
                foreach (( array )(WC_Subscriptions_Manager::get_users_subscriptions($user->ID)) as $subscription) {
                    $subscription_payment_method = get_post_meta($subscription['order_id'], 'payment_method_number', true);
                    // Cancel subscriptions that were purchased with the deleted method
                    if ($subscription_payment_method == $paymentMethodId) {
                        delete_post_meta($subscription['order_id'], 'payment_method_number');
                        WC_Subscriptions_Manager::cancel_subscription($user->ID, WC_Subscriptions_Manager::get_subscription_key($subscription['order_id']));
                    } else if ($subscription_payment_method == $last_method && $subscription['status'] != 'cancelled') {
                        update_post_meta($subscription['order_id'], 'payment_method_number', $paymentMethodId);
                    }
                }
            }

            // if payment method found in $customer_vault_ids, delete it
            if (($key = array_search($paymentMethodId, $customer_vault_ids)) !== false) {
                unset($customer_vault_ids[$key]);
            }

            update_user_meta($user->ID, 'customer_vault_ids', $customer_vault_ids);

            $reload = "<a class='payment-reload' href='" . $WC_Gateway_EBiz_Rec->getPermalinkUrl() . "'> Reload</a>";
            wc_add_notice(__('The selected payment method has been deleted successfully! ' . $reload, 'ebizcharge'), 'success');
            wc_print_notices();

            $this->javascript_pmethod_success($method_type == 'check' ? 'bank' : 'cc');

        }

        public function getCustomerData($userID)
        {
            return array(
                'CustomerId' => $userID,
                'FirstName' => get_user_meta($userID, 'first_name', true),
                'LastName' => get_user_meta($userID, 'last_name', true),
                'CompanyName' => get_user_meta($userID, 'billing_company', true),
                'Phone' => get_user_meta($userID, 'billing_phone', true),
                'CellPhone' => get_user_meta($userID, 'billing_phone', true),
                'Fax' => '',
                'Email' => get_user_meta($userID, 'billing_email', true),
                'WebSite' => '',
                'BillingAddress' => array(
                    'FirstName' => get_user_meta($userID, 'billing_first_name', true),
                    'LastName' => get_user_meta($userID, 'billing_last_name', true),
                    'CompanyName' => get_user_meta($userID, 'billing_company', true),
                    'Address1' => get_user_meta($userID, 'billing_address_1', true),
                    'Address2' => get_user_meta($userID, 'billing_address_2', true),
                    'City' => get_user_meta($userID, 'billing_city,', true),
                    'State' => get_user_meta($userID, 'billing_state', true),
                    'ZipCode' => get_user_meta($userID, 'billing_postcode', true),
                    'Country' => get_user_meta($userID, 'billing_country', true),
                    'Phone' => get_user_meta($userID, 'billing_phone', true),
                    'Email' => get_user_meta($userID, 'billing_email', true),
                ),
                'ShippingAddress' => array(
                    'FirstName' => get_user_meta($userID, 'shipping_first_name', true),
                    'LastName' => get_user_meta($userID, 'shipping_last_name', true),
                    'CompanyName' => get_user_meta($userID, 'shipping_company', true),
                    'Address1' => get_user_meta($userID, 'shipping_address_1', true),
                    'Address2' => get_user_meta($userID, 'shipping_address_2', true),
                    'City' => get_user_meta($userID, 'shipping_city,', true),
                    'State' => get_user_meta($userID, 'shipping_state', true),
                    'ZipCode' => get_user_meta($userID, 'shipping_postcode', true),
                    'Country' => get_user_meta($userID, 'shipping_country', true),
                ),
                'SoftwareId' => 'woocommerce'
            );
        }

        public function sendInvoiceEmail($transactionRefNum = null, $receiptRefNum = null, $email = null)
        {
            $WCGatewayEBizRec = new WC_Gateway_EBizCharge_Recurring();

            if (isset($transactionRefNum)) {
                $emailReceiptResult = $WCGatewayEBizRec->emailReceipt($transactionRefNum, $email);
                if ($emailReceiptResult) {
                    echo 1;
                    die;
                }

            } else {
                echo 0;
            }
        }

        public function getInvoicePrint($transactionRefNum = null, $receiptRefNum = null)
        {
            $WCGatewayEBizRec = new WC_Gateway_EBizCharge_Recurring();

            if (isset($transactionRefNum)) {
                $response = $WCGatewayEBizRec->printReceiptData($transactionRefNum);
                echo !empty($response) ? base64_decode($response) : '';
            }
            return 0;
        }

        public function getPaymentHistory($customerId = null, $start = 0, $limit = 0)
        {
            global $wpdb, $WC_Gateway_EBizCharge, $WC_Gateway_EBiz_Rec;
            $tran = $this->_initTransaction();

            try {

                $filterClerk = array(
                    'FieldName' => 'Clerk',
                    'ComparisonOperator' => 'eq',
                    'FieldValue' => 'Recurring'
                );
                $filterStart = array(
                    'FieldName' => 'created',
                    'ComparisonOperator' => 'gt',
                    'FieldValue' => '2021-11-15'
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

                $response = $WC_Gateway_EBiz_Rec->getListSearchTransactions($searchFilters, $start, $limit);

                if (!isset ($response->SearchTransactionsResult->Transactions->TransactionObject)) {
                    $recurringDetail = array();
                } elseif (
                    (is_array($response->SearchTransactionsResult->Transactions->TransactionObject))
                    &&
                    (count($response->SearchTransactionsResult->Transactions->TransactionObject))
                    > 1) {
                    $recurringDetail = $response->SearchTransactionsResult->Transactions->TransactionObject;
                } else {
                    $recurringDetail[] = $response->SearchTransactionsResult->Transactions->TransactionObject;
                }
                return $recurringDetail;

            } catch (Exception $ex) {
                return [];
            }
        }

        public function add_new_subscription()
        {
            global $wpdb, $WC_Gateway_EBizCharge, $WC_Gateway_EBiz_Rec;
            $WC_Gateway_EBizCharge->ebiz_log(__METHOD__);

            try {
                /*my function start here */
                $customerId = !empty($this->get_post('customer_id')) ? $this->get_post('customer_id') : '';

                if (!empty($this->get_post('method_id'))) {
                    $paymentMethodId = $this->get_post('method_id');
                }

                $paymentMethodName = '';

                $productId = $this->get_post('selectdivProduct');

                $addNewForm = $this->get_post('payment');

                if (!empty($this->get_post('payment_method_name'))) {
                    $paymentMethodName = $this->get_post('payment_method_name');
                }

                $customerInternalId = $this->get_customer_internalid($customerId);

                if ((isset($addNewForm['ebiz_option_payment'])) and $addNewForm['ebiz_option_payment'] == 'new') {

                    if ($addNewForm['ebzc_option_type'] == 'credit_card') {
                        list($paymentMethodId, $paymentMethodName) = $this->addNewPaymentMethod($customerInternalId, $addNewForm);
                    }

                    if ($addNewForm['ebzc_option_type'] == 'ACH') {
                        list($paymentMethodId, $paymentMethodName) = $this->addNewPaymentAccount($customerInternalId, $addNewForm);
                    }
                } elseif ((isset($addNewForm['ebiz_option_payment'])) and $addNewForm['ebiz_option_payment'] == 'update') {

                    if ($addNewForm['ebzc_option_type'] == 'credit_card') {
                        // update payment method here
                        $custNum = get_user_meta($customerId, 'CustNum', true);
                        $WC_Gateway_EBizCharge->street = $this->get_post('avs_street');
                        $WC_Gateway_EBizCharge->zip = $this->get_post('avs_zip');
                        $WC_Gateway_EBizCharge->updatePaymentMethod($custNum, $paymentMethodId);
                        $WC_Gateway_EBizCharge->ebiz_log('add-update-' . $paymentMethodName . '-' . $paymentMethodId . '-' . $customerId . '-' . $custNum . '-' . $this->get_post('avs_street') . '-' . $this->get_post('avs_zip'));
                    }

                }

                /*---------------------*/

                $product = wc_get_product($productId);

                $shippingMethod = !empty($this->get_post('shipping_method')) ? $this->get_post('shipping_method') : 0;
                $shippingAmount = !empty($this->get_post('shipping_amount')) ? $this->get_post('shipping_amount') : 0;
                $productPrice = $product->get_price();
                $productName = $product->get_title();
                $productId = $product->get_id();
                $amountGross = ($productPrice * (int)$this->get_post('qty'));
                $amount = number_format($amountGross, 2, '.', '');
                $coupon = $this->get_post('coupon') ? $this->get_post('coupon') : '';
                $schedule = $this->get_post('schedule');
                $recIndefinitely = !empty($this->get_post('rec_indefinitely')) ? 1 : 0;
                $qty = $this->get_post('qty');
                $start = !empty($this->get_post('start_date')) ? $this->get_post('start_date') : '';
                $expire = !empty($this->get_post('expire_date')) ? $this->get_post('expire_date') : '';
                $scheduleName = 'WooCommerce-' . $productId . '-' . $customerId . '-' . $schedule . '-' . $paymentMethodId . '-' . $qty;
                $amountDiscounted = '';

                if (!empty($coupon)) {
                    $amountDiscounted = $this->checkCouponCodeDiscount($amount, $coupon, $customerId, $productId);

                    if ($amountDiscounted > 0 && $amount > $amountDiscounted) {
                        $finalAmount = $amountDiscounted;
                    } else {
                        echo $this->getErrorMessage('Sorry, This coupon code is not valid for this subscription. Final amount after discount should be greater than zero.');
                        $finalAmount = $amount;
                        return false;
                    }
                } else {
                    $finalAmount = $amount;
                }

                if (empty($qty)) {
                    $qty = 1;
                }

                if (!empty($start)) {
                    $start = date("Y-m-d", strtotime($start));
                    $startDate = strtotime($start);
                }

                if ($recIndefinitely == 1) {
                    $expire = date('Y-m-d', strtotime('+10 years', $startDate));
                } else {
                    $recIndefinitely = 0;

                    if (!empty($expire)) {
                        $expire = date("Y-m-d", strtotime($expire));
                    } else {
                        $expire = date('Y-m-d', strtotime('+10 years', $startDate));
                    }
                }

                $recurringBilling = array(
                    'Amount' => $finalAmount,
                    'Enabled' => true,
                    'Start' => $start,
                    'Expire' => $expire,
                    'Next' => $expire,
                    'Schedule' => $schedule,
                    'ScheduleName' => $scheduleName,
                    'ReceiptNote' => 'Item [' . $productId . '-' . $productName . '] recurring payment added.',
                    'ReceiptTemplateName' => false,
                    'SendCustomerReceipt' => true
                );
                $addRecurrParameters = array(
                    'securityToken' => '',
                    'customerInternalId' => $customerInternalId,
                    'paymentMethodProfileId' => $paymentMethodId,
                    'recurringBilling' => $recurringBilling
                );

                $scheduledPaymentInternalId = $WC_Gateway_EBiz_Rec->addScheduleRecurringPaymentToGateway($addRecurrParameters);

                $recurringDates = $WC_Gateway_EBiz_Rec->getRecurringScheduledDates($scheduledPaymentInternalId);
                $ebzRecurringTotal = count($recurringDates);
                $nextRecDate = $WC_Gateway_EBiz_Rec->getNextRecurringDate($recurringDates);

                $billing_address = $this->get_post('addressBill');
                $shipping_address = $this->get_post('addressShip');

                $methodName = $WC_Gateway_EBizCharge->get_payment_method_details($paymentMethodId, $customerId);

                $product_parent_id = $product->get_parent_id();

                if (empty($product_parent_id) || $product_parent_id == 0) {
                    $item_id = $productId;
                    $item_variation_id = 0;
                } else {
                    $item_id = $product_parent_id;
                    $item_variation_id = $productId;
                }

                $tableName = $wpdb->prefix . 'ebizcharge_recurring';
                $data = array(
                    'rec_status' => 0,
                    'rec_indefinitely' => $recIndefinitely,
                    'customer_id' => $customerId,
                    'order_id' => '0',
                    'item_id' => $item_id,
                    'item_variation_id' => $item_variation_id,
                    'item_name' => $productName,
                    'rec_item_qty' => $qty,
                    'rec_start_date' => $start,
                    'rec_end_date' => $expire,
                    'rec_frequency' => $schedule,
                    'rec_pmethod_id' => $paymentMethodId,
                    'rec_pmethod_name' => $methodName,
                    'rec_scheduled_payment_internal_id' => $scheduledPaymentInternalId,
                    'rec_total' => $ebzRecurringTotal,
                    'rec_processed' => 0,
                    'rec_next_due' => $nextRecDate,
                    'rec_remaining' => $ebzRecurringTotal,
                    'billing_address' => $billing_address,
                    'shipping_address' => $shipping_address,
                    'rec_coupon' => $coupon,
                    'rec_amount' => $finalAmount,
                    'rec_shipping_method' => $shippingMethod,
                    'rec_shipping_amount' => $shippingAmount,
                    'rec_shipping_tax' => 0,
                    'rec_failed_attempts' => 0,
                    'created_at' => date("Y-m-d h:i:s"),
                    'updated_at' => date("Y-m-d h:i:s")
                );
                $wpdb->insert($tableName, $data);
                $recurringId = $wpdb->insert_id;
                //echo $wpdb->last_error;
                // Add new recurring dates
                return $WC_Gateway_EBiz_Rec->insertRecurringDatesToDb($recurringId, $recurringDates);
            } catch (Exception $exception) {
                $WC_Gateway_EBizCharge->ebiz_log(__METHOD__ . " " . $exception->getMessage(), 'critical');
                echo $this->getErrorMessage('Sorry, Subscription not saved. ' . $exception->getMessage());
                return;
            }
        }

        public function update_subscription()
        {
            global $woocommerce, $wpdb, $WC_Gateway_EBizCharge, $WC_Gateway_EBiz_Rec;
            try {
                $tran = $this->_initTransaction();
                $securityToken = $tran->_getUeSecurityToken();

                $addNewForm = $this->get_post('payment');
                $recurringId = $this->get_post('table_rec_id');
                $custId = $this->get_post('custId');
                $custIntId = $this->get_post('custIntId');
                $methodId = $this->get_post('method_id');
                $oldMethodId = $this->get_post('eb_rec_method_id');
                $shippingId = trim($this->get_post('addressShip'));
                $billingId = trim($this->get_post('addressBill'));
                $coupon = $this->get_post('coupon') ? $this->get_post('coupon') : '';
                $productId = $this->get_post('item_id');
                $schedulePaymentInternalId = $this->get_post('mid');
                $paymentMethodName = '';
                $amountDiscounted = '';

                /********** recurring payment methods start ************/

                if ($addNewForm['ebzc_option_type'] == 'credit_card') {

                    if ($addNewForm['ebiz_option_payment'] == 'new') {

                        $cardExpiration = $addNewForm['cc_exp_year'] . "-" . $addNewForm['cc_exp_month'];
                        $cardType = $addNewForm['cc_type'];
                        $cardCode = $addNewForm['cc_cid'] ?? '';

                        $paymentMethodName = $cardType . ' ' . substr($addNewForm['cc_number'], -4) . ' - ' . $addNewForm['cc_owner'];
                        $paymentParameters = array(
                            'MethodName' => $paymentMethodName,
                            'AccountHolderName' => $addNewForm['cc_owner'],
                            'SecondarySort' => 1,
                            'Created' => date('Y-m-d\TH:i:s'),
                            'Modified' => date('Y-m-d\TH:i:s'),
                            'CardCode' => $cardCode,
                            'CardExpiration' => $cardExpiration,
                            'CardNumber' => $addNewForm['cc_number'],
                            'CardType' => $addNewForm['cc_type'],
                            'Balance' => 0,
                            'MaxBalance' => 0,
                            'AvsStreet' => $addNewForm['avs_street'] ?? '',
                            'AvsZip' => $addNewForm['avs_zip'] ?? ''
                        );

                        $methodId = $this->addCustomerPaymentMethod($custIntId, $paymentParameters);

                    } elseif ($addNewForm['ebiz_option_payment'] == 'saved') {
                        $paymentMethodName = $this->get_post('payment_method_name');
                        $methodId = $this->get_post('method_id');
                        $WC_Gateway_EBizCharge->ebiz_log('edit-saved-' . $paymentMethodName . "-" . $methodId);
                    } elseif ($addNewForm['ebiz_option_payment'] == 'update') {
                        $paymentMethodName = $this->get_post('payment_method_name');
                        $methodId = $this->get_post('method_id');
                        // update payment method here
                        //$custId = $this->get_post('custId');
                        $custNum = get_user_meta($custId, 'CustNum', true);
                        $WC_Gateway_EBizCharge->street = $this->get_post('avs_street');
                        $WC_Gateway_EBizCharge->zip = $this->get_post('avs_zip');
                        $WC_Gateway_EBizCharge->updatePaymentMethod($custNum, $methodId);
                        $WC_Gateway_EBizCharge->ebiz_log('edit-update-' . $paymentMethodName . '-' . $methodId . '-' . $custId . '-' . $custNum . '-' . $this->get_post('avs_street') . '-' . $this->get_post('avs_zip'));
                    }

                } elseif ($addNewForm['ebzc_option_type'] == 'ACH') {

                    if ($addNewForm['ebiz_option_payment'] == 'new') {
                        $methodOfPayment = $this->addNewPaymentAccount($custIntId, $addNewForm);
                        $methodId = isset($methodOfPayment[0]) ? $methodOfPayment[0] : '';
                        $paymentMethodName = isset($methodOfPayment[1]) ? $methodOfPayment[1] : 'NA';

                    } elseif ($addNewForm['ebiz_option_payment'] == 'saved') {
                        $methodId = $this->get_post('method_id');
                        $paymentMethodName = $this->get_post('payment_method_name');
                    }

                } else {
                    return 4; //Unable to add payment method.
                }
                /************** recurring payment methods end *************/
                $product = wc_get_product($productId);
                $shippingMethod = !empty($this->get_post('shipping_method')) ? $this->get_post('shipping_method') : 0;
                $shippingAmount = !empty($this->get_post('shipping_amount')) ? $this->get_post('shipping_amount') : 0;
                $productPrice = $product->get_price();
                $amountGross = ($productPrice * $this->get_post('qty'));
                $amount = number_format((float)$amountGross, 2, '.', '');

                if (!empty($coupon)) {
                    $amountDiscounted = $this->checkCouponCodeDiscount($amount, $coupon, $custId, $productId);

                    if ($amountDiscounted > 0 && $amount > $amountDiscounted) {
                        $finalAmount = $amountDiscounted;
                    } else {
                        echo $this->getErrorMessage('Sorry, This coupon code is not valid for this subscription. Final amount after discount should be greater than zero.');
                        $finalAmount = $amount;
                        return false;
                    }
                } else {
                    $finalAmount = $amount;
                }

                if ($schedulePaymentInternalId) {
                    $recIndefinitely = !empty($this->get_post('rec_indefinitely')) ? 1 : 0;
                    $schedule = $this->get_post('schedule');
                    $rec_indefinitely = $this->get_post('rec_indefinitely');
                    $qty = $this->get_post('qty');
                    $start = $this->get_post('start_date');
                    $expire = $this->get_post('expire_date');
                    $scheduleName = $this->get_post('schedulename');
                    $receiptNote = $this->get_post('receiptnote');

                    if (empty($qty)) {
                        $qty = 1;
                    }

                    if (!empty($start)) {
                        $start = date("Y-m-d", strtotime($start));
                        $startDate = strtotime($start);
                    }

                    if ($recIndefinitely == 1) {
                        $expire = date('Y-m-d', strtotime('+10 years', $startDate));
                    } else {
                        $recIndefinitely = 0;

                        if (!empty($expire)) {
                            $expire = date("Y-m-d", strtotime($expire));
                        } else {
                            $expire = date('Y-m-d', strtotime('+10 years', $startDate));
                        }
                    }

                    $paymentMethodProfileStatus = 1;

                    if (!empty($methodId) && $oldMethodId != $methodId) {
                        $paymentMethodProfileStatus = $WC_Gateway_EBiz_Rec->modifyRecurringPaymentMethod($methodId, $schedulePaymentInternalId);
                        $newMethodID = $methodId;
                    }

                    $sNames = explode("-", $scheduleName);
                    $sNames[5] = $newMethodID ?? '';
                    $sNames[6] = $qty ?? '';
                    $scheduleNameUpdated = $sNames[0] . '-' . $sNames[1] . '-' . $sNames[2] . '-' . $sNames[3] . '-' . $sNames[4] . '-' . $sNames[5] . '-' . $sNames[6];

                    $methodName = $WC_Gateway_EBizCharge->get_payment_method_details($methodId, $_REQUEST['custId']);

                    $recurringBilling = array(
                        'Amount' => $finalAmount,
                        'Enabled' => true,
                        'Start' => trim($start),
                        'Expire' => trim($expire),
                        'Next' => trim($expire),
                        'Schedule' => trim($schedule),
                        'ScheduleName' => trim($scheduleNameUpdated),
                        'ReceiptNote' => $receiptNote,
                        'ReceiptTemplateName' => false,
                        'SendCustomerReceipt' => true
                    );
                    $recurringObject = array(
                        'securityToken' => $securityToken,
                        'scheduledPaymentInternalId' => trim($schedulePaymentInternalId),
                        'recurringBilling' => $recurringBilling
                    );

                    if ($paymentMethodProfileStatus != 1) {
                        $WC_Gateway_EBizCharge->ebiz_log('Unable to update subscription payment method.');
                        return 2;
                    }

                    $modifyScheduledResult = $WC_Gateway_EBiz_Rec->modifyScheduleRecurringPaymentOnGateway($recurringObject);
                    if ($modifyScheduledResult->StatusCode == 1) {
                        $recurringDates = $WC_Gateway_EBiz_Rec->getRecurringScheduledDates($schedulePaymentInternalId);
                        $ebzRecurringTotal = count($recurringDates);
                        $nextRecDate = $WC_Gateway_EBiz_Rec->getNextRecurringDate($recurringDates);
                        $tablename = $wpdb->prefix . 'ebizcharge_recurring';
                        $data = array(
                            'rec_indefinitely' => $recIndefinitely,
                            'rec_frequency' => $schedule,
                            'rec_item_qty' => $qty,
                            'rec_start_date' => trim($start),
                            'rec_end_date' => trim($expire),
                            'rec_pmethod_id' => $methodId,
                            'rec_pmethod_name' => $methodName,
                            'rec_total' => $ebzRecurringTotal,
                            'rec_processed' => '0',
                            'rec_next_due' => $nextRecDate,
                            'rec_remaining' => $ebzRecurringTotal,
                            'billing_address' => $billingId,
                            'shipping_address' => $shippingId,
                            'rec_coupon' => $coupon,
                            'rec_amount' => $finalAmount,
                            'rec_shipping_method' => $shippingMethod,
                            'rec_shipping_amount' => $shippingAmount,
                            'rec_shipping_tax' => 0,
                            'rec_failed_attempts' => 0,
                            'updated_at' => date("Y-m-d h:i:s")
                        );

                        $condition = array(
                            "rec_scheduled_payment_internal_id" => $schedulePaymentInternalId
                        );

                        $flag = $wpdb->update($tablename, $data, $condition);
                        // Update recurring dates
                        $WC_Gateway_EBiz_Rec->deleteRecurringDatesBeforeUpdate($recurringId);
                        $WC_Gateway_EBiz_Rec->insertRecurringDatesToDb($recurringId, $recurringDates);
                        return isset($flag) ? 1 : 0;
                    } else {
                        $WC_Gateway_EBizCharge->ebiz_log('Unable to update subscription.');
                        return 2;
                    }

                } else {
                    $WC_Gateway_EBizCharge->ebiz_log('Unable to find method ID.');
                    return 3;
                }

            } catch (Exception $exception) {
                $WC_Gateway_EBizCharge->ebiz_log(__METHOD__ . $exception->getMessage(), 'critical');
                echo $this->getErrorMessage('Sorry, Subscription not saved. ' . $exception->getMessage());
                return;
            }
        }

        // 1 amount // 2 coupon, 3 customerID, 4 productID
        public function checkCouponCodeDiscount($productFinalPrice, $coupon, $customerId, $productId)
        {
            global $woocommerce, $WC_Gateway_EBizCharge;
            $WC_Gateway_EBizCharge->ebiz_log(__METHOD__);
            $cpData = new WC_Coupon($coupon);

            $cpStatus = $cpData->get_status();
            $expiryDate = strtotime(strtok($cpData->get_date_expires(), "T"));
            $today = strtotime(date('Y-m-d'));

            $usedByCustomersArray = $cpData->get_used_by();
            $usedByCustomersArrayCount = count(array_keys($usedByCustomersArray, $customerId));

            $allowedProductsArray = $cpData->get_product_ids();
            $excludedProductsArray = $cpData->get_excluded_product_ids();
            $allowedCategoriesArray = $cpData->get_product_categories();
            $excludedCategoriesArray = $cpData->get_excluded_product_categories();

            $productCategory = get_the_terms($productId, 'product_cat');
            foreach ($productCategory as $term) {
                $productCatId = $term->term_id;
            }

            $productDiscountedPrice = 0;
            $productPayablePrice = $productFinalPrice;

            // active // valid // allowed limit_usage_to_x_items
            if (($cpStatus == "publish") && ($expiryDate > $today)) {
                if ((empty($allowedProductsArray) || in_array($productId, $allowedProductsArray)) && (empty($excludedProductsArray) || !in_array($productCatId, $excludedProductsArray))) {

                    if ((empty($allowedCategoriesArray) || in_array($productCatId, $allowedCategoriesArray)) && (empty($excludedCategoriesArray) || !in_array($productCatId, $excludedCategoriesArray))) {

                        if (empty($cpData->get_minimum_amount()) || $productFinalPrice >= $cpData->get_minimum_amount()) {

                            if (empty($cpData->get_maximum_amount()) || $productFinalPrice <= $cpData->get_maximum_amount()) {

                                if (empty($cpData->get_usage_limit()) || $usedByCustomersArrayCount <= $cpData->get_usage_limit()) {

                                    if (empty($cpData->get_usage_limit_per_user()) || $usedByCustomersArrayCount <= $cpData->get_usage_limit_per_user()) {

                                        if ($cpData->get_discount_type() == 'percent') {
                                            $productDiscountedPercentage = round(($cpData->get_amount() * $productFinalPrice) / 100, 2);
                                            $productDiscountedPrice = $productFinalPrice - $productDiscountedPercentage;
                                        }

                                        if ($cpData->get_discount_type() == 'fixed_cart' || $cpData->get_discount_type() == 'fixed_product') {
                                            $productDiscountedPrice = $productFinalPrice - $cpData->get_amount();
                                        }

                                        $productPayablePrice = $productDiscountedPrice;
                                    }

                                }
                            }
                        }
                    }
                }
            }

            return $productPayablePrice;
        }

        public function addNewPaymentMethod($customerInternalId, $addNewForm)
        {
            $tran = $this->_initTransaction();
            $cardType = $addNewForm['cc_type'];
            $cardExpiration = $addNewForm['cc_exp_year'] . "-" . $addNewForm['cc_exp_month'];

            if ((isset($addNewForm['cc_cid'])) || (!empty($addNewForm['cc_cid']))) {
                $cardCode = $addNewForm['cc_cid'];
            } else {
                $cardCode = '';
            }

            $paymentMethodName = $cardType . ' ' . substr($addNewForm['cc_number'], -4) . ' - ' . $addNewForm['cc_owner'];
            $paymentParameters = array(
                'MethodName' => $paymentMethodName,
                'AccountHolderName' => $addNewForm['cc_owner'],
                'SecondarySort' => 1,
                'Created' => date('Y-m-d\TH:i:s'),
                'Modified' => date('Y-m-d\TH:i:s'),
                'CardCode' => $cardCode,
                'CardExpiration' => $cardExpiration,
                'CardNumber' => $addNewForm['cc_number'],
                'CardType' => $addNewForm['cc_type'],
                'Balance' => 0,
                'MaxBalance' => 0,
                'AvsStreet' => isset($addNewForm['avs_street']) ? $addNewForm['avs_street'] : '',
                'AvsZip' => isset($addNewForm['avs_zip']) ? $addNewForm['avs_zip'] : ''
            );
            //add customer payment method
            $paymentMethodId = $this->addCustomerPaymentMethod($customerInternalId, $paymentParameters);
            return [$paymentMethodId, $paymentMethodName];
        }

        public function addNewPaymentAccount($customerInternalId, $addNewForm)
        {
            $paymentMethodName = $addNewForm['cc_type_ach'] . ' ' . substr($addNewForm['cc_number_ach'], -4) . ' - ' . $addNewForm['cc_owner_ach'];
            $paymentParameters = array(
                'MethodName' => $paymentMethodName,
                'Created' => date('Y-m-d\TH:i:s'),
                'Modified' => date('Y-m-d\TH:i:s'),
                'Account' => $addNewForm['cc_number_ach'],
                'AccountType' => $addNewForm['cc_type_ach'],
                'AccountHolderName' => isset($addNewForm['cc_owner_ach']) ? $addNewForm['cc_owner_ach'] : '',
                'Routing' => $addNewForm['cc_routing_ach'],
                'MethodType' => 'ACH'
            );
            //add customer payment method
            $methodId = $this->addCustomerPaymentMethod($customerInternalId, $paymentParameters);
            return [$methodId, $paymentMethodName];
        }

        public function addCustomerPaymentMethod($customerInternalId, $parameters)
        {
            $tran = $this->_initTransaction();
            try {
                $addedPaymentMethodId = $tran->addPaymentMethodProfileToGateway($customerInternalId, $parameters);

                if (!empty($addedPaymentMethodId)) {
                    return $addedPaymentMethodId;
                }

                return false;

            } catch (\Exception $ex) {
                $tran->ebiz_log('Exception: ' . $ex->getMessage(), 'critical');
                print_r('Exception: ' . $ex->getMessage());
            }
        }

        public function get_customer_internalid($userId)
        {
            global $wpdb;
            global $woocommerce;
            $user = get_user_by('ID', $userId);

            $row = $wpdb->get_results("SELECT ec_customer_id,ec_internal_id FROM {$wpdb->prefix}users WHERE ID = $userId ", OBJECT);

            if (!empty($row[0]->ec_internal_id)) {
                return $user->ec_internal_id;

            } else {

                $tran = $this->_initTransaction();
                $securityToken = $tran->_getUeSecurityToken();
                $client = new SoapClient($tran->_getWsdlUrl(), $tran->SoapParams());

                if (!$ebizCustomer = $tran->searchCustomer($user)) {

                    if (!$ebizCustomer = $tran->searchAndSyncCustomer($user)) {
                        // add customer if not found
                        $customerResult = $client->AddCustomer(array(
                            'securityToken' => $securityToken,
                            'customer' => $this->getCustomerData($user->ID)
                        ));

                        $ebizCustomer = $customerResult->AddCustomerResult;

                        $tran->syncCustomer($ebizCustomer, $user->ID);

                        if (!empty($ebizCustomerNumber = $tran->getCustomerToken($ebizCustomer))) {
                            update_user_meta($user->ID, 'CustNum', $ebizCustomerNumber);
                        }
                    }

                    return $ebizCustomer->CustomerInternalId;
                }
            }
            return null;
        }

        public function getShippingMethods($method = null)
        {
            $delivery_zones = WC_Shipping_Zones::get_zones();

            if (!empty($delivery_zones)) {

                foreach ((array)$delivery_zones as $key => $the_zone) {

                    foreach ($the_zone['shipping_methods'] as $value) {
                        $selected = ($value->id == $method) ? 'selected' : '';

                        echo "<option value='$value->id'  $selected >$value->method_title</option>";
                        // don't remove below code
                        //echo $value->title . " ( " . $value->method_title . " - " . $value->cost . ")" . $value->id . ":" . $value->instance_id;
                    }
                }
            }
        }

        public function getSearchScheduledRecurringPayments($customerId, $customerInternalId, $schedulePaymentId)
        {
            try {
                $tran = $this->_initTransaction();
                $securityToken = $tran->_getUeSecurityToken();
                $client = new SoapClient($tran->_getWsdlUrl(), $tran->SoapParams());

                $response = $client->SearchScheduledRecurringPayments(
                    array(
                        'securityToken' => $securityToken,
                        'customerInternalId' => $customerInternalId,
                        'customerId' => $customerId,
                        'start' => 0,
                        'limit' => 900,
                    ));

                if (!isset ($response->SearchScheduledRecurringPaymentsResult->RecurringBillingDetails)) {
                    $recurringDetail = array();
                } elseif (
                    (is_array($response->SearchScheduledRecurringPaymentsResult->RecurringBillingDetails))
                    &&
                    (count($response->SearchScheduledRecurringPaymentsResult->RecurringBillingDetails))
                    > 1) {
                    $recurringDetail = $response->SearchScheduledRecurringPaymentsResult->RecurringBillingDetails;
                } else {
                    $recurringDetail[] = $response->SearchScheduledRecurringPaymentsResult->RecurringBillingDetails;
                }

                if (!empty($recurringDetail)) {
                    $key = array_search($schedulePaymentId, array_column($recurringDetail, 'ScheduledPaymentInternalId'));
                    return $paymentMethods = $recurringDetail[$key] ?? null;
                }
            } catch (\Exception $ex) {
                print_r($ex->getMessage());
                return null;
            }

            return null;
        }

        public function getAllRecurrings($id = null)
        {
            global $wpdb;
            $tableName = $wpdb->prefix . "ebizcharge_recurring";
            $cond = !empty($id) ? "and id = $id " : '';
            return $wpdb->get_row("SELECT * FROM $tableName  where id <> 0 $cond", OBJECT);
        }

        public function unsubscribe()
        {
            global $wpdb, $WC_Gateway_EBizCharge;
            $WC_Gateway_EBizCharge->ebiz_log(__METHOD__);
            $Susbscriptions_Econnect = new Susbscriptions_Econnect();

            $mid = $this->get_post('mid');
            $sid = $this->get_post('sid');

            try {
                //0 Active //1 Suspended //2 Expired //3 Canceled
                if (!empty($mid)) {
                    $unsubscribe = $Susbscriptions_Econnect->suspendDeleteBulkSubscription(array($mid), $sid);
                    if ($unsubscribe) {
                        return 1;
                    }
                }

            } catch (\Exception $e) {
                return 0;
            }

        }

        public function delete_subscription()
        {
            global $wpdb, $WC_Gateway_EBizCharge;
            $WC_Gateway_EBizCharge->ebiz_log(__METHOD__);
            $Susbscriptions_Econnect = new Susbscriptions_Econnect();

            $mid = $this->get_post('mid');
            $sid = 3;

            try {
                //0 Active //1 Suspended //2 Expired //3 Canceled
                if (!empty($mid)) {
                    $delete = $Susbscriptions_Econnect->suspendDeleteBulkSubscription(array($mid), $sid);
                    if ($delete) {
                        return 1;
                    }
                }
            } catch (\Exception $e) {
                $WC_Gateway_EBizCharge->ebiz_log($e->getMessage(), 'critical');
                return 2;
            }

        }

        /* rizwan function ends here  */

        public function add_new_method($ebiz_method)
        {
            global $woocommerce, $WC_Gateway_EBiz_Rec;
            $user = wp_get_current_user();

            $tran = $this->_initTransaction();
            $securityToken = $tran->_getUeSecurityToken();
            $client = new SoapClient($tran->_getWsdlUrl(), $tran->SoapParams());

            if ($ebiz_method == 'cc') {
                // Convert CC expiration date from (M)M-YYYY to MMYY
                $expmonth = $this->get_post('expmonth');

                if ($this->get_post('expyear') != null) {
                    $expyear = substr($this->get_post('expyear'), -2);
                }
                $billingAddress = $this->get_post('avs_street') ?? get_user_meta($user->ID, 'billing_address_1', true);
                $billingPostCode = $this->get_post('avs_zip') ?? get_user_meta($user->ID, 'billing_postcode', true);

                $paymentMethod = array(
                    'MethodName' => $this->get_post('cardtype') . ' ' . substr($this->get_post('ccnum'), -4) . ' - ' . $this->get_post('ccholder'), # . ' - Expires on: ' . $this->exp,
                    'SecondarySort' => 1,
                    'Created' => date('Y-m-d\TH:i:s'),
                    'Modified' => date('Y-m-d\TH:i:s'),
                    'AvsStreet' => $billingAddress,
                    'AvsZip' => $billingPostCode,
                    'CardCode' => '',
                    'CardExpiration' => $expmonth . $expyear,
                    'CardNumber' => $this->get_post('ccnum'),
                    'CardType' => $this->get_post('cardtype'),
                    'AccountHolderName' => $this->get_post('ccholder'),
                );
            }

            if ($ebiz_method == 'check') {
                $paymentMethod = array(
                    'MethodName' => $this->get_post('actype') . ' ' . substr($this->get_post('acnum'), -4) . ' - ' . $this->get_post('acholder'),
                    'MethodType' => 'check',
                    'SecondarySort' => 1,
                    'Created' => date('Y-m-d\TH:i:s'),
                    'Modified' => date('Y-m-d\TH:i:s'),
                    'Account' => $this->get_post('acnum'),
                    'AccountType' => $this->get_post('actype'),
                    'AccountHolderName' => $this->get_post('acholder'),
                    'Routing' => $this->get_post('routingnum')
                );
            }

            try {
                //  search customer by Id
                $ebizCustomer = $tran->getEbizCustomer($user);
                $ebizCustomerNumber = null;

                if ($ebizCustomer === null) {
                    // search customer by email
                    if (!$ebizCustomer = $tran->searchAndSyncCustomer($user)) {
                        // add customer if not found
                        $customerResult = $client->AddCustomer(array(
                            'securityToken' => $securityToken,
                            'customer' => $this->getCustomerData($user->ID)
                        ));

                        $ebizCustomer = $customerResult->AddCustomerResult;
                        $tran->syncCustomer($ebizCustomer, $user->ID);

                        $ebizCustomerNumber = $tran->getCustomerToken($ebizCustomer);
                        if (!empty($ebizCustomerNumber)) {
                            update_user_meta($user->ID, 'CustNum', $ebizCustomerNumber);
                        }
                        $ebizCustomer->CustomerToken = $ebizCustomerNumber;
                    }
                }
                //add customer payment method
                $wpCustomerToken = get_user_meta($user->ID, 'CustNum', true);

                if (($wpCustomerToken == $ebizCustomer->CustomerToken)) {
                    //add customer payment method
                    $paymentMethodId = $tran->addPaymentMethodProfileToGateway($ebizCustomer->CustomerInternalId, $paymentMethod);
                    $new_customer_vault_id = $paymentMethodId;
                } else {
                    wc_add_notice(__('Sorry, Customer with this user ID already exists on gateway.'), 'error');
                    wc_print_notices();
                    return;
                }

            } catch (SoapFault $ex) {
                wc_add_notice(__('Sorry, there was an error: ' . $ex->getMessage(), 'ebizcharge'), 'error');
                wc_print_notices();
                return;
            }

            if ($new_customer_vault_id > 0) {
                $customer_vault_ids = get_user_meta($user->ID, 'customer_vault_ids', true);

                if (!empty($customer_vault_ids)) {
                    $customer_vault_ids[] = $new_customer_vault_id;
                } else {
                    $customer_vault_ids = array($new_customer_vault_id);
                }

                update_user_meta($user->ID, 'customer_vault_ids', $customer_vault_ids);

                $reload = "<a class='payment-reload' href='" . $WC_Gateway_EBiz_Rec->getPermalinkUrl() . "'> Reload</a>";
                wc_add_notice(__('The payment method has been added successfully! ' . $reload, 'ebizcharge'), 'success');
                wc_print_notices();

                $this->javascript_pmethod_success($ebiz_method == 'check' ? 'bank' : 'cc');
            }
        }

        // woo-720:
        public function account_creation_possible(): bool
        {
            $create_account = $this->get_post('createaccount');
            $account_password = $this->get_post('account_password');
            //$enabled_guest_checkout = get_option('woocommerce_enable_guest_checkout', 'no') === 'yes';
            $email_password = get_option('woocommerce_registration_generate_password', 'no') === 'yes';
            $is_signup_from_checkout_allowed = get_option('woocommerce_enable_signup_and_login_from_checkout', 'no') === 'yes';
            // If automatically generate username/password are disabled, the Payment Request API
            // can't include any of those fields, so account creation is not possible.

            return ($create_account
                || ($is_signup_from_checkout_allowed && !empty($account_password))
                || ($is_signup_from_checkout_allowed && $email_password)
            );
        }

        /**
         * Check payment details for valid format
         */
        public function validate_fields()
        {
            global $woocommerce;

	        //woo-720: when guest checkout is enabled and allow to save card setting is OFF, we should not show error message
	        $enabled_guest_checkout_and_not_save_card =
                $this->saveinfo == 'no' && get_option('woocommerce_enable_guest_checkout', 'no') === 'yes';

	        $show_signup_error = $this->get_post('saveinfo')
	                             && !is_user_logged_in()
	                             && !$this->account_creation_possible()
	                             && !$enabled_guest_checkout_and_not_save_card;

	        if ($this->get_post('ebizcharge-payment-method') == 'cc') {

                if ($this->get_post('ebizcharge-use-stored-payment-info') == 'yes') {

                    $ebizcharge_stored_card = $this->get_post('ebizcharge-stored-card');

                    if (empty($ebizcharge_stored_card)) {
                        wc_add_notice(__('No saved credit card. ', 'ebizcharge'), 'error');
                        return false;

                    } else if (empty($this->get_post('exp_month_saved')) || empty($this->get_post('exp_year_saved'))) {
                        wc_add_notice(__('Expiry month or year is empty.', 'ebizcharge'), 'error');
                        return false;
                    } else {
                        return true;
                    }
                }

                // checkout update payment method
                if ($this->get_post('ebizcharge-use-stored-payment-info') == 'update') {

                    $ebizcharge_stored_card = $this->get_post('ebizcharge_update_cc');

                    if (empty($ebizcharge_stored_card)) {
                        wc_add_notice(__('No saved credit card. ', 'ebizcharge'), 'error');
                        return false;

                    } else if (empty($this->get_post('avs_street')) || empty($this->get_post('avs_zip'))) {
                        wc_add_notice(__('Billing street or Zip/Postal code is empty.', 'ebizcharge'), 'error');
                        return false;
                    } else {
                        return true;
                    }
                }

                // or my account new payment method
                if ($this->get_post('add_new_method') != null) {
                    if (empty($this->get_post('avs_street')) || empty($this->get_post('avs_zip'))) {
                        wc_add_notice(__('Billing street or Zip/Postal code is empty.', 'ebizcharge'), 'error');
                        return false;
                    }
                }

                // Check for saving payment info without having or creating an account
                // We need to remove this check for some customeres if they use custom theme
                if ($show_signup_error) {
                    wc_add_notice(__('Sorry, you need to create an account in order for us to save your payment information. ', 'ebizcharge'), 'error');
                    return false;
                }

                $ccholder = $this->get_post('ccholder');
                $cardType = $this->get_post('cardtype');
                $cardNumber = $this->get_post('ccnum');
                $cardCSC = $this->get_post('cvv');
                $cardExpirationMonth = $this->get_post('expmonth');
                $cardExpirationYear = $this->get_post('expyear');

                // Check card holder name
                if (empty($ccholder)) {
                    wc_add_notice(__('<b>Name on Card</b> is a required field. ', 'ebizcharge'), 'error');
                    return false;
                }
                // Check card number
                if (empty($cardNumber) || !ctype_digit($cardNumber)) {
                    wc_add_notice(__('<b> Credit Card Number</b> is a required field.', 'ebizcharge'), 'error');
                    return false;
                }

                //woo-806: validate card types
                if (!in_array($cardType, $this->cardtypes)) {
                    wc_add_notice(__("<b> Card Type '" . $cardType. "'</b> is invalid.", 'ebizcharge'), 'error');
                    return false;
                }

                // Check card Type MasterCard
                if (($cardType == "MasterCard") && (!preg_match('/^5[12345]\d{14}$/', $cardNumber))) {
                    //$woocommerce->add_error( __( 'Card number is invalid.', 'woocommerce' ) );
                    wc_add_notice(__('<b>Master Card</b> is invalid.', 'ebizcharge'), 'error');
                    return false;
                }
                // Check card Type Visa
                if (($cardType == "Visa") && (!preg_match('/^4\d{12}(\d\d\d){0,1}$/', $cardNumber))) {
                    wc_add_notice(__('<b>Visa Card</b> is invalid.', 'ebizcharge'), 'error');
                    return false;
                }
                // Check card Type Discover
                if (($cardType == "Discover") && (!preg_match('/^6011\d{12}$/', $cardNumber))) {
                    wc_add_notice(__('<b>Discover Card</b> is invalid.', 'ebizcharge'), 'error');
                    return false;
                }
                // Check card Type American Express
                if (($cardType == "American Express") && (!preg_match('/^3[47]\d{13}$/', $cardNumber))) {
                    wc_add_notice(__('<b>American Express Card</b> is invalid.', 'ebizcharge'), 'error');
                    return false;
                }

                if ($this->cvv == 'yes') {
                    // Check security code
                    if (!ctype_digit($cardCSC)) {
                        wc_add_notice(__('<b>CVV</b> is invalid (only digits are allowed)', 'ebizcharge'), 'error');
                        return false;
                    }

                    if ((strlen($cardCSC) != 3 && in_array($cardType, array('Visa', 'MasterCard', 'Discover'))) || (strlen($cardCSC) != 4 && $cardType == 'American Express')) {
                        wc_add_notice(__('<b>CVV</b> is invalid (wrong length).', 'ebizcharge'), 'error');
                        return false;
                    }
                }

                // Check expiration data
                $currentYear = date('Y');

                if (!ctype_digit($cardExpirationMonth) || !ctype_digit($cardExpirationYear) ||
                    $cardExpirationMonth > 12 ||
                    $cardExpirationMonth < 1 ||
                    $cardExpirationYear < $currentYear ||
                    $cardExpirationYear > $currentYear + 20
                ) {
                    wc_add_notice(__('Card expiration date is invalid.', 'ebizcharge'), 'error');
                    return false;
                }
            }

            if ($this->get_post('ebizcharge-payment-method') == 'check') {
                if ($this->get_post('ebizcharge-use-stored-bank-info') == 'yes') {
                    $ebizcharge_stored_bank = $this->get_post('ebizcharge-stored-bank');

                    // Check account holder name
                    if (empty($ebizcharge_stored_bank)) {
                        wc_add_notice(__('No saved account. ', 'ebizcharge'), 'error');
                        return false;
                    }

                    return true;
                }

                // Check for saving payment info without having or creating an account
                if ($show_signup_error) {
                    wc_add_notice(__('Sorry, you need to create an account in order for us to save your payment information. ', 'ebizcharge'), 'error');
                    return false;
                }

                $acholder = $this->get_post('acholder');
                $actype = $this->get_post('actype');
                $acnum = $this->get_post('acnum');
                $routingnum = $this->get_post('routingnum');

                // Check account holder name
                if (empty($acholder)) {
                    wc_add_notice(__('Account Holder is empty. ', 'ebizcharge'), 'error');
                    return false;
                }

                // Check account type
                if (empty($actype)) {
                    wc_add_notice(__('Account Type is invalid or empty.', 'ebizcharge'), 'error');
                    return false;
                }

                // Check account number
                if (empty($acnum) || !ctype_digit($acnum)) {
                    wc_add_notice(__('Account Number is invalid or empty.', 'ebizcharge'), 'error');
                    return false;
                }

                // Check account number length
                if ((strlen($acnum) < 5) || (strlen($acnum) > 17)) {
                    wc_add_notice(__('Account Number is invalid (wrong length), that should be 5-17 digits.', 'ebizcharge'), 'error');
                    return false;
                }

                // Check routing number
                if (empty($routingnum) || !ctype_digit($routingnum)) {
                    wc_add_notice(__('Routing Number is invalid or empty.', 'ebizcharge'), 'error');
                    return false;
                }

                // Check routing number length
                if ((strlen($routingnum) < 9) || (strlen($routingnum) > 9)) {
                    wc_add_notice(__('Routing Number is invalid (wrong length), that should be 9 digits.', 'ebizcharge'), 'error');
                    return false;
                }

            }

            return true;
        }

        /**
         * click radio button for validate
         */
        public function javascript_validation($ebiz_method)
        {
            echo '<script>
						document.getElementById("ebizcharge-' . $ebiz_method . '-payment").checked = true;
						document.getElementById("ebizcharge-' . $ebiz_method . '-payment").click();
					</script>';
        }

        public function javascript_pmethod_success($ebiz_method)
        {
            echo "<script>
				jQuery(document).ready(function () {
                    setTimeout(function() {
                        jQuery('#ebizcharge-" . $ebiz_method . "-payment').trigger('click');
                        window.location.replace(window.location.href);
                    },1);
				});
			</script>";
        }

        /**
         * Add ability to view and edit payment details on the My Account page.(The WooCommerce 'force ssl' option also secures the My Account page, so we don't need to do that.)
         */
        public function add_payment_method_options()
        {
            global $woocommerce;
            $user = wp_get_current_user();
            $tran = $this->_initTransaction();
            //$user_has_stored_data = $this->user_has_stored_data($user->ID);
            $WC_Gateway_EBiz_Rec = new WC_Gateway_EBizCharge_Recurring();

            if ($this->get_post('delete') != null) {

                $method_to_delete = $this->get_post('delete');
                $method_type = $this->get_post('methodtype');

                $this->delete_payment_method($method_to_delete, $method_type);

            } else if ($this->get_post('update') != null) {
                $method_to_update = $this->get_post('update');
                $ccnumber = $this->get_post('edit-cc-number-' . $method_to_update);

                if (empty($ccnumber) || !ctype_digit($ccnumber)) {

                    global $woocommerce;
                    wc_add_notice(__('Card number is invalid.', 'ebizcharge'), 'error');
                    wc_print_notices();

                } else {
                    $avsStreet = $this->get_post('edit-cc-address-' . $method_to_update);
                    $avsZip = $this->get_post('edit-cc-zip-' . $method_to_update);
                    if (empty($avsStreet) || empty($avsZip)) {
                        wc_add_notice(__('Billing street or zip is empty.', 'ebizcharge'), 'error');
                        return false;
                    }

                    $ccexp = $this->get_post('edit-cc-exp-' . $method_to_update);
                    $expmonth = substr($ccexp, 0, 2);
                    $expyear = substr($ccexp, -2);
                    $currentYear = substr(date('Y'), -2);

                    if (empty($ccexp) || !ctype_digit(str_replace('/', '', $ccexp)) ||
                        $expmonth > 12 || $expmonth < 1 ||
                        $expyear < $currentYear || $expyear > $currentYear + 20) {

                        global $woocommerce;
                        //$woocommerce->add_error( __( 'Card expiration date is invalid', 'woocommerce' ) );
                        wc_add_notice(__('Card expiration date is invalid.', 'ebizcharge'), 'error');
                        wc_print_notices();

                    } else {
                        $this->update_payment_method($method_to_update, $ccexp, $avsStreet, $avsZip);
                    }
                }
            }
            // MY account section
            $paymentMethods = $tran->getCustomerPaymentMethodsByGetCustomer($user->ID);

            ?>
            <h2 class="ebiz-methods">Saved Payment Methods</h2>
            <p>This information is stored to save time at the checkout and to pay for subscriptions.</p>

            <?php if ($tran->is_cc_enabled() && $tran->enabled_cc_ach_both()) { ?>

            <input type="radio" name="ebizcharge-payment-method" id="ebizcharge-cc-payment"
                   class="ebizcharge-cc-payment" value="cc" checked="checked" onclick="showcc()"/>

            <label for="ebizcharge-cc-payment" class="ebizcharge-cc-payment-label">
                <?php _e('Saved Credit Cards', 'woocommerce'); ?>
            </label>
            <?php
        } ?>
            <?php if ($tran->is_ach_enabled() && $tran->enabled_cc_ach_both()) { ?>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="radio" name="ebizcharge-payment-method" id="ebizcharge-bank-payment"
                   class="ebizcharge-bank-payment" value="check" onclick="showbank()"/>
            <label for="ebizcharge-bank-payment" class="ebizcharge-bank-payment-label">
                <?php _e('Saved Bank Accounts', 'woocommerce') ?>
            </label>
            <?php if (!$tran->is_cc_enabled()) { ?>
                <script type="text/javascript">
                    jQuery(document).ready(function () {
                        jQuery('#ebizcharge-bank-payment').prop('checked', true);
                        jQuery("#ebizcharge-bank-payment").trigger('click');
                        jQuery("#cc-div").css('display', 'none');
                        jQuery("#cc-div").prop("disabled", true);
                        jQuery("#bank-div-ac").css('display', 'block');
                        jQuery("#bank-div-ac").prop("disabled", false);
                    });
                </script>
            <?php } ?>
        <?php } ?>
            <script type="text/javascript">
                function showcc() {
                    document.getElementById('bank-div-ac').style.display = 'none';
                    document.getElementById('cc-div').style.display = 'block';
                }

                function showbank() {
                    document.getElementById('cc-div').style.display = 'none';
                    document.getElementById('bank-div-ac').style.display = 'block';
                }
            </script>
            <?php if ($tran->is_cc_enabled()) { ?>
            <div id="cc-div" class="cc-div">
                <?php
                $this->payment_method_card_form_myaccount();
                if ($this->get_post('add_new_card') != null) {
                    if ($this->validate_fields()) {
                        $this->add_new_method('cc');
                        $this->javascript_pmethod_success('cc');
                    } else {
                        $this->javascript_validation('cc');
                        wc_print_notices();
                    }
                }

                // Show Option to store credit card data only logged in users
                if (is_user_logged_in() && $this->saveinfo == 'yes') {
                    // Option to store credit card data
                    echo '<div id="hide-method-bar-card">
                    <button id="Add-new-card" class="button">Add New Card</button><br/></div>';
                }

                $paymentMethodsCc = $tran->groupCustomerPaymentMethodsByType($paymentMethods, 'cc');
                if (is_user_logged_in() && $paymentMethodsCc) {
                    foreach ($paymentMethodsCc as $ccMethod) {
                        ?>
                        <header class="title">
                            <?php /*?><h3><?php echo $ccMethod->MethodName; ?></h3><?php */ ?>
                            <?php
                            $card_type = $tran->get_card_type($ccMethod->CardType);
                            $get_pmethod_type_image_type = $WC_Gateway_EBiz_Rec->getCCTypeImage($card_type);
                            $get_pmethod_type_image_path = plugin_dir_url(__FILE__) . 'assets/images/' . $get_pmethod_type_image_type;
                            $card_image = "<img class='ac-card-img' src='" . $get_pmethod_type_image_path . "' width='35' height='25' alt='" . $ccMethod->MethodName . "'>";
                            $card_text = "<span class='ac-card-text'> ending in " . substr($ccMethod->CardNumber, -4) . "</span>";
                            ?>
                            <h3><?php echo $card_image . $card_text; ?></h3>

                            <button class="button pull-right"
                                    id="unlock-delete-button-<?php echo $ccMethod->MethodID; ?>">
                                <?php _e('Delete', 'woocommerce'); ?>
                            </button>

                            <button style="display:none" class="button pull-right"
                                    id="cancel-delete-button-<?php echo $ccMethod->MethodID; ?>">
                                <?php _e('No', 'woocommerce'); ?>
                            </button>
                            <form action="<?php echo get_permalink(wc_get_page_id('myaccount')) ?>" method="post"
                                  class="pull-right">
                                <input type="submit" value="<?php _e('Yes', 'woocommerce'); ?>"
                                       class="button mr-5" id="delete-button-<?php echo $ccMethod->MethodID; ?>"
                                       style="display:none;">
                                <input type="hidden" name="methodtype" value="cc">
                                <input type="hidden" name="delete" value="<?php echo $ccMethod->MethodID ?>">
                            </form>
                            <span id="delete-confirm-msg-<?php echo $ccMethod->MethodID; ?>" class="pull-left"
                                  style="display:none">
								<strong class="del-message">Are you sure?</strong></br>
                                <strong class="del-message">Subscriptions purchased with this card will be canceled.</strong>
								</span>

                            <button class="button pull-right mr-5" id="edit-button-<?php echo $ccMethod->MethodID; ?>">
                                <?php _e('Edit', 'woocommerce'); ?>
                            </button>
                            <button style="display:none" class="button pull-right"
                                    id="cancel-button-<?php echo $ccMethod->MethodID; ?>">
                                <?php _e('Cancel', 'woocommerce'); ?>
                            </button>

                            <form action="<?php echo get_permalink(wc_get_page_id('myaccount')) ?>"
                                  id="method-card-update" method="post">
                                <input type="submit" value="<?php _e('Save', 'woocommerce'); ?>"
                                       class="button pull-right mr-5"
                                       id="save-button-<?php echo $ccMethod->MethodID; ?>" style="display:none;">
                                <span style="float:left">Credit card:&nbsp;</span>
                                <input type="hidden" style="display:none"
                                       id="edit-cc-number-<?php echo $ccMethod->MethodID; ?>"
                                       name="edit-cc-number-<?php echo $ccMethod->MethodID; ?>" minlength="15"
                                       maxlength="16"
                                       value="<?php echo '000000000000' . substr($ccMethod->CardNumber, 12, 16); ?>"/>
                                <?php echo 'XXXXXXXXXXXX' . substr($ccMethod->CardNumber, 12, 16); ?>
                                <span id="cc-number-<?php echo $ccMethod->MethodID; ?>"></span>
                                <div style="margin: 15px 0 0 0;">
                                    <span class="pull-left">Expiration:<span class="del-message">*</span>&emsp;&emsp;&nbsp;&nbsp;&nbsp;</span>
                                    <input type="text" class="pull-left" style="display:none"
                                           id="edit-cc-exp-<?php echo $ccMethod->MethodID; ?>"
                                           name="edit-cc-exp-<?php echo $ccMethod->MethodID; ?>" maxlength="7"
                                           value="<?php echo substr($ccMethod->CardExpiration, -2) . '/' . substr($ccMethod->CardExpiration, 0, 4); ?>"
                                           placeholder="MM/YYYY" style="margin-top: 20px"/>
                                    <span id="cc-exp-<?php echo $ccMethod->MethodID; ?>">
									<?php echo substr($ccMethod->CardExpiration, -2) . '/' . substr($ccMethod->CardExpiration, 0, 4); ?>
									</span>
                                    <label id="expiration-error-<?php echo $ccMethod->MethodID; ?>" class="error"
                                           for="expiration" style="display: none;"></label>
                                </div>
                                <div style="margin: 15px 0 0 0;">
                                    <span class="">Billing Street:<span
                                                class="del-message">*</span>&emsp;&nbsp;&nbsp;</span>
                                    <input type="text" class="mt-5" style="display:none"
                                           id="edit-cc-address-<?php echo $ccMethod->MethodID; ?>"
                                           name="edit-cc-address-<?php echo $ccMethod->MethodID; ?>"
                                           value="<?php echo $ccMethod->AvsStreet ?? ''; ?>"/>
                                    <span id="cc-address-<?php echo $ccMethod->MethodID; ?>">
										<?php echo $ccMethod->AvsStreet ?? ''; ?>
									</span>
                                    <label id="avs-error-<?php echo $ccMethod->MethodID; ?>" class="error" for="avs"
                                           style="display: none;"></label>
                                </div>
                                <div style="margin: 15px 0 0 0;">
                                    <span class="mr-20">Zip/Postal Code:<span class="del-message">*</span>&nbsp;</span>
                                    <input type="text" class="mt-5" style="display:none;"
                                           id="edit-cc-zip-<?php echo $ccMethod->MethodID; ?>"
                                           name="edit-cc-zip-<?php echo $ccMethod->MethodID; ?>"
                                           value="<?php echo $ccMethod->AvsZip ?? ''; ?>"/>
                                    <span id="cc-zip-<?php echo $ccMethod->MethodID; ?>">
										<?php echo $ccMethod->AvsZip ?? ''; ?>
									</span>
                                    <label id="zip-error-<?php echo $ccMethod->MethodID; ?>" class="error" for="zip"
                                           style="display: none;"></label>
                                </div>
                                <input type="hidden" name="update" value="<?php echo $ccMethod->MethodID ?>">
                            </form>

                        </header>
                        <?php
                    }
                } else {
                    echo '<p class="no-cards">There are no saved credit cards.</p>';
                }
                ?>
            </div>
        <?php } ?>
            <?php if ($tran->is_ach_enabled()) { ?>
            <div id="bank-div-ac" class="bank-div-ac"
                <?php if (!$tran->is_cc_enabled()) {
                    echo 'style="display:block;"';
                } else {
                    echo 'style="display:none;"';
                } ?>>
                <?php
                $this->payment_method_bank_form_myaccount();
                if ($this->get_post('add_new_bank') != null) {
                    if ($this->validate_fields()) {
                        $this->add_new_method('check');
                        $this->javascript_pmethod_success('bank');
                    } else {
                        $this->javascript_validation('bank');
                        wc_print_notices();
                    }
                }

                // Show Option to store credit card data only logged in users
                if (is_user_logged_in() && $this->saveinfo == 'yes') {
                    // Option to store credit card data
                    echo '<div id="hide-method-bar-bank">
                            <button id="Add-new-bank" class="button pull-right mr-5">Add New Bank Account</button><br/>
                        </div>';
                }

                $paymentMethodsBank = $tran->groupCustomerPaymentMethodsByType($paymentMethods, 'check');
                if (is_user_logged_in() && $paymentMethodsBank) {
                    foreach ($paymentMethodsBank as $bankMethod) {
                        ?>
                        <header class="title">
                            <?php /*?><h3><?php echo $bankMethod->MethodName; ?></h3><?php */ ?>
                            <?php
                            $card_type = $tran->get_card_type($bankMethod->AccountType);
                            $get_pmethod_type_image_type = $WC_Gateway_EBiz_Rec->getCCTypeImage($card_type);
                            $get_pmethod_type_image_path = plugin_dir_url(__FILE__) . 'assets/images/' . $get_pmethod_type_image_type;
                            $card_image = "<img class='ac-card-img' src='" . $get_pmethod_type_image_path . "' width='30' height='25'>";
                            $card_text = ' ending in ' . substr($bankMethod->Account, -4);
                            ?>
                            <h3><?php echo $card_image . '<span class="ac-card-text">' . $card_text . '</span>'; ?></h3>

                            <button class="button pull-right"
                                    id="unlock-delete-button-<?php echo $bankMethod->MethodID; ?>">
                                <?php _e('Delete', 'woocommerce'); ?>
                            </button>

                            <button style="display:none" class="button pull-right"
                                    id="cancel-delete-button-<?php echo $bankMethod->MethodID; ?>">
                                <?php _e('No', 'woocommerce'); ?>
                            </button>
                            <form action="<?php echo get_permalink(wc_get_page_id('myaccount')) ?>" method="post"
                                  class="pull-right">
                                <input type="submit" value="<?php _e('Yes', 'woocommerce'); ?>"
                                       class="button mr-5" id="delete-button-<?php echo $bankMethod->MethodID; ?>"
                                       style="display:none;">
                                <input type="hidden" name="methodtype" value="check">
                                <input type="hidden" name="delete" value="<?php echo $bankMethod->MethodID ?>">
                            </form>
                            <span id="delete-confirm-msg-<?php echo $bankMethod->MethodID; ?>" class="pull-left"
                                  style="display:none">
								<strong class="del-message">Are you sure?</strong></br>
                                <strong class="del-message">Subscriptions purchased with this bank account will be canceled.</strong>
							</span>

                            <button style="display:none" class="button pull-right"
                                    id="cancel-button-<?php echo $bankMethod->MethodID; ?>">
                                <?php _e('Cancel', 'woocommerce'); ?>
                            </button>

                            <form action="<?php echo get_permalink(wc_get_page_id('myaccount')) ?>" method="post">
                                <input type="submit" value="<?php _e('Save', 'woocommerce'); ?>"
                                       class="button pull-right mr-5"
                                       id="save-button-<?php echo $bankMethod->MethodID; ?>" style="display:none;">
                                <span style="float:left">Account Number:&nbsp;</span>
                                <input type="hidden" style="display:none"
                                       id="edit-cc-number-<?php echo $bankMethod->MethodID; ?>"
                                       name="edit-cc-number-<?php echo $bankMethod->MethodID; ?>" minlength="5"
                                       maxlength="17" value="<?php echo substr($bankMethod->Account, 5, 9); ?>"/>
                                <?php echo $bankMethod->Account; ?>
                                <br/>
                                <span style="float:left">Account Type:&nbsp;</span>
                                <?php echo $bankMethod->AccountType; ?>
                                <br/>
                                <input type="hidden" name="update" value="<?php echo $bankMethod->MethodID ?>">
                            </form>

                        </header>
                        <?php
                    }
                } else {
                    echo '<p class="no-cards">There are no saved bank accounts.</p>';
                }
                ?>
            </div>
        <?php } ?>
            <?php
        }

        public function getExpiryMonths($selectedValue = null)
        {
            $months = array();
            for ($i = 1; $i <= 12; $i++) {
                $timestamp = mktime(0, 0, 0, $i, 1);
                $months[date('n', $timestamp)] = date('F', $timestamp);
            }
            foreach ($months as $num => $name) {
                $selected = '';
                if ($num == $selectedValue) {
                    $selected = 'selected="selected"';
                }
                if ($num < 10) {
                    $num = '0' . $num;
                }
                echo "<option value='$num' $selected>$name</option>";
            }
        }

        public function getExpiryYears($selectedValue = null)
        {
            for ($year = date('Y'); $year <= date('Y') + 15; $year++) {
                $selected = '';
                if ($year == $selectedValue) {
                    $selected = 'selected="selected"';
                }
                printf("<option value='%u' $selected>%u</option>", $year, $year);
            }
        }

        public function getCardOptions()
        {
            if ($this->cardtypes) {
                ?>
                <option value="">
                    <?php _e('Card Type', 'woocommerce'); ?>
                </option>
                <?php
                foreach ($this->cardtypes as $type) { ?>
                    <option value="<?php echo $type ?>">
                        <?php _e($type, 'woocommerce'); ?>
                    </option>
                <?php }
            } else { ?>
                <option value="">
                    <?php _e('Card type not available', 'woocommerce'); ?>
                </option>
                <?php
            }
        }

        public function getAllowedCardOptions()
        {
            $activeCardTypes = "[";
            if (!empty($this->cardtypes) && is_array($this->cardtypes)) {

                $counter = 1;
                $countTypes = count($this->cardtypes);
                foreach ($this->cardtypes as $type) {
                    $activeCardTypes .= "'" . $type . "'";

                    if ($counter < $countTypes) {
                        $activeCardTypes .= ',';
                    }

                    $counter++;
                }
            }
            $activeCardTypes .= "]";
            return $activeCardTypes;
        }

        public function payment_method_card_form_myaccount()
        {
            // check for permission to save form
            // Show Option to store credit card data only logged in users
            if (is_user_logged_in()) {
                // Option to store credit card data
                if ($this->saveinfo == 'yes') {
                    ?>
                    <form action="<?php echo get_permalink(wc_get_page_id('myaccount')) ?>" method="post"
                          id="method-card" style="display: none;">
                        <h3>Add New Card</h3>
                        <fieldset>
                            <!-- Show input boxes for new data -->
                            <div id="ebizcharge-new-info">
                                <input type="hidden" class="ebizcharge-payment-method" id="ebizcharge-payment-method"
                                       name="ebizcharge-payment-method" value="cc"/>
                                <!-- Credit card Holder Name -->
                                <p class="form-row ">
                                    <label class="ccholder" for="ccholder">
                                        <?php echo __('Name on Card', 'woocommerce') ?> <span class="required">*</span>
                                    </label>
                                    <input type="text" class="input-text" id="ccholder" name="ccholder" maxlength="50"
                                           placeholder="Name on Card"/>
                                </p>
                                <!-- Credit card number -->
                                <p class="form-row form-row-first">
                                    <label class="ccnum" for="ccnum">
                                        <?php echo __('Credit Card#', 'woocommerce') ?>
                                        <span class="required">*</span>
                                    </label>
                                    <input onkeyup="getCardType(this.value)" type="text" class="input-text" id="ccnum"
                                           name="ccnum" minlength="15" maxlength="16" placeholder="Credit Card#"
                                           oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"/>
                                </p>
                                <!-- Credit card type -->
                                <p class="form-row form-row-last">
                                    <label class="cardtype" for="cardtype">
                                        <?php echo __('Card Type', 'woocommerce') ?> <span class="required">*</span>
                                    </label>
                                    <select name="cardtype" id="cardtype" class="woocommerce-select">
                                        <?php $this->getCardOptions() ?>
                                    </select>
                                    <input type="hidden" id="allowedCardTypes" class="allowedCardTypes"
                                           value="<?php echo $this->getAllowedCardOptions(); ?>"/>
                                </p>
                                <div class="empty-error ccnum-new-error"></div>
                                <div class="empty-error cardtype-new-error"></div>
                                <div class="clear"></div>
                                <!-- Credit card expiration -->
                                <p class="form-row form-row-first">
                                    <label class="expmonthyear" for="cc-expire-month">
                                        <?php echo __('Expiration Date', 'woocommerce') ?> <span
                                                class="required">*</span>
                                    </label>
                                    <select name="expmonth" id="expmonth"
                                            class="mb-5 woocommerce-select woocommerce-cc-month woocommerce-cc-month-my-ac">
                                        <option value="">
                                            <?php _e('Month', 'woocommerce') ?>
                                        </option>
                                        <?php $this->getExpiryMonths(); ?>
                                    </select>
                                    <label id="expmonth-error" class="error" for="expmonth"
                                           style="display: none;"></label>
                                    <select name="expyear" id="expyear"
                                            class="woocommerce-select woocommerce-cc-year woocommerce-cc-year-my-ac">
                                        <option value="">
                                            <?php _e('Year', 'woocommerce') ?>
                                        </option>
                                        <?php $this->getExpiryYears(); ?>
                                    </select>
                                </p>
                                <?php
                                // Credit card security code
                                if ($this->cvv == 'yes') {
                                    ?>
                                    <p class="form-row form-row-last">
                                        <label class="cvv" for="cvv">
                                            <?php _e('CVV', 'woocommerce') ?> <span
                                                    class="required">*</span>
                                        </label>
                                        <input oninput="validate_cvv(this.value)" placeholder="cvv" type="password"
                                               woocommerce_ebizcharge_salemethod="text" class="input-text" id="cvv"
                                               name="cvv" maxlength="4"/>
                                        <span class="help">
											<?php //_e('3 or 4 digits usually found on the signature strip.', 'woocommerce') ?>
										</span>
                                    </p>
                                    <?php
                                }

                                // Show Option to store credit card data only logged in users
                                if (is_user_logged_in()) {
                                    // Option to store credit card data
                                    if ($this->saveinfo == 'yes' && !(class_exists('WC_Subscriptions_Cart') && WC_Subscriptions_Cart::cart_contains_subscription())) {
                                        ?>
                                        <div style="clear: both;"></div>
                                        <input type="hidden" class="input-checkbox" id="saveinfo" name="saveinfo"
                                               value="yes"/>
                                        <?php
                                    }
                                }
                                ?>
                                <p class="form-row form-row-first">
                                    <label for="avs_street">
                                        <?php echo __('Billing Street', 'woocommerce') ?> <span
                                                class="required">*</span>
                                    </label>
                                    <input type="text" class="input-text" id="avs_street" name="avs_street"
                                           maxlength="200"/>
                                </p>
                                <p class="form-row form-row-last">
                                    <label for="avs_zip">
                                        <?php echo __('Zip/Postal Code', 'woocommerce') ?> <span
                                                class="required">*</span>
                                    </label>
                                    <input type="text" class="input-text" id="avs_zip" name="avs_zip" maxlength="10"
                                           oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"/>
                                </p>
                            </div>
                        </fieldset>
                        <div class="add-card">
                            <input type="submit" class="add_new_card" id="add_new_card" name="add_new_card"
                                   value="Save"/>
                            <button id="cancel_button_card" class="cancel-btn">Cancel</button>
                        </div>
                    </form>
                    <?php
                }
            }
        }

        public function payment_method_bank_form_myaccount()
        {
            // check for permission to save form
            // Show Option to store credit card data only logged in users
            if (is_user_logged_in()) {
                // Option to store bank details
                if ($this->saveinfo == 'yes') {
                    ?>
                    <form action="<?php echo get_permalink(wc_get_page_id('myaccount')) ?>" method="post"
                          id="method-bank" style="display: none;">
                        <h3>Add New Bank Account</h3>
                        <fieldset>
                            <!-- Show input boxes for new data -->
                            <div id="ebizcharge-new-info">
                                <input type="hidden" class="ebizcharge-payment-method" id="ebizcharge-payment-method"
                                       name="ebizcharge-payment-method" value="check"/>
                                <!-- Account Holder Name -->
                                <p class="form-row ">
                                    <label for="acholder">
                                        <?php echo __('Account Holder', 'woocommerce') ?>
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" class="input-text" id="acholder" name="acholder" maxlength="50"/>
                                <div class="empty-error acholder-error"></div>
                                </p>
                                <!-- Account type -->
                                <p class="form-row">
                                    <label for="actype">
                                        <?php echo __('Account Type', 'woocommerce') ?> <span class="required">*</span>
                                    </label>
                                    <select name="actype" id="actype" class="woocommerce-select">
                                        <option value="checking">Checking</option>
                                        <option value="savings">Savings</option>
                                    </select>
                                </p>
                                <!-- Account Number -->
                                <p class="form-row">
                                    <label for="acnum">
                                        <?php echo __('Account Number', 'woocommerce') ?>
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" class="input-text" id="acnum"
                                           name="acnum" minlength="5" maxlength="17"/>
                                <div class="empty-error acnum-error"></div>
                                </p>
                                <!-- Routing number -->
                                <p class="form-row">
                                    <label for="routingnum">
                                        <?php echo __('Routing Number', 'woocommerce') ?>
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" class="input-text" id="routingnum" name="routingnum"
                                           minlength="9" maxlength="9"/>
                                <div class="empty-error routingnum-error"></div>
                                </p>
                                <div class="clear"></div>
                            </div>
                        </fieldset>
                        <div class="add-bank">
                            <input type="submit" class="add_new_bank" id="add_new_bank" name="add_new_bank"
                                   value="Save"/>
                            <button id="cancel_button_bank" class="cancel-btn">Cancel</button>
                        </div>
                    </form>
                    <?php
                }
            }
        }

        public function receipt_page($order)
        {
            echo '<p>' . __('Thank you for your order.', 'woocommerce') . '</p>';
        }

        /**
         * Get the current user's login name
         */
        private function get_user_login()
        {
            global $user_login;
            get_currentuserinfo();
            return $user_login;
        }

        /**
         * Get post data if set
         */
        private function get_post($name)
        {
            if (isset($_POST[$name])) {
                return $_POST[$name];
            }
            return null;
        }

        /**
         * Check whether an order is a subscription
         */
        private function is_subscription($order)
        {
            return class_exists('WC_Subscriptions_Order') && WC_Subscriptions_Order::order_contains_subscription($order);
        }

        private function getAvsMessage($code): array
        {
            return [
                'YYY' => [
                    'Address' => 'Match',
                    'Zip/Postal Code' => 'Match'
                ],
                'NYZ' => [
                    'Address' => 'Not a match',
                    'Zip/Postal Code' => 'Match'
                ],
                'YNA' => [
                    'Address' => 'Match',
                    'Zip/Postal Code' => 'Not a match'
                ],
                'NNN' => [
                    'Address' => 'Not a match',
                    'Zip/Postal Code' => 'Not a match'
                ],
                'YYX' => [
                    'Address' => 'Match',
                    'Zip/Postal Code' => 'Not a match'
                ],
                'NYW' => [
                    'Address' => 'Not a match',
                    'Zip/Postal Code' => 'Match'
                ],
                'XXW' => ['Message' => 'Card number not on file'],
                'XXU' => ['Message' => 'Address not verified for domestic transaction.'],
                'XXR' => ['Message' => 'Retry / system unavailable.'],
                'XXS' => ['Message' => 'Service not supported.'],
                'XXE' => ['Message' => 'Address verification not allowed for card type.'],
                'XXG' => ['Message' => 'Global non-AVS participant.'],
                'YYG' => [
                    'International address' => 'Match',
                    'Zip/Postal Code' => 'Not compatible'
                ],
                'GGG' => [
                    'International address' => 'Match',
                    'Zip/Postal Code' => 'Match'
                ],
                'YGG' => [
                    'International address' => 'Not compatible',
                    'Zip/Postal Code' => 'Match'
                ]
            ][$code] ?? ['Message' => 'Unknown AVS code'];
        }

        public function getAvsAndCvvMessages($avsCode, $cvvMatch): string
        {
            $avsMessages = $this->getAvsMessage($avsCode);
            $output = '';

            $errorKeywords = [
                'not a match',
                'not compatible',
                'not supported',
                'not verified',
                'not allowed',
                'not on file',
                'unavailable',
                'global non-avs participant',
                'retry',
                'card number not on file',
            ];

            foreach ($avsMessages as $label => $value) {
                $isError = false;
                foreach ($errorKeywords as $keyword) {
                    if (stripos($value, $keyword) !== false) {
                        $isError = true;
                        break;
                    }
                }

                if ($isError) {
                    $output .= "$label: <span style='color:red;'>$value</span><br>";
                } else {
                    $output .= "$label: $value<br>";
                }
            }

            // Add CVV2/CVC message
            $output .= 'CVV2/CVC: ';
            if (stripos($cvvMatch, 'no') !== false) {
                $output .= "<span style='color:red;'>$cvvMatch</span><br><br>";
            } else {
                $output .= "$cvvMatch<br><br>";
            }

            $output .= "<span>Our card verification indicates this could result in a chargeback.</span><br>";
            $output .= "<b>Do you still wish to continue?</b><br>";

            return $output;
        }


    }

    $GLOBALS['WC_ebizcharge'] = new WC_ebizcharge();

    /**
     * Add the gateway to woocommerce
     */
    function add_ebizcharge_commerce_gateway($methods)
    {
        $methods[] = 'WC_ebizcharge';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_ebizcharge_commerce_gateway');
}

function get_common_fields()
{
    return array(
        'ec_status' => 'varchar(20)',
        'ec_status_code' => 'varchar(20)',
        'ec_error' => 'varchar(255)',
        'ec_error_code' => 'varchar(20)',
        'ec_internal_id' => 'varchar(255)',
        'ec_last_modified_date' => 'datetime',
    );
}

function econnect_install()
{
    global $wpdb;

    $userFields = array_merge(get_common_fields(), [
        'ec_customer_id' => 'varchar(255)',
        'ec_customer_token' => 'varchar(255)', // customer token used in update scenario
    ]);

    foreach ($userFields as $field => $type) {
        addFieldIfNotExist($wpdb->prefix . 'users', $field, $type);
    }

    addSalesOrderFields();
}

/**
 * This function add Econenct sales order sync related fields
 */
function addSalesOrderFields()
{
    global $wpdb;

    $postTableFields = array_merge(get_common_fields(), array(
        'ec_order_status' => 'varchar(20)', // transaction status (success/failed)
        'ec_order_error' => 'varchar(255)', // transaction error
        'ec_order_last_modified_date' => 'datetime',
        'ec_order_internal_id' => 'varchar(255)',
        'ec_order_id' => 'varchar(255)',
        'ec_invoice_id' => 'varchar(255)', // invoice id used in update scenario
        'ec_product_id' => 'varchar(255)', // product id used in update scenario
        'ec_customer_id' => 'varchar(255)',
    ));

    foreach ($postTableFields as $field => $type) {
        addFieldIfNotExist($wpdb->prefix . 'posts', $field, $type);
    }

    foreach ($postTableFields as $field => $type) {
        addFieldIfNotExist($wpdb->prefix . 'wc_orders', $field, $type);
    }
}

function addFieldIfNotExist($table, $column, $type)
{
    global $wpdb;
    try {
        $databaseName = $wpdb->dbname;
        $row = $wpdb->get_results("SELECT '" . $column . "' FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '" . $databaseName . "' AND table_name = '" . $table . "' AND column_name = '" . $column . "'");

        if (empty($row)) {
            $wpdb->query("alter table " . $table . " add column " . $column . " $type NULL; ");
        }

    } catch (Exception $e) {
        print_r($e->getMessage());
    }
}

//--------- Recurring Functionality DB Create Start -------
function recurring_db_install()
{
    global $wpdb;
    $table_recurring = $wpdb->prefix . 'ebizcharge_recurring';
    $charset_collate = $wpdb->get_charset_collate();

    $sql1 = "CREATE TABLE IF NOT EXISTS $table_recurring (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		rec_status mediumint(9) NULL,
		rec_indefinitely mediumint(9) NULL,
		customer_id varchar(11) NULL,
		order_id varchar(50) NULL,
		item_id varchar(50) NULL,
		item_variation_id varchar(50) NULL,
		item_name varchar(200) NULL,
		rec_item_qty smallint(9) NULL,
		rec_start_date datetime DEFAULT '0000-00-00 00:00:00' NULL,
		rec_end_date datetime DEFAULT '0000-00-00 00:00:00' NULL,
		rec_frequency varchar(50) NULL,
		rec_pmethod_id varchar(11) NULL,
		rec_pmethod_name varchar(100) NULL,
		rec_scheduled_payment_internal_id varchar(100) NULL,
		rec_total mediumint(9) NULL,
		rec_processed mediumint(9) NULL,
		rec_next_due datetime DEFAULT '0000-00-00 00:00:00' NULL,
		rec_remaining mediumint(9) NULL,
		billing_address text NULL,
		shipping_address text NULL,
		rec_coupon text NULL,
		rec_amount float(6) NULL,
		rec_shipping_method varchar(100) NULL,
		rec_shipping_amount float(6) NULL,
		rec_shipping_tax float(6) NULL,
		rec_failed_attempts mediumint(9) NULL,
		PRIMARY KEY (id)
	) $charset_collate;";

    $table_recurring_dates = $wpdb->prefix . 'ebizcharge_recurring_dates';

    $sql2 = "CREATE TABLE IF NOT EXISTS $table_recurring_dates (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		rec_id mediumint(9) NULL,
		rec_date varchar(50) NULL,
		PRIMARY KEY (id)
	) $charset_collate;";

    $table_recurring_order = $wpdb->prefix . 'ebizcharge_recurring_order';

    $sql3 = "CREATE TABLE IF NOT EXISTS $table_recurring_order (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		rec_id mediumint(9) NULL,
		rec_order_id varchar(50) NULL,
		order_date varchar(50) NULL,
		order_entity_id varchar(50) NULL,
		created_date datetime DEFAULT '0000-00-00 00:00:00' NULL,
		status mediumint(9) NULL,
		message text NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql1);
    dbDelta($sql2);
    dbDelta($sql3);

    $additionalFields = array(
        'rec_coupon' => 'text(100)',
        'rec_shipping_amount' => 'float(6)',
        'rec_shipping_tax' => 'float(6)',
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'item_variation_id' => 'varchar(50)',
    );

    foreach ($additionalFields as $field => $type) {
        addFieldIfNotExist($table_recurring, $field, $type);
    }
}
//--------- Recurring Functionality DB Create end ----------