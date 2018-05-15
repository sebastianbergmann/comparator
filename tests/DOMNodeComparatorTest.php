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
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass SebastianBergmann\Comparator\DOMNodeComparator
 *
 * @uses SebastianBergmann\Comparator\Comparator
 * @uses SebastianBergmann\Comparator\Factory
 * @uses SebastianBergmann\Comparator\ComparisonFailure
 */
class DOMNodeComparatorTest extends TestCase
{
    private $comparator;

    protected function setUp()
    {
        $this->comparator = new DOMNodeComparator;
    }

    public function acceptsSucceedsProvider()
    {
        $document = new DOMDocument;
        $node     = new DOMNode;

        return [
          [$document, $document],
          [$node, $node],
          [$document, $node],
          [$node, $document]
        ];
    }

    public function acceptsFailsProvider()
    {
        $document = new DOMDocument;

        return [
          [$document, null],
          [null, $document],
          [null, null]
        ];
    }

    public function assertEqualsSucceedsProvider()
    {
        return [
          [
            $this->createDOMDocument('<root></root>'),
            $this->createDOMDocument('<root/>')
          ],
          [
            $this->createDOMDocument('<root attr="bar"></root>'),
            $this->createDOMDocument('<root attr="bar"/>')
          ],
          [
            $this->createDOMDocument('<root><foo attr="bar"></foo></root>'),
            $this->createDOMDocument('<root><foo attr="bar"/></root>')
          ],
          [
            $this->createDOMDocument("<root>\n  <child/>\n</root>"),
            $this->createDOMDocument('<root><child/></root>')
          ],
          [
            $this->createDOMDocument("<a x='' a=''/>"),
            $this->createDOMDocument("<a a='' x=''/>"),
          ],
          [
            $this->createDOMDocument('<?xml version="1.0"?><foo>тест</foo>'),
            $this->createDOMDocument('<?xml version="1.0"?><foo>&#x442;&#x435;&#x441;&#x442;</foo>'),
          ],
          [
            $this->createDOMDocument('<?xml version="1.0"?><foo>тест</foo>'),
            $this->createDOMDocument('<?xml version="1.0" encoding="UTF-8"?><foo>тест</foo>'),
          ],
        ];
    }

    public function assertEqualsFailsProvider()
    {
        return [
          [
            $this->createDOMDocument('<root></root>'),
            $this->createDOMDocument('<bar/>')
          ],
          [
            $this->createDOMDocument('<foo attr1="bar"/>'),
            $this->createDOMDocument('<foo attr1="foobar"/>')
          ],
          [
            $this->createDOMDocument('<foo> bar </foo>'),
            $this->createDOMDocument('<foo />')
          ],
          [
            $this->createDOMDocument('<foo xmlns="urn:myns:bar"/>'),
            $this->createDOMDocument('<foo xmlns="urn:notmyns:bar"/>')
          ],
          [
            $this->createDOMDocument('<foo> bar </foo>'),
            $this->createDOMDocument('<foo> bir </foo>')
          ],
          [
            $this->createDOMDocument('<?xml version="1.0" encoding="UTF-8"?><foo>test</foo>'),
            $this->createDOMDocument('<?xml version="1.0" encoding="CP1251"?><foo>test</foo>'),
          ],
          [
            $this->createDOMDocument('<?xml version="1.0"?><foo>test</foo>'),
            $this->createDOMDocument('<?xml version="1.0" encoding="CP1251"?><foo>test</foo>'),
          ],
        ];
    }

    /**
     * @covers       ::accepts
     * @dataProvider acceptsSucceedsProvider
     *
     * @param mixed $expected
     * @param mixed $actual
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
     *
     * @param mixed $expected
     * @param mixed $actual
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
     *
     * @param mixed $expected
     * @param mixed $actual
     */
    public function testAssertEqualsSucceeds($expected, $actual)
    {
        $exception = null;

        try {
            $this->comparator->assertEquals($expected, $actual);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    /**
     * @covers       ::assertEquals
     * @dataProvider assertEqualsFailsProvider
     *
     * @param mixed $expected
     * @param mixed $actual
     */
    public function testAssertEqualsFails($expected, $actual)
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage('Failed asserting that two DOM');

        $this->comparator->assertEquals($expected, $actual);
    }

    /**
     * Ensures that non-latin text is not escaped
     */
    public function testNodeToTextNotEscaped() {
      $xml = "<root>тест</root>";
      $expected = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<root>тест</root>\n";
      $document = $this->createDOMDocument($xml);

      $this->assertSame($expected, $this->comparator->nodeToText($document, true));
    }

    private function createDOMDocument($content)
    {
        $document                     = new DOMDocument;
        $document->preserveWhiteSpace = false;
        $document->loadXML($content);

        return $document;
    }
}
