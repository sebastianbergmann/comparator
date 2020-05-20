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

use PHPUnit\Framework\TestCase;

/**
 * @covers \SebastianBergmann\Comparator\StringComparator<extended>
 *
 * @uses \SebastianBergmann\Comparator\Comparator
 * @uses \SebastianBergmann\Comparator\Factory
 * @uses \SebastianBergmann\Comparator\ComparisonFailure
 */
final class StringComparatorTest extends TestCase
{
    /**
     * @var ScalarComparator
     */
    private $comparator;

    protected function setUp(): void
    {
        $this->comparator = new StringComparator;
    }

    public function acceptsSucceedsProvider()
    {
        return [
            ['string', 'string'],
            ['', 'String'],
        ];
    }

    public function acceptsFailsProvider()
    {
        return [
            [[], []],
            ['string', []],
            ['10', 10],
            ['', false],
            ['1', true],
        ];
    }

    public function assertEqualsSucceedsProvider()
    {
        return [
            ['string', 'string'],
            ['string', 'STRING', true],
            ['STRING', 'string', true],
            ['Camión', 'camión', true],
            ["\xC3\x85", "\xCC\x8A", false, true],
        ];
    }

    public function assertEqualsFailsProvider()
    {
        $stringException = 'Failed asserting that two strings are equal.';

        return [
            ['string', 'other string', $stringException],
            ['string', 'STRING', $stringException],
            ['STRING', 'string', $stringException],
            ['string', 'other string', $stringException],
            // https://github.com/sebastianbergmann/phpunit/issues/1023
            ['9E6666666', '9E7777777', $stringException],
            ['0', '0.0', $stringException],
            ['0.', '0.0', $stringException],
            ['0e1', '0e2', $stringException],
        ];
    }

    /**
     * @dataProvider acceptsSucceedsProvider
     */
    public function testAcceptsSucceeds($expected, $actual): void
    {
        $this->assertTrue(
            $this->comparator->accepts($expected, $actual)
        );
    }

    /**
     * @dataProvider acceptsFailsProvider
     */
    public function testAcceptsFails($expected, $actual): void
    {
        $this->assertFalse(
            $this->comparator->accepts($expected, $actual)
        );
    }

    /**
     * @dataProvider assertEqualsSucceedsProvider
     */
    public function testAssertEqualsSucceeds($expected, $actual, $ignoreCase = false, $canonicalize = false): void
    {
        if ($canonicalize) {
            $this->markTestSkipped('Canonicalize not implemented yet');
        }
        $exception = null;

        try {
            $this->comparator->assertEquals($expected, $actual, 0.0, $canonicalize, $ignoreCase);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    /**
     * @dataProvider assertEqualsFailsProvider
     */
    public function testAssertEqualsFails($expected, $actual, $message): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage($message);

        $this->comparator->assertEquals($expected, $actual);
    }

    public function testCanonicalizeString(): void
    {
        $this->markTestSkipped('Canonicalize not implemented yet');
    }
}
