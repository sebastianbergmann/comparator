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

use DOMDocument;
use DOMNode;

/**
 * Compares DOMNode instances for equality.
 */
class DOMNodeComparator extends ObjectComparator
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
        return $expected instanceof DOMNode && $actual instanceof DOMNode;
    }

    /**
     * Asserts that two values are equal.
     *
     * @param mixed $expected     First value to compare
     * @param mixed $actual       Second value to compare
     * @param float $delta        Allowed numerical distance between two values to consider them equal
     * @param bool  $canonicalize The value of this argument ignored and always considered as true
     * @param bool  $ignoreCase   Case is ignored when set to true
     * @param array $processed    List of already processed elements (used to prevent infinite recursion)
     *
     * @throws ComparisonFailure
     */
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false, array &$processed = [])
    {
        $expectedAsString = $this->nodeToText($expected, $ignoreCase);
        $actualAsString   = $this->nodeToText($actual, $ignoreCase);

        if ($expectedAsString !== $actualAsString) {
            $type = $expected instanceof DOMDocument ? 'documents' : 'nodes';

            throw new ComparisonFailure(
                $expected,
                $actual,
                $expectedAsString,
                $actualAsString,
                false,
                \sprintf("Failed asserting that two DOM %s are equal.\n", $type)
            );
        }
    }

    /**
     * Returns the normalized, whitespace-cleaned, and indented textual representation of a DOMNode.
     *
     * @param  DOMNode $node
     * @param  bool $ignoreCase If true - whole xml (tags and inner text) will be converted to lowercase during
     *   comparison
     *
     * @return string Text representation of DOMNode
     */
    private function nodeToText(DOMNode $node, bool $ignoreCase): string
    {
        $document = new DOMDocument();

        $nodeString = $node->C14N();

        // If an empty string is passed as the source, a warning will be generated. We prevent it here.
        if ($nodeString !== "") {
            $document->loadXML($nodeString);
        }

        $node = $document;

        $text = $node->saveXML();

        return $ignoreCase ? \strtolower($text) : $text;
    }
}
