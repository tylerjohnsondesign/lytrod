<?php
/**
 * Admin new order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/admin-new-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails\HTML
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;

$valid = false;
$product_name = '';
foreach ($order->get_items() as $item_id => $item) {
    $variation_id = $item->get_variation_id();

    if ($variation_id) {
        $product = wc_get_product($item->get_product_id());
        $product_name = $product ? $product->get_name() : '';
        $variation = wc_get_product($variation_id);
        $variation_name = $variation ? $variation->get_name() : '';

        // Remove the parent product name from the variation name
        $variation_name = str_replace($product_name . ' - ', '', $variation_name);

        // Check if variation contains "sign up"
        if (strpos(strtolower($variation_name), 'sign up') !== false || strpos(strtolower($variation_name), 'registration') !== false) {
            $valid = true;
        }
    }
}

if($valid) {
    $email_heading = $product_name .' '.'Registration';
    do_action( 'woocommerce_email_header', $email_heading, $email );
} else {
    do_action( 'woocommerce_email_header', $email_heading, $email );
}

if($valid) {

    ?>
    <p><?php printf( esc_html__( 'You have received the following order from %s,', 'woocommerce' ), esc_html( $order->get_billing_first_name(). ' ' . $order->get_billing_last_name() ) ); ?></p>
      
	<div style="margin-bottom: 40px;">
		<ul style="list-style-type: none; padding: 0;">
			<?php 
			$first_name = $order->get_billing_first_name();
			$last_name = $order->get_billing_last_name();
			if ($first_name && $last_name): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'First And Last Name:', 'woocommerce' ); ?></strong>
					<?php echo $first_name . ' ' . $last_name; ?>
				</li>
			<?php endif; ?>

			<?php 
			$email = $order->get_billing_email();
			if ($email): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Email:', 'woocommerce' ); ?></strong>
					<?php echo $email; ?>
				</li>
			<?php endif; ?>

			<?php 
			$company_name = get_post_meta( $order->get_id(), '_company_name', true );
			if ($company_name): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Company Name:', 'woocommerce' ); ?></strong>
					<?php echo $company_name; ?>
				</li>
			<?php endif; ?>

			<?php 
			$product_id = get_post_meta( $order->get_id(), 'intellicut_serial_number', true );
			if ($product_id): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Product ID:', 'woocommerce' ); ?></strong>
					<?php echo $product_id; ?>
				</li>
			<?php endif; ?>

			<?php 
			$aerocut_type = get_post_meta( $order->get_id(), '_machine_type', true );
			if ($aerocut_type): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Aerocut Type:', 'woocommerce' ); ?></strong>
					<?php echo $aerocut_type; ?>
				</li>
				<?php endif; ?>
			
			
			<?php 
			$intellicut_global_phone_number = get_post_meta( $order->get_id(), 'intellicut_global_phone_number', true );
			if ($intellicut_global_phone_number): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Phone Number:', 'woocommerce' ); ?></strong>
					<?php echo $intellicut_global_phone_number; ?>
				</li>
				<?php endif; ?>
			
			
			<?php 
			$intellicut_phone_number = get_post_meta( $order->get_id(), '_intellicut_phone_number', true );
			if ($intellicut_phone_number): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Phone Number:', 'woocommerce' ); ?></strong>
					<?php echo $intellicut_phone_number; ?>
				</li>
				<?php endif; ?>
		
			<?php 
			$ucidealer_field = get_post_meta( $order->get_id(), 'uchidadealer_field', true );
			if ($ucidealer_field): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Uchida Dealer:', 'woocommerce' ); ?></strong>
					<?php echo $ucidealer_field; ?>
				</li>
				<?php endif; ?>
			
			
			<?php 
			$intellicut_global_product_id = get_post_meta( $order->get_id(), 'intellicut_global_product_id', true );
			if ($intellicut_global_product_id): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Intellicut Global Product ID:', 'woocommerce' ); ?></strong>
					<?php echo $intellicut_global_product_id; ?>
				</li>
				<?php endif; ?>
			
			
			<?php 
			$intellicut_global_machine_type = get_post_meta( $order->get_id(), 'intellicut_global_machine_type', true );
			if ($intellicut_global_machine_type): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'AeroCut Type:', 'woocommerce' ); ?></strong>
					<?php echo $intellicut_global_machine_type; ?>
				</li>
				<?php endif; ?>
			
			
			<?php 
			$intellicut_global_aerocut_machine_number = get_post_meta( $order->get_id(), 'intellicut_global_aerocut_machine_number', true );
			if ($intellicut_global_aerocut_machine_number): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'AeroCut Machine Number:', 'woocommerce' ); ?></strong>
					<?php echo $intellicut_global_aerocut_machine_number; ?>
				</li>
				<?php endif; ?>
			
			
			<?php 
			$vrcutimposeinstallation_phone_number = get_post_meta( $order->get_id(), 'vrcutimposeinstallation_phone_number', true );
			if ($vrcutimposeinstallation_phone_number): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Phone Number:', 'woocommerce' ); ?></strong>
					<?php echo $vrcutimposeinstallation_phone_number; ?>
				</li>
				<?php endif; ?>
			
			
			<?php 
			$vrcutimposeinstallation_company_name = get_post_meta( $order->get_id(), 'vrcutimposeinstallation_company_name', true );
			if ($vrcutimposeinstallation_company_name): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Company Name:', 'woocommerce' ); ?></strong>
					<?php echo $vrcutimposeinstallation_company_name; ?>
				</li>
				<?php endif; ?>
			
			<?php 
			$vrcutimposeinstallation_product_id = get_post_meta( $order->get_id(), 'vrcutimposeinstallation_product_id', true );
			if ($vrcutimposeinstallation_product_id): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'VRCut Impose Product ID:', 'woocommerce' ); ?></strong>
					<?php echo $vrcutimposeinstallation_product_id; ?>
				</li>
				<?php endif; ?>
			
			
			<?php 
			$vrcutimposeinstallation_triump_type = get_post_meta( $order->get_id(), 'vrcutimposeinstallation_triumph_type', true );
			if ($vrcutimposeinstallation_triump_type): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Triumph Type:', 'woocommerce' ); ?></strong>
					<?php echo $vrcutimposeinstallation_triump_type; ?>
				</li>
				<?php endif; ?>
			
			
			<?php 
			$intellicutaneinstallation_phone_number = get_post_meta( $order->get_id(), 'intellicutaneinstallation_phone_number', true );
			if ($intellicutaneinstallation_phone_number): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Phone Number:', 'woocommerce' ); ?></strong>
					<?php echo $intellicutaneinstallation_phone_number; ?>
				</li>
				<?php endif; ?>
			
			
			<?php 
			$intellicutaneinstallation_company_name = get_post_meta( $order->get_id(), 'intellicutaneinstallation_company_name', true );
			if ($intellicutaneinstallation_company_name): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Company Name:', 'woocommerce' ); ?></strong>
					<?php echo $intellicutaneinstallation_company_name; ?>
				</li>
				<?php endif; ?>
			
			
			<?php 
			$intellicutaneinstallation_product_id = get_post_meta( $order->get_id(), 'intellicutaneinstallation_product_id', true );
			if ($intellicutaneinstallation_product_id): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'IntellicutAeroCut nano Edition Product ID:', 'woocommerce' ); ?></strong>
					<?php echo $intellicutaneinstallation_product_id; ?>
				</li>
				<?php endif; ?>
			
			
			<?php 
			$intellicutaneinstallation_aeroCut_type = get_post_meta( $order->get_id(), 'intellicutaneinstallation_aeroCut_type', true );
			if ($intellicutaneinstallation_aeroCut_type): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'AeroCut Type:', 'woocommerce' ); ?></strong>
					<?php echo $intellicutaneinstallation_aeroCut_type; ?>
				</li>
				<?php endif; ?>
			
			
			<?php 
			$installation_company_name = get_post_meta( $order->get_id(), 'installation_company_name', true );
			if ($installation_company_name): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Company Name:', 'woocommerce' ); ?></strong>
					<?php echo $installation_company_name; ?>
				</li>
				<?php endif; ?>
			
			
			<?php 
			$installation_phone_number = get_post_meta( $order->get_id(), 'installation_phone_number', true );
			if ($installation_phone_number): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Phone Number:', 'woocommerce' ); ?></strong>
					<?php echo $installation_phone_number; ?>
				</li>
				<?php endif; ?>
			
			
			<?php 
			$installation_product_id = get_post_meta( $order->get_id(), 'installation_product_id', true );
			if ($installation_product_id): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Vision Direct Product ID:', 'woocommerce' ); ?></strong>
					<?php echo $installation_product_id; ?>
				</li>
				<?php endif; ?>
			
			
			<?php 
			$bizcard_product_id = get_post_meta( $order->get_id(), 'bizcard_product_id', true );
			if ($bizcard_product_id): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Bizcard Product ID:', 'woocommerce' ); ?></strong>
					<?php echo $bizcard_product_id; ?>
				</li>
				<?php endif; ?>
			
			<?php 
			$bizcard_phone_number = get_post_meta( $order->get_id(), 'bizcard_phone_number', true );
			if ($bizcard_phone_number): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Bizcard Phone Number:', 'woocommerce' ); ?></strong>
					<?php echo $bizcard_phone_number; ?>
				</li>
				<?php endif; ?>
			
			
			<?php 
			$bizcard_cn = get_post_meta( $order->get_id(), 'bizcard_company_name', true );
			if ($bizcard_cn): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Bizcard Company Name:', 'woocommerce' ); ?></strong>
					<?php echo $bizcard_cn; ?>
				</li>
				<?php endif; ?>
			
			<?php 
			$bizcard_mt = get_post_meta( $order->get_id(), 'bizcard_machine_type', true );
			if ($bizcard_mt): ?>
				<li style="margin-bottom: 15px;">
					<strong><?php esc_html_e( 'Bizcard Type:', 'woocommerce' ); ?></strong>
					<?php echo $bizcard_mt; ?>
				</li>
				<?php endif; ?>
		
			</ul>
		</div>
	
    <?php
} else {

    ?>
    <?php /* translators: %s: Customer billing full name */ ?>
    <p><?php printf( esc_html__( 'You’ve received the following order from %s:', 'woocommerce' ), $order->get_formatted_billing_full_name() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
    <?php

    /*
    * @hooked WC_Emails::order_details() Shows the order details table.
    * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
    * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
    * @since 2.5.0
    */
    do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

    /*
    * @hooked WC_Emails::order_meta() Shows order meta data.
    */
    do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

    /*
    * @hooked WC_Emails::customer_details() Shows customer details
    * @hooked WC_Emails::email_address() Shows email address
    */
    do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

    /**
     * Show user-defined additional content - this is set in each email's settings.
     */
    if ( $additional_content ) {
        echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
    }
}

if($valid) {
    echo 'Congratulations on the sales.';
}

/*
* @hooked WC_Emails::email_footer() Output the email footer
*/
do_action( 'woocommerce_email_footer', $email );

?>


