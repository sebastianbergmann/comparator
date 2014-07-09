<?php
/**
 * User: Tom
 * Date: 09.07.2014
 */
namespace SebastianBergmann\Comparator;


/**
 * Factory interface for comparators which compare values for equality.
 *
 * @package    Comparator
 * @author     Tomasz Kunicki
 * @copyright  2014 Tomasz Kunicki
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.github.com/sebastianbergmann/comparator
 */
interface FactoryInterface
{
    /**
     * Returns the correct comparator for comparing two values.
     *
     * @param  mixed $expected The first value to compare
     * @param  mixed $actual The second value to compare
     * @return ComparatorInterface
     */
    public function getComparatorFor($expected, $actual);
}