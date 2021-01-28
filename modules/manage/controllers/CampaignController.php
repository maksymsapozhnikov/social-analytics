<?php
namespace app\modules\manage\controllers;

use app\components\exceptions\ValidationError;
use app\modules\manage\models\Campaign;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Response;

class CampaignController extends BaseManageController
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
    }

    public function actionIndex()
    {
        try {
            $request = \Yii::$app->request;
            $attributes = Json::decode($request->rawBody);
            switch (\Yii::$app->request->method) {
                case 'GET':
                    $campaigns = Campaign::find()
                        ->select(['id', 'name'])
                        ->andWhere(['like', 'name', $request->get('q', '')])
                        ->orderBy('name')
                        ->asArray()
                        ->all();

                    return $campaigns;
                    break;
                case 'POST':
                    $result = $this->create($attributes);
                    break;
            }

        } catch (ValidationError $e) {
            \Yii::$app->response->statusCode = 422;
            $result = [
                'message' => $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            \Yii::$app->response->statusCode = 500;
            $result = [
                'message' => 'Internal Server Error.',
            ];
        }

        return $result;
    }

    /**
     * @param array $attributes
     * @return array
     * @throws ValidationError
     */
    protected function create(array $attributes)
    {
        $model = new Campaign();

        $model->setAttributes($attributes);
        if (!$model->save()) {
            $error = ArrayHelper::getValue(array_pop($model->getErrors()), 0);

            throw new ValidationError($error);
        }

        return $model->attributes;
    }
}
