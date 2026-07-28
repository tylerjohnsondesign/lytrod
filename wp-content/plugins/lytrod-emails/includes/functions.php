<?php
/**
 * Public template helpers and the standalone rendering API.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render a call-to-action button.
 *
 * Emits a VML `roundrect` behind an MSO conditional comment so Outlook on Windows
 * (Word rendering engine, which ignores border-radius) still gets a rounded
 * button rather than a square block.
 *
 * @param string $label   Button label.
 * @param string $url     Destination.
 * @param string $variant `primary` or `secondary`.
 * @return string
 */
function lytrod_emails_button( string $label, string $url, string $variant = 'primary' ): string {
    if ( '' === $label || '' === $url ) {
        return '';
    }

    $radius    = (int) Lytrod_Emails_Brand::c( 'radius' );
    $primary   = Lytrod_Emails_Brand::c( 'primary' );
    $on_primary = Lytrod_Emails_Brand::c( 'on_primary' );
    $font      = Lytrod_Emails_Brand::c( 'font' );

    if ( 'secondary' === $variant ) {
        $bg     = Lytrod_Emails_Brand::c( 'surface' );
        $fg     = $primary;
        $border = $primary;
    } else {
        $bg     = $primary;
        $fg     = $on_primary;
        $border = $primary;
    }

    ob_start();
    ?>
    <table border="0" cellpadding="0" cellspacing="0" role="presentation" class="lytrod-cta" style="margin:0 0 8px;">
        <tr>
            <td align="center" style="border-radius:<?php echo (int) $radius; ?>px;background-color:<?php echo esc_attr( $bg ); ?>;">
                <!--[if mso]>
                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="<?php echo esc_url( $url ); ?>" style="height:46px;v-text-anchor:middle;width:260px;" arcsize="14%" strokecolor="<?php echo esc_attr( $border ); ?>" fillcolor="<?php echo esc_attr( $bg ); ?>">
                    <w:anchorlock/>
                    <center style="color:<?php echo esc_attr( $fg ); ?>;font-family:Helvetica,Arial,sans-serif;font-size:15px;font-weight:bold;"><?php echo esc_html( $label ); ?></center>
                </v:roundrect>
                <![endif]-->
                <!--[if !mso]><!-- -->
                <a class="lytrod-cta-link" href="<?php echo esc_url( $url ); ?>" style="display:inline-block;padding:14px 28px;border:1px solid <?php echo esc_attr( $border ); ?>;border-radius:<?php echo (int) $radius; ?>px;background-color:<?php echo esc_attr( $bg ); ?>;color:<?php echo esc_attr( $fg ); ?>;font-family:<?php echo $font; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static font stack; single quotes must survive. ?>;font-size:15px;font-weight:700;line-height:18px;text-decoration:none;text-align:center;mso-hide:all;"><?php echo esc_html( $label ); ?></a>
                <!--<![endif]-->
            </td>
        </tr>
    </table>
    <?php

    return (string) ob_get_clean();
}

/**
 * Render a highlighted panel — the `surface_alt` block used for Product IDs,
 * next steps and support details.
 *
 * @param string $inner_html Panel contents. Must already be escaped.
 * @param string $label      Optional eyebrow label above the contents.
 * @return string
 */
function lytrod_emails_panel( string $inner_html, string $label = '' ): string {
    if ( '' === trim( $inner_html ) ) {
        return '';
    }

    ob_start();
    ?>
    <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" class="lytrod-panel">
        <tr>
            <td class="lytrod-panel-cell">
                <?php if ( '' !== $label ) : ?>
                    <p class="lytrod-eyebrow"><?php echo esc_html( $label ); ?></p>
                <?php endif; ?>
                <?php echo $inner_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Caller-escaped markup. ?>
            </td>
        </tr>
    </table>
    <?php

    return (string) ob_get_clean();
}

/**
 * Render the Product ID panel for an order or subscription, if one is stored.
 *
 * @param mixed  $object Order or subscription.
 * @param string $label  Eyebrow label.
 * @return string Empty string when no Product ID exists.
 */
