<?php
$subscriptionObj = new Subscription_List();
$ebiz = new WC_ebizcharge();
$WC_Gateway_EBizCharge = new WC_Gateway_EBizCharge();

if (($_GET['msg'] ?? '') == "success") {
	$message = 'Subscription successfully saved.';
	echo $ebiz->getSuccessMessage($message);
}
$add_subscription_url = esc_html(admin_url('admin.php?page=add-subscription'));
?>
<div class="ajax-loader" style="visibility: hidden">
    <img src="<?php echo plugin_dir_url(dirname(__FILE__)) . '../assets/images/ajax-loader.gif'; ?>" width="50"
         height="50" class="img-responsive"/>
</div>
<div class="wrap" style="overflow-x: scroll;">
    <h2>Subscriptions</h2>
    <div id="icon-users" class="icon32"></div>
	<?php if ($WC_Gateway_EBizCharge->is_ebizcharge_enabled() && $WC_Gateway_EBizCharge->is_recurring_enabled()) { ?>
        <a class="button button-primary" style="float: right; margin-top:10px" target="_blank"
           href="<?php echo $add_subscription_url ?>">+ Add Subscription
        </a>
	<?php } ?>
    <form method="get">
		<?php $subscriptionObj->prepare_items(); ?>
        <input type="hidden" name="page" value="view-subscription">
		<?php $subscriptionObj->search_box('Search', 'search_id'); ?>
    </form>

    <form method="post">
		<?php $subscriptionObj->display(); ?>
    </form>
</div>
