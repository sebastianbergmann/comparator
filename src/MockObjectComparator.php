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
 * Compares PHPUnit_Framework_MockObject_MockObject instances for equality.
 */
class MockObjectComparator extends ObjectComparator
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
        return ($expected instanceof \PHPUnit_Framework_MockObject_MockObject || $expected instanceof \PHPUnit\Framework\MockObject\MockObject) &&
               ($actual instanceof \PHPUnit_Framework_MockObject_MockObject || $actual instanceof \PHPUnit\Framework\MockObject\MockObject);
    }
}
