<?php
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
 * @covers \SebastianBergmann\Comparator\ResourceComparator<extended>
 *
 * @uses \SebastianBergmann\Comparator\Comparator
 * @uses \SebastianBergmann\Comparator\Factory
 * @uses \SebastianBergmann\Comparator\ComparisonFailure
 */
final class ResourceComparatorTest extends TestCase
{
    /**
     * @var ResourceComparator
     */
    private $comparator;

    protected function setUp(): void
    {
        $this->comparator = new ResourceComparator;
    }

    public function acceptsSucceedsProvider()
    {
        $tmpfile1 = \tmpfile();
        $tmpfile2 = \tmpfile();

        return [
            [$tmpfile1, $tmpfile1],
            [$tmpfile2, $tmpfile2],
            [$tmpfile1, $tmpfile2]
        ];
    }

    public function acceptsFailsProvider()
    {
        $tmpfile1 = \tmpfile();

        return [
            [$tmpfile1, null],
            [null, $tmpfile1],
            [null, null]
        ];
    }

    public function assertEqualsSucceedsProvider()
    {
        $tmpfile1 = \tmpfile();
        \fwrite($tmpfile1, 'foo');
        $tmpfile2 = \tmpfile();
        \fwrite($tmpfile2, 'foo');

        $memory1 = \fopen('php://memory', 'r+');
        \fwrite($memory1, 'foo');
        $memory2 = \fopen('php://memory', 'w+');
        \fwrite($memory2, 'foo');
        $memory3 = \fopen('php://memory', 'a+');
        \fwrite($memory3, 'foo');
        $memory4 = \fopen('php://memory', 'x+');
        \fwrite($memory4, 'foo');
        $memory5 = \fopen('php://memory', 'c+');
        \fwrite($memory5, 'foo');

        $image1 = \imagecreate(100, 100);
        $image2 = \imagecreate(100, 100);

        return [
            [$tmpfile1, $tmpfile1],
            [$tmpfile2, $tmpfile2],
            [$tmpfile1, $tmpfile2],
            [$tmpfile2, $tmpfile1],
            [$tmpfile1, $memory1],
            [$memory1, $tmpfile1],
            [$memory1, $memory1],
            [$memory2, $memory2],
            [$memory3, $memory3],
            [$memory1, $memory2],
            [$memory2, $memory1],
            [$memory1, $memory3],
            [$memory1, $memory4],
            [$memory1, $memory5],
            [$image1, $image1],
            [$image2, $image2]
        ];
    }

    public function assertEqualsFailsProvider()
    {
        $tmpfile1 = \tmpfile();
        \fwrite($tmpfile1, 'foo');
        $tmpfile2 = \tmpfile();

        $memory1 = \fopen('php://memory', 'r+');
        \fwrite($memory1, 'foo');
        $memory2 = \fopen('php://memory', 'r+');
        \fwrite($memory2, 'bar');
        $memory3 = \fopen('php://memory', 'x');
        \fwrite($memory3, 'foo');
        $memory4 = \fopen('php://memory', 'c');
        \fwrite($memory4, 'foo');

        $image1 = \imagecreate(100, 100);
        $image2 = \imagecreate(100, 100);

        return [
            [$tmpfile1, $tmpfile2],
            [$tmpfile2, $tmpfile1],
            [$memory1, $memory2],
            [$memory2, $memory1],
            [$memory1, $memory3],
            [$memory1, $memory4],
            [$image1, $image2],
            [$image2, $image1]
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
