<?php
/**
 * Aliases list
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var \yii\web\View $this
 */

use app\components\helpers\GridViewHelper;
use app\components\helpers\WidgetHelper;
use app\models\Alias;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css');
$this->registerJsFile('@web/js/manage/aliases.js', ['depends' => \app\assets\ManageAsset::class]);

$this->title = 'Aliases';

$webColumns = [
    GridViewHelper::columnActionItem([
        'icon' => function(Alias $model) {
            return $model->is_sticked ? 'pushpin' : '';
        },
        'columnClass' => 'info-column item-pin small',
        'header' => '<span class="glyphicon glyphicon-pushpin small"></span>',
    ]),
    GridViewHelper::columnActionItem([
        'attribute' => 'status',
        'icon' => function(Alias $model) {
            return $model->status === \app\models\SurveyStatus::ACTIVE ? 'play text-success' : 'pause text-danger';
        },
        'columnClass' => 'info-column item-status small',
        'header' => '<span class="glyphicon glyphicon-play small"></span>',
    ]),
    [
        'label' => 'ID',
        'attribute' => 'rmsid',
        'headerOptions' => ['class' => 'text-center small'],
        'contentOptions' => ['style' => 'font-family: monospace', 'class' => 'text-center'],
    ],
    [
        'label' => 'Alias URL',
        'value' => [Alias::class, 'formatAliasUrl'],
        'attribute' => 'rmsid',
        'format' => 'raw',
        'headerOptions' => ['style' => 'width:150px', 'class' => 'text-left small'],
        'contentOptions' => ['style' => 'font-family: monospace', 'class' => 'text-left small'],
    ],
    [
        'label' => 'Start',
        'attribute' => 'used',
        'headerOptions' => ['style' => 'width:10px', 'class' => 'text-right small'],
        'contentOptions' => [
            'class' => 'text-right danger-column item-reset',
        ],
    ],
    [
        'label' => 'SCR',
        'attribute'=>'scr',
        'headerOptions' => ['style' => 'width:30px', 'class' => 'text-right small'],
        'contentOptions' => [
            'class' => 'text-right danger-column item-reset-scr',
        ],
    ],
    [
        'label' => 'DSQ',
        'attribute'=>'dsq',
        'headerOptions' => ['style' => 'width:30px', 'class' => 'text-right small'],
        'contentOptions' => [
            'class' => 'text-right danger-column item-reset-dsq',
        ],
    ],
    [
        'label' => 'QFL',
        'attribute'=>'qfl',
        'headerOptions' => ['style' => 'width:30px', 'class' => 'text-right small'],
        'contentOptions' => [
            'class' => 'text-right danger-column item-reset-qfl',
        ],
    ],
    [
        'label' => 'Block',
        'attribute'=>'block',
        'headerOptions' => ['style' => 'width:30px', 'class' => 'text-right small'],
        'contentOptions' => [
            'class' => 'text-right danger-column item-reset-block',
        ],
    ],
    [
        'label' => 'Done',
        'attribute' => 'cnt_finished',
        'headerOptions' => ['style' => 'width:30px', 'class' => 'text-right small'],
        'contentOptions' => [
            'class' => 'text-right text-muted',
        ],
    ],
    [
        'label' => 'Survey',
        'attribute' => 'survey.rmsid',
        'headerOptions' => ['style' => 'width:80px', 'class' => 'text-center small'],
        'contentOptions' => ['style' => 'font-family: monospace', 'class' => 'text-center text-muted'],
    ],
    [
        'label' => 'Name',
        'attribute' => 'survey.name',
        'headerOptions' => ['style' => 'width:20%;min-width:150px', 'class' => 'text-left small'],
        'contentOptions' => ['class' => 'text-left'],
    ],
    [
        'label' => 'Country',
        'attribute' => 'survey.country',
        'headerOptions' => ['style' => 'width:0', 'class' => 'text-left small'],
        'contentOptions' => ['class' => 'text-left small'],
    ],
    [
        'label' => 'Bid',
        'attribute' => 'bid',
        'value' => [Alias::class, 'extractBidParam'],
        'format' => 'raw',
        'headerOptions' => ['style' => 'width:10px', 'class' => 'text-left small'],
        'contentOptions' => ['style' => 'white-space:nowrap;font-family: monospace', 'class' => 'text-left small danger-column item-bid'],
    ],
    [
        'label' => 'Lang',
        'value' => [Alias::class, 'extractLangParam'],
        'format' => 'raw',
        'headerOptions' => ['style' => 'width:0', 'class' => 'text-center small'],
        'contentOptions' => ['style' => 'white-space:nowrap;font-family:monospace', 'class' => 'text-center small'],
    ],
    [
        'label' => 'Source',
        'value' => [Alias::class, 'extractSourceParam'],
        'format' => 'raw',
        'headerOptions' => ['style' => 'width:0', 'class' => 'text-center small'],
        'contentOptions' => ['style' => 'white-space:nowrap;font-family:monospace', 'class' => 'text-center small'],
    ],
    [
        'label' => 'Parameters & Note',
        'value' => function($data) {

            $noteStr = preg_replace('!(http|https)(s)?:\/\/[a-zA-Z0-9.?&_/]+!', "<a href=\"\\0\" target='_blank'>\\0</a>",$data->note);

            return '<div class="params-notes-content-box">
                        <div class="query-params">
                            <span class="title">Query:</span>
                            <span class="content">'.Alias::formatSurveyParams($data).'</span>
                        </div>
                        <div class="notes">
                            <span class="title">Note:</span>
                            <span class="content">'.$noteStr.'</span>
                        </div>
                    </div>';
        },
        'format' => 'raw',
        'headerOptions' => ['style' => 'width:35%', 'class' => 'text-left small'],
        'contentOptions' => [
            'style' => 'max-width:150px; overflow:hidden; text-overflow:ellipsis; font-family:monospace',
            'class' => 'text-left',
        ],
    ],
    GridViewHelper::columnEditItem(),
    GridViewHelper::columnCopyItem(),
    GridViewHelper::columnDeleteOrRecoveryItem(),
];

