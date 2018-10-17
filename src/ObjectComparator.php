<?php
/*
 * This file is part of sebastian/comparator.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\Comparator;

/**
 * Compares objects for equality.
 */
class ObjectComparator
{
    /**
     * Returns whether the comparator can compare two values.
     *
     * @param mixed $expected The first value to compare
     * @param mixed $actual   The second value to compare
     *
     * @return bool
     */
    public function accepts($expected, $actual)
    {
        return \is_object($expected) && \is_object($actual);
    }

    /**
     * Asserts that two objects are equal.
     *
     * @param mixed $expected     First value to compare
     * @param mixed $actual       Second value to compare
     * @param float $delta        Allowed numerical distance between two values to consider them equal
     * @param bool  $canonicalize Arrays are sorted before comparison when set to true
     * @param bool  $ignoreCase   Case is ignored when set to true
     * @param array $processed    List of already processed elements (used to prevent infinite recursion)
     *
     * @throws ComparisonFailure
     */
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false, array &$processed = [])
    {
        if (\get_class($actual) !== \get_class($expected)) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                $this->exporter->export($expected),
                $this->exporter->export($actual),
                false,
                \sprintf(
                    '%s is not instance of expected class "%s".',
                    $this->exporter->export($actual),
                    \get_class($expected)
                )
            );
        }

        // don't compare twice to allow for cyclic dependencies
        if (\in_array([$actual, $expected], $processed, true) ||
            \in_array([$expected, $actual], $processed, true)) {
            return;
        }

        $processed[] = [$actual, $expected];

        if ($actual != $expected) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                \get_class($expected) . ' Object',
                \get_class($actual) . ' Object',
                false,
                'Failed asserting that two objects are equal.'
            );
        }
    }
}
