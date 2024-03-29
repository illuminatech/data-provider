<?php

namespace Illuminatech\DataProvider\Test\Filters;

use Illuminatech\DataProvider\DataProvider;
use Illuminatech\DataProvider\Filters\FilterSearch;
use Illuminatech\DataProvider\Test\Support\Category;
use Illuminatech\DataProvider\Test\Support\Item;
use Illuminatech\DataProvider\Test\TestCase;

class FilterSearchTest extends TestCase
{
    public function testApply()
    {
        $dataProvider = (new DataProvider(Item::class))->setFilters([
            'search' => new FilterSearch(['name', 'slug']),
        ]);

        $items = $dataProvider->prepare(['filter' => ['search' => 'm-5']])
            ->get();

        $this->assertCount(1, $items);
        $this->assertSame('item-5', $items[0]->slug);

        $items = $dataProvider->prepare(['filter' => ['search' => 'm 5']])
            ->get();

        $this->assertCount(1, $items);
        $this->assertSame('item-5', $items[0]->slug);
    }

    public function testApplyNested()
    {
        $categories = (new DataProvider(Category::class))->setFilters([
            'search' => new FilterSearch(['items.slug']),
        ])
            ->prepare(['filter' => ['search' => 'm-5']])
            ->get();

        $this->assertCount(1, $categories);
        $this->assertTrue($categories[0]->items()->where('slug', '=', 'item-5')->exists());
    }
}