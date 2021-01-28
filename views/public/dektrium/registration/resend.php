<?php
/**
 * @var yii\web\View $this
 * @var dektrium\user\models\ResendForm $model
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = \app\components\helpers\TranslateMessage::t('user', 'Request new confirmation message');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="jumbotron jumbotron-form" style="padding-top: 5vh;">

    <h2><?= Html::encode($this->title) ?></h2>

    <?php $form = ActiveForm::begin([
        'id' => 'resend-form',
        'enableAjaxValidation' => false,
        'enableClientValidation' => false,
    ]); ?>

    <div class="row text-left" style="padding-top: 5vh; margin:0 !important;">
        <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
            <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>
        </div>
    </div>

    <div class="row text-left" style="padding-top:5vh; margin:0 !important;">
        <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
            <?= Html::submitButton(\app\components\helpers\TranslateMessage::t('user', 'Continue'), ['class' => 'btn btn-danger btn-block']) ?><br>

        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
