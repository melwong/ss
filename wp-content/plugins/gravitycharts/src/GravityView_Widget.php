<?php
/**
 * Display the GravityCharts chart as GravityView widget.
 *
 * @package GravityKit\GravityCharts
 */

namespace GravityKit\GravityCharts;

if ( ! class_exists( '\GV\Widget' ) ) {
	return;
}

use GFAPI;
use GF_Query_Condition;
use GV\Widget;
use GV\View;
use GV\Entry_Collection;
use GravityKit\GravityCharts\QueryFilters\QueryFilters;

/**
 * GravityCharts GravityView widget.
 */
class GravityView_Widget extends Widget {
	/**
	 * Widget admin ID.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	protected $widget_id = 'gk-gravitycharts';

	/**
	 * Widget icon.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	public $icon = 'data:image/svg+xml,%3Csvg style="height: 24px; width: 37px;" enable-background="new 0 0 630 630" height="630" viewBox="0 0 630 630" width="630" xmlns="http://www.w3.org/2000/svg"%3E%3Cpath clip-rule="evenodd" d="m3.5 375.2v-264.2c0-6.6 4.9-12 12-12h23.9c6.2 0 11.9 5.4 11.9 12v264.2c0 6.4-5.7 12-11.9 12h-23.9c-7.1 0-12-5.6-12-12zm611.5 107.8h-530l188.6-188.1 87 87.5c4.7 4.7 12.3 4.7 17 0l246-246c4.7-4.7 4.7-12.3 0-17l-17-17c-4.7-4.7-12.3-4.7-17 0l-220.5 221.2-87.6-88.1c-4.7-4.7-12.3-4.7-17 0l-254.2 254.8c-4.8 4.2-6.8 10.3-6.8 16.7v12c0 6.6 4.9 12 12 12h599.5c6.6 0 12-5.4 12-12v-24c0-6.6-5.4-12-12-12z" fill-rule="evenodd"/%3E%3C/svg%3E';

	/**
	 * GravityCharts plugin instance.
	 *
	 * @since 1.0
	 *
	 * @var Plugin
	 */
	private $gravitycharts;

	/**
	 * Query Filters instance.
	 *
	 * @since 1.0
	 *
	 * @var QueryFilters
	 */
	public $query_filters;

	/**
	 * Class instance.
	 *
	 * @since 1.0.4
	 *
	 * @var GravityView_Widget
	 */
	private static $_instance;

	/**
	 * Initializes widget and configures hooks.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->widget_label       = esc_html__( 'GravityCharts', 'gk-gravitycharts' );
		$this->widget_description = esc_html__( 'Display View entries as a chart.', 'gk-gravitycharts' );
		$this->query_filters      = new QueryFilters();
		$this->gravitycharts      = Plugin::get_instance();

		$default_values = [
			'header' => 1,
			'footer' => 1,
		];

		$widget_options = []; // Defined at render time (see the set_widget_options() method).

		add_filter( 'gravityview_template_widget_options', [ $this, 'set_widget_options' ], 10, 6 );

		add_filter( 'gravityview/datatables/output', [ $this, 'modify_datatables_output' ], 10, 3 );

		// Enqueue assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_ui_assets' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_ui_assets' ] );
		add_action( 'gravityview/shortcode/before-processing', [ $this, 'gravityview_shortcode_embed' ] );

		parent::__construct( $this->widget_label, $this->get_widget_id(), $default_values, $widget_options );
	}

	/**
	 * Returns class instance.
	 *
	 * @since 1.0.4
	 *
	 * @return GravityView_Widget
	 */
	public static function get_instance(): GravityView_Widget {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new GravityView_Widget();
		}

