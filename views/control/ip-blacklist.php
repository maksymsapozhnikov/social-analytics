<?php
/**
 * @var $this yii\web\View
 * @var $dataProvider \yii\data\SqlDataProvider
 */

use app\components\FormatHelper as Format;

$this->title = 'IP Blacklist';

?>

<h1><?= $this->title ?></h1>

<?= \kartik\grid\GridView::widget([
    'id' => 'ip-blacklist',
    'dataProvider' => $dataProvider,
    'layout' => '{items} <div class="rol text-center">{pager}</div>',
    'rowOptions' => function ($model, $key, $index, $grid) {
        return ['data-id' => $model->id];
    },
    'columns' => [
        [
            'label' => 'Since',
            'format' => 'raw',
            'value' => function($data) {
                return Format::toDate($data->since_dt, 'd.m.Y H:i');
            },
            'headerOptions' => ['style' => 'width:120px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'IP',
            'format' => 'raw',
            'value' => 'ip_v4',
            'attribute' => 'ip',
            'headerOptions' => ['style' => 'width:200px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'Country',
            'format' => 'raw',
            'value' => function($data) {
                return $data->details->country_name;
            },
            'headerOptions' => ['style' => 'width:20%', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'Region',
            'format' => 'raw',
            'value' => function($data) {
                return $data->details->region_name;
            },
            'headerOptions' => ['style' => 'width:25%', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'City',
            'format' => 'raw',
            'value' => function($data) {
                return $data->details->city_name;
            },
            'headerOptions' => ['style' => 'width:20%', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => ' ',
            'format' => 'raw',
            'value' => function($data) {
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

$deleteUrl = \yii\helpers\Url::to(['/control/ip-blacklist-delete', 'id' => '__id__']);

$js = <<<JS
function onDeleteClick(event) {
    var url = '$deleteUrl';
    var resultId = $(event).closest('tr').data('id');
    
    if (confirm('Unblock this IP?')) {
        document.location = buildUrl(url, {id: resultId});
    }
}

JS;

$this->registerJs($js, \yii\web\View::POS_HEAD);
