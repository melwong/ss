<?php
if (!defined('ABSPATH')) {
    exit;
}
// Get constant messages
$const_msg = $this->cpmw_const_messages();

// Get plugin options
$options = get_option('cpmw_settings');

// Get supported network names
$network_name = $this->cpmw_supported_networks();

// Default messages
$payment_msg = !empty($options['payment_msg']) ? $options['payment_msg'] : __("Payment Completed Successfully", "cpmw");
$confirm_msg = !empty($options['confirm_msg']) ? $options['confirm_msg'] : __("Confirm Payment in your wallet", "cpmw");
$process_msg = !empty($options['payment_process_msg']) ? $options['payment_process_msg'] : __("Payment in process", "cpmw");
$rejected_msg = !empty($options['rejected_message']) ? $options['rejected_message'] : __("Transaction Rejected ", "cpmw");

// Get network and redirect options
$network = !empty($options['Chain_network']) ? $options['Chain_network'] : "";
$redirect = !empty($options['redirect_page']) ? $options['redirect_page'] : "";

// Determine crypto currency based on network
$crypto_currency = ($network == '0x1' || $network == '0x5') ? $options["eth_select_currency"] : $options["bnb_select_currency"];

// Get order details
$order = new WC_Order($order_id);
$total = $order->get_total();
$nonce = wp_create_nonce('cpmw_metamask_pay'.$order_id);
$user_wallet = $order->get_meta('cpmw_user_wallet');
$in_crypto = $order->get_meta('cpmw_in_crypto');
$currency_symbol = $order->get_meta('cpmw_currency_symbol');
$payment_status = $order->get_status();

// Get additional network and token information
$add_networks = $this->cpmw_add_networks();
$add_networks = isset($add_networks[$network]) ? json_encode($add_networks[$network]) : '';
$add_tokens = $this->cpmw_add_tokens();
$token_address = isset($add_tokens[$network][$currency_symbol]) ? $add_tokens[$network][$currency_symbol] : '';
$transaction_id = (!empty($order->get_meta('TransactionId'))) ? $order->get_meta('TransactionId') : "";
$shop_page_url = get_permalink(wc_get_page_id('shop'));
$sig_token_address = $order->get_meta('cpmwp_contract_address');

// Generate signature for transaction request
$secret_key = $this->cpmw_get_secret_key();
$tx_req_data = json_encode(
    array(
        'order_id' => $order_id,
        'selected_network' => $network,
        'receiver' => strtoupper($user_wallet),
        'amount' => str_replace(',', '', $in_crypto),
        'token_address' => strtoupper($sig_token_address)
    )
);
$signature = hash_hmac('sha256', $tx_req_data, $secret_key);
//Enqueue required scrips
wp_enqueue_script('cpmw-sweet-alert2', CPMW_URL . 'assets/js/sweetalert2.js', array('jquery'), CPMW_VERSION, true);
wp_enqueue_script('cpmw-ether', CPMW_URL . 'assets/js/ethers-5.2.umd.min.js', array('jquery'), CPMW_VERSION, true);
wp_enqueue_script('cpmw_custom', CPMW_URL . 'assets/js/cpmw-main.min.js', array('jquery', 'cpmw-sweet-alert2'), CPMW_VERSION, true);
wp_localize_script('cpmw_custom', "extradata",
    array(
        'url' => CPMW_URL,
        'supported_networks' => $network_name,
        'network_name' => $network_name[$network],
        'token_address' => $token_address,
        'network_data' => $add_networks,
        'transaction_id' => $transaction_id,
        'const_msg' => $const_msg,
        'redirect' => $redirect,
        'order_page' => get_home_url() . '/my-account/orders/',
        'currency_symbol' => $currency_symbol,
        'confirm_msg' => $confirm_msg,
        'network' => $network,
        'is_paid' => $order->is_paid(),
        'process_msg' => $process_msg,
        'payment_msg' => $payment_msg,
        'rejected_msg' => $rejected_msg,
        'in_crypto' =>str_replace(',', '', $in_crypto),
        'receiver' => $user_wallet,
        'ajax' => admin_url( 'admin-ajax.php' ),
        'order_status' => $payment_status,
        'id' => $order_id,
        'nonce' => $nonce,
        'payment_status' => $options['payment_status'],
        'shop_page'=>$shop_page_url,
        'signature'          => $signature
    ));
