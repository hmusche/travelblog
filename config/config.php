<?php

return [
    'host' => 'http://localhost',
    'cdnhost' => 'http://localhost',
    'path' => '/~hmu/travelblog/public/',
    'asset_path' => 'asset/',

    'namespace' => 'TravelBlog',

    'title' => 'no-fly-zone.de',

    'default_timezone' => 'Europe/Berlin',

    'journey' => [
        'start' => '2019-02-18'
    ],

    'translation' => [
        'type' => 'model',
        'source' => 'TravelBlog\\Model\Translation',

        'supported_locales' => [
            'en',
            'de'
        ]
    ],

    'db' => [
    	'database_type' => 'mysql',
    	'database_name' => 'travelblog',
    	'server' => 'localhost',

	    'charset' => 'utf8mb4',
    ]
];
