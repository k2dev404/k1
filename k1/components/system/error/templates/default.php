<?php
/** @var $result array
 */
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $result['message'] ?></title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        ol, li {
            padding: 0;
            margin: 0;
        }

        hr {
            border: none;
            border-top: 1px solid #ddd;
        }

        .header {
            padding: 30px 50px;
        }

        .header__title {
            color: #353c4a;
        }

        .header__message {
            font-size: 25px;
            padding-top: 10px;
        }

        .trace-item {
            border: 1px solid #eaecf0;
            margin: 0 50px 10px 50px;
        }

        .trace-header {
            position: relative;
            padding: 15px 0;
            background-color: #f7f7f7;
        }

        .trace-header__number {
            position: absolute;
            left: 20px;
        }

        .trace-header__title {
            padding: 0 0 0 50px;
        }

        .trace-code {
            padding: 15px 0;
        }

        .trace-code pre {
            margin: 0;
            padding: 0 0 0 50px;
            position: relative;
            z-index: 100;
            overflow-x: auto;
        }


        pre {
            line-height: 20px;
        }

        .code-lines {
            position: relative;
        }

        .code-lines__line {
            position: absolute;
            width: 100%;
            line-height: 20px;
            font-size: 15px;
            color: #aaa;
            padding-left: 20px;
        }

        .code-lines__line--error {
            background-color: #ffe0e0;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="header__title"><?= $result['title'] ?></div>
    <div class="header__message"><?= $result['message'] ?></div>
</div>

<?php
if (!empty($result['trace'])) {
    ?>
    <ol class="trace">
        <?php
        foreach ($result['trace'] as $item) {
            ?>
            <li>
                <div class="trace-item">
                    <div class="trace-header">
                        <div class="trace-header__number"><?= $item['number'] ?></div>
                        <div class="trace-header__title"><?= $item['file'] ?>:<?= $item['line'] ?></div>
                    </div>

                    <div class="trace-code">
                        <div class="code-lines">
                            <?php
                            for ($i = $item['begin'], $line = 1; $i <= $item['end']; $i++, $line++) {
                                ?>
                                <div class="code-lines__line<?= ($i + 1 == $item['line'] ? ' code-lines__line--error' : '') ?>" style="top: <?= ($line * 20 - 20) ?>px"><?= ($i + 1) ?></div>
                                <?php
                            }
                            ?>
                        </div>
                        <pre><?= $item['code'] ?></pre>
                    </div>
                </div>
            </li>
            <?php
        }
        ?>
    </ol>
    <?php
}
?>
</body>
</html>