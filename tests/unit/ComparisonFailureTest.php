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

use function substr_count;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesClassesThatExtendClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComparisonFailure::class)]
#[UsesClassesThatExtendClass(Comparator::class)]
#[UsesClass(Factory::class)]
#[Small]
final class ComparisonFailureTest extends TestCase
{
    public function testComparisonFailure(): void
    {
        $actual   = "\nB\n";
        $expected = "\nA\n";
        $message  = 'Test message';

        $failure = new ComparisonFailure(
            $expected,
            $actual,
            '|' . $expected,
            '|' . $actual,
            $message,
        );

        $this->assertSame($actual, $failure->getActual());
        $this->assertSame($expected, $failure->getExpected());
        $this->assertSame('|' . $actual, $failure->getActualAsString());
        $this->assertSame('|' . $expected, $failure->getExpectedAsString());

        $diff = '
--- Expected
+++ Actual
@@ @@
 |
-A
+B
';
        $this->assertSame($diff, $failure->getDiff());
        $this->assertSame($message . $diff, $failure->toString());
    }

    public function testDiffNotPossible(): void
    {
        $failure = new ComparisonFailure('a', 'b', '', '', 'test');
        $this->assertSame('', $failure->getDiff());
        $this->assertSame('test', $failure->toString());
    }

    public function testCustomContextLines(): void
    {
        $expected = "line1\nline2\nline3\nline4\nline5\nline6\nline7\nline8\n";
        $actual   = "line1\nline2\nline3\nline4\nLINE5\nline6\nline7\nline8\n";

        $failureDefault = new ComparisonFailure($expected, $actual, $expected, $actual);
        $failureOne     = new ComparisonFailure($expected, $actual, $expected, $actual, '', 1);

        $diffDefault = $failureDefault->getDiff();
        $diffOne     = $failureOne->getDiff();

        $this->assertStringContainsString('-line5', $diffDefault);
        $this->assertStringContainsString('+LINE5', $diffDefault);
        $this->assertStringContainsString('-line5', $diffOne);
        $this->assertStringContainsString('+LINE5', $diffOne);

        // With 1 context line, fewer surrounding lines should appear than with the default of 3
        $this->assertGreaterThan(
            substr_count($diffOne, "\n"),
            substr_count($diffDefault, "\n"),
        );
    }
}
