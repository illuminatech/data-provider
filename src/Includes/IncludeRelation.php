<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Includes;

use Illuminatech\DataProvider\IncludeContract;

/**
 * IncludeRelation
 *
 * @see \Illuminate\Database\Eloquent\Builder::with()
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class IncludeRelation implements IncludeContract
{
    /**
     * @var string|array name of the attribute to select from source.
     */
    protected $relation;

    /**
     * @var callable|null callback to be executed while loading relation.
     */
    protected $callback;

    public function __construct(string $relation, ?callable $callback = null)
    {
        $this->relation = $relation;
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(object $source, string $name): object
    {
        if ($this->callback !== null) {
            return $source->with([$this->relation => $this->callback]);
        }

        return $source->with($this->relation);
    }
}