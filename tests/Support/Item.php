<?php

namespace Illuminatech\DataProvider\Test\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 */
class Item extends Model
{
    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'name',
        'slug',
    ];
}
