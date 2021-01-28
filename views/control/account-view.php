<?php
/**
 * @var \app\models\account\Account $account
 */

use app\components\FormatHelper as Format;
use app\models\account\Operation;
use app\models\enums\PhoneSystemEnum;

$this->title = 'Account ' . $account->phone;

?>

<h1><?= $this->title ?></h1>

<h3>Account details</h3>
<table class="table table-bordered">
    <tr>
        <th class="col-xs-2">Phone number</th>
        <td><?= $account->phone ?></td>
    </tr>
    <tr>
        <th class="col-xs-2">Phone plan</th>
        <td><?= PhoneSystemEnum::getTitle($account->payment_system) ?></td>
    </tr>
    <tr>
        <th><b>Balance</b></th>
        <td><?= $account->value ?></td>
    </tr>
    <tr>
        <th><b>Currency</b></th>
        <td><?= $account->currency ?></td>
    </tr>
    <tr>
        <th><b>Created</b></th>
        <td><?= Format::toDate($account->dt_create, 'd.m.Y H:i:s') ?></td>
    </tr>
    <tr>
        <th><b>Updated</b></th>
        <td><?= Format::toDate($account->dt_modify, 'd.m.Y H:i:s') ?></td>
    </tr>
</table>

<h3>Transactions</h3>
<?= \kartik\grid\GridView::widget([
    'id' => 'account-transaction-list',
    'dataProvider' => new \yii\data\ActiveDataProvider([
        'query' => $account->getTransactions()->orderBy(['dt' => SORT_DESC]),
        'pagination' => false,
    ]),
    'layout' => '{items}',
    'rowOptions' => function ($model, $key, $index, $grid) {
        $type = Operation::getType($model->operation);

        if ($type > 0) {
            $class = 'success';
        } elseif ($type < 0) {
            $class = 'danger';
        } else {
            $class = '';
        }

        return [
            'data' => [
                'id' => $model->id,
            ],
            'class' => $class
        ];
    },
    'columns' => [
        [
            'label' => 'Date',
            'value' => function($data) {
                return Format::toDate($data->dt, 'd.m.Y H:i:s');
            },
            'headerOptions' => ['style' => 'width:100px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'Survey',
            'value' => function($data) {
                if (!$data->survey instanceof \app\models\Survey) {
                    return '';
                }

                return $data->survey->name . ' (' . $data->survey->rmsid . ')';
            },
            'headerOptions' => ['style' => 'width:25%', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'Operation',
            'value' => function($data) {
                return Operation::getName($data->operation);
            },
            'headerOptions' => ['style' => 'width:20%', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
        [
            'label' => 'Currency',
            'value' => 'currency',
            'headerOptions' => ['style' => 'width:40px', 'class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
        ],
        [
            'label' => 'Dt',
            'value' => function($data) {
                return Operation::getType($data->operation) < 0 ? sprintf('%.2f', $data->value) : '';
            },
            'headerOptions' => ['style' => 'width:30px', 'class' => 'text-right'],
            'contentOptions' => ['class' => 'text-right'],
        ],
        [
            'label' => 'Ct',
            'value' => function($data) {
                return Operation::getType($data->operation) > 0 ? sprintf('%.2f', $data->value) : '';
            },
            'headerOptions' => ['style' => 'width:30px', 'class' => 'text-right'],
            'contentOptions' => ['class' => 'text-right'],
        ],
        [
            'label' => 'Note',
            'value' => 'note',
            'headerOptions' => ['style' => 'width:25%', 'class' => 'text-left'],
            'contentOptions' => ['class' => 'text-left'],
        ],
    ],
]);
