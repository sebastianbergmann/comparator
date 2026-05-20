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

use function abs;
use function assert;
use function in_array;
use DateInterval;
use DateTimeImmutable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for sebastian/comparator
 *
 * @internal This class is not covered by the backward compatibility promise for sebastian/comparator
 */
final class DateIntervalComparator extends ObjectComparator
{
    public function accepts(mixed $expected, mixed $actual): bool
    {
        return $expected instanceof DateInterval && $actual instanceof DateInterval;
    }

    /**
     * @param array<mixed> $processed
     *
     * @throws ComparisonFailure
     */
    public function assertEquals(mixed $expected, mixed $actual, float $delta = 0.0, bool $canonicalize = false, bool $ignoreCase = false, array &$processed = []): void
    {
        assert($expected instanceof DateInterval);
        assert($actual instanceof DateInterval);

        if (in_array([$actual, $expected], $processed, true) ||
            in_array([$expected, $actual], $processed, true)) {
            return;
        }

        $processed[] = [$actual, $expected];

        if (abs($this->toSeconds($expected) - $this->toSeconds($actual)) > abs($delta)) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                $this->format($expected),
                $this->format($actual),
                'Failed asserting that two DateInterval objects are equal.',
                $this->contextLines(),
            );
        }
    }

    /**
     * Converts a DateInterval to a duration in seconds by applying it to the
     * Unix epoch. Month and year components are therefore evaluated against a
     * fixed anchor (a month is not always 30 days, a year not always 365), so
     * two intervals whose calendar components differ may still be considered
     * equal within a delta when the resulting durations match.
     */
    private function toSeconds(DateInterval $interval): float
    {
        $applied = new DateTimeImmutable('@0')->add($interval);

        return (float) $applied->format('U') + ((float) $applied->format('u')) / 1_000_000;
    }

    private function format(DateInterval $interval): string
    {
        return $interval->format('%RP%yY%mM%dDT%hH%iM%sS.%fF');
    }
}
