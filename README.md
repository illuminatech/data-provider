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

You can publish predefined configuration file using following console command:

```
php artisan vendor:publish --provider="Illuminatech\DataProvider\DataProviderServiceProvider" --tag=config
```


Usage
-----

This extension allows building of the complex search queries based on the request data. In handles filtering, sorting, pagination,
include of extra fields or relations on demand.

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
                'status',
                'search' => ['name', 'description'],
            ])
            ->sort(['id', 'name', 'status', 'created_at'])
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
            ->paginate($request);
            
        // ...
    }
}
```


### Filtering


### Sorting


### Pagination


### Fields


### Includes


### JSON API Specification Support

This extension is compatible with [JSON API Specification](https://jsonapi.org/) but only with a proper configuration.
Configuration example:

```php
<?php
// file 'config/data_provider.php'

return [];
```
