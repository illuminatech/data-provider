<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Fields;

use Illuminatech\DataProvider\FieldContract;

/**
 * Field allows selection of simple attributes (columns).
 *
 * Usage example:
 *
 * ```php
 * DataProvider(Item::class)
 *     ->fields([
 *         'id', // short syntax, equals to `'id' => new Field('id')`
 *         'title' => 'name', // short syntax, equals to `'title' => new Field('name')`
 *         'name' => new Field('name'),
 *     ]);
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Field implements FieldContract
{
    /**
     * @var string|array name of the attribute to select from source.
     */
    public $attribute;

    /**
     * Constructor.
     *
     * @param string|array $attribute name of the attribute to select from source.
     */
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