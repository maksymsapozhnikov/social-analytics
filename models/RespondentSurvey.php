<?php

namespace app\models;

use app\components\AppLogger;
use app\components\enums\ResponseField;
use app\components\enums\SessionStatusEnum;
use app\components\fraud\BlockingException;
use app\components\fraud\FraudChecker;
use app\components\helpers\PostbackHelper;
use app\components\helpers\TranslateMessage;
use app\components\LogSession;
use app\components\MobileChecker;
use app\components\QueriesHelper;
use app\components\RespondentIdentity;
use app\components\TransferTo;
use app\models\account\Account;
use app\models\enums\PhoneSystemEnum;
use app\models\enums\TransfertoError;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\components\AppHelper;

/**
 * Class RespondentSurvey
 *
 * @package app\models
 * @property $id integer primary key
 * @property $respondent_id integer FK respondent.id
 * @property Respondent $respondent
 * @property $survey_id integer FK survey.id
 * @property $status integer current status for this survey
 * @property $uri string ad url clicked by respondent so start this survey
 * @property Survey $survey
 * @property $response string JSON with responses
 * @property $started_at integer time survey started
 * @property $finished_at integer time survey finished/screened out/disqualified
 * @property $tryings integer number of attempts to start a survey
 * @property integer $suspicious SuspiciousStatus
 * @property integer $ip respondent IP
 * @property integer $ip_dec respondent IP ip2long
 * @property bool $is_payed
 * @property string $phone
 * @property float $bid
 * @property string $dirty_score_json
 * @property string $dirty_score
 * @property string $timing_score_json
 * @property double $timing_score_sum
 * @property double $timing_score_avg
 * @property Alias $alias
 * @property integer $alias_id
 * @property string $jsf_project_id
 * @property string $jsf_country
 * @property string $jpi_hash
 * @property string $jc_hash
 * @property string $s2s_callback
 * @property string $s2s_response
 */
class RespondentSurvey extends ActiveRecord
{
    /** @var array a copy of the $this->response string */
    protected $_response;

    /** @var array a copy of the $this->dirty_score string */
    protected $_dirty_score = [];

    /** @var array a copy of the $this->timing_score_json string */
    protected $_timing_score = [];

    const DS_TYPE = 'TYPE';
    const DS_TYPE_CHECKBOX = 'CHECKBOX';
    const DS_DIRTY = 'DIRTY';
    const DS_SELECTED = 'SELECTED';

    const TS_STARTED = 'STARTED';
    const TS_TIMING = 'TIMING';
    const TS_QUESTION = 'QUESTION';
    const TS_PAGE = 'PAGE';

    const AG_SUM = 'sum';
    const AG_AVG = 'avg';

    public function responseAsArray()
    {
        return $this->_response;
    }

    public function setResponseOnly(array $response)
    {
        $this->_response = $response;
    }

    /**
     * {@inheritDoc}
     */
    public static function tableName()
    {
        return 'respondent_survey';
    }

    /**
     * @return bool
     */
    public function callPostback()
    {
        if ($this->s2s_callback !== null) {
            return true;
        }

        $response = PostbackHelper::call($this->uri, $this->status, $this->survey);

        $this->s2s_callback = $response->callback;
        $this->s2s_response = $response->response;

        return $response->success;
    }

    /**
     * @param string $param
     * @return string
     */
    public function getUriParam($param)
    {
        $parts = parse_url($this->uri);
        parse_str($parts['query'], $query);

        return ArrayHelper::getValue($query, $param, '');
    }

    /**
     * Returns the reason respondent can't start the survey
     * @return string|null the reason message
     */
    public function cantSurvey()
    {
        if ($this->status != RespondentSurveyStatus::ACTIVE) {
            return 'Respondent already finished this survey';
        }

        if ($this->respondent->isBlocked()) {
            return 'Respondent is in the blacklist';
        }

        if ($this->hasFinishedCampaign()) {
            return 'Respondent has finished this campaign';
        }

        if ($this->hasFinishedUrl()) {
            return 'Respondent finished the survey with the same SurveyGizmo URL';
        }
    }

