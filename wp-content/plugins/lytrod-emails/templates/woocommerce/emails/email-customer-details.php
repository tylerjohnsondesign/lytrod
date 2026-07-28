<?php
/**
 * Additional customer detail fields, contributed by plugins.
 *
 * @package Lytrod_Emails
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $fields ) ) {
    return;
}
?>

<h2 class="lytrod-section-title"><?php esc_html_e( 'Customer details', 'lytrod-emails' ); ?></h2>

<ul class="additional-fields font-family">
    <?php foreach ( $fields as $field ) : ?>
        <li>
            <b class="address-title"><?php echo wp_kses_post( $field['label'] ); ?></b><br />
            <span class="text"><?php echo wp_kses_post( $field['value'] ); ?></span>
        </li>
    <?php endforeach; ?>
</ul>