?>

<h1 class="mob-mt-60"><?= $this->title ?></h1>

<div class="panel panel-success">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-2 col-sm-4 col-xs-12"><label for="filter__statuses">Status</label>
                <?= WidgetHelper::select2AliasStatus(['id' => 'filter__statuses']) ?>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-12"><label for="filter__countries">Country</label>
                <?= WidgetHelper::select2Countries(['id' => 'filter__countries']) ?>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-12"><label for="filter__parameters">Parameters</label>
                <?= Html::textInput('filter__parameters', '', [
                    'id' => 'filter__parameters',
                    'class' => 'form-control',
                    'placeholder' => 'All parameters',
                ]) ?>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-12"><label for="filter__parameters">Survey ID</label>
                <?= Html::textInput('filter__search_by_id', '', [
                    'id' => 'filter__search_by_id',
                    'class' => 'form-control',
                    'placeholder' => '',
                ]) ?>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12"><label for="filter__parameters">Survey Name / Alias / Note</label>
                <?= Html::textInput('filter__search', '', [
                    'id' => 'filter__search',
                    'class' => 'form-control',
                    'placeholder' => '',
                ]) ?>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-2 col-sm-4 col-xs-12"><?= WidgetHelper::newButton(['title' => 'New Alias']) ?></div>
        </div>
    </div>
</div>

<?php

Pjax::begin([
    'id' => 'pjax-surveys-list',
    'timeout' => 10000,
    'enablePushState' => true,
]);

try {
    echo GridView::widget([
        'id' => 'surveys-list',
        'dataProvider' => $dataProvider,
        'layout' => '<div class="table-content">{items}</div> <div class="rol text-center">{pager}</div>',
        'rowOptions' => function ($model, $key, $index, $grid) {
            return [
                'data' => [
                    'id' => $model->id,
                    'status' => $model->status,
                ],
            ];
        },
        'columns' => $webColumns,
        'pager' => [
            'maxButtonCount' => 20,
        ],
    ]);
} catch (\Throwable $e) {

    echo $this->render('/_common/error', ['exception' => $e]);

}

Pjax::end();