<?php
/**
 * @var $searchModel \app\models\search\SurveySearch
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $gridColumns array
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\export\ExportMenu;
use \kartik\date\DatePicker;
use kartik\select2\Select2;
use app\modules\manage\models\Campaign;
use yii\helpers\ArrayHelper;
use app\components\QueriesHelper;

$countries = \Yii::$app->db->createCommand('select distinct country from survey order by country')->queryAll();

$countries = ArrayHelper::getColumn($countries, 'country');
$countries = array_combine($countries, $countries);

$campaign = Campaign::findOne($searchModel->cid);
$campaignName = $campaign ? $campaign->name : '';

?>

<h1 class="mob-mt-60"><?= $this->title ?></h1>

<div class="panel panel-success">
    <div class="panel-heading">
        <div class="row">
            <?php
            $form = ActiveForm::begin([
                'method' => 'get',
                'id' => 'survey-filter',
                'action' => \yii\helpers\Url::to(['/control/survey-list']),
            ]);
            ?>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <?= $form->field($searchModel, 'cid')->widget(Select2::class, [
                    'options' => ['multiple' => false, 'placeholder' => ''],
                    'data' => QueriesHelper::getAllCampaigns(),
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ])->label('Campaign') ?>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12"><?= $form->field($searchModel, 'rmsid')->widget(Select2::class, [
                    'data' => \app\components\QueriesHelper::select2Surveys(),
                    'options' => ['multiple' =>true, 'placeholder' => 'All surveys'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ])->label('Survey') ?></div>

            <div class="col-md-2 col-sm-6 col-xs-12"><?= $form->field($searchModel, 'country')->widget(Select2::class, [
                    'data' => $countries,
                    'options' => ['multiple' =>true, 'placeholder' => 'All countries'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ])->label('Country') ?>
            </div>

            <div class="col-md-3 col-sm-4 col-xs-12"><?= $form->field($searchModel, 'status')->widget(Select2::class, [
                    'data' => \app\models\SurveyStatus::TITLES,
                    'options' => ['multiple' =>true, 'placeholder' => 'All statuses'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ])->label('Status') ?>
            </div>

            <div class="col-md-1 col-sm-2 col-xs-12 pull-right"><?= Html::submitButton('<span class="glyphicon glyphicon-search"></span>', [
                    'class' => 'btn btn-primary form-control',
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-4 col-sm-4 col-xs-6">
                <?= Html::a('<span class="glyphicon glyphicon-plus"></span> New Survey', ['/control/survey-create'], [
                    'class' => 'btn btn-primary',
                ]) ?>
            </div>
            <div class="col-md-3 col-sm-4 col-xs-6 pull-right export-buttons" style="text-align: right;">
                <?= \kartik\export\ExportMenu::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => $gridColumns,
                    'target' => \kartik\export\ExportMenu::TARGET_SELF,
                    'filename' => 'Surveys',
                    'exportConfig' => [
                        ExportMenu::FORMAT_TEXT => false,
                        ExportMenu::FORMAT_PDF => false,
                    ],
                ]);
                ?>
            </div>
        </div>
    </div>
</div>
