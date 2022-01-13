<?php

namespace Illuminatech\DataProvider\Test;

use Illuminate\Config\Repository;
use Illuminatech\DataProvider\DataProvider;
use Illuminatech\DataProvider\Exceptions\InvalidQueryException;
use Illuminatech\DataProvider\Filters\FilterCallback;
use Illuminatech\DataProvider\Filters\FilterExact;
use Illuminatech\DataProvider\Sort;
use Illuminatech\DataProvider\Test\Support\Item;

class DataProviderTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->seedDatabase();
    }

    public function testNormalizeFilters()
    {
        $dataProvider = new DataProvider(Item::class);

        $dataProvider->setFilters([
            'id',
            'search' => new FilterExact('name'),
            'callback' => function ($source, $name, $value) {
                return $source;
            },
        ]);

        $filters = $dataProvider->getFilters();

        $this->assertTrue($filters['id'] instanceof FilterExact);
        $this->assertTrue($filters['search'] instanceof FilterExact);
        $this->assertTrue($filters['callback'] instanceof FilterCallback);
    }

    /**
     * @depends testNormalizeFilters
     */
    public function testNotSupportedFilter()
    {
        $dataProvider = new DataProvider(Item::class);

        $dataProvider->setFilters([
            'search' => new FilterExact('name'),
        ]);

        $this->expectException(InvalidQueryException::class);

        $dataProvider->prepare([
            'filter' => [
                'fake' => 'some'
            ],
        ]);
    }

    public function testSetupSort()
    {
        $dataProvider = new DataProvider(Item::class);

        $dataProvider->setSort(['id', 'name']);

        $sort = $dataProvider->getSort();

        $this->assertTrue($sort instanceof Sort);

        $this->assertArrayHasKey('id', $sort->getAttributes());
        $this->assertArrayHasKey('name', $sort->getAttributes());
    }

    /**
     * @depends testSetupSort
     */
    public function testSort()
    {
        $items = (new DataProvider(Item::class))
            ->setSort(['id', 'name'])
            ->prepare(['sort' => '-id'])
            ->get();

        $this->assertSame(10, $items[0]['id']);
        $this->assertSame(9, $items[1]['id']);
    }

    /**
     * @depends testSort
     */
    public function testGetConfigFromContainer()
    {
        $sortKeyword = 'sort-from-config';

        $this->app->instance('config', new Repository([
            'data_provider' => [
                'sort' => [
                    'keyword' => $sortKeyword,
                ],
            ],
        ]));

        $items = (new DataProvider(Item::class))
            ->setSort(['id', 'name'])
            ->prepare([$sortKeyword => '-id'])
            ->get();

        $this->assertSame(10, $items[0]['id']);
    }
}
