<?php
namespace app\modules\manage\controllers;

use app\models\RestError;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;

/**
 * Class BaseManageController
 * Base Manager controller
 * @package app\modules\manage\controllers
 */
class BaseManageController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'denyCallback' => function() {
                    return \Yii::$app->response->redirect(['/login']);
                },
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['*'],
                    ],
                ],
            ],
        ];
    }

    protected function go($url = ['index'])
    {
        return $this->redirect(Url::to($url));
    }

    protected function rest($code = 200, $message = 'Ok')
    {
        return new RestError([
            'code' => $code,
            'message' => $message
        ]);
    }
}
