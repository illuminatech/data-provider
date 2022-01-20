<?php

namespace Illuminatech\DataProvider\Test\includes;

use Illuminate\Database\Query\Builder;
use Illuminatech\DataProvider\DataProvider;
use Illuminatech\DataProvider\Includes\IncludeCallback;
use Illuminatech\DataProvider\Test\TestCase;

class IncludeCallbackTest extends TestCase
{
    public function testApply()
    {
        $item = (new DataProvider($this->getConnection()->table('items')))->includes([
            'custom' => new IncludeCallback(function (Builder $source) {
                return $source->join('categories', 'categories.id', '=', 'items.category_id')
                    ->addSelect('items.id')
                    ->addSelect('categories.name as custom');
            }),
        ])
            ->prepare(['include' => ['custom']])
            ->orderBy('items.id', 'asc')
            ->first();

        $this->assertSame('Category 1', $item->custom);
    }
}