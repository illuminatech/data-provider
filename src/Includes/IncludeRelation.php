<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Includes;

use Illuminatech\DataProvider\IncludeContract;

/**
 * IncludeRelation allows eager loading of the relate specified via request parameters.
 *
 * Usage example:
 *
 * ```php
 * DataProvider(Item::class)
 *     ->includes([
 *         'category', // short syntax, equals to `'category' => new IncludeRelation('category')`,
 *         'published_comments' => new IncludeRelation('comments', function ($commentsQuery) {
 *              $commentsQuery->where('status', '=', 'published');
 *          }),
 *     ]);
 * ```
 *
 * @see \Illuminate\Database\Eloquent\Builder::with()
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class IncludeRelation implements IncludeContract
{
    /**
     * @var string|array name of the relation to be eager loaded.
     */
    public $relation;

    /**
     * @var callable|null callback to be executed while loading relation.
     */
    public $callback;

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