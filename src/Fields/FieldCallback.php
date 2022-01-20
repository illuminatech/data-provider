<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Fields;

use Illuminatech\DataProvider\FieldContract;

/**
 * FieldCallback allows specification of the custom PHP callback for the field application.
 *
 * Callback signature:
 *
 * ```
 * function(\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|object $source, string $fieldName)
 * ```
 *
 * Usage example:
 *
 * ```php
 * DataProvider(Tour::class)
 *     ->fields([
 *         'custom' => function ($query) {...}, // short syntax, equals to `'custom' => new FieldCallback(function ($query) {...})`
 *         'price_per_day' => new FieldCallback(function ($query) {
 *             $query->addSelect(new Expression('price / days_count as price_per_day'));
 *         }),
 *     ]);
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class FieldCallback implements FieldContract
{
    /**
     * @var callable PHP callback, which should be executed against the data source, once field selection requested.
     */
    public $callback;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(object $source, string $name): object
    {
        $result = call_user_func($this->callback, $source, $name);

        return $result ?? $source;
    }
}