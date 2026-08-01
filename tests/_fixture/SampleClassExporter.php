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

use function assert;
use SebastianBergmann\Exporter\ExportContext;
use SebastianBergmann\Exporter\Exporter;
use SebastianBergmann\Exporter\ObjectExporter;

final readonly class SampleClassExporter implements ObjectExporter
{
    public function handles(object $object): bool
    {
        return $object instanceof SampleClass;
    }

    public function export(object $object, Exporter $exporter, int $indentation, ExportContext $context): string
    {
        assert($object instanceof SampleClass);

        return 'SampleClass(' . $exporter->export($object->a, $indentation, $context) . ')';
    }
}
