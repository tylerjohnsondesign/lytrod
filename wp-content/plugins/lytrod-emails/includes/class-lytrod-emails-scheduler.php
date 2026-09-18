<?php
/**
 * Decides when the licence lifecycle emails go out.
 *
 * A once-daily sweep picks up renewal reminders; the ended email rides status transitions. Both
 * stand down entirely while the plugins they replace are still installed, so the two can never
 * both mail the same customer.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/**
 * Reminder scheduling and dispatch.
 */
class Lytrod_Emails_Scheduler {

    /**
     * Recurring action hook.
     */
    const HOOK = 'lytrod_emails_daily_reminders';

    /**
     * Action Scheduler group.
     */
    const GROUP = 'lytrod-emails';

    /**
     * How many days before the renewal each reminder fires.
     *
     * Order does not matter here; the sweep always evaluates the nearest window first so a
     * subscription can only ever match one per run.
     */
    const WINDOWS = array( 30, 3 );

    /**
     * How far past its window a reminder may still be sent, in days.
     *
     * This exists only to cover a missed run. Action Scheduler makes those rare, so one day is
     * enough — and every extra day of grace is another day on which a subscription with no
     * dedupe marker would be mailed.
     */
    const GRACE_DAYS = 1;

    /**
     * Ceiling on how many subscriptions one sweep will load.
     *
     * There are fewer than 300 today, so this never trips. It is here because
     * `wcs_get_subscriptions()` hydrates every match into a full object before returning, and an
     * unbounded query in code that outlives its dataset is how that becomes an out-of-memory.
     */
    const MAX_SCAN = 2000;

    /**
     * Wire up.
     *
     * @return void
     */
    public static function init(): void {
        add_action( self::HOOK, array( __CLASS__, 'run' ) );
        add_action( 'init', array( __CLASS__, 'maybe_schedule' ), 20 );

        // The ended email rides the transitions that actually cost the customer access.
        add_action( 'woocommerce_subscription_status_active_to_on-hold', array( __CLASS__, 'maybe_send_ended' ), 10, 1 );
        add_action( 'woocommerce_subscription_status_active_to_expired', array( __CLASS__, 'maybe_send_ended' ), 10, 1 );

        // Ours replaces both of WooCommerce Subscriptions' own endings, but only once it is live.
        add_filter( 'woocommerce_email_enabled_customer_on_hold_subscription', array( __CLASS__, 'suppress_wcs_ending' ), 20 );
        add_filter( 'woocommerce_email_enabled_customer_expired_subscription', array( __CLASS__, 'suppress_wcs_ending' ), 20 );

        /*
         * WooCommerce Subscriptions 8.2 ships its own reminder system, currently switched off but
         * already configured with a 3-day offset — switching it on would duplicate our 3-day
         * reminder exactly. Pin it off while ours are running, using the same technique
         * Lytrod_Emails_Compat uses for the block email editor.
         */
        add_filter( 'pre_option_woocommerce_subscriptions_customer_notifications_enabled', array( __CLASS__, 'pin_wcs_notifications' ) );
    }

    /**
     * Ensure the daily action exists.
     *
     * Scheduled through Action Scheduler rather than WP-Cron: `DISABLE_WP_CRON` is not set here,
     * so WP-Cron is request-triggered, and behind full-page caching a quiet day can pass without
     * a single request reaching PHP. Action Scheduler already runs on this install every minute
     * because WooCommerce Subscriptions depends on it for the renewals themselves.
     *
     * Re-checked on every `init` rather than only on activation, so a lost schedule heals itself.
     *
     * @return void
     */
    public static function maybe_schedule(): void {
        if ( ! function_exists( 'as_schedule_recurring_action' ) || ! function_exists( 'as_next_scheduled_action' ) ) {
            return;
        }

        if ( as_next_scheduled_action( self::HOOK, null, self::GROUP ) ) {
            return;
        }

        as_schedule_recurring_action( self::next_run_timestamp(), DAY_IN_SECONDS, self::HOOK, array(), self::GROUP );
    }

    /**
     * Next 08:00 in the store's own timezone.
     *
     * The plugin this replaces intended 08:00 local but passed a UTC timestamp, so its reminders
     * have been going out at 1am store time.
     *
     * @return int
     */
    private static function next_run_timestamp(): int {
        $tz  = wp_timezone();
        $now = new DateTimeImmutable( 'now', $tz );
        $run = $now->setTime( 8, 0, 0 );

        if ( $run <= $now ) {
            $run = $run->modify( '+1 day' );
        }

        return $run->getTimestamp();
    }

