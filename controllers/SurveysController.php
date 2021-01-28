<?php
namespace app\controllers;

use yii\web\Controller;

class SurveysController extends Controller
{
    use \app\controllers\UserLanguageTrait;

    public $layout = 'public/main';

    public function actionIndex()
    {
        return $this->render('index');
    }

}
