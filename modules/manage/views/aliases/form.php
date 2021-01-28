<?php
/**
 * @var $this yii\web\View
 * @var $model \app\models\Alias
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2;
use app\modules\manage\models\search\SurveyAjax;
use yii\helpers\ArrayHelper;
use app\components\QueriesHelper;
use yii\web\JsExpression;

$this->title = $model->isNewRecord ? 'Creating Alias' : 'Editing Alias '.$model->rmsid;
if (!$model->isNewRecord) {
    $model->makeCustomParams();
}
$this->registerJs("makeQueryParam();", \yii\web\View::POS_READY);
?>

<h1 class="mob-mt-60"><?= $this->title ?></h1>

<?php $form = ActiveForm::begin([
    'id' => 'alias-form',
    'action' => Url::to(['/manage/aliases/edit', 'id' => $model->id]),
    'enableClientValidation' => true,
]); ?>

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <?= $form->field($model, 'survey_id')->widget(Select2::class, [
                        'data' => ArrayHelper::map(SurveyAjax::searchByParams($terms = '', $status = 'active', $sortParams = 'date_create_DESC', $limit = false), 'id', 'text'),
                        'options' => ['multiple' => false, 'placeholder' => 'Select Survey'],
                        'pluginOptions' => [
                            'ajax' => [
                                'url' => Url::to('/manage/surveys/search-by-params'),
                                'dataType' => 'json',
                                'data' => new JsExpression('function(params) {return {
                                    term: params.term,
                                    status: "active",
                                    sortParams: "date_create_DESC",
                                    country: $("#alias-country-id").val(),
                                }}')
                            ],
                        ],
                    ]) ?>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <?= $form->field($model, 'shortParams')
                        ->textInput()
                        ->hint('For example: <b>kn=Vietman</b>')
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2 col-sm-2 col-xs-12 min-height-100">
                    <div class="form-group required alias-lang__div">
                        <?= $form->field($model, 'lang')
                            ->textInput()
                        ?>
                    </div>
                </div>
                <div class="col-md-2 col-sm-2 col-xs-12 min-height-100">
                    <div class="form-group required alias-bd__div">
                        <?= $form->field($model, 'bid')
                            ->textInput()
                        ?>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 col-xs-12 min-height-100">
                    <div class="form-group required alias-source__div">
                        <?= $form->field($model, 'source')
                            ->textInput()
                        ?>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12 min-height-100">
                    <div class="form-group required alias-utm_medium__div">
                        <label class="control-label">Country</label>
                        <?= Select2::widget([
                            'id' => 'alias-country-id',
                            'name' => 'country',
                            'data' => QueriesHelper::getAllCountries(),
                            'options' => ['multiple' => false, 'placeholder' => 'Choose country'],
                        ])?>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12 min-height-100">
                    <div class="form-group required alias-utm_medium__div">
                        <?= $form->field($model, 'utmMedium')
                            ->textInput()
                        ?>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <label class="help-block result-query-wrap">Result query: <span id="result-query"><?= $model->params?></span></label>
                    <div class="form-group alias-note__div">
                        <?= $form->field($model, 'note')
                            ->textInput()
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group text-center">
        <?= $form->field($model, 'params')
            ->hiddenInput()->label(false)
        ?>
        <?= Html::button($model->isNewRecord ? 'Create Alias' : 'Save Alias', [
            'class' => 'btn-lg btn-primary',
            'type' => '',
        ]) ?>
    </div>

<?php

ActiveForm::end();