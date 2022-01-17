<?php
/**
 * @see https://github.com/illuminatech/data-provider
 */

return [
    'filter' => [
        'keyword' => 'filter',
    ],
    'sort' => [
        'keyword' => 'sort',
        'enable_multisort' => false,
    ],
    'pagination' => [
        'keyword' => null,
        'appends' => true, // whether to automatically append query string values to the paginator.
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
