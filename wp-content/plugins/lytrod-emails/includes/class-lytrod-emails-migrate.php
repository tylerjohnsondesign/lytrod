<?php
/**
 * Carries the live email copy over from the plugins being retired.
 *
 * The copy is the part that cannot be recreated from code: all four reminder bodies and two of
 * the subjects were edited in the admin and no longer match the defaults they shipped with. This
 * reads the effective values from the source plugins and writes them into the new emails'
 * settings.
 *
 * It copies the words, never the on/off switch. Turning these on is a decision for whoever is
 * also turning the old plugins off.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

/**
 * One-time copy migration.
 */
class Lytrod_Emails_Migrate {

    /**
     * Set once the migration has run, so it never fights an administrator's later edits.
     */
    const DONE_OPTION = 'lytrod_emails_copy_migrated';

    /**
     * Legacy reminder id => new email id.
     *
     * @return array<string, string>
     */
    public static function reminder_map(): array {
        return array(
            '30_with_payment'    => 'lytrod_renewal_30_stored',
            '3_with_payment'     => 'lytrod_renewal_3_stored',
            '30_without_payment' => 'lytrod_renewal_30_unstored',
            '3_without_payment'  => 'lytrod_renewal_3_unstored',
        );
    }

    /**
     * Whether the migration has already been recorded as done.
     *
     * @return bool
     */
    public static function is_done(): bool {
        return 'yes' === get_option( self::DONE_OPTION, 'no' );
    }

    /**
     * Gather the copy that would be written.
     *
     * No tag rewriting happens here: the new emails keep every legacy tag working as an alias, so
     * the copy is carried across byte for byte and renders identically.
     *
     * @return array<int, array<string, string>>
     */
    public static function plan(): array {
        $rows = array();

        if ( class_exists( 'LRE_Settings' ) ) {
            foreach ( self::reminder_map() as $legacy_id => $target_id ) {
                $source = LRE_Settings::get( $legacy_id );

                $rows[] = array(
                    'target'  => $target_id,
                    'source'  => 'lre_settings[' . $legacy_id . ']',
                    'subject' => (string) ( $source['subject'] ?? '' ),
                    'body'    => (string) ( $source['body'] ?? '' ),
                );
            }
        }

        if ( class_exists( 'SU_Ended_Email_Settings' ) ) {
            $source = SU_Ended_Email_Settings::get();

            $rows[] = array(
                'target'  => 'lytrod_license_ended',
                'source'  => 'su_ended_email_settings',
                'subject' => (string) ( $source['subject'] ?? '' ),
                'body'    => (string) ( $source['body'] ?? '' ),
            );
        }

        return $rows;
    }

    /**
     * Write the copy into the new emails' settings.
     *
     * @param bool $write Whether to persist.
     * @param bool $force Overwrite a target that already has a body.
     * @return array<int, array<string, string>> Report rows.
     */
    public static function run( bool $write, bool $force = false ): array {
        $report = array();

        foreach ( self::plan() as $row ) {
            $option   = 'woocommerce_' . $row['target'] . '_settings';
            $settings = get_option( $option, array() );
            $settings = is_array( $settings ) ? $settings : array();

            $occupied = '' !== trim( (string) ( $settings['body'] ?? '' ) );

            if ( $occupied && ! $force ) {
                $report[] = array(
                    'target' => $row['target'],
                    'source' => $row['source'],
                    'action' => 'skipped — already has a body',
                );

                continue;
            }

            if ( '' === trim( $row['subject'] ) && '' === trim( $row['body'] ) ) {
                $report[] = array(
                    'target' => $row['target'],
                    'source' => $row['source'],
                    'action' => 'skipped — nothing to copy',
                );

                continue;
            }

            if ( $write ) {
                $settings['subject'] = $row['subject'];
                $settings['body']    = $row['body'];

                // Never carried over. Enabling these is a deliberate act, taken together with
                // deactivating the plugin that currently sends them.
                if ( ! isset( $settings['enabled'] ) ) {
                    $settings['enabled'] = 'no';
                }

                update_option( $option, $settings );
            }

            $report[] = array(
                'target' => $row['target'],
                'source' => $row['source'],
                'action' => $write ? ( $occupied ? 'overwritten' : 'copied' ) : 'would copy',
            );
        }

        if ( $write ) {
            update_option( self::DONE_OPTION, 'yes' );
        }

        return $report;
    }
}
