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
 * Compares resources for equality.
 */
class ResourceComparator extends Comparator
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
        return \is_resource($expected) && \is_resource($actual);
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
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        if ($this->asString($actual) !== $this->asString($expected)) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                $this->exporter->export($expected),
                $this->exporter->export($actual)
            );
        }
    }

    private function asString($resource)
    {
        if ('stream' !== \get_resource_type($resource)) {
            return (string) $resource;
        }

        $metaData = \stream_get_meta_data($resource);

        if (!\preg_match('(a\+|c\+|r|w\+|x\+)', $metaData['mode'])) {
            return (string) $resource;
        }

        $position = \ftell($resource);
        \rewind($resource);

        $context = \hash_init('md5');
        \hash_update_stream($context, $resource);

        if (\is_int($position)) {
            \fseek($resource, $position);
        }

        return \hash_final($context);
    }
}