    /**
     * @todo refactor this method
     */
    public function hasFinishedCampaign()
    {
        if ($this->survey->campaign_id) {
            $respondentId = $this->respondent_id;
            $status = RespondentSurveyStatus::ACTIVE;
            $query = <<<SQL
            select 1
            from respondent_survey rs
            where respondent_id = $respondentId
            and survey_id in (
                select id from survey where campaign_id = {$this->survey->campaign_id}
            )
            and status <> $status
            limit 1
SQL;

            return !!\Yii::$app->db->createCommand($query)->queryScalar();
        }

        return false;
    }

    /**
     * @todo refactor this method
     */
    public function hasFinishedUrl()
    {
        $respondentId = $this->respondent_id;
        $status = RespondentSurveyStatus::ACTIVE;
        $query = <<<SQL
            select 1
            from respondent_survey rs
            where respondent_id = $respondentId
            and survey_id in (
                select id from survey where url = '{$this->survey->url}'
            )
            and status <> $status
            limit 1
SQL;

        return !!\Yii::$app->db->createCommand($query)->queryScalar();
    }

    protected function updateLogSession()
    {
        if (!$this->getDirtyAttributes(['status'])) {
            return;
        }

        $logSession = new LogSession([
            'respondent' => $this->respondent,
            'survey' => $this->survey,
        ]);

        $statuses = [
            RespondentSurveyStatus::SCREENED_OUT => SessionStatusEnum::ST_SCREENED_OUT,
            RespondentSurveyStatus::DISQUALIFIED => SessionStatusEnum::ST_DISQUALIFIED,
            RespondentSurveyStatus::FINISHED => SessionStatusEnum::ST_FINISHED,
        ];

        if ($status = ArrayHelper::getValue($statuses, $this->status, false)) {
            $logSession->set($status);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->isNewRecord) {
            $this->started_at = AppHelper::timeUtc();
        }

        $this->updateLogSession();

        if (parent::save($runValidation, $attributeNames)) {
            Survey::updateStatistics($this->survey_id);

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $id = $this->survey_id;

        $result = parent::delete();

        if ($result) {
            Survey::updateStatistics($id);
        }

        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRespondent()
    {
        return $this->hasOne(Respondent::class, ['id' => 'respondent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSurvey()
    {
        return $this->hasOne(Survey::className(), ['id' => 'survey_id']);
    }

    /**
     *
     * @param $respondentRmsid string
     * @param $surveyRmsid string
     * @return null|static
     */
    public static function findByRmsids($respondentRmsid, $surveyRmsid)
    {
        $respondent = Respondent::findOne(['rmsid' => $respondentRmsid]);
        $survey = Survey::findOne(['rmsid' => $surveyRmsid]);

        if (is_null($respondent) || is_null($survey)) {
            return null;
        }

        return static::findOne([
            'respondent_id' => $respondent->id,
            'survey_id' => $survey->id,
        ]);
    }

    protected function updateInfoQuestions()
    {
        try {
            $surveyQuestions = array_keys($this->_response);
            ArrayHelper::removeValue($surveyQuestions, ResponseField::SG_QUESTION_SKU);
            ArrayHelper::removeValue($surveyQuestions, '');
            $surveyQuestions = array_values($surveyQuestions);

            QueriesHelper::addAnswerKeys($surveyQuestions);
        } catch (\Throwable $e) {
        }
    }

    /**
     * Checks if the survey is active
     * @return bool
     */
    public function isActive()
    {
        return $this->status === RespondentSurveyStatus::ACTIVE;
    }

    /**
     * Checks if the survey is finished successfully
     * @return bool
     */
    public function isFinished()
    {
        return $this->status === RespondentSurveyStatus::FINISHED;
    }

    /**
     * Processes response posted and merges into the saved one
     * @param $posted array
     * @throws \Exception
     */
    protected function loadResponse($posted)
    {
        $posted = ArrayHelper::toArray($posted);
        $posted = array_map('trim', $posted);
        $newResponse = array_change_key_case($posted, CASE_UPPER);
        if (count($posted) !== count($newResponse)) {
            AppLogger::warning('Response contains wrong keys (various cases for one key). Survey: ' . $this->survey->rmsid);
        }
        $this->setDefaults($newResponse);
        $this->status = $this->getPostedStatus($newResponse[ResponseField::STATUS]);
        $this->removeRedundant($newResponse);

        $this->_response = (array)$this->_response;
        $this->_response = array_merge($this->_response, $newResponse);
    }

    protected function fetchAttributeFromResponse($attribute, $responseKey, $defaultValue = null)
    {
        $this->{$attribute} = ArrayHelper::getValue($this->_response, $responseKey, $defaultValue);
    }

    /**
     * Dirty-Score
     * @return float value between 0 and 100
     */
    public function calculateDirtyScore()
    {
        $overall = 0.0;
        $matched = 0.0;

        $threshold = 2;

        $this->_dirty_score = $this->_dirty_score ?: [];

        if (!$this->_dirty_score) {
            return 0.0;
        }

        foreach ($this->_dirty_score as $item) {
            $dirty = ArrayHelper::getValue($item, self::DS_DIRTY, []);
            $selected = ArrayHelper::getValue($item, self::DS_SELECTED, []);

            $weight = count(array_intersect($dirty, $selected));
            $intersection = count(array_intersect($dirty, $selected));

            if ($weight > 0) {
                $matched += $weight * ($intersection/count($dirty));
                $overall += $weight;
            }
        }

        return $overall > $threshold ? 100*$matched/$overall : 0.0;
    }

    public function beforeSave($insert)
    {
        $this->bid = floatval($this->bid);
        $this->response = Json::encode($this->_response);
        $this->dirty_score_json = Json::encode($this->_dirty_score);
        $this->dirty_score = $this->calculateDirtyScore();

        $this->timing_score_json = Json::encode($this->_timing_score);
        $this->timing_score_sum = $this->aggregateTimings(self::AG_SUM);
        $this->timing_score_avg = $this->aggregateTimings(self::AG_AVG);

        /** denormalized data and hashes */
        $this->fetchAttributeFromResponse('jsf_project_id', ResponseField::PROJECT_ID, $this->jsf_project_id);
        $this->jsf_project_id = trim($this->jsf_project_id) ?: null;

        $this->fetchAttributeFromResponse('jsf_country', ResponseField::COUNTRY, $this->jsf_country);
        $this->jsf_country = trim($this->jsf_country) ?: null;

        $this->jpi_hash = new Expression('crc32(:jsf_project_id)', [':jsf_project_id' => $this->jsf_project_id]);
        $this->jc_hash = new Expression('crc32(:jsf_country)', [':jsf_country' => $this->jsf_country]);

        return parent::beforeSave($insert);
    }

    public function afterFind()
    {
        parent::afterFind();

        $this->_response = Json::decode($this->response);
        $this->_response = is_array($this->_response) ? $this->_response : [];
        $this->_response = array_change_key_case($this->_response, CASE_UPPER);

        $this->_dirty_score = Json::decode($this->dirty_score_json);
        $this->_dirty_score = is_array($this->_dirty_score) ? $this->_dirty_score : [];

        $this->_timing_score = Json::decode($this->timing_score_json);
        $this->_timing_score = is_array($this->_timing_score) ? $this->_timing_score : [];
    }

    /**
     * Save respondent's response into the DB
     * @param $posted array
     * @return bool
     * @throws \Exception
     */
    public function saveResponse($posted)
    {
        if (!$this->isActive()) {
            throw new \Exception('Respondent already finished this survey', 400);
        }

        $this->loadResponse($posted);
        $this->finished_at = AppHelper::timeUtc();

        if ($this->status === RespondentSurveyStatus::DISQUALIFIED) {
            $this->respondent->disqualify($this->survey->id);
            if ($this->alias_id && $aliasModel = Alias::findOne($this->alias_id)) {
                $aliasModel->countDsq();
            }
        }

        if (!$this->is_payed) {
            $this->fetchAttributeFromResponse('phone', ResponseField::PHONE, $this->phone);
        }
        $this->fetchAttributeFromResponse('bid', ResponseField::BID);

        if ($this->phone && $this->survey->has_topup && !$this->is_payed) {
            $check = MobileChecker::getDetails($this->phone);
            if (!$check['valid']) {
                throw new \Exception('Phone number doesn\'t exist or out of range', 422);
            }
            if ($check['currency'] != $this->survey->topup_currency) {
                throw new \Exception('Wrong phone currency: ' . $check['currency'] . '. Expected: ' . $this->survey->topup_currency . '.', 422);
            }
        }

        try {
            /** @var FraudChecker $fraudChecker */
            $fraudChecker = \Yii::$app->fraudChecker;
            $this->suspicious = $fraudChecker->checkResponse($this);
        } catch (BlockingException $e) {
            $this->suspicious = $e->getCode();
            if ($this->status === RespondentSurveyStatus::DISQUALIFIED && $this->alias_id && $aliasModel = Alias::findOne($this->alias_id)) {
                $aliasModel->countDsq();
            }
        }

        $saved = $this->save();

        if (!$saved) {
            throw new \Exception($this->getFirstErrors()[0], 500);
        }

        $this->updateInfoQuestions();

        if ($this->isFinished()) {
            $this->onSurveyFinished();
        }

        return true;
    }

    /**
     * @throws \Exception
     */
    protected function onSurveyFinished()
    {
        if ($this->survey->has_topup && !$this->is_payed) {
            $this->is_payed = true;
            $this->save();
            $account = Account::addSurveyIncentive($this);
            $spent = $account->paySurveyIncentive($this->survey->topup_value, $this->survey->topup_sms);
            $this->survey->updateSpent($spent);
        }

        if ($this->alias) {
            $this->alias->onSurveyFinished();
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAlias()
    {
        return $this->hasOne(Alias::class, ['id' => 'alias_id']);
    }

    /**
     * Sets defaults for the response posted
     * @param $posted array
     */
    protected function setDefaults(array &$posted)
    {
        $posted[ResponseField::STATUS] = ArrayHelper::getValue($posted, ResponseField::STATUS);
    }

    /**
     * Removes fields received but not required to be saved in the response JSON
     * @param $responses array
     */
    protected function removeRedundant(array &$responses)
    {
        ArrayHelper::remove($responses, ResponseField::RESPONDENT_RMSID);
        ArrayHelper::remove($responses, ResponseField::SURVEY_RMSID);
        ArrayHelper::remove($responses, ResponseField::STATUS);
    }

    /**
     * Converts REST API string status into integer
     * @param $status string
     * @return int
     */
    protected function getPostedStatus($status)
    {
        $statuses = [
            'active' => RespondentSurveyStatus::ACTIVE,
            'finished' => RespondentSurveyStatus::FINISHED,
            'screened out' => RespondentSurveyStatus::SCREENED_OUT,
            'disqualified' => RespondentSurveyStatus::DISQUALIFIED,
        ];

        return ArrayHelper::getValue($statuses, $status, RespondentSurveyStatus::ACTIVE);
    }

    /**
     * Starts new respondent survey or returns an existent
     * @param $id int survey identifier
     * @return RespondentSurvey|bool
     */
    public static function startSurvey($id, $alias_id = null)
    {
        /** @var RespondentIdentity $identity */
        $identity = \Yii::$app->respondentIdentity;

        $respSurvey = RespondentSurvey::findOne([
            'respondent_id' => $identity->respondent->id,
            'survey_id' => $id,
        ]);

        if (is_null($respSurvey)) {
            $respSurvey = new RespondentSurvey([
                'respondent_id' => $identity->respondent->id,
                'survey_id' => $id,
                'status' => RespondentSurveyStatus::ACTIVE,
                'uri' => $identity->getRms('uri'),
                'tapjoy_subid' => $identity->getUriParam('aff_sub2'),
                'ip' => $identity->respondent->ip,
                'ip_dec' => AppHelper::ip2long($identity->respondent->ip),
                'started_at' => AppHelper::timeUtc(),
                'alias_id' => $alias_id,
                'bid' => $identity->getUriParam('bd'),
            ]);

            $respSurvey->jsf_project_id = $respSurvey->survey->project_id ?: null;
            $respSurvey->jsf_country = $respSurvey->survey->project_id ? $respSurvey->survey->country : null;
        } else if ($respSurvey->status == RespondentSurveyStatus::ACTIVE) {
            $respSurvey->tryings = $respSurvey->tryings + 1;
            $respSurvey->save();
        }

        return $respSurvey;
    }

    /**
     * Returns a survey started by respondent.
     *
     * @param Survey $survey
     * @param Respondent $respondent
     * @return RespondentSurvey|null
     */
    public static function getStartedSurvey(Survey $survey, Respondent $respondent)
    {
        $respSurvey = RespondentSurvey::findOne([
            'respondent_id' => $respondent->id,
            'survey_id' => $survey->id,
        ]);

        if (is_null($respSurvey)) {
            $respSurvey = new RespondentSurvey([
                'respondent_id' => $respondent->id,
                'survey_id' => $survey->id,
                'status' => RespondentSurveyStatus::ACTIVE,
                'uri' => 'unknown',
            ]);

            $respSurvey->save();
        }

        return $respSurvey;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function sendTopUp()
    {
        set_time_limit(0);
        if (RespondentSurvey::findOne(['survey_id' => $this->survey->id, 'phone' => $this->phone, 'is_payed' => true])) {
            return [
                'valid' => false,
                'message' => TranslateMessage::t('survey-process', 'This phone number has been payed already for this survey'),
            ];
        }

        $phoneCache = PhoneCache::findOne(['phone' => $this->phone]);

        if (!$phoneCache->canPay()) {
            return $phoneCache->getReason();
        }

        /** @var TransferTo $transferTo */
        $transferTo = \Yii::$app->transferTo;

        $ttResponse = $transferTo->sendTopUps($this->phone, $this->survey->topup_value, $this->survey->topup_sms);

        if ($transferTo->isError()) {
            if ($transferTo->errorCode == TransfertoError::POSTPAID) {
                $account = Account::findOrCreate([
                    'phone' => $this->phone,
                    'currency' => $phoneCache->currency,
                    'value' => 0,
                    'payment_system' => PhoneSystemEnum::POSTPAID,
                ]);
                $account->payment_system = PhoneSystemEnum::POSTPAID;
                $account->save();

                $phoneCache->payment_system = PhoneSystemEnum::POSTPAID;
                $phoneCache->save();

                return [
                    'valid' => false,
                    'message' => TranslateMessage::t('survey-process', 'Please provide prepaid phone number'),
                ];
            }

            return [
                'valid' => false,
                'message' => 'Internal error',
            ];
        }

        $phoneCache->payment_system = PhoneSystemEnum::PREPAID;
        $phoneCache->save();

        $this->is_payed = true;
        $this->save();

        $this->survey->status = RespondentSurveyStatus::FINISHED;
        $this->survey->save();
        $this->survey->updateSpent($transferTo->getSpentValue());

        // creates all required records:
        $account = Account::addSurveyIncentive($this);
        $account->payment_system = PhoneSystemEnum::PREPAID;
        $accountTransaction = $account->addAccountTransaction($this->survey->topup_value);

        $accountTransaction->details = Json::encode($ttResponse);
        $accountTransaction->save();

        return [
            'valid' => true,
            'payment_system' => PhoneSystemEnum::getTitle($phoneCache->payment_system),
            'operator' => $phoneCache->operator,
        ];
    }

    public function isSuspicious()
    {
        return !!$this->suspicious || $this->respondent->isSuspicious();
    }

    /**
     * @param $questionId
     * @param $selected
     * @param $dirty
     * @param string $type
     * @return bool|mixed
     */
    public function updateDirtyScore($questionId, $selected, $dirty, $type)
    {
        if (!$this->isActive()) {
            AppLogger::error('[RespondentSurvey:updateDirtyScore] Trying to update an inactive response [' . $this->id . ']');

            return false;
        }

        $this->_dirty_score[$questionId] = [
            self::DS_TYPE => $type,
            self::DS_DIRTY => $dirty,
            self::DS_SELECTED => $selected,
        ];

        $this->save();

        return $this->getDirtyScore();
    }

    public function getDirtyScore()
    {
        return $this->dirty_score ?: 0;
    }

    /**
     *
     * @param $pageId
     * @param $questionId
     * @param $time
     * @return bool
     */
    public function updateTimeScore($pageId, $questionId, $time)
    {
        if (!$this->isActive()) {
            AppLogger::error('[RespondentSurvey:updateDirtyScore] Trying to update an inactive response [' . $this->id . ']');

            return false;
        }

        $this->setTimingObject($pageId, $questionId, $time);

        $this->save();

        return true;
    }

    protected function setTimingObject($pageId, $questionId, $time)
    {
        $hasSet = false;

        $this->_timing_score = $this->_timing_score ?: [];

        foreach ($this->_timing_score as $key => $tobj) {
            if ($tobj[self::TS_QUESTION] === $questionId && $tobj[self::TS_PAGE] === $pageId) {
                $this->_timing_score[$key] = $this->prepareTimingObject($pageId, $questionId, $time);
                $hasSet = true;
            }
        }

        if (!$hasSet) {
            $this->_timing_score[] = $this->prepareTimingObject($pageId, $questionId, $time);
        }
    }

    protected function prepareTimingObject($pageId, $questionId, $time)
    {
        $now = time();

        return [
            self::TS_PAGE => $pageId,
            self::TS_QUESTION => $questionId,
            self::TS_STARTED => $now - $time,
            self::TS_TIMING => $time,
        ];
    }

    public function aggregateTimings($v, $qs = [], $ps = [], $eqs = [], $eps = [])
    {
        $sum = null;
        $count = 0;

        $lowThreshold = 0.750;
        $hghThreshold = 30;

        $this->_timing_score = $this->_timing_score ?: [];

        foreach ($this->_timing_score as $tobj) {
            $inQuestion = in_array($tobj[self::TS_QUESTION], $qs) || empty($qs);
            $inPage = in_array($tobj[self::TS_PAGE], $qs) || empty($ps);
            $exQuestion = in_array($tobj[self::TS_QUESTION], $eqs);
            $exPage = in_array($tobj[self::TS_PAGE], $eps);

            if ($inQuestion && $inPage && !$exQuestion && !$exPage) {

                $timeValue = $tobj[self::TS_TIMING];

                $filterAverage = $v == self::AG_AVG && $timeValue >= $lowThreshold && $timeValue <= $hghThreshold;
                $isSumma = $v != self::AG_AVG;

                if ($filterAverage || $isSumma) {
                    $sum += $timeValue;
                    ++$count;
                }
            }
        }

        switch ($v) {
            case self::AG_SUM:
                $result = $sum;
                break;
            case self::AG_AVG:
                $result = $count > 0 ? $sum/$count : null;
                break;
            default:
                $result = null;
        }

        return $result;
    }
}
