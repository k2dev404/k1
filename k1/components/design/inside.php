<?php
/**
 * @var $this \K1\System\Application
 */

?>
<!doctype html>
<html lang="ru">
<head>
    <?php
    $this->include('block/head');
    ?>
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