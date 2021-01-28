<?php
/**
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $language \app\models\Language
 * @var $this \yii\web\View
 */

use yii\grid\GridView;

?>

<h3>Translations</h3>
<div class="pull-right" style="margin-top:20px;">
    <input type="checkbox" id="show-selector" style="cursor:pointer" checked> <label style="cursor:pointer" for="show-selector">Show messages without translations only</label>
</div>

<?= GridView::widget([
    'id' => 'translations-grid',
    'dataProvider' => $dataProvider,
    'layout' => '{items} <div class="rol text-center">{pager}</div>',
    'rowOptions' => function ($model, $key, $index, $grid) {
        return ['data' => [
            'id' => $model->id,
        ]];
    },
    'columns' => [
        [
            'label' => 'Category',
            'format' => 'raw',
            'value' => 'category',
            'headerOptions' => ['style' => 'width:10%', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'English language',
            'format' => 'raw',
            'value' => function(\app\models\search\TranslationMessageSearch $model) {
                return $model->eng_message ?? $model->message;
            },
            'headerOptions' => ['style' => 'width:40%', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'Translation',
            'format' => 'raw',
            'value' => function($data) {
                return $data->translation;
            },
            'headerOptions' => ['style' => 'width:50%', 'class' => 'text-left'],
            'contentOptions' => [
                'class' => 'text-left info-column',
            ],
        ],
    ],
]);

?>
<div class="row">
    <div class="col-xs-12 text-center">
        <?= \yii\helpers\Html::button('Close', [
            'class' => 'btn-lg btn-primary',
            'id' => 'translations-grid__close-button',
        ]);
        ?>
    </div>
</div>

<?php

$form = \yii\bootstrap\ActiveForm::begin([
    'id' => 'translation__update-form',
    'action' => false,
]);

\yii\bootstrap\Modal::begin([
    'id' => 'translation-modal',
    'header' => '<h3 style="margin:0">Editing translation</h3>',
    'toggleButton' => false,
    'footer' => \yii\helpers\Html::button('Save', [
        'class' => 'btn btn-primary',
        'id' => 'message-modal__submit'
    ]) . \yii\helpers\Html::button('Cancel', [
            'class' => 'btn btn-default',
            'id' => 'message-modal__cancel'
        ])
]);

$messageModel = new \app\models\translation\Message();

?>
    <div class="row">
        <div class="col-xs-12">
            <label>Source language</label>
            <div class="col-xs-12"><p id="message-modal__source-value"></p></div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <?= $form->field($messageModel, 'translation')->textInput(['id' => 'message-modal__value']) ?>
        </div>
    </div>
<?php

\yii\bootstrap\Modal::end();

\yii\bootstrap\ActiveForm::end();

$this->registerJs("window.appRms = window.appRms || {}; window.appRms.lang = '{$language->lang}';");

$this->registerJsFile('@web/js/control/translation-edit.js', [
    'depends' => 'yii\web\YiiAsset',
]);
