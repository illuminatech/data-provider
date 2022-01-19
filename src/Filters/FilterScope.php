<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Filters;

/**
 * FilterScope applies specified scope method passing filter value inside it.
 *
 * Configuration example:
 *
 * ```php
 * DataProvider(Item::class)
 *     ->filters([
 *         'allow_purchase' => new FilterScope('allowPurchase'),
 *     ]);
 * ```
 *
 * Model example:
 *
 * ```php
 * class Item extends Model
 * {
 *     public function scopeAllowPurchase(Builder $query, bool $allowPurchase)
 *     {
 *         // ...
 *     }
 * }
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class FilterScope extends FilterRelatedRecursive
{
    /**
     * {@inheritdoc}
     */
    protected function applyInternal(object $source, string $target, string $name, $value): object
    {
        $source->{$this->target}($value);

        return $source;
    }
}