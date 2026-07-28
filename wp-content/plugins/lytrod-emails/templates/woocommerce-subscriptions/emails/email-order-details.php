<?php
/**
 * Order / subscription summary card for Subscriptions emails.
 *
 * This is the `$default_path` collision case: WooCommerce core and WooCommerce
 * Subscriptions both ship `emails/email-order-details.php` with different markup and
 * different template args. Lytrod_Emails_Templates branches on `$default_path` so
 * this file only ever serves the Subscriptions caller
 * (WC_Subscriptions_Email::order_details(), includes/core/class-wc-subscriptions-email.php:274).
 *
 * Receives $order (which may be a subscription), $order_type ('order' or
 * 'subscription') and $order_items_table_args in addition to the usual args.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

$text_align   = is_rtl() ? 'right' : 'left';
$number_align = is_rtl() ? 'left' : 'right';

$order_type             = $order_type ?? 'order';
$sent_to_admin          = $sent_to_admin ?? false;
$plain_text             = $plain_text ?? false;
$email                  = $email ?? Lytrod_Emails_Context::get();
$order_items_table_args = $order_items_table_args ?? array();
$is_subscription        = 'subscription' === $order_type;

/** This action is documented in woocommerce-subscriptions/templates/emails/email-order-details.php */
do_action( 'woocommerce_email_before_' . $order_type . '_table', $order, $sent_to_admin, $plain_text, $email );

$email_id = $email instanceof WC_Email && ! empty( $email->id ) ? $email->id : '';

if ( 'cancelled_subscription' !== $email_id ) :
    $link = $sent_to_admin ? wcs_get_edit_post_link( $order->get_id() ) : $order->get_view_order_url();

    $meta = sprintf(
        $is_subscription
            /* translators: 1: subscription number, 2: formatted date. */
            ? __( 'License %1$s started %2$s', 'lytrod-emails' )
            /* translators: 1: order number, 2: formatted date. */
            : __( 'Order %1$s placed %2$s', 'lytrod-emails' ),
        $order->get_order_number(),
        $order->get_date_created() ? wcs_format_datetime( $order->get_date_created() ) : ''
    );
    ?>
    <h2 class="lytrod-section-title email-order-detail-heading">
        <?php echo esc_html( $is_subscription ? __( 'License summary', 'lytrod-emails' ) : __( 'Order summary', 'lytrod-emails' ) ); ?>
        <span class="lytrod-section-meta">
            <?php if ( $link ) : ?>
                <a class="link" href="<?php echo esc_url( $link ); ?>" style="text-decoration:none;"><?php echo esc_html( $meta ); ?></a>
            <?php else : ?>
                <?php echo esc_html( $meta ); ?>
            <?php endif; ?>
        </span>
    </h2>
<?php endif; ?>

<table class="lytrod-table font-family email-order-details" cellspacing="0" cellpadding="0" width="100%" border="0" role="presentation" style="width:100%;margin-bottom:20px;">
    <thead>
        <tr>
            <th scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo esc_html_x( 'Product', 'order table heading', 'lytrod-emails' ); ?></th>
            <th class="lytrod-numeric" scope="col" style="text-align:<?php echo esc_attr( $number_align ); ?>;"><?php echo esc_html_x( 'Qty', 'order table heading', 'lytrod-emails' ); ?></th>
            <th class="lytrod-numeric" scope="col" style="text-align:<?php echo esc_attr( $number_align ); ?>;"><?php echo esc_html_x( 'Amount', 'order table heading', 'lytrod-emails' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php echo wp_kses_post( WC_Subscriptions_Email::email_order_items_table( $order, $order_items_table_args ) ); ?>
    </tbody>
    <?php
    $item_totals = $order->get_order_item_totals();

    if ( $item_totals ) :
        $total_count = count( $item_totals );
        $index       = 0;
        ?>
        <tfoot>
            <?php
            foreach ( $item_totals as $total ) :
                ++$index;
                $type       = $total['type'] ?? 'unknown';
                $last_class = ( $index === $total_count ) ? ' order-totals-last' : '';
                ?>
                <tr class="order-totals order-totals-<?php echo esc_attr( $type ); ?><?php echo esc_attr( $last_class ); ?>">
                    <th class="text-align-left" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;">
                        <?php
                        echo wp_kses_post( $total['label'] );

                        if ( isset( $total['meta'] ) ) {
                            echo ' ' . wp_kses_post( $total['meta'] );
                        }
                        ?>
                    </th>
                    <td class="lytrod-numeric" style="text-align:<?php echo esc_attr( $number_align ); ?>;">
                        <?php
                        /*
                         * The `payment_method` row's value is the stored gateway title, and
                         * the theme's `woocommerce_gateway_title` filter
                         * (themes/lytrod/functions.php:646) injects an <img> that WooCommerce
                         * then persists into `_payment_method_title`. 110 rows on this site
                         * contain markup and 30 point at the staging hostname, so
                         * wp_kses_post() — which permits <img> — would render a broken image
                         * and leak the staging URL. Strip tags on that row only; the money
                         * rows still need wp_kses_post() for <del>/<ins> on refunds.
                         */
                        echo 'payment_method' === $type
                            ? esc_html( trim( wp_strip_all_tags( $total['value'] ) ) )
                            : wp_kses_post( $total['value'] );
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tfoot>
    <?php endif; ?>
</table>

<?php if ( $order->get_customer_note() ) : ?>
    <?php
    echo lytrod_emails_panel( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
        '<p class="lytrod-panel-text" style="margin-bottom:0;">' . wp_kses( nl2br( wptexturize( $order->get_customer_note() ) ), array( 'br' => array() ) ) . '</p>',
        __( 'Customer note', 'lytrod-emails' )
    );
    ?>
<?php endif; ?>

<?php
/** This action is documented in woocommerce-subscriptions/templates/emails/email-order-details.php */
do_action( 'woocommerce_email_after_' . $order_type . '_table', $order, $sent_to_admin, $plain_text, $email );
