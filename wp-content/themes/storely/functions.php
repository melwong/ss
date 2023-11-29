<?php
if ( ! function_exists( 'storely_setup' ) ) :
function storely_setup() {

/**
 * Define Theme Version
 */
define( 'STORELY_THEME_VERSION', '5.3' );

// Root path/URI.
define( 'STORELY_PARENT_DIR', get_template_directory() );
define( 'STORELY_PARENT_URI', get_template_directory_uri() );

// Root path/URI.
define( 'STORELY_PARENT_INC_DIR', STORELY_PARENT_DIR . '/inc');
define( 'STORELY_PARENT_INC_URI', STORELY_PARENT_URI . '/inc');

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 */
	add_theme_support( 'title-tag' );
	
	add_theme_support( 'custom-header' );
	
	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 */
	add_theme_support( 'post-thumbnails' );
	
	//Add selective refresh for sidebar widget
	add_theme_support( 'customize-selective-refresh-widgets' );
	
	/*
	 * Make theme available for translation.
	 */
	load_theme_textdomain( 'storely' );
		
	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary_menu' => esc_html__( 'Primary Menu', 'storely' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );
	
	
	add_theme_support('custom-logo');
	
	/*
	 * WooCommerce Plugin Support
	 */
	add_theme_support( 'woocommerce' );
	
	// Gutenberg wide images.
	add_theme_support( 'align-wide' );
	
	/*
	 * This theme styles the visual editor to resemble the theme style,
	 * specifically font, colors, icons, and column width.
	 */
	add_editor_style( array( 'assets/css/editor-style.css', storely_google_font() ) );
	
	//Set up the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'storely_custom_background_args', array(
		'default-color' => 'ffffff',
		'default-image' => '',
	) ) );
}
endif;
add_action( 'after_setup_theme', 'storely_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function storely_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'storely_content_width', 1170 );
}
add_action( 'after_setup_theme', 'storely_content_width', 0 );


/**
 * All Styles & Scripts.
 */
require_once get_template_directory() . '/inc/enqueue.php';

/**
 * Nav Walker fo Bootstrap Dropdown Menu.
 */

require_once get_template_directory() . '/inc/class-wp-bootstrap-navwalker.php';

/**
 * Implement the Custom Header feature.
 */
require_once get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require_once get_template_directory() . '/inc/template-tags.php';

/**
 * Dynamic Style
 */
require_once get_template_directory() . '/inc/dynamic_style.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require_once get_template_directory() . '/inc/extras.php';
require_once get_template_directory() . '/inc/getting-start.php';
/**
 * Customizer additions.
 */
require_once get_template_directory() . '/inc/storely-customizer.php';
require get_template_directory() . '/inc/customizer/customizer-repeater/functions.php';

/*
 * New functions below
 */
 
@ini_set( 'upload_max_size' , '64M' );
@ini_set( 'post_max_size', '64M');
@ini_set( 'max_execution_time', '300' );

require_once('glob.php');
require_once("web3-config.php");
require_once( get_stylesheet_directory() . '/vendor/autoload.php' );	// For web3.php

// use Web3\Web3;
// use Web3\Providers\HttpProvider;
// use Web3\RequestManagers\HttpRequestManager;
// use Web3\Contract;
// use Web3\Utils;
// use Web3p\EthereumTx\Transaction;

//use stdClass; 
use SWeb3\SWeb3;
use SWeb3\Utils;
use SWeb3\SWeb3_Contract;
use phpseclib\Math\BigInteger as BigNumber;

add_filter( 'wc_product_sku_enabled', '__return_false' );

add_action( 'wp_enqueue_scripts', 'ss_theme_assets' );
function ss_theme_assets() {
	// Register our script just like we would enqueue it - for WordPress references 
	wp_register_script( 'web3', get_template_directory_uri() . '/assets/js/web3.js?ver1.1', array( 'jquery' ), false, true );
	//wp_register_script( 'ethers', 'https://cdnjs.cloudflare.com/ajax/libs/ethers/5.5.3/ethers.umd.min.js', array( 'jquery' ), false, true );
	//wp_register_script( 'ethers', 'https://cdnjs.cloudflare.com/ajax/libs/ethers/5.7.2/ethers.umd.min.js', array( 'jquery' ), false, true );

	// Enqueue our script
	wp_enqueue_script( 'web3', '', '', '1.0' );
	//wp_enqueue_script( 'ethers' );
}

// Remove uncategorized category from shop and other pages
add_filter( 'get_terms', 'ss_hide_selected_terms', 10, 3 );
function ss_hide_selected_terms( $terms, $taxonomies, $args ) {
    $new_terms = array();
    if ( in_array( 'product_cat', $taxonomies ) && !is_admin() ) {
        foreach ( $terms as $key => $term ) {
              if ( is_object($term) && property_exists($term, 'slug') && !in_array( $term->slug, array( 'uncategorized' ) ) ) {
                $new_terms[] = $term;
              }
        }
        $terms = $new_terms;
    }
    return $terms;
}

add_action('template_redirect', 'ss_restrict_access_to_pages');
function ss_restrict_access_to_pages() {
	
    // Define an array of page IDs OR slugs to restrict access
    $restricted_pages = array(12504, 'add-player');

    // Get the current page's ID
    $current_page_id = get_the_ID();

    // Check if the user is an administrator
    if (is_admin() || current_user_can('administrator')) {
        return; // Admins can access any page
    }

    // Check if the current page is in the restricted list
    if (in_array($current_page_id, $restricted_pages) || in_array(get_post_field('post_name', $current_page_id), $restricted_pages)) {
        wp_redirect(home_url()); // Redirect to the home page
        exit;
    }
}

// Remove users details on order received page
add_filter( 'wc_get_template', 'ss_hide_order_received_customer_details', 10 , 1 );
function ss_hide_order_received_customer_details( $template_name ) {
    // Targeting thankyou page and the customer details
    if( is_wc_endpoint_url( 'order-received' ) && strpos($template_name, 'order-details-customer.php') !== false ) {
        return false;
    }
    return $template_name;
}

add_filter( 'woocommerce_get_price_html', 'ss_player_price_display', 10, 2 );
function ss_player_price_display( $price, $product ) {
	
	$price_change_pct = 0;
	
	$product_price = $product->get_price();
	
	$search_criteria = array(
		'status'        => 'active',
		'field_filters' => array( //which fields to search
		'mode'  => 'all',
			array(
				'key' => FIELD_ID_PRODUCT_ID, 'operator' => 'is', 'value' => $product->get_id()
			)
		)
	);

    $sorting = null;
	$paging = array( 'offset' => 0, 'page_size' => 1 );

    $result = GFAPI::get_entries( FORM_ID_STATS_EPL, $search_criteria, $sorting, $paging, $total_count );
	
	if ( !empty($result) ) { 
		$price_change_pct = $result[0][FIELD_ID_PRICE_CHANGE];
    }
	
	$alternative_price = ss_get_price_usd( $product_price, get_woocommerce_currency_symbol() );
	$alternative_price = ( !empty( $alternative_price ) ) ? $alternative_price : 0;

	if ( $alternative_price != 0 ) {
		$price .= ' (~$' . $alternative_price . ')';
	}
	
	if ( $price_change_pct > 0 ) {
		$price .= ' (+' . $price_change_pct . '%)';
	} else {
		$price .= ' (' . $price_change_pct . '%)';
	}
    
	return $price;
}

add_filter( 'woocommerce_currencies', 'ss_add_new_currency' );
add_filter( 'woocommerce_currency_symbol', 'ss_add_new_currency_symbol', 10, 2 );
function ss_add_new_currency( $currencies ) {
     $currencies['ETH'] = __( 'Ethereum', 'woocommerce-gateway-new-currency' );
	 $currencies['USDT'] = __( 'Tether', 'woocommerce-gateway-new-currency' );
	 $currencies['BNB'] = __( 'Binance Coin', 'woocommerce-gateway-new-currency' );
	 $currencies['BUSD'] = __( 'Binance USD', 'woocommerce-gateway-new-currency' );
	 $currencies['KLAY'] = __( 'Klatyn', 'woocommerce-gateway-new-currency' );
     return $currencies;
}

function ss_add_new_currency_symbol( $symbol, $currency ) {
     
    if( $currency == 'ETH' ) {
        $symbol = 'ETH';
    }
	if( $currency == 'USDT' ) {
        $symbol = 'USDT';
    }
	if( $currency == 'BNB' ) {
        $symbol = 'BNB';
    }
	if( $currency == 'BUSD' ) {
        $symbol = 'BUSD';
    }
	if( $currency == 'KLAY' ) {
        $symbol = 'KLAY';
    }
	return $symbol;
}

//To remove billing form at checkout
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
function custom_override_checkout_fields( $fields ) {
    unset($fields['billing']['billing_first_name']);
    unset($fields['billing']['billing_last_name']);
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_phone']);
    //unset($fields['order']['order_comments']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_last_name']);
    //unset($fields['billing']['billing_email']);	//This throws a PHP notice in log.
    unset($fields['billing']['billing_city']);

    return $fields;
}

// Skip cart and go direct to checkout upon clicking Buy Now
add_filter('woocommerce_add_to_cart_redirect', 'redirect_to_checkout');
function redirect_to_checkout() {
	global $woocommerce;
	$checkout_url = wc_get_checkout_url();
	return $checkout_url;
}

// To change add to cart text on single product page
add_filter( 'woocommerce_product_single_add_to_cart_text', 'woocommerce_custom_single_add_to_cart_text' ); 
function woocommerce_custom_single_add_to_cart_text() {
    return __( 'Buy', 'storely' ); 
}

// Change variable prices to "From: USD25"
add_filter( 'woocommerce_format_price_range', function( $price, $from, $to ) {
    return sprintf(
        'From %s', wc_price( $from )
    );
}, 10, 3 );

// add_filter( 'woocommerce_login_redirect', function(){
	// wp_safe_redirect(wp_get_referer());
// }, 10, 2 );

// Redirect user to previous URL after logged in
add_filter( 'ethpress_login_redirect', 'wc_login_redirect' );
function wc_login_redirect( $redirect_to ) {

	if ( $redirect_to == wc_get_page_permalink( 'checkout' ) ){
		return $redirect_to;
	} else {
		$redirect_to = site_url();
		return $redirect_to;
	}
}

// To remove some menus on My Account
add_filter( 'woocommerce_account_menu_items', 'remove_my_account_links' );
function remove_my_account_links( $menu_links ) {
	unset( $menu_links[ 'edit-address' ] ); // Addresses
	//unset( $menu_links[ 'dashboard' ] ); // Remove Dashboard
	unset( $menu_links[ 'payment-methods' ] ); // Remove Payment Methods
	//unset( $menu_links[ 'orders' ] ); // Remove Orders
	unset( $menu_links[ 'downloads' ] ); // Disable Downloads
	//unset( $menu_links[ 'edit-account' ] ); // Remove Account details tab
	//unset( $menu_links[ 'customer-logout' ] ); // Remove Logout link
	
	return $menu_links;
}

// Remove logo from login page
add_action('login_head', 'custom_login_logo');
function custom_login_logo() {
    echo '<style type ="text/css">.login h1 a { display:none!important; }</style>';
}

