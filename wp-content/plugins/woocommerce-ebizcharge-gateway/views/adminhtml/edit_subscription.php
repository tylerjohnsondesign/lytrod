<?php
$requestCC = 1;
$ebiz = new WC_ebizcharge();
$WC_ebizcharge = $ebiz->_initTransaction(true);

$WC_Gateway_EBizCharge = new WC_Gateway_EBizCharge();
$apiSettings = $WC_Gateway_EBizCharge->getMerchantTransactionData();
$verifyCardBeforeSaving = $apiSettings->VerifyCreditCardBeforeSaving ?? false;

$subscription = $ebiz->getAllRecurrings($_GET['id'] ?? '');
$start = date("Y-m-d", strtotime($subscription->rec_start_date));
$expire = date("Y-m-d", strtotime($subscription->rec_end_date));

$item = $subscription->item_name;

$product_id = $subscription->item_id;
$product = new WC_product($product_id);
$recurringId = $subscription->id;

$qty = $subscription->rec_item_qty;
$price = $product->get_price();
$productUnitPrice = number_format($price, 2);
$productFinalPrice = number_format($price * $qty, 2);

$customerId = $subscription->customer_id;
$customer = new WC_Customer($customerId);

$billingAddress = @unserialize(stripslashes($subscription->billing_address));
if (is_array($billingAddress)) {
	$billingAddress = implode(' - ', array_filter($billingAddress));
} else {
	$billingAddress = $subscription->billing_address;
}
$shippingAddress = @unserialize(stripslashes($subscription->shipping_address));
if (is_array($shippingAddress)) {
	$shippingAddress = implode(' - ', array_filter($shippingAddress));
} else {
	$shippingAddress = $subscription->shipping_address;
}

$customerData = $ebiz->getCustomerData($customerId);
$getEbzcCustInternalId = $ebiz->get_customer_internalid($customerId);
$schedulePaymentId = $subscription->rec_scheduled_payment_internal_id;

$payment = $ebiz->getSearchScheduledRecurringPayments($customerId, $getEbzcCustInternalId, $schedulePaymentId);
$paymentType = !empty($payment->PmType) ? $payment->PmType : '';
$payment_type = ($paymentType == 'ACH') ? 'ACH' : 'credit_card';

if (!empty($_GET['pref']))
{
	$referer = esc_html(admin_url('admin.php?page='.$_GET['pref']));
	$pref = $_GET['pref'];
}else{
	$referer = wp_get_raw_referer();
	$pref = '';
}

if (!empty($customerData['BillingAddress'])) {
	$billCustomerName = $customerData['BillingAddress']['FirstName'] . ' ' . $customerData['ShippingAddress']['LastName'];
	$billAddress1 = $customerData['BillingAddress']['Address1'];
	$billCity = $customerData['BillingAddress']['City'];
	$billState = $customerData['BillingAddress']['State'];
	$billZipCode = $customerData['BillingAddress']['ZipCode'];
}

if (!empty($customerData['ShippingAddress'])) {
	$shipCustomerName = $customerData['ShippingAddress']['FirstName'] . ' ' . $customerData['ShippingAddress']['LastName'];
	$shipAddress1 = $customerData['ShippingAddress']['Address1'];
	$shipCity = $customerData['ShippingAddress']['City'];
	$shipState = $customerData['ShippingAddress']['State'];
	$shipZipCode = $customerData['ShippingAddress']['ZipCode'];
} else {
	$shipCustomerName = '';
	$shipAddress1 = '';
	$shipCity = '';
	$shipState = '';
	$shipZipCode = '';
}
?>
<div class="ajax-loader" style="visibility: hidden">
    <img src="<?php echo plugin_dir_url(dirname(__FILE__)) . '../assets/images/ajax-loader.gif'; ?>" width="50"
         height="50" class="img-responsive"/>
