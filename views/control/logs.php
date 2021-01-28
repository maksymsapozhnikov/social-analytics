<?php
/**
 * @var $this yii\web\View
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel \app\models\search\RespondentLogSearch
 */

use app\components\FormatHelper as Format;
use kartik\export\ExportMenu;
use kartik\form\ActiveForm;
use kartik\date\DatePicker;
use app\models\RespondentLog;
use app\components\enums\SessionStatusEnum;

$this->title = 'Logs';

$gridColumns = [
    ['class' => 'yii\grid\SerialColumn'],
    [
        'label' => 'Start Date',
        'attribute' => 'create_dt',
        'value' => function(RespondentLog $data) {
            return Format::toDate($data->create_dt, 'd.m.Y H:i:s');
        }
    ],
    [
        'label' => 'Redirection&nbsp;Date',
        'attribute' => 'modify_dt',
        'value' => function(RespondentLog $data) {
            return Format::toDate($data->modify_dt, 'd.m.Y H:i:s');
        }
    ],
    [
        'label' => 'Respondent',
        'value' => 'respondent.rmsid',
    ],
    [
        'label' => 'Survey',
        'value' => 'survey_rmsid',
    ],
    [
        'label' => 'Status',
        'value' => function(RespondentLog $data) {
            return SessionStatusEnum::getTitle($data->status);
        }
    ],
    [
        'label' => 'Status message',
        'value' => 'status_message',
    ],
    [
        'label' => 'Survey Country',
        'value' => 'survey.country',
    ],
    'device_id',
    [
        'label' => 'Device Brand',
        'value' => function(\app\models\RespondentLog $data) {
            return $data->deviceatlasDetails('vendor');
        }
    ],
    [
        'label' => 'Device Model',
        'value' => function(\app\models\RespondentLog $data) {
            return $data->deviceatlasDetails('model');
        }
    ],
    'fingerprint_id',
    'ip',
    [
        'label' => 'IP: Country',
        'value' => function(\app\models\RespondentLog $data) {
            return $data->ipDetails('country');
        }
    ],
    [
        'label' => 'IP: Region',
        'value' => function(\app\models\RespondentLog $data) {
            return $data->ipDetails('region');
        }
    ],
    [
        'label' => 'IP: City',
        'value' => function(\app\models\RespondentLog $data) {
            return $data->ipDetails('city');
        }
    ],
    [
        'label' => 'Request: URL',
        'value' => function(\app\models\RespondentLog $data) {
            return $data->requestDetails('url');
        }
    ],
    [
        'label' => 'Request: Cookie',
        'value' => function(\app\models\RespondentLog $data) {
            $headers = $data->requestDetails('headers');

            return isset($headers['cookie']) ? $headers['cookie'][0] : null;
        }
    ],
    [
        'label' => 'Browser name',
        'value' => function(\app\models\RespondentLog $data) {
            return $data->deviceatlasDetails('browserName');
        }
    ],
    [
        'label' => 'Browser version',
        'value' => function(\app\models\RespondentLog $data) {
            return $data->deviceatlasDetails('browserVersion');
        }
    ],
    [
        'label' => 'User-Agent',
        'value' => function(\app\models\RespondentLog $data) {
            $headers = $data->requestDetails('headers');

            return $headers['user-agent'][0];
        }
    ],
    'request_details',
    'deviceatlas_details',
];

?>

<h1><?= $this->title ?></h1>

