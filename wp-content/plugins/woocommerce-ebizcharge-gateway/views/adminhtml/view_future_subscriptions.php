<?php
$subscriptionObj = new Future_Subscription();
?>
<div class="ajax-loader" style="visibility: hidden">
	<img src="<?php echo plugin_dir_url(dirname(__FILE__)) . '../assets/images/ajax-loader.gif'; ?>" width="50"
		 height="50" class="img-responsive"/>
</div>
<div class="wrap">
	<h2>Upcoming Subscription Orders</h2>
        <div id="icon-users" class="icon32"></div>
        <form method="get">
        <?php $subscriptionObj->prepare_items(); ?>
        <?php $subscriptionObj->search_box( 'Search', 'search_id' );?>
        <input type="hidden" name="page" value="future-subscription">
        </form>
        <form method="post">
        <?php $subscriptionObj->display(); ?>
        <input type="hidden" name="page" value="future-subscription">
        </form>
</div>
<style>
.wrap {
  overflow-x: scroll;
}
</style>