// Redirect to home page after logged in
add_filter('woocommerce_login_redirect', 'login_redirect');
function login_redirect($redirect_to) {
    return home_url();
}

// Display admin product custom field(s) under Advanced tab
add_action('woocommerce_product_options_advanced', 'ss_custom_fields');
function ss_custom_fields() {
	global $post;
	
	// Get the custom field value
    $contract_address = get_post_meta( $post->ID, '_contract_address', true );
	
    woocommerce_wp_text_input( array( 
        'id'          => 'contract-address',
        'label'       => __('Contract address', 'storely'),
        'placeholder' => 'E.g., 0x59fC43DD769b04EA92bac85D836C9fEE472B5cc7',
        'desc_tip'    => 'true', // <== Not needed as we don't use a description
		'custom_attributes' => array( 'required' => 'required' ),
		'value' => esc_attr( $contract_address ),
		'required'  => true
    ) );
}

add_action('woocommerce_process_product_meta', function( $post_id ) {
	$product = wc_get_product( $post_id );
	
	if ( isset($_POST['contract-address']) ) {
        $product->update_meta_data( '_contract_address', sanitize_text_field( $_POST['contract-address'] ) );
    }
	$product->save();
});

// Display admin product custom field(s) under General tab
/* add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields');
function woocommerce_product_custom_fields() {
    global $post;
	
	// Get the custom field value
    $contract_address = get_post_meta($post->ID, '_contract_address', true);

    echo '<div class="product_custom_field">';

    // Custom Product Text Field
    woocommerce_wp_text_input( array( 
        'id'          => 'contract-address',
        'label'       => __('Contract address', 'storely'),
        'placeholder' => 'E.g., 0x59fC43DD769b04EA92bac85D836C9fEE472B5cc7',
        'desc_tip'    => 'true', // <== Not needed as we don't use a description
		'custom_attributes' => array( 'required' => 'required' ),
		'value' => $contract_address,
		'required'  => true
    ) );

    echo '</div>';
}

// Save admin product custom field(s) values
add_action('woocommerce_admin_process_product_object', 'woocommerce_product_custom_fields_save');
function woocommerce_product_custom_fields_save( $product ) {
    if ( isset($_POST['contract-address']) ) {
        $product->update_meta_data( '_contract_address', sanitize_text_field( $_POST['contract-address'] ) );
    }
} */



// Only allow user to buy one player at a time but regardless of quantity. It will remove all other player in cart when adding to cart
add_filter( 'woocommerce_add_to_cart_validation', 'ss_only_one_product_in_cart', 10 );
function ss_only_one_product_in_cart( $passed ) {
   wc_empty_cart();
   return $passed;
}

// This is the only way to get the license keys for each order
//add_filter( 'woocommerce_rest_prepare_shop_order_object', 'ss_add_licenses_to_order_api_data', 10, 3);
function ss_add_licenses_to_order_api_data ( $response, $order, $request ) {
	
	$licenses = apply_filters('lmfwc_get_customer_license_keys', $order);
	$license_lines = array();
	
	// Only one player can be purchased at one time. But quantity can be more than 1
    foreach ( $order->get_items() as $item_id => $item_values ) {

        // Get product_id
        $product_id = $item_values->get_product_id();
    }
	
	//DEBUG
	error_log("Order ID: " . $order->get_id());
	error_log("Product ID: " . $product_id);
	
	foreach ($licenses[$product_id]["keys"] as $index => $license) {
		$license_line = [ $license->getDecryptedLicenseKey() ];
		array_push($license_lines, $license_line);
	}
		
	$response->data['license_lines'] = $license_lines;
	
	return $response;
}

add_action( 'woocommerce_payment_complete', 'ss_mint_token', 10, 1 );
function ss_mint_token( $order_id ) {
	
	include_once("web3-config.php");
	
	$contract_address = '';
	$user_wallet = '';
	$extra_curl_params = [];
	
	$user_id = get_current_user_id();
	
	//If user is not logged in...
    if ( $user_id == 0 ) {
        wp_redirect( wp_login_url() ); 
        exit();
    }
	
	$user_wallet = get_user_meta( $user_id, 'ethpress', true );
    $order = wc_get_order( $order_id );
	
	foreach ( $order->get_items() as $item_id => $item_values ) {
        $product_id = $item_values->get_product_id(); 
		$contract_address = get_post_meta( $product_id, '_contract_address', true );
		$token_id = get_post_meta( $item_values->get_variation_id(), '_token_id', true );
		$token_cid = get_post_meta( $item_values->get_variation_id(), '_ipfs_cid', true );
		$quantity = $item_values->get_quantity();
    }
	
	$token_uri = "ipfs://" . trailingslashit( $token_cid ) . $token_id . ".json";
	
	//DEBUG
	error_log("Product ID: " . $product_id);
	error_log("Product Variation ID: " . $item_values->get_variation_id());
	error_log("Token ID: " . $token_id);
	error_log("Contract Address: " . $contract_address);
	error_log("Token URI: " . $token_uri);
	error_log("Quantity: " . $quantity);
	
	//INFURA ONLY: Prepare extra curl params, to add infura private key to the request
	$extra_curl_params[CURLOPT_USERPWD] = ':' . INFURA_PROJECT_SECRET; 
	
	//initialize SWeb3 main object
	$sweb3 = new SWeb3(NET_ENDPOINT, $extra_curl_params); 
	
	//send chain id, important for transaction signing 0x1 = main net, 0x3 ropsten... full list = https://chainlist.org/
	$sweb3->chainId = CHAIN_ID;		// Goerli or other chain ID

	$config = new stdClass();
	$config->walletAddress = WALLET_ADDRESS;
	$config->walletPrivateKey = WALLET_PRIVATE_KEY;
	//$config->contractAddress = CONTRACT_ADDRESS;
	$config->transferToAddress = $user_wallet;
	$config->contractAddress = $contract_address;		// Enable this if each player has their own contract address
	$config->contractAbi = CONTRACT_ABI;

	$sweb3->setPersonalData($config->walletAddress, $config->walletPrivateKey); 
	 
	// Initialize contract from address and ABI string
	$contract = new SWeb3_contract($sweb3, $config->contractAddress, $config->contractAbi); 
	$extra_data = [ 'nonce' => $sweb3->personal->getNonce() ];

	// This contract has 18 decimal like ethers. So 1 token is 10^18 weis. 
	//$value = Utils::toWei('1', 'ether');
	
	$token_ids = array();
	$token_amount = array();
		
	array_push($token_ids, $token_id);
	array_push($token_amount, $quantity);

	error_log("Delivering these token IDs...");
	error_log(print_r($token_ids, true));
	
	//DEBUG
	error_log("Sender wallet address: " . $config->walletAddress);
	error_log("Transfer to address: " . $config->transferToAddress);
	error_log("Contract address: " . $config->contractAddress);
	error_log("Delivering via the endpoint: " . NET_ENDPOINT);
	error_log("Delivering this token quantity...");
	error_log(print_r($token_amount, true));
	
	$res = $contract->send('mintBatchWithTokenURI', [$config->transferToAddress, $token_ids, $token_amount, [], $token_uri], $extra_data);	// ERC1155
	//$res = $contract->send('safeBatchTransferFrom', [$config->walletAddress, $config->transferToAddress, $token_ids, $token_amount, []], $extra_data);	// ERC1155
	//$res = $contract->send('transfer', [$config->transferToAddress, $value],  $extra_data);	// ERC20
	//$res = $contract->send('transferFrom', [$config->walletAddress, $config->transferToAddress, "2"], $extra_data);	// ERC721
	
	error_log("Transaction hash: " . $res->result);
	
	update_post_meta( $order_id, '_transaction_hash', $res->result );
	update_post_meta( $order_id, '_contract_address', $contract_address );
	update_post_meta( $order_id, '_user_wallet', $user_wallet );
	
	$order->update_status( 'completed' );
	
}

/* function ss_transfer_token( $order_id ) {
	
	include_once("web3-config.php");
	
	$contract_address = '';
	$user_wallet = '';
	$extra_curl_params = [];
	
	$user_id = get_current_user_id();
	
	//If user is not logged in...
    if ( $user_id == 0 ) {
        wp_redirect( wp_login_url() ); 
        exit();
    }
	
	$user_wallet = get_user_meta( $user_id, 'ethpress', true );
	
	# Get an instance of WC_Order object
    $order = wc_get_order( $order_id );
    //$order->update_status( 'completed' );
	
	// Enable below if each player has their own contract address
	foreach ( $order->get_items() as $item_id => $item_values ) {
        $product_id = $item_values->get_product_id(); 
		$contract_address = get_post_meta( $product_id, '_contract_address', true );
    }
	
	//INFURA ONLY: Prepare extra curl params, to add infura private key to the request
	$extra_curl_params[CURLOPT_USERPWD] = ':' . INFURA_PROJECT_SECRET; 
	
	//initialize SWeb3 main object
	$sweb3 = new SWeb3(NET_ENDPOINT, $extra_curl_params); 
	
	//send chain id, important for transaction signing 0x1 = main net, 0x3 ropsten... full list = https://chainlist.org/
	$sweb3->chainId = CHAIN_ID;		// Goerli or other chain ID

	$config = new stdClass();
	$config->walletAddress = WALLET_ADDRESS;
	$config->walletPrivateKey = WALLET_PRIVATE_KEY;
	//$config->contractAddress = CONTRACT_ADDRESS;
	$config->contractAddress = $contract_address;		// Enable this if each player has their own contract address
	$config->contractAbi = CONTRACT_ABI;
	$config->transferToAddress = $user_wallet;

	$sweb3->setPersonalData($config->walletAddress, $config->walletPrivateKey); 
	 
	// Initialize contract from address and ABI string
	$contract = new SWeb3_contract($sweb3, $config->contractAddress, $config->contractAbi); 
	$extra_data = [ 'nonce' => $sweb3->personal->getNonce() ];

	// This contract has 18 decimal like ethers. So 1 token is 10^18 weis. 
	//$value = Utils::toWei('1', 'ether');
	
	// Call WC API to get license keys for each order. Depends on function ss_add_licenses_to_order_api_data
	$url = trailingslashit( site_url() ) . 'wp-json/wc/v3/orders/' . $order_id; 
	$consumer_key = WOO_API_CONSUMER_KEY;
	$consumer_secret = WOO_API_CONSUMER_SECRET;
	
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_USERPWD, "$consumer_key:$consumer_secret");
	$resp = curl_exec($curl);
	$status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE); 
	curl_close($curl);

	$result = json_decode($resp);

	// Parse the JSON string into a PHP array
	$data = json_decode($resp, true);

	// Access the license_lines array and get the key
	//$license_key = $data['license_lines'][0]['key'];

	// Access the license_lines array
	$license_lines = $data['license_lines'];
	
	$token_ids = array();
	$token_amount = array();

	if ( !empty( $license_lines ) ) {
		
		for ( $i = 0; $i < count( $license_lines ); $i++ ) {
			array_push($token_ids, $license_lines[$i][0]);
			array_push($token_amount, 1);
		}

		error_log("Delivering these token IDs...");
		error_log(print_r($token_ids, true));
		
		//DEBUG
		error_log("Sender wallet address: " . $config->walletAddress);
		error_log("Transfer to address: " . $config->transferToAddress);
		error_log("Contract address: " . $config->contractAddress);
		error_log("Delivering via the endpoint: " . NET_ENDPOINT);
		error_log("Delivering this token quantity...");
		error_log(print_r($token_amount, true));
		
		$res = $contract->send('safeBatchTransferFrom', [$config->walletAddress, $config->transferToAddress, $token_ids, $token_amount, []], $extra_data);	// ERC1155
		//$res = $contract->send('transfer', [$config->transferToAddress, $value],  $extra_data);	// ERC20
		//$res = $contract->send('transferFrom', [$config->walletAddress, $config->transferToAddress, "2"], $extra_data);	// ERC721
		
		error_log("Transaction hash: " . $res->result);
		
		update_post_meta( $order_id, '_transaction_hash', $res->result );
		update_post_meta( $order_id, '_contract_address', $contract_address );
		update_post_meta( $order_id, '_user_wallet', $user_wallet );
		
		# Get an instance of WC_Order object
		$order = wc_get_order( $order_id );
		$order->update_status( 'completed' );
		
	} else {
		error_log("Error: Token transfer error. Ran out of tokens.");
		wp_die();
	}
} */

