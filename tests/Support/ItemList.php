<?php

namespace Illuminatech\DataProvider\Test\Support;

use Illuminatech\DataProvider\DedicatedDataProvider;

class ItemList extends DedicatedDataProvider
{
    public function __construct()
    {
        parent::__construct(Item::query()->with('category'));
    }

    /**
     * {@inheritdoc}
     */
    protected function defineConfig(): array
    {
        return [
            'pagination' => [
                'per_page' => [
                    'default' => 16,
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function defineFilters(): array
    {
        return [
            'id',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function defineSort(): array
    {
        return [
            'id',
        ];
    }
}