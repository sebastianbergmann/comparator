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

    #[DataProvider('acceptsSucceedsProvider')]
    public function testAcceptsSucceeds($expected, $actual): void
    {
        $this->assertTrue(
            $this->comparator->accepts($expected, $actual)
        );
    }

    #[DataProvider('acceptsFailsProvider')]
    public function testAcceptsFails($expected, $actual): void
    {
        $this->assertFalse(
            $this->comparator->accepts($expected, $actual)
        );
    }

    #[DataProvider('assertEqualsSucceedsProvider')]
    public function testAssertEqualsSucceeds($expected, $actual): void
    {
        $exception = null;

        try {
            $this->comparator->assertEquals($expected, $actual);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    #[DataProvider('assertEqualsFailsProvider')]
    public function testAssertEqualsFails($expected, $actual): void
    {
        $this->expectException(ComparisonFailure::class);

        $this->comparator->assertEquals($expected, $actual);
    }
}