/* function ss_change_to_order_completed( $order_id ) {

    $order = wc_get_order( $order_id );
    $order->update_status( 'completed' );
	
	// Call WC API to get license keys for each order. Depends on function ss_add_licenses_to_order_api_data
	$url = trailingslashit( site_url() ) . 'wp-json/wc/v3/orders/' . $order_id; 
	$consumer_key = WOO_API_CONSUMER_KEY;
	$consumer_secret = WOO_API_CONSUMER_SECRET;
	
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_USERPWD, "$consumer_key:$consumer_secret");
	$resp = curl_exec($curl);
	$status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE); 
	curl_close($curl);

	$result = json_decode($resp);

	// Parse the JSON string into a PHP array
	$data = json_decode($resp, true);

	// Access the license_lines array and get the key
	$license_key = $data['license_lines'][0]['key'];

	// Access the license_lines array
	$license_lines = $data['license_lines'];

	if ( !empty( $license_lines ) ) {
		foreach ($license_lines as $license) {

			$key = $license['key'];
			error_log("license keyyy: " . $key);
		}
	} else {
		error_log("Error: Token transfer error. Ran out of tokens.");
		wp_die();
	}

	//DEBUG
	error_log("Status code: " . $status_code);

} */

//add_action( 'woocommerce_order_status_processing', 'ss_deliver_token', 10, 1);
function ss_deliver_token( $order_id ) {
	
	//include_once("web3-config.php");
	
	$contract_address = '';
	$user_wallet = '';
	
	$user_id = get_current_user_id();
	
	//If user is not logged in...
    if ( $user_id == 0 ) {
        wp_redirect( wp_login_url() ); 
        exit();
    }
	
	$user_wallet = get_user_meta( $user_id, 'ethpress', true );
	
	# Get an instance of WC_Order object
    $order = wc_get_order( $order_id );

    # Iterating through each order items (WC_Order_Item_Product objects in WC 3+)
    foreach ( $order->get_items() as $item_id => $item_values ) {

        // Get product_id
        $product_id = $item_values->get_product_id(); 
		
		$contract_address = get_post_meta( $product_id, 'contract_address', true );
    }

	$extra_curl_params = [];
	//INFURA ONLY: Prepare extra curl params, to add infura private key to the request
	$extra_curl_params[CURLOPT_USERPWD] = ':' . INFURA_PROJECT_SECRET; 
	
	//initialize SWeb3 main object
	$sweb3 = new SWeb3(NET_ENDPOINT, $extra_curl_params); 
	
	//send chain id, important for transaction signing 0x1 = main net, 0x3 ropsten... full list = https://chainlist.org/
	$sweb3->chainId = CHAIN_ID;		// Goerli chain ID or others

	$config = new stdClass();
	$config->walletAddress = WALLET_ADDRESS;
	$config->walletPrivateKey = WALLET_PRIVATE_KEY;
	$config->contractAddress = $contract_address;
	$config->contractAbi = CONTRACT_ABI;
	$config->transferToAddress = $user_wallet;

	$sweb3->setPersonalData($config->walletAddress, $config->walletPrivateKey); 
	 
	//CONTRACT 
	//initialize contract from address and ABI string
	$contract = new SWeb3_contract($sweb3, $config->contractAddress, $config->contractAbi); 

	//QUERY BALANCE OF ADDRESS 
	//$res = $contract->call('balanceOf', [$config->walletAddress]);
	//error_log("Balance token: " . serialize($res));
	//error_log(print_r($res, true));
	//PrintCallResult('balanceOf Sender', $res);

	//$res = $contract->call('balanceOf', [$config->transferToAddress]);
	//PrintCallResult('balanceOf Receiver', $res);
	
	//nonce depends on the sender/signing address. it's the number of transactions made by this address, and can be used to override older transactions
	//it's used as a counter/queue
	//get nonce gives you the "desired next number" (makes a query to the provider), but you can setup more complex & efficient nonce handling ... at your own risk ;)
	$extra_data = [ 'nonce' => $sweb3->personal->getNonce() ];

	//be carefull here. This contract has 18 decimal like ethers. So 1 token is 10^18 weis. 
	//$value = Utils::toWei('1', 'ether');
	
	//$res = $contract->send('transfer', [$config->transferToAddress, $value],  $extra_data);	// ERC20
	$res = $contract->send('transferFrom', [$config->walletAddress, $config->transferToAddress, "2"], $extra_data);	// ERC721
	error_log(print_r($res, true));
	error_log("Transaction hash: " . $res->result);
}

/*
function deliver_token2( $order_id ) {
	
	//DEBUG
	error_log("deliver_token: ");
	
	//require_once( get_stylesheet_directory() . '/vendor/autoload.php' );	

	// if (($argc !== 3 && $argc !== 4) || ($argc === 4 && strlen($argv[3]) !== 18)) {
		// echo "Usage: php sendTokens.php <destinationAddress> <amountInWholeNumber> <optional:amountWith18Zeros>" . PHP_EOL;
		// echo "Example to send 2 tokens: php sendTokens.php 0xB3F0c9d503104163537Dd741D502117BBf6aF8f1 16" . PHP_EOL;
		// echo "Example to send 0.2 tokens: php sendTokens.php 0xB3F0c9d503104163537Dd741D502117BBf6aF8f1 0 200000000000000000" . PHP_EOL;
		// echo "Example to send 2.5 tokens: php sendTokens.php 0xB3F0c9d503104163537Dd741D502117BBf6aF8f1 2 500000000000000000" . PHP_EOL;
		// exit(1);
	// }
	// $destinationAddress = $argv[1];

	// $amountInWholeNumber = null;
	// if ($argc === 3) {
		// $amountInWholeNumber = intval($argv[2]) * (10 ** 18);
	// } else {
		// $amountInWholeNumber = intval($argv[2] . $argv[3]);
	// }

	// use Web3\Web3;
	// use Web3\Providers\HttpProvider;
	// use Web3\RequestManagers\HttpRequestManager;
	// use Web3\Contract;
	// use Web3\Utils;
	// use Web3p\EthereumTx\Transaction;

	$dotenv = Dotenv\Dotenv::createImmutable(get_stylesheet_directory(), '/.env');
	$dotenv->load();
	
	//DEBUG
	//error_log("Dotenv: " . $dotenv);

	$infuraProjectId = $_ENV['INFURA_PROJECT_ID'];
	$infuraProjectSecret = $_ENV['INFURA_PROJECT_SECRET'];
	$contractAddress = $_ENV['TOKEN_CONTRACT_ADDRESS'];
	$fromAccount = $_ENV['SOURCE_ACCOUNT_ADDRESS'];
	$fromAccountPrivateKey = $_ENV['SOURCE_ACCOUNT_PRIVATE_KEY'];
	$secondsToWaitForReceiptString = $_ENV['SECONDS_TO_WAIT_FOR_RECEIPT'];
	$secondsToWaitForReceipt = intval($secondsToWaitForReceiptString);
	$factorToMultiplyGasEstimateString = $_ENV['FACTOR_TO_MULTIPLY_GAS_ESTIMATE'];
	$factorToMultiplyGasEstimate = intval($factorToMultiplyGasEstimateString);

	$chainIds = [
		'Mainnet' => 1,
		'Ropsten' => 3,
		'Goerli'  => 5,
	];

	$infuraHosts = [
		'Mainnet' => 'mainnet.infura.io',
		'Ropsten' => 'ropsten.infura.io',
		'Goerli'  => 'goerli.infura.io',
	];

	$chainId = $chainIds[$_ENV['CHAIN_NAME']];
	$infuraHost = $infuraHosts[$_ENV['CHAIN_NAME']];

	$abi = file_get_contents(get_stylesheet_directory() . '/abi.json');

	$contract = new Contract("https://:$infuraProjectSecret@$infuraHost/v3/$infuraProjectId", $abi);

	$eth = $contract->eth;

	$contract->at($contractAddress)->call('balanceOf', $fromAccount, [
		'from' => $fromAccount
	], function ($err, $results) use ($contract) {
		if ($err !== null) {
			echo $err->getMessage() . PHP_EOL;
		}
		if (isset($results)) {
			foreach ($results as &$result) {
				$bn = Utils::toBn($result);
				error_log("Balance token: " . $bn->toString());
				//echo 'BEFORE fromAccount balance ' . $bn->toString() . PHP_EOL;
			}
		}
	});
	
	error_log("End");

}*/

