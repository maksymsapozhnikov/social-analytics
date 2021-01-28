<?php
namespace app\components\helpers;

use app\components\enums\SurveySettings;
use app\components\enums\TrafficSource;
use app\models\PostbackResponse;
use app\models\RespondentSurveyStatus;
use app\models\Survey;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;

/**
 * Class PostbackHelper
 * @package app\components\helpers
 */
class PostbackHelper
{
    const UP_TRAFFIC_SOURCE = 's';
    const UP_FYBER_AFFSUB2 = 'aff_sub2';
    const UP_FYBER_APPID = 'app';
    const UP_TAPJOY_AFFSUB2 = 'aff_sub2';

    /**
     * @param string $uri
     * @param string $status
     * @param Survey $survey
     * @return PostbackResponse
     */
    public static function call($uri, $status, $survey)
    {
        $trafficSource = static::getUriParam($uri, self::UP_TRAFFIC_SOURCE);

        if (!static::isPostbackRequired($trafficSource, $status, $survey)) {
            return new PostbackResponse(['callback' => 'is not required']);
        }

        switch ($trafficSource) {
            case TrafficSource::TAPJOY:
                return static::tapjoyCallback($uri);

            case TrafficSource::FYBER:
                return static::fyberCallback($uri);

            default:
        }

        return new PostbackResponse();
    }

    /**
     * Calls Fyber postback
     *
     * @param string $uri
     * @return PostbackResponse
     */
    protected static function fyberCallback($uri)
    {
        $affSub2 = static::getUriParam($uri, self::UP_FYBER_AFFSUB2);
        $appId = static::getUriParam($uri, self::UP_FYBER_APPID);

        $request = PostbackHelper::fyberRequest($affSub2, $appId);

        return static::sendPostbackRequest($request);
    }

    /**
     * Calls Tapjoy postback
     *
     * @param string $uri
     * @return PostbackResponse
     * @throws
     */
    protected static function tapjoyCallback($uri)
    {
        $transactionId = static::getUriParam($uri, self::UP_TAPJOY_AFFSUB2);
        $request = PostbackHelper::tapjoyRequest($transactionId);

        return static::sendPostbackRequest($request);
    }

    /**
     * Sends the request and save URL and response to s2s attributes
     *
     * @param \yii\httpclient\Request $request
     * @return PostbackResponse
     * @throws
     */
    protected static function sendPostbackRequest($request)
    {
        $response = $request->send();

        $postbackResponse = new PostbackResponse([
            'callback' => $request->getUrl(),
            'response' => $response->getContent(),
            'success' => $response->isOk,
        ]);

        return $postbackResponse;
    }

    /**
     * @param string $uri
     * @param string $param
     * @return string
     */
    protected static function getUriParam($uri, $param)
    {
        $parts = parse_url($uri);
        parse_str($parts['query'], $query);

        return ArrayHelper::getValue($query, $param, '');
    }

    /**
     * @param $trafficSource
     * @param $status
     * @param Survey $survey
     * @return bool
     */
    public static function isPostbackRequired($trafficSource, $status, $survey)
    {
        $map = [
            TrafficSource::FYBER => [
                RespondentSurveyStatus::DISQUALIFIED => SurveySettings::FYBER_DSQ,
                RespondentSurveyStatus::SCREENED_OUT => SurveySettings::FYBER_SCR,
                RespondentSurveyStatus::FINISHED => SurveySettings::FYBER_FIN,
            ],
            TrafficSource::TAPJOY => [
                RespondentSurveyStatus::DISQUALIFIED => SurveySettings::TAPJOY_DSQ,
                RespondentSurveyStatus::SCREENED_OUT => SurveySettings::TAPJOY_SCR,
                RespondentSurveyStatus::FINISHED => SurveySettings::TAPJOY_FIN,
            ],
        ];

        $key = ArrayHelper::getValue($map, "{$trafficSource}.{$status}", false);

        return $key ? ArrayHelper::getValue($survey->settings, $key, false) : false;
    }

    /**
     * @param string $affSub2
     * @param string $appId
     * @return \yii\httpclient\Request
     * @throws
     */
    public static function fyberRequest($affSub2, $appId)
    {
        $fyberUrl = 'https://service.fyber.com/actions/v2?answer_received=%s&action_id=%s&appid=%s&subid=%s';

        $answerReceived = 0;
        $actionId = 'SRV';

        return (new Client())->createRequest()
            ->setMethod('GET')
            ->setUrl(sprintf($fyberUrl, $answerReceived, $actionId, $appId, $affSub2));
    }

    /**
     * @param string $transactionId
     * @return \yii\httpclient\Request
     * @throws
     */
    public static function tapjoyRequest($transactionId)
    {
        $urlTemplate = 'http://tapjoy.go2cloud.org/SP1vB?adv_sub=SUB_ID&amount=AMOUNT&transaction_id=%s';

        return (new Client())->createRequest()
            ->setMethod('POST')
            ->setUrl(sprintf($urlTemplate, $transactionId));
    }
}