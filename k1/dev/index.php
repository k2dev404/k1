<?php
/** @var Application $app */

use K1\Router\Router;
use K1\System\Application;

$router = Router::getInstance();

$app->setDesign('design/inside');
$app->setProp('title', 'Внутренняя страница');

$router->get('/', function () use ($app) {
	$app->setDesign('design/index');
	$app->setProp('title', 'Главная страница');

	$app->component('page/index');
});

$router->get('/news/', function () use ($app) {
	$app->component('block/news');
});

$router->get('/news/(\d+)\.html', function ($id) use ($app) {
	$app->component('block/news', [
		'id' => $id,
	]);
});

$router->set404(function () use ($app) {
	$app->setDesign('design/404');

	//$app->setProp('title', '404');
	//echo 'Страница не найдена';
});

$app->run();