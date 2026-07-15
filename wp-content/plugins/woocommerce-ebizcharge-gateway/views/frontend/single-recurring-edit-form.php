<?php
/*
Edit single recurring Form
*/

global $wpdb, $WC_Gateway_EBizCharge, $WC_Gateway_EBiz_Rec;
$recurrings_list_url = $WC_Gateway_EBiz_Rec->makePermalinkUrl('subscriptions');
$recurrings_update_url = $WC_Gateway_EBiz_Rec->makePermalinkUrl('subscriptions-update?recurringID=' . $_GET['recID']);

$ebiz = new WC_ebizcharge();
$WC_ebizcharge = $ebiz->_initTransaction(true);

$table = $wpdb->prefix . "ebizcharge_recurring";
$currentUserId = $WC_Gateway_EBiz_Rec->getCurrentUserId();
$customer = new WC_Customer($currentUserId);

$recID = $_GET['recID'];
// get data for edit recurring
$sql = $wpdb->prepare("SELECT * FROM " . $table . " Where customer_id=%d AND id=%d", $currentUserId, $recID);
$query = $wpdb->get_results($sql);

foreach ($query as $recurring) {
	if ($currentUserId == $recurring->customer_id) {
		$product = wc_get_product($recurring->item_id);

		$recurring_billing = $customer->get_billing();
		$recurring_shipping = $customer->get_shipping();

		if (empty($recurring_shipping['address_1'])) {
			$recurring_shipping = $recurring_billing;
		}

        $recurring_billing_address = $recurring_billing['first_name'] . ', '
            . $recurring_billing['last_name'] . ', '
            . $recurring_billing['company'] . ', '
            . $recurring_billing['address_1'] . ', '
            . $recurring_billing['address_2'] . ', '
            . $recurring_billing['city'] . ', '
            . $recurring_billing['state'] . ', '
            . $recurring_billing['postcode'] . ', '
            . $recurring_billing['country'];

        $recurring_shipping_address = $recurring_shipping['first_name'] . ', '
            . $recurring_shipping['last_name'] . ', '
            . $recurring_shipping['company'] . ', '
            . $recurring_shipping['address_1'] . ', '
            . $recurring_shipping['address_2'] . ', '
            . $recurring_shipping['city'] . ', '
            . $recurring_shipping['state'] . ', '
            . $recurring_shipping['postcode'] . ', '
            . $recurring_shipping['country'];
        ?>
        <div class="container-subs">
            <div class="woocommerce" id="sub-alerts">
				<?php
				if (isset($_GET['action'])) {
					$action = $_GET['action'];
					if (in_array($action, ['unsubscribed', 'resubscribed', 'updated', 'deleted'])) {
						?>
                        <ul class="woocommerce-message" role="alert">
                            <li>Subscription successfully <?php echo $action; ?>.</li>
                        </ul>
					<?php } else { ?>
                        <ul class="woocommerce-error" role="alert">
                            <li>Subscription not successfully updated.</li>
                        </ul>
						<?php
					}
				}
				if ($recurring->rec_status == 1) { ?>
                    <ul class="woocommerce-error" role="alert" style="display: block !important;">
                        <li>This subscription has been unsubscribed.</li>
                    </ul>
				<?php }
				if ($recurring->rec_status == 3) { ?>
                    <ul class="woocommerce-error" role="alert" style="display: block !important;">
                        <li>This subscription has been suspended.</li>
                    </ul>
				<?php } ?>
                <ul class="woocommerce-error show-error" role="alert" style="display: none;">
                    <li></li>
                </ul>
                <ul class="woocommerce-message show-message" role="alert" style="display: none;">
                    <li></li>
                </ul>
            </div>
            <div id="subscription-edit" class="subscription-edit">
                <h1></h1>
                <form method="post" id="update" action="<?php echo $recurrings_update_url; ?>" novalidate="novalidate">
                    <input type="hidden" name="update" value="yes">
                    <input type="hidden" name="scheduledPaymentInternalId"
                           value="<?php echo $recurring->rec_scheduled_payment_internal_id; ?>">
                    <input type="hidden" name="itemID" value="<?php echo $recurring->item_id; ?>">
                    <input type="hidden" name="variationID" value="<?php echo $recurring->item_variation_id; ?>">
                    <input type="hidden" name="itemName" value="<?php echo $recurring->item_name; ?>">
                    <input type="hidden" name="itemPrice" value="<?php echo $product->get_price(); ?>">
                    <input type="hidden" name="customerID" value="<?php echo $recurring->customer_id; ?>">
                    <input type="hidden" name="savedPMethod" value="<?php echo $recurring->rec_pmethod_id; ?>">
                    <div class="ebiz-row clearAll cartContainer">
                        <table class="col100">
                            <tbody>
                            <tr class="itemTr">
                                <td colspan="2"><strong>Item Information</strong></td>
                                <td><strong>Price</strong></td>
                                <td><strong>Qty</strong></td>
                                <td><strong>Subtotal</strong></td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="<?php echo wp_get_attachment_image_url($product->get_image_id(), 'thumbnail') ?>"
                                         width="100">
                                </td>
                                <td><?php echo $product->get_name(); ?></td>
                                <td><?php echo $WC_Gateway_EBiz_Rec->get_store_currency() . $product->get_price(); ?></td>
                                <td><input type="text" class="qtyWidth qty" name="qty"
                                           value="<?php echo $recurring->rec_item_qty; ?>" maxlength="3" size="3"></td>
                                <td> <?php echo $WC_Gateway_EBiz_Rec->get_store_currency() . ($product->get_price() * $recurring->rec_item_qty); ?> </td>
                            </tr>
                            <tr>
                                <td colspan="4">Existing subscribed amount is
                                    <strong>(<?php echo number_format($recurring->rec_amount, 2); ?>)</strong> for
                                    <strong>(<?php echo $recurring->rec_item_qty; ?>)</strong> item(s).
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <p class="border"></p>
                    </div>
                    <div class="cartContainer">
                        <div class="widthBlock60">
                            <div class="ebiz-row widthBlockEdit clearAll">
                                <div class="col-25 moveLeft">
                                    <label class="myLabel" for="frequency">Coupon Code</label>
                                </div>
                                <div class="col-75 moveRight">
                                    <input type="text" class="couponWidth" name="coupon" id="coupon"
                                           value="<?php echo $recurring->rec_coupon; ?>">
                                </div>
                            </div>
                            <div class="ebiz-row widthBlockEdit clearAll">
                                <div class="col-25 moveLeft">
                                    <label class="myLabel" for="frequency">Frequency</label>
                                </div>
                                <div class="col-75 moveRight">
                                    <select name="frequency" id="frequency" class="input fixedWidth frequency">
										<?php echo $WC_Gateway_EBiz_Rec->getFrequencyOptions($recurring->rec_frequency); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="ebiz-row widthBlockEdit clearAll">
                                <div class="col-25 moveLeft">
                                    <label class="myLabel" for="sdate">Start Date</label>
                                </div>
                                <div class="col-75 moveRight">
                                    <input type="text" class="sdate _has-datepicker" name="sdate" id="sdate"
                                           autocomplete="on"
                                           value="<?php echo date("Y-m-d", strtotime($recurring->rec_start_date)); ?>"
                                           placeholder="YYYY-MM-DD">
                                </div>
                            </div>
                            <div class="ebiz-row widthBlockEdit clearAll">
                                <div class="col-25 moveLeft">
                                    <label class="myLabel" for="edate">End Date</label>
                                </div>
                                <div class="col-75 moveRight">
                                    <input type="text" class="edate _has-datepicker" name="edate" id="edate"
                                           autocomplete="on"
                                           value="<?php echo date("Y-m-d", strtotime($recurring->rec_end_date)); ?>"
                                           placeholder="YYYY-MM-DD">
                                </div>
                            </div>

                            <div class="ebiz-row widthBlockEdit clearAll">
                                <div class="col-25 moveLeft">
                                    <label class="myLabel" for="indefinitely">Recur Indefinitely</label>
                                </div>
                                <div class="col-75 moveRight">
                                    <input type="checkbox" id="indefinitely" class="indefinitely"
                                           name="indefinitely"
                                           value="<?php echo $recurring->rec_indefinitely; ?>" <?php if ($recurring->rec_indefinitely == 1) {
										echo "checked";
									} ?>>
                                </div>
                            </div>

                            <div class="ebiz-row widthBlockEdit clearAll">
                                <div class="col-25 moveLeft">
                                    <label class="myLabel" for="addressBill">Billing Address</label>
                                </div>
                                <div class="col-75 moveRight" id="blockBillId">
                                    <select name="addressBill" id="addressBill" class="input fixedWidth addressBill">
                                        <option value="<?php echo $recurring->customer_id; ?>"><?php echo $recurring_billing_address; ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="ebiz-row widthBlockEdit clearAll">
                                <div class="col-25 moveLeft">
                                    <label class="myLabel" for"addressShip">Shipping Address</label>
                                </div>
                                <div class="col-75 moveRight">
                                    <select name="addressShip" id="addressShip" class="input fixedWidth addressShip">
                                        <option value="<?php echo $recurring->customer_id; ?>"><?php echo $recurring_shipping_address; ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="ebiz-row widthBlockEdit clearAll">
                                <div class="col-25 moveLeft">
                                    <label class="myLabel" for="shippingMethod">Shipping Method</label>
                                </div>
                                <div class="col-75 moveRight">
									<?php ?>
                                    <select name="shippingMethod" id="shippingMethod"
                                            class="input fixedWidth shippingMethod">
										<?php echo $WC_Gateway_EBiz_Rec->getActiveShippingMethods($recurring->rec_shipping_method); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="ebiz-row clearAll widthBlockEdit tgl-radio-tabs" id="payop">
                                <div class="col-25 moveLeft">
                                    <label class="myLabel" for="ebizs_option">Payment Type</label>
                                </div>
                                <div class="col-75 moveRight flLeft">
                                    <div class="ebiz-row payment-div" id="my-select-content">
										<?php if ($WC_Gateway_EBizCharge->is_cc_enabled()) { ?>
                                            <div class="cc-radio">
                                                <input type="radio" name="ebzc_option_type" id="ebizs_option" value="cc"
                                                       class="ebizs_option tgl-radio-tab-child">
                                                <label for="ebizs_option" class="label pmethod cc-method">
                                                    <span>Pay by Card</span>&nbsp;
                                                    <img class="card-img"
                                                         src="<?php echo PLUGIN_DIR . 'assets/images/card.png'; ?>"
                                                         width="20"
                                                         height="15"/>
                                                </label>
                                            </div>
										<?php } ?>
										<?php if ($WC_Gateway_EBizCharge->is_ach_enabled()) { ?>
                                            <div class="bank-radio">
                                                <input type="radio" name="ebzc_option_type" id="ebizs_option_ach"
                                                       value="check" class="ebizs_option ach-radio tgl-radio-tab-child">
                                                <label for="ebizs_option_ach" class="label pmethod check-method">
                                                    <span>Pay by Bank </span>
                                                    <img class="bank-img"
                                                         src="<?php echo PLUGIN_DIR . 'assets/images/bank.png'; ?>"
                                                         width="20"
                                                         height="15"/>
                                                </label>
                                            </div>
										<?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="ebiz-row clearAll widthBlockEdit" id="add-new-card-saved">
                                <div class="col-25 moveLeft">
                                    <label class="myLabel" for="selectdivPayment">Payment Method</label>
                                </div>

								<?php
								$paymentMethods = $WC_Gateway_EBizCharge->getCustomerPaymentMethodsByGetCustomer($recurring->customer_id);
								if ($WC_Gateway_EBizCharge->is_cc_enabled()) { ?>
                                    <div class="col-75 flLeft cc-div" id="cc-div">
                                        <select name="selectdivPayment" id="selectdivPayment"
                                                class="selectdivPayment selectdivPaymentCc">
											<?php echo $WC_Gateway_EBiz_Rec->getSavedCC($paymentMethods, 'cc', $recurring->rec_pmethod_id); ?>
                                        </select>
                                    </div>
								<?php } ?>
								<?php if ($WC_Gateway_EBizCharge->is_ach_enabled()) { ?>
                                    <div class="col-75 flLeft bank-div" id="bank-div">
                                        <select name="selectdivPayment" id="selectdivPayment"
                                                class="selectdivPayment selectdivPaymentBank">
											<?php echo $WC_Gateway_EBiz_Rec->getSavedBanks($paymentMethods, 'check', $recurring->rec_pmethod_id); ?>
                                        </select>
                                    </div>
								<?php } ?>
                            </div>
                        </div>
                        <div class="left">
                            <div class="ebiz-row leftOnly ">
                                <h3 class="">Billing Address</h3>
                                <address>
                                    <span class="fontSize"><?php echo $recurring_shipping['first_name']; ?><?php echo $recurring_shipping['last_name']; ?></span><br>
                                    <span class="fontSize"><?php echo $recurring_billing['address_1']; ?></span> <br>
                                    <span class="fontSize"><?php echo $recurring_billing['address_2']; ?></span> <br>
                                    <span class="fontSize"><?php echo $recurring_billing['postcode']; ?></span> <br>
                                    <span class="fontSize"><?php echo $recurring_billing['country']; ?></span>
                                </address>
                            </div>
                            <div class="ebiz-row rightOnly ">
                                <h3 class="">Shipping Address</h3>
                                <address>
                                    <span class="fontSize"><?php echo $recurring_shipping['first_name']; ?><?php echo $recurring_shipping['last_name']; ?></span><br>
                                    <span class="fontSize"><?php echo $recurring_shipping['address_1']; ?></span> <br>
                                    <span class="fontSize"><?php echo $recurring_shipping['address_2']; ?></span> <br>
                                    <span class="fontSize"><?php echo $recurring_shipping['postcode']; ?></span> <br>
                                    <span class="fontSize"><?php echo $recurring_shipping['country']; ?></span>
                                </address>
                            </div>
                        </div>
                    </div>
                </form>
                <form class="" id="unsub" action="<?php echo $recurrings_update_url; ?>" method="post">
                    <input type="hidden" name="unsub" value="yes">
                    <input type="hidden" name="ScheduledPaymentInternalId"
                           value="<?php echo $recurring->rec_scheduled_payment_internal_id; ?>">
                </form>
                <form class="" id="delsub" action="<?php echo $recurrings_update_url; ?>" method="post">
                    <input type="hidden" name="delete" value="yes">
                    <input type="hidden" name="ScheduledPaymentInternalId"
                           value="<?php echo $recurring->rec_scheduled_payment_internal_id; ?>">
                </form>
                <form class="" id="resub" action="<?php echo $recurrings_update_url; ?>" method="post">
                    <input type="hidden" name="resub" value="yes">
                    <input type="hidden" name="ScheduledPaymentInternalId"
                           value="<?php echo $recurring->rec_scheduled_payment_internal_id; ?>">
                </form>

                <div class="ebiz-row clearAll cartContainer">
                    <div class="actions-toolbar">
                        <div class="primary">
							<?php if ($WC_Gateway_EBizCharge->is_ebizcharge_enabled()) {

                                if($WC_Gateway_EBizCharge->is_recurring_enabled()) {
                                ?>
                                <button type="button" id="" class="button save_btn" title="Update Subscription">
                                    <span>Update Subscription</span>
                                </button>
								<?php if (in_array($recurring->rec_status, [1,3]) ) { ?>
                                    <button type="button" id="resub_btn" class="button resub_btn" title="Resubscribe">
                                        <span>Resubscribe</span>
                                    </button>
                                <?php }
                                }
								if ($recurring->rec_status == 0) { ?>
                                    <button type="button" id="unsub_btn" class="button unsub_btn" title="Unsubscribe">
                                        <span>Unsubscribe</span>
                                    </button>
								<?php }
                            }?>
                            <button type="button" class="button" id="new-cancel-btn"
                                    onclick="location.href='<?php echo $recurrings_list_url; ?>';"><span>Cancel</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	<?php } else { ?>
        <div class="container-subs">
            <div id="subscription-edit" class="subscription-edit">
                You are not allowed to edit this subscription.
            </div>
            <button type="button" class="button" id="new-cancel-btn"
                    onclick="location.href='<?php echo esc_html($recurrings_list_url); ?>';">
                <span>Go back</span>
            </button>
        </div>
		<?php
	}
}
