<?php

/**
 * Displays and acts on the plugin's options page.
 *
 * @since 0.3.0
 * @package ethpress
 */
namespace losnappas\Ethpress\Admin;

defined( 'ABSPATH' ) || die;
use  losnappas\Ethpress\Logger ;
/**
 * Static.
 *
 * @since 0.3.0
 */
class Options
{
    /**
     * Adds options page.
     *
     * @since 0.3.0
     */
    public static function admin_menu()
    {
        $page = esc_html__( 'EthPress', 'ethpress' );
        
        if ( is_multisite() ) {
            add_submenu_page(
                'settings.php',
                $page,
                $page,
                'manage_network_options',
                'ethpress',
                [ __CLASS__, 'create_page' ]
            );
        } else {
            add_options_page(
                $page,
                $page,
                'manage_options',
                'ethpress',
                [ __CLASS__, 'create_page' ]
            );
        }
    
    }
    
    /**
     * Creates options page.
     *
     * @since 0.3.0
     */
    public static function create_page()
    {
        // Require admin privs
        $capability = ( is_multisite() ? 'manage_network_options' : 'manage_options' );
        if ( !current_user_can( $capability ) ) {
            return false;
        }
        // Which tab is selected?
        $possible_screens = array(
            'default' => esc_html( __( 'Standard', 'ethpress' ) ),
        );
        $possible_screens = apply_filters( 'ethpress_settings_tabs', $possible_screens );
        asort( $possible_screens );
        $current_screen = ( isset( $_GET['tab'] ) && isset( $possible_screens[$_GET['tab']] ) ? esc_attr( $_GET['tab'] ) : 'default' );
        
        if ( isset( $_POST['Submit'] ) ) {
            // Nonce verification
            check_admin_referer( 'ethpress-update-options' );
            // Get all existing EthPress options
            $existing_options = get_option( 'ethpress', array() );
            $input = ( isset( $_POST['ethpress'] ) ? $_POST['ethpress'] : [] );
            /**
             * ethpress_get_save_options
             * 
             * Return options to save
             *
             * @param  array $options Current updated options array
             * @param  array $input New option values array from user input
             * @param  string $current_screen The tab id
             * @return array $options
             */
            $new_options = apply_filters(
                'ethpress_get_save_options',
                self::options_validate( $input, $existing_options, $current_screen ),
                $input,
                $current_screen
            );
            // Merge $new_options into $existing_options to retain EthPress options from all other screens/tabs
            if ( $existing_options ) {
                $new_options = array_merge( $existing_options, $new_options );
            }
            
            if ( empty($new_options['recursive']) && is_multisite() ) {
                // This calls this validation function recursively.
                // Nothing happens on "return" because this is multisite.
                update_site_option( 'ethpress', $new_options );
            } else {
                
                if ( false !== get_option( 'ethpress' ) ) {
                    update_option( 'ethpress', $new_options );
                } else {
                    $deprecated = '';
                    $autoload = 'no';
                    add_option(
                        'ethpress',
                        $new_options,
                        $deprecated,
                        $autoload
                    );
                }
            
            }
            
            ?>
            <div class="updated">
                <p><?php 
            _e( 'Settings saved.', 'ethpress' );
            ?></p>
            </div>
        <?php 
        } else {
            
            if ( isset( $_POST['Reset'] ) ) {
                // Nonce verification
                check_admin_referer( 'ethpress-update-options' );
                delete_option( 'ethpress' );
            }
        
        }
        
        $options = stripslashes_deep( get_option( 'ethpress', array() ) );
        // Man that html looks bad!
        ?>
        <div class="wrap">
            <h1><?php 
        esc_html_e( 'EthPress Options Page', 'ethpress' );
        ?></h1>
            <p><?php 
        esc_html_e( 'The MetaMask login plugin.', 'ethpress' );
        ?></p>

            <?php 
        settings_errors();
        ?>

            <?php 
        
        if ( \losnappas\Ethpress\ethpress_fs()->is_not_paying() ) {
            ?>
                <p><a aria-label="<?php 
            esc_attr_e( 'Opens in new tab', 'ethpress' );
            ?>" href="https://etherscan.io/address/0x106417f7265e15c1aae52f76809f171578e982a9" target="_blank" title="<?php 
            esc_attr_e( 'Developer\'s wallet, etherscan.io', 'ethpress' );
            ?>" rel="noopener noreferer"><?php 
            esc_html_e( 'Donate to support development!', 'ethpress' );
            ?> <span style="text-decoration: none;" aria-hidden="true" class="dashicons dashicons-external"></span></a> <?php 
            esc_html_e( 'For fiat, find the charity link on wp plugin directory.', 'ethpress' );
            ?></p>
            <?php 
        }
        
        ?>
            <p>
                <?php 
        echo  sprintf( __( 'If you like <strong>EthPress</strong> please leave us a %1$s rating. A huge thanks in advance!', 'ethpress' ), '<a href="https://wordpress.org/support/plugin/ethpress/reviews?rate=5#new-post" target="_blank" rel="noopener noreferer">★★★★★</a>' ) ;
        ?>
            </p>

            <?php 
        
        if ( \losnappas\Ethpress\ethpress_fs()->is_not_paying() ) {
            echo  '<section><h1>' . esc_html__( 'Awesome Premium Features', 'ethpress' ) . '</h1>' ;
            echo  esc_html__( 'Managed Verification Service and more.', 'ethpress' ) ;
            echo  ' <a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . esc_html__( 'Upgrade Now!', 'ethpress' ) . '</a>' ;
            echo  '</section>' ;
        }
        
        ?>

            <h2 class="nav-tab-wrapper">
                <?php 
        if ( $possible_screens ) {
            foreach ( $possible_screens as $s => $sTitle ) {
                $path = 'options-general.php?page=ethpress&tab=' . esc_attr( $s );
                $admin_url = ( is_multisite() ? network_admin_url( $path ) : admin_url( $path ) );
                ?>
                    <a href="<?php 
                echo  $admin_url ;
                ?>" class="nav-tab<?php 
                if ( $s == $current_screen ) {
                    echo  ' nav-tab-active' ;
                }
                ?>"><?php 
                echo  esc_html( $sTitle ) ;
                ?></a>
                <?php 
            }
        }
        ?>
            </h2>

            <?php 
        // $action = is_multisite() ? "../options.php" : "options.php";
        ?>
            <!-- <form action="<?php 
        //echo $action
        ?>" method="POST"> -->
            <form id="ethpress_admin_form" method="POST" action="">

                <?php 
        wp_nonce_field( 'ethpress-update-options' );
        ?>

                <table class="form-table">

                    <?php 
        
        if ( 'default' == $current_screen ) {
            self::section_main();
            self::input_api_url( $options );
            self::input_use_managed_service( $options );
            self::input_redirect_url( $options );
            self::section_projectId();
            self::projectId_setting( $options );
            self::section_login_form();
            self::theme_mode_setting( $options );
            self::section_login();
            self::woocommerce_login_form_setting( $options );
            self::woocommerce_register_form_setting( $options );
            self::woocommerce_after_checkout_registration_form_setting( $options );
            self::woocommerce_account_details_link_button_setting( $options );
            self::section_strings();
            self::login_button_label_setting( $options );
            self::link_button_label_setting( $options );
            self::register_button_label_setting( $options );
            self::link_message_setting( $options );
        }
        
        do_action( 'ethpress_print_options', $options, $current_screen );
        ?>

                </table>

                <?php 
        // Other tab or our own condition
        // $can_save_settings = 'default' !== $current_screen || !self::_check_ecrecovers_with_php();
        // if (!$can_save_settings) {
        //     // if (\losnappas\Ethpress\ethpress_fs()->can_use_premium_code__premium_only()) {
        //     $can_save_settings = true;
        //     // }
        // }
        // if ($can_save_settings) {
        do_action( 'ethpress_before_submit_button', $options, $current_screen );
        ?>
                <p class="submit">
                    <input class="button-primary" type="submit" name="Submit" value="<?php 
        _e( 'Save Changes', 'ethpress' );
        ?>" />
                    <input id="ETHPRESS_reset_options" type="submit" name="Reset" onclick="return confirm('<?php 
        _e( 'Are you sure you want to delete all EthPress options?', 'ethpress' );
        ?>')" value="<?php 
        _e( 'Reset', 'ethpress' );
        ?>" />
                </p>
                <?php 
        // }
        ?>
            </form>
            <p class="alignleft">
                <?php 
        echo  sprintf( __( 'If you like <strong>EthPress</strong> please leave us a %1$s rating. A huge thanks in advance!', 'ethpress' ), '<a href="https://wordpress.org/support/plugin/ethpress/reviews?rate=5#new-post" target="_blank" rel="noopener noreferer">★★★★★</a>' ) ;
        ?>
            </p>
        </div>
    <?php 
    }
    
