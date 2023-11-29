<?php
if (!defined('ABSPATH')) {
    exit();
}

trait CPMW_HELPER
{
    public function __construct()
    {

    }
    // Generate a dynamic secret key for hash_hmac
	protected function cpmw_get_secret_key() {
		if ( get_option( 'cpmwp_secret_key' ) == false ) {
			update_option( 'cpmwp_secret_key', wp_generate_password( 4, true, true ) );
		}
		return get_option( 'cpmwp_secret_key' );
	}

    //Price conversion API start

    protected function cpmw_price_conversion($total, $crypto, $type)
    {
        global $woocommerce;
        $lastprice = "";
        $currency = get_woocommerce_currency();
        $settings_obj = get_option('cpmw_settings');

        if ($type == "cryptocompare") {
            $api = !empty($settings_obj['crypto_compare_key']) ? $settings_obj['crypto_compare_key'] : "";
            if (empty($api)) {
                return "no_key";
            }
            $current_price = CPMW_API_DATA::cpmw_crypto_compare_api($currency, $crypto);
            $current_price_array = (array) $current_price;

            if (isset($current_price_array['Response'])) {
                return;
            }
            if(isset($current_price_array[$crypto])){
            $in_crypto = !empty(($current_price_array[$crypto]) * $total) ? ($current_price_array[$crypto]) * $total : "";
            return $this->cpmw_format_number($in_crypto);
            }
           

        } else {
            $price_list = CPMW_API_DATA::cpmw_openexchangerates_api();
            if (isset($price_list->error)) {
                return 'error';
            }

            $price_arryay = (array) $price_list->rates;
            $current_rate = $price_arryay[$currency];
            if ($crypto == "USDT") {
                $current_price_USDT = CPMW_API_DATA::cpmw_crypto_compare_api($currency, $crypto);
                $current_price_array_USDT = (array) $current_price_USDT;
                if (isset($current_price_array_USDT['Response'])) {
                    return;
                }
                if(isset($current_price_array_USDT[$crypto])){
                $in_crypto_USDT = !empty(($current_price_array_USDT[$crypto]) * $total) ? ($current_price_array_USDT[$crypto]) * $total : "";
                return $in_crypto_USDT;
                }
            } else {
                $binance_price = CPMW_API_DATA::cpmw_binance_price_api('' . $crypto . 'USDT');
                if(isset( $binance_price->lastPrice)){
                $lastprice = $binance_price->lastPrice;
                $cal = (!empty($price_arryay) && !empty($current_rate)) ? ($total / $current_rate) / $lastprice : "";
                return $this->cpmw_format_number($cal);
                }
                else{
                    if(current_user_can( 'manage_options' )){
                    return isset($binance_price->msg)?array('restricted'=>__("Binance API Is Restricted In Your region, Please Switch With CryptoCompare API.","cpmw")):"error";
                    }
                }
            }
        }
    }

    protected function cpmw_format_number($n)
    {
        if (is_numeric($n)) {
            if ($n >= 25) {
                return $formatted = number_format($n, 2, '.', ',');
            } else if ($n >= 0.50 && $n < 25) {
                return $formatted = number_format($n, 3, '.', ',');
            } else if ($n >= 0.01 && $n < 0.50) {
                return $formatted = number_format($n, 4, '.', ',');
            } else if ($n >= 0.001 && $n < 0.01) {
                return $formatted = number_format($n, 5, '.', ',');
            } else if ($n >= 0.0001 && $n < 0.001) {
                return $formatted = number_format($n, 6, '.', ',');
            } else {
                return $formatted = number_format($n, 8, '.', ',');
            }
        }
    }

//Price conversion API end here

