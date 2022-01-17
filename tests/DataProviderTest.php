<?php

namespace Illuminatech\DataProvider\Test;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
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

    public function testPaginate()
    {
        $items = (new DataProvider(Item::class))
            ->paginate([
                'per-page' => 2,
                'page' => 2,
            ]);

        $this->assertTrue($items instanceof LengthAwarePaginator);
        $this->assertCount(2, $items->items());

        $items = (new DataProvider(Item::class))
            ->simplePaginate([
                'per-page' => 2,
                'page' => 2,
            ]);

        $this->assertTrue($items instanceof Paginator);
        $this->assertCount(2, $items->items());

        if (class_exists(CursorPaginator::class)) {
            $items = (new DataProvider(Item::class))
                ->cursorPaginate([
                    'per-page' => 2,
                ]);

            $this->assertTrue($items instanceof CursorPaginator);
            $this->assertCount(2, $items->items());
        }
    }

    /**
     * @depends testPaginate
     */
    public function testPaginateFromNestedParams()
    {
        $items = (new DataProvider(Item::class, ['pagination' => ['keyword' => 'pagination']]))
            ->paginate([
                'pagination' => [
                    'per-page' => 2,
                    'page' => 2,
                ],
            ]);

        $this->assertTrue($items instanceof LengthAwarePaginator);
        $this->assertCount(2, $items->items());

        $this->assertStringContainsString(urlencode('pagination[page]'), $items->nextPageUrl());
    }

    /**
     * @depends testPaginate
     */
    public function testPaginatePreserveSelect()
    {
        $items = (new DataProvider(Item::query()->select(['id'])))
            ->paginate([
                'per-page' => 2,
                'page' => 2,
            ]);

        $attributes = $items->items()[0]->getAttributes();

        $this->assertCount(1, $attributes);
        $this->assertTrue(isset($attributes['id']));
    }

    /**
     * @depends testNormalizeFilters
     */
    public function testPreserveSourceState()
    {
        $source = Item::query();

        $preparedSource = (new DataProvider($source))
            ->filters([
                'id'
            ])
            ->prepare([
                'filter' => [
                    'id' => 1,
                ],
            ]);

        $this->assertNotEquals($source->count(), $preparedSource->count());
    }

    public function testGet()
    {
        $items = (new DataProvider(Item::class))->get([]);

        $this->assertTrue($items instanceof Collection);
        $this->assertEquals(Item::query()->count(), $items->count());

        $source = [
            [
                'id' => 1,
                'name' => 'foo',
            ],
            [
                'id' => 2,
                'name' => 'bar',
            ],
        ];
        $items = (new DataProvider($source))->get([]);
        $this->assertEquals(count($source), $items->count());
    }
}
