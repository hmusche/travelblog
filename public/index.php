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

/**
 * Merge credentials.php in config dir for secrets not to be pushed in git
 */
$credentials = include('config/credentials.php');
$config      = include('config/config.php');
$version     = include('config/version.php');
$config      = array_replace_recursive($config, $credentials);

$config['version'] = $version;

Solsken\Profiler::start();

$app = new Solsken\Application($config);
$app->run();
