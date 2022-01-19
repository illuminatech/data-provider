<?php

namespace Illuminatech\DataProvider\Test\Filters;

use Illuminatech\DataProvider\DataProvider;
use Illuminatech\DataProvider\Filters\FilterTrashed;
use Illuminatech\DataProvider\Test\Support\Item;
use Illuminatech\DataProvider\Test\TestCase;

class FilterTrashedTest extends TestCase
{
    public function testApply()
    {
        $dataProvider = (new DataProvider(Item::class))->setFilters([
            'trashed' => new FilterTrashed(),
        ]);

        $item = Item::query()->first();
        $item->delete();

        $items = $dataProvider->prepare(['filter' => ['trashed' => 'without']])
            ->get();

        $this->assertCount(19, $items);

        $items = $dataProvider->prepare(['filter' => ['trashed' => 'only']])
            ->get();

        $this->assertCount(1, $items);

        $items = $dataProvider->prepare(['filter' => ['trashed' => 'with']])
            ->get();

        $this->assertCount(20, $items);
    }
}