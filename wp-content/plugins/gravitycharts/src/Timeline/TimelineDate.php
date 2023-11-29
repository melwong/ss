<?php
/**
 * Manipulate dates to match the timeline needs.
 *
 * @package GravityKit\GravityCharts
 */

namespace GravityKit\GravityCharts\Timeline;

/**
 * DateTime class that represents a Timeline date.
 *
 * @since 1.6
 */
final class TimelineDate {
	/**
	 * The timeline scales.
	 *
	 * @since 1.6
	 */
	public const SCALE_DAY     = 'day';
	public const SCALE_WEEK    = 'week';
	public const SCALE_MONTH   = 'month';
	public const SCALE_QUARTER = 'quarter';
	public const SCALE_YEAR    = 'year';

	/**
	 * The internal date..
	 *
	 * @since 1.6
	 * @var \DateTimeImmutable
	 */
	private $date;

	/**
	 * Creates the date object.
	 *
	 * @param string             $date      The date.
	 * @param \DateTimeZone|null $time_zone The time zone.
	 *
	 * @throws \Exception If the date could not be parsed.
	 */
	public function __construct( string $date, ?\DateTimeZone $time_zone = null ) {
		$this->date = new \DateTimeImmutable( $date, $time_zone );
	}

	/**
	 * Formats the date to the first of a provided scale (other than day).
	 *
	 * @since 1.6
	 *
	 * @param string $scale The scale.
	 *
	 * @return TimelineDate
	 */
	public function with_scale( string $scale ): self {
		$this->guard_against_invalid_scales( $scale );

		switch ( $scale ) {
			case self::SCALE_WEEK:
				return $this->first_day_of_week();
			case self::SCALE_MONTH:
				return $this->first_day_of_month();
			case self::SCALE_QUARTER:
				return $this->first_day_of_quarter();
			case self::SCALE_YEAR:
				return $this->first_day_of_year();
			case self::SCALE_DAY:
			default:
				return clone $this;
		}
	}

	/**
	 * Makes sure the provided scale is valid.
	 *
	 * @since 1.6
	 *
	 * @param string $scale The provided scale.
	 *
	 * @throws \InvalidArgumentException If an invalid scale was provided.
	 *
	 * @return void
	 */
	private function guard_against_invalid_scales( string $scale ): void {
		if ( ! in_array( $scale, self::scales(), true ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Provided scale "%s" is invalid. Can only be one of: "%s".',
					$scale,
					implode( '", "', self::scales() )
				)
			);
		}
	}

	/**
	 * Returns the first day of the week for the current date.
	 *
	 * @since 1.6
	 *
	 * @return TimelineDate
	 */
	private function first_day_of_week(): self {
		$clone       = clone $this;
		$clone->date = $this->date->setISODate(
			(int) $this->date->format( 'o' ),
			(int) $this->date->format( 'W' ),
			1
		);

		return $clone;
	}

	/**
	 * Returns the first day of the month for the current date.
	 *
	 * @since 1.6
	 *
	 * @return TimelineDate
	 */
	private function first_day_of_month(): self {
		$clone       = clone $this;
		$clone->date = $this->date->modify( 'first day of' );

		return $clone;
	}

	/**
	 * Returns the first day of the quarter for the current date.
	 *
	 * @since 1.6
	 *
	 * @return TimelineDate
	 */
	private function first_day_of_quarter(): self {
		$month       = ( ceil( $this->date->format( 'n' ) / 3 ) * 3 ) - 2;
		$clone       = clone $this;
		$clone->date = $this->date->setDate( $this->date->format( 'Y' ), $month, 1 );

		return $clone;
	}

	/**
	 * Returns the first day of the year for the current date.
	 *
	 * @since 1.6
	 *
	 * @return TimelineDate
	 */
	private function first_day_of_year(): self {
		$clone       = clone $this;
		$clone->date = $this->date->setDate( $this->date->format( 'Y' ), 1, 1 );

		return $clone;
	}

	/**
	 * Returns the current date in a specific format.
	 *
	 * @since 1.6
	 *
	 * @param string $format $the format to return.
	 *
	 * @return string
	 */
	public function format( string $format ): string {
		$quarter = ceil( $this->date->format( 'n' ) / 3 );
		$format  = preg_replace( '/(?<!\\\)q/', $quarter, $format );

		return date_i18n( $format, $this->date->getTimestamp() );
	}

	/**
	 * Returns the available scales.
	 *
	 * @since 1.6
	 * @return string[]
	 */
	private static function scales(): array {
		return [
			self::SCALE_DAY,
			self::SCALE_WEEK,
			self::SCALE_MONTH,
			self::SCALE_QUARTER,
			self::SCALE_YEAR,
		];
	}
}
