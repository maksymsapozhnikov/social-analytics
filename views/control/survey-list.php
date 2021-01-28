<?php
/**
 * @var $this yii\web\View
 * @var $dataprovider \yii\data\SqlDataProvider
 * @var $searchModel \app\models\search\SurveySearch
 */

use app\components\FormatHelper;
use app\components\AppHelper as App;
use app\models\Survey;
use app\models\SurveyStatus;
use app\models\RespondentSurveyStatus;
use app\models\search\SurveySearch;
use yii\helpers\Url;

$this->title = 'Surveys';

$gridColumns = SurveySearch::columns();

$totalAll = App::total($dataprovider->models, 'stat_count_all');
$totalFinished = App::total($dataprovider->models, 'stat_count_fin');
$totalActive = App::total($dataprovider->models, 'stat_count_act');
$totalScreenedOut = App::total($dataprovider->models, 'stat_count_scr');
$totalDisqualified = App::total($dataprovider->models, 'stat_count_dsq');

?><?= $this->render('survey-filter', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataprovider,
    'gridColumns' => $gridColumns,
])
?>

<?php
$columns = [
    [
        'label' => 'State',
        'format' => 'raw',
        'attribute' => 'status',
        'value' => function($data) {
            return FormatHelper::iconSurveyStatus($data->status);
        },
        'headerOptions' => ['style' => 'width:0', 'class' => 'text-center'],
        'contentOptions' => function(Survey $data) {
            if ($data->isTrash()) {
                return [
                    'title' => SurveyStatus::getTitle($data->status),
                    'class' => 'text-center',
                ];
            }

            return [
                'title' => SurveyStatus::getTitle($data->status),
                'class' => 'text-center info-column',
                'onClick' => $data->status == SurveyStatus::ACTIVE ? 'onStopSurvey(event)' : 'onStartSurvey(event)',
            ];
        },
    ],
    [
        'label' => 'KN',
        'value' => 'country',
        'attribute' => 'country',
        'headerOptions' => ['style' => 'width:0', 'class' => 'text-center'],
        'contentOptions' => ['class' => 'text-center text-muted small'],
    ],
    [
        'label' => 'RMSID',
        'format' => 'raw',
        'value' => 'rmsid',
        'attribute' => 'rmsid',
        'headerOptions' => ['style' => 'width:0', 'class' => 'text-center'],
        'contentOptions' => ['style' => 'font-family: monospace', 'class' => 'text-center text-muted'],
    ],
    [
        'label' => 'Camp.',
        'format' => 'raw',
        'attribute' => 'campaign.name',
        'value' => [FormatHelper::class, 'surveyCampaignName'],
        'headerOptions' => ['style' => 'width:0', 'class' => 'text-right small'],
        'contentOptions' => ['class' => 'text-right small', 'style' => 'white-space:nowrap'],
    ],
    [
        'label' => 'Survey Name',
        'format' => 'raw',
        'contentOptions' => [
            'onMouseOver' => 'showControlButtons(event)',
            'onMouseOut' => 'hideControlButtons(event)',
        ],
        'value' => function(Survey $data) {
            if ($data->isTrash()) {
                return FormatHelper::surveyFormatDeletedName($data);
            }

            return FormatHelper::surveyFormatName($data);
        },
        'headerOptions' => ['style' => 'min-width:25%', 'class' => 'text-left'],
        'attribute' => 'name',
        'footer' => '<b>Total</b>',
    ],
    [
        'label' => 'Created',
        'format' => 'raw',
        'value' => function(Survey $data) {
            return FormatHelper::surveyCreated($data->dt_created);
        },
        'attribute' => 'dt_created',
        'headerOptions' => ['class' => 'text-center small'],
        'contentOptions' => ['class' => 'text-center text-muted'],
    ],
    [
        'label' => 'Done',
        'format' => 'raw',
        'attribute' => 'countFinished',
        'value' => function(Survey $data) {
            return FormatHelper::percent($data->stat_count_fin, $data->stat_count_all);
        },
        'headerOptions' => ['class' => 'text-right small', 'style' => 'max-width:5%'],
        'contentOptions' => [
            'class' => 'text-right success-column',
            'onclick' => 'onCellClick(this, ' . RespondentSurveyStatus::FINISHED . ')',
        ],
        'footer' => FormatHelper::percent($totalFinished, $totalAll),
        'footerOptions' => ['class' => 'text-right'],
    ],
    [
        'label' => 'INP',
        'format' => 'raw',
        'attribute' => 'countActive',
        'value' => function(Survey $data) {
            return FormatHelper::percent($data->stat_count_act, $data->stat_count_all);
        },
        'headerOptions' => ['class' => 'text-right small', 'style' => 'max-width:5%'],
        'contentOptions' => [
            'class' => 'text-right success-column',
            'onclick' => 'onCellClick(this, ' . RespondentSurveyStatus::ACTIVE . ')',
        ],
        'footer' => FormatHelper::percent($totalActive, $totalAll),
        'footerOptions' => ['class' => 'text-right'],
    ],
    [
        'label' => 'SCR',
        'format' => 'raw',
        'attribute' => 'countScreenedOut',
        'value' => function(Survey $data) {
            return FormatHelper::percent($data->stat_count_scr, $data->stat_count_all);
        },
        'headerOptions' => ['class' => 'text-right small', 'style' => 'max-width:5%'],
        'contentOptions' => [
            'class' => 'text-right success-column',
            'onclick' => 'onCellClick(this, ' . RespondentSurveyStatus::SCREENED_OUT . ')',
        ],
        'footer' => FormatHelper::percent($totalScreenedOut, $totalAll),
        'footerOptions' => ['class' => 'text-right'],
    ],
    [
        'label' => 'DSQ',
        'format' => 'raw',
        'attribute' => 'countDisqualified',
        'value' => function(Survey $data) {
            return FormatHelper::percent($data->stat_count_dsq, $data->stat_count_all);
        },
        'headerOptions' => ['class' => 'text-right small', 'style' => 'max-width:5%'],
        'contentOptions' => [
            'class' => 'text-right success-column',
            'onclick' => 'onCellClick(this, ' . RespondentSurveyStatus::DISQUALIFIED . ')',
        ],
        'footer' => FormatHelper::percent($totalDisqualified, $totalAll),
        'footerOptions' => ['class' => 'text-right'],
    ],
    [
        'label' => 'Start',
        'format' => 'raw',
        'attribute' => 'countAll',
        'value' => function(Survey $data) {
            return FormatHelper::percent($data->stat_count_all, $data->stat_count_all);
        },
        'headerOptions' => ['class' => 'text-right small', 'style' => 'max-width:5%'],
        'contentOptions' => [
            'class' => 'text-right success-column',
            'onclick' => 'onCellClick(this, ' . RespondentSurveyStatus::ALL . ')',
        ],
        'footer' => FormatHelper::percent($totalAll, $totalAll),
        'footerOptions' => ['class' => 'text-right'],
    ],
    [
        'label' => 'Trust',
        'format' => 'raw',
        'attribute' => 'fieldTrustScore',
        'value' => function(SurveySearch $data) {
            return FormatHelper::dirtyScore(100 - $data->stat_dirty_score);
        },
        'headerOptions' => ['class' => 'text-right small', 'style' => 'max-width:5%'],
        'contentOptions' => ['class' => 'text-right'],
    ],
    [
        'label' => 'Time',
        'format' => 'raw',
        'attribute' => 'fieldTimeScore',
        'value' => function(SurveySearch $data) {
            return FormatHelper::timingScoreSum($data->stat_time_score);
        },
        'headerOptions' => ['class' => 'text-right small', 'style' => 'max-width:5%'],
        'contentOptions' => ['class' => 'text-right'],
    ],
    [
        'label' => 'avTim',
        'format' => 'raw',
        'attribute' => 'fieldAvgTimeScore',
        'value' => function(SurveySearch $data) {
            return FormatHelper::timingScoreAvg($data->stat_avg_time_score);
        },
        'headerOptions' => ['class' => 'text-right small', 'style' => 'max-width:5%'],
        'contentOptions' => ['class' => 'text-right'],
    ],
    [
        'label' => 'Spent',
        'format' => 'raw',
        'attribute' => 'topup_spent',
        'value' => function(Survey $data) {
            return $data->topup_spent > 0 ? \Yii::$app->formatter->asCurrency($data->topup_spent, 'USD') : null;
        },
        'headerOptions' => ['class' => 'text-right small', 'style' => 'max-width:5%'],
        'contentOptions' => ['class' => 'text-right'],
    ],
    [
        'label' => 'Bid',
        'format' => 'raw',
        'attribute' => 'bid_summa',
        'value' => function(Survey $data) {
            return $data->bid_summa != 0 ? sprintf('%.2f', $data->bid_summa) : null;
        },
        'headerOptions' => ['class' => 'text-right small', 'style' => 'max-width:5%'],
        'contentOptions' => ['class' => 'text-right'],
    ],
    [
        'label' => ' ',
        'format' => 'raw',
        'value' => function(Survey $data) {
            if ($data->isTrash()) {
                return '<span class="glyphicon glyphicon-remove"></span>';
            }

            return '<span class="glyphicon glyphicon-trash"></span>';
        },
        'headerOptions' => ['style' => 'width:20px'],
        'contentOptions' => function(Survey $data) {
            if ($data->isTrash()) {
                return [
                    'class' => 'text-center danger-column',
                    'onClick' => 'onRemoveSurvey(event)',
                ];
            }

            return [
                'class' => 'text-center danger-column',
                'onClick' => 'onTrashSurvey(event)',
            ];
        },
    ],
];

