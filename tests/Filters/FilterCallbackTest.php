<?php

namespace Illuminatech\DataProvider\Test\Filters;

use Illuminatech\DataProvider\DataProvider;
use Illuminatech\DataProvider\Filters\FilterCallback;
use Illuminatech\DataProvider\Test\Support\Item;
use Illuminatech\DataProvider\Test\TestCase;

class FilterCallbackTest extends TestCase
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

    public function testApply()
    {
        $items = (new DataProvider(Item::class))->setFilters([
            'search' => new FilterCallback(function ($source, $name, $value) {
                return $source->where('slug', '=', $value);
            }),
        ])
            ->prepare(['filter' => ['search' => 'item-3']])
            ->get();

        $this->assertCount(1, $items);
        $this->assertSame('item-3', $items[0]->slug);
    }
}
