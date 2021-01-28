<?php
namespace app\modules\manage\controllers;

use app\modules\manage\models\BadWords;
use app\modules\manage\models\search\BadWordsSearch;
use yii\web\NotFoundHttpException;

class BadWordsController extends BaseManageController
{
    public function actionIndex()
    {
        return $this->render('index', [
            'dataProvider' => (new BadWordsSearch())->search(\Yii::$app->request->get()),
        ]);
    }

    public function actionCreate()
    {
        $model = new BadWords();
        $post = \Yii::$app->request->post();

        if ($model->load($post) && $model->save()) {
            return $this->redirect(['/manage/bad-words']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id = null)
    {
        $model = BadWords::findOne($id);

        if (is_null($model)) {
            throw new NotFoundHttpException('');
        }

        $post = \Yii::$app->request->post();
        if ($model->load($post) && $model->save()) {
            return $this->redirect(['/manage/bad-words']);
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = BadWords::findOne($id);
        if (!is_null($model)) {
            try {
                $model->delete();
            } catch (\Throwable $e) {
            }
        }

        return $this->redirect(['/manage/bad-words']);
    }
}
