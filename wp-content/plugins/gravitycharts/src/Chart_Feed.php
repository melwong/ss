<?php
/**
 * Gravity Forms Chart Feed Add-on.
 *
 * @package GravityKit\GravityCharts
 */

namespace GravityKit\GravityCharts;

use Closure;
use Exception;
use GFForms;
use GFFeedAddOn;
use GravityKitFoundation;
use Gravity_Forms\Gravity_Forms\Settings\Fields;
use GravityKit\GravityCharts\QueryFilters\QueryFilters;

GFForms::include_feed_addon_framework();

/**
 * GravityCharts Chart Feed.
 */
class Chart_Feed extends GFFeedAddOn {
	/**
	 * Minimum Gravity Forms version.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	protected $_min_gravityforms_version = '2.5';

	/**
	 * GravityCharts slug.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	protected $_slug = 'gravitycharts';

	/**
	 * GravityCharts title.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	protected $_title = 'GravityCharts Feed Add-On';

	/**
	 * GravityCharts short title.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	protected $_short_title = 'GravityCharts';

	/**
	 * The plugin basename.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	protected $_path;

	/**
	 * The full path to the plugin.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	protected $_full_path;

	/**
	 * The version of the plugin.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	protected $_version = GK_GRAVITYCHARTS_PLUGIN_VERSION;

	/**
	 * The capability required to access the plugin.
	 *
	 * @since 1.0.2
	 *
	 * @var string
	 */
	protected $_capabilities_form_settings = 'manage_options';

	/**
	 * Query Filters instance.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	private $query_filters;

	/**
	 * Class instance.
	 *
	 * @since 1.0.4
	 *
	 * @var Chart_Feed
	 */
	private static $_instance;

	/**
	 * {@inheritdoc}
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->_path         = plugin_basename( GK_GRAVITYCHARTS_PLUGIN_FILE );
		$this->_full_path    = GK_GRAVITYCHARTS_PLUGIN_FILE;
		$this->query_filters = new QueryFilters();

		/**
		 * Modifies the default capability required to access the plugin.
		 *
		 * @filter `gk/gravitycharts/capabilities/access`
		 *
		 * @since  1.0.2
		 *
		 * @param string $capability
		 */
		$this->_capabilities_form_settings = apply_filters( 'gk/gravitycharts/capabilities/access', $this->_capabilities_form_settings );

		add_filter( 'gk/foundation/integrations/helpscout/display', [ $this, 'maybe_display_helpscout_beacon' ] );

