<?php
/**
 * Accounts list
 * @var $dataProvider \yii\data\ActiveDataProvider
 */

use yii\helpers\Url;
use app\models\enums\PhoneSystemEnum;

$this->title = 'Accounts for top ups';

?>

    <h1><?= $this->title ?></h1>

    <div class="panel panel-success">
        <div class="panel-heading">
            <div class="row">
                <div class="col-xs-2">

                </div>
            </div>
        </div>
    </div>

<?= \kartik\grid\GridView::widget([
    'id' => 'accounts-list',
    'dataProvider' => $dataProvider,
    'layout' => '{items} <div class="rol text-center">{pager}</div>',
    'rowOptions' => function ($model, $key, $index, $grid) {
        return [
            'data' => [
                'id' => $model->id,
            ],
            'onClick' => 'onViewClick(event)',
        ];
    },
    'columns' => [
        [
            'label' => 'Phone',
            'value' => 'phone',
            'headerOptions' => ['style' => 'width:15%', 'class' => 'text-left'],
            'contentOptions' => ['style' => 'font-family: monospace', 'class' => 'text-left'],
        ],
        [
            'label' => 'Plan',
            'value' => function($data) {
                return PhoneSystemEnum::getTitle($data->payment_system);
            },
            'headerOptions' => ['style' => 'width:40px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'Country',
            'value' => function($data) {
                return $data->phoneDetails->country;
            },
            'headerOptions' => ['style' => 'width:25%', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'Operator',
            'value' => function($data) {
                return $data->phoneDetails->operator;
            },
            'headerOptions' => ['style' => 'width:35%', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'Balance',
            'value' => 'value',
            'format' => 'raw',
            'headerOptions' => ['style' => 'width:80px', 'class' => 'text-right'],
            'contentOptions' => ['class' => 'text-right'],
        ],
        [
            'label' => 'Currency',
            'value' => 'currency',
            'headerOptions' => ['style' => 'width:30px', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
    ],
]);

$viewUrl = Url::to(['/control/account-view', 'id' => '__id__']);

$js = <<<JS
function onViewClick(event) {
    var url = '$viewUrl';
    var accountId = $(event.target).closest('tr').data('id');

    document.location = buildUrl(url, {id: accountId});
}
JS;

$this->registerJs($js, \yii\web\View::POS_HEAD);
