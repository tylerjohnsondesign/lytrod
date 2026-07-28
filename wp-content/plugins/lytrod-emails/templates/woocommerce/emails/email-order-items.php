<?php
/**
 * Order item rows inside the order summary card.
 *
 * Rendered by wc_get_email_order_items() (woocommerce/includes/wc-template-functions.php:3788),
 * which supplies $order, $items, $show_sku, $show_image, $image_size, $plain_text,
 * $sent_to_admin, $show_purchase_note and $show_download_links.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

$text_align   = is_rtl() ? 'right' : 'left';
$number_align = is_rtl() ? 'left' : 'right';

$show_image         = $show_image ?? true;
$show_sku           = $show_sku ?? false;
$show_purchase_note = $show_purchase_note ?? false;
$plain_text         = $plain_text ?? false;
$image_size         = $image_size ?? array( 48, 48 );

foreach ( $items as $item_id => $item ) :
    /** This filter is documented in woocommerce/templates/emails/email-order-items.php */
    if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
        continue;
    }

    $product       = $item->get_product();
    $sku           = '';
    $purchase_note = '';
    $image         = '';

    if ( is_object( $product ) ) {
        $sku           = $product->get_sku();
        $purchase_note = $product->get_purchase_note();
        $image         = $product->get_image( $image_size );
    }

    /** This filter is documented in woocommerce/templates/emails/email-order-items.php */
    $item_class = apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order );
    ?>
    <tr class="<?php echo esc_attr( $item_class ); ?>">
        <td class="font-family" style="text-align:<?php echo esc_attr( $text_align ); ?>;vertical-align:top;word-wrap:break-word;">
            <table class="order-item-data" cellpadding="0" cellspacing="0" border="0" role="presentation">
                <tr>
                    <?php if ( $show_image && $image ) : ?>
                        <td style="vertical-align:top;padding-<?php echo is_rtl() ? 'left' : 'right'; ?>:12px !important;">
                            <?php
                            /** This filter is documented in woocommerce/templates/emails/email-order-items.php */
                            echo wp_kses_post( apply_filters( 'woocommerce_order_item_thumbnail', $image, $item ) );
                            ?>
                        </td>
                    <?php endif; ?>
                    <td style="vertical-align:top;">
                        <?php
                        /** This filter is documented in woocommerce/templates/emails/email-order-items.php */
                        $order_item_name = apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false );

                        echo wp_kses_post( '<strong>' . $order_item_name . '</strong>' );

                        if ( $show_sku && $sku ) {
                            echo ' <span class="lytrod-meta">' . esc_html( '#' . $sku ) . '</span>';
                        }

                        /** This action is documented in woocommerce/templates/emails/email-order-items.php */
                        do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text );

                        $item_meta = wc_display_item_meta(
                            $item,
                            array(
                                'before'       => '',
                                'after'        => '',
                                'separator'    => '<br>',
                                'echo'         => false,
                                'label_before' => '<span class="wc-item-meta-label">',
                                'label_after'  => ':</span> ',
                            )
                        );

                        if ( $item_meta ) {
                            echo '<div class="email-order-item-meta">';
                            echo wp_kses(
                                $item_meta,
                                array(
                                    'br'     => array(),
                                    'span'   => array( 'class' => true ),
                                    'strong' => array( 'class' => true ),
                                    'a'      => array(
                                        'href'   => true,
                                        'target' => true,
                                        'rel'    => true,
                                        'title'  => true,
                                    ),
                                )
                            );
                            echo '</div>';
                        }

                        /** This action is documented in woocommerce/templates/emails/email-order-items.php */
                        do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, $plain_text );
                        ?>
                    </td>
                </tr>
            </table>
        </td>
        <td class="font-family lytrod-numeric" style="text-align:<?php echo esc_attr( $number_align ); ?>;vertical-align:top;">
            <?php
            $qty          = $item->get_quantity();
            $refunded_qty = $order->get_qty_refunded_for_item( $item_id );

            if ( $refunded_qty ) {
                $qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * -1 ) ) . '</ins>';
            } else {
                $qty_display = esc_html( $qty );
            }

            /** This filter is documented in woocommerce/templates/emails/email-order-items.php */
            echo wp_kses_post( apply_filters( 'woocommerce_email_order_item_quantity', $qty_display, $item ) );
            ?>
        </td>
        <td class="font-family lytrod-numeric" style="text-align:<?php echo esc_attr( $number_align ); ?>;vertical-align:top;">
            <?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
        </td>
    </tr>
    <?php if ( $show_purchase_note && $purchase_note ) : ?>
        <tr>
            <td colspan="3" class="font-family" style="text-align:<?php echo esc_attr( $text_align ); ?>;vertical-align:top;">
                <?php echo wp_kses_post( wpautop( do_shortcode( $purchase_note ) ) ); ?>
            </td>
        </tr>
    <?php endif; ?>
<?php endforeach; ?>