\yii\widgets\Pjax::begin([
    'id' => 'pjax-surveys-list',
    'timeout' => 10000,
    'enablePushState' => true,
]);

?>

<?= \kartik\grid\GridView::widget([
    'id' => 'surveys-list',
    'dataProvider' => $dataprovider,
    'layout' => '{items} <div class="rol text-center">{pager}</div>',
    'rowOptions' => function (Survey $model, $key, $index, $grid) {
        return [
            'data' => [
                'id' => $model->id,
                'rmsid' => $model->rmsid,
            ],
            'class' => $model->isTrash() ? 'text-trash' : '',
        ];
    },
    'showFooter' => true,
    'columns' => $columns,
]);

\yii\widgets\Pjax::end();

$editUrl = Url::to(['/control/survey-update', 'id' => '__id__']);
$copyUrl = Url::to(['/control/survey-create', 'id' => '__id__']);
$cleanUrl = Url::to(['/control/survey-clean', 'id' => '__id__']);
$removeUrl = Url::to(['/control/survey-remove', 'id' => '__id__']);
$trashUrl = Url::to(['/control/survey-trash', 'id' => '__id__']);
$restoreUrl = Url::to(['/control/survey-restore', 'id' => '__id__']);
$statusUrl = Url::to(['/control/survey-set-status', 'id' => '__id__', 'status' => '__status__']);

