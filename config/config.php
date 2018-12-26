<?php

return [
    'host' => 'http://localhost',
    'path' => '/~hmu/travelblog/public/',

    'namespace' => 'TravelBlog',

    'default_timezone' => 'Europe/Berlin',

    'translation' => [
        'type' => 'model',
        'source' => 'TravelBlog\\Model\Translation',

        'supported_locales' => [
            'de_DE',
            'en_GB'
        ]
    ],

    'db' => [
    	'database_type' => 'mysql',
    	'database_name' => 'travelblog',
    	'server' => 'localhost',

	    'charset' => 'utf8mb4',
    ]
];
