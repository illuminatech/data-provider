<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Filters;

use Illuminatech\DataProvider\FilterContract;

abstract class FilterRelatedRecursive implements FilterContract
{
    /**
     * @var string name of the target (attribute, scope and so on) to match filter value against.
     */
    public $target;

    public function __construct(string $target)
    {
        $this->target = $target;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(object $source, string $name, $value): object
    {
        if (strpos($this->target, '.') !== false && $this->shouldApplyRecursive($source)) {
            $targetPath = explode('.', $this->target);
            $target = array_pop($targetPath);
            $relation = implode('.', $targetPath);

            return $source->whereHas($relation, function ($internalSource) use ($target, $name, $value) {
                return $this->applyInternal($internalSource, $target, $name, $value);
            });
        }

        return $this->applyInternal($source, $this->target, $name, $value);
    }

    /**
     * Detects whether filter should be applied to related query or to the main source.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Support\Collection|object $source raw data source.
     * @return bool
     */
    protected function shouldApplyRecursive(object $source): bool
    {
        return method_exists($source, 'getModel');
    }

    /**
     * Applies this filter to the given data source.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Support\Collection|object $source raw data source.
     * @param string $target filter target (attribute, scope and so on) name.
     * @param string $name filter attribute name.
     * @param mixed $value filter value.
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Support\Collection|object adjusted data source.
     */
    abstract protected function applyInternal(object $source, string $target, string $name, $value): object;
}