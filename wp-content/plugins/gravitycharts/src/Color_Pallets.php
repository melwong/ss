<?php
/**
 *  GravityCharts Color pallets.
 *
 * @package GravityKit\GravityCharts
 */

namespace GravityKit\GravityCharts;

/**
 * GravityCharts Color pallets
 */
final class Color_Pallets {
	/**
	 * Gets color palettes used to customize Chart.js.
	 *
	 * "Qualitative colors are used to render categorical data, such as gender or race, that has no inherent sequential order."
	 *
	 * This product includes color specifications and designs developed by Cynthia Brewer (http://colorbrewer.org/).
	 *
	 * @since     1.0
	 *
	 * @return array
	 * @copyright Copyright (c) 2002 Cynthia Brewer, Mark Harrower, and The Pennsylvania State University.
	 */
	public static function get_all(): array {
		$color_palettes = [
			'default'             => [
				'label'                => esc_html__( 'Default', 'gk-gravitycharts' ),
				'nature'               => 'qualitative',
				'pointBackgroundColor' => 'rgb(0,160,210)',
				'borderColor'          => [
					'rgb(0,160,210)',
					'rgb(130,110,180)',
					'rgb(70,180,80)',
					'rgb(255,185,0)',
					'rgb(245,110,40)',
					'rgb(220,50,50)',
				],
				'backgroundColor'      => [
					'rgb(0,160,210,0.85)',
					'rgb(130,110,180,0.85)',
					'rgb(70,180,80,0.85)',
					'rgb(255,185,0,0.85)',
					'rgb(245,110,40,0.85)',
					'rgb(220,50,50,0.85)',
				],
			],
			'google_standard'     => [
				'label'                => esc_html__( 'Standard', 'gk-gravitycharts' ),
				'nature'               => 'qualitative',
				'pointBackgroundColor' => 'rgb(66, 133, 244)',
				'borderColor'          => [
					'rgb(66, 133, 244)',
					'rgb(234, 67, 53)',
					'rgb(251, 188, 4)',
					'rgb(52, 168, 83)',
					'rgb(255, 109, 1)',
					'rgb(70, 189, 198)',
				],
				'backgroundColor'      => [
					'rgba(66, 133, 244,0.85)',
					'rgba(234, 67, 53,0.85)',
					'rgba(251, 188, 4,0.85)',
					'rgba(52, 168, 83,0.85)',
					'rgba(255, 109, 1,0.85)',
					'rgba(70, 189, 198,0.85)',
				],
			],
			'google_simple_light' => [
				'label'                => esc_html__( 'Simple Light', 'gk-gravitycharts' ),
				'nature'               => 'qualitative',
				'pointBackgroundColor' => 'rgb(88, 145, 173)',
				'borderColor'          => [
					'rgb(88, 145, 173)',
					'rgb(0, 69, 97)',
					'rgb(255, 111, 49)',
					'rgb(28, 118, 133)',
					'rgb(15, 69, 168)',
					'rgb(76, 220, 139)',
				],
				'backgroundColor'      => [
					'rgba(88, 145, 173,0.85)',
					'rgba(0, 69, 97,0.85)',
					'rgba(255, 111, 49,0.85)',
					'rgba(28, 118, 133,0.85)',
					'rgba(15, 69, 168,0.85)',
					'rgba(76, 220, 139,0.85)',
				],
			],
			'google_streamline'   => [
				'label'                => esc_html__( 'Streamline', 'gk-gravitycharts' ),
				'nature'               => 'qualitative',
				'pointBackgroundColor' => 'rgb(26, 153, 136)',
				'borderColor'          => [
					'rgb(26, 153, 136)',
					'rgb(45, 114, 157)',
					'rgb(31, 62, 120)',
					'rgb(235, 86, 0)',
					'rgb(255, 153, 172)',
					'rgb(255, 212, 184)',
				],
				'backgroundColor'      => [
					'rgba(26, 153, 136,0.85)',
					'rgba(45, 114, 157,0.85)',
					'rgba(31, 62, 120,0.85)',
					'rgba(235, 86, 0,0.85)',
					'rgba(255, 153, 172,0.85)',
					'rgba(255, 212, 184,0.85)',
				],
			],
			'google_paradigm'     => [
				'label'                => esc_html__( 'Paradigm', 'gk-gravitycharts' ),
				'nature'               => 'qualitative',
				'pointBackgroundColor' => 'rgb(111, 200, 214)',
				'borderColor'          => [
					'rgb(111, 200, 214)',
					'rgb(0, 47, 74)',
					'rgb(173, 132, 99)',
					'rgb(184, 87, 65)',
					'rgb(0, 147, 132)',
					'rgb(237, 218, 201)',
				],
				'backgroundColor'      => [
					'rgba(111, 200, 214,0.85)',
					'rgba(0, 47, 74,0.85)',
					'rgba(173, 132, 99,0.85)',
					'rgba(184, 87, 65,0.85)',
					'rgba(0, 147, 132,0.85)',
					'rgba(237, 218, 201,0.85)',
				],
			],
			'google_shift'        => [
				'label'                => esc_html__( 'Shift', 'gk-gravitycharts' ),
				'nature'               => 'qualitative',
				'pointBackgroundColor' => 'rgb(20, 76, 245)',
				'borderColor'          => [
					'rgb(20, 76, 245)',
					'rgb(0, 121, 107)',
					'rgb(0, 67, 94)',
					'rgb(191, 134, 89)',
					'rgb(217, 86, 63)',
					'rgb(231, 187, 99)',
				],
				'backgroundColor'      => [
					'rgba(20, 76, 245,0.85)',
					'rgba(0, 121, 107,0.85)',
					'rgba(0, 67, 94,0.85)',
					'rgba(191, 134, 89,0.85)',
					'rgba(217, 86, 63,0.85)',
					'rgba(231, 187, 99,0.85)',
				],
			],
			'google_momentum'     => [
				'label'                => esc_html__( 'Momentum', 'gk-gravitycharts' ),
				'nature'               => 'qualitative',
				'pointBackgroundColor' => 'rgb(215, 230, 163)',
				'borderColor'          => [
					'rgb(215, 230, 163)',
					'rgb(192, 121, 27)',
					'rgb(253, 91, 88)',
					'rgb(11, 99, 116)',
					'rgb(39, 39, 139)',
					'rgb(141, 216, 211)',
				],
				'backgroundColor'      => [
					'rgba(215, 230, 163,0.85)',
					'rgba(192, 121, 27,0.85)',
					'rgba(253, 91, 88,0.85)',
					'rgba(11, 99, 116,0.85)',
					'rgba(39, 39, 139,0.85)',
					'rgba(141, 216, 211,0.85)',
				],
			],
			'google_earthy'       => [
				'label'                => esc_html__( 'Earthy', 'gk-gravitycharts' ),
				'nature'               => 'qualitative',
				'pointBackgroundColor' => 'rgb(139, 137, 72)',
				'borderColor'          => [
					'rgb(139, 137, 72)',
					'rgb(176, 114, 93)',
					'rgb(231, 154, 60)',
					'rgb(143, 55, 56)',
					'rgb(68, 120, 116)',
					'rgb(210, 212, 121)',
				],
				'backgroundColor'      => [
					'rgba(139, 137, 72,0.85)',
					'rgba(176, 114, 93,0.85)',
					'rgba(231, 154, 60,0.85)',
					'rgba(143, 55, 56,0.85)',
					'rgba(68, 120, 116,0.85)',
					'rgba(210, 212, 121,0.85)',
				],
			],
			'google_energetic'    => [
				'label'                => esc_html__( 'Energetic', 'gk-gravitycharts' ),
				'nature'               => 'qualitative',
				'pointBackgroundColor' => 'rgb(209, 235, 96)',
				'borderColor'          => [
					'rgb(209, 235, 96)',
					'rgb(29, 35, 132)',
					'rgb(245, 166, 155)',
					'rgb(228, 72, 25)',
					'rgb(166, 128, 87)',
					'rgb(244, 170, 17)',
				],
				'backgroundColor'      => [
					'rgba(209, 235, 96,0.85)',
					'rgba(29, 35, 132,0.85)',
					'rgba(245, 166, 155,0.85)',
					'rgba(228, 72, 25,0.85)',
					'rgba(166, 128, 87,0.85)',
					'rgba(244, 170, 17,0.85)',
				],
			],
			'google_ocean'        => [
				'label'                => esc_html__( 'Ocean', 'gk-gravitycharts' ),
				'nature'               => 'qualitative',
				'pointBackgroundColor' => 'rgb(143, 225, 255)',
				'borderColor'          => [
					'rgb(143, 225, 255)',
					'rgb(0, 120, 184)',
					'rgb(0, 163, 250)',
					'rgb(0, 145, 222)',
					'rgb(66, 189, 255)',
					'rgb(110, 204, 255)',
				],
				'backgroundColor'      => [
					'rgba(143, 225, 255,0.85)',
					'rgba(0, 120, 184,0.85)',
					'rgba(0, 163, 250,0.85)',
					'rgba(0, 145, 222,0.85)',
					'rgba(66, 189, 255,0.85)',
					'rgba(110, 204, 255,0.85)',
				],
			],
			'google_cozy'         => [
				'label'                => esc_html__( 'Cozy', 'gk-gravitycharts' ),
				'nature'               => 'qualitative',
				'pointBackgroundColor' => 'rgb(56, 98, 181)',
				'borderColor'          => [
					'rgb(56, 98, 181)',
					'rgb(0, 83, 92)',
					'rgb(159, 84, 92)',
					'rgb(205, 132, 14)',
					'rgb(217, 193, 173)',
					'rgb(213, 232, 172)',
				],
				'backgroundColor'      => [
					'rgba(56, 98, 181,0.85)',
					'rgba(0, 83, 92,0.85)',
					'rgba(159, 84, 92,0.85)',
					'rgba(205, 132, 14,0.85)',
					'rgba(217, 193, 173,0.85)',
					'rgba(213, 232, 172,0.85)',
				],
			],
			'google_groovy'       => [
				'label'                => esc_html__( 'Groovy', 'gk-gravitycharts' ),
				'nature'               => 'qualitative',
				'pointBackgroundColor' => 'rgb(31, 149, 209)',
				'borderColor'          => [
					'rgb(31, 149, 209)',
					'rgb(201, 50, 50)',
					'rgb(13, 57, 140)',
					'rgb(226, 114, 40)',
					'rgb(252, 68, 121)',
					'rgb(220, 220, 18)',
				],
				'backgroundColor'      => [
					'rgba(31, 149, 209,0.85)',
					'rgba(201, 50, 50,0.85)',
					'rgba(13, 57, 140,0.85)',
					'rgba(226, 114, 40,0.85)',
					'rgba(252, 68, 121,0.85)',
					'rgba(220, 220, 18,0.85)',
				],
			],
			'google_calm'         => [
				'label'                => esc_html__( 'Calm', 'gk-gravitycharts' ),
				'nature'               => 'qualitative',
				'pointBackgroundColor' => 'rgb(230, 140, 165)',
				'borderColor'          => [
					'rgb(230, 140, 165)',
					'rgb(96, 143, 102)',
					'rgb(223, 163, 152)',
					'rgb(39, 71, 102)',
					'rgb(171, 144, 132)',
					'rgb(97, 57, 66)',
				],
				'backgroundColor'      => [
					'rgba(230, 140, 165,0.85)',
					'rgba(96, 143, 102,0.85)',
					'rgba(223, 163, 152,0.85)',
					'rgba(39, 71, 102,0.85)',
					'rgba(171, 144, 132,0.85)',
					'rgba(97, 57, 66,0.85)',
				],
			],
			'google_forest'       => [
				'label'                => esc_html__( 'Forest', 'gk-gravitycharts' ),
				'nature'               => 'qualitative',
				'pointBackgroundColor' => 'rgb(160, 219, 193)',
				'borderColor'          => [
					'rgb(160, 219, 193)',
					'rgb(15, 107, 79)',
					'rgb(93, 166, 138)',
					'rgb(56, 138, 102)',
					'rgb(118, 181, 154)',
					'rgb(140, 207, 172)',
				],
				'backgroundColor'      => [
					'rgba(160, 219, 193,0.85)',
					'rgba(15, 107, 79,0.85)',
					'rgba(93, 166, 138,0.85)',
					'rgba(56, 138, 102,0.85)',
					'rgba(118, 181, 154,0.85)',
					'rgba(140, 207, 172,0.85)',
				],
			],
			'google_vintage'      => [
				'label'                => esc_html__( 'Vintage', 'gk-gravitycharts' ),
				'nature'               => 'qualitative',
				'pointBackgroundColor' => 'rgb(155, 187, 170)',
				'borderColor'          => [
					'rgb(155, 187, 170)',
					'rgb(212, 84, 27)',
					'rgb(222, 151, 121)',
					'rgb(0, 84, 84)',
					'rgb(137, 94, 33)',
					'rgb(129, 148, 98)',
				],
				'backgroundColor'      => [
					'rgba(155, 187, 170,0.85)',
					'rgba(212, 84, 27,0.85)',
					'rgba(222, 151, 121,0.85)',
					'rgba(0, 84, 84,0.85)',
					'rgba(137, 94, 33,0.85)',
					'rgba(129, 148, 98,0.85)',
				],
			],
			'google_retro'        => [
				'label'                => esc_html__( 'Retro', 'gk-gravitycharts' ),
				'nature'               => 'qualitative',
				'pointBackgroundColor' => 'rgb(245, 149, 156)',
				'borderColor'          => [
					'rgb(245, 149, 156)',
					'rgb(0, 99, 145)',
					'rgb(254, 185, 41)',
					'rgb(226, 74, 56)',
					'rgb(40, 153, 139)',
					'rgb(192, 222, 0)',
				],
				'backgroundColor'      => [
					'rgba(245, 149, 156,0.85)',
					'rgba(0, 99, 145,0.85)',
					'rgba(254, 185, 41,0.85)',
					'rgba(226, 74, 56,0.85)',
					'rgba(40, 153, 139,0.85)',
					'rgba(192, 222, 0,0.85)',
				],
			],
			'google_coral'        => [
				'label'                => esc_html__( 'Coral', 'gk-gravitycharts' ),
				'nature'               => 'qualitative',
				'pointBackgroundColor' => 'rgb(155, 204, 198)',
				'borderColor'          => [
					'rgb(155, 204, 198)',
					'rgb(215, 118, 89)',
					'rgb(0, 69, 82)',
					'rgb(171, 64, 40)',
					'rgb(69, 125, 124)',
					'rgb(80, 166, 166)',
				],
				'backgroundColor'      => [
					'rgba(155, 204, 198,0.85)',
					'rgba(215, 118, 89,0.85)',
					'rgba(0, 69, 82,0.85)',
					'rgba(171, 64, 40,0.85)',
					'rgba(69, 125, 124,0.85)',
					'rgba(80, 166, 166,0.85)',
				],
			],
			'chartJS'             => [
				'label'           => 'Chart.js',
				'nature'          => 'qualitative',
				'backgroundColor' => [
					'rgb(255, 99, 132, 0.85)',
					'rgb(255, 159, 64, 0.85)',
					'rgb(255, 205, 86, 0.85)',
					'rgb(75, 192, 192, 0.85)',
					'rgb(54, 162, 235, 0.85)',
					'rgb(153, 102, 255, 0.85)',
					'rgb(201, 203, 207, 0.85)',
				],
				'borderColor'     => [
					'rgb(255, 99, 132)',
					'rgb(255, 159, 64)',
					'rgb(255, 205, 86)',
					'rgb(75, 192, 192)',
					'rgb(54, 162, 235)',
					'rgb(153, 102, 255)',
					'rgb(201, 203, 207)',
				],
			],
			'paired'              => [
				'nature'               => 'qualitative',
				'label'                => esc_html__( 'Paired', 'gk-gravitycharts' ),
				'pointBackgroundColor' => 'rgb(166,206,227)',
				'borderColor'          => [
					'rgb(166,206,227)',
					'rgb(31,120,180)',
					'rgb(178,223,138)',
					'rgb(51,160,44)',
					'rgb(251,154,153)',
					'rgb(227,26,28)',
					'rgb(253,191,111)',
					'rgb(255,127,0)',
					'rgb(202,178,214)',
				],
				'backgroundColor'      => [
					'rgb(166,206,227,0.85)',
					'rgb(31,120,180,0.85)',
					'rgb(178,223,138,0.85)',
					'rgb(51,160,44,0.85)',
					'rgb(251,154,153,0.85)',
					'rgb(227,26,28,0.85)',
					'rgb(253,191,111,0.85)',
					'rgb(255,127,0,0.85)',
					'rgb(202,178,214,0.85)',
				],
			],
			'set1'                => [
				'nature'               => 'qualitative',
				'label'                => esc_html__( 'Bright', 'gk-gravitycharts' ),
				'pointBackgroundColor' => 'rgb(228,26,28)',
				'borderColor'          => [
					'rgb(228,26,28)',
					'rgb(55,126,184)',
					'rgb(77,175,74)',
					'rgb(152,78,163)',
					'rgb(255,127,0)',
					'rgb(255,255,51)',
					'rgb(166,86,40)',
					'rgb(247,129,191)',
					'rgb(153,153,153)',
				],
				'backgroundColor'      => [
					'rgb(228,26,28,0.85)',
					'rgb(55,126,184,0.85)',
					'rgb(77,175,74,0.85)',
					'rgb(152,78,163,0.85)',
					'rgb(255,127,0,0.85)',
					'rgb(255,255,51,0.85)',
					'rgb(166,86,40,0.85)',
					'rgb(247,129,191,0.85)',
					'rgb(153,153,153,0.85)',
				],
			],
			'learnui_qualitative' => [
				'nature'               => 'qualitative',
				'label'                => esc_html__( 'LearnUI', 'gk-gravitycharts' ),
				'pointBackgroundColor' => 'rgb(0, 63, 92)',
				'borderColor'          => [
					'rgb(0, 63, 92)',
					'rgb(47, 75, 124)',
					'rgb(102, 81, 145)',
					'rgb(160, 81, 149)',
					'rgb(212, 80, 135)',
					'rgb(249, 93, 106)',
					'rgb(255, 124, 67)',
					'rgb(255, 166, 0)',
				],
				'backgroundColor'      => [
					'rgb(0, 63, 92, 0.85)',
					'rgb(47, 75, 124, 0.85)',
					'rgb(102, 81, 145, 0.85)',
					'rgb(160, 81, 149, 0.85)',
					'rgb(212, 80, 135, 0.85)',
					'rgb(249, 93, 106, 0.85)',
					'rgb(255, 124, 67, 0.85)',
					'rgb(255, 166, 0, 0.85)',
				],
			],
			'census_qualitative'  => [
				'nature'               => 'qualitative',
				'label'                => esc_html__( 'Census', 'gk-gravitycharts' ),
				'pointBackgroundColor' => 'rgb(0, 149, 168)',
				'borderColor'          => [
					'rgb(0, 149, 168)',
					'rgb(17, 46, 81)',
					'rgb(255, 112, 67)',
					'rgb(120, 144, 156)',
					'rgb(46, 120, 210)',
					'rgb(0, 108, 122)',
					'rgb(255, 151, 118)',
				],
				'backgroundColor'      => [
					'rgb(0, 149, 168, 0.85)',
					'rgb(17, 46, 81, 0.85)',
					'rgb(255, 112, 67, 0.85)',
					'rgb(120, 144, 156, 0.85)',
					'rgb(46, 120, 210, 0.85)',
					'rgb(0, 108, 122, 0.85)',
					'rgb(255, 151, 118, 0.85)',
				],
			],
			'census_teal'         => [
				'nature'               => 'sequential',
				// translators: %s is replaced with color name (e.g., Blue).
				'label'                => sprintf( esc_html__( 'Census %s', 'gk-gravitycharts' ), esc_html__( 'Teal', 'gk-gravitycharts' ) ),
				'pointBackgroundColor' => 'rgb(0, 40, 46)',
				'borderColor'          => [
					'rgb(0, 40, 46)',
					'rgb(0, 72, 81)',
					'rgb(0, 108, 122)',
					'rgb(0, 149, 168)',
					'rgb(0, 190, 214)',
					'rgb(99, 225, 234)',
					'rgb(168, 245, 255)',
				],
				'backgroundColor'      => [
					'rgb(0, 40, 46, 0.85 )',
					'rgb(0, 72, 81, 0.85 )',
					'rgb(0, 108, 122, 0.85 )',
					'rgb(0, 149, 168, 0.85 )',
					'rgb(0, 190, 214, 0.85 )',
					'rgb(99, 225, 234, 0.85 )',
					'rgb(168, 245, 255, 0.85 )',
				],
			],
			'census_blue'         => [
				'nature'               => 'sequential',
				// translators: %s is replaced with color name (e.g., Blue).
				'label'                => sprintf( esc_html__( 'Census %s', 'gk-gravitycharts' ), esc_html__( 'Blue', 'gk-gravitycharts' ) ),
				'pointBackgroundColor' => 'rgb(8, 22, 39)',
				'borderColor'          => [
					'rgb(8, 22, 39)',
					'rgb(17, 46, 81)',
					'rgb(32, 84, 147)',
					'rgb(46, 120, 210)',
					'rgb(109, 161, 224)',
					'rgb(151, 188, 233)',
					'rgb(193, 215, 242)',
				],
				'backgroundColor'      => [
					'rgb(8, 22, 39, 0.85)',
					'rgb(17, 46, 81, 0.85)',
					'rgb(32, 84, 147, 0.85)',
					'rgb(46, 120, 210, 0.85)',
					'rgb(109, 161, 224, 0.85)',
					'rgb(151, 188, 233, 0.85)',
					'rgb(193, 215, 242, 0.85)',
				],
			],
			'census_orange'       => [
				'nature'               => 'sequential',
				// translators: %s is replaced with color name (e.g., Blue).
				'label'                => sprintf( esc_html__( 'Census %s', 'gk-gravitycharts' ), esc_html__( 'Orange', 'gk-gravitycharts' ) ),
				'pointBackgroundColor' => 'rgb(93, 40, 24)',
				'borderColor'          => [
					'rgb(93, 40, 24)',
					'rgb(133, 58, 34)',
					'rgb(194, 84, 50)',
					'rgb(255, 112, 67)',
					'rgb(255, 151, 118)',
					'rgb(255, 190, 169)',
					'rgb(255, 228, 220)',
				],
				'backgroundColor'      => [
					'rgb(93, 40, 24, 0.85)',
					'rgb(133, 58, 34, 0.85)',
					'rgb(194, 84, 50, 0.85)',
					'rgb(255, 112, 67, 0.85)',
					'rgb(255, 151, 118, 0.85)',
					'rgb(255, 190, 169, 0.85)',
					'rgb(255, 228, 220, 0.85)',
				],
			],
			'census_grey'         => [
				'nature'               => 'sequential',
				// translators: %s is replaced with color name (e.g., Blue).
				'label'                => sprintf( esc_html__( 'Census %s', 'gk-gravitycharts' ), esc_html__( 'Grey', 'gk-gravitycharts' ) ),
				'pointBackgroundColor' => 'rgb(34, 44, 49)',
				'borderColor'          => [
					'rgb(34, 44, 49)',
					'rgb(54, 72, 80)',
					'rgb(75, 99, 110)',
					'rgb(120, 144, 156)',
					'rgb(167, 192, 205)',
					'rgb(200, 215, 223)',
					'rgb(232, 239, 242)',
				],
				'backgroundColor'      => [
					'rgb(34, 44, 49, 0.85)',
					'rgb(54, 72, 80, 0.85)',
					'rgb(75, 99, 110, 0.85)',
					'rgb(120, 144, 156, 0.85)',
					'rgb(167, 192, 205, 0.85)',
					'rgb(200, 215, 223, 0.85)',
					'rgb(232, 239, 242, 0.85)',
				],
			],
		];

		/**
		 * Modifies the default colors used when creating a chart.
		 *
		 * @filter `gk/gravitycharts/color-palettes`
		 *
		 * @since  1.0
		 *
		 * @see    https://www.chartjs.org/docs/latest/charts/line.html for example settings.
		 * @see    https://xdgov.github.io/data-design-standards/components/colors for accessible color guidance.
		 *
		 * @param array $color_palettes Chart.js settings presets.
		 */
		return apply_filters( 'gk/gravitycharts/color-palettes', $color_palettes );
	}
}