<div class="panel panel-success">
    <div class="panel-heading">
        <div class="row">
            <?php
            $form = ActiveForm::begin([
                'method' => 'get',
                'action' => \yii\helpers\Url::to(['/control/logs']),
            ]);
            ?>
            <div class="col-xs-3">
                <label class="control-label">Date interval</label>
                <?= DatePicker::widget([
                    'form' => $form,
                    'model' => $searchModel,
                    'attribute' => 'start_date',
                    'attribute2' => 'end_date',
                    'type' => DatePicker::TYPE_RANGE,
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'dd.mm.yyyy'
                    ]
                ]);
                ?></div>
            <div class="col-xs-4"><?= $form->field($searchModel, 'survey_rmsid')->widget(\kartik\select2\Select2::classname(), [
                    'data' => \app\components\QueriesHelper::select2Surveys(),
                    'options' => ['multiple' =>true, 'placeholder' => 'All surveys'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ])->label('Survey RMSID') ?></div>
            <div class="col-xs-2"><?= $form->field($searchModel, 'resp')->label('Respondent RMSID') ?></div>
            <div class="col-xs-2"><?= $form->field($searchModel, 'ip')->label('IP') ?></div>
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
            <div class="pull-left col-xs-2"><?= \Yii::$app->getTimeZone() ?> timezone:<br><b><?= date('d.m.Y H:i') ?></b></div>
            <div class="col-xs-2 pull-right" style="text-align: right;">
                <?= \kartik\export\ExportMenu::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => $gridColumns,
                    'target' => \kartik\export\ExportMenu::TARGET_SELF,
                    'filename' => $searchModel->getExportFilename(),
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

    $gridProvider = $dataProvider;
    $gridProvider->query->orderBy(['create_dt' => SORT_DESC]);

?>
<?= \kartik\grid\GridView::widget([
    'id' => 'logs-list',
    'dataProvider' => $dataProvider,
    'layout' => '<div class="text-muted">{summary}</div>{items} <div class="row text-center">{pager}</div>',
    'rowOptions' => function ($model, $key, $index, $grid) {
        return ['data' => [
            'fingerprint_id' => $model['fingerprint_id'],
            'ip' => $model['ip'],
            'survey' => $model['survey_rmsid'],
        ]];
    },
    'columns' => [
        [
            'label' => 'Date',
            'format' => 'raw',
            'value' => function($data) {
                return Format::toDate($data->create_dt, 'd.m.Y H:i:s');
            },
            'headerOptions' => ['style' => 'width:80px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'Respondent',
            'format' => 'raw',
            'value' => function($data) {
                return $data->respondent ? $data->respondent->rmsid : null;
            },
            'headerOptions' => ['style' => 'width:60px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'Survey',
            'format' => 'raw',
            'value' => 'survey_rmsid',
            'headerOptions' => ['style' => 'width:60px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'Name',
            'format' => 'raw',
            'value' => function(RespondentLog $data) {
                return $data->survey ? $data->survey->name : null;
            },
            'headerOptions' => ['style' => 'width:60px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'Status',
            'format' => 'raw',
            'value' => function(RespondentLog $data) {
                return SessionStatusEnum::getTitle($data->status)
                    . ($data->status_message ?
                        '<div class="text-muted small">' . $data->status_message . '</div>' :
                        '');
            },
            'headerOptions' => ['style' => 'width:70px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'Device ID',
            'format' => 'raw',
            'value' => function(RespondentLog $data) {
                return ($data->device_id ?: \yii\helpers\Html::tag('span', '(not set)', ['class' => 'not-set'])) . '<br>'
                    . '<div class="text-muted small">'
                    . implode(', ', [$data->deviceatlasDetails('vendor'), $data->deviceatlasDetails('model')])
                    . '</div>';
            },
            'headerOptions' => ['style' => 'width:120px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'Fingerprint',
            'format' => 'raw',
            'value' => function(RespondentLog $data) {
                return $data->fingerprint_id
                    ? \yii\helpers\Html::tag('span', \yii\helpers\StringHelper::truncate($data->fingerprint_id, 8), ['title' => $data->fingerprint_id])
                    : \yii\helpers\Html::tag('span', '(not set)', ['class' => 'not-set']);
            },
            'headerOptions' => ['style' => 'width:100px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'IP',
            'format' => 'raw',
            'value' => function(RespondentLog $data) {
                return $data->ip . '<br>'
                    . '<div class="text-muted small">'
                    . implode(', ', [$data->ipDetails('country'), $data->ipDetails('region'), $data->ipDetails('city')])
                    . '</div>';
            },
            'headerOptions' => ['style' => 'width:100px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'URL',
            'format' => 'raw',
            'value' => function($data) {
                /** @var \app\models\search\RespondentLogSearch $data */
                $url = $data->requestDetails('url');

                return \yii\helpers\Html::tag('span', \yii\helpers\StringHelper::truncate($url, 40), [
                    'title' => $url,
                    'style' => 'white-space:nowrap',
                ]) .
                ($data->end_url ?
                    '<br>' . \yii\helpers\Html::tag('span', \yii\helpers\StringHelper::truncate($data->end_url, 40), [
                        'title' => $data->end_url,
                        'style' => 'white-space:nowrap;font-weight:bold',
                        'class' => 'text-danger',
                    ]) : '');
            },
            'headerOptions' => ['style' => 'width:100px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => ' ',
            'format' => 'raw',
            'value' => function($data) {
                return '<span title="Block this IP-address" class="glyphicon glyphicon-minus-sign" style="color:darkred; padding-left: 7px; padding-right: 7px;"></span>';
            },
            'headerOptions' => ['style' => 'width:20px'],
            'contentOptions' => [
                'class' => 'text-center danger-column',
                'onClick' => 'onBlockIp(this)',
            ],
        ],
    ],
]);

$blockIpUrl = \yii\helpers\Url::to(['/control/ip-blacklist-add', 'ip' => '__ip__']);

$js = <<<JS
function onBlockIp(event) {
    var url = '$blockIpUrl';
    var respondentIp = $(event).closest('tr').data('ip');

    if (confirm('Block IP ' + respondentIp + '?')) {
        $.getJSON(buildUrl(url, {ip: respondentIp}), function() {
            showMessage('IP Blacklist', arguments[0].message);
        }).fail(function() {
            showMessage('Error', arguments[0].statusText);            
        });
    }
}

JS;

$this->registerJs($js, \yii\web\View::POS_HEAD);
