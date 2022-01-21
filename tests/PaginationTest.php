<?php

namespace Illuminatech\DataProvider\Test;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminatech\DataProvider\Pagination;
use Illuminatech\DataProvider\Test\Support\Category;
use Illuminatech\DataProvider\Test\Support\Item;

class PaginationTest extends TestCase
{
    public function testPaginate()
    {
        $items = (new Pagination())
            ->paginate(Item::query(), [
                'per-page' => 2,
                'page' => 2,
            ]);

        $this->assertTrue($items instanceof LengthAwarePaginator);
        $this->assertCount(2, $items->items());

        $items = (new Pagination())
            ->simplePaginate(Item::query(), [
                'per-page' => 2,
                'page' => 2,
            ]);

        $this->assertTrue($items instanceof Paginator);
        $this->assertCount(2, $items->items());

        if (class_exists(CursorPaginator::class)) {
            $items = (new Pagination())
                ->cursorPaginate(Item::query(), [
                    'per-page' => 2,
                ]);

            $this->assertTrue($items instanceof CursorPaginator);
            $this->assertCount(2, $items->items());
        }
    }

    /**
     * @depends testPaginate
     */
    public function testPaginateFromNestedParams()
    {
        $items = (new Pagination(['keyword' => 'pagination']))
            ->paginate(Item::query(), [
                'pagination' => [
                    'per-page' => 2,
                    'page' => 2,
                ],
            ]);

        $this->assertTrue($items instanceof LengthAwarePaginator);
        $this->assertCount(2, $items->items());

        $this->assertStringContainsString(urlencode('pagination[page]'), $items->nextPageUrl());
    }

    /**
     * @depends testPaginate
     */
    public function testPaginatePreserveSelect()
    {
        $items = (new Pagination())
            ->paginate(Item::query()->select(['id']), [
                'per-page' => 2,
                'page' => 2,
            ]);

        $attributes = $items->items()[0]->getAttributes();

        $this->assertCount(1, $attributes);
        $this->assertTrue(isset($attributes['id']));
    }

    /**
     * @depends testPaginate
     */
    public function testPaginateRawDb()
    {
        $items = (new Pagination())
            ->paginate($this->getConnection()->table('items'), [
                'per-page' => 2,
                'page' => 2,
            ]);

        $this->assertTrue($items instanceof LengthAwarePaginator);
        $this->assertCount(2, $items->items());
    }

    /**
     * @depends testPaginate
     */
    public function testPaginateRelation()
    {
        $category = Category::query()->first();

        $items = (new Pagination())
            ->paginate($category->items(), [
                'per-page' => 2,
                'page' => 2,
            ]);

        $this->assertTrue($items instanceof LengthAwarePaginator);
        $this->assertCount(2, $items->items());
    }
}