wp_enqueue_style('cpmw_custom_css', CPMW_URL . 'assets/css/cpmw.css', array(), CPMW_VERSION, null, 'all');

$trasn_id=$order->get_meta('TransactionId');
$link_hash = "--";

if (!empty($trasn_id) && $trasn_id != "false") {
    $networkToLink = array(
        '0x61' => 'testnet.bscscan.com',
        '0x38' => 'bscscan.com',
        '0x1' => 'etherscan.io',
        '0x5' => 'goerli.etherscan.io',
        '0xaa36a7' => 'sepolia.etherscan.io',
    );
    $networkDomain = isset($networkToLink[$network]) ? $networkToLink[$network] : '';
    if (!empty($networkDomain)) {
        $link_hash = '<a href="https://' . $networkDomain . '/tx/' . $trasn_id . '" target="_blank">' . $trasn_id . '</a>';
    }
}

?>
        <div class="cpmw_loader_wrap">

        <div class="cpmw_loader">
            <img src="<?php echo esc_url(CPMW_URL . '/assets/images/metamask.png') ?>" alt="metamask" >
            <h2><?php echo esc_html($confirm_msg) ?></h2>
            </div>
        </div>
       <div class="cmpw_meta_connect">
           <div class="wallet-icon" >
               <img src="<?php echo esc_url(CPMW_URL . '/assets/images/metamask.png') ?>" alt="metamask" >
            </div>

            <div class="connect-wallet" >
                <div class="cpmw_connect_btn">
                    <button class="confirm-btn button" > <?php echo esc_html($const_msg['metamask_connect']); ?></button>
                </div>
            </div>
        </div>

       <div class="cmpw_meta_wrapper" >
           <div class="container" >
               <div class="cpmw-pay-wallet-icon" >
               <img src="<?php echo esc_url(CPMW_URL . '/assets/images/metamask.png') ?>" alt="MetaMask" >
            </div>
               <div class="cpmw-info" >
                   <div class="connected-wallet" ><span ><?php echo esc_html($const_msg['connnected_wallet']); ?> </span><?php echo esc_html($const_msg['metamask']); ?></div>
                   <div class="active-chain" ><span ><?php echo esc_html($const_msg['active_chain']); ?> </span><p class="cpmw_active_chain"> </p></div>
                </div>
                <div class="cpmw-info" >
                    <div class="connected-account" ><span ><?php echo esc_html($const_msg['connected_account']); ?></span>
                    <div class="account-address" ></div>
                    </div>
                    <div class="order-price" ><span ><?php echo esc_html($const_msg['order_price']); ?> </span><?php echo esc_html(get_woocommerce_currency_symbol() . $total) ?></div>
                </div>
                 <div class="clear" ></div>
                <div class="pay-btn-wrapper" ><button class="confirm-btn button" > <?php echo esc_html($const_msg['pay_with'] . $in_crypto . $currency_symbol) ?></button></div>

            </div>
        </div>

<section class="cpmw-woocommerce-woocommerce-order-details">
    <h2 class="woocommerce-order-details__title"><?php echo __('Crypto payment details','cpmw') ?></h2>
    <table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
        <tbody>
            <tr>
                <th scope="row">   <?php echo __('Price:', 'cpmw') ?></th>
                <td><?php echo esc_html($in_crypto . $currency_symbol) ?></td>
            </tr>
            <tr>
                <th scope="row"> <?php echo __('Payment Status','cpmw') ?></th>
                <td><?php echo $order->get_status(); ?></td>
            </tr>
            <?php
             if (!empty($trasn_id)&& $trasn_id != "false") {
            ?>
             <tr>
                <th scope="row"> <?php echo __('Transaction id:', 'cpmw') ?></th>
                <td><?php echo wp_kses_post( $link_hash )?></td>
            </tr>
            <?php
             }
            ?>

        </tbody>
    </table>
</section>

        <?php
