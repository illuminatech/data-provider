<?php

namespace Illuminatech\DataProvider\Test\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 *
 * @property-read Category $category
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
        'category_id',
        'name',
        'slug',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
