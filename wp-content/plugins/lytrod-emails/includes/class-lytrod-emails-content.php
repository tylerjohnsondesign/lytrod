<?php
/**
 * Per-email content decisions: status pill, primary call to action, and
 * resolution of the Lytrod Product ID.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/**
 * Content maps keyed on WC_Email::$id.
 */
class Lytrod_Emails_Content {

    /**
     * Order and subscription meta keys that hold a Lytrod Product ID, in
     * precedence order. First non-empty wins.
     *
     * Two divergent copies of this rule exist on the site today — one reading off
     * the order in subscriptions-upgrader/templates/woocommerce/emails/admin-new-order.php:84,
     * one reading off the subscription in the hand-edited
     * woocommerce-subscriptions/templates/emails/subscription-info.php:52. This is
     * the single implementation.
     *
     * @var string[]
     */
    private static $product_id_keys = array(
        'intellicut_serial_number',
        'intellicut_global_product_id',
        'vrcutimposeinstallation_product_id',
        'intellicutaneinstallation_product_id',
        'installation_product_id',
        'bizcard_product_id',
    );

    /**
     * Status pill per email id: label plus a tone from the brand palette.
     *
     * Tones are `primary`, `accent`, `danger` and `muted`.
     *
     * @return array<string, array{label: string, tone: string}>
     */
    public static function pills(): array {
        $pills = array(

            // WooCommerce core — customer.
            'customer_processing_order'                  => array( 'label' => __( 'Order received', 'lytrod-emails' ), 'tone' => 'accent' ),
            'customer_completed_order'                   => array( 'label' => __( 'Complete', 'lytrod-emails' ), 'tone' => 'primary' ),
            'customer_on_hold_order'                     => array( 'label' => __( 'On hold', 'lytrod-emails' ), 'tone' => 'accent' ),
            'customer_invoice'                           => array( 'label' => __( 'Payment due', 'lytrod-emails' ), 'tone' => 'accent' ),
            'customer_failed_order'                      => array( 'label' => __( 'Payment failed', 'lytrod-emails' ), 'tone' => 'danger' ),
            'customer_refunded_order'                    => array( 'label' => __( 'Refunded', 'lytrod-emails' ), 'tone' => 'danger' ),
            'customer_cancelled_order'                   => array( 'label' => __( 'Cancelled', 'lytrod-emails' ), 'tone' => 'muted' ),
            'customer_note'                              => array( 'label' => __( 'Update', 'lytrod-emails' ), 'tone' => 'accent' ),
            'customer_new_account'                       => array( 'label' => __( 'Welcome', 'lytrod-emails' ), 'tone' => 'primary' ),
            'customer_reset_password'                    => array( 'label' => __( 'Security', 'lytrod-emails' ), 'tone' => 'accent' ),
            'customer_pos_completed_order'               => array( 'label' => __( 'Complete', 'lytrod-emails' ), 'tone' => 'primary' ),
            'customer_pos_refunded_order'                => array( 'label' => __( 'Refunded', 'lytrod-emails' ), 'tone' => 'danger' ),

            // WooCommerce core — admin.
            'new_order'                                  => array( 'label' => __( 'New order', 'lytrod-emails' ), 'tone' => 'primary' ),
            'cancelled_order'                            => array( 'label' => __( 'Cancelled', 'lytrod-emails' ), 'tone' => 'muted' ),
            'failed_order'                               => array( 'label' => __( 'Payment failed', 'lytrod-emails' ), 'tone' => 'danger' ),
            'admin_payment_gateway_enabled'              => array( 'label' => __( 'Gateway enabled', 'lytrod-emails' ), 'tone' => 'primary' ),

            // Subscriptions.
            'new_renewal_order'                          => array( 'label' => __( 'Renewal order', 'lytrod-emails' ), 'tone' => 'primary' ),
            'new_switch_order'                           => array( 'label' => __( 'Plan change', 'lytrod-emails' ), 'tone' => 'primary' ),
            'customer_processing_renewal_order'          => array( 'label' => __( 'Renewal received', 'lytrod-emails' ), 'tone' => 'accent' ),
            'customer_completed_renewal_order'           => array( 'label' => __( 'Renewed', 'lytrod-emails' ), 'tone' => 'primary' ),
            'customer_on_hold_renewal_order'             => array( 'label' => __( 'On hold', 'lytrod-emails' ), 'tone' => 'accent' ),
            'customer_completed_switch_order'            => array( 'label' => __( 'Plan updated', 'lytrod-emails' ), 'tone' => 'primary' ),
            'customer_renewal_invoice'                   => array( 'label' => __( 'Renewal due', 'lytrod-emails' ), 'tone' => 'accent' ),
            'cancelled_subscription'                     => array( 'label' => __( 'Cancelled', 'lytrod-emails' ), 'tone' => 'muted' ),
            'expired_subscription'                       => array( 'label' => __( 'Expired', 'lytrod-emails' ), 'tone' => 'danger' ),
            'suspended_subscription'                     => array( 'label' => __( 'Suspended', 'lytrod-emails' ), 'tone' => 'accent' ),
            'customer_notification_manual_renewal'       => array( 'label' => __( 'Renewal due', 'lytrod-emails' ), 'tone' => 'accent' ),
            'customer_notification_auto_renewal'         => array( 'label' => __( 'Renewing soon', 'lytrod-emails' ), 'tone' => 'accent' ),
            'customer_notification_manual_trial_expiry'  => array( 'label' => __( 'Trial ending', 'lytrod-emails' ), 'tone' => 'accent' ),
            'customer_notification_auto_trial_expiry'    => array( 'label' => __( 'Trial ending', 'lytrod-emails' ), 'tone' => 'accent' ),
            'customer_notification_subscription_expiry'  => array( 'label' => __( 'Expiring', 'lytrod-emails' ), 'tone' => 'accent' ),
            'payment_retry'                              => array( 'label' => __( 'Retrying payment', 'lytrod-emails' ), 'tone' => 'accent' ),
            'customer_payment_retry'                     => array( 'label' => __( 'Payment issue', 'lytrod-emails' ), 'tone' => 'danger' ),

            /*
             * Subscriptions Gifting. Note the first id is a class name rather than a
             * slug (includes/gifting/emails/class-wcsg-email-customer-new-account.php:58),
             * so do not assume sanitize_key( $id ) === $id anywhere in this plugin.
             */
            'WCSG_Email_Customer_New_Account'            => array( 'label' => __( 'Gift received', 'lytrod-emails' ), 'tone' => 'primary' ),
            'recipient_completed_order'                  => array( 'label' => __( 'Gift activated', 'lytrod-emails' ), 'tone' => 'primary' ),
            'recipient_completed_renewal_order'          => array( 'label' => __( 'Renewed', 'lytrod-emails' ), 'tone' => 'primary' ),
            'gift_recipient_processing_renewal_order'    => array( 'label' => __( 'Renewal received', 'lytrod-emails' ), 'tone' => 'accent' ),

            // Stripe.
            'failed_renewal_authentication'              => array( 'label' => __( 'Action required', 'lytrod-emails' ), 'tone' => 'danger' ),
            'failed_preorder_sca_authentication'         => array( 'label' => __( 'Action required', 'lytrod-emails' ), 'tone' => 'danger' ),
            'failed_authentication_requested'            => array( 'label' => __( 'Action required', 'lytrod-emails' ), 'tone' => 'danger' ),
            'wc_stripe_failed_refund_admin'              => array( 'label' => __( 'Refund failed', 'lytrod-emails' ), 'tone' => 'danger' ),
            'wc_stripe_failed_refund_customer'           => array( 'label' => __( 'Refund issue', 'lytrod-emails' ), 'tone' => 'danger' ),
        );

        /**
         * Filter the email id => status pill map.
         *
         * @since 1.0.0
         * @param array $pills Each entry has `label` and `tone`.
         */
        return (array) apply_filters( 'lytrod_emails_pills', $pills );
    }

