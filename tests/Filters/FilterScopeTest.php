<?php

namespace Illuminatech\DataProvider\Test\Filters;

use Illuminatech\DataProvider\DataProvider;
use Illuminatech\DataProvider\Filters\FilterScope;
use Illuminatech\DataProvider\Test\Support\Item;
use Illuminatech\DataProvider\Test\TestCase;

class FilterScopeTest extends TestCase
{
    public function testApply()
    {
        $items = (new DataProvider(Item::class))->setFilters([
            'search' => new FilterScope('slugByNumber'),
        ])
            ->prepare(['filter' => ['search' => '5']])
            ->get();

        $this->assertCount(1, $items);
        $this->assertSame('item-5', $items[0]->slug);
    }
}