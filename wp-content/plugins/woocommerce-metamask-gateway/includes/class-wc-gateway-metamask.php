<?php
/**
 * MetaMask Payment Gateway
 *
 * Provides payment via MetaMask wallet.
 *
 * @class  woocommerce_metamask
 * @package WooCommerce
 * @category Payment Gateways
 * @author Melvin Wong
 */
class WC_Gateway_MetaMask extends WC_Payment_Gateway {

	/**
	 * Version
	 *
	 * @var string
	 */
	public $version;

	/**
	 * @access protected
	 * @var array $data_to_send
	 */
	protected $data_to_send = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->version = WC_GATEWAY_METAMASK_VERSION;
		$this->id = 'metamask';
		$this->method_title       = __( 'MetaMask', 'woocommerce-gateway-metamask' );
		/* translators: 1: a href link 2: closing href */
		$this->method_description = __( 'This payment plugin works by calling your customer MetaMask wallet to sign a message to confirm their purchase.', 'woocommerce-gateway-metamask' );
		$this->icon               = WP_PLUGIN_URL . '/' . plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/assets/images/pay_with_metamask_sm.png';
		$this->debug_email        = get_option( 'admin_email' );
		$this->available_currencies = (array)apply_filters('woocommerce_gateway_metamask_available_currencies', array( 'LINK', 'XRP', 'CRO', 'KLAY', 'ETH', 'USDT', 'USDC', 'BTC' ) );

		$this->init_form_fields();
		$this->init_settings();

		if ( ! is_admin() ) {
			$this->setup_constants();
		}

		// Setup gateway data
		$this->title            = $this->get_option( 'title' );
		$this->response_url	    = add_query_arg( 'wc-api', 'WC_Gateway_MetaMask', home_url( '/' ) );
		$this->send_debug_email = 'yes' === $this->get_option( 'send_debug_email' );
		$this->description      = $this->get_option( 'description' );
		$this->enabled          = 'yes' === $this->get_option( 'enabled' ) ? 'yes' : 'no';
		$this->enable_logging   = 'yes' === $this->get_option( 'enable_logging' );
		$this->currency     = strtoupper( $this->get_option( 'currency' ) );