    /**
     * Status pill for a given email, or null when there is nothing useful to show.
     *
     * @param WC_Email|null $email Email being rendered.
     * @return array{label: string, tone: string}|null
     */
    public static function pill_for( $email = null ) {
        $id = $email instanceof WC_Email && ! empty( $email->id ) ? (string) $email->id : Lytrod_Emails_Context::id();

        if ( '' === $id ) {
            return null;
        }

        $pills = self::pills();
        $pill  = $pills[ $id ] ?? null;

        /**
         * Filter the resolved pill for one email.
         *
         * @since 1.0.0
         * @param array|null    $pill  Pill with `label` and `tone`, or null.
         * @param string        $id    Email id.
         * @param WC_Email|null $email Email object.
         */
        return apply_filters( 'lytrod_emails_pill', $pill, $id, $email );
    }

    /**
     * Primary call to action for a given email.
     *
     * Templates whose action target is not derivable from the order — password
     * reset and new-account, whose URLs are only available as template args —
     * build their own button and never call this.
     *
     * @param WC_Email|null $email  Email being rendered.
     * @param mixed         $object Order or subscription. Defaults to $email->object.
     * @return array{label: string, url: string}|null
     */
    public static function cta_for( $email = null, $object = null ) {
        $id = $email instanceof WC_Email && ! empty( $email->id ) ? (string) $email->id : Lytrod_Emails_Context::id();

        if ( null === $object && $email instanceof WC_Email ) {
            $object = $email->object;
        }

        $cta = null;

        if ( $object instanceof WC_Order ) {
            $pay_now = array(
                'label' => __( 'Pay now', 'lytrod-emails' ),
                'url'   => $object->get_checkout_payment_url(),
            );

            $view_order = array(
                'label' => __( 'View your order', 'lytrod-emails' ),
                'url'   => $object->get_view_order_url(),
            );

            switch ( $id ) {
                case 'customer_invoice':
                case 'customer_renewal_invoice':
                case 'customer_failed_order':
                case 'customer_payment_retry':
                case 'failed_renewal_authentication':
                case 'failed_preorder_sca_authentication':
                case 'failed_authentication_requested':
                    $cta = $pay_now;

                    if ( in_array( $id, array( 'customer_failed_order', 'customer_payment_retry' ), true ) ) {
                        $cta['label'] = __( 'Retry payment', 'lytrod-emails' );
                    } elseif ( 'customer_renewal_invoice' === $id ) {
                        $cta['label'] = __( 'Renew now', 'lytrod-emails' );
                    } elseif ( 0 === strpos( $id, 'failed_' ) ) {
                        $cta['label'] = __( 'Authorize payment', 'lytrod-emails' );
                    }
                    break;

                case 'new_order':
                case 'cancelled_order':
                case 'failed_order':
                case 'new_renewal_order':
                case 'new_switch_order':
                case 'payment_retry':
                case 'wc_stripe_failed_refund_admin':
                    $cta = array(
                        'label' => __( 'Open in admin', 'lytrod-emails' ),
                        'url'   => $object->get_edit_order_url(),
                    );
                    break;

                default:
                    $cta = $view_order;
                    break;
            }
        }

        // Subscription-scoped emails point at the subscription, not an order.
        if ( function_exists( 'wcs_is_subscription' ) && wcs_is_subscription( $object ) ) {
            $cta = array(
                'label' => __( 'Manage license', 'lytrod-emails' ),
                'url'   => $object->get_view_order_url(),
            );

            if ( in_array( $id, array( 'customer_notification_subscription_expiry', 'expired_subscription' ), true ) ) {
                $cta['label'] = __( 'Renew now', 'lytrod-emails' );
            } elseif ( in_array( $id, array( 'customer_notification_manual_trial_expiry', 'customer_notification_auto_trial_expiry' ), true ) ) {
                $cta['label'] = __( 'Choose a plan', 'lytrod-emails' );
            } elseif ( 'new_renewal_order' === $id || 'new_switch_order' === $id ) {
                $cta = array(
                    'label' => __( 'Open in admin', 'lytrod-emails' ),
                    'url'   => $object->get_edit_order_url(),
                );
            }
        }

        if ( null === $cta ) {
            $account = wc_get_page_permalink( 'myaccount' );

            if ( $account ) {
                $cta = array(
                    'label' => __( 'Go to your account', 'lytrod-emails' ),
                    'url'   => $account,
                );
            }
        }

        /**
         * Filter the resolved call to action for one email.
         *
         * @since 1.0.0
         * @param array|null    $cta    CTA with `label` and `url`, or null.
         * @param string        $id     Email id.
         * @param WC_Email|null $email  Email object.
         * @param mixed         $object Order or subscription.
         */
        return apply_filters( 'lytrod_emails_cta', $cta, $id, $email, $object );
    }

