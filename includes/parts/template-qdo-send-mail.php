<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?= $title ?></title>
    </head>
    <body>
        <h3><?= $observacao ?></h3>
        <hr>
        <?php foreach ($info as $label => $value): ?>
            <p>
                <strong><?= mb_convert_case(preg_replace('/' . QDO_JOB_PREFIX_INPUT_NAME . '/', '', $label), MB_CASE_UPPER) ?>: </strong>
                <?= $value ?>
            </p>
        <?php endforeach; ?>
    </body>
</html>
