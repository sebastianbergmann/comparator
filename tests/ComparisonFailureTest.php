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

use PHPUnit\Framework\TestCase;

/**
 * @covers SebastianBergmann\Comparator\ComparisonFailure
 */
final class ComparisonFailureTest extends TestCase
{
    public function testComparisonFailure()
    {
        $actual   = "\nB\n";
        $expected = "\nA\n";
        $message  = 'Test message';

        $failure = new ComparisonFailure(
            $expected,
            $actual,
            '|' . $expected,
            '|' . $actual,
            false,
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

    public function testDiffNotPossible()
    {
        $failure = new ComparisonFailure('a', 'b', false, false, true, 'test');
        $this->assertSame('', $failure->getDiff());
        $this->assertSame('test', $failure->toString());
    }
}
