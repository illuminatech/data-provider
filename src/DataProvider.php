<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider;

use Illuminate\Container\Container;
use Illuminatech\DataProvider\Exceptions\InvalidQueryException;
use Illuminatech\DataProvider\Filters\FilterCallback;
use Illuminatech\DataProvider\Filters\FilterExact;
use Illuminatech\DataProvider\Filters\FilterSearch;
use Symfony\Component\HttpFoundation\Request;

/**
 * DataProvider allows building of the complex search queries based on the request data.
 *
 * It handles filtering, sorting, pagination, include of extra fields or relations on demand.
 *
 * Eloquent usage example:
 *
 * ```php
 * use App\Models\Item;
 * use Illuminate\Http\Request;
 * use Illuminatech\DataProvider\DataProvider;
 *
 * class ItemController extends Controller
 * {
 *     public function index(Request $request)
 *     {
 *         $items = (new DataProvider(Item::class))
 *             ->filters([
 *                 'id',
 *                 'status',
 *                 'search' => ['name', 'description'],
 *             ])
 *             ->sort(['id', 'name', 'status', 'created_at'])
 *             ->paginate($request);
 *
 *         // ...
 *     }
 * }
 * ```
 *
 * Plain database query usage example:
 *
 * ```php
 * use Illuminate\Http\Request;
 * use Illuminate\Support\Facades\DB;
 * use Illuminatech\DataProvider\DataProvider;
 *
 * class ItemController extends Controller
 * {
 *     public function index(Request $request)
 *     {
 *         $items = (new DataProvider(DB::table('items')))
 *             ->filters([
 *                 'id',
 *                 'status',
 *                 'search' => ['name', 'description'],
 *             ])
 *             ->sort(['id', 'name', 'status', 'created_at'])
 *             ->paginate($request);
 *
 *         // ...
 *     }
 * }
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class DataProvider
{
    /**
     * @var \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|object data source.
     */
    protected $source;

    /**
     * @var array this instance config.
     */
    protected $config = [];

    /**
     * @var \Illuminatech\DataProvider\FilterContract[] list of filters indexed by request param name.
     */
    private $filters = [];

    /**
     * @var \Illuminatech\DataProvider\Selector|null related selector instance.
     */
    private $selector;

    /**
     * @var \Illuminatech\DataProvider\Sort|null related sort instance.
     */
    private $sort;

    /**
     * @var \Illuminatech\DataProvider\Pagination|null related pagination instance.
     */
    private $pagination;

    /**
     * Constructor.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|object|string $source data source.
     * @param array $config configuration.
     */
    public function __construct($source, array $config = [])
    {
        if (is_object($source)) {
            $this->source = $source;
        } elseif (is_string($source)) {
            $this->source = $source::query();
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
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|object adjusted data source.
     */
    public function prepare($request): object
    {
        $source = clone $this->source;

        $params = $this->extractRequestParams($request);

        if ($this->selector !== null) {
            $source = $this->selector->apply($source, $params);
        }

        $source = $this->applyFilters($source, $params);

        if ($this->sort !== null) {
            $this->sort->apply($source, $params);
        }

        return $source;
    }

    /**
     * Applies filters to the given data source.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $source data source.
     * @param array $params request parameters.
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder adjusted data source.
     */
    protected function applyFilters(object $source, $params): object
    {
        $filterKeyword = $this->config['filter']['keyword'];

        if ($filterKeyword === null) {
            $allowUndeclaredFilter = true;
            $filterParams = $params;
        } else {
            $allowUndeclaredFilter = false;
            $filterParams = $params[$filterKeyword] ?? [];
        }

        foreach ($filterParams as $name => $value) {
            if (!isset($this->filters[$name])) {
                if (!$allowUndeclaredFilter) {
                    throw new InvalidQueryException('Filter "' . $name . '" is not supported.');
                }
                continue;
            }

            if ($this->isEmpty($value)) {
                continue;
            }

            $this->filters[$name]->apply($source, $name, $value);
        }

        return $source;
    }

    /**
     * Paginate results.
     *
     * @param \Symfony\Component\HttpFoundation\Request|iterable $request request instance or query data.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model[] paginator instance.
     */
    public function paginate($request): object
    {
        $params = $this->extractRequestParams($request);

        $source = $this->prepare($params);

        return $this->getPagination()
            ->paginate($source, $params);
    }

    /**
     * Paginate results into a simple paginator.
     *
     * @param \Symfony\Component\HttpFoundation\Request|iterable $request request instance or query data.
     * @return \Illuminate\Contracts\Pagination\Paginator|\Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model[] paginator instance.
     */
    public function simplePaginate($request): object
    {
        $params = $this->extractRequestParams($request);

        $source = $this->prepare($params);

        return $this->getPagination()
            ->simplePaginate($source, $params);
    }

    /**
     * Create a paginator only supporting simple next and previous links for the results.
     *
     * @param \Symfony\Component\HttpFoundation\Request|iterable $request request instance or query data.
     * @return \Illuminate\Contracts\Pagination\CursorPaginator|\Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model[] paginator instance.
     */
    public function cursorPaginate($request): object
    {
        $params = $this->extractRequestParams($request);

        $source = $this->prepare($params);

        return $this->getPagination()
            ->cursorPaginate($source, $params);
    }

    /**
     * Checks whether the given value is empty or not.
     * @since 1.0.2
     *
     * @param mixed $value value to be checked.
     * @return bool whether the value is empty or not.
     */
    protected function isEmpty($value): bool
    {
        return $value === '' || $value === [] || $value === null || is_string($value) && trim($value) === '';
    }

    /**
     * @param \Illuminatech\DataProvider\Selector $selector selector instance.
     * @return static self reference.
     */
    public function setSelector(Selector $selector): self
    {
        $this->selector = $selector;

        return $this;
    }

    /**
     * @return \Illuminatech\DataProvider\Selector selector instance.
     */
    public function getSelector(): Selector
    {
        if ($this->selector === null) {
            $this->selector = $this->makeSelector();
        }

        return $this->selector;
    }

    /**
     * Creates default selector instance.
     *
     * @return \Illuminatech\DataProvider\Selector
     */
    protected function makeSelector(): Selector
    {
        return new Selector($this->config);
    }

    /**
     * @param \Illuminatech\DataProvider\Sort $sort sort instance.
     * @return static self reference.
     */
    public function setSort(Sort $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return \Illuminatech\DataProvider\Sort sort instance.
     */
    public function getSort(): Sort
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
        return new Sort($this->config['sort']);
    }

    /**
     * @param \Illuminatech\DataProvider\Pagination $pagination pagination instance.
     * @return static self reference.
     */
    public function setPagination(Pagination $pagination): self
    {
        $this->pagination = $pagination;

        return $this;
    }

    /**
     * @return \Illuminatech\DataProvider\Pagination pagination instance.
     */
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

            if (is_string($name) && is_string($rawFilter)) {
                $filters[$name] = new FilterExact($rawFilter);
                continue;
            }

            if (is_string($name) && is_array($rawFilter)) {
                $filters[$name] = new FilterSearch($rawFilter);
                continue;
            }

            throw new \InvalidArgumentException('Unsupported filter specification: ' . gettype($name) . ' => ' . (is_object($rawFilter) ? get_class($rawFilter) : gettype($rawFilter)));
        }

        return $filters;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request|iterable $request request instance or query data.
     * @return array|iterable request params.
     */
    protected function extractRequestParams($request)
    {
        return $request instanceof Request ? $request->query->all() : $request;
    }

    // Fluent interface :

    /**
     * Specifies filters for this data provider.
     * For example:
     *
     * ```php
     * [
     *     'id',
     *     'alias' => 'db_column',
     *     'object' => new FilterExact('name'),
     *     'search' => ['name', 'description'],
     *     'callback' => function ($source, $name, $value) {
     *         // ...
     *     },
     * ]
     * ```
     *
     * @param iterable $filters filters declaration.
     * @return static self reference.
     */
    public function filters(iterable $filters): self
    {
        return $this->setFilters($filters);
    }

    /**
     * Specifies sort fields for this data provider.
     * For example:
     *
     * ```php
     * [
     *     'id',
     *     'name' => [
     *         'asc' => ['first_name' => 'asc', 'last_name' => 'asc'],
     *         'desc' => ['first_name' => 'desc', 'last_name' => 'desc'],
     *     ],
     * ]
     * ```
     *
     * @see \Illuminatech\DataProvider\Sort::$attributes
     *
     * @param iterable $fields sort fields declaration.
     * @return static self reference.
     */
    public function sort(iterable $fields): self
    {
        $this->getSort()->setAttributes($fields);

        return $this;
    }

    /**
     * Specifies default sort to be applied for this data provider.
     * For example: `'-id'`, `['name', '-id']` and so on.
     *
     * @see \Illuminatech\DataProvider\Sort::$defaultSort
     *
     * @param string|array $defaultSort default sort.
     * @return static self reference.
     */
    public function sortDefault($defaultSort): self
    {
        $this->getSort()->defaultSort = $defaultSort;

        return $this;
    }

    /**
     * Specifies selectable fields for this data provider.
     * For example:
     *
     * ```php
     * [
     *     'id',
     *     'alias' => 'db_column',
     *     'items_count' => new FieldRelationAggregate('items', '*', 'count'),
     *     'callback' => function ($source) {
     *         // ...
     *     },
     *     'related_group' => [
     *         'id',
     *         'name',
     *         'related_category' => [
     *             'id',
     *             'name',
     *         ],
     *     ],
     * ]
     * ```
     *
     * @see \Illuminatech\DataProvider\Selector::setFields()
     *
     * @param iterable $fields fields specification.
     * @return static self reference.
     */
    public function fields(iterable $fields): self
    {
        $this->getSelector()->setFields($fields);

        return $this;
    }

    /**
     * Specifies of relations, allowed to be included, for this data provider.
     * For example:
     *
     * ```php
     * [
     *     'category',
     *     'alias' => 'relation_name',
     *     'object' => new IncludeRelation('group', function ($groupQuery) {...}),
     *     'callback' => function ($source) {
     *         // ...
     *     },
     *     'nested.relation',
     * ]
     * ```
     *
     * @see \Illuminatech\DataProvider\Selector::setIncludes()
     *
     * @param iterable $includes includes specification.
     * @return static self reference.
     */
    public function includes(iterable $includes): self
    {
        $this->getSelector()->setIncludes($includes);

        return $this;
    }

    /**
     * Returns all matching data from the data source.
     *
     * @param \Symfony\Component\HttpFoundation\Request|iterable $request request instance or query data.
     * @return \Illuminate\Database\Eloquent\Model[]|\Illuminate\Support\Collection|array rows.
     */
    public function get($request)
    {
        return $this->prepare($request)
            ->get();
    }
}