    /**
     * Resolve the Lytrod Product ID from an order or subscription.
     *
     * Uses WC_Data::get_meta() rather than get_post_meta( $object->get_id(), ... ).
     * The latter is what the existing implementations do, and it returns empty
     * silently once High-Performance Order Storage is enabled — losing serial
     * numbers with no error.
     *
     * @param WC_Order|WC_Abstract_Order|null $object Order or subscription.
     * @return string Empty string when no Product ID is stored.
     */
    public static function product_id( $object ): string {
        if ( ! $object instanceof WC_Data ) {
            return '';
        }

        /**
         * Filter the meta keys searched for a Lytrod Product ID.
         *
         * @since 1.0.0
         * @param string[] $keys In precedence order.
         */
        $keys = (array) apply_filters( 'lytrod_emails_product_id_keys', self::$product_id_keys );

        foreach ( $keys as $key ) {
            $value = $object->get_meta( $key, true );

            if ( ! empty( $value ) && is_scalar( $value ) ) {
                return trim( (string) $value );
            }
        }

        return '';
    }

    /**
     * Inbox preview text for a given email.
     *
     * Rendered as a zero-dimension block at the top of the body, so it shows in the
     * inbox preview line without appearing in the message itself.
     *
     * @param WC_Email|null $email Email being rendered.
     * @return string Empty string to render no preheader at all.
     */
    public static function preheader_for( $email = null ): string {
        $email = $email instanceof WC_Email ? $email : Lytrod_Emails_Context::get();
        $text  = '';

        if ( $email instanceof WC_Email ) {
            $object = $email->object;

            if ( $object instanceof WC_Order ) {
                $pill = self::pill_for( $email );

                $text = sprintf(
                    /* translators: 1: pill label such as "Complete", 2: order number. */
                    __( '%1$s — order %2$s', 'lytrod-emails' ),
                    $pill['label'] ?? __( 'Update', 'lytrod-emails' ),
                    $object->get_order_number()
                );
            }
        }

        /**
         * Filter the inbox preview text.
         *
         * @since 1.0.0
         * @param string        $text  Preheader text.
         * @param WC_Email|null $email Email object.
         */
        return (string) apply_filters( 'lytrod_emails_preheader', $text, $email );
    }

