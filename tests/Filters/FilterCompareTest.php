<?php

namespace Illuminatech\DataProvider\Test\Filters;

use Illuminatech\DataProvider\DataProvider;
use Illuminatech\DataProvider\Filters\FilterCompare;
use Illuminatech\DataProvider\Test\Support\Item;
use Illuminatech\DataProvider\Test\TestCase;

class FilterCompareTest extends TestCase
{
    public function testApply()
    {
        $dataProvider = (new DataProvider(Item::class))->setFilters([
            'search' => new FilterCompare('id', '<='),
        ]);

        $items = $dataProvider->prepare(['filter' => ['search' => 1]])
            ->get();

        $this->assertCount(1, $items);
        $this->assertSame(1, $items[0]->id);
    }
}