<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider\Filters;

use Illuminate\Database\PostgresConnection;
use Illuminatech\DataProvider\Exceptions\InvalidQueryException;

/**
 * FilterLike performs string comparison using operator 'LIKE'.
 *
 * By default it sanitizes request value and performs search for string partial match.
 *
 * Usage example:
 *
 * ```php
 * DataProvider(Item::class)
 *     ->filters([
 *         'name' => new FilterLike('name'),
 *         'allow_user_regex' => new FilterLike('name', false),
 *         'for_postgres' => new FilterLike('name', true, 'ilike),
 *     ]);
 * ```
 *
 * @see \Illuminatech\DataProvider\Filters\FilterSearch
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class FilterLike extends FilterRelatedRecursive
{
    /**
     * @var bool whether to escape special search chars like '%' in filter value or allow passing them.
     * If enabled value will be wrapped in '%' for the search.
     */
    public $escape = true;

    /**
     * @var string|null operator name to be used, e.g. 'like' or 'ilike'.
     * If not set ite will be detected from given data source.
     */
    public $operator;

    public function __construct(string $target, bool $escape = true, ?string $operator = null)
    {
        parent::__construct($target);

        $this->escape = $escape;
        $this->operator = $operator;
    }

    /**
     * {@inheritdoc}
     */
    protected function applyInternal(object $source, string $target, string $name, $value): object
    {
        if (!is_scalar($value)) {
            throw new InvalidQueryException('Filter "' . $name . '" requires scalar value.');
        }

        if ($this->escape) {
            $value = $this->escape($value);
        }

        $operator = $this->operator ?? $this->detectOperator($source);

        return $source->where($target, $operator, $value);
    }

    /**
     * Escapes given value according to 'LIKE' SQL operator syntax.
     *
     * @param string $value raw value.
     * @return string escaped value.
     */
    protected function escape($value): string
    {
        $value = strtr(
            $value,
            [
                '%' => '\%',
                '_' => '\_',
                '\\' => '\\\\',
            ]
        );

        return '%' . $value . '%';
    }

    /**
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Support\Collection|object $source data source.
     * @return string operator name.
     */
    protected function detectOperator(object $source): string
    {
        $connection = null;
        if (method_exists($source, 'getConnection')) {
            $connection = $source->getConnection();
        }
        if (method_exists($source, 'getModel')) {
            $model = $source->getModel();
            if (method_exists($model, 'getConnection')) {
                $connection = $model->getConnection();
            }
        }

        if (isset($connection) && $connection instanceof PostgresConnection) {
            return 'ilike';
        }

        return 'like';
    }
}