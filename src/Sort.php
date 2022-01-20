<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider;

use Illuminatech\DataProvider\Exceptions\InvalidQueryException;

/**
 * Sort handles ordering of the data source according to the specified request parameters.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Sort
{
    /**
     * @var string keyword, which should be used to get sort orders from the request data.
     */
    public $keyword = 'sort';

    /**
     * @var array|string the order that should be used when the processed request does not specify any order.
     * Format should match the one passed from request, for example: '-id'.
     */
    public $defaultSort = [];

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
     * @var array list of attributes that are allowed to be sorted. Its syntax can be
     * described using the following example:
     *
     * ```php
     * [
     *     'id',
     *     'name' => [
     *         'asc' => ['first_name' => 'asc', 'last_name' => 'asc'],
     *         'desc' => ['first_name' => 'desc', 'last_name' => 'desc'],
     *     ],
     * ]
     * ```
     *
     * In the above, the `id` attribute is a simple attribute which is equivalent to the following:
     *
     * ```php
     * 'id' => [
     *     'asc' => ['id' => 'asc'],
     *     'desc' => ['id' => 'desc'],
     * ]
     * ```
     */
    private $attributes = [];

    /**
     * Constructor.
     *
     * @param array $config configuration.
     */
    public function __construct(array $config = [])
    {
        $this->keyword = $config['keyword'] ?? $this->keyword;
        $this->enableMultiSort = $config['enable_multisort'] ?? $this->enableMultiSort;
        $this->separator = $config['separator'] ?? $this->separator;
    }

    /**
     * Sets the list of attributes that are allowed to be sorted.
     * For example:
     *
     * ```php
     * [
     *     'id',
     *     'name' => [
     *         'asc' => ['first_name' => 'asc', 'last_name' => 'asc'],
     *         'desc' => ['first_name' => 'desc', 'last_name' => desc'],
     *     ],
     * ]
     * ```
     *
     * @return array order attributes.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param iterable $attributes raw attributes definition.
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
     * Applies this sort to the given source according to the specified request params.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $source data source.
     * @param array $params request parameters.
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder adjusted data source.
     */
    public function apply(object $source, $params): object
    {
        foreach ($this->detectOrders($params[$this->keyword] ?? null) as $column => $direction) {
            $source->orderBy($column, $direction);
        }

        return $source;
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
            $rawSort = $this->defaultSort;
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
            throw new InvalidQueryException('Sort by ' . implode($this->separator, $sorts) . ' is not supported.');
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
