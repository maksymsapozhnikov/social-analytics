<?php
namespace app\modules\manage\controllers;

use app\models\Survey;
use app\modules\manage\models\search\SurveyAjax;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class SurveysController
 * @package app\modules\manage\controllers
 */
class SurveysController extends Controller
{
    /**
     * {@inheritDoc}
     */
    public function beforeAction($action)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    public function actionSearch($term = '')
    {
        $surveys = SurveyAjax::search($term);

        return ['results' => $surveys];
    }

    public function actionSearchByParams($term = '')
    {
        $status = \Yii::$app->request->get('status') ? : false;
        $sortParams = \Yii::$app->request->get('sortParams') ? : false;
        $limit = \Yii::$app->request->get('limit') ? : 20;
        $country = \Yii::$app->request->get('country') ? : false;

        $surveys = SurveyAjax::searchByParams($term, $status, $sortParams, $limit, $country);

        return ['results' => $surveys];
    }

    public function actionRest($id)
    {
        try {
            switch (\Yii::$app->request->method) {
                case 'GET':
                    $survey = Survey::findOne($id);
                    if (!$survey) {
                        throw new NotFoundHttpException('Alias not found');
                    }

                    return $survey;
                    break;
                default:
            }
        } catch (\Throwable $e) {
        }
    }
}