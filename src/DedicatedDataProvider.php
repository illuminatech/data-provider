<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider;

/**
 * DedicatedDataProvider allows creation of the custom data provider class dedicated to a specific use case.
 *
 * Such approach allows to organize the code and keep controllers clean.
 *
 * > Note: unlike other methods, `__construct()` is exempt from the usual signature compatibility rules when being extended.
 *   Thus you can its signature in your class as you like, defining your own dependencies.
 *
 * ```php
 * use App\Models\User;
 * use Illuminatech\DataProvider\DedicatedDataProvider;
 * use Illuminatech\DataProvider\Filters\FilterIn;
 *
 * class UserPurchasesList extends DedicatedDataProvider
 * {
 *     public function __construct(User $user)
 *     {
 *         parent::__construct($user->purchases()->with('item'));
 *     }
 *
 *     protected function defineConfig(): array
 *     {
 *         return [
 *             'pagination' => [
 *                 'per_page' => [
 *                     'default' => 16,
 *                 ],
 *             ],
 *         ];
 *     }
 *
 *     protected function defineFilters(): array
 *     {
 *          return [
 *              'id',
 *              'status' => new FilterIn('status'),
 *          ];
 *     }
 *
 *     protected function defineSort(): array
 *     {
 *         return [
 *             'id',
 *             'created_at',
 *         ];
 *     }
 * }
 * ```
 *
 * @see https://www.php.net/manual/en/language.oop5.decon.php
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
abstract class DedicatedDataProvider extends DataProvider
{
    /**
     * {@inheritdoc}
     */
    public function __construct($source, array $config = [])
    {
        $config = array_merge_recursive($this->defineConfig(), $config);

        parent::__construct($source, $config);

        if ($filters = $this->defineFilters()) {
            $this->filters($filters);
        }

        if ($sort = $this->defineSort()) {
            $this->sort($sort);
        }

        if ($sortDefault = $this->defineSortDefault()) {
            $this->sortDefault($sortDefault);
        }

        if ($fields = $this->defineFields()) {
            $this->fields($fields);
        }

        if ($includes = $this->defineIncludes()) {
            $this->includes($includes);
        }
    }

    /**
     * Defines the configuration for this data provider.
     *
     * @see \Illuminatech\DataProvider\DataProvider::$config
     *
     * @return array configuration for this data provider.
     */
    protected function defineConfig(): array
    {
        return [];
    }

    /**
     * Defines the list of filters for this data provider.
     *
     * @see \Illuminatech\DataProvider\DataProvider::filters()
     *
     * @return array filters declaration.
     */
    protected function defineFilters(): array
    {
        return [];
    }

    /**
     * Defines the sort for this data provider.
     *
     * @see \Illuminatech\DataProvider\DataProvider::sort()
     *
     * @return array sort declaration.
     */
    protected function defineSort(): array
    {
        return [];
    }

    /**
     * Defines the default sort for this data provider.
     *
     * @see \Illuminatech\DataProvider\DataProvider::sortDefault()
     *
     * @return array default sort.
     */
    protected function defineSortDefault()
    {
        return [];
    }

    /**
     * Defines the list of includes for this data provider.
     *
     * @see \Illuminatech\DataProvider\DataProvider::includes()
     *
     * @return array includes declaration.
     */
    protected function defineIncludes(): array
    {
        return [];
    }

    /**
     * Defines the list of fields for this data provider.
     *
     * @see \Illuminatech\DataProvider\DataProvider::fields()
     *
     * @return array fields declaration.
     */
    protected function defineFields(): array
    {
        return [];
    }
}