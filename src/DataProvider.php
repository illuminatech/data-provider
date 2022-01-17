<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider;

use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Illuminatech\DataProvider\Exceptions\InvalidQueryException;
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
     * @var \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Support\Collection|object data source.
     */
    protected $source;

    /**
     * @var array this instance config.
     */
    protected $config = [];

    /**
     * @var \Illuminatech\DataProvider\FilterContract[] list of filters indexed by request param name.
     */
    protected $filters = [];

    /**
     * @var \Illuminatech\DataProvider\Sort|null related sort instance.
     */
    protected $sort;

    /**
     * @var \Illuminatech\DataProvider\Pagination|null related pagination instance.
     */
    protected $pagination;

    /**
     * Constructor.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Support\Collection|array|object|string $source data source.
     * @param array $config
     */
    public function __construct($source, array $config = [])
    {
        if (is_object($source)) {
            $this->source = $source;
        } elseif (is_string($source)) {
            $this->source = $source::query();
        } elseif (is_array($source)) {
            $this->source = new Collection($source);
        } else {
            throw new \InvalidArgumentException('Unsupported source type: ' . gettype($source));
        }

        $this->config = array_replace_recursive(
            require __DIR__ . '/../config/data_provider.php',
            Container::getInstance()->has('config') ? Container::getInstance()->get('config')->get('data_provider') : [],
            $config
        );
    }

    /**
     * Applies given request to the {@see source}, applying filters, sort and so on to it.
     * This method is immutable, leaving original {@see source} object intact.
     *
     * @param \Symfony\Component\HttpFoundation\Request|iterable $request request instance or query data.
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Support\Collection|object adjusted data source.
     */
    public function prepare($request): object
    {
        $source = clone $this->source;

        $params = $this->extractRequestParams($request);

        // apply filter
        $filterKeyword = $this->config['filter']['keyword'];

        if (isset($params[$filterKeyword])) {
            foreach ($params[$filterKeyword] as $name => $value) {
                if (!isset($this->filters[$name])) {
                    throw new InvalidQueryException('Filter "' . $name . '" is not supported.');
                }

                $this->filters[$name]->apply($source, $name, $value);
            }
        }

        // apply sort
        $sortKeyword = $this->config['sort']['keyword'];
        if ($this->sort !== null) {
            foreach ($this->sort->detectOrders($params[$sortKeyword] ?? null) as $column => $direction) {
                $source->orderBy($column, $direction);
            }
        }

        return $source;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request|iterable $request request instance or query data.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator paginator instance.
     */
    public function paginate($request): object
    {
        $params = $this->extractRequestParams($request);

        $source = $this->prepare($params);

        return $this->getPagination()
            ->paginate($source, $params);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request|iterable $request request instance or query data.
     * @return \Illuminate\Contracts\Pagination\Paginator paginator instance.
     */
    public function simplePaginate($request): object
    {
        $params = $this->extractRequestParams($request);

        $source = $this->prepare($params);

        return $this->getPagination()
            ->simplePaginate($source, $params);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request|iterable $request request instance or query data.
     * @return \Illuminate\Contracts\Pagination\CursorPaginator paginator instance.
     */
    public function cursorPaginate($request): object
    {
        $params = $this->extractRequestParams($request);

        $source = $this->prepare($params);

        return $this->getPagination()
            ->cursorPaginate($source, $params);
    }

    public function setSort($sort): self
    {
        if (!$sort instanceof Sort) {
            $sort = $this->makeSort()
                ->setAttributes($sort);
        }

        $this->sort = $sort;

        return $this;
    }

    public function getSort(): ?Sort
    {
        if ($this->sort === null) {
            $this->sort = $this->makeSort();
        }

        return $this->sort;
    }

    /**
     * Creates default sort instance.
     *
     * @return \Illuminatech\DataProvider\Sort
     */
    protected function makeSort(): Sort
    {
        $sort = new Sort();
        $sort->enableMultiSort = $this->config['sort']['enable_multisort'];

        return $sort;
    }

    public function setPagination(Pagination $pagination): self
    {
        $this->pagination = $pagination;

        return $this;
    }

    public function getPagination(): Pagination
    {
        if ($this->pagination === null) {
            $this->pagination = $this->makePagination();
        }

        return $this->pagination;
    }

    /**
     * Creates default pagination instance.
     *
     * @return \Illuminatech\DataProvider\Pagination
     */
    protected function makePagination(): Pagination
    {
        return new Pagination($this->config['pagination']);
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
    public function setFilters(iterable $filters): self
    {
        $this->filters = $this->normalizeFilters($filters);

        return $this;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request|iterable $request request instance or query data.
     * @return array|iterable request params.
     */
    protected function extractRequestParams($request)
    {
        return $request instanceof Request ? $request->query->all() : $request;
    }

    /**
     * Normalizes filters definition.
     *
     * @param iterable $rawFilters raw filters list.
     * @return \Illuminatech\DataProvider\FilterContract[] filter instances indexed by request param name.
     */
    protected function normalizeFilters(iterable $rawFilters): array
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

            // @todo search filter

            throw new \InvalidArgumentException('Unsupported filter specification: ' . gettype($name) . ' => ' . gettype($rawFilter));
        }

        return $filters;
    }

    // Fluent interface :

    public function filters(iterable $filters): self
    {
        return $this->setFilters($filters);
    }

    public function sortFields(iterable $fields): self
    {
        $this->getSort()->setAttributes($fields);

        return $this;
    }

    public function defaultSort($defaultSort): self
    {
        $this->getSort()->defaultOrder = $defaultSort;

        return $this;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request|iterable $request request instance or query data.
     * @return \Illuminate\Database\Eloquent\Model[]|\Illuminate\Support\Collection|array rows.
     */
    public function get($request)
    {
        $source = $this->prepare($request);

        if ($source instanceof Collection) {
            return $source;
        }

        return $source->get();
    }
}
