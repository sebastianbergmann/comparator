<?php
/**
 * Comparator
 *
 * Copyright (c) 2001-2014, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @author     Jeff Welch <whatthejeff@gmail.com>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.github.com/sebastianbergmann/comparator
 */

namespace SebastianBergmann\Comparator;

/**
 * @coversDefaultClass SebastianBergmann\Comparator\NumericComparator
 *
 * @package    Comparator
 * @author     Jeff Welch <whatthejeff@gmail.com>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.github.com/sebastianbergmann/comparator
 */
class NumericComparatorTest extends \PHPUnit_Framework_TestCase
{
    private $comparator;

    protected function setUp()
    {
        $this->comparator = new NumericComparator;
    }

    public function acceptsSucceedsProvider()
    {
        return array(
          array(5, 10),
          array(8, '0'),
          array('10', 0),
          array(0x74c3b00c, 42),
          array(0755, 0777)
        );
    }

    public function acceptsFailsProvider()
    {
        return array(
          array('5', '10'),
          array(8, 5.0),
          array(5.0, 8),
          array(10, null),
          array(false, 12)
        );
    }

    public function assertEqualsSucceedsProvider()
    {
        return array(
          array(1337, 1337),
          array('1337', 1337),
          array(0x539, 1337),
          array(02471, 1337),
          array(1337, 1338, 1),
          array('1337', 1340, 5),
        );
    }

    public function assertEqualsFailsProvider()
    {
        return array(
          array(1337, 1338),
          array('1338', 1337),
          array(0x539, 1338),
          array(1337, 1339, 1),
          array('1337', 1340, 2),
        );
    }

    /**
     * @covers       ::accepts
     * @dataProvider acceptsSucceedsProvider
     */
    public function testAcceptsSucceeds($expected, $actual)
    {
        $this->assertTrue(
          $this->comparator->accepts($expected, $actual)
        );
    }

    /**
     * @covers       ::accepts
     * @dataProvider acceptsFailsProvider
     */
    public function testAcceptsFails($expected, $actual)
    {
        $this->assertFalse(
          $this->comparator->accepts($expected, $actual)
        );
    }

    /**
     * @covers       ::assertEquals
     * @dataProvider assertEqualsSucceedsProvider
     */
    public function testAssertEqualsSucceeds($expected, $actual, $delta = 0.0)
    {
        $exception = null;

        try {
            $this->comparator->assertEquals($expected, $actual, $delta);
        }

        catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    /**
     * @covers       ::assertEquals
     * @dataProvider assertEqualsFailsProvider
     */
    public function testAssertEqualsFails($expected, $actual, $delta = 0.0)
    {
        $this->setExpectedException(
          'SebastianBergmann\\Comparator\\ComparisonFailure', 'matches expected'
        );
        $this->comparator->assertEquals($expected, $actual, $delta);
    }
}
