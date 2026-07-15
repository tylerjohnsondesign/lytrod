<?php
$paymentObj = new Payment_History();
?>
<div class="ajax-loader" style="visibility: hidden">
	<img src="<?php echo plugin_dir_url(dirname(__FILE__)) . '../assets/images/ajax-loader.gif'; ?>" width="50"
		 height="50" class="img-responsive"/>
</div>
<div class="wrap">
	<h2>Subscription Payment History</h2>
    <div id="success" class="notice notice-success is-dismissible" style="display: none"></div>
    <div id="error" class="notice notice-error is-dismissible" style="display: none"></div>
    <form method="post">
        <input type="hidden" name="page" value="ttest_list_table">
        <div id="icon-users" class="icon32"></div>
        <?php $paymentObj->prepare_items(); ?>
        <?php $paymentObj->search_box( 'Search', 'search_id' );?>
        <?php $paymentObj->display(); ?>
    </form>
</div>
<style>
.wrap {
  overflow-x: scroll;
}
</style>