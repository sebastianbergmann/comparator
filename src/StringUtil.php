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

use function str_replace;
use function strlen;
use function substr;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class StringUtil
{
    private const OVERLONG_PREFIX_THRESHOLD = 40;
    private const KEEP_PREFIX_CHARS         = 15;

    /**
     * @return array{string, string}
     */
    public static function removeOverlongCommonPrefix(string $string1, string $string2): array
    {
        $commonPrefix = self::findCommonPrefix($string1, $string2);

        if (strlen($commonPrefix) > self::OVERLONG_PREFIX_THRESHOLD) {
            $string1 = str_replace(substr($commonPrefix, 0, -self::KEEP_PREFIX_CHARS), '...', $string1);
            $string2 = str_replace(substr($commonPrefix, 0, -self::KEEP_PREFIX_CHARS), '...', $string2);
        }

        return [$string1, $string2];
    }

    private static function findCommonPrefix(string $string1, string $string2): string
    {
        for ($i = 0; $i < strlen($string1); $i++) {
            if (!isset($string2[$i]) || $string1[$i] != $string2[$i]) {
                break;
            }
        }

        return substr($string1, 0, $i);
    }
}
