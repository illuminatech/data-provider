<p align="center">
    <a href="https://github.com/illuminatech" target="_blank">
        <img src="https://avatars1.githubusercontent.com/u/47185924" height="100px">
    </a>
    <h1 align="center">Laravel Data Provider</h1>
    <br>
</p>

This extension allows building of the complex search queries based on the request in Laravel.
In particular, it is useful for REST API composition.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://img.shields.io/packagist/v/illuminatech/data-provider.svg)](https://packagist.org/packages/illuminatech/data-provider)
[![Total Downloads](https://img.shields.io/packagist/dt/illuminatech/data-provider.svg)](https://packagist.org/packages/illuminatech/data-provider)
[![Build Status](https://github.com/illuminatech/data-provider/workflows/build/badge.svg)](https://github.com/illuminatech/data-provider/actions)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist illuminatech/data-provider
```

or add

```json
"illuminatech/data-provider": "*"
```

to the "require" section of your composer.json.


Usage
-----

This extension allows building of the complex search queries based on the request data. It handles filtering, sorting, pagination,
include of extra fields or relations on demand. Both Eloquent Active Record and plain database queries are supported.

This extension provides `Illuminatech\DataProvider\DataProvider` class, which wraps given data source object like database
query builder, and provide the means to define controller-level interaction to search through this data source.

Usage example:

```php
<?php

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminatech\DataProvider\DataProvider;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $items = (new DataProvider(Item::class))
            ->filters([
                'id',
                'status' => 'status_id',
                'search' => ['name', 'description'],
            ])
            ->sort(['id', 'name', 'status', 'created_at'])
            ->sortDefault('-id')
            ->paginate($request);
            
        // ...
    }
}
```

This example will respond to the following request:

```
GET http://example.com/items?filter[status]=active&filter[search]=foo&sort=-id&page=2&per-page=20
```

Same example with the plain database query usage:

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminatech\DataProvider\DataProvider;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $items = (new DataProvider(DB::table('items')))
            ->filters([
                'id',
                'status',
                'search' => ['name', 'description'],
            ])
            ->sort(['id', 'name', 'status', 'created_at'])
            ->sortDefault('-id')
            ->paginate($request);
            
        // ...
    }
}
```

Data provider defines only a few methods for the data querying:

 - `get()` - returns all records matching request
 - `paginate()` - returns all records matching request with a paginator
 - `simplePaginate()` - returns all records matching request with a simple paginator
 - `cursorPaginate()` - returns all records matching request with a cursor paginator

However, you can use `prepare()` method to get data source object, adjusted to given request, to invoke the method you need.
For example:

```php
<?php

use App\Models\Item;
use Illuminatech\DataProvider\DataProvider;

$items = (new DataProvider(Item::class))
    ->filters(/* ... */)
    ->prepare($request) // applies all requested filters, returns `\Illuminate\Database\Eloquent\Builder` instance
    ->chunk(100, function ($items) {
        // ...
    });
```

Method `prepare()` is immutable, leaving original data source object intact. Thus, you can re-use same data provider to
process several different search requests. For example:

```php
<?php

use App\Models\Article;
use Illuminatech\DataProvider\DataProvider;

$query = Article::query() 
    ->with('category');

$dataProvider = (new DataProvider($query))
    ->filters([
        'status'
    ]);

$publishedArticles = $dataProvider->get(['filter' => ['status' => 'published']]); // has no side affect on `$query` instance
$draftArticles = $dataProvider->get(['filter' => ['status' => 'draft']]); // can process multiple requests in isolation
```


### Specifying Data Source

There are several ways to specify a data source for the `DataProvider`:

 - instance of `Illuminate\Database\Eloquent\Builder`
 - instance of `Illuminate\Database\Query\Builder`
 - instance of Eloquent relation like `Illuminate\Database\Eloquent\Relations\HasMany`
 - name of the Eloquent model class

For example:

```php
<?php

use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminatech\DataProvider\DataProvider;

$items = (new DataProvider(Item::query())) // instance of `\Illuminate\Database\Eloquent\Builder`
    ->filters(/* ... */)
    ->get();
    
$items = (new DataProvider( // all default conditions and eager loading should be applied to data source before passing it to data provider
        Item::query()
            ->with('category')
            ->where('status', 'published')
    ))
    ->filters(/* ... */)
    ->get();

$items = (new DataProvider(Item::class)) // invokes `Item::query()` automatically
    ->filters(/* ... */)
    ->get();

$items = (new DataProvider(DB::table('items'))) // instance of `\Illuminate\Database\Query\Builder`
    ->filters(/* ... */)
    ->get();

$item = Item::query()->first();
$purchases = (new DataProvider($item->purchases())) // instance of `\Illuminate\Database\Eloquent\Relations\HasMany`
    ->filters(/* ... */)
    ->get();
```

> Note: this extension does not put explicit restriction on the data source object type - it simply expected to match
  database query builder notation. Thus, you may create a custom query builder class, which works with special data storage
  like MongoDB or Redis, and pass its instance as a data source. If its methods signature matches `\Illuminate\Database\Query\Builder` -
  it should work. Although it is not guaranteed.


### Configuration

You can publish predefined configuration file using following console command:

```
php artisan vendor:publish --provider="Illuminatech\DataProvider\DataProviderServiceProvider" --tag=config
```

This will create an application-wide configuration for all `DataProvider` instances. You can see its example at [config/data_provider.php](config/data_provider.php).
You may adjust configuration per each `DataProvider` instance, using second argument of its constructor.
For example:

```php
<?php

use App\Models\Item;
use Illuminatech\DataProvider\DataProvider;

$items = (new DataProvider(Item::class, [
    'pagination' => [
        'per_page' => [
            'max' => 80,
            'default' => 20,
        ],
    ],
]))
    ->filters([
        // ...
    ])
    ->paginate($request); // creates paginator with page size 20
```

Additional configuration will be merged recursively with one you specified at your "config/data_provider.php" file, so you
should specify only those keys you wish to change.


### Filtering

Filters setup example:

```php
<?php

use App\Models\Item;
use Illuminatech\DataProvider\DataProvider;
use Illuminatech\DataProvider\Filters\FilterExact;

$items = (new DataProvider(Item::class))
    ->filters([
        'id', // short syntax, equals to `'id' => new FilterExact('id')`,
        'status' => 'status_id', // short syntax, equals to `'status' => new FilterExact('status_id')`,
        'search' => ['name', 'description'], // short syntax, equals to `'search' => new FilterSearch(['name', 'description'])`
        'exact_name' => new FilterExact('name'), // pass filter instance directly
        'callback' => function ($query, $name, $value) { // short syntax, equals to `'callback' => new FilterCallback(function ($query, $name, $value) {})`
            $query->whereNotIn('status', $value);
        },
    ])
    ->paginate($request);
```

This example will respond to the following request:

```
GET http://example.com/items?filter[id]=12&filter[status]=2&filter[search]=any&filter[exact_name]=foo&filter[callback][0]=1&filter[callback][1]=2
```

> Tip: you may disable filters grouping in request by setting `null` as `data_provider.filter.keyword` configuration value.

While specifying filter attribute, you can use a dot ('.') notation to make filter challenge against relation instead of main source.
In this case `Illuminate\Database\Eloquent\Concerns::QueriesRelationships::whereHas()` will be executed under the hood.
However, this behavior will apply only for Eloquent query and relations.
For example:

```php
<?php

use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminatech\DataProvider\DataProvider;
use Illuminatech\DataProvider\Filters\FilterExact;

// Eloquent processes dot attributes via relations:
$items = (new DataProvider(Item::class))
    ->filters([
        'category_name' => new FilterExact('category.name'),
    ])
    ->get(['category_name' => 'programming']); // applies $itemQuery->whereHas('category', function() {...});

// Regular DB query consider dot attribute as 'table.column' specification:
$items = (new DataProvider(
        DB::table('items')
            ->join('categories', 'categories.id', '=', 'items.category_id')
    ))
    ->filters([
        'category_name' => new FilterExact('category.name'),
    ])
    ->get(['category_name' => 'programming']); // applies $itemQuery->where('category.name', '=', 'programming');
```

List of supported filters:

- [Illuminatech\DataProvider\Filters\FilterExact](src/Filters/FilterExact.php)
- [Illuminatech\DataProvider\Filters\FilterSearch](src/Filters/FilterSearch.php)
- [Illuminatech\DataProvider\Filters\FilterCallback](src/Filters/FilterCallback.php)
- [Illuminatech\DataProvider\Filters\FilterCompare](src/Filters/FilterCompare.php)
- [Illuminatech\DataProvider\Filters\FilterIn](src/Filters/FilterIn.php)
- [Illuminatech\DataProvider\Filters\FilterLike](src/Filters/FilterLike.php)
- [Illuminatech\DataProvider\Filters\FilterScope](src/Filters/FilterScope.php)
- [Illuminatech\DataProvider\Filters\FilterTrashed](src/Filters/FilterTrashed.php)

Please refer to the particular filter class for more details and examples.

You can create your custom filter implementing [Illuminatech\DataProvider\FilterContract](src/FilterContract.php) interface.


### Sorting

Sorting setup example:

```php
<?php

use App\Models\User;
use Illuminatech\DataProvider\DataProvider;

$dataProvider = (new DataProvider(User::class))
    ->sort([
        'id', // short syntax, equals to `['id' => ['asc' => ['id' => 'asc'], 'desc' => ['id' => 'desc']]]`
        'name' => [
            'asc' => ['first_name' => 'asc', 'last_name' => 'asc'],
            'desc' => ['first_name' => 'desc', 'last_name' => 'desc'],
        ],
    ])
    ->sortDefault('-id');

$users = $dataProvider->get(['sort' => 'id']); // applies 'ORDER BY id ASC'
$users = $dataProvider->get(['sort' => '-id']); // applies 'ORDER BY id DESC'
$users = $dataProvider->get(['sort' => 'name']); // applies 'ORDER BY first_name ASC, last_name ASC'
$users = $dataProvider->get([]); // applies default sort: 'ORDER BY id DESC'
```

You may enable multisort support for the data provider setting `data_provider.sort.enable_multisort` configuration value to `true`.
For example:

```php
<?php

use App\Models\User;
use Illuminatech\DataProvider\DataProvider;

$dataProvider = (new DataProvider(User::class, [
        'sort' => [
            'enable_multisort' => true,
        ],
    ]))
    ->sort([
        'id',
        'first_name',
    ]);

$users = $dataProvider->get(['sort' => 'first_name,-id']); // applies 'ORDER BY first_name ASC, id DESC'
```

> Note: sort parameter for multi-sort can be passed both as comma separated string and as an array.


### Pagination

Data provider defines following pagination methods, wrapping the ones provided by query builder:

 - `paginate()` - returns all records matching request with a paginator
 - `simplePaginate()` - returns all records matching request with a simple paginator
 - `cursorPaginate()` - returns all records matching request with a cursor paginator

In addition to Laravel standard pagination behavior, these methods also allow control over page size via request parameters.
For example:

```php
<?php

use App\Models\Item;
use Illuminatech\DataProvider\DataProvider;

$dataProvider = new DataProvider(Item::class);

var_dump(count($dataProvider->paginate([])->items())); // outputs: 15
var_dump(count($dataProvider->paginate(['per-page' => 5])->items())); // outputs: 5
var_dump(count($dataProvider->paginate(['per-page' => 999999999])->items())); // throws a 'bad request' HTTP exception
```

You may control page size boundaries per each data provider using constructor configuration parameter. For example:

```php
<?php

use App\Models\Item;
use Illuminatech\DataProvider\DataProvider;

$dataProvider = new DataProvider(Item::class, [
    'pagination' => [
        'per_page' => [
            'min' => 1,
            'max' => 200,
            'default' => 20,
        ],
    ],
]);
```

If `data_provider.pagination.appends` enabled, all pagination methods will automatically append passed request data to
the created paginator instance, so you do not need to invoke `Illuminate\Contracts\Pagination\Paginator::appends()` manually.


### Include Relations

While creating an API, you may allow its client to "expand" particular entities, including their relations to the HTTP response.

Available for inclusion relations setup example:

```php
<?php

use App\Models\Item;
use Illuminatech\DataProvider\DataProvider;
use Illuminatech\DataProvider\Includes\IncludeRelation;

$dataProvider = (new DataProvider(Item::class))
    ->includes([
        'category', // short syntax, equals to `'category' => new IncludeRelation('category')`
        'alias' => 'relation', // short syntax, equals to `'alias' => new IncludeRelation('relation')`,
        'published_comments' => new IncludeRelation('comments', function ($commentsQuery) {
            $commentsQuery->where('status', '=', 'published');
        }),
        'category.group', // nested relation include
    ]);

$item = $dataProvider->prepare(['include' => 'category'])->first();
var_dump($item->relationLoaded('category')); // outputs `true`
```


### Selecting Fields

While creating an API, you may allow its client to specify the list of fields to be returned by particular listing endpoint.
This may be useful to reduce HTTP traffic, skipping large text fields in response.

Selectable fields setup example:

```php
<?php

use App\Models\Item;
use Illuminatech\DataProvider\DataProvider;
use Illuminatech\DataProvider\Fields\Field;

$dataProvider = (new DataProvider(Item::class))
    ->fields([
        'id', // short syntax, equals to `'id' => new Field('id')`
        'name' => new Field('name'),
        'brief' => 'description', // short syntax, equals to `'brief' => new Field('description')`
        'price',
    ]);

$item = $dataProvider->prepare(['fields' => 'id,name'])->first();
var_dump(isset($item->id)); // outputs `true`
var_dump(isset($item->name)); // outputs `true`
var_dump(isset($item->description)); // outputs `false`
```

You may specify selectable fields for the related models as well. For example:

```php
<?php

use App\Models\Item;
use Illuminatech\DataProvider\DataProvider;

$dataProvider = (new DataProvider(Item::class))
    ->fields([
        'id',
        'name',
        'category' => [
            'id',
            'name',
        ],
    ]);

$item = $dataProvider->prepare([
    'fields' => [
        'id',
        'category' => [
            'id',
        ],
    ],
])->first();

var_dump(isset($item->id)); // outputs `true`
var_dump(isset($item->name)); // outputs `false`
var_dump(isset($item->category->id)); // outputs `true`
var_dump(isset($item->category->name)); // outputs `false`
```

Note that passing fields for the particular relation causes its eager loading. This way you actually declare "includes" 
while writing "fields". This may create an inconsistency in your API, as it allows loading of the particular relation via
"fields", but does not allow its loading via "includes". It is your responsibility to setup `includes()` and `fields()`
in consistent way.


### JSON API Specification Support

This extension is compatible with [JSON API Specification](https://jsonapi.org/). However, the default configuration for
the pagination mismatches it, since it provides compatibility with native Laravel pagination.
But you can easily fix this with a proper configuration. For example:

```php
<?php
// file 'config/data_provider.php'

return [
    // ...
    'pagination' => [
        'keyword' => 'page',
        'page' => [
            'keyword' => 'number',
        ],
        'per_page' => [
            'keyword' => 'size',
            // ...
        ],
        // ...
    ],
];
```


### Dedicated Data Providers

You may create a custom data provider class dedicated to the specific use case. Such approach allows to organize the code
and keep your controllers clean.

This goal can be easily achieved using `Illuminatech\DataProvider\DedicatedDataProvider` as a base class. It predefines
a set of methods named `define*`, like `defineConfig()`, `defineFilters()` and so on, which you can override, creating a
structured custom class. Also note, that unlike other methods, `__construct()` is exempt from the usual signature compatibility
rules when being extended. Thus, you can specify its signature in your class as you like, defining your own dependencies.
For example:

```php
<?php

use App\Models\User;
use Illuminatech\DataProvider\DedicatedDataProvider;
use Illuminatech\DataProvider\Filters\FilterIn;

class UserPurchasesList extends DedicatedDataProvider
{
    public function __construct(User $user)
    {
        parent::__construct($user->purchases()->with('item'));
    }

    protected function defineConfig(): array
    {
        return [
            'pagination' => [
                'per_page' => [
                    'default' => 16,
                ],
            ],
        ];
    }

    protected function defineFilters(): array
    {
         return [
             'id',
             'status' => new FilterIn('status'),
             // ...
         ];
    }

    protected function defineSort(): array
    {
        return [
            'id',
            'created_at',
            // ...
        ];
    }
    
    // ...
}

// Controller code :

use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $items = (new UserPurchasesList($request->user()))
            ->paginate($request);
            
        // ...
    }
}
```