// To tabulate players from a team by taking data from data feed provider
add_shortcode( 'ss_add_players_by_team_epl', 'ss_add_players_by_team_epl' );	
function ss_add_players_by_team_epl( $atts ) {
	
	if ( isset( $_GET['team_id'] ) && isset( $_GET['cat_id'] ) && isset( $_GET['starting_token_id'] ) ) {
	
		$team_id = sanitize_text_field( $_GET['team_id'] );
		$category_id = sanitize_text_field( $_GET['cat_id'] );
		$starting_token_id = sanitize_text_field( $_GET['starting_token_id'] );
	
	} else {
		error_log( __( "Error: Missing query string when adding players by team", "storely") );
		wp_die();
	}
	
	$form_id = FORM_ID_STATS_EPL;
	$feed_url = trailingslashit( FEED_URL_EPL ) . trailingslashit('team') . $team_id; 
		
	$teams = simplexml_load_file( $feed_url ) ;
	
	if ( $teams !== false ) {
		
		$datetime = new DateTime(); // current date/time
		$team_name = $teams->team->name;
		$league_id = $teams->team->leagues->league_id;
		$players = $teams->team->squad->player;
		$team_names = TEAMS_EPL;
		$player_positions = FOOTBALL_POS;
		
		//DEBUG
		error_log("url: " . $feed_url);
		error_log("team_name: " . $team_name);
		error_log("team abbr: " . $team_names[strval( $team_name) ]);
		error_log("league_id: " . $league_id[0]);
		
		$entry = array();
		
		foreach ( $players as $player ) {

			$player_id = $player['id'];
			
			// Search if player with same player ID already exists in our database
			$search_criteria = array(
				'status'        	=> 'active',
				'field_filters' => array( //which fields to search
				'mode'  => 'all',
					array(
						'key' => FIELD_ID_PLAYER_ID, 'operator' => 'is', 'value' => $player_id
					)
				)
			);

			$result = GFAPI::count_entries( $form_id, $search_criteria );
			//$result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

			if ( $result == 0 ) {	// If player ID does not exist, proceed to grab player's data and add player
			
				// Read each player's URL
				$player_url = trailingslashit( FEED_URL_EPL ) . trailingslashit('player') . $player_id;
				$player_data = simplexml_load_file( $player_url ) ;
				
				if ( !empty( $player_data->player->firstname ) && !empty( $player_data->player->lastname ) && ( str_word_count( $player_data->player->lastname ) < 3  ) ) {
					$player_name = strtok( $player_data->player->firstname, " " ) . ' ' . $player_data->player->lastname;
				} else {
					$player_name = $player['name'];
				}
				
				$player_country = $player_data->player->nationality;
				$player_birthdate = $player_data->player->birthdate;
				$player_image = $player_data->player->image;
				$player_number = $player['number'];
				$player_age = $player['age'];
				$player_position = $player['position'];
				$player_rating = $player['rating'];
				
				$default_price = number_format( (float) 0, DECIMAL_PLACES, '.', '' );
				$default_price_chg = number_format( (float) 0, DECIMAL_PLACES_PCT, '.', '' );
				$default_number = 0;
				
				$starting_price = !empty( $player_rating ) ? ss_calculate_player_starting_price( $player_rating ): $default_price;
				$market_cap = $starting_price * STARTING_TOKENS;
				
				/* Add player as a product */
				$product = new WC_Product_Variable();
				//$product = new WC_Product_Simple();
				$product->set_name( $player_name ); 
				//$product->set_slug( '' );
				$product->set_virtual( true );
				//$product->set_regular_price( $starting_price ); // in current currency. Comment this for variable product
				$product->set_short_description( $team_name . '-' . $player_positions[ strval( $player_position ) ] );
	/* 			$product->set_short_description( 
					sprintf( esc_html__( 'Team: %s', 'storely' ) , $team_name ) . PHP_EOL .
					sprintf( esc_html__( 'Position: %s', 'storely' ) , $player_positions[ strval( $player_position ) ]  ) . PHP_EOL
					); */
				$product->set_description( '[gravityview id="' . VIEW_ID_EPL . '"]' );
				$product->set_image_id( PLAYER_IMG_PLACEHOLDER_ID );
				$product->set_category_ids( array( $category_id ) );
				//$product->set_tag_ids() for tags, brands etc
				//$product->set_status('draft');

				/* Set the product attribute */
				$attribute = new WC_Product_Attribute();
				$attribute->set_name( 'Token ID' );
				$attribute->set_options( array( $starting_token_id ) );
				$attribute->set_position( 0 );
				$attribute->set_visible( true );
				$attribute->set_variation( true );
				$product->set_attributes( array( $attribute ) );	
				
				/* Save the product and get player product ID */
				$product->save();
				$product_id = $product->get_id();
				
				// Create the variation for the player product
				$variation = new WC_Product_Variation();
				$variation->set_virtual( true );
				$variation->set_regular_price( $starting_price ); // in current currency
				$variation->set_parent_id( $product_id );
				$variation->set_attributes( array('attribute_token-id' => $starting_token_id) );	// If we use 'Token ID' instead of 'attribute_token-id' then the product variation will be named as "Any Token ID"
                $variation->set_manage_stock(true);
                $variation->set_stock_quantity( STARTING_TOKENS );
                $variation->set_stock_status('instock');				
				$variation->save();
				
				update_post_meta( $variation->get_id(), '_token_id', $starting_token_id );
				update_post_meta( $product_id, 'player_id', strval( $player_id ) );
				update_post_meta( $product_id, '_contract_address', CONTRACT_ADDRESS );	// Set the default contract address
				//$product->update_meta_data('player_id', $player_id );
				
				// To set product as a license key product. Works with License Manager plugin 
				update_post_meta( $product_id, 'lmfwc_licensed_product', 1 );
				update_post_meta( $product_id, 'lmfwc_licensed_product_use_stock', 1 );
				
				$starting_token_id++;	// Increment the token ID for the next player
				
				/* Add product ends */
				
				$entry = array(
					'form_id'       		=> $form_id,
					'date_created'  		=> $datetime->format( 'Y-m-d H:i:s' ),
					'is_starred'    		=> false,
					'is_read'       		=> false,
					'status'        		=> 'active',
					FIELD_ID_PLAYER_NAME	=> strval( $player_name ),
					FIELD_ID_PLAYER_POS		=> strval( $player_positions[ strval( $player_position ) ] ),
					FIELD_ID_PLAYER_ID		=> strval( $player_id ),
					FIELD_ID_PRODUCT_ID		=> strval( $product_id ),
					FIELD_ID_TEAM			=> strval( $team_names[ strval( $team_name ) ] ),
					FIELD_ID_TEAM_ID		=> strval( $team_id ),
					FIELD_ID_PLAYER_NUM		=> strval( $player_number ),
					FIELD_ID_COUNTRY		=> strval( $player_country ),
					FIELD_ID_BIRTHDATE		=> strval( $player_birthdate ),
					FIELD_ID_PLAYER_AGE		=> strval( $player_age ),
					FIELD_ID_PLAYER_RATING	=> strval( $player_rating ),
					FIELD_ID_PLAYER_IMAGE	=> strval( $player_image ),
					FIELD_ID_STARTING_PRICE => $starting_price,
					FIELD_ID_CURRENT_PRICE	=> $starting_price,
					FIELD_ID_PREVIOUS_PRICE	=> intval( $default_price ),
					FIELD_ID_PRICE_CHANGE	=> intval( $default_price_chg ),
					FIELD_ID_TOTAL_TOKENS	=> intval( STARTING_TOKENS ),
					FIELD_ID_MARKET_CAP		=> $market_cap,
					FIELD_ID_START			=> intval( $default_number ),
					FIELD_ID_SUB			=> intval( $default_number ),
					FIELD_ID_GOAL			=> intval( $default_number ),
					FIELD_ID_ASSIST			=> intval( $default_number ),
					FIELD_ID_GOAL_CONC		=> intval( $default_number ),
					FIELD_ID_SHOT_ON		=> intval( $default_number ),
					FIELD_ID_YELLOW_CARD	=> intval( $default_number ),
					FIELD_ID_RED_CARD		=> intval( $default_number ),
					FIELD_ID_FOUL			=> intval( $default_number ),
					FIELD_ID_TACKLE_SUCCESS	=> intval( $default_number ),
					FIELD_ID_TACKLE_FAILED	=> intval( $default_number ),
					FIELD_ID_POSS_WON		=> intval( $default_number ),
					FIELD_ID_POSS_LOST		=> intval( $default_number ),
					FIELD_ID_CLEANSHEET		=> intval( $default_number ),
					FIELD_ID_SAVE			=> intval( $default_number ),
					FIELD_ID_PEN_SAVED		=> intval( $default_number ),
					FIELD_ID_PEN_MISSED		=> intval( $default_number ),
					FIELD_ID_OWN_GOAL		=> intval( $default_number )
				);
			
			
				GFAPI::add_entry( $entry );
				unset( $entry );
				
				//DEBUG
				error_log("player_url: " . $player_url);
				error_log("player_id: " . $player_id);
				error_log("player_product_id: " . $product_id);
				error_log("Name: " . $player_name);
				error_log("player_number: " . $player_number);
				error_log("player_country: " . $player_country);
				error_log("player_birthdate: " . $player_birthdate);
				error_log("player_image: " . (!empty($player_image) ? "Yes" : "No"));
				error_log("rating: " . $player_rating);
				error_log("starting_price: " . $starting_price);
				error_log("************************************");
				
			} else {
				error_log( __( sprintf( "Player with ID %s already exists" , $player_id ), "storely" ) );
			}
			//break;
		}
		
	} else {
		error_log( __( 'Data Feed Error: Cannot access feed URL ' . $url, 'storely' ) );
		wp_die();
	}
}

// Add IPFS CID field on each player product variation
add_action( 'woocommerce_product_after_variable_attributes', 'ss_variation_fields', 10, 3 );
function ss_variation_fields( $loop, $variation_data, $variation ) {

	$product_variation = wc_get_product($variation->ID);

	// Get variation attributes
	$variation_attributes = $product_variation->get_variation_attributes();

	// Construct the variation name
	$variation_name = implode(', ', $variation_attributes);

	woocommerce_wp_text_input( 
		array( 
			'id'          => 'ipfs-cid', 
			'label'       => __( 'IPFS CID', 'storely' ), 
			'placeholder' => 'E.g. bafybeihhgtr7gt4gjkb7n...',
			'desc_tip'    => 'true',
			'description' => __( 'Contains IPFS content identifier (CID) hash for the current image. If you are removing or changing an image, you must delete the existing CID from here. To view the file on IPFS, go to https://ipfs.io/ipfs/bafybeihhgtr7gt4gjkb7n...', 'storely' ),
			'value'       => esc_attr( get_post_meta( $variation->ID, '_ipfs_cid', true ) )
		)
	);
	
    woocommerce_wp_text_input(
        array(
            'id'          => 'token-id',
            'label'       => __('Token ID', 'storely'),
            'desc_tip'    => 'true',
            'description' => __('This is the token ID for this NFT. This NFT is only minted if someone orders.', 'storely'),
			'value'       => esc_attr( get_post_meta( $variation->ID, '_token_id', true ) ),
            'custom_attributes' => array(
                'readonly' => 'readonly', // Add the readonly attribute
            ),
        )
    );
}