    protected static function _check_ecrecovers_with_php()
    {
        $ecrecovers_with_php = extension_loaded( 'gmp' ) || extension_loaded( 'bcmath' );
        return $ecrecovers_with_php;
    }
    
    /**
     * Outputs main section title.
     *
     * @since 0.3.0
     */
    public static function section_main()
    {
        ?>
        <tr valign="top">
            <th scope="row" colspan="2">
                <h2>
                    <?php 
        _e( "Main Settings", 'ethpress' );
        ?>
                </h2>
            </th>
            <td>
            </td>
        </tr>
    <?php 
    }
    
    /**
     * Outputs login section title.
     *
     * @since 1.2.0
     */
    public static function section_login()
    {
        ?>
        <tr valign="top">
            <th scope="row" colspan="2">
                <h2>
                    <?php 
        _e( "WooCommerce Settings", 'ethpress' );
        ?>
                </h2>
            </th>
            <td>
            </td>
        </tr>
    <?php 
    }
    
    /**
     * Outputs login section title.
     *
     * @since 1.2.0
     */
    public static function section_projectId()
    {
        ?>
        <tr valign="top">
            <th scope="row" colspan="2">
                <h2>
                    <?php 
        _e( "WalletConnect Settings", 'ethpress' );
        ?>
                </h2>
            </th>
            <td>
            </td>
        </tr>
    <?php 
    }
    
