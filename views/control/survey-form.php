<?php
/**
 * @var $this yii\web\View
 * @var $survey \app\models\Survey
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2;
use app\components\enums\SurveySettings;
use app\modules\manage\models\Campaign;
use app\components\QueriesHelper;
use app\models\SurveyStatus;

$campaign = Campaign::findOne($survey->campaign_id);
$campaignName = $campaign ? $campaign->name : '';

?>
<?php $form = ActiveForm::begin([
    'id' => 'survey-form',
    'action' => $survey->isNewRecord ? Url::to(['/control/survey-create']) : Url::to(['/control/survey-update', 'id' => $survey->id]),
    'enableClientValidation' => true,
]); ?>

<div class="row">
<div class="col-xs-8">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-6">
                    <?= $form->field($survey, 'name')->textInput(['maxlength' => true, ])->label('Survey Name') ?>
                </div>
                <div class="col-xs-5">
                    <?= $form->field($survey, 'campaign_id')->widget(Select2::class, [
                        'options' => ['multiple' => false, 'placeholder' => ''],
                        'data' => QueriesHelper::getAllCampaigns(),
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]) ?>
                </div>
                <div class="col-xs-1">
                    <label class="control-label" for="">&nbsp;</label><br>
                    <?= Html::button('<span class="glyphicon glyphicon-plus"></span>', [
                        'type' => 'button',
                        'class' => 'btn btn-default btn-new-campaign',
                    ]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($survey, 'url')->textInput(['maxlength' => true])->label('URL')->hint(
                        'URL parameter replacements:<br>{sguid} - respondent sguid, {sur} - survey rmsid, {kn} - country name, {lang} - locale.',
                        ['style' => 'margin-left:14px']
                    ) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($survey, 'url_end')->textInput(['maxlength' => true])->label('End URL')->hint(
                        'Address where the user will be redirected after the survey ends. Placeholders:<br>{age} - respondent age, {gen} - respondent gender (m or f), {dob} - date of birth (YYYYMMDD), {src} - source.',
                        ['style' => 'margin-left:14px']
                    ) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($survey, 'country')->widget(Select2::class, [
                        'data' => QueriesHelper::getAllCountries(),
                        'options' => ['multiple' => false, 'placeholder' => 'Choose country'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-6">
                    <?= $form->field($survey, 'project_id')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-xs-6">
                    <?= $form->field($survey, 'sample')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($survey, 'status')->dropDownList(SurveyStatus::TITLES) ?>
                </div>
            </div>
        </div>
        <?php
        if(!$survey->isNewRecord) {
            $surveySearch = new \app\models\search\SurveySearch();
            $surveySearch->id = $survey->id;
            /** @var \app\models\search\SurveySearch $found */
            $found = $surveySearch->search([])->getModels()[0];
            ?>
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-12">
                        <h3 style="margin-top: 0">Survey Statistics</h3>
                        <table class="table table-bordered">
                            <tr>
                                <th style="width:20%" class="text-center">Finished</th>
                                <th style="width:20%" class="text-center">Started</th>
                                <th style="width:20%" class="text-center">Active</th>
                                <th style="width:20%" class="text-center">Screened out</th>
                                <th style="width:20%" class="text-center">Disqualified</th>
                            </tr>
                            <tr>
                                <td style="font-size:large" class="text-center"><?= $found->countFinished ?></td>
                                <td style="font-size:large" class="text-center"><?= $found->countAll ?></td>
                                <td style="font-size:large" class="text-center"><?= $found->countActive ?></td>
                                <td style="font-size:large" class="text-center"><?= $found->countScreenedOut ?></td>
                                <td style="font-size:large" class="text-center"><?= $found->countDisqualified ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<div class="col-xs-4">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row" style="min-height: 74px;">
                <div class="col-xs-12 text-center">
                    <?= $form->field($survey, 'has_topup')->checkbox([
                        'id' => 'survey__has_topup',
                        'style' => 'margin-top:35px',
                        'onchange' => 'onTopUpsSwitched(event)',
                    ])->label(false); ?>&nbsp;<label for="survey__has_topup" style="font-size: larger; cursor:pointer;">Enable top ups for this survey</label>
                </div>
            </div>
            <div class="row" id="survey__topup-row" style="display: none;">
                <div class="col-xs-4">
                    <?= $form->field($survey, 'topup_value')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-xs-8">
                    <?= $form->field($survey, 'topup_currency')->widget(\kartik\select2\Select2::classname(), [
                        'data' => \app\components\QueriesHelper::getAllCurrencies(),
                        'options' => ['multiple' => false, 'placeholder' => 'Choose currency'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]) ?>
                </div>
                <div class="col-xs-12">
                    <?= $form->field($survey, 'topup_sms')
                        ->textInput(['id' => 'survey-form__topup-sms', 'maxlength' => 30, 'placeholder' => 'SMS is empty, notification is not sent'])
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">Fraudulent</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($survey, 'strict')->checkbox()
                        ->label('Strict Respondent Checking', ['style' => 'font-weight:bold']) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">Recruitment</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($survey, 'panel_register_type')->dropDownList(\app\models\enums\PanelRegisterType::listData())
                        ->label('TGM Panel Registration', ['style' => 'font-weight:bold']) ?>
                </div>
                <div class="col-xs-12">
                    <?= $form->field($survey, 'tgm_recruitment')->checkbox()
                        ->label('Requires Recruitment Survey', ['style' => 'font-weight:bold']) ?>
                </div>
                <div class="col-xs-12">
                    <?= $form->field($survey, 'strict_recruitment')->checkbox()
                        ->label('Strict recruitment, disqualifies respondents', ['style' => 'font-weight:bold']) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">Postback calls</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-6">
                    <?= $form->field($survey, 'settings[' . SurveySettings::FYBER_FIN . ']')->checkbox()
                        ->label(SurveySettings::getLabel(SurveySettings::FYBER_FIN), ['style' => 'font-weight:bold']) ?>
                </div>
                <div class="col-xs-6">
                    <?= $form->field($survey, 'settings[' . SurveySettings::TAPJOY_FIN . ']')->checkbox()
                        ->label(SurveySettings::getLabel(SurveySettings::TAPJOY_FIN), ['style' => 'font-weight:bold']) ?>
                </div>
                <div class="col-xs-6">
                    <?= $form->field($survey, 'settings[' . SurveySettings::FYBER_SCR . ']')->checkbox()
                        ->label(SurveySettings::getLabel(SurveySettings::FYBER_SCR), ['style' => 'font-weight:bold']) ?>
                </div>
                <div class="col-xs-6">
                    <?= $form->field($survey, 'settings[' . SurveySettings::TAPJOY_SCR . ']')->checkbox()
                        ->label(SurveySettings::getLabel(SurveySettings::TAPJOY_SCR), ['style' => 'font-weight:bold']) ?>
                </div>
                <div class="col-xs-6">
                    <?= $form->field($survey, 'settings[' . SurveySettings::FYBER_DSQ . ']')->checkbox()
                        ->label(SurveySettings::getLabel(SurveySettings::FYBER_DSQ), ['style' => 'font-weight:bold']) ?>
                </div>
                <div class="col-xs-6">
                    <?= $form->field($survey, 'settings[' . SurveySettings::TAPJOY_DSQ . ']')->checkbox()
                        ->label(SurveySettings::getLabel(SurveySettings::TAPJOY_DSQ), ['style' => 'font-weight:bold']) ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div class="form-group text-center">
    <?= Html::button($survey->isNewRecord ? 'Create Survey' : 'Save Survey', [
        'class' => 'btn-lg btn-primary',
        'id' => 'survey__btn-save',
    ]) ?>
</div>

<?php ActiveForm::end();

$this->registerJsFile('@web/js/manage/campaign.js', ['depends' => '\app\assets\JqueryMinified']);
$this->registerJsFile('@web/js/manage/survey-update.js', ['depends' => '\app\assets\JqueryMinified']);
