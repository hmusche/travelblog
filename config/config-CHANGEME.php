<?php

return [
    'host' => 'http://HOSTNAME',                // either http or https
    'cdnhost' => 'http://CDN_HOSTNAME',         // optional, can be the same as 'host'
    'path' => '/',                              // path to be appended to host
    'asset_path' => 'asset/',                   // file path for assets
    'default_timezone' => 'Europe/Berlin',      // default timezone of installation

    'mapbox_token' => 'MAPBOX_TOKEN',           // your MapBox Application token
    'timezonedb_token' => 'TimeZoneDB_TOKEN',   // Your timezonedb.com token

    'namespace' => 'TravelBlog',                // Namespace of Application

    'translation' => [
        'type' => 'model',                      // Translation source for Application
        'source' => 'TravelBlog\\Model\Translation',

        'supported_locales' => [                // Supported locales in installation
            'en',
            'de'
        ]
    ],

    'db' => [                                   // SQL config,
    	'database_type' => 'mysql',
    	'database_name' => 'SQL_DATABASENAME',
    	'server' => 'localhost',

	    'charset' => 'utf8mb4',

    	'username' => 'SQL_USERNAME',
    	'password' => 'SQL_PASSWORD',
    ]
];
