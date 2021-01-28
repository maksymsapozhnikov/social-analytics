<?php
/**
 * @var $this \yii\web\View
 * @var $content string
 */

use yii\helpers\Html;
use app\components\AppHelper;

?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow" />
    <link rel="icon" href="/favicon.ico?v=<?= Yii::$app->version ?>" type="image/x-icon" />
    <?= Html::csrfMetaTags() ?>
    <title><?= AppHelper::environmentPreffix() ?><?= Html::encode($this->title) ?></title>
    <style type="text/css">
        body, html {
            margin: 0; padding: 0; height: 100%; overflow: hidden;
        }

        #content {
            position:absolute; left: 0; right: 0; bottom: 0; top: 0;
        }

        iframe {
            height: 1px;
            min-height: 100%;
            max-height: 100%;
            *height: 100%;
            overflow-y: auto;
        }
    </style>
    <?php $this->head() ?>
</head>
<body>
<div id="content">
    <?= $content ?>
</div>
</body>
</html>
