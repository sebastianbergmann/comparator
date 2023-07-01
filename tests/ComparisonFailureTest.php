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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComparisonFailure::class)]
#[UsesClass(Comparator::class)]
#[UsesClass(Factory::class)]
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
            $message
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

    public function testSerialize(): void
    {
        $failure = new ComparisonFailure(true, false, 'true', 'false', 'test');
        $serialised = $failure->__serialize();
        $this->assertSame([true, false, 'true', 'false'], $serialised);
    }

    public function testUnserialize(): void
    {
        $failure = new ComparisonFailure(true, false, 'true', 'false', 'test');
        $failure->__unserialize([true, false, 'true', 'false']);

        $this->assertTrue($failure->getExpected());
        $this->assertFalse($failure->getActual());
        $this->assertSame('true', $failure->getExpectedAsString());
        $this->assertSame('false', $failure->getActualAsString());
        $diff = '
--- Expected
+++ Actual
@@ @@
-true
+false
';
        $this->assertSame($diff, $failure->getDiff());
        $this->assertSame('test' . $diff, $failure->toString());
    }
}
