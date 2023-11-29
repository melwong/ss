<?php

//namespace SWeb3;

/**
 * make sure child theme style.css will be loaded as last file before user.css
 */
function a13_child_style(){
    global $wp_styles;

    //get current user.css dependencies
    $user_css_deps = $wp_styles->registered['a13-user-css']->deps;

    //register child theme style.css and add it with dependencies for user.css, to be sure it will be loaded after all other style files
    //it is useful for doing easier style overwrites
    wp_register_style('child-style', get_stylesheet_directory_uri(). '/style.css', $user_css_deps, A13FRAMEWORK_THEME_VER);

    //add child theme style.css as also needed for user.css
    array_push($wp_styles->registered['a13-user-css']->deps, 'child-style');
}
//register it later then parent theme styles
add_action('wp_enqueue_scripts', 'a13_child_style', 27);

/*
 * Add here your functions below, and overwrite native theme functions
 */
 
require_once('glob.php');
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

add_filter( 'woocommerce_get_price_html', 'ss_player_price_display', 10, 2 );
function ss_player_price_display( $price, $product ) {
	
	$search_criteria = array(
		'status'        => 'active',
		'field_filters' => array( //which fields to search
		'mode'  => 'all',
			array(
				'key' => FIELD_ID_PRODUCT_ID, 'operator' => 'is', 'value' => $product->id
			)
		)
	);

    $sorting = null;
	$paging = array( 'offset' => 0, 'page_size' => 1 );

    $result = GFAPI::get_entries( FORM_ID_STATS, $search_criteria, $sorting, $paging, $total_count );
	
	if ( !empty($result) ) { 
		$price_change_pct = $result[0][FIELD_ID_PRICE_CHANGE];
    }
	
	if ( $price_change_pct > 0 ) {
		$price .= ' (+' . $price_change_pct . '%)';
	} else {
		$price .= ' (' . $price_change_pct . '%)';
	}
    
	return $price;
}

add_filter( 'woocommerce_currencies', 'bc_add_new_currency' );
add_filter( 'woocommerce_currency_symbol', 'bc_add_new_currency_symbol', 10, 2 );
function bc_add_new_currency( $currencies ) {
     $currencies['ETH'] = __( 'Ethereum', 'woocommerce-gateway-new-currency' );
	 $currencies['USDT'] = __( 'Tether', 'woocommerce-gateway-new-currency' );
	 $currencies['BNB'] = __( 'Binance Coin', 'woocommerce-gateway-new-currency' );
	 $currencies['BUSD'] = __( 'Binance USD', 'woocommerce-gateway-new-currency' );
     return $currencies;
}

