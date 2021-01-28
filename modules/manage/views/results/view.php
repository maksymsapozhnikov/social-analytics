<?php
/**
 * @var \app\models\RespondentSurvey $model
 */

use app\components\FormatHelper;
use app\models\enums\SuspiciousStatus;
use yii\helpers\ArrayHelper;

$ipLocation = \app\models\Ip2Location::getDetails($model->respondent->ip);

?>
<div class="row">
    <div class="col-xs-12">
        <table class="table table-striped">
            <tr>
                <th width="20%">Started</th>
                <td width="20%"><?= FormatHelper::toDate($model->started_at)?></td>
                <th width="20%">Finished</th>
                <td width="20%"><?= FormatHelper::toDate($model->finished_at)?> </td>
                <th width="20%"><?= \app\models\RespondentSurveyStatus::getTitle($model->status) ?></th>
            </tr>
        </table>
        <table class="table table-striped">
            <tr>
                <th>Answers</th>
                <td colspan="3"><div style="max-height:200px;overflow-y:scroll">
                        <pre><?= json_encode(json_decode($model->response ), JSON_PRETTY_PRINT) ?></pre>
                    </div></td>
            </tr>
            <tr>
                <th>Recruitment Profile</th>
                <td colspan="3"><div style="max-height:200px;overflow-y:scroll">
                        <pre><?= json_encode(json_decode($model->respondent->recruitmentProfile->content ), JSON_PRETTY_PRINT) ?></pre>
                    </div></td>
            </tr>
        </table>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <h4>Respondent</h4>
    </div>
</div>
<div class="row">
    <div class="col-xs-5">
        <table class="table table-striped">
            <tr>
                <th>RMSID</th>
                <td><?= $model->respondent->rmsid ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    <b><?= FormatHelper::respondentStatus($model->respondent) ?></b>,
                    <i><?= ArrayHelper::getValue(SuspiciousStatus::$titles, $model->respondent->suspicious, 'Unknown suspicious reason') ?></i>
                </td>
            </tr>
            <tr>
                <th>Failed tryings</th>
                <td><?= $model->respondent->failed_tryings ?></td>
            </tr>
            <tr>
                <th>First seen</th>
                <td><?= FormatHelper::toDate($model->respondent->registered_at) ?></td>
            </tr>
            <tr>
                <th>Last seen</th>
                <td><?= FormatHelper::toDate($model->respondent->last_seen_at) ?></td>
            </tr>
            <tr>
                <th>IP</th>
                <td><b><?= $model->respondent->ip ?></b><br>
                     <?= $ipLocation->country_name ?>, <?= $ipLocation->region_name ?>, <?= $ipLocation->city_name ?>
                </td>
            </tr>
        </table>
    </div>
    <div class="col-xs-7">
        <table class="table table-striped">
            <tr>
                <th>Device</th>
                <td>
                    <b><?= $model->respondent->device_marketing_name ?: '<i>No device marketing name</i>' ?></b>,
                    <?= $model->respondent->device_year_released ?><br>
                    <?= $model->respondent->device_vendor ?>, <?= $model->respondent->device_model ?>
                </td>
                <th>OS</th>
                <td><b><?= $model->respondent->os_name ?></b>, <?= $model->respondent->os_version ?></td>
            </tr>
            <tr>
                <th>Browser</th>
                <td colspan="3"><?= $model->respondent->browser ?></td>
            </tr>
            <tr>
                <th>Device Atlas</th>
                <td colspan="3"><div style="max-height:120px;overflow-y:scroll">
                        <pre><?= json_encode(json_decode($model->respondent->device_atlas), JSON_PRETTY_PRINT) ?></pre>
                    </div></td>
            </tr>
            <tr>
                <th>S2S Called</th>
                <td colspan="3" style="max-width:467px;text-overflow: ellipsis; white-space: nowrap; overflow: hidden;" title="<?= $model->s2s_callback ?>">
                        <xmp><?= $model->s2s_callback ?: 'N/A' ?></xmp>
                </td>
            </tr>
            <tr>
                <th>S2S Response</th>
                <td colspan="3">
                    <xmp><?= $model->s2s_response ?? 'N/A' ?></xmp>
                </td>
            </tr>
        </table>
    </div>
</div>
