<?php
/**
 * Primary plugin file.
 *
 * @package GravityKit\GravityCharts
 */

namespace GravityKit\GravityCharts;

use GFCommon;

/**
 * Class Plugin.
 */
class Plugin {
	/**
	 * API instance.
	 *
	 * @since 1.0
	 *
	 * @var API
	 */
	public $api;

	/**
	 * Gutenberg block instance.
	 *
	 * @since 1.0
	 *
	 * @var Chart_Block
	 */
	public $chart_block;

	/**
	 * The Gravity Forms Chart feed instance.
	 *
	 * @since 1.0
	 *
	 * @var Chart_Feed
	 */
	public $chart_feed;

	/**
	 * The plugin instance.
	 *
	 * @since 1.0
	 *
	 * @var Plugin
	 */
	private static $_instance;

	/**
	 * Minimum requirements check fail.
	 *
	 * @since 1.0
	 *
	 * @var bool
	 */
	private static $_min_reqs_fail;

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		add_action( 'gform_loaded', [ $this, 'init' ] );
		add_action( 'init', [ $this, 'check_min_requirements' ] );
	}

	/**
	 * Returns an instance of the GravityCharts plugin.
	 *
	 * @since 1.0
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new Plugin();
		}

		return self::$_instance;
	}

	/**
	 * Checks if minimum requirements are met.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function check_min_requirements(): void {
		if ( self::$_min_reqs_fail ) {
			return;
		}

		if ( ! class_exists( 'GFCommon' ) || version_compare( GFCommon::$version, GK_GRAVITYCHARTS_MIN_GF_VERSION, '<' ) ) {
			$message = wpautop(
				strtr(
				// translators: Do not translate {version}: it will be replaced with a Gravity Forms version number.
					esc_html__( 'GravityCharts requires Gravity Forms {version} or newer.', 'gk-gravitycharts' ),
					[ '{version}' => GK_GRAVITYCHARTS_MIN_GF_VERSION ]
				)
			);

			self::$_min_reqs_fail = true;

			gk_gravitycharts_init_error( $message );
		}
	}

	/**
	 * Initializes required classes.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function init(): void {
		$this->check_min_requirements();

		if ( self::$_min_reqs_fail ) {
			return;
		}

		$this->api         = API::get_instance();
		$this->chart_block = Chart_Block::get_instance();
		$this->chart_feed  = Chart_Feed::get_instance();
		$this->shortcode   = Shortcode::get_instance();
		$this->merge_tag   = Merge_Tag::get_instance();

		if ( class_exists( 'GravityKit\GravityCharts\GravityView_Widget' ) ) {
			$this->gravityview_widget = GravityView_Widget::get_instance();
		}
	}
}