    /**
     * Whether an email should render without a billing/shipping address block.
     *
     * Renewals ship nothing, so an address block is noise on them.
     *
     * @param string $id Email id. Defaults to the email currently rendering.
     * @return bool
     */
    public static function hides_addresses( string $id = '' ): bool {
        if ( '' === $id ) {
            $id = Lytrod_Emails_Context::id();
        }

        if ( '' === $id ) {
            return false;
        }

        $ids = array(
            'new_renewal_order',
            'customer_processing_renewal_order',
            'customer_completed_renewal_order',
            'customer_on_hold_renewal_order',
            'customer_renewal_invoice',
            'new_switch_order',
            'customer_completed_switch_order',
            'recipient_completed_renewal_order',
            'gift_recipient_processing_renewal_order',
            'payment_retry',
            'customer_payment_retry',
        );

        /**
         * Filter the email ids that render without an address block.
         *
         * @since 1.0.0
         * @param string[] $ids Email identifiers.
         */
        $ids = (array) apply_filters( 'lytrod_emails_hide_addresses_for', $ids );

        return in_array( $id, $ids, true );
    }

    /**
     * The subscription behind a renewal order, if there is one.
     *
     * `wcs_get_subscriptions_for_order()` defaults `order_type` to
     * `['parent','switch']` and therefore returns nothing for a renewal — always go
     * through the renewal-specific wrapper.
     *
     * @param mixed $order Order.
     * @return WC_Subscription|null
     */
    public static function subscription_for( $order ) {
        if ( ! $order instanceof WC_Order ) {
            return null;
        }

        if ( function_exists( 'wcs_is_subscription' ) && wcs_is_subscription( $order ) ) {
            return $order;
        }

        foreach ( array( 'wcs_get_subscriptions_for_renewal_order', 'wcs_get_subscriptions_for_switch_order', 'wcs_get_subscriptions_for_order' ) as $fn ) {
            if ( ! function_exists( $fn ) ) {
                continue;
            }

            $found = $fn( $order );

            if ( ! empty( $found ) && is_array( $found ) ) {
                return reset( $found );
            }
        }

        return null;
    }

