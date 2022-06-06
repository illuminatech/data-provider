<?php

namespace Illuminatech\DataProvider\Test;

use Illuminatech\DataProvider\Test\Support\ItemList;

class DedicatedDataProviderTest extends TestCase
{
    public function testApply()
    {
        $dataProvider = new ItemList();

        $items = $dataProvider->prepare(['filter' => ['id' => 5]])->get();

        $this->assertCount(1, $items);
        $this->assertSame(5, $items[0]->id);

        $item = $dataProvider->prepare(['sort' => '-id'])->first();
        $this->assertSame(20, $item->id);

        $items = $dataProvider->paginate([])->items();
        $this->assertCount(16, $items);
    }

    public function testStaticNew()
    {
        $dataProvider = ItemList::new();

        $this->assertTrue($dataProvider instanceof ItemList);
    }
}