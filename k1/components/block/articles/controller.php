<?php
/**
 * @var $this \K1\System\Component
 */

if (!empty($result['id'])) {
	$this->template('detail');
} else {
	$this->template('list');
}