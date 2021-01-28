<?php
/**
 * @var yii\web\View $this
 * @var dektrium\user\models\LoginForm $model
 * @var dektrium\user\Module $module
 */

use himiklab\yii2\recaptcha\ReCaptcha;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = \app\components\helpers\TranslateMessage::t('user', 'Sign in');
$this->params['breadcrumbs'][] = $this->title;

?>

<?= $this->render('/_alert', ['module' => Yii::$app->getModule('user')]) ?>

<div class="jumbotron jumbotron-form" style="padding-top: 5vh">

    <h2><?= Html::encode($this->title) ?></h2>

    <div class="row text-left" style="padding-top: 5vh; margin:0 !important;">
        <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'enableAjaxValidation' => false,
                'enableClientValidation' => false,
                'validateOnBlur' => false,
                'validateOnType' => false,
                'validateOnChange' => false,
            ]) ?>

            <?= $form->field($model, 'login',
                ['inputOptions' => ['autofocus' => 'autofocus', 'class' => 'form-control', 'tabindex' => '1']]
            ) ?>

            <?= $form->field(
                $model,
                'password',
                ['inputOptions' => ['class' => 'form-control', 'tabindex' => '2']])
                ->passwordInput()
                ->label(
                    \app\components\helpers\TranslateMessage::t('user', 'Password')
                    . ($module->enablePasswordRecovery ?
                        ' (' . Html::a(
                            \app\components\helpers\TranslateMessage::t('user', 'Forgot password?'),
                            ['/user/recovery/request'],
                            ['tabindex' => '5']
                        )
                        . ')' : '')
                ) ?>

            <?= $form->field($model, 'rememberMe')->checkbox(['tabindex' => '3']) ?>

            <div>
                <?= $form->field($model, 'recaptcha')
                    ->label(false)
                    ->widget(ReCaptcha::className())
                ?>
            </div>

            <?= Html::submitButton(
                \app\components\helpers\TranslateMessage::t('user', 'Sign in'),
                ['class' => 'btn btn-danger btn-block', 'tabindex' => '4']
            ) ?>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?= $this->render('@app/views/public/social-buttons', []) ?>

    <div class="row text-left" style="padding-top: 2vh; margin:0 !important;">
        <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">

        <?php if ($module->enableConfirmation) { ?>
            <div class="col-xs-12 text-center" style="padding-top: 1vh; margin:0 !important;">
                <?= Html::a(\app\components\helpers\TranslateMessage::t('user', 'Didn\'t receive confirmation message?'), ['/user/registration/resend']) ?>
            </div>
        <?php } ?>

        <?php if ($module->enableRegistration) { ?>
            <div class="col-xs-12 text-center" style="padding-top: 1vh; margin:0 !important;">
                <?= Html::a(\app\components\helpers\TranslateMessage::t('user', 'Don\'t have an account? Sign up!'), ['/user/registration/register']) ?>
            </div>
        <?php }  ?>

        </div>
    </div>
</div>
