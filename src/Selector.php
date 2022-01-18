<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\DataProvider;

use Illuminate\Support\Str;
use Illuminatech\DataProvider\Exceptions\InvalidQueryException;
use Illuminatech\DataProvider\Fields\Field;
use Illuminatech\DataProvider\Fields\FieldCallback;

/**
 * Selector
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Selector
{
    public $fieldsKeyword = 'fields';

    public $includeKeyword = 'include';

    /**
     * @var string|bool name of the main source to be used in fields selection group.
     * If set to `false` - no name will be used, if set to `true` - name will be detected from given data source.
     * If set as `string` its value will be used as name.
     */
    public $sourceSelfName = false;

    /**
     * @var array|\Illuminatech\DataProvider\FieldContract[]
     */
    private $fields = [];

    private $includes = [];

    public function __construct(array $config = [])
    {
        $this->fieldsKeyword = $config['fields']['keyword'] ?? $this->fieldsKeyword;
        $this->includeKeyword = $config['include']['keyword'] ?? $this->includeKeyword;
        $this->sourceSelfName = $config['fields']['source_self_name'] ?? $this->sourceSelfName;
    }

    public function setFields(iterable $fields): self
    {
        $this->fields = $this->normalizeFields($fields);

        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    protected function normalizeFields(iterable $rawFields): array
    {
        $fields = [];
        foreach ($rawFields as $name => $rawField) {
            if ($rawField instanceof FieldContract) {
                $fields[$name] = $rawField;
                continue;
            }

            if ($rawField instanceof \Closure) {
                $fields[$name] = new FieldCallback($rawField);
                continue;
            }

            if (is_iterable($rawField)) {
                if (!is_string($name)) {
                    throw new \InvalidArgumentException('Fields group should be indexed by relation name. "' . gettype($name) . '"(' . $name . ') given instead.');
                }
                $fields[$name] = $this->normalizeFields($rawField);
                continue;
            }

            if (is_int($name) && is_string($rawField)) {
                $fields[$rawField] = new Field($rawField);
                continue;
            }

            if (is_string($name) && is_string($rawField)) {
                $fields[$name] = new Field($rawField);
                continue;
            }

            throw new \InvalidArgumentException('Unsupported field specification: ' . gettype($name) . ' => ' . is_object($rawField) ? get_class($rawField) : gettype($rawField));
        }

        return $fields;
    }

    public function setIncludes(): self
    {
        return $this;
    }

    public function getIncludes(): array
    {
        return $this->includes;
    }

    /**
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $source data source.
     * @param array $params request parameters.
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder adjusted data source.
     */
    public function apply(object $source, $params): object
    {
        if (isset($params[$this->fieldsKeyword])) {
            if (is_string($params[$this->fieldsKeyword])) {
                $fieldParams = array_map('trim', explode(',', $params[$this->fieldsKeyword]));
            } elseif (!is_iterable($params[$this->fieldsKeyword])) {
                throw new InvalidQueryException('"' . $this->fieldsKeyword . '" should be a list of fields.');
            } else {
                $fieldParams = $params[$this->fieldsKeyword];
            }

            $sourceSelfName = null;
            if (is_string($this->sourceSelfName)) {
                $sourceSelfName = $this->sourceSelfName;
            } elseif ($this->sourceSelfName === true) {
                $sourceSelfName = $this->detectSourceSelfName($source);
            }

            $fields = $this->getFields();
            if ($sourceSelfName !== null) {
                foreach ($fields as $key => $field) {
                    if (!is_array($field)) {
                        $fields[$sourceSelfName][$key] = $field;
                        unset($fields[$key]);
                    }
                }

                if (isset($fieldParams[$sourceSelfName])) {
                    if (empty($fields[$sourceSelfName])) {
                        throw new InvalidQueryException('Unsupported include "' . $sourceSelfName . '" in "' . $this->fieldsKeyword . '".');
                    }

                    $source = $this->applyFieldsRecursive($source, $fields[$sourceSelfName], $fieldParams[$sourceSelfName], [$this->fieldsKeyword, $sourceSelfName]);
                    unset($fieldParams[$sourceSelfName]);
                }
            }

            $source = $this->applyFieldsRecursive($source, $fields, $fieldParams, [$this->fieldsKeyword]);
        }

        return $source;
    }

    /**
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $source data source.
     * @param array $fields
     * @param string|iterable $params
     * @return object
     */
    protected function applyFieldsRecursive(object $source, array $fields, $params, array $keywordPath): object
    {
        if (!is_iterable($params)) {
            $params = array_map('trim', explode(',', $params));
        }

        foreach ($params as $name => $value) {
            if (is_int($name)) {
                $fieldName = $value;

                if (!isset($fields[$fieldName])) {
                    throw new InvalidQueryException('Unsupported field "' . $fieldName . '" in "' . implode('->', $keywordPath) . '".');
                }

                $source = $fields[$fieldName]->apply($source, $fieldName);

                continue;
            }

            $relationName = $name;
            if (!isset($fields[$relationName]) || !is_array($fields[$relationName])) {
                throw new InvalidQueryException('Unsupported include "' . $relationName . '" in "' . implode('->', $keywordPath) . '".');
            }

            $relationFields = $fields[$relationName];

            $keywordPath[] = $relationName;

            $source->with([$relationName => function ($query) use ($relationFields, $value, $keywordPath) {
                return $this->applyFieldsRecursive($query, $relationFields, $value, $keywordPath);
            }]);
        }

        return $source;
    }

    /**
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $source data source.
     * @return string detected name.
     */
    protected function detectSourceSelfName(object $source): string
    {
        if (method_exists($source, 'getModel')) {
            $modelClassName = get_class($source->getModel());

            return Str::camel(basename(str_replace('\\', '/', $modelClassName)));
        }

        if (!empty($source->from) && is_string($source->from)) {
            $parts = explode(' ', $source->from);

            return array_shift($parts);
        }

        return 'self';
    }
}