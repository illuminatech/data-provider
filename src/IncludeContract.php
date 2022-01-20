<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider;

/**
 * IncludeContract defines interface, which each relationship include should implement.
 *
 * @see \Illuminatech\DataProvider\DataProvider::includes()
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
interface IncludeContract
{
    /**
     * Applies this include to the given data source.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|object $source raw data source.
     * @param string $name relation full name (path).
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|object adjusted data source.
     */
    public function apply(object $source, string $name): object;
}