function lytrod_emails_product_id_panel( $object, string $label = '' ): string {
    $product_id = Lytrod_Emails_Content::product_id( $object );

    if ( '' === $product_id ) {
        return '';
    }

    if ( '' === $label ) {
        $label = __( 'Product ID', 'lytrod-emails' );
    }

    return lytrod_emails_panel(
        '<p class="lytrod-mono">' . esc_html( $product_id ) . '</p>',
        $label
    );
}

/**
 * Render a status pill.
 *
 * @param string $label Pill text.
 * @param string $tone  `primary`, `accent`, `danger` or `muted`.
 * @return string
 */
function lytrod_emails_pill( string $label, string $tone = 'accent' ): string {
    if ( '' === $label ) {
        return '';
    }

    switch ( $tone ) {
        case 'primary':
            $bg = Lytrod_Emails_Brand::c( 'primary' );
            $fg = Lytrod_Emails_Brand::c( 'on_primary' );
            break;

        case 'danger':
            $bg = Lytrod_Emails_Brand::c( 'danger_bg' );
            $fg = Lytrod_Emails_Brand::c( 'danger' );
            break;

        case 'muted':
            $bg = Lytrod_Emails_Brand::c( 'surface_alt' );
            $fg = Lytrod_Emails_Brand::c( 'ink_muted' );
            break;

        case 'accent':
        default:
            $bg = Lytrod_Emails_Brand::c( 'surface_alt' );
            $fg = Lytrod_Emails_Brand::c( 'primary' );
            break;
    }

    $font = Lytrod_Emails_Brand::c( 'font' );

    return sprintf(
        '<span class="lytrod-pill" style="display:inline-block;padding:5px 12px;border-radius:%1$s;background-color:%2$s;color:%3$s;font-family:%4$s;font-size:11px;font-weight:700;line-height:14px;letter-spacing:.06em;text-transform:uppercase;white-space:nowrap;">%5$s</span>',
        esc_attr( Lytrod_Emails_Brand::c( 'radius_pill' ) ),
        esc_attr( $bg ),
        esc_attr( $fg ),
        $font, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static font stack; single quotes must survive.
        esc_html( $label )
    );
}

/**
 * Render the standard support block used at the foot of customer emails.
 *
 * @param string $intro Optional lead-in sentence.
 * @return string
 */
function lytrod_emails_support_block( string $intro = '' ): string {
    if ( '' === $intro ) {
        $intro = __( 'Questions about your license or installation? Our team reads every message.', 'lytrod-emails' );
    }

    $email = Lytrod_Emails_Brand::support_email();

    $inner  = '<p class="lytrod-panel-text">' . esc_html( $intro ) . '</p>';
    $inner .= '<p class="lytrod-panel-text" style="margin-bottom:0;"><a class="link" href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a></p>';

    return lytrod_emails_panel( $inner, __( 'Need a hand?', 'lytrod-emails' ) );
}

/**
 * Render a section heading with an optional trailing meta line.
 *
 * @param string $title Heading text.
 * @param string $meta  Optional secondary line.
 * @return string
 */
function lytrod_emails_heading( string $title, string $meta = '' ): string {
    if ( '' === $title ) {
        return '';
    }

    $html = '<h2 class="lytrod-section-title">' . esc_html( $title );

    if ( '' !== $meta ) {
        $html .= '<span class="lytrod-section-meta">' . esc_html( $meta ) . '</span>';
    }

    return $html . '</h2>';
}

/**
 * Render the per-email "additional content" set in WooCommerce > Settings > Emails.
 *
 * @param string $content Raw additional content.
 * @return string
 */
function lytrod_emails_additional_content( string $content ): string {
    if ( '' === trim( $content ) ) {
        return '';
    }

    return '<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation"><tr><td class="email-additional-content">'
        . wp_kses_post( wpautop( wptexturize( $content ) ) )
        . '</td></tr></table>';
}

