<?php
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var dektrium\user\models\RecoveryForm $model
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use himiklab\yii2\recaptcha\ReCaptcha;

$this->title = \app\components\helpers\TranslateMessage::t('user', 'Reset your password');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="jumbotron jumbotron-form" style="padding-top: 5vh;">

    <h2><?= Html::encode($this->title) ?></h2>

    <?php

    $form = ActiveForm::begin([
        'id' => 'resend-form',
        'enableAjaxValidation' => false,
        'enableClientValidation' => false,
    ]);

    ?>

    <div class="row text-left" style="padding-top: 5vh; margin:0 !important;">
        <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
            <?= $form->field($model, 'password')->passwordInput() ?>

            <?= $form->field($model, 'confirm_password')->passwordInput() ?>

            <div style="padding-top:3vh; margin:0 !important;">
                <?= $form->field($model, 'recaptcha')
                    ->label(false)
                    ->widget(ReCaptcha::className())
                ?>
            </div>
        </div>
    </div>

    <div class="row text-left" style="padding-top:5vh; margin:0 !important;">
        <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
            <?= Html::submitButton(\app\components\helpers\TranslateMessage::t('user', 'Continue'), ['class' => 'btn btn-danger btn-block']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
