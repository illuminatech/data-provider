<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider;

use Illuminatech\DataProvider\Filters\FilterCallback;
use Illuminatech\DataProvider\Filters\FilterExact;
use Symfony\Component\HttpFoundation\Request;

/**
 * DataProvider
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class DataProvider
{
    /**
     * @var \Illuminate\Database\Query\Builder|\Illuminate\Support\Collection|object data source.
     */
    public $source;

    /**
     * @var array instance config.
     */
    public $config = [];

    /**
     * @var \Illuminatech\DataProvider\FilterContract[]|array
     */
    protected $filters = [];

    /**
     * @var \Illuminatech\DataProvider\Sort|null related sort instance.
     */
    protected $sort;

    public function __construct($source)
    {
        if (is_object($source)) {
            $this->source = $source;
        } elseif (is_string($source)) {
            $this->source = $source::query();
        } else {
            throw new \InvalidArgumentException("Unsupported source type: ".gettype($source));
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request|iterable $request request instance or query data.
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Support\Collection|object adjusted data source.
     */
    public function prepare($request): object
    {
        $params = $request instanceof Request ? $request->query->all() : $request;

        // apply filter
        if (isset($params['filter'])) {
            foreach ($params['filter'] as $name => $value) {
                $this->filters[$name]->apply($this->source, $name, $value);
            }
        }

        // apply sort
        if ($this->sort !== null) {
            foreach ($this->sort->detectOrders($params) as $column => $direction) {
                $this->source->orderBy($column, $direction);
            }
        }

        return $this->source;
    }

    public function setSort($sort): self
    {
        if (! $sort instanceof Sort) {
            $sort = (new Sort())
                ->setAttributes($sort);
        }

        $this->sort = $sort;

        return $this;
    }

    /**
     * @return \Illuminatech\DataProvider\FilterContract[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param \Illuminatech\DataProvider\FilterContract[]|array $filters
     * @return static self reference.
     */
    public function setFilters($filters): self
    {
        $this->filters = $this->normalizeFilters($filters);

        return $this;
    }

    protected function normalizeFilters($rawFilters): array
    {
        $filters = [];
        foreach ($rawFilters as $name => $rawFilter) {
            if ($rawFilter instanceof FilterContract) {
                $filters[$name] = $rawFilter;
                continue;
            }

            if ($rawFilter instanceof \Closure) {
                $filters[$name] = new FilterCallback($rawFilter);
                continue;
            }

            if (is_int($name) && is_scalar($rawFilter)) {
                $filters[$rawFilter] = new FilterExact($rawFilter);
                continue;
            }

            throw new \InvalidArgumentException('Unsupported filter specification: '.gettype($name).' => '.gettype($rawFilter));
        }

        return $filters;
    }
}
