<?php

return [
    'driver' => env('MODEL_SETTINGS_DRIVER', 'field'),

    'drivers' => [
        'field' => [
            'class' => \Glorand\Model\Settings\Managers\FieldSettingsManager::class,
            'field_name' => env('MODEL_SETTINGS_FIELD_NAME', 'settings'),
            'persistent' => env('MODEL_SETTINGS_PERSISTENT', true),
            // used only for the internal schema-check cache
            'cache_prefix' => env('MODEL_SETTINGS_FIELD_CACHE_PREFIX', 'model_settings:'),
        ],
        'table' => [
            'class' => \Glorand\Model\Settings\Managers\TableSettingsManager::class,
            'table_name' => env('MODEL_SETTINGS_TABLE_NAME', 'model_settings'),
            'use_cache' => env('MODEL_SETTINGS_TABLE_USE_CACHE', true),
            'cache_prefix' => env('MODEL_SETTINGS_TABLE_CACHE_PREFIX', 'model_settings:'),
        ],
        'redis' => [
            'class' => \Glorand\Model\Settings\Managers\RedisSettingsManager::class,
            'connection' => env('MODEL_SETTINGS_REDIS_CONNECTION'), // null = default connection
            'key_prefix' => env('MODEL_SETTINGS_REDIS_PREFIX', 'r-k-'),
        ],
    ],

    // Legacy flat keys - still read by the storage traits, removed later in the v9 refactor.
    'settings_field_name' => env('MODEL_SETTINGS_FIELD_NAME', 'settings'),
    'settings_table_name' => env('MODEL_SETTINGS_TABLE_NAME', 'model_settings'),
    'settings_persistent' => env('MODEL_SETTINGS_PERSISTENT', true),
    'settings_table_use_cache' => env('MODEL_SETTINGS_TABLE_USE_CACHE', true),
    'settings_table_cache_prefix' => env('MODEL_SETTINGS_TABLE_CACHE_PREFIX', 'model_settings:'),

    'defaultSettings' => [
        'users' => [

        ]
    ]
];