// Save IPFS CID field on each player product variation
add_action( 'woocommerce_save_product_variation', 'ss_save_variation_fields', 10, 2 );
function ss_save_variation_fields( $post_id ) {
	
	$variation = wc_get_product($post_id);
	
	// Get variation name
	$variation_attributes = $variation->get_variation_attributes();
	$variation_name = implode(', ', $variation_attributes);
	
	// Get the parent product name a.k.a player's name and content
	$parent_product = wc_get_product( $variation->get_parent_id() );
	$parent_product_title = $parent_product->get_name();
	$parent_product_content = $parent_product->get_short_description();
	
	if( empty( $_POST['token-id'] ) ) {
		update_post_meta( $post_id, '_token_id', $variation_name );
	}
	
	if( isset( $_POST['ipfs-cid'] ) && !empty( $_POST['ipfs-cid'] ) ) {
		update_post_meta( $post_id, '_ipfs_cid', sanitize_text_field( $_POST['ipfs-cid'] ) );
		
	} else {		
	
		$image_id = $variation->get_image_id();

		$image_path = wp_get_original_image_path( $image_id );	// E.g. C:\laragon\www\ss/wp-content/uploads/2023/11/1000.png
		$image_content_type = get_post_mime_type( $image_id );	// E.g. image/png
		$image_filename = wp_basename( $image_path );			// E.g. 1000.png
		
		$info = pathinfo( $image_filename );
		$ext  = empty( $info['extension'] ) ? '' : '.' . $info['extension'];

		// API endpoint
		$api_url = 'https://api.nft.storage/upload';

		// Create an array of files to upload
		$files = array(
			'file' => new CURLFile( $image_path, $image_content_type, $variation_name . $ext ),
		);

		// Create cURL request
		$ch = curl_init($api_url);

		// Set cURL options
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $files);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer ' . NFT_STORAGE_API_KEY,
			'Accept: ' . $image_content_type,
		));

		// Execute cURL request
		$response = curl_exec($ch);

		// Check for errors
		if (curl_errno($ch)) {
			error_log( 'Error uploading files: ' . curl_error($ch) );
			
			$error_message = __( 'There is an error while uploading to IPFS. Please check log.', 'storely' );
			add_settings_error( 'ss_ipfs_error', '', $error_message, 'warning' );
			settings_errors( 'ss_ipfs_error' ); 
			
		} else {
			// Decode and print the response
			$result = json_decode($response, true);
			$ipfs_image_cid = $result['value']['cid'];
			$ipfs_image_filename = $result['value']['files'][0]['name'];
			
			//DEBUG
			error_log("Image CID: " . $ipfs_image_cid);
			error_log("Image Filename: " . $ipfs_image_filename);
			
			// Data to be included in the JSON file
			$data = array(
				"name" => $parent_product_title,
				"description" => $parent_product_content,
				"image" => "ipfs://" . trailingslashit( $ipfs_image_cid ) . $ipfs_image_filename,
				"external_url" => WEBSITE_URL
			);
			
			// Get the upload directory information
			$upload_dir = wp_upload_dir();
			
			// Get the base directory path
			$base_dir = wp_normalize_path($upload_dir['basedir']);

			// Get the subdirectory path (relative to the base directory)
			$subdir = wp_normalize_path($upload_dir['subdir']);

			// Construct the full path by combining the base directory and subdirectory
			$full_path = $base_dir . $subdir;
			
			//Convert the array to JSON string.
			$json = json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
			
			$full_json_file_path = trailingslashit( $full_path ) . pathinfo( $ipfs_image_filename, PATHINFO_FILENAME) . ".json";
			$json_filename = pathinfo( $ipfs_image_filename, PATHINFO_FILENAME) . ".json";

			$json_file = fopen( $full_json_file_path, "w+");
			
			//Write the json data into a json file
			if ( fwrite($json_file, $json) ) {
				fclose($json_file);
				
				// Create an array of files to upload
				$files2 = array(
					'file' => new CURLFile( $full_json_file_path, 'application/json', $json_filename)
				);

				// Set cURL options
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $files2);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Authorization: Bearer ' . NFT_STORAGE_API_KEY,
					'Accept: application/json',
				));
				
				// Execute cURL request
				$response = curl_exec($ch);
				
				if ( curl_errno($ch) ) {
					error_log( 'Error uploading files: ' . curl_error($ch) );
					
					$error_message = __( 'There is an error while uploading to IPFS. Please check log.', 'storely' );
					add_settings_error( 'ss_ipfs_error', '', $error_message, 'warning' );
					settings_errors( 'ss_ipfs_error' ); 
					
				} else {
					// Decode and print the response
					$result = json_decode($response, true);
					$ipfs_final_cid = $result['value']['cid'];
					$ipfs_final_filename = $result['value']['files'][0]['name'];
					
					update_post_meta( $post_id, '_ipfs_cid', $ipfs_final_cid );
					
					//DEBUG
					error_log("Final CID: " . $ipfs_final_cid);
					error_log("Final CID Filename: " . $ipfs_final_filename);
				}
			}
		}

		// Close cURL session
		curl_close($ch);
	}
}

// Display error message on admin
function ss_admin_notices() {

	// Get requirement errors.
	$errors_to_show = $this->check_requirements();

	// If everything is in place, don't display it.
	if ( ! count( $errors_to_show ) ) {
		return;
	}

	// Use transients to display the admin notice once after saving values.
	if ( ! get_transient( 'ss-admin-notice-transient' ) ) {
		set_transient( 'ss-admin-notice-transient', 1, 1);

		echo '<div class="notice notice-error is-dismissible"><p>'
			. __( 'There is an error:', 'storely' ) . '</p>'
			. '<ul style="list-style-type: disc; list-style-position: inside; padding-left: 2em;">'
			. array_reduce( $errors_to_show, function( $errors_list, $error_item ) {
				$errors_list = $errors_list . PHP_EOL . ( '<li>' . $this->get_error_message($error_item) . '</li>' );
				return $errors_list;
			}, '' )
			. '</ul></p></div>';
	}
}

/* function rename_variation_image_filename($variation_id, $image_id, $new_filename) {
    // Get the attachment post
    $attachment = get_post($image_id);

    if ($attachment) {
        // Get the current file path
        $file_path = get_attached_file($image_id);

        // Generate the new file path with the new filename
        $new_file_path = dirname($file_path) . '/' . $new_filename;
		
		$image_attributes = wp_get_attachment_image_src( $image_id, 'full' );
		$image_original_path = wp_get_original_image_path( $image_id );
		$image_content_type = get_post_mime_type( $image_id );

		$image_attributes[0] = get_template_directory_uri() . '/img/placeholder_portrait.png';

        // Rename the physical file on the server
        rename($file_path, $new_file_path);

        // Update the attachment data
        $attachment_data = array(
            'ID'         => $image_id,
            'post_title' => $new_filename,
            'post_name'  => sanitize_title($new_filename),
        );

        wp_update_post($attachment_data);

        // Update WooCommerce variation meta to reflect the changes
        update_post_meta($variation_id, '_thumbnail_id', $image_id);
    }
} */

// Change the player's image filename to the token ID when uploading in product variation
add_filter( 'sanitize_file_name', 'ss_change_player_img_filename', 10 );
function ss_change_player_img_filename( $filename ) {
    $info = pathinfo( $filename );
    $ext  = empty( $info['extension'] ) ? '' : '.' . $info['extension'];
    //$name = basename( $filename, $ext );

    if ( isset( $_REQUEST['post_id'] ) && is_numeric( $_REQUEST['post_id'] ) && get_post_type($_REQUEST['post_id']) == 'product_variation' ) {
		
		$post_id = sanitize_text_field( $_REQUEST['post_id'] );
		
		$variation = wc_get_product($post_id);
	
		// Get variation attributes
		$variation_attributes = $variation->get_variation_attributes();

		// Construct the variation name
		$variation_name = implode(', ', $variation_attributes);

		$new_file_name = $variation_name;
		
		return $new_file_name . $ext;

    }

    return $filename;
}

// Set the title and description of each player image that is uploaded
add_action( 'add_attachment', 'ss_set_player_image_metadata', 10 );
function ss_set_player_image_metadata( $post_id ) {

    // Check if uploaded file is an image, else do nothing
    if ( wp_attachment_is_image( $post_id ) ) {
		
		if( isset( $_REQUEST['post_id'] ) && get_post_type( $_REQUEST['post_id'] ) == 'product_variation' ) {
			
			$product_post_id = $_REQUEST['post_id'];
		  
			$variation = wc_get_product( $product_post_id );
	
			// Get variation attributes
			$variation_attributes = $variation->get_variation_attributes();

			// Construct the variation name
			$variation_name = implode(', ', $variation_attributes);
		
			$image_metadata = array(
				'ID'        	=> $post_id,
				'post_title'    => sprintf( __( 'Token ID: %s', 'storely' ), $variation_name ),
				'post_content'  => sprintf( __( 'Player Variation ID: %s', 'storely' ), $product_post_id ),     
			);

			// Set the image metadata
			wp_update_post( $image_metadata );
        }
    } 
}

// Get the player's starting price based on player's rating taken from data feed like Goalserve
function ss_calculate_player_starting_price( $rating ) {
    $minRating = PLAYER_MIN_RATING;
    $maxRating = PLAYER_MAX_RATING;
    $minPrice = PLAYER_MIN_PRICE;
    $maxPrice = PLAYER_MAX_PRICE;

    // Check if the rating is within the valid range
    if ($rating < $minRating) {
        return number_format( $minPrice, DECIMAL_PLACES, '.', '' );
    } elseif ($rating > $maxRating) {
        return number_format( $maxPrice, DECIMAL_PLACES, '.', '' );
    } else {
        // Calculate the price using linear mapping
        $price = ($rating - $minRating) / ($maxRating - $minRating) * ($maxPrice - $minPrice) + $minPrice;
        return number_format( $price, DECIMAL_PLACES, '.', '' );
    }
}

