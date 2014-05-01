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

use DateTime;
use DateTimeZone;

/**
 * @coversDefaultClass SebastianBergmann\Comparator\DateTimeComparator
 *
 * @package    Comparator
 * @author     Jeff Welch <whatthejeff@gmail.com>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.github.com/sebastianbergmann/comparator
 */
class DateTimeComparatorTest extends \PHPUnit_Framework_TestCase
{
    private $comparator;

    protected function setUp()
    {
        $this->comparator = new DateTimeComparator;
    }

    public function acceptsFailsProvider()
    {
        $datetime = new DateTime;

        return array(
          array($datetime, null),
          array(null, $datetime),
          array(null, null)
        );
    }

    public function assertEqualsSucceedsProvider()
    {
        return array(
          array(
            new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
            new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York'))
          ),
          array(
            new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
            new DateTime('2013-03-29 04:13:25', new DateTimeZone('America/New_York')),
            10
          ),
          array(
            new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
            new DateTime('2013-03-29 04:14:40', new DateTimeZone('America/New_York')),
            65
          ),
          array(
            new DateTime('2013-03-29', new DateTimeZone('America/New_York')),
            new DateTime('2013-03-29', new DateTimeZone('America/New_York'))
          ),
          array(
            new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
            new DateTime('2013-03-29 03:13:35', new DateTimeZone('America/Chicago'))
          ),
          array(
            new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
            new DateTime('2013-03-29 03:13:49', new DateTimeZone('America/Chicago')),
            15
          ),
          array(
            new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
            new DateTime('2013-03-29 23:00:00', new DateTimeZone('America/Chicago'))
          ),
          array(
            new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
            new DateTime('2013-03-29 23:01:30', new DateTimeZone('America/Chicago')),
            100
          ),
          array(
            new DateTime('@1364616000'),
            new DateTime('2013-03-29 23:00:00', new DateTimeZone('America/Chicago'))
          ),
          array(
            new DateTime('2013-03-29T05:13:35-0500'),
            new DateTime('2013-03-29T04:13:35-0600')
          )
        );
    }

    public function assertEqualsFailsProvider()
    {
        return array(
          array(
            new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
            new DateTime('2013-03-29 03:13:35', new DateTimeZone('America/New_York'))
          ),
          array(
            new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
            new DateTime('2013-03-29 03:13:35', new DateTimeZone('America/New_York')),
            3500
          ),
          array(
            new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
            new DateTime('2013-03-29 05:13:35', new DateTimeZone('America/New_York')),
            3500
          ),
          array(
            new DateTime('2013-03-29', new DateTimeZone('America/New_York')),
            new DateTime('2013-03-30', new DateTimeZone('America/New_York'))
          ),
          array(
            new DateTime('2013-03-29', new DateTimeZone('America/New_York')),
            new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
            43200
          ),
          array(
            new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
            new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/Chicago')),
          ),
          array(
            new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
            new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/Chicago')),
            3500
          ),
          array(
            new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
            new DateTime('2013-03-30', new DateTimeZone('America/Chicago'))
          ),
          array(
            new DateTime('2013-03-29T05:13:35-0600'),
            new DateTime('2013-03-29T04:13:35-0600')
          ),
          array(
            new DateTime('2013-03-29T05:13:35-0600'),
            new DateTime('2013-03-29T05:13:35-0500')
          ),
        );
    }

    /**
     * @covers  ::accepts
     */
    public function testAcceptsSucceeds()
    {
        $this->assertTrue(
          $this->comparator->accepts(
            new DateTime,
            new DateTime
          )
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
          'SebastianBergmann\\Comparator\\ComparisonFailure',
          'Failed asserting that two DateTime objects are equal.'
        );
        $this->comparator->assertEquals($expected, $actual, $delta);
    }
}