</div>
<div class="wrap" id="PageMain">
    <h2>Manage Subscription</h2>
    <form method="post" id="form-validate" class="sub-edit-form"
          action="<?php echo esc_html(admin_url('admin.php?page=edit-subscription')); ?>">

        <input type="hidden" id="verify_card_before_saving" value="<?php echo $verifyCardBeforeSaving; ?>"/>
        <input type="hidden" name="action" id="action" value="update_applicant">
        <input type="hidden" name="requestCC" id="requestCC" value="1">
        <input type="hidden" name="pref" id="pref" value="<?php echo $pref; ?>">
        <input type="hidden" name="payment_method_name" id="payment_method_name"
               value="<?php echo $subscription->rec_pmethod_name; ?>"/>
        <input type="hidden" name="saved_method_name" id="saved_method_name"
               value="<?php echo $subscription->rec_pmethod_name; ?>"/>
        <div class="row clearAll widthBlock">
            <div id="Frecheck" style="color: red; font-size : medium; text-align: center; "></div>
        </div>
        <div class="row clearAll cartContainer">
			<?php
			//if ($payment && $payment->ScheduleStatus == 1
			if ($subscription && $subscription->rec_status == 1) { ?>
                <div class="msgUnsub">
                    <p>(This subscription has been unsubscribed.)</p>
                </div>
				<?php
				//else if ($payment && $payment->ScheduleStatus == 3
			} else if ($subscription && $subscription->rec_status == 3) { ?>
                <div class="msgUnsub">
                    <p>(This subscription has been suspended.)</p>
                </div>
			<?php } ?>

            <table class="col100">
                <tbody>
                <tr class="itemTr">
                    <td><strong>Item Information</strong></td>
                    <td><strong>Price</strong></td>
                    <td><strong>Qty</strong></td>
                    <td><strong>Subtotal</strong></td>
                </tr>
                <tr>
                    <td>
                        <p class="left">
                            <img src="<?php echo wp_get_attachment_url($product->get_image_id()); ?>" width="100"/>
                        </p>
                        <p class="left title">
							<?php echo $item; ?>
                        </p>
                    </td>
                    <td><?php echo $productUnitPrice; ?></td>
                    <td width="225px"><input type="text" class="qtyWidth" name="qty" value="<?php echo $qty; ?>" maxlength="3"></td>
                    <td>
						<?php echo $productFinalPrice; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">Existing subscribed amount is <strong>(<?php echo number_format($subscription->rec_amount, 2); ?>)</strong> for <strong>(<?php echo $qty; ?>)</strong> item(s).
						<?php if (!empty($subscription->rec_coupon)) { echo 'Coupon code ('.$subscription->rec_coupon.') is applied.'; } ?></td>
                </tr>
                </tbody>
            </table>

            <p class="border"></p>

        </div>
        <div class="cartContainer" style="background: #0c0c0c;">
            <div class="widthBlock60">
                <input type="hidden" id="recurring_id" name="table_rec_id" value="<?php echo $recurringId; ?>">
                <input type="hidden" id ="eb_rec_method_id" name="eb_rec_method_id" value="<?php echo $subscription->rec_pmethod_id; ?>"/>
                <input type="hidden" name="mid"
                       value="<?php echo $subscription->rec_scheduled_payment_internal_id; ?>"/>
                <input type="hidden" id="selectdivCustomer" name="custId" value="<?php echo $customerId; ?>"/>
                <input type="hidden" name="custIntId" value="<?php echo $getEbzcCustInternalId; ?>"/>
                <input type="hidden" name="item_id" id="selectdivProduct"
                       value="<?php echo $subscription->item_id; ?>"/>
                <input type="hidden" id="rec_id" name="rec_id" value="<?php echo $recurringId; ?>"/>
                <input type="hidden" name="amount" value="<?php echo $price; ?>"/>
                <input type="hidden" name="schedulename" value="<?php echo $payment->ScheduleName ?? ''; ?>"/>
                <input type="hidden" name="receiptnote" value="<?php echo $payment->ReceiptNote ?? ''; ?>"/>
                <input type="hidden" name="pageCase" id="pageCase" value="edit"/>

                <div class="row widthBlockEdit clearAll">
                    <div class="col-25 moveLeft">
                        <label class="myLabel">Coupon Code</label>
                    </div>
                    <div class="col-75 moveRight">
                        <input type="text" class="couponWidth" name="coupon" id="coupon" value="<?php echo $subscription->rec_coupon; ?>">
                    </div>
                </div>

                <div class="row widthBlockEdit clearAll">
                    <div class="col-25 moveLeft">
                        <label class="myLabel">Frequency<span class="required_field"> *</span></label>
                    </div>
                    <div class="col-75 moveRight">
                        <select name="schedule" id="freqId" class="input fixedWidth search-custom-class">
							<?php $WC_Gateway_EBiz_Rec->getFrequencyOptions($subscription->rec_frequency); ?>
                        </select>
                    </div>
                </div>
                <fieldset id="dates">
                    <div class="row widthBlockEdit clearAll">
                        <div class="col-25 moveLeft">
                            <label class="myLabel">Subscription Dates</label>
                        </div>
                        <div class="col-75 moveRight widthFix400">

                            <div class="rangeDate">
                                <label class="label" for="start_date"><span>Start Date</span><span class="required_field"> *</span></label>&nbsp;&nbsp;
                                <input type="text" class="dateclass widthFix120" name="start_date" id="start_date"
                                       autocomplete="off" value="<?php echo !empty($start) ? $start : ''; ?>"
                                       placeholder="YYYY-MM-DD"/>

                                <div id="start_date_required" style="color:red; float: right"></div>
                            </div>
                            <div class="rangeDate">
                                <label class="label" for="expire_date"><span>End Date</span></label>&nbsp;&nbsp;
                                <input type="text" class="dateclass widthFix120" name="expire_date" id="expire_date"
                                       value="<?php echo !empty($expire) ? $expire : ''; ?>" autocomplete="off"
                                       placeholder="YYYY-MM-DD"/>

                                <div id="Datecheck" style="color: red; margin-left: 65px"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row widthBlockEdit clearAll">
                        <div class="col-25 moveLeft">
                            <label class="myLabel">
								<?php echo __('Recur Indefinitely'); ?>
                            </label>
                        </div>
                        <div class="col-75 moveRight">
                            <input type="checkbox" id="rec_indefinitely" name="rec_indefinitely" value="1" <?php echo ($subscription->rec_indefinitely == 1) ? 'checked' : ''; ?>>
                        </div>
                    </div>
                </fieldset>
                <div class="row widthBlockEdit clearAll">
                    <div class="col-25 moveLeft">
                        <label class="myLabel">Billing Address<span class="required_field"> *</span></label>
                    </div>
                    <div class="col-75 moveRight" id="blockBillId">
                        <select name="addressBill" id="addressBill" class="input fixedWidth search-custom-class">
                            <option value='<?php echo stripslashes($subscription->billing_address); ?>'><?php echo $billingAddress; ?></option>
                        </select>
                    </div>
                </div>
                <div class="row widthBlockEdit clearAll">
                    <div class="col-25 moveLeft">
                        <label class="myLabel">Shipping Address <span class="required_field"> *</span></label>
                    </div>
                    <div class="col-75 moveRight">
                        <select name="addressShip" id="addressShip" class="input fixedWidth search-custom-class">
                            <option value='<?php echo stripslashes($subscription->shipping_address); ?>'><?php echo $shippingAddress; ?></option>
                        </select>
                    </div>
                </div>

                <div class="row widthBlockEdit clearAll">
                    <div class="col-25 moveLeft">
                        <label class="myLabel">Shipping Method <span class="required_field"> *</span></label>
                    </div>
                    <div class="col-75 moveRight">
                        <select name="shipping_method" id="shippingMethod" class="input fixedWidth search-custom-class">
                            <option value=''>Please Select Shipping Method</option>
							<?php $ebiz->getShippingMethods($subscription->rec_shipping_method); ?>
                        </select>
                    </div>
                </div>

                <div class="row widthBlockEdit clearAll">
                    <div class="col-25 moveLeft">
                        <label class="myLabel">Shipping Amount <span class="required_field"> *</span></label>
                    </div>
                    <div class="col-75 moveRight">
                        <input type="text" class="qtyWidth numberonly" id="shipping_amount" name="shipping_amount" value="<?php echo $subscription->rec_shipping_amount; ?>" pattern="\d*">
                    </div>
                </div>

                <div class="row clearAll widthBlockEdit tgl-radio-tabs" id="payop">
                    <div class="col-25 moveLeft">
                        <label class="myLabel">Payment Type</label>
                    </div>
                    <div class="col-75 moveRight">
                        <input type="hidden" name="ebiz_option" id="ebiz_option" value="<?php echo $payment_type ?>"/>
                        <div class="row" id="my-select-content"
                             style="padding-bottom: 10px; border-bottom: 1px solid silver">
							<?php if ($WC_Gateway_EBizCharge->is_cc_enabled()) { ?>
                                <input type="radio" name="payment[ebzc_option_type]" id="ebizs_option" value="credit_card"
                                       class="ebizs_option tgl-radio-tab-child" checked/>
                                <label for="ebizs_option" class="label">
                                    <span>Pay by Card</span>&nbsp;
                                    <img class="card-img" src="<?php echo PLUGIN_DIR . 'assets/images/card.png'; ?>" width="20"
                                         height="15"/>
                                </label>
							<?php } ?>
							<?php if ($WC_Gateway_EBizCharge->is_ach_enabled()) { ?>
                                <input style="margin-left: 15px" type="radio" name="payment[ebzc_option_type]"
                                       id="ebizs_option_ach" value="ACH" class="ebizs_option tgl-radio-tab-child"/>
                                <label for="ebizs_option_ach" class="label">
                                    <span>Pay by Bank </span>
                                    <img class="bank-img" src="<?php echo PLUGIN_DIR . 'assets/images/bank.png'; ?>" width="20"
                                         height="15"/>
                                </label>
							<?php if (!$WC_Gateway_EBizCharge->is_cc_enabled()) { ?>
                                <script type="text/javascript">
                                    jQuery(document).ready(function () {
                                        jQuery('#ebizs_option_ach').prop('checked', true);
                                        jQuery("#ebizs_option_ach").trigger('click');
                                    });
                                </script>
							<?php } ?>
							<?php } ?>
                        </div>
                    </div>
                </div>
				<?php if ($WC_Gateway_EBizCharge->is_ach_enabled() || $WC_Gateway_EBizCharge->is_cc_enabled()) { ?>
                    <div class="row clearAll widthBlockEdit">
                        <div class="col-25 moveLeft">
                            <label class="myLabel">Payment Options</label>
                        </div>
                        <div class="col-75 moveRight">
                            <input type="hidden" name="payment[ebiz_option_payment]" id="ebiz_option_payment"
                                   value="saved"/>

                            <div class="row" style="padding-bottom: 10px; border-bottom: 1px solid silver">
                                <input type="radio" name="payment[ebzc_account_type]" id="new" value="new"
                                       class="ebiz_option_card" checked/>
                                <label for="new" class="label">
                                    <span id="newLabel">New Card</span>
                                </label>

                                <input style="margin-left: 15px" type="radio" name="payment[ebzc_account_type]" id="saved"
                                       value="saved" class="ebiz_option_card"/>
                                <label for="saved" class="label">
                                    <span id="savedLabel">Saved Card</span>
                                </label>

                                <input style="margin-left: 15px" type="radio" name="payment[ebzc_account_type]" id="update"
                                       value="update" class="ebiz_option_card update-radio"/>
                                <label for="update" class="label update-radio">
                                    <span id="updateLabel">Update Card</span>
                                </label>

                            </div>
                        </div>
                    </div>
				<?php } ?>
                <div class="widthBlockEdit" id="add-new">

                    <div style="color: red; display: none; text-align: center;" id="payment_msg">
                        All fields are required.
                    </div>
                    <div style="color: red; display: none;" id="payment_msgcNum">
                        Please enter valid credit card information.
                    </div>
                    <div style="color: red; display: none; text-align: center;" id="payment_msgCvv">
                        Please enter CVV between 3 and 4 digits.
                    </div>
                    <br>
                    <div class="clearAll customFullWidth">
                        <div class="col-25 moveLeft">
                            <label class="myLabel">
                                <span><?php echo __('Name on Card'); ?></span>
                                <span class="required_field"> *</span>
                            </label>
                        </div>
                        <div class="col-75 moveRight">
                            <input type="text" id="payment_cc_owner_new" name="payment[cc_owner]"
                                   title="<?php echo __('Name on Card'); ?>" class="input-text required" value=""/>
                        </div>
                    </div>

                    <div class="clearAll customFullWidth">
                        <div class="col-25 moveLeft">
                            <label class="myLabel">
                                <span><?php echo __('Credit Card Number'); ?></span>
                                <span class="required_field"> *</span>
                            </label>
                        </div>
                        <div class="col-75 moveRight">
                            <input type="text" id="payment_cc_number_new" name="payment[cc_number]"
                                   title="<?php echo __('Credit Card Number'); ?>"
                                   class="input-text control-text required" value="" pattern="\d*" minlength="15"
                                   maxlength="16"/>
                        </div>
                    </div>
                    <div class="clearAll customFullWidth">
                        <div class="col-25 moveLeft">
                            <label class="myLabel">
                                <span><?php echo __('Credit Card Type'); ?></span>
                                <span class="required_field"> *</span>
                            </label>
                        </div>
                        <div class="col-75 moveRight">
                            <select id="payment_cc_type_new" name="payment[cc_type]" class="select control-select" style="float: left; margin-left: 0;">
								<?php $ebiz->getCardOptions() ?>
                            </select>
                        </div>
                    </div>

                    <div class="clearAll customFullWidth" id="payment_cc_exp_new">
                        <div class="col-25 moveLeft">
                            <label class="myLabel">
                                <span><?php echo __('Expiration Date'); ?></span>
                                <span class="required_field"> *</span>
                            </label>
                        </div>
                        <div class="col-75 moveRight control">
                            <select id="payment_exp_new" name="payment[cc_exp_month]"
                                    class="month select control-select">
								<?php $ebiz->getExpiryMonths(); ?>
                            </select>
                            <label id="payment_exp_new-error" class="error" for="payment_exp_new" style="display: none;"></label>
                            <select id="payment_exp_yr_new" name="payment[cc_exp_year]"
                                    class="year select control-select">
								<?php $ebiz->getExpiryYears(); ?>
                            </select>
                        </div>
                    </div>
					<?php if ($requestCC) { ?>
                        <div class="clearAll customFullWidth">
                            <div class="col-25 moveLeft">
                                <label class="myLabel">
                                    <span><?php echo __('CVV'); ?></span>
                                    <span class="required_field"> *</span>
                                </label>
                            </div>
                            <div class="col-75 moveRight control">
                                <input type="text" id="payment_cc_cid_new" name="payment[cc_cid]"
                                       title="<?php echo __('Card Verification Number'); ?>" style="width: 50px;"
                                       pattern="\d*" minlength="3" maxlength="4" class="required"/>
                            </div>
                        </div>
					<?php } ?>

                    <div class="clearAll customFullWidth">
                        <div class="col-25 moveLeft">
                            <label class="myLabel">
                                <span><?php echo __('Billing Street'); ?></span>
                                <span class="required_field"> *</span>
                            </label>
                        </div>
                        <div class="col-75 moveRight control">
                            <input type="text" id="payment_avs_street_new" name="payment[avs_street]"
                                   title="<?php echo __('Billing Street'); ?>" class="input-text required" value=""/>
                        </div>
                    </div>

                    <div class="clearAll customFullWidth">
                        <div class="col-25 moveLeft">
                            <label class="myLabel">
                                <span><?php echo __('Billing Zip/Postal Code'); ?></span>
                                <span class="required_field"> *</span>
                            </label>
                        </div>
                        <div class="col-75 moveRight control">
                            <input type="text" id="payment_avs_zip_new" name="payment[avs_zip]"
                                   title="<?php echo __('Billing Zip/Postal Code'); ?>" class="input-text required" value=""/>
                        </div>
                    </div>
                </div>
                <div class="row clearAll widthBlockEdit" id="add-new-card-saved">
                    <div class="col-25 moveLeft">
                        <label class="myLabel">Payment Method</label>
                    </div>
                    <div class="col-75 moveRight">
                        <select name="method_id" id="selectdivPayment" style="width: 600px; float: left; margin-left: 0;" class="required">
                            <option value=''>Please Select Payment Method</option>
                        </select>
                    </div>
                </div>
                <div class="row clearAll widthBlockEdit" id="add-new-card-update">

                    <div class="clearAll customFullWidth">
                        <div class="col-25 moveLeft">
                            <label class="myLabel">
                                <span><?php echo __('Billing Street'); ?></span>
                                <span class="required_field"> *</span>
                            </label>
                        </div>
                        <div class="col-75 moveRight control">
                            <input type="text" id="payment_avs_street_update" name="avs_street"
                                   title="<?php echo __('Billing Street'); ?>" class="input-text required avs_street" />
                        </div>
                    </div>

                    <div class="clearAll customFullWidth">
                        <div class="col-25 moveLeft">
                            <label class="myLabel">
                                <span><?php echo __('Billing Zip/Postal Code'); ?></span>
                                <span class="required_field"> *</span>
                            </label>
                        </div>
                        <div class="col-75 moveRight control">
                            <input type="text" id="payment_avs_zip_update" name="avs_zip"
                                   title="<?php echo __('Billing Zip/Postal Code'); ?>" class="input-text required avs_zip" />
                        </div>
                    </div>
                </div>

				<?php if ($WC_Gateway_EBizCharge->is_ach_enabled()) { ?>
                    <div class="add-new widthBlockEdit" id="add-new-ach" style="display: none;">
                        <span style="color: red; display: none;" id="payment_msg_ach">All fields are required.</span>
                        <span style="color: red; display: none;" id="payment_msgcNumAch">The account number should be between 5 and 17 digits.</span>
                        <span style="color: red; display: none;" id="payment_msgcNumRoutAch">Please enter valid 9 digit routing number.</span>

                        <div class="clearAll customFullWidth">
                            <div class="col-25 moveLeft">
                                <label class="myLabel">
                                    <span><?php echo __('Account Holder'); ?></span>
                                </label>
                            </div>

                            <div class="col-75 moveRight control">
                                <input type="text" id="payment_cc_owner_new_ach" name="payment[cc_owner_ach]"
                                       title="<?php echo __('Account Holder'); ?>" class="input-text required"
                                       value=""/>
                            </div>
                        </div>

                        <div class="clearAll customFullWidth">
                            <div class="col-25 moveLeft">
                                <label class="myLabel">
                                    <span><?php echo __('Account Type'); ?></span>
                                </label>
                            </div>

                            <div class="col-75 moveRight">
                                <select id="payment_cc_type_new_ach" name="payment[cc_type_ach]"
                                        class="required-entry select control-select">
                                    <option value="checking">Checking</option>
                                    <option value="savings">Savings</option>
                                </select>
                            </div>
                        </div>

                        <div class="clearAll customFullWidth">
                            <div class="col-25 moveLeft">
                                <label class="myLabel">
                                    <span><?php echo __('Account Number'); ?></span>
                                </label>
                            </div>

                            <div class="col-75 moveRight">
                                <input type="text" id="payment_cc_number_new_ach" name="payment[cc_number_ach]"
                                       title="<?php echo __('Account Number'); ?>" minlength="5" class="input-text required numberonly"
                                       value=""/>
                            </div>
                        </div>

                        <div class="clearAll customFullWidth">
                            <div class="col-25 moveLeft">
                                <label class="myLabel">
                                    <span><?php echo __('Routing Number'); ?></span>
                                </label>
                            </div>

                            <div class="col-75 moveRight">
                                <input type="text" id="payment_cc_cid_new_ach" minlength="9" maxlength="9" name="payment[cc_routing_ach]"
                                       title="<?php echo __('Routing Number'); ?>" class="required numberonly"/>
                            </div>
                        </div>
                    </div>
				<?php } ?>
            </div>
            <div class="left">
                <div class="row leftOnly ">
                    <h3 class="">Customer Info</h3>
                    <address>
                            <span class="fontSize"><?php echo $billCustomerName;
	                            echo !empty($billCustomerName) ? '<br>' : ''; ?></span>
                        <span class="fontSize"><?php echo $billAddress1;
							echo !empty($billAddress1) ? '<br>' : '';
							?></span>
                        <span class="fontSize"><?php echo $billCity;
							echo !empty($billCity) ? '<br>' : '';
							?></span>

                        <span class="fontSize"> <?php echo $billState;
							echo !empty($billState) ? '<br>' : '';
							?></span>
                        <span class="fontSize"><?php echo $billZipCode;
							echo !empty($billZipCode) ? '<br>' : '';
							?></span>
                    </address>
                </div>
                <div class="row rightOnly ">
                    <h3 class="">Shipping Address</h3>

                    <address>
                      <span class="fontSize"><?php echo $shipCustomerName;
	                      echo !empty($shipCustomerName) ? '<br>' : ''; ?></span>
                        <span class="fontSize"><?php echo $shipAddress1;
							echo !empty($shipAddress1) ? '<br>' : '';
							?></span>
                        <span class="fontSize">
                            <?php echo $shipCity;
                            echo !empty($shipCity) ? '<br>' : ''; ?>
                        </span>

                        <span class="fontSize"> <?php echo $shipState;
							echo !empty($shipState) ? '<br>' : '';
							?></span>
                        <span class="fontSize"><?php echo $shipZipCode;
							echo !empty($shipZipCode) ? '<br>' : '';
							?></span>
                    </address>
                </div>
            </div>
        </div>
    </form>

    <form class="" id="unsub" action="<?php echo esc_html(admin_url('admin.php?page=edit-subscription')); ?>"
          method="post">
		<?php
		$scheduleStatus = $subscription->rec_status;
        // unsubscribed or deleted
		if ($scheduleStatus == 1 || $scheduleStatus == 3) {
			$statusValue = 0;
			$statusTtile = 'Resubscribe';
		} else {
			$statusValue = 1;
			$statusTtile = 'Unsubscribe';
		}
		?>
        <input type="hidden" name="table_rec_id" value="<?php echo $recurringId ?>">
        <input type="hidden" id="selectdivCustomer" name="custId" value="<?php echo $customerId; ?>"/>
        <input type="hidden" name="unsub" value="yes"/>
        <input type="hidden" name="sid" id="sid" value="<?php echo $statusValue; ?>"/>
        <input type="hidden" name="mid" value="<?php echo $_GET['mid'] ?? ''; ?>"/>
        <input type="hidden" name="pref" id="pref" value="<?php echo $pref; ?>">
    </form>
    <form class="" id="delsub" action="<?php echo esc_html(admin_url('admin.php?page=edit-subscription')); ?>"
          method="post">
        <input type="hidden" name="table_rec_id" value="<?php echo $recurringId ?>">
        <input type="hidden" id="selectdivCustomer" name="custId" value="<?php echo $customerId; ?>"/>
        <input type="hidden" name="delete" value="yes"/>
        <input type="hidden" name="mid" value="<?php echo $_GET['mid'] ?? ''; ?>"/>
        <input type="hidden" name="pref" id="pref" value="<?php echo $pref; ?>">
    </form>
    <div class="row clearAll cartContainer">
        <div class="actions-toolbar">
            <div class="primary">
				<?php if ($WC_Gateway_EBizCharge->is_ebizcharge_enabled()) {

                    if($WC_Gateway_EBizCharge->is_recurring_enabled()) {
                    ?>
                    <button type="button" id="" class="button button-primary save_btn" title="<?php echo __('Update Subscription'); ?>">
                        <span><?php echo __('Update Subscription'); ?></span>
                    </button>
                    <button type="button" id="unsub_btn" class="button button-info unsub_btn" title="<?php echo __($statusTtile); ?>">
                        <span><?php echo __($statusTtile); ?></span>
                    </button>
				<?php } else if($scheduleStatus == 0) { ?>

                        <button type="button" id="unsub_btn" class="button button-info unsub_btn" title="<?php echo __($statusTtile); ?>">
                            <span><?php echo __($statusTtile); ?></span>
                        </button>
                    <?php }

                } ?>
                <button type="button" class="button" id="new-cancel-btn"
                        onclick="location.href='<?php echo $referer; ?>';">
                    <span>Cancel</span>
                </button>
            </div>
        </div>
    </div>
</div>