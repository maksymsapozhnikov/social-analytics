<?php
/**
 * Bad words list
 * @var $dataProvider \yii\data\ActiveDataProvider
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Bad words';

?>

<h1><?= $this->title ?></h1>

<div class="panel panel-success">
    <div class="panel-heading">
        <div class="row">
            <div class="col-xs-2">
                <div class="col-xs-2">
                    <?= Html::a('<span class="glyphicon glyphicon-plus"></span> New List', ['/manage/bad-words/create'], [
                        'class' => 'btn btn-primary',
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?= \kartik\grid\GridView::widget([
    'id' => 'bad-words-list',
    'dataProvider' => $dataProvider,
    'layout' => '{items} <div class="rol text-center">{pager}</div>',
    'rowOptions' => function ($model, $key, $index, $grid) {
        return [
            'data' => [
                'id' => $model->id,
            ],
        ];
    },
    'columns' => [
        [
            'label' => 'Country',
            'value' => 'country',
            'headerOptions' => ['style' => 'width:20%', 'class' => 'text-left'],
        ],
        [
            'label' => 'Words',
            'value' => 'words',
            'headerOptions' => ['style' => 'width:80%', 'class' => 'text-left'],
        ],
        [
            'label' => ' ',
            'format' => 'raw',
            'value' => function() {
                return '<span class="glyphicon glyphicon-edit"></span>';
            },
            'headerOptions' => ['style' => 'width:0', 'class' => 'text-right'],
            'contentOptions' => [
                'class' => 'text-right info-column',
                'onclick' => 'onEditClick(event)',
            ],
        ],
        [
            'label' => ' ',
            'format' => 'raw',
            'value' => function($data) {
                return '<span class="glyphicon glyphicon-remove"></span>';
            },
            'headerOptions' => ['style' => 'width:20px'],
            'contentOptions' => function($data) {
                return [
                    'class' => 'text-center danger-column',
                    'onClick' => 'onRemoveList(event)',
                ];
            },
        ],
    ],
]);

$editUrl = Url::to(['/manage/bad-words/update', 'id' => '__id__']);
$removeUrl = Url::to(['/manage/bad-words/delete', 'id' => '__id__']);

$js = <<<JS
function onEditClick(event) {
    var url = '$editUrl';
    var surveyId = $(event.target).closest('tr').data('id');
    
    document.location = buildUrl(url, {id: surveyId});
}

function onRemoveList(event) {
    var url = '$removeUrl';
    var surveyId = $(event.target).closest('tr').data('id');

    if (confirm('Are you sure you want to remove this list?')) {
        document.location = buildUrl(url, {id: surveyId});
    }
}
JS;

$this->registerJs($js, \yii\web\View::POS_HEAD);
