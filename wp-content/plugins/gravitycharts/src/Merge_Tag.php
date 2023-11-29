<?php
/**
 * Gravity Forms Merge Tag support
 *
 * @package GravityKit\GravityCharts
 */

namespace GravityKit\GravityCharts;

/**
 * GravityCharts shortcode.
 */
class Merge_Tag {
	/**
	 * Shortcode name.
	 *
	 * @since 1.1
	 *
	 * @var string
	 */
	const MERGE_TAG = 'gravitycharts';

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
	 * @since 1.2
	 */
	public function __construct() {
		add_filter( 'gform_custom_merge_tags', [ $this, 'add_merge_tag' ], 10, 4 );
		add_filter( 'gform_replace_merge_tags', [ $this, 'replace_merge_tags' ], 10, 7 );
	}

	/**
	 * Returns class instance.
	 *
	 * @since 1.2
	 *
	 * @return Merge_Tag
	 */
	public static function get_instance(): Merge_Tag {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new Merge_Tag();
		}

		return self::$_instance;
	}

	/**
	 * Add custom merge tags to merge tag options. DO NOT OVERRIDE.
	 *
	 * @internal Not to be overridden by fields
	 *
	 * @since 1.8.4
	 *
	 * @param array       $custom_merge_tags Existing array of merge tags.
	 * @param int         $form_id GF Form ID.
	 * @param \GF_Field[] $fields Array of fields in the form.
	 * @param string      $element_id The ID of the input that Merge Tags are being used on.
	 *
	 * @return array Modified merge tags
	 */
	public function add_merge_tag( $custom_merge_tags = [], $form_id = 0, $fields = [], $element_id = '' ) {

		$feeds = \GFAPI::get_feeds( null, $form_id, Chart_Feed::get_instance()->get_slug() );

		if ( empty( $feeds ) ) {
			return $custom_merge_tags;
		}

		if ( is_wp_error( $feeds ) && Plugin::get_instance()->chart_feed ) {
			Plugin::get_instance()->chart_feed->log_error( 'Error fetching feeds for form ID ' . esc_html( $form_id ) . ': ' . $feeds->get_error_message() );
			return $custom_merge_tags;
		}

		$merge_tags = [];

		foreach ( $feeds as $feed ) {

			if ( empty( $feed['id'] ) ) {
				continue;
			}

			$merge_tags[] = [
				'label' => strtr(
					esc_html_x( 'GravityCharts: {name} #{id}', 'Label in the merge tag picker. Do not translate {name} and {id}; {name} will be replaced by the chart feed name and {id} will be replaced by the chart feed ID.', 'gk-gravitycharts' ),
					[
						'{name}' => esc_html( rgars( $feed, 'meta/chartName', __( 'Feed', 'gk-gravitycharts' ) ) ),
						'{id}'   => (int) rgar( $feed, 'id' ),
					]
				),
				'tag'   => '{' . self::MERGE_TAG . ':' . rgar( $feed, 'id' ) . '}',
			];
		}

		return array_merge( $custom_merge_tags, $merge_tags );
	}

	/**
	 * Match the merge tag in replacement text for the field.  DO NOT OVERRIDE.
	 *
	 * @since 1.2
	 *
	 * @see replace_merge_tag Override replace_merge_tag() to handle any matches
	 *
	 * @param string|null $text Text to replace.
	 * @param array       $form Gravity Forms form array.
	 * @param array       $entry Entry array.
	 * @param bool        $url_encode Whether to URL-encode output.
	 * @param bool        $esc_html Whether to encode HTML.
	 *
	 * @return string|null Original text if {_custom_merge_tag} isn't found. Otherwise, replaced text.
	 */
	public function replace_merge_tags( $text = '', $form = [], $entry = [], $url_encode = false, $esc_html = false ): ?string {

		if ( is_null( $text ) ) {
			return $text;
		}

		if ( false === strpos( $text, '{' . self::MERGE_TAG . ':' ) ) {
			return $text;
		}

		// Get all instances of the merge tag in {gravitycharts:[id]:[atts]} format.
		preg_match_all( '/{' . preg_quote( self::MERGE_TAG, '/' ) . ':(?<feed_id>[\d]+):?(?<atts>.+?)?}/ism', $text, $matches, PREG_SET_ORDER );

		// If there are no matches, return original text.
		if ( empty( $matches ) ) {
			return $text;
		}

		$return = $text;

		// Replace each match with the chart image by performing the shortcode.
		foreach ( $matches as $match ) {

			if ( empty( $match['feed_id'] ) ) {
				continue;
			}

			// The atts should be formatted in a string parseable by shortcodes.
			$atts = (array) shortcode_parse_atts( (string) rgar( $match, 'atts', '' ) );

			$atts['id']         = (int) rgar( $match, 'feed_id', 0 );
			$atts['embed_type'] = 'image';

			// Add the current entry ID if 'entry=true' was provided.
			if ( isset( $entry['id'] ) && wp_validate_boolean( rgar( $atts, 'entry', false ) ) ) {
				$atts['entry'] = (int) rgar( $entry, 'id', 0 );
			}

			$shortcode_output = Shortcode::get_instance()->do_shortcode( $atts );

			if ( ! $shortcode_output ) {
				continue;
			}

			// Call the shortcode method directly to reduce execution time.
			$return = str_replace( $match[0], $shortcode_output, $return );
		}

		return $return;
	}
}
