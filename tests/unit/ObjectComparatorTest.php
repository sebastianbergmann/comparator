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
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(ObjectComparator::class)]
#[UsesClass(Comparator::class)]
#[UsesClass(ComparisonFailure::class)]
#[UsesClass(Factory::class)]
#[Small]
final class ObjectComparatorTest extends TestCase
{
    private ObjectComparator $comparator;

    /**
     * @return non-empty-list<array{0: stdClass|TestClass, 1: stdClass|TestClass}>
     */
    public static function acceptsSucceedsProvider(): array
    {
        return [
            [new TestClass, new TestClass],
            [new stdClass, new stdClass],
            [new stdClass, new TestClass],
        ];
    }

    /**
     * @return non-empty-list<array{0: ?stdClass, 1: ?stdClass}>
     */
    public static function acceptsFailsProvider(): array
    {
        return [
            [new stdClass, null],
            [null, new stdClass],
            [null, null],
        ];
    }

    /**
     * @return non-empty-list<array{0: object, 1: object, 2?: float}>
     */
    public static function assertEqualsSucceedsProvider(): array
    {
        // cyclic dependencies
        $book1                  = new Book;
        $book1->author          = new Author('Terry Pratchett');
        $book1->author->books[] = $book1;
        $book2                  = new Book;
        $book2->author          = new Author('Terry Pratchett');
        $book2->author->books[] = $book2;

        $object1 = new SampleClass(4, 8, 15);
        $object2 = new SampleClass(4, 8, 15);

        return [
            [$object1, $object1],
            [$object1, $object2],
            [$book1, $book1],
            [$book1, $book2],
            [new Struct(2.3), new Struct(2.5), 0.5],
        ];
    }

    /**
     * @return non-empty-list<array{0: object, 1: object, 2?: string, 3?: float}>
     */
    public static function assertEqualsFailsProvider(): array
    {
        $typeMessage  = 'is not instance of expected class';
        $equalMessage = 'Failed asserting that two objects are equal.';

        // cyclic dependencies
        $book1                  = new Book;
        $book1->author          = new Author('Terry Pratchett');
        $book1->author->books[] = $book1;
        $book2                  = new Book;
        $book2->author          = new Author('Terry Pratch');
        $book2->author->books[] = $book2;

        $book3         = new Book;
        $book3->author = new Author('Terry Pratchett');
        $book4         = new stdClass;
        $book4->author = new Author('Terry Pratchett');

        $object1 = new SampleClass(4, 8, 15);
        $object2 = new SampleClass(16, 23, 42);

        return [
            [new SampleClass(4, 8, 15), new SampleClass(16, 23, 42), $equalMessage],
            [$object1, $object2, $equalMessage],
            [$book1, $book2, $equalMessage],
            [$book3, $book4, $typeMessage],
            [new Struct(2.3), new Struct(4.2), $equalMessage, 0.5],
        ];
    }

    protected function setUp(): void
    {
        $this->comparator = new ObjectComparator;
        $this->comparator->setFactory(new Factory);
    }

    #[DataProvider('acceptsSucceedsProvider')]
    public function testAcceptsSucceeds(stdClass|TestClass $expected, stdClass|TestClass $actual): void
    {
        $this->assertTrue(
            $this->comparator->accepts($expected, $actual),
        );
    }

    #[DataProvider('acceptsFailsProvider')]
    public function testAcceptsFails(?stdClass $expected, ?stdClass $actual): void
    {
        $this->assertFalse(
            $this->comparator->accepts($expected, $actual),
        );
    }

    #[DataProvider('assertEqualsSucceedsProvider')]
    public function testAssertEqualsSucceeds(object $expected, object $actual, float $delta = 0.0): void
    {
        $exception = null;

        try {
            $this->comparator->assertEquals($expected, $actual, $delta);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    #[DataProvider('assertEqualsFailsProvider')]
    public function testAssertEqualsFails(object $expected, object $actual, string $message, float $delta = 0.0): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage($message);

        $this->comparator->assertEquals($expected, $actual, $delta);
    }
}
