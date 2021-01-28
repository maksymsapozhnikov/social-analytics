<?php
namespace app\modules\manage\controllers;

use app\models\RespondentSurvey;
use yii\web\NotFoundHttpException;

/**
 * Class ResultsController
 * @package app\modules\manage\controllers
 */
class ResultsController extends BaseManageController
{
    /**
     * @param integer $id
     * @return array
     * @throws
     */
    public function actionView($id)
    {
        $this->layout = false;

        $model = RespondentSurvey::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException();
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }
}