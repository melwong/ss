<?php
/**
 * Limited, WordPress-y implementation of the QuickChart API.
 *
 * That's why we're not using WordPress method/variable/property naming conventions.
 *
 * @since 1.2
 *
 * @package GravityKit\GravityCharts
 */

// phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
// phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
// phpcs:disable WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

namespace GravityKit\GravityCharts;

/**
 * QuickChart API class.
 *
 * Methods that have not been implemented from the original have been removed; in the future, this class may be updated
 * to support all of the original methods, but re-written to use the WordPress Remote API instead of curl.
 *
 * In addition, properties have been made more private.
 *
 * @see https://github.com/typpo/quickchart-php Based on the QuickChart PHP library, written by Ian Webster
 *
 * @since 1.2
 */
class QuickChart {

	/**
	 * Valid chart format types.
	 *
	 * @const VALID_FORMATS string[]
	 */
	public const VALID_FORMATS = [ 'png', 'webp', 'svg', 'pdf' ];

	/**
	 * Valid encoding options for the 'chart' parameter.
	 *
	 * @const ENCODING_OPTIONS string[]
	 */
	public const ENCODING_OPTIONS = [ 'url', 'base64' ];

	/**
	 * The API key to use for requests.
	 *
	 * @var string
	 */
	protected $protocol = 'https';

	/**
	 * The API host to use for requests.
	 *
	 * @var string
	 */
	protected $host = 'quickchart.io';

	/**
	 * The port to use for requests.
	 *
	 * @var int $port
	 */
	protected $port = 443;

	/**
	 * The chart configuration array or JSON-formatted string.
	 *
	 * @var string|array|null $config
	 */
	protected $config = null;

	/**
	 * The width of the chart.
	 *
	 * @var int $width
	 */
	protected $width = 500;

	/**
	 * The height of the chart.
	 *
	 * @var int $height
	 */
	protected $height = 300;

	/**
	 * The device pixel ratio. Defaults to 2.0 for retina displays. Width and height are multiplied by this value.
	 *
	 * @var float $devicePixelRatio
	 */
	protected $devicePixelRatio = 2.0;

	/**
	 * The format of the chart. Options: 'png', 'pdf'. Default: 'png'.
	 *
	 * @var string $format
	 */
	protected $format = 'png';

	/**
	 * The background color of the chart.
	 *
	 * @var string $backgroundColor
	 */
	protected $backgroundColor = 'transparent';

	/**
	 * The encoding of the chart. Options: 'base64' and 'url'. Default: 'url'.
	 *
	 * @var string $encoding
	 */
	protected $encoding = 'url';

	/**
	 * The API key to use for requests to QuickChart.io.
	 *
	 * @var string $apiKey
	 */
	protected $apiKey = '';

	/**
	 * The Chart.js version API to use when rendering the configuration.
	 *
	 * @var string $version
	 */
	protected $version = '2.9.4';

	/**
	 * QuickChart constructor.
	 *
	 * @param array $options Chart configuration options.
	 */
	public function __construct( array $options ) {

		$defaults = [
			'protocol'         => $this->protocol,
			'host'             => $this->host,
			'port'             => $this->port,
			'width'            => $this->width,
			'height'           => $this->height,
			'devicePixelRatio' => $this->devicePixelRatio,
			'format'           => $this->format,
			'backgroundColor'  => $this->backgroundColor,
			'encoding'         => $this->encoding,
			'apiKey'           => $this->apiKey,
			'version'          => $this->version,
		];

		// Remove any options that are empty strings or null.
		$options = array_filter( $options, [ $this, 'filterEmptyValues' ] );

		$options = shortcode_atts( $defaults, $options );

		$this->protocol         = (string) $options['protocol'];
		$this->host             = (string) $options['host'];
		$this->port             = (int) $options['port'];
		$this->width            = (int) $options['width'];
		$this->height           = (int) $options['height'];
		$this->devicePixelRatio = round( $options['devicePixelRatio'], 1 );
		$this->format           = (string) $options['format'];
		$this->backgroundColor  = rawurlencode( (string) $options['backgroundColor'] );
		$this->encoding         = (string) $options['encoding'];
		$this->apiKey           = (string) $options['apiKey'];
		$this->version          = (string) $options['version'];
	}

	/**
	 * Get the chart configuration.
	 *
	 * @return array|string
	 */
	public function getConfig() {
		return $this->config;
	}

	/**
	 * Set the chart configuration.
	 *
	 * @param string|array $chartjsConfig The Chart.js configuration to render.
	 *
	 * @return void
	 */
	public function setConfig( $chartjsConfig ): void {
		$this->config = $chartjsConfig;
	}

