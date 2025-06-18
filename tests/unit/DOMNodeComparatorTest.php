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
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DOMNodeComparator::class)]
#[UsesClass(Comparator::class)]
#[UsesClass(ComparisonFailure::class)]
#[UsesClass(Factory::class)]
#[Small]
final class DOMNodeComparatorTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: DOMDocument|DOMNode, 1: DOMDocument|DOMNode}>
     */
    public static function acceptsSucceedsProvider(): array
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

    /**
     * @return non-empty-list<array{0: ?DOMDocument, 1: ?DOMDocument}>
     */
    public static function acceptsFailsProvider(): array
    {
        $document = new DOMDocument;

        return [
            [$document, null],
            [null, $document],
            [null, null],
        ];
    }

    /**
     * @return non-empty-list<array{0: DOMDocument, 1: DOMDocument}>
     */
    public static function assertEqualsSucceedsProvider(): array
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
                true,
            ],
            [
                self::createDOMDocument("<a x='' a=''/>"),
                self::createDOMDocument("<a a='' x=''/>"),
            ],
        ];
    }

    /**
     * @return non-empty-list<array{0: DOMDocument, 1: DOMDocument}>
     */
    public static function assertEqualsFailsProvider(): array
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

    #[DataProvider('acceptsSucceedsProvider')]
    public function testAcceptsSucceeds(DOMDocument|DOMNode $expected, DOMDocument|DOMNode $actual): void
    {
        $this->assertTrue(
            (new DOMNodeComparator)->accepts($expected, $actual),
        );
    }

    #[DataProvider('acceptsFailsProvider')]
    public function testAcceptsFails(?DOMDocument $expected, ?DOMDocument $actual): void
    {
        $this->assertFalse(
            (new DOMNodeComparator)->accepts($expected, $actual),
        );
    }

    #[DataProvider('assertEqualsSucceedsProvider')]
    public function testAssertEqualsSucceeds(DOMDocument $expected, DOMDocument $actual, bool $ignoreCase = false): void
    {
        $exception = null;

        try {
            $delta        = 0.0;
            $canonicalize = false;
            (new DOMNodeComparator)->assertEquals($expected, $actual, $delta, $canonicalize, $ignoreCase);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    #[DataProvider('assertEqualsFailsProvider')]
    public function testAssertEqualsFails(DOMDocument $expected, DOMDocument $actual): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage('Failed asserting that two DOM');

        (new DOMNodeComparator)->assertEquals($expected, $actual);
    }

    /**
     * @param non-empty-string $content
     */
    private static function createDOMDocument(string $content): DOMDocument
    {
        $document                     = new DOMDocument;
        $document->preserveWhiteSpace = false;
        $document->loadXML($content);

        return $document;
    }
}
