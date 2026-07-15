<?php
/**
 * EbizCharge Transaction Class
 *
 */

use Automattic\WooCommerce\Utilities\OrderUtil;

ini_set('default_socket_timeout', 1000);

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WC_Gateway_EBizCharge
{
    public $enableEconnect = false;
    public $showSavedMethods = false;
    // Required for all transactions
    public $key;   // Source key
    public $userid;   // User Id
    public $pin;   // Source pin (optional)
    public $amount;  // the entire amount that will be charged to the customers card
    public $order_partial_amount;  // the partial amount that will be charged to the customers card
    public $order_captured_amount;  // the partial amount charged to the customers card
    public $order_remaining_amount;  // the amount remaining to be charged to the customers card
    public $payment_status;  // saved order status
    // (including tax, shipping, etc)
    public $invoice;  // invoice number.  must be unique.  limited to 10 digits.  use orderid if you need longer.
    // Required for Commercial Card support
    public $ponum;   // Purchase Order Number
    public $tax;   // Tax
    public $nontaxable; // Order is non taxable
    // Amount details (optional)
    public $tip;    // Tip
    public $shipping = 0;  // Shipping charge
    public $discount = 0;  // Discount amount (ie gift certificate or coupon code)
    public $subtotal = 0;  // if subtotal is set, then
    // subtotal + tip + shipping - discount + tax must equal amount
    // or the transaction will be declined.  If subtotal is left blank
    // then it will be ignored
    public $currency;  // Currency of $amount
    // Required Fields for Card Not Present transacitons (Ecommerce)
    public $card;   // card number, no dashes, no spaces
    public $cardtype;  //type of the card
    public $exp;   // expiration date 4 digits no /
    public $cardholder;  // name of card holder
    public $street;  // street address
    public $zip;   // zip code
    // Fields for Card Present (POS)
    public $magstripe;   // mag stripe data.  can be either Track 1, Track2  or  Both  (Required if card,exp,cardholder,street and zip aren't filled in)
    public $cardpresent;   // Must be set to true if processing a card present transaction  (Default is false)
    public $termtype;   // The type of terminal being used:  Optons are  POS - cash register, StandAlone - self service terminal,  Unattended - ie gas pump, Unkown  (Default:  Unknown)
    public $magsupport;   // Support for mag stripe reader:   yes, no, contactless, unknown  (default is unknown unless magstripe has been sent)
    public $contactless;   // Magstripe was read with contactless reader:  yes, no  (default is no)
    public $dukpt;   // DUK/PT for PIN Debit
    public $signature;     // Signature Capture data
    // fields required for check transactions
    public $account;  // bank account number
    public $accountholder;  // bank routing number
    public $routingnumber;  // bank routing number
    public $ssn;   // social security number
    public $dlnum;   // drivers license number (required if not using ssn)
    public $dlstate;  // drivers license issuing state
    public $checknum;  // Check Number
    public $accounttype;       // Checking or Savings
    public $checkformat; // Override default check record format
    public $checkimage_front;    // Check front
    public $checkimage_back;  // Check back
    // Fields required for Secure Vault Payments (Direct Pay)
    public $svpbank;  // ID of cardholders bank
    public $svpreturnurl; // URL that the bank should return the user to when tran is completed
    public $svpcancelurl;  // URL that the bank should return the user if they cancel
    // Option parameters
    public $origauthcode; // required if running postauth transaction.
    public $command;  // type of command to run; Possible values are:
    // sale, credit, void, preauth, postauth, check and checkcredit.
    // Default is sale.
    public $orderid;  // Unique order identifier.  This field can be used to reference
    // the order for which this transaction corresponds to. This field
    // can contain up to 64 characters and should be used instead of
    // UMinvoice when orderids longer that 10 digits are needed.
    public $custid;   // Alpha-numeric id that uniquely identifies the customer.
    public $description; // description of charge
    public $cvv;   // cvv code
    public $cvv2;   // cvv2 code
    public $custemail;  // customers email address
    public $custreceipt = false; // send customer a receipt
    public $custreceipt_template; // select receipt template
    public $ignoreduplicate; // prevent the system from detecting and folding duplicates
    public $ip;   // ip address of remote host
    public $testmode;  // test transaction but don't process it
    public $timeout;       // transaction timeout.  defaults to 90 seconds
    public $gatewayurl;    // url for the gateway
    public $proxyurl;  // proxy server to use (if required by network)
    public $ignoresslcerterrors;  // Bypasses ssl certificate errors.  It is highly recommended that you do not use this option.  Fix your openssl installation instead!
    public $cabundle;      // manually specify location of root ca bundle (useful of root ca is not in default location)
    public $transport;     // manually select transport to use (curl or stream), by default the library will auto select based on what is available
    // Card Authorization - Verified By Visa and Mastercard SecureCode
    public $cardauth;     // enable card authentication
    // Recurring Billing
    public $recurring;  //  Save transaction as a recurring transaction:  yes/no
    public $schedule;  //  How often to run transaction: daily, weekly, biweekly, monthly, bimonthly, quarterly, annually.  Default is monthly.
    public $numleft;   //  The number of times to run. Either a number or * for unlimited.  Default is unlimited.
    public $start;   //  When to start the schedule.  Default is tomorrow.  Must be in YYYYMMDD  format.
    public $end;   //  When to stop running transactions. Default is to run forever.  If both end and numleft are specified, transaction will stop when the ealiest condition is met.
    public $billamount; //  Optional recurring billing amount.  If not specified, the amount field will be used for future recurring billing payments
    public $billtax;
    public $billsourcekey;
    // Billing Fields
    public $billfname;
    public $billlname;
    public $billcompany;
    public $billstreet;
    public $billstreet2;
    public $billcity;
    public $billstate;
    public $billzip;
    public $billcountry;
    public $billphone;
    public $email;
    public $fax;
    public $website;
    // Shipping Fields
    public $delivery;  // type of delivery method ('ship','pickup','download')
    public $shipfname;
    public $shiplname;
    public $shipcompany;
    public $shipstreet;
    public $shipstreet2;
    public $shipcity;
    public $shipstate;
    public $shipzip;
    public $shipcountry;
    public $shipphone;
    // Custom Fields
    public $custom1;
    public $custom2;
    public $custom3;
    public $custom4;
    public $custom5;
    public $custom6;
    public $custom7;
    public $custom8;
    public $custom9;
    public $custom10;
    // Line items  (see addLine)
    public $lineitems;
    // Line items for tokenization (see addLineItem())
    public $lineItems;
    public $comments; // Additional transaction details or comments (free form text field supports up to 65,000 chars)
    public $software; // Allows developers to identify their application to the gateway (for troubleshooting purposes)
    // response fields
    public $rawresult;  // raw result from gateway
    public $result;  // full result:  Approved, Declined, Error
    public $resultcode;  // abreviated result code: A D E
    public $authcode;  // authorization code
    public $refnum;  // reference number
    public $batch;  // batch number
    public $avs_result;  // avs result
    public $avs_result_code;  // avs result
    public $avs;       // obsolete avs result
    public $cvv2_result;  // cvv2 result
    public $cvv2_result_code;  // cvv2 result
    public $vpas_result_code;      // vpas result
    public $isduplicate;      // system identified transaction as a duplicate
    public $convertedamount;  // transaction amount after server has converted it to merchants currency
    public $convertedamountcurrency;  // merchants currency
    public $conversionrate;  // the conversion rate that was used
    public $custnum;  //  gateway assigned customer ref number for recurring billing
    // Cardinal Response Fields
    public $acsurl; // card auth url
    public $pareq;  // card auth request
    public $cctransid; // cardinal transid
    // Errors Response Feilds
    public $error;   // error message if result is an error
    public $errorcode;  // numerical error code
    public $blank;   // blank response
    public $transporterror;  // transport error
    public $methodID;  // transport error
    public $enableACH;
    public $payMethod;
    // 3DS verification fields added
    public $CAVV;   // CAVV value of 3DS validated response
    public $XID;  // XID value of 3DS validated response
    public $ECI;   // ECI value of 3DS validated response
    public $Pares;  // Pares value of 3DS validated response
    public $DSTransactionId;  // DSTransactionId of 3DS validated response
    public $WCPlaceOrder;  // WC Place Order validated bit

    public function __construct()
    {
        // Set default values.
        $this->command = "sale";
        $this->result = "Error";
        $this->resultcode = "E";
        $this->error = "Transaction not processed yet.";
        $this->timeout = 90;
        $this->cardpresent = false;
        $this->lineitems = array();
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $this->ip = $_SERVER['REMOTE_ADDR'];
        }
        $this->software = "Woocommerce";
        $this->enableACH = $this->is_ach_enabled();
    }

    public function log_write($level, $message, $logFileName)
    {
        $level = (!empty($level) ? $level : 'info');
        $logFileName = (!empty($logFileName) ? $logFileName : 'EBizCharge');
        wc_get_logger()->$level($message, array('source' => $logFileName));
    }

    // Add EBizCharge logs to Woocommerce logs
    public function ebiz_log($message, $level = 'info')
    {
        $logFileName = 'EBizCharge';
        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }
        $this->log_write($level, $message, $logFileName);
    }

    public function _getGatewayBaseUrl()
    {
        return 'https://soap.ebizcharge.net';
    }

    public function _getWsdlUrl()
    {
        //Old WSDL
        //return 'https://soap.ebizcharge.net/eBizService.svc?singleWsdl';
        //Updated WSDL
        return 'https://soapapi1.ebizcharge.net/v2/wsdl/ebizsoap1.Wsdl';
    }

    function _getWsdlUrl3dSecure()
    {
        //WSDL for 3dSecure Validation
        return 'https://privatesoapapi1.ebizcharge.net/v2/wsdl/privatesoapapi1.wsdl';
    }

    public function _getUeSecurityToken()
    {
        $ebizchargeSettings = $this->ebiz_admin_settings();

        if (!empty($ebizchargeSettings)) {
            return array(
                'SecurityId' => $ebizchargeSettings['securityid'] ?? '',
                'UserId' => $ebizchargeSettings['username'] ?? '',
                'Password' => $ebizchargeSettings['password'] ?? ''
            );
        }

        return [];
    }

    // Set Soap Client Parameters
    public function SoapParams()
    {
        return array(
            'cache_wsdl' => false
            //'trace' => true,
            //'exceptions' => true
        );
    }

    public function ebiz_admin_settings()
    {
        return get_option('woocommerce_ebizcharge_settings');
    }

    public function getMerchantTransactionData()
    {
        try {
            $client = new SoapClient($this->_getWsdlUrl(), $this->SoapParams());
            $response = $client->GetMerchantTransactionData(
                array(
                    'securityToken' => $this->_getUeSecurityToken(),
                ));
            return $response->GetMerchantTransactionDataResult;

        } catch (\Exception $ex) {
            $this->ebiz_log(__METHOD__ . ' Exception: ' . $ex->getMessage(), 'critical');
            return false;
        }
    }

    public function calculateSurchargeAmount($amount, $zip, $user = null, $methodId = null, $cc = null)
    {
        try {
            $customerInternalId = '';
            if (!empty($methodId) && !empty($user)) {

                if (empty($customerInternalId = $user->ec_internal_id)
                    && !empty($ebizCustomer = $this->getEbizCustomer($user))) {

                    $customerInternalId = $ebizCustomer->CustomerInternalId;
                }
            }

            $client = new SoapClient($this->_getWsdlUrl(), $this->SoapParams());
            $response = $client->CalculateSurchargeAmount(
                array(
                    'securityToken' => $this->_getUeSecurityToken(),
                    'amount' => $amount,
                    'cardNumber' => $cc,
                    'cardZipCode' => $zip,
                    'customerInternalId' => $customerInternalId,
                    'paymentMethodId' => $methodId,
                ));

            return $response->CalculateSurchargeAmountResult;

        } catch (\Exception $ex) {
            $this->ebiz_log(__METHOD__ . ' Exception: ' . $ex->getMessage(), 'critical');
            return null;
        }
    }

    /* Fetch frequency Array from Gateway */
    public function getFrequencies()
    {
        try {
            global $WC_Gateway_EBizCharge;
            $client = new SoapClient($WC_Gateway_EBizCharge->_getWsdlUrl(), $WC_Gateway_EBizCharge->SoapParams());
            $transaction = $client->GetRecurringFrequencyList([
                'securityToken' => $WC_Gateway_EBizCharge->_getUeSecurityToken()
            ]);

            $recurringFrequencyList = $transaction->GetRecurringFrequencyListResult->RecurringFrequency;

            if (!empty($recurringFrequencyList)) {
                $recurringFrequencies = array();
                foreach ($recurringFrequencyList as $recurringFrequency) {
                    foreach ($recurringFrequency as $key => $value) {
                        if ($recurringFrequency->FrequencyDescription != 'Once') {
                            $recurringFrequencies[$recurringFrequency->FrequencyId] = $recurringFrequency->FrequencyDescription;
                        }
                    }
                }

                return $recurringFrequencies;
            } else {
                $this->ebiz_log('No frequency list found on gateway.');
                return null;
            }

        } catch (Exception $ex) {
            $this->ebiz_log(__METHOD__ . ' Exception: ' . $ex->getMessage());
            return null;
        }
    }

    public function is_ebizcharge_enabled(): bool
    {
        $settings = $this->ebiz_admin_settings();
        return !empty($settings) && $settings['enabled'] == 'yes';
    }

    public function saveCardBinEnabled(): bool
    {
        $settings = $this->ebiz_admin_settings();
        return !empty($settings) && $settings['saveCardBin'] == 'yes';
    }

    public function isPaymentSettingEmpty(): bool
    {
        $settings = $this->ebiz_admin_settings();
        return empty($settings['allowedPayments']);
    }

    /** Task :WOO-506. Fix is added because this issue comes when plugin updated from simple to recurring build. */
    public function is_cc_enabled(): bool
    {
        $ebizchargeSettings = $this->ebiz_admin_settings();
        if (!empty($ebizchargeSettings['allowedPayments']) &&
            ($ebizchargeSettings['allowedPayments'] == 'enableCC' ||
                $ebizchargeSettings['allowedPayments'] == 'enableCCACHBoth')) {
            return true;
        } elseif (empty($ebizchargeSettings['allowedPayments'])) {
            return true;
        }
        return false;
    }

    public function is_ach_enabled(): bool
    {
        $ebizchargeSettings = $this->ebiz_admin_settings();
        if (isset($ebizchargeSettings['allowedPayments']) &&
            ($ebizchargeSettings['allowedPayments'] == 'enableACH' ||
                $ebizchargeSettings['allowedPayments'] == 'enableCCACHBoth')) {
            return true;
        }
        return false;
    }

    public function enabled_cc_ach_both(): bool
    {
        $ebizchargeSettings = $this->ebiz_admin_settings();
        return isset($ebizchargeSettings['allowedPayments']) && $ebizchargeSettings['allowedPayments'] == 'enableCCACHBoth';
    }

    public function is_recurring_enabled(): bool
    {
        $ebizchargeSettings = $this->ebiz_admin_settings();
        return isset($ebizchargeSettings['enableRecurring']) && $ebizchargeSettings['enableRecurring'] == 'yes';
    }

    public function get_active_frequencies()
    {
        $ebizchargeSettings = $this->ebiz_admin_settings();
        return $ebizchargeSettings['recurringFrequencies'];
    }

    /**
     * Add a line item to the transaction
     *
     * @param string $sku
     * @param string $name
     * @param string $description
     * @param double $cost
     * @param string $taxable
     * @param int $qty
     */
    public function addLine($sku, $name, $description, $cost, $qty, $taxAmount)
    {
        $this->lineitems[] = array(
            'sku' => $sku,
            'name' => $name,
            'description' => $description,
            'cost' => $cost,
            'taxable' => ($taxAmount > 0) ? 'Y' : 'N',
            'qty' => $qty
        );
    }

    /**
     * Add line items to the transaction used in tokenization
     *
     * @param string $sku
     * @param string $name
     * @param string $description
     * @param double $cost
     * @param int $qty
     * @param double $taxAmount
     */
    public function addLineItem($sku, $name, $description, $cost, $qty, $taxAmount): void
    {
        $this->lineItems[] = array(
            'SKU' => $sku,
            'ProductName' => $name,
            'Description' => $description,
            'UnitPrice' => $cost,
            'Taxable' => ($taxAmount > 0) ? 'Y' : 'N',
            'TaxAmount' => $taxAmount,
            'Qty' => $qty
        );
    }

    public function clearLines(): void
    {
        $this->lineitems = array();
    }

    public function clearLineItems(): void
    {
        $this->lineItems = array();
    }

    //------------------- Developer New Functions Added Start -------------------
	/**
     * This function set gateway transaction result
     */
    public function setTransactionResult($transaction): bool
    {
        $this->result = $transaction->Result ?? '';
        $this->resultcode = $transaction->ResultCode ?? '';
        $this->authcode = $transaction->AuthCode ?? '';
        $this->refnum = $transaction->RefNum ?? '';
        $this->batch = $transaction->BatchNum ?? '';
        $this->avs_result = $transaction->AvsResult ?? '';
        $this->avs_result_code = $transaction->AvsResultCode ?? '';
        $this->cvv2_result = $transaction->CardCodeResult ?? '';
        $this->cvv2_result_code = $transaction->CardCodeResultCode ?? '';
        $this->vpas_result_code = $transaction->VpasResultCode ?? '';
        $this->convertedamount = $transaction->ConvertedAmount ?? '';
        $this->convertedamountcurrency = $transaction->ConvertedAmountCurrency ?? '';
        $this->conversionrate = $transaction->ConversionRate ?? '';
        $this->error = $transaction->Error ?? '';
        $this->errorcode = $transaction->ErrorCode ?? '';
        $this->custnum = $transaction->CustNum ?? '';
        // Obsolete variable (for backward compatibility) At some point they will no longer be set.
        $this->avs = $transaction->AvsResult ?? '';
        $this->cvv2 = $transaction->CardCodeResult ?? '';

        $this->cctransid = $transaction->RefNum ?? '';
        $this->acsurl = $transaction->AcsUrl ?? '';
        $this->pareq = $transaction->Payload ?? '';

        if ($this->resultcode == 'A') {
            $this->ebiz_log('RefNum: ' . $this->refnum . ' Result: ' . $this->result . ' avs_result: ' . $this->avs_result);
            return true;
        } else {
            $message = $this->error . '(' . $this->errorcode . '). RefNum (' . $this->refnum . ') is "' . $this->result . '".';
            $this->ebiz_log($message, 'error');
            return false;
        }
    }

    /**
     * Return customer billing address
     */
    private function getBillingAddress(): array
    {
        return array(
            'FirstName' => $this->billfname,
            'LastName' => $this->billlname,
            'Company' => $this->billcompany,
            'Street' => $this->billstreet,
            'Street2' => $this->billstreet2,
            'City' => $this->billcity,
            'State' => $this->billstate,
            'Zip' => $this->billzip,
            'Country' => $this->billcountry,
            'Phone' => $this->billphone,
            'Fax' => $this->fax,
            'Email' => $this->email
        );
    }

    /**
     * Return customer shipping address
     */
    private function getShippingAddress(): array
    {
        return array(
            'FirstName' => $this->shipfname,
            'LastName' => $this->shiplname,
            'Company' => $this->shipcompany,
            'Street' => $this->shipstreet,
            'Street2' => $this->shipstreet2,
            'City' => $this->shipcity,
            'State' => $this->shipstate,
            'Zip' => $this->shipzip,
            'Country' => $this->shipcountry,
            'Phone' => $this->shipphone,
            'Fax' => $this->fax,
            'Email' => $this->email
        );
    }

    /**
     * Customer billing address
     * @return array
     */
    private function getBillingAddressAddCust(): array
    {
        return array(
            'FirstName' => $this->billfname,
            'LastName' => $this->billlname,
            'Company' => $this->billcompany,
            'Address1' => $this->billstreet,
            'Address2' => $this->billstreet2,
            'City' => $this->billcity,
            'State' => $this->billstate,
            'ZipCode' => $this->billzip,
            'Country' => $this->billcountry,
            'Phone' => $this->billphone,
            'Fax' => $this->fax,
            'Email' => $this->email
        );
    }

    /**
     * New shipping for add customer
     */
    private function getShippingAddressAddCust(): array
    {
        return array(
            'FirstName' => $this->shipfname,
            'LastName' => $this->shiplname,
            'Company' => $this->shipcompany,
            'Address1' => $this->shipstreet,
            'Address2' => $this->shipstreet2,
            'City' => $this->shipcity,
            'State' => $this->shipstate,
            'ZipCode' => $this->shipzip,
            'Country' => $this->shipcountry,
            'Phone' => $this->shipphone,
            'Fax' => $this->fax,
            'Email' => $this->email
        );
    }

    /**
     * Return payment details
     */
    private function getTransactionDetails(): array
    {
        return array(
            'OrderID' => $this->orderid,
            'Invoice' => $this->invoice,
            'PONum' => $this->ponum,
            'Description' => $this->description,
            'Amount' => $this->amount,
            'Tax' => $this->tax,
            'Currency' => $this->currency,
            'Shipping' => $this->shipping,
            'ShipFromZip' => $this->shipzip,
            'Discount' => $this->discount,
            'Subtotal' => $this->subtotal,
            'AllowPartialAuth' => false,
            'Tip' => 0,
            'NonTax' => false,
            'Duty' => 0,
        );
    }

    /**
     * Return transaction object for API
     */
    private function getTransactionRequest(): array
    {
        $customerId = empty($this->custid) ? 'Guest' : $this->custid;

        $transactionRequest = array(
            'CustReceipt' => $this->custreceipt,
            'CustReceiptName' => $this->custreceipt_template,
            'Software' => $this->software,
            'LineItems' => $this->lineItems,
            'IsRecurring' => false,
            'IgnoreDuplicate' => false,
            'Details' => $this->getTransactionDetails(),
            'CustomerID' => $customerId,
            'CreditCardData' => '',
            'CheckData' => '',
            'Command' => '',
            'ClientIP' => $this->ip,
            'AccountHolder' => $this->cardholder,
            'RefNum' => $this->refnum,
            'BillingAddress' => $this->getBillingAddress(),
            'ShippingAddress' => $this->getShippingAddress(),
        );

        if (!empty($this->card)) {

            $creditCardData = array(
                'InternalCardAuth' => false,
                'CardPresent' => false,
                'CardNumber' => $this->card,
                'CardExpiration' => $this->exp,
                'CardCode' => $this->cvv2,
                'AvsStreet' => $this->billstreet,
                'AvsZip' => $this->billzip,
            );

            $transactionRequest['CreditCardData'] = $creditCardData;
            $transactionRequest['Command'] = $this->command;

            if ($this->Is3DS2Enabled()) {

                $transactionRequest['CreditCardData']['CAVV'] = $this->CAVV ?? '';
                $transactionRequest['CreditCardData']['XID'] = $this->XID ?? '';
                $transactionRequest['CreditCardData']['ECI'] = $this->ECI ?? '';
                $transactionRequest['CreditCardData']['Pares'] = $this->Pares ?? '';
                $transactionRequest['CustomFields'][] = [
                    'Field' => 'DSTransactionId',
                    'Value' => $this->DSTransactionId ?? ''
                ];
            }
        }

        if (!empty($this->account)) {

            $checkData = array(
                'Account' => $this->account,
                'AccountType' => $this->accounttype,
                'Routing' => $this->routingnumber
            );

            $transactionRequest['CheckData'] = $checkData;
            $transactionRequest['Command'] = 'check';
            $transactionRequest['AccountHolder'] = $this->accountholder;
        }

        return $transactionRequest;
    }

    /**
     * Get Customer Data for Add New Customer
     * @param $customerId
     * @return array
     */
    public function getCustomerData($customerId): array
    {
        return array(
            'CustomerId' => $customerId,
            'FirstName' => $this->billfname,
            'LastName' => $this->billlname,
            'CompanyName' => $this->billcompany,
            'Phone' => $this->billphone,
            'CellPhone' => $this->billphone,
            'Fax' => $this->fax,
            'Email' => $this->email,
            'WebSite' => $this->website,
            'ShippingAddress' => $this->getShippingAddressAddCust(),
            'BillingAddress' => $this->getBillingAddressAddCust(),
            'SoftwareId' => $this->software
        );
    }

    /**
     * Get Customer Payment data
     */
    private function getCustomerPayment(): array
    {
		$customerPayment = [];

		if (!empty($this->card)) {
            $customerPayment = array(
                'MethodName' => $this->cardtype . ' ' . substr($this->card, -4) . ' - ' . $this->cardholder, # . ' - Expires on: ' . $this->exp,
                'AccountHolderName' => $this->cardholder,
                'SecondarySort' => 1,
                'Created' => date('Y-m-d\TH:i:s'),
                'Modified' => date('Y-m-d\TH:i:s'),
                'AvsStreet' => $this->billstreet,
                'AvsZip' => $this->billzip,
                'CardCode' => $this->cvv2,
                'CardExpiration' => $this->exp,
                'CardNumber' => $this->card,
                'CardType' => $this->cardtype,
                'Balance' => $this->amount,
                'MaxBalance' => $this->amount,
            );
        }

        if (!empty($this->account)) {
	        $customerPayment = array(
                'MethodName' => $this->accounttype . ' ' . substr($this->account, -4) . ' - ' . $this->accountholder,
                'MethodType' => 'check',
                'SecondarySort' => 1,
                'Created' => date('Y-m-d\TH:i:s'),
                'Modified' => date('Y-m-d\TH:i:s'),
                'Account' => $this->account,
                'AccountType' => $this->accounttype,
                'AccountHolderName' => $this->accountholder,
                'Routing' => $this->routingnumber,
            );
        }

        return $customerPayment;
    }

    /**
     * Save getCustomerTransactionRequest data
     */
    private function getCustomerTransactionRequest(): array
    {
        return [
            'isRecurring' => false,
            'IgnoreDuplicate' => true,
            'Details' => $this->getTransactionDetails(),
            'Software' => $this->software,
            'MerchReceipt' => true,
            'CustReceiptName' => $this->custreceipt_template,
            'CustReceiptEmail' => '',
            'CustReceipt' => $this->custreceipt,
            'ClientIP' => $this->ip,
            'CardCode' => $this->cvv2,
            'Command' => $this->command,
            'LineItems' => $this->lineItems
        ];
    }

    public function runTransactionOnGateway($tranParams)
    {
        $this->ebiz_log(__METHOD__);
        $client = new SoapClient($this->_getWsdlUrl(), $this->SoapParams());
        $transaction = $client->runTransaction(
            array(
                'securityToken' => $this->_getUeSecurityToken(),
                'tran' => $tranParams
            )
        );
        // Return TransactionResult
        return $transaction->runTransactionResult;
    }

    // This function is used for Void, Cancel and Refund
    public function executeTransaction(): bool
    {
        $this->ebiz_log(__METHOD__);
        try {
            $tranParams = array(
                'Command' => $this->command,
                'RefNum' => $this->refnum,
                'IsRecurring' => false,
                'IgnoreDuplicate' => false,
                'CustReceipt' => $this->custreceipt
            );
            // setTransactionResult called for success
            $transactionResult = $this->runTransactionOnGateway($tranParams);
            if (!empty($transactionResult)) {
                return $this->setTransactionResult($transactionResult);
            }
        } catch (SoapFault $ex) {
            $this->error = $ex->getMessage();
            $this->ebiz_log('Error: ' . $this->error, 'critical');
            return false;
        }
        return false;
    }

    // This function is used for partial capture order amount
    public function partialCaptureTransaction($amount): bool
    {
        try {
            $client = new SoapClient($this->_getWsdlUrl(), $this->SoapParams());
            $transaction = $client->runTransaction(
                array(
                    'securityToken' => $this->_getUeSecurityToken(),
                    'tran' => array(
                        'Command' => $this->command,
                        'RefNum' => $this->refnum,
                        'IsRecurring' => false,
                        'IgnoreDuplicate' => false,
                        'CustReceipt' => $this->custreceipt,
                        'LineItems' => $this->lineItems,
                        'Details' => array(
                            'Amount' => $amount,
                            'Subtotal' => $amount,
                            'NonTax' => 0,
                            'Tax' => 0,
                            'Shipping' => 0,
                            'Duty' => 0,
                            'Discount' => 0,
                            'AllowPartialAuth' => 1,
                            'Tip' => 0,
                        )
                    )
                )
            );
            // setTransactionResult called for success
            if (!empty($transactionResult = $transaction->runTransactionResult)) {
                return $this->setTransactionResult($transactionResult);
            }
        } catch (SoapFault $ex) {
            $this->error = $ex->getMessage();
            $this->ebiz_log('Error: ' . $this->error, 'critical');
            return false;
        }

        return false;
    }

    // This function is used for manual Refund
    public function refundTransaction(): bool
    {
        $this->ebiz_log(__METHOD__);
        try {
            $tranParams = array(
                'Command' => $this->command,
                'RefNum' => $this->refnum,
                'IsRecurring' => false,
                'IgnoreDuplicate' => false,
                'CustReceipt' => $this->custreceipt,
                'Details' => $this->getTransactionDetails()
            );
            // setTransactionResult called for success
            $transactionResult = $this->runTransactionOnGateway($tranParams);
            if (!empty($transactionResult)) {
                return $this->setTransactionResult($transactionResult);
            }
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
            $this->ebiz_log('Error: ' . $this->error, 'critical');
            return false;
        }

        return false;
    }

    /**
     * @param $wpCustomer
     * @return bool|string
     * @throws SoapFault
     */
    public function searchCustomer($wpCustomer)
    {
        $this->ebiz_log(__METHOD__);
        $wpMappedCustomerId = !empty($wpCustomer->ec_customer_id) ? $wpCustomer->ec_customer_id : $wpCustomer->ID;

        try {
            // find CustomerInternalId using SearchCustomers ebiz method
            $ebizCustomer = $this->searchCustomerFromGateway($wpMappedCustomerId);

            if (isset($ebizCustomer) && $ebizCustomer !== false) {
                $this->ebiz_log('Customer found. WP customerId: ' . $ebizCustomer->CustomerId . ' Email: ' . $ebizCustomer->Email);
                // matching id and email always create a new customer
                /*if (strtolower(trim($ebizCustomer->Email)) == strtolower(trim($wpCustomer->user_email))) {
                    $ebizCustomer = $searchCustomer;
                }*/
            }

        } catch (SoapFault $ex) {
            $this->ebiz_log('Error: ' . $ex->getMessage(), 'critical');
            return false;
        }

        return $ebizCustomer;
    }

    public function searchCustomerFromGateway($wpMappedCustomerId)
    {
        $this->ebiz_log(__METHOD__);

        $client = new SoapClient($this->_getWsdlUrl(), $this->SoapParams());
        $ebizCustomer = false;

        $searchCustomer = $client->SearchCustomers(array(
                'securityToken' => $this->_getUeSecurityToken(),
                'customerId' => $wpMappedCustomerId,
                'start' => 0,
                'limit' => 1
            )
        );

        if (isset($searchCustomer->SearchCustomersResult->Customer)) {
            $this->ebiz_log('Customer found. WP customerId: ' . $wpMappedCustomerId);
            $ebizCustomer = $searchCustomer->SearchCustomersResult->Customer;
        }

        return $ebizCustomer;
    }

    /**
     * @param $wpCustomer
     * @return array|bool|mixed
     * @throws SoapFault
     */
    public function searchAndSyncCustomer($wpCustomer)
    {
        global $wpdb;
        $this->ebiz_log(__METHOD__);
        $ebizCustomer = false;

        if ($searchCustomers = $this->searchCustomerListByEmail($wpCustomer->user_email)) {

            foreach ($searchCustomers as $searchCustomer) {
                // if email is same, we assume its same customer
                // can't match customer Id because it can't create issue
                if (strtolower(trim($searchCustomer->Email)) == strtolower(trim($wpCustomer->user_email))) {
                    $ebizCustomer = $searchCustomer;
                    break;
                }
            }

            if ($ebizCustomer) {
                $now = new DateTime(null);
                $customerApiInfo = [
                    'ec_internal_id' => $ebizCustomer->CustomerInternalId,
                    'ec_status' => 'Success',
                    'ec_status_code' => 1,
                    'ec_last_modified_date' => $now->format('Y-m-d H:i:s'),
                    'ec_customer_id' => $ebizCustomer->CustomerId,
                    'ec_customer_token' => $ebizCustomer->CustomerToken,
                ];

                $dataFormat = [
                    '%s', '%s', '%d', '%s', '%s', '%s'
                ];

                delete_user_meta($wpCustomer->ID, 'CustNum');

                $wpdb->update(
                    $wpdb->prefix . 'users',
                    $customerApiInfo,
                    ['ID' => $wpCustomer->ID],
                    $dataFormat,
                    ['%s']
                );
                return $ebizCustomer;
            }
        }

        return false;
    }

    /**
     * @param $email
     * @return array
     * @throws SoapFault
     */
    private function searchCustomerListByEmail($email)
    {
        $client = new SoapClient($this->_getWsdlUrl(), $this->SoapParams());
        $ebizCustomers = [];

        try {
            $filters = [
                'FieldName' => 'Email',
                'ComparisonOperator' => 'eq',
                'FieldValue' => $email,
            ];

            $params = [
                'securityToken' => $this->_getUeSecurityToken(),
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

                $this->ebiz_log(__METHOD__ . ', Customers found: ' . $api->SearchCustomerListResult->Count);
                $this->ebiz_log($ebizCustomers);
            }

        } catch (SoapFault $ex) {
            $this->ebiz_log(__METHOD__ . ', Error: ' . $ex->getMessage(), 'critical');
        }

        return $ebizCustomers;
    }

    public function getCustomerPaymentMethods($userId)
    {
        $ebizCustomerNumber = get_user_meta($userId, 'CustNum', true);

        if (!empty($ebizCustomerNumber)) {
            $client = new SoapClient($this->_getWsdlUrl(), $this->SoapParams());

            try {
                $methodProfiles = $client->GetCustomerPaymentMethodProfiles(
                    array(
                        'securityToken' => $this->_getUeSecurityToken(),
                        'customerToken' => $ebizCustomerNumber
                    ));

                if (!isset($methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile)) {
                    $paymentMethods = array();
                } else if (is_array($methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile)) {
                    $paymentMethods = $methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile;
                } else {
                    $paymentMethods[] = $methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile;
                }

                return $paymentMethods;

            } catch (Exception $ex) {
                $this->error = $ex->getMessage();
                $this->ebiz_log('Error: ' . $this->error, 'critical');
                return array();
            }
        }

        return array();
    }

    public function getCustomerFromGateway($wpCustomer)
    {
        $this->ebiz_log(__METHOD__);
        $client = new SoapClient($this->_getWsdlUrl(), $this->SoapParams());
        try {
            if (!empty($wpCustomer->ec_internal_id)) {
                return $this->getCustomerByInternalId($wpCustomer->ec_internal_id);

            } else {
                $wpMappedCustomerId = !empty($wpCustomer->ec_customer_id) ? $wpCustomer->ec_customer_id : $wpCustomer->ID;
                $customer = $client->GetCustomer([
                        'securityToken' => $this->_getUeSecurityToken(),
                        'customerId' => $wpMappedCustomerId,
                        'start' => 0,
                        'limit' => 1
                    ]
                );

                if (isset($customer->GetCustomerResult)) {
                    return $customer->GetCustomerResult;
                }
            }

        } catch (SoapFault $ex) {
            return null;
        }
        return null;
    }

    public function getCustomerByInternalId($customerInternalId)
    {
        $this->ebiz_log(__METHOD__ . ' : ' . $customerInternalId);
        $client = new SoapClient($this->_getWsdlUrl(), $this->SoapParams());
        try {
            $customer = $client->GetCustomer([
                    'securityToken' => $this->_getUeSecurityToken(),
                    'customerInternalId' => $customerInternalId,
                    'start' => 0,
                    'limit' => 1
                ]
            );

            if (isset($customer->GetCustomerResult)) {
                return $customer->GetCustomerResult;
            }

        } catch (SoapFault $ex) {
            return null;
        }
        return null;
    }

    public function getConfirmedEbizCustomer($userId)
    {
        if (!empty($userId)) {

            try {
                $wpCustomer = get_user_by('id', $userId);
                $ebizCustomer = $this->getCustomerFromGateway($wpCustomer);

                if (!empty($ebizCustomer)) {
                    $this->ebiz_log(__METHOD__ . ', customer found. WP customerId: ' . $userId .
                        ' ,MappedId: ' . $wpCustomer->ec_customer_id . ',  Email: ' . $wpCustomer->user_email);

                    $wpCustomerToken = get_user_meta($userId, 'CustNum', true);
                    if ($wpCustomerToken == $ebizCustomer->CustomerToken) {
                        return $ebizCustomer;
                    }
                }

            } catch (SoapFault $ex) {
                $this->ebiz_log(__METHOD__ . ', Error: ' . $ex->getMessage(), 'critical');
                return null;
            }
        }

        return null;
    }

    public function getWpCustomerMapedId($userId)
    {
        $wpCustomer = get_user_by('id', $userId);
        return !empty($wpCustomer->ec_customer_id) ? $wpCustomer->ec_customer_id : $wpCustomer->ID;
    }

    public function getEbizCustomer($wpCustomer)
    {
        try {
            $ebizCustomer = $this->getCustomerFromGateway($wpCustomer);

            if (!empty($ebizCustomer)) {
                $this->ebiz_log(__METHOD__ . ', customer found. 
                    WP customerId: ' . $wpCustomer->ID . ' MappedId: ' . $wpCustomer->ec_customer_id . ' Email: ' . $wpCustomer->user_email);

                return $ebizCustomer;
            }
        } catch (SoapFault $ex) {
            $this->ebiz_log(__METHOD__ . ', Error: ' . $ex->getMessage(), 'critical');
            return null;
        }

        return null;
    }

    public function getCustomerPaymentMethodsByGetCustomer($userId): array
    {
        $ebizCustomer = $this->getConfirmedEbizCustomer($userId);
        // show only credit cards or ACH depending on given parameter $pmType
        $paymentMethods = [];

        if ($ebizCustomer) {

            $allPaymentMethods = $ebizCustomer->PaymentMethodProfiles ?? [];

            if (!empty($allPaymentMethods)) {
                if (!isset($allPaymentMethods->PaymentMethodProfile)) {
                    $paymentMethods = array();
                } else if (is_array($allPaymentMethods->PaymentMethodProfile)) {
                    $paymentMethods = $allPaymentMethods->PaymentMethodProfile;
                } else {
                    $paymentMethods[] = $allPaymentMethods->PaymentMethodProfile;
                }
            }
        }
        return $paymentMethods;
    }

    public function groupCustomerPaymentMethodsByType($paymentMethods, $pmType): array
    {
        $methods = [];
        if (!empty($paymentMethods)) {
            foreach ($paymentMethods as $key => $paymentMethod) {
                if ($paymentMethod->MethodType === $pmType) {
                    $methods[] = $paymentMethod;
                }
            }
        }
        return $methods;
    }

    public function getCustomerPaymentMethodProfile($customerToken, $paymentMethodId)
    {
        $this->ebiz_log(__METHOD__);
        $securityToken = $this->_getUeSecurityToken();
        $client = new SoapClient($this->_getWsdlUrl(), $this->SoapParams());
        try {
            $methodProfile = $client->GetCustomerPaymentMethodProfile(
                array(
                    'securityToken' => $securityToken,
                    'customerToken' => $customerToken,
                    'paymentMethodId' => $paymentMethodId
                ));

            return $methodProfile->GetCustomerPaymentMethodProfileResult;

        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
            $this->ebiz_log('Error: ' . $this->error, 'critical');
            return array();
        }

    }

    private function updateExistingCustomerMap($ebizCustomer, $wpCustomerId)
    {
        global $wpdb;
        $this->ebiz_log(__METHOD__);
        $now = new DateTime(null);
        $now->modify("+10 seconds"); //add 10 seconds to make sure sync last_modified_date is greater than object update date

        $data['ec_internal_id'] = $ebizCustomer->CustomerInternalId;
        $data['ec_customer_id'] = $ebizCustomer->CustomerId;
        $data['ec_customer_token'] = $ebizCustomer->CustomerToken;
        $data['ec_last_modified_date'] = $now->format('Y-m-d H:i:s');
        $dataFormat = ['%s', '%s', '%s', '%s'];

        $wpdb->update($wpdb->prefix . 'users', $data, ['ID' => $wpCustomerId], $dataFormat, ['%s']);
    }

    /**
     * update customer mapping id
     * @param $ebizCustomer
     * @param $wpCustomerId
     */
    public function syncCustomer($ebizCustomer, $wpCustomerId)
    {
        if ($ebizCustomer) {
            $this->ebiz_log(__METHOD__ . ' Econnect enabled. sync customer.');

            $ebiz = new WC_ebizcharge();
            $econnect = $ebiz->_initTransaction(true);

            $ebizCustomer->ec_customer_id = $ebizCustomer->CustomerId;
            $econnect->updateWPTable($econnect->user_table, $ebizCustomer, array('ID' => (int)$wpCustomerId));
        }
    }
    //------------------- Developer New Functions Added End -------------------

    /**
     * Tokenization customer checkout.<br>
     * Add customer to gateway and process the transaction
     * @param $wpCustomer
     * @param $saveInfo
     * @return bool|string
     * @throws SoapFault
     */
    public function TokenProcess($wpCustomer, $saveInfo)
    {
        $this->ebiz_log(__METHOD__ . ' Local customerId: ' . $wpCustomer->ID . ' Mapped customer Id: ' . $wpCustomer->ec_customer_id);

        $securityToken = $this->_getUeSecurityToken();
        $client = new SoapClient($this->_getWsdlUrl(), $this->SoapParams());

        try {

            if ($ebizCustomer = $this->getEbizCustomer($wpCustomer)) {
                $this->custid = $ebizCustomer->CustomerId;
                /*if ($ebizCustomer && $this->enableEconnect) {
                 // this can create issue and map to invalid customer data
                    $this->updateExistingCustomerMap($ebizCustomer, $wpCustomerId);
                }*/
                // customer already exist
                if (!empty($saveInfo)) { // want to save payment method
                    $this->ebiz_log(__METHOD__ . ' Customer already exist, Unable to save payment method and use run transaction.');
                }
                // don't save payment method but allow to run transaction
                return $this->RunTransaction();
            }

            // if customer already exist with same email address, we will map customer locally.
            if ($ebizCustomer = $this->searchAndSyncCustomer($wpCustomer)) {
                $this->ebiz_log(__METHOD__ . ' customer already exist with same email address on the gateway, we will map customer locally.');

            } else {
                //  If customer not exist in the gateway, add customer
                $wpMappedCustomerId = !empty($wpCustomer->ec_customer_id) ? $wpCustomer->ec_customer_id : $wpCustomer->ID;
                $this->ebiz_log(__METHOD__ . ' customer not exist in the gateway, add customer. ID: ' . $wpMappedCustomerId);

                $customerResult = $client->AddCustomer(array(
                    'securityToken' => $securityToken,
                    'customer' => $this->getCustomerData($wpMappedCustomerId)
                ));

                $ebizCustomer = $customerResult->AddCustomerResult ?? false;
                $this->syncCustomer($ebizCustomer, $wpCustomer->ID);
            }

            $this->custid = $ebizCustomer->CustomerId;
            // The  ebiz cusNum should be available in API customer Object to save this request
            if ($ebizCustomerNumber = $this->getCustomerToken($ebizCustomer)) {
                $this->updateCustomerToken($wpCustomer->ID, $ebizCustomerNumber);
            }

            if (!empty($saveInfo) && $ebizCustomer && $ebizCustomerNumber) {
                return $this->addMethodAndRunCustomerTransaction($ebizCustomer, $ebizCustomerNumber);
            }

            // user don't want to save card info use runTransaction
            return $this->RunTransaction();

        } catch (SoapFault $ex) {
            $this->error = $ex->getMessage();
            $this->ebiz_log('Error: ' . $this->error, 'critical');
            return $this->error;
        }
    }

    public function updateCustomerToken($userId, $ebizCustomerNumber): void
    {
        if (!empty($userId) && !empty($ebizCustomerNumber)) {
            global $wpdb;
            $wpdb->update($wpdb->prefix . 'users', ['ec_customer_token' => $ebizCustomerNumber], ['ID' => $userId]);
            update_user_meta($userId, 'CustNum', $ebizCustomerNumber);

            $this->ebiz_log(__METHOD__ . ", Customer (Id: $userId) token updated to. " . $ebizCustomerNumber);
        }
    }

    public function addPaymentMethodProfileToGateway($customerInternalId, $paymentMethodProfile)
    {
        try {
            $this->ebiz_log(__METHOD__);
            $securityToken = $this->_getUeSecurityToken();
            $client = new SoapClient($this->_getWsdlUrl(), $this->SoapParams());
            //add customer payment method
            $paymentMethod = $client->AddCustomerPaymentMethodProfile(
                array(
                    'securityToken' => $securityToken,
                    'customerInternalId' => $customerInternalId,
                    'paymentMethodProfile' => $paymentMethodProfile
                ));

            if (isset($paymentMethod)) {
                return $paymentMethod->AddCustomerPaymentMethodProfileResult;
            }
            return null;

        } catch (Exception $ex) {
            $this->ebiz_log(' Exception: ' . $ex->getMessage(), 'critical');
        }
    }

    /**
     * @param $ebizCustomer
     * @param $ebizCustomerNumber
     *
     * @return bool
     * @throws SoapFault
     */
    private function addMethodAndRunCustomerTransaction($ebizCustomer, $ebizCustomerNumber): bool
    {
        $this->ebiz_log(__METHOD__);
		//add customer payment method
        $paymentMethodId = $this->addPaymentMethodProfileToGateway($ebizCustomer->CustomerInternalId, $this->getCustomerPayment());
        $this->methodID = $paymentMethodId;

		//WOO-863: when 3DSecure is enabled, we need to save the 3ds data, so use runTransaction
		if($this->Is3DS2Enabled()) {
			$status = $this->RunTransaction();
		} else {
			$status = $this->runCustomerTransaction($paymentMethodId, $ebizCustomerNumber);
		}


		return $status;
    }

	private function runCustomerTransaction($paymentMethodId, $ebizCustomerNumber): bool
	{
		$this->ebiz_log(__METHOD__);

		$securityToken = $this->_getUeSecurityToken();
		$client = new SoapClient($this->_getWsdlUrl(), $this->SoapParams());

		$transactionResult = $client->runCustomerTransaction(
			array(
				'securityToken' => $securityToken,
				'custNum' => $ebizCustomerNumber,
				'paymentMethodID' => $paymentMethodId,
				'tran' => $this->getCustomerTransactionRequest()
			));

		$transaction = $transactionResult->runCustomerTransactionResult;

		if (isset($transaction)) {
			return $this->setTransactionResult($transaction);
		}

		return false;
	}

    /**
     * Add new payment method and process the transaction
     * @param $ebizCustomerId
     * @param $wpCustomer
     * @param $saveCardInfo
     * @return bool|string
     * @throws SoapFault
     */
    public function NewPaymentProcess($ebizCustomerId, $wpCustomer, $saveCardInfo)
    {
        $this->ebiz_log('Transaction using ' . __METHOD__);

        $securityToken = $this->_getUeSecurityToken();
        $client = new SoapClient($this->_getWsdlUrl(), $this->SoapParams());

        try {
            // customer don't want to save payment method. use RunTransaction
            if (empty($saveCardInfo)) {
                $this->ebiz_log(__METHOD__ . ' save payment method info empty. customer donnot want to save payment method. use RunTransaction');
                return $this->RunTransaction();
            } else {
                // find customer using SearchCustomers ebiz method
                if ($ebizCustomer = $this->getEbizCustomer($wpCustomer)) {
                    // If local and live CustNum match add payment method start
                    if ($ebizCustomer->CustomerToken == $ebizCustomerId) {
                        $this->custid = $ebizCustomer->CustomerId;

                        $this->ebiz_log(__METHOD__ . ' local and live CustNum matched. use runCustomerTransaction');

                        try {
                            return $this->addMethodAndRunCustomerTransaction($ebizCustomer, $ebizCustomerId);

                        } catch (SoapFault $ex) {
                            $this->error = $ex->getMessage();
                            $this->ebiz_log('Error: ' . $this->error, 'critical');
                            return $this->error;
                        }

                    } else {
                        // Local and live CustNum not matched, use RunTransaction
                        $this->ebiz_log(__METHOD__ . " Local CustNum($ebizCustomerId) and live CustNum($ebizCustomer->CustomerToken) not matched, use RunTransaction.");
                        return $this->RunTransaction();
                    }

                } else {
                    //Ebiz customer not found, add customer and add payment method
                    $this->ebiz_log(__METHOD__ . ' Ebiz customer not found, add customer and add payment method.');
                    // search customer by email
                    if ($ebizCustomer = $this->searchAndSyncCustomer($wpCustomer)) {
                        $this->ebiz_log(__METHOD__ . ' customer already exist with same email address on the gateway, we will map customer locally.');

                        $this->custid = $ebizCustomer->CustomerId;
                    } else {

                        $customerResult = $client->AddCustomer(array(
                            'securityToken' => $securityToken,
                            'customer' => $this->getCustomerData($wpCustomer->ID)
                        ));

                        $ebizCustomer = $customerResult->AddCustomerResult ?? false;

                        $this->syncCustomer($ebizCustomer, $wpCustomer->ID);
                    }

	                if ($ebizCustomerNumber = $this->getCustomerToken($ebizCustomer)) {
		                $this->updateCustomerToken($wpCustomer->ID, $ebizCustomerNumber);
	                }

					return $this->addMethodAndRunCustomerTransaction($ebizCustomer, $ebizCustomerNumber);
                }
            }

        } catch (SoapFault $ex) {
            $this->error = $ex->getMessage();
            $this->ebiz_log('Error: ' . $this->error, 'critical');
            return $this->error;
        }

        return false;
    }

    /**
     * Process a transaction from saved payment method
     *
     * @param int $ebizCustomerId eBizCharge Customer ID
     * @param int $ebizMethodId eBizCharge Payment method ID
     *
     * @return boolean
     */
    public function savedProcess($ebizCustomerId, $ebizMethodId)
    {
        $this->ebiz_log('Transaction using ' . __METHOD__);
        $client = new SoapClient($this->_getWsdlUrl(), $this->SoapParams());
        try {
            $transactionResult = $client->runCustomerTransaction(
                array(
                    'securityToken' => $this->_getUeSecurityToken(),
                    'custNum' => $ebizCustomerId,
                    'paymentMethodID' => $ebizMethodId,
                    'tran' => $this->getCustomerTransactionRequest()
                ));

            $transaction = $transactionResult->runCustomerTransactionResult;

            if (isset($transaction)) {
                return $this->setTransactionResult($transaction);
            }

        } catch (SoapFault $ex) {
            $this->error = $ex->getMessage();
            $this->ebiz_log('Error: ' . $this->error, 'critical');
            return $this->error;
        }

        return false;
    }

    public function updateProcess($customerToken, $paymentMethodId)
    {
        $this->ebiz_log('Transaction using ' . __METHOD__);

        if ($this->updatePaymentMethod($customerToken, $paymentMethodId)) {
            return $this->savedProcess($customerToken, $paymentMethodId);
        }
        return false;
    }

    public function updatePaymentMethod($customerToken, $paymentMethodId, $ccexp = null)
    {
        $this->ebiz_log(__METHOD__);
        try {
            $securityToken = $this->_getUeSecurityToken();
            $client = new SoapClient($this->_getWsdlUrl(), $this->SoapParams());

            $paymentMethod = $this->getCustomerPaymentMethodProfile($customerToken, $paymentMethodId);

            if (!empty($paymentMethod)) {

                if (!empty($paymentMethod->AccountHolderName)) {
                    $paymentMethod->AccountHolderName = $paymentMethod->AccountHolderName;
                }

                $paymentMethod->CardNumber = 'XXXXXX' . substr($paymentMethod->CardNumber, 6);
                //$paymentMethod->CardExpiration = ($this->exp ?? $ccexp) ?? $paymentMethod->CardExpiration;

                if (!empty($this->exp)) {
                    $paymentMethod->CardExpiration = $this->exp;
                } elseif (!empty($ccexp)) {
                    $paymentMethod->CardExpiration = $ccexp;
                } else {
                    $paymentMethod->CardExpiration = substr($paymentMethod->CardExpiration, -2) . '-' . substr($paymentMethod->CardExpiration, 2, 2);
                }

                $paymentMethod->AvsStreet = $this->street ?? $paymentMethod->AvsStreet;
                $paymentMethod->AvsZip = $this->zip ?? $paymentMethod->AvsZip;

                $updatedMethodProfile = $client->updateCustomerPaymentMethodProfile(
                    array(
                        'securityToken' => $securityToken,
                        'customerToken' => $customerToken,
                        'paymentMethodProfile' => $paymentMethod
                    ));

                if ($updatedMethodProfile->UpdateCustomerPaymentMethodProfileResult) {
                    return true;
                }
            }

        } catch (SoapFault $e) {
            $this->error = ' Error: ' . $e->getMessage();
            $this->ebiz_log('Payment method update failed. Error: ' . $e->getMessage(), 'critical');
            return false;
        }

        return false;
    }

    public function RunTransaction()
    {
        $this->ebiz_log('Transaction using ' . __METHOD__);
        try {
            $transactionResult = $this->runTransactionOnGateway($this->getTransactionRequest());
            // setTransactionResult called for success
            if (!empty($transactionResult)) {
                return $this->setTransactionResult($transactionResult);
            }
        } catch (SoapFault $ex) {
            $this->error = $ex->getMessage();
            $this->ebiz_log('Error: ' . $this->error, 'critical');
            return $this->error;
        }

        return false;
    }

    public function getCustomerToken($ebizCustomer)
    {
        $this->ebiz_log(__METHOD__);
        // The ebiz cusNum should be available in API customer Object to save this request
        try {
            $ebizCustomer = $this->getCustomerByInternalId($ebizCustomer->CustomerInternalId);
            return !empty($ebizCustomer) ? $ebizCustomer->CustomerToken : false;
        } catch (SoapFault $ex) {
            return false;
        }
    }

    /**
     * @param $ebizCustomer
     * @throws SoapFault
     */
    public function get_payment_method_details($payment_method_id, $custID)
    {
        global $woocommerce;
        $CustNum = get_user_meta($custID, 'CustNum', true);
        $cardType = '';
        $cardNumber = '';

        try {
            $paymentMethod = $this->getCustomerPaymentMethodProfile($CustNum, $payment_method_id);

            if ($paymentMethod->MethodType == 'cc') {
                $cardType = $this->get_card_type($paymentMethod->CardType);
                $cardNumber = substr($paymentMethod->CardNumber, -4);
            }

            if ($paymentMethod->MethodType == 'check') {
                $cardType = $this->get_card_type($paymentMethod->AccountType);
                $cardNumber = substr($paymentMethod->Account, -4);
            }
            return $cardType . ' ending in ' . $cardNumber;

        } catch (SoapFault $ex) {
            $this->ebiz_log($ex->getMessage(), 'critical');
            return false;
        }
    }

    public function get_card_type($typeof): string
    {
        $type = strtolower($typeof);
        if ($type == 'a') {
            $cardType = 'American Express';
        } elseif ($type == 'm') {
            $cardType = 'Master Card';
        } elseif ($type == 'v') {
            $cardType = 'Visa';
        } elseif ($type == 'ds') {
            $cardType = 'Discover';
        } elseif ($type == 'jcb') {
            $cardType = 'JCB';
        } elseif ($type == 'checking') {
            $cardType = 'Checking';
        } elseif ($type == 'savings') {
            $cardType = 'Savings';
        } else {
            $cardType = $type;
        }
        return $cardType;
    }

    public function HPOS_Enabled(): bool
    {
        if (class_exists(\Automattic\WooCommerce\Utilities\OrderUtil::class)) {
            return OrderUtil::custom_orders_table_usage_is_enabled();
        }

        return false;
    }

    public function getOrderByVersion($orderId)
    {
        if (version_compare(WC()->version, '3.0', '>=')) {
            $order = wc_get_order($orderId);
        } else {
            $order = new Backwards_Compatible_Order($orderId);
        }

        return $order;
    }
    //--------- 3DScure functions start -------------

    /* 3dSecure validation Settings*/
    public function get3DSSettings()
    {
        try {
            $get3DS2SettingsResult = wp_cache_get('get3DS2SettingsResult');
            if ($get3DS2SettingsResult === false) {
                $client = new SoapClient($this->_getWsdlUrl3dSecure(), $this->SoapParams());
                $transactionResult = $client->Get3DS2Settings(
                    ['securityToken' => $this->_getUeSecurityToken()]
                );

                wp_cache_set('get3DS2SettingsResult', $transactionResult->Get3DS2SettingsResult);
                $get3DS2SettingsResult = $transactionResult->Get3DS2SettingsResult;
            }
            return $get3DS2SettingsResult;

        } catch (SoapFault $ex) {
            $this->error = $ex->getMessage();
            $this->ebiz_log('Error: ' . $this->error, 'critical');
            return false;
        }
    }

    public function getSurchargeSettings()
    {
        try {
            $getSurchargeSettingsResult = wp_cache_get('getSurchargeSettingsResult');
            if ($getSurchargeSettingsResult === false) {
                $client = new SoapClient($this->_getWsdlUrl(), $this->SoapParams());
                $transactionResult = $client->GetSurchargeSettings(
                    ['securityToken' => $this->_getUeSecurityToken()]
                );

                wp_cache_set('getSurchargeSettingsResult', $transactionResult->GetSurchargeSettingsResult);
                $getSurchargeSettingsResult = $transactionResult->GetSurchargeSettingsResult;
            }
            return $getSurchargeSettingsResult;

        } catch (SoapFault $ex) {
            $this->error = $ex->getMessage();
            $this->ebiz_log('Error: ' . $this->error, 'critical');
            return false;
        }
    }

    public function addSurchargeInNotes($order, $payment, $totalAmount, $surchargePercentage, $surchargeAmount): void
    {
        $surchargeAmount = (empty($surchargeAmount) || $surchargeAmount === '0.00') ? 'Ineligible' : wc_price($surchargeAmount);
        $order->add_order_note(__("Payment Amount:  <span style='float: right'><b>" . wc_price($payment) . "</b></span> 
             Surcharge ($surchargePercentage%): <span style='float: right'><b>" . $surchargeAmount . "</b></span> <br>
             Total Amount: <span style='float: right'><b>$totalAmount</b></span>", 'woocommerce'));
    }

    /* Get 3dSecure active/inactive bits*/
    public function Is3DS2Enabled(): bool
    {
		$settings = $this->get3DSSettings();
        return isset($settings->Is3DS2Enabled) && (bool) $settings->Is3DS2Enabled;
    }

    public function get_store_currency_symbol()
    {
        return get_woocommerce_currency_symbol();
    }

    public function get_store_currency()
    {
        return get_woocommerce_currency();
    }

    public function getLastOrdrIdHpos()
    {
        $orders = wc_get_orders(array(
            'limit' => 1,
            'orderby' => 'ID',
            'order' => 'DESC',
        ));

        if ($orders) {
            return $orders[0]->get_id();
        }
        return 0;

    }

    public function getLastOrdrId()
    {
        global $wpdb;
        $results = $wpdb->get_col("SELECT MAX(ID) FROM {$wpdb->prefix}posts");
        return reset($results);
    }

    public function getNextOrdrId()
    {
        if ($this->HPOS_Enabled()) {
            return ($this->getLastOrdrIdHpos() + 1);
        }
        return ($this->getLastOrdrId() + 1);
    }

    public function get3DSFormData($orderId = null): array
    {
        $total = WC()->cart->get_total('') * 100;
        $total = str_replace('.', '', $total);

        $data =  [
            'SecurityId' => $this->_getUeSecurityToken()['SecurityId'],
            'Is3DS2Enabled' => $this->Is3DS2Enabled(),
            'storeCurrency' => $this->get_store_currency(),
        ];

		// we have order_id from order_pay page
		if(!empty($orderId)) {
			$order = wc_get_order($orderId);
			$data = array_merge($data, [
				'orderAmount' => $order->get_total() * 100,
				'orderNumber' => $orderId,
				'billing_first_name' => $order->get_billing_first_name(),
				'billing_last_name' => $order->get_billing_last_name(),
				'billing_email' => $order->get_billing_email(),
				'billing_address_1' => $order->get_billing_address_1(),
				'billing_address_2' => $order->get_billing_address_2(),
				'billing_city' => $order->get_billing_city(),
				'billing_phone' => $order->get_billing_phone(),
				'billing_postcode' => $order->get_billing_postcode(),
			]);

		} else {
			$data =  array_merge($data, [
				'orderAmount' => str_replace('.', '', $total),
				'orderNumber' => $this->getNextOrdrId(),
			]);
		}

		return $data;
    }
}