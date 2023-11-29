<?php

/**
 * Adds [ethpress_link_button] shortcode.
 *
 * Since version 0.7.0 you should use the EthPress widget instead.
 *
 * @since 0.5.0
 * @package ethpress
 */

namespace losnappas\Ethpress\Shortcodes;

defined('ABSPATH') || die;

use losnappas\Ethpress\Front;
use losnappas\Ethpress\Plugin;

/**
 * Contains LinkButton's internals.
 *
 * @since 0.5.0
 */
class LinkButton
{
    /**
     * Name of the shortcode.
     *
     * @var String shortcode name
     *
     * @since 0.5.0
     */
    public static $shortcode_name = 'ethpress_link_button';

    /**
     * Creates shortcode content. Runs on `\add_shortcode`.
     *
     * Outputs nothing when user is logged in. Button otherwise.
     *
     * @since 0.5.0
     */
    public static function add_shortcode()
    {
        Plugin::register_scripts();
        Plugin::login_enqueue_scripts_and_styles();
        $button = Front::get_link_button();
        return $button;
    }
}