    /**
     * Outputs input for login_button_label option.
     *
     * @since 1.5.12
     */
    public static function projectId_setting( $options = null )
    {
        ?>
        <tr valign="top">
            <th scope="row"><?php 
        _e( "WalletConnect Project ID", 'ethpress' );
        ?></th>
            <td>
                <fieldset>
                    <label>
                        <input class="regular-text" id="ethpress_project_Id" name="ethpress[wallet_connect_project_id]" type="text" placeholder="<?php 
        echo  esc_attr( __( "WalletConnect Project ID", 'ethpress' ) ) ;
        ?>" value="<?php 
        echo  esc_attr( ( isset( $options['wallet_connect_project_id'] ) ? esc_attr( $options['wallet_connect_project_id'] ) : '' ) ) ;
        ?>">
                        <p class="description">
                            <?php 
        echo  sprintf(
            __( 'The %1$s should be set there. You can get it from %2$sWalletConnect.com%3$s. Follow this %4$svideo guide%3$s if unsure.', 'ethpress' ),
            __( "WalletConnect Project ID", 'ethpress' ),
            '<a href="https://cloud.walletconnect.com/sign-in" target="_blank">',
            '</a>',
            '<a href="https://youtu.be/jn44Q1h1QvI" target="_blank">'
        ) ;
        ?>
                        </p>
                    </label>
                </fieldset>
            </td>
        </tr>
    <?php 
    }
    
    /**
     * Outputs login section title.
     *
     * @since 1.2.0
     */
    public static function section_login_form()
    {
        ?>
        <tr valign="top">
            <th scope="row" colspan="2">
                <h2>
                    <?php 
        _e( "Web3 Login Form", 'ethpress' );
        ?>
                </h2>
            </th>
            <td>
            </td>
        </tr>
    <?php 
    }
    
    /**
     * Outputs input for login form theme mode.
     *
     * @since 1.5.12
     */
    public static function theme_mode_setting( $options = null )
    {
        $ethpress_theme_mode = ( isset( $options['theme_mode'] ) ? $options['theme_mode'] : "light" );
        ?>
        <tr valign="top">
            <th scope="row"><?php 
        _e( "Theme Mode", 'ethpress' );
        ?></th>
            <td>
                <fieldset>
                    <label>
                        <input class="regular-text" id="ethpress_theme_mode_dark" name="ethpress[theme_mode]" type="radio" value="dark" <?php 
        echo  ( $ethpress_theme_mode == "dark" ? 'checked' : '' ) ;
        ?>>
                        <label for="ethpress_theme_mode_dark"><?php 
        _e( "Dark Mode", 'ethpress' );
        ?></label>
                        <input class="regular-text" id="ethpress_theme_mode_lite" name="ethpress[theme_mode]" type="radio" value="light" <?php 
        echo  ( $ethpress_theme_mode == "light" ? 'checked' : '' ) ;
        ?>>
                        <label for="ethpress_theme_mode_lite"><?php 
        _e( "Light Mode", 'ethpress' );
        ?></label>
                    </label>
                </fieldset>
            </td>
        </tr>
    <?php 
    }
    
    /**
     * Outputs login section title.
     *
     * @since 1.2.0
     */
    public static function section_strings()
    {
        ?>
        <tr valign="top">
            <th scope="row" colspan="2">
                <h2>
                    <?php 
        _e( "Strings Settings", 'ethpress' );
        ?>
                </h2>
            </th>
            <td>
            </td>
        </tr>
    <?php 
    }
    
    /**
     * Outputs input for login_button_label option.
     *
     * @since 1.5.12
     */
    public static function login_button_label_setting( $options = null )
    {
        $login_button_label = esc_html__( 'Log In With a Crypto Wallet', 'ethpress' );
        $disabled = 'disabled';
        ?>
        <tr valign="top">
            <th scope="row"><?php 
        _e( "Login button label", 'ethpress' );
        ?></th>
            <td>
                <fieldset>
                    <label>
                        <input <?php 
        echo  $disabled ;
        ?> class="regular-text" id="ethpress_login_button_label" name="ethpress[login_button_label]" type="text" placeholder="<?php 
        echo  esc_attr( $login_button_label ) ;
        ?>" value="<?php 
        echo  esc_attr( ( isset( $options['login_button_label'] ) ? esc_attr( $options['login_button_label'] ) : '' ) ) ;
        ?>">
                        <p class="description">
                            <?php 
        _e( 'The Login button text can be set there.', 'ethpress' );
        ?></p>
                        <?php 
        
        if ( \losnappas\Ethpress\ethpress_fs()->is_trial() ) {
            ?>
                            <h2 class="description"><?php 
            echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to keep using this feature!', 'ethpress' ) . '</a>' ;
            ?></h2>
                        <?php 
        } else {
            
            if ( 'disabled' === $disabled ) {
                ?>
                            <h2 class="description"><?php 
                echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to use this feature!', 'ethpress' ) . '</a>' ;
                ?></h2>
                        <?php 
            }
        
        }
        
        ?>
                    </label>
                </fieldset>
            </td>
        </tr>
    <?php 
    }
    
