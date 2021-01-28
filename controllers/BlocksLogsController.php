<?php
namespace app\controllers;

use app\models\search\BlockLogSearch;
use yii\filters\AccessControl;
use yii\web\Controller;

class BlocksLogsController extends Controller
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
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $search = new BlockLogSearch();

        $params = \Yii::$app->request->queryParams;

        return $this->render('index', [
            'searchModel' => $search,
            'dataProvider' => $search->search($params),
        ]);
    }
}
