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
 * @coversDefaultClass SebastianBergmann\Comparator\MockObjectComparator
 *
 * @package    Comparator
 * @author     Jeff Welch <whatthejeff@gmail.com>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.github.com/sebastianbergmann/comparator
 */
class MockObjectComparatorTest extends \PHPUnit_Framework_TestCase
{
    private $comparator;

    protected function setUp()
    {
        $this->comparator = new MockObjectComparator;
        $this->comparator->setFactory(new Factory);
    }

    public function acceptsSucceedsProvider()
    {
        $testmock = $this->getMock('SebastianBergmann\\Comparator\\TestClass');
        $stdmock = $this->getMock('stdClass');

        return array(
          array($testmock, $testmock),
          array($stdmock, $stdmock),
          array($stdmock, $testmock)
        );
    }

    public function acceptsFailsProvider()
    {
        $stdmock = $this->getMock('stdClass');

        return array(
          array($stdmock, null),
          array(null, $stdmock),
          array(null, null)
        );
    }

    public function assertEqualsSucceedsProvider()
    {
        // cyclic dependencies
        $book1 = $this->getMock('SebastianBergmann\\Comparator\\Book', null);
        $book1->author = $this->getMock('SebastianBergmann\\Comparator\\Author', null, array('Terry Pratchett'));
        $book1->author->books[] = $book1;
        $book2 = $this->getMock('SebastianBergmann\\Comparator\\Book', null);
        $book2->author = $this->getMock('SebastianBergmann\\Comparator\\Author', null, array('Terry Pratchett'));
        $book2->author->books[] = $book2;

        $object1 = $this->getMock('SebastianBergmann\\Comparator\\SampleClass', null, array(4, 8, 15));
        $object2 = $this->getMock('SebastianBergmann\\Comparator\\SampleClass', null, array(4, 8, 15));

        return array(
          array($object1, $object1),
          array($object1, $object2),
          array($book1, $book1),
          array($book1, $book2),
          array(
            $this->getMock('SebastianBergmann\\Comparator\\Struct', null, array(2.3)),
            $this->getMock('SebastianBergmann\\Comparator\\Struct', null, array(2.5)),
            0.5
          )
        );
    }

    public function assertEqualsFailsProvider()
    {
        $typeMessage = 'is not instance of expected class';
        $equalMessage = 'Failed asserting that two objects are equal.';

        // cyclic dependencies
        $book1 = $this->getMock('SebastianBergmann\\Comparator\\Book', null);
        $book1->author = $this->getMock('SebastianBergmann\\Comparator\\Author', null, array('Terry Pratchett'));
        $book1->author->books[] = $book1;
        $book2 = $this->getMock('SebastianBergmann\\Comparator\\Book', null);
        $book2->author = $this->getMock('SebastianBergmann\\Comparator\\Author', null, array('Terry Pratch'));
        $book2->author->books[] = $book2;

        $book3 = $this->getMock('SebastianBergmann\\Comparator\\Book', null);
        $book3->author = 'Terry Pratchett';
        $book4 = $this->getMock('stdClass');
        $book4->author = 'Terry Pratchett';

        $object1 = $this->getMock('SebastianBergmann\\Comparator\\SampleClass', null, array(4, 8, 15));
        $object2 = $this->getMock('SebastianBergmann\\Comparator\\SampleClass', null, array(16, 23, 42));

        return array(
          array(
            $this->getMock('SebastianBergmann\\Comparator\\SampleClass', null, array(4, 8, 15)),
            $this->getMock('SebastianBergmann\\Comparator\\SampleClass', null, array(16, 23, 42)),
            $equalMessage
          ),
          array($object1, $object2, $equalMessage),
          array($book1, $book2, $equalMessage),
          array($book3, $book4, $typeMessage),
          array(
            $this->getMock('SebastianBergmann\\Comparator\\Struct', null, array(2.3)),
            $this->getMock('SebastianBergmann\\Comparator\\Struct', null, array(4.2)),
            $equalMessage,
            0.5
          )
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
    public function testAssertEqualsFails($expected, $actual, $message, $delta = 0.0)
    {
        $this->setExpectedException(
          'SebastianBergmann\\Comparator\\ComparisonFailure', $message
        );
        $this->comparator->assertEquals($expected, $actual, $delta);
    }
}