    /**
     * Outputs input for link_button_label option.
     *
     * @since 1.5.12
     */
    public static function link_button_label_setting( $options = null )
    {
        $link_button_label = esc_html__( 'Link Your Crypto Wallets', 'ethpress' );
        $disabled = 'disabled';
        ?>
        <tr valign="top">
            <th scope="row"><?php 
        _e( "Link button label", 'ethpress' );
        ?></th>
            <td>
                <fieldset>
                    <label>
                        <input <?php 
        echo  $disabled ;
        ?> class="regular-text" id="ethpress_link_button_label" name="ethpress[link_button_label]" type="text" placeholder="<?php 
        echo  esc_attr( $link_button_label ) ;
        ?>" value="<?php 
        echo  esc_attr( ( isset( $options['link_button_label'] ) ? esc_attr( $options['link_button_label'] ) : '' ) ) ;
        ?>">
                        <p class="description">
                            <?php 
        _e( 'The Link button text can be set there.', 'ethpress' );
        ?></p>
                        <?php 
        
        if ( \losnappas\Ethpress\ethpress_fs()->is_trial() ) {
            ?>
                            <h2 class="description"><?php 
            echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to keep using this feature!', 'ethpress' ) . '</a>' ;
            ?></h2>
                        <?php 
        } else {
            
            if ( 'disabled' === $disabled ) {
                ?>
                            <h2 class="description"><?php 
                echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to use this feature!', 'ethpress' ) . '</a>' ;
                ?></h2>
                        <?php 
            }
        
        }
        
        ?>
                    </label>
                </fieldset>
            </td>
        </tr>
    <?php 
    }
    
    /**
     * Outputs input for register_button_label option.
     *
     * @since 1.5.12
     */
    public static function register_button_label_setting( $options = null )
    {
        $register_button_label = esc_html__( 'Register With a Crypto Wallet', 'ethpress' );
        $disabled = 'disabled';
        ?>
        <tr valign="top">
            <th scope="row"><?php 
        _e( "Register button label", 'ethpress' );
        ?></th>
            <td>
                <fieldset>
                    <label>
                        <input <?php 
        echo  $disabled ;
        ?> class="regular-text" id="ethpress_register_button_label" name="ethpress[register_button_label]" type="text" placeholder="<?php 
        echo  esc_attr( $register_button_label ) ;
        ?>" value="<?php 
        echo  esc_attr( ( isset( $options['register_button_label'] ) ? esc_attr( $options['register_button_label'] ) : '' ) ) ;
        ?>">
                        <p class="description">
                            <?php 
        _e( 'The Register button text can be set there.', 'ethpress' );
        ?></p>
                        <?php 
        
        if ( \losnappas\Ethpress\ethpress_fs()->is_trial() ) {
            ?>
                            <h2 class="description"><?php 
            echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to keep using this feature!', 'ethpress' ) . '</a>' ;
            ?></h2>
                        <?php 
        } else {
            
            if ( 'disabled' === $disabled ) {
                ?>
                            <h2 class="description"><?php 
                echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to use this feature!', 'ethpress' ) . '</a>' ;
                ?></h2>
                        <?php 
            }
        
        }
        
        ?>
                    </label>
                </fieldset>
            </td>
        </tr>
    <?php 
    }
    
    public static function link_message_setting( $options = null )
    {
        $link_message = esc_html__( 'Success! Address %s is now linked to your account.', 'ethpress' );
        $disabled = 'disabled';
        ?>
        <tr valign="top">
            <th scope="row"><?php 
        _e( "The account link success message", 'ethpress' );
        ?></th>
            <td>
                <fieldset>
                    <label>
                        <input <?php 
        echo  $disabled ;
        ?> class="regular-text" id="ethpress_link_message" name="ethpress[link_message]" type="text" placeholder="<?php 
        echo  esc_attr( $link_message ) ;
        ?>" value="<?php 
        echo  esc_attr( ( isset( $options['link_message'] ) ? esc_attr( $options['link_message'] ) : '' ) ) ;
        ?>">
                        <p class="description">
                            <?php 
        _e( 'The successful account link message. The %s can be used to insert the account address linked.', 'ethpress' );
        ?></p>
                        <?php 
        
        if ( \losnappas\Ethpress\ethpress_fs()->is_trial() ) {
            ?>
                            <h2 class="description"><?php 
            echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to keep using this feature!', 'ethpress' ) . '</a>' ;
            ?></h2>
                        <?php 
        } else {
            
            if ( 'disabled' === $disabled ) {
                ?>
                            <h2 class="description"><?php 
                echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to use this feature!', 'ethpress' ) . '</a>' ;
                ?></h2>
                        <?php 
            }
        
        }
        
        ?>
                    </label>
                </fieldset>
            </td>
        </tr>
    <?php 
    }
    
