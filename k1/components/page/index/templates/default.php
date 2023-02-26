<?php
/**
 * @var $this \K1\System\Component;
 * @var $app \K1\System\Application;
 */

?>
<fieldset>
	<legend>Новости на главной</legend>
    <?php
	$app->component('block/news');
	?>
</fieldset>
<fieldset>
	<legend>Статьи на главной</legend>
    <?php
	$app->component('block/articles');
	?>
</fieldset>