/**
 * Render the body of a customer order email: lede, primary CTA, Product ID panel.
 *
 * Keeps the twelve order-shaped templates consistent without hiding the
 * WooCommerce action hooks, which stay visible in each template.
 *
 * @param mixed         $order Order or subscription.
 * @param WC_Email|null $email Email being rendered.
 * @param string        $lede  Lead paragraph. Plain text; escaped here.
 * @return string
 */
function lytrod_emails_intro( $order, $email = null, string $lede = '' ): string {
    $html = '';

    if ( '' !== $lede ) {
        $html .= '<div class="email-introduction"><p class="lytrod-lede">' . esc_html( $lede ) . '</p></div>';
    }

    $cta = Lytrod_Emails_Content::cta_for( $email, $order );

    if ( ! empty( $cta['label'] ) && ! empty( $cta['url'] ) ) {
        $html .= lytrod_emails_button( (string) $cta['label'], (string) $cta['url'] );
    }

    $html .= lytrod_emails_product_id_panel( $order );

    return $html;
}

/**
 * Render the subscription status table shared by the cancelled, expired and
 * suspended subscription admin emails.
 *
 * WooCommerce Subscriptions duplicates this markup across three templates with a
 * hardcoded inline Helvetica stack that Emogrifier cannot override. This is one
 * implementation using the brand palette.
 *
 * @param WC_Subscription $subscription Subscription object.
 * @param string          $final_label  Heading for the final column.
 * @param string          $final_value  Value for the final column, already escaped.
 * @return string
 */
function lytrod_emails_subscription_table( $subscription, string $final_label, string $final_value ): string {
    if ( ! $subscription instanceof WC_Order ) {
        return '';
    }

    $text_align   = is_rtl() ? 'right' : 'left';
    $number_align = is_rtl() ? 'left' : 'right';

    $last_order_time = $subscription->get_time( 'last_order_date_created', 'site' );
    $last_order      = $last_order_time
        ? esc_html( date_i18n( wc_date_format(), $last_order_time ) )
        : '&mdash;';

    ob_start();
    ?>
    <table class="lytrod-table font-family" cellspacing="0" cellpadding="0" width="100%" border="0" role="presentation" style="width:100%;margin-bottom:20px;">
        <thead>
            <tr>
                <th scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'License', 'lytrod-emails' ); ?></th>
                <th class="lytrod-numeric" scope="col" style="text-align:<?php echo esc_attr( $number_align ); ?>;"><?php esc_html_e( 'Price', 'lytrod-emails' ); ?></th>
                <th scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Last order', 'lytrod-emails' ); ?></th>
                <th scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo esc_html( $final_label ); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align:<?php echo esc_attr( $text_align ); ?>;vertical-align:top;">
                    <a class="link" href="<?php echo esc_url( wcs_get_edit_post_link( $subscription->get_id() ) ); ?>" style="text-decoration:none;"><strong><?php echo esc_html( '#' . $subscription->get_order_number() ); ?></strong></a>
                </td>
                <td class="lytrod-numeric" style="text-align:<?php echo esc_attr( $number_align ); ?>;vertical-align:top;">
                    <?php echo wp_kses_post( $subscription->get_formatted_order_total() ); ?>
                </td>
                <td style="text-align:<?php echo esc_attr( $text_align ); ?>;vertical-align:top;">
                    <?php echo $last_order; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped when built. ?>
                </td>
                <td style="text-align:<?php echo esc_attr( $text_align ); ?>;vertical-align:top;">
                    <?php echo $final_value; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Caller-escaped. ?>
                </td>
            </tr>
        </tbody>
    </table>
    <?php

    return (string) ob_get_clean();
}

/**
 * Render the success header for a renewal receipt: a circular check badge beside a
 * primary-colour headline, with a supporting paragraph beneath.
 *
 * The badge is a bundled PNG rather than CSS. Outlook on Windows ignores
 * border-radius, so a CSS circle would render as a blue square there.
 *
 * @param string $headline Headline text.
 * @param string $body     Supporting paragraph.
 * @return string
 */