    /**
     * Outputs input for api url option.
     *
     * @since 0.3.0
     */
    public static function input_api_url( $options = null )
    {
        $ecrecovers_with_php = self::_check_ecrecovers_with_php();
        if ( is_null( $options ) ) {
            $options = get_site_option( 'ethpress' );
        }
        // Logger::log("Options::input_api_url: options = " . print_r($options, true));
        $api_url = ( isset( $options['api_url'] ) ? esc_url( $options['api_url'] ) : '' );
        ?>
        <tr valign="top">
            <th scope="row"><?php 
        _e( "Verification Service API URL", 'ethpress' );
        ?></th>
            <td>
                <?php 
        
        if ( $ecrecovers_with_php ) {
            ?>
                    <p class="description">
                        <?php 
            esc_html_e( 'Your PHP installation has the necessary PHP extension to do verifications on your server, so there is nothing to configure.', 'ethpress' );
            ?>
                    </p>
                <?php 
        } else {
            ?>
                    <fieldset>
                        <label>
                            <input class="regular-text" id="ethpress_api_url" name="ethpress[api_url]" type="text" value="<?php 
            echo  esc_attr( $api_url ) ;
            ?>">
                            <p class="description">
                                <?php 
            echo  wp_kses( sprintf(
                __( 'Use an API or install %1$sPHP-GMP%2$s or %3$sPHP-BCMath%2$s to verify Ethereum signatures.', 'ethpress' ),
                '<a href="https://www.php.net/manual/en/book.gmp.php" target="_blank" rel="noopener noreferrer">',
                '</a>',
                '<a href="https://www.php.net/manual/en/book.bc.php" target="_blank" rel="noopener noreferrer">'
            ), [
                'a' => [
                'href'   => [],
                'target' => [],
                'rel'    => [],
            ],
            ] ) ;
            ?></p>
                            <p class="description">
                                <?php 
            echo  wp_kses( sprintf(
                /* translators: a link. */
                __( 'To deploy your own verification service, see %1$s.', 'ethpress' ),
                '<a href="https://gitlab.com/losnappas/verify-eth-signature/-/tree/master" target="_blank" rel="noopener noreferrer">https://gitlab.com/losnappas/verify-eth-signature</a>'
            ), [
                'a' => [
                'href'   => [],
                'target' => [],
                'rel'    => [],
            ],
            ] ) ;
            ?></p>
                        </label>
                    </fieldset>
                <?php 
        }
        
        ?>
            </td>
        </tr>
    <?php 
    }
    
    /**
     * Outputs input for redirect url option.
     *
     * @since 1.5.12
     */
    public static function input_redirect_url( $options = null )
    {
        if ( is_null( $options ) ) {
            $options = get_site_option( 'ethpress' );
        }
        // Logger::log("Options::input_redirect_url: options = " . print_r($options, true));
        $redirect_url = ( isset( $options['redirect_url'] ) ? esc_url( $options['redirect_url'] ) : '' );
        $disabled = 'disabled';
        ?>
        <tr valign="top">
            <th scope="row"><?php 
        _e( "Redirect URL", 'ethpress' );
        ?></th>
            <td>
                <fieldset>
                    <label>
                        <input <?php 
        echo  $disabled ;
        ?> class="regular-text" id="ethpress_redirect_url" name="ethpress[redirect_url]" type="text" value="<?php 
        echo  esc_attr( $redirect_url ) ;
        ?>">
                        <p class="description">
                            <?php 
        _e( 'The page to redirect after a successful login', 'ethpress' );
        ?></p>
                        <?php 
        
        if ( \losnappas\Ethpress\ethpress_fs()->is_trial() ) {
            ?>
                            <h2 class="description"><?php 
            echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to keep using this feature!', 'ethpress' ) . '</a>' ;
            ?></h2>
                        <?php 
        } else {
            
            if ( 'disabled' === $disabled ) {
                ?>
                            <h2 class="description"><?php 
                echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to use this feature!', 'ethpress' ) . '</a>' ;
                ?></h2>
                        <?php 
            }
        
        }
        
        ?>
                    </label>
                </fieldset>
            </td>
        </tr>
    <?php 
    }
    
