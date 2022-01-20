<?php

namespace Illuminatech\DataProvider\Test\Fields;

use Illuminate\Database\Query\Expression;
use Illuminatech\DataProvider\DataProvider;
use Illuminatech\DataProvider\Fields\FieldCallback;
use Illuminatech\DataProvider\Test\Support\Item;
use Illuminatech\DataProvider\Test\TestCase;

class FieldCallbackTest extends TestCase
{
    public function testApply()
    {
        $item = (new DataProvider(Item::class))->fields([
            'custom' => new FieldCallback(function ($source) {
                return $source->addSelect(new Expression('id || "-" || slug as custom'));
            }),
        ])
            ->prepare(['fields' => ['custom']])
            ->orderBy('id', 'asc')
            ->first();

        $this->assertSame('1-item-1', $item->custom);
    }
}