<?php
/**
 * One tier row. Doubles as the add-row template.
 *
 * Parallel arrays with bare `[]` names, following WooCommerce's downloadable-files pattern
 * (woocommerce/includes/admin/meta-boxes/views/html-product-download.php). DOM order is the
 * index, so add, remove and drag-reorder all work without reindexing.
 *
 * @package Lytrod_Licensing
 *
 * @var array $tier Row values: name, from, to, rate.
 */

defined( 'ABSPATH' ) || exit;
?>
<tr>
    <td class="sort"></td>
    <td>
        <input type="text" class="input_text" name="lytrod_tier_name[]"
            placeholder="<?php esc_attr_e( 'Basic', 'lytrod-licensing' ); ?>"
            value="<?php echo esc_attr( $tier['name'] ); ?>" />
    </td>
    <td>
        <input type="number" class="input_text lytrod-narrow" name="lytrod_tier_from[]" min="1" step="1"
            value="<?php echo esc_attr( $tier['from'] ); ?>" />
    </td>
    <td>
        <input type="number" class="input_text lytrod-narrow" name="lytrod_tier_to[]" min="1" step="1"
            placeholder="<?php esc_attr_e( 'no limit', 'lytrod-licensing' ); ?>"
            value="<?php echo esc_attr( $tier['to'] ); ?>" />
    </td>
    <td>
        <input type="text" class="input_text wc_input_price lytrod-narrow" name="lytrod_tier_rate[]"
            value="<?php echo esc_attr( '' === $tier['rate'] ? '' : wc_format_localized_price( $tier['rate'] ) ); ?>" />
    </td>
    <td width="1%">
        <a href="#" class="lytrod-delete-row"><?php esc_html_e( 'Delete', 'lytrod-licensing' ); ?></a>
    </td>
</tr>
