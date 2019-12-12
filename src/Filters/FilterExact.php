<?php

namespace Illuminatech\DataProvider\Filters;

use Illuminatech\DataProvider\FilterContract;

class FilterExact implements FilterContract
{
    /**
     * @var string name of the attribute to match filter value against.
     */
    protected $attribute;

    public function __construct(string $attribute)
    {
        $this->attribute = $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(object $source, string $name, $value): object
    {
        if (is_array($value)) {
            return $source->whereIn($this->attribute, $value);
        }

        return $source->where($this->attribute, '=', $value);
    }
}
