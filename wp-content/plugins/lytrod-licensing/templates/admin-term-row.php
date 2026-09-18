<?php
/**
 * One term-discount row. Doubles as the add-row template.
 *
 * @package Lytrod_Licensing
 *
 * @var int|string   $years    Term in years.
 * @var float|string $discount Percent off.
 */

defined( 'ABSPATH' ) || exit;
?>
<tr>
    <td class="sort"></td>
    <td>
        <input type="number" class="input_text lytrod-narrow" name="lytrod_term_years[]" min="1" max="6" step="1"
            value="<?php echo esc_attr( $years ); ?>" />
    </td>
    <td>
        <input type="number" class="input_text lytrod-narrow" name="lytrod_term_discount[]" min="0" max="100" step="0.01"
            value="<?php echo esc_attr( $discount ); ?>" />
    </td>
    <td width="1%">
        <a href="#" class="lytrod-delete-row"><?php esc_html_e( 'Delete', 'lytrod-licensing' ); ?></a>
    </td>
</tr>
