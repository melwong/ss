<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get plugin options
$options = get_option('cpmw_settings');

// Get supported network names
$network_name = $this->cpmw_supported_networks();

// Get selected network
$get_network = $options["Chain_network"];

// Get constant messages
$const_msg = $this->cpmw_const_messages();

// Enqueue necessary styles
wp_enqueue_style('ca-loader-css', CPMW_URL . 'assets/css/cpmw.css');

// Determine crypto currency based on network
$crypto_currency = ($get_network == '0x1' || $get_network == '0x5' || $get_network == '0xaa36a7') ?
    $options["eth_select_currency"] : $options["bnb_select_currency"];

// Get type and total price
$type = $options['currency_conversion_api'];
$total_price = $this->get_order_total();

// Initialize variables
$metamask = "";
$inc = 1;

// Trigger WooCommerce action to start the form
do_action('woocommerce_cpmw_form_start', $this->id);
?>

<div class="form-row form-row-wide">
    <p>
        <?php
        // Get Metamask settings link
        $cpmw_settings = admin_url() . 'admin.php?page=cpmw-metamask-settings';

        // Get user wallet settings
        $user_wallet = $options['user_wallet'];

        // Get currency options
        $bnb_currency = $options['bnb_select_currency'];
        $eth_currency = $options['eth_select_currency'];

        // Get currency conversion API options
        $compare_key = $options['crypto_compare_key'];
        $openex_key = $options['openexchangerates_key'];
        $select_currecny = $options['currency_conversion_api'];

        // Generate settings link HTML for admin
        $link_html = (current_user_can('manage_options')) ?
            '<a href="' . esc_url($cpmw_settings) . '" target="_blank">' .
            __("Click here", "cpmw") . '</a>' . __('to open settings', 'cpmw') : "";

        // Check for various conditions
        if (empty($user_wallet)) {
            echo '<strong>' . esc_html($const_msg['metamask_address']) . wp_kses_post($link_html) . '</strong>';
            return false;
        }
        if (!empty($user_wallet) && strlen($user_wallet) != "42") {
            echo '<strong>' . esc_html($const_msg['valid_wallet_address']) . wp_kses_post($link_html) . '</strong>';
            return false;
        }
        if ($select_currecny == "cryptocompare" && empty($compare_key)) {
            echo '<strong>' . esc_html($const_msg['required_fiat_key']) . wp_kses_post($link_html) . '</strong>';
            return false;
        }
        if ($select_currecny == "openexchangerates" && empty($openex_key)) {
            echo '<strong>' . esc_html($const_msg['required_fiat_key']) . wp_kses_post($link_html) . '</strong>';
            return false;
        }
        if (empty($bnb_currency) || empty($eth_currency)) {
            echo '<strong>' . esc_html($const_msg['required_currency']) . wp_kses_post($link_html) . '</strong>';
            return false;
        }

        // Display selected network
        echo ' <label class="cpmw_selected_network">' . esc_html($network_name[$get_network]) . '</label>';

        if (is_array($crypto_currency)) {
            foreach ($crypto_currency as $key => $value) {
                // Get coin logo image URL
                $image_url = $this->cpmw_get_coin_logo($value);

                // Perform price conversion
                $in_busd = $this->cpmw_price_conversion($total_price, $value, $type);

                // Check conversion result and display relevant content
                if (!empty($in_busd) && $in_busd != "error" && !is_array($in_busd)) {
                    ?>
                    <div class="cpmw-pymentfield">
                        <input class="cpmw_payment_method" type="radio" class="input-radio"
                               name="cpmw_crypto_coin" value="<?php echo !empty($in_busd) ? esc_attr($value) : ""; ?>"
                            <?php echo ($key == '0') ? 'checked' : ""; ?> />
                        <img src="<?php echo esc_url($image_url); ?>"/>
                        <span><?php echo esc_html($value) ?></span>
                        <p class="cpmw_crypto_price"><?php echo esc_html($in_busd . $value) ?></p>
                    </div>
                    <?php
                } else {
                    if ($inc == 1 && $in_busd == "error") {
                        echo '<strong>' . esc_html($const_msg['valid_fiat_key']) . wp_kses_post($link_html) . '</strong>';
                    } else if (is_array($in_busd)) {
                        echo '<strong>' . wp_kses_post($in_busd['restricted']) . '</strong>';
                    }
                    $inc++;
                    ?>
                    <input id="invalid_app_id" type="hidden" name="invalid_app_id"
                           value="<?php echo esc_attr($in_busd); ?>"/>
                    <?php
                }
            }
        }
        ?>
    </p>
</div>

<?php
// Trigger WooCommerce action to end the form
do_action('woocommerce_cpmw_form_end', $this->id);
?>
