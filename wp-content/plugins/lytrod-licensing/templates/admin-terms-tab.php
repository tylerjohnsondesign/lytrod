<?php
/**
 * Product data → Term Discounts.
 *
 * @package Lytrod_Licensing
 *
 * @var array  $discounts  Years => percent off.
 * @var string $classes    Product-type visibility classes.
 * @var int    $product_id Product being edited.
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="lytrod_terms_data" class="panel woocommerce_options_panel hidden">

    <div class="options_group <?php echo esc_attr( $classes ); ?>">
        <div class="form-field lytrod-rows">
            <label><?php esc_html_e( 'Term discounts', 'lytrod-licensing' ); ?></label>
            <table class="widefat">
                <thead>
                    <tr>
                        <th class="sort">&nbsp;</th>
                        <th>
                            <?php esc_html_e( 'Years', 'lytrod-licensing' ); ?>
                            <?php
                            echo wc_help_tip( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core helper.
                                __( 'The licence is billed once per term, so a 3-year licence renews every 3 years. Six is the longest term WooCommerce Subscriptions supports.', 'lytrod-licensing' )
                            );
                            ?>
                        </th>
                        <th><?php esc_html_e( 'Discount %', 'lytrod-licensing' ); ?></th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $discounts as $lytrod_years => $lytrod_discount ) : ?>
                        <?php Lytrod_Licensing_Admin::term_row( $lytrod_years, $lytrod_discount ); ?>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4">
                            <a href="#" class="button lytrod-add-row" data-row="
                            <?php
                            ob_start();
                            Lytrod_Licensing_Admin::term_row();
                            echo esc_attr( ob_get_clean() );
                            ?>
                            "><?php esc_html_e( 'Add term', 'lytrod-licensing' ); ?></a>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php if ( $product_id ) : ?>
            <p class="form-field">
                <label><?php esc_html_e( 'One seat costs', 'lytrod-licensing' ); ?></label>
                <span class="lytrod-preview">
                    <?php foreach ( array_keys( $discounts ) as $lytrod_years ) : ?>
                        <?php $lytrod_price = Lytrod_Licensing_Rates::price( $product_id, 1, (int) $lytrod_years ); ?>
                        <span class="lytrod-preview-cell">
                            <strong>
                                <?php
                                printf(
                                    /* translators: %d: years. */
                                    esc_html( _n( '%d yr', '%d yrs', (int) $lytrod_years, 'lytrod-licensing' ) ),
                                    (int) $lytrod_years
                                );
                                ?>
                            </strong>
                            <?php echo wp_kses_post( wc_price( $lytrod_price['term_total'] ) ); ?>
                        </span>
                    <?php endforeach; ?>
                </span>
                <span class="description">
                    <?php esc_html_e( 'Total charged once per term for a single seat, after the discount.', 'lytrod-licensing' ); ?>
                </span>
            </p>
        <?php endif; ?>
    </div>

</div>