    /**
     * Outputs input for use_managed_service option.
     *
     * @since 1.2.0
     */
    public static function input_use_managed_service( $options = null )
    {
        $ecrecovers_with_php = self::_check_ecrecovers_with_php();
        if ( is_null( $options ) ) {
            $options = get_site_option( 'ethpress' );
        }
        // Logger::log("Options::input_use_managed_service: options = " . print_r($options, true));
        $use_managed_service = false;
        $disabled = 'disabled';
        ?>
        <tr valign="top">
            <th scope="row"><?php 
        _e( "Managed Verification Service", 'ethpress' );
        ?></th>
            <td>
                <?php 
        
        if ( $ecrecovers_with_php ) {
            ?>
                    <p class="description">
                        <?php 
            esc_html_e( 'Your PHP installation has the necessary PHP extension to do verifications on your server, so there is nothing to configure.', 'ethpress' );
            ?>
                    </p>
                <?php 
        } else {
            ?>
                    <fieldset>
                        <input <?php 
            echo  $disabled ;
            ?> class="regular-text" id="ethpress_use_managed_service" name="ethpress[use_managed_service]" type="checkbox" value="yes" <?php 
            echo  ( $use_managed_service ? 'checked' : '' ) ;
            ?>>
                        <label for="ethpress_use_managed_service">
                            <?php 
            _e( 'Use Managed Verification Service', 'ethpress' );
            ?>
                        </label>
                        <p class="description">
                            <?php 
            _e( 'Check to use the Managed Verification Service.', 'ethpress' );
            ?></p>
                        <?php 
            
            if ( \losnappas\Ethpress\ethpress_fs()->is_trial() ) {
                ?>
                            <h2 class="description"><?php 
                echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to keep using this feature!', 'ethpress' ) . '</a>' ;
                ?></h2>
                        <?php 
            } else {
                
                if ( 'disabled' === $disabled ) {
                    ?>
                            <h2 class="description"><?php 
                    echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to use this feature!', 'ethpress' ) . '</a>' ;
                    ?></h2>
                        <?php 
                }
            
            }
            
            ?>
                    </fieldset>
                <?php 
        }
        
        ?>
            </td>
        </tr>
    <?php 
    }
    
    /**
     * Outputs input for woocommerce_login_form_show option.
     *
     * @since 1.2.0
     */
    public static function woocommerce_login_form_setting( $options = null )
    {
        if ( is_null( $options ) ) {
            $options = get_site_option( 'ethpress' );
        }
        /**
         * Check if WooCommerce is active
         * https://wordpress.stackexchange.com/a/193908/137915
         **/
        $woocommerce_active = in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
        $woocommerce_login_form_show = false;
        $disabled = 'disabled';
        ?>
        <tr valign="top">
            <th scope="row"><?php 
        _e( "WooCommerce Login Form", 'ethpress' );
        ?></th>
            <td>
                <fieldset>
                    <input <?php 
        echo  $disabled ;
        ?> class="regular-text" id="ethpress_woocommerce_login_form_show" name="ethpress[woocommerce_login_form_show]" type="checkbox" value="yes" <?php 
        echo  ( $woocommerce_login_form_show ? 'checked' : '' ) ;
        ?> />
                    <label for="ethpress_woocommerce_login_form_show">
                        <?php 
        _e( 'Show on WooCommerce Login Form?', 'ethpress' );
        ?>
                    </label>
                    <p class="description">
                        <?php 
        _e( 'Check to show EthPress login button on the WooCommerce Login Form.', 'ethpress' );
        ?></p>
                    <?php 
        
        if ( \losnappas\Ethpress\ethpress_fs()->is_trial() ) {
            ?>
                        <h2 class="description"><?php 
            echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to keep using this feature!', 'ethpress' ) . '</a>' ;
            ?></h2>
                    <?php 
        } else {
            
            if ( !\losnappas\Ethpress\ethpress_fs()->can_use_premium_code() ) {
                ?>
                        <h2 class="description"><?php 
                echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to use this feature!', 'ethpress' ) . '</a>' ;
                ?></h2>
                    <?php 
            }
        
        }
        
        
        if ( !$woocommerce_active ) {
            ?>
                        <h2 class="description"><?php 
            echo  '<a href="https://woocommerce.com/?aff=12943&cid=17113767">' . __( 'Install WooCommerce to use this feature!', 'ethpress' ) . '</a> ' . __( 'WooCommerce is a customizable, open-source eCommerce platform built on WordPress.', 'ethpress' ) ;
            ?></h2>
                    <?php 
        }
        
        ?>
                </fieldset>
            </td>
        </tr>
    <?php 
    }
    
