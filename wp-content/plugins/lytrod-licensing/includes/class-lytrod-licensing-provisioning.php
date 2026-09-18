<?php
/**
 * The LimeLM manual-step guardrail.
 *
 * Selling N seats does not grant N activations. There is no code anywhere on this site that
 * talks to LimeLM/wyday — no API client even in the disabled theme files, no credentials, no
 * cron, no webhook — and `totalactivationslots` is a frozen 2022 import that nothing reads or
 * writes. Serials are typed in by the customer and checked only for format and local
 * uniqueness. What gives a Plus buyer 3 activations today is a human in the LimeLM console.
 *
 * Seats therefore ship as a billing change, and this class exists to make the human step
 * impossible to miss rather than to pretend it isn't there.
 *
 * @package Lytrod_Licensing
 */

defined( 'ABSPATH' ) || exit;

/**
 * Provisioning visibility.
 */
class Lytrod_Licensing_Provisioning {

    /**
     * Register the guardrail.
     *
     * @return void
     */
    public static function init(): void {
        add_action( 'woocommerce_email_order_meta', array( __CLASS__, 'email_callout' ), 5, 4 );
        add_action( 'admin_notices', array( __CLASS__, 'pending_notice' ) );
        add_filter( 'manage_edit-shop_subscription_columns', array( __CLASS__, 'add_column' ), 20 );
        add_action( 'manage_shop_subscription_posts_custom_column', array( __CLASS__, 'render_column' ), 20, 2 );
    }

    /**
     * Prominent action block on admin order email for any multi-seat order.
     *
     * @param WC_Order $order         Order.
     * @param bool     $sent_to_admin Whether this is the admin copy.
     * @param bool     $plain_text    Whether plain text.
     * @param mixed    $email         Email object.
     * @return void
     */
    public static function email_callout( $order, $sent_to_admin = false, $plain_text = false, $email = null ): void {
        if ( ! $sent_to_admin || ! $order instanceof WC_Order ) {
            return;
        }

        $seats = Lytrod_Licensing_Seats::get( $order );

        if ( $seats < 2 ) {
            return;
        }

        $serial = class_exists( 'Lytrod_Emails_Content' )
            ? Lytrod_Emails_Content::product_id( $order )
            : '';

        $lines = array(
            sprintf(
                /* translators: %d: seat count. */
                __( 'Set the LimeLM activation limit to %d.', 'lytrod-licensing' ),
                $seats
            ),
        );

        if ( '' !== $serial ) {
            $lines[] = sprintf(
                /* translators: %s: product id / serial. */
                __( 'Product ID: %s', 'lytrod-licensing' ),
                $serial
            );
        }

        $lines[] = __( 'This is not automated. The licence will only allow one activation until it is done.', 'lytrod-licensing' );

        if ( $plain_text ) {
            echo "\n" . esc_html( strtoupper( __( 'Action required', 'lytrod-licensing' ) ) ) . "\n";

            foreach ( $lines as $line ) {
                echo esc_html( $line ) . "\n";
            }

            return;
        }

        $body = '';

        foreach ( $lines as $line ) {
            $body .= '<p style="margin:0 0 6px;font-size:14px;line-height:150%;">' . esc_html( $line ) . '</p>';
        }

        printf(
            '<table border="0" cellpadding="0" cellspacing="0" width="100%%" role="presentation" style="margin:0 0 20px;background-color:#fdecea;border-radius:6px;">
                <tr><td style="padding:16px 18px;border-left:4px solid #b3261e;border-radius:6px;">
                    <p style="margin:0 0 8px;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#b3261e;">%s</p>
                    %s
                </td></tr>
            </table>',
            esc_html__( 'Action required', 'lytrod-licensing' ),
            $body // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped line by line above.
        );
    }

    /**
     * Subscriptions still awaiting a LimeLM activation-limit change.
     *
     * @param int $limit Maximum to return.
     * @return WC_Subscription[]
     */
    public static function pending( int $limit = 50 ): array {
        if ( ! function_exists( 'wcs_get_subscriptions' ) ) {
            return array();
        }

        $subscriptions = wcs_get_subscriptions(
            array(
                'subscriptions_per_page' => $limit,
                'subscription_status'    => array( 'active', 'on-hold', 'pending' ),
                'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                    array(
                        'key'     => Lytrod_Licensing_Seats::META_PROVISIONED,
                        'compare' => 'NOT EXISTS',
                    ),
                ),
            )
        );

        return array_values(
            array_filter(
                $subscriptions,
                static function ( $subscription ): bool {
                    return Lytrod_Licensing_Seats::needs_provisioning( $subscription );
                }
            )
        );
    }

    /**
     * Admin notice listing what still needs provisioning.
     *
     * @return void
     */
    public static function pending_notice(): void {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

        if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'edit-shop_subscription', 'edit-shop_order', 'woocommerce_page_wc-orders' ), true ) ) {
            return;
        }

        $pending = self::pending();

        if ( ! $pending ) {
            return;
        }

        $links = array();

        foreach ( array_slice( $pending, 0, 8 ) as $subscription ) {
            $links[] = sprintf(
                '<a href="%s">#%s (%d seats)</a>',
                esc_url( get_edit_post_link( $subscription->get_id() ) ),
                esc_html( $subscription->get_order_number() ),
                Lytrod_Licensing_Seats::get_or_infer( $subscription )
            );
        }

        echo '<div class="notice notice-warning"><p><strong>';
        printf(
            /* translators: %d: number of subscriptions. */
            esc_html( _n( '%d licence needs its LimeLM activation limit set.', '%d licences need their LimeLM activation limit set.', count( $pending ), 'lytrod-licensing' ) ),
            count( $pending )
        );
        echo '</strong> ';
        echo wp_kses_post( implode( ' · ', $links ) );

        if ( count( $pending ) > 8 ) {
            printf( ' <em>%s</em>', esc_html( sprintf( __( 'and %d more', 'lytrod-licensing' ), count( $pending ) - 8 ) ) );
        }

        echo '</p></div>';
    }

    /**
     * Add a Seats column to the subscriptions list.
     *
     * @param array $columns Existing columns.
     * @return array
     */
    public static function add_column( $columns ) {
        $inserted = array();

        foreach ( (array) $columns as $key => $label ) {
            $inserted[ $key ] = $label;

            if ( 'order_title' === $key || 'status' === $key ) {
                $inserted['lytrod_seats'] = __( 'Seats', 'lytrod-licensing' );
            }
        }

        if ( ! isset( $inserted['lytrod_seats'] ) ) {
            $inserted['lytrod_seats'] = __( 'Seats', 'lytrod-licensing' );
        }

        return $inserted;
    }

    /**
     * Render the Seats column.
     *
     * @param string $column      Column key.
     * @param int    $post_id     Subscription id.
     * @return void
     */
    public static function render_column( $column, $post_id = 0 ): void {
        if ( 'lytrod_seats' !== $column ) {
            return;
        }

        $subscription = function_exists( 'wcs_get_subscription' ) ? wcs_get_subscription( $post_id ) : null;

        if ( ! $subscription ) {
            echo '&mdash;';

            return;
        }

        $seats = Lytrod_Licensing_Seats::get_or_infer( $subscription );

        echo esc_html( (string) $seats );

        if ( Lytrod_Licensing_Seats::needs_provisioning( $subscription ) ) {
            echo ' <span style="color:#b3261e;font-weight:600;" title="' . esc_attr__( 'LimeLM activation limit not yet set', 'lytrod-licensing' ) . '">&#9679;</span>';
        }
    }
}
