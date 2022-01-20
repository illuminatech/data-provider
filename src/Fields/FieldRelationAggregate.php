<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Fields;

use Illuminatech\DataProvider\FieldContract;

/**
 * FieldRelationAggregate allows selection of fields holding related entities aggregation info.
 *
 * Usage example:
 *
 * ```php
 * DataProvider(Category::class)
 *     ->fields([
 *         'items_count' => new FieldRelationAggregate('items', '*', 'count'),
 *         'items_max_price' => new FieldRelationAggregate('items', 'price', 'max'),
 *     ]);
 * ```
 *
 * @see \Illuminate\Database\Eloquent\Concerns\QueriesRelationships::withAggregate()
 * @see https://laravel.com/docs/eloquent-relationships#aggregating-related-models
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class FieldRelationAggregate implements FieldContract
{
    /**
     * @var string name of the relation to be counted.
     */
    public $relation;

    /**
     * @var string attribute of the related entity, which value should be aggregated using {@see function}
     */
    public $attribute;

    /**
     * @var string name of SQL aggregation function to be used for aggregation. For example: 'count', 'avg', 'min', 'max' and so on.
     */
    public $function;

    public function __construct(string $relation, string $attribute, string $function)
    {
        $this->relation = $relation;
        $this->attribute = $attribute;
        $this->function = $function;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(object $source, string $name): object
    {
        return $source->withAggregate($this->relation, $this->attribute, $this->function);
    }
}