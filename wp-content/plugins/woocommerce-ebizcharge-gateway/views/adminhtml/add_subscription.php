<?php
$requestCC = 1;
$ebiz = new WC_ebizcharge();
$WC_ebizcharge = $ebiz->_initTransaction(true);
$products = $ebiz->get_instock_items();
$tran = new WC_Gateway_EBizCharge();
$apiSettings = $tran->getMerchantTransactionData();
$verifyCardBeforeSaving = $apiSettings->VerifyCreditCardBeforeSaving ?? false;
?>
<div class="ajax-loader" style="visibility: hidden">
    <img src="<?php echo plugin_dir_url(dirname(__FILE__)) . '../assets/images/ajax-loader.gif'; ?>" width="50"
         height="50" class="img-responsive"/>
</div>
<div class="wrap" id="PageMain">
    <h2>Add Subscription</h2>
    <form method="post" id="form-validate" class="sub-add-form"
          action="<?php echo esc_html(admin_url('admin.php?page=add-subscription')); ?>">
        <input type="hidden" name="action" id="action" value="creat_applicant">
        <input type="hidden" name="requestCC" id="requestCC" value="1">
        <input type="hidden" name="payment_method_name" id="payment_method_name"/>
        <input type="hidden" id="verify_card_before_saving" value="<?php echo $verifyCardBeforeSaving; ?>"/>
        <div class="row clearAll widthBlock">
            <div id="Frecheck" style="color: red; font-size : medium; text-align: left; "></div>
        </div>
        <div class="row clearAll widthBlock">
            <div class="col-25 moveLeft">
                <span class="myLabel">Select Product</span><span class="required_field"> *</span>
            </div>
            <div class="col-50 moveRight">
                <select id="selectdivProduct" name="selectdivProduct" class="input fixedWidth search-custom-class">
                    <option value=''>Please Select Product</option>
                    <?php
                    foreach ($products as $post):
                        ?>
                        <option value="<?php echo $post->get_id(); ?>">
                            <?php echo $post->get_name(); ?>
                        </option>
                    <?php
                    endforeach; ?>
                </select>
            </div>

        </div>
        <div class="row clearAll widthBlock">
            <div class="col-25 moveLeft">
                <label class="myLabel">Enter Quantity</label><span class="required_field"> *</span>
            </div>
            <div class="col-50 moveRight">
                <input type="text" class="qtyWidth input fixedWidth" name="qty" id="qty" value="" pattern="\d*"
                       minlength="1" maxlength="3">
            </div>
        </div>

        <div class="row clearAll widthBlock">
            <div class="col-25 moveLeft">
                <label class="myLabel">Coupon Code</label>
            </div>
            <div class="col-50 moveRight">
                <input type="text" class="couponWidth" name="coupon" id="coupon" value="">
            </div>
        </div>

        <div class="row clearAll widthBlock">
            <div class="col-25 moveLeft">
                <label class="myLabel">Select Customer</label><span class="required_field"> *</span>
            </div>
            <div class="col-75 moveRight" id="blockSelectdivCustomer">
                <select name="customer_id" id="selectdivCustomer" class="input fixedWidth search-custom-class">
                    <option value=''>Please Select Customer</option>
                    <?php
                    $args = [
                        'blog_id' => 1,
                        'role__not_in' => ['administrator'],
                        'orderby' => 'nicename',
                        'order' => 'ASC',
                        'fields' => 'all',
                    ];

                    $users = get_users($args);
                    foreach ($users as $user) {
                        ?>
                        <option value="<?php echo $user->ID; ?>">
                            <?php echo $user->user_nicename." (".$user->user_email.")"; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="row clearAll widthBlock">
            <div class="col-25 moveLeft">
                <label class="myLabel">Billing Address</label><span class="required_field"> *</span>
            </div>
            <div class="col-75 moveRight" id="blockBillId">
                <select name="addressBill" id="addressBill" class="input fixedWidth search-custom-class">
                    <option value=''>Please Select Billing Address</option>
                </select>
            </div>
        </div>
        <div class="row clearAll widthBlock">
            <div class="col-25 moveLeft">
                <label class="myLabel">Shipping Address</label><span class="required_field"> *</span>
            </div>
            <div class="col-75 moveRight">
                <select name="addressShip" id="addressShip" class="input fixedWidth search-custom-class">
                    <option value=''>Please Select Shipping Address</option>
                </select>
            </div>
        </div>
        <div class="row clearAll widthBlock">
            <div class="col-25 moveLeft">
                <label class="myLabel">Frequency</label><span class="required_field"> *</span>
            </div>
            <div class="col-75 moveRight">
                <select name="schedule" id="freqId" class="input fixedWidth">
                    <?php $WC_Gateway_EBiz_Rec->getFrequencyOptions(); ?>
                </select>
            </div>
        </div>
        <fieldset id="dates">
            <div class="row clearAll widthBlock">
                <div class="col-25 moveLeft">
                    <label class="myLabel">Subscription Dates</label>
                </div>
                <div class="col-75 moveRight widthFix400">
                    <div class="rangeDate">
                        <label class="label" for="start_date"><span>Start Date</span></label><span class="required_field"> *</span>
                        <input type="text" class="dateclass widthFix120" name="start_date" id="start_date"
                               autocomplete="off" placeholder="YYYY-MM-DD"/>
                        <div id="start_date_required" style="color:red; float: right"></div>
                    </div>

                    <div class="rangeDate">
                        <label class="label" for="expire_date"><span>End Date</span></label><span class="required_field"></span>
                        <input type="text" class="dateclass widthFix120" name="expire_date" id="expire_date"
                               autocomplete="off" placeholder="YYYY-MM-DD"/>

                        <div id="Datecheck" style="color: red; margin-left: 57px"></div>
                    </div>
                </div>
            </div>
            <div class="row clearAll widthBlock">
                <div class="col-25 moveLeft">
                    <label class="myLabel">
                        <?php echo __('Recur Indefinitely'); ?>
                    </label>
                </div>
                <div class="col-75 moveRight">
                    <input type="checkbox" id="rec_indefinitely" name="rec_indefinitely" value="1">
                </div>
            </div>
        </fieldset>
        <div class="row clearAll widthBlock">
            <div class="col-25 moveLeft">
                <label class="myLabel">Shipping Method</label><span class="required_field"> *</span>
            </div>
            <div class="col-75 moveRight">
                <select name="shipping_method" id="shippingMethod" class="input fixedWidth search-custom-class">
                    <option value=''>Please Select Shipping Method</option>
                    <?php $ebiz->getShippingMethods(); ?>
                </select>
            </div>
        </div>

        <div class="row clearAll widthBlock">
            <div class="col-25 moveLeft">
                <label class="myLabel">Shipping Amount</label><span class="required_field"> *</span>
            </div>
            <div class="col-75 moveRight">
                <input type="text" class="qtyWidth numberonly" id="shipping_amount" name="shipping_amount" pattern="\d*">
            </div>
        </div>

        <div class="row clearAll widthBlock tgl-radio-tabs" id="payop">
            <div class="col-25 moveLeft">
                <label class="myLabel">Payment Type</label>
            </div>
            <div class="col-75 moveRight">
                <input type="hidden" name="ebiz_option" id="ebiz_option" value="credit_card"/>
                <div class="row" id="my-select-content" style="padding-bottom: 10px; border-bottom: 1px solid silver">
					<?php if ($tran->is_cc_enabled()) { ?>
                    <input type="radio" name="payment[ebzc_option_type]" id="ebizs_option" value="credit_card"
                           class="ebizs_option tgl-radio-tab-child"/>
                    <label for="ebizs_option" class="label">
                        <span>Pay by Card</span>
                        <img class="card-img" src="<?php echo PLUGIN_DIR . 'assets/images/card.png'; ?>" width="20"
                             height="15" />
                    </label>
					<?php } ?>
                    <?php if ($tran->is_ach_enabled()) { ?>
                        <input style="margin-left: 15px" type="radio" name="payment[ebzc_option_type]" id="ebizs_option_ach"
                               value="ACH" class="ebizs_option tgl-radio-tab-child"/>
                        <label for="ebizs_option_ach" class="label">
                            <span>Pay by Bank </span>
                            <img class="bank-img" src="<?php echo PLUGIN_DIR . 'assets/images/bank.png'; ?>" width="20"
                                 height="15" />
                        </label>
						<?php if (!$tran->is_cc_enabled()) { ?>
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

		<div class="row clearAll widthBlock">
            <div class="col-25 moveLeft">
                <label class="myLabel">Payment Options</label>
            </div>
            <div class="col-75 moveRight">
                <input type="hidden" name="payment[ebiz_option_payment]" id="ebiz_option_payment" value="new"/>

                <div class="row" style="padding-bottom: 10px; border-bottom: 1px solid silver">
                    <input type="radio" name="payment[ebzc_account_type]" id="new" value="new" class="ebiz_option_card"
                           checked/>
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
        <div class="widthBlock" id="add-new">

            <div style="color: red; display: none; text-align: left;" id="payment_msg">All fields are required.</div>

            <div style="color: red; display: none; text-align: left" id="payment_msgCvv">
                Please enter CVV between 3 and 4 digits.
            </div>
            <br>
            <div class="clearAll customFullWidth">
                <div class="col-25 moveLeft">
                    <label class="myLabel">
                        <?php echo __('Name on Card'); ?><span class="required_field"> *</span>
                    </label>
                </div>
                <div class="col-75 moveRight">
                    <input type="text" class="input-text required" name="payment[cc_owner]" id="payment_cc_owner_new" value=""/>
                </div>
            </div>

            <div class="clearAll customFullWidth">
                <div class="col-25 moveLeft">
                    <label class="myLabel">
                        <span><?php echo __('Credit Card Number'); ?></span><span class="required_field"> *</span>
                    </label>
                </div>
                <div class="col-75 moveRight">
                    <input type="text" id="payment_cc_number_new" name="payment[cc_number]"
                           class="input-text control-text required"
                           value=""  minlength="15" maxlength="16"/>
                </div>
            </div>
            <div class="clearAll customFullWidth">
                <div class="col-25 moveLeft">
                    <label class="myLabel">
                        <span><?php echo __('Credit Card Type'); ?></span><span class="required_field"> *</span>
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
                        <span><?php echo __('Expiration Date'); ?></span><span class="required_field"> *</span>
                    </label>
                </div>
                <div class="col-75 moveRight control">
                    <select id="payment_exp_new" name="payment[cc_exp_month]"  class="month select control-select" style="float: left;margin-left: 0;">
						<option value="">Month</option>
                        <?php $ebiz->getExpiryMonths(); ?>
                    </select>
					<label id="payment_exp_new-error" class="error" for="payment_exp_new" style="display: none;"></label>
                    <select id="payment_exp_yr_new" name="payment[cc_exp_year]" class="year select control-select" style="float: left;">
                        <option value="">Year</option>
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
                               style="width: 50px;" pattern="\d*" minlength="3" maxlength="4" />
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
                           class="input-text required" value=""/>
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
                           class="input-text required" value=""/>
                </div>
            </div>
        </div>

        <div class="row clearAll widthBlock" id="add-new-card-saved">
            <div class="col-25 moveLeft">
                <label class="myLabel">Payment Method</label>
                <span class="required_field"> *</span>
            </div>
            <div class="col-75 moveRight">
                <select name="method_id" id="selectdivPayment" style="width: 600px; float: left; margin-left: 0;" class="required">
                    <!-- <option value=''>Please Select Payment Method</option> -->
                </select>
            </div>
        </div>

        <div class="row clearAll widthBlock" id="add-new-card-update">

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

		<?php if ($tran->is_ach_enabled()) { ?>
            <div class="add-new widthBlock" id="add-new-ach" style="display: none;">
                <span style="color: red; display: none;" id="payment_msg_ach">All fields are required.</span>
                <span style="color: red; display: none;" id="payment_msgcNumAch">The account number should be between 5 and 17 digits.</span>
                <span style="color: red; display: none;" id="payment_msgcNumRoutAch">Please enter valid 9 digit routing number.</span>

                <div class="clearAll customFullWidth">
                    <div class="col-25 moveLeft">
                        <label class="myLabel">
                            <span><?php echo __('Account Holder'); ?></span><span class="required_field"> *</span>
                        </label>
                    </div>
                    <div class="col-75 moveRight control">
                        <input type="text" id="payment_cc_owner_new_ach" name="payment[cc_owner_ach]"
                               title="<?php echo __('Account Holder'); ?>"
                               class="input-text required" value=""/>
                    </div>
                </div>

                <div class="clearAll customFullWidth">
                    <div class="col-25 moveLeft">
                        <label class="myLabel">
                            <span><?php echo __('Account Type'); ?></span><span class="required_field"> *</span>
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
                            <span><?php echo __('Account Number'); ?></span><span class="required_field"> *</span>
                        </label>
                    </div>
                    <div class="col-75 moveRight">
                        <input type="text" id="payment_cc_number_new_ach" name="payment[cc_number_ach]"
                               title="<?php echo __('Account Number'); ?>" minlength="5" class="input-text required numberonly"  value=""/>
                    </div>
                </div>

                <div class="clearAll customFullWidth">
                    <div class="col-25 moveLeft">
                        <label class="myLabel">
                            <span><?php echo __('Routing Number'); ?></span><span class="required_field"> *</span>
                        </label>
                    </div>
                    <div class="col-75 moveRight">
                        <input type="text" id="payment_cc_cid_new_ach" name="payment[cc_routing_ach]" minlength="9" maxlength="9"
                               title="<?php echo __('Routing Number'); ?>" class="required numberonly"/>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="row clearAll widthBlock">
            <div class="actions-toolbar">
                <div class="primary">
                    <button type="button" id="" class="button button-primary save_btn" title="<?php echo __('Save Subscription'); ?>">
                        <span><?php echo __('Save Subscription'); ?></span>
                    </button>

                    <button type="button" class="button" id="new-cancel-btn"
                            onclick="location.href='<?php echo esc_html(admin_url('admin.php?page=view-subscription')); ?>';">
                        <span>Cancel</span>
                    </button>

                </div>
            </div>
        </div>
    </form>
</div>