<?php

namespace Illuminatech\DataProvider\Test\Filters;

use Illuminatech\DataProvider\DataProvider;
use Illuminatech\DataProvider\Filters\FilterLike;
use Illuminatech\DataProvider\Test\Support\Item;
use Illuminatech\DataProvider\Test\TestCase;

class FilterLikeTest extends TestCase
{
    public function testApply()
    {
        $dataProvider = (new DataProvider(Item::class))->setFilters([
            'search' => new FilterLike('slug'),
        ]);

        $items = $dataProvider->prepare(['filter' => ['search' => 'm-5']])
            ->get();

        $this->assertCount(1, $items);
        $this->assertSame('item-5', $items[0]->slug);

        $items = $dataProvider->prepare(['filter' => ['search' => '%m-5%']])
            ->get();

        $this->assertCount(0, $items);
    }

    public function testApplyNoEscape()
    {
        $dataProvider = (new DataProvider(Item::class))->setFilters([
            'search' => new FilterLike('slug', false),
        ]);

        $items = $dataProvider->prepare(['filter' => ['search' => '%m-5%']])
            ->get();

        $this->assertCount(1, $items);
        $this->assertSame('item-5', $items[0]->slug);

        $items = $dataProvider->prepare(['filter' => ['search' => 'm-5']])
            ->get();

        $this->assertCount(0, $items);
    }
}