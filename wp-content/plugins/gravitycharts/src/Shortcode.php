<?php
/**
 * [gravitycharts] shortcode
 *
 * @package GravityKit\GravityCharts
 */

namespace GravityKit\GravityCharts;

/**
 * GravityCharts shortcode.
 */
class Shortcode {

	/**
	 * Shortcode name.
	 *
	 * @since 1.1
	 *
	 * @var string
	 */
	const SHORTCODE = 'gravitycharts';

	/**
	 * Handle used to register UI assets.
	 *
	 * @since 1.1
	 *
	 * @var string
	 */
	const ASSETS_HANDLE = 'gravitycharts-shortcode';

	/**
	 * GravityCharts plugin instance.
	 *
	 * @since 1.1
	 *
	 * @var Plugin
	 */
	private $gravitycharts;

	/**
	 * Class instance.
	 *
	 * @since 1.1
	 *
	 * @var Shortcode
	 */
	private static $_instance;

	/**
	 * Constructor.
	 *
	 * @since 1.1
	 */
	public function __construct() {
		add_shortcode( self::SHORTCODE, [ $this, 'do_shortcode' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'register_ui_assets' ] );

		$this->gravitycharts = Plugin::get_instance();
	}

	/**
	 * Returns class instance.
	 *
	 * @since 1.0.4
	 *
	 * @return Shortcode
	 */
	public static function get_instance(): Shortcode {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new Shortcode();
		}

		return self::$_instance;
	}

	/**
	 * Processes the shortcode.
	 *
	 * @since 1.1
	 *
	 * @param string|array $atts Shortcode attributes.
	 *
	 * @return void|string
	 */
	public function do_shortcode( $atts = [] ) {

		$this->gravitycharts->init();

		if ( is_string( $atts ) ) {
			$atts = shortcode_parse_atts( $atts );
		}

		if ( ! is_array( $atts ) ) {
			$this->gravitycharts->chart_feed->log_error( '[gravitycharts] shortcode $atts parameter is invalid: ' . print_r( $atts, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

			return;
		}

		$feed_id  = (int) rgar( $atts, 'id', 0 );
		$entry_id = (int) rgar( $atts, 'entry', 0 );

		if ( empty( $feed_id ) ) {
			$this->gravitycharts->chart_feed->log_error( '[gravitycharts] shortcode is missing the feed ID' );

			return;
		}

		$chart_data = $this->gravitycharts->api->get_chart( $feed_id, $entry_id );

		if ( empty( $chart_data ) ) {
			$error_message = '[gravitycharts] shortcode data for feed ID #%d';

			if ( $entry_id ) {
				$error_message .= ' and entry ID #%d';
			}

			$error_message .= ' is empty';

			$this->gravitycharts->chart_feed->log_error( sprintf( $error_message, $feed_id, $entry_id ) );

			return;
		}

		$style_attr = '';
		$aria_label = rgar( $chart_data, 'ariaLabel', '' );
		$height     = rgar( $atts, 'height' );
		$width      = rgar( $atts, 'width' );
		$embed_type = rgar( $atts, 'embed_type' );

		if ( 'image' === $embed_type ) {

			$chart = new QuickChart(
				[
					'width'   => $width,
					'height'  => $height,
					'version' => GK_GRAVITYCHARTS_CHART_JS_VERSION,
				]
			);

			$config = $this->gravitycharts->api->get_chart_js( $feed_id, $entry_id );

			$chart->setConfig( $config );

			/**
			 * Override QuickChart settings for the chart.
			 *
			 * @since 1.2
			 *
			 * @param QuickChart $chart
			 * @param [] $atts
			 */
			do_action_ref_array( 'gk/gravitycharts/image-charts/quickchart/instance', [ $chart, $atts ] );

			$chart_template = <<<HTML
<div class="gk-gravitycharts-shortcode">
	<img src="{$chart->getUrl()}" width="{$chart->getWidth()}" height="{$chart->getHeight()}" style="max-width: 100%; aspect-ratio: initial;" alt="{aria_label}" />
</div>
HTML;

		} else {

			wp_enqueue_script( self::ASSETS_HANDLE );

			if ( $height || $width ) {
				$style_attr .= 'position: relative;';
				$style_attr .= $height ? sprintf( 'max-height: %spx;', (int) $height ) : '';
				$style_attr .= $width ? sprintf( 'max-width: %spx;', (int) $width ) : '';
			}

			$chart_template = <<<HTML
<div class="gk-gravitycharts-shortcode" style="display: none !important; {style_attr}" data-chart-feed-id="{chart_feed_id}" data-chart-data="{chart_data}">
	<canvas aria-label="{aria_label}" style="{style_attr}"></canvas>
</div>
HTML;
		}

		$output = strtr(
			$chart_template,
			[
				'{chart_feed_id}'          => esc_attr( $atts['id'] ),
				'{chart_data}'             => esc_attr( wp_json_encode( $chart_data ) ),
				'{chart_data_url_encoded}' => rawurlencode( wp_json_encode( $chart_data ) ),
				'{aria_label}'             => esc_attr( $aria_label ),
				'{style_attr}'             => esc_attr( $style_attr ),
			]
		);

		return $output;
	}

	/**
	 * Registers UI assets.
	 *
	 * @since 1.1
	 *
	 * @return void
	 */
	public function register_ui_assets(): void {
		$script = '/build/shortcode.js';

		wp_register_script(
			self::ASSETS_HANDLE,
			plugins_url( $script, GK_GRAVITYCHARTS_PLUGIN_FILE ),
			[ 'gravitycharts-chart-js' ],
			filemtime( plugin_dir_path( GK_GRAVITYCHARTS_PLUGIN_FILE ) . $script ),
			true
		);
	}
}
