<?php defined('ABSPATH') || exit;

if (class_exists('CSF')):

    $prefix = "cpmw_settings";

    CSF::createOptions($prefix, array(
        'framework_title' => esc_html__('Settings', 'cpmw'),
        'menu_title' => false,
        'menu_slug' => "cpmw-metamask-settings",
        'menu_capability' => 'manage_options',
        'menu_type' => 'submenu',
        'menu_parent' => 'woocommerce',
        'menu_position' => 103,
        'menu_hidden' => true,
        'nav' => 'inline',
        'show_bar_menu' => false,
        'show_sub_menu' => false,
        'show_reset_section' => false,
        'show_reset_all' => false,
        'theme' => 'light',

    ));

    CSF::createSection($prefix, array(

        'id' => 'general_options',
        'title' => esc_html__('General Options', 'cpmw'),
        'icon' => 'fa fa-cog',
        'fields' => array(

            array(
                'id' => 'user_wallet',
                'title' => __('Payment Address <span style="color:red">(Required)</span>', 'cpmwp'),
                'type' => 'text',
                'placeholder' => '0x1dCXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
                'validate' => 'csf_validate_required',
                'help' => esc_html__('Default wallet address to receive payments in metamask.', 'cpmwp'),
                'desc' => 'Default wallet address to receive payments in metamask.<br>
																				                                    <span style="color:red">You can use different payment addresses for different networks/chains in pro version.<a href="'.CPMW_BUY_PRO.'" target="_blank" > (Buy Pro) </a></span>',
            ),
            array(
                'id' => 'currency_conversion_api',
                'title' => esc_html__('Currency Conversion API', 'cpmwp'),
                'type' => 'select',
                'options' => array(
                    'cryptocompare' => __('CryptoCompare', 'cpmwp'),
                    'openexchangerates' => __('Binance + Openexchangerates', 'cpmwp'),
                ),
                'default' => 'openexchangerates',
                'desc' => 'It will convert product price from fiat currency to cryptocurrency in real time.<br>
																				                                    <span style="color:red">You can add custom price for a token or use Coinbrain api in pro version. <a href="'.CPMW_BUY_PRO.'" target="_blank"> (Buy Pro) </a></span>',
            ),
            array(
                'id' => 'crypto_compare_key',
                'title' => __('CryptoCompare API Key <span style="color:red">(Required)</span>', 'cpmwp'),
                'type' => 'text',
                'dependency' => array('currency_conversion_api', '==', 'cryptocompare'),
                'desc' => 'Get your free API key<a href="https://min-api.cryptocompare.com/documentation?key=Price&cat=SingleSymbolPriceEndpoint" target="_blank">Click Here</a>',
            ),
            array(
                'id' => 'openexchangerates_key',
                'title' => __('Openexchangerates API Key <span style="color:red">(Required)</span>', 'cpmwp'),
                'type' => 'text',
                'dependency' => array('currency_conversion_api', '==', 'openexchangerates'),
                'desc' => 'Get your free API key<a href="https://openexchangerates.org/account/app-ids" target="_blank"> Click Here</a>',

            ),
            array(
                'id' => 'Chain_network',
                'title' => esc_html__('Select MetaMask Network', 'cpmw'),
                'type' => 'select',
                'options' => array(
                    '0x1' => __('Ethereum Main Network (Mainnet)', 'cpmw'),
                    '0xaa36a7' => __('Ethereum-Sepolia (Test Network)', 'cpmw'),
                    '0x5' => __('GoerliETH Test Network (Testnet)', 'cpmw'),
                    '0x38' => __('Binance Smart Chain (Mainnet)', 'cpmw'),
                    '0x61' => __('Binance Smart Chain (Testnet)', 'cpmw'),
                ),
                'desc' => '<span style="color:red">You can add custom network/chain or select multiple networks/chains in pro version.<a href="'.CPMW_BUY_PRO.'" target="_blank"> (Buy Pro) </a></span>',
                'default' => '0x1',

            ),
            array(
                'id' => 'eth_select_currency',
                'title' => __('Select Crypto Currency <span style="color:red">(Required )</span>', 'cpmw'),
                'type' => 'select',
                'validate' => 'csf_validate_required',
                'placeholder' => 'Select Crypto currency',
                'options' => array(
                    'ETH' => __('Ethereum', 'cpmw'),
                ),
                'chosen' => true,
                'multiple' => true,
                'settings' => array('width' => '50%'),
                'dependency' => array('Chain_network', 'any', '0x1,0x5,0xaa36a7'),
                'desc' => '<span style="color:red">You can add any custom token/coin in pro version. <a href="'.CPMW_BUY_PRO.'" target="_blank"> (Buy Pro) </a></span>',
                'default' => 'ETH',

            ),
            array(
                'id' => 'bnb_select_currency',
                'title' => __('Select Crypto Currency <span style="color:red">(Required )</span>', 'cpmw'),
                'type' => 'select',
                'placeholder' => 'Select Crypto Currency',
                'validate' => 'csf_validate_required',
                'options' => array(
                    'BNB' => __('Binance Coin', 'cpmw'),
                    'BUSD' => __('BUSD', 'cpmw'),
                ),
                'chosen' => true,
                'multiple' => true,
                'settings' => array('width' => '50%'),
                'dependency' => array('Chain_network', 'any', '0x38,0x61'),
                'desc' => '<span style="color:red">You can add any custom token/coin in pro version. <a href="'.CPMW_BUY_PRO.'" target="_blank"> (Buy Pro) </a></span>',
                'default' => 'BNB',
            ),
            array(
                'id' => 'enable_refund',
                'title' => esc_html__('Enable Refund', 'cpmwp'),
                'type' => 'switcher',
                'text_on' => 'Enable',
                'text_off' => 'Disable',
                'text_width' => 80,
                'desc' => '<span style="color:red">A pro feature to refund customer via metamask from order page. <a href="'.CPMW_BUY_PRO.'" target="_blank"> (Buy Pro) </a></span>',
                'help' => esc_html__('Enable refund option', 'cpmwp'),
                'default' => true,
            ),

            array(
                'id' => 'confirm_msg',
                'title' => esc_html__('Payment Confirmation', 'cpmwp'),
                'type' => 'text',
                'desc' => 'Custom message to show  while confirming payment inside metamask wallet.',
                'default' => 'Confirm payment in your wallet.',
            ),
            array(
                'id' => 'payment_process_msg',
                'title' => esc_html__('Payment Processing', 'cpmwp'),
                'type' => 'text',
                'desc' => 'Custom message to show  while processing payment via blockchain.',
                'default' => 'Payment in process.',
            ),
            array(
                'id' => 'rejected_message',
                'title' => esc_html__('Payment Rejected', 'cpmwp'),
                'type' => 'text',
                'desc' => 'Custom message to show  if you rejected payment via metamask.',
                'default' => 'Transaction rejected. ',
            ),
            array(
                'id' => 'payment_msg',
                'title' => esc_html__('Payment Success', 'cpmwp'),
                'type' => 'text',
                'default' => 'Payment completed successfully.',
                'desc' => 'Custom message to show  if  payment confirm  by blockchain.',

            ),
            array(
                'id' => 'payment_status',
                'title' => esc_html__('Payment Success Status', 'cpmwp'),
                'type' => 'select',
                'options' => apply_filters(
                    'cpmwp_settings_order_statuses',
                    array(
                        'default' => __('Woocommerce Default Status', 'cpmwp'),
                        'on-hold' => __('On Hold', 'cpmwp'),
                        'processing' => __('Processing', 'cpmwp'),
                        'completed' => __('Completed', 'cpmwp'),
                    )
                ),
                'desc' => __('Order status on successful payment via metamask.', 'cpmwp'),
                'default' => 'default',
            ),

            array(
                'id' => 'redirect_page',
                'title' => esc_html__('Redirect Success Order', 'cpmwp'),
                'type' => 'text',
                'placeholder' => 'https://coolplugins.net/my-account/orders/',
                'desc' => 'Enter custom url to redirect or leave blank to update order status on same page.',
            ),

        ),
    ));
    CSF::createSection(
        $prefix,
        array(
            'title' => 'Wallets',
            'icon' => 'fas fa-wallet',
            'fields' => array(
                array(
                    'id' => 'supported_wallets',
                    'title' => 'Supported Wallets<strong style="color:red">(Pro only)</strong>',
                    'type' => 'fieldset',
                    'fields' => array(
                        array(
                            'type' => 'content',
                            'content' => '<a href="'.CPMW_BUY_PRO.'" target="_blan" ><img src="' . CPMW_URL . '/assets/images/wallets-promotion.png"></a>',
                        ),

                    ),

                ),

            ),

        )
    );

    CSF::createSection(
        $prefix,
        array(
            'title' => 'Networks/Chains',
            'icon' => 'fas fa-network-wired',
            'fields' => array(
                array(
                    'type' => 'content',
                    'content' => ' <center><h2 style="color:red"><a href="'.CPMW_BUY_PRO.'" target="_blan" >Buy Pro</a> Version To Activate Below Features</h2></center>',
                ),
                array(
                    'type' => 'content',
                    'content' => '<a href="'.CPMW_BUY_PRO.'" target="_blan" ><img src="' . CPMW_URL . '/assets/images/promotion.png"></a>',
                ),
            ),
        ));
    CSF::createSection(
    $prefix,
    array(
        'title' => 'Login With Crypto Wallets',
        'icon' => 'fas fa-key',

                'fields' => array(
                 array(
                    'type' => 'content',
                    'content' => ' <center><h2 style="color:red"><a href="'.CPMW_BUY_PRO.'" target="_blan" >Buy Pro</a> Version To Activate Below Features</h2></center>',
                ),
                 array(
                    'type' => 'content',
                    'content' => '<a href="'.CPMW_BUY_PRO.'" target="_blan" ><img src="' . CPMW_URL . '/assets/images/wallet-login.png"></a>',
                ),

            ),
        ));

    CSF::createSection($prefix, array(
        'title' => 'Free Test Tokens',
        'icon' => 'fas fa-rocket',
        'fields' => array(
            array(
                'type' => 'heading',
                'content' => 'Get Free Test Tokens to Test Payment via Metamask on Test Networks/Chains.',
            ),
            array(
                'type'    => 'subheading',
                'content' => ' ETH Test Token For Sepolia Network: <a href="https://sepoliafaucet.com/" target="_blank">https://sepoliafaucet.com</a>',
            ),
            array(
                'type' => 'subheading',
                'content' => ' ETH Test Token For Goerli Network: <a href="https://goerlifaucet.com/" target="_blank">https://goerlifaucet.com/</a>',
            ),
            array(
                'type' => 'subheading',
                'content' => 'Binance Test Tokens For Binance Network: <a href="https://testnet.binance.org/faucet-smart" target="_blank">https://testnet.binance.org/faucet-smart</a>',
            ),

        ),

    ));
    CSF::createSection($prefix, array(
        'title' => 'Buy Pro',
        'icon' => 'fas fa-shopping-cart',
        'fields' => array(
            array(
                'type' => 'content',
                'content' => '<a href="'.CPMW_BUY_PRO.'" target="_blan" ><img src="' . CPMW_URL . '/assets/images/pro-features.png"></a><br><center><h1> <a class="button button-primary" href="'.CPMW_BUY_PRO.'" target="_blan">Buy Pro</a> <a class="button button-primary" href="' . CPMW_DEMO_URL . '" target="_blan"> Demo </a> <a class="button button-secondary" href="https://docs.coolplugins.net/docs/pay-with-metamask-for-woo-commerce-pro/" target="_blan">Docs</a></h1></center>',
            ),

        ),

    ));

endif;