function lytrod_emails_success_header( string $headline, string $body = '' ): string {
    $primary = Lytrod_Emails_Brand::c( 'primary' );
    $font    = Lytrod_Emails_Brand::c( 'font' );

    ob_start();
    ?>
    <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="margin:0 0 8px;">
        <tr>
            <td width="88" valign="middle" style="width:88px;padding-<?php echo is_rtl() ? 'left' : 'right'; ?>:16px;">
                <img src="<?php echo esc_url( Lytrod_Emails_Brand::icon_url( 'check-circle' ) ); ?>"
                    alt="" width="72" aria-hidden="true"
                    style="display:block;border:0;width:72px;max-width:72px;height:auto;" />
            </td>
            <td valign="middle">
                <h1 class="lytrod-success-title" style="margin:0;color:<?php echo esc_attr( $primary ); ?>;font-family:<?php echo $font; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static font stack. ?>;font-size:24px;font-weight:700;line-height:125%;">
                    <?php echo esc_html( $headline ); ?>
                </h1>
            </td>
        </tr>
    </table>
    <?php if ( '' !== $body ) : ?>
        <p class="lytrod-success-body"><?php echo esc_html( $body ); ?></p>
    <?php endif; ?>
    <?php

    return (string) ob_get_clean();
}

/**
 * Render a card of labelled rows, each with an icon.
 *
 * @param array<int, array{icon: string, label: string, value: string}> $rows Rows to render.
 * @return string
 */
function lytrod_emails_meta_card( array $rows ): string {
    $rows = array_values(
        array_filter(
            $rows,
            static function ( $row ): bool {
                return ! empty( $row['label'] ) && ! empty( $row['value'] );
            }
        )
    );

    if ( empty( $rows ) ) {
        return '';
    }

    ob_start();
    ?>
    <table class="lytrod-panel" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
        <tr>
            <td class="lytrod-panel-cell">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                    <?php foreach ( $rows as $index => $row ) : ?>
                        <tr>
                            <td width="40" valign="top" style="width:40px;padding:<?php echo 0 === $index ? '0' : '14px'; ?> 0 0;">
                                <img src="<?php echo esc_url( Lytrod_Emails_Brand::icon_url( $row['icon'] ) ); ?>"
                                    alt="" width="24" aria-hidden="true"
                                    style="display:block;border:0;width:24px;max-width:24px;height:auto;" />
                            </td>
                            <td valign="top" style="padding:<?php echo 0 === $index ? '0' : '14px'; ?> 0 0;">
                                <p class="lytrod-eyebrow"><?php echo esc_html( $row['label'] ); ?></p>
                                <p class="lytrod-panel-text" style="margin-bottom:0 !important;font-weight:700;">
                                    <?php echo esc_html( $row['value'] ); ?>
                                </p>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </td>
        </tr>
    </table>
    <?php

    return (string) ob_get_clean();
}

/**
 * Render the License Details table for an order.
 *
 * Replaces the generic order-items table on renewal receipts: a licensee needs to know
 * what was renewed, for how many seats, over what period and through what date.
 *
 * @param mixed $order Order.
 * @return string
 */
