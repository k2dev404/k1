<?php
/**
 * @var $this \K1\System\Application
 */

?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <?php
    $this->showCss();
    $this->showJs();
    ?>
    <title><?= $this->getProp('title') ?></title>
</head>
<body>
<header>
    HEADER INSIDE PAGE
    <h1><?= $this->getProp('title') ?></h1>
</header>
<fieldset>
    <legend>
        content
    </legend>
    <?= $this->getContent() ?>
</fieldset>
<footer>
    FOOTER
</footer>
</body>
</html>