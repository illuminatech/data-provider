<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Filters;

use Illuminatech\DataProvider\FilterContract;

/**
 * FilterCallback allows specification of the custom PHP callback for the filter application.
 *
 * Callback signature:
 *
 * ```
 * function(\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|object $source, string $filterName, mixed $filterValue)
 * ```
 *
 * ```php
 * DataProvider(Item::class)
 *     ->filters([
 *         'custom' => function ($query, $name, $value) {...}, // short syntax, equals to `'custom' => new FilterCallback(function ($query, $name, $value) {...})`
 *         'custom_price_from' => new FilterCallback(function ($query, $name, $value) {
 *              $query->where('type', '=', 'custom')
 *                  ->andWhere('price', '>=', $value);
 *          }),
 *     ]);
 * ```
 *
 * @package Illuminatech\DataProvider\Filters
 */
class FilterCallback implements FilterContract
{
    /**
     * @var callable PHP callback, which should be executed against the data source, once filter value is passed.
     */
    public $callback;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(object $source, string $name, $value): object
    {
        $result = call_user_func($this->callback, $source, $name, $value);

        return $result ?? $source;
    }
}
