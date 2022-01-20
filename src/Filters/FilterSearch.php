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
 * FilterSearch picks up the records, with at least one of listed attribute contains a requested string.
 *
 * Usage example:
 *
 * ```php
 * DataProvider(Item::class)
 *     ->filters([
 *         'search' => ['name', 'description'], // short syntax, equals to `'search' => new FilterSearch(['name', 'description'])`
 *         'text_search' => new FilterSearch(['name', 'description']),
 *     ]);
 * ```
 *
 * @see \Illuminatech\DataProvider\Filters\FilterLike
 *
 * @package Illuminatech\DataProvider\Filters
 */
class FilterSearch implements FilterContract
{
    /**
     * @var string[]|array list of attributes to be searched against.
     */
    public $attributes = [];

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(object $source, string $name, $value): object
    {
        if (!is_scalar($value)) {
            throw new InvalidQueryException('Filter "' . $name . '" requires scalar value.');
        }

        $source->whereNested(function ($innerSource) use ($name, $value) {
            foreach ($this->attributes as $attribute) {
                $innerSource->orWhere(function ($src) use ($attribute, $name, $value) {
                    return (new FilterLike($attribute, true))->apply($src, $name, $value);
                });
            }
        });

        return $source;
    }
}