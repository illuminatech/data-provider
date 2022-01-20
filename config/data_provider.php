<?php
/**
 * @see https://github.com/illuminatech/data-provider
 * @see \Illuminatech\DataProvider\DataProvider::__construct()
 * @see \Illuminatech\DataProvider\Selector::__construct()
 * @see \Illuminatech\DataProvider\Sort::__construct()
 * @see \Illuminatech\DataProvider\Pagination::__construct()
 */

return [
    'fields' => [
        'keyword' => 'fields',
        'source_self_name' => false,
    ],
    'include' => [
        'keyword' => 'include',
    ],
    'filter' => [
        'keyword' => 'filter',
    ],
    'sort' => [
        'keyword' => 'sort',
        'enable_multisort' => false,
    ],
    'pagination' => [
        'keyword' => null,
        'appends' => true,
        'page' => [
            'keyword' => 'page',
        ],
        'per_page' => [
            'keyword' => 'per-page',
            'min' => 1,
            'max' => 100,
            'default' => 15,
        ],
        'cursor' => [
            'keyword' => 'cursor',
        ],
    ],
];
