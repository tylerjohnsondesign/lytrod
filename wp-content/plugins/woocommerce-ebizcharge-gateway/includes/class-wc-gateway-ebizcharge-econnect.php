<?php
set_time_limit(0);
ini_set('default_socket_timeout', 1000);

class WC_Gateway_EBizCharge_Econnect
{
    public $enableEconnect = false;
    public $showSavedMethods = false;
    public $voidOnCancelled = false;
    public $key;   // Source key
    public $userid;   // User Id
    public $pin;   // Source pin (optional)
    public $software;
    public $user_table;
    public $user_meta_table;
    public $posts_table;
    public $wc_orders_table;
    public $wc_order_item_table;
    public $wc_order_item_meta_table;

    public function __construct()
    {
        global $wpdb;
        $this->user_table = $wpdb->prefix . 'users';
        $this->user_meta_table = $wpdb->prefix . 'usermeta';
        $this->posts_table = $wpdb->prefix . 'posts';
        $this->wc_orders_table = $wpdb->prefix . 'wc_orders';
        $this->wc_order_item_table = $wpdb->prefix . 'woocommerce_order_items';
        $this->wc_order_item_meta_table = $wpdb->prefix . 'woocommerce_order_itemmeta';
        $this->software = "Woocommerce";
    }

    // Add EConnect logs to Woocommerce logs
    public function log($message, $level = 'info')
    {
        global $WC_Gateway_EBizCharge;
        $logFileName = 'EConnect';
        try {
            if (!is_string($message)) {
                $message = print_r($message, true);
            }
            $WC_Gateway_EBizCharge->log_write($level, $message, $logFileName); // Info

        } catch (Exception $e) {
            $WC_Gateway_EBizCharge->log_write('critical', $e, $logFileName); // critical/exception
        }
    }

    /**
     * Get Security Token array
     *
     * @return array
     */

    // Set Soap Client Parameters
    public function SoapParamsEconnect()
    {
        return array(
            'cache_wsdl' => false,
            'trace' => true,
            'exceptions' => true
        );
    }

    /**
     * Get Response message HTML string
     * @return string
     */
    private function _prepareMessages($messages)
    {
        $messagesHtml = '';
        foreach ($messages as $message) {
            switch ($message['type']) {
                case 'error':
                    $messagesHtml .= '<div class="notice notice-error is-dismissible"> <p>' . $message['message'] . '</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                    </div>';
                    break;
                case 'notice':
                    $messagesHtml .= '<div class="notice notice-info is-dismissible"> <p>' . $message['message'] . '</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>

                    </div>';
                    break;
                case 'success':
                    $messagesHtml .= '<div class="notice notice-success is-dismissible"> <p>' . $message['message'] . '</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                    </div>';
                    break;
                default:
                    break;
            }
        }

        return $messagesHtml;
    }

    private function getCustomerObject($customer)
    {
        $billingAddress = [];
        $shippingAddress = [];

        if (!empty($customer['billing_first_name'])) {

            $billingAddress = [
                'FirstName' => $customer['billing_first_name'],
                'LastName' => $customer['billing_last_name'],
                'CompanyName' => $customer['billing_company'] ?? '',
                'Address1' => $customer['billing_address_1'] ?? '',
                'City' => $customer['billing_city'] ?? '',
                'State' => $customer['billing_state'] ?? '',
                'ZipCode' => $customer['billing_postcode'] ?? '',
                'Country' => $customer['billing_country'] ?? ''
            ];
        }

        if (!empty($customer['shipping_first_name'])) {
            $shippingAddress = [
                'FirstName' => $customer['shipping_first_name'],
                'LastName' => $customer['shipping_last_name'],
                'CompanyName' => $customer['shipping_company'] ?? '',
                'Address1' => $customer['shipping_address_1'] ?? '',
                'City' => $customer['shipping_city'] ?? '',
                'State' => $customer['shipping_state'] ?? '',
                'ZipCode' => $customer['shipping_postcode'] ?? '',
                'Country' => $customer['shipping_country'] ?? ''
            ];
        }

        return array(
            'CustomerId' => $customer['ID'],
            'FirstName' => $customer['first_name'],
            'LastName' => $customer['last_name'],
            'CompanyName' => $customer['billing_company'] ?? '',
            'Phone' => $customer['billing_phone'] ?? '',
            'Fax' => '',
            'Email' => $customer['user_email'],
            'BillingAddress' => $billingAddress,
            'ShippingAddress' => $shippingAddress,
            'SoftwareId' => $this->software,
        );

    }

    private function formatApiResponse($table, $responseObj)
    {
        $now = new DateTime(null);
        $now->modify("+10 seconds"); //add 10 seconds to make sure sync last_modified_date is greater than object update date
        $data = array();
        $dataCondFormat = array();
        $dataResponseFormat = array();
        $dataFormat = array();

        if ($responseObj->Status == "Success") {

            if ($table == $this->user_table) {
                $data['ec_internal_id'] = $responseObj->CustomerInternalId;
                $data['ec_customer_id'] = $responseObj->ec_customer_id;
                $data['ec_customer_token'] = $responseObj->CustomerToken ?? null;
                $dataCondFormat = array('%s', '%s', '%s');
            } elseif ($table == $this->posts_table || $table == $this->wc_orders_table) {
                // product internal id
                if (isset($responseObj->ItemInternalId)) {
                    $data['ec_internal_id'] = $responseObj->ItemInternalId;
                    $data['ec_product_id'] = $responseObj->ec_product_id;
                    $dataCondFormat = array('%s', '%d');
                } else {
                    // invoice internal id
                    $data['ec_internal_id'] = $responseObj->InvoiceInternalId;
                    $data['ec_invoice_id'] = $responseObj->ec_invoice_id;
                    $data['ec_customer_id'] = $responseObj->ec_customer_id ?? null;
                    $dataCondFormat = array('%s', '%d', '%s');
                }

            }
        }

        $data['ec_status'] = $responseObj->Status;
        $data['ec_status_code'] = $responseObj->StatusCode ?? null;
        $data['ec_error'] = $responseObj->Error ?? null;
        $data['ec_error_code'] = $responseObj->ErrorCode ?? null;
        $data['ec_last_modified_date'] = $now->format('Y-m-d H:i:s');

        $dataResponseFormat = array('%s', '%d', '%s', '%d', '%s');
        $dataFormat = array_merge($dataCondFormat, $dataResponseFormat);

        $insertData['data'] = $data;
        $insertData['format'] = $dataFormat;

        return $insertData;
    }

    public function updateWPTable($table, $apiResponse, $where)
    {
        global $wpdb;
        $data = $this->formatApiResponse($table, $apiResponse);
        $wpdb->update($table, $data['data'], $where, $data['format'], ['%d']);
    }

    /**
     * Sync Customers to eConnect
     * @param null $id
     * @return string
     */
    public function syncCustomer($id = null)
    {
        $this->log(__METHOD__);
        global $wpdb, $WC_Gateway_EBizCharge;
        $_messages = array();
        $_errorCount = 0;
        $_processedCount = 0;

        $securityToken = $WC_Gateway_EBizCharge->_getUeSecurityToken();

        try {
            $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $this->SoapParamsEconnect());
            //filter if update is for a single record
            $user_arg['role'] = 'customer';
            if ($id != null) {
                $user_arg['include'] = array($id);
            }

            foreach (get_users($user_arg) as $user) {

                $userMeta = array_map(function ($a) {
                    return $a[0];
                }, get_user_meta($user->ID));

                $customer = array_merge((array)$user->data, $userMeta);

                if (!empty($customer['last_update']) && !empty($customer['ec_last_modified_date']) &&
                    date(strtotime($customer['ec_last_modified_date'])) > $customer['last_update']
                ) {
                    continue; // don't need to process
                }

                try {
                    $customerObj = $this->getCustomerObject($customer);

                    if (empty($customer['ec_internal_id'])) {

                        if ($searchCustomers = $this->searchCustomerListByEmail($customer['user_email'])) {

                            $matchingCustomer = null;
                            foreach ($searchCustomers as $searchCustomer) {

                                // if email is same, we assume its same customer
                                if (strtolower(trim($searchCustomer->Email)) == strtolower(trim($customer['user_email']))) {
                                    $matchingCustomer = $searchCustomer;
                                    break;
                                }
                            }

                            if ($matchingCustomer) {
                                $now = new DateTime(null);
                                $customerApiInfo = [
                                    'ec_status' => 'Success',
                                    'ec_status_code' => 1,
                                    'ec_last_modified_date' => $now->format('Y-m-d H:i:s'),
                                    'ec_internal_id' => $matchingCustomer->CustomerInternalId,
                                    'ec_customer_id' => $matchingCustomer->CustomerId,
                                    'ec_customer_token' => $matchingCustomer->CustomerToken
                                ];

                                $customerApiInfoFormat = ['%s', '%d', '%s', '%s', '%s', '%s'];

                                update_user_meta($user->ID, 'CustNum', $searchCustomer->CustomerToken);

                                $wpdb->update($this->user_table, $customerApiInfo, ['ID' => $user->ID], $customerApiInfoFormat, ['%d']);
                                $_processedCount++;
                                continue;

                            }
                        }

                        $parameters = array(
                            'securityToken' => $securityToken,
                            'customer' => $customerObj
                        );

                        $addCustomerResponse = $client->AddCustomer($parameters);
                        $obj = $addCustomerResponse->AddCustomerResult;

                        if ($obj->Status == 'Success') {
                            $this->log('Customer added successfully.');
                            $this->log($obj);

                            if ($ebizCustomerNumber = $WC_Gateway_EBizCharge->getCustomerToken($obj)) {
                                $obj->CustomerToken = $ebizCustomerNumber;
                            }

                        } else {
                            $this->log('Add customer Failed: ');
                            $this->log($obj);
                        }

                        $_processedCount++;

                    } else {

                        if (!empty($customer['ec_customer_id'])) {
                            $customerObj['CustomerId'] = $customer['ec_customer_id']; // use econnect customer id in update
                        }

                        $parameters = array(
                            'securityToken' => $securityToken,
                            'customer' => $customerObj,
                            'customerId' => $customerObj['CustomerId'],
                            'customerInternalId' => $customer['ec_internal_id']
                        );

                        $updateCustomerResponse = $client->UpdateCustomer($parameters);
                        $obj = $updateCustomerResponse->UpdateCustomerResult;

                        if ($obj->Status == 'Success') {
                            $this->log('Customer updated successfully.');
                            $obj->CustomerToken = $customer['ec_customer_token'];
                            $this->log($obj);
                            
                            if (empty($customer['ec_customer_token']) && $ebizCustomerNumber = $WC_Gateway_EBizCharge->getCustomerToken($obj)) {
                                $obj->CustomerToken = $ebizCustomerNumber;
                            }
                        }
                    }

                    if ($obj->Status == "Error") {
                        if ($_errorCount == 0)
                            $_messages[] = array("type" => 'error', "message" => "Errors have occurred during the sync Customers process, please review the logs. First error occurred is: " . $obj->Error);

                        $_errorCount++;
                    }

                    if (!empty($obj)) {
                        $whereCondition = array('ID' => (int)$customer['ID']);
                        $obj->ec_customer_id = $customerObj['CustomerId'];
                        
                        $this->updateWPTable($this->user_table, $obj, $whereCondition);
                    }

                    $this->log("Customer info updated in WP tables.");
                    $_processedCount++;

                } catch (Exception $e) {
                    $this->log($e->getMessage(), 'critical');

                    if ($_errorCount == 0)
                        $_messages[] = array("type" => 'error', "message" => "Errors have occurred during the sync Customers process, please review the logs. First error occurred is: " . $e->getMessage());

                    $_errorCount++;
                } finally {
                    //$_processedCount++;
                }
            }

            array_unshift($_messages, array("type" => 'notice', "message" => sprintf("Sync Customers process has been completed. %s record(s) processed.", $_processedCount)));

            if ($_errorCount == 0)
                $_messages[] = array("type" => 'success', "message" => "Completed the sync Customers process with no errors.");
        } catch (Exception $e) {
            $this->log($e->getMessage(), 'critical');
            $_messages[] = array("type" => 'error', "message" => $e->getMessage());
        }