	/**
	 * Set the host to use for requests.
	 *
	 * @param string $host The host to use for requests. Default: 'quickchart.io'.
	 *
	 * @return void
	 */
	public function setHost( string $host ): void {
		$this->host = $host;
	}

	/**
	 * Set the API key to use for requests. Optional.
	 *
	 * @param string|null $apiKey The API key to use for requests.
	 *
	 * @return void
	 */
	public function setApiKey( string $apiKey ) {
		$this->apiKey = $apiKey;
	}

	/**
	 * Set the width of the chart (before device pixel ratio is applied).
	 *
	 * @param int $width The width of the chart.
	 *
	 * @return void
	 */
	public function setWidth( int $width ): void {
		$this->width = $width;
	}

	/**
	 * Set the height of the chart (before device pixel ratio is applied).
	 *
	 * @param int $height The height of the chart.
	 *
	 * @return void
	 */
	public function setHeight( int $height ): void {
		$this->height = $height;
	}

	/**
	 * Get the width of the chart (before device pixel ratio is applied).
	 *
	 * @return int
	 */
	public function getWidth(): int {
		return $this->width;
	}

	/**
	 * Get the height of the chart (before device pixel ratio is applied).
	 *
	 * @return int
	 */
	public function getHeight(): int {
		return $this->height;
	}

	/**
	 * Set the device pixel ratio.
	 *
	 * @param float $devicePixelRatio The device pixel ratio. Use 2.0 for retina displays. Chart sizes will be multiplied by this value.
	 *
	 * @return void
	 */
	public function setDevicePixelRatio( float $devicePixelRatio ): void {
		$this->devicePixelRatio = $devicePixelRatio;
	}

	/**
	 * Set the image format of the chart.
	 *
	 * @param string $format The format of the chart. Options: 'png', 'webp', 'svg', 'pdf'.
	 *
	 * @return void
	 */
	public function setFormat( string $format ): void {

		if ( ! in_array( $format, self::VALID_FORMATS, true ) ) {
			return;
		}

		$this->format = $format;
	}

	/**
	 * Set the encoding of the chart.

	 * @param string $encoding The encoding of the chart. Defaults to 'url'. Options: 'url' and 'base64'.
	 *
	 * @return void
	 */
	public function setEncoding( string $encoding ): void {

		if ( ! in_array( $encoding, self::ENCODING_OPTIONS, true ) ) {
			return;
		}

		$this->encoding = $encoding;
	}

	/**
	 * Set the background color of the chart canvas.
	 *
	 * @param string $backgroundColor The background color of the chart canvas. Accepts rgb (rgb(255,255,120)), colors (red), and hex values (#ff00ff). Will be url-encoded.
	 *
	 * @return void
	 */
	public function setBackgroundColor( string $backgroundColor ): void {
		$this->backgroundColor = rawurlencode( $backgroundColor );
	}


	/**
	 * Get the chart configuration as a string.
	 *
	 * @return string If encoding is base64, a base64 string. Default: JSON-encoded string.
	 */
	public function getConfigStr(): string {

		$config = $this->config;

		if ( ! is_string( $config ) ) {
			$config = wp_json_encode( $config );
		}

		if ( 'base64' === $this->encoding ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			return base64_encode( $config );
		}

		return rawurlencode( $config );
	}

	/**
	 * Get the URL for the chart.
	 *
	 * @return string The URL for the chart.
	 */
	public function getUrl() {

		$configStr        = $this->getConfigStr();
		$devicePixelRatio = number_format( $this->devicePixelRatio, 1 );

		$args = [
			'c'                => $configStr,
			'w'                => $this->width,
			'h'                => $this->height,
			'v'                => $this->version,
			'devicePixelRatio' => $devicePixelRatio,
			'format'           => $this->format,
			'bkg'              => $this->backgroundColor,
			'apiKey'           => $this->apiKey,
			'encoding'         => $this->encoding,
		];

		// Remove any options that are empty strings or null.
		$args = array_filter( $args, [ $this, 'filterEmptyValues' ] );

		$url = $this->getRootEndpoint() . '/chart';

		return add_query_arg( $args, $url );
	}

	/**
	 * Get the root endpoint for the chart.
	 *
	 * @return string The root endpoint for the chart, including the protocol, host, and port.
	 */
	protected function getRootEndpoint() {
		return $this->protocol . '://' . $this->host . ':' . $this->port;
	}

	/**
	 * Filter out empty values.
	 *
	 * @param mixed $value The value to check.
	 *
	 * @return bool True if the value is not an empty string or null.
	 */
	private function filterEmptyValues( $value ) {
		return '' !== $value && null !== $value;
	}
}
