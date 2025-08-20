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
use function spl_object_id;
use function sprintf;
use Closure;
use ReflectionFunction;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for sebastian/comparator
 *
 * @internal This class is not covered by the backward compatibility promise for sebastian/comparator
 */
final class ClosureComparator extends Comparator
{
    public function accepts(mixed $expected, mixed $actual): bool
    {
        return $expected instanceof Closure && $actual instanceof Closure;
    }

    public function assertEquals(mixed $expected, mixed $actual, float $delta = 0.0, bool $canonicalize = false, bool $ignoreCase = false): void
    {
        assert($expected instanceof Closure);
        assert($actual instanceof Closure);

        /** @phpstan-ignore equal.notAllowed */
        if ($expected == $actual) {
            return;
        }

        $expectedReflector = new ReflectionFunction($expected);
        $actualReflector   = new ReflectionFunction($actual);

        throw new ComparisonFailure(
            $expected,
            $actual,
            'Closure Object #' . spl_object_id($expected) . ' ()',
            'Closure Object #' . spl_object_id($actual) . ' ()',
            sprintf(
                'Failed asserting that closure declared at %s:%d is equal to closure declared at %s:%d.',
                $expectedReflector->getFileName(),
                $expectedReflector->getStartLine(),
                $actualReflector->getFileName(),
                $actualReflector->getStartLine(),
            ),
        );
    }
}
