<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Filters;

use Illuminatech\DataProvider\FilterContract;

class FilterCallback implements FilterContract
{
    /**
     * @var callable
     */
    protected $callback;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(object $source, string $name, $value): object
    {
        $result = call_user_func($this->callback, $source, $name, $value);

        return $result ?? $source;
    }
}
