<?php
/**
 * @var $this \K1\System\Component;
 * @var $result array;
 */

use K1\Web\Pagination;

if (empty($result)) {
    return;
}

$ob = new Pagination($result['total'], $result['size'], $result['current']);

$this->template('', $ob->getCalculate());