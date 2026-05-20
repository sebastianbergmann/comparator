<?php declare(strict_types=1);
/*
 * This file is part of sebastian/comparator.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\Comparator;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesClassesThatExtendClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateIntervalComparator::class)]
#[UsesClassesThatExtendClass(Comparator::class)]
#[UsesClass(ComparisonFailure::class)]
#[UsesClass(Factory::class)]
#[Small]
final class DateIntervalComparatorTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: ?DateInterval, 1: ?DateInterval}>
     */
    public static function acceptsFailsProvider(): array
    {
        $interval = new DateInterval('PT1S');

        return [
            [$interval, null],
            [null, $interval],
            [null, null],
        ];
    }

    /**
     * @return non-empty-list<array{0: DateInterval, 1: DateInterval, 2?: float}>
     */
    public static function assertEqualsSucceedsProvider(): array
    {
        $invertedHour         = new DateInterval('PT1H');
        $invertedHour->invert = 1;

        $withMicroseconds    = new DateInterval('PT1S');
        $withMicroseconds->f = 0.25;

        return [
            // identical components
            [new DateInterval('PT1S'), new DateInterval('PT1S')],
            // equivalent normalized components
            [new DateInterval('PT1H'), new DateInterval('PT3600S')],
            [new DateInterval('P1D'), new DateInterval('PT24H')],
            // delta tolerates difference
            [new DateInterval('PT10S'), new DateInterval('PT12S'), 5.0],
            // microseconds within delta
            [new DateInterval('PT1S'), $withMicroseconds, 0.5],
            // intervals computed from diff()
            [
                (new DateTimeImmutable('2026-05-20 12:00:00', new DateTimeZone('UTC')))
                    ->diff(new DateTimeImmutable('2026-05-20 12:00:10', new DateTimeZone('UTC'))),
                new DateInterval('PT10S'),
            ],
            // inverted interval matches its mirror
            [
                $invertedHour,
                (clone $invertedHour),
            ],
        ];
    }

    /**
     * @return non-empty-list<array{0: DateInterval, 1: DateInterval, 2?: float}>
     */
    public static function assertEqualsFailsProvider(): array
    {
        $invertedSecond         = new DateInterval('PT1S');
        $invertedSecond->invert = 1;

        return [
            // outside delta
            [new DateInterval('PT1S'), new DateInterval('PT2S')],
            [new DateInterval('PT10S'), new DateInterval('PT20S'), 5.0],
            // sign is honored: -1s vs +1s differ by 2s
            [$invertedSecond, new DateInterval('PT1S'), 1.0],
        ];
    }

    public function testAcceptsSucceeds(): void
    {
        $this->assertTrue(
            (new DateIntervalComparator)->accepts(
                new DateInterval('PT1S'),
                new DateInterval('PT1S'),
            ),
        );
    }

    #[DataProvider('acceptsFailsProvider')]
    public function testAcceptsFails(?DateInterval $expected, ?DateInterval $actual): void
    {
        $this->assertFalse(
            (new DateIntervalComparator)->accepts($expected, $actual),
        );
    }

    #[DataProvider('assertEqualsSucceedsProvider')]
    public function testAssertEqualsSucceeds(DateInterval $expected, DateInterval $actual, float $delta = 0.0): void
    {
        $exception = null;

        try {
            (new DateIntervalComparator)->assertEquals($expected, $actual, $delta);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    #[DataProvider('assertEqualsFailsProvider')]
    public function testAssertEqualsFails(DateInterval $expected, DateInterval $actual, float $delta = 0.0): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage('Failed asserting that two DateInterval objects are equal.');

        (new DateIntervalComparator)->assertEquals($expected, $actual, $delta);
    }

    public function testAssertEqualsSkipsAlreadyProcessedPair(): void
    {
        $expected = new DateInterval('PT1S');
        $actual   = new DateInterval('PT2S');

        $processed = [[$actual, $expected]];

        $exception = null;

        try {
            (new DateIntervalComparator)->assertEquals($expected, $actual, 0.0, false, false, $processed);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull(
            $exception,
            'DateIntervalComparator must not report a failure for a pair already present in $processed',
        );
    }

    public function testAssertEqualsSkipsAlreadyProcessedPairInReverseOrder(): void
    {
        $expected = new DateInterval('PT1S');
        $actual   = new DateInterval('PT2S');

        $processed = [[$expected, $actual]];

        $exception = null;

        try {
            (new DateIntervalComparator)->assertEquals($expected, $actual, 0.0, false, false, $processed);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull(
            $exception,
            'DateIntervalComparator must recognize a pair regardless of order in $processed',
        );
    }

    public function testAssertEqualsRecordsProcessedPair(): void
    {
        $expected = new DateInterval('PT1S');
        $actual   = new DateInterval('PT2S');

        $processed = [];

        try {
            (new DateIntervalComparator)->assertEquals($expected, $actual, 0.0, false, false, $processed);
            $this->fail('Expected ComparisonFailure was not thrown');
        } catch (ComparisonFailure) {
        }

        $this->assertSame([[$actual, $expected]], $processed);
    }
}
