<?php
namespace app\components;

use app\components\enums\EndpageStatusEnum;
use app\components\helpers\EndpageHelper;
use app\components\helpers\PostbackHelper;
use app\models\Respondent;
use app\models\RespondentSurvey;
use app\models\Survey;
use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Class WebEndUrl
 * @package app\components
 */
class WebEndUrl extends BaseObject
{
    /**
     * @param Survey $survey
     * @param Respondent $respondent
     * @param string $status
     * @return mixed
     */
    public static function getEndPageUrlStartedSurvey($survey, $respondent, $status)
    {
        static::prepareUiEndpageUrl($survey, $respondent);

        if (!$survey || !$respondent) {
            return static::getStatusUrl($status, null);
        }

        $respondentSurvey = RespondentSurvey::getStartedSurvey($survey, $respondent);

        try {
            $respondentSurvey->saveResponse(['status' => static::getStatusConverted($status)]);
        } catch (\Throwable $e) {
        }

        $respondent->seen();
        $isPostbackSucceed = $respondentSurvey->callPostback();

        if ($isPostbackSucceed && $status === EndpageStatusEnum::SCR) {
            return static::getStatusUrl(EndpageStatusEnum::FIN, $respondentSurvey);
        }

        return static::getStatusUrl($status, $respondentSurvey);
    }

    /**
     * @param Survey $survey
     * @param Respondent $respondent
     * @param string $status
     * @return mixed
     */
    public static function getEndPageByIdentity($survey, $respondent, $status)
    {
        /** @var RespondentIdentity $identity */
        $identity = Yii::$app->respondentIdentity;

        static::prepareUiEndpageUrl($survey, $respondent);

        if (!$survey || !$respondent) {
            return static::getStatusUrl($status, null);
        }

        $respondent->seen();

        $isPostbackSucceed = PostbackHelper::call($identity->getRms('uri'), $status, $survey);

        if ($isPostbackSucceed && $status === EndpageStatusEnum::SCR) {
            return static::getStatusUrlByStatus(EndpageStatusEnum::FIN);
        }

        return static::getStatusUrlByStatus($status);
    }

    /**
     * Saves UI EndPage URL (survey.end_url) to session.
     *
     * @param $survey
     * @param $respondent
     * @return array|string
     */
    protected static function prepareUiEndpageUrl($survey, $respondent)
    {
        $session = Yii::$app->session;

        $session->remove('end_url');

        if (!$survey || !$respondent) {
            return false;
        }

        $session->set('end_url', EndpageHelper::getSurveyEndUrl($survey->url_end, $respondent->recruitmentProfile));

        return true;
    }

    /**
     * @param string $status
     * @return string
     */
    protected static function getStatusConverted($status)
    {
        $defaultStatus = 'disqualified';
        $statusMap = [
            EndpageStatusEnum::QFL => 'screened out',
            EndpageStatusEnum::SCR => 'screened out',
            EndpageStatusEnum::DSQ => 'disqualified',
            EndpageStatusEnum::FIN => 'finished',
        ];

        return ArrayHelper::getValue($statusMap, $status, $defaultStatus);
    }

    /**
     * @param string $status
     * @param RespondentSurvey $respondentSurvey
     * @return string|array
     */
    protected static function getStatusUrl($status, $respondentSurvey)
    {
        $url = EndpageHelper::getEndpageUrl($status, $respondentSurvey);

        if (Url::isRelative($url)) {
            return [$url];
        }

        return $url;
    }

    protected static function getStatusUrlByStatus($status)
    {
        $url = EndpageHelper::getEndpageUrlByIdentity($status);

        if (Url::isRelative($url)) {
            return [$url];
        }

        return $url;
    }
}