    /**
     * Outputs input for woocommerce_register_form_show option.
     *
     * @since 1.3.0
     */
    public static function woocommerce_register_form_setting( $options = null )
    {
        if ( is_null( $options ) ) {
            $options = get_site_option( 'ethpress' );
        }
        /**
         * Check if WooCommerce is active
         * https://wordpress.stackexchange.com/a/193908/137915
         **/
        $woocommerce_active = in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
        $woocommerce_register_form_show = false;
        $disabled = 'disabled';
        ?>
        <tr valign="top">
            <th scope="row"><?php 
        _e( "WooCommerce Register Form", 'ethpress' );
        ?></th>
            <td>
                <fieldset>
                    <input <?php 
        echo  $disabled ;
        ?> class="regular-text" id="ethpress_woocommerce_register_form_show" name="ethpress[woocommerce_register_form_show]" type="checkbox" value="yes" <?php 
        echo  ( $woocommerce_register_form_show ? 'checked' : '' ) ;
        ?> />
                    <label for="ethpress_woocommerce_register_form_show">
                        <?php 
        _e( 'Show on WooCommerce Register Form?', 'ethpress' );
        ?>
                    </label>
                    <p class="description">
                        <?php 
        _e( 'Check to show EthPress register button on the WooCommerce Register Form.', 'ethpress' );
        ?></p>
                    <?php 
        
        if ( \losnappas\Ethpress\ethpress_fs()->is_trial() ) {
            ?>
                        <h2 class="description"><?php 
            echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to keep using this feature!', 'ethpress' ) . '</a>' ;
            ?></h2>
                    <?php 
        } else {
            
            if ( !\losnappas\Ethpress\ethpress_fs()->can_use_premium_code() ) {
                ?>
                        <h2 class="description"><?php 
                echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to use this feature!', 'ethpress' ) . '</a>' ;
                ?></h2>
                    <?php 
            }
        
        }
        
        
        if ( !$woocommerce_active ) {
            ?>
                        <h2 class="description"><?php 
            echo  '<a href="https://woocommerce.com/?aff=12943&cid=17113767">' . __( 'Install WooCommerce to use this feature!', 'ethpress' ) . '</a> ' . __( 'WooCommerce is a customizable, open-source eCommerce platform built on WordPress.', 'ethpress' ) ;
            ?></h2>
                    <?php 
        }
        
        
        if ( !get_option( 'users_can_register' ) ) {
            ?>
                        <h2 class="description"><?php 
            echo  '<a href="' . get_admin_url() . 'options-general.php' . '">' . __( 'Check the Administration > Settings > General > Membership: Anyone can register box to use this feature.', 'ethpress' ) . '</a> ' ;
            ?></h2>
                    <?php 
        }
        
        ?>
                </fieldset>
            </td>
        </tr>
    <?php 
    }
    
    /**
     * Outputs input for woocommerce_after_checkout_registration_form_show option.
     *
     * @since 1.3.0
     */
    public static function woocommerce_after_checkout_registration_form_setting( $options = null )
    {
        if ( is_null( $options ) ) {
            $options = get_site_option( 'ethpress' );
        }
        /**
         * Check if WooCommerce is active
         * https://wordpress.stackexchange.com/a/193908/137915
         **/
        $woocommerce_active = in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
        $woocommerce_after_checkout_registration_form_show = false;
        $disabled = 'disabled';
        ?>
        <tr valign="top">
            <th scope="row"><?php 
        _e( "WooCommerce Checkout Page Register Form", 'ethpress' );
        ?></th>
            <td>
                <fieldset>
                    <input <?php 
        echo  $disabled ;
        ?> class="regular-text" id="ethpress_woocommerce_after_checkout_registration_form_show" name="ethpress[woocommerce_after_checkout_registration_form_show]" type="checkbox" value="yes" <?php 
        echo  ( $woocommerce_after_checkout_registration_form_show ? 'checked' : '' ) ;
        ?> />
                    <label for="ethpress_woocommerce_after_checkout_registration_form_show">
                        <?php 
        _e( 'Show on the WooCommerce Checkout page?', 'ethpress' );
        ?>
                    </label>
                    <p class="description">
                        <?php 
        _e( 'Check to show EthPress register button on the WooCommerce Checkout page.', 'ethpress' );
        ?></p>
                    <?php 
        
        if ( \losnappas\Ethpress\ethpress_fs()->is_trial() ) {
            ?>
                        <h2 class="description"><?php 
            echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to keep using this feature!', 'ethpress' ) . '</a>' ;
            ?></h2>
                    <?php 
        } else {
            
            if ( !\losnappas\Ethpress\ethpress_fs()->can_use_premium_code() ) {
                ?>
                        <h2 class="description"><?php 
                echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to use this feature!', 'ethpress' ) . '</a>' ;
                ?></h2>
                    <?php 
            }
        
        }
        
        
        if ( !$woocommerce_active ) {
            ?>
                        <h2 class="description"><?php 
            echo  '<a href="https://woocommerce.com/?aff=12943&cid=17113767">' . __( 'Install WooCommerce to use this feature!', 'ethpress' ) . '</a> ' . __( 'WooCommerce is a customizable, open-source eCommerce platform built on WordPress.', 'ethpress' ) ;
            ?></h2>
                    <?php 
        }
        
        
        if ( !get_option( 'users_can_register' ) ) {
            ?>
                        <h2 class="description"><?php 
            echo  '<a href="' . get_admin_url() . 'options-general.php' . '">' . __( 'Check the Administration > Settings > General > Membership: Anyone can register box to use this feature.', 'ethpress' ) . '</a> ' ;
            ?></h2>
                    <?php 
        }
        
        ?>
                </fieldset>
            </td>
        </tr>
    <?php 
    }
    