    protected function cpmw_supported_currency()
    {
        $oe_currency = array("AED", "AFN", "ALL", "AMD", "ANG", "AOA", "ARS", "AUD", "AWG", "AZN", "BAM", "BBD", "BDT", "BGN", "BHD", "BIF", "BMD", "BND", "BOB", "BRL", "BSD", "BTC", "BTN", "BWP", "BYN", "BZD", "CAD", "CDF", "CHF", "CLF", "CLP", "CNH", "CNY", "COP", "CRC", "CUC", "CUP", "CVE", "CZK", "DJF", "DKK", "DOP", "DZD", "EGP", "ERN", "ETB", "EUR", "FJD", "FKP", "GBP", "GEL", "GGP", "GHS", "GIP", "GMD", "GNF", "GTQ", "GYD", "HKD", "HNL", "HRK", "HTG", "HUF", "IDR", "ILS", "IMP", "INR", "IQD", "IRR", "ISK", "JEP", "JMD", "JOD", "JPY", "KES", "KGS", "KHR", "KMF", "KPW", "KRW", "KWD", "KYD", "KZT", "LAK", "LBP", "LKR", "LRD", "LSL", "LYD", "MAD", "MDL", "MGA", "MKD", "MMK", "MNT", "MOP", "MRO", "MRU", "MUR", "MVR", "MWK", "MXN", "MYR", "MZN", "NAD", "NGN", "NIO", "NOK", "NPR", "NZD", "OMR", "PAB", "PEN", "PGK", "PHP", "PKR", "PLN", "PYG", "QAR", "RON", "RSD", "RUB", "RWF", "SAR", "SBD", "SCR", "SDG", "SEK", "SGD", "SHP", "SLL", "SOS", "SRD", "SSP", "STD", "STN", "SVC", "SYP", "SZL", "THB", "TJS", "TMT", "TND", "TOP", "TRY", "TTD", "TWD", "TZS", "UAH", "UGX", "USD", "UYU", "UZS", "VES", "VND", "VUV", "WST", "XAF", "XAG", "XAU", "XCD", "XDR", "XOF", "XPD", "XPF", "XPT", "YER", "ZAR", "ZMW", "ZWL");
        return $oe_currency;
    }

//Add blockchain networks
    protected function cpmw_add_networks()
    {
        $data['0x38'] = [
            'chainId' => '0x38',
            'chainName' => 'Binance Smart Chain',
            'nativeCurrency' => array(
                'name' => 'BNB',
                'symbol' => 'BNB',
                'decimals' => 18,
            ),
            'rpcUrls' => ['https://bsc-dataseed.binance.org/'],
            'blockExplorerUrls' => ['https://bscscan.com/'],
        ];
        $data['0x61'] = [
            'chainId' => '0x61',
            'chainName' => 'Binance Smart Chain Testnet',
            'nativeCurrency' => array(
                'name' => 'BNB',
                'symbol' => 'BNB',
                'decimals' => 18,
            ),
            'rpcUrls' => ['https://data-seed-prebsc-1-s1.binance.org:8545/'],
            'blockExplorerUrls' => ['https://testnet.bscscan.com'],
        ];

        return $data;
    }
//Add custom tokens for networks
    protected function cpmw_add_tokens()
    {
        $tokens = [];

        $tokens['0x1'] = array(
            'USDT' => '0xdac17f958d2ee523a2206206994597c13d831ec7',
        );
        $tokens['0x38'] = array(
            'BUSD' => '0xe9e7cea3dedca5984780bafc599bd69add087d56',
        );
        $tokens['0x61'] = array(
            'BUSD' => '0xeD24FC36d5Ee211Ea25A80239Fb8C4Cfd80f12Ee',
        );

        return $tokens;

    }

    //Add network names here
    protected function cpmw_supported_networks()
    {
        $networks = [];
        $networks = array(
            '0x1' => 'Ethereum Main Network(Mainnet)',
            '0x5' => 'GoerliETH Test Network',
            '0xaa36a7' =>'Ethereum-Sepolia Test Network',
            '0x38' => 'Binance Smart Chain (Mainnet)',
            '0x61' => 'Binance Smart Chain (Testnet)',
        );

        return $networks;

    }

    //Add all constant messages
    protected function cpmw_const_messages()
    {
        $messages = "";

        $messages = array(
            //Checkout&validate fields static messages start here
            'metamask_address' => __("Please enter your MetaMask Payment address", "cpmw"),
            'valid_wallet_address' => __("Please enter valid MetaMask Payment address", "cpmw"),
            'required_fiat_key' => __("Please enter price conversion API key", "cpmw"),
            'valid_fiat_key' => __("Please enter valid price conversion API key", "cpmw"),
            'required_currency' => __("Please select a currency", "cpmw"),
            //Checkout&validate fields static messages end here
            //Process order fields static messages start here
            'notice_msg' => __("Note:Please do not edit Gas fee untill transaction process completed", "cpmw"),
            'processing' => __("Processing", "cpmw"),
            'insufficient_balance' => __("Insufficient Balance:", "cpmw"),
            'metamask_connect' => __('MetaMask Connect', 'cpmw'),
            'connnected_wallet' => __('Connected wallet:', 'cpmw'),
            'metamask' => __('MetaMask', 'cpmw'),
            'active_chain' => __('Active chain:', 'cpmw'),
            'connected_account' => __('Connected account: ', 'cpmw'),
            'order_price' => __('Order price: ', 'cpmw'),
            'pay_with' => __('Pay with ', 'cpmw'),
            'connection_establish' => __('Please wait while connection established', 'cpmw'),
            'required_network' => __('Currently you have not selected the required network', 'cpmw'),
            'switch_network' => __('Click ok to switch the network', 'cpmw'),
            'confirm_order' => __('Confirm Order Payment', 'cpmw'),
            'ext_not_detected' => __('MetaMask Wallet extention not detected !', 'cpmw'),
            //Process order fields static messages end here
        );
        return $messages;

    }

    //Add network names here
    protected function cpmw_get_coin_logo($value)
    {
        $coin_svg = CPMW_PATH . 'assets/images/' . $value . '.svg';
        $coin_png = CPMW_PATH . 'assets/images/' . $value . '.png';
        $coin_svg_img = CPMW_URL . 'assets/images/' . $value . '.svg';
        $coin_png_img = CPMW_URL . 'assets/images/' . $value . '.png';
        $image_url = "";

        if (file_exists($coin_svg)) {

            $image_url = $coin_svg_img;

        } else if (file_exists($coin_png)) {
            $image_url = $coin_png_img;
        } else {
            $image_url = CPMW_URL . 'assets/images/default-logo.png';
        }
        return $image_url;

    }

    /**
     * Generate secret code for transaction
     */
    protected function cpmw_generate_secretcode($orderId)
    {
        return hash_hmac('ripemd160', $orderId, time());
    }

}