add_action( 'gform_after_update_entry_' . FORM_ID_STATS_EPL, 'ss_calculate_player_price', 10, 2 );
function ss_calculate_player_price( $form, $entry_id ) {
	
	$product_id = 0;
	$total_tokens = 0;
	$starting_price = 0;
	$current_price = 0;
	$new_price = 0;
	$market_cap = 0;
	$total_pts_start = 0;
	$total_pts_sub = 0;
	$total_pts_goal = 0;
	$total_pts_assist = 0;
	$total_pts_goal_conc = 0;
	$total_pts_shot_on = 0;
	$total_pts_yellow_card = 0;
	$total_pts_red_card = 0;
	$total_pts_foul = 0;
	$total_pts_tackle_success = 0;
	$total_pts_tackle_failed = 0;
	$total_pts_poss_won = 0;
	$total_pts_poss_lost = 0;
	$total_pts_cleansheet = 0;
	$total_pts_save = 0;
	$total_pts_pen_saved = 0;
	$total_pts_pen_missed = 0;
	$total_pts_own_goal = 0;
	
	$new_total_pts = 0;
	$current_total_pts = 0;
	$price_change = 0;
	$price_change_pct = 0;
	
	$entry = GFAPI::get_entry( $entry_id );
	$product_id = rgar( $entry, FIELD_ID_PRODUCT_ID );
	$starting_price = rgar( $entry, FIELD_ID_STARTING_PRICE );
	$current_price = rgar( $entry, FIELD_ID_CURRENT_PRICE );
	$total_tokens = rgar( $entry, FIELD_ID_TOTAL_TOKENS );
	$current_total_pts = rgar( $entry, FIELD_ID_CURRENT_PTS );
	
	$total_pts_start = rgar( $entry, FIELD_ID_START ) * PTS_START;
	$total_pts_sub = rgar( $entry, FIELD_ID_SUB ) * PTS_SUB;
	$total_pts_goal = rgar( $entry, FIELD_ID_GOAL ) * PTS_GOAL;
	$total_pts_assist = rgar( $entry, FIELD_ID_ASSIST ) * PTS_ASSIST;
	$total_pts_goal_conc = rgar( $entry, FIELD_ID_GOAL_CONC) * PTS_GOAL_CONC;
	$total_pts_shot_on = rgar( $entry, FIELD_ID_SHOT_ON) * PTS_SHOT_ON;
	$total_pts_yellow_card = rgar( $entry, FIELD_ID_YELLOW_CARD) * PTS_YELLOW_CARD;
	$total_pts_red_card = rgar( $entry, FIELD_ID_RED_CARD) * PTS_RED_CARD;
	$total_pts_foul = rgar( $entry, FIELD_ID_FOUL) * PTS_FOUL;
	$total_pts_tackle_success = rgar( $entry, FIELD_ID_TACKLE_SUCCESS) * PTS_TACKLE_SUCCESS;
	$total_pts_poss_won = rgar( $entry, FIELD_ID_POSS_WON) * PTS_POSS_WON;
	$total_pts_save = rgar( $entry, FIELD_ID_SAVE) * PTS_SAVE;
	$total_pts_pen_saved = rgar( $entry, FIELD_ID_PEN_SAVED) * PTS_PEN_SAVED;
	$total_pts_pen_missed = rgar( $entry, FIELD_ID_PEN_MISSED) * PTS_PEN_MISSED;
	
	$new_total_pts = ($total_pts_start) + ($total_pts_sub) + ($total_pts_goal) + ($total_pts_assist) + ($total_pts_goal_conc) + ($total_pts_shot_on)
				+ ($total_pts_yellow_card) + ($total_pts_red_card) + ($total_pts_foul) + ($total_pts_tackle_success) + ($total_pts_poss_won)
				+ ($total_pts_save) + ($total_pts_pen_saved) + ($total_pts_pen_missed);
	
	if ( $current_total_pts != 0 || !empty( $current_total_pts ) ) {
		
		$pts_diff = ( $new_total_pts ) - ( $current_total_pts );
	
		// We basically calculate growth. Price cannot be negative
		$new_price = ( ( $pts_diff / abs( $current_total_pts ) ) * $current_price ) + ( $current_price );
		$new_price = ( $new_price < 0 ) ? PLAYER_MIN_PRICE : $new_price;	// If price goes negative, set it to our min price
		$market_cap = $new_price * $total_tokens;
		
		$price_change = ( $new_price ) - ( $current_price );
		$price_change_pct = ( $price_change / abs( $current_price ) ) * 100;
		$price_change_pct = number_format( (float) $price_change_pct, 2, '.', '' );
		$new_price = number_format( (float) $new_price, DECIMAL_PLACES, '.', '' );
		
		// To add + sign for price increase
		if ( $price_change_pct != 0.00 && substr($price_change_pct, 0, 1) != '-' ) {
			$price_change_pct = '+' . $price_change_pct;
		}
		
	} else {
		
		$new_price = ( ( $new_total_pts / 100 ) * $current_price ) + ( $current_price );
		$market_cap = $new_price * $total_tokens;
		
		$price_change = ( $new_price ) - ( $current_price );
		$price_change_pct = ( $price_change / abs( $current_price ) ) * 100;
		$price_change_pct = number_format( (float) $price_change_pct, 2, '.', '' );	// E.g. 10.20
		$new_price = number_format( (float) $new_price, DECIMAL_PLACES, '.', '' );
		
		// To add + sign for price increase
		if ( $price_change_pct != 0.00 && substr($price_change_pct, 0, 1) != '-' ) {
			$price_change_pct = '+' . $price_change_pct;
		}
	}
	
	GFAPI::update_entry_field( $entry_id, FIELD_ID_PREVIOUS_PTS, $current_total_pts );
	GFAPI::update_entry_field( $entry_id, FIELD_ID_CURRENT_PTS, $new_total_pts );
	GFAPI::update_entry_field( $entry_id, FIELD_ID_PREVIOUS_PRICE, $current_price );
	GFAPI::update_entry_field( $entry_id, FIELD_ID_CURRENT_PRICE, $new_price );
	GFAPI::update_entry_field( $entry_id, FIELD_ID_PRICE_CHANGE, $price_change_pct );
	GFAPI::update_entry_field( $entry_id, FIELD_ID_MARKET_CAP, $market_cap );
	
	update_post_meta( $product_id, '_regular_price', $new_price );
	update_post_meta( $product_id, '_price', $new_price );
	
	//DEBUG
	error_log("New total pts: " . $new_total_pts);
	error_log("New price: " . sprintf('%.10F',$new_price));
	error_log("Price change: " . sprintf('%.10F',$price_change));
	error_log("Price change pct: " . $price_change_pct . '%');
	error_log("Market cap: " . $market_cap);

}

// To create a transient name for the use in ss_read_players_stats_epl function
function get_ss_read_players_stats_by_team_epl_transient_name() {
    return 'ss_read_players_stats_by_team_epl_transient_name';
}

add_action( 'ss_read_players_stats_by_team_epl', 'ss_read_players_stats_by_team_epl' );
function ss_read_players_stats_by_team_epl() {
	
	$form_id = FORM_ID_STATS_EPL;
	$feed_url = FEED_URL_EPL;
	$team_ids = TEAMS_IDS_EPL;
	
	// Get the existing transient.If the transient does not exist, does not have a value, or has expired, 
    // then the return value will be false.
    $process_running = get_site_transient( get_ss_read_players_stats_by_team_epl_transient_name() );

    if ( $process_running ) {
        // bail out in case the transient exists and has not expired
        // this means the process is still running
        return; 
    }

    // set the transient to flag the process as started
    // 120 is the time until expiration, in seconds
    set_site_transient( get_ss_read_players_stats_by_team_epl_transient_name(), 1, 120 );
	
	foreach( $team_ids as $team_id ) {
		
		$url = trailingslashit( $feed_url ) . trailingslashit( 'team' ) . $team_id; 
	
		//DEBUG
		error_log("***************************************************************");
		error_log("team_id: " . $team_id);
		error_log("url: " . $url);
			
		$team = simplexml_load_file( $url );
		
		if ( $team !== false ) {

			$players = $team->team->squad->player;
			
			//DEBUG
			error_log("team name: " . $team->team->name);
			error_log("total players: " . count( $players ));
			
			foreach ( $players as $player ) {
				$player_id = (int) $player['id'];
				
				//DEBUG
				error_log("**************");
				error_log("player_id: " . $player_id);
				
				// Search for the player in our database
				$search_criteria = array(
					'status'        	=> 'active',
					'field_filters' => array( //which fields to search
					'mode'  => 'all',
						array(
							'key' => FIELD_ID_PLAYER_ID, 'operator' => 'is', 'value' => $player_id
						)
					)
				);

				$sorting = null;
				$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

				// Get the entries. Ref: https://docs.gravityforms.com/searching-and-getting-entries-with-the-gfapi/
				$result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

				if ( !empty( $result ) ) {
					
					$entry_id = $result[0]['id'];
					
					GFAPI::update_entry_field( $entry_id, FIELD_ID_START, intval( $player[FEED_STAT_START] ) );
					GFAPI::update_entry_field( $entry_id, FIELD_ID_SUB, intval( $player[FEED_STAT_SUB] ) );
					GFAPI::update_entry_field( $entry_id, FIELD_ID_GOAL, intval( $player[FEED_STAT_GOAL] ) );
					GFAPI::update_entry_field( $entry_id, FIELD_ID_ASSIST, intval( $player[FEED_STAT_ASSIST] ) );
					GFAPI::update_entry_field( $entry_id, FIELD_ID_GOAL_CONC, intval( $player[FEED_STAT_GOAL_CONC] ) );
					GFAPI::update_entry_field( $entry_id, FIELD_ID_SHOT_ON, intval( $player[FEED_STAT_SHOT_ON] ) );
					GFAPI::update_entry_field( $entry_id, FIELD_ID_YELLOW_CARD, intval( $player[FEED_STAT_YELLOW_CARD] ) );
					GFAPI::update_entry_field( $entry_id, FIELD_ID_RED_CARD, intval( $player[FEED_STAT_RED_CARD] ) );
					GFAPI::update_entry_field( $entry_id, FIELD_ID_FOUL, intval( $player[FEED_STAT_FOUL] ) );
					GFAPI::update_entry_field( $entry_id, FIELD_ID_TACKLE_SUCCESS, intval( $player[FEED_STAT_TACKLE_SUCCESS] ) );
					GFAPI::update_entry_field( $entry_id, FIELD_ID_POSS_WON, intval( $player[FEED_STAT_POSS_WON] ) );
					GFAPI::update_entry_field( $entry_id, FIELD_ID_SAVE, intval( $player[FEED_STAT_SAVE] ) );
					GFAPI::update_entry_field( $entry_id, FIELD_ID_PEN_SAVED, intval( $player[FEED_STAT_PEN_SAVED] ) );
					GFAPI::update_entry_field( $entry_id, FIELD_ID_PEN_MISSED, intval( $player[FEED_STAT_PEN_MISSED] ) );
					
					//DEBUG
					error_log("entry_id: " . $entry_id);
					error_log("Name: " . $player['name']);
					error_log("Starts: " .  $player[FEED_STAT_START]);
					error_log("Goal: " .  $player[FEED_STAT_GOAL]);
					error_log("Assist: " . $player[FEED_STAT_ASSIST]);

				}
			}
			
		} else {
			error_log( __( 'Data Feed Error: Cannot access feed URL ' . $url, 'rife-free-child' ) );
			wp_die();
		}
	}
	
	// delete the transient to remove the flag and allow the process to run again
    delete_site_transient( get_ss_read_players_stats_by_team_epl_transient_name() ); 
}

// To create a transient name for the use in ss_read_players_stats_epl function
function get_ss_read_players_stats_epl_transient_name() {
    return 'ss_read_players_stats_epl_transient_name';
}

function ss_read_players_stats_epl() {
	
	$form_id = FORM_ID_STATS_EPL;
	$feed_url = FEED_URL_EPL;
	
	// Get the existing transient.If the transient does not exist, does not have a value, or has expired, 
    // then the return value will be false.
    $process_running = get_site_transient( get_ss_read_players_stats_epl_transient_name() );

    if ( $process_running ) {
        // bail out in case the transient exists and has not expired
        // this means the process is still running
        return; 
    }

    // set the transient to flag the process as started
    // 120 is the time until expiration, in seconds
    set_site_transient( get_ss_read_players_stats_epl_transient_name(), 1, 120 );
	
	$search_criteria = array(
		'status'        => 'active'
	);

    $sorting = null;
	$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

    // Get the entries. Ref: https://docs.gravityforms.com/searching-and-getting-entries-with-the-gfapi/
    $result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

    if ( !empty( $result ) ) { 

        foreach( $result as $key => $value ) {
			$entry_id = $value['id'];
			$url = trailingslashit( $feed_url) . $value[FIELD_ID_PLAYER_ID];
			
			//DEBUG
			error_log("url: " . $url);
				
			$player = simplexml_load_file( $url ) ;
			
			if ( $player !== false ) {

				$name = $player->player->name;
				$stat = $player->player->statistic->club;
				
				GFAPI::update_entry_field( $entry_id, FIELD_ID_START, intval( $stat[0][FEED_STAT_START] ) );
				GFAPI::update_entry_field( $entry_id, FIELD_ID_SUB, intval( $stat[0][FEED_STAT_SUB] ) );
				GFAPI::update_entry_field( $entry_id, FIELD_ID_GOAL, intval( $stat[0][FEED_STAT_GOAL] ) );
				GFAPI::update_entry_field( $entry_id, FIELD_ID_ASSIST, intval( $stat[0][FEED_STAT_ASSIST] ) );
				GFAPI::update_entry_field( $entry_id, FIELD_ID_GOAL_CONC, intval( $stat[0][FEED_STAT_GOAL_CONC] ) );
				GFAPI::update_entry_field( $entry_id, FIELD_ID_SHOT_ON, intval( $stat[0][FEED_STAT_SHOT_ON] ) );
				GFAPI::update_entry_field( $entry_id, FIELD_ID_YELLOW_CARD, intval( $stat[0][FEED_STAT_YELLOW_CARD] ) );
				GFAPI::update_entry_field( $entry_id, FIELD_ID_RED_CARD, intval( $stat[0][FEED_STAT_RED_CARD] ) );
				GFAPI::update_entry_field( $entry_id, FIELD_ID_FOUL, intval( $stat[0][FEED_STAT_FOUL] ) );
				GFAPI::update_entry_field( $entry_id, FIELD_ID_TACKLE_SUCCESS, intval( $stat[0][FEED_STAT_TACKLE_SUCCESS] ) );
				GFAPI::update_entry_field( $entry_id, FIELD_ID_POSS_WON, intval( $stat[0][FEED_STAT_POSS_WON] ) );
				GFAPI::update_entry_field( $entry_id, FIELD_ID_SAVE, intval( $stat[0][FEED_STAT_SAVE] ) );
				GFAPI::update_entry_field( $entry_id, FIELD_ID_PEN_SAVED, intval( $stat[0][FEED_STAT_PEN_SAVED] ) );
				GFAPI::update_entry_field( $entry_id, FIELD_ID_PEN_MISSED, intval( $stat[0][FEED_STAT_PEN_MISSED] ) );
				
				//DEBUG
				error_log("entry_id: " . $entry_id);
				error_log("Name: " . $name);
				error_log("Season: " . $stat['season']);
				error_log("Minutes: " . $stat['minutes']);
				error_log("Goal: " .  $stat[FEED_STAT_GOAL]);
				error_log("Assist: " . $stat[FEED_STAT_ASSIST]);
			
			} else {
				//wp_die( __( 'Data Feed Error: Cannot access feed URL ' . $url, 'rife-free-child' ) );
				error_log( __( 'Data Feed Error: Cannot access feed URL ' . $url, 'rife-free-child' ) );
				wp_die();
			}
        }
    }

    // delete the transient to remove the flag and allow the process to run again
    delete_site_transient( get_ss_read_players_stats_epl_transient_name() ); 
}

