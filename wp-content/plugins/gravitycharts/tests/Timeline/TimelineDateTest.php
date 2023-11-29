<?php
/**
 * Tests for timeline date object.
 *
 * @package GravityKit\GravityCharts
 */

namespace Timeline;

use GravityKit\GravityCharts\Timeline\TimelineDate;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for {@see TimelineDate}
 *
 * @since 1.6
 */
final class TimelineDateTest extends TestCase {

	/**
	 * Test cases for {@see TimelineDate::with_scale()} with an invalid scale.
	 *
	 * @since 1.6
	 */
	public function testScaleGuard(): void {
		$this->expectExceptionMessage( 'Provided scale "invalid" is invalid. Can only be one of: "day", "week", "month", "quarter", "year".' );

		( new TimelineDate( '2023-07-01' ) )->with_scale( 'invalid' );
	}

	/**
	 * Data provider for unit tests.
	 *
	 * @since 1.6
	 * @return array[]
	 */
	public function scalesDataProvider(): array {
		return [
			'day'       => [ '14-02-1988', 'day', '14-02-1988' ],
			'week'      => [ '13-07-2023', 'week', '10-07-2023' ],
			'month'     => [ '13-07-2023', 'month', '01-07-2023' ],
			'quarter 1' => [ '27-02-2023', 'quarter', '01-01-2023' ],
			'quarter 2' => [ '13-06-2023', 'quarter', '01-04-2023' ],
			'quarter 3' => [ '17-09-2023', 'quarter', '01-07-2023' ],
			'quarter 4' => [ '04-11-2023', 'quarter', '01-10-2023' ],
			'year'      => [ '13-07-2023', 'year', '01-01-2023' ],
		];
	}

	/**
	 * Test cases for {@see TimelineDate::with_scale()}.
	 *
	 * @param string $date          The Date.
	 * @param string $scale         The scale.
	 * @param string $expected_date The expected date.
	 *
	 * @dataProvider scalesDataProvider The data provider.
	 * @return void
	 * @throws \Exception If the date could not be created.
	 */
	public function testScale( string $date, string $scale, string $expected_date ): void {
		$date     = new TimelineDate( $date );
		$new_date = $date->with_scale( $scale );
		self::assertSame( $expected_date, $new_date->format( 'd-m-Y' ) );
		self::assertNotSame( $date, $new_date );
	}


	/**
	 * Data provider for unit tests.
	 *
	 * @since 1.6
	 * @return array[]
	 */
	public function formatDataProvider(): array {
		return [
			'normal'    => [ '14-02-1988', 'Y-m-d', '1988-02-14' ],
			'quarter 1' => [ '27-02-2023', 'Y \Qq', '2023 Q1' ],
			'quarter 2' => [ '13-06-2023', 'Y \Qq', '2023 Q2' ],
			'quarter 3' => [ '17-09-2023', 'Y \Qq', '2023 Q3' ],
			'quarter 4' => [ '04-11-2023', 'Y \Qq', '2023 Q4' ],
			'quarter q' => [ '04-11-2023', 'Y \Q\q', '2023 Qq' ],
		];
	}

	/**
	 * Test cases for {@see TimelineDate::format()}.
	 *
	 * @param string $date            The Date.
	 * @param string $format          The format.
	 * @param string $expected_result The expected result.
	 *
	 * @dataProvider formatDataProvider The data provider.
	 * @return void
	 * @throws \Exception If the date could not be created.
	 */
	public function testFormat( string $date, string $format, string $expected_result ): void {
		self::assertSame( $expected_result, ( new TimelineDate( $date ) )->format( $format ) );
	}
}
