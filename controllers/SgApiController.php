<?php
namespace app\controllers;

use app\components\AppLogger;
use app\components\enums\SessionStatusEnum;
use app\components\GoogleApi;
use app\components\helpers\CarsOptionsHelper;
use app\components\LogSession;
use app\components\TransferTo;
use app\models\PostCode;
use app\models\RecruitmentProfile;
use app\models\Respondent;
use app\models\SurveyOptions;
use app\modules\manage\models\BadWords;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\Response;
use app\models\RespondentStatus;
use app\models\RestError;
use app\modules\surveybot\models\Response as SurveybotResponse;
use app\components\RespondentIdentity;
use app\components\MobileChecker;
use yii\helpers\ArrayHelper;
use app\models\Survey;
use app\models\RespondentSurvey;
use app\components\EmailChecker;
use app\components\helpers\TranslateMessage;

class SgApiController extends Controller
{
    public $enableCsrfValidation = false;
    public $layout = false;

    protected $request;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $this->request = [];
        $request = \Yii::$app->request;

        if ($request->isPost && $request->post('_')) {
            try {
                $this->request = Json::decode(base64_decode($request->post('_')));
            } catch (\Throwable $e) {
                $this->request = [];
            }
        }

        if ($request->isGet && $request->get('_')) {
            try {
                $this->request = Json::decode(base64_decode($request->get('_')));
            } catch (\Throwable $e) {
                $this->request = [];
            }
        }
    }

    public function actionCheckCoordinates()
    {
        $request = \Yii::$app->request;

        $rmsid = $request->post('rmsid');
        $lat = $request->post('latitude');
        $lon = $request->post('longitude');

        $address = GoogleApi::getAddress($lon, $lat);

        $respondent = Respondent::findOne(['rmsid' => $rmsid]);

        if ($respondent) {
            $respondent->setAttributes([
                'geo_latitude' => $lat,
                'geo_longitude' => $lon,
                'geo_address' => $address ?: '',
            ], false);

            $respondent->save();
        }

        return [
            'latitude' => $lat,
            'longitude' => $lon,
            'address' => $address ?: 'Address not found',
        ];
    }

    public function actionPostCode($code)
    {
        $code = str_pad($code, 5, '0', STR_PAD_LEFT);
        $postCode = PostCode::findOne(['postcode' => $code]);
        if (is_null($postCode)) {
            return ['postcode' => null];
        }

        return $postCode->asArray();
    }

    public function actionCheckRespondent($rmsid)
    {
        $respondentStatuses = [
            RespondentStatus::ACTIVE => 'active',
            RespondentStatus::DISQUALIFIED => 'disqualified',
        ];
        $unknownStatus  = 'unknown';

        $respondent = Respondent::findOne(['rmsid' => $rmsid]);

        if (is_null($respondent)) {
            return (new RestError(['code' => 404, 'message' => 'Respondent not found']))->asArray();
        } else {
            $response = [
                'rmsid' => $respondent->rmsid,
                'lang' => $respondent->language,
                's' => $respondent->traffic_source,
                'status' => isset($respondentStatuses[$respondent->status]) ? $respondentStatuses[$respondent->status] : $unknownStatus,
                'device_id' => $respondent->device_id,
                'device_vendor' => $respondent->device_vendor,
                'device_model' => $respondent->device_model,
                'device_type' => $respondent->getDeviceAtlasProp('primaryHardwareType'),
                'os_name' => $respondent->os_name,
                'os_version' => $respondent->os_version,
                'suspicious' => $respondent->isSuspicious(),
            ];

            $surveyBotResponse = SurveybotResponse::getLastResponse($respondent->getAttribute('sb_respondent_id'));

            if ($surveyBotResponse) {
                $ans = $surveyBotResponse->getAttribute('sb_response');
                foreach($ans as $key => $item) {
                    $key = strtolower($key);
                    if (substr($key, -3) === 'ans') {
                        $key = 'sbot.' . substr($key, 0, -3);
                        $response[$key] = $item;
                    }
                }
            }
        }

        return $response;
    }

    /**
     * @param string $rmsid
     * @return array
     */
    public function actionGetResponse($rmsid)
    {
        $rmsid = trim($rmsid);

        $respondentRmsid = substr($rmsid, 0, Respondent::RMSID_LENGTH);
        $surveyRmsid = substr($rmsid, Respondent::RMSID_LENGTH, Survey::RMSID_LENGTH);

        if (RecruitmentProfile::RMSID === $surveyRmsid) {
            $decoded = RecruitmentProfile::getProfileByRmsid($respondentRmsid);
        } else {
            $response = RespondentSurvey::findByRmsids($respondentRmsid, $surveyRmsid);
            $decoded = $response ? Json::decode($response->response) : [];
        }

        return array_change_key_case($decoded ?: [], CASE_UPPER);
    }

    /**
     * @todo refactoring is required
     * @return array
     * @throws
     */
    public function actionCheckPhone()
    {
        /** @var RespondentIdentity $identity */
        $identity = \Yii::$app->respondentIdentity;

        $request = \Yii::$app->request;

        $phone = $request->post('phone');
        $survey_rmsid = $request->post('survey_rmsid');
        $respondent_rmsid = $request->post('rmsid');

        $identity->loadRepondent($respondent_rmsid);

        preg_match('/([\w\d]{7})/', $survey_rmsid, $matches);
        $survey_rmsid = ArrayHelper::getValue($matches, 1, $survey_rmsid);

        $survey = Survey::findOne(['rmsid' => $survey_rmsid]);

        if (!$request->isPost) {
            return [
                'valid' => false,
                'message' => 'Bad request',
            ];
        }

        if (is_null($survey) || ($respondent_rmsid && is_null($identity->respondent))) {
            return [
                'valid' => false,
                'message' => 'Internal error'
            ];
        }

        $details = MobileChecker::getDetails($phone);

        if (!$details['valid']) {
            $message = 'The phone number doesn\'t exist. Please correct the number and check again.';
            return [
                'valid' => false,
                'message' => TranslateMessage::t('survey-process', $message),
            ];
        }

        if ($survey->has_topup && $survey->topup_currency != $details['currency']) {
            $message = 'Unable to top up this phone, should use {CURR} as a currency';
            return [
                'valid' => false,
                'message' => str_replace('{CURR}', $survey->topup_currency, TranslateMessage::t('survey-process', $message)),
            ];
        }

        $respSurvey = $respondent_rmsid ? RespondentSurvey::getStartedSurvey($survey, $identity->respondent) : null;

        if ($survey->has_topup && $respSurvey && !$respSurvey->is_payed) {
            $respSurvey->phone = $phone;

            return $respSurvey->sendTopUp();
        }

        return [
            'valid' => true,
            'operator' => ArrayHelper::getValue($details, 'operator', 'Unknown'),
            'payment_system' => ArrayHelper::getValue($details, 'payment_system', 'Unknown'),
        ];
    }

    public function actionCheckEmail()
    {
        $request = \Yii::$app->request;

        $email = $request->post('email');

        $details = EmailChecker::getDetails($email);

        if (!$details['valid']) {
            $details['message'] = TranslateMessage::t('survey-process', 'Please correct the email and check again');
        }

        return $details;
    }

    public function actionTransfertoCountries()
    {
        /** @var TransferTo $transferto */
        $transferto = \Yii::$app->transferTo;
        $cache = \Yii::$app->cache;
        $cacheKey = 'countries';

        $cached = $cache->get($cacheKey);
        if (false === $cached) {
            $cached = $transferto->sendRequest([
                'action' => 'pricelist',
                'info_type' => 'countries',
            ]);
            $cache->set($cacheKey, $cached, 3600);
        }

        return $cached;
    }

    public function actionTransfertoOperators($code)
    {
        $transferto = \Yii::$app->transferTo;
        $cache = \Yii::$app->cache;
        $cacheKey = 'operators-' . $code;

        $cached = $cache->get($cacheKey);
        if (false === $cached) {
            $cached = $transferto->sendRequest([
                'action' => 'pricelist',
                'info_type' => 'country',
                'content' => $code,
            ]);
            $cache->set($cacheKey, $cached, 3600);
        }

        return $cached;
    }

    /**
     * Checks if the text contains any of BadWords
     * @return array
     */
    public function actionCheckText()
    {
        if (!\Yii::$app->request->isPost) {
            return [
                'valid' => false,
            ];
        }

        $country = \Yii::$app->request->post('country');
        $text = \Yii::$app->request->post('text');

        $list = BadWords::findOne(['country' => $country]);

        if (is_null($list) || !$country) {
            return [
                'valid' => true,
            ];
        }

        return [
            'valid' => !$list->isContainedAnyWord($text),
        ];
    }

    public function actionOptions()
    {
        if (!\Yii::$app->request->isPost) {
            return [];
        }

        $identifier = \Yii::$app->request->post('identifier');
        $filter = \Yii::$app->request->post('filter', []);

        $options = SurveyOptions::findOne([
            'identifier' => $identifier,
        ]);

        if (is_null($options)) {
            return [];
        }

        return CarsOptionsHelper::prepareModel($options->filter($filter));
    }

    /**
     * Updates response's dirty-score
     * @return array
     */
    public function actionDs()
    {
        if (empty($this->request)) {
            AppLogger::warning(\Yii::$app->request->post('_'));
            AppLogger::error("[dirty-score] Wrong request type");

            \Yii::$app->response->statusCode = 400;

            return [
                'error' => true,
                'message' => 'Bad request',
            ];
        }

        $respondentRmsid = ArrayHelper::getValue($this->request, 'respondent');
        $surveyRmsid = ArrayHelper::getValue($this->request, 'survey');

        $questionId = ArrayHelper::getValue($this->request, 'question');
        $selected = ArrayHelper::getValue($this->request, 'selected');
        $dirty = ArrayHelper::getValue($this->request, 'dirty');
        $type = ArrayHelper::getValue($this->request, 'type');

        $response = RespondentSurvey::findByRmsids($respondentRmsid, $surveyRmsid);
        if (is_null($response)) {
            AppLogger::error("[dirty-score] Response not found Respondent:{$respondentRmsid}, Survey:{$surveyRmsid}");

            \Yii::$app->response->statusCode = 400;

            return [
                'error' => true,
                'message' => 'Bad request',
            ];
        }

        if ($questionId) {
            $response->updateDirtyScore($questionId, $selected, $dirty, $type);
        }

        $dirtyScore = $response->getDirtyScore();

        if (false === $dirtyScore) {
            \Yii::$app->response->statusCode = 422;

            return [
                'error' => true,
                'message' => 'Unprocessable Entity',
            ];
        }

        return [
            'dirtyscore' => \Yii::$app->formatter->asDecimal(100 - $dirtyScore, 2),
        ];
    }

    /**
     * Updates survey time-score
     * @return array
     */
    public function actionTs()
    {
        if (empty($this->request)) {
            AppLogger::warning(\Yii::$app->request->post('_'));
            AppLogger::error("[dirty-score] Wrong request type");

            \Yii::$app->response->statusCode = 400;

            return [
                'error' => true,
                'message' => 'Bad request',
            ];
        }

        $respondentRmsid = ArrayHelper::getValue($this->request, 'respondent');
        $surveyRmsid = ArrayHelper::getValue($this->request, 'survey');

        $response = RespondentSurvey::findByRmsids($respondentRmsid, $surveyRmsid);

        if (is_null($response)) {
            AppLogger::error("[dirty-score] Response not found Respondent:{$respondentRmsid}, Survey:{$surveyRmsid}");

            \Yii::$app->response->statusCode = 400;

            return [
                'error' => true,
                'message' => 'Bad request',
            ];
        }

        (new LogSession([
            'respondent' => $response->respondent,
            'survey' => $response->survey,
        ]))->set(SessionStatusEnum::ST_PROGRESS);

        if ($v = ArrayHelper::getValue($this->request, 'value', false)) {
            $qs = ArrayHelper::getValue($this->request, 'questions', []);
            $ps = ArrayHelper::getValue($this->request, 'pages', []);
            $eqs = ArrayHelper::getValue($this->request, 'exceptQuestions', []);
            $eps = ArrayHelper::getValue($this->request, 'exceptPages', []);

            $agg = $response->aggregateTimings($v, $qs, $ps, $eqs, $eps);

            return [
                'value' => is_null($agg) ? $agg : sprintf('%.3f', $agg),
            ];
        }

        $pageId = ArrayHelper::getValue($this->request, 'pageId');
        $questionId = ArrayHelper::getValue($this->request, 'questionId');
        $time = ArrayHelper::getValue($this->request, 'time');

        $timeScore = $response->updateTimeScore($pageId, $questionId, $time);

        if (!$timeScore) {
            \Yii::$app->response->statusCode = 422;

            return [
                'error' => true,
                'message' => 'Unprocessable Entity',
            ];
        }

        return [];
    }
}
