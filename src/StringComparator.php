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

/**
 * Compares scalar or NULL values for equality.
 */
class StringComparator extends Comparator
{
    /**
     * Returns whether the comparator can compare two values.
     *
     * @param mixed $expected The first value to compare
     * @param mixed $actual   The second value to compare
     *
     * @return bool
     *
     * @since  Method available since Release 3.6.0
     */
    public function accepts($expected, $actual)
    {
        return \is_string($expected) && \is_string($actual);
    }

    /**
     * Asserts that two values are equal.
     *
     * @param mixed $expected     First value to compare
     * @param mixed $actual       Second value to compare
     * @param float $delta        Allowed numerical distance between two values to consider them equal
     * @param bool  $canonicalize Arrays are sorted before comparison when set to true
     * @param bool  $ignoreCase   Case is ignored when set to true
     *
     * @throws ComparisonFailure
     */
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false): void
    {
        $expectedToCompare = $expected;
        $actualToCompare   = $actual;

        if ($canonicalize) {
            $expectedToCompare = $this->canonicalizeString($expectedToCompare);
            $actualToCompare   = $this->canonicalizeString($actualToCompare);
        }

        if ($ignoreCase) {
            $expectedToCompare = \strtolower($expectedToCompare);
            $actualToCompare   = \strtolower($actualToCompare);
        }

        if ($expectedToCompare !== $actualToCompare) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                $this->exporter->export($expected),
                $this->exporter->export($actual),
                false,
                'Failed asserting that two strings are equal.'
            );
        }
    }

    private function canonicalizeString($string)
    {
        return \urlencode(\normalizer_normalize($string));
    }
}
