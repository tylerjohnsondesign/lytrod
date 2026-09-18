<?php
/**
 * Term and licence-count picker on the single product page.
 *
 * @package Lytrod_Licensing
 *
 * @var int[]  $terms    Selectable terms in years.
 * @var string $free_for Human-readable free period, empty when there is none.
 */

defined( 'ABSPATH' ) || exit;

global $product;

$lytrod_default_term = $terms ? (int) min( $terms ) : 1;
?>
<div class="lytrod-picker">

    <?php if ( '' !== $free_for ) : ?>
        <p class="lytrod-picker__free">
            <?php
            printf(
                /* translators: %s: free period, e.g. "1 year". */
                esc_html__( 'Free for %s — no card needed to start.', 'lytrod-licensing' ),
                esc_html( $free_for )
            );
            ?>
        </p>
    <?php endif; ?>

    <?php if ( count( $terms ) > 1 ) : ?>
        <fieldset class="lytrod-picker__field">
            <legend class="lytrod-picker__label"><?php esc_html_e( 'Licence term', 'lytrod-licensing' ); ?></legend>
            <div class="lytrod-bubbles">
                <?php foreach ( $terms as $lytrod_years ) : ?>
                    <?php
                    $lytrod_id       = 'lytrod-term-' . (int) $lytrod_years;
                    $lytrod_discount = Lytrod_Licensing_Rates::term_discounts( $product->get_id() )[ $lytrod_years ] ?? 0;
                    ?>
                    <input type="radio" class="lytrod-bubble__input" id="<?php echo esc_attr( $lytrod_id ); ?>"
                        name="<?php echo esc_attr( Lytrod_Licensing_Seats::CART_TERM ); ?>"
                        value="<?php echo esc_attr( $lytrod_years ); ?>"
                        <?php checked( $lytrod_years, $lytrod_default_term ); ?> />
                    <label class="lytrod-bubble" for="<?php echo esc_attr( $lytrod_id ); ?>">
                        <span class="lytrod-bubble__years">
                            <?php
                            printf(
                                /* translators: %d: number of years. */
                                esc_html( _n( '%d year', '%d years', (int) $lytrod_years, 'lytrod-licensing' ) ),
                                (int) $lytrod_years
                            );
                            ?>
                        </span>
                        <?php if ( $lytrod_discount > 0 ) : ?>
                            <span class="lytrod-bubble__save">
                                <?php
                                printf(
                                    /* translators: %s: percentage. */
                                    esc_html__( 'save %s', 'lytrod-licensing' ),
                                    esc_html( rtrim( rtrim( number_format( (float) $lytrod_discount, 2 ), '0' ), '.' ) . '%' )
                                );
                                ?>
                            </span>
                        <?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>
    <?php else : ?>
        <input type="hidden" name="<?php echo esc_attr( Lytrod_Licensing_Seats::CART_TERM ); ?>"
            value="<?php echo esc_attr( $lytrod_default_term ); ?>" />
    <?php endif; ?>

    <div class="lytrod-picker__field">
        <label class="lytrod-picker__label" for="lytrod-seats">
            <?php esc_html_e( 'Licences', 'lytrod-licensing' ); ?>
        </label>
        <div class="lytrod-stepper">
            <button type="button" class="lytrod-stepper__btn" data-step="-1" aria-label="<?php esc_attr_e( 'One licence fewer', 'lytrod-licensing' ); ?>">&minus;</button>
            <input type="number" id="lytrod-seats"
                name="<?php echo esc_attr( Lytrod_Licensing_Seats::CART_SEATS ); ?>"
                class="lytrod-stepper__input"
                value="1" min="1" step="1"
                max="<?php echo esc_attr( Lytrod_Licensing_Rates::max_seats() ); ?>"
                inputmode="numeric" />
            <button type="button" class="lytrod-stepper__btn" data-step="1" aria-label="<?php esc_attr_e( 'One licence more', 'lytrod-licensing' ); ?>">+</button>
        </div>
        <p class="lytrod-picker__tier" aria-live="polite"></p>
    </div>

    <div class="lytrod-quote" aria-live="polite">
        <div class="lytrod-quote__row">
            <span class="lytrod-quote__label"><?php esc_html_e( 'Total today', 'lytrod-licensing' ); ?></span>
            <span class="lytrod-quote__total"></span>
        </div>
        <p class="lytrod-quote__note"></p>
    </div>

</div>
