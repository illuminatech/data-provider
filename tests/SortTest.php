<?php

namespace Illuminatech\DataProvider\Test;

use Illuminatech\DataProvider\Exceptions\InvalidQueryException;
use Illuminatech\DataProvider\Sort;
use Illuminatech\DataProvider\Test\Support\Item;

class SortTest extends TestCase
{
    public function testNormalizeAttributes()
    {
        $sort = new Sort();

        $sort->setAttributes([
            'id',
            'name',
        ]);

        $expectedAttributes = [
            'id' => [
                'asc' => [
                    'id' => 'asc',
                ],
                'desc' => [
                    'id' => 'desc',
                ],
            ],
            'name' => [
                'asc' => [
                    'name' => 'asc',
                ],
                'desc' => [
                    'name' => 'desc',
                ],
            ],
        ];
        $this->assertSame($expectedAttributes, $sort->getAttributes());
    }

    /**
     * @depends testNormalizeAttributes
     */
    public function testDetectOrders()
    {
        $sort = new Sort();

        $sort->setAttributes([
            'id',
            'name',
        ]);

        $this->assertSame(['name' => 'asc'], $sort->detectOrders('name'));
        $this->assertSame(['name' => 'desc'], $sort->detectOrders('-name'));
    }

    /**
     * @depends testDetectOrders
     */
    public function testDefaultSort()
    {
        $sort = new Sort();

        $sort->setAttributes([
            'id',
            'name',
        ]);

        $sort->defaultSort = '-name';

        $this->assertSame(['name' => 'desc'], $sort->detectOrders([]));
    }

    /**
     * @depends testDetectOrders
     */
    public function testMultiSort()
    {
        $sort = new Sort();

        $sort->setAttributes([
            'id',
            'name',
        ]);

        $sort->enableMultiSort = true;
        $this->assertSame(['id' => 'asc', 'name' => 'desc'], $sort->detectOrders('id,-name'));

        $sort->enableMultiSort = false;

        $this->expectException(InvalidQueryException::class);
        $sort->detectOrders('id,-name');
    }

    /**
     * @depends testDetectOrders
     */
    public function testNotSupportedAttribute()
    {
        $sort = new Sort();

        $sort->setAttributes([
            'id',
            'name',
        ]);

        $this->expectException(InvalidQueryException::class);

        $sort->detectOrders('slug');
    }

    /**
     * @depends testDetectOrders
     */
    public function testApplyEloquent()
    {
        $sort = new Sort();

        $sort->setAttributes([
            'id',
        ]);

        $this->assertEquals(1, $sort->apply(Item::query(), ['sort' => 'id'])->first()->id);
        $this->assertEquals(20, $sort->apply(Item::query(), ['sort' => '-id'])->first()->id);
    }

    /**
     * @depends testDetectOrders
     */
    public function testApplyRawDb()
    {
        $db = $this->getConnection();

        $sort = new Sort();

        $sort->setAttributes([
            'id',
        ]);

        $this->assertEquals(1, $sort->apply($db->table('items'), ['sort' => 'id'])->first()->id);
        $this->assertEquals(20, $sort->apply($db->table('items'), ['sort' => '-id'])->first()->id);
    }
}
