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

#[CoversClass(ResourceComparator::class)]
#[UsesClass(Comparator::class)]
#[UsesClass(ComparisonFailure::class)]
#[UsesClass(Factory::class)]
final class ResourceComparatorTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: resource, 1: resource}>
     */
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

    /**
     * @return non-empty-list<array{0: ?resource, 1: ?resource}>
     */
    public static function acceptsFailsProvider(): array
    {
        $tmpfile1 = tmpfile();

        return [
            [$tmpfile1, null],
            [null, $tmpfile1],
            [null, null],
        ];
    }

    /**
     * @return non-empty-list<array{0: resource, 1: resource}>
     */
    public static function assertEqualsSucceedsProvider(): array
    {
        $tmpfile1 = tmpfile();
        $tmpfile2 = tmpfile();

        return [
            [$tmpfile1, $tmpfile1],
            [$tmpfile2, $tmpfile2],
        ];
    }

    /**
     * @return non-empty-list<array{0: resource, 1: resource}>
     */
    public static function assertEqualsFailsProvider(): array
    {
        $tmpfile1 = tmpfile();
        $tmpfile2 = tmpfile();

        return [
            [$tmpfile1, $tmpfile2],
            [$tmpfile2, $tmpfile1],
        ];
    }

    /**
     * @param resource $expected
     * @param resource $actual
     */
    #[DataProvider('acceptsSucceedsProvider')]
    public function testAcceptsSucceeds($expected, $actual): void
    {
        $this->assertTrue(
            (new ResourceComparator)->accepts($expected, $actual),
        );
    }

    /**
     * @param ?resource $expected
     * @param ?resource $actual
     */
    #[DataProvider('acceptsFailsProvider')]
    public function testAcceptsFails($expected, $actual): void
    {
        $this->assertFalse(
            (new ResourceComparator)->accepts($expected, $actual),
        );
    }

    /**
     * @param resource $expected
     * @param resource $actual
     */
    #[DataProvider('assertEqualsSucceedsProvider')]
    public function testAssertEqualsSucceeds($expected, $actual): void
    {
        $exception = null;

        try {
            (new ResourceComparator)->assertEquals($expected, $actual);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    /**
     * @param resource $expected
     * @param resource $actual
     */
    #[DataProvider('assertEqualsFailsProvider')]
    public function testAssertEqualsFails($expected, $actual): void
    {
        $this->expectException(ComparisonFailure::class);

        (new ResourceComparator)->assertEquals($expected, $actual);
    }
}
