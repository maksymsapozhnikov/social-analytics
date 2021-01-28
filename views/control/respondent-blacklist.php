<?php
/**
 * @var $this yii\web\View
 * @var $dataProvider \yii\data\SqlDataProvider
 */

use app\components\FormatHelper as Format;

$this->title = 'Respondent Blacklist';

?>

    <h1><?= $this->title ?></h1>

<?= \kartik\grid\GridView::widget([
    'id' => 'respondent-blacklist',
    'dataProvider' => $dataProvider,
    'layout' => '{items} <div class="rol text-center">{pager}</div>',
    'rowOptions' => function ($model, $key, $index, $grid) {
        return ['data-id' => $model->id];
    },
    'columns' => [
        [
            'label' => 'Blocked',
            'format' => 'raw',
            'value' => function($data) {
                return Format::toDate($data->dt_blacklist, 'd.m.Y H:i:s');
            },
            'headerOptions' => ['style' => 'width:60px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'Respondent',
            'format' => 'raw',
            'value' => 'rmsid',
            'attribute' => 'rmsid',
            'headerOptions' => ['style' => 'width:80px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'Survey',
            'value' => 'survey.rmsid',
            'format' => 'raw',
            'headerOptions' => ['style' => 'width:60px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'Country',
            'value' => 'survey.country',
            'format' => 'raw',
            'headerOptions' => ['style' => 'width:60px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'Device',
            'format' => 'raw',
            'value' => function($data) {
                return $data->device_vendor . ', '. $data->device_model;
            },
            'headerOptions' => ['style' => 'width:170px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'OS',
            'format' => 'raw',
            'value' => function($data) {
                return $data->os_name . ', '. $data->os_version;
            },
            'headerOptions' => ['style' => 'width:170px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'IP',
            'format' => 'raw',
            'value' => 'ip',
            'attribute' => 'ip',
            'headerOptions' => ['style' => 'width:120px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'First Seen',
            'format' => 'raw',
            'value' => function($data) {
                return Format::toDate($data->registered_at, 'd.m.Y H:i:s');
            },
            'headerOptions' => ['style' => 'width:60px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'Last Seen',
            'format' => 'raw',
            'value' => function($data) {
                return Format::toDate($data->last_seen_at, 'd.m.Y H:i:s');
            },
            'headerOptions' => ['style' => 'width:60px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'Suspicious',
            'format' => 'raw',
            'value' => function($data) {
                return \app\models\enums\SuspiciousStatus::getTitle($data->suspicious);
            },
            'headerOptions' => ['style' => 'width:60px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left', 'style' => 'font-size:12px'],
        ],
        [
            'label' => ' ',
            'format' => 'raw',
            'value' => function() {
                return '<span class="glyphicon glyphicon-trash" style="padding-left: 7px; padding-right: 7px;"></span>';
            },
            'headerOptions' => ['style' => 'width:20px'],
            'contentOptions' => [
                'class' => 'text-center danger-column',
                'onClick' => 'onDeleteClick(this)',
            ],
        ],
    ],
]);

$deleteUrl = \yii\helpers\Url::to(['/control/respondent-blacklist-delete', 'id' => '__id__']);

$js = <<<JS
function onDeleteClick(event) {
    var url = '$deleteUrl';
    var resultId = $(event).closest('tr').data('id');
    
    if (confirm('Unblock this respondent?')) {
        document.location = buildUrl(url, {id: resultId});
    }
}

JS;

$this->registerJs($js, \yii\web\View::POS_HEAD);