        return $this->_prepareMessages($_messages);
    }

    private function insertWpCustomer($customer)
    {
        global $wpdb;

        if (empty($customer->Email)) {
            return false;
        }

        $wpCustomer = [
            'first_name' => $customer->FirstName,
            'last_name' => $customer->LastName,
            'billing_company' => $customer->CompanyName,
            'billing_phone' => $customer->Phone,
            'user_email' => $customer->Email,
            'role' => 'customer',
            'user_login' => $customer->Email,
            'user_pass' => $customer->FirstName . '*0334',
        ];

        $this->log('saving customer, info: ');

        $customerId = wp_insert_user($wpCustomer);

        if (is_wp_error($customerId)) {
            $this->log('Customer not created. email: ' . $customer->Email . ' ' . $customerId->get_error_message(), 'error');
            // this can create issue as API allowing multiple customers with same email
            if ($user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_email = %s LIMIT 1", $customer->Email))) {
                // if customer found with same email and CustomerId
                if (!empty($user->ec_customer_id) && $user->ec_customer_id == $customer->CustomerId) {
                    //$this->log('Local customer matched: ' . $user->ID);
                    return $user->ID;
                } else {
                    $this->log('Local customer not matched: local ec_customer_id: ' . $user->ec_customer_id . ' Order CustomerId: ' . $customer->CustomerId);
                    return false;
                }

            } else {
                return false;
            }

        } else {
            $this->log('Customer Successfully Added. CustomerId: ' . $customerId);
        }

        if ($customerId) {
            $now = new DateTime(null);
            $customerApiInfo = [
                'ec_internal_id' => $customer->CustomerInternalId,
                'ec_status' => 'Success',
                'ec_status_code' => 1,
                'ec_last_modified_date' => $now->format('Y-m-d H:i:s'),
                'ec_customer_id' => $customer->CustomerId,
                'ec_customer_token' => $customer->CustomerToken,
            ];

            $customerApiInfoFormat = ['%s', '%s', '%d', '%s', '%s', '%s'];

            $wpdb->update($this->user_table, $customerApiInfo, ['ID' => $customerId], $customerApiInfoFormat, ['%d']);

            $shippingMeta = [];
            $billingMeta = [];
            $customerShipping = $customer->ShippingAddress;
            $customerBilling = $customer->BillingAddress;

            if (!empty($customerBilling) && isset($customerBilling->FirstName)) {
                $billingMeta = [
                    'billing_first_name' => $customerBilling->FirstName,
                    'billing_last_name' => $customerBilling->LastName,
                    'billing_address_1' => $customerBilling->Address1,
                    'billing_city' => $customerBilling->City,
                    'billing_state' => $customerBilling->State,
                    'billing_postcode' => $customerBilling->ZipCode,
                    'billing_country' => $customerShipping->Country,
                ];

            } else {
                $this->log('Customer billing address is empty. CustomerId: ' . $customerId, 'warning');
            }

            if (empty($customerShipping) && !empty($customerBilling)) {
                $this->log('Customer shipping address is empty. CustomerId: ' . $customerId, 'warning');
                $customerShipping = $customerBilling;
            }

            if (!empty($customerShipping) && isset($customerShipping->FirstName)) {
                $shippingMeta = [
                    'shipping_first_name' => $customerShipping->FirstName,
                    'shipping_last_name' => $customerShipping->LastName,
                    'shipping_company' => $customerShipping->CompanyName,
                    'shipping_address_1' => $customerShipping->Address1,
                    'shipping_city' => $customerShipping->City,
                    'shipping_state' => $customerShipping->State,
                    'shipping_postcode' => $customerShipping->ZipCode,
                    'shipping_country' => $customerShipping->Country,
                ];
            }

            $customerMeta = array_merge($billingMeta, $shippingMeta);
            // add customer token
            $customerMeta = array_merge($customerMeta, ['CustNum' => $customer->CustomerToken]);
            foreach ($customerMeta as $key => $value) {
                // Update user meta.
                add_user_meta($customerId, $key, $value);
            }
        }

        return $customerId;
    }

    private function getWpCustomers($customerMap = false)
    {
        global $wpdb;
        if ($customerMap) {
            $userQuery = "SELECT u.ec_customer_id, u.ID, u.user_email, ec_internal_id FROM " . $this->user_table . " u WHERE u.ec_customer_id <> ''";
        } else {
            $userQuery = "SELECT u.user_email, u.ID, u.ec_customer_id, ec_internal_id FROM " . $this->user_table . " u WHERE u.user_email <> ''";
        }

        return $wpdb->get_results($userQuery, OBJECT_K);

        /*$user_arg['role'] = 'customer';
        $customers = [];
        foreach (get_users($user_arg) as $user) {
            $customer = $user->data;

            if ($customerMap) {
                // used in download order
                if (!empty($customer->ec_customer_id)) {
                    $customers[$customer->ec_customer_id] = $customer->ID;
                }

            } else {

                if (!empty($customer->ec_internal_id)) {
                    $customers[$customer->ec_internal_id] = $customer->ID;
                }
            }
        }

        return $customers;*/
    }

    public function downloadCustomers()
    {
        $this->log(__METHOD__);
        $_messages = array();
        $_errorCount = 0;
        $_processedCount = 0;

        $start = 0;
        $limit = 500;

        try {
            $wpCustomers = $this->getWpCustomers();

            while (true) {
                $this->log('Download customer start is: ' . $start . ' , Limit: '. $limit);
                $customers = $this->searchCustomerList($start, $limit);
                $allCustomers = $customers->Customer ?? [];
                $itemsCount = is_countable($allCustomers) ? count($allCustomers) : 0;

                if (empty($allCustomers)) {
                    $this->log('No more customers to process, exit the loop.');
                    break; // No more customers to process, exit the loop
                }

                foreach ($allCustomers as $customer) {
                    if (empty($customer->CustomerId) || empty($customer->Email)) {
                        $this->log('CustomerId or Email is empty. Adding customer is skipped.');
                        continue;
                    }

                    if (!array_key_exists($customer->Email, $wpCustomers)) {
                        if ($this->insertWpCustomer($customer)) {
                            $this->log('Customer ' . $customer->Email . ' does not exist. Adding new one.');
                            $_processedCount++;
                        }
                    }
                }
                $this->log('New customers downloaded: '. $_processedCount);

                if ($itemsCount < $limit) {
                    $this->log('All records have been fetched. Its last iteration');
                    break; // If fetched less than 1000 records, it means all records have been fetched
                }

                $start += $itemsCount; // Update the start for the next iteration
            }

        } catch (Exception $e) {
            $this->log($e->getMessage(), 'critical');
            if ($_errorCount == 0) {
                $_messages[] = array(
                    "type" => 'error',
                    "message" => "Errors have occurred during the download customers process, please review the logs. First error occurred is: " . $e->getMessage()
                );
                $_errorCount++;
            }
        }

        if ($_errorCount == 0) {
            $_messages[] = array(
                "type" => 'success',
                "message" => sprintf("Download customers has been completed. %s record(s) processed.", $_processedCount)
            );
        }

        return $this->_prepareMessages($_messages);
    }

    /**
     * Search Ebiz customer
     * @param int $customerId - wp customer id
     * Return false | customer object
     */
    public function searchCustomerById($customerId)
    {
        global $WC_Gateway_EBizCharge;
        $this->log(__METHOD__);

        try {
            // find CustomerInternalId using SearchCustomers ebiz method
            $ebizCustomer = $WC_Gateway_EBizCharge->searchCustomerFromGateway($customerId);
            return $ebizCustomer ?? false;

        } catch (SoapFault $ex) {
            $this->log(__METHOD__ . ', Error: ' . $ex->getMessage(), 'critical');
            return $this->error = 'SoapFault:' . __METHOD__ . $ex->getMessage();
        }

    }

    /**
     * @param int $start
     * @param int $limit
     * @return array
     */
    private function searchCustomerList($start = 0, $limit = 1000)
    {
        global $WC_Gateway_EBizCharge;
        $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $WC_Gateway_EBizCharge->SoapParams());
        $ebizCustomers = [];

        try {
            $filters = [
                'FieldName' => 'SoftwareId',
                'ComparisonOperator' => 'notequal',
                'FieldValue' => $this->software,
            ];

            $params = [
                'securityToken' => $WC_Gateway_EBizCharge->_getUeSecurityToken(),
                'filters' => ['SearchFilter' => $filters],
                'includeCustomerToken' => 1,
                'includePaymentMethodProfiles' => 0,
                'countOnly' => 0,
                'start' => $start,
                'limit' => $limit
            ];

            $api = $client->SearchCustomerList($params);

            if (isset($api->SearchCustomerListResult) && $api->SearchCustomerListResult->Count > 0) {
                $ebizCustomers = $api->SearchCustomerListResult->CustomerList;
                $count = $api->SearchCustomerListResult->Count;

                $this->log(__METHOD__ . ', Customers found: ' . $count);
            }

        } catch (SoapFault $ex) {
            $this->log(__METHOD__ . ', Error: ' . $ex->getMessage(), 'critical');
        }

        return $ebizCustomers;
    }

    /**
     * @param $email
     * @return array
     * @throws SoapFault
     */
    private function searchCustomerListByEmail($email)
    {
        global $WC_Gateway_EBizCharge;
        $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $WC_Gateway_EBizCharge->SoapParams());
        $ebizCustomers = [];

        try {
            $filters = [
                'FieldName' => 'Email',
                'ComparisonOperator' => 'eq',
                'FieldValue' => $email,
            ];

            $params = [
                'securityToken' => $WC_Gateway_EBizCharge->_getUeSecurityToken(),
                'filters' => ['SearchFilter' => $filters],
                'includeCustomerToken' => 1,
                'includePaymentMethodProfiles' => 0,
                'countOnly' => 0,
                'start' => 0,
                'limit' => 1
            ];

            $api = $client->SearchCustomerList($params);
            if (isset($api->SearchCustomerListResult) && $api->SearchCustomerListResult->Count > 0) {
                $ebizCustomers = $api->SearchCustomerListResult->CustomerList;
            }

        } catch (SoapFault $ex) {
            $this->log(__METHOD__ . ', Error: ' . $ex->getMessage(), 'critical');
        }

        return $ebizCustomers;
    }

    /**
     * @param int $start
     * @param int $limit
     * @return array|string
     */
    private function searchItems($start = 0, $limit = 1000, $itemId = null)
    {
        global $WC_Gateway_EBizCharge;
        $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $WC_Gateway_EBizCharge->SoapParams());
        $results = array();

        try {
            $searchItems = $client->SearchItems(
                [
                    'securityToken' => $WC_Gateway_EBizCharge->_getUeSecurityToken(),
                    'start' => $start,
                    'limit' => $limit,
                    'itemId' => $itemId,
                    'filters' => [
                        'FieldName' => 'SoftwareId',
                        'ComparisonOperator' => 'notequal',
                        'FieldValue' => $this->software,
                    ],
                ]
            );

            if (isset($searchItems->SearchItemsResult->ItemDetails)) {
                $resultsCount = 0;
                $results = $searchItems->SearchItemsResult->ItemDetails;

                if (is_countable($results) && count($results) > 0) {
                    $resultsCount = count($results);
                }

                $this->log(__METHOD__ . ', search item result count ' . $resultsCount);
            }

        } catch (SoapFault $ex) {
            $this->log(__METHOD__ . ', Error: ' . $ex->getMessage(), 'critical');
            return $this->error = 'SoapFault: SearchItems' . $ex->getMessage();
        }

        return $results;
    }

    private function searchWpProducts($forOrder = false)
    {
        global $wpdb;

        if ($forOrder) {
            // group by ec_product_id
            $productsQuery = "SELECT p.ec_product_id, p.ID
            FROM " . $this->posts_table . " p
            WHERE p.post_type = 'product' AND p.ec_internal_id <> ''
            ";

        } else {
            $productsQuery = "SELECT p.ec_internal_id, p.ec_product_id, p.ID
            FROM " . $this->posts_table . " p
            WHERE p.post_type = 'product' AND p.ec_internal_id <> ''
            ";
        }

        return $wpdb->get_results($productsQuery, OBJECT_K);
    }

    /**
     * @param $item
     * @return int|WP_Error
     */
    private function insertWpProduct($item)
    {
        global $wpdb;

        $postData = [
            'post_title' => $item->Name,
            'post_content' => '',
            'post_content_filtered' => '',
            'post_excerpt' => $item->Description,
            'post_status' => ($item->Active == 1) ? 'publish' : 'draft',
            'post_type' => 'product',
            'meta_input' => [
                '_price' => $item->UnitPrice,
                '_stock' => $item->QtyOnHand,
                '_sku' => $item->SKU,
                '_tax_status' => ($item->Taxable == 1) ? 'taxable' : '',
                '_stock_status' => 'instock',
            ]
        ];

        $itemId = wp_insert_post($postData);

        if (is_wp_error($itemId)) {
            $this->log('Insert item error: ' . $itemId->get_error_message(), 'error');

        } else if ($itemId) {

            $now = new DateTime(null);
            $itemApiInfo = [
                'ec_internal_id' => $item->ItemInternalId,
                'ec_status' => 'Success',
                'ec_status_code' => 1,
                'ec_last_modified_date' => $now->format('Y-m-d H:i:s'),
                'ec_product_id' => $item->ItemId
            ];

            $itemApiInfoFormat = ['%s', '%s', '%d', '%s', '%d'];

            $wpdb->update($this->posts_table, $itemApiInfo, ['ID' => $itemId], $itemApiInfoFormat, ['%d']);

            $this->log('Item inserted successfully. ItemId: ' . $itemId);
        }

        return $itemId;
    }

    /**
     * download Products to eConnect
     * @return string
     */
    public function downloadItems()
    {
        $this->log(__METHOD__);
        $_messages = array();
        $_errorCount = 0;
        $_processedCount = 0;

        $start = 0;
        $limit = 1000;

        try {
            $wpProducts = $this->searchWpProducts();

            while (true) {
                $items = $this->searchItems($start, $limit);
                $itemsCount = count($items);

                if (empty($items)) {
                    break; // No more items to process, exit the loop
                }

                foreach ($items as $item) {
                    if (!array_key_exists($item->ItemInternalId, $wpProducts)) {
                        if (!empty($item->ItemId) && !empty($item->Name)) {
                            $this->insertWpProduct($item);
                            $_processedCount = $_processedCount + 1;
                        } else {
                            $this->log('Product not inserted. ItemId and ItemName was empty', 'error');
                        }
                    }
                }

                if ($itemsCount < $limit) {
                    break; // If fetched less than 1000 records, it means all records have been fetched
                }

                $start += $itemsCount; // Update the start for the next iteration
            }
        } catch (Exception $e) {
            $this->log($e->getMessage(), 'critical');

            if ($_errorCount == 0) {
                $_messages[] = array(
                    "type" => 'error',
                    "message" => "Errors have occurred during the download products process, please review the logs. First error occurred is: " . $e->getMessage()
                );
                $_errorCount++;
            }
        } finally {
            //$_processedCount = $_processedCount+1;
        }

        if ($_errorCount == 0) {
            $_messages[] = array(
                "type" => 'success',
                "message" => sprintf("Download Products has been completed. %s record(s) processed.", $_processedCount)
            );
        }

        return $this->_prepareMessages($_messages);
    }

    /**
     * @param int $start
     * @param int $limit
     * @return array|string
     */
    private function searchOrders($start = 0, $limit = 1000)
    {
        global $WC_Gateway_EBizCharge;
        $results = [];
        $filters = [
            'FieldName' => 'Software',
            'ComparisonOperator' => 'notequal',
            'FieldValue' => $this->software,
        ];

        try {
            $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $WC_Gateway_EBizCharge->SoapParams());

            $search = $client->SearchSalesOrders(
                [
                    'securityToken' => $WC_Gateway_EBizCharge->_getUeSecurityToken(),
                    'start' => $start,
                    'limit' => $limit,
                    'includeItems' => true,
                    'filters' => ['SearchFilter' => $filters]
                ]
            );

            if (isset($search->SearchSalesOrdersResult->SalesOrder)) {
                $results = $search->SearchSalesOrdersResult->SalesOrder;

                //$this->log(__METHOD__ . ', search orders result count ' . count($results));
            }

        } catch (SoapFault $ex) {
            $this->log(__METHOD__ . ', Error: ' . $ex->getMessage(), 'critical');
            //$this->error = 'SoapFault: SearchItems' . $ex->getMessage();
        }

        return $results;
    }

    private function getWpOrders()
    {
        global $wpdb, $WC_Gateway_EBizCharge;

        if ($WC_Gateway_EBizCharge->HPOS_Enabled()) {
            $query = "SELECT p.ec_order_internal_id, p.ID
            FROM " . $this->wc_orders_table . " p
            WHERE p.type = 'shop_order'
            AND p.status IN ('wc-processing', 'wc-completed', 'wc-pending')
            AND (p.ec_order_internal_id <> '' OR p.ec_order_internal_id is not null)
            ";
        } else {
            $query = "SELECT p.ec_order_internal_id, p.ID
            FROM " . $this->posts_table . " p
            WHERE p.post_type = 'shop_order'
            AND p.post_status IN ('wc-processing', 'wc-completed', 'wc-pending')
            AND (p.ec_order_internal_id <> '' OR p.ec_order_internal_id is not null)
            ";
        }
        return $wpdb->get_results($query, OBJECT_K);
    }

    /**
     * @param $order
     * @param $wpProducts
     * @param $wpCustomerId
     * @return int|WP_Error
     */
    private function insertWpOrder($order, $wpProducts, $wpCustomerId)
    {
        global $WC_Gateway_EBizCharge;
        $this->log(__METHOD__);
        if ($WC_Gateway_EBizCharge->HPOS_Enabled()) {
            return $this->insertWpOrderHpos($order, $wpProducts, $wpCustomerId);
        } else {
            return $this->insertWpOrderPost($order, $wpProducts, $wpCustomerId);
        }
    }

    private function insertWpOrderPost($order, $wpProducts, $wpCustomerId)
    {
        global $wpdb, $WC_Gateway_EBizCharge;

        $postMeta = [
            '_customer_user' => $wpCustomerId,
            '_order_currency' => $order->Currency ? $order->Currency : $WC_Gateway_EBizCharge->currency,
            '_order_total' => $order->Amount,
            '_order_tax' => $order->TotalTaxAmount,
            '_created_via' => 'Download Orders',
            '_payment_method' => 'ebizcharge',
            '_payment_method_title' => 'Credit Card (EBizCharge)',
        ];

        $salesOrderNumber = (string)$order->SalesOrderNumber;
        $salesOrderInternalId = (string)$order->SalesOrderInternalId;

        $transaction = $this->searchOrderTransactions($salesOrderNumber, $order->CustomerId);

        if (!empty($transaction)) {
            $postMeta['_payment_status'] = $transaction['PaymentStatus'];
            $postMeta['_transaction_id'] = $transaction['RefNum'];
            //$postMeta['_order_total'] = $transaction['Amount'];
            //$postMeta['_order_tax'] = $transaction['Tax'];
            $postMeta['_order_shipping'] = $transaction['Shipping'];
            $postMeta['_paid_date'] = $transaction['DateTime'];
            $postMeta['_customer_ip_address'] = $transaction['ClientIP'];
            $postMeta['_card_holder'] = $transaction['AccountHolder'];
            $postMeta['_card_number'] = $transaction['CardNumber'];
            $postMeta['_card_expiry'] = $transaction['CardExpiration'];
            $postMeta['_card_type'] = $WC_Gateway_EBizCharge->get_card_type($transaction['CardType']);

            $billing = $transaction['BillingAddress'];
            $shipping = $transaction['ShippingAddress'];

            if (!empty($billing) && isset($billing->FirstName) && !empty($shipping)) {
                $billingMeta = [
                    '_billing_first_name' => $billing->FirstName,
                    '_billing_last_name' => $billing->LastName,
                    '_billing_company' => $billing->Company,
                    '_billing_address_1' => $billing->Street,
                    '_billing_address_2' => $billing->Street2,
                    '_billing_city' => $billing->City,
                    '_billing_state' => $billing->State,
                    '_billing_postcode' => $billing->Zip,
                    '_billing_country' => $billing->Country,
                    '_billing_email' => $billing->Email,
                    '_billing_phone' => $billing->Phone,
                    '_shipping_first_name' => $shipping->FirstName,
                    '_shipping_last_name' => $shipping->LastName,
                    '_shipping_company' => $shipping->Company,
                    '_shipping_address_1' => $shipping->Street,
                    '_shipping_address_2' => $shipping->Street2,
                    '_shipping_city' => $shipping->City,
                    '_shipping_state' => $shipping->State,
                    '_shipping_postcode' => $shipping->Zip,
                    '_shipping_country' => $shipping->Country,
                ];

                $postMeta = array_merge($postMeta, $billingMeta);
            } else {
                $this->log('Order# ' . $salesOrderNumber . ' billing and shipping address not found in the transaction.');
            }
        }

	    $orderStatus = $this->getOrderStatusFromTransaction($transaction);

	    $postData = [
            'post_title' => 'Order# - ' . $salesOrderNumber . ' - ' . $order->Date,
            'post_content' => '',
            'post_content_filtered' => '',
            'post_excerpt' => '',
            'post_status' => $orderStatus,
            'post_type' => 'shop_order',
            'post_date' => $order->Date,
            'meta_input' => $postMeta,
        ];


        $orderId = wp_insert_post($postData);

        if (is_wp_error($orderId)) {
            $this->log('Order not saved: error: ' . $orderId->get_error_message(), 'error');

        } else if ($orderId) {

            $now = new DateTime(null);
            $itemApiInfo = [
                'ec_order_internal_id' => $salesOrderInternalId,
                'ec_order_status' => 'Success',
                'ec_order_last_modified_date' => $now->format('Y-m-d H:i:s'),
                'ec_customer_id' => trim($order->CustomerId),
                'ec_order_id' => $salesOrderNumber,
            ];

            $itemApiInfoFormat = ['%s', '%s', '%s', '%s', '%d'];

            // wp_insert_post can't insert the custom columns--
            $wpdb->update($this->posts_table, $itemApiInfo, ['ID' => $orderId], $itemApiInfoFormat, ['%d']);

            // update customer Ebizcharge CustNum
            if (!empty($transaction['CustNum'])) {
                update_user_meta($wpCustomerId, 'CustNum', $transaction['CustNum']);
            }

            $this->insertWpOrderItems($orderId, $order, $wpProducts, $transaction);

            $this->log('Order inserted successfully. OrderId: ' . $orderId);

            return $orderId;
        }

        return false;
    }

    private function getOrderDetails($customerId, $salesOrderNumber)
    {
        global $WC_Gateway_EBizCharge;

        try {
            $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $WC_Gateway_EBizCharge->SoapParams());

            $salesOrder = $client->GetSalesOrder(
                [
                    'securityToken' => $WC_Gateway_EBizCharge->_getUeSecurityToken(),
                    'customerId' => $customerId,
                    'salesOrderNumber' => $salesOrderNumber
                ]
            );

            if (isset($salesOrder->GetSalesOrderResult)) {
                return $salesOrder->GetSalesOrderResult;
            }

        } catch (SoapFault $ex) {
            $this->log(__METHOD__ . ', Error: ' . $ex->getMessage(), 'critical');
            $this->error = 'SoapFault: getOrder' . $ex->getMessage();
            return null;
        }

        return null;
    }

	private function getOrderStatusFromTransaction($transaction): string
	{
		$orderStatus = 'wc_pending';

		if(!empty($transaction['PaymentStatus'])) {

			if ($transaction['PaymentStatus'] == 'Captured') {
				$orderStatus = 'wc-completed';
			} elseif ($transaction['PaymentStatus'] == 'Authorized') {
				$orderStatus = 'wc-processing';
			} elseif ($transaction['PaymentStatus'] == 'Refunded') {
				$orderStatus = 'wc-refunded';
			}
		}

		return $orderStatus;
	}

    private function insertWpOrderHpos($order, $wpProducts, $wpCustomerId)
    {
        global $wpdb, $WC_Gateway_EBizCharge;
        $this->log(__METHOD__);

        $salesOrderNumber = (string)$order->SalesOrderNumber;
        $customerId = trim($order->CustomerId);

        $getEbizSalesOrderData = $this->getOrderDetails($customerId, $salesOrderNumber);
        $transaction = $this->searchOrderTransactions($salesOrderNumber, $customerId);
        if (!empty($getEbizSalesOrderData) && !empty($transaction)) {
            return $this->createOrderNew($wpCustomerId, $getEbizSalesOrderData, $transaction);
        }
        return false;
    }

    public function createOrderNew($wpCustomerId, $getEbizSalesOrderData, $transaction)
    {
        global $wpdb, $WC_Gateway_EBizCharge;
        $this->log(__METHOD__);
        if (empty($transaction)) {
            $this->log('Order not saved, Transaction not found against Order# ' . $getEbizSalesOrderData->SalesOrderNumber, 'error');
            return false;
        }
        // add billing and shipping addresses
        $orderBillingAddress = $getEbizSalesOrderData->BillingAddress;
        $addressBilling = array(
            'first_name' => $orderBillingAddress->FirstName,
            'last_name' => $orderBillingAddress->LastName,
            'company' => $orderBillingAddress->CompanyName,
            'email' => '',
            'phone' => '',
            'address_1' => $orderBillingAddress->Address1,
            'address_2' => $orderBillingAddress->Address2,
            'city' => $orderBillingAddress->City,
            'state' => $orderBillingAddress->State,
            'postcode' => $orderBillingAddress->ZipCode,
            'country' => $orderBillingAddress->Country,
        );

        // add billing and shipping addresses
        $orderShippingAddress = $getEbizSalesOrderData->ShippingAddress;
        $addressShipping = array(
            'first_name' => $orderShippingAddress->FirstName,
            'last_name' => $orderShippingAddress->LastName,
            'company' => $orderShippingAddress->CompanyName,
            'email' => '',
            'phone' => '',
            'address_1' => $orderShippingAddress->Address1,
            'address_2' => $orderShippingAddress->Address2,
            'city' => $orderShippingAddress->City,
            'state' => $orderShippingAddress->State,
            'postcode' => $orderShippingAddress->ZipCode,
            'country' => $orderShippingAddress->Country,
        );

        $order = wc_create_order();

        // add customer
        $order->set_customer_id($wpCustomerId);

        // set addresses
        $order->set_address($addressBilling, 'billing');
        $order->set_address($addressShipping, 'shipping');

        // add products
        $orderItems = $getEbizSalesOrderData->Items->Item;
        foreach ($orderItems as $item) {
            $newItemId = $this->downloadOrderItem($item);
            $order->add_product(wc_get_product($newItemId), $item->Qty);
        }

        // add shipping
        $shipping = new WC_Order_Item_Shipping();
        $shipping->set_method_title('Free shipping');
        $shipping->set_method_id('free_shipping:1'); // set an existing Shipping method ID
        $shipping->set_total(0); // optional
        $order->add_item($shipping);

        // add payment method
        $order->set_payment_method('ebizcharge');
        $order->set_payment_method_title('Credit card (EBizCharge)');

        // calculate and save
        $order->calculate_totals();
        $orderId = $order->get_id();

        if (is_wp_error($orderId)) {
            $this->log('Order not saved: error: ' . $orderId->get_error_message(), 'error');

        } else if ($orderId) {

            $now = new DateTime(null);
            $orderApiInfo = [
                'ec_order_internal_id' => $getEbizSalesOrderData->SalesOrderInternalId,
                'ec_order_status' => 'Success',
                'ec_order_last_modified_date' => $now->format('Y-m-d H:i:s'),
                'ec_customer_id' => $getEbizSalesOrderData->CustomerId,
                'ec_order_id' => $getEbizSalesOrderData->SalesOrderNumber,
                'transaction_id' => (!empty($transaction)) ? $transaction['RefNum'] : '',
            ];

            $orderApiInfoFormat = ['%s', '%s', '%s', '%s', '%d', '%s'];

            // wp_insert_post can't insert the custom columns--
            $wpdb->update($this->wc_orders_table, $orderApiInfo, ['id' => $orderId], $orderApiInfoFormat, ['%d']);

	        // update customer Ebizcharge CustNum
            if (!empty($transaction)) {

                if (!empty($transaction['CustNum'])) {
                    update_user_meta($wpCustomerId, 'CustNum', $transaction['CustNum']);
                }
                $order->update_meta_data('_payment_method', 'ebizcharge');
                $order->update_meta_data('_payment_method_title', 'EBizCharge');
                $order->update_meta_data('_payment_status', $transaction['PaymentStatus']);
                $order->set_transaction_id($transaction['RefNum']);
                $order->update_meta_data('_paid_date', $transaction['DateTime']);
                $order->update_meta_data('_customer_user', $wpCustomerId);
                $order->update_meta_data('_order_currency', $getEbizSalesOrderData->Currency);
                $order->update_meta_data('_order_total', $getEbizSalesOrderData->Amount);
                $order->set_total($getEbizSalesOrderData->Amount);
                $order->update_meta_data('_order_tax', $getEbizSalesOrderData->TotalTaxAmount);
                $order->update_meta_data('_created_via', 'Download Orders');

                if (!empty($transaction['CardNumber'])) {
                    $order->update_meta_data('_card_holder', $transaction['AccountHolder']);
                    $order->update_meta_data('_card_number', $transaction['CardNumber']);
                    $order->update_meta_data('_card_expiry', $transaction['CardExpiration']);
                    $order->update_meta_data('_card_type', $WC_Gateway_EBizCharge->get_card_type($transaction['CardType']));
                    $order->update_meta_data('_ebiz_payment_type', 'cc');
                }

                if (!empty($transaction['Account'])) {
                    $order->update_meta_data('_account_holder', $transaction['AccountHolder']);
                    $order->update_meta_data('_account_number', $transaction['Account']);
                    $order->update_meta_data('_routing_number', $transaction['Routing']);
                    $order->update_meta_data('_account_type', $WC_Gateway_EBizCharge->get_card_type($transaction['AccountType']));
                    $order->update_meta_data('_ebiz_payment_type', 'check');
                }

            }
        }

        // order status
	    $orderStatus = $this->getOrderStatusFromTransaction($transaction);
	    $order->add_order_note(__('Order# ' . $orderId . ' is successfully downloaded from gateway.'));
        $order->set_status($orderStatus, 'Order# ' . $orderId . ' is successfully downloaded.');
        $order->save();
        return $orderId;
    }

    public function downloadOrderItem($getEbizSalesOrderItem)
    {
        $this->log(__METHOD__);
        try {
            $wpProducts = $this->searchWpProducts();
            $ebizItem = $this->searchItems(0, 1, $getEbizSalesOrderItem->ItemId);

            if (!array_key_exists($ebizItem->ItemInternalId, $wpProducts)) {
                if (!empty($ebizItem->ItemId) && !empty($ebizItem->Name)) {
                    $itemId = $this->insertWpProduct($ebizItem);
                } else {
                    $this->log('Product not inserted. ItemId and ItemName was empty', 'error');
                }
            } else {
                $itemId = $wpProducts[$ebizItem->ItemInternalId]->ID;
            }
            return $itemId;
        } catch (Exception $e) {
            $this->log($e->getMessage(), 'critical');
        }
        return false;
    }

    public function downloadOrders()
    {
        $this->log(__METHOD__);
        $_messages = array();
        $_errorCount = 0;
        $_processedCount = 0;

        $itemsCount = 1;
        $start = 0;
        $limit = 1000;

        try {
            $wpOrders = $this->getWpOrders();
            $wpCustomers = $this->getWpCustomers(true);
            $wpProducts = $this->searchWpProducts(true);

            while ($itemsCount > 0 && $limit >= 1000) {
                $salesOrders = $this->searchOrders($start, $limit);
                $itemsCount = is_countable($salesOrders) ? count($salesOrders) : 0;

                $this->log($itemsCount . ' orders found.');

                if ($itemsCount > 0) {
                    if ($itemsCount === 1) {
                        $salesOrders = array($salesOrders);
                    }

                    foreach ($salesOrders as $order) {

                        $salesOrderNumber = (string)$order->SalesOrderNumber;
                        $customerId = trim($order->CustomerId);
                        $salesOrderInternalId = (string)$order->SalesOrderInternalId;

                        if ($order->Software == $this->software) {
                            $this->log('Sales Order ' . $salesOrderNumber . ' is uploaded from WooCommerce.');
                            continue;
                        }

                        if ((empty($salesOrderNumber)) || (empty($customerId))) {
                            $this->log('SalesOrderNumber(' . $salesOrderNumber . ') or CustomerId(' . $customerId . ') not valid.');
                            continue;
                        }

                        if (array_key_exists($salesOrderInternalId, $wpOrders)) {
                            // Order already exists, skip processing
                            $this->log($salesOrderInternalId . ' already exist.');
                        } else {
                            // Logic for processing orders and customers
                            if (empty($customerId) || $customerId == 0 || strtolower($customerId) == 'guest') {
                                // insert Guest order
                                if ($this->insertWpOrder($order, $wpProducts, $customerId)) {
                                    $_processedCount = $_processedCount + 1;
                                }

                            } else if (array_key_exists($customerId, $wpCustomers)) {  // if customer exists in WP
                                // get wp customer
                                $wpCustomer = $wpCustomers[$customerId];
                                // insert wp order
                                if ($this->insertWpOrder($order, $wpProducts, $wpCustomer->ID)) {
                                    $_processedCount = $_processedCount + 1;
                                }

                            } else if ($customer = $this->searchCustomerById($customerId)) {
                                // create customer first and then insert order for that customer
                                if ($wpCustomerId = $this->insertWpCustomer($customer)) {

                                    if ($this->insertWpOrder($order, $wpProducts, $wpCustomerId)) {
                                        $_processedCount = $_processedCount + 1;
                                    }

                                } else {
                                    $this->log('Order not created because customer already exist with same email address  ' . $customer->Email . ' ', 'notice');
                                }
                            }

                        }

                    }
                }

                $start += $itemsCount + 1;
                $limit = $start + 1000;
            }
        } catch (Exception $e) {
            $this->log($e->getMessage(), 'critical');

            if ($_errorCount == 0) {
                $_messages[] = array(
                    "type" => 'error',
                    "message" => "Errors have occurred during the download Orders process, please review the logs. First error occurred is: " . $e->getMessage()
                );
                $_errorCount++;
            }
        } finally {
            //$_processedCount = $_processedCount+1;
        }

        if ($_errorCount == 0) {
            $_messages[] = array(
                "type" => 'success',
                "message" => sprintf("Download Orders has been completed. %s record(s) processed.", $_processedCount)
            );
        }

        return $this->_prepareMessages($_messages);
    }

    private function insertWpOrderItems($orderId, $apiOrder, $wpProducts, $transaction)
    {
        global $wpdb;

        $this->log('Order items inserting. OrderId: ' . $orderId);

        $orderItems = isset($apiOrder->Items->Item) ? $apiOrder->Items->Item : array();

        if (!empty($orderItems) && is_object($orderItems)) { // API result is different when items count is 1
            $orderItems = [$orderItems];
        }

        foreach ($orderItems as $item) {

            if (!empty($item->ItemId) && !empty($item->Name)) {

                $item_names[] = $item->Name . '(' . intval($item->Qty) . ')';

                $orderItemId = wc_add_order_item($orderId, array(
                    'order_item_name' => $item->Name,
                    'order_item_type' => 'line_item', // product
                    'order_id' => $orderId
                ));

                if (array_key_exists($item->ItemId, $wpProducts)) {
                    $product = $wpProducts[$item->ItemId];
                    $productId = $product->ID;
                } else {
                    $productId = $item->ItemId;
                }

                $itemMeta = [
                    '_qty' => $item->Qty,
                    '_product_id' => $productId,
                    '_line_subtotal' => $item->TotalLineAmount,
                    '_line_total' => $item->TotalLineAmount,
                    //'cost' => $item->UnitPrice,
                    '_line_tax' => $item->TotalLineTax,
                ];

                foreach ($itemMeta as $key => $value) {
                    $wpdb->insert($this->wc_order_item_meta_table, [
                        'order_item_id' => $orderItemId,
                        'meta_key' => $key,
                        'meta_value' => $value,
                    ]);
                }
            } else {
                $this->log('Order item id and name was empty. item not added. OrderId: ' . $orderId, 'warning');
            }
        }

        // add shipping
        $orderItemId = wc_add_order_item($orderId, array(
            'order_item_name' => 'Flat rate',
            'order_item_type' => 'shipping',
            'order_id' => $orderId
        ));

        $itemMeta = [
            'method_id' => 'flat_rate',
            'instance_id' => 2,
            'cost' => isset($transaction['Shipping']) ? $transaction['Shipping'] : 0,
            'Items' => !empty($item_names) ? implode(', ', $item_names) : '',
            'total_tax' => 0,
        ];
        // insert shipping meta
        foreach ($itemMeta as $key => $value) {
            $wpdb->insert($this->wc_order_item_meta_table, [
                'order_item_id' => $orderItemId,
                'meta_key' => $key,
                'meta_value' => $value,
            ]);
        }
    }

    private function searchOrderTransactions($orderId, $customerId)
    {
        global $WC_Gateway_EBizCharge, $WC_Gateway_EBiz_Rec;
        $results = array();

        try {
            $searchFilter1 = array(
                'FieldName' => 'CustomerId',
                'ComparisonOperator' => 'eq',
                'FieldValue' => $customerId
            );

            $searchFilter2 = array(
                'FieldName' => 'OrderID',
                'ComparisonOperator' => 'eq',
                'FieldValue' => trim($orderId)
            );

            $searchFilters['SearchFilter'][0] = $searchFilter1;
            $searchFilters['SearchFilter'][1] = $searchFilter2;

            $searchTransactions = $WC_Gateway_EBiz_Rec->getListSearchTransactions($searchFilters, 0, 1000);
            $transactions = $searchTransactions->SearchTransactionsResult;

            if ($transactions->TransactionsMatched > 0) {
                $transactionsCount = $transactions->TransactionsReturned;
                $transactionsCountFinal = $transactionsCount - 1;

                if ((is_array($transactions->Transactions->TransactionObject)) &&
                    (count($transactions->Transactions->TransactionObject)) > 1
                ) {
                    $transactionObject = $transactions->Transactions->TransactionObject;
                    $transactionObjectFinal = $transactionObject[$transactionsCountFinal];
                } else {
                    $transactionObjectFinal = $transactions->Transactions->TransactionObject;
                }

                $transactionType = $transactionObjectFinal->TransactionType;
				$this->log('Transaction Type: ' . $transactionType . ' ,Transaction Status; '. $transactionObjectFinal->Status);

                switch ($transactionType) {
                    case "Sale":
                        if (strpos($transactionObjectFinal->Status, 'Authorized') !== false) {
                            $paymentStatus = "Authorized";
                        } else {
                            $paymentStatus = "Captured";
                        }
                        break;
                    case "Credit":
                    case "Voided Sale":
                        $paymentStatus = "Voided";
                        break;
                    case "Auth Only":
                        $paymentStatus = "Authorized";
                        break;
                    case "Refunded":
                        $paymentStatus = "Refunded";
                        break;
                    default:
                        $paymentStatus = "Authorized";
                }

                $results['PaymentStatus'] = $paymentStatus;
                $results['TransactionType'] = $transactionObjectFinal->TransactionType;
                $results['AccountHolder'] = $transactionObjectFinal->AccountHolder;

                if (isset($transactionObjectFinal->CreditCardData)) {
                    $results['CardType'] = $transactionObjectFinal->CreditCardData->CardType;
                    $results['CardCode'] = $transactionObjectFinal->CreditCardData->CardCode;
                    $results['CardExpiration'] = $transactionObjectFinal->CreditCardData->CardExpiration;
                    $results['CardNumber'] = $transactionObjectFinal->CreditCardData->CardNumber;
                }
                if (isset($transactionObjectFinal->CheckData)) {
                    $results['Account'] = $transactionObjectFinal->CheckData->Account;
                    $results['AccountType'] = $transactionObjectFinal->CheckData->AccountType;
                    $results['Routing'] = $transactionObjectFinal->CheckData->Routing;
                }
                $results['RefNum'] = $transactionObjectFinal->Response->RefNum;
                $results['Amount'] = $transactionObjectFinal->Details->Amount;
                $results['CustNum'] = $transactionObjectFinal->Response->CustNum;
                $results['CustomerID'] = $transactionObjectFinal->CustomerID;
                $results['OrderID'] = $transactionObjectFinal->Details->OrderID;
                $results['Shipping'] = $transactionObjectFinal->Details->Shipping;
                $results['Tax'] = $transactionObjectFinal->Details->Tax;
                $results['ClientIP'] = $transactionObjectFinal->ClientIP;
                $results['DateTime'] = $transactionObjectFinal->DateTime;
                $results['BillingAddress'] = $transactionObjectFinal->BillingAddress;
                $results['ShippingAddress'] = $transactionObjectFinal->ShippingAddress;
                if (empty($transactionObjectFinal->ShippingAddress->FirstName)) {
                    $results['ShippingAddress'] = $transactionObjectFinal->BillingAddress;
                }

            } else {
                $this->log(__METHOD__ . ', No result found. Filters: ', 'alert');
            }

        } catch (SoapFault $ex) {
            $this->log(__METHOD__ . ', Error: ' . $ex->getMessage(), 'critical');
        }

        return $results;
    }

    /**
     * Sync Products to eConnect
     *
     * @return string
     */
    public function syncItem($id = null)
    {
        global $wpdb, $WC_Gateway_EBizCharge;
        $_messages = array();
        $_errorCount = 0;
        $_processedCount = 0;

        $securityToken = $WC_Gateway_EBizCharge->_getUeSecurityToken();

        try {
            $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $this->SoapParamsEconnect());

            $productsQuery = "SELECT p.*
            FROM " . $this->posts_table . " p
            WHERE p.post_type = 'product'
            AND ((p.ec_last_modified_date is null)
            OR TIMESTAMPDIFF(minute, p.ec_last_modified_date, IFNULL( p.post_modified, p.post_date )) > 0)
            ";

            //filter if update is for a single record
            if ($id != null) {
                $productsQuery .= " AND p.ID = {$id}";
            }

            foreach ($wpdb->get_results($productsQuery) as $post) {

                $postMeta = array_map(
                    function ($a) {
                        return $a[0];
                    }, get_post_meta($post->ID)
                );

                $product = array_merge((array)$post, $postMeta);

                try {
                    $itemDetails = array(
                        'ItemId' => $product['ID'],
                        'Name' => $product['post_title'],
                        'SKU' => '',
                        'Description' => isset($product['post_excerpt']) ? $product['post_excerpt'] : $product['post_name'],
                        'UnitPrice' => isset($product['_price']) ? floatval($product['_price']) : 0,
                        'UnitCost' => '0',
                        'UnitOfMeasure' => '',
                        'Active' => ($product['post_status'] == 'publish') ? true : false,
                        'ItemType' => 'simple',
                        'QtyOnHand' => isset($product['_stock']) ? floatval($product['_stock']) : 9999,
                        'UPC' => '',
                        'Taxable' => (isset($product['_tax_status']) && $product['_tax_status'] == 'taxable') ? 1 : 0,
                        'TaxRate' => '0',
                        'ItemCategoryId' => '',
                        'TaxCategoryID' => '',
                        'SoftwareId' => $this->software,
                        'ImageUrl' => '',
                        'ItemNotes' => '',
                        'GrossPrice' => 0,
                        'WarrantyDiscount' => 0,
                        'SalesDiscount' => 0,
                    );
                    // insert product
                    if (empty($product['ec_internal_id'])) {
                        $parameters = array(
                            'securityToken' => $securityToken,
                            'itemDetails' => $itemDetails
                        );

                        $addItemResponse = $client->AddItem($parameters);
                        $obj = $addItemResponse->AddItemResult;

                    } else { // update product
                        if (!empty($product['ec_product_id'])) {
                            $itemDetails['ItemId'] = $product['ec_product_id'];
                        }

                        $parameters = array(
                            'securityToken' => $securityToken,
                            'itemDetails' => $itemDetails,
                            'itemId' => $itemDetails['ItemId'],
                            'itemInternalId' => $product['ec_internal_id']
                        );

                        $updateItemResponse = $client->UpdateItem($parameters);
                        $obj = $updateItemResponse->UpdateItemResult;
                    }

                    if ($obj->Status == "Error") {
                        if ($_errorCount == 0)
                            $_messages[] = array("type" => 'error', "message" => "Errors have occurred during the sync products process, please review the logs. First error occurred is: " . $obj->Error);

                        $_errorCount++;
                    }

                    $whereCondition = array('ID' => (int)$product['ID']);
                    $obj->ec_product_id = $itemDetails['ItemId'];

                    $this->updateWPTable($this->posts_table, $obj, $whereCondition);

                    $this->log('Product info saved');

                } catch (Exception $e) {
                    $this->log($e->getMessage(), 'critical');

                    if ($_errorCount == 0)
                        $_messages[] = array("type" => 'error', "message" => "Errors have occurred during the sync products process, please review the logs. First error occurred is: " . $e->getMessage());

                    $_errorCount++;
                } finally {
                    $_processedCount++;
                }
            }
            array_unshift($_messages, array("type" => 'notice', "message" => sprintf("Sync Products process has been completed. %s record(s) processed.", $_processedCount)));

            if ($_errorCount == 0)
                $_messages[] = array("type" => 'success', "message" => "Completed the sync product process with no errors.");
        } catch (Exception $e) {
            $this->log($e->getMessage(), 'critical');
            $_messages[] = array("type" => 'error', "message" => $e->getMessage());
        }

        return $this->_prepareMessages($_messages);
    }

    private function getWpProductsById()
    {
        global $wpdb;

        $productsQuery = "SELECT p.ID, p.ec_product_id
            FROM " . $this->posts_table . " p
            WHERE p.post_type = 'product' AND p.ec_internal_id <> ''";

        return $wpdb->get_results($productsQuery, OBJECT_K);
    }

    /**
     * return order object
     * @param $order
     * @param $customerId
     * @return array
     */
    private function getOrderObject($order, $customerId)
    {
        global $WC_Gateway_EBizCharge;
        if ($WC_Gateway_EBizCharge->HPOS_Enabled()) {

            $order_data = $order->get_data();
            $paymentStatus = !empty($order->get_meta('_payment_status')) ? $order->get_meta('_payment_status') : '';

            $orderParams = [
                'SalesOrderNumber' => $order_data['id'],
                'CustomerId' => $customerId,
                'Date' => $order_data['date_created']->date('Y-m-d H:i:s'),
                'Currency' => $order_data['currency'],
                'Amount' => $order_data['total'],
                'DueDate' => $order_data['date_created']->date('Y-m-d H:i:s'),
                'DateUploaded' => $order_data['date_created']->date('Y-m-d H:i:s'),
                'AmountDue' => ($paymentStatus == 'Captured') ? '0' : $order_data['total'],
                'PoNum' => $order_data['id'],
                'TotalTaxAmount' => $order_data['total_tax'],
                'Description' => 'Order# ' . $order_data['id'],
                'NotifyCustomer' => 'false',
                'BillingAddress' => $this->getOrderBillingAddress($order),
                'ShippingAddress' => $this->getOrderShippingAddress($order),
                'Memo' => 'No',
                'ShipDate' => $order_data['date_created']->date('Y-m-d H:i:s'),
                'ShipVia' => 'NA',
                'IsToBeEmailed' => 'false',
                'IsToBePrinted' => 'false',
                'UniqueId' => $order_data['id'],
                'Software' => $this->software,
            ];

        } else {
            $paymentStatus = $order['_payment_status'] ?? '';

            $orderParams = [
                'SalesOrderNumber' => $order['ID'],
                'CustomerId' => $customerId,
                'Date' => $order['post_date'],
                'Currency' => $order['_order_currency'] ? $order['_order_currency'] : $WC_Gateway_EBizCharge->currency,
                'Amount' => $order['_order_total'],
                'DueDate' => $order['post_date'],
                'DateUploaded' => $order['post_date'],
                'AmountDue' => ($paymentStatus == 'Captured') ? '0' : $order['_order_total'],
                'PoNum' => $order['ID'],
                'TotalTaxAmount' => $order['_order_tax'],
                'Description' => isset($order['_billing_address_index']) ? $order['_billing_address_index'] : 'Order# ' . $order['ID'],
                'NotifyCustomer' => 'false',
                'BillingAddress' => $this->getOrderBillingAddress($order),
                'ShippingAddress' => $this->getOrderShippingAddress($order),
                'Memo' => 'No',
                'ShipDate' => $order['post_date'],
                'ShipVia' => 'NA',
                'IsToBeEmailed' => 'false',
                'IsToBePrinted' => 'false',
                'UniqueId' => $order['ID'],
                'Software' => $this->software,
            ];
        }

        return $orderParams;
    }

    /**
     * Sync order and transaction to eConnect
     * @param null $id
     * @param null $tranNo
     * @param null $tranType
     * @return string
     */
    public function syncOrder($id = null)
    {
        global $WC_Gateway_EBizCharge;

        if ($WC_Gateway_EBizCharge->HPOS_Enabled()) {
            return $this->syncOrderWc($id);
        } else {
            return $this->syncOrderPost($id);
        }

    }

    public function syncOrderPost($id = null)
    {
        global $wpdb, $WC_Gateway_EBizCharge;
        $_messages = array();
        $_errorCount = 0;
        $_processedCount = 0;

        try {

            $securityToken = $WC_Gateway_EBizCharge->_getUeSecurityToken();
            $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $this->SoapParamsEconnect());

            $query = "SELECT p.* FROM " . $this->posts_table . " p WHERE p.post_type = 'shop_order' AND p.post_status IN ('wc-processing', 'wc-completed')";

            //filter if update is for a single record
            if ($id != null) {
                $query .= " AND p.ID = {$id}";
            } else {
                $query .= " AND ((p.ec_order_last_modified_date is null)
					OR TIMESTAMPDIFF(minute, p.ec_order_last_modified_date, IFNULL( p.post_modified, p.post_date )) > 0)";
            }

            $wpOrders = $wpdb->get_results($query);

            if (!empty($wpOrders)) {

                $wpProducts = $this->getWpProductsById();

                foreach ($wpOrders as $post) {

                    $postMeta = array_map(function ($a) {
                        return $a[0];
                    }, get_post_meta($post->ID));

                    $order = array_merge((array)$post, $postMeta);
                    // if No customer: Adding Record error. Cannot insert the value NULL into column 'PayerInternalId', table 'ebizportalgroup1-db.dbo.SalesOrders'; column does not allow nulls. INSERT fails.

                    try {
                        $customerInternalId = null;
                        $ebizCustomerToken = null;

                        if (intval($order['_customer_user']) > 0) {
                            $customerId = intval($order['_customer_user']);
                            if ($user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE ID = %s LIMIT 1", $customerId))) {
                                $customerId = !empty($user->ec_customer_id) ? $user->ec_customer_id : $customerId;
                                $customerInternalId = $user->ec_internal_id;
                                $ebizCustomerToken = !empty($user->ec_customer_token) ? $user->ec_customer_token : null;
                            }
                        } else {
                            $customerId = 'Guest';
                        }

                        $salesOrder = $this->getOrderObject($order, $customerId);

                        $lineItems = $this->getOrderItems($order['ID'], $wpProducts);

                        if (count($lineItems) > 0) {
                            $salesOrder['Items'] = $lineItems;
                        }
                        // add sales order
                        if (empty($order['ec_order_internal_id'])) {

                            $parameters = array(
                                'securityToken' => $securityToken,
                                'salesOrder' => $salesOrder
                            );

                            $addOrderResponse = $client->AddSalesOrder($parameters);
                            $syncOrderResult = $addOrderResponse->AddSalesOrderResult;

                            $tranNo = $order['_transaction_id'] ?? false;

                            if ($syncOrderResult->Status == "Success" && !empty($tranNo) && !empty($customerInternalId)) {
                                $paymentStatus = (isset($order['_payment_status']) && $order['_payment_status'] == 'Captured') ? 'sale' : 'authonly';
                                // sync transaction
                                $addTranResult = $client->AddApplicationTransaction(array(
                                    'securityToken' => $securityToken,
                                    'applicationTransactionRequest' => [
                                        'CustomerInternalId' => $customerInternalId,
                                        'TransactionId' => $tranNo,
                                        'TransactionTypeId' => $paymentStatus,
                                        'LinkedToTypeId' => 'SalesOrder',
                                        'LinkedToExternalUniqueId' => $order['ID'],
                                        'LinkedToInternalId' => $syncOrderResult->SalesOrderInternalId,
                                        'SoftwareId' => $this->software,
                                        'TransactionDate' => date('Y-m-d H:i:s'),
                                        'TransactionNotes' => 'Order Id: ' . $order['ID'],
                                    ]
                                ));

                                $this->log('success: application transaction added for WC Order# ' . $order['ID'] . ' Response:');

                            } else {
                                $this->log('Failed: application transaction not added. WC Order#: ' . $order['ID'] . ' CustomerInternalId: ' . $customerInternalId . ' transaction No#:' . $tranNo, 'error');
                            }

                        } else { // update order
                            // use econnect customerId in update
                            if (!empty($order['ec_customer_id']) && $order['ec_customer_id'] != 'Guest') {
                                $salesOrder['CustomerId'] = $order['ec_customer_id'];
                            }
                            // use econnect orderId in update
                            if (!empty($order['ec_order_id'])) {
                                $salesOrder['SalesOrderNumber'] = $order['ec_order_id'];
                                $salesOrder['PoNum'] = $order['ec_order_id'];
                                $salesOrder['UniqueId'] = $order['ec_order_id'];
                            }

                            $parameters = array(
                                'securityToken' => $securityToken,
                                'salesOrder' => $salesOrder,
                                'SalesOrderNumber' => $order['ID'],
                                'salesOrderInternalId' => $order['ec_order_internal_id']
                            );

                            $updateOrderResponse = $client->UpdateSalesOrder($parameters);
                            $syncOrderResult = $updateOrderResponse->UpdateSalesOrderResult;
                        }

                        if ($syncOrderResult->Status == "Error") {
                            $this->log('Sales order not synced to Econnect.');
                            $this->log($syncOrderResult);

                            if ($_errorCount == 0)
                                $_messages[] = array(
                                    'type' => 'error',
                                    'message' => 'Errors have occurred during the Sales order sync process, please review the logs. First error occurred is: ' . $syncOrderResult->Error
                                );

                            $_errorCount++;
                        }

                        $dataCondFormat = '';
                        $now = new DateTime(null);
                        $now->modify("+10 seconds"); //add 10 seconds to make sure sync last_modified_date is greater than object update date
                        $data = array();
                        $data['ec_order_internal_id'] = $syncOrderResult->SalesOrderInternalId;
                        $data['ec_order_status'] = $syncOrderResult->Status;
                        $data['ec_order_error'] = $syncOrderResult->Error;
                        $data['ec_order_last_modified_date'] = $now->format('Y-m-d H:i:s');

                        if (empty($order['ec_order_id'])) {
                            $data['ec_order_id'] = (int)$order['ID'];
                        }
                        if (empty($order['ec_customer_id'])) {
                            $data['ec_customer_id'] = $salesOrder['CustomerId'];
                        }

                        $whereCondition = array('ID' => (int)$order['ID']);

                        $wpdb->update($this->posts_table, $data, $whereCondition);

                        $this->log('Sales order info saved. Order ID:' . (int)$order['ID']);

                    } catch (Exception $e) {
                        $this->log($e->getMessage(), 'critical');

                        if ($_errorCount == 0)
                            $_messages[] = array("type" => 'error', "message" => "Errors have occurred during the Sales orders sync process, please review the logs.
                        First error occurred is: " . $e->getMessage());

                        $_errorCount++;
                    } finally {
                        $_processedCount++;
                    }

                }
            }

            array_unshift($_messages, array("type" => 'notice', "message" => sprintf("Sync Orders process has been completed. %s record(s) processed.", $_processedCount)));

            if ($_errorCount == 0)
                $_messages[] = array("type" => 'success', "message" => "Completed the Sales order sync process with no errors.");

        } catch (Exception $e) {
            $this->log($e->getMessage(), 'critical');
            $_messages[] = array("type" => 'error', "message" => $e->getMessage());
        }

        return $this->_prepareMessages($_messages);
    }

    public function syncOrderWc($id = null)
    {
        global $wpdb, $WC_Gateway_EBizCharge;
        $_messages = array();
        $_errorCount = 0;
        $_processedCount = 0;

        try {

            $securityToken = $WC_Gateway_EBizCharge->_getUeSecurityToken();
            $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $this->SoapParamsEconnect());

            $query = "SELECT p.* FROM " . $this->wc_orders_table . " p WHERE p.type = 'shop_order' AND p.status IN ('wc-processing', 'wc-completed')";

            //filter if update is for a single record
            if ($id != null) {
                $query .= " AND p.id = {$id}";
            } else {
                $query .= " AND ((p.ec_order_last_modified_date is null)
					OR TIMESTAMPDIFF(minute, p.ec_order_last_modified_date, IFNULL( p.date_updated_gmt, p.date_created_gmt )) > 0)";
            }

            $wpOrders = $wpdb->get_results($query);

            if (!empty($wpOrders)) {

                $wpProducts = $this->getWpProductsById();

                foreach ($wpOrders as $order) {
                    if (empty($order)) {
                        $this->log('Order not found', 'notice');
                        continue;
                    }

                    $wcOrder = wc_get_order($order->id);

                    if (empty($wcOrder)) {
                        $this->log('WC Order not found', 'notice');
                        continue;
                    }

                    if (empty($order_data = $wcOrder->get_data())) {
                        $this->log('WC Order Data not found', 'notice');
                        continue;
                    }

                    try {
                        $customerInternalId = null;
                        $wcUser = get_user_by('id', $wcOrder->get_customer_id());

                        if ($wcUser) {
                            $wcUserData = $wcUser->data;
                            $customerId = !empty($wcUserData->ec_customer_id) ? $wcUserData->ec_customer_id : $wcUserData->ID;
                            $customerInternalId = !empty($wcUserData->ec_internal_id) ? $wcUserData->ec_internal_id : null;
                            $ebizCustomerToken = !empty($wcUserData->ec_customer_token) ? $wcUserData->ec_customer_token : null;
                        } else {
                            $customerId = 'Guest';
                        }

                        $order_id = $wcOrder->get_id();

                        $salesOrder = $this->getOrderObject($wcOrder, $customerId);
                        $lineItems = $this->getOrderItems($order_id, $wpProducts);

                        if (count($lineItems) > 0) {
                            $salesOrder['Items'] = $lineItems;
                        }
                        // add sales order
                        if (empty($order->ec_order_internal_id)) {

                            $parameters = array(
                                'securityToken' => $securityToken,
                                'salesOrder' => $salesOrder
                            );

                            $addOrderResponse = $client->AddSalesOrder($parameters);
                            $syncOrderResult = $addOrderResponse->AddSalesOrderResult;

                            $tranNo = $order->transaction_id ?? false;

                            if ($syncOrderResult->Status == "Success" && !empty($tranNo) && !empty($customerInternalId)) {

                                $paymentStatus = (!empty($wcOrder->get_meta('_payment_status'))
                                    && $wcOrder->get_meta('_payment_status') == 'Captured') ? 'sale' : 'authonly';
                                // sync transaction
                                $addTranResult = $client->AddApplicationTransaction(array(
                                    'securityToken' => $securityToken,
                                    'applicationTransactionRequest' => [
                                        'CustomerInternalId' => $customerInternalId,
                                        'TransactionId' => $tranNo,
                                        'TransactionTypeId' => $paymentStatus,
                                        'LinkedToTypeId' => 'SalesOrder',
                                        'LinkedToExternalUniqueId' => $order_id,
                                        'LinkedToInternalId' => $syncOrderResult->SalesOrderInternalId,
                                        'SoftwareId' => $this->software,
                                        'TransactionDate' => date('Y-m-d H:i:s'),
                                        'TransactionNotes' => 'Order Id: ' . $order_id,
                                    ]
                                ));

                                $this->log('success: application transaction added for WC Order# ' . $order_id);
                            } else {
                                $this->log('Failed: application transaction not added. WC Order#: ' . $order_id
                                    . ' CustomerInternalId: ' . $customerInternalId . ' transaction No#:' . $tranNo, 'error');
                            }

                        } else { // update order
                            // use econnect customerId in update
                            if (!empty($order->ec_customer_id) && $order->ec_customer_id != 'Guest') {
                                $salesOrder['CustomerId'] = $order->ec_customer_id;
                            }
                            // use econnect orderId in update
                            if (!empty($order->ec_order_id)) {
                                $salesOrder['SalesOrderNumber'] = $order->ec_order_id;
                                $salesOrder['PoNum'] = $order->ec_order_id;
                                $salesOrder['UniqueId'] = $order->ec_order_id;
                            }

                            $parameters = array(
                                'securityToken' => $securityToken,
                                'salesOrder' => $salesOrder,
                                'SalesOrderNumber' => $order_id,
                                'salesOrderInternalId' => $order->ec_order_internal_id
                            );

                            $updateOrderResponse = $client->UpdateSalesOrder($parameters);
                            $syncOrderResult = $updateOrderResponse->UpdateSalesOrderResult;
                        }

                        if ($syncOrderResult->Status == 'Error') {
                            $this->log('Sales order not synced to Econnect.', 'error');
                            $this->log($syncOrderResult);

                            if ($_errorCount == 0)
                                $_messages[] = array(
                                    'type' => 'error',
                                    'message' => 'Errors have occurred during the Sales order sync process, please review the logs. First error occurred is: ' . $syncOrderResult->Error
                                );

                            $_errorCount++;
                        }

                        $dataCondFormat = '';
                        $now = new DateTime(null);
                        $now->modify("+10 seconds"); //add 10 seconds to make sure sync last_modified_date is greater than object update date
                        $data = array();
                        $data['ec_order_internal_id'] = $syncOrderResult->SalesOrderInternalId ?? null;
                        $data['ec_order_status'] = $syncOrderResult->Status;
                        $data['ec_order_error'] = $syncOrderResult->Error;
                        $data['ec_order_last_modified_date'] = $now->format('Y-m-d H:i:s');

                        if (empty($order->ec_order_id)) {
                            $data['ec_order_id'] = (int)$order_id;
                            $dataCondFormat = '%d';
                        }
                        if (empty($order->ec_customer_id)) {
                            $data['ec_customer_id'] = $salesOrder['CustomerId'];
                            $dataCondFormat = '%s';
                        }

                        $dataFormat = ['%s', '%s', '%s', '%s', '%s', $dataCondFormat];

                        $whereCondition = array('ID' => (int)$order_id);

                        $wpdb->update($this->wc_orders_table, $data, $whereCondition, $dataFormat, ['%d']);

                        $this->log('Sales order info saved. Order ID:' . $order_id);

                    } catch (Exception $e) {
                        $this->log($e->getMessage(), 'critical');

                        if ($_errorCount == 0)
                            $_messages[] = array("type" => 'error', "message" => "Errors have occurred during the Sales orders sync process, please review the logs.
                        First error occurred is: " . $e->getMessage());

                        $_errorCount++;
                    } finally {
                        $_processedCount++;
                    }

                }
            }

            array_unshift($_messages, array("type" => 'notice', "message" => sprintf("Sync Orders process has been completed. %s record(s) processed.", $_processedCount)));

            if ($_errorCount == 0)
                $_messages[] = array("type" => 'success', "message" => "Completed the Sales order sync process with no errors.");

        } catch (Exception $e) {
            $this->log($e->getMessage(), 'critical');
            $_messages[] = array("type" => 'error', "message" => $e->getMessage());
        }

        return $this->_prepareMessages($_messages);
    }

    public function getOrderBillingAddress($order): array
    {
        global $WC_Gateway_EBizCharge;
        if ($WC_Gateway_EBizCharge->HPOS_Enabled()) {
            $order_data = $order->get_data(); // The Order data
            if (!empty($order_data['billing']['first_name'])) {
                return array(
                    'FirstName' => $order_data['billing']['first_name'],
                    'LastName' => $order_data['billing']['last_name'] ?? '',
                    'CompanyName' => $order_data['billing']['company'] ?? '',
                    'Address1' => $order_data['billing']['address_1'] ?? 'N/A',
                    'Address2' => $order_data['billing']['address_2'] ?? 'N/A',
                    'City' => $order_data['billing']['city'] ?? 'N/A',
                    'State' => $order_data['billing']['state'] ?? 'N/A',
                    'ZipCode' => $order_data['billing']['postcode'] ?? 'N/A',
                    'Country' => $order_data['billing']['country'] ?? 'N/A',
                    'IsDefault' => false
                );
            }
        } else {
            if (!empty($order['_billing_first_name'])) {
                return array(
                    'FirstName' => $order['_billing_first_name'],
                    'LastName' => $order['_billing_last_name'] ?? '',
                    'CompanyName' => $order['_billing_company'] ?? '',
                    'Address1' => $order['_billing_address_1'] ?? 'N/A',
                    'Address2' => $order['_billing_address_2'] ?? 'N/A',
                    'City' => $order['_billing_city'] ?? 'N/A',
                    'State' => $order['_billing_state'] ?? 'N/A',
                    'ZipCode' => $order['_billing_postcode'] ?? 'N/A',
                    'Country' => $order['_billing_country'] ?? 'N/A',
                    'IsDefault' => false
                );
            }
        }

        return [];
    }

    public function getOrderShippingAddress($order): array
    {
        global $WC_Gateway_EBizCharge;

        if ($WC_Gateway_EBizCharge->HPOS_Enabled()) {

            $orderData = $order->get_data(); // The Order data
            if (!empty($orderData['shipping']['first_name'])) {
                return array(
                    'FirstName' => $orderData['shipping']['first_name'],
                    'LastName' => $orderData['shipping']['last_name'] ?? '',
                    'CompanyName' => $orderData['shipping']['company'] ?? '',
                    'Address1' => $orderData['shipping']['address_1'] ?? 'N/A',
                    'Address2' => $orderData['shipping']['address_2'] ?? 'N/A',
                    'City' => $orderData['shipping']['city'] ?? 'N/A',
                    'State' => $orderData['shipping']['state'] ?? 'N/A',
                    'ZipCode' => $orderData['shipping']['postcode'] ?? 'N/A',
                    'Country' => $orderData['shipping']['country'] ?? 'N/A',
                    'IsDefault' => false
                );
            }
        } else {
            if (!empty($order['_shipping_first_name'])) {
                return array(
                    'FirstName' => $order['_shipping_first_name'],
                    'LastName' => $order['_shipping_last_name'] ?? '',
                    'CompanyName' => $order['_shipping_company'] ?? '',
                    'Address1' => $order['_shipping_address_1'] ?? 'N/A',
                    'Address2' => $order['_shipping_address_2'] ?? 'N/A',
                    'City' => $order['_shipping_city'] ?? 'N/A',
                    'State' => $order['_shipping_state'] ?? 'N/A',
                    'ZipCode' => $order['_shipping_postcode'] ?? 'N/A',
                    'Country' => $order['_shipping_country'] ?? 'N/A',
                    'IsDefault' => false,
                );
            }
        }

        return [];
    }

    public function getOrderItems($orderId, $wpProducts)
    {
        $lineItems = array();
        $lineNumber = 0;

        $order = wc_get_order($orderId);
        // Iterating through each WC_Order_Item_Product objects
        foreach ($order->get_items() as $key => $item) {

            $product = $item->get_product(); // the WC_Product object

            ## Access Order Items data properties (in an array of values) ##
            $item_data = $item->get_data();
            if (array_key_exists($item_data['product_id'], $wpProducts)) {
                $tempProduct = $wpProducts[$item_data['product_id']];
                $productId = $tempProduct->ec_product_id;
            } else {
                $productId = $item_data['product_id'];
            }

            $lineItems[] = array(
                'ItemId' => $productId,
                'Name' => $item_data['name'],
                'Description' => $item_data['name'],
                'UnitPrice' => is_object($product) ? $product->get_price() : $item_data['total'],
                'Qty' => $item_data['quantity'],
                'Taxable' => $item_data['total_tax'] > 0,
                'TaxRate' => '0',
                'TotalLineAmount' => $item_data['total'],
                'TotalLineTax' => $item_data['total_tax'],
                'ItemLineNumber' => ++$lineNumber,
                'GrossPrice' => 0,
                'WarrantyDiscount' => 0,
                'SalesDiscount' => 0,
            );
        }

        return $lineItems;
    }

    /**
     * Return invoice object
     * @param $order
     * @param $customerId
     * @return array
     */
    private function getInvoiceObject($order, $customerId)
    {
        global $WC_Gateway_EBizCharge;

        if ($WC_Gateway_EBizCharge->HPOS_Enabled()) {

            $orderData = $order->get_data();
            $invoiceParams = [
                'InvoiceNumber' => $orderData['id'],
                'CustomerId' => $customerId,
                'InvoiceDate' => $orderData['date_created']->date('Y-m-d H:i:s'),
                'Currency' => $orderData['currency'],
                'InvoiceAmount' => $orderData['total'],
                'AmountDue' => $orderData['total'],
                'InvoiceDueDate' => $orderData['date_created']->date('Y-m-d H:i:s'),
                'SoNum' => $orderData['id'],
                'TotalTaxAmount' => $orderData['total_tax'],
                'InvoiceUniqueId' => $orderData['id'],
                'InvoiceDescription' => 'Invoice# ' . $orderData['id'],
                'NotifyCustomer' => 'false',
                'Software' => $this->software,
            ];
        } else {
            $invoiceParams = [
                'InvoiceNumber' => $order['ID'],
                'CustomerId' => $customerId,
                'InvoiceDate' => $order['post_date'],
                'Currency' => $order['_order_currency'] ? $order['_order_currency'] : $WC_Gateway_EBizCharge->currency,
                'InvoiceAmount' => $order['_order_total'],
                'AmountDue' => $order['_order_total'],
                'InvoiceDueDate' => $order['post_date'],
                'SoNum' => $order['ID'],
                'TotalTaxAmount' => $order['_order_tax'],
                'InvoiceUniqueId' => $order['ID'],
                'InvoiceDescription' => isset($order['_billing_address_index']) ? $order['_billing_address_index'] : 'Order# ' . $order['ID'],
                'NotifyCustomer' => 'false',
                'Software' => $this->software,
            ];
        }

        return $invoiceParams;
    }

    /**
     * Sync order Invoices and payment to eConnect
     * @param null $id
     * @return string
     */
    public function syncInvoice($id = null)
    {
        global $WC_Gateway_EBizCharge;

        if ($WC_Gateway_EBizCharge->HPOS_Enabled()) {
            return $this->syncInvoiceWc($id);
        } else {
            return $this->syncInvoicePost($id);
        }

    }

    public function syncInvoicePost($id = null)
    {
        global $wpdb, $WC_Gateway_EBizCharge;
        $_messages = array();
        $_errorCount = 0;
        $_processedCount = 0;
        $this->log(__METHOD__);

        $securityToken = $WC_Gateway_EBizCharge->_getUeSecurityToken();

        try {

            $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $this->SoapParamsEconnect());

            $query = "SELECT p.* FROM " . $this->posts_table . " p WHERE p.post_type = 'shop_order' AND p.post_status IN ('wc-completed', 'wc-processing')";
            //filter if update is for a single record

            if ($id != null) {
                $query .= " AND p.ID = {$id}";
            } else {
                $query .= " AND ((p.ec_last_modified_date is null)
                    OR TIMESTAMPDIFF(minute, p.ec_last_modified_date, IFNULL( p.post_modified, p.post_date )) > 0)";
            }

            $wpOrders = $wpdb->get_results($query);

            if (!empty($wpOrders)) {

                $wpProducts = $this->getWpProductsById();

                foreach ($wpOrders as $post) {

                    $postMeta = array_map(function ($a) {
                        return $a[0];
                    }, get_post_meta($post->ID));

                    $order = array_merge((array)$post, $postMeta);

                    if (isset($order['_payment_status']) && $order['_payment_status'] != 'Captured') {
                        $this->log('Order payment status is not captured. skipping this invoice Order#' . $order['ID'] . ' payment status: ' . $order['_payment_status'], 'notice');
                        continue;
                    }

                    if (intval($order['_customer_user']) > 0) {
                        $customerId = intval($order['_customer_user']);

                        $wcUser = get_user_by('id', $customerId);
                        if ($wcUser) {
                            $wcUserData = $wcUser->data;
                            $customerId = !empty($wcUserData->ec_customer_id) ? $wcUserData->ec_customer_id : $wcUserData->ID;
                            $customerInternalId = !empty($wcUserData->ec_internal_id) ? $wcUserData->ec_internal_id : null;
                            $ebizCustomerToken = !empty($wcUserData->ec_customer_token)
                                ? $wcUserData->ec_customer_token
                                : get_user_meta($wcUserData->ID, 'CustNum');
                        }

                    } else {
                        $this->log('Invoice cannot be created for Guest customer order. WC Order#' . $order['ID'], 'notice');
                        continue;
                    }

                    try {
                        $invoice = $this->getInvoiceObject($order, $customerId);
                        $lineItems = $this->getOrderItems($order['ID'], $wpProducts);

                        if (count($lineItems) > 0) {
                            $invoice['Items'] = $lineItems;
                        }
                        // add invoice
                        if (empty($order['ec_internal_id'])) {

                            $parameters = array(
                                'securityToken' => $securityToken,
                                'invoice' => $invoice
                            );

                            $addInvoiceResponse = $client->AddInvoice($parameters);
                            $syncInvoiceResult = $addInvoiceResponse->AddInvoiceResult;
                            if ($syncInvoiceResult->Status == 'Success') {
                                $this->log('Invoice has been synced successfully for order Id: ' . $order['ID'] . ', Invoice Id: ' . $syncInvoiceResult->InvoiceInternalId);
                            }
                            // add invoice payment
                            $tranNo = $order['_transaction_id'] ?? false;
                            //$custNo = $order['CustNum'] ?? 0;
                            if ($syncInvoiceResult->Status == 'Success' && !empty($tranNo) && !empty($ebizCustomerToken)) {

                                $paymentDetails[0] = [
                                    'InvoiceInternalId' => $syncInvoiceResult->InvoiceInternalId,
                                    'PaidAmount' => $invoice['InvoiceAmount'],
                                    'Currency' => $invoice['Currency'],
                                ];
                                $paymentParms = [
                                    'securityToken' => $securityToken,
                                    'payment' => [
                                        'InvoicePaymentDetails' => $paymentDetails,
                                        'CustomerId' => $customerId,
                                        'RefNum' => $tranNo,
                                        'Currency' => $invoice['Currency'],
                                        'TotalPaidAmount' => $invoice['InvoiceAmount'],
                                        'CustNum' => $ebizCustomerToken,
                                        'PaymentMethodId' => '?',
                                        'PaymentMethodType' => '?',
                                        'Software' => $this->software,
                                    ],
                                ];

                                $invoicePayment = $client->AddInvoicePayment($paymentParms);
                                $invoicePaymentResult = $invoicePayment->AddInvoicePaymentResult;

                                if ($invoicePaymentResult->Status == "Success") {
                                    $this->log('Invoice payment added successfully for invoice Id: ' . $syncInvoiceResult->InvoiceInternalId);
                                } else {
                                    $this->log('Invoice payment not added for order# ' . $order['ID']);
                                    $this->log($invoicePayment);
                                }
                            } else {
                                $this->log('Invoice payment not added for order# ' . $order['ID']);
                            }

                        } else { // update invoice
                            // use econnect customerId in update
                            if (!empty($order['ec_customer_id'])) {
                                $invoice['CustomerId'] = $order['ec_customer_id'];
                            }
                            // use econnect invoice id in update
                            if (!empty($order['ec_invoice_id'])) {
                                $invoice['InvoiceNumber'] = $order['ec_invoice_id'];
                                $invoice['SoNum'] = $order['ec_invoice_id'];
                                $invoice['InvoiceUniqueId'] = $order['ec_invoice_id'];
                            }

                            $parameters = array(
                                'securityToken' => $securityToken,
                                'invoice' => $invoice,
                                'invoiceNumber' => $invoice['InvoiceNumber'],
                                'invoiceInternalId' => $order['ec_internal_id'],
                                'customerId' => $customerId // WOO-712: Fixed: expects the parameter '@prmPayerId', which was not supplied
                            );

                            $updateInvoiceResponse = $client->UpdateInvoice($parameters);
                            $syncInvoiceResult = $updateInvoiceResponse->UpdateInvoiceResult;
                        }

                        if ($syncInvoiceResult->Status == "Error") {
                            $this->log('Invoice not synced to Econnect.');
                            $this->log($syncInvoiceResult);

                            if ($_errorCount == 0)
                                $_messages[] = array("type" => 'error', "message" => "Errors have occurred during the Invoice sync process, please review the logs. First error occurred is: " . $syncInvoiceResult->Error);

                            $_errorCount++;
                        }

                        $whereCondition = array('ID' => (int)$order['ID']);
                        $syncInvoiceResult->ec_invoice_id = $invoice['InvoiceNumber'];

                        if (empty($order['ec_customer_id'])) {
                            $syncInvoiceResult->ec_customer_id = $invoice['CustomerId'];
                        }

                        $this->updateWPTable($this->posts_table, $syncInvoiceResult, $whereCondition);

                        $this->log('Invoice info saved. Invoice ID: ' . $syncInvoiceResult->ec_invoice_id);


                    } catch (Exception $e) {
                        $this->log($e->getMessage(), 'critical');

                        if ($_errorCount == 0)
                            $_messages[] = array("type" => 'error', "message" => "Errors have occurred during the Invoice sync process, please review the logs.
                        First error occurred is: " . $e->getMessage());

                        $_errorCount++;
                    } finally {
                        $_processedCount++;
                    }
                }
            }

            array_unshift($_messages, array("type" => 'notice', "message" => sprintf("Sync Invoices process has been completed. %s record(s) processed.", $_processedCount)));

            if ($_errorCount == 0)
                $_messages[] = array("type" => 'success', "message" => "Completed the Invoice sync process with no errors.");

        } catch (Exception $e) {
            $this->log($e->getMessage(), 'critical');
            $_messages[] = array("type" => 'error', "message" => $e->getMessage());
        }

        return $this->_prepareMessages($_messages);
    }

    public function syncInvoiceWc($id = null)
    {
        global $wpdb, $WC_Gateway_EBizCharge;
        $_messages = array();
        $_errorCount = 0;
        $_processedCount = 0;
        $this->log(__METHOD__);
        $securityToken = $WC_Gateway_EBizCharge->_getUeSecurityToken();

        try {
            $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $this->SoapParamsEconnect());

            $query = "SELECT p.* FROM " . $this->wc_orders_table . " p WHERE p.type = 'shop_order' AND p.status IN ('wc-processing', 'wc-completed')";

            //filter if update is for a single record
            if ($id != null) {
                $query .= " AND p.id = {$id}";
            } else {
                $query .= " AND ((p.ec_order_last_modified_date is null)
					OR TIMESTAMPDIFF(minute, p.ec_order_last_modified_date, IFNULL( p.date_updated_gmt, p.date_created_gmt )) > 0)";
            }

            $wpOrders = $wpdb->get_results($query);

            if (!empty($wpOrders)) {

                $wpProducts = $this->getWpProductsById();

                foreach ($wpOrders as $order) {

                    if (empty($order)) {
                        $this->log('Order not found', 'notice');
                        continue;
                    }

                    $wcOrder = wc_get_order($order->id);
                    $order_data = $wcOrder->get_data();
                    $order_id = $wcOrder->get_id();

                    if (empty($wcOrder) || empty($order_data)) {
                        $this->log('WC Order or data not found', 'notice');
                        continue;
                    }

                    $paymentStatus = $wcOrder->get_meta('_payment_status');
                    if (!empty($paymentStatus) && $paymentStatus != 'Captured') {
                        $this->log('Order payment status is not captured. skipping this invoice Order#' .
                            $order_id . ' payment status: ' . $paymentStatus, 'notice');
                        continue;
                    }

                    $wcUser = get_user_by('id', $wcOrder->get_customer_id());
                    if ($wcUser) {
                        $wcUserData = $wcUser->data;
                        $customerId = !empty($wcUserData->ec_customer_id) ? $wcUserData->ec_customer_id : $wcUserData->ID;
                        $customerInternalId = !empty($wcUserData->ec_internal_id) ? $wcUserData->ec_internal_id : null;
                        $ebizCustomerToken = !empty($wcUserData->ec_customer_token)
                            ? $wcUserData->ec_customer_token
                            : get_user_meta($wcUserData->ID, 'CustNum');

                    } else {
                        $this->log('Invoice cannot be created for Guest customer order. WC Order#' . $order_id, 'notice');
                        continue;
                    }

                    try {
                        $invoice = $this->getInvoiceObject($wcOrder, $customerId);
                        $lineItems = $this->getOrderItems($wcOrder->get_id(), $wpProducts);

                        if (count($lineItems) > 0) {
                            $invoice['Items'] = $lineItems;
                        }
                        // add invoice
                        if (empty($order->ec_internal_id)) {

                            $parameters = array(
                                'securityToken' => $securityToken,
                                'invoice' => $invoice
                            );

                            $addInvoiceResponse = $client->AddInvoice($parameters);
                            $syncInvoiceResult = $addInvoiceResponse->AddInvoiceResult;
                            if ($syncInvoiceResult->Status == "Success") {
                                $this->log('Invoice added successfully for Order Id: ' . $order_id .
                                    ' Invoice ID: ' . $syncInvoiceResult->InvoiceInternalId);
                            } else {
                                $this->log('Failed: Invoice not added for order Id: '. $order_id, 'warning');
                                $this->log($syncInvoiceResult);
                            }

                            // add invoice payment
                            $tranNo = $order->transaction_id ?? false;

                            if ($syncInvoiceResult->Status == "Success" && !empty($tranNo) && !empty($ebizCustomerToken)) {

                                $paymentDetails[0] = [
                                    'InvoiceInternalId' => $syncInvoiceResult->InvoiceInternalId,
                                    'PaidAmount' => $invoice['InvoiceAmount'],
                                    'Currency' => $invoice['Currency'],
                                ];
                                $paymentParms = [
                                    'securityToken' => $securityToken,
                                    'payment' => [
                                        'InvoicePaymentDetails' => $paymentDetails,
                                        'CustomerId' => $customerId,
                                        'RefNum' => $tranNo,
                                        'Currency' => $invoice['Currency'],
                                        'TotalPaidAmount' => $invoice['InvoiceAmount'],
                                        'CustNum' => $ebizCustomerToken,
                                        'PaymentMethodId' => '?',
                                        'PaymentMethodType' => '?',
                                        'Software' => $this->software,
                                    ],
                                ];

                                $invoicePayment = $client->AddInvoicePayment($paymentParms);
                                $invoicePaymentResult = $invoicePayment->AddInvoicePaymentResult;
                                if ($invoicePaymentResult->Status == "Success") {
                                    $this->log('Invoice payment added successfully for invoice Id: ' . $syncInvoiceResult->InvoiceInternalId);
                                } else {
                                    $this->log('Invoice payment not added for order# ' . $order_id);
                                    $this->log($invoicePayment);
                                }
                            } else {
                                $this->log('Invoice payment not added for order# ' . $order_id . ' ');
                            }

                        } else { // update invoice
                            // use econnect customerId in update
                            if (!empty($order->ec_customer_id)) {
                                $invoice['CustomerId'] = $order->ec_customer_id;
                            }
                            // use econnect invoice id in update
                            if (!empty($order->ec_invoice_id)) {
                                $invoice['InvoiceNumber'] = $order->ec_invoice_id;
                                $invoice['SoNum'] = $order->ec_invoice_id;
                                $invoice['InvoiceUniqueId'] = $order->ec_invoice_id;
                            }

                            $parameters = array(
                                'securityToken' => $securityToken,
                                'invoice' => $invoice,
                                'invoiceNumber' => $invoice['InvoiceNumber'],
                                'invoiceInternalId' => $order->ec_internal_id,
                                'customerId' => $customerId // WOO-712: Fixed: expects the parameter '@prmPayerId', which was not supplied
                            );

                            $updateInvoiceResponse = $client->UpdateInvoice($parameters);
                            $syncInvoiceResult = $updateInvoiceResponse->UpdateInvoiceResult;
                        }

                        if ($syncInvoiceResult->Status == "Error") {
                            $this->log('Invoice not synced to Econnect.');
                            $this->log($syncInvoiceResult);

                            if ($_errorCount == 0)
                                $_messages[] = array("type" => 'error', "message" => "Errors have occurred during the Invoice sync process, please review the logs. First error occurred is: " . $syncInvoiceResult->Error);

                            $_errorCount++;
                        }

                        $whereCondition = array('ID' => $order_id);
                        $syncInvoiceResult->ec_invoice_id = $invoice['InvoiceNumber'];

                        if (empty($order->ec_customer_id)) {
                            $syncInvoiceResult->ec_customer_id = $invoice['CustomerId'];
                        }

                        $this->updateWPTable($this->wc_orders_table, $syncInvoiceResult, $whereCondition);
                        $this->log('Invoice info saved. Invoice ID: ' . $syncInvoiceResult->ec_invoice_id);

                    } catch (Exception $e) {
                        $this->log($e->getMessage(), 'critical');

                        if ($_errorCount == 0)
                            $_messages[] = array("type" => 'error', "message" => "Errors have occurred during the Invoice sync process, please review the logs.
                        First error occurred is: " . $e->getMessage());

                        $_errorCount++;
                    } finally {
                        $_processedCount = $_processedCount + 1;
                    }
                }
            }

            array_unshift($_messages, array("type" => 'notice', "message" => sprintf("Sync Invoices process has been completed. %d record(s) processed.", $_processedCount)));

            if ($_errorCount == 0)
                $_messages[] = array("type" => 'success', "message" => "Completed the Invoice sync process with no errors.");

        } catch (Exception $e) {
            $this->log($e->getMessage(), 'critical');
            $_messages[] = array("type" => 'error', "message" => $e->getMessage());
        }

        return $this->_prepareMessages($_messages);
    }

}