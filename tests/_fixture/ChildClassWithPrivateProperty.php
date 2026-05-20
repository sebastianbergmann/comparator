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

final class ChildClassWithPrivateProperty extends ParentClassWithPrivateProperty
{
    /**
     * @phpstan-ignore property.onlyWritten
     */
    private string $property;

    public function __construct(string $parentProperty, string $childProperty)
    {
        parent::__construct($parentProperty);

        $this->property = $childProperty;
    }
}
