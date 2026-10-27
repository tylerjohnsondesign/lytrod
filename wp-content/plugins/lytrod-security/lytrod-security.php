<?php
/**
 * Plugin Name: Lytrod Security
 * Description: Guards against the backdoors found in the October 2026 incident: elevated customer roles, hidden per-user admins, and an open Ninja Forms debug-log endpoint.
 * Version:     1.0.0
 * Author:      Lytrod
 *
 * @package Lytrod_Security
 */

defined( 'ABSPATH' ) || exit;

/**
 * Roles that must never manage orders.
 *
 * WooCommerce treats anyone with `edit_others_shop_orders` as a store admin, which opens the
 * whole orders REST API and the wc-admin options allowlist — including the Stripe keys. An
 * attacker gave these roles shop-order capabilities, and a self-registered customer used them
 * to read and wipe the keys.
 */
const LYTROD_SECURITY_LOW_ROLES = array( 'customer', 'subscriber' );

/**
 * Strip order-management capabilities from the low roles if they ever reappear.
 *
 * Roles live in the database, so a file-level malware cleanup leaves them alone. Cheap to run on
 * every request: the roles are already in memory, and nothing is written unless a bad cap exists.
 *
 * @return void
 */
function lytrod_security_guard_roles() {
	foreach ( LYTROD_SECURITY_LOW_ROLES as $role_name ) {
		$role = get_role( $role_name );

		if ( ! $role ) {
			continue;
		}

		foreach ( array_keys( array_filter( $role->capabilities ) ) as $cap ) {
			if ( false === strpos( $cap, 'shop_order' ) && 'manage_woocommerce' !== $cap ) {
				continue;
			}

			$role->remove_cap( $cap );
			lytrod_security_log( sprintf( 'Removed capability "%s" from role "%s".', $cap, $role_name ) );
		}
	}
}
add_action( 'init', 'lytrod_security_guard_roles', 1 );

/**
 * Flag users who hold admin powers on their own account rather than through a role.
 *
 * That is exactly how seven backdoor admins hid as "customers": the admin list filters by role,
 * so they never appeared in it. Checked once a day; nothing is changed automatically, because
 * deleting accounts should be a human decision.
 *
 * @return void
 */
function lytrod_security_audit_users() {
	if ( get_transient( 'lytrod_security_user_audit' ) ) {
		return;
	}

	set_transient( 'lytrod_security_user_audit', 1, DAY_IN_SECONDS );

	global $wpdb;

	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value LIKE %s",
			$wpdb->prefix . 'capabilities',
			'%manage_options%'
		)
	);

	foreach ( $rows as $row ) {
		$caps = maybe_unserialize( $row->meta_value );

		if ( ! is_array( $caps ) || ! empty( $caps['administrator'] ) ) {
			continue;
		}

		lytrod_security_log( sprintf( 'User #%d is not an administrator but holds manage_options on their own account.', $row->user_id ), 'critical' );
	}
}
add_action( 'admin_init', 'lytrod_security_audit_users' );

/**
 * Remove Ninja Forms File Uploads' debug-log routes.
 *
 * Both register with a permission callback that returns true, so anyone — logged in or not —
 * can read the log or wipe it. The October attacker wiped it.
 *
 * @param array $endpoints Registered REST endpoints.
 * @return array
 */
function lytrod_security_block_nf_debug_log( $endpoints ) {
	unset( $endpoints['/nf-file-uploads/debug-log/get-all'], $endpoints['/nf-file-uploads/debug-log/delete-all'] );

	return $endpoints;
}
add_filter( 'rest_endpoints', 'lytrod_security_block_nf_debug_log' );

/**
 * Log to the WooCommerce logger when it is available.
 *
 * @param string $message Message.
 * @param string $level   Log level.
 * @return void
 */
function lytrod_security_log( $message, $level = 'warning' ) {
	if ( function_exists( 'wc_get_logger' ) ) {
		wc_get_logger()->log( $level, $message, array( 'source' => 'lytrod-security' ) );
	}
}
