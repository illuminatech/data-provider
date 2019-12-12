<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider;

/**
 * FilterContract
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
interface FilterContract
{
    public function apply(object $source, string $name, $value): object;
}
