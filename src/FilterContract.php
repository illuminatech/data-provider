<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider;

/**
 * FilterContract defines interface, which each data filter should implement.
 *
 * @see \Illuminatech\DataProvider\DataProvider::filters()
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
interface FilterContract
{
    /**
     * Applies this filter to the given data source.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|object $source raw data source.
     * @param string $name filter attribute name, e.g. filter name from request.
     * @param mixed $value filter value.
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|object adjusted data source.
     */
    public function apply(object $source, string $name, $value): object;
}
