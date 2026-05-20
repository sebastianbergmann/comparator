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

use Error;

final class NonSerializableClass
{
    public function __serialize(): array
    {
        throw new Error('This class cannot be serialized');
    }

    public function __unserialize(array $data): void
    {
    }
}
