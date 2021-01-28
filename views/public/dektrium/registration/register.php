<?php
/**
 * @var yii\web\View $this
 * @var dektrium\user\models\User $model
 * @var dektrium\user\Module $module
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use himiklab\yii2\recaptcha\ReCaptcha;

$this->title = \app\components\helpers\TranslateMessage::t('user', 'Sign up');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="jumbotron jumbotron-form" style="padding-top: 5vh;">

    <h2><?= Html::encode($this->title) ?></h2>

    <?php $form = ActiveForm::begin([
        'id' => 'registration-form',
        'enableAjaxValidation' => false,
        'enableClientValidation' => false,
    ]); ?>

    <div class="row text-left" style="padding-top: 5vh; margin:0 !important;">
        <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">

            <?= $form->field($model, 'name') ?>

            <?= $form->field($model, 'last_name') ?>

            <?= $form->field($model, 'email') ?>

            <?php if ($module->enableGeneratingPassword == false) { ?>
                <?= $form->field($model, 'password')->passwordInput() ?>
            <?php } ?>

            <div style="padding-top: 5vh; margin:0 !important;">
                <?= $form->field($model, 'recaptcha')
                    ->label(false)
                    ->widget(ReCaptcha::className())
                ?>
            </div>
        </div>
    </div>

    <div class="row text-left" style="padding-top:5vh; margin:0 !important;">
        <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
            <?= Html::submitButton(\app\components\helpers\TranslateMessage::t('user', 'Sign up'), ['class' => 'btn btn-danger btn-block']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

    <?= $this->render('@app/views/public/social-buttons', []) ?>
</div>
