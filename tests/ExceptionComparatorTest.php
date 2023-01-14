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

use Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \SebastianBergmann\Comparator\ExceptionComparator
 *
 * @uses \SebastianBergmann\Comparator\Comparator
 * @uses \SebastianBergmann\Comparator\ComparisonFailure
 * @uses \SebastianBergmann\Comparator\Factory
 */
final class ExceptionComparatorTest extends TestCase
{
    /**
     * @var ExceptionComparator
     */
    private $comparator;

    public static function acceptsSucceedsProvider()
    {
        return [
            [new Exception, new Exception],
            [new RuntimeException, new RuntimeException],
            [new Exception, new RuntimeException],
        ];
    }

    public static function acceptsFailsProvider()
    {
        return [
            [new Exception, null],
            [null, new Exception],
            [null, null],
        ];
    }

    public static function assertEqualsSucceedsProvider()
    {
        $exception1 = new Exception;
        $exception2 = new Exception;

        $exception3 = new RuntimeException('Error', 100);
        $exception4 = new RuntimeException('Error', 100);

        return [
            [$exception1, $exception1],
            [$exception1, $exception2],
            [$exception3, $exception3],
            [$exception3, $exception4],
        ];
    }

    public static function assertEqualsFailsProvider()
    {
        $typeMessage  = 'not instance of expected class';
        $equalMessage = 'Failed asserting that two objects are equal.';

        $exception1 = new Exception('Error', 100);
        $exception2 = new Exception('Error', 101);
        $exception3 = new Exception('Errors', 101);

        $exception4 = new RuntimeException('Error', 100);
        $exception5 = new RuntimeException('Error', 101);

        return [
            [$exception1, $exception2, $equalMessage],
            [$exception1, $exception3, $equalMessage],
            [$exception1, $exception4, $typeMessage],
            [$exception2, $exception3, $equalMessage],
            [$exception4, $exception5, $equalMessage],
        ];
    }

    protected function setUp(): void
    {
        $this->comparator = new ExceptionComparator;
        $this->comparator->setFactory(new Factory);
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
    public function testAssertEqualsFails($expected, $actual, $message): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage($message);

        $this->comparator->assertEquals($expected, $actual);
    }
}
