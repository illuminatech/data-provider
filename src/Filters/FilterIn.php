<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Filters;

class FilterIn extends FilterRelatedRecursive
{
    /**
     * {@inheritdoc}
     */
    protected function applyInternal(object $source, string $target, string $name, $value): object
    {
        if (is_scalar($value)) {
            $value = array_map('trim', explode(',', $value));
        }

        return $source->whereIn($target, $value);
    }
}