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

use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClosureComparator::class)]
#[UsesClass(ComparisonFailure::class)]
#[UsesClass(Factory::class)]
#[Small]
final class ClosureComparatorTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function acceptsSucceedsProvider(): array
    {
        $f = static function (): void
        {
        };

        $g = static function (): void
        {
        };

        return [
            [$f, $f],
            [$f, $g],
        ];
    }

    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function acceptsFailsProvider(): array
    {
        $f = static function (): void
        {
        };

        return [
            [$f, null],
            [null, $f],
        ];
    }

    /**
     * @return non-empty-list<array{0: Closure, 1: Closure}>
     */
    public static function assertEqualsSucceedsProvider(): array
    {
        $f = static function (): void
        {
        };

        return [
            [$f, $f],
        ];
    }

    /**
     * @return non-empty-list<array{0: Closure, 1: Closure}>
     */
    public static function assertEqualsFailsProvider(): array
    {
        $f = static function (): void
        {
        };

        $g = static function (): void
        {
        };

        return [
            [$f, $g],
        ];
    }

    #[DataProvider('acceptsSucceedsProvider')]
    public function testAcceptsSucceeds(mixed $expected, mixed $actual): void
    {
        $this->assertTrue(
            (new ClosureComparator)->accepts($expected, $actual),
        );
    }

    #[DataProvider('acceptsFailsProvider')]
    public function testAcceptsFails(mixed $expected, mixed $actual): void
    {
        $this->assertFalse(
            (new ClosureComparator)->accepts($expected, $actual),
        );
    }

    #[DataProvider('assertEqualsSucceedsProvider')]
    public function testAssertEqualsSucceeds(mixed $expected, mixed $actual): void
    {
        $exception = null;

        try {
            (new ClosureComparator)->assertEquals($expected, $actual);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    #[DataProvider('assertEqualsFailsProvider')]
    public function testAssertEqualsFails(mixed $expected, mixed $actual): void
    {
        try {
            (new ClosureComparator)->assertEquals($expected, $actual);
        } catch (ComparisonFailure $e) {
            $this->assertStringMatchesFormat(
                'Failed asserting that closure declared at %sClosureComparatorTest.php:%d is equal to closure declared at %sClosureComparatorTest.php:%d.',
                $e->getMessage(),
            );

            return;
        }

        $this->fail('Expected ComparisonFailure to be thrown');
    }

    public function testAsStringComparisonFormat(): void
    {
        $f = static function (): void
        {
        };

        $g = static function (): void
        {
        };

        try {
            (new ClosureComparator)->assertEquals($f, $g);
        } catch (ComparisonFailure $e) {
            $this->assertStringMatchesFormat(
                'Closure Object #%d ()',
                $e->getActualAsString(),
            );
            $this->assertStringMatchesFormat(
                'Closure Object #%d ()',
                $e->getExpectedAsString(),
            );

            return;
        }

        $this->fail('Expected ComparisonFailure to be thrown');
    }
}
