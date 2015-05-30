<?php
/*
 * This file is part of the Comparator package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\Comparator;

use SplObjectStorage;
use stdClass;

/**
 * @coversDefaultClass SebastianBergmann\Comparator\SplObjectStorageComparator
 *
 * @package    Comparator
 * @author     Jeff Welch <whatthejeff@gmail.com>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.github.com/sebastianbergmann/comparator
 */
class SplObjectStorageComparatorTest extends \PHPUnit_Framework_TestCase
{
    private $comparator;

    protected function setUp()
    {
        $this->comparator = new SplObjectStorageComparator;
        $this->comparator->setFactory(new Factory);
    }

    public function acceptsFailsProvider()
    {
        return array(
          array(new SplObjectStorage, new stdClass),
          array(new stdClass, new SplObjectStorage),
          array(new stdClass, new stdClass)
        );
    }

    public function assertEqualsSucceedsProvider()
    {
        $object1 = new stdClass();
        $object2 = new stdClass();

        $storage1 = new SplObjectStorage();
        $storage2 = new SplObjectStorage();

        $storage3 = new SplObjectStorage();
        $storage3->attach($object1);
        $storage3->attach($object2);

        $storage4 = new SplObjectStorage();
        $storage4->attach($object2);
        $storage4->attach($object1);

        $storage5 = new SplObjectStorage();
        $storage5->attach($object1);

        $storage6 = new SplObjectStorage();
        $storage6->attach($object2);

        $storage7 = new SplObjectStorage();
        $storage7->attach($object1, 'data');

        $storage8 = new SplObjectStorage();
        $storage8->attach($object2, 'data');

        return array(
          array($storage1, $storage1),
          array($storage1, $storage2),
          array($storage3, $storage3),
          array($storage3, $storage4),
          array($storage5, $storage6),
          array($storage7, $storage8),
        );
    }

    public function assertEqualsFailsProvider()
    {
        $object1 = new stdClass;
        $object2 = new stdClass;

        $object3 = new stdClass;
        $object3->name = 'object3';

        $object4 = new stdClass;
        $object4->name = 'object4';

        $storage1 = new SplObjectStorage;

        $storage2 = new SplObjectStorage;
        $storage2->attach($object1);

        $storage3 = new SplObjectStorage;
        $storage3->attach($object2);
        $storage3->attach($object1);

        $storage4 = new SplObjectStorage;
        $storage4->attach($object3);

        $storage5 = new SplObjectStorage;
        $storage5->attach($object4);

        $storage6 = new SplObjectStorage;
        $storage6->attach($object1, 'data1');

        $storage7 = new SplObjectStorage;
        $storage7->attach($object1, 'data2');

        return array(
          array($storage1, $storage2),
          array($storage1, $storage3),
          array($storage2, $storage3),
          array($storage4, $storage5),
          array($storage6, $storage7),
        );
    }

    /**
     * @covers  ::accepts
     */
    public function testAcceptsSucceeds()
    {
        $this->assertTrue(
          $this->comparator->accepts(
            new SplObjectStorage,
            new SplObjectStorage
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
        $this->setExpectedException(
          'SebastianBergmann\\Comparator\\ComparisonFailure',
          'Failed asserting that two objects are equal.'
        );
        $this->comparator->assertEquals($expected, $actual);
    }
}
