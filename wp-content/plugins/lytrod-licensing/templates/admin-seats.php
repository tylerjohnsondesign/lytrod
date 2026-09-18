<?php
/**
 * Licensed seats on the subscription edit screen.
 *
 * @package Lytrod_Licensing
 *
 * @var int  $seats       Seat count.
 * @var int  $years       Term in whole years.
 * @var bool $provisioned Whether LimeLM has been updated.
 */

defined( 'ABSPATH' ) || exit;
?>
<p>
    <label for="lytrod_seats"><strong><?php esc_html_e( 'Seats', 'lytrod-licensing' ); ?></strong></label><br />
    <input type="number" id="lytrod_seats" name="lytrod_seats" min="1" step="1"
        value="<?php echo esc_attr( $seats ); ?>" style="width:100px;" />
</p>

<p style="color:#5b5b5b;font-size:12px;margin-top:-6px;">
    <?php
    printf(
        /* translators: %d: number of years. */
        esc_html( _n( 'Billed every %d year.', 'Billed every %d years.', $years, 'lytrod-licensing' ) ),
        (int) $years
    );
    ?>
</p>

<p>
    <label>
        <input type="checkbox" name="lytrod_seats_reprice" value="1" />
        <?php esc_html_e( 'Reprice to match', 'lytrod-licensing' ); ?>
    </label>
</p>
<p style="color:#5b5b5b;font-size:12px;margin-top:-6px;">
    <?php esc_html_e( 'Every renewal is copied from this subscription, so leaving this unticked changes the recorded seat count without changing what the customer is billed.', 'lytrod-licensing' ); ?>
</p>

<hr />

<p>
    <label>
        <input type="checkbox" name="lytrod_seats_provisioned" value="1" <?php checked( $provisioned ); ?> />
        <strong><?php esc_html_e( 'Activation limit set in LimeLM', 'lytrod-licensing' ); ?></strong>
    </label>
</p>
<p style="color:#5b5b5b;font-size:12px;margin-top:-6px;">
    <?php esc_html_e( 'Seats are not provisioned automatically. Tick this once the limit has been raised by hand.', 'lytrod-licensing' ); ?>
</p>
