<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
// WooCommerce V2 -> V3 helper classes
if( !class_exists( 'Backwards_Compatible_Order' ) && class_exists('WC_Order'))
{
	class Backwards_Compatible_Order extends WC_Order 
	{
		public $id;
		public $shipping_country;
		public $shipping_first_name;
		public $shipping_last_name;
		public $shipping_company;
		public $shipping_address_1;
		public $shipping_address_2;
		public $shipping_city;
		public $shipping_state;
		public $shipping_postcode;
		public $billing_email;
		public $billing_phone;
		public $billing_address_1;
		public $billing_address_2;
		public $billing_city;
		public $billing_postcode;
		public $billing_country;
		public $billing_state;
		public $billing_company;
		public $billing_first_name;
		public $billing_last_name;
		
		public function __construct( $order )
		{
			global $woocommerce;
			$this->wc_version = WC()->version;
			$this->set_order( $order );
		}

		public function set_order( $order )
		{
			if( is_numeric( $order) ){
				parent::__construct( $order );
			}elseif( is_object( $order) ){
				parent::__construct( $this->get_id_from_order_obj( $order ) );
			}
			$this->set_order_properties();
		}

		private function get_id_from_order_obj( $order_obj )
		{
			return ( $this->wc_version < '2.7.0' ) ? $order_obj->id : $order_obj->get_id();
		}

		private function set_order_properties()
		{
			$this->id 					= ( $this->wc_version < '2.7.0' ) ? $this->id : $this->get_id();
			$this->shipping_country 	= ( $this->wc_version < '2.7.0' ) ? $this->shipping_country : $this->get_shipping_country();
			$this->shipping_first_name 	= ( $this->wc_version < '2.7.0' ) ? $this->shipping_first_name : $this->get_shipping_first_name();
			$this->shipping_last_name 	= ( $this->wc_version < '2.7.0' ) ? $this->shipping_last_name : $this->get_shipping_last_name();
			$this->shipping_company 	= ( $this->wc_version < '2.7.0' ) ? $this->shipping_company : $this->get_shipping_company();
			$this->shipping_address_1 	= ( $this->wc_version < '2.7.0' ) ? $this->shipping_address_1 : $this->get_shipping_address_1();
			$this->shipping_address_2 	= ( $this->wc_version < '2.7.0' ) ? $this->shipping_address_2 : $this->get_shipping_address_2();
			$this->shipping_city 		= ( $this->wc_version < '2.7.0' ) ? $this->shipping_city : $this->get_shipping_city();
			$this->shipping_state 		= ( $this->wc_version < '2.7.0' ) ? $this->shipping_state : $this->get_shipping_state();
			$this->shipping_postcode 	= ( $this->wc_version < '2.7.0' ) ? $this->shipping_postcode : $this->get_shipping_postcode();
			$this->billing_email 		= ( $this->wc_version < '2.7.0' ) ? $this->billing_email : $this->get_billing_email();
			$this->billing_phone 		= ( $this->wc_version < '2.7.0' ) ? $this->billing_phone : $this->get_billing_phone();
			$this->billing_address_1 	= ( $this->wc_version < '2.7.0' ) ? $this->billing_address_1 : $this->get_billing_address_1();
			$this->billing_address_2 	= ( $this->wc_version < '2.7.0' ) ? $this->billing_address_1 : $this->get_billing_address_2();
			$this->billing_city 		= ( $this->wc_version < '2.7.0' ) ? $this->billing_city : $this->get_billing_city();
			$this->billing_postcode 	= ( $this->wc_version < '2.7.0' ) ? $this->billing_postcode  : $this->get_billing_postcode();
			$this->billing_country 		= ( $this->wc_version < '2.7.0' ) ? $this->billing_country  : $this->get_billing_country();
			$this->billing_state 		= ( $this->wc_version < '2.7.0' ) ? $this->billing_state  : $this->get_billing_state();
			$this->billing_company 		= ( $this->wc_version < '2.7.0' ) ? $this->billing_company  : $this->get_billing_company();
			$this->billing_first_name 	= ( $this->wc_version < '2.7.0' ) ? $this->billing_first_name  : $this->get_billing_first_name();
			$this->billing_last_name 	= ( $this->wc_version < '2.7.0' ) ? $this->billing_last_name  : $this->get_billing_last_name();
			$this->payment_method 		= ( $this->wc_version < '2.7.0' ) ? $this->payment_method  : $this->get_payment_method();
		}

		public function get_order_currency()
		{
			return ( $this->wc_version < '2.7.0' ) ? parent::get_order_currency() : $this->get_currency();
		}
	}
}

if( !class_exists( 'Backwards_Compatible_Product' ) && class_exists('WC_Product') )
{
	class Backwards_Compatible_Product extends WC_Product
	{
		public $id;
		public $length;
		public $width;
		public $height;
		public $weight;
		public $variation_id;

		public function __construct( $item )
		{
			$this->wc_version = WC()->version;

			$this->set_item( $item );
			$this->set_item_properties();
		}

		public function set_item( $item )
		{
			if( is_object($item) ){
				parent::__construct( $this->get_id_from_item_obj( $item ) );
			}else{
				parent::__construct( $item );
			}
		}

		private function get_id_from_item_obj( $item_obj )
		{
			if( $this->wc_version < '2.7.0' ){
				return $item_obj->id;
			}else{
				if( $item_obj->get_type() == 'simple' )
					return $item_obj->get_id();
				else
					return $item_obj->get_parent_id();
			}
		}

		private function set_item_properties()
		{
			$this->id 			= ( $this->wc_version < '2.7.0' ) ? $this->id : $this->get_id();
			$this->length 		= ( $this->wc_version < '2.7.0' ) ? $this->length : $this->get_length();
			$this->width 		= ( $this->wc_version < '2.7.0' ) ? $this->width : $this->get_width();
			$this->height 		= ( $this->wc_version < '2.7.0' ) ? $this->height : $this->get_height();
			$this->weight 		= ( $this->wc_version < '2.7.0' ) ? $this->weight : $this->get_weight();
			$this->variation_id = ( $this->wc_version < '2.7.0' ) ? $this->variation_id : $this->get_id();
		}
	}
}