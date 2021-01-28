<?php
/**
 *
 * @var \app\models\search\ResultsExtendedSearch $searchModel
 * @var integer $pageSize
 */

use kartik\form\ActiveForm;
use app\models\RespondentSurveyStatus as Status;
use yii\bootstrap\Modal;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use app\components\enums\ResponseField;
use app\components\QueriesHelper;

Modal::begin([
    'options' => [
        'id' => 'kartik-modal',
        'tabindex' => false,
    ],
    'headerOptions' => [
        'style' => 'background-color: #d6e9c6',
    ],
    'size' => Modal::SIZE_LARGE,
    'header' => '<h4 class="modal-title">Extended Results Filter</h4>',
    'toggleButton' => ['label' => '<span class="glyphicon glyphicon-filter"></span> Extended results filter',
        'class' => $searchModel->isLoaded ? 'btn btn-success' : 'btn btn-default'
    ],
    'footer' => ' <button type="button" class="btn btn-primary search-button"><span class="glyphicon glyphicon-search"></span> Search</button><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>',
]);

$form = ActiveForm::begin([
    'id' => 'extended-search',
    'method' => 'get',
    'action' => \yii\helpers\Url::to(['/control/result-list']),
]);
?>
<?= \yii\helpers\Html::hiddenInput('per-page', $pageSize) ?>

<div class="row">
    <div class="col-xs-4"><?= $form->field($searchModel, 'resp')
            ->label('RMSID') ?>
    </div>
    <div class="col-xs-8"><?= $form->field($searchModel, 'rmsid')->widget(\kartik\select2\Select2::classname(), [
            'data' => \app\components\QueriesHelper::select2Surveys(),
            'options' => [
                'multiple' =>true,
                'placeholder' => 'All surveys'
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]) ?>
    </div>
</div>
<div class="row">
    <div class="col-xs-4">
        <label class="control-label">Started</label>
        <?= DatePicker::widget([
            'form' => $form,
            'model' => $searchModel,
            'attribute' => 'start_date',
            'attribute2' => 'end_date',
            'type' => DatePicker::TYPE_RANGE,
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'dd.mm.yyyy'
            ]
        ]); ?>
    </div>
    <div class="col-xs-4"><?= $form->field($searchModel, 'statuses')->widget(Select2::classname(), [
            'data' => Status::SHORT_TITLES,
            'options' => ['multiple' =>true, 'placeholder' => 'All except in progress'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label('Status') ?>
    </div>
    <div class="col-xs-4"><?= $form->field($searchModel, 'suspicious')->widget(Select2::classname(), [
            'data' => \app\models\enums\SuspiciousStatus::$titles,
            'options' => ['multiple' =>true, 'placeholder' => 'All'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label('Suspicious') ?>
    </div>
</div>
<div class="row">
    <div class="panel panel-default col-xs-12">
        <div class="panel-heading">Responses</div>
        <div class="panel-body">
            <?php
            $exceptKeys = [ResponseField::EMAIL, ResponseField::PHONE, ResponseField::PHONE_MERGED, ResponseField::TAPAFF];
            $filterKeys = QueriesHelper::getAnswersKeys();
            $filterKeys = array_diff($filterKeys, $exceptKeys);
            foreach($filterKeys as $key) { ?>
                <div class="col-xs-6"><?= $form->field($searchModel, 'filters[' . $key . '][]')->widget(Select2::classname(), [
                    'data' => \app\components\QueriesHelper::getAnswersValues($key),
                    'options' => ['multiple' =>true, 'placeholder' => ''],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ])->label($key) ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php Modal::end(); ?>

<?php

$js = <<<JS
    $(function() {
        $('.search-button').click(function(){
            $('#extended-search').submit();
        });
    });
JS;

$this->registerJs($js, \yii\web\View::POS_READY);
