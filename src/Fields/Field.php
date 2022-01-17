<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Fields;

use Illuminatech\DataProvider\FieldContract;

/**
 * Field
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Field implements FieldContract
{
    /**
     * @var string|array name of the attribute to select from source.
     */
    protected $attribute;

    public function __construct($attribute)
    {
        $this->attribute = $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(object $source, string $name): object
    {
        return $source->addSelect($this->attribute);
    }
}