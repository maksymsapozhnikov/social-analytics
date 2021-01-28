<?php
/**
 * @var yii\web\View $this
 * @var dektrium\user\models\User $model
 * @var dektrium\user\Module $module
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = \app\components\helpers\TranslateMessage::t('user', 'Sign up');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="jumbotron jumbotron-form" style="padding-top: 5vh;">
    <h2><?= Html::encode($this->title) ?></h2>
    <div class="row text-left" style="padding-top: 5vh; margin:0 !important;">
        <div class="col-md-6 col-md-offset-3 col-sm-10 col-sm-offset-1">
            <p style="font-size:1.7rem">
                <?= \app\components\helpers\TranslateMessage::t('user', 'Your account has been created and a message with further instructions has been sent to your email') ?>
            </p>
        </div>
    </div>
    <div class="row text-left" style="padding-top: 5vh; margin:0 !important;">
        <div class="col-md-6 col-md-offset-3 col-sm-10 col-sm-offset-1">
            <?= Html::a(\app\components\helpers\TranslateMessage::t('user', 'Sign in'), ['/login'], ['class' => 'btn btn-danger btn-block']) ?>
        </div>
    </div>
</div>
