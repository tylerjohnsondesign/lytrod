<?php
/**
 * Registers a daily WP-Cron event that finds subscriptions expiring
 * in exactly 30 days or 3 days and fires the mailer.
 */
class LRE_Scheduler {

    const CRON_HOOK = 'lre_daily_check';

    public static function init() {
        add_action( self::CRON_HOOK, [ __CLASS__, 'run_check' ] );
    }

    public static function schedule_cron() {
        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            // Schedule at 08:00 server time each day
            $start = strtotime( 'today 08:00:00' );
            if ( $start < time() ) {
                $start = strtotime( 'tomorrow 08:00:00' );
            }
            wp_schedule_event( $start, 'daily', self::CRON_HOOK );
        }
    }

    public static function unschedule_cron() {
        $ts = wp_next_scheduled( self::CRON_HOOK );
        if ( $ts ) {
            wp_unschedule_event( $ts, self::CRON_HOOK );
        }
    }

    /**
     * Main daily check: find subscriptions expiring in 30 or 3 days.
     */
    public static function run_check() {
        $windows = [ 30, 3 ];

        foreach ( $windows as $days ) {
            $target_date = gmdate( 'Y-m-d', strtotime( "+{$days} days" ) );
            self::process_expiring_subscriptions( $days, $target_date );
        }
    }

    private static function process_expiring_subscriptions( int $days, string $target_date ) {
        // Query subscriptions whose next_payment or end date falls on target_date
        $subscriptions = wcs_get_subscriptions( [
            'subscription_status' => [ 'active', 'pending-cancel' ],
            'subscriptions_per_page' => -1,
            'meta_query' => [
                [
                    'key'     => '_schedule_next_payment',
                    'value'   => [ $target_date . ' 00:00:00', $target_date . ' 23:59:59' ],
                    'compare' => 'BETWEEN',
                    'type'    => 'DATETIME',
                ],
            ],
        ] );

        // Also check end date for subscriptions without auto-renewal
        $end_subscriptions = wcs_get_subscriptions( [
            'subscription_status' => [ 'active', 'pending-cancel' ],
            'subscriptions_per_page' => -1,
            'meta_query' => [
                [
                    'key'     => '_schedule_end',
                    'value'   => [ $target_date . ' 00:00:00', $target_date . ' 23:59:59' ],
                    'compare' => 'BETWEEN',
                    'type'    => 'DATETIME',
                ],
            ],
        ] );

        $all = array_merge( $subscriptions, $end_subscriptions );

        foreach ( $all as $subscription ) {
            // Prevent duplicate sends within same day
            $sent_key = "lre_sent_{$days}_{$subscription->get_id()}_{$target_date}";
            if ( get_transient( $sent_key ) ) {
                continue;
            }

            LRE_Mailer::send_for_subscription( $subscription, $days );
            set_transient( $sent_key, 1, DAY_IN_SECONDS * 2 );
        }
    }
}
