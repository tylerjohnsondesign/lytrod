<?php
/**
 * Product data → Licenses.
 *
 * Every `.options_group` carries the same `show_if_*` classes as the tab itself. WooCommerce
 * hides a tab whose panel has options_groups that are ALL invisible
 * (woocommerce/assets/js/admin/meta-boxes-product.js:272-298), so they have to become visible
 * together or the tab disappears for subscription products.
 *
 * @package Lytrod_Licensing
 *
 * @var array  $tiers      Tier rows in force.
 * @var array  $free       Free period: length + period.
 * @var string $classes    Product-type visibility classes.
 * @var int    $product_id Product being edited.
 */

defined( 'ABSPATH' ) || exit;

$lytrod_periods = function_exists( 'wcs_get_available_time_periods' ) ? wcs_get_available_time_periods() : array(
    'day'   => __( 'day', 'lytrod-licensing' ),
    'week'  => __( 'week', 'lytrod-licensing' ),
    'month' => __( 'month', 'lytrod-licensing' ),
    'year'  => __( 'year', 'lytrod-licensing' ),
);

$lytrod_preview = array( 1, 2, 3, 5, 10, 25, 50 );
?>
<div id="lytrod_licenses_data" class="panel woocommerce_options_panel hidden">

    <div class="options_group <?php echo esc_attr( $classes ); ?>">
        <p class="form-field _lytrod_free_for_field">
            <label for="_subscription_trial_length"><?php esc_html_e( 'Free for', 'lytrod-licensing' ); ?></label>
            <span class="wrap">
                <input type="text" id="_subscription_trial_length" name="_subscription_trial_length"
                    class="wc_input_subscription_trial_length"
                    value="<?php echo esc_attr( $free['length'] ); ?>" />
                <label for="_subscription_trial_period" class="screen-reader-text"><?php esc_html_e( 'Free period', 'lytrod-licensing' ); ?></label>
                <select id="_subscription_trial_period" name="_subscription_trial_period"
                    class="wc_input_subscription_trial_period last wc-enhanced-select">
                    <?php foreach ( $lytrod_periods as $lytrod_value => $lytrod_label ) : ?>
                        <option value="<?php echo esc_attr( $lytrod_value ); ?>" <?php selected( $lytrod_value, $free['period'] ); ?>>
                            <?php echo esc_html( $lytrod_label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </span>
            <?php
            echo wc_help_tip( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core helper returns escaped markup.
                __( 'How long the licence is free before the first payment. Set to 0 to charge immediately. The customer can start a free licence without entering a card; one is requested at the first renewal.', 'lytrod-licensing' )
            );
            ?>
        </p>
    </div>

    <div class="options_group <?php echo esc_attr( $classes ); ?>">
        <div class="form-field lytrod-rows">
            <label><?php esc_html_e( 'Licence tiers', 'lytrod-licensing' ); ?></label>
            <table class="widefat">
                <thead>
                    <tr>
                        <th class="sort">&nbsp;</th>
                        <th><?php esc_html_e( 'Tier', 'lytrod-licensing' ); ?></th>
                        <th><?php esc_html_e( 'Seats from', 'lytrod-licensing' ); ?></th>
                        <th><?php esc_html_e( 'Seats to', 'lytrod-licensing' ); ?></th>
                        <th>
                            <?php esc_html_e( 'Price per seat', 'lytrod-licensing' ); ?>
                            <?php
                            echo wc_help_tip( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core helper.
                                __( 'Charged for each seat that falls inside this tier, not for the whole licence. Seats are priced tier by tier, so a 3-seat licence pays the first tier for seat 1 and the second tier for seats 2 and 3. The preview below shows the resulting totals.', 'lytrod-licensing' )
                            );
                            ?>
                        </th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $tiers as $lytrod_tier ) : ?>
                        <?php Lytrod_Licensing_Admin::tier_row( $lytrod_tier ); ?>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6">
                            <a href="#" class="button lytrod-add-row" data-row="
                            <?php
                            ob_start();
                            Lytrod_Licensing_Admin::tier_row();
                            echo esc_attr( ob_get_clean() );
                            ?>
                            "><?php esc_html_e( 'Add tier', 'lytrod-licensing' ); ?></a>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php if ( $product_id ) : ?>
            <p class="form-field">
                <label><?php esc_html_e( 'Annual price', 'lytrod-licensing' ); ?></label>
                <span class="lytrod-preview">
                    <?php foreach ( $lytrod_preview as $lytrod_seats ) : ?>
                        <span class="lytrod-preview-cell">
                            <strong><?php echo esc_html( $lytrod_seats ); ?></strong>
                            <?php echo wp_kses_post( wc_price( Lytrod_Licensing_Rates::annual( $product_id, $lytrod_seats ) ) ); ?>
                        </span>
                    <?php endforeach; ?>
                </span>
                <span class="description">
                    <?php esc_html_e( 'Seats across the top, annual price beneath. Multiply by the term and apply its discount for a multi-year licence.', 'lytrod-licensing' ); ?>
                </span>
            </p>
        <?php endif; ?>
    </div>

</div>
