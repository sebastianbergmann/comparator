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

    #[DataProvider('acceptsSucceedsProvider')]
    public function testAcceptsSucceeds($expected, $actual): void
    {
        $this->assertTrue(
            (new ScalarComparator)->accepts($expected, $actual),
        );
    }

    #[DataProvider('acceptsFailsProvider')]
    public function testAcceptsFails($expected, $actual): void
    {
        $this->assertFalse(
            (new ScalarComparator)->accepts($expected, $actual),
        );
    }

    #[DataProvider('assertEqualsSucceedsProvider')]
    public function testAssertEqualsSucceeds($expected, $actual, $ignoreCase = false): void
    {
        $exception = null;

        try {
            (new ScalarComparator)->assertEquals($expected, $actual, 0.0, false, $ignoreCase);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    #[DataProvider('assertEqualsFailsProvider')]
    public function testAssertEqualsFails($expected, $actual, $message): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage($message);

        (new ScalarComparator)->assertEquals($expected, $actual);
    }
}