function lytrod_emails_license_table( $order ): string {
    if ( ! $order instanceof WC_Order ) {
        return '';
    }

    $items = $order->get_items();

    if ( empty( $items ) ) {
        return '';
    }

    $subscription = Lytrod_Emails_Content::subscription_for( $order );
    $text_align   = is_rtl() ? 'right' : 'left';
    $number_align = is_rtl() ? 'left' : 'right';

    ob_start();
    ?>
    <h2 class="lytrod-section-title"><?php esc_html_e( 'License details', 'lytrod-emails' ); ?></h2>

    <div class="lytrod-scroll">
        <table class="lytrod-table font-family" cellspacing="0" cellpadding="0" width="100%" border="0" role="presentation" style="width:100%;margin-bottom:20px;">
            <thead>
                <tr>
                    <th scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Product name', 'lytrod-emails' ); ?></th>
                    <th class="lytrod-numeric" scope="col" style="text-align:<?php echo esc_attr( $number_align ); ?>;"><?php esc_html_e( 'Licensed seats', 'lytrod-emails' ); ?></th>
                    <th scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Coverage period', 'lytrod-emails' ); ?></th>
                    <th scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Expiration date', 'lytrod-emails' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ( $items as $item ) :
                    /** This filter is documented in woocommerce/templates/emails/email-order-items.php */
                    if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
                        continue;
                    }

                    $coverage = Lytrod_Emails_Content::coverage_period( $item, $order, $subscription );
                    $expiry   = Lytrod_Emails_Content::expiry_date( $subscription );
                    ?>
                    <tr class="order_item">
                        <td style="text-align:<?php echo esc_attr( $text_align ); ?>;vertical-align:top;">
                            <strong><?php echo esc_html( Lytrod_Emails_Content::product_name( $item, $order ) ); ?></strong>
                            <?php $tier = Lytrod_Emails_Content::tier( $item ); ?>
                            <?php if ( '' !== $tier ) : ?>
                                <div class="email-order-item-meta"><?php echo esc_html( $tier ); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="lytrod-numeric" style="text-align:<?php echo esc_attr( $number_align ); ?>;vertical-align:top;">
                            <?php echo esc_html( (string) Lytrod_Emails_Content::licensed_seats( $item, $order ) ); ?>
                        </td>
                        <td style="text-align:<?php echo esc_attr( $text_align ); ?>;vertical-align:top;">
                            <?php echo '' !== $coverage ? esc_html( $coverage ) : '&mdash;'; ?>
                        </td>
                        <td style="text-align:<?php echo esc_attr( $text_align ); ?>;vertical-align:top;">
                            <?php echo '' !== $expiry ? esc_html( $expiry ) : '&mdash;'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php

    return (string) ob_get_clean();
}

/**
 * Render the Payment Summary / Amount Due block.
 *
 * Adaptive by necessity, not for polish. On this store the theme strips the gateway off
 * every renewal order (themes/lytrod/functions.php:570-597, whose gate options
 * `lytrod_auto_renew_all` / `lytrod_auto_renew_subs` do not exist), so 41 of 68 renewal
 * orders carry no payment method, transaction id or card at all — including every one of
 * the most recent, at real dollar amounts. Unpaid is the common case, so it gets a
 * first-class treatment rather than a row of blanks.
 *
 * @param mixed $order Order.
 * @return string
 */