    /**
     * License tier string for an order line item, e.g. "Intellicut Plus 1 year".
     *
     * Tier has 100% coverage on this store: every active subscription carries it as
     * item meta `choose`, with `years` and the variation attribute as fallbacks.
     *
     * @param WC_Order_Item $item Line item.
     * @return string
     */
    public static function tier( $item ): string {
        if ( ! $item instanceof WC_Order_Item ) {
            return '';
        }

        foreach ( array( 'choose', 'years' ) as $key ) {
            $value = $item->get_meta( $key, true );

            if ( ! empty( $value ) && is_scalar( $value ) ) {
                return trim( (string) $value );
            }
        }

        if ( is_callable( array( $item, 'get_variation_id' ) ) && $item->get_variation_id() ) {
            $variation = wc_get_product( $item->get_variation_id() );

            if ( $variation ) {
                $attribute = $variation->get_attribute( 'choose' );

                if ( ! empty( $attribute ) ) {
                    return trim( (string) $attribute );
                }
            }
        }

        return '';
    }

    /**
     * Number of licensed seats for a line item.
     *
     * BUSINESS RULE, NOT DERIVED DATA. Seat entitlement is enforced externally in
     * LimeLM/wyday, and WordPress has no live copy of it. The only seat field that
     * exists here — the `totalactivationslots` ACF field on the `*_wyday` CPTs — is a
     * one-off 2022 import: every row's `post_modified` falls inside a 2-minute window on
     * 2022-02-12, two of its three CPTs are commented out in
     * mu-plugins/customposttypes.php, and nothing reads it. It also disagrees with the
     * tier: of 17 Plus subscriptions it records 1 slot for 9 of them and 3 for only 4,
     * while Base ranges 1-20.
     *
     * So this maps tier => seats by rule and will misstate entitlement for roughly 30
     * subscriptions. Replace the `lytrod_emails_licensed_seats` filter with a real
     * LimeLM lookup when one exists; no template needs to change.
     *
     * @param WC_Order_Item $item  Line item.
     * @param mixed         $order Order the item belongs to.
     * @return int
     */
    public static function licensed_seats( $item, $order = null ): int {
        $tier  = self::tier( $item );
        $seats = ( '' !== $tier && false !== stripos( $tier, 'plus' ) ) ? 3 : 1;

        /**
         * Filter the licensed seat count for a line item.
         *
         * @since 1.0.0
         * @param int           $seats Seats.
         * @param string        $tier  Resolved tier string.
         * @param WC_Order_Item $item  Line item.
         * @param mixed         $order Order.
         */
        return (int) apply_filters( 'lytrod_emails_licensed_seats', $seats, $tier, $item, $order );
    }

