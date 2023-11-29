<?php
/**
 * Display the GravityCharts chart based on a date value.
 *
 * @package GravityKit\GravityCharts
 */

namespace GravityKit\GravityCharts;

/**
 * Gravity Chars Data types.
 *
 * @since 1.4
 */
final class Data_Types {

	public const DEFAULT_TYPE = 'field';

	public const DEFAULT_TIMELINE_TYPE = 'entry_count';

	/**
	 * Gets an array of all the supported data types.
	 *
	 * @since 1.4
	 *
	 * @return array{label:string,value:string} Returns array of data types.
	 */
	public static function get_all(): array {
		return [
			'field'    => [
				'label' => sprintf( '%s (%s)', esc_html__( 'By Field', 'gk-gravitycharts' ), esc_html__( 'Default', 'gk-gravitycharts' ) ),
				'value' => 'field',
			],
			'timeline' => [
				'label' => esc_html__( 'Show on Timeline', 'gk-gravitycharts' ),
				'value' => 'timeline',
			],
		];
	}

	/**
	 * Gets an array of all supported value types for timeline data type.
	 *
	 * @since 1.4
	 *
	 * @return array{label:string,value:string} Returns array of data types.
	 */
	public static function get_timeline_types(): array {
		return [
			'entry_count' => [
				'label' => esc_html__( 'Entry count', 'gk-gravitycharts' ),
				'value' => 'entry_count',
			],
			'sum'         => [
				'label' => esc_html__( 'Sum values', 'gk-gravitycharts' ),
				'value' => 'sum',
			],
			'average'     => [
				'label' => esc_html__( 'Average values', 'gk-gravitycharts' ),
				'value' => 'average',
			],
		];
	}
}