		add_action( 'woocommerce_api_wc_gateway_metamask', array( $this, 'check_itn_response' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_receipt_metamask', array( $this, 'receipt_page' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

	}

	/**
	 * Initialise Gateway Settings Form Fields
	 *
	 * @since 1.0.0
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'       => __( 'Enable/Disable', 'woocommerce-gateway-metamask' ),
				'label'       => __( 'Enable MetaMask', 'woocommerce-gateway-metamask' ),
				'type'        => 'checkbox',
				'description' => __( 'This controls whether or not this gateway is enabled within WooCommerce.', 'woocommerce-gateway-metamask' ),
				'default'     => 'no',		// User should enter the required information before enabling the gateway.
				'desc_tip'    => true,
			),
			'title' => array(
				'title'       => __( 'Title', 'woocommerce-gateway-metamask' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-gateway-metamask' ),
				'default'     => __( 'MetaMask', 'woocommerce-gateway-metamask' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'woocommerce-gateway-metamask' ),
				'type'        => 'text',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-gateway-metamask' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'send_debug_email' => array(
				'title'   => __( 'Send Debug Emails', 'woocommerce-gateway-metamask' ),
				'type'    => 'checkbox',
				'label'   => __( 'Send debug e-mails for transactions through the MetaMask gateway (sends on successful transaction as well).', 'woocommerce-gateway-metamask' ),
				'default' => 'yes',
			),
			'debug_email' => array(
				'title'       => __( 'Who Receives Debug E-mails?', 'woocommerce-gateway-metamask' ),
				'type'        => 'text',
				'description' => __( 'The e-mail address to which debugging error e-mails are sent when in test mode.', 'woocommerce-gateway-metamask' ),
				'default'     => get_option( 'admin_email' ),
			),
			'enable_logging' => array(
				'title'   => __( 'Enable Logging', 'woocommerce-gateway-metamask' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable transaction logging for gateway.', 'woocommerce-gateway-metamask' ),
				'default' => 'no',
			),
			'currency' => array(
				'title'   => __( 'Cryptocurrency Symbol', 'woocommerce-gateway-metamask' ),
				'type'    => 'text',
				'description'   => __( 'The cryptocurrency that you accept such as USDT, USDC, ETH, BTC.', 'woocommerce-gateway-metamask' ),
				'default' => '',
				'desc_tip'	=> true,
			),
		);
	}

	/**
	 * check_requirements()
	 *
	 * Check if this gateway is enabled and available in the base currency being traded with.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function check_requirements() {

		$errors = [
			// Check if the store currency is supported by MetaMask
			! in_array( get_woocommerce_currency(), $this->available_currencies ) ? 'wc-gateway-metamask-error-invalid-currency' : null,
			empty( $this->get_option( 'currency' ) ) ? 'wc-gateway-metamask-error-no-currency' : null,
		];

		return array_filter( $errors );
	}

	/**
	 * Check if the gateway is available for use.
	 *
	 * @return bool
	 */
	public function is_available() {
		if ( 'yes' === $this->enabled ) {
			$errors = $this->check_requirements();
			// Prevent using this gateway on frontend if there are any configuration errors.
			return 0 === count( $errors );
		}

		return parent::is_available();
	}

	/**
	 * Admin Panel Options
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * @since 1.0.0
	 */
	public function admin_options() {
		if ( in_array( get_woocommerce_currency(), $this->available_currencies ) ) {
			parent::admin_options();
		} else {
		?>
			<h3><?php _e( 'MetaMask', 'woocommerce-gateway-metamask' ); ?></h3>
			<div class="inline error"><p><strong><?php _e( 'No Supported Cryptocurrency', 'woocommerce-gateway-metamask' ); ?></strong> <?php /* translators: 1: a href link 2: closing href */ echo sprintf( __( 'Include a supported cryptocurrency on your WooCommerce site, such as USDT, USDC, ETH, BTC.', 'woocommerce-gateway-metamask' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=general' ) ) . '">', '</a>' ); ?></p></div>
			<?php
		}
	}

	/**
	 * Generate the MetaMask button link.
	 *
	 * @since 1.0.0
	 */
	public function generate_metamask_form( $order_id ) {
		$order         = wc_get_order( $order_id );
		// Construct variables for post
		$this->data_to_send = array(
			// Payment details
			'return_url'       => $this->get_return_url( $order ),
			'cancel_url'       => $order->get_cancel_order_url(),
			'notify_url'       => $this->response_url,

			// Item details
			'm_payment_id'     => ltrim( $order->get_order_number(), _x( '#', 'hash before order number', 'woocommerce-gateway-metamask' ) ),
			'amount'           => $order->get_total(),
			'item_name'        => get_bloginfo( 'name' ) . ' - ' . $order->get_order_number(),
			/* translators: 1: blog info name */
			'item_description' => sprintf( __( 'New order from %s', 'woocommerce-gateway-metamask' ), get_bloginfo( 'name' ) ),
			'currency'		=> $this->currency,

			// Custom strings
			'custom_str1'      => self::get_order_prop( $order, 'order_key' ),
			'custom_str3'      => self::get_order_prop( $order, 'id' ),
		);

		ksort($this->data_to_send); //Sorts an associative array in ascending order, according to the key.

		$metamask_args_array = array();
		$sign_strings = [];
		foreach ( $this->data_to_send as $key => $value ) {

			if ( $key !== 'return_url' && $key !== 'cancel_url' && $key !== 'notify_url' && $key !== 'item_name' && $key !== 'item_description' && $key !== 'currency' ) {
				$sign_strings[] = esc_attr( $key ) . '=' . urlencode(str_replace('&amp;', '&', trim( $value )));
			}

			$metamask_args_array[] = '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		}

		$metamask_args_array[] = '<input type="hidden" name="signature" value="' . md5( implode('&', $sign_strings) ) . '" />';

		wp_register_script( 'woocommerce_pay_metamask_ethers', 'https://cdnjs.cloudflare.com/ajax/libs/ethers/5.5.3/ethers.umd.min.js' );
        wp_enqueue_script( 'woocommerce_pay_metamask_ethers' );
		wp_register_script( 'woocommerce_pay_metamask', plugins_url( 'assets/js/web3-connector.js', dirname(__FILE__) ), '', time() );
		wp_enqueue_script( 'woocommerce_pay_metamask', 'web3-connector.js', '', time() );

		return '<form action="" method="post" id="metamask_payment_form">
				' . implode( '', $metamask_args_array ) .
				'<input type="submit" class="button-alt" id="submit_metamask_payment_form" value="' . __( 'Pay via MetaMask', 'woocommerce-gateway-metamask' ) . '" />
				<style>
					.metamask-overlay {
						top: 100px !important;
					}
				</style>
				<script type="text/javascript">

					jQuery(function(){
						jQuery(document).ready(function() {
							jQuery(".blockUI").addClass("metamask-overlay");
						});

						jQuery("body").block(
							{
								message: "' . __( 'Thank you for your order. We are now redirecting you to MetaMask to make payment.', 'woocommerce-gateway-metamask' ) . '",
								overlayCSS:
								{
									background: "#fff",
									opacity: 0.6
								},
								css: {
									padding:        20,
									textAlign:      "center",
									color:          "#555",
									border:         "3px solid #aaa",
									backgroundColor:"#fff",
									cursor:         "wait",
								}
							});
						//jQuery( "#submit_metamask_payment_form" ).click();
					});
				</script>
			</form>';
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @since 1.0.0
	 */
	public function process_payment( $order_id ) {

		$order = wc_get_order( $order_id );
		return array(
			'result' 	 => 'success',
			'redirect'	 => $order->get_checkout_payment_url( true ),
		);
	}

	/**
	 * Reciept page.
	 *
	 * Display text and a button to direct the user to MetaMask.
	 *
	 * @since 1.0.0
	 */
	public function receipt_page( $order ) {
		echo '<p>' . __( 'Thank you for your order, please click the button below to pay with MetaMask.', 'woocommerce-gateway-metamask' ) . '</p>';
		echo $this->generate_metamask_form( $order );
	}

	/**
	 * Check MetaMask ITN response.
	 *
	 * @since 1.0.0
	 */
	public function check_itn_response() {

		$_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS );	//To sanitize query string
		
		$this->handle_itn_request( stripslashes_deep( $_POST ) );

		// Notify MetaMask that information has been received
		header( 'HTTP/1.0 200 OK' );
		flush();
	}

	/**
	 * Check MetaMask ITN validity.
	 *
	 * @param array $data
	 * @since 1.0.0
	 */
	public function handle_itn_request( $data ) {
		$this->log( PHP_EOL
			. '----------'
			. PHP_EOL . 'MetaMask transaction call received'
			. PHP_EOL . '----------'
		);
		$this->log( 'Get posted data' );
		$this->log( 'MetaMask Data: ' . print_r( $data, true ) );

		$metamask_error  = false;
		$metamask_done   = false;
		$debug_email    = $this->get_option( 'debug_email', get_option( 'admin_email' ) );
		$session_id     = $data['custom_str1'];
		$vendor_name    = get_bloginfo( 'name', 'display' );
		$vendor_url     = home_url( '/' );
		$order_id       = absint( $data['custom_str3'] );
		$order_key      = wc_clean( $session_id );
		$order          = wc_get_order( $order_id );
		$original_order = $order;

		if ( false === $data ) {
			$metamask_error  = true;
			$metamask_error_message = PF_ERR_BAD_ACCESS;
		}

		// Verify security signature
		if ( ! $metamask_error && ! $metamask_done ) {
			$this->log( 'Verify security signature' );

			$signature = md5( $this->_generate_parameter_string( $data, false, false ) ); // false not to sort data
			
			// If signature different, log for debugging
			if ( ! $this->validate_signature( $data, $signature ) ) {
				$metamask_error         = true;
				$metamask_error_message = PF_ERR_INVALID_SIGNATURE;
			}
		}

		// Check data against internal order
		if ( ! $metamask_error && ! $metamask_done ) {
			$this->log( 'Check data against internal order' );

			// Check order amount
			if ( ! $this->amounts_equal( $data['amount'], self::get_order_prop( $order, 'order_total' ) ) ) {
				$metamask_error  = true;
				$metamask_error_message = PF_ERR_AMOUNT_MISMATCH;
			} elseif ( strcasecmp( $data['custom_str1'], self::get_order_prop( $order, 'order_key' ) ) != 0 ) {
				// Check session ID
				$metamask_error  = true;
				$metamask_error_message = PF_ERR_SESSIONID_MISMATCH;
			}
		}

		// Get internal order and verify it hasn't already been processed
		if ( ! $metamask_error && ! $metamask_done ) {
			$this->log_order_details( $order );

			// Check if order has already been processed
			if ( 'completed' === self::get_order_prop( $order, 'status' ) ) {
				$this->log( 'Order has already been processed' );
				$metamask_done = true;
			}
		}

		// If an error occurred
		if ( $metamask_error ) {
			$this->log( 'Error occurred: ' . $metamask_error_message );

			if ( $this->send_debug_email ) {
				$this->log( 'Sending email notification' );

				 // Send an email
				$subject = 'MetaMask transaction error: ' . $metamask_error_message;
				$body =
					"Hi,\n\n" .
					"An invalid MetaMask transaction on your website requires attention\n" .
					"------------------------------------------------------------\n" .
					'Site: ' . esc_html( $vendor_name ) . ' (' . esc_url( $vendor_url ) . ")\n" .
					'Remote IP Address: ' . $_SERVER['REMOTE_ADDR'] . "\n" .
					'Remote host name: ' . gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) . "\n" .
					'Purchase ID: ' . self::get_order_prop( $order, 'id' ) . "\n" .
					'User ID: ' . self::get_order_prop( $order, 'user_id' ) . "\n";
				if ( isset( $data['payment_hash'] ) ) {
					$body .= 'MetaMask Payment Hash: ' . esc_html( $data['payment_hash'] ) . "\n";
				}
				if ( isset( $data['payment_status'] ) ) {
					$body .= 'MetaMask Payment Status: ' . esc_html( $data['payment_status'] ) . "\n";
				}

				$body .= "\nError: " . $metamask_error_message . "\n";

				switch ( $metamask_error_message ) {
					case PF_ERR_AMOUNT_MISMATCH:
						$body .=
							'Value received : ' . esc_html( $data['amount'] ) . "\n"
							. 'Value should be: ' . self::get_order_prop( $order, 'order_total' );
						break;

					case PF_ERR_ORDER_ID_MISMATCH:
						$body .=
							'Value received : ' . esc_html( $data['custom_str3'] ) . "\n"
							. 'Value should be: ' . self::get_order_prop( $order, 'id' );
						break;

					case PF_ERR_SESSIONID_MISMATCH:
						$body .=
							'Value received : ' . esc_html( $data['custom_str1'] ) . "\n"
							. 'Value should be: ' . self::get_order_prop( $order, 'id' );
						break;

					// For all other errors there is no need to add additional information
					default:
						break;
				}

				wp_mail( $debug_email, $subject, $body );
			} // End if().
		} elseif ( ! $metamask_done ) {

			$this->log( 'Check status and update order' );

			if ( self::get_order_prop( $original_order, 'order_key' ) !== $order_key ) {
				$this->log( 'Order key does not match' );
				exit;
			}

			$status = strtolower( $data['payment_status'] );

			if ( 'complete' === $status ) {
				$this->handle_itn_payment_complete( $data, $order );
			} elseif ( 'failed' === $status ) {
				$this->handle_itn_payment_failed( $data, $order );
			} elseif ( 'pending' === $status ) {
				$this->handle_itn_payment_pending( $data, $order );
			} elseif ( 'cancelled' === $status ) {
				$this->handle_itn_payment_cancelled( $data, $order );
			}
		} // End if().

		$this->log( PHP_EOL
			. '----------'
			. PHP_EOL . 'End transaction call'
			. PHP_EOL . '----------'
		);

	}

	/**
	 * Handle logging the order details.
	 *
	 * @since 1.4.5
	 */
	public function log_order_details( $order ) {
		if ( version_compare( WC_VERSION,'3.0.0', '<' ) ) {
			$customer_id = get_post_meta( $order->get_id(), '_customer_user', true );
		} else {
			$customer_id = $order->get_user_id();
		}

		$details = "Order Details:"
		. PHP_EOL . 'customer id:' . $customer_id
		. PHP_EOL . 'order id:   ' . $order->get_id()
		. PHP_EOL . 'parent id:  ' . $order->get_parent_id()
		. PHP_EOL . 'status:     ' . $order->get_status()
		. PHP_EOL . 'total:      ' . $order->get_total()
		. PHP_EOL . 'currency:   ' . $order->get_currency()
		. PHP_EOL . 'key:        ' . $order->get_order_key()
		. "";

		$this->log( $details );
	}

	/**
	 * This function handles payment complete request by MetaMask.
	 * @version 1.4.3 Subscriptions flag
	 *
	 * @param array $data should be from the gateway callback.
	 * @param WC_Order $order
	 */
	public function handle_itn_payment_complete( $data, $order ) {
		$this->log( '- Complete' );
		$order->add_order_note( __( 'MetaMask payment completed', 'woocommerce-gateway-metamask' ) );
		$order->update_meta_data( 'metamask_amount_paid', $data['amount'] );
		$order->update_meta_data( 'metamask_message', $data['description'] );
		$order->update_meta_data( 'metamask_message_signature', $data['wallet_signature'] );
		$order->update_meta_data( 'metamask_wallet_address', $data['wallet_address'] );
		$order->update_meta_data( 'metamask_transaction_hash', $data['payment_hash'] );

		$order->payment_complete();

		$debug_email   = $this->get_option( 'debug_email', get_option( 'admin_email' ) );
		$vendor_name    = get_bloginfo( 'name', 'display' );
		$vendor_url     = home_url( '/' );
		if ( $this->send_debug_email ) {
			$subject = esc_html( 'MetaMask transaction completed', 'woocommerce-gateway-metamask' );
			$body =
				"Hi,\n\n"
				. "A transaction has been completed\n"
				. "------------------------------------------------------------\n"
				. 'Site: ' . esc_html( $vendor_name ) . ' (' . esc_url( $vendor_url ) . ")\n"
				. 'Purchase ID: ' . esc_html( $data['m_payment_id'] ) . "\n"
				. 'MetaMask Wallet Address: ' . esc_html( $data['wallet_address'] ) . "\n"
				. 'MetaMask Message Signature: ' . esc_html( $data['wallet_signature'] ) . "\n"
				. 'MetaMask Payment Status: ' . esc_html( $data['payment_status'] ) . "\n"
				. 'MetaMask Transaction Hash: ' . esc_html( $data['payment_hash'] ) . "\n"				
				. 'Order Status Code: ' . self::get_order_prop( $order, 'status' );
			wp_mail( $debug_email, $subject, $body );
		}
	}

	/**
	 * @param $data
	 * @param $order
	 */
	public function handle_itn_payment_failed( $data, $order ) {
		$this->log( '- Failed' );
		/* translators: 1: payment status */
		$order->update_status( 'failed', sprintf( __( 'Payment %s via MetaMask.', 'woocommerce-gateway-metamask' ), strtolower( sanitize_text_field( $data['payment_status'] ) ) ) );
		$debug_email   = $this->get_option( 'debug_email', get_option( 'admin_email' ) );
		$vendor_name    = get_bloginfo( 'name', 'display' );
		$vendor_url     = home_url( '/' );

		if ( $this->send_debug_email ) {
			$subject = esc_html( 'MetaMask Transaction on your site', 'woocommerce-gateway-metamask' );
			$body =
				"Hi,\n\n" .
				"A failed MetaMask transaction on your website requires attention\n" .
				"------------------------------------------------------------\n" .
				'Site: ' . esc_html( $vendor_name ) . ' (' . esc_url( $vendor_url ) . ")\n" .
				'Purchase ID: ' . self::get_order_prop( $order, 'id' ) . "\n" .
				'User ID: ' . self::get_order_prop( $order, 'user_id' ) . "\n" .
				'MetaMask Wallet Address: ' . esc_html( $data['wallet_address'] ) . "\n" .
				'MetaMask Message Signature: ' . esc_html( $data['wallet_signature'] ) . "\n" .
				'MetaMask Payment Status: ' . esc_html( $data['payment_status'] ) . "\n" .
			wp_mail( $debug_email, $subject, $body );
		}

		wc_add_notice( $data['metamask_message'], 'error' );	//notice type can be 'error', 'notice' or 'success'
	}

	/**
	 * @since 1.4.0 introduced
	 * @param $data
	 * @param $order
	 */
	public function handle_itn_payment_pending( $data, $order ) {
		$this->log( '- Pending' );
		// Need to wait for "Completed" before processing
		/* translators: 1: payment status */
		$order->update_status( 'on-hold', sprintf( __( 'Payment %s via MetaMask.', 'woocommerce-gateway-metamask' ), strtolower( sanitize_text_field( $data['payment_status'] ) ) ) );
	}

	/**
	 * @since 1.4.0 introduced.
	 * @param      $api_data
	 * @param bool $sort_data_before_merge? default true.
	 * @param bool $skip_empty_values Should key value pairs be ignored when generating signature?  Default true.
	 *
	 * @return string
	 */
	protected function _generate_parameter_string( $api_data, $sort_data_before_merge = true, $skip_empty_values = true ) {

		if ( $sort_data_before_merge ) {
			ksort( $api_data );
		}

		// concatenate the array key value pairs.
		$parameter_string = '';
		foreach ( $api_data as $key => $val ) {

			if ( $skip_empty_values && empty( $val ) ) {
				continue;
			}
			
			// We only want to generate signature using these keys and their values - amount, custom_str1, custom_str3, m_payment_id
			if ( $key !== 'return_url' && $key !== 'cancel_url' && $key !== 'notify_url' && $key !== 'item_name' && $key !== 'item_description' && $key !== 'description' && $key !== 'wallet_address' && $key !== 'wallet_signature' && $key !== 'payment_hash' && $key != 'block_number' && $key !== 'gas_used'  && $key !== 'signature' && $key !== 'payment_status' ) {
				$val = urlencode( $val );
				$parameter_string .= "$key=$val&";
			}
		}
		// when not sorting passphrase should be added to the end before md5
		if ( $sort_data_before_merge ) {
			$parameter_string = rtrim( $parameter_string, '&' );
		} else {
			$parameter_string = rtrim( $parameter_string, '&' );
		}

		return $parameter_string;
	}

	/**
	 * Setup constants.
	 *
	 * Setup common values and messages used by the MetaMask gateway.
	 *
	 * @since 1.0.0
	 */
	public function setup_constants() {
		// Create user agent string.
		define( 'PF_SOFTWARE_NAME', 'WooCommerce' );
		define( 'PF_SOFTWARE_VER', WC_VERSION );
		define( 'PF_MODULE_NAME', 'WooCommerce-MetaMask-Free' );
		define( 'PF_MODULE_VER', $this->version );

		// Features
		// - PHP
		$pf_features = 'PHP ' . phpversion() . ';';

		// - cURL
		if ( in_array( 'curl', get_loaded_extensions() ) ) {
			define( 'PF_CURL', '' );
			$pf_version = curl_version();
			$pf_features .= ' curl ' . $pf_version['version'] . ';';
		} else {
			$pf_features .= ' nocurl;';
		}

		// Create user agrent
		define( 'PF_USER_AGENT', PF_SOFTWARE_NAME . '/' . PF_SOFTWARE_VER . ' (' . trim( $pf_features ) . ') ' . PF_MODULE_NAME . '/' . PF_MODULE_VER );

		// General Defines
		define( 'PF_TIMEOUT', 15 );
		define( 'PF_EPSILON', 0.01 );

		// Messages
		// Error
		define( 'PF_ERR_AMOUNT_MISMATCH', __( 'Amount mismatch', 'woocommerce-gateway-metamask' ) );
		define( 'PF_ERR_BAD_ACCESS', __( 'Bad access of page', 'woocommerce-gateway-metamask' ) );
		define( 'PF_ERR_BAD_SOURCE_IP', __( 'Bad source IP address', 'woocommerce-gateway-metamask' ) );
		define( 'PF_ERR_CONNECT_FAILED', __( 'Failed to connect to MetaMask', 'woocommerce-gateway-metamask' ) );
		define( 'PF_ERR_INVALID_SIGNATURE', __( 'Security signature mismatch', 'woocommerce-gateway-metamask' ) );
		define( 'PF_ERR_MERCHANT_ID_MISMATCH', __( 'Merchant ID mismatch', 'woocommerce-gateway-metamask' ) );
		define( 'PF_ERR_NO_SESSION', __( 'No saved session found for ITN transaction', 'woocommerce-gateway-metamask' ) );
		define( 'PF_ERR_ORDER_ID_MISSING_URL', __( 'Order ID not present in URL', 'woocommerce-gateway-metamask' ) );
		define( 'PF_ERR_ORDER_ID_MISMATCH', __( 'Order ID mismatch', 'woocommerce-gateway-metamask' ) );
		define( 'PF_ERR_ORDER_INVALID', __( 'This order ID is invalid', 'woocommerce-gateway-metamask' ) );
		define( 'PF_ERR_ORDER_NUMBER_MISMATCH', __( 'Order Number mismatch', 'woocommerce-gateway-metamask' ) );
		define( 'PF_ERR_ORDER_PROCESSED', __( 'This order has already been processed', 'woocommerce-gateway-metamask' ) );
		define( 'PF_ERR_PDT_FAIL', __( 'PDT query failed', 'woocommerce-gateway-metamask' ) );
		define( 'PF_ERR_PDT_TOKEN_MISSING', __( 'PDT token not present in URL', 'woocommerce-gateway-metamask' ) );
		define( 'PF_ERR_SESSIONID_MISMATCH', __( 'Session ID mismatch', 'woocommerce-gateway-metamask' ) );
		define( 'PF_ERR_UNKNOWN', __( 'Unkown error occurred', 'woocommerce-gateway-metamask' ) );

		// General
		define( 'PF_MSG_OK', __( 'Payment was successful', 'woocommerce-gateway-metamask' ) );
		define( 'PF_MSG_FAILED', __( 'Payment has failed', 'woocommerce-gateway-metamask' ) );
		define( 'PF_MSG_PENDING', __( 'The payment is pending. Please note, you will receive another Instant Transaction Notification when the payment status changes to "Completed", or "Failed"', 'woocommerce-gateway-metamask' ) );

		do_action( 'woocommerce_gateway_metamask_setup_constants' );
	}

	/**
	 * Log system processes.
	 * @since 1.0.0
	 */
	public function log( $message ) {

		if ( 'yes' === $this->get_option( 'testmode' ) || $this->enable_logging ) {
			if ( empty( $this->logger ) ) {
				$this->logger = new WC_Logger();
			}
			$this->logger->add( 'metamask', $message );
		}
	}

	/**
	 * validate_signature()
	 *
	 * Validate the signature against the returned data.
	 *
	 * @param array $data
	 * @param string $signature
	 * @since 1.0.0
	 * @return string
	 */
	public function validate_signature( $data, $signature ) {
	    // Compare the signature generated by payment gateway form with the signature generated after MetaMask payment return
		$result = $data['signature'] === $signature;	

	    $this->log( 'Signature = ' . ( $result ? 'valid' : 'invalid' ) );
	    return $result;
	}

	/**
	 * amounts_equal()
	 *
	 * Checks to see whether the given amounts are equal using a proper floating
	 * point comparison with an Epsilon which ensures that insignificant decimal
	 * places are ignored in the comparison.
	 *
	 * eg. 100.00 is equal to 100.0001
	 *
	 * @author Jonathan Smit
	 * @param $amount1 Float 1st amount for comparison
	 * @param $amount2 Float 2nd amount for comparison
	 * @since 1.0.0
	 * @return bool
	 */
	public function amounts_equal( $amount1, $amount2 ) {
		return ! ( abs( floatval( $amount1 ) - floatval( $amount2 ) ) > PF_EPSILON );
	}

	/**
	 * Get order property with compatibility check on order getter introduced
	 * in WC 3.0.
	 *
	 * @since 1.4.1
	 *
	 * @param WC_Order $order Order object.
	 * @param string   $prop  Property name.
	 *
	 * @return mixed Property value
	 */
	public static function get_order_prop( $order, $prop ) {
		switch ( $prop ) {
			case 'order_total':
				$getter = array( $order, 'get_total' );
				break;
			default:
				$getter = array( $order, 'get_' . $prop );
				break;
		}

		return is_callable( $getter ) ? call_user_func( $getter ) : $order->{ $prop };
	}

	/**
	 * Gets user-friendly error message strings from keys
	 *
	 * @param   string  $key  The key representing an error
	 *
	 * @return  string        The user-friendly error message for display
	 */
	public function get_error_message( $key ) {
		switch ( $key ) {
			case 'wc-gateway-metamask-error-invalid-currency':
				return sprintf( __( 'Your site uses the currency %s that is not supported.', 'woocommerce-gateway-metamask' ), get_woocommerce_currency() );
			case 'wc-gateway-metamask-error-no-currency':
				return __( 'Fill in your cryptocurrency symbol.', 'woocommerce-gateway-metamask' );
			default:
				return '';
		}
	}

	/**
	*  Show possible admin notices
	*/
	public function admin_notices() {

		// Get requirement errors.
		$errors_to_show = $this->check_requirements();

		// If everything is in place, don't display it.
		if ( ! count( $errors_to_show ) ) {
			return;
		}

		// If the gateway isn't enabled, don't show it.
		if ( "no" ===  $this->enabled ) {
			return;
		}

		// Use transients to display the admin notice once after saving values.
		if ( ! get_transient( 'wc-gateway-metamask-admin-notice-transient' ) ) {
			set_transient( 'wc-gateway-metamask-admin-notice-transient', 1, 1);

			echo '<div class="notice notice-error is-dismissible"><p>'
				. __( 'Please fix the problems below:', 'woocommerce-gateway-metamask' ) . '</p>'
				. '<ul style="list-style-type: disc; list-style-position: inside; padding-left: 2em;">'
				. array_reduce( $errors_to_show, function( $errors_list, $error_item ) {
					$errors_list = $errors_list . PHP_EOL . ( '<li>' . $this->get_error_message($error_item) . '</li>' );
					return $errors_list;
				}, '' )
				. '</ul></p></div>';
		}
	}

}
