<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider;

use Illuminatech\DataProvider\Exceptions\InvalidQueryException;

/**
 * Sort
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Sort
{
    /**
     * @var array the order that should be used when the processed request does not specify any order.
     * The array keys are attribute names and the array values are the corresponding sort directions. For example:
     *
     * ```php
     * [
     *     'name' => 'asc',
     *     'created_at' => 'desc',
     * ]
     * ```
     *
     * @see attributeOrders
     */
    public $defaultOrder = [];

    /**
     * @var bool whether the sorting can be applied to multiple attributes simultaneously.
     * Defaults to `false`, which means each time the data can only be sorted by one attribute.
     */
    public $enableMultiSort = false;

    /**
     * @var string the character used to separate different attributes that need to be sorted by.
     */
    public $separator = ',';

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param iterable $attributes
     * @return static self reference.
     */
    public function setAttributes(iterable $attributes): self
    {
        $this->attributes = $this->normalizeAttributes($attributes);

        return $this;
    }

    /**
     * Normalizes sort attributes definition.
     *
     * @param iterable $rawAttributes raw sort attributes definition.
     * @return array normalized sort attributes definition.
     */
    protected function normalizeAttributes(iterable $rawAttributes): array
    {
        $attributes = [];
        foreach ($rawAttributes as $name => $attribute) {
            if (!is_array($attribute)) {
                $attributes[$attribute] = [
                    'asc' => [$attribute => 'asc'],
                    'desc' => [$attribute => 'desc'],
                ];
            } elseif (!isset($attribute['asc'], $attribute['desc'])) {
                $attributes[$name] = array_merge([
                    'asc' => [$name => 'asc'],
                    'desc' => [$name => 'desc'],
                ], $attribute);
            } else {
                $attributes[$name] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Returns the requested sort information.
     *
     * @param array|string $rawSort requested raw sort value.
     * @return array sort directions in format: `[attribute => direction]`.
     */
    public function detectOrders($rawSort): array
    {
        $orders = [];

        if (empty($rawSort)) {
            return $this->defaultOrder;
        }

        $sorts = $this->parseSortParam($rawSort);
        if (!$this->enableMultiSort && count($sorts) > 1) {
            throw new InvalidQueryException('Sort by multiple fields is not supported.');
        }

        foreach ($sorts as $attribute) {
            $descending = false;
            if (strncmp($attribute, '-', 1) === 0) {
                $descending = true;
                $attribute = substr($attribute, 1);
            }

            if (isset($this->attributes[$attribute])) {
                $orders[$attribute] = $descending ? 'desc' : 'asc';
            }
        }

        if (empty($orders)) {
            throw new InvalidQueryException('Sort by '.implode($this->separator, $sorts).' is not supported.');
        }

        return $orders;
    }

    /**
     * Parses the value of sort specification into an array of sort attributes.
     *
     * The format must be the attribute name only for ascending
     * or the attribute name prefixed with `-` for descending.
     *
     * For example the following return value will result in ascending sort by
     * `category` and descending sort by `created_at`:
     *
     * ```php
     * [
     *     'category',
     *     '-created_at'
     * ]
     * ```
     *
     * @param array|string $param the raw sort value.
     * @return array the valid sort attributes.
     * @see $separator for the attribute name separator.
     */
    protected function parseSortParam($param): array
    {
        return is_array($param) ? $param : explode($this->separator, $param);
    }
}
