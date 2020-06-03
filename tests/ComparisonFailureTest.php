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

use PHPUnit\Framework\TestCase;

/**
 * @covers \SebastianBergmann\Comparator\ComparisonFailure
 *
 * @uses \SebastianBergmann\Comparator\Factory
 */
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

    public function testDiffNotPossible(): void
    {
        $failure = new ComparisonFailure('a', 'b', false, false, true, 'test');
        $this->assertSame('', $failure->getDiff());
        $this->assertSame('test', $failure->toString());
    }

    public function testSerializesWithoutStackTrace()
    {
        $instance = new NonSerializableClass;

        $failure = $this->functionWithNonSerializableParam($instance);

        $serialised = serialize($failure);
        $deserialised = unserialize($serialised);

        $this->assertSame($failure->getActual(), $deserialised->getActual());
        $this->assertSame($failure->getExpected(), $deserialised->getExpected());
        $this->assertSame($failure->getActualAsString(), $deserialised->getActualAsString());
        $this->assertSame($failure->getExpectedAsString(), $deserialised->getExpectedAsString());
        $this->assertSame($failure->toString(), $deserialised->toString());
    }

    private function functionWithNonSerializableParam(NonSerializableClass $instance) {
        return new ComparisonFailure('a', 'b', 'a', 'b', false, 'test');
    }
}
