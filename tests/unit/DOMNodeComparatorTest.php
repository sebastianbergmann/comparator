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
use PHPUnit\Framework\Attributes\UsesClassesThatExtendClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DOMNodeComparator::class)]
#[UsesClassesThatExtendClass(Comparator::class)]
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
            [
                self::createDOMDocument('<root>Тест кириллицы</root>'),
                self::createDOMDocument('<root>Тест кириллицы</root>'),
            ],
            [
                self::createDOMDocument('<root>Тест кириллицы</root>'),
                self::createDOMDocument('<root>&#x422;&#x435;&#x441;&#x442; &#x43A;&#x438;&#x440;&#x438;&#x43B;&#x43B;&#x438;&#x446;&#x44B;</root>'),
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

    /**
     * @return non-empty-array<string, array{0: DOMDocument|DOMNode, 1: DOMDocument|DOMNode}>
     */
    public static function assertEqualsSucceedsWhenCanonicalizationFailsProvider(): array
    {
        $entityXml = '<!DOCTYPE foo SYSTEM "foo.dtd" [<!ENTITY ns_foo "http://uri.tld/foo">]><i:foo xmlns:i="&ns_foo;"/>';

        $emptyDocumentA = new DOMDocument;
        $emptyDocumentB = new DOMDocument;

        $unattachedOwnerA = new DOMDocument;
        $unattachedOwnerB = new DOMDocument;

        return [
            'two empty documents'             => [$emptyDocumentA, $emptyDocumentB],
            'two equivalent unattached nodes' => [
                $unattachedOwnerA->createElement('foo'),
                $unattachedOwnerB->createElement('foo'),
            ],
            'two documents with entity namespace' => [
                self::createDOMDocument($entityXml),
                self::createDOMDocument($entityXml),
            ],
        ];
    }

    /**
     * @return non-empty-array<string, array{0: DOMDocument|DOMNode, 1: DOMDocument|DOMNode}>
     */
    public static function assertEqualsFailsWhenCanonicalizationFailsProvider(): array
    {
        $entityXmlFoo = '<!DOCTYPE foo SYSTEM "foo.dtd" [<!ENTITY ns_foo "http://uri.tld/foo">]><i:foo xmlns:i="&ns_foo;"/>';
        $entityXmlBar = '<!DOCTYPE foo SYSTEM "foo.dtd" [<!ENTITY ns_foo "http://uri.tld/foo">]><i:bar xmlns:i="&ns_foo;"/>';

        $owner = new DOMDocument;

        return [
            'differing unattached nodes' => [
                $owner->createElement('foo'),
                $owner->createElement('bar'),
            ],
            'differing entity-namespace documents' => [
                self::createDOMDocument($entityXmlFoo),
                self::createDOMDocument($entityXmlBar),
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

    #[DataProvider('assertEqualsSucceedsWhenCanonicalizationFailsProvider')]
    public function testAssertEqualsSucceedsWhenCanonicalizationFails(DOMDocument|DOMNode $expected, DOMDocument|DOMNode $actual): void
    {
        $exception = null;

        try {
            (new DOMNodeComparator)->assertEquals($expected, $actual);
        } catch (ComparisonFailure $exception) {
        }

        $this->assertNull($exception, 'Unexpected ComparisonFailure');
    }

    #[DataProvider('assertEqualsFailsWhenCanonicalizationFailsProvider')]
    public function testAssertEqualsFailsWhenCanonicalizationFails(DOMDocument|DOMNode $expected, DOMDocument|DOMNode $actual): void
    {
        $this->expectException(ComparisonFailure::class);
        $this->expectExceptionMessage('Failed asserting that two DOM');

        (new DOMNodeComparator)->assertEquals($expected, $actual);
    }

    public function testFailingComparisonOfNonAsciiContentProducesReadableDiff(): void
    {
        try {
            (new DOMNodeComparator)->assertEquals(
                self::createDOMDocument('<root>Привет</root>'),
                self::createDOMDocument('<root>Прощай</root>'),
            );
        } catch (ComparisonFailure $failure) {
            $this->assertStringContainsString('Привет', $failure->getExpectedAsString());
            $this->assertStringContainsString('Прощай', $failure->getActualAsString());
            $this->assertStringNotContainsString('&#x', $failure->getExpectedAsString());
            $this->assertStringNotContainsString('&#x', $failure->getActualAsString());

            return;
        }

        $this->fail('Expected ComparisonFailure was not thrown');
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