function lytrod_emails_payment_summary( $order ): string {
    if ( ! $order instanceof WC_Order || $order->get_total() <= 0 ) {
        return '';
    }

    $currency = array( 'currency' => $order->get_currency() );
    $paid     = $order->is_paid();
    $tax      = (float) $order->get_total_tax();
    $discount = (float) $order->get_total_discount();
    $shipping = (float) $order->get_shipping_total();

    $rows = array(
        array( 'label' => __( 'Subtotal', 'lytrod-emails' ), 'value' => wc_price( $order->get_subtotal(), $currency ) ),
    );

    if ( $discount > 0 ) {
        $rows[] = array( 'label' => __( 'Discount', 'lytrod-emails' ), 'value' => '-' . wc_price( $discount, $currency ) );
    }

    if ( $shipping > 0 ) {
        $rows[] = array( 'label' => __( 'Shipping', 'lytrod-emails' ), 'value' => wc_price( $shipping, $currency ) );
    }

    // Taxes are disabled store-wide, so the row would always read $0.00.
    if ( wc_tax_enabled() && $tax > 0 ) {
        $rows[] = array( 'label' => __( 'Tax', 'lytrod-emails' ), 'value' => wc_price( $tax, $currency ) );
    }

    $method  = Lytrod_Emails_Payment::describe( $order );
    $txn     = (string) $order->get_transaction_id();
    $heading = $paid ? __( 'Payment summary', 'lytrod-emails' ) : __( 'Amount due', 'lytrod-emails' );
    $total   = $paid ? __( 'Total charged', 'lytrod-emails' ) : __( 'Total due', 'lytrod-emails' );

    ob_start();
    ?>
    <h2 class="lytrod-section-title" style="margin-top:0;"><?php echo esc_html( $heading ); ?></h2>

    <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
        <?php foreach ( $rows as $row ) : ?>
            <tr>
                <td class="lytrod-meta" style="padding:0 0 6px;"><?php echo esc_html( $row['label'] ); ?></td>
                <td class="lytrod-meta lytrod-numeric" style="padding:0 0 6px;"><?php echo wp_kses_post( $row['value'] ); ?></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td style="padding:10px 0 0;border-top:1px solid <?php echo esc_attr( Lytrod_Emails_Brand::c( 'hairline' ) ); ?>;">
                <strong><?php echo esc_html( $total ); ?></strong>
            </td>
            <td class="lytrod-numeric" style="padding:10px 0 0;border-top:1px solid <?php echo esc_attr( Lytrod_Emails_Brand::c( 'hairline' ) ); ?>;">
                <strong><?php echo wp_kses_post( wc_price( $order->get_total(), $currency ) ); ?></strong>
            </td>
        </tr>
    </table>

    <?php if ( $paid && '' !== $method ) : ?>
        <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="margin-top:16px;">
            <tr>
                <td width="36" valign="middle" style="width:36px;">
                    <img src="<?php echo esc_url( Lytrod_Emails_Brand::icon_url( 'card' ) ); ?>"
                        alt="" width="24" aria-hidden="true"
                        style="display:block;border:0;width:24px;max-width:24px;height:auto;" />
                </td>
                <td valign="middle">
                    <p class="lytrod-panel-text" style="margin:0 !important;font-weight:700;"><?php echo esc_html( $method ); ?></p>
                    <?php if ( '' !== $txn ) : ?>
                        <p class="lytrod-meta" style="margin:2px 0 0;">
                            <?php
                            printf(
                                /* translators: %s: gateway transaction identifier. */
                                esc_html__( 'Transaction %s', 'lytrod-emails' ),
                                esc_html( $txn )
                            );
                            ?>
                        </p>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    <?php elseif ( ! $paid ) : ?>
        <div style="margin-top:16px;">
            <?php
            echo lytrod_emails_button( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
                __( 'Pay this renewal', 'lytrod-emails' ),
                $order->get_checkout_payment_url()
            );
            ?>
        </div>
    <?php endif; ?>
    <?php

    return (string) ob_get_clean();
}

/**
 * Render the Important Links column.
 *
 * @return string
 */
function lytrod_emails_links_panel(): string {
    $account = wc_get_page_permalink( 'myaccount' );
    $support = Lytrod_Emails_Brand::support_email();

    ob_start();
    ?>
    <h2 class="lytrod-section-title" style="margin-top:0;"><?php esc_html_e( 'Important links', 'lytrod-emails' ); ?></h2>

    <?php if ( $account ) : ?>
        <p class="lytrod-panel-text" style="margin-bottom:2px !important;">
            <a class="link" href="<?php echo esc_url( $account ); ?>"><strong><?php esc_html_e( 'View your account', 'lytrod-emails' ); ?></strong></a>
        </p>
        <p class="lytrod-meta" style="margin:0 0 16px;">
            <?php esc_html_e( 'Log in to your dashboard to manage your licenses and your billing information.', 'lytrod-emails' ); ?>
        </p>
    <?php endif; ?>

    <p class="lytrod-panel-text" style="margin-bottom:2px !important;">
        <a class="link" href="<?php echo esc_url( 'mailto:' . $support ); ?>"><strong><?php esc_html_e( 'Need help?', 'lytrod-emails' ); ?></strong></a>
    </p>
    <p class="lytrod-meta" style="margin:0;">
        <?php esc_html_e( 'Our support team is here to help with any questions you may have.', 'lytrod-emails' ); ?>
    </p>
    <?php

    return (string) ob_get_clean();
}

/**
 * Lay the Payment Summary and Important Links side by side, stacking on mobile.
 *
 * @param mixed $order Order.
 * @return string
 */
