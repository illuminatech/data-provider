<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider;

use Symfony\Component\HttpFoundation\Request;

/**
 * Sort
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Sort
{
    private $attributes = [];

    public $defaultOrder = [];

    public $sortParam = 'sort';

    /**
     * @var string the character used to separate different attributes that need to be sorted by.
     */
    public $separator = ',';

    public $enableMultiSort = false;

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
            if (! is_array($attribute)) {
                $attributes[$attribute] = [
                    'asc' => [$attribute => 'asc'],
                    'desc' => [$attribute => 'desc'],
                ];
            } elseif (! isset($attribute['asc'], $attribute['desc'])) {
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

    public function detectOrders($request)
    {
        $params = $request instanceof Request ? $request->query->all() : $request;

        $orders = [];

        if (isset($params[$this->sortParam])) {
            foreach ($this->parseSortParam($params[$this->sortParam]) as $attribute) {
                $descending = false;
                if (strncmp($attribute, '-', 1) === 0) {
                    $descending = true;
                    $attribute = substr($attribute, 1);
                }

                if (isset($this->attributes[$attribute])) {
                    $orders[$attribute] = $descending ? 'desc' : 'asc';
                    if (! $this->enableMultiSort) {
                        return $orders;
                    }
                }
            }
        }

        if (empty($orders) && is_array($this->defaultOrder)) {
            $orders = $this->defaultOrder;
        }

        return $orders;
    }

    /**
     * Parses the value of {@see sortParam} into an array of sort attributes.
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
     * @param string $param the value of the {@see sortParam}.
     * @return array the valid sort attributes.
     * @see $separator for the attribute name separator.
     * @see $sortParam
     */
    protected function parseSortParam($param)
    {
        return is_scalar($param) ? explode($this->separator, $param) : [];
    }
}
