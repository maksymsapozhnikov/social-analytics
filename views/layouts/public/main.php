<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\assets\PublicAsset;
use app\components\AppHelper;
use yii\bootstrap\NavBar;
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\helpers\TranslateMessage;

PublicAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow" />
    <link rel="icon" href="/favicon.ico?v=<?= Yii::$app->version ?>" type="image/x-icon" />
    <?= Html::csrfMetaTags() ?>
    <title><?= AppHelper::environmentPreffix() ?><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="white-page">
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => '<img src="/favicon.ico" height="50px" style="float:left;padding-left:15px;padding-right:15px;margin-top:-15px;"> TGM Research',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse',
            'style' => 'color: #FFFFFF !important',
        ],
    ]);

    echo AppHelper::environmentBadge();

    echo \yii\bootstrap\Nav::widget([
        'encodeLabels' => false,
        'items' => [

            [
                'label' => '',
                'url' => '#',
            ],
            [
                'label' => \app\components\helpers\PublicHelper::getUsername(),
                'items' => [
                    [
                        'label' => TranslateMessage::t('app', 'Profile'),
                        'url' => Url::to(['/profile']),
                    ],
                    '<li class="divider"></li>',
                    [
                        'label' => TranslateMessage::t('app', 'Logout'),
                        'url' => Url::to(['/logout']),
                    ],
                ],
            ],
        ],
        'options' => [
            'class' => 'navbar-nav pull-right',
        ],
    ]);

    NavBar::end();
    ?>

    <div class="container" style="min-height:90vh">
        <?= $content ?>
    </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
