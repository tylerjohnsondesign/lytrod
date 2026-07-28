<?php
/**
 * Lytrod email footer — closes the content card and renders the blue footer band.
 *
 * Closes email-header.php's markup in the same nesting order. The `$email` object
 * is read from Lytrod_Emails_Context rather than the template scope:
 * WC_Emails::email_footer() declares zero parameters and calls wc_get_template()
 * with no args (class-wc-emails.php:385), so the object that per-email templates
 * pass to `do_action( 'woocommerce_email_footer', $email )` never arrives here.
 *
 * The `woocommerce_email_footer_text` option is deliberately not used. It is stored
 * as an empty string on this install, which rendered an empty but still-padded
 * table cell at the bottom of every email.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

$primary          = Lytrod_Emails_Brand::c( 'primary' );
$footer_links     = Lytrod_Emails_Brand::footer_links();
$footer_note      = Lytrod_Emails_Brand::footer_note();
$logo_white_width = Lytrod_Emails_Brand::logo_white_width();

?>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            <!-- End Content -->
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- End Body -->
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="top" style="padding-top:16px;">
                                    <!-- Footer -->
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_footer" role="presentation" bgcolor="<?php echo esc_attr( $primary ); ?>">
                                        <tr>
                                            <td valign="top">
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                                                    <tr>
                                                        <td colspan="2" valign="middle" id="credit" align="center" bgcolor="<?php echo esc_attr( $primary ); ?>">
                                                            <div class="lytrod-footer-logo">
                                                                <img src="<?php echo esc_url( Lytrod_Emails_Brand::logo_white_url() ); ?>"
                                                                    alt="<?php echo esc_attr( Lytrod_Emails_Brand::store_name() ); ?>"
                                                                    width="<?php echo esc_attr( $logo_white_width ); ?>"
                                                                    style="display:block;margin:0 auto 18px;border:0;width:<?php echo esc_attr( $logo_white_width ); ?>px;max-width:<?php echo esc_attr( $logo_white_width ); ?>px;height:auto;" />
                                                            </div>

                                                            <?php if ( ! empty( $footer_links ) ) : ?>
                                                                <p class="lytrod-footer-links">
                                                                    <?php
                                                                    $last = count( $footer_links ) - 1;

                                                                    foreach ( $footer_links as $index => $link ) {
                                                                        printf(
                                                                            '<a href="%1$s">%2$s</a>',
                                                                            esc_url( $link['url'] ),
                                                                            esc_html( $link['label'] )
                                                                        );

                                                                        if ( $index !== $last ) {
                                                                            echo '<span class="lytrod-footer-sep">&middot;</span>';
                                                                        }
                                                                    }
                                                                    ?>
                                                                </p>
                                                            <?php endif; ?>

                                                            <?php if ( '' !== $footer_note ) : ?>
                                                                <p class="lytrod-footer-note">
                                                                    <?php echo wp_kses_post( $footer_note ); ?>
                                                                </p>
                                                            <?php endif; ?>

                                                            <p class="lytrod-footer-note" style="margin-top:8px !important;">
                                                                <?php
                                                                printf(
                                                                    /* translators: %s: Current year. */
                                                                    esc_html__( '© %s Lytrod Software. This is a transactional message about your account.', 'lytrod-emails' ),
                                                                    esc_html( gmdate( 'Y' ) )
                                                                );
                                                                ?>
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Footer -->
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td><!-- Deliberately empty to support consistent sizing and layout across multiple email clients. --></td>
            </tr>
        </table>
    </body>
</html>
