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

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateTimeComparator::class)]
#[UsesClass(Comparator::class)]
#[UsesClass(ComparisonFailure::class)]
#[UsesClass(Factory::class)]
#[Small]
final class DateTimeComparatorTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: ?DateTimeImmutable, 1: ?DateTimeImmutable}>
     */
    public static function acceptsFailsProvider(): array
    {
        $datetime = new DateTimeImmutable;

        return [
            [$datetime, null],
            [null, $datetime],
            [null, null],
        ];
    }

    /**
     * @return non-empty-list<array{0: DateTimeImmutable, 1: DateTimeImmutable, 2?: float}>
     */
    public static function assertEqualsSucceedsProvider(): array
    {
        return [
            [
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 04:13:25', new DateTimeZone('America/New_York')),
                10,
            ],
            [
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 04:14:40', new DateTimeZone('America/New_York')),
                65,
            ],
            [
                new DateTimeImmutable('2013-03-29', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29', new DateTimeZone('America/New_York')),
            ],
            [
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 03:13:35', new DateTimeZone('America/Chicago')),
            ],
            [
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 03:13:49', new DateTimeZone('America/Chicago')),
                15,
            ],
            [
                new DateTimeImmutable('2013-03-30', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 23:00:00', new DateTimeZone('America/Chicago')),
            ],
            [
                new DateTimeImmutable('2013-03-30', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 23:01:30', new DateTimeZone('America/Chicago')),
                100,
            ],
            [
                new DateTimeImmutable('@1364616000'),
                new DateTimeImmutable('2013-03-29 23:00:00', new DateTimeZone('America/Chicago')),
            ],
            [
                new DateTimeImmutable('2013-03-29T05:13:35-0500'),
                new DateTimeImmutable('2013-03-29T04:13:35-0600'),
            ],
            [
                new DateTimeImmutable('2013-03-30', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 23:01:30', new DateTimeZone('America/Chicago')),
                100,
            ],
            [
                new DateTimeImmutable('2013-03-30 12:00:00', new DateTimeZone('UTC')),
                new DateTimeImmutable('2013-03-30 12:00:00.5', new DateTimeZone('UTC')),
                0.5,
            ],
        ];
    }

    /**
     * @return non-empty-list<array{0: DateTimeImmutable, 1: DateTimeImmutable, 2?: float}>
     */
    public static function assertEqualsFailsProvider(): array
    {
        return [
            [
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 03:13:35', new DateTimeZone('America/New_York')),
            ],
            [
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 03:13:35', new DateTimeZone('America/New_York')),
                3500,
            ],
            [
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 05:13:35', new DateTimeZone('America/New_York')),
                3500,
            ],
            [
                new DateTimeImmutable('2013-03-29', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-30', new DateTimeZone('America/New_York')),
            ],
            [
                new DateTimeImmutable('2013-03-29', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-30', new DateTimeZone('America/New_York')),
                43200,
            ],
            [
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/Chicago')),
            ],
            [
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/Chicago')),
                3500,
            ],
            [
                new DateTimeImmutable('2013-03-30', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-30', new DateTimeZone('America/Chicago')),
            ],
            [
                new DateTimeImmutable('2013-03-29T05:13:35-0600'),
                new DateTimeImmutable('2013-03-29T04:13:35-0600'),
            ],
            [
                new DateTimeImmutable('2013-03-29T05:13:35-0600'),
                new DateTimeImmutable('2013-03-29T05:13:35-0500'),
            ],
        ];
    }

    public function testAcceptsSucceeds(): void
    {
        $this->assertTrue(
            (new DateTimeComparator)->accepts(
                new DateTime,
                new DateTime,
            ),
        );
    }

    #[DataProvider('acceptsFailsProvider')]
    public function testAcceptsFails(?DateTimeImmutable $expected, ?DateTimeImmutable $actual): void
    {
        $this->assertFalse(
            (new DateTimeComparator)->accepts($expected, $actual),
        );
    }

    #[DataProvider('assertEqualsSucceedsProvider')]
    public function testAssertEqualsSucceeds(DateTimeImmutable $expected, DateTimeImmutable $actual, float $delta = 0.0): void
    {
        $exception = null;

        try {
            (new DateTimeComparator)->assertEquals($expected, $actual, $delta);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    #[DataProvider('assertEqualsFailsProvider')]
    public function testAssertEqualsFails(DateTimeImmutable $expected, DateTimeImmutable $actual, float $delta = 0.0): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage('Failed asserting that two DateTime objects are equal.');

        (new DateTimeComparator)->assertEquals($expected, $actual, $delta);
    }

    public function testAcceptsDateTimeInterface(): void
    {
        $this->assertTrue((new DateTimeComparator)->accepts(new DateTime, new DateTimeImmutable));
    }
}
