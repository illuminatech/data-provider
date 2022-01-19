<?php

namespace Illuminatech\DataProvider\Test\Filters;

use Illuminatech\DataProvider\DataProvider;
use Illuminatech\DataProvider\Filters\FilterExact;
use Illuminatech\DataProvider\Test\Support\Category;
use Illuminatech\DataProvider\Test\Support\Item;
use Illuminatech\DataProvider\Test\TestCase;

class FilterExactTest extends TestCase
{
    public function testApply()
    {
        $items = (new DataProvider(Item::class))->setFilters([
            'search' => new FilterExact('slug'),
        ])
            ->prepare(['filter' => ['search' => 'item-5']])
            ->get();

        $this->assertCount(1, $items);
        $this->assertSame('item-5', $items[0]->slug);
    }

    public function testApplyNested()
    {
        $categories = (new DataProvider(Category::class))->setFilters([
            'search' => new FilterExact('items.slug'),
        ])
            ->prepare(['filter' => ['search' => 'item-5']])
            ->get();

        $this->assertCount(1, $categories);
        $this->assertTrue($categories[0]->items()->where('slug', '=', 'item-5')->exists());
    }
}
