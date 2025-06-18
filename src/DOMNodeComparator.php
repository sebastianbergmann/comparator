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
use function mb_strtolower;
use function sprintf;
use DOMDocument;
use DOMNode;
use ValueError;

final class DOMNodeComparator extends ObjectComparator
{
    public function accepts(mixed $expected, mixed $actual): bool
    {
        return $expected instanceof DOMNode && $actual instanceof DOMNode;
    }

    /**
     * @param array<mixed> $processed
     *
     * @throws ComparisonFailure
     */
    public function assertEquals(mixed $expected, mixed $actual, float $delta = 0.0, bool $canonicalize = false, bool $ignoreCase = false, array &$processed = []): void
    {
        assert($expected instanceof DOMNode);
        assert($actual instanceof DOMNode);

        $expectedAsString = $this->nodeToText($expected, $ignoreCase);
        $actualAsString   = $this->nodeToText($actual, $ignoreCase);

        if ($expectedAsString !== $actualAsString) {
            $type = $expected instanceof DOMDocument ? 'documents' : 'nodes';

            throw new ComparisonFailure(
                $expected,
                $actual,
                $expectedAsString,
                $actualAsString,
                sprintf("Failed asserting that two DOM %s are equal.\n", $type),
            );
        }
    }

    /**
     * Canonicalizes nodes, removes empty text nodes and merges adjacent text nodes,
     * and optionally ignores case.
     *
     * @see https://github.com/sebastianbergmann/phpunit/pull/1236#issuecomment-41765023
     */
    private function nodeToText(DOMNode $node, bool $ignoreCase): string
    {
        $document = new DOMDocument;

        try {
            $c14n = $node->C14N();

            assert($c14n !== false && $c14n !== '');

            @$document->loadXML($c14n);
            // @codeCoverageIgnoreStart
        } catch (ValueError) {
            // @codeCoverageIgnoreEnd
        }

        $document->formatOutput = true;
        $document->normalizeDocument();

        $text = $document->saveXML();

        assert($text !== false);

        if ($ignoreCase) {
            return mb_strtolower($text, 'UTF-8');
        }

        return $text;
    }
}
