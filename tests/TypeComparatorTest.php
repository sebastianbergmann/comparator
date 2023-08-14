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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(TypeComparator::class)]
#[UsesClass(Comparator::class)]
#[UsesClass(ComparisonFailure::class)]
#[UsesClass(Factory::class)]
final class TypeComparatorTest extends TestCase
{
    public static function acceptsSucceedsProvider(): array
    {
        return [
            [true, 1],
            [false, [1]],
            [null, new stdClass],
            [1.0, 5],
            ['', ''],
        ];
    }

    public static function assertEqualsSucceedsProvider(): array
    {
        return [
            [true, true],
            [true, false],
            [false, false],
            [null, null],
            [new stdClass, new stdClass],
            [0, 0],
            [1.0, 2.0],
            ['hello', 'world'],
            ['', ''],
            [[], [1, 2, 3]],
        ];
    }

    public static function assertEqualsFailsProvider(): array
    {
        return [
            [true, null],
            [null, false],
            [1.0, 0],
            [new stdClass, []],
            ['1', 1],
        ];
    }

    #[DataProvider('acceptsSucceedsProvider')]
    public function testAcceptsSucceeds($expected, $actual): void
    {
        $this->assertTrue(
            (new TypeComparator)->accepts($expected, $actual)
        );
    }

    #[DataProvider('assertEqualsSucceedsProvider')]
    public function testAssertEqualsSucceeds($expected, $actual): void
    {
        $exception = null;

        try {
            (new TypeComparator)->assertEquals($expected, $actual);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    #[DataProvider('assertEqualsFailsProvider')]
    public function testAssertEqualsFails($expected, $actual): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage('does not match expected type');

        (new TypeComparator)->assertEquals($expected, $actual);
    }
}
