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

use BcMath\Number;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[RequiresPhp('^8.4')]
#[RequiresPhpExtension('bcmath')]
#[CoversClass(NumberComparator::class)]
#[UsesClass(Comparator::class)]
#[UsesClass(ComparisonFailure::class)]
#[UsesClass(Factory::class)]
final class NumberComparatorTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function acceptsSucceedsProvider(): array
    {
        $number        = new Number('13.37');
        $anotherNumber = new Number('13');

        return [
            [$number, $anotherNumber],
            [$number, '13'],
            [$number, 13],
            [$anotherNumber, $number],
            ['13', $number],
            [13, $number],
        ];
    }

    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function acceptsFailsProvider(): array
    {
        $number = new Number('13.37');

        return [
            [$number, null],
            [$number, 'foo'],
            [null, $number],
            ['foo', $number],
            [null, null],
        ];
    }

    /**
     * @return non-empty-list<array{0: int|Number|numeric-string, 1: int|Number|numeric-string, 2?: float}>
     */
    public static function assertEqualsSucceedsProvider(): array
    {
        return [
            [new Number('13.37'), new Number('13.37')],
            [new Number('13.37'), new Number('13.370000')],
            ['13.37', new Number('13.370000')],
            ['13.37', new Number('13.37')],
            ['13.370000', new Number('13.37')],
            [13, new Number('13')],
            [new Number('13.37'), new Number('13.38'), .1],
            ['13.37', new Number('13.38'), .1],
            [13, new Number('13.38'), 1],
            [new Number('47.11'), new Number('47.11'), .00001],
        ];
    }

    /**
     * @return non-empty-list<array{0: int|Number|numeric-string, 1: int|Number|numeric-string, 2?: float}>
     */
    public static function assertEqualsFailsProvider(): array
    {
        return [
            [new Number('13'), new Number('13.37')],
            ['13', new Number('13.37')],
            [13, new Number('13.37')],
            [new Number('13'), new Number('13.37'), .1],
            ['13', new Number('13.37'), .1],
            [13, new Number('13.37'), .1],
            [new Number('47.11'), new Number('47.12'), .00001],
        ];
    }

    public function testAcceptsSucceeds(): void
    {
        $this->assertTrue(
            (new NumberComparator)->accepts(
                new Number('1'),
                new Number('2'),
            ),
        );
    }

    #[DataProvider('acceptsFailsProvider')]
    public function testAcceptsFails(mixed $expected, mixed $actual): void
    {
        $this->assertFalse(
            (new NumberComparator)->accepts($expected, $actual),
        );
    }

    #[DataProvider('assertEqualsSucceedsProvider')]
    public function testAssertEqualsSucceeds(int|Number|string $expected, int|Number|string $actual, float $delta = 0.0): void
    {
        $exception = null;

        try {
            (new NumberComparator)->assertEquals($expected, $actual, $delta);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    #[DataProvider('assertEqualsSucceedsProvider')]
    public function testAssertEqualsCanBeInverted(int|Number|string $actual, int|Number|string $expected, float $delta = 0.0): void
    {
        $exception = null;

        try {
            (new NumberComparator)->assertEquals($expected, $actual, $delta);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    #[DataProvider('assertEqualsFailsProvider')]
    public function testAssertEqualsFails(int|Number|string $expected, int|Number|string $actual, float $delta = 0.0): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage('Failed asserting that two Number objects are equal.');

        (new NumberComparator)->assertEquals($expected, $actual, $delta);
    }
}
