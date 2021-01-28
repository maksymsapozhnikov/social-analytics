<?php
/**
 * @var $this yii\web\View
 * @var $searched \app\models\search\ResultsSearch
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel \app\models\search\ResultsSearch
 * @var $extendedSearchModel \app\models\search\ResultsExtendedSearch
 */

use app\components\FormatHelper as Format;
use app\components\QueriesHelper;
use app\models\RespondentSurveyStatus;
use app\models\RespondentSurveyStatus as Status;
use kartik\export\ExportMenu;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

$this->title = 'Results';

$additionalColumns = ['Age', 'Country', 'Gender'];
$exportColumns = $searched->exportColumns();

$pageSizes = [20 => '20', '50' => '50', '100' => '100', '200' => '200', '500' => '500'];
$pageSize = \Yii::$app->request->get('per-page') ?: $dataProvider->pagination->pageSize;

$this->registerJsFile('@web/js/control/results-list.js', ['depends' => \app\assets\ManageAsset::class]);

?>

<h1><?= $this->title ?></h1>

<div class="panel panel-success">
    <div class="panel-heading">
        <div class="row">
            <?php
            $form = ActiveForm::begin([
                'id' => 'search',
                'method' => 'get',
                'action' => \yii\helpers\Url::to(['/control/result-list']),
            ]);

            echo \yii\helpers\Html::hiddenInput('per-page', $pageSize);

            ?>
            <div class="col-xs-2">
                <?= $form->field($searchModel, 'resp')->label('RMSID') ?>
            </div>
            <div class="col-xs-5"><?= $form->field($searchModel, 'rmsid')->widget(\kartik\select2\Select2::classname(), [
                    'data' => \app\components\QueriesHelper::select2Surveys(),
                    'options' => ['multiple' =>true, 'placeholder' => 'All surveys'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ])->label('Survey') ?></div>
            <div class="col-xs-5"><?= $form->field($searchModel, 'statuses')->widget(\kartik\select2\Select2::classname(), [
                    'data' => Status::TITLES,
                    'options' => ['multiple' =>true, 'placeholder' => 'All except in progress'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ])->label('Status') ?>
            </div>
            <div class="col-xs-6">
                <?= $form->field($searchModel, 'project_identifiers')->widget(Select2::class, [
                    'data' => QueriesHelper::getResponseProjectIdentifiers(),
                    'options' => ['multiple' => true, 'placeholder' => 'All identifiers'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ])->label('Project_ID') ?>
            </div>
            <div class="col-xs-5">
                <?= $form->field($searchModel, 'countries')->widget(Select2::class, [
                    'data' => QueriesHelper::getResponseCountries(),
                    'options' => ['multiple' => true, 'placeholder' => 'All countries'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ])->label('Country (+Project_ID exists)') ?>
            </div>
            <div class="col-xs-1"><?= \yii\bootstrap\Html::submitButton('<span class="glyphicon glyphicon-search"></span>', [
                    'class' => 'btn btn-primary form-control',
                    'style' => 'margin-top:25px',
                ]) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <?php if (0) { ?>
            <div class="col-xs-2">
                <?= $this->render('result-filter-extended', [
                    'searchModel' => $extendedSearchModel,
                    'pageSize' => $pageSize,
                ]) ?>
            </div>
            <?php } ?>
            <div class="col-xs-2">
                <?= \nterms\pagesize\PageSize::widget([
                    'defaultPageSize' => \app\models\search\ResultsSearch::DEFAULT_PAGESIZE,
                    'sizes' => $pageSizes,
                    'template' => '{list}',
                    'options' => [
                        'class' => 'form-control',
                    ]
                ]) ?>
            </div>
            <div class="col-xs-2 pull-right" style="text-align: right;">
                <?= \kartik\export\ExportMenu::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => $exportColumns,
                    'target' => \kartik\export\ExportMenu::TARGET_SELF,
                    'filename' => $searched->getExportFilename(),
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

<?php
if ($searched->validate()) {

    $webColumns = [
        [
            'label' => 'RMSID',
            'attribute' => 'respondent',
            'format' => 'raw',
            'value' => function ($data) {
                return $data->respondent->rmsid;
            },
            'headerOptions' => ['style' => 'width:80px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center success-column', 'onClick' => 'onRespondentClick(this)'],
        ],
        [
            'label' => 'BL',
            'attribute' => 'respondent_status',
            'format' => 'raw',
            'value' => function ($data) {
                return $data->respondent->status == \app\models\RespondentStatus::DISQUALIFIED
                        ? '<span title="This Respondent is blacklisted" class="text-danger glyphicon glyphicon-alert"></span>'
                        : null;
            },
            'headerOptions' => ['style' => 'width:5px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'Src',
            'format' => 'raw',
            'value' => function ($data) {
                /** @var \app\models\search\ResultsSearch $data */
                return $data->respondent->traffic_source;
            },
            'headerOptions' => ['style' => 'width:5px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'S',
            'attribute' => 'suspicious',
            'format' => 'raw',
            'value' => function ($data) {
                $title = $data->suspicious != \app\models\enums\SuspiciousStatus::LEGAL ? \app\models\enums\SuspiciousStatus::getTitle($data->suspicious) : null;

                return $title
                    ? '<span title="' . $title . '" class="text-danger glyphicon glyphicon-alert"></span>'
                    : null;
            },
            'headerOptions' => ['style' => 'width:5px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'Survey',
            'attribute' => 'survey_rmsid',
            'format' => 'raw',
            'value' => function ($data) {
                return $data->survey->rmsid;
            },
            'headerOptions' => ['style' => 'width:80px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'Name',
            'attribute' => 'survey_name',
            'format' => 'raw',
            'value' => function ($data) {
                return $data->survey->name;
            },
            'headerOptions' => ['style' => 'width:170px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'Started',
            'format' => 'raw',
            'attribute' => 'started_at',
            'value' => function ($data) {
                return Format::toDate($data->started_at, 'd.m.Y H:i:s');
            },
            'headerOptions' => ['style' => 'width:50px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        /*
        [
            'label' => 'Finished',
            'format' => 'raw',
            'attribute' => 'finished_at',
            'value' => function ($data) {
                return $data->finished_at > 0 ? Format::toDate($data->finished_at, 'd.m.Y H:i:s') : null;
            },
            'headerOptions' => ['style' => 'width:50px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        */
        [
            'label' => 'Time, sec',
            'attribute' => 'time_sec',
            'format' => 'raw',
            'value' => function ($data) {
                return $data->finished_at > 0 ? $data->finished_at - $data->started_at : null;
            },
            'headerOptions' => ['style' => 'width:70px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'TimeSc',
            /* 'attribute' => 'time_sec', */
            'format' => 'raw',
            'value' => function (\app\models\RespondentSurvey $data) {
                return \app\components\FormatHelper::timingScoreSum($data->timing_score_sum ?: null);
            },
            'headerOptions' => ['style' => 'width:70px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'avgTime',
            /* 'attribute' => 'time_sec', */
            'format' => 'raw',
            'value' => function (\app\models\RespondentSurvey $data) {
                return \app\components\FormatHelper::timingScoreAvg($data->timing_score_avg ?: null);
            },
            'headerOptions' => ['style' => 'width:70px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'TrustSc',
            'attribute' => 'dirty_score',
            'format' => 'raw',
            'value' => function (\app\models\RespondentSurvey $data) {
                return \app\components\FormatHelper::dirtyScore(100 - $data->dirty_score);
            },
            'headerOptions' => ['style' => 'width:70px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'Phone',
            'attribute' => 'phone',
            'format' => 'raw',
            'headerOptions' => ['style' => 'width:70px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'Tryings',
            'format' => 'raw',
            'attribute' => 'tryings',
            'value' => 'tryings',
            'headerOptions' => ['style' => 'width:70px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'Status',
            'format' => 'raw',
            'attribute' => 'status',
            'value' => function($data) {
                return RespondentSurveyStatus::getShortTitle($data->status);
            },
            'headerOptions' => ['style' => 'width:60px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
    ];

    foreach($additionalColumns as $additionalColumn) {
        $column = null;
        foreach ($searched->responseColumns as $responseColumn) {
            if ($responseColumn['attribute'] == 'response__' . $additionalColumn) {
                $column = $responseColumn;
                break;
            }
        }

        if ($column) {
            $column['format'] = 'raw';
            $column['label'] = $additionalColumn;
            $column['attribute'] = 'response_' . $additionalColumn;
            $column['headerOptions'] = ['style' => 'width:80px', 'class' => 'text-left'];
            $webColumns[] = $column;
        }
    }

    $webColumns[] = [
        'label' => ' ',
        'format' => 'raw',
        'value' => function($data) {
            return '<span class="glyphicon glyphicon-trash"></span>';
        },
        'headerOptions' => ['style' => 'width:20px'],
        'contentOptions' => [
            'class' => 'text-center danger-column',
            'onClick' => 'onDeleteClick(this)',
        ],
    ];

    $webColumns[] = [
        'label' => ' ',
        'format' => 'raw',
        'value' => function($data) {
            return '<span class="glyphicon glyphicon-minus-sign"></span>';
        },
        'headerOptions' => ['style' => 'width:20px'],
        'contentOptions' => [
            'class' => 'text-center danger-column',
            'onClick' => 'onBlockId(this)',
        ],
    ];

    echo \kartik\grid\GridView::widget([
        'id' => 'results-list',
        'dataProvider' => $dataProvider,
        'layout' => '<div class="text-muted">{summary}</div>{items} <div class="row text-center">{pager}</div>',
        'filterSelector' => 'select[name="per-page"]',
        'rowOptions' => function ($model, $key, $index, $grid) {
            return [
                'data-id' => $model->id,
                'data-respondent-rmsid' => $model->respondent->rmsid,
                'data-ip' => $model->respondent->ip,
            ];
        },
        'columns' => $webColumns,
    ]);
}
