<?php
/**
 * Retrieve form entry data for use in charts.
 *
 * @package GravityKit\GravityCharts
 */

namespace GravityKit\GravityCharts;

use Exception;
use GF_Field;
use GF_Query;
use GF_Query_Condition;
use GFAPI;
use GFCommon;
use GFFormsModel;
use GravityKit\GravityCharts\QueryFilters\QueryFilters;
use GravityKit\GravityCharts\Timeline\TimelineDate;
use WP_REST_Request;
use WP_REST_Server;

/**
 * GravityCharts API.
 */
class API {
	/**
	 * REST API Namespace.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	const NAMESPACE = 'gravitycharts/v1';

	/**
	 * GravityCharts plugin instance.
	 *
	 * @since 1.0
	 *
	 * @var Plugin
	 */
	private $gravitycharts;

	/**
	 * Class instance.
	 *
	 * @since 1.0.4
	 *
	 * @var API
	 */
	private static $_instance;

	/**
	 * REST API constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->gravitycharts = Plugin::get_instance();

		add_action( 'rest_api_init', [ $this, 'init' ] );
	}

	/**
	 * Returns class instance.
	 *
	 * @since 1.0.4
	 *
	 * @return API
	 */
	public static function get_instance(): API {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new API();
		}

		return self::$_instance;
	}

	/**
	 * Initializes API.
	 *
	 * @since 1.0
	 */
	public function init() {

		$this->gravitycharts->init();

		register_rest_route(
			self::NAMESPACE,
			'/chart',
			[
				'methods'             => WP_REST_Server::READABLE,
				'args'                => [
					'feed_id' => [
						'required' => true,
						'type'     => 'number',
					],
				],
				'permission_callback' => [ $this, 'permissions' ],
				'callback'            => function ( WP_REST_Request $request ) {
					$feed_id  = intval( $request->get_param( 'feed_id' ) );
					$entry_id = intval( $request->get_param( 'entry_id' ) );

					$response = $this->get_chart( $feed_id, $entry_id );

					return rest_ensure_response( $response );
				},
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/chart-options',
			[
				'methods'             => WP_REST_Server::READABLE,
				'args'                => [
					'feed_id' => [
						'required' => true,
						'type'     => 'number',
					],
				],
				'permission_callback' => [ $this, 'permissions' ],
				'callback'            => function ( WP_REST_Request $request ) {
					$feed_id  = intval( $request->get_param( 'feed_id' ) );
					$response = $this->get_chart_options( $feed_id );

					return rest_ensure_response( $response );
				},
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/chart-data',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'args'                => [
					'form_id'                => [
						'required' => true,
						'type'     => 'number',
					],
					'data_type'              => [
						'required' => true,
						'type'     => 'string',
						'enum'     => array_keys( Data_Types::get_all() ),
					],
					'timeline_type'          => [
						'required' => false,
						'type'     => 'string',
						'enum'     => array_keys( Data_Types::get_timeline_types() ),
					],
					'timeline_data_field_id' => [
						'required' => false,
						'type'     => 'number',
					],
					'field_id'               => [
						'required' => true,
						'type'     => [ 'number', 'string' ],
					],
					'entry_id'               => [
						'required' => false,
						'type'     => 'number',
					],
					'condition'              => [
						'required' => false,
						'type'     => 'string',
					],
				],
				'permission_callback' => [ $this, 'permissions' ],
				'callback'            => function ( WP_REST_Request $request ) {
					$form_id           = intval( $request->get_param( 'form_id' ) );
					$chart_type        = $request->get_param( 'data_type' );
					$feed_id           = intval( $request->get_param( 'feed_id' ) );
					$field_id          = intval( $request->get_param( 'field_id' ) );
					$filters           = $request->get_param( 'filters' );
					$conditional_logic = 'null';

					if ( $filters && 'null' !== $filters ) {
						try {
							$conditional_logic = json_decode( stripslashes( $filters ), true );
						} catch ( Exception $e ) {
							$conditional_logic = 'null';
						}
					}

					switch ( $chart_type ) {
						case 'timeline':
							$response = $this->get_timeline_chart_data(
								$form_id,
								$feed_id,
								$field_id,
								$request->get_param( 'timeline_type' ),
								$request->get_param( 'timeline_scale', 'day' ),
								$request->get_param( 'timeline_data_field_id' ),
								$conditional_logic
							);
							break;
						default:
							$response = $this->get_chart_data( $form_id, $feed_id, $field_id, $conditional_logic );
					}

					return rest_ensure_response( $response );
				},
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/forms',
			[
				'methods'             => WP_REST_Server::READABLE,
				'permission_callback' => [ $this, 'permissions' ],
				'callback'            => function () {
					$response = $this->get_forms();

					return rest_ensure_response( $response );
				},
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/feeds',
			[
				'methods'             => WP_REST_Server::READABLE,
				'args'                => [
					'form_id' => [
						'required' => true,
						'type'     => 'number',
					],
				],
				'permission_callback' => [ $this, 'permissions' ],
				'callback'            => function ( WP_REST_Request $request ) {
					$form_id  = intval( $request->get_param( 'form_id' ) );
					$response = $this->get_feeds( $form_id );

					return rest_ensure_response( $response );
				},
			]
		);
	}

	/**
	 * Checks REST API permissions.
	 *
	 * @since 1.0
	 *
	 * @return boolean
	 */
	public function permissions(): bool {
		$capabilities = $this->gravitycharts->chart_feed->get_capabilities( 'form_settings' );

		return GFCommon::current_user_can_any( $capabilities );
	}

	/**
	 * Gets chart type, options, and data.
	 *
	 * @since 1.0
	 *
	 * @param integer $feed_id  The feed ID.
	 * @param integer $entry_id An (optional) entry ID.
	 *
	 * @return array
	 */
	public function get_chart( int $feed_id, int $entry_id = 0 ): array {
		$response = [];
		$feed     = GFAPI::get_feed( $feed_id );

		if ( is_wp_error( $feed ) ) {
			Plugin::get_instance()->chart_feed->log_error( 'Error fetching feed for feed ID ' . esc_html( $feed_id ) . ': ' . $feed->get_error_message() );

			return [];
		}

		if (
			! isset( $feed['meta'] ) ||
			! isset( $feed['meta']['dataField'] ) ||
			! isset( $feed['meta']['chartType'] ) ||
			! isset( $feed['form_id'] )
		) {
			return $response;
		}

		$filters    = rgars( $feed, 'meta/conditional_logic', 'null' );
		$chart_type = rgars( $feed, 'meta/dataType', 'field' );
		$form_id    = (int) rgars( $feed, 'form_id', 0 );

		if ( $entry_id > 0 ) {
			// Replace filters if a single entry was requested.
			$filters = [
				'mode'       => 'AND',
				'conditions' => [
					[
						'key'      => 'entry_id',
						'operator' => 'is',
						'value'    => $entry_id,
					],
				],
			];
		}

		switch ( $chart_type ) {
			case 'timeline':
				$data = $this->get_timeline_chart_data(
					$form_id,
					$feed_id,
					rgars( $feed, 'meta/timelineField', 0 ),
					rgars( $feed, 'meta/timelineTypeField', 'entry_count' ),
					rgars( $feed, 'meta/xTimelineScale', 'day' ),
					rgars( $feed, 'meta/timelineDataField', 0 ),
					$filters
				);
				break;
			default:
				$data = $this->get_chart_data(
					$form_id,
					$feed_id,
					rgars( $feed, 'meta/dataField', 0 ),
					$filters
				);
		}

		$response['type']    = $feed['meta']['chartType'];
		$response['options'] = array_merge( $this->get_chart_options( $feed_id ), rgar( $data, 'options', [] ) );
		unset( $data['options'] );

		$response['data']      = $data;
		$response['ariaLabel'] = $this->get_chart_description( $response['data'], $response['options'], $response['type'] );

		return $response;
	}

	/**
	 * Returns the chart options formatted for QuickChart.
	 *
	 * @param int $feed_id  The feed ID.
	 * @param int $entry_id The entry ID.
	 *
	 * @return string
	 */
	public function get_chart_js( int $feed_id, int $entry_id = 0 ): string {
		// Todo: Dispatch event to change json and replaceable strings from other places.
		$response = $this->get_chart( $feed_id, $entry_id );

		$datalabels        = $response['options']['plugins']['datalabels'];
		$is_label_custom   = $datalabels['color_custom'] ?? false;
		$label_color_dark  = $is_label_custom ? $datalabels['color_dark'] ?? '#000000' : '#000000';
		$label_color_light = $is_label_custom ? $datalabels['color_light'] ?? '#FFFFFF' : '#FFFFFF';

		$response['options']['plugins']['datalabels'] = array_merge(
			$datalabels,
			[
				'color'     => '__CALLBACK_COLOR__',
				'display'   => '__CALLBACK_DISPLAY__',
				'formatter' => '__CALLBACK_FORMATTER__',
			]
		);

		$legend = $response['options']['plugins']['legend'];

		$response['options']['plugins']['legend'] = array_merge(
			$legend,
			[
				'labels' => [ 'generateLabels' => '__CALLBACK_GENERATE_LABELS__' ],
			]
		);

		$json = str_replace(
			[
				'"__CALLBACK_FORMATTER__"',
				'"__CALLBACK_COLOR__"',
				'"__CALLBACK_DISPLAY__"',
				'"__CALLBACK_GENERATE_LABELS__"',
			],
			[
				$this->get_datalabels_formatter( $response['options']['plugins']['datalabels']['text'] ),
				$this->get_datalabels_color_function(
					$datalabels['position'] ?? 'default',
					$label_color_dark,
					$label_color_light
				),
				$this->get_datalabels_display_function(),
				$this->get_legend_label_generator_function(),
			],
			wp_json_encode( $response )
		);

		return $json;
	}

	/**
	 * Generates an assistive text description for the chart.
	 *
	 * @param array  $data    Chart data, as fetched by {@see get_chart_data}.
	 * @param array  $options Chart options from {@see get_chart_options}.
	 * @param string $type    Slug of the type of chart ("bar", "pie", "radarArea", etc.).
	 *
	 * @return string
	 */
	public function get_chart_description( array $data, array $options, string $type ): string {

		$data_description = [];

		// TODO: When we have multiple datasets the labels array will need to be nested.
		foreach ( $data['datasets'] as $dataset_key => $dataset ) {

			$labels = array_values(
				array_map(
					function ( $label ) {
						return is_array( $label ) ? implode( ' ', $label ) : $label;
					},
					$data['labels']
				)
			);
			$values = array_combine( $labels, array_values( $dataset['data'] ) );

			// translators: The placeholder is replaced with the number of the dataset being described in the chart data.
			$dataset_output = sprintf( esc_html__( 'Dataset %d: ', 'gk-gravitycharts' ), $dataset_key + 1 );

			$glue = '; ';
			foreach ( $values as $key => $value ) {
				if ( is_array( $value ) ) {
					$value = $value['label'] ?? $value['value'] ?? '';
				}

				$dataset_output .= sprintf( '%s: %s', esc_html( $key ), esc_html( $value ) . $glue );
			}

			$dataset_output = rtrim( $dataset_output, $glue );

			$data_description[] = $dataset_output;
		}

		switch ( $type ) {
			case 'pie':
				// translators: Do not translate the words inside the {} curly brackets; they are replaced.
				$description_template = esc_html__( 'A {chart type} chart with the following data: {data description}.', 'gk-gravitycharts' );
				break;
			default:
				// translators: Do not translate the words inside the {} curly brackets; they are replaced.
				$description_template = esc_html__( 'A {chart type} chart, values from {min} to {max}, with the following data: {data description}.', 'gk-gravitycharts' );
				break;
		}

		$chart_type       = Chart_Types::get( $type );
		$chart_type_label = rgar( $chart_type, 'label', $type );

		return strtr(
			$description_template,
			[
				'{chart type}'       => mb_strtolower( $chart_type_label ),
				'{min}'              => rgars( $options, 'scales/y/min', 0 ),
				'{max}'              => rgars( $options, 'scales/y/max', esc_html__( 'the maximum value', 'gk-gravitycharts' ) ),
				'{data description}' => implode( '. ', $data_description ),
			]
		);
	}

	/**
	 * Gets chart options.
	 *
	 * @since 1.0
	 *
	 * @param integer $feed_id The feed ID.
	 *
	 * @return array
	 */
	protected function get_chart_options( int $feed_id ): array {
		$response = [];
		$feed     = GFAPI::get_feed( $feed_id );

		if ( empty( $feed ) ) {
			Plugin::get_instance()->chart_feed->log_error( 'Feed #' . esc_html( $feed_id ) . ' not found.' );

			return $response;
		}

		if ( is_wp_error( $feed ) ) {
			Plugin::get_instance()->chart_feed->log_error( 'Error fetching feed #' . esc_html( $feed_id ) . ': ' . $feed->get_error_message() );

			return $response;
		}

		if ( ! isset( $feed['meta'] ) || ! isset( $feed['meta']['chartType'] ) ) {
			return $response;
		}

		$type = $feed['meta']['chartType'];

		if ( 'pie' === $type || 'polarArea' === $type ) {
			unset( $feed['meta']['backgroundColor'] );
			unset( $feed['meta']['borderColor'] );
		} else {
			unset( $feed['meta']['backgroundColors'] );
			unset( $feed['meta']['borderColors'] );
		}

		$this->gravitycharts->init();

		$response    = $this->gravitycharts->chart_feed->default_chartjs_options();
		$options_map = $this->gravitycharts->chart_feed->settings_to_chartjs_options_map();

		foreach ( $options_map as $option_path => $meta_keys ) {
			foreach ( $meta_keys as $meta_key ) {
				if ( isset( $feed['meta'][ $meta_key ] ) ) {
					if ( 'xAxisType' === $meta_key && '' === $feed['meta'][ $meta_key ] ) {
						continue;
					}

					$this->set_dot_notation_value(
						$response,
						$option_path,
						$feed['meta'][ $meta_key ]
					);
				}
			}
		}

		$auto_scale = $feed['meta']['autoScale'] ?? false;
		$max        = (int) rgars( $feed, 'meta/max', 1 );
		$min        = (int) rgars( $feed, 'meta/min', 0 );

		if ( 'line' === $type || ( 'bar' === $type && 'x' === $response['indexAxis'] ) ) {
			if ( ! $auto_scale ) {
				$response['scales']['y']['max'] = $max;
				$response['scales']['y']['min'] = $min;
			}

			unset( $response['scales']['r'] );
			unset( $response['scales']['x']['grid'] );
		}
		if ( 'bar' === $type && 'y' === $response['indexAxis'] ) {
			if ( ! $auto_scale ) {
				$response['scales']['x']['max'] = $max;
				$response['scales']['x']['min'] = $min;
			}

			unset( $response['scales']['r'] );
			unset( $response['scales']['y']['grid'] );
		}
		if ( 'radar' === $type || 'polarArea' === $type ) {
			if ( ! $auto_scale ) {
				$response['scales']['r']['max'] = $max;
				$response['scales']['r']['min'] = $min;
			}

			unset( $response['scales']['x'] );
			unset( $response['scales']['y'] );
		}
		if ( 'pie' === $type ) {
			unset( $response['scales'] );
			if ( 0 !== intval( $response['offset'] ) ) {
				$response['borderAlign'] = 'center';
			}
		}

		if ( 'default' === ( $response['plugins']['datalabels']['position'] ?? null ) ) {
			$response['plugins']['datalabels']['anchor'] = 'center';
			$response['plugins']['datalabels']['align']  = 'center';
		}
		if ( 'border' === ( $response['plugins']['datalabels']['position'] ?? null ) ) {
			$response['plugins']['datalabels']['anchor'] = 'end';
			$response['plugins']['datalabels']['align']  = 'start';
		}
		if ( 'outside' === ( $response['plugins']['datalabels']['position'] ?? null ) ) {
			$padding   = intval( $response['layout']['padding'] );
			$font_size = intval( $response['plugins']['datalabels']['font']['size'] );

			$response['layout']['padding'] = $padding + $font_size;

			$response['plugins']['datalabels']['anchor'] = 'end';
			$response['plugins']['datalabels']['align']  = 'end';
		}

		if ( 'line' === $type || ( 'bar' === $type && 'time' === ( $response['scales']['x']['type'] ?? '' ) ) ) {
			$response['indexAxis'] = 'x';
		}

		return $response;
	}


	/**
	 * Returns the form object based on the form id.
	 *
	 * Hooks are provided to modify the form object used for chart data.
	 *
	 * @filter `gk/gravitycharts/api/form`
	 * @filter `gform_pre_render`
	 *
	 * @param int        $form_id  The form ID.
	 * @param int        $feed_id  The feed ID.
	 * @param int|string $field_id The field id.
	 *
	 * @return mixed
	 */
	protected function get_form( int $form_id, int $feed_id, $field_id ) {
		// Adding default pre render hook for plugins to update the fields before choice retrieval.
		$form = gf_apply_filters( [ 'gform_pre_render', $form_id ], GFAPI::get_form( $form_id ), false, [] );

		return gf_apply_filters( [ 'gk/gravitycharts/api/form', $form_id ], $form, $feed_id, $field_id );
	}

	/**
	 * Retrieves and modifies the entries object used for chart data.
	 *
	 * @filter `gk/gravitycharts/api/entries`
	 *
	 * @since  1.0
	 *
	 * @param array        $form    The form object.
	 * @param array        $feed    The feed object.
	 * @param string|array $filters Any conditional logic filters applied to the chart.
	 *
	 * @return \WP_Error|array The entries.
	 */
	protected function get_entries( array $form, array $feed, $filters ) {
		$entries = apply_filters( 'gk/gravitycharts/api/entries', null, $feed, $form, $filters );

		if ( is_null( $entries ) && ! empty( $feed ) && 'null' !== $filters ) {
			try {
				$query_filters = new QueryFilters();
				$query_filters->set_form( $form );
				$query_filters->set_filters( $filters );

				$conditions = $query_filters->get_query_conditions();

				$query = new GF_Query(
					$form['id'],
					[
						'field_filters' => [],
						'status'        => 'active',
					],
					null,
					[ 'page_size' => 0 ]
				);

				$query_parts = $query->_introspect();

				$query->where( GF_Query_Condition::_and( $query_parts['where'], $conditions ) );

				$entries = $query->get();
			} catch ( Exception $e ) {
				$entries = null;
			}
		}

		if ( is_null( $entries ) ) {
			$paging = [
				'offset'    => 0,
				'page_size' => 0,
			];

			$entries = GFAPI::get_entries(
				$form['id'],
				[
					'status' => 'active',
				],
				null,
				$paging
			);
		}

		return $entries;
	}

	/**
	 * Gets chart data for a regular field.
	 *
	 * @since 1.0
	 *
	 * @param integer        $form_id  The form ID.
	 * @param integer        $feed_id  The feed ID.
	 * @param integer|string $field_id The field ID.
	 * @param string|array   $filters  Any conditional logic filters applied to the chart.
	 *
	 * @return array
	 */
	protected function get_chart_data( int $form_id, int $feed_id, $field_id, $filters ): array {
		$form  = $this->get_form( $form_id, $feed_id, $field_id );
		$field = GFAPI::get_field( $form, $field_id );
		$feed  = GFAPI::get_feed( $feed_id );

		if ( empty( $field->choices ) ) {
			return [
				'labels'   => [],
				'datasets' => [],
			];
		}

		if ( is_wp_error( $feed ) ) {
			Plugin::get_instance()->chart_feed->log_error( 'Error fetching feed #' . esc_html( $feed_id ) . ': ' . $feed->get_error_message() );
			$feed = [];
		}

		$entries = $this->get_entries( $form, $feed, $filters );

		if ( is_wp_error( $entries ) ) {
			Plugin::get_instance()->chart_feed->log_error( 'Error fetching entries: ' . $entries->get_error_message() );

			return [];
		}

		if ( isset( $field->inputs ) ) {
			// Checkboxes use the "inputs" property, not choices.
			$chart_data = $this->parse_checkbox_entries( $entries, $field );
		} else {
			$chart_data = $this->parse_standard_entries( $entries, $field );
		}

		static::apply_chart_settings( $chart_data, $feed );

		return $chart_data;
	}

	/**
	 * Gets chart data for a timeline field.
	 *
	 * @since 1.4
	 *
	 * @param integer        $form_id                The form ID.
	 * @param integer        $feed_id                The feed ID.
	 * @param integer|string $timeline_field_id      The field ID.
	 * @param string         $timeline_type          The timeline type (entry count, sum, average).
	 * @param string         $timeline_scale         The timeline scale (day, week, month, quarter, year).
	 * @param int|null       $timeline_data_field_id The timeline data field (for sum and average).
	 * @param string|array   $filters                Any conditional logic filters applied to the chart.
	 *
	 * @return array
	 */
	protected function get_timeline_chart_data( int $form_id, int $feed_id, $timeline_field_id, string $timeline_type, string $timeline_scale, ?int $timeline_data_field_id, $filters ): array {
		$form                = $this->get_form( $form_id, $feed_id, $timeline_field_id );
		$timeline_field      = GFAPI::get_field( $form, $timeline_field_id );
		$timeline_data_field = GFAPI::get_field( $form, $timeline_data_field_id );
		$feed                = GFAPI::get_feed( $feed_id );

		if ( is_wp_error( $feed ) ) {
			Plugin::get_instance()->chart_feed->log_error( 'Error fetching feed #' . esc_html( $feed_id ) . ': ' . $feed->get_error_message() );
			$feed = [];
		}

		$entries = $this->get_entries( $form, $feed, $filters );

		if ( is_wp_error( $entries ) ) {
			Plugin::get_instance()->chart_feed->log_error( 'Error fetching entries: ' . $entries->get_error_message() );

			return [];
		}

		$chart_data = $this->parse_timeline_entries(
			$entries,
			$timeline_field ? $timeline_field : null,
			$timeline_type,
			$timeline_scale,
			$timeline_data_field ? $timeline_data_field : null
		);

		static::apply_chart_settings( $chart_data, $feed );

		return $chart_data;
	}

	/**
	 * Gets all Gravity Forms that have active Chart feeds.
	 *
	 * @since 1.0
	 *
	 * @return array{value: int, label: string} Forms with feeds or empty array if none found.
	 */
	protected function get_forms(): array {
		global $wpdb;

		$feeds_table = $wpdb->prefix . 'gf_addon_feed';
		$forms_table = GFFormsModel::get_form_table_name();
		$addon_slug  = $this->gravitycharts->chart_feed->get_slug();

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT `form`.`id` as value, `form`.`title` as label
					FROM $feeds_table feed
					JOIN $forms_table form
					ON `feed`.`form_id` = `form`.`id`
					WHERE 1 = 1
					    AND `form`.`is_trash` = 0
						AND `feed`.`is_active` = 1
						AND `feed`.`addon_slug` = %s
					GROUP BY value
					ORDER BY `form`.`title` ASC",
				[ $addon_slug ]
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Gets all GravityCharts feeds for a given form.
	 *
	 * @since 1.0
	 *
	 * @param integer $form_id The form ID.
	 *
	 * @return array{value: int, label: string} Feed IDs and names or empty array if none found.
	 */
	protected function get_feeds( int $form_id ): array {
		$response = [];
		$feeds    = GFAPI::get_feeds( null, $form_id, $this->gravitycharts->chart_feed->get_slug() );

		if ( is_wp_error( $feeds ) ) {
			Plugin::get_instance()->chart_feed->log_error( 'Error fetching feeds for form ID ' . esc_html( $form_id ) . ': ' . $feeds->get_error_message() );

			return [];
		}

		foreach ( $feeds as $feed ) {
			if ( empty( $feed['id'] ) ) {
				continue;
			}
			$response[] = [
				'value' => $feed['id'],
				'label' => $this->gravitycharts->chart_feed->get_column_value_chartName( $feed ),
			];
		}

		return $response;
	}

	/**
	 * Parses checkbox entry values into a format that is ready for use in Chart.js.
	 *
	 * @since 1.0
	 *
	 * @param array    $entries The form entries.
	 * @param GF_Field $field   The field to retrieve the value from.
	 *
	 * @return array
	 */
	protected function parse_standard_entries( array $entries, GF_Field $field ): array {
		$data   = [];
		$labels = [];
		$values = [];

		$key = 0;
		foreach ( $field->choices as $choice ) {
			$text  = $choice['text'];
			$value = $choice['value'];

			if ( '' === $value ) {
				continue;
			}

			$labels[ $key ] = $this->maybe_split_label( $text, $field );
			$values[ $key ] = $this->maybe_split_label( $value, $field );
			$data[ $value ] = 0;

			$key ++;
		}

		foreach ( $entries as $entry ) {
			if ( empty( $entry[ $field->id ] ) ) {
				continue;
			}

			// TODO: use $field->get_selected_choice() method when minimum GF version is bumped to ≥2.5.11.
			$choice = [];
			foreach ( $field->choices as $_choice ) {
				if ( GFFormsModel::choice_value_match( $field, $_choice, $entry[ $field->id ] ) ) {
					$choice = $_choice;
				}
			}

			$value = $choice['value'] ?? null;

			$decoded_values = null;
			if ( ! empty( $value ) ) {
				// Sometimes the value is json encoded, so try to decode.
				$decoded_values = json_decode( $value, true );
			}

			if ( is_array( $decoded_values ) ) {
				foreach ( $decoded_values as $decoded_value ) {
					if ( isset( $data[ $decoded_value ] ) ) {
						$data[ $decoded_value ] ++;
					}
				}
			}

			if ( isset( $data[ $value ] ) ) {
				$data[ $value ] ++;
			}
		}

		return [
			'fieldLabels' => $labels,
			'values'      => $values,
			'datasets'    => [
				[
					'label' => $field['label'],
					'data'  => array_values( $data ),
				],
			],
		];
	}

	/**
	 * Parses checkbox entry values into a format that is ready for use in Chart.js.
	 *
	 * @since 1.0
	 *
	 * @param array    $entries The form entries.
	 * @param GF_Field $field   The field to retrieve the value from.
	 *
	 * @return array
	 */
	protected function parse_checkbox_entries( array $entries, GF_Field $field ): array {
		$data   = [];
		$labels = [];
		$values = [];

		foreach ( $field->get_entry_inputs() as $key => $choice ) {
			$text      = $choice['label'];
			$choice_id = $choice['id'];

			$labels[ $key ]     = $this->maybe_split_label( $text, $field );
			$values[ $key ]     = $this->maybe_split_label( $field->choices[ $key ]['value'] ?? $text, $field );
			$data[ $choice_id ] = 0;

			foreach ( $entries as $entry ) {
				if ( empty( $entry[ $choice_id ] ) ) {
					continue;
				}

				if ( isset( $data[ $choice_id ] ) ) {
					$data[ $choice_id ] ++;
				}
			}
		}

		return [
			'fieldLabels' => $labels,
			'values'      => $values,
			'datasets'    => [
				[
					'label' => $field['label'],
					'data'  => array_values( $data ),
				],
			],
		];
	}

	/**
	 * Parses checkbox entry values into a format that is ready for use in Chart.js.
	 *
	 * @since 1.4
	 *
	 * @param array         $entries             The form entries.
	 * @param GF_Field|null $timeline_field      The field to retrieve the value from.
	 * @param string        $timeline_type       The timeline type (entry_count, sum, average).
	 * @param string        $timeline_scale      The timeline scale (day, week, month, quarter, year).
	 * @param GF_Field|null $timeline_data_field The timeline data field (for sum and average).
	 *
	 * @return array
	 */
	protected function parse_timeline_entries( array $entries, ?GF_Field $timeline_field, string $timeline_type, string $timeline_scale, ?GF_Field $timeline_data_field ): array {
		$collect = [];

		foreach ( $entries as $entry ) {
			if ( $timeline_field ) {
				$date = $timeline_field->get_value_export( $entry );
			} else {
				// Only route here is via date_created.
				$date = $entry['date_created'];
			}
			try {
				$date = ( new TimelineDate( $date, wp_timezone() ) );
				$date = $date->with_scale( $timeline_scale )->format( 'Y-m-d' );
			} catch ( Exception $e ) {
				// skip this entry.
				continue;
			}

			if ( ! array_key_exists( $date, $collect ) ) {
				$collect[ $date ] = [];
			}

			if ( 'entry_count' === $timeline_type ) {
				$collect[ $date ][] = 1; // A single entry.
			} elseif ( $timeline_data_field ) {
				if ( self::is_product_field( $timeline_data_field ) ) {
					$values = $this->get_product_value( $timeline_data_field, $entry );
				} else {
					$values = $timeline_data_field->get_value_export( $entry, '', false, true );
				}

				// Let plugins alter their values.
				$values = apply_filters( 'gform_export_field_value', $values, rgobj( $timeline_data_field, 'formId' ), $timeline_data_field->id, $entry );

				// Explode a string as comma separated values.
				if ( is_string( $values ) ) {
					$values = array_map( 'trim', explode( ',', $values ) );
				}

				if ( ! is_array( $values ) ) {
					$values = [ $values ];
				}

				foreach ( $values as $value ) {
					if ( ! is_numeric( $value ) ) {
						// We can only sum / average numeric values.
						continue;
					}
					$collect[ $date ][] = $value;
				}
			}
		}

		$data = [];
		foreach ( $collect as $date => $values ) {
			$datapoint = [
				'date'  => $date,
				'value' => $this->process_timeline_values( $values, $timeline_type ),
				'label' => $this->get_time_scale_label( $date, $timeline_scale ),
			];

			if ( $timeline_data_field && $this->is_price_field( $timeline_data_field ) ) {
				$datapoint['label'] = html_entity_decode( \GFCommon::to_money( $datapoint['value'], $entry['currency'] ?? '' ) );
			}

			$data[ $date ] = $datapoint;
		}

		ksort( $data );

		$label = null;

		if ( 'entry_count' === $timeline_type ) {
			$text  = esc_html__( 'Entry count', 'gk-gravitycharts' );
			$label = $text;
		} elseif ( $timeline_data_field ) {
			$label = $timeline_data_field->get_field_label( true, '' );
		}

		return [
			'fieldLabels' => array_keys( $data ),
			'values'      => array_keys( $data ),
			'datasets'    => [
				[
					'label'   => $label,
					'data'    => array_values( $data ),
					'parsing' => [
						'xAxisKey' => 'date',
						'yAxisKey' => 'value',
					],
				],
			],
		];
	}


	/**
	 * Sets a nested array value based on dot notation.
	 *
	 * @since 1.0
	 *
	 * @param array  $array The array to update.
	 * @param string $path  The dot notation string.
	 * @param mixed  $value The value to set.
	 */
	protected function set_dot_notation_value( array &$array, string $path, $value ) {
		$location = &$array;
		$steps    = explode( '.', $path );
		foreach ( $steps as $step ) {
			$location = &$location[ $step ];
		}

		$location = $value;
	}

	/**
	 * Applies the selected color palette and label type to the dataset.
	 *
	 * @since 1.4
	 *
	 * @param array $chart_data The chart data to modify.
	 * @param array $feed       The feed object.
	 */
	protected static function apply_chart_settings( array &$chart_data, array $feed ): void {
		$color_palettes = Color_Pallets::get_all();
		$feed_palette   = rgars( $feed, 'meta/palette', 'default' );
		$color_palette  = $color_palettes[ $feed_palette ];
		$border_align   = rgars( $feed, 'meta/offset', 0 ) ? 'center' : 'inner';
		$use_values     = (bool) rgars( $feed, 'meta/useValues', false );

		$chart_data['labels'] = $chart_data[ $use_values ? 'values' : 'fieldLabels' ];

		$chart_data['datasets'][0] = array_merge(
			$chart_data['datasets'][0],
			[
				'backgroundColor'      => $color_palette['backgroundColor'],
				'borderColor'          => $color_palette['borderColor'],
				'pointBackgroundColor' => $color_palette['pointBackgroundColor'] ?? null,
				'borderAlign'          => $border_align,
			]
		);
	}

	/**
	 * Splits the label into multiple lines if it gets too long.
	 *
	 * @since 1.4
	 *
	 * @param string   $label The label to update.
	 * @param GF_Field $field The field that contains the label.
	 *
	 * @return string|string[] The label, possibly split as an array.
	 */
	protected function maybe_split_label( string $label, GF_Field $field ) {
		$width = (int) gf_apply_filters(
		//phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			[ 'gk/gravitycharts/api/label-width', $field->formId, $field->id ],
			50,
			$label,
			$field
		);

		if ( $width <= 0 ) {
			return $label;
		}

		$parts = explode( "\n", wordwrap( $label, $width ) );

		return count( $parts ) > 1 ? $parts : $label;
	}

	/**
	 * Returns the raw formatter function as a string.
	 *
	 * @param string $type The formatting type.
	 *
	 * @return string|null The formatter function.
	 */
	private function get_datalabels_formatter( string $type ): ?string {
		$dir = __DIR__ . '/../build/helpers/label-formatter/';

		$files = [
			'percentage' => 'percentage-label-formatter.js',
			'label'      => 'text-label-formatter.js',
			'value'      => 'value-label-formatter.js',
		];

		if ( ! isset( $files[ $type ] ) ) {
			$type = 'value';
		}

		$file = $dir . $files[ $type ];

		return $this->get_js_function_from_file( $file );
	}

	/**
	 * Returns the raw color function as a string.
	 *
	 * @param string $position    The label position.
	 * @param string $dark_color  The dark text color on a light background.
	 * @param string $light_color The light color text on a dark background.
	 * @param int    $threshold   The luminance threshold on which to switch to dark text.
	 *
	 * @return string|null The color function.
	 */
	private function get_datalabels_color_function(
		string $position,
		string $dark_color,
		string $light_color,
		int $threshold = 140
	): ?string {
		if ( 'outside' === $position ) {
			return sprintf( '"%s"', $dark_color );
		}

		$script = $this->get_js_function_from_file( __DIR__ . '/../build/helpers/label-color.js' );

		return sprintf( "(%s)('%s','%s',%d)", $script, $dark_color, $light_color, $threshold );
	}

	/**
	 * Return the label display function.
	 *
	 * @since 1.4
	 *
	 * @return string The label display function.
	 */
	private function get_datalabels_display_function() {
		$file = __DIR__ . '/../build/helpers/label-display.js';

		return $this->get_js_function_from_file( $file );
	}

	/**
	 * Returns the `onClick` function for the `legend` plugin.
	 *
	 * @since 1.5
	 *
	 * @return string The legend onclick function.
	 */
	private function get_legend_onclick_function() {
		$file = __DIR__ . '/../build/helpers/legend/on-click.js';

		return $this->get_js_function_from_file( $file );
	}

	/**
	 * Returns the `generateLabel` function for the `legend` plugin.
	 *
	 * @since 1.5
	 *
	 * @return string The legend `generateLabel`` function.
	 */
	private function get_legend_label_generator_function() {
		$file = __DIR__ . '/../build/helpers/legend/label-generator.js';

		return $this->get_js_function_from_file( $file );
	}

	/**
	 * Retrieves the javascript function from a js file as a string.
	 *
	 * @since 1.4
	 *
	 * @param string $file The javascript file.
	 *
	 * @return string|null The function..
	 */
	private function get_js_function_from_file( string $file ): ?string {
		$script = ( new \WP_Filesystem_Direct( [] ) )->get_contents( $file );
		if ( ! $script ) {
			return null;
		}

		// filter out the actual function.
		$script = preg_replace( '/^const .+?=/is', '', $script, 1 );
		$script = preg_replace( '/export default .+;/is', '', $script );
		$script = str_replace( '};', '}', $script );

		return rtrim( $script, ';' );
	}

	/**
	 * Processes values for a date according to the parsing type.
	 *
	 * @since 1.4
	 *
	 * @param int[]|float[] $values The values to parse.
	 * @param string        $type   The parsing type.
	 *
	 * @return float|int The numeric value.
	 */
	protected function process_timeline_values( array $values, string $type ) {
		if ( ! $values ) {
			return 0;
		}
		$sum = array_sum( $values );

		switch ( $type ) {
			case 'sum':
			case 'entry_count':
				return $sum;
			case 'average':
				return $sum / count( $values );
			default:
				return 0;
		}
	}

	/**
	 * Retrieves the total value of a product field, including its quantity.
	 *
	 * @since 1.4
	 *
	 * @param \GF_Field $product_field The product field.
	 * @param array     $entry         The entry object.
	 *
	 * @return float|int The value.
	 */
	private function get_product_value( \GF_Field $product_field, array $entry ) {
		$products = \GFCommon::get_product_fields( \GFAPI::get_form( rgobj( $product_field, 'formId' ) ), $entry );
		if ( 'total' === $product_field->type ) {
			return \GFCommon::get_total( $products );
		}

		$current_fields = [ $product_field->id ];
		if ( 'option' === $product_field->type ) {
			foreach ( $products['products'] as $field_id => $product ) {
				foreach ( $product['options'] ?? [] as $option ) {
					if ( $product_field->id === $option['id'] ?? 0 ) {
						$current_fields[]                           = $field_id;
						$products['products'][ $field_id ]['price'] = 0; // Empty out, to only collect option value.
						break 2;
					}
				}
			}
		}

		// Remove all other fields except the current one.
		$products['products'] = array_filter(
			$products['products'],
			function ( $key ) use ( $current_fields ) {
				return in_array( $key, $current_fields, true );
			},
			ARRAY_FILTER_USE_KEY
		);

		$products['shipping']['price'] =
			'shipping' === $product_field->type
				? $products['shipping']['price']
				: 0;

		return \GFCommon::get_total( $products );
	}

	/**
	 * Whether the field is a product field.
	 *
	 * Values for product fields are retrieved from the product calculation, instead of the field itself.
	 *
	 * @since 1.4
	 *
	 * @param \GF_Field $field The field to check.
	 *
	 * @return bool
	 */
	public static function is_product_field( \GF_Field $field ): bool {
		// Quantity is a product field, but it can be handled like a number field.
		if ( 'quantity' === $field->type ) {
			return false;
		}

		return \GFCommon::is_product_field( $field->type );
	}

	/**
	 * Whether the field output is monetary
	 *
	 * @since 1.4
	 *
	 * @param \GF_Field $field The field to check.
	 *
	 * @return bool
	 */
	private function is_price_field( \GF_Field $field ): bool {
		if ( 'currency' === $field->{'numberFormat'} ?? null ) {
			return true;
		}

		return self::is_product_field( $field );
	}

	/**
	 * Display formatting for timeline scales per scale (PHP).
	 *
	 * `q` will return the current quarter.
	 *
	 * @see   https://www.php.net/manual/en/datetime.format.php
	 * @since 1.6
	 *
	 * @return array<string, string>
	 */
	private function timeline_label_formats(): array {
		$formats = [
			'day'     => 'Y-m-d',
			'week'    => '\WW',
			'month'   => 'Y-m',
			'quarter' => 'Y \Qq',
			'year'    => 'Y',
		];

		/**
		 * Filters the label formats for the timeline scales.
		 *
		 * @since 1.6
		 * @param array<string, string> $formats The formats in an associative array, with the scale as key.
		 */
		return apply_filters( 'gk/gravitycharts/timeline/label-formats', $formats );
	}

	/**
	 * Returns the label of a date, based on the scale.
	 *
	 * @param string $date           The date.
	 * @param string $timeline_scale The scale.
	 *
	 * @return string The formatted date according to the scale format.
	 * @throws \Exception If the date could not be created.
	 * @since 1.6
	 */
	private function get_time_scale_label( string $date, string $timeline_scale ): string {
		$format = $this->timeline_label_formats()[ $timeline_scale ];

		return ( new TimelineDate( $date, wp_timezone() ) )->with_scale( $timeline_scale )->format( $format );
	}
}
