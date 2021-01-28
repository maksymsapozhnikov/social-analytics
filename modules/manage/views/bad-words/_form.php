<?php
/**
 * BadWords editing/creating form
 * @var $model \app\modules\manage\models\BadWords
 */

use app\components\QueriesHelper;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use kartik\select2\Select2;
use wbraganca\tagsinput\TagsinputWidget;

$form = ActiveForm::begin([
    'id' => 'survey-form',
    'enableClientValidation' => true,
]);

$countries = \app\components\QueriesHelper::getAllCountries();

?>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="col-xs-12">
            <?= $form->field($model, 'country')
                ->widget(Select2::classname(), [
                'data' => QueriesHelper::getAllCountries(),
                'options' => ['multiple' => false, 'placeholder' => 'Choose country'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ])->label('Country') ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <?= $form->field($model, 'words')
                    ->widget(TagsinputWidget::classname(), [
                        'clientOptions' => [
                            'trimValue' => true,
                            'allowDuplicates' => false,
                        ],
                    ])
                    ->label('Words') ?>
            </div>
        </div>
    </div>
</div>

<div class="form-group text-center">
    <?= Html::submitButton($model->isNewRecord ? 'Create List' : 'Save List', ['class' => 'btn-lg btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>