function bc_add_new_currency_symbol( $symbol, $currency ) {
     
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
    return __( 'Buy Now', 'woocommerce' ); 
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
	//DEBUG
	//error_log( "wc_get_page_permalink: " . wc_get_page_permalink( 'checkout' ) );
	//error_log( "redirect_to: " . $redirect_to );

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

// Create log in and logout menu links
add_filter( 'wp_nav_menu_items', 'uwp_menu_last_to_nav_menu', 10, 2 );
function uwp_menu_last_to_nav_menu( $items, $args ) {
    if ( is_user_logged_in() ) {
        $last = '<li class="mega-menu-item mega-menu-item-type-custom mega-menu-item-object-custom mega-align-bottom-left mega-menu-flyout"><a class="mega-menu-link" href="'. esc_url( home_url( '/my-account' ) ) .'">' . esc_html__( 'My Account', 'rife-free-child' ) . '</a></li>';

        $last .= '<li class="mega-menu-item mega-menu-item-type-custom mega-menu-item-object-custom mega-align-bottom-left mega-menu-flyout"><a class="mega-menu-link" href="'. wp_logout_url( home_url() ). '">' . esc_html__( 'Log Out', 'rife-free-child' ) . '</a></li>';
        $lastitems =  $items . $last;
    }
    if ( !is_user_logged_in() ) {
        $last = '<li class="mega-menu-item mega-menu-item-type-custom mega-menu-item-object-custom mega-align-bottom-left mega-menu-flyout"><a class="mega-menu-link" href="'. esc_url( home_url( '/my-account' ) ). '">' . esc_html__( 'Log In', 'rife-free-child' ) . '</a></li>';
        $lastitems =  $items . $last;
    }

    return $lastitems;
}

add_action( 'woocommerce_order_status_processing', 'ss_deliver_token', 10, 1);
function deliver_token( $order_id ) {

	include_once("web3-config.php");

	$extra_curl_params = [];
	//INFURA ONLY: Prepare extra curl params, to add infura private key to the request
	$extra_curl_params[CURLOPT_USERPWD] = ':' . INFURA_PROJECT_SECRET; 
	
	//initialize SWeb3 main object
	$sweb3 = new SWeb3(NET_ENDPOINT, $extra_curl_params); 
	
	//send chain id, important for transaction signing 0x1 = main net, 0x3 ropsten... full list = https://chainlist.org/
	$sweb3->chainId = CHAIN_ID;		// Goerli

	$config = new stdClass();
	$config->walletAddress = WALLET_ADDRESS;
	$config->walletPrivateKey = WALLET_PRIVATE_KEY;
	$config->contractAddress = CONTRACT_ADDRESS;
	$config->contractAbi = CONTRACT_ABI;
	$config->transferToAddress = "0x3b75AE8E4780Baf7203EeD991144E95aCD8bD447";

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
	$res = $contract->send('transferFrom', [$config->walletAddress, $config->transferToAddress, "2"], $extra_data);
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

add_action( 'gform_after_update_entry_' . FORM_ID_STATS, 'ss_calculate_player_price', 10, 2 );
function ss_calculate_player_price( $form, $entry_id ) {
	
	$product_id = 0;
	$starting_price = 0;
	$current_price = 0;
	$new_price = 0;
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
	
	$total_pts = 0;
	$price_change = 0;
	$price_change_pct = 0;
	$price_change_pct = 0;
	
	$entry = GFAPI::get_entry( $entry_id );
	
	$product_id = rgar( $entry, FIELD_ID_PRODUCT_ID );
	$starting_price = rgar( $entry, FIELD_ID_STARTING_PRICE );
	$current_price = rgar( $entry, FIELD_ID_CURRENT_PRICE );

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

	$total_pts = $total_pts_start + $total_pts_sub + $total_pts_goal + $total_pts_assist + $total_pts_goal_conc + $total_pts_shot_on
				+ $total_pts_yellow_card + $total_pts_red_card + $total_pts_foul + $total_pts_tackle_success + $total_pts_poss_won
				+ $total_pts_save + $total_pts_pen_saved + $total_pts_pen_missed;
	//$total_pts = $total_pts_start + $total_pts_sub + $total_pts_goal + $total_pts_assist;
	
	$new_price = ( $total_pts / 100 * $starting_price ) + $starting_price;
	$new_price = number_format( (float) $new_price, DECIMAL_PLACES, '.', '' );
	
	$price_change = $new_price - $current_price;
	$price_change_pct = ( $price_change / $current_price ) * 100;
	$price_change_pct = number_format( (float) $price_change_pct, 2, '.', '' );
	
	GFAPI::update_entry_field( $entry_id, FIELD_ID_PREVIOUS_PRICE, $current_price );
	GFAPI::update_entry_field( $entry_id, FIELD_ID_CURRENT_PRICE, $new_price );
	GFAPI::update_entry_field( $entry_id, FIELD_ID_PRICE_CHANGE, $price_change_pct );
	
	update_post_meta( $product_id, '_regular_price', $new_price );
	update_post_meta( $product_id, '_price', $new_price );
	
	//DEBUG
	error_log("Total pts: " . $total_pts);
	error_log("New price: " . $new_price);
	error_log("Price change: " . $price_change);
	error_log("Price change pct: " . $price_change_pct . '%');

}

// To create a transient name for the use in ss_read_players_stats function
function get_ss_read_players_stats_transient_name() {
    return 'ss_read_players_stats_transient_name';
}

add_action( 'ss_read_players_stats', 'ss_read_players_stats' );
function ss_read_players_stats() {
	
	$form_id = FORM_ID_STATS;
	$feed_url = FEED_URL;
	
	// Get the existing transient.If the transient does not exist, does not have a value, or has expired, 
    // then the return value will be false.
    $process_running = get_site_transient( get_ss_read_players_stats_transient_name() );

    if ( $process_running ) {
        // bail out in case the transient exists and has not expired
        // this means the process is still running
        return; 
    }

    // set the transient to flag the process as started
    // 120 is the time until expiration, in seconds
    set_site_transient( get_ss_read_players_stats_transient_name(), 1, 120 );
	
	$search_criteria = array(
		'status'        => 'active'
	);
    
    if ( !empty( $start) ) {
        $start_date                    = date( 'Y-m-d', strtotime( $start ) );
        $search_criteria['start_date'] = $start_date;
    } 

    if ( !empty( $end) ) {
        $end_date                      = date( 'Y-m-d', strtotime( $end ) );
        $search_criteria['end_date']   = $end_date;
    }

    $sorting = null;
	$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

    // Get the entries. Ref: https://docs.gravityforms.com/searching-and-getting-entries-with-the-gfapi/
    $result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

    if ( !empty( $result ) ) { 

        foreach( $result as $key => $value ) {
			$entry_id = $value['id'];
			$url = trailingslashit( $feed_url) . $value[FIELD_ID_PLAYER_ID];
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
				// error_log("entry_id: " . $entry_id);
				// error_log("Name: " . $name);
				// error_log("Season: " . $stat['season']);
				// error_log("Minutes: " . $stat['minutes']);
				// error_log("Goal: " .  $stat[FEED_STAT_GOAL]);
				// error_log("Assist: " . $stat[FEED_STAT_ASSIST]);
				
			} else {
				//wp_die( __( 'Data Feed Error: Cannot access feed URL ' . $url, 'rife-free-child' ) );
				error_log( __( 'Data Feed Error: Cannot access feed URL ' . $url, 'rife-free-child' ) );
			}
        }
    }

    // delete the transient to remove the flag and allow the process to run again
    delete_site_transient( get_ss_read_players_stats_transient_name() ); 
}

// Schedule action if it's not already scheduled
if ( !wp_next_scheduled( 'ss_read_players_stats' ) ) {
	wp_schedule_event( strtotime('08:00:00'), 'daily', 'ss_read_players_stats' );
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
add_filter('gravityview_use_cache', '__return_false' );	// To turn cache off, uncomment this. If on, the ranking numbers will not appear after running cron job
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


// Enable GravityView inline edit by default
// Src: https://docs.gravitykit.com/article/418-inline-edit-enable-editing
add_action('gravityview/template/before', 'set_gravityview_inline_edit_cookies');
/**
 * Set cookies toggling Inline Edit to on by default. Requires GravityView 2.0.
 *
 * @uses gravityview_get_current_view_data
 * @uses setcookie
 *
 * @return void
 */
function set_gravityview_inline_edit_cookies( \GV\Template_Context $gravityview = null ) {
	wp_print_scripts( 'jquery-cookie' );
?>
	<script>
		jQuery( window ).on( 'load', function() {
			if( jQuery.cookie ) {
			<?php
				printf( "jQuery.cookie( 'gv-inline-edit-view-%d', 'enabled', { path: '%s', domain: '%s' } );", $gravityview->view->ID, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
			?>
			} else {
				console.error("Could not set cookie for inline-edit.");
			}

		}); 
	</script>
<?php
}

// To disable the search on datatables
// Src: https://docs.gravitykit.com/article/201-how-to-disable-the-datatables-search-filter
add_filter( 'gravityview_datatables_js_options', function( $config ) {

  $config['searching'] = false;

  return $config;
}, 20 );


// Redirect user to previous URL after they logged in
// Src: https://www.2hac.com/help/redirect-to-previous-page-after-login-when-using-ultimate-member-plugin-for-wordpress/
add_action( 'um_after_form', 'vr_redirect_to_previous_url', 10, 1 );
function vr_redirect_to_previous_url( $args ) {
	
	if(isset($_SERVER['HTTP_REFERER'])) {
		echo '<input type="hidden" name="redirect_to" value="' . $_SERVER['HTTP_REFERER'] . '" >'; 
	}
}

// Redirect to profile page after user confirmed email
add_filter('um_after_email_confirmation_redirect', 'vr_redirect_after_activation', 10, 3);
function vr_redirect_after_activation( $redirect, $user_id, $login ) {
	return home_url() . '/user/?user_id=' . $user_id;	// Redirect to profile page
}


// To get average score for a player or all players. It accepts relative date and time. Refer to https://www.php.net/manual/en/datetime.formats.relative.php
// To test relative date/time, visit http://docs.wp-event-organiser.com/querying-events/relative-date-formats/
function get_average_score( $form_id, $score_key, $username_key, $username = '', $start = '', $end = '' ) {
	
    $score_sum = 0;

    if ( !empty( $username ) ) {
        $search_criteria = array(
            'status'        => 'active',
            'field_filters' => array( //which fields to search
			'mode'  => 'all',
                array(
                    'key' => $username_key, 'operator' => 'is', 'value' => $username
                )
            )
        );
    } else {
        $search_criteria = array(
            'status'        => 'active'
        );
    }
    
    if ( !empty( $start) ) {
        $start_date                    = date( 'Y-m-d', strtotime( $start ) );
        $search_criteria['start_date'] = $start_date;
    } 

    if ( !empty( $end) ) {
        $end_date                      = date( 'Y-m-d', strtotime( $end ) );
        $search_criteria['end_date']   = $end_date;
    }

    $sorting = null;
	$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

    // Get the entries. Ref: https://docs.gravityforms.com/searching-and-getting-entries-with-the-gfapi/
    $result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

    if ( !empty($result) ) { 

        foreach($result as $key => $value) {
			if ( is_numeric( $value[ $score_key ] ) ) {
				$score_sum += $value[ $score_key ];
			}
        }
    }
	
	$avg_score = $score_sum / $total_count;
	
	return ( is_float( $avg_score ) || is_int( $avg_score ) ) ? number_format( $avg_score, DECIMAL_PLACES, '.', '' ) : 0;
}

// To get the sum of a field for a player or all players
function get_total_sum( $form_id, $field_key, $username_key, $username = '', $start = '', $end = '' ) {
	
    $field_sum = 0;

    if ( !empty( $username ) ) {
        $search_criteria = array(
            'status'        => 'active',
            'field_filters' => array( //which fields to search
			'mode'  => 'all',
                array(
                    'key' => $username_key, 'operator' => 'is', 'value' => $username
                )
            )
        );
    } else {
        $search_criteria = array(
            'status'        => 'active'
        );
    }
    
    if ( !empty( $start) ) {
        $start_date                    = date( 'Y-m-d', strtotime( $start ) );
        $search_criteria['start_date'] = $start_date;
    } 

    if ( !empty( $end) ) {
        $end_date                      = date( 'Y-m-d', strtotime( $end ) );
        $search_criteria['end_date']   = $end_date;
    }

    $sorting = null;
	$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

    $result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

    if ( !empty($result) ) { 

        foreach($result as $key => $value) {            
            if ( is_numeric( $value[ $field_key ] ) ) {
				$field_sum += $value[ $field_key ];
			}
        }

    }

    return $field_sum;
}

// To get the total count of entries for a player or all players
function get_total_count( $form_id, $username_key, $username = '', $start = '', $end = '' ) {

    if ( !empty( $username ) ) {
        $search_criteria = array(
            'status'        => 'active',
            'field_filters' => array( //which fields to search
			'mode'  => 'all',
                array(
                    'key' => $username_key, 'operator' => 'is', 'value' => $username
                )
            )
        );
	} else {
        $search_criteria = array(
            'status'        => 'active'
        );
    }
    
    if ( !empty( $start) ) {
        $start_date                    = date( 'Y-m-d', strtotime( $start ) );
        $search_criteria['start_date'] = $start_date;
    } 

    if ( !empty( $end) ) {
        $end_date                      = date( 'Y-m-d', strtotime( $end ) );
        $search_criteria['end_date']   = $end_date;
    }

    $sorting = null;
	$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

    $result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

    return $total_count;
}

// To get the highest value of a field for a player or all players
function get_max_value( $form_id, $field_key, $username_key, $username = '', $start = '', $end = '' ) {
	
    $max_value = 0;

    if ( !empty( $username ) ) {
        $search_criteria = array(
            'status'        => 'active',
            'field_filters' => array( //which fields to search
			'mode'  => 'all',
                array(
                    'key' => $username_key, 'operator' => 'is', 'value' => $username
                )
            )
        );
    } else {
        $search_criteria = array(
            'status'        => 'active'
        );
    }
    
    if ( !empty( $start) ) {
        $start_date                    = date( 'Y-m-d', strtotime( $start ) );
        $search_criteria['start_date'] = $start_date;
    } 

    if ( !empty( $end) ) {
        $end_date                      = date( 'Y-m-d', strtotime( $end ) );
        $search_criteria['end_date']   = $end_date;
    }

    $sorting = null;
	$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

    $result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

    if ( !empty($result) ) { 
		$max_value = max( array_column( $result, $field_key ) );
    }

    return $max_value;
}

// To get latest date/time a player or any player submitted their score
function get_latest_submission( $form_id, $username_key, $username = '', $start = '', $end = '' ) {
	
    $latest_entry_datetime = '';

    if ( !empty( $username ) ) {
        $search_criteria = array(
            'status'        => 'active',
            'field_filters' => array( //which fields to search
			'mode'  => 'all',
                array(
                    'key' => $username_key, 'operator' => 'is', 'value' => $username
                )
            )
        );
    } else {
        $search_criteria = array(
            'status'        => 'active'
        );
    }
    
    if ( !empty( $start) ) {
        $start_date                    = date( 'Y-m-d', strtotime( $start ) );
        $search_criteria['start_date'] = $start_date;
    } 

    if ( !empty( $end) ) {
        $end_date                      = date( 'Y-m-d', strtotime( $end ) );
        $search_criteria['end_date']   = $end_date;
    }

    $sorting = array( 'key' => 'date_created', 'direction' => 'DESC' );;
	$paging = null;

    $result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

    if ( !empty($result) ) { 
		$latest_entry_datetime = $result[0]['date_created'];
    }
	
	$timestamp = strtotime( $latest_entry_datetime );
	$latest_date = date( 'Y-m-d', $timestamp );

    return $latest_date;
}

// To get the percentage of a high score that player (or all players) could get. For example, if 10 scores were submitted, how many pct are above 200 points
function get_high_scoring_percentage( $form_id, $field_key, $username_key, $username = '', $start = '', $end = '', $score_benchmark = 0 ) {
	
    $score_hit_benchmark = 0;
	$percentage = '';

    if ( !empty( $username ) ) {
        $search_criteria = array(
            'status'        => 'active',
            'field_filters' => array( //which fields to search
			'mode'  => 'all',
                array(
                    'key' => $username_key, 'operator' => 'is', 'value' => $username
                )
            )
        );
    } else {
        $search_criteria = array(
            'status'        => 'active'
        );
    }
    
    if ( !empty( $start) ) {
        $start_date                    = date( 'Y-m-d', strtotime( $start ) );
        $search_criteria['start_date'] = $start_date;
    } 

    if ( !empty( $end) ) {
        $end_date                      = date( 'Y-m-d', strtotime( $end ) );
        $search_criteria['end_date']   = $end_date;
    }

    $sorting = null;
	$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

    $result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

    if ( !empty($result) ) { 
	
		foreach ( $result as $key => $value ) {
			if ( $value[ $field_key ] >= $score_benchmark ) {
				$score_hit_benchmark++;
			} 
		}
    } 

	$percentage = ($score_hit_benchmark / $total_count) * 100;
	$percentage = number_format( $percentage, DECIMAL_PLACES, '.', '');

    return $percentage . '%';
}

function get_players_list( $form_id, $username_key, $start = '', $end = '' ) {
	
    $players = array();

	$search_criteria = array(
		'status'        => 'active'
	);
    
    if ( !empty( $start) ) {
        $start_date                    = date( 'Y-m-d', strtotime( $start ) );
        $search_criteria['start_date'] = $start_date;
    } 

    if ( !empty( $end) ) {
        $end_date                      = date( 'Y-m-d', strtotime( $end ) );
        $search_criteria['end_date']   = $end_date;
    }

    $sorting = null;
	$paging = array( 'offset' => 0, 'page_size' => 9999999999 );

    // Get the entries. Ref: https://docs.gravityforms.com/searching-and-getting-entries-with-the-gfapi/
    $result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

    if ( !empty( $result ) ) { 

        foreach( $result as $key => $value ) {
			if ( !in_array( $value[ $username_key ], $players ) ) {
				$players[] = $value[ $username_key ];
			}	
        }
    }

    return $players;
}


// Get an array of players' countries who are participating in a game
function vr_get_participating_countries( $form_id, $country_field ) {
	
    $countries = array();

	$search_criteria = array(
		'status'        => 'active'
	);

    $sorting = null;
	$paging = array( 'offset' => 0, 'page_size' => 9999999999 );

    // Get the entries. Ref: https://docs.gravityforms.com/searching-and-getting-entries-with-the-gfapi/
    $result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

    if ( !empty( $result ) ) { 

        foreach( $result as $key => $value ) {
			if ( !in_array( $value[ $country_field ], $countries ) ) {
				$countries[] = $value[ $country_field ];
			}	
        }
    }

    return $countries;

}

// Get an array of players' states who are participating in a game
function vr_get_participating_states( $form_id, $state_field ) {
	
    $states = array();

	$search_criteria = array(
		'status'        => 'active'
	);

    $sorting = null;
	$paging = array( 'offset' => 0, 'page_size' => 9999999999 );

    // Get the entries. Ref: https://docs.gravityforms.com/searching-and-getting-entries-with-the-gfapi/
    $result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

    if ( !empty( $result ) ) { 

        foreach( $result as $key => $value ) {
			if ( !in_array( $value[ $state_field ], $states ) ) {
				$states[] = $value[ $state_field ];
			}	
        }
    }

    return $states;

}


// Get an array of players' cities who are participating in a game
function vr_get_participating_cities( $form_id, $city_field ) {
	
    $cities = array();

	$search_criteria = array(
		'status'        => 'active'
	);

    $sorting = null;
	$paging = array( 'offset' => 0, 'page_size' => 9999999999 );

    // Get the entries. Ref: https://docs.gravityforms.com/searching-and-getting-entries-with-the-gfapi/
    $result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

    if ( !empty( $result ) ) { 

        foreach( $result as $key => $value ) {
			if ( !in_array( $value[ $city_field ], $cities ) ) {
				$cities[] = $value[ $city_field ];
			}	
        }
    }

    return $cities;

}


// Sort ranking from highest average score to lowest (desc)
function sort_by_avg_score($a, $b)
{
    $a = $a['avg_score'];
    $b = $b['avg_score'];

    if ($a == $b) return 0;
    return ($a > $b) ? -1 : 1;
}


// Tell user to join a game right after they signed-up on site
//add_action( 'vr_display_notices', 'vr_has_user_joined_a_game' );	// Disabled for now since some user may join mini league only
function vr_has_user_joined_a_game() {
    if ( is_user_logged_in() ) {
		
		$user_id = get_current_user_id();

		if ( empty( get_user_meta( $user_id, '_joined_game', true ) ) ) {
			echo '<input type="checkbox" id="vr-hide" />
					<div id="vr-info" class="vr-notice-info"><small>' . wp_kses( __( 'We are so glad you are here! Now, go ahead and <a href="/join-game">join a game</a>.', 'rife-free-child' ), array( 'a' => array( 'href' => array(), 'title' => array() ) ) ) . '</small><label for="vr-hide" class="vr-close">x</label>
					</div>';
			/*echo '<input type="checkbox" id="vr-hide" />
					<div id="vr-info" class="vr-notice-info"><small>' . wp_kses( __( 'You need to <a href="/join-game">join a game</a> before submitting your score.', 'rife-free-child' ), array( 'a' => array( 'href' => array(), 'title' => array() ) ) ) . '</small><label for="vr-hide" class="vr-close">x</label>
					</div>';*/
		}
    }
}


/***PREMIUM BOWLING************************************************/

// To append URL query parameter with search filter for Premium Bowling Stats tab that is on user profile page. To enable us to search the leaderboard using user ID of the particular profile
add_filter( 'um_profile_menu_link_premium-bowling-stats', 'vr_add_query_parameter_pb', 10, 1 );
function vr_add_query_parameter_pb( $nav_link ) {
	
	$profile_username = get_query_var( 'um_user' );	// To get the username of the profile. Hidden in the URL like https://vr.test/user/test10/
	$user = get_user_by( 'login', $profile_username);
	$user_id = $user->ID;
	$player_name = get_user_meta( $user_id, '_player_name_pb', true );
	
	if ( empty( $player_name ) ) {
		$player_name = '0';
	}
	
	// filter_24 means search field with id 24 of the GravityView leaderboard table. Similar to [gravityview id="12274" search_field="24" search_value="" ] where $user_id is the search value
	$nav_link = add_query_arg( array(
			'filter_24' => $user_id,
			'filter_9' => $user_id,
			'filter_1' => $player_name,
			'filter_2' => $player_name,
			'mode' => 'any'
	), $nav_link );
	
	return $nav_link;
}


// Process user after they joined a game
add_action( 'gform_after_submission_' . FORM_ID_FOR_JOIN_PREMIUM_BOWLING, 'vr_process_user_after_joined_pb', 10, 2 );
function vr_process_user_after_joined_pb( $entry, $form ) {
	
	$user_id = get_current_user_id();

    //If user is not logged in...
    if ( $user_id == 0 ) {
        wp_redirect( wp_login_url() ); 
        exit();
    }
	
	if ( empty( get_user_meta( $user_id, '_joined_game', true ) ) ) { 
		add_user_meta( $user_id, '_joined_game', 1 );
	}
	
	if ( empty( get_user_meta( $user_id, '_player_name_pb', true ) ) ) { 
		add_user_meta( $user_id, '_player_name_pb', $entry['3'] );
	}
	
	//dEBUG
	error_log("entry['3']: " . $entry['3']);
	
}


// To check if player name exists or already registered for Premium Bowling
add_filter( 'gform_field_validation_' . FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD . '_1', 'vr_does_username_exist_in_premium_bowling', 10, 4 );
function vr_does_username_exist_in_premium_bowling( $result, $value, $form, $field ) {
	
	$form_id = FORM_ID_FOR_JOIN_PREMIUM_BOWLING;
	
	$search_criteria = array(
		'status'        => 'active',
		'field_filters' => array( //which fields to search
		'mode'  => 'all',
			array(
				'key' => '3', 'operator' => 'is', 'value' => $value
			)
		)
	);
	
    $search_result = GFAPI::get_entries( $form_id, $search_criteria );

    if ( empty( $search_result ) && $result['is_valid'] ) { 
		$result['is_valid'] = false;
		$result['message'] = esc_html__( 'Player name does not exist. Ensure player has signed up and the username is entered with the right case.', 'rife-free-child');
    }
	
	/* Use below to check if username is used across the entire site, not just in a particular game */
	
	// $user = get_user_by( 'login', $value);

    // if ( !$user && $result['is_valid'] ) { 
		// $result['is_valid'] = false;
		// $result['message'] = esc_html__( 'Player name does not exist. Ensure the name is entered with the right case.', 'rife-free-child');
    // }
  
    return $result;
}


// To check if either one of two player names exists or already registered for a specific game
add_filter( 'gform_field_validation_' . FORM_ID_FOR_PREMIUM_BOWLING_CHALLENGE_SCORECARD . '_1', 'vr_does_either_username_exist', 10, 4 );
function vr_does_either_username_exist( $result, $value, $form, $field ) {
	
	$form_id = FORM_ID_FOR_JOIN_PREMIUM_BOWLING;
	
	$value2 = rgpost( 'input_2' );
	
	$search_criteria = array(
		'status'        => 'active',
		'field_filters' => array( //which fields to search
		'mode'  => 'any',	// This checks if either key value below exists
			array('key' => '3', 'operator' => 'is', 'value' => $value),
			array('key' => '3', 'operator' => 'is', 'value' => $value2)
		)
	);
	
    $search_result = GFAPI::get_entries( $form_id, $search_criteria );

    if ( empty( $search_result ) && $result['is_valid'] ) { 
		$result['is_valid'] = false;
		$result['message'] = esc_html__( 'Either player name does not exist. Ensure either player has signed up and the usernames are entered with the right case without spaces.', 'rife-free-child');
    }
  
    return $result;
}

// To check if player name is taken for Premium Bowling tournament
add_filter( 'gform_field_validation_' . FORM_ID_FOR_JOIN_PREMIUM_BOWLING . '_3', 'vr_is_username_taken', 10, 4 );
function vr_is_username_taken( $result, $value, $form, $field ) {
	
	$form_id = FORM_ID_FOR_JOIN_PREMIUM_BOWLING;
	
	$total_chars = strlen($value);
	
	//preg_match( '/^(?i)[a-z\d\-_\s]+$/', $value )	Regex to detect alphanumeric, -, _ and space
	
	if ( $total_chars > 20 || $total_chars < 3 ) {
		$result['is_valid'] = false;
		$result['message'] = esc_html__( 'Min is three characters and maximum is 20 (including spaces or dashes)', 'rife-free-child');
		
	} else {
		$search_criteria = array(
			'status'        => 'active',
			'field_filters' => array( //which fields to search
			'mode'  => 'all',
				array(
					'key' => '3', 'operator' => 'is', 'value' => $value
				)
			)
		);
		
		$search_result = GFAPI::get_entries( $form_id, $search_criteria );

		if ( !empty( $search_result ) && $result['is_valid'] ) { 
			$result['is_valid'] = false;
			$result['message'] = esc_html__( 'Username already taken. Enter the same username that you use in Premium Bowling game', 'rife-free-child');
		}
	}
  
    return $result;
}


// Stop user from submitting the form FORM_ID_FOR_JOIN_PREMIUM_BOWLING twice.
add_filter( 'gform_get_form_filter_' . FORM_ID_FOR_JOIN_PREMIUM_BOWLING, 'vr_check_user_in_premium_bowling', 10, 2 );
function vr_check_user_in_premium_bowling( $form_string, $form ) {
	
    $form_id = FORM_ID_FOR_JOIN_PREMIUM_BOWLING;
	
    $current_user = wp_get_current_user();

    //If user is not logged in...
    if ( empty( $current_user ) ) {
        nocache_headers(); 
        wp_redirect( wp_login_url() ); 
        exit();
    }

    $search_criteria = array(
        'status'        => 'active',
        'field_filters' => array( //which fields to search
            array(
                'key' => 'created_by', 'value' => $current_user->ID, //Current logged in user
            )
        )
    );

    $entry = GFAPI::get_entries( $form_id, $search_criteria );

    if ( !empty( $entry ) ) {
        $form_string = esc_html__( 'Sorry, you already joined this game. You cannot join twice.', 'rife-free-child' );
    }   
    return $form_string;
}


// Check if user has registered for a game before they can submit scores.
add_filter( 'gform_get_form_filter_' . FORM_ID_FOR_SUBMIT_SCORE_FOR_PREMIUM_BOWLING, 'vr_is_player_registered_for_premium_bowling', 10, 2 );
function vr_is_player_registered_for_premium_bowling( $form_string, $form ) {
	
    $form_id = FORM_ID_FOR_JOIN_PREMIUM_BOWLING;
	
    $current_user = wp_get_current_user();

    //If user is not logged in...
    if ( empty( $current_user ) ) {
        nocache_headers(); 
        wp_redirect( wp_login_url() ); 
        exit();
    }

    $search_criteria = array(
        'status'        => 'active',
        'field_filters' => array( //which fields to search
            array(
                'key' => 'created_by', 'value' => $current_user->ID, //Current logged in user
            )
        )
    );

    $entry = GFAPI::get_entries( $form_id, $search_criteria );

    if ( empty( $entry ) ) {
        $form_string = wp_kses( __( 'Sorry, you have not registered for this game. You need to <a href="/join-premium-bowling">enroll first</a>.', 'rife-free-child' ), array( 'a' => array( 'href' => array(), 'title' => array() ) ) );
    }   
    return $form_string;
}


// To add player name in hidden username text field 
add_filter( 'gform_pre_render_' . FORM_ID_FOR_SUBMIT_SCORE_FOR_PREMIUM_BOWLING, 'vr_add_player_name_pb' );
function vr_add_player_name_pb( $form ) {
	
	$form_id = FORM_ID_FOR_JOIN_PREMIUM_BOWLING;
	
	$user_id = get_current_user_id();
	
	$search_criteria = array(
		'status'        => 'active',
		'field_filters' => array( //which fields to search
			array(
				'key' => '9', 'value' => $user_id
			)
		)
	);
	
	$paging = array( 'offset' => 0, 'page_size' => 1 );

	$result = GFAPI::get_entries( $form_id, $search_criteria, null, $paging, $total_count );
	
	if ( !empty( $result ) ) {
		$player_name = $result[0]['3'];
		
		// Add player username to the username hidden field
		$fields = $form['fields'];
		foreach( $form['fields'] as &$field ) {
		  if ( $field->id == 12 ) {
			$field->defaultValue = $player_name;
		  }
		}
	}
    return $form;
}


// Process the score submitted by user
add_action( 'gravityflow_step_complete', 'vr_process_diy_score_in_premium_bowling', 10, 4 );
function vr_process_diy_score_in_premium_bowling( $step_id, $entry_id, $form_id, $status ) {
	
	$form_id = FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD;
	$join_form_id = FORM_ID_FOR_JOIN_PREMIUM_BOWLING;
	
    if ( $step_id == '4' ) {
		
        $entry = GFAPI::get_entry( $entry_id );
		
        if ( ! is_wp_error( $entry ) && isset( $entry['12'] ) && ! empty( $entry['12'] ) ) {
		
			$player_name = rgar( $entry, '12' );
			$user_id = get_form_field_value( $join_form_id, '9', '3', $player_name );
			
			$search_criteria = array(
				'status'        => 'active',
				'field_filters' => array( //which fields to search
					array(
						'key' => '1', 'value' => $player_name
					)
				)
			);

			$result = GFAPI::get_entries( $form_id, $search_criteria, null, null, $total_count );
			
			if ( $total_count == 1 ) {
				vr_submitted_first_score_in_premium_bowling_listener( $user_id );
			}
			
			vr_process_diy_score_premium_bowling_weekly( $entry );
			vr_process_diy_score_premium_bowling_monthly( $entry );
			vr_process_diy_score_premium_bowling_overall( $entry );
        }
    }
}


// Process the score submitted by admin on behalf of user (i.e: score submitted via email)
add_action( 'gform_after_submission_' . FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, 'vr_process_score_premium_bowling', 10, 2 );
function vr_process_score_premium_bowling( $entry, $form ) {
	
	$form_id = FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD;
	$join_form_id = FORM_ID_FOR_JOIN_PREMIUM_BOWLING;
	
	if ( ! is_wp_error( $entry ) && isset( $entry['1'] ) && ! empty( $entry['1'] ) ) {
	
		$player_name = rgar( $entry, '1' );
		$user_id = get_form_field_value( $join_form_id, '9', '3', $player_name );
		
		$search_criteria = array(
			'status'        => 'active',
			'field_filters' => array( //which fields to search
				array(
					'key' => '1', 'value' => $player_name
				)
			)
		);

		$result = GFAPI::get_entries( $form_id, $search_criteria, null, null, $total_count );
		
		if ( $total_count == 1 ) {
			vr_submitted_first_score_in_premium_bowling_listener( $user_id );
		}
		
		GFAPI::update_entry_field( $entry['id'], '9', $user_id );	// Update entry with user ID

		vr_process_score_premium_bowling_weekly( $entry, $form );
		vr_process_score_premium_bowling_monthly( $entry, $form );
		vr_process_score_premium_bowling_overall( $entry, $form );
	}
}


// Process score submitted by user for weekly leaderboard
function vr_process_diy_score_premium_bowling_weekly( $entry ) {
	
	$leaderboard_form_id = FORM_ID_FOR_PREMIUM_BOWLING_LEADERBOARD_WEEKLY;
	$join_form_id = FORM_ID_FOR_JOIN_PREMIUM_BOWLING;
		
	$player_name = rgar( $entry, '12' );
	$score = rgar( $entry, '16' );
	$strikes = rgar( $entry, '13' );
	$spares = rgar( $entry, '14' );
	$turkeys = rgar( $entry, '15' );
	
    $search_criteria = array(
        'status'        => 'active',
        'field_filters' => array(
		'mode'  => 'all',
            array(
                'key' => '1', 'value' => $player_name
            )
        )
    );

    $result = GFAPI::get_entries( $leaderboard_form_id, $search_criteria );

    if ( empty( $result ) ) {
		
		//Get player's user ID
		$user_id = get_form_field_value( $join_form_id, '9', '3', $player_name );
		
		//Get country of player
		//$user = get_user_by( 'login', $player_name);
		//$user_id = $user->ID;
		$country = get_user_meta( $user_id, 'country', true);
		$state = ( $country == esc_html__( 'United States', 'rife-free-child' ) ) ? get_user_meta( $user_id, 'us_state', true) : get_user_meta( $user_id, 'user_state', true);
		$city = get_user_meta( $user_id, 'city', true);
		
		//dEBUG
		error_log("country: " . $country);
		
		// Add player into leaderboard
		$player_entry = array(
			"form_id" => $leaderboard_form_id,
			"24" => $user_id,
			"1" => $player_name,
			"23.6" => $country,
			"23.4" => $state,
			"23.3" => $city,
			"8" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ),
			"9" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ),
			"10" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name ),
			"13" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ),
			"6" => get_max_value( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ),
			"14" => get_total_count( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ),
			"18" => get_latest_submission( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ),
			"17" => get_high_scoring_percentage( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name, '', '', 200 ),
			"25" => get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ),
			"26" => get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ),
			"27" => get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name )
		);

		$entry_id = GFAPI::add_entry( $player_entry );
	
    } elseif ( !empty( $result ) ) {	
		
        $entry_id = $result[0]['id'];
		
		// Update existing player stats
		GFAPI::update_entry_field( $entry_id, '8', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '9', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '10', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '13', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '6', get_max_value( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '14', get_total_count( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '18', get_latest_submission( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '17', get_high_scoring_percentage( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name, '', '', 200 ) );
		GFAPI::update_entry_field( $entry_id, '25', get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '26', get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '27', get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name ) );

	}
}


// Process score submitted by user for monthly leaderboard
function vr_process_diy_score_premium_bowling_monthly( $entry ) {
	
	$leaderboard_form_id = FORM_ID_FOR_PREMIUM_BOWLING_LEADERBOARD_MONTHLY;
	$join_form_id = FORM_ID_FOR_JOIN_PREMIUM_BOWLING;
	
	$player_name = rgar( $entry, '12' );
	$score = rgar( $entry, '16' );
	$strikes = rgar( $entry, '13' );
	$spares = rgar( $entry, '14' );
	$turkeys = rgar( $entry, '15' );
	
    $search_criteria = array(
        'status'        => 'active',
        'field_filters' => array(
		'mode'  => 'all',
            array(
                'key' => '1', 'value' => $player_name
            )
        )
    );

    $result = GFAPI::get_entries( $leaderboard_form_id, $search_criteria );

    if ( empty( $result ) ) {
		
		//Get player's user ID
		$user_id = get_form_field_value( $join_form_id, '9', '3', $player_name );
		
		//Get country of player
		//$user = get_user_by( 'login', $player_name);
		//$user_id = $user->ID;
		$country = get_user_meta( $user_id, 'country', true);
		$state = ( $country == esc_html__( 'United States', 'rife-free-child' ) ) ? get_user_meta( $user_id, 'us_state', true) : get_user_meta( $user_id, 'user_state', true);
		$city = get_user_meta( $user_id, 'city', true);
		
		// Add player into leaderboard
		$player_entry = array(
			"form_id" => $leaderboard_form_id,
			"24" => $user_id,
			"1" => $player_name,
			"23.6" => $country,
			"23.4" => $state,
			"23.3" => $city,
			"8" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ),
			"9" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ),
			"10" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name ),
			"13" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ),
			"6" => get_max_value( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ),
			"14" => get_total_count( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ),
			"18" => get_latest_submission( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ),
			"17" => get_high_scoring_percentage( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name, '', '', 200 ),
			"25" => get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ),
			"26" => get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ),
			"27" => get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name )
		);

		$entry_id = GFAPI::add_entry( $player_entry );
	
    } elseif ( !empty( $result ) ) {	
		
        $entry_id = $result[0]['id'];
		
		// Update existing player stats
		GFAPI::update_entry_field( $entry_id, '8', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '9', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '10', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '13', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '6', get_max_value( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '14', get_total_count( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '18', get_latest_submission( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '17', get_high_scoring_percentage( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name, '', '', 200 ) );
		GFAPI::update_entry_field( $entry_id, '25', get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '26', get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '27', get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name ) );

	}
}


// Process score submitted by user for overall leaderboard
function vr_process_diy_score_premium_bowling_overall( $entry ) {
	
	$leaderboard_form_id = FORM_ID_FOR_PREMIUM_BOWLING_LEADERBOARD_OVERALL;
	$join_form_id = FORM_ID_FOR_JOIN_PREMIUM_BOWLING;
	
	$player_name = rgar( $entry, '12' );
	$score = rgar( $entry, '16' );
	$strikes = rgar( $entry, '13' );
	$spares = rgar( $entry, '14' );
	$turkeys = rgar( $entry, '15' );
	
    $search_criteria = array(
        'status'        => 'active',
        'field_filters' => array(
		'mode'  => 'all',
            array(
                'key' => '1', 'value' => $player_name
            )
        )
    );

    $result = GFAPI::get_entries( $leaderboard_form_id, $search_criteria );

    if ( empty( $result ) ) {
		
		//Get player's user ID
		$user_id = get_form_field_value( $join_form_id, '9', '3', $player_name );
		
		//Get country of player
		//$user = get_user_by( 'login', $player_name);
		//$user_id = $user->ID;
		$country = get_user_meta( $user_id, 'country', true);
		$state = ( $country == esc_html__( 'United States', 'rife-free-child' ) ) ? get_user_meta( $user_id, 'us_state', true) : get_user_meta( $user_id, 'user_state', true);
		$city = get_user_meta( $user_id, 'city', true);
		
		// Add player into leaderboard
		$player_entry = array(
			"form_id" => $leaderboard_form_id,
			"24" => $user_id,
			"1" => $player_name,
			"23.6" => $country,
			"23.4" => $state,
			"23.3" => $city,
			"8" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ),
			"9" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ),
			"10" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name ),
			"13" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ),
			"6" => get_max_value( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ),
			"14" => get_total_count( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ),
			"18" => get_latest_submission( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ),
			"17" => get_high_scoring_percentage( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name, '', '', 200 ),
			"25" => get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ),
			"26" => get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ),
			"27" => get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name )
		);

		$entry_id = GFAPI::add_entry( $player_entry );
	
    } elseif ( !empty( $result ) ) {	
		
        $entry_id = $result[0]['id'];
		
		// Update existing player stats
		GFAPI::update_entry_field( $entry_id, '8', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '9', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '10', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '13', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '6', get_max_value( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '14', get_total_count( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '18', get_latest_submission( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '17', get_high_scoring_percentage( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name, '', '', 200 ) );
		GFAPI::update_entry_field( $entry_id, '25', get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '26', get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '27', get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name ) );

	}
}


// Process score submitted by admin on behalf of user for weekly leaderboard
function vr_process_score_premium_bowling_weekly( $entry, $form ) {
	
	$leaderboard_form_id = FORM_ID_FOR_PREMIUM_BOWLING_LEADERBOARD_WEEKLY;
	$join_form_id = FORM_ID_FOR_JOIN_PREMIUM_BOWLING;
		
	$player_name = rgar( $entry, '1' );
	$score = rgar( $entry, '4' );
	$strikes = rgar( $entry, '5' );
	$spares = rgar( $entry, '6' );
	$turkeys = rgar( $entry, '7' );
	
    $search_criteria = array(
        'status'        => 'active',
        'field_filters' => array(
		'mode'  => 'all',
            array(
                'key' => '1', 'value' => $player_name
            )
        )
    );

    $result = GFAPI::get_entries( $leaderboard_form_id, $search_criteria );

    if ( empty( $result ) ) {
		
		//Get player's user ID
		$user_id = get_form_field_value( $join_form_id, '9', '3', $player_name );
		
		//Get country of player
		//$user = get_user_by( 'login', $player_name);
		//$user_id = $user->ID;
		$country = get_user_meta( $user_id, 'country', true);
		$state = ( $country == esc_html__( 'United States', 'rife-free-child' ) ) ? get_user_meta( $user_id, 'us_state', true) : get_user_meta( $user_id, 'user_state', true);
		$city = get_user_meta( $user_id, 'city', true);
		
		// Add player into leaderboard
		$player_entry = array(
			"form_id" => $leaderboard_form_id,
			"24" => $user_id,
			"1" => $player_name,
			"23.6" => $country,
			"23.4" => $state,
			"23.3" => $city,
			"8" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ),
			"9" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ),
			"10" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name ),
			"13" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ),
			"6" => get_max_value( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ),
			"14" => get_total_count( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ),
			"18" => get_latest_submission( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ),
			"17" => get_high_scoring_percentage( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name, '', '', 200 ),
			"25" => get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ),
			"26" => get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ),
			"27" => get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name )
		);

		$entry_id = GFAPI::add_entry( $player_entry );
	
    } elseif ( !empty( $result ) ) {	
		
        $entry_id = $result[0]['id'];
		
		// Update existing player stats
		GFAPI::update_entry_field( $entry_id, '8', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '9', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '10', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '13', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '6', get_max_value( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '14', get_total_count( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '18', get_latest_submission( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '17', get_high_scoring_percentage( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name, '', '', 200 ) );
		GFAPI::update_entry_field( $entry_id, '25', get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '26', get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '27', get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name ) );

	}
}


// Process score submitted by admin on behalf of user for monthly leaderboard
function vr_process_score_premium_bowling_monthly( $entry, $form ) {
	
	$leaderboard_form_id = FORM_ID_FOR_PREMIUM_BOWLING_LEADERBOARD_MONTHLY;
	$join_form_id = FORM_ID_FOR_JOIN_PREMIUM_BOWLING;
		
	$player_name = rgar( $entry, '1' );
	$score = rgar( $entry, '4' );
	$strikes = rgar( $entry, '5' );
	$spares = rgar( $entry, '6' );
	$turkeys = rgar( $entry, '7' );
	
    $search_criteria = array(
        'status'        => 'active',
        'field_filters' => array(
		'mode'  => 'all',
            array(
                'key' => '1', 'value' => $player_name
            )
        )
    );

    $result = GFAPI::get_entries( $leaderboard_form_id, $search_criteria );

    if ( empty( $result ) ) {
		
		//Get player's user ID
		$user_id = get_form_field_value( $join_form_id, '9', '3', $player_name );
		
		//Get country of player
		//$user = get_user_by( 'login', $player_name);
		//$user_id = $user->ID;
		$country = get_user_meta( $user_id, 'country', true);
		$state = ( $country == esc_html__( 'United States', 'rife-free-child' ) ) ? get_user_meta( $user_id, 'us_state', true) : get_user_meta( $user_id, 'user_state', true);
		$city = get_user_meta( $user_id, 'city', true);
		
		// Add player into leaderboard
		$player_entry = array(
			"form_id" => $leaderboard_form_id,
			"24" => $user_id,
			"1" => $player_name,
			"23.6" => $country,
			"23.4" => $state,
			"23.3" => $city,
			"8" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ),
			"9" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ),
			"10" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name ),
			"13" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ),
			"6" => get_max_value( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ),
			"14" => get_total_count( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ),
			"18" => get_latest_submission( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ),
			"17" => get_high_scoring_percentage( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name, '', '', 200 ),
			"25" => get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ),
			"26" => get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ),
			"27" => get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name )
		);

		$entry_id = GFAPI::add_entry( $player_entry );
	
    } elseif ( !empty( $result ) ) {	
		
        $entry_id = $result[0]['id'];
		
		// Update existing player stats
		GFAPI::update_entry_field( $entry_id, '8', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '9', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '10', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '13', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '6', get_max_value( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '14', get_total_count( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '18', get_latest_submission( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '17', get_high_scoring_percentage( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name, '', '', 200 ) );
		GFAPI::update_entry_field( $entry_id, '25', get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '26', get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '27', get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name ) );

	}
}


// Process score submitted by admin on behalf of user for overall leaderboard
function vr_process_score_premium_bowling_overall( $entry, $form ) {
	
	$leaderboard_form_id = FORM_ID_FOR_PREMIUM_BOWLING_LEADERBOARD_OVERALL;
	$join_form_id = FORM_ID_FOR_JOIN_PREMIUM_BOWLING;
	
	$player_name = rgar( $entry, '1' );
	$score = rgar( $entry, '4' );
	$strikes = rgar( $entry, '5' );
	$spares = rgar( $entry, '6' );
	$turkeys = rgar( $entry, '7' );
	
    $search_criteria = array(
        'status'        => 'active',
        'field_filters' => array(
		'mode'  => 'all',
            array(
                'key' => '1', 'value' => $player_name
            )
        )
    );

    $result = GFAPI::get_entries( $leaderboard_form_id, $search_criteria );

    if ( empty( $result ) ) {
		
		//Get player's user ID
		$user_id = get_form_field_value( $join_form_id, '9', '3', $player_name );
		
		//Get country of player
		//$user = get_user_by( 'login', $player_name);
		//$user_id = $user->ID;
		$country = get_user_meta( $user_id, 'country', true);
		$state = ( $country == esc_html__( 'United States', 'rife-free-child' ) ) ? get_user_meta( $user_id, 'us_state', true) : get_user_meta( $user_id, 'user_state', true);
		$city = get_user_meta( $user_id, 'city', true);
		
		// Add player into leaderboard
		$player_entry = array(
			"form_id" => $leaderboard_form_id,
			"24" => $user_id,
			"1" => $player_name,
			"23.6" => $country,
			"23.4" => $state,
			"23.3" => $city,
			"8" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ),
			"9" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ),
			"10" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name ),
			"13" => get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ),
			"6" => get_max_value( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ),
			"14" => get_total_count( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ),
			"18" => get_latest_submission( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ),
			"17" => get_high_scoring_percentage( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name, '', '', 200 ),
			"25" => get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ),
			"26" => get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ),
			"27" => get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name )
		);

		$entry_id = GFAPI::add_entry( $player_entry );
	
    } elseif ( !empty( $result ) ) {	
		
        $entry_id = $result[0]['id'];
		
		// Update existing player stats
		GFAPI::update_entry_field( $entry_id, '8', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '9', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '10', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '13', get_average_score( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '6', get_max_value( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '14', get_total_count( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '18', get_latest_submission( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '17', get_high_scoring_percentage( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '4', '1', $player_name, '', '', 200 ) );
		GFAPI::update_entry_field( $entry_id, '25', get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '5', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '26', get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '6', '1', $player_name ) );
		GFAPI::update_entry_field( $entry_id, '27', get_total_sum( FORM_ID_FOR_PREMIUM_BOWLING_SCORECARD, '7', '1', $player_name ) );

	}
}


// To update the weekly leaderboard with the rankings of players
add_action( 'vr_generate_ranking_premium_bowling_weekly', 'vr_generate_ranking_premium_bowling_weekly' );
function vr_generate_ranking_premium_bowling_weekly() {
	
	$form_id = FORM_ID_FOR_PREMIUM_BOWLING_LEADERBOARD_WEEKLY;
	$rank = 0;

	$search_criteria = array(
		'status'        => 'active'
	);

	$sorting = array( 'key' => '13', 'direction' => 'DESC', 'is_numeric' => true );	

	$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

    $result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

    if ( !empty($result) ) { 
	
		foreach($result as $key => $value) {
			$rank++;
			GFAPI::update_entry_field( $value['id'], '21', $value['20'] );	// Save old ranking
			GFAPI::update_entry_field( $value['id'], '20', $rank );	// Set new ranking
        }
    }	
}


// To update the weekly leaderboard with the rankings of players from specific countries
add_action( 'vr_generate_country_ranking_premium_bowling_weekly', 'vr_generate_country_ranking_premium_bowling_weekly' );
function vr_generate_country_ranking_premium_bowling_weekly() {
	
	$form_id = FORM_ID_FOR_PREMIUM_BOWLING_LEADERBOARD_WEEKLY;
	$country_field = '23.6';
	$countries = vr_get_participating_countries( $form_id, $country_field );
	
	foreach ( $countries as $country ) {
		
		$rank = 0;
	
		$search_criteria = array(
			'status'        => 'active',
			'field_filters' => array(
			'mode'  => 'all',
				array(
					'key' => $country_field, 'value' => $country
				)
			)
			
		);

		$sorting = array( 'key' => '13', 'direction' => 'DESC', 'is_numeric' => true );	

		$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

		$result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

		if ( !empty($result) ) { 
		
			foreach($result as $key => $value) {
				$rank++;
				GFAPI::update_entry_field( $value['id'], '22', $rank );	// Set country ranking
			}
		}
	}		
}


// To update the weekly leaderboard with the rankings of players from specific states
add_action( 'vr_generate_state_ranking_premium_bowling_weekly', 'vr_generate_state_ranking_premium_bowling_weekly' );
function vr_generate_state_ranking_premium_bowling_weekly() {
	
	$form_id = FORM_ID_FOR_PREMIUM_BOWLING_LEADERBOARD_WEEKLY;
	$state_field = '23.4';
	$states = vr_get_participating_states( $form_id, $state_field );
	
	foreach ( $states as $state ) {
		
		$rank = 0;
	
		$search_criteria = array(
			'status'        => 'active',
			'field_filters' => array(
			'mode'  => 'all',
				array(
					'key' => $state_field, 'value' => $state
				)
			)
			
		);

		$sorting = array( 'key' => '13', 'direction' => 'DESC', 'is_numeric' => true );	

		$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

		$result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

		if ( !empty($result) ) { 
		
			foreach($result as $key => $value) {
				$rank++;
				GFAPI::update_entry_field( $value['id'], '28', $rank );	// Set state ranking
			
			}
		}
	}		
}

// NOT IMPLEMENTED: To update the leaderboard with the rankings of players from specific cities
//add_action( 'vr_generate_city_ranking_premium_bowling', 'vr_generate_city_ranking_premium_bowling' );
function vr_generate_city_ranking_premium_bowling() {
	
	$form_id = FORM_ID_FOR_PREMIUM_BOWLING_LEADERBOARD_WEEKLY;
	$city_field = '23.3';
	$cities = vr_get_participating_cities( $form_id, $city_field );
	
	foreach ( $cities as $city ) {
		
		$rank = 0;
	
		$search_criteria = array(
			'status'        => 'active',
			'field_filters' => array(
			'mode'  => 'all',
				array(
					'key' => $city_field, 'value' => $city
				)
			)
			
		);

		$sorting = array( 'key' => '13', 'direction' => 'DESC', 'is_numeric' => true );	

		$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

		$result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

		if ( !empty($result) ) { 
		
			foreach($result as $key => $value) {
				$rank++;
				GFAPI::update_entry_field( $value['id'], '29', $rank );	// Set city ranking
			}
		}
	}		
}


// To update the monthly leaderboard with the rankings of players
add_action( 'vr_generate_ranking_premium_bowling_monthly', 'vr_generate_ranking_premium_bowling_monthly' );
function vr_generate_ranking_premium_bowling_monthly() {
	
	$form_id = FORM_ID_FOR_PREMIUM_BOWLING_LEADERBOARD_MONTHLY;
	$rank = 0;

	$search_criteria = array(
		'status'        => 'active'
	);

	$sorting = array( 'key' => '13', 'direction' => 'DESC', 'is_numeric' => true );	

	$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

    $result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

    if ( !empty($result) ) { 
	
		foreach($result as $key => $value) {
			$rank++;
			GFAPI::update_entry_field( $value['id'], '21', $value['20'] );	// Save old ranking
			GFAPI::update_entry_field( $value['id'], '20', $rank );	// Set new ranking
        }
    }	
}


// To update the monthly leaderboard with the rankings of players from specific countries
add_action( 'vr_generate_country_ranking_premium_bowling_monthly', 'vr_generate_country_ranking_premium_bowling_monthly' );
function vr_generate_country_ranking_premium_bowling_monthly() {
	
	$form_id = FORM_ID_FOR_PREMIUM_BOWLING_LEADERBOARD_MONTHLY;
	$country_field = '23.6';
	$countries = vr_get_participating_countries( $form_id, $country_field );
	
	foreach ( $countries as $country ) {
		
		$rank = 0;
	
		$search_criteria = array(
			'status'        => 'active',
			'field_filters' => array(
			'mode'  => 'all',
				array(
					'key' => $country_field, 'value' => $country
				)
			)
			
		);

		$sorting = array( 'key' => '13', 'direction' => 'DESC', 'is_numeric' => true );	

		$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

		$result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

		if ( !empty($result) ) { 
		
			foreach($result as $key => $value) {
				$rank++;
				GFAPI::update_entry_field( $value['id'], '22', $rank );	// Set country ranking
			}
		}
	}		
}


// To update the monthly leaderboard with the rankings of players from specific states
add_action( 'vr_generate_state_ranking_premium_bowling_monthly', 'vr_generate_state_ranking_premium_bowling_monthly' );
function vr_generate_state_ranking_premium_bowling_monthly() {
	
	$form_id = FORM_ID_FOR_PREMIUM_BOWLING_LEADERBOARD_MONTHLY;
	$state_field = '23.4';
	$states = vr_get_participating_states( $form_id, $state_field );
	
	foreach ( $states as $state ) {
		
		$rank = 0;
	
		$search_criteria = array(
			'status'        => 'active',
			'field_filters' => array(
			'mode'  => 'all',
				array(
					'key' => $state_field, 'value' => $state
				)
			)
			
		);

		$sorting = array( 'key' => '13', 'direction' => 'DESC', 'is_numeric' => true );	

		$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

		$result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

		if ( !empty($result) ) { 
		
			foreach($result as $key => $value) {
				$rank++;
				GFAPI::update_entry_field( $value['id'], '28', $rank );	// Set state ranking
			
			}
		}
	}		
}


// To update the overall leaderboard with the rankings of players
add_action( 'vr_generate_ranking_premium_bowling_overall', 'vr_generate_ranking_premium_bowling_overall' );
function vr_generate_ranking_premium_bowling_overall() {
	
	$form_id = FORM_ID_FOR_PREMIUM_BOWLING_LEADERBOARD_OVERALL;
	$rank = 0;

	$search_criteria = array(
		'status'        => 'active'
	);

	$sorting = array( 'key' => '13', 'direction' => 'DESC', 'is_numeric' => true );	

	$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

    $result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

    if ( !empty($result) ) { 
	
		foreach($result as $key => $value) {
			$rank++;
			GFAPI::update_entry_field( $value['id'], '21', $value['20'] );	// Save old ranking
			GFAPI::update_entry_field( $value['id'], '20', $rank );	// Set new ranking
        }
    }	
}

// To update the overall leaderboard with the rankings of players from specific countries
add_action( 'vr_generate_country_ranking_premium_bowling_overall', 'vr_generate_country_ranking_premium_bowling_overall' );
function vr_generate_country_ranking_premium_bowling_overall() {
	
	$form_id = FORM_ID_FOR_PREMIUM_BOWLING_LEADERBOARD_OVERALL;
	$country_field = '23.6';
	$countries = vr_get_participating_countries( $form_id, $country_field );
	
	foreach ( $countries as $country ) {
		
		$rank = 0;
	
		$search_criteria = array(
			'status'        => 'active',
			'field_filters' => array(
			'mode'  => 'all',
				array(
					'key' => $country_field, 'value' => $country
				)
			)
			
		);

		$sorting = array( 'key' => '13', 'direction' => 'DESC', 'is_numeric' => true );	

		$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

		$result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

		if ( !empty($result) ) { 
		
			foreach($result as $key => $value) {
				$rank++;
				GFAPI::update_entry_field( $value['id'], '22', $rank );	// Set country ranking
			}
		}
	}		
}


// To update the overall leaderboard with the rankings of players from specific states
add_action( 'vr_generate_state_ranking_premium_bowling_overall', 'vr_generate_state_ranking_premium_bowling_overall' );
function vr_generate_state_ranking_premium_bowling_overall() {
	
	$form_id = FORM_ID_FOR_PREMIUM_BOWLING_LEADERBOARD_OVERALL;
	$state_field = '23.4';
	$states = vr_get_participating_states( $form_id, $state_field );
	
	foreach ( $states as $state ) {
		
		$rank = 0;
	
		$search_criteria = array(
			'status'        => 'active',
			'field_filters' => array(
			'mode'  => 'all',
				array(
					'key' => $state_field, 'value' => $state
				)
			)
			
		);

		$sorting = array( 'key' => '13', 'direction' => 'DESC', 'is_numeric' => true );	

		$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

		$result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

		if ( !empty($result) ) { 
		
			foreach($result as $key => $value) {
				$rank++;
				GFAPI::update_entry_field( $value['id'], '28', $rank );	// Set state ranking
			
			}
		}
	}		
}


// To render a text field to be read-only if the user is logged in. Used to pre-fill user's username in a field
add_filter( 'gform_pre_render_' . FORM_ID_FOR_PREMIUM_BOWLING_CHALLENGE_SCORECARD, 'vr_add_readonly_script' );
function vr_add_readonly_script( $form ) {
	
	$current_user = wp_get_current_user();
	
	//If user is not logged in...
    if ( empty( $current_user ) ) {
        nocache_headers(); 
        wp_redirect( wp_login_url() ); 
        exit();
    }
	
	$roles = ( array ) $current_user->roles;

    //Is user logged in and is user a normal user, not admin?
    if ( !empty( $current_user ) && in_array( 'subscriber', $roles ) ) {
		$username = $current_user->user_login;
		 ?>
		<script type="text/javascript">
			jQuery(document).on('gform_post_render', function(){
				/* apply only to a input with a class of gf_readonly */
				jQuery(".gf_readonly input").attr("readonly","readonly");
			});
		</script>
		<?php
    }
   
    return $form;
}


//add_filter( 'um_template_tags_patterns_hook', 'my_template_tags_patterns', 10, 1 );
/* function my_template_tags_patterns( $placeholders ) {
	// your code here
	$placeholders[] = '{user_id}';
	
		//DEBUG
	//error_log("placeholders: ");
	//error_log(print_r( $placeholders, true) );
	
	return $placeholders;
} */

	 
//add_filter( 'um_template_tags_replaces_hook', 'my_template_tags_replaces', 10, 1 );
/* function my_template_tags_replaces( $replace_placeholders ) {
	
	$requested_user = get_query_var( 'um_user' );
	
	//DEBUG
	error_log("requested_user: " . $requested_user);

	$user = get_user_by( 'login', $requested_user);
	$user_id = $user->ID;
	
	$replace_placeholders[] = '234';
	//$replace_placeholders[] = $user_id;
	
			//DEBUG
	//error_log("replace_placeholders: ");
	//error_log(print_r( $replace_placeholders, true) );

	return $replace_placeholders;
} */

//add_filter( 'um_profile_field_filter_hook__', 'my_template_tags_convert' );
/* function my_template_tags_convert( $value, $data ) {
	//DEBUG
	error_log("my_template_tags_convert: ");
	error_log( "value: " . $value );
	error_log( "data: " . $data );
	
	return $value;
} */

/* add_filter( 'um_convert_tags', 'my_template_tags_convert' );
function my_template_tags_convert( $value, $key ) {
	
		
	//DEBUG
	error_log("my_template_tags_convert: ");
	error_log( "value: " . $value );
	error_log( "key: " . $key );
	//error_log(print_r( $replace_placeholders, true) );
	
	if ( $key == 'user_id' ) {
	$value = '123455';
	}
	return $value;

} */


// Process the challenge score submission for Premium Bowling
add_action( 'gform_after_submission_' . FORM_ID_FOR_PREMIUM_BOWLING_CHALLENGE_SCORECARD, 'vr_process_challenge_score_premium_bowling', 10, 2 );
function vr_process_challenge_score_premium_bowling( $entry, $form ) {
	
	if ( ! is_wp_error( $entry ) && isset( $entry['1'] ) && ! empty( $entry['1'] ) ) {
	
		$player_1_game_1_score = rgar( $entry, '5', 0 );
		$player_2_game_1_score = rgar( $entry, '7', 0 );
		$player_1_game_2_score = rgar( $entry, '16', 0 );
		$player_2_game_2_score = rgar( $entry, '17', 0 );
		$player_1_game_3_score = rgar( $entry, '26', 0 );
		$player_2_game_3_score = rgar( $entry, '27', 0 );
		
		$player_1_total_score = $player_1_game_1_score + $player_1_game_2_score + $player_1_game_3_score;
		$player_2_total_score = $player_2_game_1_score + $player_2_game_2_score + $player_2_game_3_score;
		
		GFAPI::update_entry_field( $entry['id'], '36', $player_1_total_score );
		GFAPI::update_entry_field( $entry['id'], '37', $player_2_total_score );
		
		if ( $player_1_total_score > $player_2_total_score ) {
			GFAPI::update_entry_field( $entry['id'], '38', 'Win' );
			GFAPI::update_entry_field( $entry['id'], '39', 'Loss' );
		} else if ( $player_2_total_score > $player_1_total_score ) {
			GFAPI::update_entry_field( $entry['id'], '39', 'Win' );
			GFAPI::update_entry_field( $entry['id'], '38', 'Loss' );
		} else if ( $player_1_total_score = $player_2_total_score ) {
			GFAPI::update_entry_field( $entry['id'], '38', 'Draw' );
			GFAPI::update_entry_field( $entry['id'], '39', 'Draw' );
		}
	}
}

add_shortcode( 'vr_user_calendar', 'user_calendar' );	
function user_calendar( $atts ) {
	
	$form_id = FORM_ID_FOR_CALENDLY;
		
	$args = shortcode_atts( array(
		'username' => ''
	), $atts );
	
	$username = $args['username'];
	
	$search_criteria = array(
		'status'        => 'active',
		'field_filters' => array(
		'mode'  => 'all',
			array(
				'key' => '2', 'value' => $username
			)
		)
		
	);

	$result = GFAPI::get_entries( $form_id, $search_criteria, null, null, $total_count );

	if ( !empty($result) ) { 
		$calendar_url = rgar( $result[0], '1', '' );
		
		$arr = array(
				'a' => array(
					'href' => array(),
					'title' => array()
				),
				'br' => array(),
				'p' => array(),
				'strong' => array(),
			);
		$output = __( '<p>We use free online calendar schedulers like <a target="_blank" href="https://calendly.com"><strong>Calendly</strong></a> to set up appointments with players to meet online and play.</p><p>Click on the <strong>Request to Challenge</strong> button below and the player’s online calendar will pop up. This is where you can set up an appointment with the player. Please include your email address (the same email you use on our site) and a message when you request to challenge.</p>', 'rife-free-child' );
		$output = wp_kses( $output, $arr );
		$output .= '<!-- Calendly link widget begin -->
			<link href="https://assets.calendly.com/assets/external/widget.css" rel="stylesheet">
			<script src="https://assets.calendly.com/assets/external/widget.js" type="text/javascript" async></script>
			<a class="um-button" href="" onclick="Calendly.initPopupWidget({url: \'' . esc_attr( $calendar_url ) . '\'});return false;">' . esc_html__( 'Request to Challenge', 'rife-free-child' ) . '</a><!-- Calendly link widget end -->';
		//$output = '<a href="' . $calendar_url . '" target="_blank" class="btn btn-secondary" role="button" title="' . esc_html__( 'Challenge', 'rife-free-child' ) . '" alt="' . esc_html__( 'Challenge', 'rife-free-child' ) . '">' . esc_html__( 'Challenge', 'rife-free-child' ) . '</a>';
		
	} else {
		$output = "";
	}

	return $output;
}

add_filter( 'gamipress_activity_triggers', 'vr_submitted_first_score_in_premium_bowling_activity_triggers' );
function vr_submitted_first_score_in_premium_bowling_activity_triggers( $triggers ) {
    $triggers['VR Events'] = array(
        // Register the event my_prefix_custom_purchase_event
        'vr_submitted_first_score_in_premium_bowling_event' => __( 'Submitted first score in Premium Bowling', 'gamipress' ),
    );
    // GamiPress automatically will use the event label for auto-generate the requirement label
    // Some examples with "Purchase a product" label:
    // Step example: Purchase a product 2 times
    // Points award example: 10 points for purchase a product 5 times
    return $triggers;
}

function vr_submitted_first_score_in_premium_bowling_listener( $user_id ) {

	// Call to gamipress_trigger_event() when user first submitted a score
	gamipress_trigger_event( array(
		'event' => 'vr_submitted_first_score_in_premium_bowling_event', // Set our custom event
		'user_id' => $user_id, // User that will be awarded
		// Add any extra parameters you want below
	) );
}

// Clean up the text entered by user when signing up. Such as making sure first letter in a text is in upper cap.
add_action( 'um_registration_complete', 'vr_clean_up_registration_entry', 10, 2 );
function vr_clean_up_registration_entry( $user_id, $args ) {
	
	$city = ( isset( $args['submitted']['city'] ) ) ? strtolower( $args['submitted']['city'] ) : '';
	$state = ( isset( $args['submitted']['user_state'] ) ) ? strtolower( $args['submitted']['user_state'] ) : '';
	
	$city = ucwords( $city );
	$state = ucwords( $state );

	update_user_meta( $user_id, 'city', $city );
	update_user_meta( $user_id, 'user_state', $state );
}


/**MINI LEAGUE PREMIUM BOWLING******************************************************************************/

// To include all Gravity Forms and Gravity Views to run a mini league and performs other stuff
add_action('um_groups_after_front_insert', 'vr_process_mini_league_pb', 10, 2);
function vr_process_mini_league_pb( $formdata, $group_id ) {
	
	$game_prefix = get_post_meta( $group_id, '_um_groups_game', true );
	
	if ( $game_prefix == PREMIUM_BOWLING_PREFIX ) {
		
		$group_name = $formdata[ 'group_name' ];
		
		// Create the league forms and form views (for ranking)
		$join_form_id = GFAPI::duplicate_form( FORM_ID_FOR_JOIN_PREMIUM_BOWLING_MINI_LEAGUE_DUPLICATION );
		
		if ( !empty( $join_form_id ) ) {
			vr_change_form_title( $join_form_id, sprintf( esc_html__( '%s Mini League - %s - Join Form', 'rife-free-child' ), PREMIUM_BOWLING_TITLE, $group_name ) );
			
			$submit_score_form_id = GFAPI::duplicate_form( FORM_ID_FOR_SUBMIT_SCORE_FOR_PREMIUM_BOWLING_MINI_LEAGUE_DUPLICATION );
		}
		
		if ( !empty( $submit_score_form_id ) ) {
			vr_change_form_title( $submit_score_form_id, sprintf( esc_html__( '%s Mini League - %s - Submit Score Form', 'rife-free-child' ), PREMIUM_BOWLING_TITLE, $group_name ) );
			
			$edit_submit_score_post_id = vr_duplicate_post( POST_ID_FOR_PREMIUM_BOWLING_SUBMIT_SCORE_EDIT_PAGE_DUPLICATION, sprintf( esc_html__( '%s Mini League - %s - Edit Score', 'rife-free-child' ), PREMIUM_BOWLING_TITLE, $group_name ), 'publish' );
			
			$challenge_score_form_id = GFAPI::duplicate_form( FORM_ID_FOR_CHALLENGE_SCORE_FOR_PREMIUM_BOWLING_MINI_LEAGUE_DUPLICATION );
		}
		
		if ( !empty( $challenge_score_form_id ) ) {
			vr_change_form_title( $challenge_score_form_id, sprintf( esc_html__( '%s Mini League - %s - Challenge Score Form', 'rife-free-child' ), PREMIUM_BOWLING_TITLE, $group_name ) );
			
			$edit_challenge_score_post_id = vr_duplicate_post( POST_ID_FOR_PREMIUM_BOWLING_CHALLENGE_SCORE_EDIT_PAGE_DUPLICATION, sprintf( esc_html__( '%s Mini League - %s - Edit Challenge Score', 'rife-free-child' ), PREMIUM_BOWLING_TITLE, $group_name ), 'publish' );
			
			$leaderboard_form_id = GFAPI::duplicate_form( FORM_ID_FOR_PREMIUM_BOWLING_LEADERBOARD_MINI_LEAGUE_DUPLICATION );
		}
		
		if ( !empty( $leaderboard_form_id ) ) {
			vr_change_form_title( $leaderboard_form_id, sprintf( esc_html__( '%s Mini League - %s - Leaderboard Form', 'rife-free-child' ), PREMIUM_BOWLING_TITLE, $group_name ) );
			
			$leaderboard_post_id = vr_duplicate_post( POST_ID_FOR_PREMIUM_BOWLING_LEADERBOARD_MINI_LEAGUE_DUPLICATION, sprintf( esc_html__( '%s Mini League - %s - Leaderboard', 'rife-free-child' ), PREMIUM_BOWLING_TITLE, $group_name ), 'publish' );
			
			$challenge_leaderboard_form_id = GFAPI::duplicate_form( FORM_ID_FOR_PREMIUM_BOWLING_CHALLENGE_LEADERBOARD_MINI_LEAGUE_DUPLICATION );
		}
		
		if ( !empty( $challenge_leaderboard_form_id ) ) {
			vr_change_form_title( $challenge_leaderboard_form_id, sprintf( esc_html__( '%s Mini League - %s - Challenge Leaderboard Form', 'rife-free-child' ), PREMIUM_BOWLING_TITLE, $group_name ) );
			
			$challenge_leaderboard_post_id = vr_duplicate_post( POST_ID_FOR_PREMIUM_BOWLING_CHALLENGE_LEADERBOARD_MINI_LEAGUE_DUPLICATION, sprintf( esc_html__( '%s Mini League - %s - Challenge Leaderboard', 'rife-free-child' ), PREMIUM_BOWLING_TITLE, $group_name ), 'publish' );
		}
		
		// To copy the column field settings of source view to new view so that the table looks like the original view 
		vr_copy_view_post_meta( POST_ID_FOR_PREMIUM_BOWLING_LEADERBOARD_MINI_LEAGUE_DUPLICATION, $leaderboard_post_id, $leaderboard_form_id );
		vr_copy_view_post_meta( POST_ID_FOR_PREMIUM_BOWLING_CHALLENGE_LEADERBOARD_MINI_LEAGUE_DUPLICATION, $challenge_leaderboard_post_id, $challenge_leaderboard_form_id );
		vr_copy_view_post_meta( POST_ID_FOR_PREMIUM_BOWLING_SUBMIT_SCORE_EDIT_PAGE_DUPLICATION, $edit_submit_score_post_id, $submit_score_form_id );
		vr_copy_view_post_meta( POST_ID_FOR_PREMIUM_BOWLING_CHALLENGE_SCORE_EDIT_PAGE_DUPLICATION, $edit_challenge_score_post_id, $challenge_score_form_id );
		
		// The newly copied views should refer from the new form IDs
		update_post_meta( $leaderboard_post_id, '_gravityview_form_id', $leaderboard_form_id );	
		update_post_meta( $challenge_leaderboard_post_id, '_gravityview_form_id', $challenge_leaderboard_form_id );	
		update_post_meta( $edit_submit_score_post_id, '_gravityview_form_id', $submit_score_form_id );
		update_post_meta( $edit_challenge_score_post_id, '_gravityview_form_id', $challenge_score_form_id );	
		
		add_post_meta( $group_id, '_um_groups_join_form_id_' . PREMIUM_BOWLING_PREFIX, $join_form_id );
		add_post_meta( $group_id, '_um_groups_submit_score_form_id_' . PREMIUM_BOWLING_PREFIX, $submit_score_form_id );
		add_post_meta( $group_id, '_um_groups_challenge_score_form_id_' . PREMIUM_BOWLING_PREFIX, $challenge_score_form_id );
		add_post_meta( $group_id, '_um_groups_leaderboard_form_id_' . PREMIUM_BOWLING_PREFIX, $leaderboard_form_id );
		add_post_meta( $group_id, '_um_groups_challenge_leaderboard_form_id_' . PREMIUM_BOWLING_PREFIX, $challenge_leaderboard_form_id );
		add_post_meta( $group_id, '_um_groups_leaderboard_post_id_' . PREMIUM_BOWLING_PREFIX, $leaderboard_post_id );
		add_post_meta( $group_id, '_um_groups_challenge_leaderboard_post_id_' . PREMIUM_BOWLING_PREFIX, $challenge_leaderboard_post_id );
		add_post_meta( $group_id, '_um_groups_edit_submit_score_post_id_' . PREMIUM_BOWLING_PREFIX, $edit_submit_score_post_id );
		add_post_meta( $group_id, '_um_groups_edit_challenge_score_post_id_' . PREMIUM_BOWLING_PREFIX, $edit_challenge_score_post_id );
	}
}


// Process the score submitted for Premium Bowling mini league
add_action( 'gform_after_submission', 'vr_process_mini_league_score_pb', 10, 2 );
function vr_process_mini_league_score_pb( $entry, $form ) {
	
	$post_id = get_post_id_by_meta_key_and_value( '_um_groups_submit_score_form_id_' . PREMIUM_BOWLING_PREFIX, $form['id'] );	// Get league ID (group ID)
	
	if ( !empty( $post_id ) ) {
		$leaderboard_form_id = get_post_meta( $post_id, '_um_groups_leaderboard_form_id_' . PREMIUM_BOWLING_PREFIX, true );	// Get the leaderboard form ID
		
		$join_form_id = get_post_meta( $post_id, '_um_groups_join_form_id_' . PREMIUM_BOWLING_PREFIX, true );
		
		if ( ! is_wp_error( $entry ) ) {
		
			$player_name = rgar( $entry, '17' );
			$score = rgar( $entry, '16' );
			$strikes = rgar( $entry, '13' );
			$spares = rgar( $entry, '14' );
			$turkeys = rgar( $entry, '15' );
			
			$search_criteria = array(
				'status'        => 'active',
				'field_filters' => array(
				'mode'  => 'all',
					array(
						'key' => '1', 'value' => $player_name
					)
				)
			);

			$result = GFAPI::get_entries( $leaderboard_form_id, $search_criteria );

			if ( empty( $result ) ) {
				
				//Get player's user ID
				$user_id = get_form_field_value( $join_form_id, '9', '3', $player_name );
				
				//Get country of player
				//$user = get_user_by( 'login', $player_name);
				//$user_id = $user->ID;
				$country = get_user_meta( $user_id, 'country', true);
				$state = ( $country == esc_html__( 'United States', 'rife-free-child' ) ) ? get_user_meta( $user_id, 'us_state', true) : get_user_meta( $user_id, 'user_state', true);
				$city = get_user_meta( $user_id, 'city', true);
				$user = get_userdata( $user_id );
				$user_login = $user->user_login;
				
				// Add player into leaderboard
				$player_entry = array(
					"form_id" => $leaderboard_form_id,
					"24" => $user_id,
					"1" => $player_name,
					"30" => $user_login,
					"23.6" => $country,
					"23.4" => $state,
					"23.3" => $city,
					"8" => get_average_score( $form['id'], '13', '17', $player_name ),	// Avg strikes
					"9" => get_average_score( $form['id'], '14', '17', $player_name ),	// Avg spares
					"10" => get_average_score( $form['id'], '15', '17', $player_name ),	// Avg turkeys
					"13" => get_average_score( $form['id'], '16', '17', $player_name ),	// Avg score
					"6" => get_max_value( $form['id'], '16', '17', $player_name ),		// Highest score
					"14" => get_total_count( $form['id'], '17', $player_name ),			// Total scores submitted
					"18" => get_latest_submission( $form['id'], '17', $player_name ),	// Last submitted score
					"17" => get_high_scoring_percentage( $form['id'], '16', '17', $player_name, '', '', 200 ),	// Scores Above 200
					"25" => get_total_sum( $form['id'], '13', '17', $player_name ),	// Total strikes
					"26" => get_total_sum( $form['id'], '14', '17', $player_name ),	// Total spares
					"27" => get_total_sum( $form['id'], '15', '17', $player_name )	// Total turkeys
				);

				$entry_id = GFAPI::add_entry( $player_entry );
			
			} elseif ( !empty( $result ) ) {	
				
				$entry_id = $result[0]['id'];
				
				// Update existing player stats
				GFAPI::update_entry_field( $entry_id, '8', get_average_score( $form['id'], '13', '17', $player_name ) );
				GFAPI::update_entry_field( $entry_id, '9', get_average_score( $form['id'], '14', '17', $player_name ) );
				GFAPI::update_entry_field( $entry_id, '10', get_average_score( $form['id'], '15', '17', $player_name ) );
				GFAPI::update_entry_field( $entry_id, '13', get_average_score( $form['id'], '16', '17', $player_name ) );
				GFAPI::update_entry_field( $entry_id, '6', get_max_value( $form['id'], '16', '17', $player_name ) );
				GFAPI::update_entry_field( $entry_id, '14', get_total_count( $form['id'], '17', $player_name ) );
				GFAPI::update_entry_field( $entry_id, '18', get_latest_submission( $form['id'], '17', $player_name ) );
				GFAPI::update_entry_field( $entry_id, '17', get_high_scoring_percentage( $form['id'], '16', '17', $player_name, '', '', 200 ) );
				GFAPI::update_entry_field( $entry_id, '25', get_total_sum( $form['id'], '13', '17', $player_name ) );
				GFAPI::update_entry_field( $entry_id, '26', get_total_sum( $form['id'], '14', '17', $player_name ) );
				GFAPI::update_entry_field( $entry_id, '27', get_total_sum( $form['id'], '15', '17', $player_name ) );

			}
		}	
	}
}


// Reprocess the scoreline if league manager edited a score.
add_filter( 'gravityview-inline-edit/entry-updated', 'vr_reprocess_mini_league_score_pb', 10, 5 );
function vr_reprocess_mini_league_score_pb( $update_result, $entry = array(), $form_id = 0, $gf_field = null, $original_entry = array() ) { 

	$post_id = get_post_id_by_meta_key_and_value( '_um_groups_submit_score_form_id_' . PREMIUM_BOWLING_PREFIX, $form_id );	// Get league ID (group ID)
	
	if ( !empty( $post_id ) ) {
		$leaderboard_form_id = get_post_meta( $post_id, '_um_groups_leaderboard_form_id_' . PREMIUM_BOWLING_PREFIX, true );	// Get the leaderboard form ID
		
		$join_form_id = get_post_meta( $post_id, '_um_groups_join_form_id_' . PREMIUM_BOWLING_PREFIX, true );
		
		if ( ! is_wp_error( $entry ) ) {
		
			$player_name = rgar( $entry, '17' );
			$score = rgar( $entry, '16' );
			$strikes = rgar( $entry, '13' );
			$spares = rgar( $entry, '14' );
			$turkeys = rgar( $entry, '15' );
			
			$search_criteria = array(
				'status'        => 'active',
				'field_filters' => array(
				'mode'  => 'all',
					array(
						'key' => '1', 'value' => $player_name
					)
				)
			);

			$result = GFAPI::get_entries( $leaderboard_form_id, $search_criteria );
			$entry_id = $result[0]['id'];
			
			// Update existing player stats
			GFAPI::update_entry_field( $entry_id, '8', get_average_score( $form['id'], '13', '17', $player_name ) );
			GFAPI::update_entry_field( $entry_id, '9', get_average_score( $form['id'], '14', '17', $player_name ) );
			GFAPI::update_entry_field( $entry_id, '10', get_average_score( $form['id'], '15', '17', $player_name ) );
			GFAPI::update_entry_field( $entry_id, '13', get_average_score( $form['id'], '16', '17', $player_name ) );
			GFAPI::update_entry_field( $entry_id, '6', get_max_value( $form['id'], '16', '17', $player_name ) );
			GFAPI::update_entry_field( $entry_id, '14', get_total_count( $form['id'], '17', $player_name ) );
			GFAPI::update_entry_field( $entry_id, '18', get_latest_submission( $form['id'], '17', $player_name ) );
			GFAPI::update_entry_field( $entry_id, '17', get_high_scoring_percentage( $form['id'], '16', '17', $player_name, '', '', 200 ) );
			GFAPI::update_entry_field( $entry_id, '25', get_total_sum( $form['id'], '13', '17', $player_name ) );
			GFAPI::update_entry_field( $entry_id, '26', get_total_sum( $form['id'], '14', '17', $player_name ) );
			GFAPI::update_entry_field( $entry_id, '27', get_total_sum( $form['id'], '15', '17', $player_name ) );
		}	
	}
}


// Process the challenge score submission for Premium Bowling
add_action( 'gform_after_submission', 'vr_process_mini_league_challenge_score_pb', 10, 2 );
function vr_process_mini_league_challenge_score_pb( $entry, $form ) {
	
	$result = '';
	$player_1_win = 0; 
	$player_1_draw = 0;
	$player_1_loss = 0; 
	$player_1_points = 0;
	
	$player_2_win = 0; 
	$player_2_draw = 0;
	$player_2_loss = 0; 
	$player_2_points = 0;
	
	$post_id = get_post_id_by_meta_key_and_value( '_um_groups_challenge_score_form_id_' . PREMIUM_BOWLING_PREFIX, $form['id'] );	// Get league ID (group ID)
	
	if ( !empty( $post_id ) ) {
		
		$leaderboard_challenge_form_id = get_post_meta( $post_id, '_um_groups_challenge_leaderboard_form_id_' . PREMIUM_BOWLING_PREFIX, true );	// Get the leaderboard form ID
		
		$join_form_id = get_post_meta( $post_id, '_um_groups_join_form_id_' . PREMIUM_BOWLING_PREFIX, true );
	
		if ( ! is_wp_error( $entry ) ) {
			
			$player_1_name = rgar( $entry, '44' );
			$player_2_name = rgar( $entry, '45' );
			//$games_played = rgar( $entry, '3' );
			//$games_with_stats = rgar( $entry, '4' );

			$player_1_game_1_score = rgar( $entry, '5', 0 );
			// $player_1_game_1_strikes = rgar( $entry, '9', 0 );
			// $player_1_game_1_spares = rgar( $entry, '10', 0 );
			// $player_1_game_1_turkeys = rgar( $entry, '11', 0 );
			
			$player_2_game_1_score = rgar( $entry, '7', 0 );
			// $player_2_game_1_strikes = rgar( $entry, '12', 0 );
			// $player_2_game_1_spares = rgar( $entry, '13', 0 );
			// $player_2_game_1_turkeys = rgar( $entry, '14', 0 );
			
			$player_1_game_2_score = rgar( $entry, '16', 0 );
			// $player_1_game_2_strikes = rgar( $entry, '19', 0 );
			// $player_1_game_2_spares = rgar( $entry, '20', 0 );
			// $player_1_game_2_turkeys = rgar( $entry, '21', 0 );
			
			$player_2_game_2_score = rgar( $entry, '17', 0 );
			// $player_2_game_2_strikes = rgar( $entry, '22', 0 );
			// $player_2_game_2_spares = rgar( $entry, '23', 0 );
			// $player_2_game_2_turkeys = rgar( $entry, '24', 0 );
			
			$player_1_game_3_score = rgar( $entry, '26', 0 );
			// $player_1_game_3_strikes = rgar( $entry, '29', 0 );
			// $player_1_game_3_spares = rgar( $entry, '30', 0 );
			// $player_1_game_3_turkeys = rgar( $entry, '31', 0 );

			$player_2_game_3_score = rgar( $entry, '27', 0 );
			// $player_2_game_3_strikes = rgar( $entry, '32', 0 );
			// $player_2_game_3_spares = rgar( $entry, '33', 0 );
			// $player_2_game_3_turkeys = rgar( $entry, '34', 0 ); 
			
			$player_1_total_score = $player_1_game_1_score + $player_1_game_2_score + $player_1_game_3_score;
			$player_2_total_score = $player_2_game_1_score + $player_2_game_2_score + $player_2_game_3_score;
			
/* 			$player_1_total_strikes = $player_1_game_1_strikes + $player_1_game_2_strikes + $player_1_game_3_strikes;
			$player_2_total_strikes = $player_2_game_1_strikes + $player_2_game_2_strikes + $player_2_game_3_strikes;
			
			$player_1_total_spares = $player_1_game_1_spares + $player_1_game_2_spares + $player_1_game_3_spares;
			$player_2_total_spares = $player_2_game_1_spares + $player_2_game_2_spares + $player_2_game_3_spares;
			
			$player_1_total_turkeys = $player_1_game_1_turkeys + $player_1_game_2_turkeys + $player_1_game_3_turkeys;
			$player_2_total_turkeys = $player_2_game_1_turkeys + $player_2_game_2_turkeys + $player_2_game_3_turkeys; */
			
			// Who wins game 1?
			if ( $player_1_game_1_score > $player_2_game_1_score ) {
				$player_1_points += PREMIUM_BOWLING_POINTS_FOR_GAME_WIN;
				
			} else if ( $player_2_game_1_score > $player_1_game_1_score ) {
				$player_2_points += PREMIUM_BOWLING_POINTS_FOR_GAME_WIN;
			
			} else if ( $player_1_game_1_score = $player_2_game_1_score ) {
				$player_1_points += PREMIUM_BOWLING_POINTS_FOR_GAME_DRAW;
				$player_2_points += PREMIUM_BOWLING_POINTS_FOR_GAME_DRAW;
			}
			
			// Who wins game 2?
			if ( $player_1_game_2_score > $player_2_game_2_score ) {
				$player_1_points += PREMIUM_BOWLING_POINTS_FOR_GAME_WIN;
				
			} else if ( $player_2_game_2_score > $player_1_game_2_score ) {
				$player_2_points += PREMIUM_BOWLING_POINTS_FOR_GAME_WIN;
				
			} else if ( $player_1_game_2_score = $player_2_game_2_score ) {
				$player_1_points += PREMIUM_BOWLING_POINTS_FOR_GAME_DRAW;
				$player_2_points += PREMIUM_BOWLING_POINTS_FOR_GAME_DRAW;
			}
			
			// Who wins game 3?
			if ( $player_1_game_3_score > $player_2_game_3_score ) {
				$player_1_points += PREMIUM_BOWLING_POINTS_FOR_GAME_WIN;
				
			} else if ( $player_2_game_3_score > $player_1_game_3_score ) {
				$player_2_points += PREMIUM_BOWLING_POINTS_FOR_GAME_WIN;
				
			} else if ( $player_1_game_3_score = $player_2_game_3_score ) {
				$player_1_points += PREMIUM_BOWLING_POINTS_FOR_GAME_DRAW;
				$player_2_points += PREMIUM_BOWLING_POINTS_FOR_GAME_DRAW;
			}
			
			// Who wins the series?
			if ( $player_1_total_score > $player_2_total_score ) {
				$result = $player_1_name;	// Player 1 wins
				$player_1_win += 1; 
				$player_2_loss += 1; 
				$player_1_points += PREMIUM_BOWLING_POINTS_FOR_SERIES_WIN;
				
			} else if ( $player_2_total_score > $player_1_total_score ) {
				$result = $player_2_name;	// Player 2 wins
				$player_2_win += 1; 
				$player_1_loss += 1; 
				$player_2_points += PREMIUM_BOWLING_POINTS_FOR_SERIES_WIN;

			} else if ( $player_1_total_score = $player_2_total_score ) {
				$result = 'draw';
				$player_1_draw += 1; 
				$player_2_draw += 1; 
				$player_1_points += PREMIUM_BOWLING_POINTS_FOR_SERIES_DRAW;
				$player_2_points += PREMIUM_BOWLING_POINTS_FOR_SERIES_DRAW;
				
			}
			
			//$player_1_stats = array( $player_1_total_score, $player_1_points, $player_1_total_strikes, $player_1_total_spares, $player_1_total_turkeys, $player_1_win, $player_1_draw, $player_1_loss );
			
			//$player_2_stats = array( $player_2_total_score, $player_2_points, $player_2_total_strikes, $player_2_total_spares, $player_2_total_turkeys, $player_2_win, $player_2_draw, $player_2_loss );
			
			// Update the entry with result and player's pts
			GFAPI::update_entry_field( $entry['id'], '47', $result );
			GFAPI::update_entry_field( $entry['id'], '48', $player_1_points );
			GFAPI::update_entry_field( $entry['id'], '49', $player_2_points );
			
			$player_1_stats = vr_get_challenge_total_sum_pb( $form['id'], $player_1_name );
			$player_2_stats = vr_get_challenge_total_sum_pb( $form['id'], $player_2_name );
			
			vr_update_mini_league_challenge_leaderboard( $leaderboard_challenge_form_id, $join_form_id, $player_1_name, $player_1_stats );
			vr_update_mini_league_challenge_leaderboard( $leaderboard_challenge_form_id, $join_form_id, $player_2_name, $player_2_stats );
			
			vr_copy_challenge_score_to_submit_score_pb( $entry, $form );
		
		}
	}
}


// Reprocess the challenge results, scores and player stats if league manager edited a score.
add_filter( 'gravityview-inline-edit/entry-updated', 'vr_reprocess_mini_league_challenge_score_pb', 10, 5 );
function vr_reprocess_mini_league_challenge_score_pb( $update_result, $entry = array(), $form_id = 0, $gf_field = null, $original_entry = array() ) {
	
	$form = array( 'id' => $form_id );
	vr_process_mini_league_challenge_score_pb( $entry, $form );

}


// To add or update the recent challenge scores submitted for a particular player to the challenge leaderboard. Such as update the total/average score, strikes, spares etc.
function vr_update_mini_league_challenge_leaderboard( $leaderboard_challenge_form_id, $join_form_id, $player_name, $stats ) {
		
	$search_criteria = array(
		'status'        => 'active',
		'field_filters' => array(
		'mode'  => 'all',
			array(
				'key' => '1', 'value' => $player_name
			)
		)
	);

	$result = GFAPI::get_entries( $leaderboard_challenge_form_id, $search_criteria );

	if ( empty( $result ) ) {
		
		//Get player's user ID
		$user_id = get_form_field_value( $join_form_id, '9', '3', $player_name );
		
		$country = get_user_meta( $user_id, 'country', true);
		$state = ( $country == esc_html__( 'United States', 'rife-free-child' ) ) ? get_user_meta( $user_id, 'us_state', true) : get_user_meta( $user_id, 'user_state', true);
		$city = get_user_meta( $user_id, 'city', true);
		$user = get_userdata( $user_id );
		$user_login = $user->user_login;
		
		// Add player into leaderboard
		$entry = array(
			"form_id" => $leaderboard_challenge_form_id,
			"24" => $user_id,
			"1" => $player_name,
			"41" => $user_login,
			"23.6" => $country,
			"23.4" => $state,
			"23.3" => $city,
			"36" => $stats['total_points'],	// Total points
			"35" => $stats['total_score'],	// Total score
			"25" => $stats['total_strikes'],	// Total strikes
			"26" => $stats['total_spares'],	// Total spares
			"27" => $stats['total_turkeys'],	// Total turkeys
			"37" => $stats['total_wins'],	// Total wins
			"38" => $stats['total_draws'],	// Total draws
			"39" => $stats['total_losses'],	// Total losses
			"40" => $stats['total_matches']	// Total matches played
		);
/* 		$entry = array(
			"form_id" => $leaderboard_challenge_form_id,
			"24" => $user_id,
			"1" => $player_name,
			"23.6" => $country,
			"23.4" => $state,
			"23.3" => $city,
			"36" => $stats[0],	// Total points
			"35" => $stats[1],	// Total score
			"25" => $stats[2],	// Total strikes
			"26" => $stats[3],	// Total spares
			"27" => $stats[4],	// Total turkeys
			"37" => $stats[5],	// Total wins
			"38" => $stats[6],	// Total draws
			"39" => $stats[7],	// Total loss
			"40" => $stats[8]	// Total matches played
		); */

		$entry_id = GFAPI::add_entry( $entry );
	
	} elseif ( !empty( $result ) ) {	
		
		$entry_id = $result[0]['id'];
		
		// Update existing player stats
		GFAPI::update_entry_field( $entry_id, '36', $stats['total_points'] );
		GFAPI::update_entry_field( $entry_id, '35', $stats['total_score'] );
		GFAPI::update_entry_field( $entry_id, '25', $stats['total_strikes'] );
		GFAPI::update_entry_field( $entry_id, '26', $stats['total_spares'] );
		GFAPI::update_entry_field( $entry_id, '27', $stats['total_turkeys'] );
		GFAPI::update_entry_field( $entry_id, '37', $stats['total_wins'] );
		GFAPI::update_entry_field( $entry_id, '38', $stats['total_draws'] );
		GFAPI::update_entry_field( $entry_id, '39', $stats['total_losses'] );
		GFAPI::update_entry_field( $entry_id, '40', $stats['total_matches'] );
		// GFAPI::update_entry_field( $entry_id, '35', $result[0]['35'] + $stats[0] );
		// GFAPI::update_entry_field( $entry_id, '36', $result[0]['36'] + $stats[1] );
		// GFAPI::update_entry_field( $entry_id, '25', $result[0]['25'] + $stats[2] );
		// GFAPI::update_entry_field( $entry_id, '26', $result[0]['26'] + $stats[3] );
		// GFAPI::update_entry_field( $entry_id, '27', $result[0]['27'] + $stats[4] );
		// GFAPI::update_entry_field( $entry_id, '37', $result[0]['37'] + $stats[5] );
		// GFAPI::update_entry_field( $entry_id, '38', $result[0]['38'] + $stats[6] );
		// GFAPI::update_entry_field( $entry_id, '39', $result[0]['39'] + $stats[7] );
		// GFAPI::update_entry_field( $entry_id, '40', $result[0]['40']++ );
	}
}


// Copy the challenge scores to submit score form so that player stats are included in the individual leaderboard. It deletes any existing score entries for a challenge (if any) and reinserts the new ones.
function vr_copy_challenge_score_to_submit_score_pb( $entry, $form ) {
	
	$post_id = get_post_id_by_meta_key_and_value( '_um_groups_challenge_score_form_id_' . PREMIUM_BOWLING_PREFIX, $form['id'] );	// Get league ID (group ID)
	
	if ( !empty( $post_id ) ) {
		
		$submit_score_form_id = get_post_meta( $post_id, '_um_groups_submit_score_form_id_' . PREMIUM_BOWLING_PREFIX, true );	// Get the submit score form ID
		
		$submit_score_form = GFAPI::get_form( $submit_score_form_id );
		
		if ( ! is_wp_error( $entry ) ) {
			
			$challenge_entry_id = rgar( $entry, 'id' );
			
			if ( !empty( $challenge_entry_id ) ) {	
				
				// Find entries in submit score form that are for the challenge match
				$search_criteria = array(
					'status'        => 'active',
					'field_filters' => array(
					'mode'  => 'all',
						array(
							'key' => '18', 'value' => $challenge_entry_id
						)
					)
				);

				$entry_ids = GFAPI::get_entry_ids( $submit_score_form_id, $search_criteria );
				
				// If scores were previously submitted for this challenge match. Meaning this is a score update not a new entry
				if ( !empty ($entry_ids ) ) {
					foreach( $entry_ids as $entry_id) {
						GFAPI::delete_entry( $entry_id );	// Delete all previous scores for that challenge match
					}
				}
			}				
		
			if ( rgar( $entry, '3' ) == 'One Game' ) {
			
				// Create player 1 game entry object
				$player_1_game_1_entry = array(
					"form_id" => $submit_score_form_id,
					"17" => rgar( $entry, '44' ),	// Player name
					"16" => rgar( $entry, '5' ),	// Game score
					"13" => rgar( $entry, '9' ),	// Total strikes
					"14" => rgar( $entry, '10' ),	// Total spares
					"15" => rgar( $entry, '11' ),	// Total turkey
					"18" => $challenge_entry_id		// Challenge match entry ID
				);

				$result = GFAPI::add_entry( $player_1_game_1_entry );
				
				vr_process_mini_league_score_pb( $player_1_game_1_entry, $submit_score_form );
				
				if ( ! is_wp_error( $result ) ) {
					
					// Create player 2 game entry object
					$player_2_game_1_entry = array(
						"form_id" => $submit_score_form_id,
						"17" => rgar( $entry, '45' ),	// Player name
						"16" => rgar( $entry, '7' ),	// Game score
						"13" => rgar( $entry, '12' ),	// Total strikes
						"14" => rgar( $entry, '13' ),	// Total spares
						"15" => rgar( $entry, '14' ),	// Total turkey
						"18" => $challenge_entry_id		// Challenge match entry ID
					);

					$result = GFAPI::add_entry( $player_2_game_1_entry );
					
					vr_process_mini_league_score_pb( $player_2_game_1_entry, $submit_score_form );
				}

			} else if ( rgar( $entry, '3' ) == 'Three Games' ) {

				// Create player 1 game entry object
				$player_1_game_1_entry = array(
					"form_id" => $submit_score_form_id,
					"17" => rgar( $entry, '44' ),	// Player name
					"16" => rgar( $entry, '5' ),	// Game score
					"13" => rgar( $entry, '9' ),	// Total strikes
					"14" => rgar( $entry, '10' ),	// Total spares
					"15" => rgar( $entry, '11' ),	// Total turkey
					"18" => $challenge_entry_id		// Challenge match entry ID
				);

				$result = GFAPI::add_entry( $player_1_game_1_entry );
				
				vr_process_mini_league_score_pb( $player_1_game_1_entry, $submit_score_form );
				
				if ( ! is_wp_error( $result ) ) {
				
					// Create player 2 game entry object
					$player_2_game_1_entry = array(
						"form_id" => $submit_score_form_id,
						"17" => rgar( $entry, '45' ),	// Player name
						"16" => rgar( $entry, '7' ),	// Game score
						"13" => rgar( $entry, '12' ),	// Total strikes
						"14" => rgar( $entry, '13' ),	// Total spares
						"15" => rgar( $entry, '14' ),	// Total turkey
						"18" => $challenge_entry_id		// Challenge match entry ID
					);

					$result = GFAPI::add_entry( $player_2_game_1_entry );
					
					vr_process_mini_league_score_pb( $player_2_game_1_entry, $submit_score_form );
				
				}
				
				if ( ! is_wp_error( $result ) ) {
				
					$player_1_game_2_entry = array(
						"form_id" => $submit_score_form_id,
						"17" => rgar( $entry, '44' ),	// Player name
						"16" => rgar( $entry, '16' ),	// Game score
						"13" => rgar( $entry, '19' ),	// Total strikes
						"14" => rgar( $entry, '20' ),	// Total spares
						"15" => rgar( $entry, '21' ),	// Total turkey
						"18" => $challenge_entry_id		// Challenge match entry ID
					);

					$result = GFAPI::add_entry( $player_1_game_2_entry );
					
					vr_process_mini_league_score_pb( $player_1_game_2_entry, $submit_score_form );
				
				}
				
				if ( ! is_wp_error( $result ) ) {
				
					$player_2_game_2_entry = array(
						"form_id" => $submit_score_form_id,
						"17" => rgar( $entry, '45' ),	// Player name
						"16" => rgar( $entry, '17' ),	// Game score
						"13" => rgar( $entry, '22' ),	// Total strikes
						"14" => rgar( $entry, '23' ),	// Total spares
						"15" => rgar( $entry, '24' ),	// Total turkey
						"18" => $challenge_entry_id		// Challenge match entry ID
					);

					$result = GFAPI::add_entry( $player_2_game_2_entry );
					
					vr_process_mini_league_score_pb( $player_2_game_2_entry, $submit_score_form );
				
				}
				
				if ( ! is_wp_error( $result ) ) {
					
					$player_1_game_3_entry = array(
						"form_id" => $submit_score_form_id,
						"17" => rgar( $entry, '44' ),	// Player name
						"16" => rgar( $entry, '26' ),	// Game score
						"13" => rgar( $entry, '29' ),	// Total strikes
						"14" => rgar( $entry, '30' ),	// Total spares
						"15" => rgar( $entry, '31' ),	// Total turkey
						"18" => $challenge_entry_id		// Challenge match entry ID
					);

					$result = GFAPI::add_entry( $player_1_game_3_entry );
					
					vr_process_mini_league_score_pb( $player_1_game_3_entry, $submit_score_form );
				
				}
				
				if ( ! is_wp_error( $result ) ) {
				
					$player_2_game_3_entry = array(
						"form_id" => $submit_score_form_id,
						"17" => rgar( $entry, '45' ),	// Player name
						"16" => rgar( $entry, '27' ),	// Game score
						"13" => rgar( $entry, '32' ),	// Total strikes
						"14" => rgar( $entry, '33' ),	// Total spares
						"15" => rgar( $entry, '34' ),	// Total turkey
						"18" => $challenge_entry_id		// Challenge match entry ID
					);

					$result = GFAPI::add_entry( $player_2_game_3_entry );
					
					vr_process_mini_league_score_pb( $player_2_game_3_entry, $submit_score_form );
				}	
			}
		}
	}
}


// To update all Premium Bowling mini leagues challenge/match leaderboards with rankings of players
add_action( 'vr_generate_mini_league_rankings_pb', 'vr_generate_mini_league_rankings_pb' );
function vr_generate_mini_league_rankings_pb() {
	
	$form_ids = get_meta_values( '_um_groups_challenge_leaderboard_form_id_' . PREMIUM_BOWLING_PREFIX, 'um_groups', 'publish' );
	
	foreach ( $form_ids as $form_id ) {
	
		$rank = 0;

		$search_criteria = array(
			'status'        => 'active'
		);

		$sorting = array( 'key' => '36', 'direction' => 'DESC', 'is_numeric' => true );		// Rank players based on total points

		$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

		$result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

		if ( !empty($result) ) { 
		
			foreach($result as $key => $value) {
				$rank++;
				GFAPI::update_entry_field( $value['id'], '21', $value['20'] );	// Save old ranking
				GFAPI::update_entry_field( $value['id'], '20', $rank );	// Set new ranking
			}
		}
	}
}


// To update all Premium Bowling mini leagues individual leaderboards with rankings of players
add_action( 'vr_generate_mini_league_players_rankings_pb', 'vr_generate_mini_league_players_rankings_pb' );
function vr_generate_mini_league_players_rankings_pb() {
	
	$form_ids = get_meta_values( '_um_groups_leaderboard_form_id_' . PREMIUM_BOWLING_PREFIX, 'um_groups', 'publish' );
	
	foreach ( $form_ids as $form_id ) {
	
		$rank = 0;

		$search_criteria = array(
			'status'        => 'active'
		);

		$sorting = array( 'key' => '13', 'direction' => 'DESC', 'is_numeric' => true );	

		$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

		$result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

		if ( !empty($result) ) { 
		
			foreach($result as $key => $value) {
				$rank++;
				GFAPI::update_entry_field( $value['id'], '21', $value['20'] );	// Save old ranking
				GFAPI::update_entry_field( $value['id'], '20', $rank );	// Set new ranking
			}
		}
	}
}


function vr_get_challenge_total_sum_pb( $form_id, $player_name ) {
	
	$total_points = 0;
	$total_score = 0;
	$total_strikes = 0;
	$total_spares = 0;
	$total_turkeys = 0;
	$total_wins = 0;
	$total_draws = 0;
	$total_losses = 0;
	$total_matches = 0;
	
	// Search for entries where player name is player 1 in the score form
	$search_criteria = array(
		'status'        => 'active',
		'field_filters' => array(
		'mode'  => 'all',
			array(
				'key' => '44', 'value' => $player_name
			)
		)
	);
	
	$entries = GFAPI::get_entries( $form_id, $search_criteria, null, array(
		'offset'    => 0,
		'page_size' => 9999999999,
	) );
	
	if ( !empty( $entries ) ) {
		foreach ( $entries as $entry ) {
			
			$total_points +=  ( is_numeric( $entry['48'] ) ) ? $entry['48'] : 0;
			$total_score += ( is_numeric( $entry['5'] ) ) ? $entry['5'] : 0;
			$total_score += ( is_numeric( $entry['16'] ) ) ? $entry['16'] : 0;
			$total_score += ( is_numeric( $entry['26'] ) ) ? $entry['26'] : 0;
			$total_strikes += ( is_numeric( $entry['9'] ) ) ? $entry['9'] : 0;
			$total_strikes += ( is_numeric( $entry['19'] ) ) ? $entry['19'] : 0;
			$total_strikes += ( is_numeric( $entry['29'] ) ) ? $entry['29'] : 0;
			$total_spares += ( is_numeric( $entry['10'] ) ) ? $entry['10'] : 0;
			$total_spares += ( is_numeric( $entry['20'] ) ) ? $entry['20'] : 0;
			$total_spares += ( is_numeric( $entry['30'] ) ) ? $entry['30'] : 0;
			$total_turkeys += ( is_numeric( $entry['11'] ) ) ? $entry['11'] : 0;
			$total_turkeys += ( is_numeric( $entry['21'] ) ) ? $entry['21'] : 0;
			$total_turkeys += ( is_numeric( $entry['31'] ) ) ? $entry['31'] : 0;
			$total_wins += ( $entry['47'] == $player_name ) ? 1 : 0;	// In the result field, the winner's player name shall appear. 
			$total_draws += ( $entry['47'] == 'draw' ) ? 1 : 0;			// In the result field, "draw" is written if the match was a draw
			$total_losses += ( $entry['47'] != $player_name ) ? 1 : 0;	
		}
		$total_matches += count( $entries );
	}
	
	
	// Search for entries where player name is player 2 in the score form
	$search_criteria = array(
		'status'        => 'active',
		'field_filters' => array(
		'mode'  => 'all',
			array(
				'key' => '45', 'value' => $player_name
			)
		)
	);
	
	$entries = GFAPI::get_entries( $form_id, $search_criteria, null, array(
		'offset'    => 0,
		'page_size' => 9999999999,
	) );
	
	if ( !empty( $entries ) ) {
		foreach ( $entries as $entry ) {
			
			$total_points +=  ( is_numeric( $entry['49'] ) ) ? $entry['49'] : 0;
			$total_score += ( is_numeric( $entry['7'] ) ) ? $entry['7'] : 0;
			$total_score += ( is_numeric( $entry['17'] ) ) ? $entry['17'] : 0;
			$total_score += ( is_numeric( $entry['27'] ) ) ? $entry['27'] : 0;
			$total_strikes += ( is_numeric( $entry['12'] ) ) ? $entry['12'] : 0;
			$total_strikes += ( is_numeric( $entry['22'] ) ) ? $entry['22'] : 0;
			$total_strikes += ( is_numeric( $entry['32'] ) ) ? $entry['32'] : 0;
			$total_spares += ( is_numeric( $entry['13'] ) ) ? $entry['13'] : 0;
			$total_spares += ( is_numeric( $entry['23'] ) ) ? $entry['23'] : 0;
			$total_spares += ( is_numeric( $entry['33'] ) ) ? $entry['33'] : 0;
			$total_turkeys += ( is_numeric( $entry['14'] ) ) ? $entry['14'] : 0;
			$total_turkeys += ( is_numeric( $entry['24'] ) ) ? $entry['24'] : 0;
			$total_turkeys += ( is_numeric( $entry['34'] ) ) ? $entry['34'] : 0;
			$total_wins += ( $entry['47'] == $player_name ) ? 1 : 0;	// In the result field, the winner's player name shall appear. 
			$total_draws += ( $entry['47'] == 'draw' ) ? 1 : 0;			// In the result field, "draw" is written if the match was a draw
			$total_losses += ( $entry['47'] != $player_name ) ? 1 : 0;	
		}
		$total_matches += count( $entries );
	}
	
	$player_stats = array( 'total_points' => $total_points, 'total_score' => $total_score, 'total_strikes' => $total_strikes, 'total_spares' => $total_spares, 'total_turkeys' => $total_turkeys, 'total_wins' => $total_wins, 'total_draws' => $total_draws, 'total_losses' => $total_losses, 'total_matches' => $total_matches );
	
	return $player_stats;
}


// Delete players data from all forms (join form, submit form, leaderboard form, challenge leaderboard form) except challenge score form because if the entry is deleted, the opponent's score will also get deleted.
add_action('vr_delete_mini_league_player', 'vr_delete_mini_league_player_pb', 10, 2 );
function vr_delete_mini_league_player_pb( $group_id, $user_id ) {
	
	$join_form_id = get_post_meta( $group_id, '_um_groups_join_form_id_' . PREMIUM_BOWLING_PREFIX, true );
	
	if ( !empty( $join_form_id ) ) {
		
		$submit_score_form_id = get_post_meta( $group_id, '_um_groups_submit_score_form_id_' . PREMIUM_BOWLING_PREFIX, true );
		$leaderboard_form_id = get_post_meta( $group_id, '_um_groups_leaderboard_form_id_' . PREMIUM_BOWLING_PREFIX, true );
		$challenge_leaderboard_form_id = get_post_meta( $group_id, '_um_groups_challenge_leaderboard_form_id_' . PREMIUM_BOWLING_PREFIX, true );
	
		// Get the entry of player's registration in the game in the join form.
		$join_form_search_criteria = array(
			'status'        => 'active',
			'field_filters' => array( //which fields to search
			'mode'  => 'all',
				array(
					'key' => '9', 'operator' => 'is', 'value' => $user_id
				)
			)
		);
		
		$join_form_result = GFAPI::get_entries( $join_form_id, $join_form_search_criteria );
		
		// Delete the entry containing player's info
		if ( !empty( $join_form_result ) ) {
			$join_form_entry_id = $join_form_result[0]['id'];
			$player_name = $join_form_result[0]['3'];
			GFAPI::delete_entry( $join_form_entry_id );
		}
		
		// Get all player's score entries in score form.
		$submit_score_form_search_criteria = array(
			'status'        => 'active',
			'field_filters' => array( //which fields to search
			'mode'  => 'all',
				array(
					'key' => '17', 'operator' => 'is', 'value' => $player_name
				)
			)
		);
		
		$submit_score_form_result = GFAPI::get_entries( $submit_score_form_id, $submit_score_form_search_criteria );
		
		// Delete each score entry
		if ( !empty( $submit_score_form_result ) ) {
			
			foreach($submit_score_form_result as $key => $value) {            
				$submit_score_form_entry_id = $value['id'];
				GFAPI::delete_entry( $submit_score_form_entry_id );
			}
			//$submit_score_form_entry_id = $submit_score_form_result[0]['id'];
			//GFAPI::delete_entry( $submit_score_form_entry_id );
		}
		
		// Get the entry of player's ranking in the leaderboard form.
		$leaderboard_form_search_criteria = array(
			'status'        => 'active',
			'field_filters' => array( //which fields to search
			'mode'  => 'all',
				array(
					'key' => '24', 'operator' => 'is', 'value' => $user_id
				)
			)
		);
		
		$leaderboard_form_result = GFAPI::get_entries( $leaderboard_form_id, $leaderboard_form_search_criteria );
		
		// Delete the entry containing player's ranking
		if ( !empty( $leaderboard_form_result ) ) {
			$leaderboard_form_entry_id = $leaderboard_form_result[0]['id'];
			GFAPI::delete_entry( $leaderboard_form_entry_id );
		}
		
		
		// Get the entry of player's ranking in the challenge leaderboard form.
		$challenge_leaderboard_form_search_criteria = array(
			'status'        => 'active',
			'field_filters' => array( //which fields to search
			'mode'  => 'all',
				array(
					'key' => '24', 'operator' => 'is', 'value' => $user_id
				)
			)
		);
		
		$challenge_leaderboard_form_result = GFAPI::get_entries( $challenge_leaderboard_form_id, $challenge_leaderboard_form_search_criteria );
		
		// Delete the entry containing player's ranking
		if ( !empty( $challenge_leaderboard_form_result ) ) {
			$challenge_leaderboard_form_entry_id = $challenge_leaderboard_form_result[0]['id'];
			GFAPI::delete_entry( $challenge_leaderboard_form_entry_id );
		}
	}	
}


// To delete all the forms/views for a league if the league is being deleted by league admin
add_action( 'vr_delete_mini_league', 'vr_delete_mini_league_pb', 10, 1 );
function vr_delete_mini_league_pb( $group_id ) {
		
	$join_form_id = get_post_meta( $group_id, '_um_groups_join_form_id_' . PREMIUM_BOWLING_PREFIX, true );
	
	if ( !empty( $join_form_id ) ) {
		
		$submit_score_form_id = get_post_meta( $group_id, '_um_groups_submit_score_form_id_' . PREMIUM_BOWLING_PREFIX, true );
		$challenge_score_form_id = get_post_meta( $group_id, '_um_groups_challenge_score_form_id_' . PREMIUM_BOWLING_PREFIX, true );
		$leaderboard_form_id = get_post_meta( $group_id, '_um_groups_leaderboard_form_id_' . PREMIUM_BOWLING_PREFIX, true );
		$challenge_leaderboard_form_id = get_post_meta( $group_id, '_um_groups_challenge_leaderboard_form_id_' . PREMIUM_BOWLING_PREFIX, true );
		$leaderboard_post_id = get_post_meta( $group_id, '_um_groups_leaderboard_post_id_' . PREMIUM_BOWLING_PREFIX, true );
		$challenge_leaderboard_post_id = get_post_meta( $group_id, '_um_groups_challenge_leaderboard_post_id_' . PREMIUM_BOWLING_PREFIX, true );
		$edit_submit_score_post_id = get_post_meta( $group_id, '_um_groups_edit_submit_score_post_id_' . PREMIUM_BOWLING_PREFIX, true );
		$edit_challenge_score_post_id = get_post_meta( $group_id, '_um_groups_edit_challenge_score_post_id_' . PREMIUM_BOWLING_PREFIX, true );
		
		if ( GFAPI::delete_form( $join_form_id) ) {
			if ( GFAPI::delete_form( $submit_score_form_id ) ) {
				if ( GFAPI::delete_form( $challenge_score_form_id ) ) {	
					if ( GFAPI::delete_form( $leaderboard_form_id ) ) {
						if ( GFAPI::delete_form( $challenge_leaderboard_form_id ) ) {
							wp_delete_post( $leaderboard_post_id, false );
							wp_delete_post( $challenge_leaderboard_post_id, false );
							wp_delete_post( $edit_submit_score_post_id, false );
							wp_delete_post( $edit_challenge_score_post_id, false );
						}
					}
				}
			}
		}
	}
}


// Copy gravity view post meta. This is used when we duplicate gravity view when generating a mini league
function vr_copy_view_post_meta( $src_post_id, $dest_post_id, $dest_form_id ) {
	
	$dest_form_id = strval( $dest_form_id );	// Change int to string since Gravity View originally stores the form ID as string.
	
	//$src_gravityview_template_settings = get_post_meta( $src_post_id, '_gravityview_template_settings', true );
	$src_gravityview_directory_fields = get_post_meta( $src_post_id, '_gravityview_directory_fields', true );
	$src_gravityview_directory_widgets = get_post_meta( $src_post_id, '_gravityview_directory_widgets', true );
	//$src_gravityview_filters = get_post_meta( $src_post_id, '_gravityview_filters', true );
	
	// foreach ( $src_gravityview_template_settings as $key1 => &$value1 ) {
        // foreach ( $value1 as $key2 => &$value2 ) {
            // $value2['form_id'] = $dest_form_id;
        // }           
	// }
	
	foreach ( $src_gravityview_directory_fields as $key1 => &$value1 ) {
        foreach ( $value1 as $key2 => &$value2 ) {
            $value2['form_id'] = $dest_form_id;
        }           
	}
	
	foreach ( $src_gravityview_directory_widgets as $key1 => &$value1 ) {
        foreach ( $value1 as $key2 => &$value2 ) {
            $value2['form_id'] = $dest_form_id;
        }           
	}
	
	//update_post_meta( $dest_post_id, '_gravityview_template_settings', $src_gravityview_template_settings );
	update_post_meta( $dest_post_id, '_gravityview_directory_fields', $src_gravityview_directory_fields );
	update_post_meta( $dest_post_id, '_gravityview_directory_widgets', $src_gravityview_directory_widgets );
	//update_post_meta( $dest_post_id, '_gravityview_filters', $src_gravityview_filters );
}


// Change the title of a form, especially when we just duplicated one
function vr_change_form_title( $form_id, $title ) {
	
	$form = GFAPI::get_form( $form_id );

	foreach ( $form as $key => $value ) {
		if ( $key == 'title' ) {
			$form['title'] = $title;
			break;
		}
	} 
	
	return GFAPI::update_form( $form, $form_id );	// Return true if success, WP_Error if failed.
}


// To get a value from an entry submitted to a form by using a different field ID. Useful when trying to retrieve user ID if we know the player name store in a different field ID
function get_form_field_value( $form_id, $field_id, $field_id_to_search, $field_value_to_search ) {
	
	$search_criteria = array(
		'status'        => 'active',
		'field_filters' => array( //which fields to search
		'mode'  => 'all',
			array(
				'key' => $field_id_to_search, 'operator' => 'is', 'value' => $field_value_to_search
			)
		)
	);
	
	$result = GFAPI::get_entries( $form_id, $search_criteria );
	
	if ( !empty( $result ) ) {
		$form_field_value = $result[0][$field_id];
		
		return $form_field_value;
		
	} else {
		return false;
	}
}

// To get meta values with specific meta key, post type and status
// Src: https://wordpress.stackexchange.com/questions/9394/getting-all-values-for-a-custom-field-key-cross-post
function get_meta_values( $meta_key = '', $post_type = 'post', $post_status = 'publish' ) {
    
    global $wpdb;
    
    if( empty( $meta_key ) )
        return;
    
    $meta_values = $wpdb->get_col( $wpdb->prepare( "
        SELECT pm.meta_value FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = %s 
        AND p.post_type = %s 
        AND p.post_status = %s 
    ", $meta_key, $post_type, $post_status ) );
    
    return $meta_values;
}


/*
 * Function duplicates a post/page or custom post such as GravityView to be used in mini leagues.
 * Src: https://wordpress.org/plugins/duplicate-page/
 */
function vr_duplicate_post( $post_id, $post_title, $post_status = 'publish' ) {
	
	global $wpdb;
            
	/*
	* and all the original post data then
	*/
	$post = get_post($post_id);
	
	/*
	* if you don't want current user to be the new post author,
	* then change next couple of lines to this: $new_post_author = $post->post_author;
	*/
	$current_user = wp_get_current_user();
	$new_post_author = $current_user->ID;
	
	/*
	* if post data exists, create the post duplicate
	*/
	if (isset($post) && $post != null) {
		/*
		* new post data array
		*/
		$args = array(
			 'comment_status' => $post->comment_status,
			 'ping_status' => $post->ping_status,
			 'post_author' => $new_post_author,
			 'post_content' => $post->post_content,
			 'post_excerpt' => $post->post_excerpt,
			 'post_parent' => $post->post_parent,
			 'post_password' => $post->post_password,
			 'post_status' => $post_status,
			 'post_title' => $post_title,
			 'post_type' => $post->post_type,
			 'to_ping' => $post->to_ping,
			 'menu_order' => $post->menu_order,
		 );
		 
		/*
		* insert the post by wp_insert_post() function
		*/
		$new_post_id = wp_insert_post($args);
		if(is_wp_error($new_post_id)){
			wp_die(__($new_post_id->get_error_message(),'rife-free-child'));
		}
	   
		/*
		* get all current post terms ad set them to the new post draft
		*/
		$taxonomies = array_map('sanitize_text_field',get_object_taxonomies($post->post_type));
		if (!empty($taxonomies) && is_array($taxonomies)):
		 foreach ($taxonomies as $taxonomy) {
			 $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
			 wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
		 }
		endif;
		
		/*
		* duplicate all post meta
		*/
		$post_meta_keys = get_post_custom_keys( $post_id );
		if(!empty($post_meta_keys)){
			foreach ( $post_meta_keys as $meta_key ) {
				$meta_values = get_post_custom_values( $meta_key, $post_id );
				foreach ( $meta_values as $meta_value ) {
					$meta_value = maybe_unserialize( $meta_value );
					update_post_meta( $new_post_id, $meta_key, wp_slash( $meta_value ) );
				}
			}
		}
	} else {
		wp_die( __( 'Error! Post creation failed. Could not find original post. ','rife-free-child') . $post_id );
	}
	
	return $new_post_id;
}


// To check if player name is taken in a Premium Bowling mini league when trying to join league
add_filter( 'gform_field_validation', 'is_player_name_taken_in_mini_league_pb', 10, 4 );
function is_player_name_taken_in_mini_league_pb( $result, $value, $form, $field ) {
	
	$post_id = get_post_id_by_meta_key_and_value( '_um_groups_join_form_id_' . PREMIUM_BOWLING_PREFIX, $form['id'] );	// Get league ID (group ID)
	
	if ( !empty( $post_id ) && ( $field['id'] == '3' ) ) {
		
		$search_criteria = array(
			'status'        => 'active',
			'field_filters' => array( //which fields to search
			'mode'  => 'all',
				array(
					'key' => '3', 'operator' => 'is', 'value' => $value
				)
			)
		);
		
		$search_result = GFAPI::get_entries( $form['id'], $search_criteria );

		if ( !empty( $search_result ) && $result['is_valid'] ) { 
			$result['is_valid'] = false;
			$result['message'] = esc_html__( 'Player name already taken.', 'rife-free-child');
		}
	}
  
    return $result;
}


// To check if player name already registered in a mini league. Used when submitting to submit score form
add_filter( 'gform_field_validation', 'vr_does_username_exist_in_mini_league_pb', 10, 4 );
function vr_does_username_exist_in_mini_league_pb( $result, $value, $form, $field ) {
	
	$post_id = get_post_id_by_meta_key_and_value( '_um_groups_submit_score_form_id_' . PREMIUM_BOWLING_PREFIX, $form['id'] );	// Get league ID (group ID)
	
	if ( !empty( $post_id ) && ( $field['id'] == '17' ) ) {
		
		$join_form_id = get_post_meta( $post_id, '_um_groups_join_form_id_' . PREMIUM_BOWLING_PREFIX, true );	// Get the join form ID for the mini league
		
		// Check if player name ($value) is registered with the mini league via the join form
		$search_criteria = array(
			'status'        => 'active',
			'field_filters' => array( //which fields to search
			'mode'  => 'all',
				array(
					'key' => '3', 'operator' => 'is', 'value' => $value
				)
			)
		);
		
		$search_result = GFAPI::get_entries( $join_form_id, $search_criteria );

		if ( empty( $search_result ) && $result['is_valid'] ) { 
			$result['is_valid'] = false;
			$result['message'] = esc_html__( 'Player name does not exist. Ensure player has joined the game and the username is entered with the right case.', 'rife-free-child');
		}
	}

    return $result;
}


// Stop user from submitting the form to join a game in a league twice by hiding the form
add_filter( 'gform_get_form_filter', 'vr_has_user_join_league_pb', 10, 2 );
function vr_has_user_join_league_pb( $form_string, $form ) {
	
	$post_id = get_post_id_by_meta_key_and_value( '_um_groups_join_form_id_' . PREMIUM_BOWLING_PREFIX, $form['id'] );	// Get league ID (group ID)
	
	if ( !empty( $post_id ) ) {
		
		$current_user = wp_get_current_user();

		$search_criteria = array(
			'status'        => 'active',
			'field_filters' => array( //which fields to search
				array(
					'key' => 'created_by', 'value' => $current_user->ID, //Current logged in user
				)
			)
		);

		$entry = GFAPI::get_entries( $form['id'], $search_criteria );

		if ( !empty( $entry ) ) {
			$form_string = esc_html__( 'You have joined Premium Bowling. You cannot register twice.', 'rife-free-child' );
		} 
	}
  
    return $form_string;
}


/**
 * Get post id from meta key and value
 * @param string $key
 * @param mixed $value
 * @return int|bool
 * @author David M&aring;rtensson <david.martensson@gmail.com>
 */
function get_post_id_by_meta_key_and_value($key, $value) {
	
	global $wpdb;
	
	$meta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->postmeta." WHERE meta_key=%s AND meta_value=%s", $key, $value ) );
	//$meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".esc_sql($key)."' AND meta_value='".esc_sql($value)."'");
	
	if (is_array($meta) && !empty($meta) && isset($meta[0])) {
		$meta = $meta[0];
	}	
	
	if (is_object($meta)) {
		return $meta->post_id;
	} else {
		return false;
	}
}


// To pre-fill challenge score form with players' names as dropdown fields
add_filter( 'gform_pre_render', 'vr_generate_dropdown_with_player_names_for_mini_league_pb' );
function vr_generate_dropdown_with_player_names_for_mini_league_pb( $form ) {

	$post_id = get_post_id_by_meta_key_and_value( '_um_groups_challenge_score_form_id_' . PREMIUM_BOWLING_PREFIX, $form['id'] );	// Get league ID (group ID)
	
	if ( !empty( $post_id ) ) {
	
		$join_form_id = get_post_meta( $post_id, '_um_groups_join_form_id_' . PREMIUM_BOWLING_PREFIX, true );	// Get the join form ID for the mini league
		
		$search_criteria = array(
			'status'        => 'active'
		);

		$entries = GFAPI::get_entries( $join_form_id, $search_criteria, null, array(
			'offset'    => 0,
			'page_size' => 9999999999,
		) );

		if ( !empty( $entries ) ) {

			$choices = array();
			
			foreach ( $entries as $entry ) {
					$choices[] = ( isset( $entry['3'] ) ? array( 'text' => $entry['3'], 'value' => $entry['3'] ) : array( 'text' => esc_html__( 'No one enrolled their player name', 'rife-free-child' ), 'value' => esc_html__( '', 'rife-free-child' ) ) );
			}

		} else {
			$choices[] = array( 'text' => esc_html__( 'No one enrolled their player name', 'rife-free-child' ), 'value' => esc_html__( 'NA', 'rife-free-child' ) );
		}
		
		// Add players list to Player 1 dropdown menu. $form['fields'][0] means first field on the form, not field ID
		$form['fields'][0]->choices = $choices;
		
		// Add players list to Player 2 dropdown menu
		$form['fields'][1]->choices = $choices;
	}
	
    return $form;
}


// To check if player names for both dropdown fields are same when submitting challenge score form
add_filter( 'gform_field_validation', 'vr_are_players_name_identical_in_mini_league_pb', 10, 4 );
function vr_are_players_name_identical_in_mini_league_pb( $result, $value, $form, $field ) {
	
	$post_id = get_post_id_by_meta_key_and_value( '_um_groups_challenge_score_form_id_' . PREMIUM_BOWLING_PREFIX, $form['id'] );	// Get league ID (group ID)
	
	if ( !empty( $post_id ) ) {
	
		$player_1_name = rgpost( 'input_44' );
    
		if ( $result['is_valid'] && $field['id'] != '44' && $value == $player_1_name ) {
			$result['is_valid'] = false;
			$result['message'] = esc_html__( 'Selected players are identical. Please choose different player names.', 'rife-free-child');
		}
	
	}	

    return $result;
}
	
	
/***GORILLA TAG******************************************************************************/

// To check if team name is taken for Gorilla Tag
add_filter( 'gform_field_validation_' . FORM_ID_FOR_CREATE_TEAM_GORILLA_TAG . '_1', 'vr_is_team_name_taken_gt', 10, 4 );
function vr_is_team_name_taken_gt( $result, $value, $form, $field ) {
	
	$form_id = FORM_ID_FOR_CREATE_TEAM_GORILLA_TAG;
	
	$total_chars = strlen($value);
	
	if ( !empty( preg_match( '/^(?i)[a-z\d\-_\s]+$/', $value ) ) ) {	// Regex to detect alphanumeric, -, _ and space
	
		if ( $total_chars > 20 || $total_chars < 3 ) {
			$result['is_valid'] = false;
			$result['message'] = esc_html__( 'Min is three characters and maximum is 20 (including spaces or dashes)', 'rife-free-child');
			
		} else {
			$search_criteria = array(
				'status'        => 'active',
				'field_filters' => array( //which fields to search
				'mode'  => 'all',
					array(
						'key' => '1', 'operator' => 'is', 'value' => $value
					)
				)
			);
			
			$search_result = GFAPI::get_entries( $form_id, $search_criteria );

			if ( !empty( $search_result ) && $result['is_valid'] ) { 
				$result['is_valid'] = false;
				$result['message'] = esc_html__( 'Name already taken', 'rife-free-child');
			}
		}
		
	} else {
		$result['is_valid'] = false;
		$result['message'] = esc_html__( 'Only alphanumeric (a-Z, 0-9) characters, dashes and spaces allowed ', 'rife-free-child');
	}		
  
    return $result;
}


// To check if player name is taken for Gorilla Tag
add_filter( 'gform_field_validation_' . FORM_ID_FOR_JOIN_GORILLA_TAG . '_3', 'vr_is_player_name_taken_gt', 10, 4 );
function vr_is_player_name_taken_gt( $result, $value, $form, $field ) {
	
	$form_id = FORM_ID_FOR_JOIN_GORILLA_TAG;
	
	$total_chars = strlen($value);
	
	if ( !empty( preg_match( '/^(?i)[a-z\d\-_\s]+$/', $value ) ) ) {	// Regex to detect alphanumeric, -, _ and space
	
		if ( $total_chars > 20 || $total_chars < 3 ) {
			$result['is_valid'] = false;
			$result['message'] = esc_html__( 'Min is three characters and maximum is 20 (including spaces or dashes)', 'rife-free-child');
			
		} else {
			$search_criteria = array(
				'status'        => 'active',
				'field_filters' => array( //which fields to search
				'mode'  => 'all',
					array(
						'key' => '3', 'operator' => 'is', 'value' => $value
					)
				)
			);
			
			$search_result = GFAPI::get_entries( $form_id, $search_criteria );

			if ( !empty( $search_result ) && $result['is_valid'] ) { 
				$result['is_valid'] = false;
				$result['message'] = esc_html__( 'Name already taken', 'rife-free-child');
			}
		}
		
	} else {
		$result['is_valid'] = false;
		$result['message'] = esc_html__( 'Only alphanumeric (a-Z, 0-9) characters, dashes and spaces allowed ', 'rife-free-child');
	}		
  
    return $result;
}


// To check if team is full before a user joins Gorilla Tag
add_filter( 'gform_field_validation_' . FORM_ID_FOR_JOIN_GORILLA_TAG . '_12', 'vr_is_team_full_gt', 10, 4 );
function vr_is_team_full_gt( $result, $value, $form, $field ) {
	
	$form_id = FORM_ID_FOR_JOIN_GORILLA_TAG;
	
	$search_criteria = array(
		'status'        => 'active',
		'field_filters' => array( //which fields to search
		'mode'  => 'all',
			array(
				'key' => '12', 'operator' => 'is', 'value' => $value
			)
		)
	);
	
	$search_result = GFAPI::get_entries( $form_id, $search_criteria );

	if ( !empty( $search_result ) && count( $search_result ) >= MAX_NUM_OF_PLAYERS_PER_TEAM_GORILLA_TAG ) { 
		$result['is_valid'] = false;
		$result['message'] = esc_html__( 'Team is full. Contact the team captain for more info.', 'rife-free-child');
	}
		
    return $result;
}


// Stop user from submitting the form FORM_ID_FOR_JOIN_GORILLA_TAG twice.
add_filter( 'gform_get_form_filter_' . FORM_ID_FOR_JOIN_GORILLA_TAG, 'vr_has_user_joined_gt', 10, 2 );
function vr_has_user_joined_gt( $form_string, $form ) {
	
    $form_id = FORM_ID_FOR_JOIN_GORILLA_TAG;
	
    $current_user = wp_get_current_user();

    //If user is not logged in...
    if ( empty( $current_user ) ) {
        nocache_headers(); 
        wp_redirect( wp_login_url() ); 
        exit();
    }

    $search_criteria = array(
        'status'        => 'active',
        'field_filters' => array( //which fields to search
            array(
                'key' => 'created_by', 'value' => $current_user->ID, //Current logged in user
            )
        )
    );

    $entry = GFAPI::get_entries( $form_id, $search_criteria );

    if ( !empty( $entry ) ) {
        $form_string = wp_kses( __( 'Sorry, you already joined a team. You cannot join two teams. To change team, <a href="/account/gorilla-tag-settings">click here</a>.', 'rife-free-child' ), array( 'a' => array( 'href' => array(), 'title' => array() ) ) );
		//$form_string = esc_html__( 'Sorry, you already joined a team. You cannot join two teams. To change team, click here.', 'rife-free-child' );
    }   
    return $form_string;
}


// Process user after they joined Gorilla Team
add_action( 'gform_after_submission_' . FORM_ID_FOR_JOIN_GORILLA_TAG, 'vr_process_user_after_joined_gt', 10, 2 );
function vr_process_user_after_joined_gt( $entry, $form ) {
	
	$user_id = get_current_user_id();

    //If user is not logged in...
    if ( $user_id == 0 ) {
        wp_redirect( wp_login_url() ); 
        exit();
    }
	
	//Get team ID
	$team_id = get_form_field_value( FORM_ID_FOR_CREATE_TEAM_GORILLA_TAG, 'id', '1', $entry['12'] );
	
	if ( empty( get_user_meta( $user_id, '_joined_game', true ) ) ) { 
		add_user_meta( $user_id, '_joined_game', 1 );
	}
	
	if ( empty( get_user_meta( $user_id, '_player_name_gt', true ) ) ) { 
		add_user_meta( $user_id, '_player_name_gt', $entry['3'] );
	}
	
	if ( empty( get_user_meta( $user_id, '_team_name_gt', true ) ) ) { 
		add_user_meta( $user_id, '_team_name_gt', $entry['12'] );
	}
	
	if ( empty( get_user_meta( $user_id, '_team_id_gt', true ) ) ) { 
		add_user_meta( $user_id, '_team_id_gt', $team_id );
	}
	
	GFAPI::update_entry_field( $entry['id'], '39', 'win' );
}


// To pre-fill join form with team names as dropdown field
//add_filter( 'gform_pre_render_' . FORM_ID_FOR_JOIN_GORILLA_TAG, 'vr_prefill_team_names_join_form_gt' );
/* function vr_prefill_team_names_join_form_gt( $form ) {
	
	$team_form_id = FORM_ID_FOR_CREATE_TEAM_GORILLA_TAG;
	
	$search_criteria = array(
		'status'        => 'active'
	);

	$entries = GFAPI::get_entries( $team_form_id, $search_criteria, null, array(
		'offset'    => 0,
		'page_size' => 9999999999,
	) );

	if ( !empty( $entries ) ) {

		$choices = array();
		
		foreach ( $entries as $entry ) {
				$choices[] = ( isset( $entry['1'] ) ? array( 'text' => $entry['1'], 'value' => $entry['1'] ) : array( 'text' => esc_html__( 'No team yet', 'rife-free-child' ), 'value' => esc_html__( 'NA', 'rife-free-child' ) ) );
		}

	} else {
		$choices[] = array( 'text' => esc_html__( 'No team yet', 'rife-free-child' ), 'value' => esc_html__( 'NA', 'rife-free-child' ) );
	}
	
	// Add team name list to team dropdown menu. $form['fields'][0] means first field on the form, not field ID
	$form['fields'][1]->choices = $choices;
	
    return $form;
}
 */

// To prevent choosing the same team for both dropdown fields when filling in the scorecard 
add_filter( 'gform_field_validation_' . FORM_ID_FOR_SCORECARD_GORILLA_TAG . '_58', 'vr_are_team_names_identical_in_scorecard_gt', 10, 4 );
function vr_are_team_names_identical_in_scorecard_gt( $result, $value, $form, $field ) {
	
	$team_name = rgpost( 'input_56' );

	if ( $result['is_valid'] && $field['id'] != '56' && $value == $team_name ) {
		$result['is_valid'] = false;
		$result['message'] = esc_html__( 'Selected teams are identical. Please choose different team.', 'rife-free-child');
	}

    return $result;
}


// Process the score submission by referee
add_action( 'gform_after_submission_' . FORM_ID_FOR_SCORECARD_GORILLA_TAG, 'vr_process_scorecard_gt', 10, 2 );
function vr_process_scorecard_gt( $entry, $form ) {
	
	$team_a_win_count = 0;
	$team_b_win_count = 0;
	
	if ( ! is_wp_error( $entry ) ) {
		
		$team_a_name = rgar( $entry, '56' );
		$team_b_name = rgar( $entry, '58' );
		
		$team_a_game_1_score = rgar( $entry, '5', 0 );
		$team_b_game_1_score = rgar( $entry, '7', 0 );
		$team_a_game_2_score = rgar( $entry, '16', 0 );
		$team_b_game_2_score = rgar( $entry, '17', 0 );
		$team_a_game_3_score = rgar( $entry, '26', 0 );
		$team_b_game_3_score = rgar( $entry, '27', 0 );
		$team_a_game_4_score = rgar( $entry, '63', 0 );
		$team_b_game_4_score = rgar( $entry, '64', 0 );
		$team_a_game_5_score = rgar( $entry, '65', 0 );
		$team_b_game_5_score = rgar( $entry, '66', 0 );
		
		$round_1_winner = rgar( $entry, '60', '' );
		$round_2_winner = rgar( $entry, '61', '' );
		$round_3_winner = rgar( $entry, '62', '' );
		$round_4_winner = rgar( $entry, '67', '' );
		$round_5_winner = rgar( $entry, '68', '' );
		
		if ( !empty( $round_1_winner ) && ( $round_1_winner == $team_a_name ) ) {
			$team_a_win_count++;
		} elseif ( !empty( $round_1_winner ) && ( $round_1_winner == $team_b_name ) ) {			
			$team_b_win_count++;
		}
		
		if ( !empty( $round_2_winner ) && ( $round_2_winner == $team_a_name ) ) {
			$team_a_win_count++;
		} elseif ( !empty( $round_2_winner ) && ( $round_2_winner == $team_b_name ) ) {			
			$team_b_win_count++;
		}
		
		if ( !empty( $round_3_winner ) && ( $round_3_winner == $team_a_name ) ) {
			$team_a_win_count++;
		} elseif ( !empty( $round_3_winner ) && ( $round_3_winner == $team_b_name ) ) {			
			$team_b_win_count++;
		}
		
		if ( !empty( $round_4_winner ) && ( $round_4_winner == $team_a_name ) ) {
			$team_a_win_count++;
		} elseif ( !empty( $round_4_winner ) && ( $round_4_winner == $team_b_name ) ) {			
			$team_b_win_count++;
		}
		
		if ( !empty( $round_5_winner ) && ( $round_5_winner == $team_a_name ) ) {
			$team_a_win_count++;
		} elseif ( !empty( $round_5_winner ) && ( $round_5_winner == $team_b_name ) ) {			
			$team_b_win_count++;
		}
		
		$win_method = rgar( $entry, '72' );
		
		$team_a_total_score = strtotime( '00:' . $team_a_game_1_score) + strtotime( '00:' . $team_a_game_2_score ) + strtotime( '00:' . $team_a_game_3_score ) + strtotime( '00:' . $team_a_game_4_score ) + strtotime( '00:' . $team_a_game_5_score );
		$team_a_total_score = date('H:i:s', $team_a_total_score);	// Save the total time in the hours: minutes: seconds
		
		$team_b_total_score = strtotime( '00:' . $team_b_game_1_score ) + strtotime( '00:' . $team_b_game_2_score ) + strtotime( '00:' . $team_b_game_3_score ) + strtotime( '00:' . $team_b_game_4_score ) + strtotime( '00:' . $team_b_game_5_score );
		$team_b_total_score = date('H:i:s', $team_b_total_score);
		
		// Each round win gets 1 round point while loss gets 0
		GFAPI::update_entry_field( $entry['id'], '69', $team_a_win_count );	
		GFAPI::update_entry_field( $entry['id'], '70', $team_b_win_count );
		
		// Total up the run time accumulated per team
		GFAPI::update_entry_field( $entry['id'], '74', $team_a_total_score );
		GFAPI::update_entry_field( $entry['id'], '75', $team_b_total_score );
		
		if ( $team_a_win_count > $team_b_win_count ) {
			GFAPI::update_entry_field( $entry['id'], '38', 'win' );
			GFAPI::update_entry_field( $entry['id'], '39', 'loss' );
			GFAPI::update_entry_field( $entry['id'], '36', LEAGUE_POINTS_FOR_WIN_GORILLA_TAG );
			GFAPI::update_entry_field( $entry['id'], '37', ( $win_method == 'normal' ) ? LEAGUE_POINTS_FOR_LOSS_GORILLA_TAG : LEAGUE_POINTS_FOR_FORFEIT_GORILLA_TAG );
			
			$team_a_result = 'win';
			$team_b_result = 'loss';
			$team_a_points = LEAGUE_POINTS_FOR_WIN_GORILLA_TAG;
			$team_b_points = ( $win_method == 'normal' ) ? LEAGUE_POINTS_FOR_LOSS_GORILLA_TAG : LEAGUE_POINTS_FOR_FORFEIT_GORILLA_TAG;

		} else if ( $team_b_win_count > $team_a_win_count ) {
			GFAPI::update_entry_field( $entry['id'], '39', 'win' );
			GFAPI::update_entry_field( $entry['id'], '38', 'loss' );
			GFAPI::update_entry_field( $entry['id'], '37', LEAGUE_POINTS_FOR_WIN_GORILLA_TAG );
			GFAPI::update_entry_field( $entry['id'], '36', ( $win_method == 'normal' ) ? LEAGUE_POINTS_FOR_LOSS_GORILLA_TAG : LEAGUE_POINTS_FOR_FORFEIT_GORILLA_TAG );
			
			$team_b_result = 'win';
			$team_a_result = 'loss';
			$team_b_points = LEAGUE_POINTS_FOR_WIN_GORILLA_TAG;
			$team_a_points = ( $win_method == 'normal' ) ? LEAGUE_POINTS_FOR_LOSS_GORILLA_TAG : LEAGUE_POINTS_FOR_FORFEIT_GORILLA_TAG;
			
		} else if ( $team_a_win_count = $team_b_win_count ) {	// No league points are given for a tie
			GFAPI::update_entry_field( $entry['id'], '38', 'draw' );
			GFAPI::update_entry_field( $entry['id'], '39', 'draw' );
			
			$team_a_result = 'draw';
			$team_b_result = 'draw';
			$team_a_points = 0;
			$team_b_points = 0;
		}
		
		$team_a_stats = vr_get_team_stats_gt( $form['id'], $team_a_name );
		$team_b_stats = vr_get_team_stats_gt( $form['id'], $team_b_name );
		
		vr_process_leaderboard_gt( $team_a_name, $team_a_stats );
		vr_process_leaderboard_gt( $team_b_name, $team_b_stats );

	}
}


// Process score submitted by referee for leaderboard
function vr_process_leaderboard_gt( $team_name, $stats ) {
	
	$leaderboard_form_id = FORM_ID_FOR_LEADERBOARD_GORILLA_TAG;
	$team_form_id = FORM_ID_FOR_CREATE_TEAM_GORILLA_TAG;
	
	$search_criteria = array(
		'status'        => 'active',
		'field_filters' => array(
		'mode'  => 'all',
			array(
				'key' => '1', 'value' => $team_name
			)
		)
	);

	$result = GFAPI::get_entries( $leaderboard_form_id, $search_criteria );

	if ( empty( $result ) ) {
		
		//Get team ID
		$team_id = get_form_field_value( $team_form_id, 'id', '1', $team_name );
		
		// Add team into leaderboard
		$entry = array(
			"form_id" => $leaderboard_form_id,
			"24" => $team_id,
			"1" => $team_name,
			"36" => $stats['total_points'],	// Total points
			"35" => $stats['total_score'],	// Total score
			"37" => $stats['total_wins'],	// Total wins
			"38" => $stats['total_draws'],	// Total draws
			"39" => $stats['total_losses'],	// Total losses
			"40" => $stats['total_matches'],	// Total matches played
			"41" => $stats['avg_score'],		// Average score based on matches played
			"42" => $stats['highest_score']	// Max score ever achieved
		);

		$entry_id = GFAPI::add_entry( $entry );
	
	} elseif ( !empty( $result ) ) {	
		
		$entry_id = $result[0]['id'];
		
		// Update existing team stats
		GFAPI::update_entry_field( $entry_id, '36', $stats['total_points'] );
		GFAPI::update_entry_field( $entry_id, '35', $stats['total_score'] );
		GFAPI::update_entry_field( $entry_id, '37', $stats['total_wins'] );
		GFAPI::update_entry_field( $entry_id, '38', $stats['total_draws'] );
		GFAPI::update_entry_field( $entry_id, '39', $stats['total_losses'] );
		GFAPI::update_entry_field( $entry_id, '40', $stats['total_matches'] );
		GFAPI::update_entry_field( $entry_id, '41', $stats['avg_score'] );
		GFAPI::update_entry_field( $entry_id, '42', $stats['highest_score'] );
	}
}


function vr_get_team_stats_gt( $form_id, $team_name ) {
		
	$total_score = '00:00:00';
	$avg_score = '00:00:00';
	$total_points = 0;
	$total_wins = 0;
	$total_draws = 0;
	$total_losses = 0;
	$total_matches = 0;
	$total_rounds = 0;
	$score_sum = array();
	$highest_score = array();
	
	// Search for entries where team name is team A in the score form
	$search_criteria = array(
		'status'        => 'active',
		'field_filters' => array(
		'mode'  => 'all',
			array(
				'key' => '56', 'value' => $team_name
			)
		)
	);
	
	$entries = GFAPI::get_entries( $form_id, $search_criteria, null, array(
		'offset'    => 0,
		'page_size' => 9999999999,
	) );
	
	if ( !empty( $entries ) ) {
		
		foreach ( $entries as $entry ) {
			
			$total_score = strtotime( $total_score ) + strtotime( $entry['74'] );
			$total_score = date('H:i:s', $total_score);	// Save the total time in the hours: minutes: seconds
			
			$total_points +=  ( is_numeric( $entry['36'] ) ) ? $entry['36'] : 0;
			$total_wins += ( $entry['38'] == 'win' ) ? 1 : 0;
			$total_draws += ( $entry['38'] == 'draw' ) ? 1 : 0;	
			$total_losses += ( $entry['38'] == 'loss' ) ? 1 : 0;
			$highest_score[] = max( $entry['5'], $entry['16'], $entry['26'], $entry['63'], $entry['65'] ); 
			$score_sum[] = strtotime( '00:' . $entry['5'] ) + strtotime( '00:' . $entry['16'] ) + strtotime( '00:' . $entry['26'] ) + strtotime( '00:' . $entry['63'] ) + strtotime( '00:' . $entry['65'] );
			$total_rounds +=  !empty( $entry['5'] ) ? 1 : 0;
			$total_rounds +=  !empty( $entry['16'] ) ? 1 : 0;
			$total_rounds +=  !empty( $entry['26'] ) ? 1 : 0;
			$total_rounds +=  !empty( $entry['63'] ) ? 1 : 0;
			$total_rounds +=  !empty( $entry['65'] ) ? 1 : 0;			
		}
		$total_matches += count( $entries );
		//$avg_score = date( 'H:i:s', strtotime( $total_score ) / $total_matches );
				
		//$avg_score = array_sum( $score_sum ) / count( $entries );
		//$avg_score = date('H:i:s', $avg_score);
	}
	
	// Search for entries where team name is team B in the score form
	$search_criteria = array(
		'status'        => 'active',
		'field_filters' => array(
		'mode'  => 'all',
			array(
				'key' => '58', 'value' => $team_name
			)
		)
	);
	
	$entries = GFAPI::get_entries( $form_id, $search_criteria, null, array(
		'offset'    => 0,
		'page_size' => 9999999999,
	) );
	
	if ( !empty( $entries ) ) {
		
		foreach ( $entries as $entry ) {
			
			$total_score = strtotime( $total_score ) + strtotime( $entry['75'] );
			$total_score = date('H:i:s', $total_score);	// Save the total time in the hours: minutes: seconds
			
			$total_points +=  ( is_numeric( $entry['37'] ) ) ? $entry['37'] : 0;
			$total_wins += ( $entry['39'] == 'win' ) ? 1 : 0;
			$total_draws += ( $entry['39'] == 'draw' ) ? 1 : 0;	
			$total_losses += ( $entry['39'] == 'loss' ) ? 1 : 0;
			$highest_score[] = max( $entry['7'], $entry['17'], $entry['27'], $entry['64'], $entry['66'] );
			$score_sum[] = strtotime( '00:' . $entry['7'] ) + strtotime( '00:' . $entry['17'] ) + strtotime( '00:' . $entry['27'] ) + strtotime( '00:' . $entry['64'] ) + strtotime( '00:' . $entry['66'] );
			$total_rounds +=  !empty( $entry['7'] ) ? 1 : 0;
			$total_rounds +=  !empty( $entry['17'] ) ? 1 : 0;
			$total_rounds +=  !empty( $entry['27'] ) ? 1 : 0;
			$total_rounds +=  !empty( $entry['64'] ) ? 1 : 0;
			$total_rounds +=  !empty( $entry['66'] ) ? 1 : 0;	
		}
		$total_matches += count( $entries );
		//$avg_score = date( 'H:i:s', strtotime( $total_score ) / $total_matches );
		
		//$avg_score = array_sum( $score_sum ) / count( $entries );
		//$avg_score = date('H:i:s', $avg_score);
	}
	
	$avg_score = array_sum( $score_sum ) / $total_rounds;
	$avg_score = date('H:i:s', $avg_score);
	
	//DEBUG
	//error_log(print_r($score_sum, true));
	//error_log(print_r($highest_score, true));
	//error_log("total rounds: " . $total_rounds);
	//error_log("avg_score: " . $avg_score);
	
	$team_stats = array( 'total_points' => $total_points, 'total_score' => $total_score, 'total_wins' => $total_wins, 'total_draws' => $total_draws, 'total_losses' => $total_losses, 'total_matches' => $total_matches, 'avg_score' => $avg_score, 'highest_score' => max( $highest_score ) );
	
	return $team_stats;
}


// To update the leaderboard with the rankings of players
add_action( 'vr_generate_rankings_gt', 'vr_generate_rankings_gt' );
function vr_generate_rankings_gt() {
	
	$form_id = FORM_ID_FOR_LEADERBOARD_GORILLA_TAG;
	$rank = 0;

	$search_criteria = array(
		'status'        => 'active'
	);

	$sorting = array( 'key' => '36', 'direction' => 'DESC', 'is_numeric' => true );	

	$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

    $result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

    if ( !empty($result) ) { 
	
		foreach($result as $key => $value) {
			$rank++;
			GFAPI::update_entry_field( $value['id'], '21', $value['20'] );	// Save old ranking
			GFAPI::update_entry_field( $value['id'], '20', $rank );	// Set new ranking
        }
    }	
}

// NOT USED
// To add a "Draw" option in the dropdown menu when selecting the winner of a round. Appears on Gorilla Tag Scorecard
// add_filter( 'gppa_input_choices_' . FORM_ID_FOR_SCORECARD_GORILLA_TAG . '_60', function( $choices, $field, $objects ) {

	// array_unshift( $choices, array(
		// 'text'       => 'Draw/Tie',
		// 'value'      => 'draw',
		// 'isSelected' => false,
	// ) );

	// return $choices;
	
// }, 10, 3 );


// Reprocess the scoreline if score is edited.
add_filter( 'gravityview-inline-edit/entry-updated', 'vr_reprocess_score_gt', 10, 5 );
function vr_reprocess_score_gt( $update_result, $entry = array(), $form_id = 0, $gf_field = null, $original_entry = array() ) {
	
	$form = array( 'id' => $form_id );
	
	vr_process_scorecard_gt( $entry, $form );
	
/* 	$post_id = get_post_id_by_meta_key_and_value( '_um_groups_submit_score_form_id_' . PREMIUM_BOWLING_PREFIX, $form_id );	// Get league ID (group ID)
	
	if ( !empty( $post_id ) ) {
		$leaderboard_form_id = get_post_meta( $post_id, '_um_groups_leaderboard_form_id_' . PREMIUM_BOWLING_PREFIX, true );	// Get the leaderboard form ID
		
		$join_form_id = get_post_meta( $post_id, '_um_groups_join_form_id_' . PREMIUM_BOWLING_PREFIX, true );
		
		if ( ! is_wp_error( $entry ) ) {
		
			$player_name = rgar( $entry, '17' );
			$score = rgar( $entry, '16' );
			$strikes = rgar( $entry, '13' );
			$spares = rgar( $entry, '14' );
			$turkeys = rgar( $entry, '15' );
			
			$search_criteria = array(
				'status'        => 'active',
				'field_filters' => array(
				'mode'  => 'all',
					array(
						'key' => '1', 'value' => $player_name
					)
				)
			);

			$result = GFAPI::get_entries( $leaderboard_form_id, $search_criteria );
			$entry_id = $result[0]['id'];
			
			// Update existing player stats
			GFAPI::update_entry_field( $entry_id, '8', get_average_score( $form['id'], '13', '17', $player_name ) );
			GFAPI::update_entry_field( $entry_id, '9', get_average_score( $form['id'], '14', '17', $player_name ) );
			GFAPI::update_entry_field( $entry_id, '10', get_average_score( $form['id'], '15', '17', $player_name ) );
			GFAPI::update_entry_field( $entry_id, '13', get_average_score( $form['id'], '16', '17', $player_name ) );
			GFAPI::update_entry_field( $entry_id, '6', get_max_value( $form['id'], '16', '17', $player_name ) );
			GFAPI::update_entry_field( $entry_id, '14', get_total_count( $form['id'], '17', $player_name ) );
			GFAPI::update_entry_field( $entry_id, '18', get_latest_submission( $form['id'], '17', $player_name ) );
			GFAPI::update_entry_field( $entry_id, '17', get_high_scoring_percentage( $form['id'], '16', '17', $player_name, '', '', 200 ) );
			GFAPI::update_entry_field( $entry_id, '25', get_total_sum( $form['id'], '13', '17', $player_name ) );
			GFAPI::update_entry_field( $entry_id, '26', get_total_sum( $form['id'], '14', '17', $player_name ) );
			GFAPI::update_entry_field( $entry_id, '27', get_total_sum( $form['id'], '15', '17', $player_name ) );
		}	
	} */
}


/***GORILLA TAG: MINI LEAGUE***********************************************/

// To include all Gravity Forms and Gravity Views to run a mini league and performs other stuff
add_action('um_groups_after_front_insert', 'vr_process_mini_league_gt', 10, 2);
function vr_process_mini_league_gt( $formdata, $group_id ) {
	
	$game_prefix = get_post_meta( $group_id, '_um_groups_game', true );
	
	if ( $game_prefix == GORILLA_TAG_PREFIX ) {
	
		$group_name = $formdata[ 'group_name' ];
		
		// Create the league forms and form views (for ranking)
		$join_form_id = GFAPI::duplicate_form( FORM_ID_FOR_JOIN_GORILLA_TAG_MINI_LEAGUE_DUPLICATION );
		
		if ( !empty( $join_form_id ) ) {
			vr_change_form_title( $join_form_id, sprintf( esc_html__( '%s Mini League - %s - Join', 'rife-free-child' ), GORILLA_TAG_TITLE, $group_name ) );
			
			$submit_score_form_id = GFAPI::duplicate_form( FORM_ID_FOR_SUBMIT_SCORE_FOR_GORILLA_TAG_MINI_LEAGUE_DUPLICATION );
		}
		
		if ( !empty( $submit_score_form_id ) ) {
			vr_change_form_title( $submit_score_form_id, sprintf( esc_html__( '%s Mini League - %s - Submit Score', 'rife-free-child' ), GORILLA_TAG_TITLE, $group_name ) );
			
			$edit_submit_score_post_id = vr_duplicate_post( POST_ID_FOR_GORILLA_TAG_SUBMIT_SCORE_EDIT_PAGE_DUPLICATION, sprintf( esc_html__( '%s Mini League - %s - Edit Score', 'rife-free-child' ), GORILLA_TAG_TITLE, $group_name ), 'publish' );
			
			$leaderboard_form_id = GFAPI::duplicate_form( FORM_ID_FOR_GORILLA_TAG_LEADERBOARD_MINI_LEAGUE_DUPLICATION );
		}
		
		if ( !empty( $leaderboard_form_id ) ) {
			vr_change_form_title( $leaderboard_form_id, sprintf( esc_html__( '%s Mini League - %s - Leaderboard', 'rife-free-child' ), GORILLA_TAG_TITLE, $group_name ) );
			
			$leaderboard_post_id = vr_duplicate_post( POST_ID_FOR_GORILLA_TAG_LEADERBOARD_MINI_LEAGUE_DUPLICATION, sprintf( esc_html__( '%s Mini League - %s - Leaderboard', 'rife-free-child' ), GORILLA_TAG_TITLE, $group_name ), 'publish' );
		}
		
		// To copy the column field settings of source view to new view so that the table looks like the original view 
		vr_copy_view_post_meta( POST_ID_FOR_GORILLA_TAG_LEADERBOARD_MINI_LEAGUE_DUPLICATION, $leaderboard_post_id, $leaderboard_form_id );
		vr_copy_view_post_meta( POST_ID_FOR_GORILLA_TAG_SUBMIT_SCORE_EDIT_PAGE_DUPLICATION, $edit_submit_score_post_id, $submit_score_form_id );
		
		// The newly copied views should refer from the new form IDs
		update_post_meta( $leaderboard_post_id, '_gravityview_form_id', $leaderboard_form_id );	
		update_post_meta( $edit_submit_score_post_id, '_gravityview_form_id', $submit_score_form_id );
		
		add_post_meta( $group_id, '_um_groups_join_form_id_' . GORILLA_TAG_PREFIX, $join_form_id );
		add_post_meta( $group_id, '_um_groups_submit_score_form_id_' . GORILLA_TAG_PREFIX, $submit_score_form_id );
		add_post_meta( $group_id, '_um_groups_leaderboard_form_id_' . GORILLA_TAG_PREFIX, $leaderboard_form_id );
		add_post_meta( $group_id, '_um_groups_leaderboard_post_id_' . GORILLA_TAG_PREFIX, $leaderboard_post_id );
		add_post_meta( $group_id, '_um_groups_edit_submit_score_post_id_' . GORILLA_TAG_PREFIX, $edit_submit_score_post_id );
	}
}


// To check if player name is taken in mini league when trying to join league
add_filter( 'gform_field_validation', 'is_player_name_taken_in_mini_league_gt', 10, 4 );
function is_player_name_taken_in_mini_league_gt( $result, $value, $form, $field ) {
	
	$post_id = get_post_id_by_meta_key_and_value( '_um_groups_join_form_id_' . GORILLA_TAG_PREFIX, $form['id'] );	// Get league ID (group ID)
	
	if ( !empty( $post_id ) && ( $field['id'] == '3' ) ) {
		
		$search_criteria = array(
			'status'        => 'active',
			'field_filters' => array( //which fields to search
			'mode'  => 'all',
				array(
					'key' => '3', 'operator' => 'is', 'value' => $value
				)
			)
		);
		
		$search_result = GFAPI::get_entries( $form['id'], $search_criteria );

		if ( !empty( $search_result ) && $result['is_valid'] ) { 
			$result['is_valid'] = false;
			$result['message'] = esc_html__( 'Player name already taken.', 'rife-free-child');
		}
	}
  
    return $result;
}


// Stop user from submitting the form to join a game in a league twice by hiding the form
add_filter( 'gform_get_form_filter', 'vr_has_user_joined_league_gt', 10, 2 );
function vr_has_user_joined_league_gt( $form_string, $form ) {
	
	$post_id = get_post_id_by_meta_key_and_value( '_um_groups_join_form_id_' . GORILLA_TAG_PREFIX, $form['id'] );	// Get league ID (group ID)
	
	if ( !empty( $post_id ) ) {
		
		$current_user = wp_get_current_user();

		$search_criteria = array(
			'status'        => 'active',
			'field_filters' => array( //which fields to search
				array(
					'key' => 'created_by', 'value' => $current_user->ID, //Current logged in user
				)
			)
		);

		$entry = GFAPI::get_entries( $form['id'], $search_criteria );

		if ( !empty( $entry ) ) {
			$form_string = esc_html__( 'You already joined. You cannot register twice.', 'rife-free-child' );
		} 
	}
  
    return $form_string;
}


// Process the score submission
add_action( 'gform_after_submission', 'vr_process_mini_league_score_gt', 10, 2 );
function vr_process_mini_league_score_gt( $entry, $form ) {
	
	$result = '';
	$player_1_win = 0; 
	$player_1_draw = 0;
	$player_1_loss = 0; 
	$player_1_points = 0;
	
	$player_2_win = 0; 
	$player_2_draw = 0;
	$player_2_loss = 0; 
	$player_2_points = 0;
	
	$post_id = get_post_id_by_meta_key_and_value( '_um_groups_submit_score_form_id_' . GORILLA_TAG_PREFIX, $form['id'] );	// Get league ID (group ID)
	
	if ( !empty( $post_id ) ) {
		
		$leaderboard_form_id = get_post_meta( $post_id, '_um_groups_leaderboard_form_id_' . GORILLA_TAG_PREFIX, true );	// Get the leaderboard form ID
		
		$join_form_id = get_post_meta( $post_id, '_um_groups_join_form_id_' . GORILLA_TAG_PREFIX, true );
	
		if ( ! is_wp_error( $entry ) ) {
			
			$name_field_ids_csv = rgar( $entry, '76' );	// Contains field id for player name. E.g: player 1 has field id of 2, player 2 is 11 and etc
			$name_field_ids = str_getcsv( $name_field_ids_csv );	// Convert CSV values to array
			
			foreach ( $name_field_ids as $name_field_id ) {
				
				$player_results = vr_calculate_mini_league_player_results_gt( $entry, $name_field_id );
				
				if ( !empty( $player_results ) ) {
					$player_stats = vr_get_mini_league_player_stats_gt( $form['id'], $name_field_ids, $player_results['name'] );
				
					vr_process_mini_league_leaderboard_gt( $leaderboard_form_id, $join_form_id, $player_results['name'], $player_stats );
				}
			}
		}
	}
}


// Src: https://gist.github.com/zackkatz/62281b55c81e183b8a84cc2706aaa495
// Reprocess the scoreline if league manager edited a score.
add_action( 'gravityview/edit_entry/after_update', 'vr_reprocess_mini_league_score_gt', 10, 3 );
function vr_reprocess_mini_league_score_gt( $form = array(), $entry_id = array(), $object ) {

	$post_id = get_post_id_by_meta_key_and_value( '_um_groups_submit_score_form_id_' . GORILLA_TAG_PREFIX, $form['id'] );	// Get league ID (group ID)
	
	if ( !empty( $post_id ) ) {
		vr_process_mini_league_score_gt( $object->entry, $form );
		
		$post = get_post( $post_id ); 
		$slug = $post->post_name;
		
		// Need to manually redirect back to the edit score page because GravityView edit page appends the URL to be like https://vr2.test/um-groups/gt-4/entry/123" and the "/entry/123/" stays permanently in the URL after edit. 
		wp_redirect( home_url() . '/um-groups/' . $slug . '?tab=edit_score', 301 );	
	}
}


// Get total points and score for a player for entire rounds. Set to only max of five rounds
function vr_calculate_mini_league_player_results_gt( $entry, $player_name_field_id ) {
		
		$player_total_points = 0;
		$player_total_score = 0;
		$player_total_wins = 0;
		
		$x = (int) $player_name_field_id;
		
		if ( !empty( $entry[ $player_name_field_id ] ) ) { 
			$player_name = rgar( $entry, $player_name_field_id, '' );
			$player_total_points += ( is_numeric( $entry[ (string) $x + 1 ] ) ) ? $entry[ (string) $x + 1 ] : 0;	// One round each
			$player_total_points += ( is_numeric( $entry[ (string) $x + 3 ] ) ) ? $entry[ (string) $x + 3 ] : 0;
			$player_total_points += ( is_numeric( $entry[ (string) $x + 5 ] ) ) ? $entry[ (string) $x + 5 ] : 0;
			$player_total_points += ( is_numeric( $entry[ (string) $x + 7 ] ) ) ? $entry[ (string) $x + 7 ] : 0;
			$player_total_points += ( is_numeric( $entry[ (string) $x + 9 ] ) ) ? $entry[ (string) $x + 9 ] : 0;
	
			$player_total_score += ( !empty( $entry[ (string) $x + 2 ] ) ) ? strtotime( '00:' . $entry[ (string) $x + 2 ] ) : 0;	// One round each
			$player_total_score += ( !empty( $entry[ (string) $x + 4 ] ) ) ? strtotime( '00:' . $entry[ (string) $x + 4 ] ) : 0;
			$player_total_score += ( !empty( $entry[ (string) $x + 6 ] ) ) ? strtotime( '00:' . $entry[ (string) $x + 6 ] ) : 0;
			$player_total_score += ( !empty( $entry[ (string) $x + 8 ] ) ) ? strtotime( '00:' . $entry[ (string) $x + 8 ] ) : 0;
			$player_total_score += ( !empty( $entry[ (string) $x + 10 ] ) ) ? strtotime( '00:' . $entry[ (string) $x + 10 ] ) : 0;
			$player_total_score = date('H:i:s', $player_total_score);	// Save the total time in the hours: minutes: seconds
			
			// Total wins is not used at the moment. KIV
/* 			$player_total_wins += ( !empty( $entry['78'] ) && ( $entry['78'] == $player_name ) ) ? 1 : 0;
			$player_total_wins += ( !empty( $entry['79'] ) && ( $entry['79'] == $player_name ) ) ? 1 : 0;
			$player_total_wins += ( !empty( $entry['80'] ) && ( $entry['80'] == $player_name ) ) ? 1 : 0;
			$player_total_wins += ( !empty( $entry['81'] ) && ( $entry['81'] == $player_name ) ) ? 1 : 0;
			$player_total_wins += ( !empty( $entry['82'] ) && ( $entry['82'] == $player_name ) ) ? 1 : 0; */
			
			GFAPI::update_entry_field( $entry['id'], $x + 11, $player_total_points );
			GFAPI::update_entry_field( $entry['id'], $x + 12, $player_total_score );
			
			return array( 'name' => $player_name, 'total_points' => $player_total_points, 'total_score' => $player_total_score );
			//return array( 'name' => $player_name, 'total_points' => $player_total_points, 'total_score' => $player_total_score, 'total_wins' => $total_wins );
		
		} else {
			return false;
		}
	
}


function vr_get_mini_league_player_stats_gt( $form_id, $player_name_field_ids, $player_name ) {
		
	$total_score = '00:00:00';
	$avg_points = 0;
	$total_points = 0;
	$total_matches = 0;
	//$total_wins = 0;
	$highest_score = array();
	
	// Search for player name in the multiple player name field ids in the form
	$search_criteria = array();
	$search_criteria['status'] = 'active';
	$search_criteria['field_filters'] = array();
	$search_criteria['field_filters']['mode'] = 'any';
	
	foreach ( $player_name_field_ids as $player_name_field_id ) {
		$search_criteria['field_filters'][] = array( 'key' => $player_name_field_id, 'value' => $player_name );
	}
	
	$entries = GFAPI::get_entries( $form_id, $search_criteria, null, array(
		'offset'    => 0,
		'page_size' => 9999999999,
	) );
	
	if ( !empty( $entries ) ) {
		
		foreach ( $entries as $entry ) {
			
			foreach ( $player_name_field_ids as $x ) {
				
				if ( $entry[$x] == $player_name ) {
					
					//KIV
/*					$total_wins += ( !empty( $entry['78'] ) && ( $entry['78'] == $player_name ) ) ? 1 : 0;
					$total_wins += ( !empty( $entry['79'] ) && ( $entry['79'] == $player_name ) ) ? 1 : 0;
					$total_wins += ( !empty( $entry['80'] ) && ( $entry['80'] == $player_name ) ) ? 1 : 0;
					$total_wins += ( !empty( $entry['81'] ) && ( $entry['81'] == $player_name ) ) ? 1 : 0;
					$total_wins += ( !empty( $entry['82'] ) && ( $entry['82'] == $player_name ) ) ? 1 : 0; */
					
					$total_points +=  ( is_numeric( $entry[ (string) $x + 11 ] ) ) ? $entry[ (string) $x + 11 ] : 0;
					$total_score = strtotime( $total_score ) + strtotime( $entry[ (string) $x + 12 ] );
					$total_score = date('H:i:s', $total_score);	// Save the total time in the hours: minutes: seconds
					
					$highest_score[] = max( $entry[ (string) $x + 2 ], $entry[ (string) $x + 4 ], $entry[ (string) $x + 6 ], $entry[ (string) $x + 8], $entry[ (string) $x + 10 ] );
					
					break;
				
				}					
			}
		}
		$total_matches += count( $entries );
	}
	
	$avg_points = $total_points / $total_matches;
	$avg_points = number_format( $avg_points, DECIMAL_PLACES, '.', '' );
	
	$player_stats = array( 'total_points' => $total_points, 'total_score' => $total_score, 'total_matches' => $total_matches, 'avg_points' => $avg_points, 'highest_score' => max( $highest_score ) );
	//$player_stats = array( 'total_points' => $total_points, 'total_score' => $total_score, 'total_matches' => $total_matches, 'avg_points' => $avg_points, 'highest_score' => max( $highest_score ), 'total_wins' => $total_wins );
	
	return $player_stats;
}


// Process score submitted for leaderboard
function vr_process_mini_league_leaderboard_gt( $leaderboard_form_id, $join_form_id, $player_name, $stats ) {
	
	$search_criteria = array(
		'status'        => 'active',
		'field_filters' => array(
		'mode'  => 'all',
			array(
				'key' => '1', 'value' => $player_name
			)
		)
	);

	$result = GFAPI::get_entries( $leaderboard_form_id, $search_criteria );

	if ( empty( $result ) ) {
		
		$user_id = get_form_field_value( $join_form_id, '9', '3', $player_name );
		
		$country = get_user_meta( $user_id, 'country', true);
		$state = ( $country == esc_html__( 'United States', 'rife-free-child' ) ) ? get_user_meta( $user_id, 'us_state', true) : get_user_meta( $user_id, 'user_state', true);
		$city = get_user_meta( $user_id, 'city', true);
		$user = get_userdata( $user_id );
		$user_login = $user->user_login;
		
		// Add player into leaderboard
		$entry = array(
			"form_id" => $leaderboard_form_id,
			"24" => $user_id,
			"1" => $player_name,
			"43" => $user_login,
			"23.6" => $country,
			"23.4" => $state,
			"23.3" => $city,
			"36" => $stats['total_points'],	// Total points
			"35" => $stats['total_score'],	// Total score
			//"37" => $stats['total_wins'],	// Total wins
			"40" => $stats['total_matches'],	// Total matches played
			"41" => $stats['avg_points'],		// Average points based on matches played
			"42" => $stats['highest_score']	// Max score ever achieved
		);

		$entry_id = GFAPI::add_entry( $entry );
	
	} elseif ( !empty( $result ) ) {	
		
		$entry_id = $result[0]['id'];
		
		// Update existing player stats
		GFAPI::update_entry_field( $entry_id, '36', $stats['total_points'] );
		GFAPI::update_entry_field( $entry_id, '35', $stats['total_score'] );
		//GFAPI::update_entry_field( $entry_id, '37', $stats['total_wins'] );
		GFAPI::update_entry_field( $entry_id, '40', $stats['total_matches'] );
		GFAPI::update_entry_field( $entry_id, '41', $stats['avg_points'] );
		GFAPI::update_entry_field( $entry_id, '42', $stats['highest_score'] );
	}
}


// To pre-fill score form with players' names as dropdown fields
add_filter( 'gform_pre_render', 'vr_generate_dropdown_with_player_names_for_mini_league_gt' );
function vr_generate_dropdown_with_player_names_for_mini_league_gt( $form ) {

	$post_id = get_post_id_by_meta_key_and_value( '_um_groups_submit_score_form_id_' . GORILLA_TAG_PREFIX, $form['id'] );	// Get league ID (group ID)
	
	if ( !empty( $post_id ) ) {
	
		$join_form_id = get_post_meta( $post_id, '_um_groups_join_form_id_' . GORILLA_TAG_PREFIX, true );	// Get the join form ID for the mini league
		
		$search_criteria = array(
			'status'        => 'active'
		);

		$entries = GFAPI::get_entries( $join_form_id, $search_criteria, null, array(
			'offset'    => 0,
			'page_size' => 9999999999,
		) );

		if ( !empty( $entries ) ) {

			$choices = array();
			
			$choices[] = array( 'text' => esc_html__( 'Select a player', 'rife-free-child' ), 'value' => esc_html__( '', 'rife-free-child' ) );
			
			foreach ( $entries as $entry ) {
					$choices[] = ( isset( $entry['3'] ) ? array( 'text' => $entry['3'], 'value' => $entry['3'] ) : array( 'text' => esc_html__( 'No one enrolled their player name', 'rife-free-child' ), 'value' => esc_html__( '', 'rife-free-child' ) ) );
			}

		} else {
			$choices[] = array( 'text' => esc_html__( 'No one enrolled their player name', 'rife-free-child' ), 'value' => esc_html__( 'NA', 'rife-free-child' ) );
		}
		
		// $form['fields'][0] means first field in the form, not field ID
		$form['fields'][4]->choices = $choices;		// Add players list to Player 1 dropdown menu
		$form['fields'][5]->choices = $choices;		// Add players list to Player 2 dropdown menu
		$form['fields'][6]->choices = $choices;		// ...
		$form['fields'][7]->choices = $choices;
		$form['fields'][8]->choices = $choices;
		$form['fields'][9]->choices = $choices;
		$form['fields'][10]->choices = $choices;
		$form['fields'][11]->choices = $choices;
		$form['fields'][29]->choices = $choices;	// Players list to select winner of round 1
		$form['fields'][47]->choices = $choices;	// Players list to select winner of round 2
		$form['fields'][65]->choices = $choices;	// ...
		$form['fields'][83]->choices = $choices;
		$form['fields'][101]->choices = $choices;
	}
	
    return $form;
}


// To check if player names for dropdown fields are same when submitting score form
add_filter( 'gform_field_validation', 'vr_are_players_name_identical_in_mini_league_gt', 10, 4 );
function vr_are_players_name_identical_in_mini_league_gt( $result, $value, $form, $field ) {
	
	$post_id = get_post_id_by_meta_key_and_value( '_um_groups_submit_score_form_id_' . GORILLA_TAG_PREFIX, $form['id'] );	// Get league ID (group ID)
	
	if ( !empty( $post_id ) ) {
		
		$name_field_ids_csv = rgpost( 'input_76' );
		$name_field_ids = str_getcsv( $name_field_ids_csv );	// Convert CSV to array
		$num_of_players = rgpost( 'input_192' );	// How many players played

		for ( $i = 0; $i < $num_of_players; $i++ ) {
			$name = rgpost( 'input_' . $name_field_ids[$i] );
			
			if ( $result['is_valid'] && $field['id'] != (string) $name_field_ids[$i] && $value == $name ) {
				$result['is_valid'] = false;
				$result['message'] = esc_html__( 'Selected values are identical. Please choose different player names.', 'rife-free-child');
			}
		}
	}

    return $result;
}


// Delete players data from all forms (join form, leaderboard form) except submit score form because removing player will affect the score for an entire match.
add_action('vr_delete_mini_league_player_gt', 'vr_delete_mini_league_player_gt', 10, 2 );
function vr_delete_mini_league_player_gt( $group_id, $user_id ) {
	
	$join_form_id = get_post_meta( $group_id, '_um_groups_join_form_id_' . GORILLA_TAG_PREFIX, true );
	//$submit_score_form_id = get_post_meta( $group_id, '_um_groups_submit_score_form_id_' . GORILLA_TAG_PREFIX, true );
	$leaderboard_form_id = get_post_meta( $group_id, '_um_groups_leaderboard_form_id_' . GORILLA_TAG_PREFIX, true );
	
	if ( !empty( $join_form_id ) ) {
	
		// Get the entry of player's registration in the game in the join form.
		$join_form_search_criteria = array(
			'status'        => 'active',
			'field_filters' => array( //which fields to search
			'mode'  => 'all',
				array(
					'key' => '9', 'operator' => 'is', 'value' => $user_id
				)
			)
		);
		
		$join_form_result = GFAPI::get_entries( $join_form_id, $join_form_search_criteria );
		
		// Delete the entry containing player's info
		if ( !empty( $join_form_result ) ) {
			$join_form_entry_id = $join_form_result[0]['id'];
			$player_name = $join_form_result[0]['3'];
			GFAPI::delete_entry( $join_form_entry_id );
		}
		
/* 		// Get all player's score entries in score form.
		$submit_score_form_search_criteria = array(
			'status'        => 'active',
			'field_filters' => array( //which fields to search
			'mode'  => 'all',
				array(
					'key' => '17', 'operator' => 'is', 'value' => $player_name
				)
			)
		);
		
		$submit_score_form_result = GFAPI::get_entries( $submit_score_form_id, $submit_score_form_search_criteria );
		
		// Delete each score entry
		if ( !empty( $submit_score_form_result ) ) {
			
			foreach($submit_score_form_result as $key => $value) {            
				$submit_score_form_entry_id = $value['id'];
				GFAPI::delete_entry( $submit_score_form_entry_id );
			}
		} */
		
		// Get the entry of player's ranking in the leaderboard form.
		$leaderboard_form_search_criteria = array(
			'status'        => 'active',
			'field_filters' => array( //which fields to search
			'mode'  => 'all',
				array(
					'key' => '24', 'operator' => 'is', 'value' => $user_id
				)
			)
		);
		
		$leaderboard_form_result = GFAPI::get_entries( $leaderboard_form_id, $leaderboard_form_search_criteria );
		
		// Delete the entry containing player's ranking
		if ( !empty( $leaderboard_form_result ) ) {
			$leaderboard_form_entry_id = $leaderboard_form_result[0]['id'];
			GFAPI::delete_entry( $leaderboard_form_entry_id );
		}
	}	
}


// To delete all the forms/views for a league if the league is being deleted by league admin
add_action( 'vr_delete_mini_league', 'vr_delete_mini_league_gt', 10, 1 );
function vr_delete_mini_league_gt( $group_id ) {
		
	$join_form_id = get_post_meta( $group_id, '_um_groups_join_form_id_' . GORILLA_TAG_PREFIX, true );
	
	if ( !empty( $join_form_id ) ) {
		
		$submit_score_form_id = get_post_meta( $group_id, '_um_groups_submit_score_form_id_' . GORILLA_TAG_PREFIX, true );
		$leaderboard_form_id = get_post_meta( $group_id, '_um_groups_leaderboard_form_id_' . GORILLA_TAG_PREFIX, true );
		$leaderboard_post_id = get_post_meta( $group_id, '_um_groups_leaderboard_post_id_' . GORILLA_TAG_PREFIX, true );
		$edit_submit_score_post_id = get_post_meta( $group_id, '_um_groups_edit_submit_score_post_id_' . GORILLA_TAG_PREFIX, true );
	
		if ( GFAPI::delete_form( $join_form_id) ) {
			if ( GFAPI::delete_form( $submit_score_form_id ) ) {
				if ( GFAPI::delete_form( $leaderboard_form_id ) ) {
					wp_delete_post( $leaderboard_post_id, false );
					wp_delete_post( $edit_submit_score_post_id, false );
				}
			}
		}
	}
}


// To update all Gorilla Tag mini leagues leaderboards with rankings of players
add_action( 'vr_generate_mini_league_rankings_gt', 'vr_generate_mini_league_rankings_gt' );
function vr_generate_mini_league_rankings_gt() {
	
	$form_ids = get_meta_values( '_um_groups_leaderboard_form_id_' . GORILLA_TAG_PREFIX, 'um_groups', 'publish' );
	
	foreach ( $form_ids as $form_id ) {
	
		$rank = 0;

		$search_criteria = array(
			'status'        => 'active'
		);

		$sorting = array( 'key' => '41', 'direction' => 'DESC', 'is_numeric' => true );	

		$paging = array( 'offset' => 0, 'page_size' => 999999999999 );

		$result = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging, $total_count );

		if ( !empty($result) ) { 
		
			foreach($result as $key => $value) {
				$rank++;
				GFAPI::update_entry_field( $value['id'], '21', $value['20'] );	// Save old ranking
				GFAPI::update_entry_field( $value['id'], '20', $rank );	// Set new ranking
			}
		}
	}
}


/***GORILLA TAG: END*******************************************************/


function vr_get_us_states( $has_parent = false ){ //get the value from the 'parent' field, sent via the AJAX post. 

	// Get the value from the 'parent' field, sent via the AJAX post.
   $parent_options = isset( $_POST['parent_option'] ) ? $_POST['parent_option'] : false;

   $all_options = array(
	'US' => array(
		'AL'=>'Alabama',
		'AK'=>'Alaska',
		'AZ'=>'Arizona',
		'AR'=>'Arkansas',
		'CA'=>'California',
		'CO'=>'Colorado',
		'CT'=>'Connecticut',
		'DE'=>'Delaware',
		'DC'=>'District of Columbia',
		'FL'=>'Florida',
		'GA'=>'Georgia',
		'HI'=>'Hawaii',
		'ID'=>'Idaho',
		'IL'=>'Illinois',
		'IN'=>'Indiana',
		'IA'=>'Iowa',
		'KS'=>'Kansas',
		'KY'=>'Kentucky',
		'LA'=>'Louisiana',
		'ME'=>'Maine',
		'MD'=>'Maryland',
		'MA'=>'Massachusetts',
		'MI'=>'Michigan',
		'MN'=>'Minnesota',
		'MS'=>'Mississippi',
		'MO'=>'Missouri',
		'MT'=>'Montana',
		'NE'=>'Nebraska',
		'NV'=>'Nevada',
		'NH'=>'New Hampshire',
		'NJ'=>'New Jersey',
		'NM'=>'New Mexico',
		'NY'=>'New York',
		'NC'=>'North Carolina',
		'ND'=>'North Dakota',
		'OH'=>'Ohio',
		'OK'=>'Oklahoma',
		'OR'=>'Oregon',
		'PA'=>'Pennsylvania',
		'RI'=>'Rhode Island',
		'SC'=>'South Carolina',
		'SD'=>'South Dakota',
		'TN'=>'Tennessee',
		'TX'=>'Texas',
		'UT'=>'Utah',
		'VT'=>'Vermont',
		'VA'=>'Virginia',
		'WA'=>'Washington',
		'WV'=>'West Virginia',
		'WI'=>'Wisconsin',
		'WY'=>'Wyoming',
		'AE'=>'Armed Forces Africa \ Canada \ Europe \ Middle East',
		'AA'=>'Armed Forces America (Except Canada)',
		'AP'=>'Armed Forces Pacific'
		)
	);

   $arr_options = array();

   if ( ! is_array( $parent_options ) ) {
      $parent_options = array( $parent_options );
   }

   foreach ( $parent_options as $parent_option ) {
      if ( isset( $all_options[ $parent_option ] ) ) {
         $arr_options = array_merge( $arr_options, $all_options[ $parent_option ] );
      } elseif ( ! isset( $_POST['parent_option'] ) ) {
         foreach ( $all_options as $k => $opts ) {
            $arr_options = array_merge( $opts, $arr_options );
         }
      }
   }

   //code to do something if other options are not selected or empty match
   if ( empty( $arr_options ) ) {
      $arr_options[ ] = "No states";
   } else {
      $arr_options = array_unique( $arr_options );
   }

   return $arr_options;
}

// To display win or loss image on match result 
add_shortcode( 'vr_show_win_or_lose', 'vr_show_win_or_lose' );	
function vr_show_win_or_lose( $atts ) {
	
	$args = shortcode_atts( array(
		'result' => '',
	), $atts );
	
	$result = $args['result'];
	
	if ( $result == 'Win' || $result == 'win' ) {
		return '<i class="fa fa-trophy" aria-hidden="true"></i>';
	} else if ( $result == 'Draw' ) {
		return '<i class="fa fa-handshake-o" aria-hidden="true"></i>';
	}
}

// To display country flag
add_shortcode( 'vr_get_country_flag', 'get_country_flag' );	
function get_country_flag( $atts ) {
	
	$args = shortcode_atts( array(
		'country' => ''
	), $atts );

	$countries = array(
		"AF" => "Afghanistan",
		"AL" => "Albania",
		"DZ" => "Algeria",
		"AS" => "American Samoa",
		"AD" => "Andorra",
		"AO" => "Angola",
		"AI" => "Anguilla",
		"AQ" => "Antarctica",
		"AG" => "Antigua and Barbuda",
		"AR" => "Argentina",
		"AM" => "Armenia",
		"AW" => "Aruba",
		"AU" => "Australia",
		"AT" => "Austria",
		"AZ" => "Azerbaijan",
		"BS" => "Bahamas",
		"BH" => "Bahrain",
		"BD" => "Bangladesh",
		"BB" => "Barbados",
		"BY" => "Belarus",
		"BE" => "Belgium",
		"BZ" => "Belize",
		"BJ" => "Benin",
		"BM" => "Bermuda",
		"BT" => "Bhutan",
		"BO" => "Bolivia",
		"BQ" => "Bonaire, Sint Eustatius and Saba",
		"BA" => "Bosnia and Herzegovina",
		"BW" => "Botswana",
		"BV" => "Bouvet Island",
		"BR" => "Brazil",
		"IO" => "British Indian Ocean Territory",
		"BN" => "Brunei Darussalam",
		"BG" => "Bulgaria",
		"BF" => "Burkina Faso",
		"BI" => "Burundi",
		"CV" => "Cabo Verde",
		"KH" => "Cambodia",
		"CM" => "Cameroon",
		"CA" => "Canada",
		"KY" => "Cayman Islands",
		"CF" => "Central African Republic",
		"TD" => "Chad",
		"CL" => "Chile",
		"CN" => "China",
		"CX" => "Christmas Island",
		"CC" => "Cocos Islands",
		"CO" => "Colombia",
		"KM" => "Comoros",
		"CG" => "Congo",
		"CD" => "Congo, Democratic Republic of the",
		"CK" => "Cook Islands",
		"CR" => "Costa Rica",
		"HR" => "Croatia",
		"CU" => "Cuba",
		"CW" => "Curaçao",
		"CY" => "Cyprus",
		"CZ" => "Czechia",
		"CI" => "Côte d’Ivoire",
		"DK" => "Denmark",
		"DJ" => "Djibouti",
		"DM" => "Dominica",
		"DO" => "Dominican Republic",
		"EC" => "Ecuador",
		"EG" => "Egypt",
		"SV" => "El Salvador",
		"GQ" => "Equatorial Guinea",
		"ER" => "Eritrea",
		"EE" => "Estonia",
		"SZ" => "Eswatini",
		"ET" => "Ethiopia",
		"FK" => "Falkland Islands",
		"FO" => "Faroe Islands",
		"FJ" => "Fiji",
		"FI" => "Finland",
		"FR" => "France",
		"GF" => "French Guiana",
		"PF" => "French Polynesia",
		"TF" => "French Southern Territories",
		"GA" => "Gabon",
		"GM" => "Gambia",
		"GE" => "Georgia",
		"DE" => "Germany",
		"GH" => "Ghana",
		"GI" => "Gibraltar",
		"GR" => "Greece",
		"GL" => "Greenland",
		"GD" => "Grenada",
		"GP" => "Guadeloupe",
		"GU" => "Guam",
		"GT" => "Guatemala",
		"GG" => "Guernsey",
		"GN" => "Guinea",
		"GW" => "Guinea-Bissau",
		"GY" => "Guyana",
		"HT" => "Haiti",
		"HM" => "Heard Island and McDonald Islands",
		"VA" => "Holy See",
		"HN" => "Honduras",
		"HK" => "Hong Kong",
		"HU" => "Hungary",
		"IS" => "Iceland",
		"IN" => "India",
		"ID" => "Indonesia",
		"IR" => "Iran",
		"IQ" => "Iraq",
		"IE" => "Ireland",
		"IM" => "Isle of Man",
		"IL" => "Israel",
		"IT" => "Italy",
		"JM" => "Jamaica",
		"JP" => "Japan",
		"JE" => "Jersey",
		"JO" => "Jordan",
		"KZ" => "Kazakhstan",
		"KE" => "Kenya",
		"KI" => "Kiribati",
		"KP" => "Korea, Democratic People's Republic of",
		"KR" => "Korea, Republic of",
		"KW" => "Kuwait",
		"KG" => "Kyrgyzstan",
		"LA" => "Lao People's Democratic Republic",
		"LV" => "Latvia",
		"LB" => "Lebanon",
		"LS" => "Lesotho",
		"LR" => "Liberia",
		"LY" => "Libya",
		"LI" => "Liechtenstein",
		"LT" => "Lithuania",
		"LU" => "Luxembourg",
		"MO" => "Macau",
		"MG" => "Madagascar",
		"MW" => "Malawi",
		"MY" => "Malaysia",
		"MV" => "Maldives",
		"ML" => "Mali",
		"MT" => "Malta",
		"MH" => "Marshall Islands",
		"MQ" => "Martinique",
		"MR" => "Mauritania",
		"MU" => "Mauritius",
		"YT" => "Mayotte",
		"MX" => "Mexico",
		"FM" => "Micronesia",
		"MD" => "Moldova",
		"MC" => "Monaco",
		"MN" => "Mongolia",
		"ME" => "Montenegro",
		"MS" => "Montserrat",
		"MA" => "Morocco",
		"MZ" => "Mozambique",
		"MM" => "Myanmar",
		"NA" => "Namibia",
		"NR" => "Nauru",
		"NP" => "Nepal",
		"NL" => "Netherlands",
		"NC" => "New Caledonia",
		"NZ" => "New Zealand",
		"NI" => "Nicaragua",
		"NE" => "Niger",
		"NG" => "Nigeria",
		"NU" => "Niue",
		"NF" => "Norfolk Island",
		"MK" => "North Macedonia",
		"MP" => "Northern Mariana Islands",
		"NO" => "Norway",
		"OM" => "Oman",
		"PK" => "Pakistan",
		"PW" => "Palau",
		"PS" => "Palestine, State of",
		"PA" => "Panama",
		"PG" => "Papua New Guinea",
		"PY" => "Paraguay",
		"PE" => "Peru",
		"PH" => "Philippines",
		"PN" => "Pitcairn",
		"PL" => "Poland",
		"PT" => "Portugal",
		"PR" => "Puerto Rico",
		"QA" => "Qatar",
		"RO" => "Romania",
		"RU" => "Russian Federation",
		"RW" => "Rwanda",
		"RE" => "Réunion",
		"BL" => "Saint Barthélemy",
		"SH" => "Saint Helena, Ascension and Tristan da Cunha",
		"KN" => "Saint Kitts and Nevis",
		"LC" => "Saint Lucia",
		"MF" => "Saint Martin",
		"PM" => "Saint Pierre and Miquelon",
		"VC" => "Saint Vincent and the Grenadines",
		"WS" => "Samoa",
		"SM" => "San Marino",
		"ST" => "Sao Tome and Principe",
		"SA" => "Saudi Arabia",
		"SN" => "Senegal",
		"RS" => "Serbia",
		"SC" => "Seychelles",
		"SL" => "Sierra Leone",
		"SG" => "Singapore",
		"SX" =>	"Sint Maarten",
		"SK" => "Slovakia",
		"SI" => "Slovenia",
		"SB" => "Solomon Islands",
		"SO" => "Somalia",
		"ZA" => "South Africa",
		"GS" => "South Georgia and the South Sandwich Islands",
		"SS" => "South Sudan",
		"ES" => "Spain",
		"LK" => "Sri Lanka",
		"SD" => "Sudan",
		"SR" => "Suriname",
		"SJ" => "Svalbard and Jan Mayen",
		"SE" => "Sweden",
		"CH" => "Switzerland",
		"SY" => "Syria Arab Republic",
		"TW" => "Taiwan",
		"TJ" => "Tajikistan",
		"TZ" => "Tanzania, the United Republic of",
		"TH" => "Thailand",
		"TL" => "Timor-Leste",
		"TG" => "Togo",
		"TK" => "Tokelau",
		"TO" => "Tonga",
		"TT" => "Trinidad and Tobago",
		"TN" => "Tunisia",
		"TR" => "Türkiye",
		"TM" => "Turkmenistan",
		"TC" => "Turks and Caicos Islands",
		"TV" => "Tuvalu",
		"UM" => "U.S. Minor Outlying Islands",
		"UG" => "Uganda",
		"UA" => "Ukraine",
		"AE" => "United Arab Emirates",
		"GB" => "United Kingdom",
		"US" => "United States",
		"ZZ" => "Unknown or Invalid Region",
		"UY" => "Uruguay",
		"UZ" => "Uzbekistan",
		"VU" => "Vanuatu",
		"VE" => "Venezuela",
		"VN" => "Vietnam",
		"VG" => "Virgin Islands, British",
		"VI" => "Virgin Islands, U.S.",
		"WF" => "Wallis and Futuna",
		"EH" => "Western Sahara",
		"YE" => "Yemen",
		"ZM" => "Zambia",
		"ZW" => "Zimbabwe",
		"AX" => "Åland Islands",
	);
	
/* 	$countries = array(
		"AF" => "Afghanistan",
		"AL" => "Albania",
		"DZ" => "Algeria",
		"AS" => "American Samoa",
		"AD" => "Andorra",
		"AO" => "Angola",
		"AI" => "Anguilla",
		"AQ" => "Antarctica",
		"AG" => "Antigua and Barbuda",
		"AR" => "Argentina",
		"AM" => "Armenia",
		"AW" => "Aruba",
		"AU" => "Australia",
		"AT" => "Austria",
		"AZ" => "Azerbaijan",
		"BS" => "Bahamas",
		"BH" => "Bahrain",
		"BD" => "Bangladesh",
		"BB" => "Barbados",
		"BY" => "Belarus",
		"BE" => "Belgium",
		"BZ" => "Belize",
		"BJ" => "Benin",
		"BM" => "Bermuda",
		"BT" => "Bhutan",
		"BO" => "Bolivia",
		"BQ" => "Bonaire, Sint Eustatius and Saba",
		"BA" => "Bosnia and Herzegovina",
		"BW" => "Botswana",
		"BV" => "Bouvet Island",
		"BR" => "Brazil",
		"BQ" => "British Antarctic Territory",
		"IO" => "British Indian Ocean Territory",
		"VG" => "British Virgin Islands",
		"BN" => "Brunei",
		"BG" => "Bulgaria",
		"BF" => "Burkina Faso",
		"BI" => "Burundi",
		"KH" => "Cambodia",
		"CM" => "Cameroon",
		"CA" => "Canada",
		"CT" => "Canton and Enderbury Islands",
		"CV" => "Cape Verde",
		"KY" => "Cayman Islands",
		"CF" => "Central African Republic",
		"TD" => "Chad",
		"CL" => "Chile",
		"CN" => "China",
		"CX" => "Christmas Island",
		"CC" => "Cocos [Keeling] Islands",
		"CO" => "Colombia",
		"KM" => "Comoros",
		"CG" => "Congo - Brazzaville",
		"CD" => "Congo - Kinshasa",
		"CK" => "Cook Islands",
		"CR" => "Costa Rica",
		"HR" => "Croatia",
		"CU" => "Cuba",
		"CY" => "Cyprus",
		"CZ" => "Czech Republic",
		"CI" => "Côte d’Ivoire",
		"DK" => "Denmark",
		"DJ" => "Djibouti",
		"DM" => "Dominica",
		"DO" => "Dominican Republic",
		"NQ" => "Dronning Maud Land",
		"DD" => "East Germany",
		"EC" => "Ecuador",
		"EG" => "Egypt",
		"SV" => "El Salvador",
		"GQ" => "Equatorial Guinea",
		"ER" => "Eritrea",
		"EE" => "Estonia",
		"ET" => "Ethiopia",
		"FK" => "Falkland Islands",
		"FO" => "Faroe Islands",
		"FJ" => "Fiji",
		"FI" => "Finland",
		"FR" => "France",
		"GF" => "French Guiana",
		"PF" => "French Polynesia",
		"TF" => "French Southern Territories",
		"FQ" => "French Southern and Antarctic Territories",
		"GA" => "Gabon",
		"GM" => "Gambia",
		"GE" => "Georgia",
		"DE" => "Germany",
		"GH" => "Ghana",
		"GI" => "Gibraltar",
		"GR" => "Greece",
		"GL" => "Greenland",
		"GD" => "Grenada",
		"GP" => "Guadeloupe",
		"GU" => "Guam",
		"GT" => "Guatemala",
		"GG" => "Guernsey",
		"GN" => "Guinea",
		"GW" => "Guinea-Bissau",
		"GY" => "Guyana",
		"HT" => "Haiti",
		"HM" => "Heard Island and McDonald Islands",
		"HN" => "Honduras",
		"HK" => "Hong Kong SAR China",
		"HU" => "Hungary",
		"IS" => "Iceland",
		"IN" => "India",
		"ID" => "Indonesia",
		"IR" => "Iran",
		"IQ" => "Iraq",
		"IE" => "Ireland",
		"IM" => "Isle of Man",
		"IL" => "Israel",
		"IT" => "Italy",
		"JM" => "Jamaica",
		"JP" => "Japan",
		"JE" => "Jersey",
		"JT" => "Johnston Island",
		"JO" => "Jordan",
		"KZ" => "Kazakhstan",
		"KE" => "Kenya",
		"KI" => "Kiribati",
		"KW" => "Kuwait",
		"KG" => "Kyrgyzstan",
		"LA" => "Laos",
		"LV" => "Latvia",
		"LB" => "Lebanon",
		"LS" => "Lesotho",
		"LR" => "Liberia",
		"LY" => "Libya",
		"LI" => "Liechtenstein",
		"LT" => "Lithuania",
		"LU" => "Luxembourg",
		"MO" => "Macau SAR China",
		"MK" => "Macedonia",
		"MG" => "Madagascar",
		"MW" => "Malawi",
		"MY" => "Malaysia",
		"MV" => "Maldives",
		"ML" => "Mali",
		"MT" => "Malta",
		"MH" => "Marshall Islands",
		"MQ" => "Martinique",
		"MR" => "Mauritania",
		"MU" => "Mauritius",
		"YT" => "Mayotte",
		"FX" => "Metropolitan France",
		"MX" => "Mexico",
		"FM" => "Micronesia",
		"MI" => "Midway Islands",
		"MD" => "Moldova",
		"MC" => "Monaco",
		"MN" => "Mongolia",
		"ME" => "Montenegro",
		"MS" => "Montserrat",
		"MA" => "Morocco",
		"MZ" => "Mozambique",
		"MM" => "Myanmar [Burma]",
		"NA" => "Namibia",
		"NR" => "Nauru",
		"NP" => "Nepal",
		"NL" => "Netherlands",
		"AN" => "Netherlands Antilles",
		"NT" => "Neutral Zone",
		"NC" => "New Caledonia",
		"NZ" => "New Zealand",
		"NI" => "Nicaragua",
		"NE" => "Niger",
		"NG" => "Nigeria",
		"NU" => "Niue",
		"NF" => "Norfolk Island",
		"KP" => "North Korea",
		"VD" => "North Vietnam",
		"MP" => "Northern Mariana Islands",
		"NO" => "Norway",
		"OM" => "Oman",
		"PC" => "Pacific Islands Trust Territory",
		"PK" => "Pakistan",
		"PW" => "Palau",
		"PS" => "Palestinian Territories",
		"PA" => "Panama",
		"PZ" => "Panama Canal Zone",
		"PG" => "Papua New Guinea",
		"PY" => "Paraguay",
		"YD" => "People's Democratic Republic of Yemen",
		"PE" => "Peru",
		"PH" => "Philippines",
		"PN" => "Pitcairn Islands",
		"PL" => "Poland",
		"PT" => "Portugal",
		"PR" => "Puerto Rico",
		"QA" => "Qatar",
		"RO" => "Romania",
		"RU" => "Russia",
		"RW" => "Rwanda",
		"RE" => "Réunion",
		"BL" => "Saint Barthélemy",
		"SH" => "Saint Helena",
		"KN" => "Saint Kitts and Nevis",
		"LC" => "Saint Lucia",
		"MF" => "Saint Martin",
		"PM" => "Saint Pierre and Miquelon",
		"VC" => "Saint Vincent and the Grenadines",
		"WS" => "Samoa",
		"SM" => "San Marino",
		"SA" => "Saudi Arabia",
		"SN" => "Senegal",
		"RS" => "Serbia",
		"CS" => "Serbia and Montenegro",
		"SC" => "Seychelles",
		"SL" => "Sierra Leone",
		"SG" => "Singapore",
		"SK" => "Slovakia",
		"SI" => "Slovenia",
		"SB" => "Solomon Islands",
		"SO" => "Somalia",
		"ZA" => "South Africa",
		"GS" => "South Georgia and the South Sandwich Islands",
		"KR" => "South Korea",
		"ES" => "Spain",
		"LK" => "Sri Lanka",
		"SD" => "Sudan",
		"SR" => "Suriname",
		"SJ" => "Svalbard and Jan Mayen",
		"SZ" => "Swaziland",
		"SE" => "Sweden",
		"CH" => "Switzerland",
		"SY" => "Syria",
		"ST" => "São Tomé and Príncipe",
		"TW" => "Taiwan",
		"TJ" => "Tajikistan",
		"TZ" => "Tanzania",
		"TH" => "Thailand",
		"TL" => "Timor-Leste",
		"TG" => "Togo",
		"TK" => "Tokelau",
		"TO" => "Tonga",
		"TT" => "Trinidad and Tobago",
		"TN" => "Tunisia",
		"TR" => "Turkey",
		"TM" => "Turkmenistan",
		"TC" => "Turks and Caicos Islands",
		"TV" => "Tuvalu",
		"UM" => "U.S. Minor Outlying Islands",
		"PU" => "U.S. Miscellaneous Pacific Islands",
		"VI" => "U.S. Virgin Islands",
		"UG" => "Uganda",
		"UA" => "Ukraine",
		"SU" => "Union of Soviet Socialist Republics",
		"AE" => "United Arab Emirates",
		"GB" => "United Kingdom",
		"US" => "United States",
		"ZZ" => "Unknown or Invalid Region",
		"UY" => "Uruguay",
		"UZ" => "Uzbekistan",
		"VU" => "Vanuatu",
		"VA" => "Vatican City",
		"VE" => "Venezuela",
		"VN" => "Vietnam",
		"WK" => "Wake Island",
		"WF" => "Wallis and Futuna",
		"EH" => "Western Sahara",
		"YE" => "Yemen",
		"ZM" => "Zambia",
		"ZW" => "Zimbabwe",
		"AX" => "Åland Islands",
	); */

	$country_name = $args['country'];
	$country_iso_code = array_search($country_name, $countries);

	return '<span class="' . esc_attr( $country_iso_code ) . '" title="' . esc_attr( $country_name ) . '" alt="Flag of ' . esc_attr( $country_name ) . '"></span>';
}

//To run the WP Cron every custom period of time
//add_filter( 'cron_schedules', 'new_cron_schedules' );
// function new_cron_schedules( $schedules ) {

	// $schedules['hourly'] = array(
            // 'interval'  => 3600,	// In seconds
            // 'display'   => esc_html__( 'Every Hour', 'rife-free-child' )
    // );
	
	// $schedules['65 minutes'] = array(
            // 'interval'  => 3900,	// In seconds
            // 'display'   => esc_html__( '65 minutes', 'rife-free-child' )
    // );
	
	// $schedules['70 minutes'] = array(
            // 'interval'  => 3900,	// In seconds
            // 'display'   => esc_html__( '70 minutes', 'rife-free-child' )
    // );

	// $schedules['75 minutes'] = array(
            // 'interval'  => 3900,	// In seconds
            // 'display'   => esc_html__( '75 minutes', 'rife-free-child' )
    // );
	
	// $schedules['daily'] = array(
            // 'interval'  => 86400,	// In seconds
            // 'display'   => esc_html__( 'Everyday', 'rife-free-child' )
    // );

    // return $schedules;
// }

// Schedule action if it's not already scheduled
if ( !wp_next_scheduled( 'vr_generate_ranking_premium_bowling_weekly' ) ) {
	wp_schedule_event( strtotime('08:00:00'), 'daily', 'vr_generate_ranking_premium_bowling_weekly' );
}

if ( !wp_next_scheduled( 'vr_generate_country_ranking_premium_bowling_weekly' ) ) {
	wp_schedule_event( strtotime('08:10:00'), 'daily', 'vr_generate_country_ranking_premium_bowling_weekly' );
}

if ( !wp_next_scheduled( 'vr_generate_state_ranking_premium_bowling_weekly' ) ) {
	wp_schedule_event( strtotime('08:20:00'), 'daily', 'vr_generate_state_ranking_premium_bowling_weekly' );
}

if ( !wp_next_scheduled( 'vr_generate_ranking_premium_bowling_monthly' ) ) {
	wp_schedule_event( strtotime('08:30:00'), 'daily', 'vr_generate_ranking_premium_bowling_monthly' );
}

if ( !wp_next_scheduled( 'vr_generate_country_ranking_premium_bowling_monthly' ) ) {
	wp_schedule_event( strtotime('08:40:00'), 'daily', 'vr_generate_country_ranking_premium_bowling_monthly' );
}

if ( !wp_next_scheduled( 'vr_generate_state_ranking_premium_bowling_monthly' ) ) {
	wp_schedule_event( strtotime('08:50:00'), 'daily', 'vr_generate_state_ranking_premium_bowling_monthly' );
}

if ( !wp_next_scheduled( 'vr_generate_ranking_premium_bowling_overall' ) ) {
	wp_schedule_event( strtotime('09:00:00'), 'daily', 'vr_generate_ranking_premium_bowling_overall' );
}

if ( !wp_next_scheduled( 'vr_generate_country_ranking_premium_bowling_overall' ) ) {
	wp_schedule_event( strtotime('09:10:00'), 'daily', 'vr_generate_country_ranking_premium_bowling_overall' );
}

if ( !wp_next_scheduled( 'vr_generate_state_ranking_premium_bowling_overall' ) ) {
	wp_schedule_event( strtotime('09:20:00'), 'daily', 'vr_generate_state_ranking_premium_bowling_overall' );
}

if ( !wp_next_scheduled( 'vr_generate_mini_league_rankings_pb' ) ) {
	wp_schedule_event( strtotime('09:30:00'), 'daily', 'vr_generate_mini_league_rankings_pb' );
}

if ( !wp_next_scheduled( 'vr_generate_mini_league_players_rankings_pb' ) ) {
	wp_schedule_event( strtotime('09:35:00'), 'daily', 'vr_generate_mini_league_players_rankings_pb' );
}

/***GORILLA TAG*******************************************************************/

if ( !wp_next_scheduled( 'vr_generate_rankings_gt' ) ) {
	wp_schedule_event( strtotime('09:40:00'), 'daily', 'vr_generate_rankings_gt' );
}	

if ( !wp_next_scheduled( 'vr_generate_mini_league_rankings_gt' ) ) {
	wp_schedule_event( strtotime('09:45:00'), 'daily', 'vr_generate_mini_league_rankings_gt' );
}


/***NFT Medals**********************************************************************/

// Src: https://sebhastian.com/pass-javascript-variables-to-php/
add_shortcode( 'vr_connect_wallet', 'vr_connect_wallet' );	
function vr_connect_wallet( $atts ) {
	
	$user_id = get_current_user_id();

    //If user is not logged in...
    if ( $user_id == 0 ) {
        wp_redirect( wp_login_url() ); 
        exit();
    }
	
?>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/web3/1.7.4-rc.1/web3.min.js"></script>
	<script>
	
	/* To connect using MetaMask */
	async function connect() {
		
	  if (window.ethereum) {
		  
		 document.getElementById("connect-button").disabled = true;
		 document.getElementById("loading").innerHTML = '<img src="/wp-includes/images/spinner.gif">';
		  
		 await window.ethereum.request({ method: "eth_requestAccounts" });
		 window.web3 = new Web3(window.ethereum);
		 const account = web3.eth.accounts;
		 const walletAddress = await account.givenProvider.selectedAddress;	//Get the current MetaMask selected/active wallet
		 console.log(`Wallet: ${walletAddress}`);
		 
		 jQuery.ajax({
			type: "post",
			url: "<?php echo admin_url('admin-ajax.php'); ?>", // Using exiting AJAX function in WP. Must be enclosed with admin_url to work
			data: {
				action: 'vr_read_wallet',
				security: "<?php echo wp_create_nonce( 'connect-wallet-nonce' ); ?>",
				wallet: walletAddress,
				user_id: <?php echo $user_id; ?>
			},
			success: function(msg) {
				document.getElementById("loading").innerHTML = '';
				document.getElementById("connect-button").disabled = false;
				setTimeout( function ( ) { alert( msg.data ); }, 100 ); //displays msg in 0.1 seconds
			}
		});    
	  
	  } else {
		alert("<?php esc_html_e('No wallet detected. Please install MetaMask or Coinbase wallet', 'rife-free-child' ); ?>");
	  }
	}
	
	</script>

<?php
	return '<input type="submit" id="connect-button" value="' . esc_html__('Connect Wallet', 'rife-free-child') . '" class="um-button" onclick="connect();"><div id="loading"></div>';
}


// The function that handles the AJAX request
add_action( 'wp_ajax_vr_read_wallet', 'vr_read_wallet_callback' );
function vr_read_wallet_callback() {
	check_ajax_referer( 'connect-wallet-nonce', 'security' );
	
	$user_id = sanitize_text_field( $_POST['user_id'] );
	$wallet_address = sanitize_text_field( $_POST['wallet'] );
	
	update_user_meta( $user_id, 'wallet_address', $wallet_address );
	
	// So check wallet_address and make sure the stored value matches
	if ( $wallet_address == get_user_meta( $user_id,  'wallet_address', true ) ) {
		wp_send_json_success( esc_html__( 'Wallet address is saved', 'rife-free-child' ) );
		die(); // this is required to return a proper result
	} else {
		wp_send_json_error( esc_html__( 'Wallet address is not saved', 'rife-free-child' ) );
		wp_die( __( 'Wallet address is not saved', 'rife-free-child' ) );
	}
}


// To save user's wallet address as user meta data. Assuming user has a MetaMask wallet
// Src: https://codepen.io/dericksozo/post/fetch-api-json-php
/* add_shortcode( 'vr_read_wallet_address', 'vr_read_wallet_address' );	
function vr_read_wallet_address( $atts ) {
	
	$contentType = isset( $_SERVER["CONTENT_TYPE"] ) ? trim( $_SERVER["CONTENT_TYPE"] ) : '';

	if ( $contentType === "application/json" ) {
		
	  //Receive the RAW post data.
	  $content = trim( file_get_contents( "php://input" ) );

	  $data = json_decode( $content, true );

	  if ( is_array( $data ) ) {
		  
		  $user_id = sanitize_text_field( $data['user_id'] );
		  $wallet_address = sanitize_text_field( $data['wallet'] );
		 
		  $updated = update_user_meta( $user_id, 'wallet_address', $wallet_address, true );
		  
		  if ( $updated ) {
			$return = esc_html__( 'Wallet address saved', 'rife-free-child' );
			wp_send_json( $return, 200 );
		  }

	  } else {
			$return = esc_html__( 'Wallet not found', 'rife-free-child' );
			wp_send_json( $return, 400 );
	  }
	  
	}
} */


// add_action( 'gamipress_award_achievement', 'vr_transfer_nft_award_to_user', 10, 5 );
// function vr_transfer_nft_award_to_user( $user_id, $achievement_id, $trigger, $site_id, $args ) {
	
	// $token_id = get_post_meta( $achievement_id, '_gamipress_token_id', true );
	// $user_wallet_address = get_user_meta( $user_id, 'wallet_address', true );

	//DEBUG
	// error_log("NFT********************");
	// error_log("ID1: " . $achievement_id);
	// error_log("ID1: " . $token_id);

// }

//Mel: 29/01/22. To publish file after token is minted
add_action( 'acadp_order_completed', 'acadp_custom_order_completed' );
function acadp_custom_order_completed( $order_id ) {      
	$post_id = (int) get_post_meta( $order_id, 'listing_id', true );
   
	if ( $post_id > 0 ) {
		// Update post
		$post_array = array(
			'ID'          => $post_id,
			'post_status' => 'publish',
		);

		wp_update_post( $post_array );
	}
}


?>