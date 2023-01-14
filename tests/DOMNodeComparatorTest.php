<?php declare(strict_types=1);
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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DOMNodeComparator::class)]
#[UsesClass(Comparator::class)]
#[UsesClass(ComparisonFailure::class)]
#[UsesClass(Factory::class)]
final class DOMNodeComparatorTest extends TestCase
{
    /**
     * @var DOMNodeComparator
     */
    private $comparator;

    public static function acceptsSucceedsProvider()
    {
        $document = new DOMDocument;
        $node     = new DOMNode;

        return [
            [$document, $document],
            [$node, $node],
            [$document, $node],
            [$node, $document],
        ];
    }

    public static function acceptsFailsProvider()
    {
        $document = new DOMDocument;

        return [
            [$document, null],
            [null, $document],
            [null, null],
        ];
    }

    public static function assertEqualsSucceedsProvider()
    {
        return [
            [
                self::createDOMDocument('<root></root>'),
                self::createDOMDocument('<root/>'),
            ],
            [
                self::createDOMDocument('<root attr="bar"></root>'),
                self::createDOMDocument('<root attr="bar"/>'),
            ],
            [
                self::createDOMDocument('<root><foo attr="bar"></foo></root>'),
                self::createDOMDocument('<root><foo attr="bar"/></root>'),
            ],
            [
                self::createDOMDocument("<root>\n  <child/>\n</root>"),
                self::createDOMDocument('<root><child/></root>'),
            ],
            [
                self::createDOMDocument('<Root></Root>'),
                self::createDOMDocument('<root></root>'),
                $ignoreCase = true,
            ],
            [
                self::createDOMDocument("<a x='' a=''/>"),
                self::createDOMDocument("<a a='' x=''/>"),
            ],
        ];
    }

    public static function assertEqualsFailsProvider()
    {
        return [
            [
                self::createDOMDocument('<root></root>'),
                self::createDOMDocument('<bar/>'),
            ],
            [
                self::createDOMDocument('<foo attr1="bar"/>'),
                self::createDOMDocument('<foo attr1="foobar"/>'),
            ],
            [
                self::createDOMDocument('<foo> bar </foo>'),
                self::createDOMDocument('<foo />'),
            ],
            [
                self::createDOMDocument('<foo xmlns="urn:myns:bar"/>'),
                self::createDOMDocument('<foo xmlns="urn:notmyns:bar"/>'),
            ],
            [
                self::createDOMDocument('<foo> bar </foo>'),
                self::createDOMDocument('<foo> bir </foo>'),
            ],
            [
                self::createDOMDocument('<Root></Root>'),
                self::createDOMDocument('<root></root>'),
            ],
            [
                self::createDOMDocument('<root> bar </root>'),
                self::createDOMDocument('<root> BAR </root>'),
            ],
        ];
    }

    protected function setUp(): void
    {
        $this->comparator = new DOMNodeComparator;
    }

    #[DataProvider('acceptsSucceedsProvider')]
    public function testAcceptsSucceeds($expected, $actual): void
    {
        $this->assertTrue(
            $this->comparator->accepts($expected, $actual)
        );
    }

    #[DataProvider('acceptsFailsProvider')]
    public function testAcceptsFails($expected, $actual): void
    {
        $this->assertFalse(
            $this->comparator->accepts($expected, $actual)
        );
    }

    #[DataProvider('assertEqualsSucceedsProvider')]
    public function testAssertEqualsSucceeds($expected, $actual, $ignoreCase = false): void
    {
        $exception = null;

        try {
            $delta        = 0.0;
            $canonicalize = false;
            $this->comparator->assertEquals($expected, $actual, $delta, $canonicalize, $ignoreCase);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    #[DataProvider('assertEqualsFailsProvider')]
    public function testAssertEqualsFails($expected, $actual): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage('Failed asserting that two DOM');

        $this->comparator->assertEquals($expected, $actual);
    }

    private static function createDOMDocument($content)
    {
        $document                     = new DOMDocument;
        $document->preserveWhiteSpace = false;
        $document->loadXML($content);

        return $document;
    }
}
