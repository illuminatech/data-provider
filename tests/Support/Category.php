<?php

namespace Illuminatech\DataProvider\Test\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 *
 * @property \Illuminate\Database\Eloquent\Collection|Item[] $items
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 */
class Category extends Model
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
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|Item
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}