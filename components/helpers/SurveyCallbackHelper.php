<?php
namespace app\components\helpers;

use app\components\enums\EndpageStatusEnum;
use app\models\RespondentSurvey;
use yii\httpclient\Client;

/**
 * Class SurveyCallbackHelper
 * @package app\components\helpers
 */
class SurveyCallbackHelper
{
    const LUCID_API_KEY = 'ce4107c5-a386-4204-93d8-8cf078c772f6';

    /**
     * @param string $status
     * @param RespondentSurvey $respondentSurvey
     * @return bool
     * @throws
     */
    protected function lucidCallback($status, $respondentSurvey)
    {
        $statusMap = [
            EndpageStatusEnum::SCR => 20,
            EndpageStatusEnum::QFL => 40,
            EndpageStatusEnum::DSQ => 30,
            EndpageStatusEnum::FIN => 10,
        ];
        $status = isset($statusMap[$status]) ? $status : EndpageStatusEnum::DSQ;
        $url = 'https://callback.samplicio.us/callback/v1/status/%s';
        $client = new Client();

        $request = $client->createRequest();
        $request->setMethod('PUT')
            ->setHeaders(['Authorization' => self::LUCID_API_KEY])
            ->setData(['status' => $statusMap[$status]])
            ->setUrl(sprintf($url, urlencode($respondentSurvey->respondent->rmsid)));

        $response = $request->send();

        return $response->isOk;
    }
}