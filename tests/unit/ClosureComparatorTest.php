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

use function assert;
use Closure;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesClassesThatExtendClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClosureComparator::class)]
#[UsesClassesThatExtendClass(Comparator::class)]
#[UsesClass(ComparisonFailure::class)]
#[UsesClass(DateTimeComparator::class)]
#[UsesClass(Factory::class)]
#[UsesClass(ObjectComparator::class)]
#[UsesClass(ArrayComparator::class)]
#[UsesClass(ScalarComparator::class)]
#[UsesClass(TypeComparator::class)]
#[Small]
final class ClosureComparatorTest extends TestCase
{
    private ClosureComparator $comparator;

    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function acceptsSucceedsProvider(): array
    {
        $f = static function (): void
        {
        };

        $g = static function (): void
        {
        };

        return [
            [$f, $f],
            [$f, $g],
        ];
    }

    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function acceptsFailsProvider(): array
    {
        $f = static function (): void
        {
        };

        return [
            [$f, null],
            [null, $f],
        ];
    }

    /**
     * @return non-empty-array<string, array{Closure, Closure}>
     */
    public static function assertEqualsSucceedsProvider(): array
    {
        $f = static function (): void
        {
        };

        return [
            'identical closure instance'    => [$f, $f],
            'same declaration, no captures' => [
                ClosureFixture::staticNoCapture(),
                ClosureFixture::staticNoCapture(),
            ],
            'same declaration, equal scalar capture' => [
                ClosureFixture::staticCapturingInt(7),
                ClosureFixture::staticCapturingInt(7),
            ],
            'same declaration, multiple equal captures' => [
                ClosureFixture::staticCapturingTwo(1, 'x'),
                ClosureFixture::staticCapturingTwo(1, 'x'),
            ],
            'same declaration, captured object compares equal recursively' => [
                ClosureFixture::staticCapturingMixed(new DateTimeImmutable('2024-01-01T00:00:00+00:00')),
                ClosureFixture::staticCapturingMixed(new DateTimeImmutable('2024-01-01T00:00:00+00:00')),
            ],
            'same declaration, bound $this objects compare equal recursively' => [
                (new ClosureFixture(1))->nonStaticReturningOne(),
                (new ClosureFixture(1))->nonStaticReturningOne(),
            ],
        ];
    }

    /**
     * @return non-empty-array<string, array{Closure, Closure}>
     */
    public static function assertEqualsFailsProvider(): array
    {
        $f = static function (): void
        {
        };

        $g = static function (): void
        {
        };

        $rescopedNoCapture = Closure::bind(ClosureFixture::staticNoCapture(), null, Author::class);

        assert($rescopedNoCapture instanceof Closure);

        return [
            'different declaration site, identical body' => [
                ClosureFixture::staticNoCapture(),
                ClosureFixture::staticAlternativeNoCapture(),
            ],
            'inline closures declared at different lines' => [$f, $g],
            'same declaration, differing scalar capture'  => [
                ClosureFixture::staticCapturingInt(1),
                ClosureFixture::staticCapturingInt(2),
            ],
            'same declaration, captured object compares not equal' => [
                ClosureFixture::staticCapturingMixed(new DateTimeImmutable('2024-01-01T00:00:00+00:00')),
                ClosureFixture::staticCapturingMixed(new DateTimeImmutable('2025-01-01T00:00:00+00:00')),
            ],
            'same declaration, differing scope class' => [
                ClosureFixture::staticNoCapture(),
                $rescopedNoCapture,
            ],
            'same declaration, bound $this objects compare not equal' => [
                (new ClosureFixture(1))->nonStaticReturningOne(),
                (new ClosureFixture(2))->nonStaticReturningOne(),
            ],
        ];
    }

    protected function setUp(): void
    {
        $this->comparator = new ClosureComparator;
        $this->comparator->setFactory(new Factory);
    }

    #[DataProvider('acceptsSucceedsProvider')]
    public function testAcceptsSucceeds(mixed $expected, mixed $actual): void
    {
        $this->assertTrue(
            $this->comparator->accepts($expected, $actual),
        );
    }

    #[DataProvider('acceptsFailsProvider')]
    public function testAcceptsFails(mixed $expected, mixed $actual): void
    {
        $this->assertFalse(
            $this->comparator->accepts($expected, $actual),
        );
    }

    #[DataProvider('assertEqualsSucceedsProvider')]
    public function testAssertEqualsSucceeds(Closure $expected, Closure $actual): void
    {
        $exception = null;

        try {
            $this->comparator->assertEquals($expected, $actual);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    #[DataProvider('assertEqualsFailsProvider')]
    public function testAssertEqualsFails(Closure $expected, Closure $actual): void
    {
        try {
            $this->comparator->assertEquals($expected, $actual);
        } catch (ComparisonFailure $e) {
            $this->assertStringMatchesFormat(
                'Failed asserting that closure declared at %s:%d is equal to closure declared at %s:%d.',
                $e->getMessage(),
            );

            return;
        }

        $this->fail('Expected ComparisonFailure to be thrown');
    }

    public function testRecordsClosureComparisonInFactory(): void
    {
        $factory    = new Factory;
        $comparator = new ClosureComparator;
        $comparator->setFactory($factory);

        $closure = static function (): void
        {
        };

        $comparator->assertEquals($closure, $closure);

        $this->assertTrue($factory->closureComparisonOccurred());
    }

    public function testAsStringComparisonFormat(): void
    {
        $f = static function (): void
        {
        };

        $g = static function (): void
        {
        };

        try {
            $this->comparator->assertEquals($f, $g);
        } catch (ComparisonFailure $e) {
            $this->assertStringMatchesFormat(
                'Closure Object #%d ()',
                $e->getActualAsString(),
            );
            $this->assertStringMatchesFormat(
                'Closure Object #%d ()',
                $e->getExpectedAsString(),
            );

            return;
        }

        $this->fail('Expected ComparisonFailure to be thrown');
    }
}