    /**
     * Whether the plugin that currently sends the reminders is still installed.
     *
     * @return bool
     */
    public static function legacy_reminders_active(): bool {
        return class_exists( 'LRE_Scheduler' );
    }

    /**
     * Whether the plugin that currently sends the ended email is still installed.
     *
     * @return bool
     */
    public static function legacy_ended_active(): bool {
        return class_exists( 'SU_Ended_Email_Mailer' );
    }

    /**
     * Whether a given email id is switched on, read straight from options.
     *
     * Deliberately avoids `WC()->mailer()`, which would instantiate every email class — including
     * from inside filters that the mailer itself is applying.
     *
     * @param string $email_id Email id.
     * @return bool
     */
    public static function is_email_enabled( string $email_id ): bool {
        $settings = get_option( 'woocommerce_' . $email_id . '_settings', array() );

        return is_array( $settings ) && isset( $settings['enabled'] ) && 'yes' === $settings['enabled'];
    }

    /**
     * The four reminder email ids.
     *
     * Hardcoded rather than read off the instantiated classes so that callers running inside an
     * option filter never have to touch `WC()->mailer()`.
     *
     * @return string[]
     */
    public static function reminder_ids(): array {
        return array(
            'lytrod_renewal_30_stored',
            'lytrod_renewal_3_stored',
            'lytrod_renewal_30_unstored',
            'lytrod_renewal_3_unstored',
        );
    }

