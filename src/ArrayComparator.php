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

use function array_key_exists;
use function assert;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_string;
use function serialize;
use function spl_object_id;
use function sprintf;
use function str_replace;
use function trim;
use function usort;
use SebastianBergmann\Exporter\Exporter;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for sebastian/comparator
 *
 * @internal This class is not covered by the backward compatibility promise for sebastian/comparator
 */
class ArrayComparator extends Comparator
{
    public function accepts(mixed $expected, mixed $actual): bool
    {
        return is_array($expected) && is_array($actual);
    }

    /**
     * Arrays are equal if they contain the same key-value pairs.
     * The order of the keys does not matter.
     * The types of key-value pairs do not matter.
     *
     * @param array<mixed> $processed
     *
     * @throws ComparisonFailure
     */
    public function assertEquals(mixed $expected, mixed $actual, float $delta = 0.0, bool $canonicalize = false, bool $ignoreCase = false, array &$processed = []): void
    {
        assert(is_array($expected));
        assert(is_array($actual));

        if ($canonicalize) {
            usort($expected, self::compare(...));
            usort($actual, self::compare(...));
        }

        $remaining        = $actual;
        $actualAsString   = "Array (\n";
        $expectedAsString = "Array (\n";
        $equal            = true;
        $exporter         = new Exporter;

        foreach ($expected as $key => $value) {
            unset($remaining[$key]);

            if (!array_key_exists($key, $actual)) {
                $expectedAsString .= sprintf(
                    "    %s => %s\n",
                    $exporter->export($key),
                    $exporter->shortenedExport($value),
                );

                $equal = false;

                continue;
            }

            try {
                $comparator = $this->factory()->getComparatorFor($value, $actual[$key]);

                /** @phpstan-ignore arguments.count */
                $comparator->assertEquals($value, $actual[$key], $delta, $canonicalize, $ignoreCase, $processed);

                $expectedAsString .= sprintf(
                    "    %s => %s\n",
                    $exporter->export($key),
                    $exporter->shortenedExport($value),
                );

                $actualAsString .= sprintf(
                    "    %s => %s\n",
                    $exporter->export($key),
                    $exporter->shortenedExport($actual[$key]),
                );
            } catch (ComparisonFailure $e) {
                $expectedAsString .= sprintf(
                    "    %s => %s\n",
                    $exporter->export($key),
                    $e->getExpectedAsString() !== '' ? $this->indent($e->getExpectedAsString()) : $exporter->shortenedExport($e->getExpected()),
                );

                $actualAsString .= sprintf(
                    "    %s => %s\n",
                    $exporter->export($key),
                    $e->getActualAsString() !== '' ? $this->indent($e->getActualAsString()) : $exporter->shortenedExport($e->getActual()),
                );

                $equal = false;
            }
        }

        foreach ($remaining as $key => $value) {
            $actualAsString .= sprintf(
                "    %s => %s\n",
                $exporter->export($key),
                $exporter->shortenedExport($value),
            );

            $equal = false;
        }

        $expectedAsString .= ')';
        $actualAsString .= ')';

        if (!$equal) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                $expectedAsString,
                $actualAsString,
                'Failed asserting that two arrays are equal.',
            );
        }
    }

    private function indent(string $lines): string
    {
        return trim(str_replace("\n", "\n    ", $lines));
    }

    private static function compare(mixed $a, mixed $b): int
    {
        $typeOrderA = self::typeOrder($a);
        $typeOrderB = self::typeOrder($b);

        if ($typeOrderA !== $typeOrderB) {
            return $typeOrderA <=> $typeOrderB;
        }

        if (is_object($a) && is_object($b)) {
            $classComparison = $a::class <=> $b::class;

            if ($classComparison !== 0) {
                return $classComparison;
            }

            return spl_object_id($a) <=> spl_object_id($b);
        }

        if (is_array($a) && is_array($b)) {
            return serialize($a) <=> serialize($b);
        }

        return $a <=> $b;
    }

    private static function typeOrder(mixed $value): int
    {
        if ($value === null) {
            return 0;
        }

        if (is_bool($value)) {
            return 1;
        }

        if (is_int($value) || is_float($value)) {
            return 2;
        }

        if (is_string($value)) {
            return 3;
        }

        if (is_array($value)) {
            return 4;
        }

        if (is_object($value)) {
            return 5;
        }

        return 6;
    }
}
