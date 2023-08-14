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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(ExceptionComparator::class)]
#[UsesClass(Comparator::class)]
#[UsesClass(ComparisonFailure::class)]
#[UsesClass(Factory::class)]
final class ExceptionComparatorTest extends TestCase
{
    private ExceptionComparator $comparator;

    public static function acceptsSucceedsProvider(): array
    {
        return [
            [new Exception, new Exception],
            [new RuntimeException, new RuntimeException],
            [new Exception, new RuntimeException],
        ];
    }

    public static function acceptsFailsProvider(): array
    {
        return [
            [new Exception, null],
            [null, new Exception],
            [null, null],
        ];
    }

    public static function assertEqualsSucceedsProvider(): array
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

    public static function assertEqualsFailsProvider(): array
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
    public function testAssertEqualsFails($expected, $actual, $message): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage($message);

        $this->comparator->assertEquals($expected, $actual);
    }
}
