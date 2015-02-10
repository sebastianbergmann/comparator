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

use SebastianBergmann\Exporter\Exporter;

/**
 * Abstract base class for comparators which compare values for equality.
 *
 * @package    Comparator
 * @subpackage Framework
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.github.com/sebastianbergmann/comparator
 */
abstract class Comparator implements ComparatorInterface
{
    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var Exporter
     */
    protected $exporter;

    public function __construct()
    {
        $this->exporter = new Exporter;
    }

    /**
     * @param FactoryInterface $factory
     */
    public function setFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }
}
