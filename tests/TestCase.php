<?php

namespace Illuminatech\DataProvider\Test;

use Illuminate\Container\Container;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager;

/**
 * Base class for the test cases.
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Illuminate\Contracts\Container\Container test application instance.
     */
    protected $app;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createApplication();

        $db = new Manager;

        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $db->bootEloquent();
        $db->setAsGlobal();

        Model::clearBootedModels();

        $this->createSchema();
        $this->seedDatabase();
    }

    /**
     * Get a database connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    protected function getConnection()
    {
        return Model::getConnectionResolver()->connection();
    }

    /**
     * Get a schema builder instance.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function getSchemaBuilder()
    {
        return $this->getConnection()->getSchemaBuilder();
    }

    /**
     * Creates dummy application instance, ensuring facades functioning.
     */
    protected function createApplication()
    {
        $this->app = Container::getInstance();

        Facade::setFacadeApplication($this->app);
    }

    /**
     * Setup the database schema.
     *
     * @return void
     */
    protected function createSchema(): void
    {
        $this->getSchemaBuilder()->create('categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
        });

        $this->getSchemaBuilder()->create('items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('category_id');
            $table->string('name');
            $table->string('slug');
            $table->softDeletes();
        });
    }

    /**
     * Seeds the database schema.
     *
     * @return void
     */
    protected function seedDatabase(): void
    {
        for ($c = 1; $c <= 5; $c++) {
            $categoryId = $this->getConnection()->table('categories')->insertGetId([
                'name' => 'Category ' . $c,
            ]);

            for ($i = 1; $i <= 4; $i++) {
                $this->getConnection()->table('items')->insert([
                    'category_id' => $categoryId,
                    'name' => 'Item ' . (($c - 1) * 4 + $i),
                    'slug' => 'item-' . (($c - 1) * 4 + $i),
                ]);
            }
        }
    }
}
