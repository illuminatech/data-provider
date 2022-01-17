<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Fields;

use Illuminatech\DataProvider\FieldContract;

/**
 * FieldRelationAggregate
 *
 * @see \Illuminate\Database\Eloquent\Concerns\QueriesRelationships::withAggregate()
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class FieldRelationAggregate implements FieldContract
{
    /**
     * @var string name of the relation to be counted.
     */
    private $relation;
    /**
     * @var string
     */
    private $attribute;
    /**
     * @var string
     */
    private $function;

    public function __construct(string $relation, string $attribute, string $function)
    {
        $this->relation = $relation;
        $this->attribute = $attribute;
        $this->function = $function;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(object $source, string $name): object
    {
        return $source->withAggregate($this->relation, $this->attribute, $this->function);
    }
}