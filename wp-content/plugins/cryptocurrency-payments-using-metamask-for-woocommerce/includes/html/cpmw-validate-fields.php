<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get constant messages
$const_msg = $this->cpmw_const_messages();

// Get plugin options
$options = get_option('cpmw_settings');

// Get Metamask settings link
$cpmw_settings = admin_url() . 'admin.php?page=cpmw-metamask-settings';

// Get user wallet settings
$user_wallet = $options['user_wallet'];

// Get currency conversion API options
$compare_key = $options['crypto_compare_key'];
$openex_key = $options['openexchangerates_key'];
$select_currecny = $options['currency_conversion_api'];

// Generate settings link HTML for admin
$link_html = (current_user_can('manage_options')) ?
    '.<a href="' . esc_url($cpmw_settings) . '" target="_blank">' .
    __("Click here", "cpmw") . '</a>' . __('to open settings', 'cpmw') : "";

// Check for various conditions and add WooCommerce notices
if (empty($user_wallet)) {
    wc_add_notice('<strong>' . esc_html($const_msg['metamask_address']) . wp_kses_post($link_html) . '</strong>', 'error');
    return false;
}
if (!empty($user_wallet) && strlen($user_wallet) != "42") {
    wc_add_notice('<strong>' . esc_html($const_msg['valid_wallet_address']) . wp_kses_post($link_html) . '</strong>', 'error');
    return false;
}

if ($select_currecny == "cryptocompare" && empty($compare_key)) {
    wc_add_notice('<strong>' . esc_html($const_msg['required_fiat_key']) . wp_kses_post($link_html) . '</strong>', 'error');
    return false;
}
if ($select_currecny == "openexchangerates" && empty($openex_key)) {
    wc_add_notice('<strong>' . esc_html($const_msg['required_fiat_key']) . wp_kses_post($link_html) . '</strong>', 'error');
    return false;
}
if (empty($_POST['cpmw_crypto_coin'])) {
    wc_add_notice('<strong>' . esc_html($const_msg['required_currency']) . wp_kses_post($link_html) . '</strong>', 'error');
    return false;
}

// If all checks pass, return true
return true;
?>