$resultsUrl = Url::to(['/control/result-list',
    'srch[rmsid][]' => '__rmsid__',
    'srch[statuses][]' => '__status__',
]);

$js = <<<JS
function onNameClick(event) {
    var url = '$editUrl';
    var surveyId = $(event.target).closest('tr').data('id');
    
    window.open(buildUrl(url, {id: surveyId}), '_blank' + surveyId);
}

function onCopySurvey(event) {
    var url = '$copyUrl';
    var surveyId = $(event.target).closest('tr').data('id');
    
    document.location = buildUrl(url, {id: surveyId});
}

function onCleanSurvey(event) {
    var url = '$cleanUrl';
    var surveyId = $(event.target).closest('tr').data('id');

    if (confirm('Are you sure you want to clean all results for this survey?')) {
        document.location = buildUrl(url, {id: surveyId});
    }
}

function onTrashSurvey(event) {
    var url = '$trashUrl';
    var surveyId = $(event.target).closest('tr').data('id');

    if (confirm('Are you sure you want to remove this survey to trash?')) {
        document.location = buildUrl(url, {id: surveyId});
    }
}

function onRestoreSurvey(event) {
    var url = '$restoreUrl';
    var surveyId = $(event.target).closest('tr').data('id');

    if (confirm('Are you sure you want to restore this survey from trash into inactive?')) {
        document.location = buildUrl(url, {id: surveyId});
    }
}

function onRemoveSurvey(event) {
    var url = '$removeUrl';
    var surveyId = $(event.target).closest('tr').data('id');

    if (confirm('Are you sure you want to remove this survey? You won\'t be able to restore these results.')) {
        document.location = buildUrl(url, {id: surveyId});
    }
}

function onStopSurvey(event) {
    var url = '$statusUrl';
    var surveyId = $(event.target).closest('tr').data('id');

    if (confirm('Are you sure you want to deactivate this survey?')) {
        document.location = buildUrl(url, {id: surveyId, status: 2});
    }
}

function onStartSurvey(event) {
    var url = '$statusUrl';
    var surveyId = $(event.target).closest('tr').data('id');

    if (confirm('Are you sure you want to activate this survey?')) {
        document.location = buildUrl(url, {id: surveyId, status: 1});
    }
}

function onCellClick(event, status) {
    var url = '$resultsUrl';
    var surveyRmsid = $(event).closest('tr').data('rmsid');

    window.open(buildUrl(url, {rmsid: surveyRmsid, status: status}), '_blank').focus();
}

function showControlButtons(event) {
    $(event.currentTarget).find('div.control-buttons').show();    
}

function hideControlButtons(event) {
    $(event.currentTarget).find('div.control-buttons').hide();    
}
JS;

$this->registerJs($js, \yii\web\View::POS_HEAD);

$this->registerJsFile('@web/js/manage/campaign.js', ['depends' => '\app\assets\JqueryMinified']);
$this->registerJsFile('@web/js/manage/survey-index.js', ['depends' => '\app\assets\JqueryMinified']);