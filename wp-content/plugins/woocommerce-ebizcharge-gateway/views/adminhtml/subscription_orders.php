<?php
$orderObj = new Subscription_Orders();
$ebiz = new WC_ebizcharge();
?>
<div class="ajax-loader" style="visibility: hidden">
	<img src="<?php echo plugin_dir_url(dirname(__FILE__)) . '../assets/images/ajax-loader.gif'; ?>" width="50"
		 height="50" class="img-responsive"/>
</div>
<div class="wrap">
	<h2>Subscription Orders</h2>
	<?php
	 //notice // error // success
	if (!empty($_REQUEST['error'])) {
		$message = base64_decode($_REQUEST['error']);
		echo $ebiz->getAdminMessage('error', $message);
	}
	
	if (!empty($_REQUEST['success'])) {
		$message = base64_decode($_REQUEST['success']);
		echo $ebiz->getAdminMessage('success', $message);
	}
	
	if (!empty($_REQUEST['notice'])) {
		$message = base64_decode($_REQUEST['notice']);
		echo $ebiz->getAdminMessage('notice', $message);
	}
	?>	
	<div id="success" class="notice notice-success is-dismissible" style="display: none"></div>
	<div id="error" class="notice notice-error is-dismissible" style="display: none"></div>
	<div id="icon-users" class="icon32"></div>
	<form method="get" id="">
		<input type="hidden" name="page" value="subscription-orders">
		<?php $orderObj->prepare_items(); ?>
		<?php $orderObj->search_box('Create Order', 'search_id'); ?>
	</form>
	<form method="post">
		<input type="hidden" name="page" value="subscription-orders">
		<?php $orderObj->display(); ?>
	</form>
</div>
<style>
.wrap {
  overflow-x: scroll;
}
#wpfooter {
  position: relative;
}
.subscriptions_page_subscription-orders {
	margin-right: 20px;
}
.wrap h2 {
  margin: 0 0 15px 0 !important;
}
</style>