    /**
     * Whether any reminder is switched on.
     *
     * Reads options directly. Going through `WC()->mailer()` here would be re-entrant: this runs
     * from a `pre_option_` filter, and WooCommerce Subscriptions reads that option while
     * WC_Emails::init() is still applying `woocommerce_email_classes` — so the mailer would hand
     * back a half-built roster.
     *
     * @return bool
     */
    public static function reminders_enabled(): bool {
        foreach ( self::reminder_ids() as $id ) {
            if ( self::is_email_enabled( $id ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Which class handles which window and payment state.
     *
     * @return array<int, array{stored: string, unstored: string}>
     */
    public static function window_map(): array {
        return array(
            30 => array(
                'stored'   => 'Lytrod_Emails_Renewal_30_Stored',
                'unstored' => 'Lytrod_Emails_Renewal_30_Unstored',
            ),
            3  => array(
                'stored'   => 'Lytrod_Emails_Renewal_3_Stored',
                'unstored' => 'Lytrod_Emails_Renewal_3_Unstored',
            ),
        );
    }

    /**
     * Fetch an instantiated email by class name.
     *
     * @param string $class Class name.
     * @return WC_Email|null
     */
    public static function email( string $class ) {
        $emails = WC()->mailer()->get_emails();

        return isset( $emails[ $class ] ) ? $emails[ $class ] : null;
    }

    /**
     * The dedupe marker for one window.
     *
     * @param int $days Window.
     * @return string
     */
    public static function marker_key( int $days ): string {
        return '_lytrod_reminder_' . $days;
    }

    /**
     * Which window a subscription currently falls in, nearest first.
     *
     * Returns 0 when it falls in none. Evaluating nearest-first guarantees a subscription can
     * match at most one window per sweep, which matters the moment the windows are ever
     * configured closely enough to overlap.
     *
     * @param WC_Subscription $subscription Subscription.
     * @return int
     */
    public static function window_for( $subscription ): int {
        $type = Lytrod_Emails_Subscription_Email::renewal_date_type( $subscription );

        if ( '' === $type ) {
            return 0;
        }

        $seconds = $subscription->get_time( $type ) - time();

        if ( $seconds <= 0 ) {
            return 0;
        }

        $days    = $seconds / DAY_IN_SECONDS;
        $windows = self::windows();
        $grace   = self::grace();

        foreach ( $windows as $window ) {
            if ( $days <= $window && $days > ( $window - $grace ) ) {
                return (int) $window;
            }
        }

        return 0;
    }

    /**
     * The reminder windows, nearest first.
     *
     * Sorted ascending so a subscription can only ever match the closest one, which is what keeps
     * two reminders off the same customer on the same day if the windows are ever configured
     * close enough together to overlap.
     *
     * @return int[]
     */
    public static function windows(): array {
        /**
         * Filter the reminder windows, in days before renewal.
         *
         * @since 1.1.0
         * @param int[] $windows Windows.
         */
        $windows = array_map( 'intval', (array) apply_filters( 'lytrod_emails_reminder_windows', self::WINDOWS ) );

        $windows = array_values( array_unique( array_filter( $windows, static function ( $w ) {
            return $w > 0;
        } ) ) );

        sort( $windows );

        return $windows;
    }

    /**
     * How many days past its window a reminder may still go out.
     *
     * Clamped below the smallest gap between adjacent windows. Without that clamp, widening the
     * grace far enough would let one subscription match two windows in a single sweep.
     *
     * @return float
     */
    public static function grace(): float {
        /**
         * Filter the catch-up grace, in days.
         *
         * @since 1.1.0
         * @param float $grace Grace in days.
         */
        $grace   = (float) apply_filters( 'lytrod_emails_reminder_grace', self::GRACE_DAYS );
        $windows = self::windows();
        $gap     = INF;

        for ( $i = 1, $count = count( $windows ); $i < $count; $i++ ) {
            $gap = min( $gap, $windows[ $i ] - $windows[ $i - 1 ] );
        }

        if ( is_finite( $gap ) ) {
            $grace = min( $grace, $gap );
        }

        return max( 0.0, $grace );
    }

    /**
     * Subscriptions worth considering.
     *
     * Only `active` and `pending-cancel`. `on-hold` is excluded deliberately: on this store
     * on-hold means the licence has already stopped, and the customer has had the ended email —
     * telling them their card is about to be charged would contradict it.
     *
     * @return WC_Subscription[]
     */
    private static function scan(): array {
        if ( ! function_exists( 'wcs_get_subscriptions' ) ) {
            return array();
        }

        return wcs_get_subscriptions(
            array(
                'subscriptions_per_page' => self::MAX_SCAN,
                'subscription_status'    => array( 'active', 'pending-cancel' ),
            )
        );
    }

    /**
     * Work out what a single subscription is owed, without sending anything.
     *
     * @param WC_Subscription $subscription Subscription.
     * @return array|null {window, class, email, renewal_day, already} or null when nothing is due.
     */
    public static function plan_for( $subscription ): ?array {
        $window = self::window_for( $subscription );

        if ( ! $window ) {
            return null;
        }

        $map = self::window_map();

        if ( ! isset( $map[ $window ] ) ) {
            return null;
        }

        $stored = Lytrod_Emails_Renewal_Reminder::has_stored_payment( $subscription );
        $class  = $stored ? $map[ $window ]['stored'] : $map[ $window ]['unstored'];
        $email  = self::email( $class );

        if ( ! $email ) {
            return null;
        }

        $type        = Lytrod_Emails_Subscription_Email::renewal_date_type( $subscription );
        $renewal_day = gmdate( 'Y-m-d', $subscription->get_time( $type ) );

        return array(
            'window'      => $window,
            'class'       => $class,
            'email'       => $email,
            'stored'      => $stored,
            'renewal_day' => $renewal_day,
            'already'     => (string) $subscription->get_meta( self::marker_key( $window ), true ) === $renewal_day,
        );
    }

    /**
     * The daily sweep.
     *
     * @param bool $force Bypass the interlock (testing only).
     * @return array{sent: int, skipped: int, failed: int, blocked: bool}
     */
    public static function run( $force = false ): array {
        $result = array(
            'sent'    => 0,
            'skipped' => 0,
            'failed'  => 0,
            'blocked' => false,
        );

        if ( ! $force && self::legacy_reminders_active() ) {
            $result['blocked'] = true;
            self::log( 'Sweep skipped: lytrod-renewal-emails is still active and owns these reminders.' );

            return $result;
        }

        foreach ( self::scan() as $subscription ) {
            $plan = self::plan_for( $subscription );

            if ( ! $plan || $plan['already'] ) {
                ++$result['skipped'];
                continue;
            }

            if ( ! $plan['email']->is_enabled() ) {
                ++$result['skipped'];
                continue;
            }

            /*
             * Claim the window before sending, not after.
             *
             * WC_Email::trigger() is void in most implementations and wp_mail() can report
             * success for mail that was never delivered — the `disable-emails` plugin active on
             * this install returns true unconditionally. Marking afterwards on a truthy result
             * therefore either never marks (and re-sends every day the window holds) or marks on
             * a phantom send. Eight subscriptions already carry the other plugin's marker from
             * exactly that second failure. Claiming first means a hard failure costs one missed
             * email rather than a repeating one, and failures are logged separately below.
             */
            self::claim( $subscription, $plan['window'], $plan['renewal_day'] );

            if ( $plan['email']->trigger( $subscription->get_id() ) ) {
                ++$result['sent'];
            } else {
                ++$result['failed'];
                self::log( sprintf( 'Reminder %s failed for subscription #%d.', $plan['class'], $subscription->get_id() ) );
            }
        }

        return $result;
    }

    /**
     * Record that a window has been handled for a given renewal.
     *
     * Keyed on the renewal's UTC calendar day rather than its exact datetime, so that an admin
     * nudging the time — or WooCommerce Subscriptions rewriting it on a retry — does not look
     * like a fresh renewal and re-send.
     *
     * @param WC_Subscription $subscription Subscription.
     * @param int             $window       Window in days.
     * @param string          $renewal_day  Y-m-d, UTC.
     * @return void
     */
    private static function claim( $subscription, int $window, string $renewal_day ): void {
        $subscription->update_meta_data( self::marker_key( $window ), $renewal_day );
        $subscription->save_meta_data();
    }

    /**
     * Mark everything currently in-window as already handled, sending nothing.
     *
     * Run once at cutover. Without it the first live sweep would re-mail every customer the old
     * plugin contacted in the preceding few days, because none of them carry our marker yet.
     *
     * @return int Number of subscriptions marked.
     */
    public static function backfill(): int {
        $marked = 0;

        foreach ( self::scan() as $subscription ) {
            $plan = self::plan_for( $subscription );

            if ( ! $plan || $plan['already'] ) {
                continue;
            }

            self::claim( $subscription, $plan['window'], $plan['renewal_day'] );
            ++$marked;
        }

        return $marked;
    }

    /**
     * Send the ended email on a status transition.
     *
     * @param WC_Subscription $subscription Subscription.
     * @return void
     */
    public static function maybe_send_ended( $subscription ): void {
        if ( self::legacy_ended_active() ) {
            return;
        }

        if ( ! $subscription instanceof WC_Subscription ) {
            return;
        }

        $email = self::email( 'Lytrod_Emails_License_Ended' );

        if ( ! $email || ! $email->is_enabled() ) {
            return;
        }

        if ( Lytrod_Emails_License_Ended::already_notified( $subscription ) ) {
            return;
        }

        Lytrod_Emails_License_Ended::mark_notified( $subscription );

        if ( ! $email->trigger( $subscription->get_id() ) ) {
            self::log( sprintf( 'Ended email failed for subscription #%d.', $subscription->get_id() ) );
        }
    }

    /**
     * Turn off WooCommerce Subscriptions' own on-hold and expired customer emails.
     *
     * Only once ours is actually doing the job — otherwise disabling everything would leave the
     * customer with no message at all.
     *
     * @param bool $enabled Incoming.
     * @return bool
     */
    public static function suppress_wcs_ending( $enabled ) {
        if ( self::legacy_ended_active() ) {
            // The other plugin already suppresses these; leave its arrangement alone.
            return $enabled;
        }

        return self::is_email_enabled( 'lytrod_license_ended' ) ? false : $enabled;
    }

    /**
     * Hold WooCommerce Subscriptions' notification system off while ours is live.
     *
     * @param mixed $value Short-circuit value.
     * @return mixed
     */
    public static function pin_wcs_notifications( $value ) {
        if ( defined( 'LYTROD_EMAILS_ALLOW_WCS_NOTIFICATIONS' ) && LYTROD_EMAILS_ALLOW_WCS_NOTIFICATIONS ) {
            return $value;
        }

        return self::reminders_enabled() ? 'no' : $value;
    }

    /**
     * Log to the WooCommerce logger under our own source.
     *
     * @param string $message Message.
     * @return void
     */
    public static function log( string $message ): void {
        if ( function_exists( 'wc_get_logger' ) ) {
            wc_get_logger()->info( $message, array( 'source' => 'lytrod-emails' ) );
        }
    }
}
