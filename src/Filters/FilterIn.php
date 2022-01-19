<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Filters;

/**
 * FilterIn picks up records with attribute value inside requested set.
 *
 * Usage example:
 *
 * ```php
 * DataProvider(Item::class)
 *     ->filters([
 *         'categories' => new FilterIn('category_id'),
 *     ]);
 * ```
 *
 * Requested set can be specified either as comma-separated string or as array.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
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