<?php
namespace app\components\fraud;

use app\components\AppHelper;
use app\components\RespondentIdentity;
use app\models\enums\SuspiciousStatus;
use app\models\RespondentSurvey;
use app\models\Respondent;
use app\models\RespondentSurveyStatus;
use yii\db\Query;

class ResponseCheck
{
    /** @var RespondentSurvey response to check */
    public $respondentSurvey;

    /** @var boolean */
    public $strict = false;

    /** @var array functions list, each function returns suspicious code */
    protected $rules = [
        'checkUnique',
        'checkPhone',
        'checkRespondentSurvey',
        'checkRespondent',
        'checkAffSub',
        'checkTemporaryBlockedIp',
//        'checkFingerprint48Hrs',
    ];

    protected $code = SuspiciousStatus::LEGAL;

    public function __construct(RespondentSurvey $respondentSurvey)
    {
        $this->respondentSurvey = $respondentSurvey;
        $this->strict = boolval($respondentSurvey->survey->strict);
        $this->code = SuspiciousStatus::LEGAL;
    }

    public function check()
    {
        if ($this->isTestRespondent()) {
            return SuspiciousStatus::LEGAL;
        }

        foreach ($this->rules as $rule) {
            $code = $this->$rule();
            $this->code = $code === SuspiciousStatus::LEGAL ? $this->code : $code;

            if ($this->code !== SuspiciousStatus::LEGAL) {
                $this->respondentSurvey->suspicious = $this->code;
                $this->respondentSurvey->save();

                if ($this->strict) {
                    throw new BlockingException($this->code);
                }
            }
        }

        return $this->code;
    }

    protected function isTestRespondent()
    {
        return $this->respondentSurvey->respondent->isTest;
    }

    protected function checkPhone()
    {
        $phone = $this->respondentSurvey->phone;
        $surveyId = $this->respondentSurvey->survey_id;
        $respondentId = $this->respondentSurvey->respondent_id;

        if ($phone) {
            $duplicates = RespondentSurvey::find()
                ->where(['phone' => $phone])
                ->andWhere(['=', 'survey_id', $surveyId])
                ->andWhere(['<>', 'respondent_id', $respondentId])
                ->count('*');

            if ($duplicates) {
                return SuspiciousStatus::PHONE_DUPLICATED;
            }
        }

        return SuspiciousStatus::LEGAL;
    }

    /**
     * @return int SuspiciousStatus value
     */
    protected function checkRespondentSurvey()
    {
        $respondent = $this->respondentSurvey->respondent;

        $subQuery = new Query();
        $subQuery->select('id')
            ->from(Respondent::tableName())
            ->where([
                'ip' => $respondent->ip,
                'device_id' => $respondent->device_id,
                'fingerprint_id' => $respondent->fingerprint_id,
            ])
            ->andWhere(['<>', 'id', $respondent->id]);

        $duplicates = RespondentSurvey::find()
            ->where(['IN', 'respondent_id', $subQuery])
            ->andWhere(['=', 'survey_id', $this->respondentSurvey->survey_id])
            ->count('*');

        if ($duplicates) {
            return SuspiciousStatus::ID_TOOKPART_SURVEY;
        }

        return SuspiciousStatus::LEGAL;
    }

    /**
     * Checks respondent suspicious status
     * @return int SuspiciousStatus value
     */
    protected function checkRespondent()
    {
        return $this->respondentSurvey->respondent->suspicious ?: SuspiciousStatus::LEGAL;
    }

    /**
     * Checks the survey has been started by another respondent
     */
    protected function checkAffSub()
    {
        $affSub = $this->respondentSurvey->tapjoy_subid;

        if ($affSub && $affSub !== '-') {
            $respSurvey = RespondentSurvey::find()
                ->where([
                    'survey_id' => $this->respondentSurvey->survey_id,
                    'tapjoy_subid' => $affSub,
                ])
                ->andWhere([
                    '<>', 'respondent_id', $this->respondentSurvey->respondent_id
                ])->one();

            if (!is_null($respSurvey)) {
                $code = SuspiciousStatus::AFFSUB_USED_TWICE;

                $this->respondentSurvey->respondent->addToBlacklist($code, $this->respondentSurvey->survey->id);

                throw new BlockingException($code);
            }
        }

        return SuspiciousStatus::LEGAL;
    }

    protected function checkTemporaryBlockedIp()
    {
        $blockingPeriod = 24 * 60 * 60;

        $respSurvey = RespondentSurvey::find()->where([
            'survey_id' => $this->respondentSurvey->survey_id,
            'ip_dec' => AppHelper::ip2long($this->respondentSurvey->respondent->ip),
        ])
            ->andWhere(['<>', 'respondent_id', $this->respondentSurvey->respondent_id])
            ->andWhere(['>=', 'started_at', AppHelper::timeUtc() - $blockingPeriod])
            ->andWhere(['<>', 'status', RespondentSurveyStatus::ACTIVE]);

        $exists = $respSurvey->one();

        if (!is_null($exists)) {
            throw new BlockingException(SuspiciousStatus::IP_HAS_FINISHED_SURVEY);
        }

        return SuspiciousStatus::LEGAL;
    }

    /**
     * Checks respondent is unique (device id, fingerprint and ip) doesn't exist
     * @return int
     */
    protected function checkUnique()
    {
        /** @var RespondentIdentity $identity */
        $identity = \Yii::$app->respondentIdentity;
        if (!$identity->respondent) {
            return SuspiciousStatus::LEGAL;
        }

        $deviceId = $identity->respondent->device_id;
        $fingerprint = $identity->getRms('fp');

        $query = RespondentSurvey::find()
            ->joinWith('respondent')
            ->andWhere([
                'respondent.device_id' => $deviceId,
                'respondent.fingerprint_id' => $fingerprint,
                'respondent.ip' => \Yii::$app->request->userIP,
            ])
            ->andWhere(['<>', 'respondent.id', $identity->respondent->id])
            ->andWhere(['respondent_survey.survey_id' => $this->respondentSurvey->survey_id])
            ->orderBy(['respondent_survey.id' => SORT_DESC])
            ->limit(1);

        $exists = $query->one();

        return !$exists ? SuspiciousStatus::LEGAL : SuspiciousStatus::ID_DUPLICATED;
    }

    protected function checkFingerprint48Hrs()
    {
        /** @var RespondentIdentity $identity */
        $identity = \Yii::$app->respondentIdentity;
        $fingerprint = $identity->getRms('fp');
        if (!$identity->respondent) {
            return SuspiciousStatus::LEGAL;
        }

        $query = RespondentSurvey::find()
            ->joinWith('respondent')
            ->where([
                'AND',
                ['=', 'survey_id', $this->respondentSurvey->survey_id],
                ['NOT IN', 'respondent_survey.status', [RespondentSurveyStatus::ACTIVE]],
                ['>=', 'respondent_survey.started_at', time() - 48 * 3600],
                ['=', 'respondent.fingerprint_id', $fingerprint],
            ])
            ->limit(1);

        $exists = $query->one();

        if (!is_null($exists)) {
            throw new BlockingException(SuspiciousStatus::FP_HAS_FINISHED_SURVEY);
        }

        return SuspiciousStatus::LEGAL;
    }
}