    /**
     * Number of years a line item's term covers.
     *
     * Multi-year terms are sold as a sign-up fee with `next_payment` pushed forward N
     * years, so the billing period (`year` / interval 1 for all 283 subscriptions) does
     * not describe the term. The tier string does.
     *
     * @param WC_Order_Item        $item         Line item.
     * @param WC_Subscription|null $subscription Related subscription.
     * @return int
     */
    public static function term_years( $item, $subscription = null ): int {
        $tier = self::tier( $item );

        if ( '' !== $tier && preg_match_all( '/(\d+)\s*Years?\b/i', $tier, $matches ) ) {
            return max( array_map( 'intval', $matches[1] ) );
        }

        if ( $subscription instanceof WC_Order ) {
            $added = (int) $subscription->get_meta( 'years_added', true );

            if ( $added > 0 ) {
                return $added;
            }
        }

        return 1;
    }

    /**
     * License expiry timestamp, matching what My Account displays.
     *
     * Deliberately reads `next_payment` and nothing else:
     *
     * - `_schedule_end` is the string `'0'` on 256 of 283 subscriptions.
     * - `calculated_end_date` is written as expiry PLUS ONE DAY by design
     *   (subscriptions-upgrader public:418-424 and admin:1364-1369), so displaying it is
     *   always off by a day.
     * - `_custom_expiration_date` is stale on 54 of 199 rows — one by four years — and
     *   the live resubscribe flow now deletes all three custom keys outright
     *   (class-su-modal-resubscribe.php:313-317).
     *
     * The site-timezone offset is applied the same way WC_Subscription::format_date_to_display()
     * does it, because 17 subscriptions have a GMT `next_payment` between 00:00 and 07:00
     * and would otherwise print one calendar day early against My Account.
     *
     * @param WC_Subscription|null $subscription Subscription.
     * @return int Unix timestamp in site time, or 0 when unknown.
     */
    public static function expiry_timestamp( $subscription ): int {
        $info = self::expiry_info( $subscription );

        return $info['timestamp'];
    }

    /**
     * Formatted license expiry date.
     *
     * @param WC_Subscription|null $subscription Subscription.
     * @return string Empty string when unknown.
     */
    public static function expiry_date( $subscription ): string {
        $info = self::expiry_info( $subscription );

        return $info['date'];
    }

    /**
     * Resolve the expiry a customer would see, mirroring the My Account template.
     *
     * The email must agree with the site, so this reproduces
     * subscriptions-upgrader/templates/myaccount/my-subscriptions.php branch for branch —
     * including a timezone inconsistency in that template which we match on purpose:
     *
     * - Active / pending: `get_date_to_display('next_payment')`, which is
     *   `next_payment` GMT plus the site offset.
     * - On hold: `WC_Subscription::format_date_to_display()` zeroes `next_payment` for
     *   any non-active status (class-wc-subscription.php:1390) and returns '-', so the
     *   template falls back to raw `_schedule_trial_end` formatted with `DateTime::format()`
     *   and NO offset applied. For subscription 28867 the two differ by a calendar day
     *   (next_payment 00:04 GMT = April 6 local, trial_end = April 7), and April 7 is what
     *   the customer sees.
     * - Expired: raw `_custom_expiration_date`, likewise unconverted.
     * - Pending cancellation: `_schedule_end`.
     *
     * `calculated_end_date` is never consulted — it is written as expiry PLUS ONE DAY by
     * design — and neither is `_schedule_end` outside the pending-cancel branch, where it
     * is the string '0' on 256 of 283 subscriptions.
     *
     * @param WC_Subscription|null $subscription Subscription.
     * @return array{timestamp: int, date: string}
     */
    public static function expiry_info( $subscription ): array {
        $none = array(
            'timestamp' => 0,
            'date'      => '',
        );

        if ( ! $subscription instanceof WC_Order || ! is_callable( array( $subscription, 'get_time' ) ) ) {
            return $none;
        }

        $status = $subscription->get_status();

        // Statuses where My Account reads a raw stored date and does not shift it.
        $raw_sources = array();

        if ( 'on-hold' === $status ) {
            $raw_sources[] = 'trial_end';
        } elseif ( 'expired' === $status ) {
            $raw_sources[] = '_custom_expiration_date';
        } elseif ( 'pending-cancel' === $status ) {
            $raw_sources[] = 'end';
        }

        foreach ( $raw_sources as $source ) {
            $raw = 0 === strpos( $source, '_' )
                ? (string) $subscription->get_meta( $source, true )
                : (string) $subscription->get_date( $source );

            if ( '' === $raw || '0' === $raw ) {
                continue;
            }

            $timestamp = (int) strtotime( $raw . ' UTC' );

            if ( $timestamp > 0 ) {
                // Third argument true: format the stored value as-is, no offset.
                return array(
                    'timestamp' => $timestamp,
                    'date'      => date_i18n( wc_date_format(), $timestamp, true ),
                );
            }
        }

        // Active and everything else: next_payment, then trial_end, in site time.
        foreach ( array( 'next_payment', 'trial_end' ) as $date_type ) {
            $gmt = (int) $subscription->get_time( $date_type, 'gmt' );

            if ( $gmt > 0 ) {
                $timestamp = $gmt + (int) wc_timezone_offset();

                return array(
                    'timestamp' => $timestamp,
                    'date'      => date_i18n( wc_date_format(), $timestamp ),
                );
            }
        }

        return $none;
    }

