<?php

namespace Illuminatech\DataProvider\Test;

use Illuminatech\DataProvider\DataProvider;
use Illuminatech\DataProvider\Filters\FilterCallback;
use Illuminatech\DataProvider\Filters\FilterExact;
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

    public function testSort()
    {
        $items = (new DataProvider(Item::class))
            ->setSort(['id', 'name'])
            ->prepare(['sort' => '-id'])
            ->get();

        $this->assertSame(10, $items[0]['id']);
        $this->assertSame(9, $items[1]['id']);
    }
}
