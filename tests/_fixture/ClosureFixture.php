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

use Closure;

final class ClosureFixture
{
    public static function staticNoCapture(): Closure
    {
        return static fn (): int => 1;
    }

    /**
     * @return Closure(): int
     */
    public static function staticCapturingInt(int $captured): Closure
    {
        return static fn (): int => $captured;
    }

    /**
     * @return Closure(): array{0: int, 1: string}
     */
    public static function staticCapturingTwo(int $a, string $b): Closure
    {
        return static fn (): array => [$a, $b];
    }

    /**
     * @return Closure(): mixed
     */
    public static function staticCapturingMixed(mixed $captured): Closure
    {
        return static fn (): mixed => $captured;
    }

    public static function staticAlternativeNoCapture(): Closure
    {
        return static fn (): int => 1;
    }

    public function __construct(public int $value = 0)
    {
    }

    public function nonStaticReturningOne(): Closure
    {
        return function (): int
        {
            return $this->value;
        };
    }
}
