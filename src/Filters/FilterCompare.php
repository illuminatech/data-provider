<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Filters;

/**
 * FilterCompare performs attribute comparison using specified comparison operator like '<', '>', '<=', '>=' and so on.
 *
 * Usage example:
 *
 * ```php
 * DataProvider(Item::class)
 *     ->filters([
 *         'price_from' => new FilterCompare('price', '>='),
 *         'price_to' => new FilterCompare('price', '<='),
 *     ]);
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class FilterCompare extends FilterRelatedRecursive
{
    /**
     * @var string operator to be used for filter value comparison.
     * For example: '<', '>', '<=', '>=' and so on.
     */
    public $operator;

    public function __construct(string $target, string $operator)
    {
        $this->operator = $operator;

        parent::__construct($target);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyInternal(object $source, string $target, string $name, $value): object
    {
        return $source->where($target, $this->operator, $value);
    }
}