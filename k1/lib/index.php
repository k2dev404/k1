<?php

use K1\System\Application;
use K1\System\Config;
use K1\System\ErrorHandler;
use K1\System\Exceptions\SystemException;

include_once 'function.php';
//include_once realpath(dirname(__FILE__) . '/..') . '/vendor/autoload.php';

spl_autoload_register(function ($class) {
    $class = str_replace(['K1\\', '\\'], ['', '/'], $class);

    $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . $class . '.php';

    if (file_exists($path)) {
        require_once $path;
    }
});

$app = Application::getInstance();
$error = new ErrorHandler();

try {
    ob_start();
    Config::load(Application::getDocumentRoot() . '/k1/config.php');
    $error->init();
} catch (Exception $e) {
    $error->handleException($e);
}