		parent::__construct();
	}

	/**
	 * Returns class instance.
	 *
	 * @since 1.0.4
	 *
	 * @return Chart_Feed
	 */
	public static function get_instance(): Chart_Feed {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new Chart_Feed();
		}

		return self::$_instance;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 1.0
	 */
	public function init() {
		parent::init();

		add_filter( 'gform_noconflict_scripts', Closure::fromCallable( [ $this, 'allow_ui_assets' ] ) );
		add_filter( 'gform_noconflict_styles', Closure::fromCallable( [ $this, 'allow_ui_assets' ] ) );
		add_filter( 'gform_tooltips', Closure::fromCallable( [ $this, 'add_tooltips' ] ) );

		add_action( 'admin_enqueue_scripts', Closure::fromCallable( [ $this, 'configure_ui_translations' ] ) );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 1.0
	 */
	public function get_menu_icon(): string {
		return '<svg style="height: 24px; width: 37px;" enable-background="new 0 0 630 630" height="630" viewBox="0 0 630 630" width="630" xmlns="http://www.w3.org/2000/svg"><path clip-rule="evenodd" d="m3.5 375.2v-264.2c0-6.6 4.9-12 12-12h23.9c6.2 0 11.9 5.4 11.9 12v264.2c0 6.4-5.7 12-11.9 12h-23.9c-7.1 0-12-5.6-12-12zm611.5 107.8h-530l188.6-188.1 87 87.5c4.7 4.7 12.3 4.7 17 0l246-246c4.7-4.7 4.7-12.3 0-17l-17-17c-4.7-4.7-12.3-4.7-17 0l-220.5 221.2-87.6-88.1c-4.7-4.7-12.3-4.7-17 0l-254.2 254.8c-4.8 4.2-6.8 10.3-6.8 16.7v12c0 6.6 4.9 12 12 12h599.5c6.6 0 12-5.4 12-12v-24c0-6.6-5.4-12-12-12z" fill-rule="evenodd"/></svg>';
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function feed_list_no_item_message(): string {
		return $this->empty_feeds_list_message();
	}

	/**
	 * Shows message when the feed list is empty.
	 *
	 * @since 1.0
	 *
	 * @param string|null $url URL to create the new feed.
	 *
	 * @return string The message.
	 */
	public function empty_feeds_list_message( $url = null ): string {
		$url = $url ? $url : add_query_arg( [ 'fid' => 0 ] );

		$output  = '<div class="gk-gravitycharts-no-feeds">';
		$output .= sprintf( '<img src="%s" width="150" height="205" alt="" style="padding: 0 20px 20px 20px; max-width: 40%%; display: block;" class="alignright" />', esc_url( plugins_url( 'images/floaty-sitting.svg', GK_GRAVITYCHARTS_PLUGIN_FILE ) ) );
		// translators: %1$s and %2$s are replaced by HTML.
		$output .= '<h5 style="font-size: 24px; font-weight: normal; max-width: 30rem; line-height: 1.4; padding: 10px; margin-top: 0; display: table-cell; vertical-align: middle; height: 185px; min-width: 60%">' . sprintf( esc_html__( "There are no charts configured for this form. Let's go %1\$screate one%2\$s!", 'gk-gravitycharts' ), "<a href='" . esc_url( $url ) . "'>", '</a>' ) . '</h5>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws Exception Throws an exception if there's an invalid form object.
	 *
	 * @since 1.0
	 */
	public function scripts(): array {
		$script_file = 'build/form-settings.js';

		if ( ! file_exists( plugin_dir_path( GK_GRAVITYCHARTS_PLUGIN_FILE ) . $script_file ) ) {
			GravityKitFoundation::logger( 'gravitycharts', 'GravityCharts' )->error( "${script_file} not found - unable to load UI assets." );

			return parent::scripts();
		}

		// Sanity check for GF 2.5.
		if ( is_callable( [ $this, 'is_feed_edit_page' ] ) && $this->is_feed_edit_page() ) {
			$this->query_filters->set_form( $this->get_current_form() );
			$this->query_filters->enqueue_scripts(
				[
					'input_element_name' => '_gform_setting_conditional_logic',
					'conditions'         => rgar( $this->get_current_settings() ?? [], 'conditional_logic' ),
				]
			);
		}

		$scripts = [
			[
				'handle'    => 'gravitycharts_feed_settings',
				'src'       => plugin_dir_url( GK_GRAVITYCHARTS_PLUGIN_FILE ) . $script_file,
				'version'   => filemtime( plugin_dir_path( GK_GRAVITYCHARTS_PLUGIN_FILE ) . $script_file ),
				'in_footer' => false,
				'deps'      => [ 'wp-api-fetch', 'jquery-ui-draggable', 'jquery-ui-resizable' ],
				'strings'   => [
					'settingsMap'    => $this->settings_to_chartjs_options_map(),
					'defaultOptions' => $this->default_chartjs_options(),
					'colorPalettes'  => Color_Pallets::get_all(),
				],
				'enqueue'   => [
					[
						'admin_page' => [ 'form_settings' ],
						'tab'        => $this->_slug,
					],
				],
			],
		];

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 1.0
	 */
	public function styles(): array {
		$style_file = 'build/feed.css';

		if ( ! file_exists( plugin_dir_path( GK_GRAVITYCHARTS_PLUGIN_FILE ) . $style_file ) ) {
			GravityKitFoundation::logger( 'gravitycharts', 'GravityCharts' )->error( "${style_file} not found - unable to load UI assets." );

			return parent::scripts();
		}

		// Sanity check for GF 2.5.
		if ( is_callable( [ $this, 'is_feed_edit_page' ] ) && $this->is_feed_edit_page() ) {
			$this->query_filters->enqueue_styles();
		}

		$styles = [
			[
				'handle'  => 'gravitycharts_feed_settings',
				'src'     => plugin_dir_url( GK_GRAVITYCHARTS_PLUGIN_FILE ) . $style_file,
				'version' => filemtime( plugin_dir_path( GK_GRAVITYCHARTS_PLUGIN_FILE ) . $style_file ),
				'enqueue' => [
					[
						'admin_page' => [ 'form_settings' ],
						'tab'        => $this->_slug,
					],
				],
			],
		];

		return array_merge( parent::styles(), $styles );
	}

	/**
	 * Configures which columns should be displayed on the feed list page.
	 *
	 * @since 1.0
	 *
	 * @return array
	 * @uses  get_column_value_previewLink
	 */
	public function feed_list_columns(): array {
		return [
			'chartName'   => esc_html__( 'Name', 'gk-gravitycharts' ),
			'chartType'   => esc_html__( 'Chart Type', 'gk-gravitycharts' ),
			'shortcode'   => esc_html__( 'Shortcode', 'gk-gravitycharts' ),
			'previewLink' => esc_html__( 'Preview', 'gk-gravitycharts' ),
		];
	}

	/**
	 * Formats the value to be displayed in the preview column.
	 *
	 * @since   1.0
	 * @used-by GFFeedAddOn::get_column_value()
	 *
	 * @param array $feed The feed being included in the feed list.
	 *
	 * @return string
	 */
	public function get_column_value_chartType( array $feed ): string {

		$chart_type = Chart_Types::get( $feed['meta']['chartType'] );

		return $chart_type ? $chart_type['label'] : $feed['meta']['chartType'];
	}

	/**
	 * Formats the value to be displayed in the preview column.
	 *
	 * @since   1.0
	 * @used-by GFFeedAddOn::get_column_value()
	 *
	 * @param array $feed The feed being included in the feed list.
	 *
	 * @return string
	 */
	public function get_column_value_chartName( array $feed ): string {

		if ( '' !== rgars( $feed, 'meta/chartName' ) ) {
			return esc_html( $feed['meta']['chartName'] );
		}

		// translators: %d is replaced by the ID of the chart.
		return esc_html( sprintf( __( 'Chart #%d', 'gk-gravitycharts' ), $feed['id'] ) );
	}

	/**
	 * Formats the value to be displayed in the preview column.
	 *
	 * @since   1.0
	 * @used-by GFFeedAddOn::get_column_value()
	 *
	 * @param array $feed The feed being included in the feed list.
	 *
	 * @return string
	 */
	public function get_column_value_previewLink( array $feed ): string {
		$url = admin_url(
			sprintf(
				'admin.php?page=gf_edit_forms&view=settings&subview=%1$s&fid=%2$s&id=%3$s#%4$s',
				$this->_slug,
				$feed['id'],
				$feed['form_id'],
				'gform-settings-section-section-chart-preview'
			)
		);

		return sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $url ),
			esc_html__( 'Preview Chart', 'gk-gravitycharts' )
		);
	}

	/**
	 * Displays the chart's shortcode in the preview column.
	 *
	 * @since   1.1
	 *
	 * @param array $feed The feed being included in the feed list.
	 *
	 * @return string
	 */
	public function get_column_value_shortcode( array $feed ): string {
		return sprintf(
			'<input class="code" readonly value="[%s id=&quot;%s&quot;]" />',
			Shortcode::SHORTCODE,
			$feed['id']
		);
	}

	/**
	 * Configures the settings which should be rendered on the feed edit page under Form Settings > GravityCharts.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function feed_settings_fields(): array {
		$fields = [
			[
				'fields' => [
					[
						'name'     => 'chartName',
						'label'    => esc_html__( 'Chart Name', 'gk-gravitycharts' ),
						'required' => false,
						'type'     => 'text',
						'class'    => 'small',
						'tooltip'  => sprintf(
							'<h6>%s</h6> %s',
							esc_html__( 'Feed Name', 'gk-gravitycharts' ),
							esc_html__( 'Enter a feed name to uniquely identify this configuration.', 'gk-gravitycharts' )
						),
					],
					[
						'name'          => 'chartType',
						'label'         => esc_html__( 'Chart Type', 'gk-gravitycharts' ),
						'type'          => 'radio',
						'required'      => true,
						'class'         => 'gravitycharts-option',
						'default_value' => Chart_Types::DEFAULT_TYPE,
						'choices'       => Chart_Types::get_all(),
					],
				],
			],
			[
				'title'  => esc_html__( 'Chart Data', 'gk-gravitycharts' ),
				'fields' => [
					[
						'name'              => 'dataType',
						'label'             => esc_html__( 'Data Type', 'gk-gravitycharts' ),
						'description'       => esc_html__( 'Choose data type to be charted. Either by field or by timeline.', 'gk-gravitycharts' ),
						'type'              => 'select',
						'choices'           => Data_Types::get_all(),
						'default_value'     => Data_Types::DEFAULT_TYPE,
						'data-chart-reload' => true,
						'dependency'        => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'timeline' ),
								],
							],
						],
					],
					[
						'name'              => 'dataField',
						'label'             => esc_html__( 'Data Source', 'gk-gravitycharts' ),
						'description'       => esc_html__( 'Choose a field to be charted. Limited field types are currently supported.', 'gk-gravitycharts' ),
						'tooltip'           => 'gravitycharts_supported_fields',
						'type'              => 'field_select',
						'required'          => true,
						'args'              => [
							'input_types' => [
								'checkbox',
								'select',
								'radio',
								'multiselect',
								'likert',
								'rank',
								'rating',
								'survey',
								'quiz',
							],
						],
						'data-chart-reload' => true,
						'dependency'        => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'dataType',
									'values' => 'field',
								],
							],
						],
					],
					[
						'name'              => 'timelineField',
						'label'             => esc_html__( 'Date field', 'gk-gravitycharts' ),
						'description'       => esc_html__( 'Choose a date field to use for charting.', 'gk-gravitycharts' ),
						'tooltip'           => esc_html__( 'The values of this field will be placed on the index axis.', 'gk-gravitycharts' ),
						'type'              => 'field_select',
						'required'          => true,
						'args'              => [
							'input_types'    => [ 'date' ],
							'append_choices' => [
								[
									'label' => esc_html__( 'Entry Date', 'gk-gravitycharts' ),
									'value' => 'date_created',
								],
							],
						],
						'default'           => 'date_created',
						'data-chart-reload' => true,
						'dependency'        => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'timeline' ),
								],
								[
									'field'  => 'dataType',
									'values' => 'timeline',
								],
							],
						],
					],
					[
						'name'              => 'timelineTypeField',
						'label'             => esc_html__( 'Timeline type', 'gk-gravitycharts' ),
						'description'       => esc_html__( 'Select what the the chart should show.', 'gk-gravitycharts' ),
						'type'              => 'select',
						'required'          => true,
						'choices'           => Data_Types::get_timeline_types(),
						'default'           => Data_Types::DEFAULT_TIMELINE_TYPE,
						'data-chart-reload' => true,
						'dependency'        => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'timeline' ),
								],
								[
									'field'  => 'dataType',
									'values' => 'timeline',
								],
								[
									'field' => 'timelineField',
								],
								[
									'field' => 'timelineDataField',
								],
							],
						],
					],
					[
						'name'              => 'timelineDataField',
						'label'             => esc_html__( 'Data Field', 'gk-gravitycharts' ),
						'description'       => esc_html__( 'Select the field the chart should show.', 'gk-gravitycharts' ),
						'tooltip'           => esc_html__( 'The values of this field are placed on the graph. You can therefore only select fields that have numeric values. Any non numeric values & fields are filtered out.', 'gk-gravitycharts' ),
						'type'              => 'field_select',
						'required'          => true,
						'args'              => [
							'append_choices'       => $this->getAdditionalTimelineFields(),
							'callback'             => \Closure::fromCallable( [ $this, 'filter_numeric_fields' ] ),
							'disable_first_choice' => true,
							'input_types'          => [
								'list',
								'checkbox',
								'radio',
								'select',
								'multiselect',
								'number',
							],
						],
						'data-chart-reload' => true,
						'dependency'        => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'timeline' ),
								],
								[
									'field'  => 'dataType',
									'values' => 'timeline',
								],
								[
									'field' => 'timelineField',
								],
								[
									'field'  => 'timelineTypeField',
									// todo: maybe add `support` for timeline types.
									'values' => array_values(
										array_filter(
											array_keys( Data_Types::get_timeline_types() ),
											function ( string $key ): bool {
												return 'entry_count' !== $key;
											}
										)
									),
								],
							],
						],
					],
					[
						'name'        => 'dataCondition',
						'full_screen' => false,
						'label'       => esc_html__( 'Conditional Logic', 'gk-gravitycharts' ),
						'tooltip'     => 'export_conditional_logic',
						'type'        => 'html',
						'html'        => '<div id="gk-query-filters"></div>',
					],
				],
			],
			[
				'title'  => esc_html__( 'Chart Colors', 'gk-gravitycharts' ),
				'class'  => 'gravitycharts-options-section',
				'fields' => [
					[
						'name'          => 'palette',
						'label'         => esc_html__( 'Color Palette', 'gk-gravitycharts' ),
						'type'          => 'radio',
						'class'         => 'gravitycharts-option small color-grid',
						'default_value' => 'default',
						'choices'       => $this->get_palette_choices(),
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'palette' ),
								],
							],
						],
					],
				],
			],
			[
				'title'  => esc_html__( 'Chart Styles', 'gk-gravitycharts' ),
				'class'  => 'gravitycharts-options-section',
				'fields' => [
					[
						'name'          => 'indexAxis',
						'label'         => esc_html__( 'Bar Direction', 'gk-gravitycharts' ),
						'type'          => 'radio',
						'choices'       => [
							[
								'label' => esc_html__( 'Vertical', 'gk-gravitycharts' ),
								'value' => 'x',
								'icon'  => Chart_Types::get( 'bar' )['icon'],
							],
							[
								'label' => esc_html__( 'Horizontal', 'gk-gravitycharts' ),
								'value' => 'y',
								'icon'  => Chart_Types::get( 'bar' )['icon'],
							],
						],
						'class'         => 'gravitycharts-option small',
						'default_value' => 'x',
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'indexAxis' ),
								],
							],
						],
					],
					[
						'name'          => 'tension',
						'label'         => esc_html__( 'Line Tension', 'gk-gravitycharts' ),
						'type'          => 'radio',
						'horizontal'    => true,
						'class'         => 'gravitycharts-option',
						'choices'       => [
							[
								'label' => esc_html__( 'No Curve', 'gk-gravitycharts' ),
								'value' => 0,
							],
							[
								'label' => esc_html__( 'Light Curve', 'gk-gravitycharts' ),
								'value' => 0.1,
							],
							[
								'label' => esc_html__( 'Medium Curve', 'gk-gravitycharts' ),
								'value' => 0.25,
							],
							[
								'label' => esc_html__( 'Strong Curve', 'gk-gravitycharts' ),
								'value' => 0.5,
							],
						],
						'default_value' => 0,
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'tension' ),
								],
							],
						],
					],
					[
						'name'          => 'fill',
						'label'         => esc_html__( 'Area fill', 'gk-gravitycharts' ),
						'type'          => 'radio',
						'horizontal'    => true,
						'class'         => 'gravitycharts-option',
						'choices'       => [
							[
								'label' => esc_html__( 'Disabled', 'gk-gravitycharts' ),
								'value' => false,
							],
							[
								'label' => esc_html__( 'On the line', 'gk-gravitycharts' ),
								'value' => 'origin',
							],
							[
								'label' => esc_html__( 'Below the line', 'gk-gravitycharts' ),
								'value' => 'start',
							],
							[
								'label' => esc_html__( 'Above the line', 'gk-gravitycharts' ),
								'value' => 'end',
							],

						],
						'default_value' => 'origin',
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'fill' ),
								],
							],
						],
					],
					[
						'name'          => 'cutout',
						'label'         => esc_html__( 'Cutout', 'gk-gravitycharts' ),
						'type'          => 'radio',
						'horizontal'    => true,
						'class'         => 'gravitycharts-option',
						'default_value' => '0',
						'choices'       => [
							[
								'label' => esc_html__( 'None (Pie&nbsp;Chart)', 'gk-gravitycharts' ),
								'value' => '0',
							],
							[
								'label' => esc_html__( 'Small', 'gk-gravitycharts' ),
								'value' => '20%',
							],
							[
								'label' => esc_html__( 'Medium', 'gk-gravitycharts' ),
								'value' => '40%',
							],
							[
								'label' => esc_html__( 'Large', 'gk-gravitycharts' ),
								'value' => '60%',
							],
							[
								'label' => esc_html__( 'Extra-Large', 'gk-gravitycharts' ),
								'value' => '80%',
							],
						],
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'cutout' ),
								],
							],
						],
					],
					[
						'name'          => 'offset',
						'label'         => esc_html__( 'Gap', 'gk-gravitycharts' ),
						'type'          => 'radio',
						'horizontal'    => true,
						'class'         => 'gravitycharts-option',
						'default_value' => '24',
						'choices'       => [
							[
								'label' => esc_html__( 'No Gap', 'gk-gravitycharts' ),
								'value' => '0',
							],
							[
								'label' => esc_html__( 'Small', 'gk-gravitycharts' ),
								'value' => '24',
							],
							[
								'label' => esc_html__( 'Medium', 'gk-gravitycharts' ),
								'value' => '48',
							],
							[
								'label' => esc_html__( 'Large', 'gk-gravitycharts' ),
								'value' => '96',
							],
							[
								'label' => esc_html__( 'Extra-Large', 'gk-gravitycharts' ),
								'value' => '192',
							],
						],
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'offset' ),
								],
							],
						],
					],
					[
						'name'          => 'borderWidth',
						'label'         => esc_html__( 'Border Width', 'gk-gravitycharts' ),
						'type'          => 'radio',
						'horizontal'    => true,
						'class'         => 'gravitycharts-option',
						'default_value' => '6',
						'choices'       => [
							[
								'label' => esc_html__( 'No Border', 'gk-gravitycharts' ),
								'value' => '0',
							],
							[
								'label' => esc_html__( 'Thin', 'gk-gravitycharts' ),
								'value' => '3',
							],
							[
								'label' => esc_html__( 'Medium', 'gk-gravitycharts' ),
								'value' => '6',
							],
							[
								'label' => esc_html__( 'Thick', 'gk-gravitycharts' ),
								'value' => '12',
							],
						],
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'borderWidth' ),
								],
							],
						],
					],
					[
						'name'          => 'pointRadius',
						'label'         => esc_html__( 'Point Size', 'gk-gravitycharts' ),
						'type'          => 'radio',
						'horizontal'    => true,
						'class'         => 'gravitycharts-option',
						'default_value' => '6',
						'choices'       => [
							[
								'label' => esc_html__( 'No Points', 'gk-gravitycharts' ),
								'value' => '0',
							],
							[
								'label' => esc_html__( 'Small', 'gk-gravitycharts' ),
								'value' => '6',
							],
							[
								'label' => esc_html__( 'Medium', 'gk-gravitycharts' ),
								'value' => '12',
							],
							[
								'label' => esc_html__( 'Large', 'gk-gravitycharts' ),
								'value' => '18',
							],
						],
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'pointRadius' ),
								],
							],
						],
					],
					[
						'name'          => 'pointStyle',
						'label'         => esc_html__( 'Point Style', 'gk-gravitycharts' ),
						'type'          => 'radio',
						'class'         => 'gravitycharts-option small',
						'default_value' => 'circle',
						'choices'       => [
							[
								'label' => esc_html__( 'Circle', 'gk-gravitycharts' ),
								'value' => 'circle',
								'icon'  => '<svg role="img" viewBox="0 0 12 12"><circle cx="6" cy="6" r="6"></circle></svg>',
							],
							[
								'label' => esc_html__( 'Square', 'gk-gravitycharts' ),
								'value' => 'rect',
								'icon'  => '<svg role="img" viewBox="0 0 12 12"><rect x="0" y="0" width="12" height="12"></rect></svg>',
							],
							[
								'label' => esc_html__( 'Round Rect', 'gk-gravitycharts' ),
								'value' => 'rectRounded',
								'icon'  => '<svg role="img" viewBox="0 0 12 12" xmlns="http://www.w3.org/2000/svg" width="12" height="12"><path d="M9 12H3c-1.7 0-3-1.3-3-3V3c0-1.7 1.3-3 3-3h6c1.7 0 3 1.3 3 3v6c0 1.7-1.3 3-3 3z"/></svg>',
							],
							[
								'label' => esc_html__( 'Diamond', 'gk-gravitycharts' ),
								'value' => 'rectRot',
								'icon'  => '<svg role="img" viewBox="0 0 12 12"><path d="M 6,0 L 12,6 L 6,12 L 0,6 z"></path></svg>',
							],
							[
								'label' => esc_html__( 'Triangle', 'gk-gravitycharts' ),
								'value' => 'triangle',
								'icon'  => '<svg role="img" viewBox="0 0 12 12"><path d="M 6,0 L 12,12 L 0,12"></path></svg>',
							],
						],
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'pointStyle' ),
								],
								[
									'field'  => 'pointRadius',
									'values' => [ '6', '12', '18' ],
								],
							],
						],
					],
				],
			],
			[
				'title'  => esc_html__( 'Axis', 'gk-gravitycharts' ),
				'class'  => 'gravitycharts-options-section',
				'fields' => [
					[
						'name'          => 'angleLinesDisplay',
						'label'         => esc_html__( 'Show Angle Lines', 'gk-gravitycharts' ),
						'type'          => 'toggle',
						'class'         => 'gravitycharts-option',
						'default_value' => true,
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'angleLinesDisplay' ),
								],
							],
						],
					],
					[
						'name'          => 'xGridDisplay',
						'label'         => esc_html__( 'Show X Axis Grid Lines', 'gk-gravitycharts' ),
						'type'          => 'toggle',
						'class'         => 'gravitycharts-option',
						'default_value' => true,
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'xGridDisplay' ),
								],
							],
						],
					],
					[
						'name'          => 'xTitleDisplay',
						'label'         => esc_html__( 'Show X Axis title', 'gk-gravitycharts' ),
						'type'          => 'toggle',
						'class'         => 'gravitycharts-option',
						'default_value' => false,
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'xGridDisplay' ),
								],
							],
						],
					],
					[
						'name'          => 'xTitleText',
						'label'         => esc_html__( 'X Axis title', 'gk-gravitycharts' ),
						'type'          => 'text',
						'class'         => 'gravitycharts-option',
						'default_value' => false,
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'xGridDisplay' ),
								],
								[
									'field'  => 'xTitleDisplay',
									'values' => [ true ],
								],
							],
						],
					],
					[
						'name'          => 'yGridDisplay',
						'label'         => esc_html__( 'Show Y Axis Grid Lines', 'gk-gravitycharts' ),
						'type'          => 'toggle',
						'class'         => 'gravitycharts-option',
						'default_value' => true,
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'yGridDisplay' ),
								],
							],
						],
					],
					[
						'name'          => 'yTitleDisplay',
						'label'         => esc_html__( 'Show Y Axis title', 'gk-gravitycharts' ),
						'type'          => 'toggle',
						'class'         => 'gravitycharts-option',
						'default_value' => false,
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'yGridDisplay' ),
								],
							],
						],
					],
					[
						'name'          => 'yTitleText',
						'label'         => esc_html__( 'Y Axis title', 'gk-gravitycharts' ),
						'type'          => 'text',
						'class'         => 'gravitycharts-option',
						'default_value' => false,
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'yGridDisplay' ),
								],
								[
									'field'  => 'yTitleDisplay',
									'values' => [ true ],
								],
							],
						],
					],
					[
						'name'          => 'rGridDisplay',
						'label'         => esc_html__( 'Show Grid Lines', 'gk-gravitycharts' ),
						'type'          => 'toggle',
						'class'         => 'gravitycharts-option',
						'default_value' => true,
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'rGridDisplay' ),
								],
							],
						],
					],
					[
						'name'          => 'ticksDisplay',
						'label'         => esc_html__( 'Show Scale Labels', 'gk-gravitycharts' ),
						'type'          => 'toggle',
						'class'         => 'gravitycharts-option',
						'default_value' => true,
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'ticksDisplay' ),
								],
							],
						],
					],
					[
						'name'          => 'pointLabelsDisplay',
						'label'         => esc_html__( 'Show Point Labels', 'gk-gravitycharts' ),
						'type'          => 'toggle',
						'class'         => 'gravitycharts-option',
						'default_value' => true,
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'pointLabelsDisplay' ),
								],
							],
						],
					],
					[
						'name'          => 'xAxisType',
						'type'          => 'hidden',
						'default_value' => null,
					],
					[
						'name'          => 'timelineTooltipFormat',
						'type'          => 'hidden',
						'default_value' => $this->timelineTooltipFormat(),
						'save_callback' => function ( $field, $value ) {
							$formats = $this->timelineDisplayFormats();
							$format  = $this->get_setting( 'xTimelineScale', 'day' );

							return $formats[ $format ];
						},
					],
					[
						'name'              => 'xTimelineScale',
						'label'             => esc_html__( 'Timeline Scale', 'gk-gravitycharts' ),
						'type'              => 'select',
						'class'             => 'gravitycharts-option',
						'choices'           => $this->timelineScales(),
						'default_value'     => 'day',
						'data-chart-reload' => true,
						'data-choices'      => wp_json_encode( $this->timelineDisplayFormats() ),
						'dependency'        => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'dataType',
									'values' => 'timeline',
								],
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'timeline' ),
								],
							],
						],
						// Update the hidden tooltip format.
						'onChange'          => 'document.getElementById("timelineTooltipFormat").value = JSON.parse(this.dataset.choices)[this.value]',
					],
					[
						'name'          => 'xTimelineSource',
						'label'         => esc_html__( 'Timeline Labels', 'gk-gravitycharts' ),
						'type'          => 'radio',
						'tooltip'       => esc_html__( 'Select which dates should be shown on the x-axis.', 'gk-gravitycharts' ),
						'class'         => 'gravitycharts-option',
						'default_value' => 'auto',
						'choices'       => [
							[
								'label' => esc_html__( 'Automatic', 'gk-gravitycharts' ),
								'value' => 'auto',
							],
							[
								'label' => esc_html__( 'Data points only', 'gk-gravitycharts' ),
								'value' => 'data',
							],
						],
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'dataType',
									'values' => 'timeline',
								],
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'timeline' ),
								],
							],
						],
					],
					[
						'name'          => 'autoScale',
						'label'         => esc_html__( 'Auto Scale', 'gk-gravitycharts' ),
						'type'          => 'toggle',
						'class'         => 'gravitycharts-option',
						'default_value' => true,
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'autoScale' ),
								],
							],
						],
					],
					[
						'name'          => 'min',
						'label'         => esc_html__( 'Scale Min', 'gk-gravitycharts' ),
						'type'          => 'text',
						'input_type'    => 'number',
						'autocomplete'  => 'off',
						'min'           => 0,
						'class'         => 'gravitycharts-option scale',
						'default_value' => 0,
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'min' ),
								],
								[
									'field'  => 'autoScale',
									'values' => [ false ],
								],
							],
						],
					],
					[
						'name'          => 'max',
						'label'         => esc_html__( 'Scale Max', 'gk-gravitycharts' ),
						'type'          => 'text',
						'input_type'    => 'number',
						'autocomplete'  => 'off',
						'min'           => 1,
						'class'         => 'gravitycharts-option scale',
						'default_value' => 1,
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field'  => 'chartType',
									'values' => Chart_Types::supports_setting( 'max' ),
								],
								[
									'field'  => 'autoScale',
									'values' => [ false ],
								],
							],
						],
					],
				],
			],
			[
				'title'  => esc_html__( 'Legend', 'gk-gravitycharts' ),
				'class'  => 'gravitycharts-options-section',
				'fields' => [
					[
						'name'          => 'useValues',
						'label'         => esc_html__( 'Use values for labels', 'gk-gravitycharts' ),
						'type'          => 'toggle',
						'class'         => 'gravitycharts-option',
						'default_value' => false,
					],
					[
						'name'          => 'titleDisplay',
						'label'         => esc_html__( 'Show Chart Title', 'gk-gravitycharts' ),
						'type'          => 'toggle',
						'class'         => 'gravitycharts-option',
						'default_value' => false,
					],
					[
						'name'          => 'titleText',
						'label'         => esc_html__( 'Chart Title Text', 'gk-gravitycharts' ),
						'type'          => 'text',
						'class'         => 'gravitycharts-option',
						'default_value' => '',
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field' => 'titleDisplay',
								],
							],
						],
					],
					[
						'name'          => 'titleSize',
						'label'         => esc_html__( 'Title Font Size', 'gk-gravitycharts' ),
						'type'          => 'text',
						'input_type'    => 'number',
						'min'           => 10,
						'max'           => 96,
						'class'         => 'gravitycharts-option',
						'default_value' => 22,
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field' => 'titleDisplay',
								],
							],
						],
					],
					[
						'name'          => 'legendDisplay',
						'label'         => esc_html__( 'Enable Legend', 'gk-gravitycharts' ),
						'type'          => 'toggle',
						'class'         => 'gravitycharts-option',
						'default_value' => true,
					],
					[
						'name'          => 'legendPosition',
						'label'         => esc_html__( 'Legend Position', 'gk-gravitycharts' ),
						'type'          => 'radio',
						'horizontal'    => true,
						'class'         => 'gravitycharts-option legend',
						'default_value' => 'top',
						'choices'       => [
							[
								'label' => esc_html__( 'Top', 'gk-gravitycharts' ),
								'value' => 'top',
							],
							[
								'label' => esc_html__( 'Bottom', 'gk-gravitycharts' ),
								'value' => 'bottom',
							],
							[
								'label' => esc_html__( 'Left', 'gk-gravitycharts' ),
								'value' => 'left',
							],
							[
								'label' => esc_html__( 'Right', 'gk-gravitycharts' ),
								'value' => 'right',
							],
						],
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field' => 'legendDisplay',
								],
							],
						],
					],
					[
						'name'          => 'legendAlign',
						'label'         => esc_html__( 'Legend Alignment', 'gk-gravitycharts' ),
						'type'          => 'radio',
						'horizontal'    => true,
						'class'         => 'gravitycharts-option legend',
						'default_value' => 'center',
						'choices'       => [
							[
								'label' => esc_html__( 'Start', 'gk-gravitycharts' ),
								'value' => 'start',
							],
							[
								'label' => esc_html__( 'Center', 'gk-gravitycharts' ),
								'value' => 'center',
							],
							[
								'label' => esc_html__( 'End', 'gk-gravitycharts' ),
								'value' => 'end',
							],
						],
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field' => 'legendDisplay',
								],
							],
						],
					],
				],
			],
			[
				'title'  => esc_html__( 'Labels', 'gk-gravitycharts' ),
				'class'  => 'gravitycharts-options-section',
				'fields' => [
					[
						'name'          => 'labelsDisplay',
						'label'         => esc_html__( 'Enable Labels', 'gk-gravitycharts' ),
						'type'          => 'toggle',
						'class'         => 'gravitycharts-option',
						'default_value' => true,
					],
					[
						'name'          => 'labelsText',
						'label'         => esc_html__( 'Label Text', 'gk-gravitycharts' ),
						'type'          => 'radio',
						'horizontal'    => true,
						'class'         => 'gravitycharts-option legend',
						'default_value' => 'value',
						'choices'       => [
							[
								'label' => esc_html__( 'Value', 'gk-gravitycharts' ),
								'value' => 'value',
							],
							[
								'label' => esc_html__( 'Label', 'gk-gravitycharts' ),
								'value' => 'label',
							],
							[
								'label' => esc_html__( 'Percentage', 'gk-gravitycharts' ),
								'value' => 'percentage',
							],
						],
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field' => 'labelsDisplay',
								],
							],
						],
					],
					[
						'name'          => 'labelsPosition',
						'label'         => esc_html__( 'Label Position', 'gk-gravitycharts' ),
						'type'          => 'radio',
						'horizontal'    => true,
						'class'         => 'gravitycharts-option legend',
						'default_value' => 'default',
						'choices'       => [
							[
								'label' => esc_html__( 'Default', 'gk-gravitycharts' ),
								'value' => 'default',
							],
							[
								'label' => esc_html__( 'Border', 'gk-gravitycharts' ),
								'value' => 'border',
							],
							[
								'label' => esc_html__( 'Outside', 'gk-gravitycharts' ),
								'value' => 'outside',
							],
						],
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field' => 'labelsDisplay',
								],
							],
						],
					],
					[
						'name'          => 'labelsSize',
						'label'         => esc_html__( 'Label Font Size', 'gk-gravitycharts' ),
						'type'          => 'text',
						'input_type'    => 'number',
						'min'           => 10,
						'max'           => 96,
						'class'         => 'gravitycharts-option',
						'default_value' => 14,
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field' => 'labelsDisplay',
								],
							],
						],
					],
					[
						'name'          => 'labelsColorCustom',
						'label'         => esc_html__( 'Label Color ', 'gk-gravitycharts' ),
						'tooltip'       => esc_html__( 'By default GravityCharts will determine which label color is appropriate. This allows you to force a single color.', 'gk-gravitycharts' ),
						'type'          => 'radio',
						'horizontal'    => true,
						'choices'       => [
							[
								'value' => false,
								'label' => esc_html__( 'Automatic', 'gk-gravitycharts' ),
							],
							[
								'value' => true,
								'label' => esc_html__( 'Custom', 'gk-gravitycharts' ),
							],

						],
						'class'         => 'gravitycharts-option',
						'default_value' => false,
						'dependency'    => [
							'live'   => true,
							'fields' => [
								[
									'field' => 'labelsDisplay',
								],
							],
						],
						'fields'        => [
							[
								'name'       => 'labelsColorDark',
								'label'      => esc_html__( 'Dark color', 'gk-gravitycharts' ),
								'default'    => '#000000',
								'type'       => 'text',
								'input_type' => 'color',
								'dependency' => [
									'live'   => true,
									'fields' => [
										[
											'field' => 'labelsColorCustom',
										],
									],
								],
							],
							[
								'name'          => 'labelsColorLight',
								'label'         => esc_html__( 'Light color', 'gk-gravitycharts' ),
								'default_value' => '#FFFFFF',
								'type'          => 'text',
								'input_type'    => 'color',
								'dependency'    => [
									'live'   => true,
									'fields' => [
										[
											'field' => 'labelsColorCustom',
										],
									],
								],
							],
						],
					],
				],
			],
			[
				'title'      => esc_html__( 'Chart Configuration', 'gk-gravitycharts' ),
				'id'         => 'section-chart-preview',
				'fields'     => [
					[
						'name' => 'chartPreview',
						'type' => 'chart_preview',
					],
				],
				'dependency' => [
					'live'     => true,
					'operator' => 'ANY',
					'fields'   => [
						[
							'field' => 'dataField',
						],
						[
							'field' => 'timelineField',
						],
					],
				],
			],
		];

		/**
		 * Modifies feed field configuration.
		 *
		 * @filter `gk/gravitycharts/feed/settings-fields`
		 *
		 * @since  1.0
		 *
		 * @param array $fields
		 */
		return apply_filters( 'gk/gravitycharts/feed/settings-fields', $fields );
	}

	/**
	 * Returns an array mapping the GravityCharts feed settings fields to Chart.js properties.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function settings_to_chartjs_options_map(): array {
		/**
		 * Modifies a map of feed settings fields to Chart.js options.
		 *
		 * @filter `gk/gravitycharts/feed/settings-to-chart-library-options-map`
		 *
		 * @since  1.0
		 *
		 * @param array $fields
		 */
		return apply_filters(
			'gk/gravitycharts/feed/settings-to-chart-library-options-map',
			[
				'indexAxis'                       => [ 'indexAxis' ],
				'tension'                         => [ 'tension' ],
				'cutout'                          => [ 'cutout' ],
				'offset'                          => [ 'offset' ],
				'scales.r.angleLines.display'     => [ 'angleLinesDisplay' ],
				'scales.x.grid.display'           => [ 'xGridDisplay' ],
				'scales.x.title.display'          => [ 'xTitleDisplay' ],
				'scales.x.title.text'             => [ 'xTitleText' ],
				'scales.x.time.unit'              => [ 'xTimelineScale' ],
				'scales.x.time.tooltipFormat'     => [ 'timelineTooltipFormat' ],
				'scales.x.ticks.source'           => [ 'xTimelineSource' ],
				'scales.x.type'                   => [ 'xAxisType' ],
				'scales.y.grid.display'           => [ 'yGridDisplay' ],
				'scales.y.title.display'          => [ 'yTitleDisplay' ],
				'scales.y.title.text'             => [ 'yTitleText' ],
				'scales.r.grid.display'           => [ 'rGridDisplay' ],
				'scales.r.ticks.display'          => [ 'ticksDisplay' ],
				'scales.r.pointLabels.display'    => [ 'pointLabelsDisplay' ],
				'pointRadius'                     => [ 'pointRadius' ],
				'pointHoverRadius'                => [ 'pointRadius' ],
				'pointStyle'                      => [ 'pointStyle' ],
				'borderColor'                     => [ 'borderColor', 'borderColors' ],
				'borderWidth'                     => [ 'borderWidth' ],
				'fill'                            => [ 'fill' ],
				'backgroundColor'                 => [ 'backgroundColor', 'backgroundColors' ],
				'useValues'                       => [ 'useValues' ],
				'plugins.legend.display'          => [ 'legendDisplay' ],
				'plugins.legend.position'         => [ 'legendPosition' ],
				'plugins.legend.align'            => [ 'legendAlign' ],
				'plugins.title.display'           => [ 'titleDisplay' ],
				'plugins.title.text'              => [ 'titleText' ],
				'plugins.title.font.size'         => [ 'titleSize' ],
				'plugins.datalabels.opacity'      => [ 'labelsDisplay' ],
				'plugins.datalabels.font.size'    => [ 'labelsSize' ],
				'plugins.datalabels.offset'       => [ 'labelsSize' ],
				'plugins.datalabels.text'         => [ 'labelsText' ],
				'plugins.datalabels.position'     => [ 'labelsPosition' ],
				'plugins.datalabels.color_custom' => [ 'labelsColorCustom' ],
				'plugins.datalabels.color_dark'   => [ 'labelsColorDark' ],
				'plugins.datalabels.color_light'  => [ 'labelsColorLight' ],
			]
		);
	}

	/**
	 * Returns an array of default options to use with Chart.js.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function default_chartjs_options(): array {
		/**
		 * Modifies default Chart.js options.
		 *
		 * @filter `gk/gravitycharts/chart-library-options`
		 *
		 * @since  1.0
		 *
		 * @param array  $fields  Array of default chart settings.
		 * @param string $library Name of the library being used.
		 */
		return apply_filters(
			'gk/gravitycharts/chart-library-options',
			[
				'animation'             => false,
				'borderAlign'           => 'inner',
				'borderJoinStyle'       => 'round',
				'spanGaps'              => true,
				'fill'                  => true,
				'locale'                => $this->getLocale(),
				'pointHitRadius'        => 0,
				'pointBorderWidth'      => 0,
				'pointHoverBorderWidth' => 0,
				'layout'                => [
					'padding' => 20,
				],
				'plugins'               => [
					'legend'     => [
						'labels' => [
							'boxHeight' => 15,
						],
					],
					'datalabels' => [
						'clamp'   => true,
						'display' => 'auto',
					],
					'tooltip'    => [
						'displayColors' => false,
					],
					'title'      => [
						'display'  => false,
						'text'     => '',
						'fullSize' => true,
						'font'     => [
							'size' => 22,
						],
					],
				],
				'scales'                => [
					'x' => [
						'time'  => [
							'displayFormats' => $this->timelineDisplayFormats(),
							'tooltipFormat'  => $this->timelineTooltipFormat(),
							'isoWeekday'     => true,
							'unit'           => 'day',
						],
						'ticks' => [
							'autoSkip' => true,
							'source'   => 'auto',
						],
					],
				],
			],
			'chart.js'
		);
	}

	/**
	 * Cleans up field select choices before rendering.
	 *
	 * @since 1.0
	 *
	 * @param Fields\Base $field The field data.
	 * @param bool        $echo  Echo the output to the screen.
	 *
	 * @return string
	 */
	public function settings_field_select( $field, $echo = true ): ?string {
		$choices = [];

		foreach ( $field['choices'] as $choice ) {
			// Remove sub-fields, like checkboxes. These have decimal place value, e.g. "4.2".
			if ( false !== strpos( $choice['value'], '.' ) ) {
				continue;
			}

			/**
			 * Renders the color field type.
			 *
			 * @see GFAddon::get_field_map_choices()
			 */
			$selected_label = ' (' . esc_html__( 'Selected', 'gk-gravitycharts' ) . ')';
			$choice_label   = preg_replace( '/' . preg_quote( $selected_label, '/' ) . '$/m', '', $choice['label'] );

			$choices[] = [
				'value' => $choice['value'],
				'label' => $choice_label,
			];
		}

		$field['choices'] = $choices;

		// Get markup.
		$html = $field->prepare_markup();

		if ( $echo ) {
			echo wp_kses_post( $html );
		}

		return $html;
	}

	/**
	 * Renders the colors field type.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_palette_choices(): array {
		$color_palettes = Color_Pallets::get_all();

		$choices = [];

		foreach ( $color_palettes as $name => $data ) {

			// Ensure 9 items in the palette.
			$border_colors = array_pad( $data['borderColor'], 9, null );

			$grid_css = '<style>';
			foreach ( $border_colors as $index => $color ) {
				$grid_css .= '.' . $name . '-color-fill-' . ( $index + 1 );
				$grid_css .= empty( $color ) ? '{display:none;}' : '{background-color: ' . $color . ';}';
			}
			$grid_css .= '</style>';

			$grid_title = rgar( $data, 'label', $name );
			$grid_title = esc_html( $grid_title );

			// Support RTL by showing tiles in reverse order.
			$order = is_rtl() ? [ 3, 2, 1, 6, 5, 4, 9, 8, 7 ] : [ 1, 2, 3, 4, 5, 6, 7, 8, 9 ];

			$tooltip = gform_tooltip( 'gravitycharts_palette_' . $name );

			$grid = <<<EOD
<span class="gravitycharts-palette-grid">
	<!-- <svg> This tells Gravity Forms to display this output as a choice icon. See GFCommon::get_icon_markup() -->
	$grid_css
	<span class="$name-color-fill-$order[0]"></span>
	<span class="$name-color-fill-$order[1]"></span>
	<span class="$name-color-fill-$order[2]"></span>
	<span class="$name-color-fill-$order[3]"></span>
	<span class="$name-color-fill-$order[4]"></span>
	<span class="$name-color-fill-$order[5]"></span>
	<span class="$name-color-fill-$order[6]"></span>
	<span class="$name-color-fill-$order[7]"></span>
	<span class="$name-color-fill-$order[8]"></span>
</span>
$tooltip
EOD;

			$choices[] = [
				'value' => $name,
				'label' => $grid_title,
				'icon'  => $grid,
			];
		}

		return $choices;
	}

	/**
	 * Renders the chart preview.
	 *
	 * @since 1.0
	 */
	public function settings_chart_preview() {
		?>
		<div id='gravitycharts-tab-wrapper'>
			<button class='button gravitycharts-settings-toggle gravitycharts-settings-toggle-closed'
					id='gravitycharts-button-colors' aria-controls='gform-settings-section-chart-colors'><span
						class='dashicons dashicons-art'></span> <?php esc_html_e( 'Colors', 'gk-gravitycharts' ); ?>
				<span class='dashicons gravitycharts-settings-toggle-arrow'></span></button>
			<button class='button gravitycharts-settings-toggle gravitycharts-settings-toggle-closed'
					id='gravitycharts-button-design' aria-controls='gform-settings-section-chart-styles'><span
						class="dashicons dashicons-admin-appearance"></span> <?php esc_html_e( 'Design', 'gk-gravitycharts' ); ?>
				<span class='dashicons gravitycharts-settings-toggle-arrow'></span></button>
			<button class='button gravitycharts-settings-toggle gravitycharts-settings-toggle-closed'
					id='gravitycharts-button-title' aria-controls='gform-settings-section-legend'><span
						class='dashicons dashicons-editor-textcolor'></span> <?php esc_html_e( 'Title & Legend', 'gk-gravitycharts' ); ?>
				<span class='dashicons dashicons-arrow-down-alt2 gravitycharts-settings-toggle-arrow'></span></button>
			<button class='button gravitycharts-settings-toggle gravitycharts-settings-toggle-closed'
					id='gravitycharts-button-axis' aria-controls='gform-settings-section-axis'><span
						class='dashicons dashicons-grid-view'></span> <?php esc_html_e( 'Axis', 'gk-gravitycharts' ); ?>
				<span class='dashicons dashicons-arrow-down-alt2 gravitycharts-settings-toggle-arrow'></span></button>
			<button class='button gravitycharts-settings-toggle gravitycharts-settings-toggle-closed'
					id='gravitycharts-button-labels' aria-controls='gform-settings-section-labels'><span
						class='dashicons dashicons-align-wide'></span> <?php esc_html_e( 'Labels', 'gk-gravitycharts' ); ?>
				<span class='dashicons dashicons-arrow-down-alt2 gravitycharts-settings-toggle-arrow'></span></button>
		</div>
		<div id="gravitycharts-no-data-available" class="gravitycharts-notice alert warning notice-warning">
			<p>
				<?php esc_html_e( 'There are currently no entries for selected data source. The chart preview below is based on sample data.', 'gk-gravitycharts' ); ?>
			</p>
		</div>
		<canvas id="gravitycharts-preview"></canvas>
		<?php
	}

	/**
	 * Adds UI assets to GF's "no conflict" list.
	 *
	 * @since 1.0
	 *
	 * @param array $assets The current allowed assets.
	 *
	 * @return array
	 */
	private function allow_ui_assets( array $assets ): array {
		$assets[] = $this->query_filters::ASSETS_HANDLE;

		return $assets;
	}

	/**
	 * Configures translations for UI assets.
	 *
	 * @since 1.0
	 */
	private function configure_ui_translations() {
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'gravitycharts_feed_settings', 'gk-gravitycharts', plugin_dir_path( GK_GRAVITYCHARTS_PLUGIN_FILE ) . 'languages' );
		}
	}

	/***
	 * Renders the save button for settings pages.
	 *
	 * @since 1.0
	 *
	 * @param array $field Field array containing the configuration options of this field.
	 * @param bool  $echo  True to echo the output to the screen; false to simply return the contents as a string.
	 *
	 * @return string The HTML
	 */
	public function settings_submit( array $field, bool $echo = true ): string {
		$field['type'] = ( isset( $field['type'] ) && in_array(
			$field['type'],
			[
				'submit',
				'reset',
				'button',
			],
			true
		) ) ? $field['type'] : 'submit';

		$attributes    = $this->get_field_attributes( $field );
		$default_value = rgar( $field, 'value' ) ? rgar( $field, 'value' ) : rgar( $field, 'default_value' );
		$value         = $this->get_setting( $field['name'], $default_value );

		$attributes['class'] = isset( $field['class'] ) ? esc_attr( $field['class'] ) : $attributes['class'];

		$html       = ! empty( $field['html_before'] ) ? $field['html_before'] : '';
		$html_after = ! empty( $field['html_after'] ) ? $field['html_after'] : '';

		if ( ! rgar( $field, 'value' ) ) {
			$field['value'] = esc_html__( 'Update Settings', 'gk-gravitycharts' );
		}

		$attributes = $this->get_field_attributes( $field );

		unset( $attributes['html_before'], $attributes['html_after'], $attributes['tooltip'] );

		$html .= '<input
                    type="' . esc_attr( $field['type'] ) . '"
                    name="' . esc_attr( $field['name'] ) . '"
                    value="' . esc_attr( $value ) . '" ' .
				 implode( ' ', $attributes ) .
				 ' />';

		$html .= $html_after;
		$html .= wp_nonce_field( $this->_slug, '_wpnonce', true, false );

		if ( $echo ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $html;
		}

		return $html;
	}

	/**
	 * Don't show the uninstall section on the settings page.
	 *
	 * @return null
	 */
	public function render_uninstall() {
		return null;
	}

	/**
	 * Adds GravityCharts tooltips to the Gravity Forms tooltips array.
	 *
	 * @since 1.3
	 *
	 * @param array $tooltips The current tooltips.
	 *
	 * @return array
	 */
	public function add_tooltips( $tooltips ) {

		$tooltips['gravitycharts_supported_fields'] = '<h6>' . esc_html__( 'Supported Field Types', 'gk-gravitycharts' ) . '</h6>' . sprintf(
			// translators: %s is replaced by a comma-separated list of fields that are supported by GravityCharts.
			esc_html__( 'The following field types are currently supported: %s', 'gk-gravitycharts' ),
			implode(
				', ',
				[
					'checkbox',
					'select',
					'radio',
					'multiselect',
					'likert',
					'rank',
					'rating',
					'survey',
					'quiz',
				]
			)
		);

		return $tooltips;
	}

	/**
	 * Determines if we're on the feed edit page.
	 *
	 * @since 1.3
	 *
	 * @return bool
	 */
	public function is_feed_edit_page() {
		if ( $this->is_gravityforms_supported( '2.5-beta' ) ) {
			return parent::is_feed_edit_page();
		}

		return 'gf_edit_forms' === rgget( 'page' ) && $this->get_slug() === rgget( 'subview' ) && array_key_exists( 'fid', $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Determines if we're on the feed list page.
	 *
	 * @since 1.3
	 *
	 * @return bool
	 */
	public function is_feed_list_page() {
		return ! isset( $_GET['fid'] ) && ( 'gf_edit_forms' === rgget( 'page' ) && $this->get_slug() === rgget( 'subview' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Conditionally displays Help Scout beacon on certain pages.
	 *
	 * @since 1.3
	 *
	 * @param bool $display Whether to display the beacon.
	 *
	 * @return bool
	 */
	public function maybe_display_helpscout_beacon( $display ) {
		if ( $display ) {
			return true;
		}

		return $this->is_feed_edit_page() || $this->is_feed_list_page();
	}

	/**
	 * Filters out fields with only non-numeric values.
	 *
	 * @since 1.4
	 *
	 * @param bool      $allowed_input_type Whether this field is allowed.
	 * @param \GF_Field $field              The field.
	 *
	 * @return bool Whether the field has numeric values.
	 */
	private function filter_numeric_fields( bool $allowed_input_type, \GF_Field $field ): bool {
		if ( ! $allowed_input_type ) {
			return false;
		}

		if ( is_array( $field->choices ) ) {
			$values = array_column( $field->choices, 'value' );

			return (bool) array_filter( $values, 'is_numeric' );
		}

		return true;
	}

	/**
	 * Retrieves additional timeline fields that can't be added the regular way. Like Product fields.
	 *
	 * @since 1.4
	 *
	 * @return array The fields as choices.
	 */
	private function getAdditionalTimelineFields(): array {
		$form = $this->get_current_form();
		if ( ! $form ) {
			return [];
		}

		$fields = array_filter(
			$form['fields'] ?? [],
			function ( \GF_Field $field ): bool {
				return API::is_product_field( $field );
			}
		);

		return array_reduce(
			array_values( $fields ),
			function ( array $choices, \GF_Field $field ): array {
				$choices[] = [
					'value' => $field->id ?? null,
					'label' => wp_strip_all_tags( \GFCommon::get_label( $field ) ),
				];

				return $choices;
			},
			[]
		);
	}

	/**
	 * Returns the available timeline scales.
	 *
	 * @see   https://www.chartjs.org/docs/latest/axes/cartesian/time.html#time-units
	 *
	 * @since 1.4
	 * @return array
	 */
	private function timelineScales(): array {
		$scales = [
			'day'     => esc_html__( 'Day', 'gk-gravitycharts' ),
			'week'    => esc_html__( 'Week', 'gk-gravitycharts' ),
			'month'   => esc_html__( 'Month', 'gk-gravitycharts' ),
			'quarter' => esc_html__( 'Quarter', 'gk-gravitycharts' ),
			'year'    => esc_html__( 'Year', 'gk-gravitycharts' ),
		];

		$scales = apply_filters( 'gk/gravitycharts/timeline/scales', $scales );

		$output = [];
		foreach ( $scales as $value => $label ) {
			$output[] = compact( 'value', 'label' );
		}

		return $output;
	}

	/**
	 * Display formatting for timeline scales per scale.
	 *
	 * @see   https://moment.github.io/luxon/#/formatting?id=table-of-tokens for display options.
	 * @since 1.4
	 *
	 * @return array
	 */
	private function timelineDisplayFormats(): array {
		$formats = [
			'day'     => 'yyyy-MM-dd',
			'week'    => '\'W\'WW',
			'month'   => 'yyyy-MM',
			'quarter' => 'yyyy \'Q\'q',
			'year'    => 'yyyy',
		];

		$formats = apply_filters_deprecated(
			'gk/gravitycharts/timeline/display_formats',
			[ $formats ],
			'1.6',
			'gk/gravitycharts/timeline/display-formats'
		);

		return apply_filters( 'gk/gravitycharts/timeline/display-formats', $formats );
	}

	/**
	 * The time format used for the tooltip.
	 *
	 * @see   https://moment.github.io/luxon/#/formatting?id=table-of-tokens for display options.
	 * @since 1.4
	 *
	 * @return string The format.
	 */
	private function timelineTooltipFormat(): string {
		$formats = $this->timelineDisplayFormats();
		$format  = $this->get_setting( 'xTimelineScale', 'day' );

		$format = apply_filters_deprecated(
			'gk/gravitycharts/timeline/tooltip_format',
			[ $formats[ $format ] ],
			'1.6',
			'gk/gravitycharts/timeline/tooltip-format'
		);

		return apply_filters( 'gk/gravitycharts/timeline/tooltip-format', $format );
	}

	/**
	 * Returns the locale for a feed.
	 *
	 * @since 1.6
	 *
	 * @return string|null The locale.
	 */
	private function getLocale(): ?string {

		/**
		 * Filters the locale for a feed.
		 *
		 * @since 1.6
		 * @param string $locale The locale. Default: Site language returned by {@see get_bloginfo()}.
		 * @param Chart_Feed $feed Instance of Chart_Feed.
		 */
		return apply_filters( 'gk/gravitycharts/locale', get_bloginfo( 'language' ), $this );
	}
}
