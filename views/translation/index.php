<?php
/* @var $this yii\web\View */
/* @var $searchModel app\models\translation\TranslationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Modal;

$this->title = 'Languages';
$this->params['breadcrumbs'][] = $this->title;

$model = new \app\models\Language();

?>

<h1><?= $this->title ?></h1>

<div class="panel panel-success">
    <div class="panel-body">
        <div class="row">
            <div class="col-xs-2">
                <?php
                $form = \yii\bootstrap\ActiveForm::begin([
                    'id' => 'translation__create-form',
                    'action' => \yii\helpers\Url::to(['/translation/create']),
                    'enableClientValidation' => true,
                ]);

                Modal::begin([
                    'header' => '<h3 style="margin:0">New language</h3>',
                    'toggleButton' => [
                        'label' => '<span class="glyphicon glyphicon-plus"></span> New Language',
                        'class' => 'btn btn-primary',
                    ],
                    'footer' => Html::submitButton('Add language', ['class' => 'btn btn-primary'])
                ]);

                ?>
                    <div class="row">
                        <div class="col-xs-4">
                            <?= $form->field($model, 'lang') ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <?= $form->field($model, 'name')->label('Language name') ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <?= $form->field($model, 'native_name')->label('Native language name') ?>
                        </div>
                    </div>
                <?php

                Modal::end();
                \yii\bootstrap\ActiveForm::end();

                ?>
            </div>
        </div>
    </div>
</div>

<div class="source-message-index row">
    <div class="col-xs-12">
        <div class="box box-default">
            <div class="box-body">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'layout' => '{items} <div class="rol text-center">{pager}</div>',
                    'rowOptions' => function ($model, $key, $index, $grid) {
                        return ['data' => [
                            'id' => $model->id,
                        ]];
                    },
                    'columns' => [
                        [
                            'label' => 'L',
                            'format' => 'raw',
                            'value' => function($data) {
                                return $data->lang;
                            },
                            'headerOptions' => ['style' => 'width:10px', 'class' => 'text-center'],
                            'contentOptions' => ['class' => 'text-center'],
                        ],
                        [
                            'label' => 'Language',
                            'format' => 'raw',
                            'value' => function($data) {
                                return $data->name;
                            },
                            'headerOptions' => ['style' => 'width:40%', 'class' => 'text-left'],
                            'contentOptions' => ['class' => 'text-left'],
                        ],
                        [
                            'label' => 'Native language name',
                            'format' => 'raw',
                            'value' => function($data) {
                                return $data->native_name;
                            },
                            'headerOptions' => ['style' => 'width:40%', 'class' => 'text-left'],
                            'contentOptions' => ['class' => 'text-left'],
                        ],
                        [
                            'label' => 'Not translated',
                            'format' => 'raw',
                            'value' => function($data) {
                                $muted = $data->cnt_nottranslated > 0 ? '' : 'opacity:0.3';

                                return "<b style=\"font-size:large;$muted\">{$data->cnt_nottranslated}</b>";
                            },
                            'headerOptions' => ['style' => 'width:80px', 'class' => 'text-right'],
                            'contentOptions' => [
                                'class' => 'text-right',
                            ],
                        ],
                        [
                            'label' => ' ',
                            'format' => 'raw',
                            'value' => function() {
                                return '<span class="glyphicon glyphicon-edit"></span>';
                            },
                            'headerOptions' => ['style' => 'width:0', 'class' => 'text-right'],
                            'contentOptions' => [
                                'class' => 'text-center info-column',
                                'onclick' => 'onEditClick(event)',
                            ],
                        ],
                    ],
                ])
                ?>
            </div>
        </div>
    </div>
</div>
<?php

$editUrl = \yii\helpers\Url::to(['/translation/edit', 'id' => '__id__']);

$js = <<<JS
    function onEditClick(event) {
    var url = '$editUrl';
    var translationId = $(event.target).closest('tr').data('id');

    document.location = buildUrl(url, {id: translationId});
    }
JS;

$this->registerJs($js, \yii\web\View::POS_HEAD);
