<?php

namespace Illuminatech\DataProvider\Test;

use Illuminatech\DataProvider\Fields\Field;
use Illuminatech\DataProvider\Fields\FieldCallback;
use Illuminatech\DataProvider\Includes\IncludeCallback;
use Illuminatech\DataProvider\Includes\IncludeRelation;
use Illuminatech\DataProvider\Selector;
use Illuminatech\DataProvider\Test\Support\Category;
use Illuminatech\DataProvider\Test\Support\Item;

class SelectorTest extends TestCase
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

    public function testNormalizeFields()
    {
        $selector = (new Selector())
            ->setFields([
                'id',
                'alias' => 'db_name',
                'callback' => function ($source) {
                    return $source;
                },
            ]);

        $fields = $selector->getFields();

        $this->assertTrue($fields['id'] instanceof Field);
        $this->assertTrue($fields['alias'] instanceof Field);
        $this->assertTrue($fields['callback'] instanceof FieldCallback);
    }

    /**
     * @depends testNormalizeFields
     */
    public function testSelectFields()
    {
        $selector = (new Selector())
            ->setFields([
                'id',
                'name',
                'slug',
            ]);

        $source = $selector->apply(Item::query(), [
            'fields' => 'id,slug',
        ]);

        $model = $source->first();

        $this->assertFalse(empty($model->id));
        $this->assertFalse(empty($model->slug));
        $this->assertTrue(empty($model->name));
    }

    /**
     * @depends testSelectFields
     */
    public function testSelectNestedFields()
    {
        $selector = (new Selector())
            ->setFields([
                'id',
                'name',
                'items' => [
                    'id',
                    'category_id',
                    'name',
                    'slug',
                ],
            ]);

        $source = $selector->apply(Category::query(), [
            'fields' => [
                'id',
                'items' => [
                    'id',
                    'category_id',
                ],
            ],
        ]);

        $model = $source->first();

        $this->assertFalse(empty($model->id));
        $this->assertTrue(empty($model->name));
        $this->assertTrue($model->relationLoaded('items'));
        $this->assertFalse(empty($model->items[0]->id));
        $this->assertTrue(empty($model->items[0]->name));
    }

    /**
     * @depends testSelectFields
     */
    public function testSelectFieldsWithSourceSelfName()
    {
        $selector = new Selector([
            'fields' => [
                'source_self_name' => 'self',
            ],
        ]);
        $selector->setFields([
            'id',
            'name',
        ]);

        $source = $selector->apply(Item::query(), [
            'fields' => [
                'self' => 'id',
            ],
        ]);

        $model = $source->first();

        $this->assertFalse(empty($model->id));
        $this->assertTrue(empty($model->name));
    }

    public function testNormalizeIncludes()
    {
        $selector = (new Selector())
            ->setIncludes([
                'item',
                'alias' => 'relation',
                'callback' => function ($source) {
                    return $source;
                },
                'nested' => [
                    'item',
                ],
                'dot.name',
            ]);

        $includes = $selector->getIncludes();

        $this->assertTrue($includes['item'] instanceof IncludeRelation);
        $this->assertTrue($includes['alias'] instanceof IncludeRelation);
        $this->assertTrue($includes['callback'] instanceof IncludeCallback);
        $this->assertTrue($includes['nested.item'] instanceof IncludeRelation);
        $this->assertTrue($includes['dot.name'] instanceof IncludeRelation);
    }

    /**
     * @depends testNormalizeIncludes
     */
    public function testIncludeRelation()
    {
        $selector = (new Selector())
            ->setIncludes([
                'category',
            ]);

        $source = $selector->apply(Item::query(), [
            'include' => [
                'category',
            ],
        ]);

        $model = $source->first();

        $this->assertTrue($model->relationLoaded('category'));
    }
}