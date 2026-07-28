<?php
/**
 * Downloadable licenses and installers.
 *
 * This is the software-delivery moment of the whole email set, so it is laid out as
 * one card per download with a real download button rather than as a three-column
 * table of links.
 *
 * `$columns` is still honoured for the `woocommerce_email_downloads_column_*`
 * actions, so third-party columns keep rendering — they are appended to the meta
 * line beneath each download.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

$plain_text = $plain_text ?? false;
$columns    = $columns ?? array();
?>

<h2 class="lytrod-section-title woocommerce-order-downloads__title"><?php esc_html_e( 'Your downloads', 'lytrod-emails' ); ?></h2>

<?php foreach ( $downloads as $download ) : ?>
    <table class="lytrod-panel" cellspacing="0" cellpadding="0" width="100%" border="0" role="presentation">
        <tr>
            <td class="lytrod-panel-cell">
                <p class="lytrod-panel-text" style="margin-bottom:4px;">
                    <?php if ( ! empty( $download['product_id'] ) && get_permalink( $download['product_id'] ) ) : ?>
                        <a class="link" href="<?php echo esc_url( get_permalink( $download['product_id'] ) ); ?>" style="text-decoration:none;"><strong><?php echo wp_kses_post( $download['product_name'] ); ?></strong></a>
                    <?php else : ?>
                        <strong><?php echo wp_kses_post( $download['product_name'] ); ?></strong>
                    <?php endif; ?>
                </p>

                <?php
                $meta = array();

                if ( ! empty( $download['access_expires'] ) ) {
                    $meta[] = sprintf(
                        /* translators: %s: expiry date. */
                        esc_html__( 'Access expires %s', 'lytrod-emails' ),
                        esc_html( date_i18n( get_option( 'date_format' ), strtotime( $download['access_expires'] ) ) )
                    );
                } else {
                    $meta[] = esc_html__( 'Access never expires', 'lytrod-emails' );
                }

                if ( isset( $download['downloads_remaining'] ) && '' !== $download['downloads_remaining'] ) {
                    $meta[] = sprintf(
                        /* translators: %s: number of downloads left. */
                        esc_html__( '%s downloads remaining', 'lytrod-emails' ),
                        esc_html( $download['downloads_remaining'] )
                    );
                }
                ?>

                <?php if ( ! empty( $meta ) ) : ?>
                    <p class="lytrod-meta" style="margin:0 0 14px;">
                        <?php echo implode( ' &nbsp;&middot;&nbsp; ', $meta ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped when built. ?>
                    </p>
                <?php endif; ?>

                <?php
                if ( ! empty( $download['download_url'] ) ) {
                    $label = ! empty( $download['download_name'] ) ? $download['download_name'] : __( 'Download', 'lytrod-emails' );

                    echo lytrod_emails_button( (string) $label, (string) $download['download_url'], 'secondary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally.
                }

                /*
                 * Let third-party columns still render. Core's own three column ids
                 * are handled above, so only added columns produce output here.
                 */
                foreach ( $columns as $column_id => $column_name ) {
                    if ( in_array( $column_id, array( 'download-product', 'download-file', 'download-expires' ), true ) ) {
                        continue;
                    }

                    if ( ! has_action( 'woocommerce_email_downloads_column_' . $column_id ) ) {
                        continue;
                    }

                    echo '<p class="lytrod-meta" style="margin:8px 0 0;"><strong>' . esc_html( $column_name ) . ':</strong> ';
                    /** This action is documented in woocommerce/templates/emails/email-downloads.php */
                    do_action( 'woocommerce_email_downloads_column_' . $column_id, $download, $plain_text );
                    echo '</p>';
                }
                ?>
            </td>
        </tr>
    </table>
<?php endforeach; ?>