    /**
     * Human-readable coverage period for a renewed line item.
     *
     * @param WC_Order_Item        $item         Line item.
     * @param mixed                $order        Renewal order.
     * @param WC_Subscription|null $subscription Related subscription.
     * @return string
     */
    public static function coverage_period( $item, $order, $subscription = null ): string {
        $end = self::expiry_timestamp( $subscription );

        if ( ! $end ) {
            return '';
        }

        $start = 0;

        if ( $order instanceof WC_Order ) {
            $date  = $order->get_date_paid() ? $order->get_date_paid() : $order->get_date_created();
            $start = $date ? (int) $date->getOffsetTimestamp() : 0;
        }

        // No usable order date: walk back by the purchased term instead.
        if ( ! $start || $start >= $end ) {
            $years = self::term_years( $item, $subscription );
            $start = (int) strtotime( '-' . $years . ' year', $end );
        }

        $format = wc_date_format();

        return sprintf(
            /* translators: 1: coverage start date, 2: coverage end date. */
            _x( '%1$s – %2$s', 'license coverage period', 'lytrod-emails' ),
            date_i18n( $format, $start ),
            date_i18n( $format, $end )
        );
    }

    /**
     * Display name for a licensed line item.
     *
     * Prefers the `_custom_product_title` meta the site maintains, because the stored
     * `order_item_name` has been rewritten into an anchor with the STAGING hostname baked
     * in by `customize_order_item_title()` (subscriptions-upgrader public:519-577, hooked
     * to `woocommerce_order_item_name`, `woocommerce_order_item_get_name` and
     * `woocommerce_email_order_item_name`). Everything is tag-stripped regardless.
     *
     * @param WC_Order_Item $item  Line item.
     * @param mixed         $order Order.
     * @return string
     */
    public static function product_name( $item, $order = null ): string {
        foreach ( array( $item, $order ) as $source ) {
            if ( ! $source instanceof WC_Data ) {
                continue;
            }

            $title = $source->get_meta( '_custom_product_title', true );

            if ( ! empty( $title ) && is_scalar( $title ) ) {
                return trim( wp_strip_all_tags( (string) $title ) );
            }
        }

        return $item instanceof WC_Order_Item
            ? trim( wp_strip_all_tags( $item->get_name() ) )
            : '';
    }

    /**
     * Customer-facing greeting name for an order.
     *
     * @param mixed $object Order or subscription.
     * @return string
     */
    public static function first_name( $object ): string {
        if ( $object instanceof WC_Order ) {
            $name = $object->get_billing_first_name();

            if ( '' !== $name ) {
                return $name;
            }
        }

        return __( 'there', 'lytrod-emails' );
    }
}
