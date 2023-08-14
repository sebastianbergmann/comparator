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

#[CoversClass(ArrayComparator::class)]
#[UsesClass(Comparator::class)]
#[UsesClass(ComparisonFailure::class)]
#[UsesClass(Factory::class)]
final class ArrayComparatorTest extends TestCase
{
    private ArrayComparator $comparator;

    public static function acceptsFailsProvider(): array
    {
        return [
            [[], null],
            [null, []],
            [null, null],
        ];
    }

    public static function assertEqualsSucceedsProvider(): array
    {
        return [
            [
                ['a' => 1, 'b' => 2],
                ['b' => 2, 'a' => 1],
            ],
            [
                [1],
                ['1'],
            ],
            [
                [3, 2, 1],
                [2, 3, 1],
                0,
                true,
            ],
            [
                [2.3],
                [2.5],
                0.5,
            ],
            [
                [[2.3]],
                [[2.5]],
                0.5,
            ],
            [
                [new Struct(2.3)],
                [new Struct(2.5)],
                0.5,
            ],
            [
                ['true'],
                [true],
            ],
        ];
    }

    public static function assertEqualsFailsProvider(): array
    {
        return [
            [
                [],
                [0 => 1],
            ],
            [
                [0 => 1],
                [],
            ],
            [
                [0 => null],
                [],
            ],
            [
                [0 => 1, 1 => 2],
                [0 => 1, 1 => 3],
            ],
            [
                ['a', 'b' => [1, 2]],
                ['a', 'b' => [2, 1]],
            ],
            [
                [2.3],
                [4.2],
                0.5,
            ],
            [
                [[2.3]],
                [[4.2]],
                0.5,
            ],
            [
                [new Struct(2.3)],
                [new Struct(4.2)],
                0.5,
            ],
            [
                ['false'],
                [false],
            ],
        ];
    }

    protected function setUp(): void
    {
        $this->comparator = new ArrayComparator;
        $this->comparator->setFactory(new Factory);
    }

    public function testAcceptsSucceeds(): void
    {
        $this->assertTrue(
            $this->comparator->accepts([], [])
        );
    }

    #[DataProvider('acceptsFailsProvider')]
    public function testAcceptsFails($expected, $actual): void
    {
        $this->assertFalse(
            $this->comparator->accepts($expected, $actual)
        );
    }

    #[DataProvider('assertEqualsSucceedsProvider')]
    public function testAssertEqualsSucceeds($expected, $actual, $delta = 0.0, $canonicalize = false): void
    {
        $exception = null;

        try {
            $this->comparator->assertEquals($expected, $actual, $delta, $canonicalize);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    #[DataProvider('assertEqualsFailsProvider')]
    public function testAssertEqualsFails($expected, $actual, $delta = 0.0, $canonicalize = false): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage('Failed asserting that two arrays are equal');

        $this->comparator->assertEquals($expected, $actual, $delta, $canonicalize);
    }
}