		return self::$_instance;
	}

	/**
	 * Returns GravityCharts plugin instance.
	 *
	 * @since 1.0
	 *
	 * @return Plugin
	 */
	public function gravitycharts() {
		if ( ! $this->gravitycharts->api ) {
			$this->gravitycharts->init();
		}

		return $this->gravitycharts;
	}

	/**
	 * Performs extra logic before the embedded [gravityview] shortcode is processed.
	 *
	 * @since 1.0.3
	 *
	 * @param View|null $view GravityView View.
	 *
	 * @return void
	 */
	public function gravityview_shortcode_embed( $view ) {
		$this->enqueue_ui_assets( null, $view );
	}

	/**
	 * Enqueues UI assets.
	 *
	 * @since 1.0
	 * @since 1.0.3 Added $hook & $view params.
	 *
	 * @param string|null $hook Current admin page.
	 * @param View|null   $view GravityView View.
	 *
	 * @return void
	 */
	public function enqueue_ui_assets( $hook = null, $view = null ): void {
		if ( ! $view instanceof View && ( ! function_exists( 'gravityview' ) || ! gravityview()->request->is_view( false ) ) ) {
			return;
		}

		if ( ! $view ) {
			$view = gravityview()->request->is_view();
		}

		$handle = 'gravityview-gravitycharts-widget';

		// Only add GravityView-specific scripts if we are in a View editor screen.
		if ( gravityview()->request->is_admin( '', 'single' ) ) {
			wp_enqueue_script(
				$handle,
				plugins_url( '/build/gravityview-widget-admin.js', GK_GRAVITYCHARTS_PLUGIN_FILE ),
				[ 'gravityview_views_scripts', 'jquery' ],
				filemtime( plugin_dir_path( GK_GRAVITYCHARTS_PLUGIN_FILE ) . 'build/gravityview-widget-admin.js' ),
				true
			);

			return;
		}

		if ( ! $view->widgets->by_id( $this->get_widget_id() )->count() ) {
			return;
		};

		wp_enqueue_script(
			$handle,
			plugins_url( '/build/gravityview-widget.js', GK_GRAVITYCHARTS_PLUGIN_FILE ),
			[ 'gravitycharts-chart-js', 'jquery' ],
			filemtime( plugin_dir_path( GK_GRAVITYCHARTS_PLUGIN_FILE ) . 'build/gravityview-widget.js' ),
			true
		);
	}

	/**
	 * Gets a list of chart feeds formatted for use as select element options.
	 *
	 * @since 1.0
	 *
	 * @param string|int $form_id GF form ID.
	 *
	 * @return array
	 */
	public function get_chart_feeds( $form_id ): array {
		$feeds = GFAPI::get_feeds( null, $form_id, $this->gravitycharts()->chart_feed->get_slug() );

		if ( is_wp_error( $feeds ) ) {
			return [];
		}

		$feeds_data = [
			'' => esc_html__( 'Select a chart feed', 'gk-gravitycharts' ),
		];

		foreach ( $feeds as $feed ) {
			if ( ! isset( $feed['meta']['chartName'] ) || ! isset( $feed['id'] ) ) {
				continue;
			}

			$feeds_data[ $feed['id'] ] = $feed['meta']['chartName'];
		}

		$field_settings = [
			'type'    => 'select',
			'label'   => sprintf( '%s:', esc_html__( 'Chart', 'gk-gravitycharts' ) ),
			'value'   => '',
			'choices' => $feeds_data,
		];

		return $field_settings;
	}

	/**
	 * Sets widget options before it is rendered.
	 *
	 * @since 1.0
	 *
	 * @param array      $field_options Widget field options.
	 * @param string     $template_id   GV table slug.
	 * @param string     $widget_id     Widget ID.
	 * @param string     $context       GV View context (e.g., header, footer).
	 * @param string     $input_type    Widget field input type.
	 * @param string|int $form_id       GF form ID.
	 *
	 * @return array
	 */
	public function set_widget_options( $field_options, $template_id, $widget_id, $context, $input_type, $form_id ): array {
		if ( $this->get_widget_id() !== $widget_id ) {
			return $field_options;
		}

		$chart_feeds = $this->get_chart_feeds( $form_id );

		if ( empty( $chart_feeds ) ) {
			$new_feed_url = admin_url(
				sprintf(
					'admin.php?page=gf_edit_forms&view=settings&subview=%s&id=%s&fid=0',
					$this->gravitycharts()->chart_feed->get_slug(),
					$form_id
				)
			);

			return [
				'no_feeds' => [
					'type' => 'html',
					'desc' => $this->gravitycharts()->chart_feed->empty_feeds_list_message( $new_feed_url ),
				],
			];
		}

		return [
			'feed'            => $chart_feeds,
			'display_entries' => [
				'type'       => 'radio',
				'full_width' => true,
				'label'      => esc_html__( 'Display Data For:', 'gk-gravitycharts' ),
				'options'    => [
					'all'     => esc_html__( 'All View entries', 'gk-gravitycharts' ),
					'visible' => esc_html__( 'Entries shown on the page', 'gk-gravitycharts' ),
				],
				'value'      => 'all',
			],
			'height'          => [
				'type'  => 'number',
				'label' => esc_html__( 'Max Height in Pixels', 'gk-gravitycharts' ),
				'desc'  => esc_html__( 'If not set, the chart will resize automatically.', 'gk-gravitycharts' ),
				'step'  => 1,
				'value' => '',
			],
			'width'           => [
				'type'  => 'number',
				'label' => esc_html__( 'Max Width in Pixels', 'gk-gravitycharts' ),
				'desc'  => esc_html__( 'If not set, the chart will resize automatically.', 'gk-gravitycharts' ),
				'step'  => 1,
				'value' => '',
			],
		];
	}

	/**
	 * Renders widget in the frontend.
	 *
	 * @since 1.0
	 *
	 * @param array                      $widget_options Widget options.
	 * @param string                     $content        Widget content.
	 * @param \GV\Template_Context|mixed $context        GV View context.
	 *
	 * @return void
	 */
	public function render_frontend( $widget_options, $content = '', $context = '' ): void {
		$view = View::by_id( gravityview_get_view_id() );
		$feed = GFAPI::get_feed( $widget_options['feed'] );

		if ( ! $view || is_wp_error( $feed ) ) {
			return;
		}

		$is_datatables = 'datatables_table' === $view->settings->get( 'template' ) && 'all' !== $widget_options['display_entries'];

		$get_entries = function ( $entries, $feed, $form, $filters ) use ( $is_datatables, $widget_options, $view, $context ) {
			return $is_datatables ? [] : $this->get_entries( $view, $context, $widget_options, $form, $filters );
		};

		add_filter( 'gk/gravitycharts/api/entries', $get_entries, 10, 4 );

		$chart_data = $this->gravitycharts()->api->get_chart( (int) $feed['id'] );

		remove_filter( 'gk/gravitycharts/api/entries', $get_entries );

		if ( ! $is_datatables && empty( $chart_data ) ) {
			return;
		}

		$aria_label = rgar( $chart_data, 'ariaLabel', '' );
		$height     = rgar( $widget_options, 'height' );
		$width      = rgar( $widget_options, 'width' );
		$style_attr = '';

		if ( $height || $width ) {
			$style_attr .= 'position: relative;';
			$style_attr .= $height ? sprintf( 'max-height: %spx;', (int) $height ) : '';
			$style_attr .= $width ? sprintf( 'max-width: %spx;', (int) $width ) : '';
		}

		$chart_template = <<<HTML
<div class="{widget_id}" style="display: none !important; {style_attr}" data-chart-data="{chart_data}" data-datatables="{datatables}">
	<canvas aria-label="{aria_label}" style="{style_attr}"></canvas>
</div>
HTML;

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo strtr(
			$chart_template,
			[
				'{widget_id}'  => esc_attr( $this->get_widget_id() ),
				'{chart_data}' => esc_attr( wp_json_encode( $chart_data ) ),
				'{datatables}' => $is_datatables ? 'true' : 'false',
				'{aria_label}' => esc_attr( $aria_label ),
				'{style_attr}' => esc_attr( $style_attr ),
			]
		);
	}

	/**
	 * Get sentries based on the View configuration.
	 *
	 * @since 1.0
	 *
	 * @param View  $view                      GravityView View.
	 * @param mixed $context                   GravityView View context.
	 * @param array $widget_config             Widget configuration.
	 * @param array $form                      GF Form.
	 * @param mixed $conditional_logic_filters GravityCharts feed conditional logic.
	 *
	 * @return array
	 */
	public function get_entries( View $view, $context, array $widget_config, array $form, $conditional_logic_filters ): array {
		$query_filters_callback = function ( &$query ) use ( $widget_config, $form, $conditional_logic_filters ) {
			try {
				$this->query_filters->set_form( $form );

				$this->query_filters->set_filters( $conditional_logic_filters );

				$conditions = $this->query_filters->get_query_conditions();

				$query_parts = $query->_introspect();

				$query->where( GF_Query_Condition::_and( $query_parts['where'], $conditions ) );
			} catch ( \Exception $e ) {
				Plugin::get_instance()->chart_feed->log_error(
					sprintf(
						'Error applying conditional logic to GV widget for feed #%s: %s',
						rgar( $widget_config, 'feed' ),
						$e->getMessage()
					)
				);
			}
		};

		if ( $conditional_logic_filters && 'null' !== $conditional_logic_filters && 'visible' !== rgar( $widget_config, 'display_entries' ) ) {
			add_filter( 'gravityview/view/query', $query_filters_callback );
		}

		$all_entries_callback = function ( &$query ) use ( $widget_config ) {
			$query->limit( 0 );
			$query->offset( 0 );
		};

		if ( 'all' === rgar( $widget_config, 'display_entries' ) ) {
			add_filter( 'gravityview/view/query', $all_entries_callback );
		}

		$entries = $view->get_entries()->all();

		remove_filter( 'gravityview/view/query', $query_filters_callback );
		remove_filter( 'gravityview/view/query', $all_entries_callback );

		return $entries;
	}

	/**
	 * Injects updated charts data into the DataTables AJAX request output object.
	 *
	 * @since 1.0
	 *
	 * @param array                 $output  AJAX output.
	 * @param View                  $view    GV View.
	 * @param Entry_Collection|null $entries View entries.
	 *
	 * @return array
	 */
	public function modify_datatables_output( $output, $view, $entries = null ): array {
		if ( ! $entries instanceof Entry_Collection ) {
			return $output;
		}

		$entries = $entries->all();

		$get_entries = function () use ( $entries ) {
			return $entries;
		};

		$output['gravitychartsWidgets'] = [];

		add_filter( 'gk/gravitycharts/api/entries', $get_entries );

		foreach ( $view->widgets->by_id( $this->get_widget_id() )->all() as $widget ) {
			$widget_options = $widget->as_configuration();
			$feed_id        = rgar( $widget_options, 'feed' );

			$feed = GFAPI::get_feed( $feed_id );

			if ( is_wp_error( $feed ) ) {
				continue;
			}

			$output['gravitychartsWidgets'][] = $this->gravitycharts()->api->get_chart( (int) $feed['id'] );
		}

		remove_filter( 'gk/gravitycharts/api/entries', $get_entries );

		return $output;
	}
}

