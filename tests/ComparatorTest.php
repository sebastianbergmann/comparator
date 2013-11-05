<?php
/**
 * Comparator
 *
 * Copyright (c) 2001-2013, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Comparator
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.github.com/sebastianbergmann/comparator
 */

namespace SebastianBergmann\Comparator;

/**
 *
 *
 * @package    Comparator
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.github.com/sebastianbergmann/comparator
 */
class ComparatorTest extends \PHPUnit_Framework_TestCase
{
    public function instanceProvider()
    {
        $tmpfile = tmpfile();

        return array(
            array(NULL, NULL, 'SebastianBergmann\\Comparator\\ScalarComparator'),
            array(NULL, TRUE, 'SebastianBergmann\\Comparator\\ScalarComparator'),
            array(TRUE, NULL, 'SebastianBergmann\\Comparator\\ScalarComparator'),
            array(TRUE, TRUE, 'SebastianBergmann\\Comparator\\ScalarComparator'),
            array(FALSE, FALSE, 'SebastianBergmann\\Comparator\\ScalarComparator'),
            array(TRUE, FALSE, 'SebastianBergmann\\Comparator\\ScalarComparator'),
            array(FALSE, TRUE, 'SebastianBergmann\\Comparator\\ScalarComparator'),
            array('', '', 'SebastianBergmann\\Comparator\\ScalarComparator'),
            array('0', '0', 'SebastianBergmann\\Comparator\\ScalarComparator'),
            array('0', 0, 'SebastianBergmann\\Comparator\\NumericComparator'),
            array(0, '0', 'SebastianBergmann\\Comparator\\NumericComparator'),
            array(0, 0, 'SebastianBergmann\\Comparator\\NumericComparator'),
            array(1.0, 0, 'SebastianBergmann\\Comparator\\DoubleComparator'),
            array(0, 1.0, 'SebastianBergmann\\Comparator\\DoubleComparator'),
            array(1.0, 1.0, 'SebastianBergmann\\Comparator\\DoubleComparator'),
            array(array(1), array(1), 'SebastianBergmann\\Comparator\\ArrayComparator'),
            array($tmpfile, $tmpfile, 'SebastianBergmann\\Comparator\\ResourceComparator'),
            array(new \stdClass, new \stdClass, 'SebastianBergmann\\Comparator\\ObjectComparator'),
            array(new \DateTime, new \DateTime, 'SebastianBergmann\\Comparator\\DateTimeComparator'),
            array(new \SplObjectStorage, new \SplObjectStorage, 'SebastianBergmann\\Comparator\\SplObjectStorageComparator'),
            array(new \Exception, new \Exception, 'SebastianBergmann\\Comparator\\ExceptionComparator'),
            array(new \DOMDocument, new \DOMDocument, 'SebastianBergmann\\Comparator\\DOMDocumentComparator'),
            // mixed types
            array($tmpfile, array(1), 'SebastianBergmann\\Comparator\\TypeComparator'),
            array(array(1), $tmpfile, 'SebastianBergmann\\Comparator\\TypeComparator'),
            array($tmpfile, '1', 'SebastianBergmann\\Comparator\\TypeComparator'),
            array('1', $tmpfile, 'SebastianBergmann\\Comparator\\TypeComparator'),
            array($tmpfile, new \stdClass, 'SebastianBergmann\\Comparator\\TypeComparator'),
            array(new \stdClass, $tmpfile, 'SebastianBergmann\\Comparator\\TypeComparator'),
            array(new \stdClass, array(1), 'SebastianBergmann\\Comparator\\TypeComparator'),
            array(array(1), new \stdClass, 'SebastianBergmann\\Comparator\\TypeComparator'),
            array(new \stdClass, '1', 'SebastianBergmann\\Comparator\\TypeComparator'),
            array('1', new \stdClass, 'SebastianBergmann\\Comparator\\TypeComparator'),
            array(new ClassWithToString, '1', 'SebastianBergmann\\Comparator\\ScalarComparator'),
            array('1', new ClassWithToString, 'SebastianBergmann\\Comparator\\ScalarComparator'),
            array(1.0, new \stdClass, 'SebastianBergmann\\Comparator\\TypeComparator'),
            array(new \stdClass, 1.0, 'SebastianBergmann\\Comparator\\TypeComparator'),
            array(1.0, array(1), 'SebastianBergmann\\Comparator\\TypeComparator'),
            array(array(1), 1.0, 'SebastianBergmann\\Comparator\\TypeComparator'),
        );
    }

    /**
     * @dataProvider instanceProvider
     */
    public function testGetInstance($a, $b, $expected)
    {
        $factory = new Factory;
        $actual = $factory->getComparatorFor($a, $b);
        $this->assertInstanceOf($expected, $actual);
    }

    public function testRegister()
    {
        $comparator = new TestClassComparator;

        $factory = new Factory;
        $factory->register($comparator);

        $a = new TestClass;
        $b = new TestClass;
        $expected = 'SebastianBergmann\\Comparator\\TestClassComparator';
        $actual = $factory->getComparatorFor($a, $b);

        $factory->unregister($comparator);
        $this->assertInstanceOf($expected, $actual);
    }

    public function testUnregister()
    {
        $comparator = new TestClassComparator;

        $factory = new Factory;
        $factory->register($comparator);
        $factory->unregister($comparator);

        $a = new TestClass;
        $b = new TestClass;
        $expected = 'SebastianBergmann\\Comparator\\ObjectComparator';
        $actual = $factory->getComparatorFor($a, $b);

        $this->assertInstanceOf($expected, $actual);
    }
}
