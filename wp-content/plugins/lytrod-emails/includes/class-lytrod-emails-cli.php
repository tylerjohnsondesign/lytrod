<?php
/**
 * WP-CLI for the licence lifecycle emails.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/**
 * `wp lytrod-emails`
 */
class Lytrod_Emails_CLI {

    /**
     * Register.
     *
     * @return void
     */
    public static function init(): void {
        WP_CLI::add_command( 'lytrod-emails', __CLASS__ );
    }

    /**
     * Whether an email's body has been customised away from its shipped default.
     *
     * WC_Settings_API::get_option() falls back to the form field default, so the stored value is
     * never empty — the only meaningful comparison is against the default itself.
     *
     * @param WC_Email|null $email Email.
     * @return string
     */
    private static function body_state( $email ): string {
        if ( ! $email instanceof Lytrod_Emails_Subscription_Email ) {
            return '-';
        }

        return trim( (string) $email->get_option( 'body', '' ) ) === trim( $email->get_default_body() )
            ? 'default'
            : 'customised';
    }

    /**
     * Show what is wired up and what is still owned by the old plugins.
     *
     * @return void
     */
    public function status(): void {
        $rows = array();

        foreach ( Lytrod_Emails_Scheduler::window_map() as $days => $variants ) {
            foreach ( $variants as $variant => $class ) {
                $email = Lytrod_Emails_Scheduler::email( $class );

                $rows[] = array(
                    'email'   => $email ? $email->id : $class,
                    'when'    => $days . ' days out',
                    'card'    => 'stored' === $variant ? 'on file' : 'none',
                    'enabled' => $email && $email->is_enabled() ? 'yes' : 'no',
                    'body'    => self::body_state( $email ),
                );
            }
        }

        $ended = Lytrod_Emails_Scheduler::email( 'Lytrod_Emails_License_Ended' );

        $rows[] = array(
            'email'   => $ended ? $ended->id : 'lytrod_license_ended',
            'when'    => 'on suspend/expire',
            'card'    => '-',
            'enabled' => $ended && $ended->is_enabled() ? 'yes' : 'no',
            'body'    => self::body_state( $ended ),
        );

        WP_CLI\Utils\format_items( 'table', $rows, array( 'email', 'when', 'card', 'enabled', 'body' ) );

        WP_CLI::log( '' );

        if ( Lytrod_Emails_Scheduler::legacy_reminders_active() ) {
            WP_CLI::warning( 'lytrod-renewal-emails is active and still owns the four reminders. The sweep will not send while it is.' );
        } else {
            WP_CLI::success( 'lytrod-renewal-emails is gone. The reminder sweep is live.' );
        }

        if ( Lytrod_Emails_Scheduler::legacy_ended_active() ) {
            WP_CLI::warning( 'subscriptions-upgrader is active and still owns the ended email.' );
        } else {
            WP_CLI::success( 'subscriptions-upgrader is gone. The ended email is live.' );
        }

        if ( function_exists( 'as_next_scheduled_action' ) ) {
            $next = as_next_scheduled_action( Lytrod_Emails_Scheduler::HOOK, null, Lytrod_Emails_Scheduler::GROUP );

            WP_CLI::log(
                $next
                    ? 'Next sweep: ' . get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $next ), 'Y-m-d H:i:s' ) . ' (site time)'
                    : 'Next sweep: NOT SCHEDULED'
            );
        }

        if ( Lytrod_Emails_Migrate::is_done() ) {
            WP_CLI::log( 'Copy migration: done.' );
        } else {
            WP_CLI::log( 'Copy migration: not yet run — see `wp lytrod-emails migrate-copy`.' );
        }
    }

    /**
     * Copy the live subject and body from the plugins being retired.
     *
     * ## OPTIONS
     *
     * [--write]
     * : Persist. Without it, reports only.
     *
     * [--force]
     * : Overwrite a target that already has a body.
     *
     * @param array $args       Positional.
     * @param array $assoc_args Flags.
     * @return void
     *
     * @subcommand migrate-copy
     */
    public function migrate_copy( $args, $assoc_args ): void {
        $write = isset( $assoc_args['write'] );
        $force = isset( $assoc_args['force'] );

        $report = Lytrod_Emails_Migrate::run( $write, $force );

        if ( ! $report ) {
            WP_CLI::warning( 'Nothing to migrate — neither source plugin is active.' );

            return;
        }

        WP_CLI\Utils\format_items( 'table', $report, array( 'target', 'source', 'action' ) );

        if ( $write ) {
            WP_CLI::success( 'Copy migrated. The emails remain disabled until you enable them.' );
        } else {
            WP_CLI::log( 'Dry run. Re-run with --write to apply.' );
        }
    }

    /**
     * Show, or send, the reminders due right now.
     *
     * ## OPTIONS
     *
     * [--write]
     * : Actually send. Without it, reports who would receive what.
     *
     * [--force]
     * : Bypass the interlock that stands down while lytrod-renewal-emails is active.
     *
     * @param array $args       Positional.
     * @param array $assoc_args Flags.
     * @return void
     *
     * @subcommand send-reminders
     */
    public function send_reminders( $args, $assoc_args ): void {
        $write = isset( $assoc_args['write'] );
        $force = isset( $assoc_args['force'] );

        if ( ! $force && Lytrod_Emails_Scheduler::legacy_reminders_active() ) {
            WP_CLI::warning( 'lytrod-renewal-emails is still active, so the sweep stands down. Use --force to test anyway.' );

            if ( ! $write ) {
                WP_CLI::log( 'Listing what it would send if it were live:' );
            } else {
                return;
            }
        }

        if ( $write ) {
            $result = Lytrod_Emails_Scheduler::run( $force );

            WP_CLI::log( sprintf( 'sent %d, skipped %d, failed %d', $result['sent'], $result['skipped'], $result['failed'] ) );

            if ( $result['blocked'] ) {
                WP_CLI::warning( 'Blocked by the interlock; nothing was sent.' );
            } else {
                WP_CLI::success( 'Sweep complete.' );
            }

            return;
        }

        $rows = array();

        foreach ( wcs_get_subscriptions(
            array(
                'subscriptions_per_page' => Lytrod_Emails_Scheduler::MAX_SCAN,
                'subscription_status'    => array( 'active', 'pending-cancel' ),
            )
        ) as $subscription ) {
            $plan = Lytrod_Emails_Scheduler::plan_for( $subscription );

            if ( ! $plan ) {
                continue;
            }

            $rows[] = array(
                'subscription' => $subscription->get_id(),
                'customer'     => $subscription->get_billing_email(),
                'window'       => $plan['window'] . 'd',
                'card'         => $plan['stored'] ? 'on file' : 'none',
                'email'        => $plan['email']->id,
                'renews'       => $plan['renewal_day'],
                'action'       => $plan['already'] ? 'already sent' : ( $plan['email']->is_enabled() ? 'WOULD SEND' : 'email disabled' ),
            );
        }

        if ( ! $rows ) {
            WP_CLI::success( 'Nothing is due in any window right now.' );

            return;
        }

        WP_CLI\Utils\format_items( 'table', $rows, array( 'subscription', 'customer', 'window', 'card', 'email', 'renews', 'action' ) );
        WP_CLI::log( 'Dry run. Re-run with --write to send.' );
    }

    /**
     * Mark everything currently in-window as already reminded, without sending.
     *
     * Run this at cutover, immediately before enabling the new emails. Without it the first sweep
     * re-mails everyone the old plugin contacted in the last few days.
     *
     * ## OPTIONS
     *
     * [--write]
     * : Persist the markers.
     *
     * @param array $args       Positional.
     * @param array $assoc_args Flags.
     * @return void
     */
    public function backfill( $args, $assoc_args ): void {
        if ( ! isset( $assoc_args['write'] ) ) {
            $due = 0;

            foreach ( wcs_get_subscriptions(
                array(
                    'subscriptions_per_page' => Lytrod_Emails_Scheduler::MAX_SCAN,
                    'subscription_status'    => array( 'active', 'pending-cancel' ),
                )
            ) as $subscription ) {
                $plan = Lytrod_Emails_Scheduler::plan_for( $subscription );

                if ( $plan && ! $plan['already'] ) {
                    ++$due;
                }
            }

            WP_CLI::log( sprintf( '%d subscription(s) would be marked as already reminded.', $due ) );
            WP_CLI::log( 'Dry run. Re-run with --write to apply.' );

            return;
        }

        $marked = Lytrod_Emails_Scheduler::backfill();

        WP_CLI::success( sprintf( 'Marked %d subscription(s). The next sweep will send nothing for them.', $marked ) );
    }
}
