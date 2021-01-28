<?php
namespace app\modules\manage\controllers;

use app\models\Alias;
use app\modules\manage\models\search\AliasSearch;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

/**
 * Class AliasesController
 * @package app\modules\manage\controllers
 */
class AliasesController extends BaseManageController
{
    const REST_ACTIONS = ['delete', 'reset', 'rest'];

    /**
     * {@inheritdoc}
     * @throws
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, self::REST_ACTIONS)) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $search = new AliasSearch();

        return $this->render('index', [
            'dataProvider' => $search->search(\Yii::$app->request->get()),
        ]);
    }

    public function actionEdit($id)
    {
        $alias = Alias::loadOrCreate($id);

        if (\Yii::$app->request->isPost) {
            $params = \Yii::$app->request->post();

            if ($alias->load($params) && $alias->save()) {
                return $this->go(['edit', 'id' => $id]);
            }
        }

        $alias->scenario = Alias::SCENARIO_EDIT;

        return $this->render('form', [
            'model' => $alias,
        ]);
    }

    public function actionDelete($id)
    {
        $alias = Alias::findOne($id);
        if (is_null($alias)) {
            return $this->rest(404, 'Alias Not Found');
        }

        try {
            $alias->moveToTrash();
        } catch (\Throwable $e) {
            return $this->rest(500, 'Internal Server Error');
        }

        return $this->rest();
    }

    public function actionReset($id, $param = 'used')
    {
        $alias = Alias::findOne($id);
        if (is_null($alias)) {
            return $this->rest(404, 'Alias Not Found');
        }

        switch ($param) {
            case 'used':
                $result = $alias->resetCounter();
                break;
            case 'scr':
                $result = $alias->resetScr();
                break;
            case 'dsq':
                $result = $alias->resetDsq();
                break;
            case 'qfl':
                $result = $alias->resetQfl();
                break;
            case 'block':
                $result = $alias->resetBlock();
                break;
            default:
                $result = false;
        }

        if (!$result) {
            return $this->rest(500, 'Internal Server Error');
        }

        return $this->rest();
    }

    public function actionPin($id)
    {
        $alias = Alias::findOne($id);

        if (is_null($alias)) {
            return $this->rest(404, 'Alias Not Found');
        }

        if (!$alias->changeStick()) {
            return $this->rest(500, 'Internal Server Error');
        }

        return $this->rest();
    }

    public function actionStatus($id, $status)
    {
        $alias = Alias::findOne($id);

        if (is_null($alias)) {
            return $this->rest(404, 'Alias Not Found');
        }

        $alias->status = $status;
        if (!$alias->save()) {
            return $this->rest(500, 'Internal Server Error');
        }

        return $this->rest();
    }

    public function actionRest($id = null)
    {
        try {
            switch (\Yii::$app->request->method) {
                case 'GET':
                    $alias = Alias::findOne($id);
                    if (!$alias) {
                        throw new NotFoundHttpException('Alias not found');
                    }

                    return $alias;
                    break;
                case 'POST':
                case 'PUT':
                    $request = \Yii::$app->request;
                    $attributes = Json::decode($request->rawBody);
                    $id = $id ?: ArrayHelper::getValue($attributes, 'id');
                    $alias = Alias::loadOrCreate($id);

                    if ($alias->load($attributes) && $alias->save()) {
                        return $alias;
                    }

                    return $this->rest(422, 'Survey Error');
                    break;

                default:
            }
        } catch (\Throwable $e) {
        }
    }

    public function actionSetBid($id, $bid)
    {
        $alias = Alias::findOne($id);
        $alias->setBidValue($bid);

        return $this->rest();
    }
}