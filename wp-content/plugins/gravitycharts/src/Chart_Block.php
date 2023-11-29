<?php
/**
 * Display the GravityCharts chart as a block.
 *
 * @package GravityKit\GravityCharts
 */

namespace GravityKit\GravityCharts;

use GravityKitFoundation;

/**
 * GravityCharts Chart Block.
 */
class Chart_Block {
	/**
	 * The block's namespace.
	 *
	 * @var string
	 */
	const BLOCK_NAMESPACE = 'gk-gravitycharts';

	/**
	 * The block's slug.
	 *
	 * @var string
	 */
	const BLOCK_SLUG = 'chart';

	/**
	 * GravityCharts plugin instance.
	 *
	 * @var Plugin
	 */
	private $gravitycharts;

	/**
	 * Class instance.
	 *
	 * @since 1.0.4
	 *
	 * @var Chart_Block
	 */
	private static $_instance;

	/**
	 * Chart Block constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_assets' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'register_assets' ], 9 );

		$this->register_block();

		$this->gravitycharts = Plugin::get_instance();

		add_filter( 'render_block', [ $this, 'render_block' ], 10, 2 );
	}

	/**
	 * Returns class instance.
	 *
	 * @since 1.0.4
	 *
	 * @return Chart_Block
	 */
	public static function get_instance(): Chart_Block {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new Chart_Block();
		}

		return self::$_instance;
	}

	/**
	 * Registers the block scripts and styles.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function register_assets(): void {
		// Already registered.
		if ( wp_script_is( 'gravitycharts-block-js' ) ) {
			return;
		}

		$version = GK_GRAVITYCHARTS_PLUGIN_VERSION;

		// Register block styles for both frontend + backend.
		wp_register_style(
			'gravitycharts-style-css',
			plugins_url( '/build/style.css', dirname( __FILE__ ) ),
			is_admin() ? [ 'wp-editor' ] : null,
			$version
		);

		// Register block editor styles for backend.
		wp_register_style(
			'gravitycharts-editor-css', // Handle.
			plugins_url( '/build/editor.css', dirname( __FILE__ ) ),
			[ 'wp-edit-blocks' ],
			$version
		);

		// Register block editor script for backend.
		wp_register_script(
			'gravitycharts-block-js',
			plugins_url( '/build/blocks.js', dirname( __FILE__ ) ),
			[ 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ],
			$version,
			true
		);

		$editor_data = [
			'gravityFormsUrl'   => esc_url_raw( admin_url( 'admin.php?page=gf_edit_forms' ) ),
			'editChartUrl'      => esc_url_raw( admin_url( 'admin.php?page=gf_edit_forms&view=settings&subview=gravitycharts' ) ),
			'gettingStartedUrl' => esc_url_raw( 'https://docs.gravitykit.com/article/841-getting-started-with-gravitycharts' ),
		];

		wp_localize_script( 'gravitycharts-block-js', 'gravitycharts', $editor_data );

		GravityKitFoundation::translations()->load_frontend_translations( 'gk-gravitycharts' );

		// Register chart.js script.
		wp_register_script(
			'gravitycharts-chart-js',
			plugins_url( '/build/gravitychart-plugin.js', dirname( __FILE__ ) ),
			[],
			$version,
			true
		);
	}

	/**
	 * Registers the block.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function register_block(): void {

		static $is_registered;

		if ( $is_registered ) {
			return;
		}

		register_block_type(
			self::BLOCK_NAMESPACE . '/' . self::BLOCK_SLUG,
			[
				'style'         => '',
				'script'        => [ '', 'gravitycharts-chart-js-plugin-datalabels' ],
				'editor_style'  => 'gravitycharts-editor-css',
				'editor_script' => 'gravitycharts-block-js',
			]
		);

		$is_registered = true;
	}

	/**
	 * Outputs the Chart.js initializer in a <script> tag when the block is output.
	 *
	 * @since 1.0
	 *
	 * @param string $block_content The block content about to be appended.
	 * @param array  $block         The full block, including name and attributes.
	 *
	 * @return string Modified block content.
	 */
	public function render_block( string $block_content, array $block ): string {
		if ( 'gk-gravitycharts/chart' !== rgar( $block, 'blockName' ) ) {
			return $block_content;
		}

		/**  Only enqueue assets when needed, instead of every page when using register_block_type() {@see register_block} */
		wp_enqueue_style( 'gravitycharts-style-css' );
		wp_enqueue_script( 'gravitycharts-chart-js' );

		global $current_screen;

		if ( $current_screen && $current_screen->is_block_editor() ) {
			return $block_content;
		}

		static $block_counter    = 0;
		static $displayed_blocks = [];

		$feed_id = (int) rgars( $block, 'attrs/feedId' );

		if ( ! $feed_id ) {
			return '';
		}

		$entry_id   = (int) rgars( $block, 'attrs/entryId', 0 );
		$embed_type = rgars( $block, 'attrs/embedType', 'chart' );

		$this->gravitycharts->init();

		$block_class = rgars( $block, 'attrs/blockClassName' );
		$block_id    = "chart-${feed_id}-${entry_id}-${embed_type}-${block_counter}";
		$chart       = $this->gravitycharts->api->get_chart( $feed_id, $entry_id );
		$json        = wp_json_encode( $chart );
		$aria_label  = esc_js( $chart['ariaLabel'] );

		if ( in_array( $block_id, $displayed_blocks, true ) ) {
			return $block_content;
		}

		if ( 'image' === $embed_type ) {
			$atts = [
				'width'   => 500,
				'height'  => 300,
				'version' => GK_GRAVITYCHARTS_CHART_JS_VERSION,
			];

			$quick_chart = new QuickChart(
				$atts
			);

			$config = $this->gravitycharts->api->get_chart_js( $feed_id, $entry_id );
			$quick_chart->setConfig( $config );

			/**
			 * Override QuickChart settings for the chart.
			 *
			 * @since 1.2
			 *
			 * @param QuickChart $quick_chart
			 * @param [] $atts
			 */
			do_action_ref_array( 'gk/gravitycharts/image-charts/quickchart/instance', [ $quick_chart, $atts ] );

			return <<<HTML
<div class="gk-gravitycharts-shortcode">
	<img src="{$quick_chart->getUrl()}" width="{$quick_chart->getWidth()}" height="{$quick_chart->getHeight()}" style="max-width: 100%; aspect-ratio: initial;" alt="{aria_label}" />
</div>
HTML;
		}

		wp_add_inline_script(
			'gravitycharts-chart-js',
			"GravityChart({$block_counter}, '{$aria_label}', $json);\n"
		);

		$block_counter ++;
		$displayed_blocks[] = $block_id;

		return $block_content;
	}
}
