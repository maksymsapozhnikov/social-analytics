<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\components\AppHelper;
use yii\bootstrap\NavBar;
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Info;
use app\components\AppHelper as App;
use \app\components\FormatHelper;

\app\assets\ManageAsset::register($this);

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
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'id' => 'navbar-top',
        'brandLabel' => '<img src="/favicon.ico" height="50px" style="float:left;padding-left:15px;padding-right:15px;margin-top:-15px;"> TGM Research',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
            'style' => 'color: #FFFFFF !important',
        ],
        'innerContainerOptions' => [
            'class' => 'container-fluid',
        ],
    ]);

    echo AppHelper::environmentBadge();

    echo \yii\bootstrap\Nav::widget([
        'encodeLabels' => false,
        'items' => [
            [
                'label' => 'Surveys',
                'url' => Url::to(['/control/survey-list']),
                'visible' => !\Yii::$app->user->isGuest,
            ],
            [
                'label' => 'Results',
                'url' => Url::to(['/control/result-list']),
                'visible' => !\Yii::$app->user->isGuest,
            ],
            [
                'label' => 'Logs',
                'visible' => !\Yii::$app->user->isGuest,
                'items' => [
                    [
                        'label' => 'Surveys access',
                        'url' => Url::to(['/control/logs']),
                    ],
                    [
                        'label' => 'Respondents blocked',
                        'url' => Url::to(['/blocks-logs']),
                    ],
                ],
            ],
            [
                'label' => 'Blacklists',
                'items' => [
                    [
                        'label' => 'IP Blacklist',
                        'url' => Url::to(['/control/ip-blacklist']),
                        'visible' => !\Yii::$app->user->isGuest,
                    ],
                    [
                        'label' => 'Respondent Blacklist',
                        'url' => Url::to(['/control/respondent-blacklist']),
                        'visible' => !\Yii::$app->user->isGuest,
                    ],
                    [
                        'label' => 'Bad words',
                        'url' => Url::to(['/manage/bad-words']),
                        'visible' => !\Yii::$app->user->isGuest,
                    ],
            ],
                'visible' => !\Yii::$app->user->isGuest,
            ],
            [
                'label' => 'Translations',
                'url' => Url::to(['/translation']),
                'visible' => !\Yii::$app->user->isGuest,
            ],
            [
                'label' => 'Aliases',
                'url' => Url::to(['/manage/aliases']),
                'visible' => !\Yii::$app->user->isGuest,
            ],
            [
                'label' => 'Accounts',
                'url' => Url::to(['/control/account-list']),
                'visible' => !\Yii::$app->user->isGuest,
            ],
            [
                'label' => 'Cost',
                'url' => Url::to(['/manage/report/cost']),
                'visible' => !\Yii::$app->user->isGuest,
            ],
        ],
        'options' => [
            'class' => 'navbar-nav',
        ],
    ]);

    echo \yii\bootstrap\Nav::widget([
        'encodeLabels' => false,
        'items' => [
            [
                'label' => 'TransferTo: <b>' . App::balance() . '</b>',
                'url' => null,
                'options' => [
                    'class' => 'highlighted',
                    'style' => 'color:#FFF',
                    'title' => 'Last update: ' . FormatHelper::toDate(Info::modified(Info::TRANSFERTO_BALANCE)),
                ],
                'visible' => !\Yii::$app->user->isGuest,
            ],
            [
                'label' => 'Login',
                'url' => Url::to(['/login']),
                'visible' => \Yii::$app->user->isGuest,
            ],
            [
                'label' => 'Logout',
                'url' => Url::to(['/logout']),
                'visible' => !\Yii::$app->user->isGuest,
            ],
        ],
        'options' => [
            'class' => 'navbar-nav pull-right',
        ],
    ]);

    NavBar::end();
    ?>
    <div class="container-fluid">
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container-fluid">
        <p class="pull-left">&copy; TGM Research, v<?= Yii::$app->version ?>, <?= date('Y') ?></p>
    </div>
</footer>

<?= $this->render('@app/views/control/_message') ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
