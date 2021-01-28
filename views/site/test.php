<?php
/**
 * @var $data array
 * @var $this \yii\web\View
 */

use yii\grid\GridView;
use yii\data\ActiveDataProvider;

$this->title = 'Test data';

?>

<h1><?= $this->title ?></h1>

<h3>Test Respondents</h3>
<?= GridView::widget([
    'dataProvider' => new ActiveDataProvider([
        'query' => \app\models\Respondent::find()->where(['=', 'left(rmsid, 1)', '@']),
        'pagination' => false,
    ]),
    'layout' => '{items}',
    'columns' => [
        'id', 'rmsid', 'device_id', 'fingerprint_id', 'ip', 'language', 'traffic_source',
        'failed_tryings', 'status',
        [
            'label' => 'Registered',
            'value' => function($data) {
                return date('d.m.Y H:i:s', $data->registered_at);
            },
        ],
        [
            'label' => 'Last seen',
            'value' => function($data) {
                return date('d.m.Y H:i:s', $data->last_seen_at);
            },
        ],
        'device_vendor', 'device_model', 'device_marketing_name', 'device_manufacturer', 'device_year_released',
        'os_vendor', 'os_name', 'os_family', 'os_version'
    ],
]);

?>

<h3>Test Results</h3>
<?php
    $subQuery = \app\models\Respondent::find()->select('id')->where(['=', 'left(rmsid, 1)', '@']);
?>
<?= GridView::widget([
    'dataProvider' => new ActiveDataProvider([
        'query' => \app\models\RespondentSurvey::find()->where(['IN', 'respondent_id', $subQuery]),
        'pagination' => false,
    ]),
    'layout' => '{items}',
    'columns' => ['id', 'respondent_id', 'survey_id', 'status', 'referrer', 'tryings',
        [
            'label' => 'Started At',
            'value' => function($data) {
                return date('d.m.Y H:i:s', $data->started_at);
            },
        ],
        [
            'label' => 'Finished At',
            'value' => function($data) {
                return date('d.m.Y H:i:s', $data->finished_at);
            },
        ],
        [
            'label' => 'Answers',
            'value' => function($data) {
                return '<pre>' . var_export(\yii\helpers\Json::decode($data->response), true) . '</pre>';
            },
            'format' => 'raw',
        ],
    ],
]);
?>

<!-- a href="<?= \yii\helpers\Url::to(['/site/cleanup']) ?>" class="btn btn-lg btn-danger">
    cleanup database<br>
    <small>except surveys</small>
</a -->