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

use const INF;
use function acos;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NumericComparator::class)]
#[UsesClass(Comparator::class)]
#[UsesClass(ComparisonFailure::class)]
#[UsesClass(Factory::class)]
final class NumericComparatorTest extends TestCase
{
    private NumericComparator $comparator;

    public static function acceptsSucceedsProvider(): array
    {
        return [
            [5, 10],
            [8, '0'],
            ['10', 0],
            [0x74C3B00C, 42],
            [0o755, 0o777],
            [8, 5.0],
            [5.0, 8],
            [5, 5],
            ['4.5', 5],
            [0x539, 0o2471],
            [0, 5.0],
            [5.0, 0],
            ['5', 4.5],
            [1.2e3, 7E-10],
            [3, acos(8)],
            [acos(8), 3],
            [acos(8), acos(8)],
        ];
    }

    public static function acceptsFailsProvider(): array
    {
        return [
            ['5', '10'],
            [10, null],
            [false, 12],
            [5.0, false],
            [null, 5.0],
        ];
    }

    public static function assertEqualsSucceedsProvider(): array
    {
        return [
            [1337, 1337],
            ['1337', 1337],
            [0x539, 1337],
            [0o2471, 1337],
            [1337, 1338, 1],
            ['1337', 1340, 5],
            [INF, INF],
            [2.3, 2.3],
            ['2.3', 2.3],
            [5.0, 5],
            [5, 5.0],
            [5.0, '5'],
            [1.2e3, 1200],
            [2.3, 2.5, 0.5],
            [3, 3.05, 0.05],
            [1.2e3, 1201, 1],
            [1 / 3, '0.3333333333333333'],
            [1 - 2 / 3, '0.33333333333333337'],
            [1 / 3, 1 - 2 / 3, 0.0000000001],
            [5.5E+123, '5.5E+123'],
            [5.5E-123, '5.5E-123'],
            [5.5E+123, '5.6E+123', 0.2E+123],
            [5.5E-123, '5.6E-123', 0.2E-123],
        ];
    }

    public static function assertEqualsFailsProvider(): array
    {
        return [
            [1337, 1338],
            ['1338', 1337],
            [0x539, 1338],
            [1337, 1339, 1],
            ['1337', 1340, 2],
            [2.3, 4.2],
            ['2.3', 4.2],
            [5.0, '4'],
            [5.0, 6],
            [1.2e3, 1201],
            [2.3, 2.5, 0.2],
            [3, 3.05, 0.04],
            [3, acos(8)],
            [acos(8), 3],
            [acos(8), acos(8)],
            [1 / 3, 1 - 2 / 3],
            [5.5E+123, '5.7E+123'],
            [5.5E-123, '5.7E-123'],
            [5.5E+123, '5.7E+123', 0.1E+123],
            [5.5E-123, '5.7E-123', 0.1E-123],
        ];
    }

    #[DataProvider('acceptsSucceedsProvider')]
    public function testAcceptsSucceeds($expected, $actual): void
    {
        $this->assertTrue(
            (new NumericComparator)->accepts($expected, $actual),
        );
    }

    #[DataProvider('acceptsFailsProvider')]
    public function testAcceptsFails($expected, $actual): void
    {
        $this->assertFalse(
            (new NumericComparator)->accepts($expected, $actual),
        );
    }

    #[DataProvider('assertEqualsSucceedsProvider')]
    public function testAssertEqualsSucceeds($expected, $actual, $delta = 0.0): void
    {
        $exception = null;

        try {
            (new NumericComparator)->assertEquals($expected, $actual, $delta);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    #[DataProvider('assertEqualsFailsProvider')]
    public function testAssertEqualsFails($expected, $actual, $delta = 0.0): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage('matches expected');

        (new NumericComparator)->assertEquals($expected, $actual, $delta);
    }
}
