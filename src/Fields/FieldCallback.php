<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Fields;

use Illuminatech\DataProvider\FieldContract;

/**
 * FieldCallback
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class FieldCallback implements FieldContract
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
    public function apply(object $source, string $name): object
    {
        $result = call_user_func($this->callback, $source, $name);

        return $result ?? $source;
    }
}