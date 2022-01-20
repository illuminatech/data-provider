<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider;

/**
 * FieldContract defines interface, which each field should implement.
 *
 * @see \Illuminatech\DataProvider\DataProvider::fields()
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
interface FieldContract
{
    /**
     * Applies this field to the given data source.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|object $source raw data source.
     * @param string $name field (attribute) name, e.g. field name from request.
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|object adjusted data source.
     */
    public function apply(object $source, string $name): object;
}
