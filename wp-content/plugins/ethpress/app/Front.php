<?php

/**
 * Handles front-end.
 *
 * @since 0.1.0
 * @package ethpress
 */
namespace losnappas\Ethpress;

defined( 'ABSPATH' ) || die;
use  losnappas\Ethpress\Plugin ;
/**
 * Handles functions for frontend.
 *
 * @since 0.1.0
 */
class Front
{
    /**
     * Displays login button. Hooked to login_form.
     *
     * @since 0.1.0
     */
    public static function login_form()
    {
        $text = esc_html__( 'Enable JavaScript to log in with a crypto wallet.', 'ethpress' );
        $noscript = '<noscript>' . $text . '</noscript>';
        // phpcs:disable -- Complains about not being escaped. They are.
        echo  $noscript ;
        ?>
		<div class="ethpress">
			<?php 
        echo  self::get_login_button() ;
        ?>
		</div>
	<?php 
        // phpcs:enable
    }
    
    /**
     * Gets the login form.
     *
     * @since 0.1.1
     */
    public static function get_login_form()
    {
        \ob_start();
        self::login_form();
        return \ob_get_clean();
    }
    
    /**
     * Creates necessary html for the login button.
     *
     * @since 0.1.0
     *
     * @return string Login button html.
     */
    public static function get_login_button()
    {
        \ob_start();
        self::login_button();
        return \ob_get_clean();
    }
    
    /**
     * Creates necessary html for the login button.
     *
     * @since 0.1.0
     *
     * @return string Login button html.
     */
    public static function login_button()
    {
        $label = esc_html__( 'Log In With a Crypto Wallet', 'ethpress' );
        ?>


		<button class="ethpress-metamask-login-button ethpress-button ethpress-button-secondary ethpress-button-large woocommerce-Button button" type="button" name="metamask">
			<!-- <w3m-core-button></w3m-core-button> -->
			<?php 
        echo  $label ;
        ?>
		</button>




		<?php 
    }
    
    /**
     * Creates necessary html for the link button.
     *
     * @since 1.5.12
     *
     * @return string Link button html.
     */
    public static function get_link_button()
    {
        \ob_start();
        self::link_button();
        return \ob_get_clean();
    }
    
    /**
     * Creates necessary html for the link button.
     *
     * @since 1.5.12
     *
     * @return void
     */
    public static function link_button()
    {
        $is_user_logged_in = is_user_logged_in();
        
        if ( !$is_user_logged_in ) {
            Front::login_form();
        } else {
            $label = esc_html__( 'Link Your Crypto Wallets', 'ethpress' );
            ?>
			<button class='ethpress-metamask-login-button ethpress-button ethpress-button-secondary ethpress-button-large ethpress-account-linker-button woocommerce-Button button' type='button' name='metamask'>
				<?php 
            echo  $label ;
            ?>
			</button>
		<?php 
        }
    
    }
    
    /**
     * Displays register button. Hooked to register_form.
     *
     * @since 0.1.0
     */
    public static function register_form()
    {
        $text = esc_html__( 'Enable JavaScript to log in with a crypto wallet.', 'ethpress' );
        $noscript = '<noscript>' . $text . '</noscript>';
        // phpcs:disable -- Complains about not being escaped. They are.
        echo  $noscript ;
        ?>
		<div class="ethpress">
			<?php 
        echo  self::get_register_button() ;
        ?>
		</div>
	<?php 
        // phpcs:enable
    }
    
    /**
     * Gets the register form.
     *
     * @since 0.1.1
     */
    public static function get_register_form()
    {
        \ob_start();
        self::register_form();
        return \ob_get_clean();
    }
    
    /**
     * Creates necessary html for the register button.
     *
     * @since 0.1.0
     *
     * @return string Register button html.
     */
    public static function get_register_button()
    {
        \ob_start();
        self::register_button();
        return \ob_get_clean();
    }
    
    /**
     * Creates necessary html for the login button.
     *
     * @since 0.1.0
     *
     * @return string Login button html.
     */
    public static function register_button()
    {
        $label = esc_html__( 'Register With a Crypto Wallet', 'ethpress' );
        ?>
		<button class="ethpress-metamask-login-button ethpress-button ethpress-button-secondary ethpress-button-large woocommerce-Button button" type="button" name="metamask">
			<?php 
        echo  $label ;
        ?>
		</button>
<?php 
    }

}