<?php

ini_set('display_errors', true);
ini_set('display_startup_errors', true);
error_reporting(E_ALL);
date_default_timezone_set('Europe/Berlin');

chdir(dirname(__DIR__));

require "vendor/autoload.php";

spl_autoload_register(function ($class) {
    $parts = explode('\\', $class);

    if ($parts[0] == 'TravelBlog') {
        $parts[0] = 'library';
        $file = implode(DIRECTORY_SEPARATOR, $parts) . '.php';

        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    return false;
});

$app = new TravelBlog\Application();
$app->run();
