<?php

use K1\System\Application;
use K1\System\Config;
use K1\System\ErrorHandler;

spl_autoload_register(function ($class) {
    $class = str_replace(['K1\\', '\\'], ['', '/'], $class);

    $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . $class . '.php';

    if (file_exists($path)) {
        require_once $path;
    }
});

$app = Application::getInstance();
$error = new ErrorHandler();
$root = Application::getDocumentRoot();

try {
    ob_start();
    Config::load($root . '/k1/config.php');
    $error->init();

    $dev = $root . '/k1/dev/index.php';

    if (file_exists($dev)) {
        include_once $dev;
    }
} catch (Exception $e) {
    $error->handleException($e);
}