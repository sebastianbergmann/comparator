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

use function tmpfile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ScalarComparator::class)]
#[UsesClass(Comparator::class)]
#[UsesClass(ComparisonFailure::class)]
#[UsesClass(Factory::class)]
final class ScalarComparatorTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function acceptsSucceedsProvider(): array
    {
        return [
            ['string', 'string'],
            [new ClassWithToString, 'string'],
            ['string', new ClassWithToString],
            ['string', null],
            [false, 'string'],
            [false, true],
            [null, false],
            [null, null],
            ['10', 10],
            ['', false],
            ['1', true],
            [1, true],
            [0, false],
            ['0', false],
            [0.1, '0.1'],
        ];
    }

    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function acceptsFailsProvider(): array
    {
        return [
            [[], []],
            ['string', []],
            [new ClassWithToString, new ClassWithToString],
            [false, new ClassWithToString],
            [tmpfile(), tmpfile()],
        ];
    }

    /**
     * @return non-empty-list<array{0: mixed, 1: mixed, 2?: bool}>
     */
    public static function assertEqualsSucceedsProvider(): array
    {
        return [
            ['string', 'string'],
            [new ClassWithToString, new ClassWithToString],
            ['string representation', new ClassWithToString],
            [new ClassWithToString, 'string representation'],
            ['string', 'STRING', true],
            ['STRING', 'string', true],
            ['String Representation', new ClassWithToString, true],
            [new ClassWithToString, 'String Representation', true],
            ['10', 10],
            ['', false],
            ['1', true],
            ['true', true],
            [1, true],
            [0, false],
            ['0', false],
            [0.1, '0.1'],
            [false, null],
            [false, false],
            [true, true],
            [null, null],
        ];
    }

    /**
     * @return non-empty-list<array{0: mixed, 1: mixed, 2?: non-empty-string}>
     */
    public static function assertEqualsFailsProvider(): array
    {
        $stringException = 'Failed asserting that two strings are equal.';
        $otherException  = 'matches expected';

        return [
            ['string', 'other string', $stringException],
            ['string', 'STRING', $stringException],
            ['STRING', 'string', $stringException],
            ['string', 'other string', $stringException],
            // https://github.com/sebastianbergmann/phpunit/issues/1023
            ['9E6666666', '9E7777777', $stringException],
            [new ClassWithToString, 'does not match', $otherException],
            ['does not match', new ClassWithToString, $otherException],
            [0, 'Foobar', $otherException],
            ['Foobar', 0, $otherException],
            ['10', 25, $otherException],
            ['1', false, $otherException],
            ['false', false, $otherException],
            ['', true, $otherException],
            [false, true, $otherException],
            [true, false, $otherException],
            [null, true, $otherException],
            [0, true, $otherException],
            ['0', '0.0', $stringException],
            ['0.', '0.0', $stringException],
            ['0e1', '0e2', $stringException],
            ["\n\n\n0.0", '                   0.', $stringException],
            ['0.0', '25e-10000', $stringException],
        ];
    }

    /**
     * @return non-empty-list<array{0: string, 1: string, 2: string}>
     */
    public static function assertEqualsFailsWithDiffProvider(): array
    {
        return [
            [
                "
--- Expected
+++ Actual
@@ @@
-'string'
+'other string'
",
                'string',
                'other string',
            ],
            [
                "
--- Expected
+++ Actual
@@ @@
-'...ch will be cut HERE some trailer'
+'...ch will be cut XYZ some trailer'
",
                'too too too long string which will be cut HERE some trailer',
                'too too too long string which will be cut XYZ some trailer',
            ],
            [
                "
--- Expected
+++ Actual
@@ @@
-'short start until HERE some llooooooo...'
+'short start until XYZ some llooooooo...'
",
                'short start until HERE some llooooooooonnng llooooooooonnng llooooooooonnng llooooooooonnng trailer',
                'short start until XYZ some llooooooooonnng llooooooooonnng llooooooooonnng llooooooooonnng trailer',
            ],
            [
                "
--- Expected
+++ Actual
@@ @@
-'...ch will be cut HERE some llooooooo...'
+'...ch will be cut XYZ some llooooooo...'
",
                'too too too long string which will be cut HERE some llooooooooonnng llooooooooonnng llooooooooonnng llooooooooonnng trailer',
                'too too too long string which will be cut XYZ some llooooooooonnng llooooooooonnng llooooooooonnng llooooooooonnng trailer',
            ],
        ];
    }

    #[DataProvider('acceptsSucceedsProvider')]
    public function testAcceptsSucceeds(mixed $expected, mixed $actual): void
    {
        $this->assertTrue(
            (new ScalarComparator)->accepts($expected, $actual),
        );
    }

    #[DataProvider('acceptsFailsProvider')]
    public function testAcceptsFails(mixed $expected, mixed $actual): void
    {
        $this->assertFalse(
            (new ScalarComparator)->accepts($expected, $actual),
        );
    }

    #[DataProvider('assertEqualsSucceedsProvider')]
    public function testAssertEqualsSucceeds(mixed $expected, mixed $actual, bool $ignoreCase = false): void
    {
        $exception = null;

        try {
            (new ScalarComparator)->assertEquals($expected, $actual, 0.0, false, $ignoreCase);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    #[DataProvider('assertEqualsFailsProvider')]
    public function testAssertEqualsFails(mixed $expected, mixed $actual, string $message): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage($message);

        (new ScalarComparator)->assertEquals($expected, $actual);
    }

    #[DataProvider('assertEqualsFailsWithDiffProvider')]
    public function testAssertEqualsFailsWithDiff(string $expectedDiff, string $expected, string $actual): void
    {
        try {
            (new ScalarComparator)->assertEquals($expected, $actual);
            $this->fail('Expected ComparisonFailure not thrown');
        } catch (ComparisonFailure $e) {
            $this->assertEquals('Failed asserting that two strings are equal.', $e->getMessage());
            $this->assertEquals($expectedDiff, $e->getDiff());
        }
    }
}
