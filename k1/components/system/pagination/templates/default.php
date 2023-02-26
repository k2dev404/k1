<?php
/** @var $result array
 */

if ($result['first']) {
    ?>
    <a href="<?=$result['first']['url']?>"> << </a>
    <?php
}

if ($result['prev']) {
    ?>
    <a href="<?=$result['prev']['url']?>"> < </a>
    <?php
}

foreach ($result['pages'] as $item) {
    ?>
    <a href="<?= $item['url'] ?>">
        <?php
        if (!empty($item['current'])) {
            ?>
            <b><?= $item['page'] ?></b>
            <?php
        } else {
            echo $item['page'];
        }
        ?>
    </a>
    <?php
}

if ($result['next']) {
    ?>
    <a href="<?=$result['next']['url']?>"> > </a>
    <?php
}


if ($result['last']) {
    ?>
    <a href="<?=$result['last']['url']?>"> >> </a>
    <?php
}
