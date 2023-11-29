<?php

/**
 * Plugin Name: EthPress
 * Plugin URI: https://wordpress.org/plugins/ethpress/
 * Description: Ethereum Web3 login. Enable crypto wallet logins to WordPress.
 * Author: Lynn (lynn.mvp at tutanota dot com), ethereumicoio
 * Version: 2.2.0
 * Author URI: https://ethereumico.io
 * Text Domain: ethpress
 * Domain Path: /languages
 *
 * @package ethpress
 */
namespace losnappas\Ethpress;

defined( 'ABSPATH' ) || die;

if ( function_exists( '\\losnappas\\Ethpress\\ethpress_fs' ) ) {
    \losnappas\Ethpress\ethpress_fs()->set_basename( false, __FILE__ );
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    
    if ( !function_exists( 'ethpress_fs' ) ) {
        // Create a helper function for easy SDK access.
        function ethpress_fs()
        {
            global  $ethpress_fs ;
            
            if ( !isset( $ethpress_fs ) ) {
                // Activate multisite network integration.
                if ( !defined( 'WP_FS__PRODUCT_9248_MULTISITE' ) ) {
                    define( 'WP_FS__PRODUCT_9248_MULTISITE', true );
                }
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $ethpress_fs = fs_dynamic_init( array(
                    'id'              => '9248',
                    'slug'            => 'ethpress',
                    'type'            => 'plugin',
                    'public_key'      => 'pk_45cc0f7a099a59d2117d9fb313d01',
                    'is_premium'      => false,
                    'premium_suffix'  => 'Professional',
                    'has_addons'      => true,
                    'has_paid_plans'  => true,
                    'trial'           => array(
                    'days'               => 7,
                    'is_require_payment' => true,
                ),
                    'has_affiliation' => 'all',
                    'menu'            => array(
                    'slug'   => 'ethpress',
                    'parent' => array(
                    'slug' => 'options-general.php',
                ),
                ),
                    'is_live'         => true,
                ) );
            }
            
            return $ethpress_fs;
        }
        
        // Init Freemius.
        \losnappas\Ethpress\ethpress_fs();
        // Signal that SDK was initiated.
        do_action( 'ethpress_fs_loaded' );
    }
    
    // ... Your plugin's main file logic ...
    require_once 'vendor/autoload.php';
    // use losnappas\Ethpress\Plugin;
    define( 'ETHPRESS_FILE', __FILE__ );
    define( 'ETHPRESS_NS', __NAMESPACE__ );
    define( 'ETHPRESS_PHP_MIN_VER', '5.4.0' );
    define( 'ETHPRESS_WP_MIN_VER', '4.6.0' );
    
    if ( version_compare( get_bloginfo( 'version' ), ETHPRESS_WP_MIN_VER, '<' ) || version_compare( PHP_VERSION, ETHPRESS_PHP_MIN_VER, '<' ) ) {
        /**
         * Displays notification.
         */
        function ethpress_compatability_warning()
        {
            echo  '<div class="error"><p>' . esc_html( sprintf(
                /* translators: version numbers. */
                __( '“%1$s” requires PHP %2$s (or newer) and WordPress %3$s (or newer) to function properly. Your site is using PHP %4$s and WordPress %5$s. Please upgrade. The plugin has been automatically deactivated.', 'ethpress' ),
                'EthPress',
                ETHPRESS_PHP_MIN_VER,
                ETHPRESS_WP_MIN_VER,
                PHP_VERSION,
                $GLOBALS['wp_version']
            ) ) . '</p></div>' ;
            // phpcs:ignore -- no nonces here.
            if ( isset( $_GET['activate'] ) ) {
                // phpcs:ignore -- no nonces here.
                unset( $_GET['activate'] );
            }
        }
        
        add_action( 'admin_notices', ETHPRESS_NS . '\\ethpress_compatability_warning' );
        /**
         * Deactivates.
         */
        function ethpress_deactivate_self()
        {
            deactivate_plugins( plugin_basename( ETHPRESS_FILE ) );
        }
        
        add_action( 'admin_init', ETHPRESS_NS . '\\ethpress_deactivate_self' );
        return;
    } else {
        function ethpress_fs_uninstall_cleanup()
        {
            \losnappas\Ethpress\Plugin::uninstall();
        }
        
        // Not like register_uninstall_hook(), you do NOT have to use a static function.
        \losnappas\Ethpress\ethpress_fs()->add_action( 'after_uninstall', ETHPRESS_NS . '\\ethpress_fs_uninstall_cleanup' );
        register_activation_hook( __FILE__, [ ETHPRESS_NS . '\\Plugin', 'activate' ] );
        function ethpress_custom_addons_pricing()
        {
            global  $ethpress_fs ;
            $is_whitelabeled = $ethpress_fs->is_whitelabeled();
            $slug = $ethpress_fs->get_slug();
            /**
             * @var \FS_Plugin[]
             */
            $addons = $ethpress_fs->get_addons();
            if ( $addons ) {
                foreach ( $addons as $addon ) {
                    // $basename = $ethpress_fs->get_addon_basename($addon->id);
                    $addon_id = $addon->id;
                    $addon_slug = $addon->slug;
                    $plans_and_pricing_by_addon_id = $ethpress_fs->_get_addons_plans_and_pricing_map_by_id();
                    $price = 0;
                    $has_trial = false;
                    $has_free_plan = false;
                    $has_paid_plan = false;
                    
                    if ( isset( $plans_and_pricing_by_addon_id[$addon_id] ) ) {
                        $plans = $plans_and_pricing_by_addon_id[$addon_id];
                        
                        if ( is_array( $plans ) && 0 < count( $plans ) ) {
                            $min_price = 999999;
                            foreach ( $plans as $plan ) {
                                
                                if ( !isset( $plan->pricing ) || !is_array( $plan->pricing ) || 0 == count( $plan->pricing ) ) {
                                    // No pricing means a free plan.
                                    $has_free_plan = true;
                                    continue;
                                }
                                
                                $has_paid_plan = true;
                                $has_trial = $has_trial || is_numeric( $plan->trial_period ) && $plan->trial_period > 0;
                                foreach ( $plan->pricing as $pricing ) {
                                    $pricing = new \FS_Pricing( $pricing );
                                    if ( !$pricing->is_usd() ) {
                                        /**
                                         * Skip non-USD pricing.
                                         *
                                         * @author Leo Fajardo (@leorw)
                                         * @since 2.3.1
                                         */
                                        continue;
                                    }
                                    if ( $pricing->has_annual() ) {
                                        $min_price = min( $min_price, $pricing->annual_price );
                                    }
                                    if ( $pricing->has_monthly() ) {
                                        $min_price = min( $min_price, $pricing->monthly_price );
                                    }
                                }
                                if ( $min_price < 999999 ) {
                                    $price = $min_price;
                                }
                            }
                        }
                    
                    }
                    
                    if ( !$has_paid_plan && !$has_free_plan ) {
                        continue;
                    }
                    $price_str = '';
                    
                    if ( $is_whitelabeled ) {
                        $price_str = '&nbsp;';
                    } else {
                        $descriptors = array();
                        if ( $has_free_plan ) {
                            $descriptors[] = \fs_text_inline( 'Free', 'free', $slug );
                        }
                        if ( $has_paid_plan && $price > 0 ) {
                            $descriptors[] = '$' . \number_format( $price, 2 );
                        }
                        if ( $has_trial ) {
                            $descriptors[] = \fs_text_x_inline(
                                'Trial',
                                'trial period',
                                'trial',
                                $slug
                            );
                        }
                        $price_str = \implode( ' - ', $descriptors );
                    }
                    
                    ?>
                <script type="text/javascript">
                    jQuery('.fs-cards-list .fs-card.fs-addon[data-slug="<?php 
                    echo  $addon_slug ;
                    ?>"] .fs-offer .fs-price').text('<?php 
                    echo  $price_str ;
                    ?>');
                </script>
<?php 
                }
            }
        }
        
        \losnappas\Ethpress\ethpress_fs()->add_action( 'addons/after_addons', ETHPRESS_NS . '\\ethpress_custom_addons_pricing' );
        // The main plugin activation
        \losnappas\Ethpress\Plugin::attach_hooks();
    }

}
