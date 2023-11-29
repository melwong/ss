<?php
/**
 * Plugin Name: WooCommerce MetaMask Payment Gateway
 * Description: Transact payments using MetaMask wallet.
 * Author: SportStreet
 * Version: 1.4.20
 * Requires at least: 4.4
 * Tested up to: 5.8
 * WC tested up to: 6.1
 * WC requires at least: 2.6
 *
 */
use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;

defined( 'ABSPATH' ) || exit;

define( 'WC_GATEWAY_METAMASK_VERSION', '1.4.20' ); // WRCS: DEFINED_VERSION.
define( 'WC_GATEWAY_METAMASK_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'WC_GATEWAY_METAMASK_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

/**
 * Initialize the gateway.
 * @since 1.0.0
 */
function woocommerce_metamask_init() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	require_once( plugin_basename( 'includes/class-wc-gateway-metamask.php' ) );
	require_once( plugin_basename( 'includes/class-wc-gateway-metamask-privacy.php' ) );
	load_plugin_textdomain( 'woocommerce-gateway-metamask', false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) );
	add_filter( 'woocommerce_payment_gateways', 'woocommerce_metamask_add_gateway' );
}
add_action( 'plugins_loaded', 'woocommerce_metamask_init', 0 );

function woocommerce_metamask_plugin_links( $links ) {
	$settings_url = add_query_arg(
		array(
			'page' => 'wc-settings',
			'tab' => 'checkout',
			'section' => 'wc_gateway_metamask',
		),
		admin_url( 'admin.php' )
	);

	$plugin_links = array(
		'<a href="' . esc_url( $settings_url ) . '">' . __( 'Settings', 'woocommerce-gateway-metamask' ) . '</a>',
		'<a href="https://www.woocommerce.com/my-account/tickets/">' . __( 'Support', 'woocommerce-gateway-metamask' ) . '</a>',
		'<a href="https://docs.woocommerce.com/document/metamask-payment-gateway/">' . __( 'Docs', 'woocommerce-gateway-metamask' ) . '</a>',
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woocommerce_metamask_plugin_links' );


/**
 * Add the gateway to WooCommerce
 * @since 1.0.0
 */
function woocommerce_metamask_add_gateway( $methods ) {
	$methods[] = 'WC_Gateway_MetaMask';
	return $methods;
}

add_action( 'woocommerce_blocks_loaded', 'woocommerce_metamask_woocommerce_blocks_support' );

function woocommerce_metamask_woocommerce_blocks_support() {
	if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
		require_once dirname( __FILE__ ) . '/includes/class-wc-gateway-metamask-blocks-support.php';
		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
				$payment_method_registry->register( new WC_MetaMask_Blocks_Support );
			}
		);
	}
}
