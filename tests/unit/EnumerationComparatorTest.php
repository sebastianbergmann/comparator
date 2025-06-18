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
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EnumerationComparator::class)]
#[UsesClass(ComparisonFailure::class)]
#[UsesClass(Factory::class)]
#[Small]
final class EnumerationComparatorTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function acceptsSucceedsProvider(): array
    {
        return [
            [Example::Foo, Example::Foo],
            [Example::Foo, Example::Bar],
            [Example::Bar, Example::Foo],
            [Example::Bar, Example::Bar],
            [ExampleString::Foo, ExampleString::Foo],
            [ExampleString::Foo, ExampleString::Bar],
            [ExampleString::Bar, ExampleString::Foo],
            [ExampleString::Bar, ExampleString::Bar],
            [ExampleInt::Foo, ExampleInt::Foo],
            [ExampleInt::Foo, ExampleInt::Bar],
            [ExampleInt::Bar, ExampleInt::Foo],
            [ExampleInt::Bar, ExampleInt::Bar],
        ];
    }

    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function acceptsFailsProvider(): array
    {
        return [
            [Example::Foo, ExampleString::Foo],
            [ExampleString::Foo, Example::Foo],
        ];
    }

    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function assertEqualsSucceedsProvider(): array
    {
        return [
            [Example::Foo, Example::Foo],
            [ExampleString::Foo, ExampleString::Foo],
            [ExampleInt::Foo, ExampleInt::Foo],
        ];
    }

    /**
     * @return non-empty-list<array{0: mixed, 1: mixed, 2?: non-empty-string}>
     */
    public static function assertEqualsFailsProvider(): array
    {
        return [
            [Example::Foo, Example::Bar, 'Failed asserting that two values of enumeration SebastianBergmann\Comparator\Example are equal, Bar does not match expected Foo.'],
            [ExampleString::Foo, ExampleString::Bar, 'Failed asserting that two values of enumeration SebastianBergmann\Comparator\ExampleString are equal, Bar does not match expected Foo.'],
            [ExampleInt::Foo, ExampleInt::Bar, 'Failed asserting that two values of enumeration SebastianBergmann\Comparator\ExampleInt are equal, Bar does not match expected Foo.'],
        ];
    }

    #[DataProvider('acceptsSucceedsProvider')]
    public function testAcceptsSucceeds(mixed $expected, mixed $actual): void
    {
        $this->assertTrue(
            (new EnumerationComparator)->accepts($expected, $actual),
        );
    }

    #[DataProvider('acceptsFailsProvider')]
    public function testAcceptsFails(mixed $expected, mixed $actual): void
    {
        $this->assertFalse(
            (new EnumerationComparator)->accepts($expected, $actual),
        );
    }

    #[DataProvider('assertEqualsSucceedsProvider')]
    public function testAssertEqualsSucceeds(mixed $expected, mixed $actual): void
    {
        $exception = null;

        try {
            (new EnumerationComparator)->assertEquals($expected, $actual);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    #[DataProvider('assertEqualsFailsProvider')]
    public function testAssertEqualsFails(mixed $expected, mixed $actual, string $message): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage($message);

        (new EnumerationComparator)->assertEquals($expected, $actual);
    }
}
