<?php
/**
 * @var $dataProvider \yii\data\ActiveDataProvider
 */

use yii\helpers\StringHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Translations';

?>

<h1><?= $this->title ?></h1>

<div class="panel panel-success">
    <div class="panel-body">
        <div class="row">
            <div class="col-xs-2">
                <?= Html::a('<span class="glyphicon glyphicon-plus"></span> New Translation', ['/control/translation-edit'], [
                    'class' => 'btn btn-primary',
                ]) ?>
            </div>
        </div>
    </div>
</div>

<?= \kartik\grid\GridView::widget([
    'id' => 'surveys-list',
    'dataProvider' => $dataProvider,
    'layout' => '{items} <div class="rol text-center">{pager}</div>',
    'rowOptions' => function ($model, $key, $index, $grid) {
        return ['data' => [
            'id' => $model->id,
        ]];
    },
    'columns' => [
        [
            'label' => 'Language',
            'value' => 'lang',
            'headerOptions' => ['style' => 'width:50px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center', 'style' => 'font-weight:bold'],
        ],
        [
            'label' => 'Messages',
            'format' => 'raw',
            'value' => function($data) {
                return $this->render('translation-item', ['data' => $data]);
            },
            'headerOptions' => ['style' => 'width:100%', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
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
    ],
]);

$editUrl = Url::to(['/control/translation-edit', 'id' => '__id__']);

$js = <<<JS
function onEditClick(event) {
    var url = '$editUrl';
    var translationId = $(event.target).closest('tr').data('id');

    document.location = buildUrl(url, {id: translationId});
}
JS;

$this->registerJs($js, \yii\web\View::POS_HEAD);