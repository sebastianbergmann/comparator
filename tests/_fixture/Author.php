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

/**
 * An author.
 */
class Author
{
    // the order of properties is important for testing the cycle!
    public array $books = [];
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
