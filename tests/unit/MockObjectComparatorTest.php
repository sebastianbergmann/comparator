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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Generator\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(MockObjectComparator::class)]
#[UsesClass(Comparator::class)]
#[UsesClass(ComparisonFailure::class)]
#[UsesClass(Factory::class)]
final class MockObjectComparatorTest extends TestCase
{
    private MockObjectComparator $comparator;

    public static function acceptsSucceedsProvider(): array
    {
        $testmock = self::createMockForComparatorTest(TestClass::class);
        $stdmock  = self::createMockForComparatorTest(stdClass::class);

        return [
            [$testmock, $testmock],
            [$stdmock, $stdmock],
            [$stdmock, $testmock],
        ];
    }

    public static function acceptsFailsProvider(): array
    {
        $stdmock = self::createMockForComparatorTest(stdClass::class);

        return [
            [$stdmock, null],
            [null, $stdmock],
            [null, null],
        ];
    }

    public static function assertEqualsSucceedsProvider(): array
    {
        // cyclic dependencies
        $book1                  = self::createMockForComparatorTest(Book::class);
        $book1->author          = self::createMockForComparatorTest(Author::class, ['Terry Pratchett']);
        $book1->author->books[] = $book1;
        $book2                  = self::createMockForComparatorTest(Book::class);
        $book2->author          = self::createMockForComparatorTest(Author::class, ['Terry Pratchett']);
        $book2->author->books[] = $book2;

        $object1 = self::createMockForComparatorTest(SampleClass::class, [4, 8, 15]);
        $object2 = self::createMockForComparatorTest(SampleClass::class, [4, 8, 15]);

        return [
            [$object1, $object1],
            [$object1, $object2],
            [$book1, $book1],
            [$book1, $book2],
            [
                self::createMockForComparatorTest(Struct::class, [2.3]),
                self::createMockForComparatorTest(Struct::class, [2.5]),
                0.5,
            ],
        ];
    }

    public static function assertEqualsFailsProvider(): array
    {
        $typeMessage  = 'is not instance of expected class';
        $equalMessage = 'Failed asserting that two objects are equal.';

        // cyclic dependencies
        $book1                  = self::createMockForComparatorTest(Book::class);
        $book1->author          = self::createMockForComparatorTest(Author::class, ['Terry Pratchett']);
        $book1->author->books[] = $book1;
        $book2                  = self::createMockForComparatorTest(Book::class);
        $book2->author          = self::createMockForComparatorTest(Author::class, ['Terry Pratch']);
        $book2->author->books[] = $book2;

        $book3         = self::createMockForComparatorTest(Book::class);
        $book3->author = 'Terry Pratchett';
        $book4         = self::createMockForComparatorTest(stdClass::class);
        $book4->author = 'Terry Pratchett';

        $object1 = self::createMockForComparatorTest(SampleClass::class, [4, 8, 15]);
        $object2 = self::createMockForComparatorTest(SampleClass::class, [16, 23, 42]);

        return [
            [
                self::createMockForComparatorTest(SampleClass::class, [4, 8, 15]),
                self::createMockForComparatorTest(SampleClass::class, [16, 23, 42]),
                $equalMessage,
            ],
            [$object1, $object2, $equalMessage],
            [$book1, $book2, $equalMessage],
            [$book3, $book4, $typeMessage],
            [
                self::createMockForComparatorTest(Struct::class, [2.3]),
                self::createMockForComparatorTest(Struct::class, [4.2]),
                $equalMessage,
                0.5,
            ],
        ];
    }

    protected function setUp(): void
    {
        $this->comparator = new MockObjectComparator;
        $this->comparator->setFactory(new Factory);
    }

    #[DataProvider('acceptsSucceedsProvider')]
    public function testAcceptsSucceeds($expected, $actual): void
    {
        $this->assertTrue(
            $this->comparator->accepts($expected, $actual),
        );
    }

    #[DataProvider('acceptsFailsProvider')]
    public function testAcceptsFails($expected, $actual): void
    {
        $this->assertFalse(
            $this->comparator->accepts($expected, $actual),
        );
    }

    #[DataProvider('assertEqualsSucceedsProvider')]
    public function testAssertEqualsSucceeds($expected, $actual, $delta = 0.0): void
    {
        $exception = null;

        try {
            $this->comparator->assertEquals($expected, $actual, $delta);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    #[DataProvider('assertEqualsFailsProvider')]
    public function testAssertEqualsFails($expected, $actual, $message, $delta = 0.0): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage($message);

        $this->comparator->assertEquals($expected, $actual, $delta);
    }

    private static function createMockForComparatorTest(string $type, array $constructorArguments = []): MockObject
    {
        $generator = new Generator;

        return $generator->testDouble($type, true, true, null, $constructorArguments);
    }
}
