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
 * @coversDefaultClass SebastianBergmann\Comparator\SplObjectStorageComparator
 *
 * @package    Comparator
 * @author     Jeff Welch <whatthejeff@gmail.com>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.github.com/sebastianbergmann/comparator
 */
class SplObjectStorageComparatorTest extends \PHPUnit_Framework_TestCase
{
    private $comparator;

    protected function setUp()
    {
        $this->comparator = new SplObjectStorageComparator();
    }

    public function acceptsFailsProvider()
    {
        return array(
          array(new \SplObjectStorage, new \stdClass),
          array(new \stdClass, new \SplObjectStorage),
          array(new \stdClass, new \stdClass)
        );
    }

    public function assertEqualsSucceedsProvider()
    {
        $object1 = new \stdClass();
        $object2 = new \stdClass();

        $storage1 = new \SplObjectStorage();
        $storage2 = new \SplObjectStorage();

        $storage3 = new \SplObjectStorage();
        $storage3->attach($object1);
        $storage3->attach($object2);

        $storage4 = new \SplObjectStorage();
        $storage4->attach($object2);
        $storage4->attach($object1);

        return array(
          array($storage1, $storage1),
          array($storage1, $storage2),
          array($storage3, $storage3),
          array($storage3, $storage4)
        );
    }

    public function assertEqualsFailsProvider()
    {
        $object1 = new \stdClass();
        $object2 = new \stdClass();

        $storage1 = new \SplObjectStorage();

        $storage2 = new \SplObjectStorage();
        $storage2->attach($object1);

        $storage3 = new \SplObjectStorage();
        $storage3->attach($object2);
        $storage3->attach($object1);

        return array(
          array($storage1, $storage2),
          array($storage1, $storage3),
          array($storage2, $storage3),
        );
    }

    /**
     * @covers  ::accepts
     */
    public function testAcceptsSucceeds()
    {
        $this->assertTrue(
          $this->comparator->accepts(
            new \SplObjectStorage,
            new \SplObjectStorage
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
    public function testAssertEqualsSucceeds($expected, $actual)
    {
        $exception = null;

        try {
            $this->comparator->assertEquals($expected, $actual);
        }

        catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    /**
     * @covers       ::assertEquals
     * @dataProvider assertEqualsFailsProvider
     */
    public function testAssertEqualsFails($expected, $actual)
    {
        $this->setExpectedException('SebastianBergmann\\Comparator\\ComparisonFailure', 'Failed asserting that two objects are equal.');
        $this->comparator->assertEquals($expected, $actual);
    }
}
