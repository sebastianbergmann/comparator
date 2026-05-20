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

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesClassesThatExtendClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(MockObjectComparator::class)]
#[UsesClassesThatExtendClass(Comparator::class)]
#[UsesClass(ComparisonFailure::class)]
#[UsesClass(Factory::class)]
#[AllowMockObjectsWithoutExpectations]
#[Small]
final class MockObjectComparatorTest extends TestCase
{
    private MockObjectComparator $comparator;

    protected function setUp(): void
    {
        $this->comparator = new MockObjectComparator;
        $this->comparator->setFactory(new Factory);
    }

    public function testAcceptsMockOfSameInstance(): void
    {
        $mock = $this->createMock(TestClass::class);

        $this->assertTrue($this->comparator->accepts($mock, $mock));
    }

    public function testAcceptsMockOfStdClass(): void
    {
        $mock = $this->createMock(stdClass::class);

        $this->assertTrue($this->comparator->accepts($mock, $mock));
    }

    public function testAcceptsMocksOfDifferentClasses(): void
    {
        $this->assertTrue(
            $this->comparator->accepts(
                $this->createMock(stdClass::class),
                $this->createMock(TestClass::class),
            ),
        );
    }

    public function testDoesNotAcceptMockAndNull(): void
    {
        $this->assertFalse(
            $this->comparator->accepts(
                $this->createMock(stdClass::class),
                null,
            ),
        );
    }

    public function testDoesNotAcceptNullAndMock(): void
    {
        $this->assertFalse(
            $this->comparator->accepts(
                null,
                $this->createMock(stdClass::class),
            ),
        );
    }

    public function testDoesNotAcceptTwoNullValues(): void
    {
        $this->assertFalse($this->comparator->accepts(null, null));
    }

    public function testAssertEqualsSucceedsForSameMockInstance(): void
    {
        $object = $this
            ->getMockBuilder(SampleClass::class)
            ->setConstructorArgs([4, 8, 15])
            ->getMock();

        $this->assertDoesNotFail($object, $object);
    }

    public function testAssertEqualsSucceedsForEqualMockInstances(): void
    {
        $expected = $this
            ->getMockBuilder(SampleClass::class)
            ->setConstructorArgs([4, 8, 15])
            ->getMock();

        $actual = $this
            ->getMockBuilder(SampleClass::class)
            ->setConstructorArgs([4, 8, 15])
            ->getMock();

        $this->assertDoesNotFail($expected, $actual);
    }

    public function testAssertEqualsSucceedsForSameCyclicMock(): void
    {
        $book = $this->createMock(Book::class);

        $book->author = $this
            ->getMockBuilder(Author::class)
            ->setConstructorArgs(['Terry Pratchett'])
            ->getMock();

        $book->author->books[] = $book;

        $this->assertDoesNotFail($book, $book);
    }

    public function testAssertEqualsSucceedsForEqualCyclicMocks(): void
    {
        $expected = $this->createMock(Book::class);

        $expected->author = $this
            ->getMockBuilder(Author::class)
            ->setConstructorArgs(['Terry Pratchett'])
            ->getMock();

        $expected->author->books[] = $expected;

        $actual = $this->createMock(Book::class);

        $actual->author = $this
            ->getMockBuilder(Author::class)
            ->setConstructorArgs(['Terry Pratchett'])
            ->getMock();

        $actual->author->books[] = $actual;

        $this->assertDoesNotFail($expected, $actual);
    }

    public function testAssertEqualsSucceedsForStructValuesWithinDelta(): void
    {
        $expected = $this
            ->getMockBuilder(Struct::class)
            ->setConstructorArgs([2.3])
            ->getMock();

        $actual = $this
            ->getMockBuilder(Struct::class)
            ->setConstructorArgs([2.5])
            ->getMock();

        $this->assertDoesNotFail($expected, $actual, 0.5);
    }

    public function testAssertEqualsFailsForDifferentMockInstances(): void
    {
        $expected = $this
            ->getMockBuilder(SampleClass::class)
            ->setConstructorArgs([4, 8, 15])
            ->getMock();

        $actual = $this
            ->getMockBuilder(SampleClass::class)
            ->setConstructorArgs([16, 23, 42])
            ->getMock();

        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage('Failed asserting that two objects are equal.');

        $this->comparator->assertEquals($expected, $actual);
    }

    public function testAssertEqualsFailsForUnequalCyclicMocks(): void
    {
        $expected = $this->createMock(Book::class);

        $expected->author = $this
            ->getMockBuilder(Author::class)
            ->setConstructorArgs(['Terry Pratchett'])
            ->getMock();

        $expected->author->books[] = $expected;

        $actual = $this->createMock(Book::class);

        $actual->author = $this
            ->getMockBuilder(Author::class)
            ->setConstructorArgs(['Terry Pratch'])
            ->getMock();

        $actual->author->books[] = $actual;

        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage('Failed asserting that two objects are equal.');

        $this->comparator->assertEquals($expected, $actual);
    }

    public function testAssertEqualsFailsForMocksOfDifferentClasses(): void
    {
        $expected         = $this->createMock(Book::class);
        $expected->author = new Author('Terry Pratchett');

        $actual         = $this->createMock(stdClass::class);
        $actual->author = new Author('Terry Pratchett');

        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage('is not instance of expected class');

        $this->comparator->assertEquals($expected, $actual);
    }

    public function testAssertEqualsFailsForStructValuesOutsideDelta(): void
    {
        $expected = $this
            ->getMockBuilder(Struct::class)
            ->setConstructorArgs([2.3])
            ->getMock();

        $actual = $this
            ->getMockBuilder(Struct::class)
            ->setConstructorArgs([4.2])
            ->getMock();

        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage('Failed asserting that two objects are equal.');

        $this->comparator->assertEquals($expected, $actual, 0.5);
    }

    private function assertDoesNotFail(MockObject $expected, MockObject $actual, float $delta = 0.0): void
    {
        $exception = null;

        try {
            $this->comparator->assertEquals($expected, $actual, $delta);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }
}
