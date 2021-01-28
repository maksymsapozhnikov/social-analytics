<?php
/**
 * @var $model \app\models\Language
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;

$action = Url::to(['/translation/update', 'id' => $model->id]);
$submit = 'Save';

$form = ActiveForm::begin([
    'id' => 'survey-form',
    'action' => $action,
    'enableClientValidation' => true,
]);

?>

<h3>Language details</h3>

<div class="row">
    <div class="col-xs-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-2">
                        <?= $form->field($model, 'lang') ?>
                    </div>
                    <div class="col-xs-5">
                        <?= $form->field($model, 'name')->label('Language name') ?>
                    </div>
                    <div class="col-xs-4">
                        <?= $form->field($model, 'native_name')->label('Native language name') ?>
                    </div>
                    <div class="col-xs-1">
                        <?= Html::submitButton($submit, ['class' => 'btn btn-primary pull-right',
                            'style' => 'margin-top:25px']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ActiveForm::end();
