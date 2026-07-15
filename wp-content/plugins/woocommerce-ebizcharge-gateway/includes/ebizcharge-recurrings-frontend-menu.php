<?php
/**
 * EbizCharge Myaccount Menu Class
 *
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Subscriptions_My_Account_Endpoint
{
    public static $endpoint_subscriptions = 'subscriptions';
    public static $endpoint_subscriptions_edit = 'subscriptions-edit';
    public static $endpoint_subscriptions_history = 'history';

    /**
     * Plugin actions.
     */
    public function __construct()
    {
        // Actions used to insert a new endpoint in the WordPress.
        add_action('init', array($this, 'add_endpoints'));
        add_filter('woocommerce_get_query_vars', array($this, 'get_query_vars'), 0);

        // Change the My Accout page title.
        add_filter('the_title', array($this, 'endpoint_title'));

        // Insering your new tab/page into the My Account page.
        add_filter('woocommerce_account_menu_items', array($this, 'new_menu_items'));
    }

    /**
     * Register new endpoint to use inside My Account page.
     *
     * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
     */
    public function add_endpoints()
    {
        add_rewrite_endpoint(self::$endpoint_subscriptions, EP_ROOT | EP_PAGES);
        add_rewrite_endpoint(self::$endpoint_subscriptions_edit, EP_ROOT | EP_PAGES);
        add_rewrite_endpoint(self::$endpoint_subscriptions_history, EP_ROOT | EP_PAGES);
    }

    /**
     * Add new query var.
     *
     * @param array $vars
     * @return array
     */
    public function get_query_vars($vars)
    {
        $vars[self::$endpoint_subscriptions] = self::$endpoint_subscriptions;
        $vars[self::$endpoint_subscriptions_edit] = self::$endpoint_subscriptions_edit;
        $vars[self::$endpoint_subscriptions_history] = self::$endpoint_subscriptions_history;
        return $vars;
    }

    /**
     * Set endpoint title.
     *
     * @param string $title
     * @return string
     */
    public function endpoint_title($title)
    {
        global $wp_query;

        $is_endpoint = isset($wp_query->query_vars[self::$endpoint_subscriptions]);
        $is_endpoint_edit = isset($wp_query->query_vars[self::$endpoint_subscriptions_edit]);
        $is_endpoint_history = isset($wp_query->query_vars[self::$endpoint_subscriptions_history]);

        if ($is_endpoint && !is_admin() && is_main_query() && in_the_loop() && is_account_page()) {
            // New page title.
            $title = __('Scheduled Subscriptions', 'woocommerce');
            remove_filter('the_title', array($this, 'endpoint_title'));
        }

        if ($is_endpoint_edit && !is_admin() && is_main_query() && in_the_loop() && is_account_page()) {
            // New page title.
            $title = __('Manage Subscription', 'woocommerce');
            remove_filter('the_title', array($this, 'endpoint_edit_title'));
        }

        if ($is_endpoint_history && !is_admin() && is_main_query() && in_the_loop() && is_account_page()) {
            // History page title.
            $title = __('Subscription Payment History', 'woocommerce');
            remove_filter('the_title', array($this, 'endpoint_history_title'));
        }

        return $title;
    }

    /**
     * Insert the new endpoint into the My Account menu.
     *
     * @param array $items
     * @return array
     */
    public function new_menu_items($items)
    {
        global $WC_Gateway_EBizCharge;

        if($WC_Gateway_EBizCharge->is_ebizcharge_enabled()) {
            // Remove the logout menu item.
            $logout = $items['customer-logout'];
            unset($items['customer-logout']);

            // Insert your custom endpoint.
            $items[self::$endpoint_subscriptions] = __('Subscriptions', 'woocommerce');
            // Insert back the logout item.
            $items['customer-logout'] = $logout;
        }

        return $items;
    }

    /**
     * Plugin install action.
     * Flush rewrite rules to make our custom endpoint available.
     */
    public static function install()
    {
        flush_rewrite_rules();
    }
}

/*
 * Add URL re-writes and define Ajax URL's
 * Register a URL that will set this variable to list
 */
add_action('generate_rewrite_rules', 'subscriptions_rw');
function subscriptions_rw($wp_rewrite)
{
    global $WC_Gateway_EBiz_Rec;
    /* Page URL for subscriptions list */
    $subscriptions_list_url = $WC_Gateway_EBiz_Rec->makePermalinkUrl('subscriptions?recurrings=list');
    $new_rules['^ebizcharge-all-recurrings.php$'] = $subscriptions_list_url;
    /* Page URL for subscriptions update */
    $subscriptions_update_url = $WC_Gateway_EBiz_Rec->makePermalinkUrl('subscriptions-update?recurringID=');
    $new_rules['^single-recurring-update.php$'] = $subscriptions_update_url;
    /* Page URL for subscriptions update */
    $history_list_url = $WC_Gateway_EBiz_Rec->makePermalinkUrl('history?payments=list');
    $new_rules['^ebizcharge-all-payments.php$'] = $history_list_url;
    $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}

// Add recurrings list as a query var
add_action('query_vars', 'subscriptions_query_vars');
function subscriptions_query_vars($query_vars)
{
    $query_vars[] = 'recurrings';
    $query_vars[] = 'recurringID';
    $query_vars[] = 'refNumber';
    $query_vars[] = 'payments';
    return $query_vars;
}

// if the variable list is set, we include our page and stop execution after it
add_action('parse_request', 'subscriptions_parse_request');
function subscriptions_parse_request(&$wp)
{
    if (array_key_exists('recurrings', $wp->query_vars)) {
        include(plugin_dir_path(__DIR__) . '/views/frontend/ebizcharge-all-recurrings.php');
        exit();
    }

    if (array_key_exists('recurringID', $wp->query_vars) || array_key_exists('refNumber', $wp->query_vars)) {
        include(plugin_dir_path(__DIR__) . '/views/frontend/single-recurring-update.php');
        exit();
    }

    if (array_key_exists('payments', $wp->query_vars)) {
        include(plugin_dir_path(__DIR__) . '/views/frontend/ebizcharge-all-payments.php');
        exit();
    }
}

/**
 * Function for `woocommerce_my_account_my_orders_columns` filter-hook.
 *
 * @param  $columns
 *
 * @return
 */
add_filter('woocommerce_account_orders_columns', 'wc_update_account_orders_columns_title');
function wc_update_account_orders_columns_title($columns = array())
{
    // Update columns title
    $columns['order-date'] = __('Order Date', 'woocommerce');
    return $columns;
}