    /**
     * Outputs input for woocommerce_account_details_link_button_show option.
     *
     * @since 1.3.0
     */
    public static function woocommerce_account_details_link_button_setting( $options = null )
    {
        if ( is_null( $options ) ) {
            $options = get_site_option( 'ethpress' );
        }
        /**
         * Check if WooCommerce is active
         * https://wordpress.stackexchange.com/a/193908/137915
         **/
        $woocommerce_active = in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
        $woocommerce_account_details_link_button_show = false;
        $disabled = 'disabled';
        ?>
        <tr valign="top">
            <th scope="row"><?php 
        _e( "WooCommerce Account Details Link Wallet Form", 'ethpress' );
        ?></th>
            <td>
                <fieldset>
                    <input <?php 
        echo  $disabled ;
        ?> class="regular-text" id="ethpress_woocommerce_account_details_link_button_show" name="ethpress[woocommerce_account_details_link_button_show]" type="checkbox" value="yes" <?php 
        echo  ( $woocommerce_account_details_link_button_show ? 'checked' : '' ) ;
        ?> />
                    <label for="ethpress_woocommerce_account_details_link_button_show">
                        <?php 
        _e( 'Show the Link Wallet button on the WooCommerce Account Details page?', 'ethpress' );
        ?>
                    </label>
                    <p class="description">
                        <?php 
        _e( 'Check to the Link Wallet button on the WooCommerce Account Details page.', 'ethpress' );
        ?></p>
                    <?php 
        
        if ( \losnappas\Ethpress\ethpress_fs()->is_trial() ) {
            ?>
                        <h2 class="description"><?php 
            echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to keep using this feature!', 'ethpress' ) . '</a>' ;
            ?></h2>
                    <?php 
        } else {
            
            if ( !\losnappas\Ethpress\ethpress_fs()->can_use_premium_code() ) {
                ?>
                        <h2 class="description"><?php 
                echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to use this feature!', 'ethpress' ) . '</a>' ;
                ?></h2>
                    <?php 
            }
        
        }
        
        
        if ( !$woocommerce_active ) {
            ?>
                        <h2 class="description"><?php 
            echo  '<a href="https://woocommerce.com/?aff=12943&cid=17113767">' . __( 'Install WooCommerce to use this feature!', 'ethpress' ) . '</a> ' . __( 'WooCommerce is a customizable, open-source eCommerce platform built on WordPress.', 'ethpress' ) ;
            ?></h2>
                    <?php 
        }
        
        ?>
                </fieldset>
            </td>
        </tr>
<?php 
    }
    
    /**
     * Validates input for api url option.
     *
     * @param array $input New options input.
     *
     * @since 0.3.0
     */
    public static function options_validate( $input, $options, $current_screen )
    {
        // Logger::log("Options::options_validate: options = " . print_r($options, true));
        // Logger::log("Options::options_validate: input = " . print_r($input, true));
        if ( 'default' !== $current_screen ) {
            return $options;
        }
        $newurl = esc_url_raw( trim( $input['api_url'] ) );
        $use_managed_service = false;
        $woocommerce_login_form_show = false;
        $woocommerce_register_form_show = false;
        $woocommerce_after_checkout_registration_form_show = false;
        $woocommerce_account_details_link_button_show = false;
        $login_button_label = '';
        $link_button_label = '';
        $register_button_label = '';
        $link_message = '';
        $redirect_url = '';
        $wallet_connect_project_id = sanitize_text_field( trim( $input['wallet_connect_project_id'] ) );
        $theme_mode = sanitize_text_field( trim( $input['theme_mode'] ) );
        if ( empty($input['recursive']) && is_multisite() ) {
            // Mark next call as recursed.
            $options['recursive'] = true;
        }
        $options['api_url'] = $newurl;
        $options['redirect_url'] = $redirect_url;
        $options['use_managed_service'] = intval( $use_managed_service );
        $options['woocommerce_login_form_show'] = intval( $woocommerce_login_form_show );
        $options['woocommerce_register_form_show'] = intval( $woocommerce_register_form_show );
        $options['woocommerce_after_checkout_registration_form_show'] = intval( $woocommerce_after_checkout_registration_form_show );
        $options['woocommerce_account_details_link_button_show'] = intval( $woocommerce_account_details_link_button_show );
        $options['login_button_label'] = $login_button_label;
        $options['link_button_label'] = $link_button_label;
        $options['register_button_label'] = $register_button_label;
        $options['link_message'] = $link_message;
        $options['wallet_connect_project_id'] = $wallet_connect_project_id;
        $options['theme_mode'] = $theme_mode;
        if ( isset( $input['have_db_users'] ) ) {
            $options['have_db_users'] = $input['have_db_users'];
        }
        return $options;
    }
    
    /**
     * Adds settings link. Hooked to filter.
     *
     * @since 0.7.0
     *
     * @param array $links Existing links.
     */
    public static function plugin_action_links( $links )
    {
        $label = esc_html__( 'Settings', 'ethpress' );
        
        if ( is_multisite() ) {
            
            if ( current_user_can( 'manage_network_options' ) ) {
                $url = esc_attr( esc_url( add_query_arg( 'page', 'ethpress', network_admin_url() . 'settings.php' ) ) );
            } else {
                return $links;
            }
        
        } else {
            $url = esc_attr( esc_url( add_query_arg( 'page', 'ethpress', get_admin_url() . 'options-general.php' ) ) );
        }
        
        $settings_link = "<a href='{$url}'>{$label}</a>";
        array_unshift( $links, $settings_link );
        return $links;
    }

}