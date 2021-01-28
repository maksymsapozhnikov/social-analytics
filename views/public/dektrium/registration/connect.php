<?php
/**
 *
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var dektrium\user\models\User $model
 * @var dektrium\user\models\Account $account
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('user', 'Sign in');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="jumbotron jumbotron-form" style="padding-top: 5vh">

    <h2><?= Html::encode($this->title) ?></h2>

    <div class="row text-left" style="padding-top: 5vh; margin:0 !important;">
        <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
            <p>
                <?= \app\components\helpers\TranslateMessage::t(
                    'user',
                    'In order to finish your registration, we need you to enter following fields'
                ) ?>
            </p>

            <?php $form = ActiveForm::begin([
                'id' => 'connect-account-form',
            ]); ?>

            <?= $form->field($model, 'email') ?>

            <?= $form->field($model, 'username') ?>

            <?= Html::submitButton(Yii::t('user', 'Continue'), ['class' => 'btn btn-success btn-block']) ?>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
