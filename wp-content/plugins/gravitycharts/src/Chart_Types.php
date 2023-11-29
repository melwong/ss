<?php
/**
 * GravityCharts Chart Types.
 *
 * @package GravityKit\GravityCharts
 */

namespace GravityKit\GravityCharts;

/**
 * GravityCharts Chart Types.
 */
class Chart_Types {
	const DEFAULT_TYPE = 'bar';

	/**
	 * Gets an array of all the supported chart types.
	 *
	 * Contains the label, icon, value.
	 *
	 * @since 1.0
	 *
	 * @return array{label:string,value:string,icon:string} Returns array of chart types.
	 */
	public static function get_all(): array {
		$types = [
			'bar'       => [
				'label'    => esc_html__( 'Bar / Column', 'gk-gravitycharts' ),
				'value'    => 'bar',
				'icon'     => '<svg role="img" xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512"><path d="m32 32c17.7 0 32 14.3 32 32v336c0 8.8 7.2 16 16 16h400c17.7 0 32 14.3 32 32s-14.3 32-32 32h-400c-44.2 0-80-35.8-80-80v-336c0-17.7 14.3-32 32-32z"/><path d="m160 224c17.7 0 32 14.3 32 32v64c0 17.7-14.3 32-32 32s-32-14.3-32-32v-64c0-17.7 14.3-32 32-32z" class="fill-blue"/><path d="m288 320c0 17.7-14.3 32-32 32s-32-14.3-32-32v-160c0-17.7 14.3-32 32-32s32 14.3 32 32z" class="fill-red"/><path d="m352 192c17.7 0 32 14.3 32 32v96c0 17.7-14.3 32-32 32s-32-14.3-32-32v-96c0-17.7 14.3-32 32-32z" class="fill-green"/><path d="m480 320c0 17.7-14.3 32-32 32s-32-14.3-32-32v-224c0-17.7 14.3-32 32-32s32 14.3 32 32z" class="fill-yellow"/></svg>',
				'supports' => [
					'palette',
					'indexAxis',
					'borderWidth',
					'xGridDisplay',
					'yGridDisplay',
					'autoScale',
					'min',
					'max',
					'timeline',
				],
			],
			'line'      => [
				'label'    => esc_html__( 'Line / Area', 'gk-gravitycharts' ),
				'value'    => 'line',
				'icon'     => '<svg role="img" xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512"><path d="M64 400c0 8.8 7.2 16 16 16h400c17.7 0 32 14.3 32 32s-14.3 32-32 32H80c-44.2 0-80-35.8-80-80V64c0-17.7 14.3-32 32-32s32 14.3 32 32v336z"/><path class="fill-blue" d="M342.6 278.6c-12.5 12.5-32.7 12.5-45.2 0L240 221.3l-89.4 89.3c-12.5 12.5-32.7 12.5-45.2 0s-12.5-32.7 0-45.2l112-112c12.5-12.5 32.7-12.5 45.2 0l57.4 57.3 105.4-105.3c12.5-12.5 32.7-12.5 45.2 0s12.5 32.7 0 45.2l-128 128z"/></svg>',
				'supports' => [
					'palette',
					'tension',
					'borderWidth',
					'pointRadius',
					'pointStyle',
					'xGridDisplay',
					'yGridDisplay',
					'autoScale',
					'min',
					'max',
					'fill',
					'timeline',
				],
			],
			'pie'       => [
				'label'    => esc_html__( 'Pie / Doughnut', 'gk-gravitycharts' ),
				'value'    => 'pie',
				'icon'     => '<svg role="img" xmlns="http://www.w3.org/2000/svg" width="489.9px" height="489.4px" viewBox="0 0 489.9 489.4"><g><path class="fill-blue" d="M243.4,23.3c0-9,1.1-16.6,11-16.6c116.9,0,221,91.3,230,216c0.6,9-8.6,16-17.6,16H244.4L243.4,23.3z"/><path class="fill-red" d="M5.1,243.7C5.1,122.4,95.2,22,211.2,5.9c10.1-1.3,17.9,6.1,17.9,15.4v231.3l156.5,163.6 c6.7,6.7,6.2,17.7-1.5,23.2c-39.2,27.9-87.2,44.3-139,44.3C112.6,483.7,5.1,376.3,5.1,243.7z"/><path class="fill-yellow" d="M468.2,251.6c9.2,0,16.6,7.8,15.4,17c-7.7,55.9-24.7,99.6-63.9,136.3c-6,4.8-15.4,5.2-21.2-0.6L246,251.7 L468.2,251.6z"/></g></svg>',
				'supports' => [
					'palette',
					'cutout',
					'borderWidth',
					'offset',
				],
			],
			'polarArea' => [
				'label'    => esc_html__( 'Polar Area', 'gk-gravitycharts' ),
				'value'    => 'polarArea',
				'icon'     => '<svg role="img" xmlns="http://www.w3.org/2000/svg" width="894.9px" height="783.9px" viewBox="0 0 894.9 783.9"><g><path class="fill-blue" d="M424.4,1.2C403.8,3,383.2,6,363,10.4C314,21,267,38.8,223.5,63.2c-53.2,29.8-100.2,68.7-139.6,115.6 c-34.5,41-62.1,87.4-82,137.9c-0.6,1.5-1.2,3.1-1.8,4.7l1.5,0.5c18.4,6,36.8,11.9,55.3,17.9l11,3.6 c61.8,20.1,123.6,40.2,185.4,60.2c22,7.1,43.9,14.3,65.9,21.4c40.5,13.2,82,26.7,123.4,40.1c0-79.2,0-158.4,0-237.5v-25.4l0-41.8 c0-53.4,0-106.9,0-160.3C436.9,0.3,430.9,0.7,424.4,1.2z"/><path class="fill-red" d="M679.9,404.5c53.1-17.3,106.2-34.5,159.4-51.7c18.5-6,37.1-12,55.6-18c-7.6-19.6-16.5-38.3-26.3-56.1 c-27.9-50.3-64.6-94.4-108.9-131C719,114.1,673.2,88,623.5,70.1c-41-14.8-84.3-23.6-128.9-26.3v5.4c0,138.5,0,277.1,0,415.6 c4.7-1.5,9.5-3.1,14.2-4.6c22.5-7.3,45.1-14.7,67.6-22C610.9,427,645.4,415.7,679.9,404.5z"/><path class="fill-green" d="M830.8,427.3c-1-5.2-2.2-10.5-3.5-15.8c-51.9,16.9-103.9,33.7-155.8,50.6c-39.7,12.9-79.4,25.8-119.1,38.8 l-41.6,13.5c23.4,32.1,47,64.6,69.9,96.1l17.1,23.5c19.7,27.2,40.9,56.3,61.9,85.3c0.1,0.2,0.2,0.3,0.4,0.5 c0.9,1.2,2.3,2.9,3.6,5.3c1,1.2,1.8,2.4,2.6,3.5c1.4,1.6,2.8,3.4,4,5.6c10.8,14.9,21.8,30,32.4,44.7l3.7,5.1 c18-15.1,34.4-31.7,48.9-49.6c35.3-43.6,59.8-93.1,72.8-147.1c8.3-34.4,11.6-69,9.9-102.7C836.7,464.8,834.4,445.5,830.8,427.3z"/><path class="fill-yellow" d="M612.2,743c-0.3-0.4-0.6-0.8-0.9-1.2c-15.9-21.8-32-44-47.6-65.4c-7.4-10.2-14.8-20.4-22.2-30.5 c-10.1-13.9-20.3-27.9-30.4-41.8c-14.1-19.3-28.5-39.1-42.8-58.9l-3.4,4.7c-16,22-31.9,44-47.9,65.9 c-17.2,23.6-34.3,47.2-51.5,70.8c-12.9,17.7-25.8,35.4-38.6,53.1c-0.8,1.1-1.6,2.2-2.4,3.3c15.4,9.2,31.4,16.7,47.8,22.6 c20.5,7.4,41.6,12.4,62.7,14.9c10.9,1.3,22.1,2,33.5,2c0.4,0,0.8,0,1.2,0c7.7,0,15.8-0.4,24.2-1.2c13.7-1.3,26.3-3.3,38.4-6.1 C560.5,768.5,587.3,757.7,612.2,743z"/><path class="fill-grey" d="M322.2,627.9c3.8,4.4,7.9,8.6,12.3,12.8c10.6-14.5,21.3-29.3,31.7-43.6l4.6-6.4c5-6.9,10.1-13.9,15.1-20.8 c6.6-9.1,13.1-18.1,19.7-27.2c6.2-8.5,12.4-17,18.6-25.5c0.7-0.9,1.3-1.8,2-2.7c-11.3-3.7-22.6-7.4-33.9-11 c-11.8-3.8-23.6-7.7-35.4-11.5c-18.5-6-37-12-55.5-18c-8-2.6-16-5.2-24-7.8c-1.4,7.3-2.2,14.2-2.5,20.9 c-0.8,18.1-0.1,32.6,2.3,45.7c3.2,18,8.5,35,15.8,50.6C300.4,599.2,310.2,614.2,322.2,627.9z"/></g></svg>',
				'supports' => [
					'palette',
					'rGridDisplay',
					'ticksDisplay',
					'autoScale',
					'min',
					'max',
					'offset',
				],
			],
			'radar'     => [
				'label'    => esc_html__( 'Radar', 'gk-gravitycharts' ),
				'value'    => 'radar',
				'icon'     => '<svg role="img" xmlns="http://www.w3.org/2000/svg" width="539.5px" height="519.9px" viewBox="0 0 539.5 519.9"><g><path d="M393.2,519.9H146.3c-30.4,0-57.2-19.4-66.6-48.4L3.5,236.8c-9.4-28.9,0.8-60.4,25.4-78.3L228.6,13.4 c24.6-17.9,57.7-17.9,82.3,0l199.7,145.1c24.6,17.9,34.8,49.3,25.4,78.3l-76.3,234.8C450.4,500.5,423.6,519.9,393.2,519.9z M269.7,40c-6.2,0-12.4,1.9-17.6,5.7L52.4,190.9c-10.5,7.7-14.9,21.1-10.9,33.5l76.3,234.8c4,12.4,15.5,20.7,28.5,20.7h246.9 c13,0,24.5-8.3,28.5-20.7L498,224.4c4-12.4-0.4-25.9-10.9-33.5L287.4,45.8C282.1,41.9,275.9,40,269.7,40z"/></g><g><polygon class="fill-red" points="128.5,301.9 271.2,152.8 410.7,378.5"/><circle class="fill-blue" cx="138.7" cy="292.5" r="34"/><circle class="fill-blue" cx="271.7" cy="152.5" r="34"/><circle class="fill-blue" cx="400.7" cy="367.5" r="34"/></g></svg>',
				'supports' => [
					'palette',
					'tension',
					'borderWidth',
					'pointRadius',
					'pointStyle',
					'angleLinesDisplay',
					'rGridDisplay',
					'ticksDisplay',
					'pointLabelsDisplay',
					'autoScale',
					'min',
					'max',
					'fill',
				],
			],
		];

		return array_map(
			static function ( array $type ): array {
				// Add `data-supports` field on the `radio`-field.
				if ( array_key_exists( 'supports', $type ) ) {
					$type['data-supports'] = wp_json_encode( $type['supports'] );
				}

				return $type;

			},
			$types
		);
	}

	/**
	 * Returns only chart types that support a specific chart setting
	 *
	 * @since 1.0
	 *
	 * @param string $setting        Name of the setting (like 'pointLabelsDisplay').
	 * @param bool   $return_as_keys Should the value be the whole chart array {@see get_all()} or only the type keys.
	 *
	 * @return array[] If $return_as_keys, keys of chart types that support $setting. Otherwise, the full array of supported settings.
	 */
	public static function supports_setting( string $setting, bool $return_as_keys = true ): array {
		$chart_types = self::get_all();

		foreach ( $chart_types as $key => $chart_type ) {
			if ( ! in_array( $setting, (array) $chart_type['supports'], true ) ) {
				unset( $chart_types[ $key ] );
			}
		}

		return $return_as_keys ? array_keys( $chart_types ) : $chart_types;
	}

	/**
	 * Gets one chart type using the key.
	 *
	 * @since 1.0
	 *
	 * @param string $type The chart type.
	 *
	 * @return array|false
	 */
	public static function get( string $type ) {
		$types = self::get_all();

		return $types[ $type ] ?? false;
	}
}
