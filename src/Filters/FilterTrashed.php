<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Filters;

use Illuminatech\DataProvider\Exceptions\InvalidQueryException;
use Illuminatech\DataProvider\FilterContract;

/**
 * FilterTrashed provides filter for soft deleted (trashed) records.
 *
 * This filter responds to particular values:
 *
 * - 'with' - include 'trashed' records to the result set.
 * - 'only' - return only 'trashed' records at the result set.
 * - any other - return only records without 'trashed' at the result set.
 *
 * Usage example:
 *
 * ```php
 * DataProvider(Item::class)
 *     ->filters([
 *         'trashed' => new FilterTrashed,
 *         // ...
 *     ]);
 * ```
 *
 * @see \Illuminate\Database\Eloquent\SoftDeletes
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class FilterTrashed implements FilterContract
{
    /**
     * {@inheritdoc}
     */
    public function apply(object $source, string $name, $value): object
    {
        if (!is_scalar($value)) {
            throw new InvalidQueryException('Filter "' . $name . '" requires scalar value.');
        }

        if ($value === 'with') {
            $source->withTrashed();

            return $source;
        }

        if ($value === 'only') {
            $source->onlyTrashed();

            return $source;
        }

        $source->withoutTrashed();

        return $source;
    }
}