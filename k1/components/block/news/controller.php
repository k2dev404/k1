<?php
/**
 * @var $app \K1\System\Application
 * @var $this \K1\System\Component
 * @var array $result
 */

$app->setProp('title', 'NEWS');
//$app->addCss('/assets/css/bootstrap.css');
$app->addCss('/assets/css/block/block.css');
$app->addJs('/assets/js/jquery-3.6.0.js');

if (!empty($result['id'])) {
	if ($result['id'] != 1) {
		$app->show404();
	}

	$this->template('detail');
} else {
    $this->template('list');
}