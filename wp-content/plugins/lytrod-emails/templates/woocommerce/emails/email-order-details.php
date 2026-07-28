<?php
/**
 * Order summary card.
 *
 * Shared by every WooCommerce order email, including the two templates ceded to
 * subscriptions-upgrader — so this file is the single highest-leverage override
 * after the chrome.
 *
 * WooCommerce's stock version hardcodes `<hr style="border-top:1px solid #1E1E1E">`
 * dividers as inline attributes, which Emogrifier cannot recolour. This plugin
 * turns them off through `woocommerce_email_body_display_section_divider` and draws
 * its own hairlines from the brand palette instead.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

$text_align   = is_rtl() ? 'right' : 'left';
$number_align = is_rtl() ? 'left' : 'right';

$sent_to_admin = $sent_to_admin ?? false;
$plain_text    = $plain_text ?? false;
$email         = $email ?? Lytrod_Emails_Context::get();

/** This filter is documented in woocommerce/templates/emails/email-order-details.php */
do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );

$order_link = $sent_to_admin ? $order->get_edit_order_url() : $order->get_view_order_url();

$meta = sprintf(
    /* translators: 1: order number, 2: formatted order date. */
    __( 'Order %1$s placed %2$s', 'lytrod-emails' ),
    $order->get_order_number(),
    $order->get_date_created() ? wc_format_datetime( $order->get_date_created() ) : ''
);
?>

<h2 class="lytrod-section-title email-order-detail-heading">
    <?php esc_html_e( 'Order summary', 'lytrod-emails' ); ?>
    <span class="lytrod-section-meta">
        <?php if ( $order_link ) : ?>
            <a class="link" href="<?php echo esc_url( $order_link ); ?>" style="text-decoration:none;"><?php echo esc_html( $meta ); ?></a>
        <?php else : ?>
            <?php echo esc_html( $meta ); ?>
        <?php endif; ?>
    </span>
</h2>

<table class="lytrod-table font-family email-order-details" cellspacing="0" cellpadding="0" width="100%" border="0" role="presentation" style="width:100%;margin-bottom:20px;">
    <thead>
        <tr>
            <th scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Product', 'lytrod-emails' ); ?></th>
            <th class="lytrod-numeric" scope="col" style="text-align:<?php echo esc_attr( $number_align ); ?>;"><?php esc_html_e( 'Qty', 'lytrod-emails' ); ?></th>
            <th class="lytrod-numeric" scope="col" style="text-align:<?php echo esc_attr( $number_align ); ?>;"><?php esc_html_e( 'Amount', 'lytrod-emails' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        echo wc_get_email_order_items( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered by a template.
            $order,
            array(
                'show_sku'      => $sent_to_admin,
                'show_image'    => true,
                'image_size'    => array( 48, 48 ),
                'plain_text'    => $plain_text,
                'sent_to_admin' => $sent_to_admin,
            )
        );
        ?>
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
        '<p class="lytrod-panel-text" style="margin-bottom:0;">' . wp_kses( nl2br( wc_wptexturize_order_note( $order->get_customer_note() ) ), array( 'br' => array() ) ) . '</p>',
        __( 'Customer note', 'lytrod-emails' )
    );
    ?>
<?php endif; ?>

<?php
/** This filter is documented in woocommerce/templates/emails/email-order-details.php */
do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email );
