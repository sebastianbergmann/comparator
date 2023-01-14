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
use PHPUnit\Framework\TestCase;

/**
 * @covers \SebastianBergmann\Comparator\ResourceComparator
 *
 * @uses \SebastianBergmann\Comparator\Comparator
 * @uses \SebastianBergmann\Comparator\ComparisonFailure
 * @uses \SebastianBergmann\Comparator\Factory
 */
final class ResourceComparatorTest extends TestCase
{
    /**
     * @var ResourceComparator
     */
    private $comparator;

    public static function acceptsSucceedsProvider()
    {
        $tmpfile1 = tmpfile();
        $tmpfile2 = tmpfile();

        return [
            [$tmpfile1, $tmpfile1],
            [$tmpfile2, $tmpfile2],
            [$tmpfile1, $tmpfile2],
        ];
    }

    public static function acceptsFailsProvider()
    {
        $tmpfile1 = tmpfile();

        return [
            [$tmpfile1, null],
            [null, $tmpfile1],
            [null, null],
        ];
    }

    public static function assertEqualsSucceedsProvider()
    {
        $tmpfile1 = tmpfile();
        $tmpfile2 = tmpfile();

        return [
            [$tmpfile1, $tmpfile1],
            [$tmpfile2, $tmpfile2],
        ];
    }

    public static function assertEqualsFailsProvider()
    {
        $tmpfile1 = tmpfile();
        $tmpfile2 = tmpfile();

        return [
            [$tmpfile1, $tmpfile2],
            [$tmpfile2, $tmpfile1],
        ];
    }

    protected function setUp(): void
    {
        $this->comparator = new ResourceComparator;
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
    public function testAssertEqualsSucceeds($expected, $actual): void
    {
        $exception = null;

        try {
            $this->comparator->assertEquals($expected, $actual);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    /**
     * @dataProvider assertEqualsFailsProvider
     */
    public function testAssertEqualsFails($expected, $actual): void
    {
        $this->expectException(ComparisonFailure::class);

        $this->comparator->assertEquals($expected, $actual);
    }
}