function lytrod_emails_renewal_footer_columns( $order ): string {
    $summary = lytrod_emails_payment_summary( $order );
    $links   = lytrod_emails_links_panel();
    $gutter  = is_rtl() ? 'right' : 'left';

    if ( '' === $summary ) {
        return '<div class="lytrod-col-single">' . $links . '</div>';
    }

    ob_start();
    ?>
    <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="margin-top:8px;">
        <tr>
            <td class="lytrod-col" width="50%" valign="top" style="width:50%;">
                <?php echo $summary; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally. ?>
            </td>
            <td class="lytrod-col" width="50%" valign="top" style="width:50%;padding-<?php echo esc_attr( $gutter ); ?>:24px;">
                <?php echo $links; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally. ?>
            </td>
        </tr>
    </table>
    <?php

    return (string) ob_get_clean();
}

/**
 * Render a complete renewal receipt body: success header, meta card, license table and
 * the payment/links columns.
 *
 * Shared by every renewal and switch email so the six of them cannot drift apart.
 *
 * @param mixed  $order    Order.
 * @param string $headline Success headline.
 * @param string $body     Supporting paragraph.
 * @return string
 */
function lytrod_emails_renewal_receipt( $order, string $headline, string $body ): string {
    if ( ! $order instanceof WC_Order ) {
        return '';
    }

    $date = $order->get_date_paid() ? $order->get_date_paid() : $order->get_date_created();

    $html  = lytrod_emails_success_header( $headline, $body );
    $html .= lytrod_emails_meta_card(
        array(
            array(
                'icon'  => 'calendar',
                'label' => __( 'Renewal date', 'lytrod-emails' ),
                'value' => $date ? date_i18n( wc_date_format(), $date->getOffsetTimestamp() ) : '',
            ),
            array(
                'icon'  => 'receipt',
                'label' => __( 'Invoice number', 'lytrod-emails' ),
                'value' => $order->get_order_number(),
            ),
        )
    );
    $html .= lytrod_emails_product_id_panel( Lytrod_Emails_Content::subscription_for( $order ) ?: $order );
    $html .= lytrod_emails_license_table( $order );
    $html .= lytrod_emails_renewal_footer_columns( $order );

    return $html;
}

/**
 * Wrap arbitrary body HTML in the full Lytrod email shell, with CSS inlined.
 *
 * This is the entry point for mail that does not go through the WC_Email
 * framework — lytrod-renewal-emails calls wp_mail() directly, so
 * WC_Email::style_inline() never runs on it and it would otherwise receive no
 * stylesheet at all.
 *
 * Inlining reuses WooCommerce's own vendored Emogrifier via the public
 * WC_Email::style_inline() (woocommerce/includes/emails/class-wc-email.php:882),
 * whose apply_inline_style() loads `emails/email-styles.php` through
 * wc_get_template() — which this plugin overrides. The standalone path therefore
 * gets byte-identical CSS to the WC_Email path.
 *
 * @param string $inner_html Body markup, already escaped.
 * @param array  $args       Optional `heading`, `preheader`, `pill`, `cta`.
 * @return string Complete HTML document with inlined styles.
 */
function lytrod_emails_wrap( string $inner_html, array $args = array() ): string {
    if ( ! function_exists( 'wc_get_template' ) || ! class_exists( 'WC_Email' ) ) {
        return $inner_html;
    }

    $args = wp_parse_args(
        $args,
        array(
            'heading'   => '',
            'preheader' => '',
            'pill'      => null,
            'cta'       => null,
        )
    );

    ob_start();

    wc_get_template(
        'emails/email-header.php',
        array(
            'email_heading'      => $args['heading'],
            'store_name'         => Lytrod_Emails_Brand::store_name(),
            'lytrod_preheader'   => $args['preheader'],
            'lytrod_pill'        => $args['pill'],
            'lytrod_standalone'  => true,
        )
    );

    if ( ! empty( $args['cta']['label'] ) && ! empty( $args['cta']['url'] ) ) {
        echo lytrod_emails_button( (string) $args['cta']['label'], (string) $args['cta']['url'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
    }

    echo $inner_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Caller-escaped markup.

    wc_get_template( 'emails/email-footer.php' );

    $html = (string) ob_get_clean();

    $mailer             = new WC_Email();
    $mailer->email_type = 'html'; // style_inline() gates on get_content_type().

    return $mailer->style_inline( $html );
}
