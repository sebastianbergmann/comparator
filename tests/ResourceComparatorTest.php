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
        $tmpfile3 = \fopen(\tempnam(\sys_get_temp_dir(), ''), 'a+');
        \fwrite($tmpfile3, 'foo');
        $tmpfile4 = \fopen(\sys_get_temp_dir().'/'.\uniqid('', true), 'x+');
        \fwrite($tmpfile4, 'foo');
        $tmpfile5 = \fopen(\tempnam(\sys_get_temp_dir(), ''), 'c+');
        \fwrite($tmpfile5, 'foo');

        $memory1 = \fopen('php://memory', 'r+');
        \fwrite($memory1, 'foo');
        $memory2 = \fopen('php://memory', 'w+');
        \fwrite($memory2, 'foo');

        $image1 = \imagecreate(100, 100);
        $image2 = \imagecreate(100, 100);

        return [
            [$tmpfile1, $tmpfile1],
            [$tmpfile2, $tmpfile2],
            [$tmpfile1, $tmpfile2],
            [$tmpfile2, $tmpfile1],
            [$tmpfile1, $tmpfile3],
            [$tmpfile3, $tmpfile1],
            [$tmpfile1, $tmpfile4],
            [$tmpfile4, $tmpfile1],
            [$tmpfile1, $tmpfile5],
            [$tmpfile5, $tmpfile1],
            [$tmpfile1, $memory1],
            [$memory1, $tmpfile1],
            [$memory1, $memory1],
            [$memory2, $memory2],
            [$memory1, $memory2],
            [$memory2, $memory1],
            [$image1, $image1],
            [$image2, $image2]
        ];
    }

    public function assertEqualsFailsProvider()
    {
        $tmpfile1 = \tmpfile();
        \fwrite($tmpfile1, 'foo');
        $tmpfile2 = \tmpfile();
        $tmpfile3 = \fopen(\tempnam(\sys_get_temp_dir(), ''), 'a');
        $tmpfile4 = \fopen(\sys_get_temp_dir().'/'.\uniqid('', true), 'x');
        $tmpfile5 = \fopen(\tempnam(\sys_get_temp_dir(), ''), 'c');

        $memory1 = \fopen('php://memory', 'r+');
        \fwrite($memory1, 'foo');
        $memory2 = \fopen('php://memory', 'r+');
        \fwrite($memory2, 'bar');

        $image1 = \imagecreate(100, 100);
        $image2 = \imagecreate(100, 100);

        return [
            [$tmpfile1, $tmpfile2],
            [$tmpfile2, $tmpfile1],
            [$tmpfile2, $tmpfile3],
            [$tmpfile3, $tmpfile2],
            [$tmpfile2, $tmpfile4],
            [$tmpfile4, $tmpfile2],
            [$tmpfile2, $tmpfile5],
            [$tmpfile5, $tmpfile2],
            [$memory1, $memory2],
            [$memory2, $memory1],
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
