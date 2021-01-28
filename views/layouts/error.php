<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\assets\AppAsset;
use app\components\AppHelper;
use app\models\Language;
use yii\helpers\Html;

AppAsset::register($this);

$lang = Language::findOne(['lang' => Yii::$app->language]);
$isRtl = $lang ? $lang->is_rtl : false;

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow" />
    <link rel="icon" href="/favicon.ico?v=<?= Yii::$app->version ?>" type="image/x-icon" />
    <link href="https://tgmpanel.com/tgmmobi.css?v=<?= Yii::$app->version ?>" rel="stylesheet" type="text/css"/>
    <?= Html::csrfMetaTags() ?>
    <title><?= AppHelper::environmentPreffix() ?><?= Html::encode($this->title) ?></title>
    <?= $this->render('@app/views/public/fb-pixel') ?>
    <?php if ($isRtl) { ?><style>html {direction: rtl}</style><?php } ?>
    <style>
        @media screen and (max-width: 768px) {
            html, body {
                height: 100% !important;
                font-size: 18px !important;
            }
            .wrap > .container, .wrap > .container-fluid {
                margin-top: 55px !important;
                width: 100% !important;
            }
        }
    </style>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <div class="container">
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; TGM Research, <?= date('Y') ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
