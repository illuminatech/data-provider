<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Includes;

use Illuminatech\DataProvider\IncludeContract;

/**
 * IncludeCallback
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class IncludeCallback implements IncludeContract
{
    /**
     * @var callable|null callback to be executed while including over the source instance.
     */
    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(object $source, string $name): object
    {
        $result = call_user_func($this->callback, $source, $name);

        return $result ?? $source;
    }
}