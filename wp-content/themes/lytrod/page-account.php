<?php 
    get_header();
?>

<!-- <header class="entry-header">	</header> -->
<div class="wrapperbanner">
<div class="containery ">
    <div class="rowy">
        <div class="coly-md-4">
            <h1>
                My Account
            </h1>
        </div>
        <div class="coly-md-8 nopadding">
            <div class="lytrodbannerlinks">
                <a href="<?php echo site_url();?>/account/register/">Register</a> |
                <a href="<?php echo site_url();?>/account/subscriptions/">Subscriptions</a>|
                <a href="<?php echo site_url();?>/account/downloads/">Downloads</a>
            </div>
        </div>
    </div>
</div>

</div>

<div class="containery accountcontent">
    <div class="rowy">
        <div class="coly-12">
                <?php 
            echo do_shortcode('[woocommerce_my_account]');
        ?>
        </div>
    </div>
</div>

<?php 
    get_footer();
?>
