<?php

if ( ! function_exists( 'lmw_fs' ) ) {
    // Create a helper function for easy SDK access.
    function lmw_fs() {
        global $lmw_fs;

        if ( ! isset( $lmw_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $lmw_fs = fs_dynamic_init( array(
                'id'                  => '9151',
                'slug'                => 'lmwoo',
                'type'                => 'plugin',
                'public_key'          => 'pk_1ddfcd402999fb8574c24d17a716f',
                'is_premium'          => false,
                // If your plugin is a serviceware, set this option to false.
                'has_premium_version' => true,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                'menu'                => array(
                    'slug'           => 'lmwoo',
                    'first-path'     => 'admin.php?page=wc-settings&tab=lmfwc_settings',
                    'contact'        => false,
                    'support'        => false,
                ),
                // Set the SDK to work in a sandbox mode (for development & testing).
                // IMPORTANT: MAKE SURE TO REMOVE SECRET KEY BEFORE DEPLOYMENT.
                'secret_key'          => 'sk_Nj5W@eb<^n{$oSSC]3Dy@EPD{iX+u',
            ) );
        }

        return $lmw_fs;
    }

    // Init Freemius.
    lmw_fs();
    // Signal that SDK was initiated.
    do_action( 'lmw_fs_loaded' );
}