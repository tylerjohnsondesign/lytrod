<?php
/**
 * Subscription information template
 *
 * @package WooCommerce_Subscriptions/Templates/Emails
 * @version 7.2.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( empty( $subscriptions ) ) {
	return;
}

$has_automatic_renewal = false;
$is_parent_order       = wcs_order_contains_subscription( $order, 'parent' );
$subscription = reset( $subscriptions );
?>
<div style="margin-bottom: 40px;">
<h2><?php esc_html_e( 'Subscription information', 'woocommerce-subscriptions' ); ?></h2>
<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 0.5em;" border="1">
	<thead>
		<tr>
			<th class="td" scope="col" style="text-align:left;"><?php echo esc_html_x( 'ID', 'subscription ID table heading', 'woocommerce-subscriptions' ); ?></th>
			
			<th style="text-align:left;">Product</th>
            <th style="text-align:left;">Product ID</th>
			<th class="td" scope="col" style="text-align:left;"><?php echo esc_html_x( 'Start date', 'table heading', 'woocommerce-subscriptions' ); ?></th>
			<th class="td" scope="col" style="text-align:left;"><?php echo esc_html_x( 'End date', 'table heading', 'woocommerce-subscriptions' ); ?></th>
			<th class="td" scope="col" style="text-align:left;"><?php echo esc_html_x( 'Recurring total', 'table heading', 'woocommerce-subscriptions' ); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ( $subscriptions as $subscription ) : ?>
		<?php $has_automatic_renewal = $has_automatic_renewal || ! $subscription->is_manual(); ?>
		<tr>
			<?php // Translators: placeholder is the subscription number. ?>
			<td class="td" scope="row" style="text-align:left;"><a href="<?php echo esc_url( ( $is_admin_email ) ? wcs_get_edit_post_link( $subscription->get_id() ) : $subscription->get_view_order_url() ); ?>"><?php echo sprintf( esc_html_x( '#%s', 'subscription number in email table. (eg: #106)', 'woocommerce-subscriptions' ), esc_html( $subscription->get_order_number() ) ); ?></a></td>
			<!-- ✅ PRODUCT NAME -->
               <td style="text-align:left;">
					<?php
					foreach ( $subscription->get_items() as $item ) {
						echo esc_html( wp_strip_all_tags( $item->get_name() ) );
					}
					?>
				</td>
			 <!-- ✅ PRODUCT ID (from your meta keys) -->
                <td style="text-align:left;">
                    <?php
                    $product_id = '';

                    $keys = [
                        'intellicut_serial_number',
                        'intellicut_global_product_id',
                        'vrcutimposeinstallation_product_id',
                        'intellicutaneinstallation_product_id',
                        'installation_product_id',
                        'bizcard_product_id'
                    ];

                    foreach ( $keys as $key ) {
                        $value = get_post_meta( $subscription->get_id(), $key, true );
                        if ( ! empty( $value ) ) {
                            $product_id = $value;
                            break;
                        }
                    }

                    echo ! empty( $product_id ) ? esc_html( $product_id ) : '-';
                    ?>
            </td>
			<td class="td" scope="row" style="text-align:left;"><?php echo esc_html( date_i18n( wc_date_format(), $subscription->get_time( 'start_date', 'site' ) ) ); ?></td>
			<td class="td" scope="row" style="text-align:left;"><?php echo esc_html( ( 0 < $subscription->get_time( 'end' ) ) ? date_i18n( wc_date_format(), $subscription->get_time( 'end', 'site' ) ) : _x( 'When cancelled', 'Used as end date for an indefinite subscription', 'woocommerce-subscriptions' ) ); ?></td>
			<td class="td" scope="row" style="text-align:left;">
				<?php echo wp_kses_post( $subscription->get_formatted_order_total() ); ?>
				<?php if ( $is_parent_order && $subscription->get_time( 'next_payment' ) > 0 ) : ?>
					<br>
					<?php // Translators: placeholder is the next payment date. ?>
					<small><?php printf( esc_html__( 'Next payment: %s', 'woocommerce-subscriptions' ), esc_html( date_i18n( wc_date_format(), $subscription->get_time( 'next_payment', 'site' ) ) ) ); ?></small>
				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
</tbody>
</table>
<?php
if ( $has_automatic_renewal && ! $is_admin_email && $subscription->get_time( 'next_payment' ) > 0 && ! $skip_my_account_link ) {
	if ( count( $subscriptions ) === 1 ) {
		$subscription   = reset( $subscriptions );
		$my_account_url = $subscription->get_view_order_url();
	} else {
		$my_account_url = wc_get_endpoint_url( 'subscriptions', '', wc_get_page_permalink( 'myaccount' ) );
	}

	printf(
		'<small>%s</small>',
		wp_kses_post(
			sprintf(
				// Translators: Placeholders are opening and closing My Account link tags.
				_n(
					'This subscription is set to renew automatically using your payment method on file. You can manage or cancel this subscription from your %1$smy account page%2$s.',
					'These subscriptions are set to renew automatically using your payment method on file. You can manage or cancel your subscriptions from your %1$smy account page%2$s.',
					count( $subscriptions ),
					'woocommerce-subscriptions'
				),
				'<a href="' . $my_account_url . '">',
				'</a>'
			)
		)
	);
}
?>
</div>
