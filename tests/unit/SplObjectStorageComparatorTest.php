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
use SplObjectStorage;
use stdClass;

#[CoversClass(SplObjectStorageComparator::class)]
#[UsesClass(Comparator::class)]
#[UsesClass(ComparisonFailure::class)]
#[UsesClass(Factory::class)]
#[Small]
final class SplObjectStorageComparatorTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: object, 1: object}>
     */
    public static function acceptsFailsProvider(): array
    {
        return [
            [new SplObjectStorage, new stdClass],
            [new stdClass, new SplObjectStorage],
            [new stdClass, new stdClass],
        ];
    }

    /**
     * @return non-empty-list<array{0: object, 1: object}>
     */
    public static function assertEqualsSucceedsProvider(): array
    {
        $object1 = new stdClass;
        $object2 = new stdClass;

        $storage1 = new SplObjectStorage;
        $storage2 = new SplObjectStorage;

        $storage3 = new SplObjectStorage;
        $storage3->attach($object1);
        $storage3->attach($object2);

        $storage4 = new SplObjectStorage;
        $storage4->attach($object2);
        $storage4->attach($object1);

        return [
            [$storage1, $storage1],
            [$storage1, $storage2],
            [$storage3, $storage3],
            [$storage3, $storage4],
        ];
    }

    /**
     * @return non-empty-list<array{0: object, 1: object}>
     */
    public static function assertEqualsFailsProvider(): array
    {
        $object1 = new stdClass;
        $object2 = new stdClass;

        $storage1 = new SplObjectStorage;

        $storage2 = new SplObjectStorage;
        $storage2->attach($object1);

        $storage3 = new SplObjectStorage;
        $storage3->attach($object2);
        $storage3->attach($object1);

        return [
            [$storage1, $storage2],
            [$storage1, $storage3],
            [$storage2, $storage3],
        ];
    }

    public function testAcceptsSucceeds(): void
    {
        $this->assertTrue(
            (new SplObjectStorageComparator)->accepts(
                new SplObjectStorage,
                new SplObjectStorage,
            ),
        );
    }

    #[DataProvider('acceptsFailsProvider')]
    public function testAcceptsFails(object $expected, object $actual): void
    {
        $this->assertFalse(
            (new SplObjectStorageComparator)->accepts($expected, $actual),
        );
    }

    #[DataProvider('assertEqualsSucceedsProvider')]
    public function testAssertEqualsSucceeds(object $expected, object $actual): void
    {
        $exception = null;

        try {
            (new SplObjectStorageComparator)->assertEquals($expected, $actual);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    #[DataProvider('assertEqualsFailsProvider')]
    public function testAssertEqualsFails(object $expected, object $actual): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage('Failed asserting that two objects are equal.');

        (new SplObjectStorageComparator)->assertEquals($expected, $actual);
    }

    public function testAssertEqualsFails2(): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage('Failed asserting that two objects are equal.');

        $t = new SplObjectStorage;
        $t->attach(new stdClass);

        (new SplObjectStorageComparator)->assertEquals($t, new SplObjectStorage);
    }
}