// Schedule action if it's not already scheduled
// if ( !wp_next_scheduled( 'ss_read_players_stats_epl' ) ) {
	// wp_schedule_event( strtotime('08:00:00'), 'daily', 'ss_read_players_stats_epl' );
// }

// To create a transient name for the use in ss_get_price_usd_api function
function get_ss_price_usd_api_transient_name() {
    return 'ss_get_price_usd_api_transient_name';
}

// Get crypto price from API in USD. Such as, 1 ETH for 1,579 USD. Currency symbol must be in three letters such as ETH and USD
add_action( 'ss_get_price_usd_api', 'ss_get_price_usd_api' );
function ss_get_price_usd_api() {
	
	$crypto_symbol = DEF_CURRENCY;
	
	$api = '';
	$request = '';
	$exchg_rate = '';
	
	// Get the existing transient.If the transient does not exist, does not have a value, or has expired, 
    // then the return value will be false.
    $process_running = get_site_transient( get_ss_price_usd_api_transient_name() );

    if ( $process_running ) {
        // bail out in case the transient exists and has not expired
        // this means the process is still running
        return; 
    }

    // set the transient to flag the process as started
    // 120 is the time until expiration, in seconds
    set_site_transient( get_ss_price_usd_api_transient_name(), 1, 120 );
	
	$api = trailingslashit( EXCHG_URL ) . '?currency=' . $crypto_symbol;
	$request = wp_remote_get( $api );
	
	if( is_wp_error( $request ) ) {
		error_log( __( "Error: Cannot retrieve exchange rate from API. URL is " . $api, "storely" ) );
	}

	$data = json_decode( $request['body'], true );
	
	if  ($data && isset($data['data']['rates']['USD'])) {
		$exchg_rate = $data['data']['rates']['USD'];
		update_option( 'xrate_usd', $exchg_rate, false );
		
	} else {
		error_log( __( "Error: Currency not found in the data.", "storely" ) );
	}
	
	// delete the transient to remove the flag and allow the process to run again
    delete_site_transient( get_ss_price_usd_api_transient_name() ); 
}

// To get the crypto price in USD. Currency symbol must be in three letters such as ETH and USD
function ss_get_price_usd( $price, $crypto_symbol ) {
	
	$api = '';
	$request = '';
	$new_price = 0;
	$exchg_rate = '';
	
	$exchg_rate = get_option( 'xrate_usd' );	// Such as 1 ETH to USD. The value should be ~USD1570
	
	if ( empty( $exchg_rate ) ) {
		$api = trailingslashit( EXCHG_URL ) . '?currency=' . $crypto_symbol;
		$request = wp_remote_get( $api );
		
		if( is_wp_error( $request ) ) {
			error_log( __( "Error: Cannot retrieve exchange rate from API. URL is " . $api, "storely" ) );
			$new_price = 0;
			return $new_price;
		}
	
		$data = json_decode( $request['body'], true );
		
		if  ($data && isset($data['data']['rates']['USD'])) {
			$exchg_rate = $data['data']['rates']['USD'];
			update_option( 'xrate_usd', $exchg_rate, false );
			
			$new_price = (float) $price * $exchg_rate;
			
		} else {
			error_log( __( "Error: Currency not found in the data.", "storely" ) );
			$new_price = 0;
			return $new_price;
		}

	} else {
		$new_price = (float) $price * $exchg_rate;
	}
	
	return number_format( $new_price, 2, '.', '' );
}

// To set form field to be read only
add_filter( 'gform_pre_render_' . FORM_ID_SELL_NFT_EPL, 'add_readonly_script' );
function add_readonly_script( $form ) {
    ?>
    <script type="text/javascript">
        jQuery(document).on('gform_post_render', function(){
            /* apply only to a input with a class of gf_readonly */
            jQuery(".gf_readonly input").attr("readonly","readonly");
        });
    </script>
    <?php
    return $form;
}

// To pre-fill the contract input field in the selling form 
add_filter( 'gform_field_value_contract_address', 'ss_prefill_contract_address' );
function ss_prefill_contract_address( $value ) {
	$product_id = isset($_GET['product-id']) ? sanitize_text_field($_GET['product-id']) : '';
	$contract_address = get_post_meta( $product_id, '_contract_address', true );
    return $contract_address;
}	

// To pre-fill the admin wallet input field in the selling form to give approval to perform token transfer from user's wallet 
add_filter( 'gform_field_value_admin_wallet', 'ss_prefill_admin_wallet' );
function ss_prefill_admin_wallet( $value ) {
    return WALLET_ADDRESS;	// constant from web3-config.php
}

// Hide submit button since we're using other button to submit
add_filter( 'gform_submit_button_' . FORM_ID_SELL_NFT_EPL, '__return_false' );

//add_filter( 'gform_submit_button_' . FORM_ID_SELL_NFT_EPL, 'ss_add_onclick_erc1155', 10, 2 );
// Function below throw errors
/* function ss_add_onclick_erc1155( $button, $form ) {
    $dom = new DOMDocument();
    $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $button );
    $input = $dom->getElementsByTagName( 'input' )->item(0);
    $onclick = $input->getAttribute( 'onclick' );
    $onclick .= " setApprovalForAllErc1155();";	// The JS function we're calling on click.
    $input->setAttribute( 'onclick', $onclick );
    return $dom->saveHtml( $input );
} */

add_filter( 'gform_pre_render_' . FORM_ID_SELL_NFT_EPL, 'ss_read_tokens_before_selling', 10, 1 );
function ss_read_tokens_before_selling( $form ) {
	
	$user_id = get_current_user_id();
	$form_id = FORM_ID_SELL_NFT_EPL;
	$form_token_id_field = 11;
	$form_token_id_balance_field = 14;
	$token_ids = [];
	$choices = [];
	
	//If user is not logged in...
    if ( $user_id == 0 ) {
        wp_redirect( wp_login_url() ); 
        exit();
    }
	
	$user_wallet = get_user_meta( $user_id, 'ethpress', true );
	
	if ( isset( $_GET['product-id'] ) && !empty( $_GET['product-id'] ) ) {
		$parent_product_id = sanitize_text_field( $_GET['product-id'] );
		
		// Get all variations of the parent product
		$variations = get_children(array(
			'post_parent' => $parent_product_id,
			'post_type'   => 'product_variation',
		));
		
		if ( !empty( $variations ) ) {

			// Loop through the variations
			foreach ($variations as $variation) {
				
				$variation_id = $variation->ID;
				$token_id = get_post_meta( $variation_id, '_token_id', true );
				$token_balance = get_token_id_balance( CONTRACT_ADDRESS, $user_wallet, $token_id );
				
				if ( !empty( $token_balance ) ) {
					
					$token_ids[ $token_id ] = $token_balance;
				
				} /* else {
					echo '<script>alert("' . __( 'You do not own tokens for this player.', 'storely' ) . '"); window.location.href = document.referrer;</script>';
				
				} */
			}
			
			if ( !empty( $token_ids ) ) {
				
				//Go through each form fields
				foreach ( $form['fields'] as $field ) {
					
					//check if field type is a select dropdown and id is for dropdown
					if ( $field->type == 'select' && $field->id == $form_token_id_field && count( $token_ids ) > 0 ) {
						
						//add name and value to the option
						foreach ($token_ids as $key => $value) {
							$choices[] = array('text' => $key . ' ' . sprintf( __('(Balance: %d)', 'storely'), $value ), 'value' => $key );
						}
						$field->placeholder = __( 'Select Token ID', 'storely' );
						$field->choices = $choices;		//Add the new names to the form choices
					} 
				}
				
			} else {
				echo '<script>alert("' . __( 'You do not own tokens for this player.', 'storely' ) . '"); window.location.href = document.referrer;</script>';
			}
			
		} else {
			echo '<script>alert("' . __( 'You entered a wrong player ID. Change the ID in the URL', 'storely' ) . '"); window.location.href = document.referrer;</script>';
		}
	}
	
	return $form;
}

function get_token_id_balance( $contract_address, $wallet_address, $token_id ) {

	include_once("web3-config.php");
	
	$extra_curl_params = [];
	$balance = [];
	
	//INFURA ONLY: Prepare extra curl params, to add infura private key to the request
	$extra_curl_params[CURLOPT_USERPWD] = ':' . INFURA_PROJECT_SECRET; 
	
	//initialize SWeb3 main object
	$sweb3 = new SWeb3(NET_ENDPOINT, $extra_curl_params); 
	
	$sweb3->chainId = CHAIN_ID;		// Goerli or other chain ID

	$config = new stdClass();
	$config->walletAddress = WALLET_ADDRESS;
	$config->walletPrivateKey = WALLET_PRIVATE_KEY;
	$config->userWallet = $wallet_address;
	$config->contractAddress = $contract_address;
	$config->contractAbi = CONTRACT_ABI;

	$sweb3->setPersonalData($config->walletAddress, $config->walletPrivateKey); 
	 
	// Initialize contract from address and ABI string
	$contract = new SWeb3_contract($sweb3, $config->contractAddress, $config->contractAbi); 
	$extra_data = [ 'nonce' => $sweb3->personal->getNonce() ];
	
	//DEBUG
	error_log("User wallet: " . $config->userWallet);
	error_log("Contract address: " . $config->contractAddress);
	
	$res = $contract->call( 'balanceOf', [$config->userWallet, $token_id] );	// ERC1155;
	
	//Convert BigInteger to int
	$bigInteger = new BigNumber($res->result);
	$token_balance = intval($bigInteger->toString());

	if ( $token_balance > 0 ) {
		return $token_balance;	
		
	} else {
		return false;
	}		
}

