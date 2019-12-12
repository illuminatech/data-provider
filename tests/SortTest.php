<?php

namespace Illuminatech\DataProvider\Test;

use Illuminatech\DataProvider\Sort;

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
    public function testMultiSort()
    {
        $sort = new Sort();

        $sort->setAttributes([
            'id',
            'name',
        ]);

        $sort->enableMultiSort = false;
        $this->assertSame(['id' => 'asc'], $sort->detectOrders('id,-name'));

        $sort->enableMultiSort = true;
        $this->assertSame(['id' => 'asc', 'name' => 'desc'], $sort->detectOrders('id,-name'));
    }
}
