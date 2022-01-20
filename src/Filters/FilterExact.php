<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Filters;

use Illuminatech\DataProvider\Exceptions\InvalidQueryException;

/**
 * FilterExact performs exact match for the attribute against requested value.
 *
 * Usage example:
 *
 * ```php
 * DataProvider(Item::class)
 *     ->filters([
 *         'id', // short syntax, equals to `'id' => new FilterExact('id')`,
 *         'title' => new FilterExact('name'),
 *     ]);
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class FilterExact extends FilterRelatedRecursive
{
    /**
     * {@inheritdoc}
     */
    protected function applyInternal(object $source, string $target, string $name, $value): object
    {
        if (!is_scalar($value)) {
            throw new InvalidQueryException('Filter "' . $name . '" requires scalar value.');
        }

        return $source->where($target, '=', $value);
    }
}