// Sell order without automatically transfering over the tokens to us
add_action( 'gform_after_submission_' . FORM_ID_SELL_NFT_EPL, 'ss_create_sell_order', 10, 2 );
function ss_create_sell_order( $entry, $form ) {
	
	$user_id = get_current_user_id();
	
	//If user is not logged in...
    if ( $user_id == 0 ) {
        wp_redirect( wp_login_url() ); 
        exit();
    }
	
	$product_id = rgar( $entry, '3' );
	$tx_hash = rgar( $entry, '9' );
	$user_wallet = rgar( $entry, '8' );
	$admin_wallet = rgar( $entry, '7' );
	$token_id = rgar( $entry, '11' );
	$contract_address = rgar( $entry, '6' );
	$quantity = rgar( $entry, '2' );
	
	$product = wc_get_product( $product_id );
	$current_price = $product->get_price();
	$current_price = number_format( $current_price, DECIMAL_PLACES, '.', '' );
	
	$note = "Player Product ID: " . $product_id . PHP_EOL
			. "Selling Price: " . get_woocommerce_currency_symbol() . $current_price . PHP_EOL
			. "Token ID(s): " . $token_id . PHP_EOL
			. "User Wallet: " . $user_wallet .  PHP_EOL
			. "Admin Wallet" . $admin_wallet . PHP_EOL
			. "Contract Address" . $contract_address . PHP_EOL
			. "Contract Function: setApprovalForAll" . PHP_EOL;
	
	$order = wc_create_order();
	$order->set_customer_id( $user_id );
	
	// Include user email address in the billing email
	$user_data = get_userdata($user_id);
	$user_email = $user_data->user_email;
	$order->set_billing_email( $user_email );

	// Add sell player NFT product
	$order->add_product( wc_get_product( PRODUCT_ID_SELL_NFT ) );
	
	// Add the NFT token ID
	$order->add_order_note( $note );

	// Use shipping fee to record selling price so that it will not be included in calculation of earnings/sales
	$shipping = new WC_Order_Item_Shipping();
	$shipping->set_method_title( __( 'Sell Order Price', 'storely' ) );
	$shipping->set_method_id( 1 ); // Use shipping method ID 1
	$shipping->set_total( $current_price * $quantity );
	$order->add_item( $shipping );

	// order status
	$order->set_status( 'wc-processing', 'Sell order created' );

	// calculate and save
	$order->calculate_totals();
	$order_id = $order->save();
	
	if ( $order_id != 0 ) {
		update_post_meta( $order_id, '_selling_price', $current_price );
		update_post_meta( $order_id, '_token_id', $token_id );
		update_post_meta( $order_id, '_user_wallet', $user_wallet );
		update_post_meta( $order_id, '_admin_wallet', $admin_wallet );
		update_post_meta( $order_id, '_contract_address', $contract_address );
		update_post_meta( $order_id, '_quantity', $quantity );
		update_post_meta( $order_id, '_total_payout', $order->get_total() );
		update_post_meta( $order_id, '_contract_function', "setApprovalForAll" );
	}
}

// The func below is not complete but attempts to auto transfer the tokens to us
/* function ss_create_sell_order_and_transfer(  $entry, $form ) {
	
	include_once("web3-config.php");
	
	$contract_address = '';
	$user_wallet = '';
	$extra_curl_params = [];
	
	$user_id = get_current_user_id();
	
	//If user is not logged in...
    if ( $user_id == 0 ) {
        wp_redirect( wp_login_url() ); 
        exit();
    }
	
	$product_id = rgar( $entry, '3' );
	$tx_hash = rgar( $entry, '9' );
	$user_wallet = rgar( $entry, '8' );
	$admin_wallet = rgar( $entry, '7' );
	$token_id = rgar( $entry, '11' );
	$contract_address = rgar( $entry, '6' );
	$quantity = rgar( $entry, '2' );
	
	$product = wc_get_product( $product_id );
	$current_price = $product->get_price();
	$current_price = number_format( $current_price, DECIMAL_PLACES, '.', '' );
	
	$note = "Player Product ID: " . $product_id . PHP_EOL
			. "Selling Price: " . $current_price . PHP_EOL
			. "Token ID(s): " . $token_id . PHP_EOL
			. "User Wallet: " . $user_wallet .  PHP_EOL
			. "Admin Wallet" . $admin_wallet . PHP_EOL
			. "Contract Address" . $contract_address . PHP_EOL
			. "Contract Function: setApprovalForAll" . PHP_EOL;
	
	$order = wc_create_order();
	$order->set_customer_id( $user_id );

	// Add sell player NFT product
	$order->add_product( wc_get_product( PRODUCT_ID_SELL_NFT ) );
	
	// Add the NFT token ID
	$order->add_order_note( $note );

	// Use shipping fee to record selling price so that it will not be included in calculation of earnings/sales
	$shipping = new WC_Order_Item_Shipping();
	$shipping->set_method_title( 'NFT Sell Order Price' );
	$shipping->set_method_id( 1 ); // Use shipping method ID 1
	$shipping->set_total( $current_price * $quantity );
	$order->add_item( $shipping );

	// order status
	$order->set_status( 'wc-processing', 'NFT sell order created' );

	// calculate and save
	$order->calculate_totals();
	$order_id = $order->save();
	
	if ( $order_id != 0 ) {
		update_post_meta( $order_id, '_selling_price', $current_price );
		update_post_meta( $order_id, '_token_id', $token_id );
		update_post_meta( $order_id, '_user_wallet', $user_wallet );
		update_post_meta( $order_id, '_admin_wallet', $admin_wallet );
		update_post_meta( $order_id, '_contract_address', $contract_address );
		update_post_meta( $order_id, '_quantity', $quantity );
		update_post_meta( $order_id, '_total_payout', $order->get_total() );
		update_post_meta( $order_id, '_contract_function', "setApprovalForAll" );
	}
	
	//DEBUG
	error_log("Product ID: " . $product_id);
	error_log("Product Variation ID: " . $item_values->get_variation_id());
	error_log("Token ID: " . $token_id);
	error_log("Contract Address: " . $contract_address);
	error_log("Token URI: " . $token_uri);
	error_log("Quantity: " . $quantity);
	
	//INFURA ONLY: Prepare extra curl params, to add infura private key to the request
	$extra_curl_params[CURLOPT_USERPWD] = ':' . INFURA_PROJECT_SECRET; 
	
	//initialize SWeb3 main object
	$sweb3 = new SWeb3(NET_ENDPOINT, $extra_curl_params); 
	
	//send chain id, important for transaction signing 0x1 = main net, 0x3 ropsten... full list = https://chainlist.org/
	$sweb3->chainId = CHAIN_ID;		// Goerli or other chain ID

	$config = new stdClass();
	$config->walletAddress = WALLET_ADDRESS;
	$config->walletPrivateKey = WALLET_PRIVATE_KEY;
	//$config->contractAddress = CONTRACT_ADDRESS;
	$config->transferFromAddress = user_wallet;
	$config->transferToAddress = WALLET_ADDRESS;
	$config->contractAddress = $contract_address;		// Enable this if each player has their own contract address
	$config->contractAbi = CONTRACT_ABI;

	$sweb3->setPersonalData($config->walletAddress, $config->walletPrivateKey); 
	 
	// Initialize contract from address and ABI string
	$contract = new SWeb3_contract($sweb3, $config->contractAddress, $config->contractAbi); 
	$extra_data = [ 'nonce' => $sweb3->personal->getNonce() ];

	// This contract has 18 decimal like ethers. So 1 token is 10^18 weis. 
	//$value = Utils::toWei('1', 'ether');
	
	$token_ids = array();
	$token_amount = array();
		
	array_push($token_ids, $token_id);
	array_push($token_amount, $quantity);

	error_log("Delivering these token IDs...");
	error_log(print_r($token_ids, true));
	
	//DEBUG
	error_log("Sender wallet address: " . $config->walletAddress);
	error_log("Transfer to address: " . $config->transferToAddress);
	error_log("Contract address: " . $config->contractAddress);
	error_log("Delivering via the endpoint: " . NET_ENDPOINT);
	error_log("Delivering this token quantity...");
	error_log(print_r($token_amount, true));
	
	$res = $contract->send('safeBatchTransferFrom', [$config->transferFromAddress, $config->transferToAddress, $token_ids, $token_amount, []], $extra_data);	// ERC1155
	//$res = $contract->send('transfer', [$config->transferToAddress, $value],  $extra_data);	// ERC20
	//$res = $contract->send('transferFrom', [$config->walletAddress, $config->transferToAddress, "2"], $extra_data);	// ERC721
	
	error_log("Transaction hash: " . $res->result);
	
	update_post_meta( $order_id, '_transaction_hash', $res->result );
	update_post_meta( $order_id, '_contract_address', $contract_address );
	update_post_meta( $order_id, '_user_wallet', $user_wallet );
	
	$order->update_status( 'completed' );
	
} */

// Add sell token button on player single product page
add_action( 'woocommerce_after_add_to_cart_button', 'ss_add_sell_button', 20 );
function ss_add_sell_button() {
	$product_id = get_the_ID();   
	echo '<a href="' . trailingslashit( site_url() ) . trailingslashit( SELL_TOKENS_SLUG ) . '?product-id=' . $product_id . '" class="button">' . esc_html__( 'Sell', 'storely' ) . '</a>';
}


/***************************************************************************************************************/

// We can't disable WP API but can only reserve it for logged in users.
// Src: https://developer.wordpress.org/rest-api/frequently-asked-questions/#can-i-disable-the-rest-api
// Src: https://stackoverflow.com/questions/41191655/safely-disable-wp-rest-api
add_filter( 'rest_authentication_errors', function( $result ) {
    // If a previous authentication check was applied,
    // pass that result along without modification.
    if ( true === $result || is_wp_error( $result ) ) {
        return $result;
    }

    // No authentication has been performed yet.
    // Return an error if user is not logged in.
    if ( ! is_user_logged_in() ) {
        return new WP_Error(
            'rest_not_logged_in',
            __( 'You are not currently logged in.' ),
            array( 'status' => 401 )
        );
    }

    // Our custom authentication check should have no effect
    // on logged-in requests
    return $result;
});


// To manage the GV cache. Ref: https://docs.gravitykit.com/article/58-about-gravityview-caching
add_filter('gravityview_use_cache', '__return_false' );	// To turn enable cache, uncomment this. If on, the ranking numbers will not appear after running cron job
add_filter( 'gravityview_cache_time_entries', 'vr_edit_gv_cache', 10, 1 );
add_filter( 'gravityview_cache_time_datatables_output', 'vr_edit_gv_cache', 10, 1 ); 
function vr_edit_gv_cache( $expiration ) {
	$expiration = 10;
	//$expiration = 86400;	// In seconds. 86400 secs is 1 day
	return $expiration;
}

// Force numbers to sort properly in GravityView
// Src: https://docs.gravitykit.com/article/112-forcing-numbers-to-sort-properly
add_filter( 'gravityview_search_criteria', 'gravityview_force_numeric_sort', 10, 3 );
function gravityview_force_numeric_sort( $criteria, $form_ids = array(), $context_view_id = 0 ) {
	$criteria['sorting']['is_numeric'] = true;
	return $criteria;
}
 