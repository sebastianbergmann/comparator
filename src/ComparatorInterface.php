<?php
/**
 * User: TimiTao
 * Date: 09.07.2014
 */
namespace SebastianBergmann\Comparator;


/**
 * Interface base class for comparators which compare values for equality.
 *
 * @package    Comparator
 * @subpackage Framework
 * @author     Tomasz Kunicki
 * @copyright  2014 Tomasz Kunicki
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.github.com/sebastianbergmann/comparator
 */
interface ComparatorInterface
{
    /**
     * Returns whether the comparator can compare two values.
     *
     * @param  mixed $expected The first value to compare
     * @param  mixed $actual The second value to compare
     * @return boolean
     */
    public function accepts($expected, $actual);

    /**
     * Asserts that two values are equal.
     *
     * @param  mixed $expected The first value to compare
     * @param  mixed $actual The second value to compare
     * @param  float $delta The allowed numerical distance between two values to
     *                      consider them equal
     * @param  bool $canonicalize If set to TRUE, arrays are sorted before
     *                             comparison
     * @param  bool $ignoreCase If set to TRUE, upper- and lowercasing is
     *                           ignored when comparing string values
     * @throws ComparisonFailure Thrown when the comparison
     *                           fails. Contains information about the
     *                           specific errors that lead to the failure.
     */
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false);
}