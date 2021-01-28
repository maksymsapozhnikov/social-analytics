<?php
/**
 * @var $dataProvider
 * @var $searchModel
 */

use app\components\FormatHelper as Format;
use app\models\BlockLog;
use kartik\date\DatePicker;
use kartik\form\ActiveForm;
use kartik\export\ExportMenu;
use app\models\Ip2Location;

$this->title = 'Respondents blocked';

$gridColumns = [
    ['class' => 'yii\grid\SerialColumn'],
    [
        'label' => 'Date',
        'format' => 'raw',
        'value' => function($data) {
            return Format::toDate($data->dt, 'd.m.Y H:i:s');
        },
    ],
    [
        'label' => 'Respondent',
        'format' => 'raw',
        'value' => function($data) {
            return $data->respondent->rmsid;
        },
    ],
    [
        'label' => 'IP',
        'value' => 'ip',
    ],
    [
        'label' => 'IP: Country',
        'value' => function($data) {
            return Ip2Location::getDetails($data->ip)->country_name;
        }
    ],
    [
        'label' => 'IP: Region',
        'value' => function($data) {
            return Ip2Location::getDetails($data->ip)->region_name;
        }
    ],
    [
        'label' => 'IP: City',
        'value' => function($data) {
            return Ip2Location::getDetails($data->ip)->city_name;
        }
    ],
    [
        'label' => 'Survey',
        'format' => 'raw',
        'value' => function($data) {
            return $data->survey->rmsid;
        },
    ],
    [
        'label' => 'Survey name',
        'format' => 'raw',
        'value' => function($data) {
            return $data->survey->name;
        },
    ],
    [
        'label' => 'Reason',
        'format' => 'raw',
        'value' => function(BlockLog $data) {
            return $data->getReason();
        },
    ],
    [
        'label' => 'URL blocked',
        'value' => 'uri'
    ],
];

?>

<h1><?= $this->title ?></h1>

<div class="panel panel-success">
    <div class="panel-heading">
        <div class="row">
            <?php
            $form = ActiveForm::begin([
                'method' => 'get',
                'action' => \yii\helpers\Url::to(['/blocks-logs']),
            ]);
            ?>
            <div class="col-xs-3">
                <label class="control-label">Date interval</label>
                <?= DatePicker::widget([
                    'form' => $form,
                    'model' => $searchModel,
                    'attribute' => 'bdt',
                    'attribute2' => 'edt',
                    'type' => DatePicker::TYPE_RANGE,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'dd.mm.yyyy'
                    ]
                ]);
                ?>
            </div>
            <div class="col-xs-4"><?= $form->field($searchModel, 'surveys')->widget(\kartik\select2\Select2::classname(), [
                    'data' => \app\components\QueriesHelper::getSurveysList(),
                    'options' => ['multiple' =>true, 'placeholder' => 'All surveys'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ])->label('Survey RMSID') ?></div>
            <div class="col-xs-1 col-xs-offset-4"><?= \yii\bootstrap\Html::submitButton('<span class="glyphicon glyphicon-search"></span>', [
                    'class' => 'btn btn-primary form-control',
                    'style' => 'margin-top:25px',
                ]) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="pull-left col-xs-2"><?= \Yii::$app->getTimeZone() ?> timezone:<br><b><?= date('d.m.Y H:i') ?></b></div>
            <div class="col-xs-2 pull-right" style="text-align: right;">
                <?= \kartik\export\ExportMenu::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => $gridColumns,
                    'target' => \kartik\export\ExportMenu::TARGET_SELF,
                    'filename' => 'Respondents Blocked',
                    'enableFormatter' => false,
                    'exportConfig' => [
                        ExportMenu::FORMAT_TEXT => false,
                        ExportMenu::FORMAT_PDF => false,
                    ],
                ]);
                ?>
            </div>
        </div>
    </div>
</div>

<?= \kartik\grid\GridView::widget([
    'id' => 'logs-list',
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'label' => 'Date',
            'format' => 'raw',
            'value' => function($data) {
                return Format::toDate($data->dt, 'd.m.Y H:i:s');
            },
            'headerOptions' => ['style' => 'width:80px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'Respondent',
            'format' => 'raw',
            'value' => function($data) {
                return $data->respondent->rmsid;
            },
            'headerOptions' => ['style' => 'width:90px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'Survey',
            'format' => 'raw',
            'value' => function($data) {
                return $data->survey->rmsid;
            },
            'headerOptions' => ['style' => 'width:80px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'Name',
            'format' => 'raw',
            'value' => function($data) {
                return $data->survey->name;
            },
            'headerOptions' => ['style' => 'width:120px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'Reason',
            'format' => 'raw',
            'value' => function(BlockLog $data) {
                return $data->getReason();
            },
            'headerOptions' => ['style' => 'width:250px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'URL blocked',
            'format' => 'raw',
            'value' => function(BlockLog $data) {
                return '<span title="' . $data->uri . '" style="white-space:nowrap;">'
                    . \yii\helpers\StringHelper::truncate($data->uri, 50) . '</span>';
            },
            'headerOptions' => ['style' => 'width:350px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
    ],
]);
