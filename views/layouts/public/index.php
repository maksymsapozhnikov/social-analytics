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
    <?= $this->render('@app/views/public/fb-pixel') ?>
    <?php $this->head() ?>
</head>
<body>
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
                'label' => TranslateMessage::t('app', 'Language'),
                'url' => null,
                'options' => [
                    'style' => 'opacity:0.5',
                ],
                'linkOptions' => [
                    'style' => 'padding-right:0',
                ],
            ],
            AppHelper::getLanguagesItem(),
            [
                'label' => TranslateMessage::t('user', 'Already registered? Sign in!'),
                'url' => Url::to(['/login']),
                'visible' => \Yii::$app->user->isGuest,
            ],
        ],
        'options' => [
            'class' => 'navbar-nav pull-right',
        ],
    ]);

    NavBar::end();
    ?>
</div>

<?= $content ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
