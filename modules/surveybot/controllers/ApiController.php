<?php
namespace app\modules\surveybot\controllers;

use app\modules\surveybot\models\Response;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Controller;

/**
 * @author Vlad Ilinyh <v.ilinyh@gmail.com>
 */
class ApiController extends Controller
{
    public $layout = false;
    public $enableCsrfValidation = false;

    protected $surveyResponse;

    public function init()
    {
        parent::init();

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }

    public function actionToken()
    {
        return [
            'token' => $this->module->apiKey,
        ];
    }

    public function actionResponse()
    {
        $model = new Response();

        $this->surveyResponse = Json::decode(\Yii::$app->getRequest()->getRawBody());

        $respondent = $this->getResponseValue('response.respondent', []);
        unset($respondent['attributes']);

        $response = $this->getResponseValue('response.respondent.attributes', []);
        $responseToSave = [];

        foreach($response as $item) {
            $key = ArrayHelper::getValue($item, 'key');
            $value = ArrayHelper::getValue($item, 'value');
            $responseToSave[$key] = $value;
        }

        $model->setAttributes([
            'sb_survey_id' => $this->getResponseValue('survey.id'),
            'sb_survey_name' => $this->getResponseValue('survey.name'),
            'sb_response_id' => $this->getResponseValue('response.id'),
            'started_at' => $this->getResponseValue('response.started_at'),
            'completed_at' => $this->getResponseValue('response.completed_at'),
            'sb_respondent_id' => $this->getResponseValue('response.respondent.id'),
            'sb_respondent' => $respondent,
            'sb_response' => $responseToSave,
        ], false);

        $isModelSaved = $model->save();

        \Yii::$app->response->setStatusCode($isModelSaved ? 200 : 422);

        return [
            'status' => $isModelSaved ? 'ok' : 'error',
            'message' => $isModelSaved ? 'Response saved successfully' : \yii\helpers\ArrayHelper::getValue(array_values($model->getErrors()), '0.0'),
        ];
    }

    /**
     * @todo inappropriate place for this function
     */
    protected function getResponseValue($key, $default = null)
    {
        return ArrayHelper::getValue($this->surveyResponse, $key, $default);
    }
}
