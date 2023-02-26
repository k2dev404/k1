<?php

use K1\System\Application;

function p($var = null)
{
	?>
	<pre>
		<?
		print_r($var);
		?>
	</pre>
	<?php
}

function e($var = null)
{
	p($var);

	exit;
}

function app()
{
	return Application::getInstance();
}